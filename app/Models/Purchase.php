<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number', 'supplier_id', 'user_id', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'paid_amount', 'notes', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'received_at'     => 'datetime',
        ];
    }

    public function supplier()      { return $this->belongsTo(Supplier::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeReceived($q) { return $q->where('status', 'received'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'   => '<span class="badge bg-warning text-dark">Pending</span>',
            'received'  => '<span class="badge bg-success">Received</span>',
            'partial'   => '<span class="badge bg-info text-dark">Partial</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
