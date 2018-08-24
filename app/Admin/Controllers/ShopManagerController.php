<?php

namespace App\Admin\Controllers;

use App\Models\Shop;

use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ShopManagerController extends Controller
{
    use ModelForm;

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
            //dd($grid);
            $grid->id('ID')->sortable();
            $grid->name('商店名');
            $grid->description('描述');
            $grid->location('商铺地址');
            $grid->logo('Logo');
            $grid->type('类型');
            $grid->status('商铺状态');
            $grid->certify('审核状态');
            $grid->open_at('开始营业时间');
            $grid->close_at('结束营业时间');
            $grid->created_at();
            $grid->updated_at();
            $grid->filter(function ($filter) {

                // 设置created_at字段的范围查询
                $filter->equal('status','未审核');
            });
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


}
