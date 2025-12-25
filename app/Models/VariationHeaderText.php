<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariationHeaderText extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'variation_id',
        'data',
        'order',
    ];

    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }
}
