<?php

function request($name, $filter = true)
{
    return Func::request($name, $filter);
}

function lang($code)
{
    $GLOBALS['RESULT_CODE'] = $code;

    return Cf::lang($code);
}

function sql()
{
    if (isset($GLOBALS['LAST_SQL_PARAMS'])) {
        array_shift($GLOBALS['LAST_SQL_PARAMS']);
        foreach ($GLOBALS['LAST_SQL_PARAMS'] as $value) {
            $GLOBALS['LAST_SQL'] = preg_replace('/\?/', "'$value'", $GLOBALS['LAST_SQL'], 1);
        }
    }

    echo $GLOBALS['LAST_SQL'];
}

if (!class_exists('Redis', false)) {
    Func::$_cacheOpen = false;
} else {
    Func::$_cacheOpen = Cf::C('REDIS_OPEN');
}

class Func
{
    //基础url
    private static $_hostUrl;
    private static $_globals = array();
    private static $soRedis = null;//redis实例
    private static $_redisPre = 'rd_';
    public static $_cacheOpen = true;
    public static $shutDownOpen = true;

    public static function C($key)
    {
        return Cf::C($key);
    }

    public static function getDbConfig($dbkey = 'db')
    {
        return Cf::getDbConfig($dbkey);
    }

    public static function getRedisConfig()
    {
        return Cf::getRedisConfig();
    }

    /**
     * 根据控制器名称和url参数获取链接，需在Cf类实现getAcUrl方法
     * @param string $action
     * @param string $controller
     * @param array $vars
     * @return string
     */
    public static function U($action = '', $controller = '', $vars = [])
    {
        if ($action == '/') {
            return self::getHostUrl();
        }

        $url = self::getHostUrl() . Cf::getAcUrl($action, $controller);
        if (!empty($vars)) {
            foreach ($vars as $k => $var) {
                $url .= '&' . $k . '=' . $var;
            }
        }

        return $url;
    }

    /*
     * 获取redis单一实例
    */
    public static function getRedis()
    {
        if (!class_exists('Redis', false)) {
            return null;
        }

        if (self::$soRedis === null) {
            $redis = new Redis();
            $config = Func::getRedisConfig();
            $redis->connect($config['host'], $config['port']);
            $redis->auth($config['password']);
            $redis->select($config['db']);
            self::$soRedis = $redis;
            if (isset($config['pre'])) {
                self::$_redisPre = $config['pre'];
            }
        }

        return self::$soRedis;
    }

    public static function getNextId($table, $main = 'global_ids')
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $main = self::$_redisPre . $main;

