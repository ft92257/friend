<?php

class SynUserAction
{

	public function run()
	{
		$debug = [];
		$realDo = true;
		$result = \CompanyMember::m()->getOaUsers();
		if (empty($result['userList'])) {
			die('没有OA用户！');
		}

		$oaUsers = Func::setArrayKey($result['userList'], 'user_id');//获取OA数据
		$oaUids = array_keys($oaUsers);

		$debug['oaUids'] = $oaUids;//debug

		$leaveUsers = Func::setArrayKey($result['invalidUserList'], 'user_id');;//离职人员
		$leaveOaUids = array_keys($leaveUsers);//离职人员oa_uid

		$debug['leaveOaUids'] = $leaveOaUids;//debug

		//更新黑名单数据
		\Company::m()->updateData(['black_users' => join(',', $leaveOaUids)], ['id' => 1]);

		$company = \Company::m()->getById(1);
		$users = \User::m()->getAll([
			'where' => ['cid' => $company['id']],
		]);
		$undo = [];
		$whiteUsers =  $company['white_users'] ? explode(',', $company['white_users']) : [];//获取白名单
		$debug['whiteUsers'] = $whiteUsers;//debug
		foreach ($users as $user) {
			if (!in_array($user['oa_uid'], $whiteUsers) && !in_array($user['oa_uid'], $oaUids)) {
				//离职用户处理
				if (in_array($user['oa_uid'], $leaveOaUids)) {
					if ($realDo) {
						\CompanyMember::m()->deleteMember($user['uid'], true);
					}
					$debug['leaveUsersDelete'][] = $user['oa_uid'];//debug
				} else {
					if ($realDo) {
						\CompanyMember::m()->deleteMember($user['uid']);
					}
					$debug['unLeaveUsersDelete'][] = $user['oa_uid'];//debug
				}
			} else {
				$undo[] = $user['oa_uid'];
				//外部人员特殊处理
				if ($user['oa_uid'] == 588) {
					continue;
				}

				//同步其他数据
				if ($realDo && isset($oaUsers[$user['oa_uid']])) {
					$data = [
						'mobile' => $oaUsers[$user['oa_uid']]['phone'],
						'realname' => $oaUsers[$user['oa_uid']]['user_name'],
						'position' => $oaUsers[$user['oa_uid']]['out_position'] ? $oaUsers[$user['oa_uid']]['out_position'] : $oaUsers[$user['oa_uid']]['position'],
					];
					if ($user['mobile'] == $data['mobile']) {
						if ($user['position'] != $data['position'] || $user['realname'] != $data['realname']) {
							\User::m()->updateData([
								'position' => $data['position'],
								'realname' => $data['realname'],
							], ['uid' => $user['uid']]);
						}
					} else {
						//异常：如果OA里有重复手机号则会导致异常来回覆盖
						if (\User::m()->isExists(['mobile' => $data['mobile']])) {
							//已存在则将存在的账号设置为旧手机号
							\User::m()->updateData([
								'mobile'   => $user['mobile'],
							], ['mobile' => $data['mobile']]);
						}

						//当前用户设置为新号
						\User::m()->updateData([
							'mobile'   => $data['mobile'],
							'position' => $data['position'],
							'realname' => $data['realname'],
						], ['uid' => $user['uid']]);
					}
				}
				$debug['unDoUsers'][] = $user['oa_uid'];//debug
			}
		}
		$newUsers = array_diff($oaUids, $undo);
		foreach ($newUsers as $oaUid) {
			//新增处理
			$data = [
				'oa_uid' => $oaUid,
				'mobile' => $oaUsers[$oaUid]['phone'],
				'realname' => $oaUsers[$oaUid]['user_name'],
				'position' => $oaUsers[$oaUid]['out_position'] ? $oaUsers[$oaUid]['out_position'] : $oaUsers[$oaUid]['position'],
			];
			if ($realDo) {
				\CompanyMember::m()->addMember($company, $data);
			}
			$debug['newUsers'][] = $oaUid;//debug
		}

		foreach ($debug as &$value) {
			$value = array_unique($value);
		}
		var_export($debug);
	}

}
?>