<?php

class BaseView {
    protected static $viewDir = '/view/';
    protected static $layoutDir = '/view/layouts/';
    protected static $tempDir = '/temp/view/';
    protected static $tempLayoutDir = '/temp/layouts/';
    public static $cacheOpen = false;

    public static function display($view, $data, $layout) {
        extract($data);
        $CONTENT_FILE = self::getViewFile($view);//layout文件中 <?php include $CONTENT_FILE; ?\> 来包含内容文件
        $layoutfile = self::getLayoutFile($layout);

        include($layoutfile);
    }

    public static function getViewFile($view) {
        $content_file = APP_ROOT . self::$tempDir . md5($view) . '.tpm.php';
        if (!self::$cacheOpen || !file_exists($content_file)) {
            $view_file = APP_ROOT . self::$viewDir . $view . '.php';
            $content = file_get_contents($view_file);
            $content = preg_replace('/{{([^}]+)}}/', '<?php echo $1; ?>', $content);
            file_put_contents($content_file, $content);
        }

        return $content_file;
    }

    public static function getLayoutFile($layout) {
        $layoutfile = APP_ROOT . self::$tempLayoutDir . md5('LAYOUT_' . $layout) . '.tpm.php';
        if (!self::$cacheOpen || !file_exists($layoutfile)) {
            $olayout = APP_ROOT . self::$layoutDir . $layout . '.php';
            $content = file_get_contents($olayout);
            $content = preg_replace('/{{([^}]+)}}/', '<?php echo $1; ?>', $content);
            file_put_contents($layoutfile, $content);
        }

        return $layoutfile;
    }
}
