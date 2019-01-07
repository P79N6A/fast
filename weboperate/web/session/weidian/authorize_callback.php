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

//https://api.vdian.com/oauth2/access_token?appkey=Appkey&secret=SECRET&code= 398f739874f17f0f65e2ed847d02ccd2
//&grant_type=authorization_code


		$app_key= '619423';
		$app_secret = 'f2ec5a14708cf920ef8066913defa4e8';

	
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
$back_url = 'http://operate.yishangonline.com:81/session/weidian/authorize_callback.php';
$url ="https://api.vdian.com/oauth2/access_token?grant_type=authorization_code&appkey=".$app_key
                            ."&secret=".$app_secret
                            ."&code=".$_REQUEST['code'];
                          //  ."&state=".$_REQUEST['state']
							//."&redirect_uri=".$back_url;
echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>';
echo $url."\n";
			$ret = do_execute($url, array());
		//	var_dump($ret);
			$data = json_decode($ret,true);
echo "<br /><br /><br />";
echo "access_token:
";
echo $data['result']['access_token'];
echo "店铺名称:
";
echo $data['result']['shop_name'];
echo "<br /><br /><br />";
echo "如果access_token为空，复制打印出来的access_token后面的值
";

die;
/*
		{
 "status":{"status_code":0,"status_reason":""},
  "result": {
      "access_token":"ACCESS_TOKEN",
      "expires_in":7200,
      "refresh_token":"REFRESH_TOKEN",
      “openid”:“21313123”
      “shop_name”:”店铺名称”
       "scope":"SCOPE"
	}
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
