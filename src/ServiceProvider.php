<?php

namespace DavinBao\LaravelLogViewer;

use DavinBao\LaravelLogViewer\Controllers\LogController;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $app = $this->app;

        $moduleName = 'LaravelLogViewer';

        $this->loadTranslationsFrom(__DIR__ . '/lang/', $moduleName);

        $configPath = __DIR__ . '/../config/laravellogviewer.php';
        $this->publishes([$configPath => config_path('laravellogviewer.php')], 'config');

        $routeConfig = [
            'namespace' => 'DavinBao\LaravelLogViewer\Controllers',
            'prefix' => $app['config']->get('laravellogviewer.route_prefix'),
            'module'=>'',
        ];

        $this->getRouter()->group($routeConfig, function($router) use($moduleName) {
            $router->get('log', [
                'uses' => 'LogController@index',
                'as' => json_encode(['parent'=>null, 'icon'=>'bug', 'display_name'=>'系统日志', 'is_menu'=>1, 'sort'=>9999, 'description'=>'']),
            ]);

            $router->get('assets/css/{name}', [
                'uses' => 'AssetController@css',
                'as' => snake_case($moduleName) . '.assets.css',
            ]);

            $router->get('assets/javascript/{name}', [
                'uses' => 'AssetController@js',
                'as' => snake_case($moduleName) . '.assets.js',
            ]);

            $router->get('assets/fonts/{name}/{type}', [
                'uses' => 'AssetController@fonts',
                'as' => snake_case($moduleName) . '.assets.fonts',
            ]);

            $router->get('assets/images/{name}/{type}', [
                'uses' => 'AssetController@images',
                'as' => snake_case($moduleName) . '.assets.images',
            ]);
        });

        $this->loadViewsFrom(__DIR__.'/Views', $moduleName);

        $this->registerMiddleware('laravel_log_viewer_catch_exception', 'DavinBao\PhpGit\Middleware\CatchException');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $configPath = __DIR__ . '/../config/laravellogviewer.php';
        $this->mergeConfigFrom($configPath, 'laravellogviewer');
    }

    protected function registerMiddleware($key, $middleware) {
        $this->app['router']->middleware($key, $middleware);
    }

    /**
     * Get the active router.
     *
     * @return Router
     */
    protected function getRouter() {
        return $this->app['router'];
    }

    /**
     * Check the App Debug status
     */
    protected function checkAppDebug() {
        return $this->app['config']->get('app.debug');
    }
}
