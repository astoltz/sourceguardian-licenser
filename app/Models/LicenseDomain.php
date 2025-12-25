<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseDomain extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'license_id',
        'domain',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
