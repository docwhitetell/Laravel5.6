<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Message as ApiMsg;
use App\Models\Goods;
use App\Models\Shop;
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
     * @SWG\Get(path="/api/user/shopcar", tags={"Shop 商店类Api"},summary="我的商店",description="商店列表",operationId="",produces={"application/json"},
     *   @SWG\Parameter(in="header",name="Authorization",type="string",description="Token",required=true),
     *   @SWG\Response(response="default", description="操作成功")
     * )
     */
    public function index(Request $request){
        $user = $request->user();
        $shopCar = $user->myShopCar;
        if(count($shopCar) === 0){
            return $this->sendSuccessMsg('您的购物车是空的！',null);
        }

        $Ids = $shopCar->goods_ids ? json_decode($shopCar->goods_ids) : array();
        $goods = [];
        if(count($Ids) === 0) {
            return $this->sendSuccessMsg('您的购物车是空的！',null);
        }

        foreach ($Ids as $goodsId){
            $goodsDetail = Goods::find($goodsId);
            if(!$goodsDetail){
                break;
            }
            try{
                $goodsDetail['media'] = json_decode($goodsDetail['media']);
            }catch(Exception $e){
                return $this->sendErrorMsg('数据错误！（'.$e->getMessage().')');
            }
            $goodsDetail->shop = $goodsDetail->shop;
            array_push($goods,$goodsDetail);
        }
        return $this->sendSuccessMsg('', $goods);
    }


    public function add(Request $request){
        $data = $request->all();
        //$data['goods']= json_encode($data['goods']);
        $validator =  Validator::make(
            $data,
            [
                'goods'=>'required|json'
            ],
            [
                "name.required"=>'商品名不能为空！',
            ]);

        if($validator->fails()){
            return $validator->errors();
        }
        //return $goodsIds;
    }


}
