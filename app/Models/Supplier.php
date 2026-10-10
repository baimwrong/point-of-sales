<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Supplier Model
 *
 * Represents a product supplier/vendor. Tracks balance (amount owed) and
 * maintains a history of purchase orders.
 *
 * @property int         $id
 * @property string      $name
 * @property string|null $company
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string      $country
 * @property string|null $tax_number
 * @property float       $balance
 * @property string|null $notes
 * @property bool        $is_active
 */
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_number',
        'balance',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'balance'   => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Get all purchase orders from this supplier.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope for active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
