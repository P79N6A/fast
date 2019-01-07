<?php
/**
upload_request_flag [0未上传 10上传中]
upload_response_flag [10上传成功 20上传失败]
cancel_request_flag [0未取消 10取消中]
cancel_response_flag [10取消成功 20取消失败]
process_flag [处理的数据wms_order_flow_end_flag = 1 （0 未处理 20 处理失败 30处理成功）]
wms_order_flow_end_flag [0 未收发货 1 已收发货 -1单据WMS关闭]

未上传 上传中 上传失败 上传成功
未取消 取消中 取消失败 取消成功(客服执行取消 WMS缺货取消 WMS关闭取消)

pur_notice,pur_return_notice,
wbm_notice,wbm_return_notice,
shift_out,shift_in,
sell_record,sell_return
*/
require_model('tb/TbModel');
class WmsBaseModel extends TbModel {
	var $kh_id;
	var $wms_address;

	var $sys_user;//当前操作的用户
	var $api_sync;//wms 是否是同步模式 为1 是同步 0 是异步

	var $cancel_after_invalid_order;//取消单据后，是否要作废原单据
	var $goods_spec1_name;//规格1中文名
	var $goods_spec2_name;//规格2中文名

	var $default_lof_no;
	var $default_lof_production_date;

	var $wms_cfg;
	var $api_product;
	var $efast_store_code;
	var $wms_store_code;
        var   $cp_record_type_arr = array('pur_return_notice','wbm_return_notice','sell_return');
	var $exists_wms_store_code_arr;

	function __construct()
	{
		parent::__construct();
		$this->record_type_map = array(
			'sell_record'=>'订单',
			'sell_return'=>'退单',
			'pur_notice'=>'采购通知单',
			'pur_return_notice'=>'采购退货通知单',
			'wbm_notice'=>'批发通知单',
			'wbm_return_notice'=>'批发退货通知单',
			'shift_out'=>'移仓出库单',
			'shift_in'=>'移仓入库单',
		);
		$this->cancel_after_invalid_order = 0;
		$ret = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1','goods_spec2'));
                $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();

		$this->goods_spec1_name = $ret['goods_spec1'];
		$this->goods_spec2_name = $ret['goods_spec2'];
		$this->default_lof_no = $ret_lof['data']['lof_no'];
		$this->default_lof_production_date = $ret_lof['data']['production_date'];
	}

	function get_exists_wms_store_code_arr($record_type = ''){
		if (!isset($this->exists_wms_store_code_arr)){
                    $sql = "select shop_store_code from sys_api_shop_store where p_type=1 AND shop_store_type=1 AND store_type = 1";


                     if(in_array($record_type, $this->cp_record_type_arr)){
                        $sql = "select shop_store_code from sys_api_shop_store where p_type=1 AND shop_store_type=1 AND (store_type = 1 or store_type = 0)";
                     }

			$this->exists_wms_store_code_arr = ctx()->db->get_all_col($sql);
		}
		return $this->exists_wms_store_code_arr;
	}

	function get_sys_user(){
		if (!isset($this->sys_user)){
			$this->sys_user = $this->set_sys_user();
		}
		return $this->sys_user;
	}
        function set_sys_user(){
	    if (php_sapi_name() == 'cli'){
	        return array(
	            'user_id' => -1,
	            'user_code' => 'sys_schedule',
	            'user_name' => '系统定时器',
	            'is_manage'=>1,
	        );
	    }


        $role = ctx()->get_session('role');
        if(empty($role)){
               return array(
	            'user_id' => -1,
	            'user_code' => 'ydwms_api',
	            'user_name' => '韵达API接口',
	            'is_manage'=>1,
	        );
        }


        $role_list = $role['data'];
        $is_manage = 0;
        foreach($role_list as $sub_row){
            if ($sub_row['role_code'] == 'manage'){
               $is_manage = 1;
               break;
            }
        }
        return array(
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'user_name' => CTX()->get_session('user_name'),
            'is_manage'=>$is_manage,
        );
    }
	function sys_user_name(){
            $this->sys_user = $this->get_sys_user();
            return $this->sys_user['user_name'];
	}

