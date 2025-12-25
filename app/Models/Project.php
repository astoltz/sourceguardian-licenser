<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Project Model
 *
 * Represents a software project that is being licensed. This is the top-level
 * container for versions, variations, and their associated settings.
 *
 * @property string $id
 * @property string $display_name A human-readable name for the project.
 * @property string $project_id Encrypted project ID used by SourceGuardian.
 * @property string $project_key Encrypted project key used by SourceGuardian.
 * @property string|null $license_filename Default filename for generated licenses.
 * @property boolean $enabled Whether the project is active.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Project extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'display_name',
        'project_id',
        'project_key',
        'license_filename',
        'enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
        'project_id' => 'encrypted',
        'project_key' => 'encrypted',
    ];

    /**
     * The "booted" method of the model.
     *
     * This method automatically generates a project_id and project_key
     * if they are not provided when a new project is created.
     */
    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->project_id)) {
                $project->project_id = bin2hex(random_bytes(32));
            }
            if (empty($project->project_key)) {
                $project->project_key = bin2hex(random_bytes(32));
            }
        });
    }

    /**
     * Get the versions for the project.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    /**
     * Get the variations for the project.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class);
    }

    /**
     * Get the constants defined at the project level.
     */
    public function projectConstants(): HasMany
    {
        // Use the grammar to wrap the column name correctly for the underlying database
        $grammar = $this->getConnection()->getQueryGrammar();
        $column = $grammar->wrap('key');
        return $this->hasMany(ProjectConstant::class)->orderByRaw("LOWER($column)");
    }

    /**
     * Get the time servers defined for the project.
     */
    public function projectTimeServers(): HasMany
    {
        return $this->hasMany(ProjectTimeServer::class)->orderBy('data');
    }

    /**
     * Get the header texts defined at the project level.
     */
    public function projectHeaderTexts(): HasMany
    {
        return $this->hasMany(ProjectHeaderText::class)->orderBy('order');
    }
}
