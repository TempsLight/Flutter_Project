<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['phone_number', 'receiver_phone_number', 'amount', 'type'];
    // You can define the relationship with the User model here
    public function user()
    {
        return $this->belongsTo(User::class, 'phone_number', 'phone_number');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_phone_number', 'phone_number');
    }
}
