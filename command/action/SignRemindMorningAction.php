<?php

class SignRemindMorningAction
{

	public function run()
	{
		//排除节假日
		if (date('w') == 0 || date('w') == 6) {
			return;
		}
		$holidays = Cf::config('holiday', []);
		if (in_array(date('Y-m-d'), $holidays)) {
			return;
		}

		//已签到的
		$data = Attendance::m()->getAll([
			'fields' => 'DISTINCT uid',
			'where' => [
				'att_date' => mktime(0,0,0),
				'status' => 0,
				'type_status' => 1,
			],
		]);
		$signed = Func::pickArrayField($data, 'uid');

		//需要提醒的：两星期内有签到的用户
		$data = Attendance::m()->getAll([
			'fields' => 'DISTINCT uid',
			'where' => [
				'att_date >' => mktime(0,0,0) - 1209600,
				'status' => 0,
				'type_status' => 1,
				'uid not in' => [1409,1413,1425,1421,1417],
			],
		]);
		$needSign = Func::pickArrayField($data, 'uid');

		//用户信息
		$users = User::m()->getAll([
			'fields' => 'uid,mobile',
			'where' => [
				'cid' => 1,
				'uid in' => $needSign,
			],
		]);

		$unsigned = [];
		foreach ($users as $user) {
			if (!in_array($user['uid'], $signed)) {
				$info = '公司今天未收到您的上班打卡记录，要赶紧签到哦。（如果您正在休假，抱歉请忽略此消息）';
				MobileVerify::m()->sendMessage($user['mobile'], $info);
				$unsigned[] = $user['uid'];
			}
		}

		print_r($unsigned);
	}

}
?>