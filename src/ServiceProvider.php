<?php
/**
 * Created by PhpStorm.
 * User: zhangrui
 * Date: 2018/11/17
 * Time: 18:38
 */

namespace Pinfankeji\Weather;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Weather::class, function (){
            return new Weather(config('services.weather.key'));
        });

        $this->app->alias(Weather::class, 'weather');
    }

    public function provides()
    {
        return [Weather::class, 'weather'];
    }
}