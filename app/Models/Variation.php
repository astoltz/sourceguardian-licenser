<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Variation Model
 *
 * Represents a specific variation of a project (e.g., "Standard", "Pro").
 *
 * @property string $id
 * @property string $project_id
 * @property string $display_name
 * @property boolean $enabled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Variation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'display_name',
        'project_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the project that owns the variation.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the constants defined at the variation level.
     */
    public function variationConstants(): HasMany
    {
        return $this->hasMany(VariationConstant::class)->orderByRaw('LOWER(key)');
    }

    /**
     * Get the header texts defined at the variation level.
     */
    public function variationHeaderTexts(): HasMany
    {
        return $this->hasMany(VariationHeaderText::class)->orderBy('order');
    }
}