	function get_wms_cfg($efast_store_code,$record_type=''){
            if(isset($this->wms_cfg['efast_store_code'])&&$this->wms_cfg['efast_store_code']!=$efast_store_code){
                $this->wms_cfg = null;
            }

            if (!isset($this->wms_cfg)||empty($this->wms_cfg)){
                $auth_data = load_model('sys/SysAuthModel')->get_auth();
                $kh_id = $auth_data['kh_id'];
              //  $cp_record_type_arr = array('pur_return_notice','wbm_return_notice','sell_return');
                $sql = "SELECT
                            t2.wms_config_id,
                            t2.wms_system_code,
                            t2.wms_address,
                            t2.wms_params,
                            t2.wms_system_type,
                            t2.wms_prefix,
                            t1.outside_code,
                            t1.store_type
                        FROM
                            sys_api_shop_store t1,
                            wms_config t2
                        WHERE
                            t1.p_id = t2.wms_config_id
                        AND t1.p_type=1
                        AND t1.shop_store_code = :efast_store_code
                        AND t1.shop_store_type = 1";
                if(in_array($record_type, $this->cp_record_type_arr)){
                    $sql .= " AND (t1.store_type = 1 or t1.store_type = 0 )";
                } else {
                    $sql .= " AND t1.store_type = 1";
                }
                $db_wms = ctx()->db->get_row($sql,array(':efast_store_code'=>$efast_store_code));
                $db_wms['wms_params'] = isset($db_wms['wms_params']) ? $db_wms['wms_params'] : '';
                $db_wms['wms_address'] = isset($db_wms['wms_address']) ? $db_wms['wms_address'] : '';
                $db_wms['wms_system_code'] = isset($db_wms['wms_system_code']) ? $db_wms['wms_system_code'] : '';
                $db_wms['outside_code'] = isset($db_wms['outside_code']) ? $db_wms['outside_code'] : '';
                $wms_params = (array)json_decode($db_wms['wms_params'],true);
                $this->wms_cfg = array( 'wms_address'=>$db_wms['wms_address'],
                                        'api_product'=>$db_wms['wms_system_code'],
                                        'kh_id'=>$kh_id,
                                        'wms_config_id'=>$db_wms['wms_config_id'],
                                        'wms_store_code'=>$db_wms['outside_code'],
                                        'product_type' => $db_wms['wms_system_type'],
                                        'prefix' => $db_wms['wms_prefix'],
                                      );
                $this->wms_cfg = array_merge($this->wms_cfg,$wms_params);
                $this->wms_cfg['store_type'] =  $db_wms['store_type'];
            }
            $this->api_sync = 1;
            $this->api_product = $this->wms_cfg['api_product'];
            $this->efast_store_code = $efast_store_code;
            $this->wms_cfg['efast_store_code'] =  $this->efast_store_code ;


            $this->wms_store_code = $this->wms_cfg['wms_store_code'];
            $conf = require_conf("sys/wms");
            if(isset($conf[$this->api_product])){
                $this->wms_cfg = array_merge($conf[$this->api_product],$this->wms_cfg);
            }
            if (in_array($this->api_product, array('qimen'))) {
                $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status','wms_split_goods_source'));
                $this->wms_cfg['is_lof'] = $lof_status['lof_status'];
                $this->wms_cfg['wms_split_goods_source'] = $lof_status['wms_split_goods_source'];//wms商品按配置上传
            }

        return $this->wms_cfg;
	}

        function get_store_code_by_out_store_code($out_store_code){
            $sql = "select shop_store_code from sys_api_shop_store where outside_code='{$out_store_code}' AND p_type=1 and shop_store_type=1 AND outside_type=1 ";
            return   $this->db->get_value($sql);
       }

