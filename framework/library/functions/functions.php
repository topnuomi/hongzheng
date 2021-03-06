<?php

/**
 * 获取/设置配置
 * @param $key
 * @param string $value
 * @return array|bool|mixed
 */
function config($key, $value = '__NULL__VALUE__')
{
    if ($value != '__NULL__VALUE__') {
        \top\library\Config::instance()->set($key, $value);
        return true;
    } else {
        return \top\library\Config::instance()->get($key);
    }
}

/**
 * 请求类
 * @return \top\library\http\Request
 */
function request()
{
    return \top\library\http\Request::instance();
}

/**
 * 响应类
 * @return \top\library\http\Response
 */
function response()
{
    return \top\library\http\Response::instance();
}

/**
 * 调用模型
 * @param $name
 * @return \top\library\Model
 */
function model($name)
{
    static $model = [];
    $class = class_full_name($name, 'model');
    if (!isset($model[$class])) {
        // 模型类存在则直接实例化
        // 模型类不存在则直接将传入的模型名当作表名处理
        if (class_exists($class)) {
            $model[$class] = new $class();
        } else {
            $model[$class] = new \top\library\Model($name);
        }
    }
    return $model[$class];
}

/**
 * 调用逻辑
 * @param $name
 * @return mixed
 */
function logic($name)
{
    static $logic = [];
    $class = class_full_name($name, 'logic');
    if (!isset($logic[$class])) {
        // 实例化逻辑类
        $logic[$class] = new $class();
    }
    return $logic[$class];
}

/**
 * 获取类全限定名
 * @param $name
 * @param string $type
 * @return string
 */
function class_full_name($name, $type = 'model')
{
    if (!strstr($name, '\\')) { // 不是类全限定名，则直接拼接全限定名
        return APP_NS . '\\' . BIND_MODULE . '\\' . $type . '\\' . $name;
    } else return $name;
}

/**
 * 拼接链接（暂时先这样
 * @param string $url
 * @param string|int $param
 * @return string
 */
function url($url, $param = '')
{
    if (!empty($param) || is_numeric($param)) {
        if (is_array($param)) {
            $param = '/' . implode('/', $param);
        } else {
            $param = '/' . $param;
        }
    }
    $url = ltrim($url, '/');
    return '/' . $url . $param . '.html';
}

/**
 * 获取当前视图文件的缓存标识
 * @return string
 */
function view_cache_ident()
{
    $ident = md5($_SERVER['REQUEST_URI'] . request()->requestMethod());
    return $ident;
}

/**
 * 设置视图缓存时间
 * @param $sec
 */
function view_cache($sec)
{
    \top\library\View::instance()->cache($sec);
}

/**
 * 参数传递
 * @param $name
 * @param $value
 */
function view_param($name, $value)
{
    \top\library\View::instance()->param($name, $value);
}

/**
 * 显示视图
 * @param string $file
 * @param array $param
 * @param bool $cache
 * @return mixed
 */
function view($file = '', $param = [], $cache = false)
{
    return \top\library\View::instance()->fetch($file, $param, $cache);
}

/**
 * 页面跳转
 * @param $url
 * @return false|string
 */
function redirect($url)
{
    if (request()->is('ajax')) {
        return json_encode([
            'redirect' => $url,
        ]);
    } else {
        header('location: ' . $url);
    }
}

/**
 * 框架session操作
 * @param $name
 * @param string $value
 * @return bool
 * @throws Exception
 */
function session($name, $value = '')
{
    $config = config('session');
    if (empty($config) || !$config['prefix']) {
        $prefix = request()->module();
    } else {
        $prefix = $config['prefix'];
    }
    if ($value === '') {
        if (isset($_SESSION[$prefix][$name])) {
            return $_SESSION[$prefix][$name];
        }
        return false;
    } else if ($value === false) {
        unset($_SESSION[$prefix][$name]);
    } else {
        $_SESSION[$prefix][$name] = $value;
    }
}

/**
 * 获取headers
 * @return array|false
 */
function get_header()
{
    if (PHP_SAPI === 'apache2handler') {
        $headers = getallheaders();
        $data = [];
        foreach ($headers as $key => $value) {
            $data[strtolower($key)] = $value;
        }
        unset($headers);
        return $data;
    } else {
        $server = $_SERVER;
        $headers = [];
        foreach ($server as $key => $value) {
            if ('http_' == strtolower(substr($key, 0, 5))) {
                $headers[strtolower(substr($key, 5))] = $value;
            }
        }
        unset($server);
        return $headers;
    }
}

/**
 * 获取表名
 * @param $classname
 * @return string
 */
function get_table_name($classname)
{
    $arr = explode('\\', $classname);
    $class = end($arr);
    $arr = str_split($class);
    for ($i = 0; $i < count($arr); $i++) {
        $ord = ord($arr[$i]);
        if ($ord > 64 && $ord < 91 && $i != 0) {
            $arr[$i - 1] = $arr[$i - 1] . '_';
        }
    }
    $table = implode('', $arr);
    return strtolower($table);
}

