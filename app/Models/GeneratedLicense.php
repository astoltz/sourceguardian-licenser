<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedLicense extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'license_id',
        'version_id',
        'data',
        'downloaded_at',
        'downloaded_ip',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
