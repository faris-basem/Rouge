<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Welcome extends Model
{
    use HasFactory;
    protected $table = 'welcoming';
    protected $guarded = [];
    public $timestamps=false;
}
