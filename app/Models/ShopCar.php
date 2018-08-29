<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCar extends Model
{
    //
    protected $fillable = [
        'user_id',
        'goods_id'
    ];

    protected $table='shop_car';


    public function user(){
        return $this->belongsTo('App\User');
    }
}
