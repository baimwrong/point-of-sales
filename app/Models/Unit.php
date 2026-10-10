<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit Model
 *
 * Represents a measurement unit (pcs, kg, liter, etc.) used to quantify products.
 *
 * @property int    $id
 * @property string $name
 * @property string $abbreviation
 */
class Unit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'abbreviation',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Get products that use this unit.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
