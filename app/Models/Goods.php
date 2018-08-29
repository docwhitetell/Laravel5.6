<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    //
    protected $fillable=[
        'shop_id',
        'name',
        'main_pic',
        'media',
        'description',
        'content',
        'type',
        'tag',
        'price',
        'stock',
        'sold',
        'discount_price'
    ];
    protected $hidden = [
        'shop_id'
    ];
    protected $table='goods';

    public function shop(){
        return $this->belongsTo('App\Models\Shop');
    }
}