        return $redis->hIncrby($main, $table, 1);
    }

    public static function getCacheLength($keymain)
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        return $redis->hLen($keymain);
    }

    public static function getCacheAll($keymain)
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        return $redis->hGetAll($keymain);
    }

    /**
     * @param $keymain
     * @param $key
     * @param $value
     * @param int $expire 0 不设置或不更改有效期
     * @param int $count
     * @return bool
     */
    public static function setCache($keymain, $key, $value, $expire = 600, $count = 2000)
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        //缓存上限数量
        $count = $count * 2;//一半key是有效期字段
        if ($redis->hLen($keymain) > $count) {
            $redis->del($keymain);
        }

        $redis->hSet($keymain, $key, serialize($value));
        if ($expire > 0) {
            $redis->hSet($keymain, $key . '_expire', time() + $expire);
        }
    }

    /**
     * 删除缓存
     * @param $keymain
     * @param string $field 不为空则删除子域
     * @return bool
     */
    public static function delCache($keymain, $field = '')
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        if ($field) {
            $redis->hDel($keymain, $field);
        } else {
            $redis->del($keymain);
        }
    }

    /**
     * @param $keymain
     * @param $key
     * @param $expire 0永久 -1立即过期
     * @return bool
     */
    public static function setCacheExpire($keymain, $key, $expire)
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        $redis->hSet($keymain, $key . '_expire', $expire);
    }

    public static function getCache($keymain, $key)
    {
        if (!self::$_cacheOpen) {
            return false;
        }

        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        $expire = $redis->hGet($keymain, $key . '_expire');
        if ($redis->hExists($keymain, $key) && (!$expire || $expire > time())) {
            $value = $redis->hGet($keymain, $key);
            $data = unserialize($value);
        } else {
            $data = false;
        }

        return $data;
    }

    public static function listLen($keymain)
    {
        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        return $redis->llen($keymain);
    }

    public static function listPush($keymain, $params)
    {
        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;


        return $redis->rpush($keymain, serialize($params));
    }

    public static function listPop($keymain)
    {
        $redis = self::getRedis();
        $keymain = self::$_redisPre . $keymain;

        $value = $redis->lpop($keymain);

        return unserialize($value);
    }

    /**
     * 获取全局变量
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getGlobal($key, $default = '')
    {
        if (isset(self::$_globals[$key])) {
            return self::$_globals[$key];
        } else {
            return $default;
        }
    }

    /**
     * 设置全局变量,只有第一次设置有效
     * @param string $key
     * @param string $value
     */
    public static function setGlobal($key, $value)
    {
        if (!isset(self::$_globals[$key])) {
            self::$_globals[$key] = $value;
        }
    }


    /**
     * 提取二维数组的单个字段
     * @param Array $arr 二维数组
     * @param String $field 提取哪个字段
     * @param String $key 哪个字段作为键值
     * @return Array
     */
    public static function pickArrayField(&$arr, $field, $key = '')
    {
        $aRet = array();
        if ($key !== '') {
            foreach ($arr AS $aVal) {
                $aRet[$aVal[$key]] = $aVal[$field];
            }
        } else {
            foreach ($arr AS $aVal) {
                $aRet[] = $aVal[$field];
            }
        }

        return $aRet;
    }

    /**
     * 根据某个字段归类
     * @param Array $arr 二维数组
     * @param sring $k 字段名
     * @param bool $merge
     * @return array
     */
    public static function setArrayKey(&$arr, $k, $merge = false)
    {
        $aRet = array();
        foreach ($arr AS $key => $val) {
            if ($merge) {
                $aRet[$val[$k]][] = $arr[$key];
            } else {
                $aRet[$val[$k]] = $arr[$key];
            }
        }

        return $aRet;
    }

    /**
     * 按$ordarr的顺序对$arr进行排序（以$arr[$k]关联）
     * @param $arr
     * @param $ordarr
     * @param string $k
     * @return array
     */
    public static function orderArray(&$arr, &$ordarr, $k = 'id')
    {
        $data = self::setArrayKey($arr, $k, false);

        $ret = array();
        foreach ($ordarr as $id) {
            $ret[] = $data[$id];
        }

        return $ret;
    }

    public static function trimArrayNullValue($arr)
    {
        if (empty($arr)) {
            return [];
        }
        foreach ($arr as &$val) {
            if ($val == null) {
                $val = '';
            }
        }

        return $arr;
    }

    /**
     * 提取数组部分字段值
     * @param $arr
     * @param $fields
     * @return array
     */
    public static function fieldsArray($data, $fields, $multi = true)
    {
        $aFields = explode(',', $fields);
        $ret = [];
        if ($multi) {
            foreach ($data as $row) {
                $arr = [];
                foreach ($aFields as $field) {
                    $arr[trim($field)] = $row[trim($field)];
                }
                $ret[] = $arr;
            }
        } else {
            foreach ($aFields as $field) {
                $ret[trim($field)] = $data[trim($field)];
            }
        }

        return $ret;
    }

    /**
     * 获取request变量
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function request($name, $filter = true)
    {
        if (isset($_POST[$name])) {
            $ret = $_POST[$name];
        } elseif (isset($_GET[$name])) {
            $ret = $_GET[$name];
        } else {
            $ret = '';
        }

        if ($filter) {
            self::filter($ret);
        }

        return $ret;
    }

    public static function R($name, $filter = true)
    {
        return self::request($name, $filter);
    }


    /**
     * 过滤输入字符串
     * @param $mVal
     */
    public static function filter(&$mVal)
    {
        //return;
        if (is_array($mVal)) {
            foreach ($mVal as &$val) {
                self::filter($val);
            }
        } else {
            $mVal = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $mVal);
            //$mVal = htmlentities($mVal, ENT_QUOTES, 'UTF-8');
            //$mVal = addslashes($mVal);
        }
    }

    /**
     * 一次获取多个提交的数据
     * @param mixed fields 例：'name,logo,type',或者数组
     * @return array data
     */
    public static function getRequests($fields)
    {
        $data = array();
        if (is_array($fields)) {
            $arr = $fields;
        } else {
            if ($fields !== '') {
                $arr = explode(',', $fields);
            } else {
                $arr = array();
            }
        }
        foreach ($arr as $name) {
            $data[$name] = self::request($name);
        }

        return $data;
    }

    /**
     * 过滤逗号分割的数字字符串 如 1,2,3
     * @param $numlist
     * @param string $separator
     */
    public static function filterNumList($numlist, $ignore_zero = true, $separator = ',')
    {
        $aNums = explode($separator, $numlist);

        $ret = array();
        foreach ($aNums as &$value) {
            $value = intval($value);
            if ($ignore_zero) {
                if ($value) {
                    $ret[] = $value;
                }
            } else {
                $ret[] = $value;
            }
        }

        return join($separator, $ret);
    }

    /**
     * 数组连接成字符串
     * @param $arr
     * @param bool ignore_zero
     * @param string separator
     * @return string
     */
    public static function joinArray($arr, $ignore_zero = true, $separator = ',', $func = 'intval')
    {
        if (!is_array($arr)) {
            return '';
        }

        $ret = array();
        foreach ($arr as $value) {
            if ($func) {
                $value = $func($value);
            }
            if ($ignore_zero) {
                if ($value) {
                    $ret[] = $value;
                }
            } else {
                $ret[] = $value;
            }
        }

        return join($separator, $ret);
    }

    /**
     * 获取当前完整域名，包含协议和端口 末尾不带/
     * @return string
     */
    public static function getHostUrl()
    {
        if (self::$_hostUrl) {
            return self::$_hostUrl;
        }

        if ($_SERVER['SERVER_PORT'] == '443') {
            $s = 'https://' . $_SERVER['HTTP_HOST'];
        } else {
            $s = 'http://' . $_SERVER['HTTP_HOST'];
            if ($_SERVER['SERVER_PORT'] != '80') {
                $s .= ':' . $_SERVER['SERVER_PORT'];
            }
        }

        $s .= dirname($_SERVER['SCRIPT_NAME']);
        if (substr($s, -1) == "\\" || substr($s, -1) == "/") {
            $s = substr($s, 0, -1);
        }
        //$s .= '/';

        self::$_hostUrl = $s;

        return $s;
    }

    /**
     * 替换{var}为$data['var']
     * @param mixed $mVal 字符串或数组
     * @param array $data 数据
     * @param string $tag_begin
     * @param string $tag_end
     */
    public static function _replaceValue(&$mVal, $data, $tag_begin = '{', $tag_end = '}')
    {
        if (is_array($mVal)) {
            foreach ($mVal as &$val) {
                self::_replaceValue($val, $data);
            }
        } else {
            preg_match_all("/\\" . $tag_begin . "\w+\\" . $tag_end . "/", $mVal, $matches);
            foreach ($matches[0] as $value) {
                $key = str_replace(array($tag_begin, $tag_end), '', $value);
                $mVal = str_replace($value, $data[$key], $mVal);
            }
        }
    }


    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    public static function get_client_ip($type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if ($ip !== null) {
            return $ip[$type];
        }
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
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);

        return $ip[$type];
    }

    /**
     * 自动创建目录
     * @param $dir
     * @return boolean
     */
    public static function mkdirs($dir)
    {
        if (!is_dir($dir)) {
            if (!self::mkdirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0777)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 删除文件夹
     * @param $dir
     * @return boolean
     */
    public static function deldir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取数组的值
     * @param  $arr
     * @param  $key
     * @param string $default
     * @return string
     */
    public static function KV($arr, $key, $default = '')
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * 获取分页 limit
     * @param int $p
     * @param int $pagesize
     */
    public static function getPageLimit($p, $pagesize)
    {
        $p = max(1, (int)$p);
        $pagesize = $pagesize ? (int)$pagesize : 10;
        $min = ($p - 1) * $pagesize;

        return $min . ',' . $pagesize;
    }

    /**
     * 从结果数组获取分页数据
     * @param $data
     * @param $p
     * @param $pagesize
     * @return array
     */
    public static function getPageData($data, $p, $pagesize)
    {
        $p = max(1, (int)$p);
        $pagesize = $pagesize ? (int)$pagesize : 10;
        $min = ($p - 1) * $pagesize;

        return array_slice($data, $min, $pagesize);
    }

    /**
     * 手机号检测
     */
    public static function checkMobile($mobile)
    {
        if (preg_match("/^1[34587]\d{9}$/", $mobile)) {
            return true;
        } else {
            return false;
        }
    }


    public static function filterParam(&$param, $filter = [], $set = true)
    {
        if (empty($filter)) {
            return;
        }

        foreach ($param as $k => $v) {
            if ($set !== in_array($k, $filter)) {
                unset($param[$k]);
            }
        }
    }

    /**
     * 邮箱格式检测
     * @param $email
     * @return bool
     */
    public static function checkEmail($email)
    {
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $email);
    }

    public static function preLoad($param, $model, $method)
    {
        Func::listPush('preload', ['param' => $param, 'model' => $model, 'method' => $method]);
    }

    public static function checkUpload($field)
    {
        return isset($_FILES[$field]) && $_FILES[$field]['name'];
    }

    /**
     * 根据原图路径获取缩略图路径
     * @param  $url
     * @param  $thumb
     * @return string
     */
    public static function getThumbUrl($url, $thumb)
    {
        $pos = strrpos($url, '/') + 1;

        return substr($url, 0, $pos) . str_replace('.', '_' . $thumb . '.', substr($url, $pos));
    }

    public static function htmlDecode($content)
    {
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    public static function shutDown($path)
    {
        if (!self::$shutDownOpen) {
            return;
        }
        $out = ob_get_contents();
        ob_clean();
        if ($out) {
            $arr = json_decode($out, true);
            if (empty($arr)) {
                $log = date('Y-m-d H:i:s') . '：' . $out . "\n";
                $log .= '参数：' . var_export($_REQUEST, true) . "\n";
                file_put_contents($path . '/error_log' . date('Y-W') . '.txt', $log, FILE_APPEND);
                $out = '';
            }
        }
        if ($out) {
            die($out);
        } else {
            die('{"done":false,"msg":"网络请求异常","code":40000,"retval":null}');
        }
    }

    /**
     * 获取控制器和方法名
     * @return array
     */
    public static function getPathInfo()
    {
        $app = 'index';
        $action = 'index';

        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $arr = explode('?', $_SERVER['REQUEST_URI']);
            $path = $arr[0];
        }

        $aPath = explode('/', $path);
        if (isset($aPath[1]) && $aPath[1]) {
            $app = $aPath[1];
        }
        if (isset($aPath[2]) && $aPath[2]) {
            $action = $aPath[2];
        }

        /*
        if (count($aPath) > 3) {
            for($i=3;$i<=count($aPath);$i+=2) {
                if (isset($aPath[$i]) && $aPath[$i]) {
                    $_GET[$aPath[$i]] = isset($aPath[$i+1]) ? $aPath[$i+1] : '';
                }
            }
        }*/

        return [
            'app'    => $app,
            'action' => $action,
        ];
    }

    /**
     * 获取二维码
     * @param $text
     * @param int $size
     * @param bool|true $return
     * @return string
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     */
    public static function getQrCode($text, $size = 150, $return = true)
    {
        if ($return) {
            ob_start();
        }

        $qrCode = new Endroid\QrCode\QrCode();
        $qrCode
            ->setText($text)
            ->setSize($size)
            ->setPadding(0)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->render(null, Endroid\QrCode\QrCode::IMAGE_TYPE_PNG);

        if ($return) {
            return ob_get_clean();
        }
    }

    /**
     * 获取首字母
     * @param $str
     * @return bool|string
     */
    public static function getFirstLetter($str)
    {
        $oldStr = $str;
        $str = iconv("UTF-8", "GBK//IGNORE", $str);//如果程序是gbk的，此行就要注释掉
        if (preg_match("/^[\x7f-\xff]/", $str)) {
            $fchar = ord($str{0});
            if ($fchar >= ord("A") and $fchar <= ord("z")) {
                return strtoupper($str{0});
            }
            $a = $str;
            $val = ord($a{0}) * 256 + ord($a{1}) - 65536;
            if ($val >= -20319 and $val <= -20284) {
                return "A";
            }
            if ($val >= -20283 and $val <= -19776) {
                return "B";
            }
            if ($val >= -19775 and $val <= -19219) {
                return "C";
            }
            if ($val >= -19218 and $val <= -18711) {
                return "D";
            }
            if ($val >= -18710 and $val <= -18527) {
                return "E";
            }
            if ($val >= -18526 and $val <= -18240) {
                return "F";
            }
            if ($val >= -18239 and $val <= -17923) {
                return "G";
            }
            if ($val >= -17922 and $val <= -17418) {
                return "H";
            }
            if ($val >= -17417 and $val <= -16475) {
                return "J";
            }
            if ($val >= -16474 and $val <= -16213) {
                return "K";
            }
            if ($val >= -16212 and $val <= -15641) {
                return "L";
            }
            if ($val >= -15640 and $val <= -15166) {
                return "M";
            }
            if ($val >= -15165 and $val <= -14923) {
                return "N";
            }
            if ($val >= -14922 and $val <= -14915) {
                return "O";
            }
            if ($val >= -14914 and $val <= -14631) {
                return "P";
            }
            if ($val >= -14630 and $val <= -14150) {
                return "Q";
            }
            if ($val >= -14149 and $val <= -14091) {
                return "R";
            }
            if ($val >= -14090 and $val <= -13319) {
                return "S";
            }
            if ($val >= -13318 and $val <= -12839) {
                return "T";
            }
            if ($val >= -12838 and $val <= -12557) {
                return "W";
            }
            if ($val >= -12556 and $val <= -11848) {
                return "X";
            }
            if ($val >= -11847 and $val <= -11056) {
                return "Y";
            }
            if ($val >= -11055 and $val <= -10247) {
                return "Z";
            }
        } else {
            return substr($oldStr, 0, 1);
        }
    }

    /**
     * 获取两点之间的距离 单位 米
     * @param  $lng1 纬度1
     * @param  $lat1 经度1
     * @param  $lng2
     * @param  $lat2
     * @return number
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2)
    {
        //将角度转为狐度
        $radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2),
                    2))) * 6378.137 * 1000;

        return round($s);
    }

    /**
     * 获取一定范围内最大最小经纬度
     * @param double $lat 纬度
     * @param double $lng 经度
     * @param int $raidus 单位米
     * @return array
     */
    public static function getAround($lat, $lng, $raidus)
    {
        $PI = 3.14159265;

        $latitude = $lat;
        $longitude = $lng;

        $degree = (24901 * 1609) / 360.0;
        $raidusMile = $raidus;

        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $raidusMile;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;

        $mpdLng = $degree * cos($latitude * ($PI / 180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = abs($dpmLng * $raidusMile);
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;

        return array(
            'minLat' => round($minLat, 6),
            'maxLat' => round($maxLat, 6),
            'minLng' => round($minLng, 6),
            'maxLng' => round($maxLng, 6),
        );
    }

    /**
     * 处理返回数据
     * @param $data
     */
    public static function formatReturnData($data)
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = self::formatReturnData($value);
            } else {
                if ($value === null) {
                    $value = '';
                } elseif ($value === 'REAL_NULL'){
                    $value = null;
                } else {
                    $value = strval($value);
                }
            }
        }

        return $data;
    }
}

