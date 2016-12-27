<?php

class BuyGoodsAction
{

	public function run()
	{
		\BuyGoods::m()->updateData(['status' => 1], [
			'status' => 0,
			'expire_date <' => date('Y-m-d H:i:s'),
			'expire_date >' => 0,
			'cancel_reason' => '过期取消',
		]);
	}

}
?>