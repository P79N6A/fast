<?php
require_lib ( 'util/web_util', true );
class inv_lof {
	function do_list(array & $request, array & $response, array & $app) {
		//类别 start
          //$time = time();
		 //$response['category'] = $this ->get_category();   
              $response['category'] = load_model('prm/CategoryModel')->get_category_trees();
             //  var_dump($time-time());
		//品牌  start
        $response['brand'] = $this->get_purview_brand();
		//年份 start
		//$response['year'] = $this->get_year();
		//季节 start
		//$response['season'] = $this->get_season();
              //spec1别名
              $arr = array('goods_spec1');
              $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
              $response['goods_spec1_rename'] =isset($arr_spec1['goods_spec1'])?$arr_spec1['goods_spec1']:'' ;
              //spec2别名
              $arr = array('goods_spec2');
              $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code($arr);
              $response['goods_spec2_rename'] =isset($arr_spec2['goods_spec2'])?$arr_spec2['goods_spec2']:'' ;
                if(isset($request['mode'])){
                    $response['mode'] = $request['mode'];
                }
                //获取系统参数 是否启用批次
                $lof_status = load_model("sys/SysParamsModel")->get_val_by_code(array("lof_status"));
                $response['lof_status'] = $lof_status['lof_status'];
	}
	function get_purview_brand(){
		//品牌  start
		$arr_brand = load_model('prm/BrandModel')->get_purview_brand();
	
		$key = 0;
		foreach ($arr_brand as $value){
			$arr_brand[$key][0] = $value['brand_code'];
			$arr_brand[$key][1] = $value['brand_name'];
			$key++;
		}
		//print_r($arr_brand);
		return $arr_brand;
	
	}  
        //实物锁定明细查询
        function lock_detail(array & $request, array & $response, array & $app){
            //获取系统参数 是否启用批次
            $lof_status = load_model("sys/SysParamsModel")->get_val_by_code(array("lof_status"));
            $response['lof_status'] = $lof_status['lof_status'];
            if (isset($request['mode'])) {
                $response['mode'] = $request['mode'];
                return;
            }elseif($response['lof_status']==1){
                $response['mode'] = 'lof_mode';
            }else{
                 $response['mode'] = 'normal_mode';
            }
            
            
            
            if (isset($request['store_code'])) {
                $response['filter']['store_code'] = $request['store_code'];
            }
            if (isset($request['sku'])) {
                $response['filter']['sku'] = $request['sku'];
                $response['filter']['barcode'] = load_model('goods/SkuCModel')->get_barcode($request['sku']);
            }
            if (isset($request['lof_no'])) {
                $response['filter']['lof_no'] = $request['lof_no'];
            }
        }
        function maintain_list(array & $request, array & $response, array & $app){
                  $response['store'] =  load_model('base/StoreModel')->get_purview_store();
        }
        function set_maintain_task(array & $request, array & $response, array & $app){
                 $response = load_model('prm/InvModel')->set_inv_maintain_task($request['store_code'],$request['type']); 
        }
        function get_maintain_task(array & $request, array & $response, array & $app){
               $response = load_model('prm/InvModel')->get_inv_maintain_task($request['store_code'],$request['type']); 
        }
              
        function inv_maintain(array & $request, array & $response, array & $app){
            if(!empty($request['store_code'])){
                $response = load_model('prm/InvModel')->inv_maintain($request['store_code']); 
            }else{
                $response['status'] = -1;
            }
            
        }
         function inv_maintain_lock(array & $request, array & $response, array & $app){
            if(!empty($request['store_code'])){
                $response = load_model('prm/InvModel')->inv_maintain_lock($request['store_code']); 
            }else{
                $response['status'] = -1;
            }
            
        }
         function stock_out_inv(array & $request, array & $response, array & $app){
            if(!empty($request['store_code'])){
                $response = load_model('prm/InvModel')->stock_out_inv($request['store_code']); 
            }else{
                $response['status'] = -1;
            }
            
        }
        function inv_maintain_road(array & $request, array & $response, array & $app){
            if(!empty($request['store_code'])){
                $response = load_model('prm/InvOpRoadModel')->inv_maintain_road($request['store_code']); 
            }else{
                $response['status'] = -1;
            }
            
        }  
        
        
       function get_inv_summary(array & $request, array & $response, array & $app){
            $response = load_model('prm/InvModel')->get_summary($request); 
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
  //安全库存导入
  function safe_import(array & $request, array & $response, array & $app){
      $response['store'] =  load_model('base/StoreModel')->get_purview_store();
  }
  //导入
  function import_safe_num(array & $request, array & $response, array & $app) {
		$app['fmt'] = 'json';

	  $request['store_code'] = explode(",", $request['store_code']);
	  $request['store_code'] = implode("','", $request['store_code']);
      $file = $request['url'];
      if(empty($file)){
      	$response = array(
      			'status' => 0,
      			'type' => '',
      			'msg' => "请先上传文件"
      	);
      }
      $ret = load_model('prm/InvModel')->imoprt_detail($request, $file);
      $response = $ret;
   
    }
	
	function edit_safe_num(array & $request, array & $response, array & $app){
		if ($request['goods_inv_id']) {
			$ret = CTX()->db->update("goods_inv", array('safe_num' => $request['safe_num']), array('goods_inv_id' => $request['goods_inv_id']));
			exit_json_response($ret);
		}
		
	}
}



