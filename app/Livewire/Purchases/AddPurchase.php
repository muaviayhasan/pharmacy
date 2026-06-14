<?php

namespace App\Livewire\Purchases;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AddPurchase extends Component
{
    public int $branchId;

    public ?int $supplierId = null;

    public string $supplierInvoiceNo = '';

    public string $invoiceDate = '';

    public string $dueDate = '';

    public string $paymentType = 'credit';

    public string $notes = '';

    public float $discount = 0;

    public float $paidAmount = 0;

    public string $search = '';

    /** @var array<int, array<string, mixed>> */
    public array $lines = [];

    public function mount(): void
    {
        $this->branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);
        $this->invoiceDate = now()->toDateString();
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::where('status', 'active')->orderBy('name')->get();
    }

    #[Computed]
    public function branches()
    {
        return Branch::orderBy('name')->get();
    }

    #[Computed]
    public function supplierInfo(): ?Supplier
    {
        return $this->supplierId ? Supplier::find($this->supplierId) : null;
    }

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->search);
        if (strlen($term) < 2) {
            return collect();
        }

        return Medicine::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")
                ->orWhere('barcode', $term))
            ->with('manufacturer')
            ->limit(8)
            ->get();
    }

    public function addMedicine(int $medicineId): void
    {
        $m = Medicine::find($medicineId);
        if (! $m) {
            return;
        }

        $this->lines[] = [
            'medicine_id' => $m->id,
            'name' => $m->name,
            'generic' => $m->generic_name,
            'manufacturer' => $m->manufacturer?->name,
            'batch_no' => '',
            'expiry_date' => '',
            'quantity' => 1,
            'bonus_quantity' => 0,
            'purchase_price' => (float) $m->purchase_price,
            'sale_price' => (float) $m->sale_price,
            'tax_percent' => (float) $m->tax_percent,
        ];

        $this->search = '';
        unset($this->searchResults);
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function setPaymentType(string $type): void
    {
        $this->paymentType = $type;
        $this->paidAmount = $type === 'credit' ? 0 : $this->grandTotal;
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->lines)->sum(fn ($l) => (float) $l['purchase_price'] * (int) $l['quantity']);
    }

    #[Computed]
    public function taxTotal(): float
    {
        return collect($this->lines)->sum(fn ($l) => round((float) $l['purchase_price'] * (int) $l['quantity'] * (float) ($l['tax_percent'] ?? 0) / 100, 2));
    }

    #[Computed]
    public function grandTotal(): float
    {
        return round($this->subtotal + $this->taxTotal - (float) $this->discount, 2);
    }

    #[Computed]
    public function dueAmount(): float
    {
        return round($this->grandTotal - (float) $this->paidAmount, 2);
    }

    #[Computed]
    public function totalQty(): int
    {
        return collect($this->lines)->sum(fn ($l) => (int) $l['quantity'] + (int) ($l['bonus_quantity'] ?? 0));
    }

    public function save(PurchaseService $purchases)
    {
        $this->validate([
            'supplierId' => ['required', 'integer', 'exists:suppliers,id'],
            'branchId' => ['required', 'integer', 'exists:branches,id'],
            'invoiceDate' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.batch_no' => ['required', 'string', 'max:100'],
            'lines.*.expiry_date' => ['required', 'date'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'lines.*.sale_price' => ['required', 'numeric', 'min:0'],
        ], [], [
            'supplierId' => 'supplier',
            'lines.*.batch_no' => 'batch number',
            'lines.*.expiry_date' => 'expiry date',
        ]);

        try {
            $purchase = $purchases->createPurchase(
                Auth::user(),
                $this->branchId,
                $this->supplierId,
                [
                    'supplier_invoice_no' => $this->supplierInvoiceNo ?: null,
                    'invoice_date' => $this->invoiceDate,
                    'due_date' => $this->dueDate ?: null,
                    'notes' => $this->notes ?: null,
                ],
                $this->lines,
                (float) $this->discount,
                (float) $this->paidAmount,
                $this->paymentType,
            );
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());

            return null;
        }

        session()->flash('status', "Purchase {$purchase->purchase_no} saved. Stock and supplier ledger updated.");

        return $this->redirectRoute('purchases.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.add-purchase');
    }
}
