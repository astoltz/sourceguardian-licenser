<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Customer Model
 *
 * Represents an end-user or organization that can be assigned licenses.
 *
 * @property string $id
 * @property string $display_name
 * @property boolean $enabled
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'display_name',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the constants defined at the customer level.
     */
    public function customerConstants(): HasMany
    {
        $grammar = $this->getConnection()->getQueryGrammar();
        $column = $grammar->wrap('key');
        return $this->hasMany(CustomerConstant::class)->orderByRaw("LOWER($column)");
    }

    /**
     * Get the header texts defined at the customer level.
     */
    public function customerHeaderTexts(): HasMany
    {
        return $this->hasMany(CustomerHeaderText::class)->orderBy('order');
    }

    /**
     * Get the licenses for the customer.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