class DbMysqli
{
    protected $mpLink = null;    // 当前使用的数据库连接符（p=resource link_identifier，表示资源符变量）

    public static $params = [];

    /*
     * 获取数据库实例
     */
    public static function getInstance($aDbConfig)
    {
        extract($aDbConfig);

        $oDb = new DbMysqli();
        $oDb->connect($host, $user, $password, $dbname, $charset);

        return $oDb;
    }

    /*
     * @desc    连接数据库
     * @para    string  $cDbHost 数据库主机地址
     * @para    string  $cDbUser 登陆帐号
     * @para    string  $cDbPass   登陆密码
     * @para    string  $cDbName 数据库名
     * @para    string  $cDbCharset  数据库字符集
     * @para    bool    $bPconnect   是否进行长连接，默认false，普通连接
     * @return
     */
    protected function connect($cDbHost, $cDbUser, $cDbPass, $cDbName = '', $cDbCharset = '')
    {
        $hosts = explode(':', $cDbHost);
        if (!isset($hosts[1])) {
            $hosts[1] = '3306';
        }
        $mysqli = new mysqli($hosts[0], $cDbUser, $cDbPass, $cDbName, $hosts[1]);
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
        }

        $mysqli->set_charset($cDbCharset);
        $this->mpLink = $mysqli;
    }

    /*
     * @desc    根据sql查询或执行，返回结果集
     * @para    string  $cSql    查询的sql语句
     * @return  resource-handler/bool   结果集资源符（针对SELECT，SHOW，EXPLAIN 或 DESCRIBE 语句），
                                        其他语句执行成功返回true，如果sql语句错误则返回false
     */
    public function query($cSql)
    {
        $GLOBALS['LAST_SQL'] = $cSql;
        //$begin = microtime(true);

        $stmt = $this->mpLink->prepare($cSql);
        if ($stmt === false) {
            $this->halt('MySQL Query Error', $cSql);
        }
        $params = [];
        if (!empty(self::$params)) {
            $pms = [str_pad('', count(self::$params), 's')];

            foreach (self::$params as $k => $pm) {
                $params[] = &self::$params[$k];
            }
            $params = array_merge($pms, $params);
            $GLOBALS['LAST_SQL_PARAMS'] = $params;
            call_user_func_array([$stmt, 'bind_param'], $params);
        }
        self::$params = [];
        $stmt->execute();
        $pQuery = $stmt->get_result();

        // 执行查询
        //$pQuery = $this->mpLink->query($cSql);

        //Func::$shutDownOpen = false;echo round(1000 * (microtime(true) - $begin)) . "ms: \n" . $cSql . "\n";

        if (!$pQuery) {
            array_shift($params);
            foreach ($params as $pm) {
                $cSql = preg_replace("/\?/", "'" . $pm . "'", $cSql, 1);
            }

            $this->halt('MySQL Query Error', $cSql);
        }

        return $pQuery;
    }

    public function execute($cSql)
    {
        $GLOBALS['LAST_SQL'] = $cSql;
        $stmt = $this->mpLink->prepare($cSql);
        if ($stmt === false) {
            $this->halt('MySQL Query Error', $cSql);
        }
        $params = [];
        if (!empty(self::$params)) {
            $pms = [str_pad('', count(self::$params), 's')];

            foreach (self::$params as $k => $pm) {
                $params[] = &self::$params[$k];
            }
            $params = array_merge($pms, $params);
            $GLOBALS['LAST_SQL_PARAMS'] = $params;
            call_user_func_array([$stmt, 'bind_param'], $params);
        }
        self::$params = [];

        $ret = $stmt->execute();
        if ($ret == false) {
            array_shift($params);
            foreach ($params as $pm) {
                $cSql = preg_replace("/\?/", "'" . $pm . "'", $cSql, 1);
            }

            $this->halt('MySQL Query Error', $cSql);
        }

        return $ret;
    }

    public function enforce($cSql)
    {
        $GLOBALS['LAST_SQL'] = $cSql;
        $ret = $this->mpLink->query($cSql);
        if ($ret === false) {
            $this->halt('MySQL Query Error', $cSql);
        }

        return $ret;
    }


    public function begin()
    {
        $this->enforce('start transaction');
    }

    public function commit()
    {
        $this->enforce('commit');
    }

    public function rollback()
    {
        $this->enforce('rollback');
    }

    /*
     * @desc    根据sql取第一条记录的数据
     * @para    string  $cSql    查询的sql语句
     * @return  bool/arr    有数据则返回记录的数据，无数据返回false（同方法fetchArray）
     */
    public function fetchFirstArray($cSql)
    {
        $pQuery = $this->query($cSql);

        $aResult = $this->fetchArray($pQuery);

        $pQuery->close();

        return empty($aResult) ? array() : $aResult;
    }

    /*
     * @desc    根据sql取所有记录的数据，返回一个保存所有记录数据的二维数组
     * @para    string  $cSql    查询的sql语句
     * @return  bool/arr    有数据则返回所有记录数据的二维数组，无数据返回false
     */
    public function fetchAllArray($cSql)
    {
        $pQuery = $this->query($cSql);

        $aResults = array();
        while ($arr = $this->fetchArray($pQuery)) {
            $aResults[] = $arr;
        }
        $pQuery->close();

        return empty($aResults) ? array() : $aResults;
    }

    /*
     * 获取所有记录，并设置其中一个字段值作为数组键值，一般是id
     */
    public function fetchAllArrayWithKey($cSql, $cKeyField)
    {
        $pQuery = $this->query($cSql);

        $aResults = array();
        while ($arr = $this->fetchArray($pQuery)) {
            $aResults[$arr[$cKeyField]] = $arr;
        }

        $pQuery->close();

        return empty($aResults) ? array() : $aResults;
    }


    /**
     * 获取多条记录同一个字段的值列表
     */
    public function fetchFieldList($cSql, $cField)
    {
        $pQuery = $this->query($cSql);

        $aResults = array();
        while ($arr = $this->fetchArray($pQuery)) {
            $aResults[] = $arr[$cField];
        }
        $pQuery->close();

        return $aResults;
    }

    /*
     * fetchFirstField() - 根据sql取第一条记录第一个字段内容
     *
     * @since 1.0
     *
     * @param string $cSql 查询的sql语句
     * @return mixed
     */
    public function fetchFirstField($cSql)
    {
        $pQuery = $this->query($cSql);

        if ($pQuery) {
            $aRow = $pQuery->fetch_row();
            $mResult = $aRow[0];
        } else {
            $mResult = false;
        }
        $pQuery->close();

        return $mResult;
    }

    /*
     * @desc    取结果集中的一条记录的数据
     * @para    resource result     $pQuery 结果集资源符（query方法返回的值）
     * @para    int     设置返回结果的类型 MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
     * @return  bool/arr    结果集中有数据，则返回记录的数组，结果中无数据返回false
     */
    public function fetchArray($pQuery, $cResultType = MYSQLI_ASSOC)
    {
        return $pQuery->fetch_array($cResultType);
    }

    /**
     * insert() - 插入一条记录
     *
     * @param string $cTable
     * @param array $aData 字段名对应字段值
     * @return mixed(resource|boolean)
     */
    public function insert($cTable, $aData)
    {
        $data = [];
        foreach ($aData as $value) {
            $data[] = '?';
            self::$params[] = strval($value);
        }

        $aFields = array_keys($aData);
        $cSql = "INSERT INTO `$cTable` (`" . implode('`,`', $aFields) . "`) VALUES (" . implode(",", $data) . ")";

        $pQuery = $this->execute($cSql);

        return $pQuery ? $this->getInsertId() : false;
    }

    /**
     * 批量插入数据
     * @param $cTable
     * @param $allData
     * @return mixed
     */
    public function multiInsert($cTable, $allData)
    {
        $aFields = array_keys($allData[0]);
        $cSql = "INSERT INTO `$cTable` (`" . implode('`,`', $aFields) . "`) VALUES ";
        $dataSql = [];
        foreach ($allData as $aData) {
            $dataSql[] = "('" . implode("','", $aData) . "')";
        }
        $cSql .= join(', ', $dataSql);

        $pQuery = $this->execute($cSql);

        return $pQuery;
    }

    /**
     * update() - 更新记录
     *
     * @param string $cTable
     * @param array $aSet 字段名对应字段值
     * @param string $cWhere
     * @return mixed(resource|boolean)
     */
    public function update($cTable, $aSet, $cWhere)
    {
        $aData = array();
        if (is_numeric($cWhere) || true === $cWhere) {
            die("UPDATE ALL ROWS IS NOT ALLOWED.");
        }
        $params = self::$params;
        self::$params = [];
        foreach ($aSet as $k => $v) {
            if (is_numeric($k)) {
                $aData[] = $v;
            } else {
                //$aData[] = "`$k` = '$v'";
                $aData[] = "`$k` = ?";
                self::$params[] = $v;
            }
        }
        self::$params = array_merge(self::$params, $params);

        $cSql = "UPDATE `$cTable` SET " . implode(', ', $aData) . " WHERE $cWhere";

        return $this->execute($cSql);
    }

    /**
     * delete() - 删除记录
     *
     * @param string $cTable
     * @param string $cWhere
     * @return mixed(resource|boolean)
     */
    public function delete($cTable, $cWhere)
    {
        if (is_numeric($cWhere) || true === $cWhere) {
            die("DELETE ALL ROWS IS NOT ALLOWED.");
        }

        $cSql = "DELETE FROM `$cTable` WHERE $cWhere";

        return $this->execute($cSql);
    }

    /*
     * @desc    取得上一步 INSERT 操作产生的 ID
     * @para
     * @return  int     返回上一步 INSERT 操作产生的 ID
     */
    public function getInsertId()
    {
        return $this->mpLink->insert_id;
    }

    /*
     * @desc    错误信息处理
     * @para    string  $cMessage    错误信息字符串
     * @para    string  $cSql        如果是运行sql出错，则传递sql语句参数
     * @return
     */
    public function halt($cMessage = '', $cSql = '')
    {
        $cSql = $cSql ? ", sql: " . $cSql : '';
        $cErr = ", ErrMsg: " . $this->mpLink->error . ", ErrNo: " . $this->mpLink->errno;

        $this->mpLink->close();

        die($cMessage . $cSql . $cErr);
    }
}

