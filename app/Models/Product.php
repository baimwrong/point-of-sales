<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'unit_id', 'name', 'slug', 'sku', 'barcode',
        'description', 'image', 'cost_price', 'selling_price', 'wholesale_price',
        'stock_quantity', 'min_stock_alert', 'tax_rate', 'discount_type',
        'discount_value', 'is_active', 'track_stock',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'      => 'decimal:2',
            'selling_price'   => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'discount_value'  => 'decimal:2',
            'is_active'       => 'boolean',
            'track_stock'     => 'boolean',
            'stock_quantity'  => 'integer',
            'min_stock_alert' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Relationships                                                        */
    /* ------------------------------------------------------------------ */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                     ->where('track_stock', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('sku', 'LIKE', "%{$term}%")
              ->orWhere('barcode', 'LIKE', "%{$term}%");
        });
    }

    /* ------------------------------------------------------------------ */
    /* Accessors                                                           */
    /* ------------------------------------------------------------------ */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('images/products/' . $this->image);
        }
        return asset('images/placeholder-product.png');
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->track_stock && $this->stock_quantity <= $this->min_stock_alert;
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->discount_type === 'fixed') {
            return max(0, $this->selling_price - $this->discount_value);
        }
        if ($this->discount_type === 'percent') {
            return $this->selling_price * (1 - $this->discount_value / 100);
        }
        return $this->selling_price;
    }
}
