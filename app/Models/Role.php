<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model
 *
 * Represents a user role in the POS system (admin, manager, cashier).
 * Roles control access levels and permissions across the application.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $display_name
 * @property string|null $description
 * @property array|null  $permissions
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Get all users that belong to this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
