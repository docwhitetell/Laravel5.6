<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\AuditUser;
use App\Models\UserCertify;
use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UserAuditController extends Controller
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

            $content->header('实名认证审批');
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

            $content->header('实名认证审批');
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
        return Admin::grid(UserCertify::class, function (Grid $grid) {
            $grid->model()->where('certificated',0);
            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                //$actions->disableEdit();
                //$actions->disableView();
                //$actions->append('<a href=""><i class="fa fa-eye"></i></a>');
            });
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                    $batch->add('通过审批', new AuditUser(1));
                    $batch->add('驳回', new AuditUser(0));

                });
            });
            $grid->id('ID')->sortable();
            $grid->column('name','姓名');
            $grid->column('user.name','昵称');
            $grid->column('user.mobile','手机号');
            $grid->column('certificate_type','认证方式');
            $grid->column('certificate_num','证件号');
            $grid->column('positive_pic','证件照(正面)')->display(function ($img){
                $realPath = env('APP_URL').'/storage'.$img;
                return "<img style='max-width: 160px;max-height: 100px' src='{$realPath}' />";
            });
            $grid->column('negative_pic','证件照(反面)')->display(function ($img){
                $realPath = env('APP_URL').'/storage'.$img;
                return "<img style='max-width: 160px;max-height: 100px' src='{$realPath}' />";
            });
            $grid->created_at('申请时间');
            $grid->updated_at('更新时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(UserCertify::class, function (Form $form) {
            $form->setTitle('申请资料详情');
            $form->display('id', 'ID');
            $form->display('name','姓名');
            $form->display('user.name','昵称');
            $form->display('user.mobile','手机号');
            $form->display('certificate_type','认证方式');
            $form->display('certificate_num','证件号');
            $form->image('positive_pic','证件照(正面)')->disk('public')->readOnly();
            $form->image('negative_pic','证件照(反面)')->disk('public')->readOnly();
            $form->display('created_at', 'Created At');

            $states = [
                'on'  => ['value' => 1, 'text' => '通过', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否决', 'color' => 'danger'],
            ];
            $form->switch('certificated','实名认证')->states($states);
            // $form->disableSubmit();
            $form->disableReset();

        });
    }
}
