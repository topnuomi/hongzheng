<?php

namespace top\library\template\ifs;

/**
 * 模板接口
 * @author topnuomi 2018年11月22日
 */
interface TemplateIfs
{

    public function run();

    public function cache($status);

    /**
     * 处理模板
     * @param $file
     * @param $params
     * @param $cache
     * @return mixed
     */
    public function fetch($file, $params, $cache);
}