    /**
     * 获取wms仓库
     * @param array $store_arr 系统仓库
     * @param string $wms_system wms产品
     */
    public function get_outside_code($store_arr, $wms_system) {
        $sql_values = array(':wms_system_code' => $wms_system);
        $store_str = $this->arr_to_in_sql_value($store_arr, 'shop_store_code', $sql_values);
        $sql = "SELECT ss.shop_store_code,ss.outside_code FROM wms_config wc
            INNER JOIN sys_api_shop_store ss ON wc.wms_config_id = ss.p_id
            WHERE wc.wms_system_code=:wms_system_code AND ss.p_type = 1 AND ss.shop_store_type = 1 AND ss.outside_type = 1 AND ss.shop_store_code IN($store_str)";
        $ret = $this->db->get_all($sql, $sql_values);
        $data = array_column($ret, 'outside_code', 'shop_store_code');
        return $data;
    }


        function get_wms_cfg_by_type($wms_type){
            if (!isset($this->wms_cfg)){
                $auth_data = load_model('sys/SysAuthModel')->get_auth();
                $kh_id = $auth_data['kh_id'];
                $sql = "SELECT
                            t1.shop_store_code,
                            t2.wms_system_code,
                            t2.wms_address,
                            t2.wms_params,
                            t1.outside_code
                        FROM
                            sys_api_shop_store t1,
                            wms_config t2
                        WHERE
                            t1.p_id = t2.wms_config_id
                        AND t1.p_type=1
                        AND t2.wms_system_code = :wms_type
                        AND t1.shop_store_type = 1";
                if($wms_type=='ydwms'||$wms_type=='qimen'){//默认查询正品仓库
                    $sql.="   AND t1.store_type = 1 ";
                }
                $db_wms = ctx()->db->get_row($sql,array(':wms_type'=>$wms_type));
                $wms_params = (array)json_decode($db_wms['wms_params'],true);
				$this->wms_cfg = array('wms_address'=>$db_wms['wms_address'],
                                       'api_product'=>$db_wms['wms_system_code'],
                                       'kh_id'=>$kh_id,
                                       'wms_store_code'=>$db_wms['outside_code'],
                                      );
                $efast_store_code = $db_wms['shop_store_code'];
                $this->wms_cfg = array_merge($this->wms_cfg,$wms_params);
            }
            $this->api_sync = 1;
            $this->api_product = $this->wms_cfg['api_product'];
            $this->efast_store_code = $efast_store_code;
            $this->wms_store_code = $this->wms_cfg['wms_store_code'];
            $conf = require_conf("sys/wms");
            $this->wms_cfg = array_merge($conf[$this->api_product],$this->wms_cfg);
            return $this->wms_cfg;
        }

        function get_cp_store_code($store_code){
               $sql1 = "SELECT	s.p_id,s.outside_code  FROM wms_config c
                INNER JOIN sys_api_shop_store s ON s.p_id = c.wms_config_id
                    WHERE s.shop_store_code=:store_code AND s.p_type = 1  AND s.outside_type=1";
            $row = $this->db->get_row($sql1,array(':store_code'=>$store_code));
            
            
            $sql = "select shop_store_code from sys_api_shop_store where  p_id = :p_id  AND  outside_code = :outside_code  AND store_type=0 AND p_type = 1 AND outside_type=1  ";
            $data = $this->db->get_row($sql,array(':p_id'=>$row['p_id'],':outside_code'=>$row['outside_code']));
            $cp_store_code = $store_code;

            if(!empty($data)){
                $cp_store_code = $data['shop_store_code'] ;
            }
            return $cp_store_code;
       }


