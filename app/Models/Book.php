<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'author',
        'price',
        'stock',
        'status',
        'description',
        'category_id',
        'cover_image',
    ];
    protected $appends = ['cover_image_url'];

    /**
     * Accessor for cover_image_url
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/default-book-cover.png');
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault(['name' => 'Uncategorized']);
    }
}
