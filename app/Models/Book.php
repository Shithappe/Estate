<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use CrudTrait;
    use HasFactory;
    

    // protected $table = 'obj_book';
    
    protected $fillable = [ 
        'id',
        'title',
        'description',
        'builder_name',
        'complex_name',
        'price',

        'main_image',
        // 'images',

        'square',
        'price_per_meter',
        'room_count',
        'floor',

        'city',
        'district',
        'street',
        'coordinate',

        'rate',
        'property_type',
        'bedrooms_count',
        'source_url'
    ];

    public function images()
    {
    return $this->morphMany(Image::class, 'imageable');
    }
}
