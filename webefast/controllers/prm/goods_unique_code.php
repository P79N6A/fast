<?php
require_lib ( 'util/web_util', true );
class goods_unique_code {
	function do_list(array & $request, array & $response, array & $app) {
		$arr = array('goods_spec1');
		$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
		//spec2别名
		$arr = array('goods_spec2');
		$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
	}
	
	function do_log_list(array & $request, array & $response, array & $app){
		
	}

	//品牌数据
	function get_brand(){
		//品牌  start
		$arr_brand = load_model('prm/BrandModel')->get_brand();
		$key = 0;
		foreach ($arr_brand as $value){
			$arr_brand[$key][0] = $value['brand_code'];
			$arr_brand[$key][1] = $value['brand_name'];
			$key++;
		}
		return $arr_brand;
		
	}
	//季节
	function get_season(){
		$arr_season = load_model('base/SeasonModel')->get_season();
		$key = 0;
		foreach ($arr_season as $value){
			$arr_season[$key][0] = $value['season_code'];
			$arr_season[$key][1] = $value['season_name'];
			$key++;
		}
		return $arr_season;
	}
	//年份
	function get_year(){
		$arr_year = load_model('base/YearModel')->get_year();
		$key = 0;
		foreach ($arr_year as $value){
			$arr_year[$key][0] = $value['year_code'];
			$arr_year[$key][1] = $value['year_name'];
			$key++;
		}
		return $arr_year;
	}
	//删除
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/GoodsBarcodeModel')->delete($request['sku_id']);
		exit_json_response($ret);
	}
	function import(array &$request, array &$response, array &$app) {

    }
    
	function import_action(array &$request, array &$response, array &$app) {

		$app['fmt'] = 'json';
		$file = $request['url'];
		if(empty($file)){
			$response = array(
					'status' => 0,
					'type' => '',
					'msg' => "请先上传文件"
			);
		}
		$res = load_model('prm/GoodsUniqueCodeModel')->add($file);
		
        $response = array('message'=>$res['msg'], 'status'=>$res['status']);
    }

    function import_upload(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
                 $ret = check_ext_execl();
        if($ret['status']<0){
            $response = $ret;
            return ;
        }
        $files = array();
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/";

        $fileInput = 'fileData';
        $dir = ROOT_PATH.'webefast/uploads/';
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        foreach($files_name_arr as $k=>$v){
            $pic = $_FILES[$v];
            $isExceedSize = $pic['size'] > 500000;
            if(!$isExceedSize){
                if(file_exists($dir.$pic['name'])){
                    @unlink($dir.$pic['name']);
                }
                // 解决中文文件名乱码问题
                //$pic['name'] = iconv('UTF-8', 'GBK', $pic['name']);
                $result = move_uploaded_file($pic['tmp_name'], $dir.$pic['name']);
                $files[$k] = $url.$dir.$pic['name'];
            }
        }
        if(!$isExceedSize && $result){
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir.$_FILES[$fileInput]['name']
            );
        }else if($isExceedSize){
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "文件大小超过500kb！"
            );
        }else{
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！".$result
            );
        }
                 set_uplaod($request, $response, $app);
    }
    
    function unique_code_log(array &$request, array &$response, array &$app){
    	$app['fmt'] = 'json';
    	$ret = load_model('prm/GoodsUniqueCodeLogModel')->unique_code_log($request);
    	$response = $ret;
    }
}


