<?php

namespace App\Livewire\Purchases;

use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Services\PurchaseReturnService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreatePurchaseReturn extends Component
{
    public string $purchaseSearch = '';

    public ?int $purchaseId = null;

    /** @var array<int, array{quantity:int, available:int, name:string, batch:string, unit:float}> keyed by batch id */
    public array $lines = [];

    public string $settlement = 'ledger_adjust';

    public string $reason = '';

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->purchaseSearch);
        if (strlen($term) < 2) {
            return collect();
        }

        return Purchase::with('supplier')
            ->where('purchase_no', 'like', "%{$term}%")
            ->orWhere('supplier_invoice_no', 'like', "%{$term}%")
            ->latest()
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function purchase(): ?Purchase
    {
        return $this->purchaseId ? Purchase::with('supplier', 'branch')->find($this->purchaseId) : null;
    }

    public function selectPurchase(int $purchaseId): void
    {
        $purchase = Purchase::find($purchaseId);
        if (! $purchase) {
            return;
        }

        $this->purchaseId = $purchase->id;
        $this->purchaseSearch = '';
        unset($this->searchResults, $this->purchase);

        $this->lines = [];
        $batches = MedicineBatch::with('medicine')->where('purchase_id', $purchase->id)->get();
        foreach ($batches as $batch) {
            if ($batch->available_quantity <= 0) {
                continue;
            }
            $this->lines[$batch->id] = [
                'name' => $batch->medicine?->name,
                'batch' => $batch->batch_no,
                'expiry' => $batch->expiry_date?->format('m/Y'),
                'available' => (int) $batch->available_quantity,
                'unit' => (float) $batch->purchase_price,
                'quantity' => 0,
            ];
        }
    }

    public function clearPurchase(): void
    {
        $this->reset(['purchaseId', 'lines', 'reason']);
    }

    #[Computed]
    public function returnTotal(): float
    {
        return collect($this->lines)->sum(fn ($l) => round($l['unit'] * (int) $l['quantity'], 2));
    }

    public function save(PurchaseReturnService $service)
    {
        if (! $this->purchase) {
            $this->addError('items', 'Select a purchase invoice first.');

            return null;
        }

        $this->validate([
            'settlement' => ['required', 'in:refund,ledger_adjust'],
            'lines' => ['required', 'array'],
            'lines.*.quantity' => ['integer', 'min:0'],
        ]);

        $payload = collect($this->lines)
            ->filter(fn ($l) => (int) $l['quantity'] > 0)
            ->map(fn ($l, $id) => ['batch_id' => $id, 'quantity' => (int) $l['quantity']])
            ->values()->all();

        try {
            $return = $service->process(Auth::user(), $this->purchase, $payload, $this->settlement, $this->reason ?: null);
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());

            return null;
        }

        session()->flash('status', "Purchase return {$return->return_no} processed. Rs. ".number_format($return->return_amount, 2).' credited against supplier.');

        return $this->redirectRoute('purchase-returns.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.create-purchase-return');
    }
}
