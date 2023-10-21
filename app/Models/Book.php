<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    

    protected $table = 'obj_book';
    
    protected $fillable = [ 
        'id',
        'title',
        'stars',
        'url',
        'coordinate',
        'street',
        'area',
        'checkin',
        'checkout',
        'status',
        'data_added'
    ];
}
