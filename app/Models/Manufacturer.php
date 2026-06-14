<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    protected $guarded = [];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
