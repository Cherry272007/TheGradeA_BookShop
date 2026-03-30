<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\TelegramService;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'order_number', 'subtotal', 'shipping_fee', 'discount_amount', 'total_amount',
        'status', 'payment_method', 'payment_status', 'address', 'phone', 'notes',
        'order_date', 'payment_date', 'delivery_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'payment_date' => 'datetime',
        'delivery_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Boot method to hook into model events
     */
    protected static function booted()
    {
        static::created(function ($order) {
            TelegramService::sendOrder($order);
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status') || $order->wasChanged('payment_status')) {
                TelegramService::sendStatusUpdate($order);
            }
        });
    }
}