<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use CrudTrait;
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
