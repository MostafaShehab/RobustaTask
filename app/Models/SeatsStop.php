<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatsStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'seat_id',
        'stop_id',
        'is_booked',
        'booking_user_id'
    ];
}