class SqlBase
{

    protected $dbConnection;//db 连接
    protected $_transaction;// 事物处理

    private static $soDbs = array();//数据库连接池

    protected $_dbsplit = false;
    protected $_dbmap = array(
        array(0, 5000, 0), //起始id*10000 大于，结束id*10000 大于等于, db编号
        array(5000, 10000, 1),
        array(10000, 15000, 2),
    );
    protected $_tbsplit = false;
    protected $_tbmap = array(
        'begin' => 3000000,//大于该值存储到分表中
        'count' => 16,//分表数量
    );
    protected $_pk = '';//拆分字段名称 一般为uid
    private $_dbno = null;
    private $_tbno = null;

    protected $_tableName = '';//基础表名
    private $_realTableName = '';//真实表名
    protected $_dbConfig = '';//db配置

    protected $_deleteStatus = 1;//默认删除的状态
    protected $_deleteField = 'status';

    public function __construct($params = array())
    {
        //parent::__construct($params);
    }

    protected function _initDb($data)
    {
        if (!$this->_pk) {
            $dbno = 0;
            $tbno = 0;
        } else {
            if (!Func::KV($data, $this->_pk)) {
                throw new Exception('必须设置分表主键值！');
            }
            $pkval = $data[$this->_pk];
            $dbno = $this->_getDbno($pkval);
            $tbno = $this->_getTbno($pkval);
        }

        $this->_setDb($dbno);
        $this->_setTb($tbno);
    }