/**
 * 获取客户端IP
 * @param int $type
 * @param bool $client
 * @return mixed
 */
function get_client_ip($type = 0, $client = true)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if ($client) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? [
        $ip,
        $long
    ] : [
        '0.0.0.0',
        0
    ];
    return $ip[$type];
}

/**
 * 创建HTTP请求
 * @param $url
 * @param array $data
 * @param array $header
 * @return bool|mixed
 */
function create_http_request($url, $data = [], $header = [])
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, $header);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($curl);
    if (!empty($header)) {
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $response = substr($response, $headerSize);
    }
    curl_close($curl);
    if ($response) {
        return $response;
    }
    return false;
}

/**
 * 删除目录（包括子目录）
 * @param string $dirName
 */
function remove_dir($dirName)
{
    $handle = @opendir($dirName);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir($dirName . '/' . $item)) {
                    remove_dir($dirName . '/' . $item);
                } else {
                    unlink($dirName . '/' . $item);
                }
            }
        }
        closedir($handle);
        rmdir($dirName);
    }
}

/**
 * 过滤字符串
 * @param string $str
 * @return string
 */
function filter($str)
{
    $replaceArr = array(
        "/select\b|insert\b|update\b|delete\b|drop\b|;|\"|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is"
    );
    $str = preg_replace($replaceArr, '', $str);
    $str = htmlspecialchars($str);
    return $str;
}

/**
 * 过滤数组
 * @param array $array
 * @param string $filter
 * @param array $result
 */
function filter_array($array = [], $filter = 'filter', &$result = [])
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            filter_array($value, $result[$key]);
        } else {
            $result[$key] = (!$filter) ? $value : $filter($value);
        }
    }
}

/**
 * 二维数组排序操作
 * @param $arr
 * @param $key
 * @return mixed
 */
function assoc_unique($arr, $key)
{
    $tmp_arr = [];
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    // sort($arr); //sort函数对数组进行排序
    return $arr;
}

/**
 * 改变图片大小
 * @param $imgSrc
 * @param $resize_width
 * @param $resize_height
 * @param string $newName
 * @param bool $isCut
 * @return string
 */
function resize_image($imgSrc, $resize_width, $resize_height, $newName = '', $isCut = false)
{
    $im = @imagecreatefromstring(file_get_contents($imgSrc));
    $exif = exif_read_data($imgSrc);
    if (!empty($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 8:
                $im = imagerotate($im, 90, 0);
                break;
            case 3:
                $im = imagerotate($im, 180, 0);
                break;
            case 6:
                $im = imagerotate($im, -90, 0);
                break;
            default:

        }
    }
    //图片的类型
    $type = substr(strrchr($imgSrc, "."), 1);
    //目标图象地址
    $dstimg = (!$newName) ? $imgSrc : $newName . '.' . $type;
    $width = imagesx($im);
    $height = imagesy($im);
    //生成图象
    //改变后的图象的比例
    $resize_ratio = ($resize_width) / ($resize_height);
    //实际图象的比例
    $ratio = ($width) / ($height);
    if (($isCut) == 1) { //裁图
        if ($ratio >= $resize_ratio) { //高度优先
            $newimg = imagecreatetruecolor($resize_width, $resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width, $resize_height, (($height) * $resize_ratio), $height);
            ImageJpeg($newimg, $dstimg);
        }
        if ($ratio < $resize_ratio) { //宽度优先
            $newimg = imagecreatetruecolor($resize_width, $resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width, $resize_height, $width, (($width) / $resize_ratio));
            ImageJpeg($newimg, $dstimg);
        }
    } else { //不裁图
        if ($ratio >= $resize_ratio) {
            $newimg = imagecreatetruecolor($resize_width, ($resize_width) / $ratio);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $resize_width, ($resize_width) / $ratio, $width, $height);
            ImageJpeg($newimg, $dstimg);
        }
        if ($ratio < $resize_ratio) {
            $newimg = imagecreatetruecolor(($resize_height) * $ratio, $resize_height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, ($resize_height) * $ratio, $resize_height, $width, $height);
            ImageJpeg($newimg, $dstimg);
        }
    }
    ImageDestroy($im);
    //imgturn($dstimg, 1);
    return $dstimg;
}

/**
 * 判断是否是移动端
 * @return bool
 */
function is_mobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = [
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        ];
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

// 模型自动验证函数

/**
 * 检查是否为空
 *
 * @param string|array $value
 * @return boolean
 */
function notNull($value)
{
    if (is_array($value)) {
        if (empty($value)) {
            return false;
        }
    } else {
        if (!$value && !is_numeric($value)) {
            return false;
        }
    }
    return true;
}

/**
 * 预置不等于判断
 * @param $value
 * @param $value1
 * @return bool
 */
function notEqual($value, $value1)
{
    if ($value == $value1) {
        return false;
    }
    return true;
}

/**
 * 长度判断
 *
 * @param string $value
 * @param int $min
 * @param int $max
 * @return boolean
 */
function length($value, $min, $max)
{
    $length = mb_strlen($value, 'utf8');
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}
