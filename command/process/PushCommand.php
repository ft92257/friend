<?php

class PushCommand extends BaseCommand
{

	public function run()
	{
		for ($i=0;$i<7200;$i++) {
			$params = Func::listPop('push_list');
			if ($params) {
				\JgPush::send($params['data']['title'], $params['data'], $params['users'], $params['msgType']);
			} else {
				sleep(1);
			}
		}
	}

}
?>