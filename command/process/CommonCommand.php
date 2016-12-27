<?php

class CommonCommand extends BaseCommand
{

	public function run()
	{
		if (isset($this->_args[2])) {
			$this->runAction($this->_args[2]);
			exit;
		}

		$configs = include(ROOT . '/command/action_config.php');
		foreach ($configs as $key => $cfg) {
			if ($this->checkRun($cfg)) {
				system('php '.ROOT.'/command/action/ActionBaseCommand.php Common ' . $key);
			}
		}
	}

	protected function runAction($action)
	{
		$class = $action . 'Action';
		if (!class_exists($class)) {
			include(ROOT . '/command/action/' . $class . '.php');
		}
		$obj = new $class();
		$obj->run();
	}

	protected function checkRun($cfg)
	{
		$cfgs = explode(' ', $cfg);

		return $this->checkMonth($cfgs[3]) && $this->checkDay($cfgs[2]) && $this->checkHour($cfgs[1]) && $this->checkMinute($cfgs[0]);
	}

	protected function checkMonth($str)
	{
		if ($str == '*') {
			return true;
		} elseif (is_numeric($str)) {
			return $str == date('n');
		} else {
			return false;
		}
	}

	protected function checkDay($str)
	{
		if ($str == '*') {
			return true;
		} elseif (strpos($str, '/') !== false) {
			$arr = explode('/', $str);
			return floor(time() / 86400) % $arr[1] == 1;
		} elseif (is_numeric($str)) {
			return $str == date('j');
		} else {
			return false;
		}
	}

	protected function checkHour($str)
	{
		if ($str == '*') {
			return true;
		} elseif (strpos($str, '/') !== false) {
			$arr = explode('/', $str);
			return floor(time() / 3600) % $arr[1] == 1;
		} elseif (is_numeric($str)) {
			return $str == date('G');
		} else {
			return false;
		}
	}

	protected function checkMinute($str)
	{
		if ($str == '*') {
			return true;
		} elseif (strpos($str, '/') !== false) {
			$arr = explode('/', $str);
			return floor(time() / 60) % $arr[1] == 1;
		} elseif (is_numeric($str)) {
			return $str == intval(date('i'));
		} else {
			return false;
		}
	}
}
?>