<?php

namespace App\Admin\Controllers;

use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UserController extends Controller
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
            $content->header('用户');
            $content->description('用户列表');
            $content->breadcrumb(
                ['text' => '首页', 'url' => '/admin'],
                ['text' => '前台用户', 'url' => '/admin/front'],
                ['text' => '用户列表']
            );
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

            $content->header('创建用户');
            $content->description('表格');

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
        return Admin::grid(User::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('昵称')->sortable();
            $grid->email('邮箱');
            $grid->mobile('手机号');
            $grid->avatar('头像');
            $grid->created_at('创建时间');
            $grid->updated_at('最近更新');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(User::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('name', '昵称');
            $form->image('avatar', '头像')->move('/admin/avatar');
            $form->email('email')->rules('nullable');
            $form->mobile('mobile', '手机号')->rules('required|size:11');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '最近更新');
        });
    }
}
