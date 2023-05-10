<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'products_colors', 'product_id', 'color_id');
    }
}
