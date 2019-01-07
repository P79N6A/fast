<?php

require_lib('util/web_util', true);
require_lib('tb_util', true);
require_lib('util/taobao_util', true);

require_model('prm/BrandModel');
require_model('prm/CategoryModel');
require_model('prm/Spec1Model');
require_model('prm/Spec2Model');
require_model('prm/GoodsModel');
require_model('prm/GoodsSpec1Model');
require_model('prm/GoodsSpec2Model');
require_model('prm/SkuModel');
require_model('prm/GoodsBarcodeModel');
require_model('prm/GoodsPriceModel');

require_model('api/BaseItemModel');
require_model('api/BaseSkuModel');

require_model('stm/StockAdjustRecordModel');
require_model('stm/StmStockAdjustRecordDetailModel');

require_model('api/TbBaseItemCatsModel');

require_model('base/ShopApiModel');

require_lib('util/Loading.class');

class auto_create {

    function do_list(array &$request, array &$response, array &$app) {

        $shop_list = load_model('base/ShopModel')->get_by_page(array('sale_channel_code' => 'taobao'));
        $response['shop_list'] = $shop_list['data']['data'];

        $store_list = load_model('base/StoreModel')->get_by_page(array());
        $response['store_list'] = $store_list['data']['data'];

        $tb_cats = load_model('api/TbBaseItemCatsModel')->get_itemcats_by_parent_id(0);
        $_tb_cats = array();
        foreach ($tb_cats['data'] as $_val) {
            $_tb_cats[$_val['cid']] = $_val['name'];
        }


        $response['tb_cats'] = json_encode($_tb_cats);
    }

