<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Encore\Admin\Config\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // 载入数据库中存放的配置信息
        // 在程序中使用config($key)来获取数据库中的配置
        // 注意，配置的Name不要和config目录中的已存在的配置key冲突，不然会覆盖掉系统的配置
        Config::load();

        // 动态修改配置文件的配置项
        // 用数据库中存放的配置信息来动态修改系统配置文件中的配置信息
        config(['admin.extensions.media-manager.disk' => config('db.admin.extensions.media-manager.disk')]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
