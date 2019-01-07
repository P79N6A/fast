<?php
/**
 * 顺丰热敏
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('api');
require_lib('util/taobao_util', true);

class ShunfengModel extends TbModel {

	protected $table = "shunfeng_rm_config";
	public $express_type = array(
        '1' => '顺丰次日',
        '2' => '顺丰隔日',
        '3' => '电商特惠',
        '7' => '电商速配',
        '15' => '生鲜速配',
        '37' => '云仓专配次日',
        '38' => '云仓专配隔日',
        '6' => '顺丰即日',
        '5' => '顺丰次晨',
    );
        public $check_city = array(
            '469000000000',
            '429000000000',
            '419000000000',
            '659000000000'
        );
        public $check_district = array(
            '420205198000'
        );

    public $express_type_code =array(
        'SFCR' => '1',//顺丰次日
    );

	function get_by_id($id) {

		return  $this->get_row(array('id'=>$id));
	}
    /*
     * 根据条件查询数据
    */
    function get_j_custid_list($filter){
    	$sql = "select * from  shunfeng_rm_config where 1 ";
    	$sql_values = array();
    	if(isset($filter['express_id']) && !empty($filter['express_id'])) {
    		$sql .= " and express_id=:express_id";
    		$sql_values[':express_id'] = $filter['express_id'];
    	}

    	$data = $this->db->get_all($sql,$sql_values);
    	$ret_status = OP_SUCCESS;
    	$ret_data['data'] = $data;
    	return $this->format_ret($ret_status, $ret_data);
    }

    /*
     * 删除记录
    * */
    function delete($id) {
    	$ret = parent::delete(array('id'=>$id));
    	return $ret;
    }

    function get_j_custid($j_custid="",$express_code=""){
    	$sql = "select * from  shunfeng_rm_config where 1  ";
    	$sql_values = array();
    	if (!empty($express_code)){
    		$sql .= " and express_code=:express_code";
    		$sql_values[':express_code'] = $express_code;
    	}
    	if (!empty($j_custid)){
    		$sql .= " and j_custid=:j_custid";
    		$sql_values[':j_custid'] = $j_custid;
    	}
    	$ret = $this->db->get_all($sql,$sql_values);
    	foreach ($ret as $row) {
    		$key = $row['express_code']."_".$row['j_custid'];
    		$data[$key] = $row;
    	}
    	return $data;

    }
    function check_exists_j_custid($j_custid,$express_code,$id = ""){
    	$sql = "select * from  shunfeng_rm_config where 1 and express_code=:express_code and j_custid=:j_custid  ";
    	$sql_values[':express_code'] = $express_code;
    	$sql_values[':j_custid'] = $j_custid;
    	if (!empty($id)){
    		$sql .= " and id !=:id";
    		$sql_values[':id'] = $id;
    	}

    	$ret = $this->db->get_all($sql,$sql_values);
    	return $ret;
    }
    /**
     * 新增月结账号
     *
     */
    function add_config($request){

    	$ret_shipping =load_model('base/ShippingModel')->get_by_id($request['express_id']);
    	$express_code = $ret_shipping['data']['express_code'];
    	$data = array(
    			'express_id' => $request['express_id'],
    			'express_code' => $express_code,
    			'j_custid' => $request['j_custid'],
    			'checkword' => $request['checkword'],
    			'api_url' => trim($request['api_url']),
    			);
    	$ret = $this->check_exists_j_custid($request['j_custid'],$express_code);
    	if (!empty($ret)) {
    		return $this->format_ret(-1,'','同一月结账号不能重复添加');
    	}
    	return $this->insert($data);

    }

    /**
     * 编辑月结账号
     *
     */
    function edit_config($request){
    	$config_ret = $this->get_by_id($request['id']);
    	$data = array(
    			'j_custid' => $request['j_custid'],
    			'checkword' => $request['checkword'],
    			'api_url' => trim($request['api_url']),
    	);
    	$ret = $this->check_exists_j_custid($request['j_custid'],$config_ret['data']['express_code'],$request['id']);
    	if (!empty($ret)) {
    		return $this->format_ret(-1,'','月结账号已存在');
    	}
    	return $this->update($data, array('id' => $request['id']));

    }

    function get_express_no($request){
    	$waves_record_id = $request['waves_record_id'];
    	$idStr = implode(',', $request['record_ids']);
    	//读取拣货单, 根据拣货单编号
    	$sql = "select * from oms_waves_record
        where is_cancel = 0 and waves_record_id = :waves_record_id";
    	$pickup = $this->db->get_row($sql, array('waves_record_id' => $waves_record_id));
    	if (empty($pickup)) {
    		return return_value(-1, '拣货单不存在');
    	}
    	//读取订单
    	$sql = "select a.* from oms_deliver_record a
	    	where a.is_cancel = 0 and a.waves_record_id = :waves_record_id and a.deliver_record_id IN ($idStr)
	    	and a.express_no = '' ";
    	$sellRecordAll = $this->db->get_all($sql, array('waves_record_id' => $pickup['waves_record_id']));
    	if (empty($sellRecordAll)) {
    		return array('status' => -1, 'message' => '请选择支持顺丰热敏的未匹配订单');
    	}
    	$sql = "select express_code from base_express where company_code='SF'";
    	$sf_express = $this->db->get_col($sql);
    	$error_express_sell = "";
    	$error_get_sell = "";
    	$succ_get_sell = "";
    	$shop_arr = array();
    	$store_arr = array();
    	$sf_updata = array();
    	$j_custid_arr = $this->get_j_custid();
        $services_item = array();
        //是否保价
        if ($request['bj'] == 1){
            $services_item[] = array(
                    'service_name' => '保价',
                    'attribute1' => $request['bj_je'],
                );

        }
         //是否保鲜服务
        if ($request['bx'] == 1){
            $services_item[] = array(
                    'service_name' => 'FRESH',
                );
        }

    	foreach ($sellRecordAll as $sellRecord) {
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sellRecord['sell_record_code']);
            $sellRecord = array_merge($sellRecord,$record_decrypt_info);

                //去掉详细地址里的直辖市、直辖区
                $sellRecord['receiver_address'] = load_model('oms/DeliverRecordModel')->check_address($sellRecord['receiver_address']);
    		$express_code = $sellRecord['express_code'];
    		//非顺丰物流的订单
    		if (!in_array($express_code, $sf_express)){
    			$error_express_sell .= "订单".$sellRecord['sell_record_code']."，";
    			continue;
    		}
    		//店铺信息
    		if (!isset($shop_arr[$sellRecord['shop_code']])){
    			$shop_ret = load_model('base/ShopModel')->get_by_code($sellRecord['shop_code']);
    			$shop_arr[$sellRecord['shop_code']] = $shop_ret['data'];
    		}
    		$shop = $shop_arr[$sellRecord['shop_code']];
    		//仓库信息
    		if (!isset($store_arr[$sellRecord['store_code']])){
    			$store_ret = load_model('base/StoreModel')->get_by_code($sellRecord['store_code']);
    			$base_store = $store_ret['data'];
    			//获取省市区的名称
    			$area_id_str = "";
    			if (!empty($base_store['country'])) {
    				$area_id_str .= $base_store['country'].',';
    			}
    			if (!empty($base_store['province'])) {
    				$area_id_str .= $base_store['province'].',';
    			}
    			if (!empty($base_store['city']) && !in_array($base_store['city'], $this->check_city)) { //直辖市去除
    				$area_id_str .= $base_store['city'].',';
    			}
    			if (!empty($base_store['district']) && !in_array($base_store['district'], $this->check_district)) {
    				$area_id_str .= $base_store['district'].',';
    			}
    			if (!empty($area_id_str)){
    				$area_id_str = rtrim($area_id_str,',');
    				$sql = "select id,name from base_area where id in($area_id_str)";
    				$area_ret = $this->db->get_all($sql);
    				$area_arr = array();
    				foreach ($area_ret as $area_row) {
    					$area_arr[$area_row['id']] = $area_row['name'];
    				}
    				$base_store['j_province'] = $area_arr[$base_store['province']];
                                if(!empty($area_arr[$base_store['city']])) {
                                    $base_store['j_city'] = $area_arr[$base_store['city']];
                                    $base_store['j_address'] = $base_store['j_province'].$base_store['j_city'].$area_arr[$base_store['district']].$base_store['street'];
                                } else {
                                    $base_store['j_city'] = $area_arr[$base_store['district']];
                                    $base_store['j_address'] = $base_store['j_province'].$base_store['j_city'].$base_store['street'];                                    
                                }
    			}
    			$store_arr[$sellRecord['store_code']] = $base_store;
    		}
    		$store = $store_arr[$sellRecord['store_code']];
    		//商品信息
    		$cargo = $this->get_goods($sellRecord['deliver_record_id']);
    		//checkword
    		$j_key = $sellRecord['express_code'].'_'.$request['j_custid'];
    		$j_custid_row = $j_custid_arr[$j_key];
    		$test_data = array(
                'orderid' => $sellRecord['sell_record_code'],
                'apply_type' => '010', //订单服务类别
                'j_custid' => $request['j_custid'], //寄件方客户卡号
                'pay_method' => 1, //1-寄方付 2-收方付 3-第三方付，默认为1
                'pay_custid' => $request['j_custid'], //付款方客户卡号
                'express_type' => $request['express_type'], //寄件类型
                'j_company' => $shop['shop_name'], //寄件方公司名称
                'cargo_net_weight' => $sellRecord['goods_weigh'] / 1000, //订单货物净重量-KG
                'j_contact' => $store['shop_contact_person'], //寄件方联系人
                'j_tel' => $store['contact_phone'], //寄件方座机
                'j_province' => $store['j_province'], //寄件方所在省份
                'j_city' => $store['j_city'], //寄件方所属城市名称
                'j_address' => $store['j_address'], //寄件方详细地址
                'd_contact' => $sellRecord['receiver_name'], //到件方联系人
                'd_mobile' => $sellRecord['receiver_mobile'], //到件方手机
                'd_tel' => $sellRecord['receiver_phone'], //到件方手机
                'd_province' => $sellRecord['receiver_province'], //到件方省
                'd_city' => !in_array($sellRecord['receiver_city'], $this->check_city) ? $sellRecord['receiver_city'] : $sellRecord['receiver_district'], //到件方市,如果是直辖市，就去系统的区
                'd_address' => $sellRecord['receiver_address'], //到件方详细地址
                'checkword' => $j_custid_row['checkword'],//校验码
                'cargoItems' =>$cargo,
    			'api_url'=>$j_custid_row['api_url'],
            );
            //增值服务
            if(!empty($services_item)){
                $test_data['services']['item'] = $services_item;
            }
            //收货地址
            $_receiver_ids[] = $sellRecord['receiver_province'];
            $_receiver_ids[] = !in_array($sellRecord['receiver_city'], $this->check_city) ? $sellRecord['receiver_city'] : $sellRecord['receiver_district'];
            $sf_updata[] = $test_data;

    	}
    	//收货地址
    	$_receiver_ids = implode("','", array_unique($_receiver_ids));
    	$_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_receiver_ids}')");
        $_receiver_data = array_column($_region_data, 'region_name','region_id');

        //获取特殊地址对照
        $area_compare = load_model('base/TaobaoAreaModel')->get_out_area_id('SF',2);

    	foreach ($sf_updata as $sf_row) {
    		$sf_row['d_province'] = isset($area_compare[$sf_row['d_province']]) ? $area_compare[$sf_row['d_province']]['out_area_name'] : $_receiver_data[$sf_row['d_province']];
    		$sf_row['d_city'] = isset($area_compare[$sf_row['d_city']]) ? $area_compare[$sf_row['d_city']]['out_area_name'] : $_receiver_data[$sf_row['d_city']];
    		$html_change = array('j_company', 'j_contact', 'j_address', 'd_contact', 'd_address');
    		foreach ($html_change as $key) {
    			$sf_row[$key] = htmlspecialchars($sf_row[$key]);
    		}
    		//规定具体错误
    		$error_array = array(
    				'j_company' => '寄件方公司名称不能为空',
    				'j_contact' => '寄件方联系人不能为空',
    				'j_tel' => '寄件方联系方式不能为空',
    				'j_province' => '寄件方省不能为空',
    				'j_city' => '寄件方市不能为空',
    				'j_address' => '寄件方具体地址不能为空',
    				'j_custid' => '月结卡号不能为空',
    				'd_contact' => '到件方联系人不能为空',
    				'd_province' => '到件方省不能为空',
    				'd_city' => '到件方市不能为空',
    				'd_address' => '到件方具体地址不能为空',
    		);
    		$is_continue = 0;
    		//校验必传参数
    		foreach ($error_array as $key => $val) {
    			$check = trim($sf_row[$key]);
    			if (empty($check)) {
    				$is_continue = 1;
    				$error_get_sell .= $sf_row['orderid'] . '原因为：' . $val . '，';
    				continue;
    			}
    		}
    		//校验长度
    		$length_array = array(
    				'orderid' => array('length' => 64, 'msg' => '订单号长度限制为64字节'),
    				'j_company' => array('length' => 100, 'msg' => '寄件方公司名称长度限制为100字节'),
    				'j_contact' => array('length' => 100, 'msg' => '寄件方联系人长度限制为30字节'),
    				'j_tel' => array('length' => 20, 'msg' => '寄件方联系方式长度限制为20字节'),
    				'j_province' => array('length' => 30, 'msg' => '寄件方省长度限制为20字节'),
    				'j_city' => array('length' => 100, 'msg' => '寄件方市长度限制为100字节'),
    				'j_address' => array('length' => 150, 'msg' => '寄件方具体地址长度限制为150字节'),
    				'j_custid' => array('length' => 12, 'msg' => '月结卡号长度限制为12字节'),
    				'd_contact' => array('length' => 30, 'msg' => '到件方联系人长度限制为30字节'),
    				'd_province' => array('length' => 30, 'msg' => '到件方省长度限制为20字节'),
    				'd_city' => array('length' => 100, 'msg' => '到件方市长度限制为100字节'),
    				'd_address' => array('length' => 250, 'msg' => '到件方具体地址长度限制为250字节'),
    		);
    		foreach ($length_array as $key => $val) {
    			$length = $val['length'];
    			$len_msg = $val['msg'];
    			$check = mb_strlen(trim($sf_row[$key]));
    			if ($check > $length) {
    				$is_continue = 1;
    				$error_get_sell .= $sf_row['orderid'] . '原因为：' . $len_msg . '，';
    				continue;
    			}
    		}
    		//手机-电话必须有一项
    		if (empty($sf_row['d_mobile']) && empty($sf_row['d_tel'])) {
    			$error_get_sell .= $sf_row['orderid'] . '原因为：收件人联系方式不能为空，';
    			continue;
    		}
    		//检验到错误，跳出循环
    		if ($is_continue) {
    			continue;
    		}
    		//上传订单
    		$api_url = $sf_row['api_url'];
    		unset($sf_row['api_url']);

    		$sf_info['data'] = json_encode($sf_row);
    		$sf_info['checkword'] = $sf_row['checkword'];
    		$sf_info['api_url'] = $api_url;

    		$ret = $this->ExpressServlet($sf_info);
    		//print_r($ret);die;

    		if ($ret['status']<0){
    			$pos = strpos($ret['message'], '2:1101');
    			if ($pos !== FALSE) {
    				//重新查询
    				$search_info = array(
    								'orderid' => $sf_row['orderid'],
    								'checkword' => $sf_row['checkword'],
    						);
    				$sf_info['data'] = json_encode($search_info);
    				$ret = $this->OrderSearchServlet($sf_info);
    				if ($ret['status']<0){
    					$error_get_sell .= $sf_row['orderid'] . '原因为：重复提交' . $ret['msg'] . '，';
    				}
    			} elseif (!(strpos($ret['message'], '2:1012') === FALSE)) {
    				$error_get_sell .= $sf_row['orderid'] . '原因为：月结卡号错误' . $ret['msg'] . '，';
    			} elseif (!empty($ret['message'])) {
    				$error_get_sell .= $sf_row['orderid'] . '原因为：' . $ret['msg'] . '，';
    			} elseif (!empty($ret['data']['msg'])){
                                $error_get_sell .= $sf_row['orderid'] . "原因为：{$ret['data']['msg']},";
                        } else {
                        $error_get_sell .= $sf_row['orderid'] . '原因为：返回空，';
    			}
    		}
    		if ($ret['status']<0){
    			continue;
    		}
    		//回写快递单号
    		$result = $ret['data'];
    		if (!empty($result)) {
    			$sell_record_code = $sf_row['orderid'];
    			//物流单号
    			$express_no = $result['mailno'];
    			//保存寄件方和到件方区号
    			$express_data = array(
    					'originCode' => $result['originCode'],
    					'destCode' => $result['destCode'],
    					'j_custid' =>$sf_row['j_custid'],
    					'express_type' =>$this->express_type[$sf_row['express_type']], //寄件类型
    			);
    			$express_data = array('express_no'=>$express_no,'express_data' =>json_encode($express_data));
    			$this->update_express_no($sell_record_code, $express_data);

    			$succ_get_sell .= $sf_row['orderid'] . '，';
    		}
    	}
    	//组装错误
    	$status = 1;
    	$msg = "";
    	if (!empty($succ_get_sell)){
    		$msg .= "获取成功的订单<br />".$succ_get_sell."<br />";
    	}
    	if (!empty($error_express_sell)){
    		$status = -1;
    		$msg .= "非顺丰物流的订单<br />".$error_express_sell."<br />";
    	}
    	if (!empty($error_get_sell)){
    		$status = -1;
    		$msg .= "获取失败的订单<br />".$error_get_sell."<br />";
    	}
    	return $this->format_ret($status,'',$msg);


    }
    //更新物流单号
    public function update_express_no($sell_record_code,$express_data){
    	//更新物流单号
    	load_model('oms/DeliverRecordModel')->save_sell_record_express_no($sell_record_code,$express_data, 'sfrm');
    }
    //上传订单 获取物流单号
    function ExpressServlet($params){
    	$method = "ExpressServlet";
    	$api_token = array('checkword' => $params['checkword'],'URL' => $params['api_url'] );
    	$params_data = json_decode($params['data'], TRUE);
    	$ret = $this->request_send($method,$api_token,$params_data);
    	return $ret;

    }
    //查询
    function OrderSearchServlet($params){
    	$method = "OrderSearchServlet";
    	$api_token = array('checkword' => $params['checkword'],'URL' => $params['api_url'] );
    	$params_data = json_decode($params['data'], TRUE);
    	$ret = $this->request_send($method,$api_token,$params_data);
    	return $ret;
    }
    function request_send($method,$api_token,$param){
    	require_model('wms/sfwms/SfwmsAPIModel');
    	$sf_mdl = new SfwmsAPIModel($api_token);
    	$ret = $sf_mdl->request_send($method,$param);
    	return $ret;
    	/*
    	if ($ret['status'] > 0) {
    		return $this->format_ret(1,$ret['data'],$ret['msg']);
    	}*/
    }


    function get_goods($deliver_record_id) {
    	/*
    	$sql = "select g.goods_name,s1.spec1_name,s2.spec2_name,d.num from oms_deliver_record_detail d
                    INNER JOIN base_goods g ON d.goods_code=g.goods_code
                    INNER JOIN base_spec1 s1 ON s1.spec1_code=d.spec1_code
                    INNER JOIN base_spec2 s2 ON s2.spec2_code=d.spec2_code
            WHERE d.deliver_record_id=:deliver_record_id";*/
    	$sql = "select g.goods_name,d.goods_price,d.num from oms_deliver_record_detail d
                    INNER JOIN base_goods g ON d.goods_code=g.goods_code
            WHERE d.deliver_record_id=:deliver_record_id";
    	$data  = $this->db->get_all($sql, array('deliver_record_id' => $deliver_record_id));

    	$cargo = array();
    	if(!empty($data)){
    		foreach ($data as $goods){
    			$goods_name = $goods['goods_name'];
    			$goods_row = array(
    					'cargo_name' => $goods_name, //商品名称
    					'quantity' => $goods['num'], //数量
    					'price' => $goods['goods_price'], //商品单价
    			);
		    	$cargo[] = array('cargo' => $goods_row);
    		}
    	}
    	return  $cargo;
    }

    /**
     * @todo 通过订单号获取顺丰单号
     */
    function get_sf_express_no_by_sell_record_code($sell_record_code) {
        //读取订单
        $sql = "SELECT * FROM oms_sell_record WHERE sell_record_code=:sell_record_code AND express_no = '' ";
        $sellRecord = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        if (empty($sellRecord)) {
            return array('status' => -1, 'message' => '请选择快递单号为空的订单');
        }
         $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sellRecord);
         $sellRecord = array_merge($sellRecord,$record_decrypt_info);

        //获取顺丰热敏配置
        $j_custid_sql_value = array();
        $express_type_str = $this->arr_to_in_sql_value(array_keys($this->express_type_code), 'express_code', $j_custid_sql_value);
        $j_custid_sql = "SELECT * FROM shunfeng_rm_config WHERE 1 AND express_code IN({$express_type_str})";
        $j_custid_arr = $this->db->get_all($j_custid_sql, $j_custid_sql_value);
        if(empty($j_custid_arr)) {
            return $this->format_ret(-1, '', '配送方式中未配置月结帐号');
        }
        foreach ($j_custid_arr as $j_custid_row) {
            $new_j_custid_arr[$j_custid_row['express_code']] = $j_custid_row;
        }
        //店铺信息
        $shop_ret = load_model('base/ShopModel')->get_by_code($sellRecord['shop_code']);
        $base_shop = $shop_ret['data'];
        //仓库信息
        $store_ret = load_model('base/StoreModel')->get_by_code($sellRecord['store_code']);
        $base_store = $store_ret['data'];

        //收货和发货地址id数组
        $_receiver_ids[] = $sellRecord['receiver_province'];
        $_receiver_ids[] = $sellRecord['receiver_city'];
        
        $_receiver_ids[] = $base_store['country'];
        $_receiver_ids[] = $base_store['province'];
        $_receiver_ids[] = $base_store['city'];
        $_receiver_ids[] = $base_store['district'];
        
        //收货地址
        $_receiver_ids_str = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_receiver_ids_str}')");
        foreach ($_region_data as $_region) {
            $_address_data[$_region['region_id']] = $_region['region_name'];
        }
        //商品信息
        $cargo = $this->get_goods_by_sell_record_code($sell_record_code);
        
        //id地址转汉字地址
        $j_province = !empty($_address_data[$base_store['province']]) ? $_address_data[$base_store['province']] : '';
        $j_city = !empty($_address_data[$base_store['city']]) ? $_address_data[$base_store['city']] : '';
        $j_district = !empty($_address_data[$base_store['district']]) ? $_address_data[$base_store['district']] : '';
        
        $d_province = !empty($_address_data[$sellRecord['receiver_province']]) ? $_address_data[$sellRecord['receiver_province']] : '';
        $d_city = !empty($_address_data[$sellRecord['receiver_city']]) ? $_address_data[$sellRecord['receiver_city']] : '';
        
        //发送数据
        $send_data = array(
            'orderid' => $sellRecord['sell_record_code'],
            'apply_type' => '010', //订单服务类别
            'j_custid' => $new_j_custid_arr[$sellRecord['express_code']]['j_custid'], //寄件方客户卡号
            'pay_method' => 1, //1-寄方付 2-收方付 3-第三方付，默认为1
            'pay_custid' => $new_j_custid_arr[$sellRecord['express_code']]['j_custid'], //付款方客户卡号
            'express_type' => $this->express_type_code[$sellRecord['express_code']], //寄件类型
            'j_company' => $base_shop['shop_name'], //寄件方公司名称
            'cargo_net_weight' => $sellRecord['goods_weigh'] / 1000, //订单货物净重量-KG
            'j_contact' => $base_store['shop_contact_person'], //寄件方联系人
            'j_tel' => $base_store['contact_phone'], //寄件方座机
            'j_province' => $j_province, //寄件方所在省份
            'j_city' => $j_city, //寄件方所属城市名称
            'j_address' => $j_province . $j_city . $j_district . $base_store['street'], //寄件方详细地址
            'd_contact' => $sellRecord['receiver_name'], //到件方联系人
            'd_mobile' => $sellRecord['receiver_mobile'], //到件方手机
            'd_tel' => $sellRecord['receiver_phone'], //到件方手机
            'd_province' => $d_province, //到件方省
            'd_city' => $d_city, //到件方市
            'd_address' => $sellRecord['receiver_address'], //到件方详细地址
            'checkword' => $new_j_custid_arr[$sellRecord['express_code']]['checkword'], //校验码
            'cargoItems' => $cargo,
        );
        $html_change = array('j_company', 'j_contact', 'j_address', 'd_contact', 'd_address');
        foreach ($html_change as $key) {
            $send_data[$key] = htmlspecialchars($send_data[$key]);
        }
        //传参错误信息
        $error_get_sell = '';
        //规定具体错误
        $error_array = array(
            'j_company' => '寄件方公司名称不能为空',
            'j_contact' => '寄件方联系人不能为空',
            'j_tel' => '寄件方联系方式不能为空',
            'j_province' => '寄件方省不能为空',
            'j_city' => '寄件方市不能为空',
            'j_address' => '寄件方具体地址不能为空',
            'j_custid' => '月结卡号不能为空',
            'd_contact' => '到件方联系人不能为空',
            'd_province' => '到件方省不能为空',
            'd_city' => '到件方市不能为空',
            'd_address' => '到件方具体地址不能为空',
        );
        //校验必传参数
        foreach ($error_array as $key => $val) {
            $check = trim($send_data[$key]);
            if (empty($check)) {
                $error_get_sell .=  $val . '<br/>';
            }
        }
        //校验长度
        $length_array = array(
            'orderid' => array('length' => 64, 'msg' => '订单号长度限制为64字节'),
            'j_company' => array('length' => 100, 'msg' => '寄件方公司名称长度限制为100字节'),
            'j_contact' => array('length' => 100, 'msg' => '寄件方联系人长度限制为30字节'),
            'j_tel' => array('length' => 20, 'msg' => '寄件方联系方式长度限制为20字节'),
            'j_province' => array('length' => 30, 'msg' => '寄件方省长度限制为20字节'),
            'j_city' => array('length' => 100, 'msg' => '寄件方市长度限制为100字节'),
            'j_address' => array('length' => 150, 'msg' => '寄件方具体地址长度限制为150字节'),
            'j_custid' => array('length' => 12, 'msg' => '月结卡号长度限制为12字节'),
            'd_contact' => array('length' => 30, 'msg' => '到件方联系人长度限制为30字节'),
            'd_province' => array('length' => 30, 'msg' => '到件方省长度限制为20字节'),
            'd_city' => array('length' => 100, 'msg' => '到件方市长度限制为100字节'),
            'd_address' => array('length' => 250, 'msg' => '到件方具体地址长度限制为250字节'),
        );
        foreach ($length_array as $key => $val) {
            $length = $val['length'];
            $len_msg = $val['msg'];
            $check = mb_strlen(trim($send_data[$key]));
            if ($check > $length) {
                $error_get_sell .= $len_msg . '<br/>';

            }
        }
        //手机-电话必须有一项
        if (empty($send_data['d_mobile']) && empty($send_data['d_tel'])) {
            $error_get_sell .= '收件人联系方式不能为空<br/>';
        }

        //传参有错误直接返回，不调用接口
        if (!empty($error_get_sell)) {
            $status = -1;
            $msg = '订单：'.$send_data['orderid'] . '获取失败，原因：' . $error_get_sell;
            return $this->format_ret($status, '', $msg);
        }
        
        //上传订单url
        $api_url = $new_j_custid_arr[$sellRecord['express_code']]['api_url'];

        $sf_info['data'] = json_encode($send_data);
        $sf_info['checkword'] = $send_data['checkword'];
        $sf_info['api_url'] = $api_url;
        $ret = $this->ExpressServlet($sf_info);

        if ($ret['status'] < 0) {
            $pos = strpos($ret['message'], '2:1101');
            if ($pos !== FALSE) {
                //重新查询
                $search_info = array(
                    'orderid' => $send_data['orderid'],
                    'checkword' => $send_data['checkword'],
                );
                $sf_info['data'] = json_encode($search_info);
                $ret = $this->OrderSearchServlet($sf_info);
                if ($ret['status'] < 0) {
                    $error_get_sell .= '重复提交' . $ret['msg'] . '，';
                }
            } elseif (!(strpos($ret['message'], '2:1012') === FALSE)) {
                $error_get_sell .= '月结卡号错误' . $ret['msg'] . '，';
            } elseif (!empty($ret['message'])) {
                $error_get_sell .= $ret['msg'] . '，';
            } elseif (!empty($ret['data']['msg'])) {
                $error_get_sell .= $ret['data']['msg'] . '，';
            } else {
                $error_get_sell .= '返回空，';
            }
        }
         //传参有错误直接返回
        if (!empty($error_get_sell)) {
            $status = -1;
            $msg = '订单：'.$send_data['orderid'] . '获取失败，原因：' . $error_get_sell;
            return $this->format_ret($status, '', $msg);
        }
        //回写快递单号
        $result = $ret['data'];
        if (!empty($result)) {
            //物流单号
            $express_no = $result['mailno'];
            //保存寄件方和到件方区号
            $sf_express_data = array(
                'originCode' => $result['originCode'],
                'destCode' => $result['destCode'],
                'j_custid' => $send_data['j_custid'],
                'express_type' => $filp_express_type[$sellRecord['express_code']], //寄件类型
            );
            $express_data = array('express_no' => $express_no, 'express_data' => json_encode($sf_express_data));
            $this->db->update('oms_sell_record', $express_data, array('sell_record_code' => $sell_record_code));
            $action ='获取顺丰热敏物流';
            $msg = '快递单号更新为：' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        
        return $this->format_ret(1, $express_data);
    }
    
    /**
     * @todo 通过订单号获取商品信息
     */
    function get_goods_by_sell_record_code($sell_record_code) {
        $sql = "SELECT
                    g.goods_name,
                    d.goods_price,
                    d.num
                FROM
                    oms_sell_record_detail d
                INNER JOIN base_goods g ON d.goods_code = g.goods_code
                WHERE
                    d.sell_record_code =:sell_record_code";
        $data = $this->db->get_all($sql, array('sell_record_code' => $sell_record_code));
        $cargo = array();
        if (!empty($data)) {
            foreach ($data as $goods) {
                $goods_row = array(
                    'cargo_name' => $goods['goods_name'], //商品名称
                    'quantity' => $goods['num'], //数量
                    'price' => $goods['goods_price'], //商品单价
                );
                $cargo[] = array('cargo' => $goods_row);
            }
        }
        return  $cargo;
    }


}
