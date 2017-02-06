<?php
//配置 文 件
return [
		'DB_CONFIG' => 'mysqli://root:@127.0.0.1:3306/meet',

        'REDIS_OPEN' => false,
        'REDIS_HOST' => '127.0.0.1',
        'REDIS_PORT' => '6379',
        'REDIS_AUTH' => '',
        'REDIS_DB' => 1,

		'upload'       => [
            'image' => [
                'allowExts' => ['jpg', 'gif', 'png', 'jpeg'],
                'maxSize'   => 3145728,
                'savePath'  => 'images/',
            ],
            'video' => array(
                'allowExts' => array('avi', 'mp4'),
                'maxSize'   => 3145728,
                'savePath'  => 'video/',
            ),
            'file' => array(
                'allowExts' => array('apk', 'zip', 'txt'),
                'maxSize'   => 31457280,
                'savePath'  => 'file/',
            ),
        ],
		
        'QINIU_BUCKET' => 'meet',
        'QINIU_KEY'    => 'St2X83Fk1zBnRcWv1F8WhJogUpi90eo7jc5qXaWe',
        'QINIU_SECRET' => 'GtM9AMbUX5BBrzhYBtkFoH2UzP8Zce8wM0mS3mIm',
        'QINIU_DOMAIN' => 'ohlpsk680.bkt.clouddn.com',

        'JPUSH_KEY' => '1f3de895a6b8f04a2989be76',
        'JPUSH_SECRET' => '9d87cfc1e30b686fdbbea04d',
        'JPUSH_PRODUCTION' => false,
		
        'statics' => '/assets/',
        'template_cache' => false,

];

?>