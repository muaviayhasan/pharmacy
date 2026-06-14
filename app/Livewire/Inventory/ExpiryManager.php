<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Services\ExpiryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ExpiryManager extends Component
{
    public string $branchFilter = '';

    public string $bucket = 'all';   // all, expired, d30, d60, d90

    public string $search = '';

    public function mount(): void
    {
        $this->branchFilter = (string) (session('active_branch_id') ?: '');
    }

    private function baseQuery()
    {
        return MedicineBatch::query()
            ->with('medicine', 'branch')
            ->where('available_quantity', '>', 0)
            ->whereIn('status', ['in_stock', 'quarantined', 'near_expiry', 'expired'])
            ->whereDate('expiry_date', '<=', now()->addDays(90))
            ->when($this->branchFilter !== '', fn ($q) => $q->where('branch_id', (int) $this->branchFilter))
            ->when($this->search !== '', fn ($q) => $q->whereHas('medicine', fn ($m) => $m->where('name', 'like', '%'.trim($this->search).'%')->orWhere('generic_name', 'like', '%'.trim($this->search).'%')));
    }

    #[Computed]
    public function counts(): array
    {
        $rows = (clone $this->baseQuery())->get(['id', 'expiry_date', 'available_quantity', 'purchase_price']);

        return [
            'all' => $rows->count(),
            'expired' => $rows->filter(fn ($b) => $b->expiry_date->lte(now()))->count(),
            'd30' => $rows->filter(fn ($b) => $b->expiry_date->gt(now()) && $b->expiry_date->lte(now()->addDays(30)))->count(),
            'value' => $rows->sum(fn ($b) => $b->available_quantity * $b->purchase_price),
        ];
    }

    #[Computed]
    public function batches()
    {
        $query = $this->baseQuery();

        match ($this->bucket) {
            'expired' => $query->whereDate('expiry_date', '<=', now()),
            'd30' => $query->whereDate('expiry_date', '>', now())->whereDate('expiry_date', '<=', now()->addDays(30)),
            'd60' => $query->whereDate('expiry_date', '>', now()->addDays(30))->whereDate('expiry_date', '<=', now()->addDays(60)),
            'd90' => $query->whereDate('expiry_date', '>', now()->addDays(60))->whereDate('expiry_date', '<=', now()->addDays(90)),
            default => null,
        };

        return $query->orderBy('expiry_date')->paginate(15);
    }

    public function dispose(int $batchId, ExpiryService $service): void
    {
        $batch = MedicineBatch::find($batchId);
        if ($batch) {
            $service->dispose($batch, Auth::user());
            $this->dispatch('toast', message: "Batch {$batch->batch_no} disposed.");
            unset($this->batches, $this->counts);
        }
    }

    public function quarantine(int $batchId, ExpiryService $service): void
    {
        $batch = MedicineBatch::find($batchId);
        if ($batch) {
            $service->quarantine($batch, Auth::user());
            $this->dispatch('toast', message: "Batch {$batch->batch_no} quarantined.");
            unset($this->batches, $this->counts);
        }
    }

    public function restore(int $batchId, ExpiryService $service): void
    {
        $batch = MedicineBatch::find($batchId);
        if ($batch) {
            $service->restore($batch);
            $this->dispatch('toast', message: "Batch {$batch->batch_no} restored to stock.");
            unset($this->batches, $this->counts);
        }
    }

    public function render()
    {
        return view('livewire.inventory.expiry-manager', [
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }
}
