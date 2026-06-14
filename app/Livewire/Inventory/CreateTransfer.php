<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Services\StockTransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateTransfer extends Component
{
    public int $fromBranchId;

    public ?int $toBranchId = null;

    public string $priority = 'normal';

    public string $reason = '';

    public string $search = '';

    /** @var array<int, array<string, mixed>> keyed by batch id */
    public array $lines = [];

    public function mount(): void
    {
        $this->fromBranchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);
    }

    #[Computed]
    public function branches()
    {
        return Branch::orderBy('name')->get();
    }

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->search);
        if (strlen($term) < 2) {
            return collect();
        }

        return MedicineBatch::with('medicine')
            ->where('branch_id', $this->fromBranchId)
            ->where('available_quantity', '>', 0)
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
            'available' => (int) $batch->available_quantity,
            'quantity' => 1,
            'sale_price' => (float) $batch->sale_price,
        ];
        $this->search = '';
        unset($this->searchResults);
    }

    public function removeLine(int $batchId): void
    {
        unset($this->lines[$batchId]);
    }

    public function save(StockTransferService $service)
    {
        $this->validate([
            'toBranchId' => ['required', 'integer', 'different:fromBranchId', 'exists:branches,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ], [], ['toBranchId' => 'destination branch']);

        try {
            $transfer = $service->create(
                Auth::user(),
                $this->fromBranchId,
                $this->toBranchId,
                $this->reason ?: null,
                $this->priority,
                collect($this->lines)->map(fn ($l) => ['batch_id' => $l['batch_id'], 'quantity' => $l['quantity']])->values()->all(),
            );
        } catch (ValidationException $e) {
            $this->addError('items', $e->validator->errors()->first());

            return null;
        }

        session()->flash('status', "Transfer {$transfer->transfer_no} created and pending dispatch.");

        return $this->redirectRoute('stock-transfers.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.create-transfer');
    }
}
