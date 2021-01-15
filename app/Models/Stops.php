<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stops extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order_of_stop',
        'trip_id'
    ];
}
