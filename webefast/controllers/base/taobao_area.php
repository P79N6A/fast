<?php
set_time_limit(0);
require_lib('util/web_util', true);
require_lib('util/taobao_util', true);
class taobao_area {
    function do_list(array &$request, array &$response, array &$app) {

    }
    function convert(array &$request, array &$response, array &$app){
    	//type = 1
    	$area = load_model('base/TaobaoAreaModel')->convert();
    }
    //淘宝区域下载
    function download_taobao(array &$request, array &$response, array &$app){
    	session_start();
    	$app_key = CTX()->get_app_conf('app_key');
    	$app_secret = CTX()->get_app_conf('app_secret');
    	$app_session = CTX()->get_app_conf('app_session');
    	$app_nick = CTX()->get_app_conf('app_nick');
    	
    	$taobao_util = new taobao_util($app_key, $app_secret, $app_session, $app_nick);
    	
    	$area = load_model('base/TaobaoAreaModel')->get_taobao_areas($taobao_util);
    	 
    }
    //抓取官方区域
    function download_gov(array &$request, array &$response, array &$app){
    	
    	/*
    	//省级start
    	$url = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2013/index.html";
    	$strCatch = $this->send($url);
    	$response = $strCatch['body'];
    	$strParentStart = "<tr class='provincetr'>";
    	$strParentEnd = "<\/tr>";
    	$strGet = "(.*)";
    	$pattern = "/".$strParentStart.$strGet.$strParentEnd."/isU";
    	if (preg_match($pattern, $response)) {
    		preg_match_all($pattern, $response, $str_province);
    	}
    	$t_id = 100;
    	foreach($str_province[1] as $value){
    		preg_match_all("/<a\s+href='(.*)'>(.*)<br\/><\/a>/isU", $value, $arr_province);
    		foreach($arr_province[1] as $k=>$v){
    			$area = array('id'=>$t_id,'type'=>'2','name'=>mb_convert_encoding($arr_province[2][$k],"UTF-8","auto"),'parent_id'=>'1','url'=>$v);
    			$ret = load_model('base/TaobaoAreaModel')->area_insert($area);
    			$t_id++;
    		}
    	}
    	echo 'success';
    	//省级end
    	*/
    	/*
    	//市级start
    	$type = '2';
    	$class_name = 'citytr';
    	$sort = '3';
    	$this ->catch_area($type,$class_name, $sort);
    	//市级end
    	*/
    	/*
    	//区级start
    	$type = '3';
    	$class_name = 'countytr';
    	$sort = '4';
    	$this ->catch_area($type,$class_name, $sort);
    	//区级end
    	*/
    	//街道start
    	$type = '4';
    	$class_name = 'towntr';
    	$sort = '5';
    	$this ->catch_area($type,$class_name, $sort);
    	//街道end
    	
    	exit;
    	
    	
    }
    function  catch_area($type = '2',$class_name,$sort){
    	//市级start
    	//$type = '2';
    	//区级start
    	if($type == '4'){
    		$limit = '500';
    		$sheng = load_model('base/TaobaoAreaModel')->get_area_type_limit($type,$limit);
    	}else{
    		$sheng = load_model('base/TaobaoAreaModel')->get_area_type($type);
    	}
    	
    	foreach($sheng as $value_sheng){
    		$str_head = substr($value_sheng['id'],0,2);
    		
    		if($type == '4'){
    			$url = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2013/".$str_head."/".$value_sheng['url'];
    		}else{
    			$url = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2013/".$value_sheng['url'];
    		}
    		
    		$strCatch = $this->send($url);
    		$response = $strCatch['body'];
    		//print_r($response);exit;
    		//市
    		//$strParentStart = "<tr class='citytr'>";
    		//区
    		$strParentStart = "<tr class='{$class_name}'>";
    		$strParentEnd = "<\/tr>";
    		$strGet = "(.*)";
    		$pattern = "/".$strParentStart.$strGet.$strParentEnd."/isU";
    		if (preg_match($pattern, $response)) {
    			preg_match_all($pattern, $response, $str_city);
    		}
    		
    		$t_id = 3;
    		if(isset($str_city[1])){
	    		foreach($str_city[1] as $value){
	    			$strParentStart = "<td>";
	    			$strParentEnd = "<\/td>";
	    			$strGet = "(.*)";
	    			$pattern = "/".$strParentStart.$strGet.$strParentEnd."/isU";
	    			preg_match_all($pattern, $value, $str_city1);
	    			//print_r($str_city1);
	    			preg_match_all('/<a\s+href.*>(.*)<\/a>/isU', $str_city1[1][0], $str_code);
	    			$code = $str_code[1][0];
	    			 
	    			preg_match_all("/<a\s+href='(.*)'>(.*)<\/a>/isU", $str_city1[1][1], $arr_city);
	    			 
	    			$url = $arr_city[1][0];
	    			$name = $arr_city[2][0];
	    			 
	    			//市级
	    			//$area = array('id'=>$code,'type'=>'3','name'=>mb_convert_encoding($name,"UTF-8","auto"),'parent_id'=>$value_sheng['id'],'url'=>$url);
	    			//区级
	    			$area = array('id'=>mb_convert_encoding($code,"UTF-8","auto"),'type'=>$sort,'name'=>mb_convert_encoding($name,"UTF-8","auto"),'parent_id'=>mb_convert_encoding($value_sheng['id'],"UTF-8","auto"),'url'=>$url);
	    			if($code <> ''){
	    				$ret = load_model('base/TaobaoAreaModel')->area_insert($area);
	    				
	    			}
	    			//print_r($area);
	    			 
	    			 
	    		}
    		}
    		//分段时标志更新
    		load_model('base/TaobaoAreaModel')->update_flag($value_sheng['id']);
    		unset($str_city,$response);
    	}
    	echo 'success';
    	 
    	//市级end
    }
    //省代码修改
    function update_code(array &$request, array &$response, array &$app){
    	$ret = load_model('base/TaobaoAreaModel')->update_code();
    	echo "success";
    	exit;
    }
    function send($url,$go='',$headers=array(),$post_data='',$crt_path='',$return_with_header=1){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	//curl_setopt($ch, CURLOPT_HEADER, (bool)$return_with_header);
    	curl_setopt($ch, CURLOPT_HEADER, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    	//		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    	//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    	if($go){
    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);  //是否抓取跳转后的页面
    	}
    	if(!empty($headers)){
    		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	}
    	if(!empty($post_data)){
    		curl_setopt($ch, CURLOPT_POST, TRUE);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    	}
    	if($crt_path){
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    		curl_setopt($ch, CURLOPT_CAINFO, $crt_path);
    	}else{
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	}
    	$response = curl_exec($ch);
    	$errmsg = curl_error($ch);
    	
