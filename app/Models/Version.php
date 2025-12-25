<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Version Model
 *
 * Represents a specific version of a project (e.g., "1.0", "2.0").
 *
 * @property string $id
 * @property string $project_id
 * @property string $display_name
 * @property boolean $enabled
 * @property string|null $override_project_id Encrypted project ID override.
 * @property string|null $override_project_key Encrypted project key override.
 * @property string|null $override_license_filename License filename override.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Version extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'display_name',
        'project_id',
        'enabled',
        'override_project_id',
        'override_project_key',
        'override_license_filename',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'override_project_id' => 'encrypted',
        'override_project_key' => 'encrypted',
    ];

    /**
     * Get the project that owns the version.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the constants defined at the version level.
     */
    public function versionConstants(): HasMany
    {
        $grammar = $this->getConnection()->getQueryGrammar();
        $column = $grammar->wrap('key');
        return $this->hasMany(VersionConstant::class)->orderByRaw("LOWER($column)");
    }

    /**
     * Get the header texts defined at the version level.
     */
    public function versionHeaderTexts(): HasMany
    {
        return $this->hasMany(VersionHeaderText::class)->orderBy('order');
    }
}
