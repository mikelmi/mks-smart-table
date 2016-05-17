<?php


namespace Mikelmi\SmartTable;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SmartTableServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SmartTable::class, function(Application $app) {
            return new SmartTable($app->make(Request::class));
        });
    }

    public function provides()
    {
        return [
            SmartTable::class
        ];
    }
}