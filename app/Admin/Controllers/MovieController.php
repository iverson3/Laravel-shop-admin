<?php

namespace App\Admin\Controllers;

use App\Movie;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\MessageBag;


class MovieController extends Controller
{
    use ModelForm;


    protected $header = "电影";
    protected $action = '';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header($this->header);
            $content->description('列表');

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

            $content->header($this->header);
            $content->description('编辑');

            $this->action = 'edit';
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

            $content->header($this->header);
            $content->description('新建');

            $this->action = 'create';
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
        return Admin::grid(Movie::class, function (Grid $grid) {

            $grid->actions(function ($actions) {
                // 可取消掉默认的两个操作按钮
                // $actions->disableDelete();   
                // $actions->disableEdit();

                // 也可添加自定义的操作按钮及功能 (看文档)
            });

            // 禁用新建按钮
            // $grid->disableCreateButton();

            // 禁用查询过滤器
            // $grid->disableFilter();

            // 过滤字段设置
            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();

                // 设置created_at字段的范围查询
                $filter->between('created_at', 'Created Time')->datetime();
                // $filter->equal('status', '状态')->select(['' => 'All', 0 => '禁用', 1 => '可用']);
                $filter->equal('status', '状态')->radio([
                    '' => 'All',
                    0  => '禁用',
                    1  => '可用',
                ]);
                $filter->like('name', '电影名');
                $filter->gt('rate', '该分数以上');
            });

            $grid->id('ID')->sortable();
            $grid->name('电影名')->editable();
            $grid->pic('封面图')->image('', 60, 60);
            $grid->rate('评分')->sortable()->color('red');

            $states = [
                'on'  => ['value' => 1, 'text' => '可用', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'default']
            ];
            $grid->status('状态')->switch($states);
            // $grid->status('状态')->select([0 => '禁用', 1 => '可用']);
            // $grid->status('状态')->display(function ($status) {
            //     return $status ? '可用' : '禁用';
            // });

            // // 添加数据表中不存在的字段
            // $grid->column('column_not_in_table')->display(function () {
            //     // display方法接收的匿名函数绑定了当前行的数据对象，可以在里面调用当前行的其它字段数据
            //     return 'blablabla....';
            // });

            $grid->created_at()->sortable();
            $grid->updated_at()->editable('datetime');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Movie::class, function (Form $form) {

            // 保存前回调
            $form->saving(function ($form) {

                // 在保存数据之前根据需要对表单数据进行需要的修改调整或校验
                // $form->id;

                // $error = new MessageBag([
                //     'name' => 'name错误',
                //     'rate' => 'rate错误',
                // ]);

                //  return back()->with(compact('error'));
            });
            // 保存后回调
            $form->saved(function ($form) {
                // $form->username;
                // $form->model()->id;
                //
                // return response('xxxx');
                // return redirect('/admin/users');
            });


            $form->display('id', 'ID');
            $form->text('name', '电影名')->rules('required|max:100', [
                'required' => '字段不能为空',
                'max'      => '不能超过100个字符',
            ]);
            $form->image('pic', '封面图')->rules('required', [
                'required' => '字段不能为空'
            ]);

            // 通过自定义的属性$action 来判断某些特殊字段的表单呈现方式
            if ($this->action == 'create') {
                $form->text('rate', '评分')->rules('required|regex:/^\d(\.\d)?$/', [
                    'required' => '字段不能为空',
                    'regex'    => '评分格式不对',
                ]);
            } else {
                $form->display('rate', '评分')->rules('required|regex:/^\d(\.\d)?$/', [
                    'required' => '字段不能为空',
                    'regex'    => '评分格式不对',
                ]);
            }

            $states = [
                'on'  => ['value' => 1, 'text' => '可用', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
            ];
            $form->switch('status', '状态')->states($states);
            $form->wangeditor('content', '电影简介')->rules('required', [
                'required' => '字段不能为空'
            ]);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });

        // $a =$this->testParasType(5, 'str');
    }

    /**
     * 参数类型测试
     * @param Int $a
     * @param String $b
     * @return array
     */
    protected function testParasType(Int $number, String $string)
    {
        return [1, 2, $number . $string];
    }
}
