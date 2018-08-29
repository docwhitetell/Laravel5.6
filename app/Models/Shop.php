<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    //
    protected $fillable = [
        'user_id',
        'location',
        'logo',
        'name',
        'type',
        'description',
        'open_at',
        'close_at',
        'status',
        'certify'
    ];
    protected $hidden = [
        'user_id'
    ];
    protected $table='shops';


    public function user(){
        return $this->belongsTo('App\User');
    }
    public function certification(){
        return $this->hasMany('App\Models\ShopCertify');
    }
    public function goods(){
        return $this->hasMany('App\Models\Goods');
    }
}
