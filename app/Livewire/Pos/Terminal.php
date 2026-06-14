<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\PosShift;
use App\Services\SaleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Terminal extends Component
{
    public int $branchId;

    public ?int $shiftId = null;

    public string $search = '';

    /** @var array<int, array<string, mixed>> keyed by batch id */
    public array $cart = [];

    public ?int $customerId = null;

    public string $customerName = 'Walk-in Customer';

    public string $customerSearch = '';

    public string $paymentMethod = 'cash';

    public float $cashReceived = 0;

    public ?array $lastSale = null;

    public function mount(SaleService $sales): void
    {
        $user = Auth::user();
        $this->branchId = (int) (session('active_branch_id') ?: $user->branches()->value('branches.id') ?: 1);
        $this->shiftId = $sales->ensureOpenShift($user, $this->branchId)->id;
    }

    /**
     * Live Echo (Reverb) listener: refresh shift totals when any counter on
     * this branch completes a sale.
     */
    public function getListeners(): array
    {
        return [
            "echo:pos.{$this->branchId},SaleCompleted" => 'onSaleBroadcast',
        ];
    }

    public function onSaleBroadcast(array $payload = []): void
    {
        unset($this->shift); // bust the computed cache so totals re-read
        $this->dispatch('sale-broadcast', saleNo: $payload['sale_no'] ?? null);
    }

    #[Computed]
    public function shift(): ?PosShift
    {
        return $this->shiftId ? PosShift::find($this->shiftId) : null;
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
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('generic_name', 'like', "%{$term}%")
                    ->orWhere('barcode', $term);
            })
            ->with(['manufacturer', 'batches' => fn ($q) => $q->sellable()->where('branch_id', $this->branchId)->orderBy('expiry_date')])
            ->get()
            ->map(function (Medicine $m) {
                $batch = $m->batches->first();

                return $batch ? [
                    'medicine_id' => $m->id,
                    'name' => $m->name,
                    'generic' => $m->generic_name,
                    'manufacturer' => $m->manufacturer?->name,
                    'prescription_required' => (bool) $m->prescription_required,
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry' => $batch->expiry_date?->format('m/Y'),
                    'price' => (float) ($batch->sale_price ?: $m->sale_price),
                    'tax_percent' => (float) $m->tax_percent,
                    'available' => $batch->available_quantity,
                ] : null;
            })
            ->filter()
            ->take(8)
            ->values();
    }

    #[Computed]
    public function customerResults()
    {
        $term = trim($this->customerSearch);
        if (strlen($term) < 2) {
            return collect();
        }

        return Customer::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
            ->limit(6)
            ->get();
    }

    public function addToCart(int $batchId): void
    {
        $result = $this->searchResults->firstWhere('batch_id', $batchId);
        if (! $result) {
            return;
        }

        if (isset($this->cart[$batchId])) {
            $this->cart[$batchId]['qty'] = min($this->cart[$batchId]['qty'] + 1, $result['available']);
        } else {
            $this->cart[$batchId] = [
                'medicine_id' => $result['medicine_id'],
                'name' => $result['name'],
                'generic' => $result['generic'],
                'batch_id' => $batchId,
                'batch_no' => $result['batch_no'],
                'expiry' => $result['expiry'],
                'price' => $result['price'],
                'tax_percent' => $result['tax_percent'],
                'qty' => 1,
                'discount_percent' => 0,
                'available' => $result['available'],
                'prescription_required' => $result['prescription_required'],
            ];
        }

        $this->search = '';
        unset($this->searchResults);
    }

    public function changeQty(int $batchId, int $delta): void
    {
        if (! isset($this->cart[$batchId])) {
            return;
        }
        $line = $this->cart[$batchId];
        $this->cart[$batchId]['qty'] = max(1, min($line['qty'] + $delta, $line['available']));
    }

    public function removeLine(int $batchId): void
    {
        unset($this->cart[$batchId]);
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customerId = $customer->id;
            $this->customerName = $customer->name;
            $this->customerSearch = '';
            unset($this->customerResults);
        }
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerName = 'Walk-in Customer';
    }

    public function setPayment(string $method): void
    {
        $this->paymentMethod = $method;
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(fn ($l) => $l['price'] * $l['qty']);
    }

    #[Computed]
    public function discountTotal(): float
    {
        return collect($this->cart)->sum(fn ($l) => round($l['price'] * $l['qty'] * ($l['discount_percent'] ?? 0) / 100, 2));
    }

    #[Computed]
    public function taxTotal(): float
    {
        return collect($this->cart)->sum(function ($l) {
            $taxable = ($l['price'] * $l['qty']) - round($l['price'] * $l['qty'] * ($l['discount_percent'] ?? 0) / 100, 2);

            return round($taxable * ($l['tax_percent'] ?? 0) / 100, 2);
        });
    }

    #[Computed]
    public function grandTotal(): float
    {
        return round($this->subtotal - $this->discountTotal + $this->taxTotal, 2);
    }

    #[Computed]
    public function change(): float
    {
        return max(0, round($this->cashReceived - $this->grandTotal, 2));
    }

    #[Computed]
    public function requiresPrescription(): bool
    {
        return collect($this->cart)->contains(fn ($l) => $l['prescription_required']);
    }

    public function completeSale(SaleService $sales): void
    {
        $shift = $this->shift;
        if (! $shift) {
            $this->addError('cart', 'No active shift. Please reload the page.');

            return;
        }

        $lines = collect($this->cart)->map(fn ($l) => [
            'batch_id' => $l['batch_id'],
            'quantity' => $l['qty'],
            'discount_percent' => $l['discount_percent'] ?? 0,
        ])->values()->all();

        try {
            $sale = $sales->completeSale(
                Auth::user(),
                $this->branchId,
                $shift,
                $this->customerId,
                $this->paymentMethod,
                (float) $this->cashReceived,
                $lines,
            );
        } catch (ValidationException $e) {
            $this->addError('cart', $e->validator->errors()->first());

            return;
        }

        $this->lastSale = [
            'sale_no' => $sale->sale_no,
            'total' => (float) $sale->grand_total,
            'change' => $this->paymentMethod === 'cash' ? max(0, $this->cashReceived - (float) $sale->grand_total) : 0,
        ];

        // Reset the terminal for the next customer.
        $this->reset(['cart', 'customerId', 'customerName', 'paymentMethod', 'cashReceived', 'search', 'customerSearch']);
        $this->customerName = 'Walk-in Customer';
        $this->paymentMethod = 'cash';
        unset($this->shift);

        $this->dispatch('sale-completed', saleNo: $sale->sale_no);
    }

    public function cancelTransaction(): void
    {
        $this->reset(['cart', 'customerId', 'paymentMethod', 'cashReceived', 'search']);
        $this->customerName = 'Walk-in Customer';
        $this->paymentMethod = 'cash';
    }

    public function render()
    {
        return view('livewire.pos.terminal');
    }
}
