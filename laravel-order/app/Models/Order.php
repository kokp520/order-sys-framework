<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'wp_wc_orders';
    public $timestamps = false;
    public $incrementing = true;
    
    protected $fillable = [
        'id',
        'status',
        'currency',
        'type',
        'tax_amount',
        'total_amount',
        'customer_id',
        'billing_email',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'ip_address',
        'user_agent',
        'customer_note',
        'parent_order_id',
        'date_created_gmt',
        'date_updated_gmt',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(OrderMeta::class, 'post_id', 'id');
    }

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->metas()->where('meta_key', $key)->first();
        return $meta ? $meta->meta_value : $default;
    }
    
    public function setMeta(string $key, $value)
    {
        $this->metas()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value]
        );
    }
}
