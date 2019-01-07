<?php
    return array(
        'jingdong'=>array(
            'type'=>array('val'=>'sop','desc'=>'','type'=>'radio','data'=>array('fbp','sop','lbp','sopl')),
            'customerCode'=>array('val'=>'','show'=>'1','desc'=>'<a target="_blank" href="assets/images/jd_help.png">customerCode为商家在京东青龙系统里的客户编码</a>'),
           // 'is_check_shipping_area'=>array('val'=>'','desc'=>'customer_code为商家在京东青龙系统里的客户编码，详情'),
           	'app_key'=>array('val'=>'','name'=>'授权：app_key','desc'=>'','show'=>'1',),
    				'app_secret'=>array('val'=>'','name'=>'授权：app_secret','desc'=>'','show'=>'1', ),
    			    'refresh_token'=>array('val'=>'','name'=>'refresh_token','desc'=>'','show'=>'1',),
    				'session'=>array('val'=>'','name'=>'授权：session','desc'=>'','show'=>'1',),
    				'nick'=>array('val'=>'','name'=>'授权：nick','desc'=>' *'),
            'wareHouseCode'=>array('val'=>'','name'=>'授权：wareHouseCode','desc'=>'<span>京东发货仓，使用京东快递配送请输入</span>','show'=>'1'),
 

        ),
    	'taobao'=>array(
    				'app_key'=>array('val'=>'','desc'=>'','show'=>'1','show'=>'1'),
    				'app_secret'=>array('val'=>'','desc'=>'','show'=>'1','show'=>'1' ),
    			    'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1','show'=>'1'),
    				'session'=>array('val'=>'','desc'=>'','show'=>'1','show'=>'1'),
    				'nick'=>array('val'=>'','desc'=>' *'),
    				'shop_type'=>array('val'=>'','desc'=>''),
    		),
    	'yihaodian'=>array(
    				//'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    				//'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
    				'session'=>array('val'=>'','desc'=>'','show'=>'1'),
                                'refreshToken'=>array('val'=>'','desc'=>'','show'=>'1'),
    				'is_general'=>array('val'=>'','desc'=>''),
                                'nick'=>array('val'=>'','desc'=>''),
    		),
    	'chuchujie'=>array(
    				'Appnick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
    				'Appkey'=>array('val'=>'','name'=>'授权：Appkey','desc'=>'请按照授权说明填写','show'=>'1'),
    				'AppSecret'=>array('val'=>'','name'=>'授权：AppSecret','desc'=>'请按照授权说明填写','show'=>'1'),
    				'org-name'=>array('val'=>'bais9XOX','name'=>'Org-Name','desc'=>'','disabled'=>'1'),
    				'authorize_date'=>array('val'=>'2030-07-01 00:00:00','name'=>'授权截止时间','desc'=>'','disabled'=>'1'),
    				
    	),
    	'mogujie'=>array(
                            'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                            'access_token'=>array('val'=>'','desc' => ''),
                            'refresh_token'=>array('val'=>'','desc'=>''),
    	 ),
    	'jumei'=>array(
    				'app_key'=>array('val'=>'','desc'=>'商家系统发货id','show'=>'1'),
    				'app_secret'=>array('val'=>'','desc'=>'商家键值key','show'=>'1'),
    				'session'=>array('val'=>'','desc'=>'接口签名sign','show'=>'1'),
    				'nick'=>array('val'=>'','desc'=>''),
    	),
    	'zhe800'=>array(
    				'shop_nick'=>array('val'=>'','desc'=>''),
    				//'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    				'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			    
    	),
        'weipinhui' => array(
            'type' => array('val' => '直发', 'desc' => '请选择对接模式', 'type' => 'radio', 'data' => array('直发', 'JIT')),
            'shop_nick' => array('val' => '', 'desc' => '请填写平台实际的店铺名称'),
            //'app_key'=>array('val'=>'','desc'=>'请填写供应商ID','show'=>'1'),
            //'app_serect'=>array('val'=>'','desc'=>'','show'=>'1'),
            'vendor_id' => array('val' => '', 'desc' => '', 'show' => '1'),
            'accessToken' => array('val' => '', 'desc' => '', 'show' => '1'),
            'cooperation_no' => array('val' => '', 'desc' => '请填写常态合作编码，多个编码请用逗号分开',),
            'co_mode' => array('val' => '分销JIT', 'desc' => '请选择模式,库存同步默认同步比例100%,维护0%不同步', 'type' => 'radio', 'data' => array('普通JIT', '分销JIT')),
        ),
    	'chuanyi'=> array(
    				'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
	    			'merchantId'=>array('val'=>'','name'=>'商家ID','desc'=>'请联系穿衣助手人员获取','show'=>'0'),
	    			'secret_key'=>array('val'=>'','name'=>'授权密钥','desc'=>'请联系穿衣助手人员获取','show'=>'0'),
    				'authorize_date'=>array('val'=>'2030-07-01 00:00:00','name'=>'授权截止时间','desc'=>'','disabled'=>'1'),
    	),
    	'meilishuo' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
    			'access_token'=>array('val'=>'','desc' => ''),
                'refresh_token'=>array('val'=>'','desc'=>''),
    	),
    	'alibaba' => array(
    			'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'memberId'=>array('val'=>'','desc'=>'','show'=>'1'),
    			
    			),
        'youzan' => array(
            'AppId'=>array('val'=>'','desc'=>'','show'=>'1'),
            'AppSecret'=>array('val'=>'','desc'=>'','show'=>'1'),
            'access_token' => array('val' => '', 'desc' => '授权获取到的access_token', 'show' => '1'),
            'refresh_token' => array('val' => '', 'desc' => '授权获取到的refresh_token', 'show' => '1'),
            'shop_nick' => array('val' => '', 'desc' => ''),
        ),
    	'yamaxun' => array(
    			'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
    			'AWSAccessKeyId'=>array('val'=>'','name'=>'AWS访问键编号','desc'=>'','show'=>'1'),
    			'AWSAccessKeyValue'=>array('val'=>'','name'=>'密钥','desc'=>'','show'=>'1'),
    			'SellerId'=>array('val'=>'','name'=>'卖家编号','desc'=>'注册授权平台分配的卖家编号','show'=>'1'),
    			'MarketplaceId_1'=>array('val'=>'','name'=>'商城编号','desc'=>'注册授权平台分配的商城编号','show'=>'1'),
    			'MWSAuthToken'=>array('val'=>'','name'=>'MWS授权令牌','desc'=>'注册授权平台分配的授权令牌','show'=>'1'),
    			'authorize_date'=>array('val'=>'2030-07-01 00:00:00','name'=>'授权截止时间','desc'=>'','disabled'=>'1'),
                'Country'=>array('val' => '中国站', 'name'=>'对接国家', 'desc' => '请选择对接国家', 'type' => 'radio', 'data' => array('中国站', '美国站')),
    			),
		'dangdang' => array(
				'shop_nick'=>array('val'=>'','desc'=>''),
				'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
				'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
				'app_session'=>array('val'=>'','desc'=>'','show'=>'1'),
				'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
				),   
    	'aliexpress' => array(
    			'shop_nick'=>array('val'=>'','desc'=>''),
    			'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			//'session'=>array('val'=>'','desc'=>'','show'=>'1'),
    			),
    	'beibei' => array(
    			'shop_nick'=>array('val'=>'','desc'=>''),
    		//	'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    		//	'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'app_session'=>array('val'=>'','desc'=>'','show'=>'1'),
    			),
    	'baidumall' => array(
    			'shop_nick'=>array('val'=>'','desc'=>''),
    			'username'=>array('val'=>'','desc'=>'登录百度MALL的用户名称','show'=>'1'),
    			'password'=>array('val'=>'','desc'=>'登录百度MALL的用户密码','show'=>'1'),
    			'token'=>array('val'=>'','desc'=>'百度MALL分配给商家的用于标识其系统的ID','show'=>'1'),
    	),
    	'zouxiu' => array(
    			'shop_nick'=>array('val'=>'','desc'=>'店铺昵称'),
    			'uid'=>array('val'=>'','desc'=>'供应商ID(走秀提供)'),
    			'username'=>array('val'=>'','desc'=>'用户名(走秀提供)','show'=>'1'),
    			'password'=>array('val'=>'','desc'=>'密码(走秀提供)','show'=>'1'),
    			'token'=>array('val'=>'','desc'=>'授权获取到的token','show'=>'1'),
    	),
    	'yougou' => array(
    			'shop_nick'=>array('val'=>'','desc'=>'店铺昵称'),
    			'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
    			),
    	'suning' => array(
    			'shop_nick'=>array('val'=>'','desc'=>'店铺昵称'),
    			'appKey'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'appSecret'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
    			),
    	'miya' => array(
    			'shop_nick'=>array('val'=>'','desc'=>'店铺昵称'),
    			'vendor_key'=>array('val'=>'','desc'=>'商家的ID','show'=>'1'),
    			'secret_key'=>array('val'=>'','desc'=>'秘钥','show'=>'1'),
    			),
    	'weimob'=> array(
    			'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
    			//'AppId'=>array('val'=>'','desc'=>'开发者凭据AppId','show'=>'1'),
    			//'AppSecret'=>array('val'=>'','desc'=>'开发者凭据AppSecret','show'=>'1'),
    			'access_token'=>array('val'=>'','desc'=>'授权获取到的access_token','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'授权获取到的refresh_token','show'=>'1'),
    			),
    	'vdian'=> array(
    			'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
    			'access_token'=>array('val'=>'','desc'=>'授权获取到的access_token','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'授权获取到的refresh_token','show'=>'1'),
    		),
    	'gonghang' => array(
    			'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
    			'app_key'=>array('val'=>'','desc'=>'会员管理-应用提交获取的应用key','show'=>'1'),
    			'app_secret'=>array('val'=>'','desc'=>'会员管理-应用提交获取的应用secret','show'=>'1'),
    			'auth_code'=>array('val'=>'','desc'=>'会员服务--应用授权获取的auth_code','show'=>'1'),
    			),
        'yintai' => array(
    			'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
    			'ClientId'=>array('val'=>'','desc'=>'客户ID','show'=>'1'),
    			'ClientName'=>array('val'=>'','desc'=>'客户名称','show'=>'1'),
    			'SecrectKey'=>array('val'=>'','desc'=>'','show'=>'1'),
    			),
        'kaola' => array(
            'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
           // 'appKey'=>array('val'=>'','desc'=>'客户appKey','show'=>'1'),
            //'appSecret'=>array('val'=>'','desc'=>'客户appSecret','show'=>'1'),
            'access_token'=>array('val'=>'','desc'=>'授权令牌','show'=>'1'),
            
        ),
        'huayang' => array(
            'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
            'appId'=>array('val'=>'','desc'=>'应用ID','show'=>'1'),
            'appSecret'=>array('val'=>'','desc'=>'appSecret','show'=>'1'),
            //'accessToken'=>array('val'=>'','desc'=>'','show'=>'1','disabled'=>'0'),
            ),
        'shangpin' => array(
            'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
            'app_key'=>array('val'=>'','desc'=>'app_key','show'=>'1'),
            'app_secret'=>array('val'=>'','desc'=>'app_secret','show'=>'1'),
            ),
        'juanpi' => array(
            'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
//            'app_key'=>array('val'=>'','desc'=>'ERP唯一标识','show'=>'1'),
//            'app_secret'=>array('val'=>'','desc'=>'ERP加密密钥','show'=>'1'),
            'jCusKey'=>array('val'=>'','desc'=>'ERP用户KEY','show'=>'1','desc'=>'<a target="_blank" href="assets/images/juanpi_help.png">商家key，由客户在卷皮后台获取</a>'),
            ),
        'okbuy' => array(
            'shop_nick' =>array('val'=>'','desc'=>'店铺昵称'),
            'PID'=>array('val'=>'','desc'=>'ERP唯一标识','show'=>'1'),
            'KEY'=>array('val'=>'','desc'=>'ERP用户KEY','show'=>'1'),
            ),
        'fenxiao'=>array(
                'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
                'app_secret'=>array('val'=>'','desc'=>'','show'=>'1' ),
                'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
                'session'=>array('val'=>'','desc'=>'','show'=>'1'),
                'nick'=>array('val'=>'','desc'=>''),
                'shop_type'=>array('val'=>'','desc'=>''),
    		),
        'renrendian'=>array(
//                'app_id'=>array('val'=>'','desc'=>'','show'=>'1'),
//                'secret'=>array('val'=>'','desc'=>'','show'=>'1' ),
                'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
                'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
                'shop_nick'=>array('val'=>'','desc'=>''),
                'type'=>array('val' => '标准版', 'name'=>'对接版本', 'desc' => '请选择对接版本', 'type' => 'radio', 'data' => array('标准版', '厂家版')),
    		),
        'mxyc' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'app_key'=>array('val'=>'','desc'=>'','show'=>'1'),
                'app_secret'=>array('val'=>'','desc'=>'','show'=>'1'),
        
            ),
        'xiaomi' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'pid'=>array('val'=>'','desc'=>'','show'=>'1'),
                'key'=>array('val'=>'','desc'=>'','show'=>'1'),
                'systemId'=>array('val'=>'','desc'=>'第三方系统代号','show'=>'1'),
        
            ),
        'feiniu' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'access_token'=>array('val'=>'','desc'=>'授权获取到的access_token','show'=>'1'),
    			'refresh_token'=>array('val'=>'','desc'=>'授权获取到的refresh_token','show'=>'1'),
        
            ),
        'xiaohongshu' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'app-key'=>array('val'=>'','desc'=>'','show'=>'1', 'desc' => '<a target="_blank" href="assets/images/xiaohongshu_help.png">app-key和app-secret在小红书ARK系统中获取</a>'),
                'app-secret'=>array('val'=>'','desc'=>'','show'=>'1'),
            ),
        'pinduoduo' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                //'uCode'=>array('val'=>'','desc'=>'','show'=>'1', 'desc' => '<a target="_blank" href="assets/images/pinduoduo_help.png">uCode和secret在拼多多系统中获取</a>'),
                //'secret'=>array('val'=>'','desc'=>'','show'=>'1'),
                'access_token'=>array('val'=>'','desc'=>'','show'=>'1'),
                'refresh_token'=>array('val'=>'','desc'=>'','show'=>'1'),
            ),
        'biyao' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'client_id'=>array('val'=>'','desc'=>'','show'=>'1',),
                'password'=>array('val'=>'','desc'=>'','show'=>'1'),
            ),
        'xiaomizhijia' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'partner_id'=>array('val'=>'','desc'=>'','show'=>'1', 'desc'=>'小米智能家庭分配的第三方ID[小米提供]',),
                'key'=>array('val'=>'','desc'=>'','show'=>'1','desc'=>'签名密钥[小米提供]'),
                'aes_key'=>array('val'=>'','desc'=>'','show'=>'1', 'desc'=>'AES加密密钥[小米提供]'),
                'userId'=>array('val'=>'','desc'=>'','show'=>'1', 'desc'=>''),
            ),
        'ofashion' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'app_key'=>array('val'=>'','desc'=>'','show'=>'1','desc'=>''),
                'app_secret'=>array('val'=>'','desc'=>'','show'=>'1', 'desc'=>''),
            ),
        'dajiashequ' => array(
                'shop_nick'=>array('val'=>'','name'=>'平台店铺名称','desc'=>'请填写平台实际的店铺名称'),
                'username' => array('val'=>'','show'=>'0','desc'=>'大家社区社区登录账号'),
                'password' => array('val'=>'','show'=>'1','desc'=>'大家社区社区登录密码', 'type'=> 'password'),
                'companyID'=>array('val'=>'','show'=>'0','desc'=>'大家社区社区ID'),
                'shopID'=>array('val'=>'','show'=>'0','desc'=>'店铺ID'),
                'openID' => array('val'=>'','show'=>'1','desc'=>''),
                'access_token' => array('val'=>'','show'=>'1','desc'=>''),
                'refresh_token' => array('val'=>'','show'=>'1','desc'=>''),
            ),
        'yoho' => array(
            'shop_nick' => array('val' => '', 'name' => '平台店铺名称', 'desc' => '店铺昵称'),
            'app_key' => array('val' => '', 'show' => '1', 'desc' => '应用key，详询有货技术人员'),
            'app_secret' => array('val' => '', 'show' => '1', 'desc' => '应用secret，详询有货技术人员'),
            'brand_id' => array('val' => '', 'name' => '品牌ID', 'desc' => '多品牌ID用逗号分开,若多品牌要分开发货,请配置多个店铺'),
        ),
        'siku' => array(
            'shop_nick' => array('val' => '', 'name' => '平台店铺名称', 'desc' => '店铺昵称'),
            'vendorCode' => array('val' => '', 'show' => '1', 'desc' => '商家编码，寺库网统一分配的编号'),
            'secret' => array('val' => '', 'show' => '1', 'desc' => '接口secret，由寺库网提供'),
        ),
        'weifenxiao' => array(
            'shop_nick' => array('val' => '', 'name' => '平台店铺名称', 'desc' => '店铺昵称'),
            'app_key' => array('val' => '', 'show' => '1', 'desc' => ''),
            'app_secret' => array('val' => '', 'show' => '1', 'desc' => ''),
        ),
        'chuizhicai' => array(
            'shop_nick' => array('val' => '', 'name' => '平台店铺名称', 'desc' => '店铺昵称'),
            'AppKey' => array('val' => '', 'show' => '1', 'desc' => ''),
            'AppSecret' => array('val' => '', 'show' => '1', 'desc' => ''),
            'access_token' => array('val' => '', 'show' => '1', 'desc' => ''),
            'refresh_token' => array('val' => '', 'show' => '1', 'desc' => '')
        ),
        'ebay' => array(
            'shop_nick' => array('val' => '', 'name' => '平台店铺名称', 'desc' => '店铺昵称'),
            'AppID' => array('val' => '', 'show' => '1', 'desc' => ''),
            'DevID' => array('val' => '', 'show' => '1', 'desc' => ''),
            'CertID' => array('val' => '', 'show' => '1', 'desc' => ''),
            'RuName' => array('val' => '', 'show' => '1', 'desc' => '')
        ),

        'shangpai' => array(
            'shop_nick' => array('val' => '', 'desc' => '店铺昵称'),
            'token' => array('val' => '', 'desc' => 'token', 'show' => '1'),
        ),

		'sappho' => array(
			'api_name' => array('val' => '', 'desc' => '接口名称'),
			'api_key' => array('val' => '', 'desc' => 'key', 'show' => '1'),
			'api_token' => array('val' => '', 'desc' => 'token', 'show' => '1'),
		),

        'akucun' => array(
            'shop_nick' => array('val' => '', 'desc' => '店铺昵称'),
        	'appid' => array('val' => '', 'desc' => 'appid', 'show' => '1'),
            'appkey' => array('val' => '', 'desc' => 'appkey', 'show' => '1'),
        ),
        'davdian' => array(
            'shop_nick' => array('val' => '', 'desc' => '店铺昵称'),
            'partner' => array('val' => '', 'desc' => '商家编码', 'show' => '1'),
            'key' => array('val' => '', 'desc' => 'key', 'show' => '1'),
        ),
    );
?>

