<?php

class Cf
{
    private static $_langs = null;
    private static $_configs = null;

    public static function getRootPath()
    {
        return ROOT;
    }

	public static function autoload($class)
	{
		$dirs = [ROOT . '/core', APP_ROOT . '/model', APP_ROOT . '/controller'];
		foreach ($dirs as $dir) {
			$filename = $dir . '/' . $class . '.php';

			if (file_exists($filename)) {
				include_once($filename);

				return;
			}
		}
	}
	
    /**
     * 获取配置 非必填
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public static function config($key, $default = null)
    {
        if (self::$_configs === null) {
            self::$_configs = include(self::getRootPath() . '/config.php');
        }

        $config = self::$_configs;
        if (isset($config[$key])) {
            return $config[$key];
        } else {
            return $default;
        }
    }

    public static function C($key)
    {
        $ret = self::config($key);
        if ($ret === null) {
            throw new Exception($key . ' 配置不存在！');
        }

        return $ret;
    }

    public static function getDbConfig($dbkey)
    {
        if ($dbkey == '' || $dbkey == 'db') {
            $cfg = parse_url(self::C('DB_CONFIG'));
        } else {
            die('没有该数据库配置' . $dbkey);
        }
        $config = [
            'host'     => $cfg['host'] . (isset($cfg['port']) ? ':' . $cfg['port'] : ''),
            'user'     => $cfg['user'],
            'password' => $cfg['pass'],
            'dbname'   => str_replace('/', '', $cfg['path']),
            'charset'  => 'utf8mb4',
        ];

        return $config;
    }

    public static function getRedisConfig()
    {
        return [
            'host'     => self::C('REDIS_HOST'),
            'port'     => self::C('REDIS_PORT'),
            'password' => self::C('REDIS_AUTH'),
            'db' => self::C('REDIS_DB'),
            'pre'      => 'contact_',
        ];
    }

    public static function isIos()
    {
        return request('clientType') == 2;//安卓1，ios 2
    }

    public static function lang($code)
    {
        if (self::$_langs === null) {
            self::$_langs = include(self::getRootPath() . '/config/langs.php');
        }

        return isset(self::$_langs[$code]) ? self::$_langs[$code] : $code;
    }

    /**
     * 是否正式环境
     * @return mixed
     * @throws Exception
     */
    public static function isProductEnv()
    {
        return self::C('JPUSH_PRODUCTION');
    }
}

?>