    /**
     * 初始化系统参数
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function param_list(array &$request, array &$response, array &$app) {
            CTX()->redirect('index/do_index');die;
    }

    /**
     * 初始化系统参数
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_param(array &$request, array &$response, array &$app) {
		$ret = load_model('sys/SysParamsModel')->update(array('value'=>$request['goods_spec1']),"param_code ='goods_spec1'");
        $ret = load_model('sys/SysParamsModel')->update(array('value'=>$request['goods_spec2']),"param_code ='goods_spec2'");
        $ret = load_model('sys/SysParamsModel')->update(array('value'=>$request['lof_status']),"param_code ='lof_status'");
        $ret = load_model('sys/SysParamsModel')->update(array('value'=>$request['delivery_lof_sort']),"param_code ='delivery_lof_sort'");
        $ret = load_model('sys/SysParamsModel')->update(array('value'=>$request['online_date']),"param_code ='online_date'");
		CTX()->redirect('sys/auto_create/ams_list&init=1');
		return;    	
    }

    /**
     * 初始化档案
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function ams_list(array &$request, array &$response, array &$app) {
        // time
        CTX()->redirect('index/do_index');die;
        $result = load_model('sys/SysParamsModel')->get_val_by_code(array('online_date'));
        $response['time'] = date('Y年m月d日', strtotime($result['online_date']));
        $response['init'] = isset($request['init'])?$request['init']:0;

        $mdl_shop_api = new ShopApiModel();
        $ret = $mdl_shop_api->get_shop('');
        $response['shop_list'] = $ret['data'];
        

        
        $response['store_list'] = ds_get_select('store', 2);
  
        $ret_cat =  load_model('api/TbBaseItemCatsModel')->get_itemcats_by_parent_id(0);
        $tb_cat_list = array();
        foreach( $ret_cat['data'] as $val){
            $tb_cat_list[] = array('value'=>$val['cid'],'text'=>$val['name']);
        }
        
        $response['tb_cat_list'] = &$tb_cat_list;

    }
    function get_init_info(array &$request, array &$response, array &$app) {
        $shop_code = $request['shop_code'];
        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $ret_init = $init->get_info($shop_code);  
        if(empty($ret_init['data'])){
            $ret = $init->create_init($shop_code);    
            $ret_init = $init->get_info($shop_code);  
        }
        $mdl_shop_api = new ShopApiModel();
        $ret = $mdl_shop_api->get_shop($shop_code);
        $ret_init['data']['tb_shop_type'] = strtoupper($ret['data']['tb_shop_type']);
        $response = $ret_init;
    }
    
    
    
    /**
     * 初始化档案
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_ams(array &$request, array &$response, array &$app) {
        
        $shop_code = &$request['shop_code'];
        $mdl_shop_api = new ShopApiModel();
        $ret = $mdl_shop_api->get_shop($shop_code);
        $this->shop_code = $shop_code;
        $this->tb_cats = isset($request['tb_cats'])?$request['tb_cats']:'' ;
        $parameter['app'] = $ret['data']['api']['app_key'];
        $parameter['secret'] = $ret['data']['api']['app_secret'];
        $parameter['session'] = $ret['data']['api']['session'];
        $parameter['nick'] = $ret['data']['api']['nick'];
        $this->api_param = $parameter;
        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $ret_init = $init->get_info($shop_code);
        
        $status =  $ret_init['data']['goods_status'] ;   
        $this->all_cids = array(); //所有分类的cid集合
        if($status == 1){
            $taobao_itemcats_authorize_get = taobao_itemcats_authorize_get($parameter);
        
            
            $tb_shop_type = strtoupper($ret['data']['tb_shop_type']);
            if ($tb_shop_type == "B") {
                //品牌建档
                if (not_null($taobao_itemcats_authorize_get['data']['seller_authorize']['brands'])) {
                    $tb_brands = $taobao_itemcats_authorize_get['data']['seller_authorize']['brands']['brand'];
                    $this->init_brands($tb_brands);
                  //  $_SESSION['tb_brand_create_over'] = 1;
                    //exit_json_response(1, array(), '品牌建档成功!');
                }
                $init->set_load($shop_code,10,0);
                ///分类
                if (not_null($taobao_itemcats_authorize_get['data']['seller_authorize']['item_cats'])) {
                    $__tb_cats = $taobao_itemcats_authorize_get['data']['seller_authorize']['item_cats']['item_cat'];
                     $this->dl_category($__tb_cats);
                } else {
                    //exit_json_response(-1, array(), '授权有误，请检查店铺档案授权情况！！！');
                    $init->save_info($shop_code, array('goods_status'=>9,'goods_message'=>'授权有误，请检查店铺档案授权情况！'),0);
                    
                }
               $init->set_load($shop_code,20,0);

            }else if ($tb_shop_type == "C") {
                    //C店铺分类
                    //
                    //下载类目
                   // $_tb_cats = load_model('api/TbBaseItemCatsModel')->dl_itemcats($shop_code);

                    //根据类目 可能是多个类目
                    $_tb_cats = load_model('api/TbBaseItemCatsModel')->get_itemcats_by_cids($this->tb_cats);
                    $__tb_cats = array();
                    $__tb_cats = $_tb_cats['data'];
                    $this->dl_category($__tb_cats);
            }
        //初始化规格
        $this->dl_itemprops($this->all_cids);
        $init->set_load($shop_code,30,0);
        $init->save_info($shop_code, array('goods_status'=>2));
        $response['status'] = 100;
        }else if($status==2){
            //初始化商品
            $num_iids = $this->get_onsale_items();
            $init->set_load($shop_code,40,0);
            $item_datas = $this->batch_get_items($num_iids);
            $init->set_load($shop_code,50,0);
            load_model('api/BaseItemModel')->insert_data($item_datas, $this->shop_code);
            $init->set_load($shop_code,60,0);
            $this->init_goods($item_datas);
            $init->save_info($shop_code, array('goods_status'=>3));
            $init->set_load($shop_code,100,0);
            $response['status'] = 1;
        }
    }
    
    function get_init_status(array &$request, array &$response, array &$app) {
        $shop_code = &$request['shop_code'];
        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $ret_init = $init->get_info($shop_code);  
        $goods_load = isset ($request['goods_load'])?$request['goods_load']:0;
        $order_load = isset ($request['order_load'])?$request['order_load']:0;
        
        if($ret_init['data']['goods_status']>0&&$ret_init['data']['goods_status']<4){
            if($ret_init['data']['goods_load']<60){
                if($ret_init['data']['goods_load']>$goods_load){
                    $goods_load = $ret_init['data']['goods_load'];
                }else{
                    $goods_load = $goods_load+rand(1, 3);
                }
            }else{
                $goods_load = $ret_init['data']['goods_load'];
            }
        }
        
         if($ret_init['data']['inv_status']>0&&$ret_init['data']['inv_status']<4){
            if($ret_init['data']['goods_load']<60){
                if($ret_init['data']['goods_load']>$goods_load){
                    $goods_load = $ret_init['data']['goods_load'];
                }else{
                    $goods_load = $goods_load+rand(1, 3);
                }
            }else{
                $goods_load = $goods_load+rand(1, 3);
            }
        }  
        if($ret_init['data']['order_status']==1){
             require_model("common/TaskModel");
             $task = new TaskModel();
             if(!empty($ret_init['data']['order_task_id'])){
                $task_ids = $ret_init['data']['order_task_id'];
                $task_arr = explode(",", $task_ids);
                $all_num  = count($task_arr);
                $ret_task =$task->get_status_tasks($task_ids); 
                $over_num = count($ret_task['data']);
                if($over_num==0){
                    $ret_init['data']['order_status'] = 2;
                    $init->save_info($shop_code, array('order_status'=>2));
                }
                $p_num = ceil(100*$all_num/$over_num);
                if($p_num>$order_load){
                    $order_load = $p_num;
                }else{
                    $order_load = $order_load+rand(1, 3);
                }
             }
            
        }

        
        
        $data = array('goods_status'=>$ret_init['data']['goods_status'],'inv_status'=>$ret_init['data']['inv_status'],'order_status'=>$ret_init['data']['order_status'],'goods_load'=>$goods_load,'order_load'=>$order_load);
        $response['data'] = $data;
        $response['status'] = 1;
        
    }
    
    function init_task(array &$request, array &$response, array &$app) {
        $mdl_shop_api = new ShopApiModel();
        $shop_code = $request['shop_code'];
       require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $ret_shop = $mdl_shop_api->get_shop($shop_code);
        require_model("common/TaskModel");
        $task = new TaskModel();
        $data = array();  
        $status_key = '';
        if($request['type']==1){
            $data['code'] = 'auto_create_do_ams';
            $t_request['app_act'] = 'sys/auto_create/do_ams';
            $t_request['app_fmt'] = 'json';
            $t_request['shop_code'] = $shop_code;
            $t_request['tb_cats'] = isset($request['tb_cats'])?$request['tb_cats']:'';
            $tb_shop_type = strtoupper($ret_shop['data']['tb_shop_type']);
            if($tb_shop_type=='C'){
                $t_request['tb_cats'] = $request['tb_cats'];
            }
            $data['request'] = $t_request;
            $status_key = 'goods_status';
             $ret = $task->save_task($data);
        }else if($request['type']==2){//订单
//            $t_request['app_act'] = 'sys/auto_create/do_ams';
//            $t_request['app_fmt'] = 'json';
//            $t_request['shop_code'] = $ret['data']['shop_code'];
//            $t_request['tb_cats'] = isset($request['tb_cats'])?$request['tb_cats']:'';
//            $data['request'] = $t_request;    
            
              $data['code'] = 'order_taobao_list';
        $n_request['app_act'] = "sys/task/get_order";
        $data['request'] = $n_request;    
            $result = load_model('sys/SysParamsModel')->get_val_by_code(array('online_date'));
            $start_time = strtotime($result['online_date']." 0:00:00");
            $now_time = time();
            $task_id = array();
            while ($start_time<$now_time){
                $end_time = $start_time+43200;
                $end_time = ($end_time>$now_time)?$now_time:$end_time;
                $data['request']['start_time'] = date('Y-m-d H:i:s',$start_time);
                $data['request']['end_time'] = date('Y-m-d H:i:s',$end_time);
                $data['request']['platform'] = 'taobao';
                $data['request']['shop_code'] = $shop_code;
                $ret = $task->save_task($data);
                $task_id[] = $ret['data'];
                $start_time  = $end_time;
            }
            $r_inv= $init->save_info($shop_code, array('order_task_id'=>  implode(",", $task_id)));
            $status_key = 'order_status';

        }else if($request['type']==3){//库存
            $store_code = '';
            if(isset($request['store_name'])&&!empty($request['store_name'])){//存在CODE没设置问题
                $store['store_name'] = trim($request['store_name']);
                if($store['store_name']!=''){
                    $ret = load_model('base/StoreModel')->insert($store);
                }else{
                      $store_code = isset($request['store_code'])?$request['store_code']:'';
                }
            }else{
                $store_code = $request['store_code'];
            }

            if($store_code==''){
                  $response = array('status'=>'-1','message'=>'请选择仓库');
                  return FALSE;
            }else{
                 $ret_s = load_model('base/ShopInitModel')->update(array('store_code'=>$store_code),array('shop_code'=>$shop_code));
            }
            
            
            $data['code'] = 'auto_create_do_init_goods_inv'; 
            
            $t_request['app_act'] = 'sys/auto_create/do_init_goods_inv';
            $t_request['app_fmt'] = 'json';
            $t_request['shop_code'] = $shop_code;
            $status_key = 'inv_status';
            $data['request'] = $t_request;   
            $ret = $task->save_task($data);
        }
        
        if(isset($ret['status'])){
            if($ret['status']>0){
                $r_inv= $init->save_info($shop_code, array($status_key=>1));
            }
            $response = $ret; 
       }else{
           $response = array('status'=>'-1','message'=>'数据参数异常');
       }
       
       
    }
  

    /**
     * 店铺change，判断B店和C店
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function shop_change(array &$request, array &$response, array &$app) {

        $shop_code = $request['shop_code'];
        $mdl_shop_api = new ShopApiModel();
        $data = $mdl_shop_api->get_shop_api_by_shop_code($shop_code);
        exit_json_response(1, $data);
    }

    /**
     * 自动建档
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_auto_create(array &$request, array &$response, array &$app) {

        set_time_limit(0);
        $shop_code = isset($request['shop_code']) ? $request['shop_code'] : '';
        $mdl_shop_api = new ShopApiModel();
        //$shop_data = $mdl_shop_api->get_shop_api_by_shop_code();
        $ret = $mdl_shop_api->get_shop($shop_code);
        if ($ret['status'] < 1) {
            $response = $ret;
            return;
        }


//		$app_key = CTX()->get_app_conf('app_key');
//		$app_secret = CTX()->get_app_conf('app_secret');
//		$app_session = CTX()->get_app_conf('app_session');
//		$app_nick = CTX()->get_app_conf('app_nick');

        if (!$shop_data['api']) {

            exit_json_response(-1, array(), '授权有误，请检查店铺档案授权情况！！');
        }

        $api_params = $shop_data['api'];

//		if ('C' == $shop_data['tb_shop_type']) {
//			if (!$this->tb_cats) {
//				//exit_json_response(-1, array(), '请选择店铺对应的所属分类!!');
//			}
//		}

        if (1 == $step) {
            $_SESSION['create_over'] = 0; //是否创建结束

            $_SESSION['tb_brand'] = $_SESSION['tb_category'] = $_SESSION['tb_spec1'] = $_SESSION['tb_spec2'] = $_SESSION['tb_item'] = array();
            $_SESSION['tb_brand_create_over'] = $_SESSION['tb_category_create_over'] = $_SESSION['tb_spec_create_over'] = $_SESSION['tb_item_create_over'] = 0;
            $_SESSION['tb_item_create_over'] = 0;
            $_SESSION['all_cids'] = array();
        }

        if (1 == $step) {

            $_SESSION['tb_brand'] = $_SESSION['tb_category'] = $_SESSION['tb_spec1'] = $_SESSION['tb_spec2'] = $_SESSION['tb_item'] = array();
            $_SESSION['tb_brand_create_over'] = $_SESSION['tb_category_create_over'] = $_SESSION['tb_spec_create_over'] = $_SESSION['tb_item_create_over'] = 0;
            $_SESSION['tb_item_create_over'] = 0;
            $_SESSION['all_cids'] = array();

            $tb_brands = array(); //淘宝品牌
            if (not_null($taobao_itemcats_authorize_get['data']['seller_authorize']['brands'])) {

                $tb_brands = $taobao_itemcats_authorize_get['data']['seller_authorize']['brands']['brand'];

                $this->init_brands($tb_brands);

                $_SESSION['tb_brand_create_over'] = 1;
                exit_json_response(1, array(), '品牌建档成功!');
            }
        }

        //分类
        if (1 == $step) {

            if ('C' == $shop_data['tb_shop_type']) {
                $_tb_cats = load_model('api/TbBaseItemCatsModel')->get_itemcats_by_cid($this->tb_cats);
                $__tb_cats = array();
                $__tb_cats[] = $_tb_cats['data'];
            }

            if ('B' == $shop_data['tb_shop_type']) {
                $taobao_itemcats_authorize_get = taobao_itemcats_authorize_get($api_params);
                $_SESSION['taobao_itemcats_authorize_get'] = $taobao_itemcats_authorize_get;
                if (not_null($taobao_itemcats_authorize_get['data']['seller_authorize']['item_cats'])) {
                    $__tb_cats = $taobao_itemcats_authorize_get['data']['seller_authorize']['item_cats']['item_cat'];
                } else {
                    exit_json_response(-1, array(), '授权有误，请检查店铺档案授权情况！！！');
                }
            }

            $_SESSION['tb_category'] = array();
            $this->all_cids = array(); //所有分类的cid集合

            $this->dl_category($__tb_cats);

            $_SESSION['tb_category_create_over'] = 1;

            $_SESSION['all_cids'] = $this->all_cids;
            //exit_json_response(1, array(), '分类建档成功!');
        }

        //规格
        if (2 == $step) {

            $this->all_cids = $_SESSION['all_cids'];
            $this->dl_itemprops($this->all_cids);
            $_SESSION['tb_spec_create_over'] = 1;

            //exit_json_response(1, array(), '规格建档成功!');
        }

        //商品
        if (3 == $step) {

            $num_iids = $this->get_onsale_items();
            $item_datas = $this->batch_get_items($num_iids);

            load_model('api/BaseItemModel')->insert_data($item_datas, $this->shop_code);
            $this->init_goods($item_datas);

            $_SESSION['tb_item_create_over'] = 1;
            $_SESSION['tb_brand_create_over'] = 1;
        }

        $_SESSION['create_over'] = 1;
        //exit_json_response(1, array(), '建档成功!');
    }

    /**
     * 创建进度页面
     */
    function create_progress_html(array &$request, array &$response, array &$app) {

        $app['tpl'] = 'sys/auto_create_progress';
        $response['shop_code'] = $request['shop_code'];

        $response['tb_cats'] = $request['tb_cats'];
        $response['_rand_code'] = rand(); //每次关掉页面，页面元素没有重置，目前这样处理，保证id不重复
    }

