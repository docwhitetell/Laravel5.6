<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    //
    protected $fillable = [
        'user_id',
        'year',
        'wechat_id',
        'alipay_id',
        'wallet_id',
        'group_id'
    ];

    protected $table = 'userinfo';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];



    /*
     * 定义 UserInfo 和 User 的关联 */
    public function user(){
        return $this->belongsTo('App\User');
    }
}
