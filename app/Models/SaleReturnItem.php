<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id', 'sale_item_id', 'product_id',
        'product_name', 'quantity', 'unit_price', 'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'integer',
            'unit_price'  => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function saleReturn() { return $this->belongsTo(SaleReturn::class, 'sale_return_id')->withTrashed(); }
    public function saleItem()   { return $this->belongsTo(SaleItem::class); }
    public function product()    { return $this->belongsTo(Product::class)->withTrashed(); }
}
