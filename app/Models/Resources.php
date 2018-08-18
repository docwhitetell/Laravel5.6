<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resources extends Model
{
    //
    protected $fillable = [
        'user_id',
        'origin_name',
        'file_name',
        'file_size',
        'type',
        'path',
        'ext'
    ];

    protected $table = 'user_resource';
    /*
     * 定义 User 和 Wallet 的关联  */
    public function user() {
        return $this->hasOne('App\User');
    }
}