    /**
     * 监测进度页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function check_create_progress(array &$request, array &$response, array &$app) {

        $data['create_over'] = $_SESSION['create_over'];

        //品牌
        $data['tb_brand_create_over'] = $_SESSION['tb_brand_create_over'];
        if ($data['tb_brand_create_over']) {
            $data['tb_brand'] = $_SESSION['tb_brand'];
            $data['tb_brand_count'] = count($_SESSION['tb_brand']);
        }

        //分类
        $data['tb_category_create_over'] = $_SESSION['tb_category_create_over'];
        if ($data['tb_category_create_over']) {
            $data['tb_category'] = $_SESSION['tb_category'];
            $data['tb_category_count'] = count($_SESSION['tb_category']);
        }

        //规格
        $data['tb_spec_create_over'] = $_SESSION['tb_spec_create_over'];
        if ($data['tb_spec_create_over']) {
            $data['tb_spec1'] = $_SESSION['tb_spec1'];
            $data['tb_spec1_count'] = count($_SESSION['tb_spec1']);

            $data['tb_spec2'] = $_SESSION['tb_spec2'];
            $data['tb_spec2_count'] = count($_SESSION['tb_spec2']);
        }

        //商品
        $data['tb_item_create_over'] = $_SESSION['tb_item_create_over'];
        if ($data['tb_item_create_over']) {
            $data['tb_item'] = $_SESSION['tb_item'];
            $data['tb_item_count'] = count($_SESSION['tb_item']);
        }

        exit_json_response(array('status' => '1', 'data' => $data, 'message' => 'success'));
    }

    /**
     * @param $cids
     */
    function dl_itemprops($cids) {


//        $mdl_shop_api = new ShopApiModel();
//        $shop_data = $mdl_shop_api->get_shop_api_by_shop_code($this->shop_code);

        $api_params = $api_params = &$this->api_param ;

        if (!$cids)
            return false;

        foreach ($cids as $_k => $_v) {


            $_api_params = $api_params;
            $_api_params['cid'] = $_k;

            $taobao_itemprops_get = taobao_itemprops_get($_api_params);

            if (not_null($taobao_itemprops_get['data']['item_props']['item_prop'])) {

                $item_props = $taobao_itemprops_get['data']['item_props']['item_prop'];

                foreach ($item_props as $_item_prop) {
                    if ($_item_prop['is_sale_prop']) {
                        if ($_item_prop['is_color_prop']) {
                            //颜色属性
                            if (not_null($_item_prop['prop_values']['prop_value'])) {
                                $spec1_values = $_item_prop['prop_values']['prop_value'];
                                $this->init_spec1($spec1_values);
                            }
                        } else {
                            //尺码属性
                            if (not_null($_item_prop['prop_values']['prop_value'])) {
                                $spec2_values = $_item_prop['prop_values']['prop_value'];
                                $this->init_spec2($spec2_values);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 初始化品牌(废弃)
     * @param array $brands
     */
    function init_brands($brands = array()) {

        if (!$brands)
            return false;

        foreach ($brands as $_brand) {

            $mdl_brand = new BrandModel();
            $brand_info = $mdl_brand->is_exists($_brand['name'], 'brand_name');
            if (!$brand_info['data']) {

//				$_insert = array(
//					'brand_code' => $this->rand_brand_code(),
//					'brand_name' => $_brand['name']
//				);
//
//				$mdl_brand = new BrandModel();
//				$mdl_brand->insert($_insert);
//				$_SESSION['tb_brand'][$_brand['name']] = $_brand['name'];

                $this->init_brand($_brand['name']);
            }
        }

        return true;
    }

    /**
     * 添加品牌
     * @param $brand_name
     */
    function init_brand($brand_name) {

        $brand_code = $this->rand_brand_code();
        $_insert = array(
            'brand_code' => $brand_code,
            'brand_name' => $brand_name
        );

        $mdl_brand = new BrandModel();
        $mdl_brand->insert($_insert);
        //$_SESSION['tb_brand'][$brand_name] = $brand_name;

        return $brand_code;
    }

    /**
     * 初始化规格1
     * @param array $brands
     */
    function init_spec1($spec1 = array()) {

        if (!$spec1)
            return false;

        foreach ($spec1 as $_value) {
            $mdl_spec1 = new Spec1Model();
            $spec1_info = $mdl_spec1->is_exists($_value['name'], 'spec1_name');
            if (!$spec1_info['data']) {

                $_insert = array(
                    'spec1_code' => $this->rand_spec1_code(),
                    'spec1_name' => $_value['name']
                );

                $mdl_spec1 = new Spec1Model();
                $mdl_spec1->insert($_insert);

                //$_SESSION['tb_spec1'][$_value['name']] = $_value['name'];
            }
        }

        return true;
    }

    /**
     * 初始化规格2
     * @param array $brands
     */
    function init_spec2($spec2 = array()) {

        if (!$spec2)
            return false;

        foreach ($spec2 as $_value) {
            $mdl_spec2 = new Spec2Model();
            $spec2_info = $mdl_spec2->is_exists($_value['name'], 'spec2_name');
            if (!$spec2_info['data']) {

                $_insert = array(
                    'spec2_code' => $this->rand_spec2_code(),
                    'spec2_name' => $_value['name']
                );

                $mdl_spec2 = new Spec2Model();
                $mdl_spec2->insert($_insert);

               // $_SESSION['tb_spec2'][$_value['name']] = $_value['name'];
            }
        }

        return true;
    }

    /**
     * 初始化商品
     * @param array $goods
     */
    function init_goods($goods = array()) {

        if (!$goods)
            return false;

        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $count = count($goods);
        $num = 40 ;
        $p_num = ceil($count/40);
        foreach ($goods as $_key => $_value) {
            if(empty($_value['outer_id'])){//暂时只获取商家外部编码不为空的商品，根据商家编码判断重复
                continue;
            }
            
            $_goods_code = $this->generate_goods_code(not_null($_value['outer_id']) ? $_value['outer_id'] : null, $_value['num_iid']);
            if (false === $_goods_code) {
                continue;
            }

            $_value['goods_code'] = $_goods_code;

            $_insert = array(
                'goods_code' => $_goods_code,
                'goods_name' => $_value['title'],
                'brand_code' => $this->headle_item_brand($_value),
                'category_code' => $this->headle_item_category($_value),
                'price' => $_value['price'],
                'cost_price' => $_value['price'],
                'sell_price' => $_value['price'],
                'supplier_code' => '001', //默认供应商
                'status' => 1
            );

            $mdl_goods = new GoodsModel();
            $ret = $mdl_goods->insert($_insert);

            $insert_id = $ret['data'];

            //更新关联关系
            $mdl_api_base_item = new BaseItemModel();
            $mdl_api_base_item->update(array('goods_id' => $insert_id), array('item_id' => $_value['num_iid']));

            //解析规格，准备生成sku等信息
            $goods[$_key] = $this->headle_item_spec($_value);

            //生成商品规格，sku，barcode
            $this->init_goods_spec($goods[$_key]);
            $this->init_sku_barcode($goods[$_key]);
            $this->init_goods_price($goods[$_key]);
            $num = $num+$p_num;
            $num = ($num>100)?99:$num;
            $init->set_load($this->shop_code,$num,0);
            //$_SESSION['tb_item'][$_value['title']] = $_value['title'];
        }

        return true;
    }

    /**
     * 解析商品的品牌
     * @param $item
     */
    function headle_item_brand($item) {

        $property = $item['props_name'];
        $arr = explode(";", $property);
        $brand_name = null;

        $brand_code = null;

        foreach ($arr as $a) {
            $_arr = explode(":", $a);
            switch ($_arr[0]) {
                case "20000":
                    $brand_name = $_arr[3];
                    break;
            }

            if ($brand_name) {
                continue;
            }
        }

        $mdl_brand = new BrandModel();
        $brand_info = $mdl_brand->is_exists($brand_name, 'brand_name');
        if ($brand_info['data']) {
            $brand_code = $brand_info['data']['brand_code'];
        } else {

            //添加品牌
            $brand_code = $this->init_brand($brand_name);
        }

        return $brand_code;
    }

    /**
     * 解析商品分类
     * @param $item
     */
    function headle_item_category($item) {

        $category_code = null;
        $all_cid = $this->all_cids;
        if (array_key_exists($item['cid'], $all_cid)) {
            $category_code = $all_cid[$item['cid']];
        }

        return $category_code;
    }

    /**
     * 解析规格
     * @param $item
     */
    function headle_item_spec($item) {

        if ($item['skus']['sku']) {

            foreach ($item['skus']['sku'] as $_key => $_sku) {

                $property = $_sku['properties_name'];
                $arr = explode(";", $property);
                foreach ($arr as $a) {
                    $_arr = explode(":", $a);
                    switch ($_arr[0]) {
                        case "1627207":
                            $item['skus']['sku'][$_key]['spec1_name'] = $_arr[3];
                            $mdl_spec1 = new Spec1Model();
                            $spec1_info = $mdl_spec1->is_exists($_arr[3], 'spec1_name');
                            if ($spec1_info['data']) {
                                $item['skus']['sku'][$_key]['spec1_code'] = $spec1_info['data']['spec1_code'];
                            }

                            break;
                        case "20509":
                        case "20549":
                            $item['skus']['sku'][$_key]['spec2_name'] = $_arr[3];
                            $mdl_spec2 = new Spec2Model();
                            $spec2_info = $mdl_spec2->is_exists($_arr[3], 'spec2_name');
                            if ($spec2_info['data']) {
                                $item['skus']['sku'][$_key]['spec2_code'] = $spec2_info['data']['spec2_code'];
                            }

                            break;
                    }
                }
            }
        }

        return $item;
    }

    /**
     * 生成商品规格
     * @param $item
     */
    function init_goods_spec($item) {

        $goods_spec1_str = $goods_spec2_str = null;

        $goods_spec1_arr = $goods_spec2_arr = array();
        if ($item['skus']['sku']) {
            foreach ($item['skus']['sku'] as $_key => $_sku) {
                if (isset($_sku['spec1_code'])) {

                    if (!in_array($_sku['spec1_code'], $goods_spec1_arr)) {
                        $goods_spec1_arr[$_sku['spec1_code']] = $_sku['spec1_code'];
                    }
                }

                if (isset($_sku['spec2_code'])) {

                    if (!in_array($_sku['spec2_code'], $goods_spec2_arr)) {
                        $goods_spec2_arr[$_sku['spec2_code']] = $_sku['spec2_code'];
                    }
                }
            }
        }

        foreach ($goods_spec1_arr as $_key => $_val) {
            $goods_spec1_str .= $_val . ',';
        }

        foreach ($goods_spec2_arr as $_key => $_val) {
            $goods_spec2_str .= $_val . ',';
        }

        //插入商品规格
        $goods_spec1_str = rtrim($goods_spec1_str, ',');
        $goods_spec2_str = rtrim($goods_spec2_str, ',');

        $mdl_goods_spec1 = new GoodsSpec1Model();
        $mdl_goods_spec2 = new GoodsSpec2Model();

        $mdl_goods_spec1->save($item['goods_code'], $goods_spec1_str);
        $mdl_goods_spec2->save($item['goods_code'], $goods_spec2_str);
    }

    /**
     * 生成sku，barcode
     * @param $item
     */
    function init_sku_barcode($item) {

        $insert_arr = array();

        if ($item['skus']['sku']) {
            foreach ($item['skus']['sku'] as $_key => $_sku) {
                if (!isset($_sku['outer_id'])||empty($_sku['outer_id'])) {
                    continue;
                }

                if (isset($_sku['spec1_code']) && isset($_sku['spec2_code'])) {
                    
                    $insert_arr[$_key]['spec1_code'] = $_sku['spec1_code'];
                    $insert_arr[$_key]['spec2_code'] = $_sku['spec2_code'];
                    $insert_arr[$_key]['price'] = $_sku['price'];

                    $code = '';
                   // if (isset($_sku['outer_id']) && '' != $_sku['outer_id']) {
                        $code .= $_sku['outer_id'];
//                    } else {
//                        $code .= $_sku['sku_id'];
//                    }

                    //$insert_arr[$_key]['sku'] = $code;
                    $insert_arr[$_key]['barcode'] = $code;

//                    if (!isset($_sku['barcode']) || '' == $_sku['barcode']) {
//                        $insert_arr[$_key]['barcode'] = $code;
//                    }
              
                 //必须找到规格
                $insert_arr[$_key]['goods_code'] = $item['goods_code'];
                $insert_arr[$_key]['sku'] = $item['goods_code'].$insert_arr[$_key]['spec1_code'].$insert_arr[$_key]['spec2_code'];
                $insert_arr[$_key]['base_sku_id'] = $_sku['sku_id'];
                }
                //			unset($item['skus']['sku'][$_key]['sku_id']);//跟表的sku_id冲突
            }
        }
        if(!empty($insert_arr)){
            $mdl_sku = new SkuModel();
            $mdl_sku->_save_sku($insert_arr);

            $mdl_barcode = new GoodsBarcodeModel();
            $mdl_barcode->_save_barcode($insert_arr);
        }
    }

    /**
     * 生成goods_price
     * @param $item
     */
    function init_goods_price($item) {

        $insert_arr = array();

        if ($item['skus']['sku']) {
            foreach ($item['skus']['sku'] as $_key => $_sku) {

                if (!isset($_sku['spec1_code']) || '' == $_sku['spec1_code'] || !isset($_sku['spec2_code']) || '' == $_sku['spec2_code']) {
                    continue;
                }

                $insert_arr[$_key]['goods_code'] = $item['goods_code'];
                $insert_arr[$_key]['color_code'] = $_sku['spec1_code'];
                $insert_arr[$_key]['size_code'] = $_sku['spec2_code'];
                $insert_arr[$_key]['cost_price'] = $_sku['price'];
                $insert_arr[$_key]['sell_price'] = $_sku['price'];
            }
        }

        $mdl_goods_price = new GoodsPriceModel();
        $mdl_goods_price->insert($insert_arr);
    }

    /**
     * 初始化分类
     * @param $categorys
     */
    function dl_category($categorys) {

        if (!$categorys) {
            return false;
        }

        foreach ($categorys as $_key => $_category) {
            $categorys[$_key]['p_code'] = '0';
        }

        $this->dl_deep_category($categorys);
    }

    function dl_deep_category($categorys) {

//		$app_key = CTX()->get_app_conf('app_key');
//		$app_secret = CTX()->get_app_conf('app_secret');
//		$app_session = CTX()->get_app_conf('app_session');
//		$app_nick = CTX()->get_app_conf('app_nick');

//        $mdl_shop_api = new ShopApiModel();
//        $shop_data = $mdl_shop_api->get_shop_api_by_shop_code($this->shop_code);

        $api_params = &$this->api_param ;

        if (!$categorys) {
            return false;
        }

        foreach ($categorys as $_key => $_category) {

            //验证是否存在
            $mdl_category = new CategoryModel();
            $category_info = $mdl_category->get_info_by_category_name_and_p_code($_category['name'], $_category['p_code']);
            if (!empty($category_info['data'])) {
                $category_code = $category_info['data']['category_code'];
            } else {
                $category_code = $this->rand_cate_code();
                $_insert = array(
                    'category_code' => $category_code,
                    'p_code' => $_category['p_code'],
                    'category_name' => $_category['name']
                );
                $mdl_category = new CategoryModel();
                $mdl_category->insert($_insert);

                //$_SESSION['tb_category'][$_category['name']] = $_category['name'];
            }

            $_category['category_code'] = $category_code;

            if (!in_array($_category['cid'], $this->all_cids)) {
                $this->all_cids[$_category['cid']] = $category_code; //保存所有的cid
            }

            if ($_category['is_parent']) {

                $_api_params = $api_params;
                $_api_params['parent_cid'] = $_category['cid'];

                $taobao_itemcats_get = taobao_itemcats_get($_api_params);

                if (not_null($taobao_itemcats_get['data']['item_cats']['item_cat'])) {

                    $_c_category = $taobao_itemcats_get['data']['item_cats']['item_cat']; //下一级的分类
                    foreach ($_c_category as $__key => $__val) {
                        $_c_category[$__key]['p_code'] = $category_code;
                    }
                    //递归获取分类
                    $this->dl_deep_category($_c_category);
                }
            }
        }
    }

    /**
     * 下载在库
     * @param $taobao
     */
    function get_onsale_items() {

//		$app_key = CTX()->get_app_conf('app_key');
//		$app_secret = CTX()->get_app_conf('app_secret');
//		$app_session = CTX()->get_app_conf('app_session');
//		$app_nick = CTX()->get_app_conf('app_nick');

//        $mdl_shop_api = new ShopApiModel();
//        $shop_data = $mdl_shop_api->get_shop_api_by_shop_code($this->shop_code);
//        $api_params = $shop_data['api'];
//        
        $api_params = &$this->api_param ;
    
        $taobao_util = new taobao_util($api_params['app'], $api_params['secret'], $api_params['session'], $api_params['nick']);

        $result = array();
        $params = array();
        $params['fields'] = "cid,num_iid,modified";
        $params['page_no'] = 1;
        $params['page_size'] = 200;
        $params['order_by'] = "modified:asc";
        $total_num = 0;
        do {
            if ($params['page_no'] * $params['page_size'] < 20000) {
                $data = $taobao_util->post('taobao.items.onsale.get', $params);

                if ($data['status'] != '1') {

                    return false;
                }
                if ($params['page_no'] == 1) {
                    $total_num = $data['data']['total_results'];
                }
                foreach ($data['data']['items']['item'] as $item) {
                    array_push($result, $item);
                }
            } else {
                $last_data = end($result);
                $params['start_modified'] = $last_data['modified'];
                $params['end_modified'] = add_time();
                $params['page_no'] = 0;
            }
            $params['page_no']++;
        } while (($params['page_no'] - 1) * $params['page_size'] < $total_num);

        //	$cid = array();
        $num_iid = array();
        foreach ($result as $item) {
            array_push($num_iid, $item['num_iid']);
        }

        return $num_iid;
    }

    /**
     * 获取详细
     * @param $num_iids
     */
    function batch_get_items($arr_num_iid = array()) {

//        $mdl_shop_api = new ShopApiModel();
//        $shop_data = $mdl_shop_api->get_shop_api_by_shop_code($this->shop_code);
//        $api_params = $shop_data['api'];
        $api_params = &$this->api_param ;
        $taobao_util = new taobao_util($api_params['app'], $api_params['secret'], $api_params['session'], $api_params['nick']);
        $page_get_count = 20; //每次获取的个数
        $num_iid_count = count($arr_num_iid);
        //$page_max = ceil($num_iid_count / $page_get_count);
        
        $new_arr = array_chunk($arr_num_iid, $page_get_count);
        
        $item_datas = array(); //所有商品总数据

       
        
        $params = array();
        $params['fields'] = "detail_url,num_iid,title,barcode,cid,props_name,desc,pic_url,num,price,property_alias,sku,outer_id,postage_id,list_time,modified,approve_status,created,is_fenxiao,sku.sku_id,sku.iid,sku.num_iid,sku.properties,sku.quantity,sku.price,sku.created,sku.modified,sku.properties,sku.properties_name,sku.outer_id,sku.barcode";
        foreach($new_arr as $num_iids_arr){
            //	for ($i = 0; $i < 2; ++$i) {
//            if(($i * $page_get_count+$page_get_count)>$num_iid_count){
//                $page_get_count = $num_iid_count-$i * $page_get_count;
//            }
//            
//            $num_iids = implode(array_slice($arr_num_iid, $i * $page_get_count, $page_get_count), ',');
            $num_iids = implode(",", $num_iids_arr);
            if ($num_iids) {
                $params['num_iids'] = $num_iids;
                $data = $taobao_util->post('taobao.items.list.get', $params);
                if ($data['status'] == '1') {

                    $item_datas = array_merge($item_datas, $data['data']['items']['item']);
                }
            }
        }

        return $item_datas;
    }

    /**
     * 生成品牌CODE
     * @return string
     */
    public function rand_brand_code() {

        $code = 'brand' . strtolower($this->rand_str(4));

        $mdl_brand = new BrandModel();
        $brand_info = $mdl_brand->is_exists($code, 'brand_code');

        if (empty($brand_info['data'])) {
            return $code;
        } else {
            $this->rand_brand_code();
        }
    }

    /**
     * SP + num_iid — 商品代码
     * 如已存在out_id, 使用out_id，需判重。判重失败此商品档案暂不下载转换。
     * 如不存在out_id, 使用SP+num_iid，需判重。判重失败暂不下载转换，转换成功
     * 生成商品代码
     */
    public function generate_goods_code($outer_id, $num_iid) {

        $code = 'SP';
        if ($outer_id) {
            $code .= $outer_id;
        } else {
            $code .= $num_iid;
        }

        if (!$outer_id) {
            //回写淘宝outer_id 暂不开放
//			$app_key = CTX()->get_app_conf('app_key');
//			$app_secret = CTX()->get_app_conf('app_secret');
//			$app_session = CTX()->get_app_conf('app_session');
//			$app_nick = CTX()->get_app_conf('app_nick');
//			$status = taobao_item_update(array('app'=>$app_key, 'secret'=>$app_secret, 'session'=>$app_session, 'outer_id'=>$code,'num_iid'=>$num_iid));
        }

        $mdl_goods = new GoodsModel();
        $goods_info = $mdl_goods->is_exists($code, 'goods_code');
        if (empty($goods_info['data'])) {
            return $code;
        } else {
            //已经存在,不添加
            return false;
        }
    }

    /**
     * 生成分类CODE
     * @return string
     */
    public function rand_cate_code() {

        $code = 'cate' . strtolower($this->rand_str(4));

        $mdl_cate = new CategoryModel();
        $category_info = $mdl_cate->is_exists($code, 'category_code');

        if (empty($category_info['data'])) {
            return $code;
        } else {
            $this->rand_cate_code();
        }
    }

    /**
     * 生成规格1CODE
     * @return string
     */
    public function rand_spec1_code() {

        $code = 'c' . strtolower($this->rand_str(4));

        $mdl_spec1 = new Spec1Model();
        $spec1_info = $mdl_spec1->is_exists($code, 'spec1_code');

        if (empty($spec1_info['data'])) {
            return $code;
        } else {
            $this->rand_spec1_code();
        }
    }

    /**
     * 生成规格2CODE
     * @return string
     */
    public function rand_spec2_code() {

        $code = 's' . strtolower($this->rand_str(4));

        $mdl_spec2 = new Spec2Model();
        $spec2_info = $mdl_spec2->is_exists($code, 'spec2_code');

        if (empty($spec2_info['data'])) {
            return $code;
        } else {
            $this->rand_spec2_code();
        }
    }

    //生成随机字串,可生成校验码, 默认长度4位,0 字母和数字混合,1 数字,-1 字母
    function rand_str($len = 4, $only_digit = 0) {
        switch ($only_digit) {
            case -1:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 1:
                $chars = str_repeat('0123456789', 3);
                break;
            default :
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; //rm 0,o
                break;
        }
        if ($len > 10)
            $chars = $only_digit == 0 ? str_repeat($chars, $len) : str_repeat($chars, 5); //位数过长重复字符串一定次数
        $chars = str_shuffle($chars);
        return substr($chars, 0, $len);
    }

    ###########初始化库存########## 

    /**
     * 初始化库存进度页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function create_init_inv_html(array &$request, array &$response, array &$app) {
        $app['tpl'] = 'sys/auto_init_inv_progress';
        $response['shop_code'] = $request['shop_code'];
        $response['store_code'] = $request['store_code'];

        $response['_rand_code'] = rand(); //每次关掉页面，页面元素没有重置，目前这样处理，保证id不重复
    }
    
    
    function do_init_goods_inv(array &$request, array &$response, array &$app) {
         $shop_code = &$request['shop_code'];
//        $mdl_shop_api = new ShopApiModel();
//        $ret = $mdl_shop_api->get_shop($shop_code);
//        $this->shop_code = $shop_code;
//        
//        $parameter['app'] = $ret['data']['api']['app_key'];
//        $parameter['secret'] = $ret['data']['api']['app_secret'];
//        $parameter['session'] = $ret['data']['api']['session'];
//        $parameter['nick'] = $ret['data']['api']['nick'];
 //       $this->api_param = $parameter;
        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $ret_init = $init->get_info($shop_code);       
        $status =  $ret_init['data']['inv_status'] ;   
          $response = array('status'=>-1);
        if($status == 1){ 
            $n_request = array('shop_code'=>$shop_code,'store_code'=>$ret_init['data']['store_code']);
            $ret = $this->pre_do_init_inv($n_request,$response,$app);
            $init->set_load($shop_code,10,0);
            if($ret['status']>0){
                 $n_request = $ret['data'];
                 $n_request['shop_code'] = $shop_code;
                 $n_request['store_code'] = $ret_init['data']['store_code'];
                 $this->do_init_inv($n_request, $response, $app) ;
                 $init->set_load($shop_code,60,0);
                 $init->save_info($shop_code, array('inv_status'=>2,'goods_message'=>$ret['data']['stock_adjust_record_id']));
                 $response = array('status'=>100);
            }else{
                 $init->save_info($shop_code, array('inv_status'=>9,'inv_message'=>$ret['message']));
                 $response = array('status'=>-1);
            }
            
        }else if($status == 2){
            $mdl_stock_adjust = new StockAdjustRecordModel();
            $stock_adjust_record_id = $ret_init['data']['goods_message'];
            $ret = $mdl_stock_adjust->checkin($stock_adjust_record_id);
            if($ret['status']>0){
                $init->set_load($shop_code,100,0);
                $init->save_info($shop_code, array('inv_status'=>3));
            }else{
                $init->save_info($shop_code, array('inv_status'=>9,'inv_message'=>$ret['message']));
            }
            $response = array('status'=>1);
        }
  
    }
    

    /**
     * 预处理初始化库存
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function pre_do_init_inv(array &$request, array &$response, array &$app) {

        $shop_code = $request['shop_code'];
        $store_code = $request['store_code'];

        $mdl_base_item = new BaseItemModel();
        $item_list = $mdl_base_item->get_list_by_is_generate_inv($shop_code, 0);

        if (!$item_list['data']) {
           // exit_json_response(-1, array(), '没有要初始化库存的商品!');
            $response['status'] = -1;
            $response['message'] = '没有要初始化库存的商品!';
            return $response;
        }

        $num_per_time = 5; //每次处理20个商品

        $item_count = count($item_list['data']);

        $times = ceil($item_count / $num_per_time); //总共要处理的次数

        $mdl_stock_adjust = new StockAdjustRecordModel();
//		$mdl_stock_adjust_detail = new StmStockAdjustRecordDetailModel();
        //添加调整单
        $stock_adjust = array(
            'record_code' =>$mdl_stock_adjust->create_fast_bill_sn(),
            'init_code' => '',
            'record_time' => date('Y-m-d'),
            'adjust_type' => '800',
            'store_code' => $store_code
        );

        $ret = $mdl_stock_adjust->insert($stock_adjust);

        $stock_adjust_record_id = $ret['data'];

        $data = array(
            'stock_adjust_record_id' => $stock_adjust_record_id,
            'num_per_time' => $num_per_time,
            'item_count' => $item_count,
            'times' => $times
        );

      $response = array('status'=>1,'data'=>$data);
      return $response;
    }

    /**
     * 处理初始化库存
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_init_inv(array &$request, array &$response, array &$app) {
        require_model('base/ShopInitModel');
        $init = new ShopInitModel();
        $app['fmt'] = 'json';
        $shop_code = &$request['shop_code'];
        $stock_adjust_record_id = $request['stock_adjust_record_id'];
        $num_per_time = $request['num_per_time'];
        $item_count = $request['item_count'];
        //$times = $request['times'];
        //$index = $request['index'];

        $shop_code = $request['shop_code'];
        $store_code = $request['store_code'];
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $lof_no = $ret_lof['data']['lof_no'];
        $production_date = $ret_lof['data']['production_date'];
        
        $mdl_base_item = new BaseItemModel();
        $mdl_base_sku = new BaseSkuModel();
        $page = 0;
        $num = 10 ;
        $page_max = ceil($item_count/100);
        $p_num = ceil($item_count/(100*50));
        while($page<$page_max){
            //获取base_base
            $page++;
            $filer = array('shop_code' => $shop_code, 'page_size' => 100, 'is_generate_inv' => '0','page'=>$page);
            $item_list = $mdl_base_item->get_main_by_page($filer);
         
            if(empty($item_list['data'])){
                break;
            }
            
            $item_ids = array();

            if ($item_list['data']) {
                foreach ($item_list['data']['data'] as $_item) {
                    $item_ids[] = $_item['base_item_id'];
                }
            }
           
            $sku_list = $mdl_base_sku->get_goods_sku_list_by_item_ids($item_ids);

            $_insert_stock_adjust_detail = array();
            $_insert_stock_adjust_detail_lof = array();
            foreach ($sku_list as $_key => $_sku) {
                $_insert_stock_adjust_detail[$_key]['goods_code'] = $_sku['goods_code'];
                $_insert_stock_adjust_detail[$_key]['spec1_code'] = $_sku['spec1_code'];
                $_insert_stock_adjust_detail[$_key]['spec2_code'] = $_sku['spec2_code'];
                $_insert_stock_adjust_detail[$_key]['sell_price'] = $_sku['price'];
                $_insert_stock_adjust_detail[$_key]['num'] = $_sku['quantit'];
                $_insert_stock_adjust_detail[$_key]['sku'] = $_sku['sku'];

                $_insert_stock_adjust_detail_lof[$_key] = $_insert_stock_adjust_detail[$_key];
                unset($_insert_stock_adjust_detail_lof[$_key]['sell_price']);
                $_insert_stock_adjust_detail_lof[$_key]['lof_no'] = $lof_no;
                $_insert_stock_adjust_detail_lof[$_key]['production_date'] = $production_date;
                $_insert_stock_adjust_detail_lof[$_key]['store_code'] = $store_code;
                $_insert_stock_adjust_detail_lof[$_key]['pid'] = $stock_adjust_record_id;
                $_insert_stock_adjust_detail_lof[$_key]['order_type'] = 'adjust';

            }

            //添加单据明晰
            $mdl_stock_adjust_detail = new StmStockAdjustRecordDetailModel();
            $mdl_stock_adjust_detail->add_detail_action($stock_adjust_record_id, $_insert_stock_adjust_detail);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($stock_adjust_record_id,$store_code,'adjust',$_insert_stock_adjust_detail_lof);
        
            
            $num = $num+$p_num;
            $num = ($num>60)?59:$num;
            $init->set_load($shop_code,$num,0);
          }
  

//        if (($index + 1) == $times) {
//
//            //验收
//            $mdl_stock_adjust = new StockAdjustRecordModel();
//            $mdl_stock_adjust->checkin($stock_adjust_record_id);
//
//            exit_json_response(2, array(), '初始化完成!');
//        }
          $response['status'] = 1;
      
    }

}