<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $table = 'abouts';

    protected $fillable = [
        'title',
        'description',
        'vision',
        'mission',
        'address',
        'phone',
        'email',
        'established_year',
        'logo',
        'banner_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}