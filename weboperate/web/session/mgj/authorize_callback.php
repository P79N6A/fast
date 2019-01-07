<?php
define('IN_EB', true);
date_default_timezone_set('PRC');
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
define('ROOT_PATH', str_replace('jd_session/authorize_callback.php', '', str_replace('\\', '/', __FILE__)));
@ini_set('memory_limit','512M');
@ini_set ('allow_call_time_pass_reference', 1);
@set_time_limit(0);


if(isset($_REQUEST['error'])){
	echo '取消授权！';die;
}
if(!isset($_REQUEST['state'])){
	echo '返回值为空';die;
}

$state = $_REQUEST['state'];
$code = $_REQUEST['code'];


		$app_key= '440670a3c818ee2c510d02079e7ac196';
		$app_secret = '37bb7776960f3bbbcf7a1cd9b91a7573';

	
/*

			$url = "https://auth.360buy.com/oauth/token";
			$params = array(
				'code'=>$_REQUEST['code'],
				'scope'=>'read',
				'grant_type'=>'authorization_code',
				'state'=>$_REQUEST['state'],
				'client_id'=>$client_id,
				'client_secret'=>$client_secret,
				'redirect_uri'=>$url,
				);
*/
$back_url = 'http://operate.yishangonline.com:81/session/mgj/authorize_callback.php';
$url ="http://www.mogujie.com/openapi/api_v1_accesstoken/index?grant_type=authorization_code&app_key=".$app_key
                            ."&app_secret=".$app_secret
                            ."&code=".$_REQUEST['code']
                            ."&state=".$_REQUEST['state']
							."&scope=read&redirect_uri=".$back_url;
echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>';
echo $url."\n";
			$ret = do_execute($url, array());
			//var_dump($ret);
			$data = json_decode($ret,true);
echo "<br /><br /><br />";
echo "access_token:
";
echo $data['result']['access_token'];
echo "授权用户:
";
echo $data['result']['uname'];
echo "<br /><br /><br />";
echo "如果access_token为空，复制打印出来的access_token后面的值
";

die;
			/*
			{ "error": "invalid_client", "error_description": "authorize code L3wK9GNBeUfz7Ss6lBJdEPlI212445 invalidate,please authorize again." }
			{
			"taobao_user_nick": "shopping_attb",
			"re_expires_in": 18855048,
			"sub_taobao_user_id": "1089536156",
			"expires_in": 18855048,
			"r1_expires_in": 18855048,
			"taobao_user_id": "58123788",
			"w2_expires_in": 18855048,
			"sub_taobao_user_nick": "shopping_attb:yf",
			"w1_expires_in": 18855048,
			"token_type": "Bearer",
			"r2_expires_in": 18855048,
			"refresh_token": "62009042e8fegia64a7700b63c880bf8e23abc7ad92cba21089536156",
			"access_token": "62018040e22bdff0b144ab0b8d63c9f464950bd90e1a8741089536156"
			}
			*/



function do_execute($url, $params) {
		//echo $url . '?' . http_build_query($param);die();
		//echo $url;
	       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$param);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,0);
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
				if($result === false){
					$err = curl_error($ch)."[{$url}]";
					//var_dump($err);
					return $err;
				}
        curl_close($ch);
        return $result;
}

function get_param($state){
	$path = ROOT_PATH.'temp/jd_session_'.$state;
	return file_get_contents($path);
}
