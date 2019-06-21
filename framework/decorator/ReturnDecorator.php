<?php

namespace top\decorator;

use top\decorator\ifs\DecoratorIfs;
use top\library\Register;

/**
 * 辅助控制器的装饰器
 *
 * @author topnuomi 2018年11月22日
 */
class ReturnDecorator implements DecoratorIfs
{

    public function before()
    {
        // TODO Auto-generated method stub
    }

    /**
     * 布尔或数组则显示视图
     * @param array $data
     * @throws \Exception
     */
    public function after($data)
    {
        // TODO Auto-generated method stub
        if (is_bool($data) && $data === true)
            $data = [];
        if (is_array($data)) {
            if (request()->isAjax()) { // 如果是ajax请求，则将数组转json，echo出去
                echo json_encode($data);
            } else { // 显示视图
                $route = Register::get('Router');
                $view = Register::get('View');
                echo $view->fetch($route->ctrl . '/' . $route->action, $data);
            }
        }
    }
}
