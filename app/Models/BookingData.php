<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingData extends Model
{
    use HasFactory;

    protected $table = 'booking_data'; 

    protected $primaryKey = 'id'; 

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'star',
        'link',
        'address',
        'city',
        'coordinates',
        'images',
        'price',
        'location',
        'score',
        'type',
        'priority',
        'review_count',
    ];
}
