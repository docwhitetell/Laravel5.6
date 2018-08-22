<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCertify extends Model
{
    //
    protected $table = 'certify';


    public function user(){
        return $this->belongsTo('App\User');
    }
}
