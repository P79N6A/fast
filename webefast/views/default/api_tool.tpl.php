<?php
header("Content-type:text/html;charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
$api_list = include '../conf/api_conf.php';

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
if ($act == 'getapiHtml') {
	$api = $_REQUEST['api'];
	$api_detail = $api_list[$api];
	
	$str = '<option value="" >--请选择API接口--</option>';
	foreach ($api_detail as $key => $val) {
		if($key != 'catecory'){
			$new_key = $api.'_'.$key;
			$str .= '<option value="'.$new_key.'">'.$key.'</option>';
		}
	}
	
	echo $str;
	exit;
}
if ($act == 'getParamsHtml') {
	$api = $_REQUEST['api'];
	$_api = explode('_', $api);
	
	echo getParamsHtml($api_list[$_api[0]][$_api[1]]);
	exit;
}
function getParamsHtml($api) {
	$str = '';
	foreach ($api['params'] as $name => $param) { 
		$str .= '
					<tr style="height: 30px;" title="'.getFieldValue($param, 'desc').'">
						<td align="right" width="140">'.$name.'：</td>
						<td width="360">
							<span class="l">
							<input type="text" class="apiParam" id="apiParam_'.$name.'" name="'.$name.'" value="'.getFieldValue($param, 'def').'" style="width: 220px;"/>
							</span>
				'.(getFieldValue($param, 'require') ? '<span style="color:red">*</span>' : '').'
						</td>
					</tr>
				';	
	}
	
	return $str;
}
function getFieldValue($arr, $f) {
	if (!isset($arr[$f])) {
		return '';
	}
	
	return $arr[$f];
}
if ($act == 'getSign') {
	$_method = explode('_', $_REQUEST['method']);
	$srt = '';
	
	$data = array();
	$data['format'] = 'json';
	$data['key'] = $_REQUEST['app_key'];
	$data['timestamp'] = date('Y-m-d H:i:s');
	$data['method'] = $_method[1];
	$data['v'] = '2.0';
	$data['sign_method'] = 'md5';
	
	$srt .= '&method='.$data['method'].'&format='.$data['format'].'&key='.$data['key'].'&timestamp='.$data['timestamp'].'&v='.$data['v'].'&sign_method='.$data['sign_method'];
	
	foreach ($_REQUEST as $key => $val) {
		if($key == 'act' || $key == 'app_key' || $key == 'app_secret' || $key == 'method'){
			continue;
		}else{
			if($val != ''){
				$data[$key] = $val;
				$srt .= '&'.$key.'='.$val;
			}
		}
	}
	
	$data['sign'] = createSign($_REQUEST['app_secret'], $data);
	$srt .= '&sign='.$data['sign'];
	
	echo $srt;
    exit;
}
function createSign($app_secret, $paramArr) {
	$sign = $app_secret;

	ksort($paramArr);
	foreach ($paramArr as $k => $v) {
		if ("@" != substr($v, 0, 1)) {
			$sign .= "$k$v";
		}
	}
	unset($k, $v);

	$sign .= $app_secret;

	return strtoupper(md5($sign));
}
if ($act == 'postData') {
	doExecute($_REQUEST['url'], $_REQUEST['post_data']);
}
function doExecute($url, $post_data) {	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 45);
	if (isset($opts['header'])) {
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $opts['header']);
	}
	
	if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
	}

	if (is_array ( $post_data ) && 0 < count ( $post_data )) {
		$postBodyString = "";
		foreach ( $post_data as $k => $v ) {
			$postBodyString .= "$k=" . urlencode ( $v ) . "&";
		}
		unset ( $k, $v );
		if(!empty($postBodyString)){
			$postBodyString = substr ( $postBodyString, 0, -1 );
		}
	} else {
		$postBodyString = $post_data;
	}
	
	curl_setopt ( $ch, CURLOPT_POST, true );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postBodyString );
	
	$reponse = curl_exec ( $ch );
	echo $reponse;
	exit;
}

?>

