<?php

class BaseView {
    protected static $viewDir = APP_ROOT . '/view/';
    protected static $layoutDir = APP_ROOT . '/view/layouts/';
    protected static $tempDir = APP_ROOT . '/temp/view/';
    protected static $tempLayoutDir = APP_ROOT . '/temp/layouts/';
    public static $cacheOpen = false;

    public static function display($view, $data, $layout) {
        extract($data);
        $content_file = self::getViewFile($view);
        $layoutfile = self::getLayoutFile($layout);

        include($layoutfile);
    }

    public static function getViewFile($view) {
        $content_file = Cf::getRootPath() . self::$tempDir . md5($view) . '.tpm.php';
        if (!self::$cacheOpen || !file_exists($content_file)) {
            $view_file = Cf::getRootPath() . self::$viewDir . $view . '.php';
            $content = file_get_contents($view_file);
            $content = preg_replace('/{{([^}]+)}}/', '<?php echo $1; ?>', $content);
            file_put_contents($content_file, $content);
        }

        return $content_file;
    }

    public static function getLayoutFile($layout) {
        $layoutfile = Cf::getRootPath() . self::$tempLayoutDir . md5('LAYOUT_' . $layout) . '.tpm.php';
        if (!self::$cacheOpen || !file_exists($layoutfile)) {
            $olayout = Cf::getRootPath() . self::$layoutDir . $layout . '.php';
            $content = file_get_contents($olayout);
            $content = preg_replace('/{{([^}]+)}}/', '<?php echo $1; ?>', $content);
            file_put_contents($layoutfile, $content);
        }

        return $layoutfile;
    }
}
