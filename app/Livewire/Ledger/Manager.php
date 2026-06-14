<?php

namespace App\Livewire\Ledger;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Supplier;
use App\Services\LedgerService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    public string $tab = 'customer';

    public ?int $partyId = null;

    public string $branchFilter = '';

    public string $voucher = '';

    public string $fromDate = '';

    public string $toDate = '';

    // Payment / receipt modal
    public bool $showModal = false;

    public string $modalType = 'receipt';

    public $amount = '';

    public ?int $accountId = null;

    public string $method = 'cash';

    public string $reference = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->partyId = null;
        $this->resetPage();
    }

    public function selectParty(int $id): void
    {
        $this->partyId = $id;
        $this->resetPage();
    }

    public function clearParty(): void
    {
        $this->partyId = null;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['partyId', 'branchFilter', 'voucher', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    public function updated($name): void
    {
        if (in_array($name, ['branchFilter', 'voucher', 'fromDate', 'toDate'])) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function parties()
    {
        return match ($this->tab) {
            'customer' => Customer::where('status', 'active')->orderBy('name')->get(),
            'supplier' => Supplier::where('status', 'active')->orderBy('name')->get(),
            'cash' => Account::where('type', 'cash')->orderBy('name')->get(),
            'bank' => Account::where('type', 'bank')->orderBy('name')->get(),
            default => collect(),
        };
    }

    #[Computed]
    public function accounts()
    {
        return Account::orderBy('name')->get();
    }

    #[Computed]
    public function entries()
    {
        $query = LedgerEntry::query()
            ->where('ledger_type', $this->tab)
            ->latest('transaction_date')
            ->latest('id');

        if ($this->partyId) {
            $column = match ($this->tab) {
                'customer' => 'customer_id',
                'supplier' => 'supplier_id',
                'cash', 'bank' => 'account_id',
                default => null,
            };
            if ($column) {
                $query->where($column, $this->partyId);
            }
        }

        if ($this->branchFilter !== '') {
            $query->where('branch_id', (int) $this->branchFilter);
        }
        if ($this->voucher !== '') {
            $query->where('voucher_no', 'like', '%'.$this->voucher.'%');
        }
        if ($this->fromDate !== '') {
            $query->whereDate('transaction_date', '>=', $this->fromDate);
        }
        if ($this->toDate !== '') {
            $query->whereDate('transaction_date', '<=', $this->toDate);
        }

        return $query->paginate(12);
    }

    #[Computed]
    public function selectedParty()
    {
        if (! $this->partyId) {
            return null;
        }

        return match ($this->tab) {
            'customer' => Customer::find($this->partyId),
            'supplier' => Supplier::find($this->partyId),
            'cash', 'bank' => Account::find($this->partyId),
            default => null,
        };
    }

    #[Computed]
    public function partySummary(): ?array
    {
        $party = $this->selectedParty;
        if (! $party) {
            return null;
        }

        $column = match ($this->tab) {
            'customer' => 'customer_id',
            'supplier' => 'supplier_id',
            default => 'account_id',
        };

        $totals = LedgerEntry::where('ledger_type', $this->tab)
            ->where($column, $party->id)
            ->selectRaw('COALESCE(SUM(debit),0) as debit, COALESCE(SUM(credit),0) as credit')
            ->first();

        return [
            'name' => $party->name,
            'opening' => (float) ($party->opening_balance ?? 0),
            'current' => (float) ($party->current_balance ?? 0),
            'debit' => (float) $totals->debit,
            'credit' => (float) $totals->credit,
        ];
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'receivable' => (float) Customer::sum('current_balance'),
            'payable' => (float) Supplier::sum('current_balance'),
            'cash' => (float) Account::where('type', 'cash')->sum('current_balance'),
            'bank' => (float) Account::where('type', 'bank')->sum('current_balance'),
            'today_payments' => (float) Payment::where('direction', 'out')->whereDate('payment_date', today())->sum('amount'),
            'today_receipts' => (float) Payment::where('direction', 'in')->whereDate('payment_date', today())->sum('amount'),
        ];
    }

    public function openModal(string $type): void
    {
        $this->modalType = $type;
        $this->reset(['amount', 'reference']);
        $this->method = 'cash';
        $this->accountId = $this->accounts->firstWhere('type', 'cash')?->id ?? $this->accounts->first()?->id;
        $this->showModal = true;
    }

    public function save(LedgerService $ledger): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'accountId' => ['nullable', 'integer', 'exists:accounts,id'],
            'method' => ['required', 'in:cash,card,bank,cheque'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $party = $this->selectedParty;
        if (! $party) {
            $this->addError('amount', 'Select a party first.');

            return;
        }

        $branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);

        if ($this->modalType === 'receipt' && $this->tab === 'customer') {
            $ledger->recordCustomerReceipt($party, (float) $this->amount, $this->accountId, $this->method, $this->reference ?: null, $branchId, Auth::id());
        } elseif ($this->modalType === 'payment' && $this->tab === 'supplier') {
            $ledger->recordSupplierPayment($party, (float) $this->amount, $this->accountId, $this->method, $this->reference ?: null, $branchId, Auth::id());
        } else {
            $this->addError('amount', 'This action is not available for the selected ledger.');

            return;
        }

        $this->showModal = false;
        unset($this->entries, $this->summary, $this->partySummary, $this->selectedParty);
        $this->dispatch('ledger-posted');
    }

    public function render()
    {
        return view('livewire.ledger.manager', [
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }
}
