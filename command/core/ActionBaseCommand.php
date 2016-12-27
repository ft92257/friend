<?php

abstract class BaseCommand
{
    protected $args;

    /**
     * @var bool 是否开启单进程模式
     */
    protected $singleProcess = false;

    public function __construct(array $args){
        $this->_args = $args;
        $baseName = basename(__FILE__);

        if ($this->singleProcess && intval(exec("ps --no-headers -eo args | grep -v grep | grep -E \"$baseName +$args[1]( |$)\" | wc -l")) > 1) {
            exit("相同的进程已经存在，本次运行退出");
        }
    }

    abstract public function run();

    public function success($msg = '操作成功!', $data = array())
    {
        die($msg);
    }

    public function error($msg = '操作失败!', $status = 55000, $data = array())
    {
        die($msg);
    }

}

if (!isset($GLOBALS['argv'][1])) {
    die('请传入参数！');
}

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

$commandName = $GLOBALS['argv'][1] . 'Command';//获取参数
define('ROOT', dirname(dirname(__FILE__)));
require ROOT . '/app/config/Cf.php';
require ROOT . '/app/core/CoreBase.php';
spl_autoload_register(['Func', 'autoload']);
include ROOT . '/vendor/autoload.php';

include(ROOT . '/app/command/' . $commandName . '.php');
$app = new $commandName($GLOBALS['argv']);
$app->run();