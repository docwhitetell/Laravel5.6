<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'remain',
    ];
    /*
     * 定义 User 和 Wallet 的关联  */
    public function user() {
        return $this->hasOne('App\User');
    }
}
