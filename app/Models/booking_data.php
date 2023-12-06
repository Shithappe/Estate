<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class booking_data extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'id',
        'title',
        'description',
        'star',
        'builder_name',
        'complex_name',

        'square',
        'price_per_meter',
        'room_count',
        'floor',
        'price',
        
        'images',
        'link',

        'address',
        'city',
        'coordinates'
    ];
}
