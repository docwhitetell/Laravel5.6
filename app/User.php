<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        filter_var($username, FILTER_VALIDATE_EMAIL)?
            $credentials['email'] = $username:
            $credentials['mobile'] = $username;
        return self::where($credentials)->first();
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getUser($query=null){
        $response = null;
        $cache = Cache::store('redis')->get('Users');
        if($cache){
            return ['from'=>'redis', 'data'=>$cache, 'memory'=>memory_get_peak_usage()];
        }
        else{
            $data = $query ? User::where($query)->get() : User::all();
            Cache::store('redis')->put('Users', $data, now()->addMinutes(10));
            $response = ['from'=>'database', 'data'=>$data, 'memory'=>memory_get_peak_usage()];
            return $response;
        }
    }
}