<div id="nav"></div>
<table
	style="border-collapse: collapse; border-spacing: 0; width: 1000px;">
	<tbody>
		<tr>
			<td valign="top">
				<table border="0" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td>
								<table class="parameters" width="500" border="0" cellpadding="4"
									cellspacing="0">
									<tbody>
										<tr>
											<td width="160" align="right">返回格式：</td>
											<td width="340">
												<select id="format" name="format" style="width: 195px;">
													<option value="json">JSON</option>
												</select>
											</td>
										</tr>
										<tr>
											<td align="right">API类目：</td>
											<td>
												<select name="sip_apicatecory" id="sip_apicatecory" style="width: 195px;">
													<option value="">--请选择API类目--</option>
													<?php foreach($api_list as $api => $name):?>
													<option value="<?php echo $api;?>"><?php echo $name['catecory'];?></option>
													<?php endforeach;?>
												</select>
											</td>
										</tr>
										<tr>
											<td align="right">API名称：</td>
											<td>
												<select name="sip_apiname" id="sip_apiname" style="width: 195px;">
													<option value="">--请选择API接口--</option>
												</select>
											</td>
										</tr>
										<!--<tr>
											<td align="right">数据环境：</td>
											<td><input id="restId" type="radio" name="restId" checked="" value="1">沙箱 
												<input id="restId" type="radio" name="restId" value="2"> 正式</td>
										</tr>-->
										<tr>
											<td align="right">提交方式：</td>
											<td><input type="radio" name="sip_http_method" value="2" checked=""> POST 
												<!--<input type="radio" name="sip_http_method" value="1"> GET-->
											</td>
										</tr>
										<!-- <tr>
											<td align="right">SDK类型：</td>
											<td><input type="radio" name="codeType" value="JAVA" checked=""> JAVA <input type="radio" name="codeType" value="PHP"> PHP <input type="radio" name="codeType" value=".NET"> .NET <input type="radio" name="codeType" value="PYTHON"> PYTHON</td>
										</tr> -->
										<tr>
											<td align="right">app_url：</td>
											<td><input type="text" id="app_url" name="app_url" value="http://openapi.baotayun.com/openapi/webefast/web/?app_act=openapi/router" style="width: 190px;" >&nbsp;</td>
										</tr>
										<tr>
											<td align="right">app_key：</td>
											<td><input type="text" id="app_key" name="app_key" value="" style="width: 190px;" >&nbsp;</td>
										</tr>
										<tr>
											<td align="right">app_secret：</td>
											<td><input type="text" id="app_secret" name="app_secret" value="" style="width: 190px;" ></td>
										</tr>
										<!--<tr id="sessionSapn" style="display: none">
											<td align="right">SessionKey：</td>
											<td><input type="text" id="session" name="session" value="" style="width: 190px;">&nbsp;<a href="javascript:void(0)" onclick="alert('当API的访问级别为‘公开’时，SessionKey不需要填写；\r\n当API的访问级别为‘须用户登录’时，SessionKey必须填写；\r\n当API的访问级别为‘隐私数据须用户登录’时，SessionKey可填可不填；\r\n如何获取SessionKey，请搜索‘用户授权介绍’或点击上面的‘Session获取工具’');">说明</a></td>
										</tr>-->
									</tbody>
								</table></td>
						</tr>
						<tr>
							<td>
								<form id="apiForm" method="post"
									enctype="multipart/form-data">
									<div id="ParamDiv">
										将鼠标移至说明上，查看参数介绍；<font color="red">*</font>
														表示必填，<font color="blue">*</font> 表示几个参数中必填一个；
										<table width="500" border="0" cellpadding="4" cellspacing="0">
											<tbody id="paramsInput">
												
											</tbody>
										</table>
									</div>
								</form>
							</td>
						</tr>
						<tr>
							<td>
								<table width="500" border="0" cellpadding="4" cellspacing="0">
									<tbody>
										<tr>
											<td width="160">&nbsp;</td>
											<td width="340" align="left"><input id="apiTestButton" type="button" value="提交测试" style="width: 80px; height: 30px;  border: #666666 1px solid; cursor: pointer" />
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>

			<td valign="top">
				API请求参数： <br>
				<textarea name="apiParam" id="apiParam" cols="90" rows="8" ></textarea> <br> <br>
				API返回结果： <a href="javascript:jsonFormat()">json格式化</a> <br>
				<textarea name="resultShow" id="resultShow" cols="90" rows="10" ></textarea> <br> <br>
				<!--SDK调用示例代码： <br> <textarea id="sampleCode"
					name="sampleCode" cols="90" rows="8" readonly=""></textarea>-->
			</td>
		</tr>
		<tr></tr>
	</tbody>
</table>
<script src="http://lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js"></script>
<script>
$(function() {
	$('#sip_apicatecory').change(function() {
		$.post('api_tool.php?act=getapiHtml&api='+$(this).val(), {}, function(data) {
			$('#sip_apiname').html(data);
		});
	});
	$('#sip_apiname').change(function() {
		$('#paramsInput').html('');
		$.post('api_tool.php?act=getParamsHtml&api='+$(this).val(), {}, function(data) {
			$('#paramsInput').html(data);
		});
	});
	$('#apiTestButton').click(function() {
		var params = {'app_key':$('#app_key').val(),'app_secret':$('#app_secret').val(),'method':$('#sip_apiname').val()};
		$('.apiParam').each(function() {
			params[$(this).attr('name')] = $(this).val();
		});
		
		$.post('api_tool.php?act=getSign', params, function(data) {
			var paramsShowStr = $('#app_url').val();
			paramsShowStr += data;
			$('#apiParam').val(paramsShowStr);
			
			$('#resultShow').val("请求中...");
			var params = {'url':paramsShowStr,'post_data':data};
			$.post('api_tool.php?act=postData', params, function(reponse) {
				$('#resultShow').val(reponse);
				jsonFormat();
			});
		});
		
	});
});

function jsonFormat() {
	var str = JSON.parse($('#resultShow').val());
	$('#resultShow').val(JSON.stringify(str, null, "\t"));
}
</script>
