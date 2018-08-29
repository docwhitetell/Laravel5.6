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
        $user = $request->user();
        if(empty($request->get('goods'))){
            return $this->sendErrorMsg('goods字段为空或字段不存在！');
        }

        $data = $request->all();
        $data['goods']=json_encode($data['goods']);
        $validator =  Validator::make(
            $request->all(),
            [
                'goods'=>'required|array'
            ],
            [
                "goods.required"=>'无修改！',
                "goods.json"=>'数据格式必须json！',
            ]);
        if($validator->fails()){
            return $this->sendValidateErrorMsg($validator);
        }

        if(count($user->myShopCar) === 0){
            $myShopCar = new ShopCar(['user_id'=>$user->id]);
        }else{
            $myShopCar = $user->myShopCar;
        }
        try{
            $myShopCar->goods_ids = $data['goods'];
            $user->myShopCar()->save($myShopCar);
            return $this->sendSuccessMsg('购物车修改成功！');
        }
        catch (Exception $e){
            return $this->sendSuccessMsg($e);
        }
    }

}
