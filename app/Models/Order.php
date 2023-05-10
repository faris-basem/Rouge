<?php

namespace App\Models;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $guarded = [];

    public function cart()
    {
        return $this->hasMany(Cart::class,'order_id');
    }

}
