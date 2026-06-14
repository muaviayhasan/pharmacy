<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Services\SaleReturnService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateSaleReturn extends Component
{
    public string $saleSearch = '';

    public ?int $saleId = null;

    /** @var array<int, array{quantity:int, restock:bool, remaining:int, name:string, batch:?string, unit_price:float, line_unit:float}> */
    public array $lines = [];

    public string $refundMethod = 'cash';

    public string $reason = '';

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->saleSearch);
        if (strlen($term) < 2) {
            return collect();
        }

        return Sale::with('customer')
            ->where('sale_no', 'like', "%{$term}%")
            ->where('invoice_status', 'completed')
            ->latest('sale_date')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function sale(): ?Sale
    {
        return $this->saleId ? Sale::with('customer', 'items.medicine')->find($this->saleId) : null;
    }

    public function selectSale(int $saleId): void
    {
        $sale = Sale::with('items.medicine')->find($saleId);
        if (! $sale) {
            return;
        }

        $this->saleId = $sale->id;
        $this->saleSearch = '';
        unset($this->searchResults, $this->sale);

        $this->lines = [];
        foreach ($sale->items as $item) {
            $remaining = $item->quantity - $item->returned_quantity;
            if ($remaining <= 0) {
                continue;
            }
            $this->lines[$item->id] = [
                'name' => $item->medicine?->name,
                'batch' => $item->batch_no,
                'remaining' => $remaining,
                'unit_price' => (float) $item->unit_price,
                'line_unit' => $item->quantity > 0 ? round($item->line_total / $item->quantity, 2) : 0,
                'quantity' => 0,
                'restock' => true,
            ];
        }

        // Customer credit refund only makes sense for credit sales.
        $this->refundMethod = $sale->payment_method === 'credit' ? 'ledger_credit' : 'cash';
    }

    public function clearSale(): void
    {
        $this->reset(['saleId', 'lines', 'reason']);
    }

    #[Computed]
    public function refundTotal(): float
    {
        return collect($this->lines)->sum(fn ($l) => round($l['line_unit'] * (int) $l['quantity'], 2));
    }

    public function save(SaleReturnService $service)
    {
        if (! $this->sale) {
            $this->addError('items', 'Select a sale invoice first.');

            return null;
        }

        $this->validate([
            'refundMethod' => ['required', 'in:cash,card,ledger_credit'],
            'lines' => ['required', 'array'],
            'lines.*.quantity' => ['integer', 'min:0'],
        ]);

        $payload = collect($this->lines)
            ->filter(fn ($l) => (int) $l['quantity'] > 0)
            ->map(fn ($l, $id) => ['sale_item_id' => $id, 'quantity' => (int) $l['quantity'], 'restock' => (bool) $l['restock']])
            ->values()->all();

        try {
            $return = $service->process(Auth::user(), $this->sale, $payload, $this->refundMethod, $this->reason ?: null);
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());

            return null;
        }

        session()->flash('status', "Sale return {$return->return_no} processed. Refund Rs. ".number_format($return->refund_amount, 2));

        return $this->redirectRoute('sale-returns.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.create-sale-return');
    }
}
