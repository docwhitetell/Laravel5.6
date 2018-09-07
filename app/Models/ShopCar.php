<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCar extends Model
{
    //
    protected $fillable = [
        'user_id',
        'shop_id',
        'goods_id',
        'amount'
    ];

    protected $table='shop_car';


    public function user(){
        return $this->belongsTo('App\User');
    }

    public function shop(){
        return $this->belongsTo('App\Models\Shop');
    }

    public function goods(){
        return $this->hasOne('App\Models\Goods','id', 'goods_id');
    }

}
