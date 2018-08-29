<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\AuditShop;
use App\Http\Controllers\Api\Message;
use App\Models\Shop;
use App\Models\ShopCertify;
use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Integer;

class ShopAuditController extends Controller
{
    use ModelForm;
    use Message;
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Shop::class, function (Grid $grid) {
            $grid->model()->where('certify','正在审核');
            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                //$actions->disableView();
                $actions->append('<a href=""><i class="fa fa-eye"></i></a>');

            });
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                    $batch->add('通过审批', new AuditShop(1));
                    $batch->add('驳回', new AuditShop(0));

                });
            });
            $grid->id('ID')->sortable();
            $grid->user_id('拥有者')->display(function ($userId){
                return User::find($userId)->name;
            });
            $grid->name('商店名');
            $grid->description('描述');
            $grid->location('商铺地址');
            $grid->logo('Logo');
            $grid->type('类型');
            $grid->status('商铺状态');
            $grid->certify('审核状态');
            $grid->open_at('开始营业时间');
            $grid->close_at('结束营业时间');
            $grid->created_at('申请时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Shop::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    public function approved(Request $request)
    {
        $action = $request->get('action');
        $ids = $request->get('ids');
        if(count($ids)===0){
            return $this->sendErrorMsg('无操作');
        }
        foreach (Shop::find($request->get('ids')) as $shop) {
            $certify = $shop->certification->where('approve','正在审核')->first();
            if ((Integer)$action === 1) {
                $certify->approve = "通过审核";
                try{
                    DB::transaction(function () use($certify, $shop){
                        $certify->save();
                        $shop->update(['certify'=>"通过审核"]);
                    });
                    $this->sendSuccessMsg('通过审核');
                }catch(\Exception $e){
                    $this->sendSqlErrorMsg($e);
                }

            }else{
                $certify->approve = "审核失败";
                try{
                    DB::transaction(function () use($certify, $shop){
                        $certify->save();
                        $shop->update(['certify'=>"审核失败"]);
                    });
                    $this->sendSuccessMsg('审核失败');
                }catch(\Exception $e){
                    $this->sendSqlErrorMsg($e);
                }
            }
        }

    }
}