	function getWmsProduct()
	{
		return array('iwms' => '百胜IWMS','bswl' => '百世物流','flux' => '富勒物流','xxwms' => '北京兴信物流','vinwms' => '德华物流','shunfeng'=>'顺丰物流','jd'=>'京东物流','jd2'=>'京东物流','sto'=>'申通物流','ydwms');
	}
        function get_sys_config_wms_product(){
            $sql = "SELECT
                        t1.shop_store_code,
                        t2.wms_system_code
                    FROM
                        sys_api_shop_store t1,
                        wms_config t2
                    WHERE
                        t1.p_id = t2.wms_config_id
                    and t1.p_type=1  AND t1.store_type = 1
                    AND t1.shop_store_type = 1";
            return $this->db->get_all($sql);

        }
        function get_sys_config_wms_by_config(){

              $sql = "SELECT
                        t1.shop_store_code,
                        t2.wms_system_code
                    FROM
                        sys_api_shop_store t1,
                        wms_config t2
                    WHERE
                        t1.p_id = t2.wms_config_id
                    and t1.p_type=1 AND t1.store_type = 1
                    AND t1.shop_store_type = 1 GROUP BY t2.wms_config_id";
            return $this->db->get_all($sql);
        }



        //获取统一待上传任务(页面)
	function uploadtask_list_get($task_type,$task_status)
	{
		$sql = "SELECT id,record_code,record_type,ckdm FROM wms_oms_trade WHERE upload_request_flag=0";
		return $this->db->getAll($sql);
	}


        /*重复方法废弃*/
        /*
	function order_shipping($task_id,$user_id,$user_name){
		$sql = "select record_code,record_type,shipping_name,invoice_no,wms_order_flow_end_flag,wms_order_time,process_flag,dj_create_wms_flag,order_weight from wms_oms_trade where id = $task_id";
		$awt_row = $this->db->getRow($sql);
		$record_code = $awt_row['record_code'];
		$record_type = $awt_row['record_type'];
		$shipping_name = $awt_row['shipping_name'];
		$invoice_no = $awt_row['invoice_no'];
		$wms_order_flow_end_flag = $awt_row['wms_order_flow_end_flag'];
		$shipping_time = $awt_row['wms_order_time'];
		$process_flag = $awt_row['process_flag'];
		$dj_create_wms_flag = $awt_row['dj_create_wms_flag'];
		$order_weight = $awt_row['order_weight'];
		if($wms_order_flow_end_flag != 1){
			return $this->format_ret(-1,'','单据不是收发货状态');
		}
		if($process_flag == 30){
			return $this->format_ret(-1,'','EFAST单据已同步WMS单据状态');
		}
		require_moudle("openapi/models/wms/Wms_{$record_type}.mdl");
		$class_name = "Wms_".$record_type;
		$obj = new $class_name();
		if($dj_create_wms_flag == 1){
			$class_fn_name = "order_create_".$record_type;
			$ret = $obj->$class_fn_name($record_code,$record_type);
			if($ret['status']<0){
				$err_arr = $obj->get_error();
				$err_msg = $err_arr['msg'];
				$this->uploadtask_order_status_sync_fail($record_code,$record_type,$user_id,$user_name,$err_msg);
				return $this->format_ret(-1,'',$err_msg);
			}
		}

		$class_fn_name = "order_shipping_".$record_type;
		$ret = $obj->$class_fn_name($record_code,$shipping_time,$shipping_name,$invoice_no,$user_id,$user_name,$order_weight);

		if($ret['status']<0){
			$err_arr = $obj->get_error();
			$err_msg = $err_arr['msg'];
			$this->uploadtask_order_status_sync_fail($record_code,$record_type,$user_id,$user_name,$err_msg);
			return $this->format_ret(-1,'',$err_msg);
		}else{
			$this->uploadtask_order_status_sync_success($record_code,$record_type,$user_id,$user_name);
		}
		return $ret;
	}
*/
	//订单状态获取(如果是中间件对接的这个接口，则调用这个方法，用于显示WMS订单详情用)
	function result_get($task_id,$user_id,$user_name)
	{
		$sql = "select * from wms_oms_trade where id = $task_id";
		$awt_row = $this->db->getRow($sql);
		$record_code = $awt_row['record_code'];
		$record_type = $awt_row['record_type'];

		$status_ret = $this->get_status_exp($awt_row);

		$ret['order_status_txt'] = $status_ret['status_txt'];
		$ret['shipping_name'] = $awt_row['shipping_name'];
		$ret['invoice_no'] = $awt_row['invoice_no'];
		$ret['flow_end_time'] = $awt_row['wms_order_time'];
		$ret['wms_djbh'] = $awt_row['wms_djbh'];
		$ret['record_code'] = $awt_row['record_code'];
		//echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';

		$sql = "select sku,wms_num as sl from api_wms_order where record_code = '{$record_code}' and record_type = '{$record_type}'";
		$ret['skus'] = $this->db->getAll($sql);

		//转换成标准格式返回
		return $ret;
	}

