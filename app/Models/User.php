<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Contracts\EventDispatcher\Event;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'password',
        'role',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function events()
    {
        return $this->hasMany(Event::class, 'CreatedBy', 'id');
    }


    /**
     * --- NEW ADDITIONS ---
     */

    /**
     * The accessors to append to the model's array form.
     * This makes 'profile_image_url' visible in your JSON response.
     */
    protected $appends = ['profile_image_url'];

    /**
     * Automatically generate the full URL for the profile image.
     */
    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) {
            return null; // Or return asset('images/default-avatar.png')
        }

        return asset('storage/' . $this->profile_image);
    }
}
