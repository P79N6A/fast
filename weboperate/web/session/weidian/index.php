<?php
define('ROOT_PATH', str_replace('jd_session/index.php', '', str_replace('\\', '/', __FILE__)));


if(isset($_REQUEST['app_key'])){

$state = time();
$url = "https://api.vdian.com/oauth2/authorize";

$param['response_type'] = 'code';
$param['redirect_uri'] = 'http://operate.yishangonline.com:81/session/weidian/authorize_callback.php';
$param['state'] = $state;
$param['appkey'] = $_REQUEST['app_key'];
$url = $url . '?' . http_build_query($param);
//save_param($param,$state);
header("location:".$url);	
}

function save_param($param,$state){
	$log = ROOT_PATH.'temp/jd_session_'.$state;
	unlink($log);
	file_put_contents($log ,json_encode($param),LOCK_EX);
}


?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en_US" xml:lang="en_US">
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>店铺授权管理 </title>
  <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
  <script type="text/javascript" src="js/jquery.cookies.2.2.0.min.js"></script>
 </head>
 <body>

	<div>
	<h2>微店API2.0 access_token</h2>
	<div>
		<form action="index.php">

			app_key <input type="text" name="app_key" value="" size="50"/><br />
			<input type="submit" name="" value="获取微店API2.0 access_token"/><br />
		</form>
	</div>

	<h3>
	1、输入微店后台取得的app_key <br/>
	2、输入客户微店商家后台的账号和密码 <br/>
	3、在出现的页面中点“授权” <br/>
	4、页面上面显示的“授权码”请COPY下来，这个“授权码”就是微店API2.0 access_token <br/>
	</h3>
	</div>


<br/>
<br/><!--h3>京东官网获取值：<a href="http://api.taobao.com/apitools/sessionPage.htm">京东官网获取session</a></h3-->
 </body>
</html>



