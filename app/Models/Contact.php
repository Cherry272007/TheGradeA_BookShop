<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
    'name',
    'email',
    'phone',
    'subject',
    'message',
    'status',         // pending | read | replied
    'reply_message',  // <--- ADD THIS so you can save the admin's text
    'replied_at',
    'replied_by',     // UserID of admin who replied
];

protected $casts = [
    'replied_at' => 'datetime',
];

/**
 * Relationship: Get the admin who replied to this message.
 * Ensure 'UserID' matches your Primary Key in the Users table.
 */
public function repliedBy()
{
    return $this->belongsTo(User::class, 'replied_by', 'UserID');
}
}
