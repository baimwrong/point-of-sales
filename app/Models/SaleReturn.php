<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'return_number', 'sale_id', 'user_id', 'reason', 'status',
        'refund_method', 'total_refund', 'notes',
    ];

    protected function casts(): array
    {
        return ['total_refund' => 'decimal:2'];
    }

    public function sale()        { return $this->belongsTo(Sale::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function returnItems() { return $this->hasMany(SaleReturnItem::class, 'sale_return_id'); }
}