    /* 121.41.164.153/webapi/?app_act=api/base/proxy_wms --测试
    * 121.41.163.99/fastapp/webapi/web/?app_act=api/base/proxy_wms -- 正式
    http://localhost/webapi/web/?app_act=api/base/proxy_wms
    http://127.0.0.1/webapi/web/?app_act=api/base/proxy_wms
    */
        //旧的废弃
	function biz_req22($method,$params){
		//echo '<hr/>biz_req<xmp>'.var_export($this->wms_cfg,true).'</xmp>';
		$kh_id = $this->wms_cfg['kh_id'];
		$sid = 'efast5';
		$source = $this->api_product;
		$array = array(
			'store_code' => $this->efast_store_code,
			'api' => $method,
			'param' => $params,
		);

		$url = "http://121.41.163.99/fastapp/webapi/web/?app_act=api/base/proxy_wms";

        /*
		echo '<hr/>$url<xmp>'.var_export($url,true).'</xmp>';
		echo '<hr/>$kh_id<xmp>'.var_export($kh_id,true).'</xmp>';
		echo '<hr/>$source<xmp>'.var_export($source,true).'</xmp>';
		echo '<hr/>$array<xmp>'.var_export($array,true).'</xmp>';
		die;*/

        $_log_file = ROOT_PATH.'webefast/logs/iwms_api.log';
        $start_t = time();
        $_tag = date('Y-m-d H:i:s').'#'.uniqid();
        error_log("\n--{$_tag} url--\n".$url,3,$_log_file);
        error_log("\n--{$_tag} params--\n".var_export($array,true),3,$_log_file);

		require_lib('apiclient/Validation');
		$ret = Validation::send($url, $kh_id, $sid, $source, $array);
        $_use_time = time() - $start_t;
        error_log("\n--{$_tag} ret--\n".var_export($ret,true),3,$_log_file);

		if (isset($ret['status']) && $ret['status']<0){
			return $ret;
		}
		/*
		echo '<hr/>$array<xmp>'.var_export($array,true).'</xmp>';
		echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
		*/
		$resp = $ret['response'];
		$pos = strpos($resp,'{');
		if ($pos>0){
			$resp = substr($resp,$pos);
		}
		$result = json_decode($resp,true);
		if (empty($result)){
			return $this->format_ret(-1,'','接口返回数据有错.'.$resp);
		}
		if (isset($result['resp_error']['app_err_msg'])){
			return $this->format_ret(-1,'','接口返回数据有错.'.$result['resp_error']['app_err_msg']);
		}
		if ($result['flag']<>'ACK'){
			return $this->format_ret(-1,$result['data'],'接口返回数据有错.'.$result['data']['msg']);
		}
		return $this->format_ret(1,$result['data']);
	}

        function biz_req($method,$params){

		$source = $this->api_product;


                $mod = $this->get_wms_api_mod($source, $this->efast_store_code);
                if($mod===false){
                      return $this->format_ret(-1,'','WMS参数异常');
                }


                $ret = $mod->request_send($method, $params);



		return $ret;
        }




