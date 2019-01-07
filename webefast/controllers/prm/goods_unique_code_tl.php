<?php
require_lib ( 'util/web_util', true );
require_lib('comm_util', true);

class goods_unique_code_tl {
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
  /*
     * 商品删除
     * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
     */

 function do_delete(array & $request, array & $response, array & $app) {
                //var_dump($request);die;
                $ret = load_model('prm/GoodsUniqueCodeTLModel')->unique_delete($request['unique_code']);
                exit_json_response($ret);
            }
            
    function import(array &$request, array &$response, array &$app) {
        //获取所有有权限仓库
         $response['store']= load_model('base/StoreModel')->get_purview_store();
    }
    
    /*
     * 商品编辑显示
     * 
     */
    function detail(array & $request, array & $response, array & $app){
  
          //get_array_vars($request, array('unique_code', 'barcode', 'factory_code', 'tongling_code', 'goods_name', 'relative_purity', 'relative_purity_of_gold', 'international_num', 'check_station_num', 'identity_num', 'jewelry_brand', 'jewelry_brand_child', 'metal_color', 'jewelry_color', 'jewelry_clarity', 'jewelry_cut', 'pri_diamond_weight', 'pri_diamond_count', 'ass_diamond_weight', 'ass_diamond_count', 'total_weight', 'jewelry_type', 'ring_size','total_price','credential_type','credential_weight','record_num','short_name','user_defined_property_1','user_defined_property_2','user_defined_property_3','user_defined_property_4','user_defined_property_5','user_defined_property_6','user_defined_property_7','user_defined_property_8',));
        session_cache('set');
        $ret = array();
        if (isset($request['unique_code']) && $request['unique_code'] != '') {
            $ret['data'] = load_model('prm/GoodsUniqueCodeTLModel')->get_by_unique_code($request['unique_code']);
        } else {
            $ret['data'] = get_array_vars($request, array('unique_code', 'barcode', 'factory_code', 'tongling_code', 'goods_name', 'relative_purity', 'relative_purity_of_gold', 'international_num', 'check_station_num', 'identity_num', 'jewelry_brand', 'jewelry_brand_child', 'metal_color', 'jewelry_color', 'jewelry_clarity', 'jewelry_cut', 'pri_diamond_weight', 'pri_diamond_count', 'ass_diamond_weight', 'ass_diamond_count', 'total_weight', 'jewelry_type', 'ring_size','total_price','credential_type','credential_weight','record_num','short_name','user_defined_property_1','user_defined_property_2','user_defined_property_3','user_defined_property_4','user_defined_property_5','user_defined_property_6','user_defined_property_7','user_defined_property_8',));
        }
        if (isset($ret['data'])) {
            $response['data'] = $ret['data'];
        }
        $response['action'] = isset($request['action']) ? $request['action'] : '';
        //var_dump($ret);die;
    }
    
