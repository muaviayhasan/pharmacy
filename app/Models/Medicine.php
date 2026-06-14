<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'max_discount_percent' => 'decimal:2',
        'prescription_required' => 'boolean',
        'controlled_medicine' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function reorderSettings(): HasMany
    {
        return $this->hasMany(MedicineReorderSetting::class);
    }

    /**
     * Available batches for sale (in stock, not expired), ordered FEFO.
     */
    public function sellableBatches(int $branchId)
    {
        return $this->batches()
            ->where('branch_id', $branchId)
            ->where('status', 'in_stock')
            ->where('available_quantity', '>', 0)
            ->whereDate('expiry_date', '>', now())
            ->orderBy('expiry_date');
    }
}
