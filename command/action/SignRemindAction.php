<?php

class SignRemindAction
{

	public function run()
	{
		$data = Attendance::m()->getAll([
			'where' => [
				'att_date' => mktime(0,0,0),
				'status' => 0,
				'type_status in' => [1,2],
			],
			'order' => 'created_at DESC',
		]);

		$users = [];
		foreach ($data as $value) {
			if (!isset($users[$value['uid']])) {
				if ($value['type_status'] != 2) {
					//没有签退 短信提醒
					$mobile = User::m()->getField(['uid' => $value['uid']], 'mobile');
					$info = '公司今天未收到您的下班打卡记录，感谢您的辛苦工作，工作结束后记得签退哦';
					MobileVerify::m()->sendMessage($mobile, $info);

					$users[$value['uid']] = 1;
				} else {
					$users[$value['uid']] = 0;
				}
			}
		}

		print_r($users);
	}

}
?>