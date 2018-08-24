<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCertify extends Model
{
    protected $fillable = [
        'owner',
        'shop_id',
        'approve',
    ];

    protected $table='shop_certify';


    public function user(){
        return $this->belongsTo('App\User');
    }
/*    public function certify(){
        return $this->hasMany('App\Models\ShopCertify');
    }*/
    public function shop(){
        return $this->belongsTo('App\Models\Shop');
    }
}