    protected function _setDb($dbno)
    {
        if ($this->_dbno !== null && $this->_dbno == $dbno) {
            return;
        }

        $config = Func::getDbConfig($this->_dbConfig);
        if ($dbno > 0) {
            $config['dbname'] .= '_' . $dbno;
        }
        if (empty(self::$soDbs[$this->_dbConfig . $dbno])) {
            $this->dbConnection = DbMysqli::getInstance($config);
            self::$soDbs[$this->_dbConfig . $dbno] = $this->dbConnection;
        } else {
            $this->dbConnection = self::$soDbs[$this->_dbConfig . $dbno];
        }

        $this->_dbno = $dbno;
    }

    protected function _setTb($tbno)
    {
        if ($this->_tbno !== null && $this->_tbno == $tbno) {
            return;
        }

        if ($tbno > 0) {
            $this->_realTableName = $this->_tableName . '_' . $tbno;
        } else {
            $this->_realTableName = $this->_tableName;
        }

        $this->_tbno = $tbno;
    }

    protected function _getDbno($pkval)
    {
        if (!$this->_dbsplit) {
            return 0;
        }

        $pkv = floor($pkval / 10000);
        foreach ($this->_dbmap as $value) {
            if ($pkv > $value[0] && $pkv <= $value[1]) {
                return $value[2];
            }
        }

        throw new Exception('错误的db映射！请先配置！');
    }

