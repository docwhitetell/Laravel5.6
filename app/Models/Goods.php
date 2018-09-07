<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
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

    protected $hidden = [];

    protected $table='goods';

    public function shop(){
        return $this->belongsTo('App\Models\Shop');
    }
    public function shopCar(){
        return $this->belongsToMany('App\Models\ShopCar');
    }
}
