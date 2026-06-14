<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::query()
            ->with('category', 'manufacturer')
            ->withSum('batches as stock', 'available_quantity')
            ->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        if ($category = $request->integer('category')) {
            $query->where('category_id', $category);
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }
        if ($request->boolean('prescription')) {
            $query->where('prescription_required', true);
        }

        $medicines = $query->paginate(15)->withQueryString();

        // Stock-derived KPIs.
        $stock = DB::table('medicine_batches')
            ->selectRaw('medicine_id, COALESCE(SUM(available_quantity),0) q')
            ->groupBy('medicine_id')->pluck('q', 'medicine_id');
        $reorderLevels = Medicine::pluck('reorder_level', 'id');
        $low = 0;
        $out = 0;
        foreach ($reorderLevels as $id => $reorder) {
            $q = (int) ($stock[$id] ?? 0);
            if ($q <= 0) {
                $out++;
            } elseif ($q <= $reorder) {
                $low++;
            }
        }

        $stats = [
            'total' => Medicine::count(),
            'active' => Medicine::where('status', 'active')->count(),
            'prescription' => Medicine::where('prescription_required', true)->count(),
            'controlled' => Medicine::where('controlled_medicine', true)->count(),
            'low' => $low,
            'out' => $out,
        ];

        return view('medicines.index', [
            'medicines' => $medicines,
            'categories' => MedicineCategory::orderBy('name')->get(),
            'stats' => $stats,
            'filters' => $request->only('search', 'category', 'status', 'prescription'),
        ]);
    }

    public function create()
    {
        return view('medicines.create', $this->formData());
    }

    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $medicine = Medicine::create($this->payload($request));

        if ($request->input('action') === 'save_add_another') {
            return redirect()->route('medicines.create')->with('status', "Medicine \"{$medicine->name}\" created. Add another.");
        }

        return redirect()->route('medicines.index')->with('status', "Medicine \"{$medicine->name}\" created.");
    }

    public function show(Medicine $medicine)
    {
        $medicine->load(['category', 'manufacturer', 'defaultSupplier', 'batches.branch']);

        $branchStock = $medicine->batches
            ->groupBy('branch_id')
            ->map(fn ($batches) => [
                'branch' => $batches->first()->branch?->name,
                'qty' => $batches->sum('available_quantity'),
            ])->values();

        return view('medicines.show', [
            'medicine' => $medicine,
            'totalStock' => $medicine->batches->sum('available_quantity'),
            'branchStock' => $branchStock,
            'earliestExpiry' => $medicine->batches->where('available_quantity', '>', 0)->min('expiry_date'),
        ]);
    }

    public function edit(Medicine $medicine)
    {
        return view('medicines.edit', array_merge($this->formData(), ['medicine' => $medicine]));
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($this->payload($request));

        return redirect()->route('medicines.index')->with('status', "Medicine \"{$medicine->name}\" updated.");
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->delete();

        return redirect()->route('medicines.index')->with('status', 'Medicine deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => MedicineCategory::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload($request): array
    {
        $data = $request->validated();
        $data['prescription_required'] = $request->boolean('prescription_required');
        $data['controlled_medicine'] = $request->boolean('controlled_medicine');
        $data['wholesale_price'] = $data['wholesale_price'] ?? 0;
        $data['tax_percent'] = $data['tax_percent'] ?? 0;
        $data['max_discount_percent'] = $data['max_discount_percent'] ?? 0;
        $data['min_stock_level'] = $data['min_stock_level'] ?? 0;
        $data['reorder_level'] = $data['reorder_level'] ?? 0;
        $data['max_stock_level'] = $data['max_stock_level'] ?? 0;

        return $data;
    }
}