    protected function _getTbno($pkval)
    {
        if (!$this->_tbsplit) {
            return 0;
        }

        if ($pkval > $this->_tbmap['begin']) {
            return $pkval % $this->_tbmap['count'] + 1;
        } else {
            return 0;
        }
    }

    public function tableName()
    {
        return $this->_realTableName;
    }

    protected function _insert($data)
    {
        return $this->dbConnection->insert($this->tableName(), $data);
    }

    protected function _multiInsert($data)
    {
        return $this->dbConnection->multiInsert($this->tableName(), $data);
    }

    protected function _update($columns, $conditions)
    {
        return $this->dbConnection->update($this->tableName(), $columns, $conditions);
    }

    protected function _execute($sql)
    {
        return $this->dbConnection->execute($sql);
    }

    protected function _delete($conditions)
    {
        return $this->dbConnection->delete($this->tableName(), $conditions);
    }

    protected function _getOne($cSql)
    {
        return $this->dbConnection->fetchFirstArray($cSql);
    }

    protected function _getField($cSql)
    {
        return $this->dbConnection->fetchFirstField($cSql);
    }

    protected function _getAll($cSql)
    {
        return $this->dbConnection->fetchAllArray($cSql);
    }

    public function begin()
    {
        $this->dbConnection->begin();
        $this->_transaction = 'begin';
    }

