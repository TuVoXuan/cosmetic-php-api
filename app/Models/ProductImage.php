<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'is_thumbnail',
        'url',
        'public_id'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
