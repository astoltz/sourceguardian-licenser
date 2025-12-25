<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseMac extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'license_id',
        'mac',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