    public function commit()
    {
        if ($this->_transaction) {
            $this->dbConnection->commit();
            $this->_transaction = null;
        } else {
            throw new Exception('请先调用begin方法!');
        }
    }

    public function back()
    {
        if ($this->_transaction) {
            $this->dbConnection->rollback();
            $this->_transaction = null;
        } else {
            throw new Exception('请先调用begin方法!');
        }
    }

    public function lock($table, $type)
    {
        $sql = "LOCK TABLES $table $type";
        $this->dbConnection->enforce($sql);
    }

    public function unlock()
    {
        $sql = "UNLOCK TABLES";
        $this->dbConnection->enforce($sql);
    }

    /**
     * 查询条件
     * $where = array(
     *            'uid' => 2,//等于
     *            'id >' => 1,//大于
     *            'fid in' => '1,2,3',//in 可以用数字列表
     * 'type not in' => array(1,2,3),//或者数组
     * 'qq like' => "%r'rr%",
     * 'money between' => array(1,2),//
     * "`account` = 'ffff'",//直接原始字符串
     * 'id >|uid <' => array(1,2),//or条件
     * );
     */
    public static function where($where = array())
    {
        if (!is_array($where)) {
            return $where;
        }

        $s = '';
        foreach ($where as $key => $value) {
            $s .= $s ? " AND (" : "(";
            $s .= self::_getWhere($key, $value) . ")";
        }

        return $s;
    }


    protected static function _filedWhere($key, $value)
    {
        $s = '';
        if (is_numeric($key)) {
            $s .= $value;
        } else {
            /*
            if (is_array($value)) {
                foreach ($value as &$val) {
                    $val = addslashes($val);
                }
            } else {
                $value = addslashes($value);
            }*/

            $pos = strpos($key, ' ');
            if ($pos === false) {
                //$s .= "`$key` = '$value'";
                $s .= "`$key` = ?";
                DbMysqli::$params[] = $value;
            } else {
                $opt = strtolower(strrchr($key, " "));
                $optall = '`' . substr($key, 0, $pos) . '`' . strtoupper(substr($key, $pos));
                if ($opt == ' in') {
                    if (!is_array($value)) {
                        $value = explode(',', $value);
                    }
                    $vals = [];
                    foreach ($value as $v) {
                        DbMysqli::$params[] = $v;
                        $vals[] = '?';
                    }
                    $value = join(",", $vals);

                    $s .= "$optall ($value)";
                } elseif ($opt == ' between') {
                    if (is_array($value)) {
                        //$value = "'" . $value[0] . "' AND '" . $value[1] . "'";
                        DbMysqli::$params[] = $value[0];
                        DbMysqli::$params[] = $value[1];
                        $value = "? AND ?";
                    }
                    $s .= "$optall $value";
                } else {
                    //$s .= "$optall '$value'";
                    $s .= "$optall ?";
                    DbMysqli::$params[] = $value;
                }
            }
        }

        return $s;
    }

    protected static function _getWhere($key, $value)
    {
        $aKeys = explode('|', $key);
        if (count($aKeys) > 1) {
            $ret = array();
            foreach ($aKeys as $i => $key) {
                $ret[] = self::_filedWhere(trim($key, ' '), $value[$i]);
            }

            return join(' OR ', $ret);
        } else {
            return self::_filedWhere($key, $value);
        }
    }
}

class ApiBaseModel extends SqlBase
{
    //错误信息
    protected $_errorMessage = '';
    protected $_errorCode = '';

    //用户对象
    protected $uid;

    //选项配置数组
    protected $aOptions = array();

    protected $fields = '*';//默认字段

    private static $_instance = [];

    public function __construct($params = array())
    {
        parent::__construct($params);

        $this->uid = (int)Func::getGlobal('uid');
    }

    public static function m($params = array())
    {
        $model = new static($params);
        $className = get_class($model);
        if (isset(self::$_instance[$className])) {
            return self::$_instance[$className];
        } else {
            self::$_instance[$className] = $model;
            return $model;
        }
    }

    /**
     * 设置或读取错误信息
     * @param string $msg
     * @return string
     */
    public function errorMessage($msg = '')
    {
        if ($msg) {
            $this->_errorMessage = $msg;

            return false;
        } else {
            return $this->_errorMessage;
        }
    }

    public function errorCode($code = '')
    {
        if ($code) {
            $this->_errorCode = $code;

            return false;
        } else {
            return $this->_errorCode;
        }
    }

    /**
     * 更新数据后的处理，一般做更新缓存操作
     * @param $where
     */
    protected function _after_modify($where)
    {

    }

    /**
     * 添加数据记录
     * @param array $data
     * @return number|boolean
     */
    public function addData($data)
    {
        $this->_initDb($data);

        $ret = $this->_insert($data);
        if ($ret !== false) {
            $this->_after_modify(array('id' => $ret));

            return $ret;
        } else {
            $this->errorMessage('数据库添加失败,请检查数据格式！');

            return false;
        }
    }

    /**
     * 批量添加数据记录
     * @param array $data
     * @return boolean
     */
    public function multiAddData($allData)
    {
        $this->_initDb($allData[0]);

        return $this->_multiInsert($allData);
    }

    /**
     * 更新记录
     * @param array $columns
     * @param array $where
     */
    public function updateData($columns, $where = array())
    {
        $this->_initDb($where);

        $conditions = Sqlbase::where($where);
        $ret = $this->_update($columns, $conditions);
        $this->_after_modify($where);

        return $ret;
    }

