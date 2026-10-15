<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'customer_id', 'user_id', 'status',
        'payment_method', 'subtotal', 'discount_type', 'discount_amount',
        'tax_rate', 'tax_amount', 'total_amount', 'paid_amount',
        'change_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'change_amount'   => 'decimal:2',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Relationships                                                        */
    /* ------------------------------------------------------------------ */
    public function customer()    { return $this->belongsTo(Customer::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function saleItems()   { return $this->hasMany(SaleItem::class); }
    public function items()       { return $this->saleItems(); }
    public function salePayments(){ return $this->hasMany(SalePayment::class); }
    public function payments()    { return $this->salePayments(); }
    public function saleReturns() { return $this->hasMany(SaleReturn::class); }
    public function returns()     { return $this->saleReturns(); }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereBetween('created_at', [
            today()->startOfDay(),
            today()->endOfDay(),
        ]);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereBetween('created_at', [
            $date . ' 00:00:00',
            $date . ' 23:59:59',
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth()->startOfDay(),
            now()->endOfMonth()->endOfDay(),
        ]);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Accessors                                                           */
    /* ------------------------------------------------------------------ */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed'      => '<span class="badge bg-success">Completed</span>',
            'refunded'       => '<span class="badge bg-danger">Refunded</span>',
            'partial_refund' => '<span class="badge bg-warning text-dark">Partial Refund</span>',
            default          => '<span class="badge bg-secondary">' . e(ucfirst((string)$this->status)) . '</span>',
        };
    }

    public function getPaymentMethodBadgeAttribute(): string
    {
        $map = [
            'cash'     => ['bg-success', 'Cash'],
            'card'     => ['bg-primary', 'Card'],
            'transfer' => ['bg-info text-dark', 'Transfer'],
            'qris'     => ['bg-purple', 'QRIS'],
            'split'    => ['bg-warning text-dark', 'Split'],
        ];
        [$cls, $label] = $map[$this->payment_method] ?? ['bg-secondary', ucfirst((string)$this->payment_method)];
        return "<span class=\"badge {$cls}\">" . e($label) . "</span>";
    }
}