	// biz_code = iwms_stock_sync | 增量库存同步 iwms_quehou_sync iwms缺货
	function get_incr_service_end_time($efast_store_code,$biz_code){
		$sql = "select end_time from wms_incr_time_tag where efast_store_code = :efast_store_code and biz_code = :biz_code AND status=1 order by end_time desc";
		$db_row = ctx()->db->get_row($sql,array(':efast_store_code'=>$efast_store_code,':biz_code'=>$biz_code));
		$db_end_time = empty($db_row) ? null : $db_row['end_time'];
		return $db_end_time;
	}

	//取当天同步失败的时间段
	function get_incr_service_fix_time($efast_store_code,$biz_code,$hours_limit = 24){
		$start_time_limit = time() - 24 * 60;
		$sql = "select start_time,end_time from wms_incr_time_tag where efast_store_code = :efast_store_code and biz_code = :biz_code and start_time>:start_time_limit and status = 0";
		$db_arr = ctx()->db->get_all($sql,array(':efast_store_code'=>$efast_store_code,':biz_code'=>$biz_code,':start_time_limit'=>$start_time_limit));
		return $db_arr;
	}


        public function get_wms_api_mod($source,$store_code){
            static $m = null;
            if(!isset($m[$store_code])){
                $token = $this->get_token_by_store_code($store_code);
                if(empty($token)||empty($source)){
                    $m[$store_code] = FALSE;
                }else{
                   $m[$store_code] =  $this->Factory($source,$token);
                }
            }
            return $m[$store_code];

        }

        public function Factory($source, $token=array()){
            $Source = ucfirst($source); //将iwms转化为Iwms
            $className = $Source.'APIModel';
            $r = require_model('wms/'. strtolower($source).'/'.$className);
            $M = FALSE;
            if($r!==FALSE){
            $M = new $className($token);
            }
            return $M;
        }

        public function  get_token_by_store_code($store_code){
           	$sql = 'SELECT
                            wc.wms_params,
                            ss.shop_store_code,
                            ss.outside_code
                        FROM
                            sys_api_shop_store AS ss
                        LEFT JOIN
                            wms_config AS wc ON ss.p_id = wc.wms_config_id
                        WHERE
                            ss.shop_store_code =:store_code
                        AND ss.p_type = 1
                        AND ss.shop_store_type = 1';
		$data = CTX()->db->get_row($sql, array(':store_code'=>$store_code));
		$api = empty($data['wms_params']) ? array() : json_decode($data['wms_params'], true);
                $data['outside_code'] = isset($data['outside_code']) ? $data['outside_code'] : '';
                $api['outside_code'] = $data['outside_code'];
		return $api;
        }
      public function get_wms_store_by_store_code($store_code){
             $sql = "SELECT  wms_config_id FROM wms_config
            c INNER JOIN sys_api_shop_store s
            ON c.wms_config_id = s.p_id AND    s.p_type=1
            WHERE s.shop_store_code=:store_code ";
            $pid = $this->db->get_value($sql,array(':store_code'=>$store_code));
            $store_arr = array();
            if(!empty($pid)){
                $sql = "select shop_store_code from sys_api_shop_store where p_id=:pid AND  p_type=1";
                $data = $this->db->get_all($sql,array(':pid'=>$pid));
                foreach ($data as $val){
                    $store_arr[]= $val['shop_store_code'];
                }
            }
            return $store_arr;


        }

    /**
     * 获取iwms/iwmscloud配置的店铺ID
     * @param int $wms_config_id
     * @param array $shop_code
     * @return int
     */
    public function get_wms_shop_id($wms_config_id, $shop_code) {
        $sql_tpl = "SELECT
                        if(ss.outside_code IS NULL,bs.shop_id,ss.outside_code) AS shop_code
                    FROM base_shop AS bs
                    LEFT JOIN sys_api_shop_store AS ss ON bs.shop_code = ss.shop_store_code AND ss.shop_store_type = 0 
                    AND ss.outside_type = 0 AND ss.p_type = 1 AND p_id = :p_id
                    WHERE bs.shop_code=:shop_code ORDER BY bs.lastchanged";
        return $this->db->get_value($sql_tpl, array(':p_id' => $wms_config_id, ':shop_code' => $shop_code));
    }

}