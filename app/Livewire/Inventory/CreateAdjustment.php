<?php

namespace App\Livewire\Inventory;

use App\Models\MedicineBatch;
use App\Services\StockAdjustmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateAdjustment extends Component
{
    public int $branchId;

    public string $adjustmentType = 'count';

    public string $reason = '';

    public string $search = '';

    /** @var array<int, array<string, mixed>> keyed by batch id */
    public array $lines = [];

    public function mount(): void
    {
        $this->branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);
    }

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->search);
        if (strlen($term) < 2) {
            return collect();
        }

        return MedicineBatch::with('medicine')
            ->where('branch_id', $this->branchId)
            ->where(fn ($q) => $q->whereHas('medicine', fn ($m) => $m->where('name', 'like', "%{$term}%")->orWhere('generic_name', 'like', "%{$term}%"))
                ->orWhere('batch_no', 'like', "%{$term}%"))
            ->orderBy('expiry_date')
            ->limit(8)
            ->get();
    }

    public function addBatch(int $batchId): void
    {
        if (isset($this->lines[$batchId])) {
            return;
        }
        $batch = MedicineBatch::with('medicine')->find($batchId);
        if (! $batch) {
            return;
        }

        $this->lines[$batchId] = [
            'batch_id' => $batch->id,
            'name' => $batch->medicine?->name,
            'batch_no' => $batch->batch_no,
            'expiry' => $batch->expiry_date?->format('m/Y'),
            'system_qty' => (int) $batch->available_quantity,
            'counted_qty' => (int) $batch->available_quantity,
            'purchase_price' => (float) $batch->purchase_price,
        ];
        $this->search = '';
        unset($this->searchResults);
    }

    public function removeLine(int $batchId): void
    {
        unset($this->lines[$batchId]);
    }

    public function save(StockAdjustmentService $service)
    {
        $this->validate([
            'adjustmentType' => ['required', 'in:count,damage,expiry,increase,decrease'],
            'reason' => ['nullable', 'string', 'max:500'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.counted_qty' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $adjustment = $service->create(
                Auth::user(),
                $this->branchId,
                $this->adjustmentType,
                $this->reason ?: null,
                collect($this->lines)->map(fn ($l) => ['batch_id' => $l['batch_id'], 'counted_qty' => $l['counted_qty']])->values()->all(),
            );
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());

            return null;
        }

        session()->flash('status', "Adjustment {$adjustment->adjustment_no} submitted for approval.");

        return $this->redirectRoute('stock-adjustments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.create-adjustment');
    }
}
