<?php
/**
 * 货架/仓库库位相关业务控制器
*/
require_lib('util/web_util', true);
class shelf{
	function do_list(array &$request, array &$response, array &$app) {
		
	}
	/**
	 * 导入
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function import(array & $request, array & $response, array & $app){
	
	}
	/**
	 * 扫描库位
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	function scan(array & $request, array & $response, array & $app){
		$response['store'] = load_model('base/StoreModel')->get_list();
	}
	/**
	 * 查看库位
	 */
	function view(array & $request, array & $response, array & $app){
		$response['store_code'] = $request['store_code'];
		$response['store_name'] = $request['store_name'];
	}
	/**
	 * 查看库位商品
	 */
	function goods_list(array & $request, array & $response, array & $app){
		$response['shelf_code'] = $request['shelf_code'];
		$response['store_code'] = $request['store_code'];
		$response['store_name'] = $request['store_name'];
		//spec1别名
		$arr = array('goods_spec1');
		$arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
		//spec2别名
		$arr = array('goods_spec2');
		$arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
		$response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
	}
	function detail(array & $request, array & $response, array & $app){
		$ret = array();
		$ret['data']['status'] = '1';
		$ret['data']['store_code'] = isset($request['store_code'])?$request['store_code']:'';
		if (isset($request['_id']) && $request['_id'] != '') {
			$ret = load_model('base/ShelfModel')->get_by_id($request['_id']);
		}
		
		$response['data'] = isset($ret['data'])?$ret['data']:'';
		//仓库
		$response['store'] = $this->get_store();
	}
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('base/ShelfModel')->delete($request['shelf_id']);
		exit_json_response($ret);
	}
        function do_delete_store(array & $request, array & $response, array & $app){
                $ret = load_model('base/ShelfModel')->delete_store($request['store']);
		exit_json_response($ret);
        }
	//仓库
	function get_store(){
		$arr_store = load_model('base/StoreModel')->get_list();
		$key = 0;
		foreach ($arr_store as $value){
			$arr_store[$key]['0'] = $value['store_code'];
			$arr_store[$key]['1'] = $value['store_name'];
			unset($arr_store[$key]['store_id'],$arr_store[$key]['store_code'],$arr_store[$key]['store_name']);
			$key++;
		}
		return $arr_store;
	}
	function do_add_sm(array &$request, array &$response, array &$app) {
		$request['shelf_name'] = $request['shelf_code'];
		$shelf = get_array_vars($request, array('shelf_code', 'shelf_name','store_code'));
		$ret = load_model('base/ShelfModel')->insert($shelf);
		exit_json_response($ret);
	}
	function do_add(array &$request, array &$response, array &$app) {
		$shelf = get_array_vars($request, array('shelf_code', 'shelf_name','store_code','status','remark'));
		$ret = load_model('base/ShelfModel')->insert($shelf);
		exit_json_response($ret);
	}
	function do_edit(array &$request, array &$response, array &$app) {
		$shelf = get_array_vars($request, array('shelf_name','status','remark'));
		$ret = load_model('base/ShelfModel')->update($shelf, $request['shelf_id']);
		exit_json_response($ret);
	}
	
	
	/**
	 * 导入excel
	 * @param array $request
	 * @param array $response
	 * @param array $app
	 */
	public function do_import(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';
		//$M = load_model('sys/ExcelImportModel');
		$excel_src = basename($request['url']);
		$path = isset($excel_src) ? $excel_src : '';
		//读取上传的excel +++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$app = CTX()->app;
		
		if (defined('CLOUD') && CLOUD) {
			$kh_code = $GLOBALS['context']->get_session("kh_code");
			$path = $kh_code.DIRECTORY_SEPARATOR.$path;
		}
		 
		$tpl_path = ROOT_PATH . $app['name'] . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $path;
		if (!file_exists($tpl_path) && !is_dir($tpl_path)) {
			return $this->format_ret(false, $tpl_path, '上传的excel文件不存在!');
		}
                $shelf_code = $this->read_csv($tpl_path, $shelf_arr, $shelf);
                $err_msg = array();
                $success = 0;
                $fail_num = 0;
		$faild = array();
                foreach($shelf_arr as $key => $value){
                    $shelf_code = trim($key);
                    $shelf_name = trim($value['shelf_name']);
                    $store_code = trim($value['store_code']);
                    $r = load_model('base/ShelfModel')->add($shelf_name, $store_code, $shelf_code);
					if($r['status'] == '1'){
                        $success++;
                    } else {
                        $faild[$shelf_code] = $r['message'];
                        $fail_num++;
                    }
                }
		if($success > 0 && $faild == array()){
			$status = '1';	
		} else {
			$status = '-1';
		}
                $message = '导入成功'.$success.'条';
                if (!empty($faild)) {
                    $status = -1;
                    $message .='，导入失败:' . $fail_num . '条';
                    $fail_top = array('仓库库位代码','错误信息');
                    $filename = 'shelf_import';
                    $file_name = $this->create_import_fail_files($faild, $fail_top, $filename);
//                    $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
                    $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                    $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
                }
		$ret = array(
				'status' => $status,
				'data' => $success,
				'message' => $message,
		);
		$response = $ret;
	}
        //读取Excel
        function read_csv($file, &$shelf_arr, &$shelf) {
            $file = fopen($file, "r");
            $i = 0;
            while (!feof($file)) {
                $row = fgetcsv($file);
                if ($i >= 2) {
                    //$this->tran_csv($row);
                    if (!empty($row[0])) {
                        $shelf[] = $row[0];
                        $shelf_arr[$row[0]]['shelf_name'] = $row[1];
                        $shelf_arr[$row[0]]['store_code'] = $row[2];
                    }
                }
                $i++;
            }
            fclose($file);
            return $i;
        }

//        private function tran_csv(&$row) {
//            if (!empty($row)) {
//                foreach ($row as &$val) {
//                    $val = iconv('gbk', 'utf-8', $val);
//                    $val = trim(str_replace('"', '', $val));
//                }
//            }
//        }

    /**
	 * excel文档下载
	 * 根据传递的code, 取到相应的excel模版文件, 读取并输出
	 */
	public function tplDownload(array & $request, array & $response, array & $app){
		//$app['fmt'] = 'json';
		$M = load_model('sys/ExcelImportModel');
		$code = $request['code'];
		$excel = $M->get_row(array('danju_code'=>$code));
		if($excel['status']==true && !empty($excel['data']['danju_path'])){
			//获取url路径
			$path = $excel['data']['danju_path'];
			$path = APP_PATH.'web'.DIRECTORY_SEPARATOR.$path;
			header("Content-type:application/vnd.ms-excel;charset=utf8");
			header("Content-Disposition:attachment; filename=$code.xlsx");
			echo file_get_contents($path);
			die();
		}else{
			exit_error_page('出错啦!', '模版文件不存在');
		}
	
	}
        function import_shelf(array & $request, array & $response, array & $app) {
             set_uplaod($request, $response, $app);
            $ret = check_ext_execl();
            if($ret['status']<0){
                $response = $ret;
                return ;
            }      
            $ret = load_model('pur/OrderRecordModel')->import_upload($request,$_FILES);
            $response = $ret;
            
        }
        function create_import_fail_files($msg, $fail_top, $filename) {
            $file_str = implode(",", $fail_top) . "\n";
            foreach ($msg as $key => $val) {
                $val_data = array($key, $val);
                $file_str .= implode(",", $val_data) . "\r\n";
            }
            $filename = md5($filename . time());
            $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
            file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
            return $filename;
        }
    
}