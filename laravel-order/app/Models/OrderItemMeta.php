<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemMeta extends Model
{
    protected $table = 'wp_woocommerce_order_itemmeta';
    public $timestamps = false;
    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'order_item_id',
        'meta_key',
        'meta_value'
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'order_item_id');
    }
}
