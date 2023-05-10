<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
    public function color()
    {
        return $this->belongsTo(Color::class,'color_id');
    }

}