    //修改商品唯一码
    function do_edit(array & $request, array & $response, array & $app){
        //var_dump($request);die;
        $ret = load_model('prm/GoodsUniqueCodeTLModel')->get_by_unique_code($request['unique_code']); //取旧数据
        
        //$ret_arr = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
        
       $goods = get_array_vars($request, array('total_price'));
       //var_dump($goods);die;
       //$goods = get_array_vars($request, array('unique_code', 'barcode', 'factory_code', 'tongling_code', 'goods_name', 'relative_purity', 'relative_purity_of_gold', 'international_num', 'check_station_num', 'identity_num', 'jewelry_brand', 'jewelry_brand_child', 'metal_color', 'jewelry_color', 'jewelry_clarity', 'jewelry_cut', 'pri_diamond_weight', 'pri_diamond_count', 'ass_diamond_weight', 'ass_diamond_count', 'total_weight','jewelry_type','ring_size','total_price','credential_type','credential_weight','record_num','short_name','user_defined_property_1','','user_defined_property_2','user_defined_property_3','user_defined_property_4','user_defined_property_5','user_defined_property_6','user_defined_property_7','user_defined_property_8','','','',));
        

        $ret1 = load_model('prm/GoodsUniqueCodeTLModel')->update($goods, $request['unique_code']);
        ###########添加操作日志start
        $log_xq = '';

        $goods_old = array();

        $goods_old['unique_code'] = $ret['unique_code'];
        $goods_old['total_price'] = $ret['total_price'];

        $goods_new = $goods;
        $goods_edit = array();
        foreach ($goods_old as $key2 => $value2) {
            if (isset($goods_new[$key2]) && ($value2 <> $goods_new[$key2])) {
                $goods_edit[$key2] = $value2;
            }
        }
        //映射名称
        $goods_name['unique_code'] = '唯一码';
        $goods_name['total_price'] = '销售含税价';

        
         foreach ($goods_edit as $key => $value) {
            $old_value = $value;
            $new_value = $goods_new[$key];
           
            $log_xq .= $goods_name[$key] . '由' . $old_value . '改为' . $new_value . ',';
        }


        $module = '唯一码'; //模块名称
        $yw_code = $request['unique_code']; //业务编码
        $operate_xq = '编辑商品'; //操作详情
        $operate_type = '编辑';

        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        $ret2 = load_model('sys/OperateLogModel')->insert($log);
        ###########添加操作日志end
        //CTX()->redirect('prm/goods/do_list');
        //return;

        $ret2['data'] = $request['unique_code'];
        exit_json_response($ret2);
    }
            
    
	function import_action(array &$request, array &$response, array &$app) {
                $name = substr($request['name'],-3,3);
                $store_code = $request['store'];
		$app['fmt'] = 'json';
		$file = $request['url'];
		if(empty($file)){
			$response = array(
					'status' => 0,
					'type' => '',
					'msg' => "请先上传文件"
			);
		}
		$res = load_model('prm/GoodsUniqueCodeTLModel')->add($file,$name,$store_code);
                
                if($res['status']==1 && !empty($res['data'])){
                    $this->add_purchase($res['data'],$store_code);
                }

        $response = array('message'=>$res['msg'], 'status'=>$res['status']);
    }

    //自动生成采购入库单
    function add_purchase($unique,$store_code) {
        $stock_adjus['record_code'] = load_model('pur/PurchaseRecordModel')->create_fast_bill_sn();//单据号
        $stock_adjus['order_time'] = date('Y-m-d H:i:s', time());//下单时间
        $stock_adjus['record_time'] = date('Y-m-d');//业务日期
        $stock_adjus['record_type_code'] = '000';//采购类型
        $stock_adjus['supplier_code'] ='000';//供应商
        $stock_adjus['store_code'] = $store_code;//仓库
        $stock_adjus['num'] = count($unique);//数量
        $stock_adjus['finish_num'] = count($unique);//实际入库数量
        $stock_adjus['remark'] = '这是由唯一码档案导入生成';
        $unique_str = "'".implode("','",$unique)."'";
        $res = load_model('prm/GoodsUniqueCodeTLModel')->get_detail_by_unique($unique_str);//获取详情
       $ret = load_model('pur/PurchaseRecordModel')->insert($stock_adjus);//插入采购表数据
         $result = load_model('pur/PurchaseRecordModel')->add_detail_goods($ret['data'], $res, $store_code);//添加批次和商品明细
       if (isset($ret['data']) && $ret['data'] <> '') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未验收', 'action_name' => "创建", 'module' => "purchase_record", 'pid' => $ret['data']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
       
       return $ret;
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
 
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('csv', 'xlsx', 'xls');
        $upload_max_filesize = 5242880;
       
         foreach ($files_name_arr as $k => $v) {
            $pic = $_FILES[$v];
            
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
                    $result = load_model('pur/OrderRecordModel')->excel2csv($dir . $new_file_name, $file_ext);
                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
                }
            }
        }
       
        if ($is_max) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'name' =>$_FILES[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            $response = array(
                'status' => 1,
                'type' => $type,
                'name' => $_FILES[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            $response = array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
            //var_dump($response);die;
                 set_uplaod($request, $response, $app);
    }
    
    function unique_code_log(array &$request, array &$response, array &$app){
    	$app['fmt'] = 'json';
    	$ret = load_model('prm/GoodsUniqueCodeLogModel')->unique_code_log($request);
    	$response = $ret;
    }
    
}


