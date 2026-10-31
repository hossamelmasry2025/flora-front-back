<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'name_en', 'fee', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
