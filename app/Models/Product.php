<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'brand_id',
        'price',
        'quantity',
        'description',
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class)
                    ->where('is_thumbnail', false);
    }

    public function thumbnail()
    {
        return $this->hasOne(ProductImage::class)
                    ->where('is_thumbnail', true);
    }
}
