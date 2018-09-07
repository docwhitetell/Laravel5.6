<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Models\Goods;
use App\Models\Shop;
use App\Models\ShopCar;
use App\Models\ShopCertify;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Exception;

class ShopCarController extends Controller
{

    use ApiMsg;
    /*
    * 我的购物车列表 */
    /**
     * @SWG\Get(path="/api/user/shopcar", tags={"User 用户"},summary="我的购物车",description="我的购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request){
        $user = $request->user();
        $shopCarsGroup = $user->myShopCars->groupBy('shop_id');
        $data = $this->getGoodsGroupByShop($shopCarsGroup);
        return $data;
    }



    public function detail(Request $request, $shopId) {
        $user = $request->user();
        $cars = $user->myShopCars->where('shop_id', $shopId);
        $shop = Shop::find($shopId);
        $goodsList = $this->getGoodsListFromShopCar($cars);
        return $this->sendSuccessMsg('',['shop'=>$shop, 'goods'=>$goodsList]);
    }
    /*
    * 更新购物车列表 */
    /**
     * @SWG\Post(path="/api/user/shopcar", tags={"User 用户"},summary="更新购物车",description="更新购物车",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Parameter(in="header",name="Content-Type",type="string",description="Content_type",required=true, default="application/json"),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function update(Request $request){

    }

    public function add(Request $request){

    }

    public function delete(Request $request){

    }

    /*
     * 返回与传入参数ShopCar列表， 对应的商品列表 */
    protected function getGoodsListFromShopCar($cars = array())
    {
        $goodsList = [];
        foreach ($cars as $car){
            array_push($goodsList, $car->goods);
        }
        return $goodsList;
    }

    /*
     * 以相同商店的商品为一组， 返回归档过的商品列表 */
    protected function getGoodsGroupByShop($carsGroups){
        $data = [];
        foreach ($carsGroups as $group){
            $shop = Shop::find($group->first()->shop_id);
            $goodsList = $this->getGoodsListFromShopCar($group);
            array_push($data, ['shop'=>$shop, 'goods'=>$goodsList]);
        }
        return $data;
    }
}
