<?php

namespace top\library;

use top\library\route\Command;
use top\library\route\Pathinfo;

class App
{

    /**
     * 执行
     * @param int $type
     * @param string $defaultModule
     */
    public static function start($type = 1, $defaultModule = 'home')
    {
        // 注册框架自动加载
        require 'Loader.php';
        $loader = new Loader();
        $loader->set('top', FRAMEWORK_PATH);
        $loader->set(APP_NS, APP_PATH);
        $loader->register();

        // composer自动加载
        $composerLoadFile = FRAMEWORK_PATH . 'vendor/autoload.php';
        if (file_exists($composerLoadFile)) {
            require $composerLoadFile;
        }

        // 使用whoops美化异常输出
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

        // if (PHP_VERSION > 5.6) {
        //     set_error_handler([new BaseError(), 'handler']);
        // }
        // set_exception_handler([new BaseException(), 'handler']);

        $routeDriver = '';
        if (php_sapi_name() == 'cli') {
            // 命令行运行程序
            $routeDriver = new Command();
        } else {
            // 其他方式
            switch ($type) {
                case 1:
                    $routeDriver = new Pathinfo();
                    break;
                default:
                    // 其他
            }
        }
        // 实例化路由
        (new Router($routeDriver, $defaultModule))->handler();
    }
}
