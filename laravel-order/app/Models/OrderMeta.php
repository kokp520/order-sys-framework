<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeta extends Model
{
    protected $table = 'wp_postmeta';
    public $timestamps = false;
    protected $primaryKey = 'meta_id';
    
    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value'
    ];

    // 關聯
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'post_id', 'id');
    }
}