    /**
     * 更新数量
     * @param array $column 字段名
     * @param array $where 条件
     * @param int $count 增加数
     */
    public function updateCount($where, $column, $count = 1)
    {
        if ($count == 0) {
            return false;
        }
        $this->_initDb($where);

        $conditions = Sqlbase::where($where);
        if (is_numeric($conditions) || true === $conditions) {
            die("UPDATE ALL ROWS IS NOT ALLOWED.");
        }
        $sign = $count > 0 ? '+' : '-';
        $count = abs($count);

        $sql = "UPDATE " . $this->tableName() . " SET `$column` = `$column` " . $sign . " $count WHERE " . $conditions;

        $ret = $this->_execute($sql);
        $this->_after_modify($where);

        return $ret;
    }

    /**
     * 删除记录
     * @param array $where
     * @param string $real
     */
    public function deleteData($where, $real = false)
    {
        $this->_initDb($where);

        $conditions = Sqlbase::where($where);
        if ($real) {
            $ret = $this->_delete($conditions);
        } else {
            $ret = $this->updateData(array($this->_deleteField => $this->_deleteStatus), $conditions);
        }
        $this->_after_modify($where);

        return $ret;
    }

    /**
     * 根据主键获取一条记录
     * @param int $id
     * @param string $pk
     */
    public function getById($id, $pk = 'id', $fields = '*')
    {
        if (!$id) {
            return array();
        }
        $this->_initDb(array($pk => $id));

        $where = Sqlbase::where([$pk => $id]);

        $cSql = "SELECT $fields FROM " . $this->tableName() . " WHERE " . $where;

        $data = $this->_getOne($cSql);

        return $data;
    }

    /**
     * 是否存在记录
     * * @param array $where
     */
    public function isExists($where)
    {
        $params = array(
            'where' => $where,
        );
        $data = $this->getOne($params);

        return empty($data) ? false : true;
    }

    /**
     * 获取单个字段
     * @param array $condition
     * @param string $field
     */
    public function getField($condition = array(), $field)
    {
        $this->_initDb($condition);

        $where = Sqlbase::where($condition);
        if ($where) {
            $where = " WHERE $where";
        } else {
            $where = '';
        }
        $cSql = "SELECT " . $field . " FROM " . $this->tableName() . $where;

        return $this->_getField($cSql);
    }

    /**
     * 获取一条记录
     * @param array $params
     * @param string $format_func
     * @return array
     */
    public function getOne($params, $format_func = '')
    {
        $this->_initDb(Func::KV($params, 'where', array()));

        $fields = isset($params['fields']) ? $params['fields'] : $this->fields;
        $cSql = "SELECT $fields FROM " . $this->tableName();
        $where = isset($params['where']) ? Sqlbase::where($params['where']) : '';
        if ($where) {
            $cSql .= " WHERE $where";
        }

        $group = isset($params['group']) ? $params['group'] : '';
        if ($group) {
            $cSql .= " GROUP BY " . $group;//包括having语句写在这里
        }

        $order = isset($params['order']) ? $params['order'] : '';
        if ($order) {
            $cSql .= " ORDER BY $order";
        }

        $cSql .= " LIMIT 1";

        $data = $this->_getOne($cSql);

        if ($format_func && !empty($data)) {
            $format_func = $format_func === true ? '_format' : $format_func;
            $this->$format_func($data);
        }

        return $data;
    }

    /**
     * 获取所有记录
     * @param array $params
     * @param string $format_func
     * @return array
     */
    public function getAll($params, $format_func = '')
    {
        $this->_initDb(Func::KV($params, 'where', array()));

        $fields = isset($params['fields']) ? $params['fields'] : $this->fields;
        $cSql = "SELECT $fields FROM " . $this->tableName();
        $where = isset($params['where']) ? Sqlbase::where($params['where']) : '';
        if ($where) {
            $cSql .= " WHERE $where";
        }

        $group = isset($params['group']) ? $params['group'] : '';
        if ($group) {
            $cSql .= " GROUP BY " . $group;//包括having语句写在这里
        }

        $order = isset($params['order']) ? $params['order'] : '';
        if ($order) {
            $cSql .= " ORDER BY $order";
        }
        $limit = isset($params['limit']) ? $params['limit'] : '';
        if ($limit) {
            if (is_array($limit)) {
                $limit = Func::getPageLimit($limit[0], $limit[1]);
            }
            $cSql .= " LIMIT $limit";
        }
        $data = $this->_getAll($cSql);

        if ($format_func) {
            $format_func = $format_func === true ? '_format' : $format_func;
            foreach ($data as &$aSet) {
                if (!empty($aSet)) {
                    $this->$format_func($aSet);
                }
            }
        }

        return $data;
    }

    public function getAllBySql($cSql)
    {
        $this->_initDb([]);

        return $this->_getAll($cSql);
    }

    protected function _format(&$row)
    {
        foreach ($row as $key => $value) {
            $arr = $this->getOptions($key);
            if (!empty($arr)) {
                $row[$key] = isset($arr[$value]) ? $arr[$value] : '';
                //$row[$key . '_o'] = $value;
            }
        }
    }


    /**
     * @param array $condition
     * @return mixed
     * @throws Exception
     */
    public function getCount($condition = array())
    {
        $this->_initDb($condition);

        $where = Sqlbase::where($condition);
        if ($where) {
            $where = " WHERE $where";
        } else {
            $where = '';
        }
        $cSql = "SELECT count(*) FROM " . $this->tableName() . $where;

        return $this->_getField($cSql);
    }

    /**
     * 获取配置数据
     */
    public function getOptions($option_name, $key = null, $default = '')
    {
        $options = $this->aOptions;
        $arr = isset($options[$option_name]) ? $options[$option_name] : array();
        if ($key === null) {
            return $arr;
        } else {
            return isset($arr[$key]) ? $arr[$key] : $default;
        }
    }

    public function begin($condition = array())
    {
        $this->_initDb($condition);

        parent::begin();
    }

    public function commit($condition = array())
    {
        $this->_initDb($condition);

        parent::commit();
    }

    public function back($condition = array())
    {
        $this->_initDb($condition);

        parent::back();
    }



}