<?php
namespace framework\library;

/**
 * 配置类
 *
 * @author topnuomi 2018年11月20日
 */
class Config {

    // 已加载的文件
    private static $files;

    // 保存配置的变量
    private $config = [];

    /**
     * 添加配置
     *
     * @param string $name            
     * @param string $value            
     */
    public function set($name, $value) {
        // 组合为数组
        $config = [
            $name => $value
        ];
        // 与原有的配置项合并
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取配置
     * @param string $name
     * @return array|mixed
     * @throws \Exception
     */
    public function get($name = '') {
        // 加载的文件名
        $module = Register::get('Router')->module;
        $file = BASEDIR . '/' . APPNS . '/' . $module . '/config/config.php';
        if (! isset(self::$files[$file])) {
            if (file_exists($file)) {
                $config = require $file;
                // 与原有的配置项合并
                $this->config = array_merge($this->config, $config);
                self::$files[$file] = true;
            }
        }
        if (empty($this->config)
            || ! isset($this->config)
            || ! $this->config
            || ! isset($this->config[$name])
            || ! $this->config[$name]
        ) {
            return [];
        }
        return $this->config[$name];
    }

    /**
     * 从配置中删除某项
     *
     * @param string $name            
     */
    public function _unset($name) {
        unset($this->config[$name]);
    }
}