    	/*var_dump($response);
    		if(''!=$errmsg){
    	return 'curl error: '.$errmsg;
    	}*/
    
    	//return $response;
    	$pos = strpos($response,"\r\n\r\n");
    	return array(
    			'code'=>curl_getinfo($ch,CURLINFO_HTTP_CODE),
    			'header'=>substr($response,0,$pos),
    			'body'=>substr($response,$pos+4),
    			'error'=>$errmsg
    	);
    	//curl_close($ch);
    }

	function dl_area_type_5(array & $request, array & $response, array & $app) {
		$app['tpl'] = "base/dl_area_type_5";
	}

	/**
	 * 生成街道区域
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function do_dl_area_type_5 (array & $request, array & $response, array & $app) {

		$db = CTX()->db;

		$sql_path = ROOT_PATH . 'install' . DIRECTORY_SEPARATOR . 'efast_oms' . DIRECTORY_SEPARATOR .'api_taobao_area'.DIRECTORY_SEPARATOR. 'api_taobao_area_type5.sql';
		if (!file_exists($sql_path)) {
			CTX()->log_error('install_db:创建数据库失败!数据库文件不存在！' );
			return false;
		}

		$sql_str = file_get_contents($sql_path);

		$db->query('set names utf8;');
		$db->query('DROP PROCEDURE IF EXISTS `install_func`;');
//		$ld->push(3.5, $total, true, '开始执行数据库脚本,时间较长请耐心等待');
		$proc = 'CREATE DEFINER = CURRENT_USER PROCEDURE `install_func`()
                    BEGIN ' . $sql_str . '
                    END;';
		try {
			$db->query($proc);
			$db->query('call install_func();');
			$db->query('DROP PROCEDURE IF EXISTS `install_func`;');
		} catch (Exception $exc) {
//			$ld->push(4, $total, false, '执行数据库脚本失败!' . $exc->getMessage())->done();
			CTX()->log_error('执行数据库脚本失败!');
			exit_json_response(array('status' => '0', 'data'=>'','message'=>'下载失败'));
		}

		exit_json_response(array('status' => '1', 'data'=>'','message'=>'下载成功'));
	}

}