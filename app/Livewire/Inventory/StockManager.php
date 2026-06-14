<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StockManager extends Component
{
    public string $branchFilter = '';

    public string $categoryFilter = '';

    public string $stockStatus = '';

    public string $expiryStatus = '';

    public string $search = '';

    public ?int $selectedMedicineId = null;

    public ?int $selectedBranchId = null;

    private const NEAR_EXPIRY_DAYS = 90;

    public function mount(): void
    {
        $this->branchFilter = (string) (session('active_branch_id') ?: '');
    }

    public function resetFilters(): void
    {
        $this->reset(['branchFilter', 'categoryFilter', 'stockStatus', 'expiryStatus', 'search', 'selectedMedicineId', 'selectedBranchId']);
    }

    public function selectMedicine(int $medicineId, int $branchId): void
    {
        $this->selectedMedicineId = $medicineId;
        $this->selectedBranchId = $branchId;
    }

    /**
     * Aggregated current stock grouped by medicine + branch.
     */
    #[Computed]
    public function rows()
    {
        $near = now()->addDays(self::NEAR_EXPIRY_DAYS)->toDateString();
        $today = now()->toDateString();

        $query = DB::table('medicine_batches as b')
            ->join('medicines as m', 'm.id', '=', 'b.medicine_id')
            ->leftJoin('branches as br', 'br.id', '=', 'b.branch_id')
            ->leftJoin('manufacturers as mf', 'mf.id', '=', 'm.manufacturer_id')
            ->where('b.available_quantity', '>', 0)
            ->whereNull('m.deleted_at');

        if ($this->branchFilter !== '') {
            $query->where('b.branch_id', (int) $this->branchFilter);
        }
        if ($this->categoryFilter !== '') {
            $query->where('m.category_id', (int) $this->categoryFilter);
        }
        if ($this->search !== '') {
            $term = trim($this->search);
            $query->where(fn ($q) => $q->where('m.name', 'like', "%{$term}%")
                ->orWhere('m.generic_name', 'like', "%{$term}%")
                ->orWhere('b.batch_no', 'like', "%{$term}%"));
        }

        $rows = $query->groupBy('b.medicine_id', 'b.branch_id', 'm.name', 'm.generic_name', 'mf.name', 'br.name', 'm.reorder_level', 'm.dosage_form')
            ->selectRaw('b.medicine_id, b.branch_id, m.name, m.generic_name, mf.name as mfr, br.name as branch, m.reorder_level, m.dosage_form,
                SUM(b.available_quantity) as qty,
                SUM(b.available_quantity * b.purchase_price) as value,
                MIN(b.expiry_date) as nearest_expiry')
            ->orderBy('m.name')
            ->get()
            ->map(function ($r) use ($near, $today) {
                $r->status = $r->qty <= 0 ? 'out' : ($r->qty <= $r->reorder_level ? 'low' : 'in_stock');
                $r->expiry_flag = $r->nearest_expiry <= $today ? 'expired' : ($r->nearest_expiry <= $near ? 'near' : 'ok');

                return $r;
            });

        if ($this->stockStatus !== '') {
            $rows = $rows->where('status', $this->stockStatus);
        }
        if ($this->expiryStatus !== '') {
            $rows = $rows->where('expiry_flag', $this->expiryStatus);
        }

        return $rows->values();
    }

    #[Computed]
    public function kpis(): array
    {
        $base = MedicineBatch::query()->where('available_quantity', '>', 0)
            ->when($this->branchFilter !== '', fn ($q) => $q->where('branch_id', (int) $this->branchFilter));

        $near = now()->addDays(self::NEAR_EXPIRY_DAYS);

        return [
            'stock_value' => (float) (clone $base)->selectRaw('COALESCE(SUM(available_quantity * purchase_price),0) v')->value('v'),
            'units' => (int) (clone $base)->sum('available_quantity'),
            'low' => $this->rows->where('status', 'low')->count(),
            'near_expiry' => (clone $base)->whereDate('expiry_date', '>', now())->whereDate('expiry_date', '<=', $near)->count(),
            'expired' => (clone $base)->whereDate('expiry_date', '<=', now())->count(),
        ];
    }

    #[Computed]
    public function expiryRisk(): array
    {
        $base = fn () => MedicineBatch::query()->where('available_quantity', '>', 0)
            ->when($this->branchFilter !== '', fn ($q) => $q->where('branch_id', (int) $this->branchFilter));

        return [
            'expired' => (clone $base())->whereDate('expiry_date', '<=', now())->count(),
            'd30' => (clone $base())->whereDate('expiry_date', '>', now())->whereDate('expiry_date', '<=', now()->addDays(30))->count(),
            'd60' => (clone $base())->whereDate('expiry_date', '>', now()->addDays(30))->whereDate('expiry_date', '<=', now()->addDays(60))->count(),
            'd90' => (clone $base())->whereDate('expiry_date', '>', now()->addDays(60))->whereDate('expiry_date', '<=', now()->addDays(90))->count(),
            'normal' => (clone $base())->whereDate('expiry_date', '>', now()->addDays(90))->count(),
        ];
    }

    #[Computed]
    public function reorderSuggestions()
    {
        return $this->rows->where('status', '!=', 'in_stock')->take(8)->values();
    }

    #[Computed]
    public function selectedBatches()
    {
        if (! $this->selectedMedicineId) {
            return collect();
        }

        return MedicineBatch::with('supplier', 'branch')
            ->where('medicine_id', $this->selectedMedicineId)
            ->when($this->selectedBranchId, fn ($q) => $q->where('branch_id', $this->selectedBranchId))
            ->where('available_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();
    }

    #[Computed]
    public function recentMovements()
    {
        return StockMovement::with('medicine', 'branch')
            ->when($this->branchFilter !== '', fn ($q) => $q->where('branch_id', (int) $this->branchFilter))
            ->latest()
            ->limit(6)
            ->get();
    }

    public function render()
    {
        return view('livewire.inventory.stock-manager', [
            'branches' => Branch::orderBy('name')->get(),
            'categories' => MedicineCategory::orderBy('name')->get(),
        ]);
    }
}
