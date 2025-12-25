<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * License Model
 *
 * Represents a license configuration for a specific customer, project variation, and version.
 * This is the core entity that defines the restrictions and properties of a license.
 *
 * @property string $id
 * @property string $display_name
 * @property string $shared_secret Encrypted shared secret for the license.
 * @property string $customer_id
 * @property string $variation_id
 * @property string $version_id
 * @property boolean $enabled
 * @property \Illuminate\Support\Carbon|null $expiration_date
 * @property boolean $bind_domain_ignore_cli
 * @property boolean $bind_ip_ignore_cli
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class License extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'display_name',
        'shared_secret',
        'customer_id',
        'variation_id',
        'version_id',
        'enabled',
        'expiration_date',
        'bind_domain_ignore_cli',
        'bind_ip_ignore_cli',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'shared_secret' => 'encrypted',
        'expiration_date' => 'datetime',
        'bind_domain_ignore_cli' => 'boolean',
        'bind_ip_ignore_cli' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->shared_secret)) {
                $license->shared_secret = bin2hex(random_bytes(32));
            }
            if (empty($license->display_name)) {
                $license->load(['variation.project', 'version']);
                $name = "{$license->variation->project->display_name} - {$license->variation->display_name} - {$license->version->display_name} - " . now()->format('Y-m-d');
                // Ensure uniqueness
                $count = License::where('display_name', 'like', "{$name}%")->count();
                if ($count > 0) {
                    $name .= ' (' . ($count + 1) . ')';
                }
                $license->display_name = $name;
            }
        });
    }

    /**
     * Get the customer who owns the license.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the variation associated with the license.
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }

    /**
     * Get the version associated with the license.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }

    /**
     * Get the constants defined at the license level.
     */
    public function licenseConstants(): HasMany
    {
        return $this->hasMany(LicenseConstant::class)->orderByRaw('LOWER(key)');
    }

    /**
     * Get the header texts defined at the license level.
     */
    public function licenseHeaderTexts(): HasMany
    {
        return $this->hasMany(LicenseHeaderText::class)->orderBy('order');
    }

    /**
     * Get the domains bound to the license.
     */
    public function licenseDomains(): HasMany
    {
        return $this->hasMany(LicenseDomain::class);
    }

    /**
     * Get the IPs bound to the license.
     */
    public function licenseIps(): HasMany
    {
        return $this->hasMany(LicenseIp::class);
    }

    /**
     * Get the MAC addresses bound to the license.
     */
    public function licenseMacs(): HasMany
    {
        return $this->hasMany(LicenseMac::class);
    }

    /**
     * Get the machine IDs bound to the license.
     */
    public function licenseMachineIds(): HasMany
    {
        return $this->hasMany(LicenseMachineId::class)->orderBy('machine_id');
    }

    /**
     * Get the generated license files for this license.
     */
    public function generatedLicenses(): HasMany
    {
        return $this->hasMany(GeneratedLicense::class);
    }

    /**
     * Accessor for sorted IPs.
     * Sorts IPs numerically, handling CIDR notation.
     */
    public function getSortedLicenseIpsAttribute()
    {
        return $this->licenseIps->sort(function ($a, $b) {
            // Extract IP part if CIDR
            $ipA = explode('/', $a->ip)[0];
            $ipB = explode('/', $b->ip)[0];

            // Use inet_pton to convert to binary for comparison (handles IPv4 and IPv6)
            $binA = inet_pton($ipA);
            $binB = inet_pton($ipB);

            if ($binA === false || $binB === false) {
                return strcmp($a->ip, $b->ip); // Fallback to string compare if invalid
            }

            return strcmp($binA, $binB);
        })->values();
    }
}
