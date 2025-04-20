<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $table = 'wp_woocommerce_order_items';
    public $timestamps = false;
    protected $primaryKey = 'order_item_id';
    protected $fillable = [
        'order_id',
        'order_item_name',
        'order_item_type'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function metas(): HasMany
    {
        return $this->hasMany(OrderItemMeta::class, 'order_item_id', 'order_item_id');
    }
    
    public function getMeta(string $key, $default = null)
    {
        $meta = $this->metas()->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }
    
    public function getPrice()
    {
        return (float)$this->getMeta('_line_subtotal', 0) / max(1, (int)$this->getMeta('_qty', 1));
    }
    
    public function getQuantity()
    {
        return (int)$this->getMeta('_qty', 1);
    }
    
    public function getSubtotal()
    {
        return (float)$this->getMeta('_line_subtotal', 0);
    }
    
    public function getTaxAmount()
    {
        return (float)$this->getMeta('_line_tax', 0);
    }
    
    public function getTotal()
    {
        return (float)$this->getMeta('_line_total', 0) + (float)$this->getMeta('_line_tax', 0);
    }
    
    public function calculateTotal(): float
    {
        $price = $this->getPrice();
        $quantity = $this->getQuantity();
        $taxAmount = $this->getTaxAmount();
        
        $subtotal = $price * $quantity;
        $total = $subtotal + $taxAmount;
        
        // 更新元數據
        $this->metas()->updateOrCreate(
            ['meta_key' => '_line_subtotal'],
            ['meta_value' => $subtotal]
        );
        
        $this->metas()->updateOrCreate(
            ['meta_key' => '_line_total'],
            ['meta_value' => $subtotal] // WooCommerce中line_total是未稅金額
        );
        
        $this->metas()->updateOrCreate(
            ['meta_key' => '_line_tax'],
            ['meta_value' => $taxAmount]
        );
        
        return $total;
    }
}
