<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'book_id', 'quantity', 'price', 'subtotal'];

    /**
     * The "booted" method of the model.
     */
    // protected static function booted()
    // {
    //     // 'creating' happens right before the data is saved to the database
    //     static::creating(function ($item) {
    //         // Automatically calculate subtotal: Price * Quantity
    //         $item->subtotal = $item->price * $item->quantity;
    //     });
    // }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
