<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mockery\Exception;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $username = "mobile";

    public function username(){
        return 'mobile';
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'mobile', 'avatar'
    ];

    /*
     * 将Passport认真方式从验证email+password 改为 mobile+password
     * */
    public function findForPassport($username){
        filter_var($username, FILTER_VALIDATE_EMAIL)
            ? $credentials['email'] = $username
            : $credentials['mobile'] = $username;
        return self::where($credentials)->first();
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    // 通过Model实例 获得关联表的关联信息，
    // 如 $user->myInfo;
    // 并不是 $user->myInfo();
    /*
     * 定义 User 和 UserInfo 的关联 */
    public function myInfo() {
        return $this->hasOne('App\Models\UserInfo');
    }
    /*
     * 定义 User 和 Wallet 的关联  */
    public function myWallet() {
        return $this->hasOne('App\Models\Wallet');
    }
    /*
   * 定义 User 和 Resources 的关联  */
    public function myResource() {
        return $this->hasMany('App\Models\Resources');
    }
}
