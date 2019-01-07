<?php

/**
 * 退货包裹单
 *
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class ReturnPackageModel extends TbModel{
    protected $table = 'oms_return_package';
    protected $detail_table = 'oms_return_package_detail';
    protected $pk = 'return_package_id';
    private $is_api = 0;

    public $tag = array(
        0 => '正常',
        1 => 'SKU异常',
        2 => '无名单',
        3 => '无退货申请',
    );

    public $return_order_status = array(
        0 => '待收货',
        1 => '已收货',
        2 => '已作废',
    );

//    public $return_package_status = array(
//        0 => '未入库',
//        1 => '已入库',
//        2 => '已作废',
//    );

    public $return_type = array(
        1 => '退',
        2 => '退+换',
        3 => '修补',
    );

    function get_package_by_page($filter) {
//        var_dump($filter);exit;
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = " left join {$this->detail_table} rr on rl.return_package_code = rr.return_package_code ";
        //判断是否导出，是就联表
        if(isset($filter['ctl_type']) && $filter['ctl_type'] == 'export'){
            $sql_join .= " LEFT JOIN goods_sku r2 ON r2.sku = rr.sku LEFT JOIN base_goods r3 ON r3.goods_code = rr.goods_code";
        }
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";

        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $shop_pur = load_model('base/ShopModel')->get_purview_shop();
        if (!empty($shop_pur)) {
            $shop_pur_arr = array_column($shop_pur, 'shop_code');
            $shop_pur_str = $this->arr_to_in_sql_value($shop_pur_arr, 'shop_pur', $sql_values);
            $sql_main .= " AND (rl.shop_code IN ({$shop_pur_str}) OR rl.shop_code='') ";
        } else {
            $sql_main .= " AND rl.shop_code='' ";
        }

        $tab = empty($filter['do_list_tab']) ? 'wait_receive' : $filter['do_list_tab'];

        switch ($tab) {
            case 'tabs_all'://全部
                break;
            case 'wait_receive'://待收货
                $sql_main .= " and rl.return_order_status = 0";
                break;
            case 'having_receive'://已收货
                $sql_main .= " and rl.return_order_status = 1";
                break;
            case 'canceled'://已作废
                $sql_main .= " and rl.return_order_status = 2";
                break;
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
	    $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if(empty($sku_arr)){
                $sql_main .= " AND 1=2 ";
            }else{
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rr.sku in({$sku_str}) ";
            }
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
	    $sql_main .= " AND rr.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //关联订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
	    $sql_main .= " AND rl.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        //关联交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
	    $sql_main .= " AND rl.deal_code = :deal_code ";
            $sql_values[':deal_code'] = $filter['deal_code'];
        }
        //是否换货
        if (isset($filter['is_exchange_goods']) && $filter['is_exchange_goods'] !== '') {
	    $sql_main .= " AND rl.is_exchange_goods = :is_exchange_goods ";
            $sql_values[':is_exchange_goods'] = $filter['is_exchange_goods'];
        }

        if (isset($filter['return_express_no']) && $filter['return_express_no'] !== '') {
            $sql_main .= " AND rl.return_express_no LIKE :return_express_no ";
            $sql_values[':return_express_no'] = '%' . $filter['return_express_no'] . '%';
        }
        if (isset($filter['sell_return_code']) && $filter['sell_return_code'] !== '') {
            $sql_main .= " AND rl.sell_return_code LIKE :sell_return_code ";
            $sql_values[':sell_return_code'] = '%' . $filter['sell_return_code'] . '%';
        }
        if (isset($filter['return_package_code']) && $filter['return_package_code'] !== '') {
            $sql_main .= " AND rl.return_package_code LIKE :return_package_code ";
            $sql_values[':return_package_code'] = '%' . $filter['return_package_code'] . '%';
        }
        if (isset($filter['remark']) && $filter['remark'] !== '') {
            $sql_main .= " AND rl.remark LIKE :remark ";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
                 $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str. " ) ";
        }
        //验收入库人
        if(!empty($filter['receive_person'])){
            $arr = explode(',',$filter['receive_person']);
            $str = $this->arr_to_in_sql_value($arr, 'receive_person', $sql_values);
            $sql_main .= " AND  rl.receive_person = " . $str. "  ";

        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
                 $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " . $str . " ) ";
        }
	//配送方式
        if (isset($filter['return_express_code']) && $filter['return_express_code'] !== '') {
               $arr = explode(',',$filter['return_express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'return_express_code', $sql_values);
            $sql_main .= " AND rl.return_express_code in ( " .$str . " ) ";
        }
        //无名包裹
        if (isset($filter['tag']) && $filter['tag'] !== '') {
            $sql_main .= " AND rl.tag =:tag ";
            $sql_values[':tag'] = $filter['tag'];
        }
        //绑定退单
        if (isset($filter['relation_return']) && $filter['relation_return'] !== '') {
            if($filter['relation_return'] == 0){
                $sql_main .= " AND rl.sell_return_code = '' ";
            }
            if($filter['relation_return'] == 1){
                $sql_main .= " AND rl.sell_return_code  != '' ";
            }
        }
        //发货时间
        if (!empty($filter['stock_date_start'])) {
            $sql_main .= " AND rl.stock_date >= :stock_date_start ";
            $sql_values[':stock_date_start'] = $filter['stock_date_start'] . ' 00:00:00';
        }
        if (!empty($filter['stock_date_end'])) {
            $sql_main .= " AND rl.stock_date <= :stock_date_end ";
            $sql_values[':stock_date_end'] = $filter['stock_date_end'] . ' 23:59:59';
        }
        //买家昵称
        if(!empty($filter['buyer_name']) && isset($filter['buyer_name'])){
     
         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){

                       $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                       $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }           

//            $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = $filter['buyer_name'] . "%" ;

            
        }
        //退货人
        if (!empty($filter['return_name']) && isset($filter['return_name'])) {
            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['return_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.return_name LIKE :return_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':return_name'] = $filter['return_name'] . "%";
            } else {
                $sql_main .= " AND rl.return_name LIKE :return_name ";
                $sql_values[':return_name'] = $filter['return_name'] . "%";
            }
        }
        //手机
        if(!empty($filter['return_mobile']) && isset($filter['return_mobile'])){
                  $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['return_mobile'], 'tel');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND (  rl.return_mobile LIKE :return_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':return_mobile'] = $filter['return_mobile'] . "%" ;
            } else {
                $sql_main .= " AND rl.return_mobile LIKE :return_mobile ";
                $sql_values[':return_mobile'] = $filter['return_mobile'] . "%" ;
            }
            
        }
        //买家退单说明
        if (isset($filter['return_buyer_memo']) && !empty($filter['return_buyer_memo'])) {
            $sql_main .= " AND r4.return_buyer_memo like :return_buyer_memo ";
            $sql_values[':return_buyer_memo'] = "%" . $filter['return_buyer_memo'] . "%";
        }
        //卖家退单备注
        if (isset($filter['return_remark']) && !empty($filter['return_remark'])) {
            $sql_main .= " AND r4.return_remark like :return_remark ";
            $sql_values[':return_remark'] = "%" . $filter['return_remark'] . "%";
        }

        //判断导出还是查询
        if(isset($filter['ctl_type']) && $filter['ctl_type'] == 'view'){
            $select = ' rl.*';
            $sql_main .= " group by rl.return_package_code ";
        } else {
            $select = 'rl.*,r2.barcode,rr.apply_num,rr.num,r3.goods_name,r2.goods_code,r2.spec1_code,r2.spec1_name,r2.spec2_code,r2.spec2_name';
        }
	$sql_main .= " order by rl.return_package_id desc";
//	$data = $this -> get_page_from_sql($filter, $sql_main, $p_sql_values, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select,true);
        filter_fk_name($data['data'], array('shop_code|shop', 'return_express_code|express'));
        $filter['ref'] = isset($filter['ref']) ? $filter['ref'] :'';
         $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach($data['data'] as $key => &$value) {
            $url = "?app_act=oms/sell_return/package_detail&return_package_code={$value['return_package_code']}&ref={$filter['ref']}";
            $_url = base64_encode($url);
            $u = "javascript:openPage('{$_url}', '{$url}', '包裹单详情')";
            $value['return_package_code_href'] = "<a href=\"{$u}\">".$value['return_package_code']."</a>";
            $return_code_url = "?app_act=oms/sell_return/after_service_detail&sell_return_code={$value['sell_return_code']}";
            $_return_code_url = base64_decode($return_code_url);
            $_return_code_url_u = "javascript:openPage('{$_return_code_url}', '{$return_code_url}', '退单详情')";
            $value['sell_return_code_href'] = "<a href=\"{$_return_code_url_u}\">".$value['sell_return_code']."</a>";
            $value['return_order_status_txt'] = $this->return_order_status[$value['return_order_status']];
            $value['tag_name'] = $this->tag[$value['tag']];
//            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
//            $value['return_express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['return_express_code']));
            if($cfg['safety_control'] == 1 ){
               safe_return_package_data($value,0);
            }
            $value['receive_time'] = $value['receive_time'] == '0000-00-00 00:00:00' ? '' : $value['receive_time'];
            if(!empty($value['sell_return_code'])){
                    $check_1 = $this->db->get_col("select tag_desc from oms_sell_return_tag where sell_return_code=:sell_return_1",array(":sell_return_1"=>$value['sell_return_code']));
                    $value['tag_desc'] = implode(",",$check_1);
            }
            $value['is_exchange_goods_str'] = $value['is_exchange_goods'] == 0 ? '否' : '是';
        }
        
        //对导出的数据进行解密
            if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'return_package_list' && !empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
                if ($is_security_role === true) {
                    $data['data'] = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_list($data['data']);
                    $log = array('user_id' =>0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '配发货', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '退货包裹单导出解密数据');
                    load_model('sys/OperateLogModel')->insert($log);
                }
            }
	    return $this -> format_ret(1, $data);
    }

    function get_shop_name($return_package_code,$shop_code){
        if(empty($shop_code)){
            $sql = "select sell_return_code from oms_return_package where return_package_code = :return_package_code";
            $sell_return_code = $this->db->get_value($sql,array(":return_package_code" => $return_package_code));
            if($sell_return_code){
                $return_sql = "select shop_code from oms_sell_return where sell_return_code = :sell_return_code";
                $shop_code = $this->db->get_value($return_sql,array(":sell_return_code" => $sell_return_code));
            }
        }
        $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $shop_code));
        return $shop_name;
    }

	function get_list_by_page($filter) {
	    $sql_main_arr = array();
	    $sql_values = array();
	    $sqlone_main_arr = array();
	    $sqlone_values = array();
	    $sql_join = "";
	    $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";
              $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
	    if (!empty($filter['return_package_code'])) {
	        $code_arr = explode(',', $filter['return_package_code']);
	        $in_sql = CTX() -> db -> get_in_sql('return_package_code', $code_arr, $sqlone_values);
	        $sqlone_main_arr[] = " AND rl.return_package_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['sell_return_code'])) {
	        $code_arr = explode(',', $filter['sell_return_code']);
	        $in_sql = CTX() -> db -> get_in_sql('sell_return_code', $code_arr, $sqlone_values);
	        $sqlone_main_arr[] = " AND rl.sell_return_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['sell_record_code'])) {
	        $code_arr = explode(',', $filter['sell_record_code']);
	        $in_sql = CTX() -> db -> get_in_sql('sell_record_code', $code_arr, $sqlone_values);
	        $sqlone_main_arr[] = " AND rl.sell_record_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['deal_code'])) {
	        $code_arr = explode(',', $filter['deal_code']);
	        $in_sql = CTX() -> db -> get_in_sql('deal_code', $code_arr, $sqlone_values);
	        $sqlone_main_arr[] = " AND rl.deal_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['return_express_code'])) {
	        $code_arr = explode(',', $filter['return_express_code']);
	        $in_sql = CTX() -> db -> get_in_sql('return_express_code', $code_arr, $sql_values);
	        $sql_main_arr[] = " AND rl.return_express_code IN (" . $in_sql . ") ";
	    }

	    if (isset($filter['return_express_no'])) {
	        $sqlone_main_arr[] = " AND rl.return_express_no = :return_express_no ";
	        $sqlone_values[':return_express_no'] = $filter['return_express_no'];
	    }

	    if (!empty($filter['shop_code'])) {
	        $code_arr = explode(',', $filter['shop_code']);
	        $in_sql = CTX() -> db -> get_in_sql('shop_code', $code_arr, $sql_values);
	        $sql_main_arr[] = " AND rl.shop_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['store_code'])) {
	        $code_arr = explode(',', $filter['store_code']);
	        $in_sql = CTX() -> db -> get_in_sql('store_code', $code_arr, $sql_values);
	        $sql_main_arr[] = " AND rl.store_code IN (" . $in_sql . ") ";
	    }

	    if (!empty($filter['return_order_status'])) {
	        $code_arr = explode(',', $filter['return_order_status']);
	        $in_sql = CTX() -> db -> get_in_sql('return_order_status', $code_arr, $sql_values);
	        $sql_main_arr[] = " AND rl.return_order_status IN (" . $in_sql . ") ";
	    }

	    if (isset($filter['stock_date_start'])) {
	        $t_start = $filter['stock_date_start'] . ' 00:00:00';
	        $sql_main_arr[] = " AND rl.stock_date >= :stock_date_start ";
	        $sql_values[':stock_date_start'] = $t_start;
	    }
	    if (isset($filter['stock_date_end'])) {
	        $t_end = $filter['stock_date_end'] . ' 23:59:59';
	        $sql_main_arr[] = " AND rl.stock_date <= :stock_date_end ";
	        $sql_values[':stock_date_end'] = $t_end;
	    }
	    $select = 'rl.*';
	    if (!empty($sqlone_main_arr)) {
	        $sql_main .= $sqlone_main_arr[0];
	        $p_sql_values = $sqlone_values[0];
	    } else {
	        $sql_main .= join(' ', $sql_main_arr);
	        $p_sql_values = $sql_values;
	    }
	    $sql_main .= " order by r1.sell_record_id asc";
	    $data = $this -> get_page_from_sql($filter, $sql_main, $p_sql_values, $select);

	    foreach($data['data'] as $key => &$value) {
			$url = "?app_act=oms/sell_record/view&sell_record_code={$value['sell_record_code']}&ref={$filter['ref']}";
			$_url = base64_encode($url);
			$u = "javascript:openPage('{$_url}', '{$url}', '包裹单详情')";
			$value['return_package_code_href'] = "<a href=\"{$url}\">".$value['sell_record_code']."</a>";
			$value['return_type_txt'] = $this->return_type[$value['return_type']];
			$value['return_order_status_txt'] = $this->return_order_status[$value['return_order_status']];
			$value['tag_txt'] = $this->tag[$value['tag']];
	    }
	    return $this -> format_ret(1, $data);
	}

    function get_detail_list_by_code($params, $cols=null, $p2=null){
        $data = $this -> create_mapper($this -> detail_table) ->select($cols)-> where($params) -> find_all_by();
        //select
        $ret_status = $data ? 1 : 'op_no_data';

        return $this -> format_ret($ret_status, $data);
    }

    function get_return_package_by_code($return_package_code){
	    $sql = "select * from oms_return_package where return_package_code = :return_package_code";
	    $row = ctx()->db->get_row($sql,array(':return_package_code'=>$return_package_code));
	    return $row;
    }

    function create_return_package($sell_return,$record_type = 0) {
        //过滤换行
        $sell_return['return_address'] = str_replace(array("\r\n", "\r", "\n"), "", $sell_return['return_address']);  
        $sell_return['return_addr'] = str_replace(array("\r\n", "\r", "\n"), "", $sell_return['return_addr']); 
        $sell_return['remark'] = str_replace(array("\r\n", "\r", "\n"), "", $sell_return['remark']);
        $sell_return['return_buyer_memo'] = str_replace(array("\r\n", "\r", "\n"), "", $sell_return['return_buyer_memo']);
        $sell_return['return_remark'] = str_replace(array("\r\n", "\r", "\n"), "", $sell_return['return_remark']);
        if (!empty($sell_return['return_express_no'])) {
            
            $sql = "SELECT count(*) AS count FROM oms_return_package WHERE return_express_no=:return_express_no AND sell_return_code='' AND return_order_status != 2";
            $d = $this->db->get_row($sql, array(':return_express_no' => $sell_return['return_express_no']));
            if($d['count']>=2) {
                return $this->format_ret(-2,'','存在多个退货包裹单的物流单号与此单据一致');
            }
            
            //如果已存在关联的有效的 包裹单号，则不要生成
            $sql = "SELECT return_package_code,return_order_status FROM oms_return_package WHERE return_express_no=:return_express_no AND sell_return_code='' AND return_order_status != 2";
            $c = $this->db->get_row($sql, array(':return_express_no' => $sell_return['return_express_no']));
            if (!empty($c)) {
                $package_data = get_array_vars($sell_return, array('sell_return_code', 'deal_code', 'sell_record_code', 'return_name', 'return_mobile', 'return_country', 'return_province', 'return_city', 'return_district', 'return_street', 'return_address', 'return_addr', 'return_phone','buyer_name','return_buyer_memo','return_remark'));
                $package_data['tag'] = 0;
                $ret = $this->update($package_data, array('return_package_code' => $c['return_package_code']));
                if ($ret['status'] != 1) {
                    return $this->format_ret(-1, $c['return_package_code']);
                }
                if($c['return_order_status'] == 1) {
                    return $this->format_ret(3, $c['return_package_code']);
                }
                return $this->format_ret(2, $c['return_package_code']);
            }
        }


        $copy_fld = explode(',','sell_return_code,deal_code,shop_code,store_code,stock_date,sell_record_code,return_name,return_country,return_province,return_city,return_district,return_street,return_address,return_addr,return_mobile,return_phone,return_express_code,return_express_no,buyer_name,return_buyer_memo,return_remark,is_exchange_goods,customer_code,customer_address_id');

        $package = array();
        foreach($copy_fld as $fld){
            $package[$fld] = $sell_return[$fld];
        }
        $package['return_type'] = 1;
        $package['return_order_status'] = 0;
        $package['create_time'] = date('Y-m-d H:i:s');
        $package['stock_date'] = date('Y-m-d');
        $package['return_package_code'] = 'BG'.load_model('util/CreateCode')->get_code('oms_return_package');

        try {
            $this->begin_trans();

            $ret = M('oms_return_package')->insert($package);
            if($ret['status'] < 0){
                $this->rollback();
                return $ret;
            }
            $create_detail_ret = $this->create_return_package_detail_by_sell_return_code($sell_return['sell_return_code'],$record_type);
            if($create_detail_ret['status'] < 0){
                $this->rollback();
                return $create_detail_ret;
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }



        return $this->format_ret(1, $package['return_package_code']);
    }

    function cancel_return_package($sell_return_code){
	    $sql = "select return_package_code,sell_return_code,return_order_status from oms_return_package where sell_return_code = :sell_return_code";
	    $db_package = ctx()->db->get_all($sql,array(':sell_return_code'=>$sell_return_code));
	    foreach($db_package as $sub_package){
		    if ($sub_package['return_order_status'] == 1){
			    return $this->format_ret(-1,'',$sub_package['return_package_code'].' 已收货不允许作废');
		    }
	    }
	    $ret = M('oms_return_package')->update(array('return_order_status'=>2),array('sell_return_code'=>$sell_return_code));
	    return $ret;
    }

	//根据退单号自动建立包裹单商品明细及批次明细
    function create_return_package_detail_by_sell_return_code($sell_return_code,$record_type = 0){
	    $sql = "select return_package_code,store_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
	    $pack_row = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));
	    if (empty($pack_row)){
		    return $this->format_ret(-1,'',$sell_return_code.' 退单缺少相关联的包裹单');
	    }
	    $store_code = $pack_row['store_code'];
	    $return_package_code = $pack_row['return_package_code'];

	    $return_detail1 = load_model('oms/SellReturnModel')->get_detail_list_by_return_code($sell_return_code);
            $return_detail = array();
            foreach ($return_detail1 as $val) {
                if(array_key_exists($val['sku'],$return_detail)) {
                    $return_detail[$val['sku']]['note_num'] += $val['note_num'];
                    continue;
                }
                $return_detail[$val['sku']] = $val;
            }
	    if (empty($return_detail)){
		    return $this->format_ret(-1,'','缺少退单明细');
	    }
	   // $first_return_detail = reset($return_detail);
            $record_code_arr = array();
            foreach($return_detail as $val){
                $record_code_arr[] = $val['sell_record_code'];
            }
            $record_code_arr = array_unique($record_code_arr);
            $sell_lof_info = array();
            foreach($record_code_arr as $record_code ){
                    $sell_lof_info_ret = load_model('oms/SellRecordOptModel')->get_lof_info_by_record_code($record_code,1,'sku','_used_sl');
                if (empty($sell_lof_info_ret)){
                        return $this->format_ret(-1,'','缺少订单批次明细');
                }
                if(empty($sell_lof_info)){
                    $sell_lof_info = $sell_lof_info_ret;
                }else{
                    foreach($sell_lof_info_ret as $sku=>$val){
                        $sell_lof_info[$sku] = $val;
                    }
                }
            }

        //查找退货包裹单的明细,实际入库数
        $sql = "SELECT * FROM oms_return_package_detail WHERE return_package_code=:return_package_code";
        $sql_value = array();
        $sql_value[':return_package_code'] = $return_package_code;
        $package_detail = $this->db->get_all($sql, $sql_value);
        $return_package_info = array();
        $is_empty = true;
        if (!empty($package_detail)) {
            foreach ($package_detail as $detail) {
                $return_package_info[$detail['sku']] = $detail;
                if ($detail['num'] > 0) {
                    $is_empty = false;
                }
            }
        }


//	    require_model('prm/InvOpModel');
	    //拼装包裹单批次明细,暂不支持一个条码占用多个批次
	    $pack_lof_mx = array();
	    $pack_mx = array();
	    foreach($return_detail as $ks=>$sub_return){
		    $find_sell = $sell_lof_info[$ks];
		    if (empty($find_sell)){
                        $sql = "select * from oms_sell_return_detail where sell_return_code = :sell_return_code and sku = :sku";
                        $find_sell_row = $this->db->get_row($sql,array(":sell_return_code" =>$sell_return_code,":sku" => $ks));
                        $moren = load_model('prm/GoodsLofModel')->get_sys_lof();
                        $find_sell_row['lof_no'] = isset($moren['data']['lof_no'])?$moren['data']['lof_no']:'';
			$find_sell_row['production_date'] = isset($moren['data']['production_date'])?$moren['data']['production_date']:'';
//		    	return $this->format_ret(-1,'',$sub_return['barcode'].'退单条码在订单中找不到');
                    } else {
                        $find_sell_row = $find_sell[0];
                    }
		    //echo '<hr/>$find_sell<xmp>'.var_export($find_sell,true).'</xmp>';
            //实际入库数
            $recv_num = isset($return_package_info[$find_sell_row['sku']]['num']) ? $return_package_info[$find_sell_row['sku']]['num'] : 0;
		    $pack_lof_mx[] = array(
		    	'record_type'=>2,
		    	'record_code'=>$return_package_code,
		    	'deal_code'=>$find_sell_row['deal_code'],
		    	'store_code'=>$store_code,
			    'goods_code'=>$find_sell_row['goods_code'],
			    'spec1_code'=>$find_sell_row['spec1_code'],
			    'spec2_code'=>$find_sell_row['spec2_code'],
			    'sku'=>$find_sell_row['sku'],
			    'barcode'=>$sub_return['barcode'],
			    'lof_no'=>$find_sell_row['lof_no'],
			    'production_date'=>$find_sell_row['production_date'],
			  //  'num'=>$sub_return['note_num'],
                'num' => ($is_empty == true) ? $sub_return['note_num'] : $recv_num,//退货包裹单的实际入库数为准
			    'stock_date'=>date('Y-m-d'),
			    'occupy_type'=>0,
                'create_time'=>time()
		    );
		    $pack_mx[] = array(
		    	'return_package_code'=>$return_package_code,
			    'goods_code'=>$find_sell_row['goods_code'],
			    'spec1_code'=>$find_sell_row['spec1_code'],
			    'spec2_code'=>$find_sell_row['spec2_code'],
			    'sku'=>$find_sell_row['sku'],
			    'barcode'=>$sub_return['barcode'],
			    'apply_num'=>$sub_return['note_num'],
			    'num'=>$sub_return['recv_num']
		    );
                    $update_str = "num = VALUES(num) + num";
                   // $update_str1 = "apply_num = VALUES(apply_num) + apply_num";
            $update_str1 = "apply_num = VALUES(apply_num)";
	    }
	    //echo '<hr/>$pack_mx<xmp>'.var_export($pack_mx,true).'</xmp>';
	    //echo '<hr/>$pack_lof_mx<xmp>'.var_export($pack_lof_mx,true).'</xmp>';die;
	    $this->begin_trans();
            try {
                if($record_type != 1) {
                    $ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof',$pack_lof_mx,$update_str);
                    if ($ret['status']<0){
                        $this->rollback();
                            return $ret;
                    }
                }
                $package_detail = M('oms_return_package_detail')->insert_multi_duplicate('oms_return_package_detail', $pack_mx, $update_str1);
                if ($package_detail['status']<0){
                    $this->rollback();
                        return $package_detail;
                }
            $this->commit();
            } catch (Exception $e) {
                $this->rollback();
                return $this->format_ret(-1, '', $e->getMessage());
            }
            $data = array('pack_lof_mx'=>$pack_lof_mx,'pack_mx'=>$pack_mx,'return_package_code'=>$return_package_code,'store_code'=>$store_code);
	    return $this->format_ret(1,$data);
    }

    function get_package_record($sell_return_code){
        $sql = "select return_package_code,store_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
        $pack_row = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));
        if (empty($pack_row)){

            return $this->format_ret(-1,'',$sell_return_code.' 退单缺少相关联的包裹单');

        }
        $store_code = $pack_row['store_code'];
        $return_package_code = $pack_row['return_package_code'];

        $pack_lof_mx = $this->db->get_all("select * from oms_sell_record_lof where record_code = '{$return_package_code}'");
        foreach ($pack_lof_mx as $key => &$sub_mx){
            if($sub_mx['num'] == 0){
                unset($pack_lof_mx[$key]);
            }
        }
        if(empty($pack_lof_mx)){
            $create_package_ret = $this->create_return_package_detail_by_sell_return_code($sell_return_code);
            if($create_package_ret['status'] < 0){
                 return $create_package_ret;
            }
            $pack_lof_mx = $create_package_ret['data']['pack_lof_mx'];
        }

        $pack_mx = $this->db->get_all("select * from oms_return_package_detail where return_package_code = '{$return_package_code}'");
        if (empty($pack_mx)){
            $create_package_ret = $this->create_return_package_detail_by_sell_return_code($sell_return_code);
            if($create_package_ret['status'] < 0){
                 return $create_package_ret;
            }
            $pack_mx = $create_package_ret['data']['pack_mx'];
        }
        $data = array('pack_lof_mx'=>$pack_lof_mx,'pack_mx'=>$pack_mx,'return_package_code'=>$return_package_code,'store_code'=>$store_code);
        return $this->format_ret(1,$data);
    }

    function get_detail_list_by_return_code($sell_return_code){
        $sql = "select return_package_code,store_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
        $pack_row = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));

        $return_package_code = $pack_row['return_package_code'];
        $detail_sql = " SELECT
                            r1.return_package_code,
                            r1.goods_code,
                            r1.sku,
                            r1.num AS recv_num,
                            r1.apply_num AS note_num
                        FROM
                            oms_return_package_detail r1
                        WHERE return_package_code = '{$return_package_code}' ";
        $data = $this->db->get_all($detail_sql);
        foreach ($data as $key => &$value){
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $data[$key] = array_merge($data[$key], $sku_info);
            $shelf_info = $this->db->get_all("select distinct bs.shelf_name from base_shelf bs left join goods_shelf gs on bs.shelf_code = gs.shelf_code where gs.store_code = :store_code and gs.sku = :sku AND bs.store_code=:store_code ", array(':store_code' => $pack_row['store_code'], ':sku' => $value['sku']));
            $shelf_name = '';
            foreach ($shelf_info as $val){
                $shelf_name .= $val['shelf_name'] . ',';
            }
            $shelf_name = rtrim($shelf_name, ',');
            $value['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
        }
        return $data;
    }

    //验收包裹单，添加库存
    function recv_in_store($return_package_code,$store_code,$stock_date,$pack_lof_mx,$pack_mx){
        ctx()->db->begin_trans();
        $ret_pack_lof_mx = array();
        foreach ($pack_lof_mx as $lof_mx) {
            if ($lof_mx['num'] > 0) {
                $ret_pack_lof_mx[] = $lof_mx;
            }
        }
        if (!empty($ret_pack_lof_mx)) {
            $invobj = new InvOpModel($return_package_code, 'oms_return', $store_code, 3, $ret_pack_lof_mx);
            $invobj->order_date = $stock_date;
            $ret = $invobj->adjust();
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if ($ret['status'] < 1) {
                ctx()->db->rollback();
                return $ret;
            }
        }
        //验收入库人、时间
        $user_name = CTX()->get_session('user_name');
        $date = date('Y-m-d H:i:s');
        //die;
        $upd_arr = array('stock_date'=>$stock_date,'return_order_status'=>1,'receive_person' => $user_name,'receive_time' => $date);
        $wh_arr = array('return_package_code'=>$return_package_code);
        $ret = M('oms_return_package')->update($upd_arr,$wh_arr);
        if ($ret['status']<0){
             ctx()->db->rollback();
	    return $ret;
        }
        $flag = TRUE;
        foreach ($pack_mx as $mx){
            if($mx['num'] == 0){
                continue;
            }
            $flag = FALSE;
        }
        $sku_data = array();
        foreach ($pack_lof_mx as $val){
            $sku_data[$val['sku']] = isset($sku_data[$val['sku']])?$sku_data[$val['sku']]+$val['num']:$val['num'];
        }
        
        if($flag == TRUE){
            foreach ($pack_mx as &$mx){
                if(isset($sku_data[$mx['sku']])){
                    $mx['num'] = $sku_data[$mx['sku']];
                    $sql = "update oms_return_package_detail set num ={$mx['num']} where return_package_detail_id = {$mx['return_package_detail_id']}";
                    $this->db->query($sql);
                }
            }
        }
        
        
        //添加操作日志
        $action_name = '商品入库';
        $action_note = '验收入库';
        $this->insert_package_log_action($return_package_code, $action_name, $action_note,$status = 1);

	ctx()->db->commit();
	return $this->format_ret(1,$pack_mx);
    }

    //包裹单收货明细根据sku回写给对应的退单
    function set_return_recv_num($pack_mx, $return_detail) {
        $pack_mx_arr = array();
        $flag = TRUE;
        foreach ($pack_mx as $sub) {
            $pack_mx_arr[$sub['sku']] = $sub['num'];
            if ($sub['num'] == 0) {
                continue;
            }
            $flag = FALSE;
        }
        //入库情况（1、实际入库数>申请入库数；2、实际入库数<申请入库数；3、实际入库数=申请入库数；4、多交易号同商品（包含前三种情况））
        $same_sku_detail = array();
        foreach($return_detail as $val) {
            $same_sku_detail[$val['sku']][] = $val;
        }
        foreach ($same_sku_detail as $value) {
            $same_sku_num = count($value);
            if ($same_sku_num == 1) { //单交易号
                $sub = $value[0];
                $find_num = $this->get_find_num($sub, $pack_mx_arr, 'single_deal_code');
                $this->update_return_find_num($sub, $find_num, $flag);
            } else { //多交易号
                foreach($value as $sub) {
                    if($same_sku_num == 1) {// 多交易号（通知数》或《入库数），通知数=入库数
                        $find_num = isset($pack_mx_arr[$sub['sku']]) && !empty($pack_mx_arr[$sub['sku']]) ? $pack_mx_arr[$sub['sku']] : 0;
                    } else {
                        $find_num = $this->get_find_num($sub, $pack_mx_arr, 'same_deal_code');
                    }
                    $this->update_return_find_num($sub, $find_num, $flag);
                    $same_sku_num--;
                }
            }
        }
        return $this->format_ret(1, $pack_mx);
    }

    //退单获取完成数量
    function get_find_num($sub, &$pack_mx_arr, $type = 'single_deal_code') {
        if (array_key_exists($sub['sku'], $pack_mx_arr) && $pack_mx_arr[$sub['sku']] > 0) {
            if (($pack_mx_arr[$sub['sku']] - $sub['note_num']) >= 0) {
                if($type == 'single_deal_code') { //单交易号（通知数《入库数），通知数=入库数
                    $find_num = $pack_mx_arr[$sub['sku']];
                    $pack_mx_arr[$sub['sku']] = 0;
                } else { //多交易号，（通知数《入库数），通知数=入库数
                    $pack_mx_arr[$sub['sku']] = $pack_mx_arr[$sub['sku']] - $sub['note_num'];
                    $find_num = $sub['note_num'];
                }
            } else { //单交易号（通知数》入库数），通知数=入库数
                $find_num = $pack_mx_arr[$sub['sku']];
                $pack_mx_arr[$sub['sku']] = 0;
            }
        } else {
            $find_num = 0;
        }
        return $find_num;
    }
    
    //回写退单入库数
    function update_return_find_num($sub, $find_num, $flag) {
        if ($flag === TRUE) {
            $find_num = $sub['note_num'];
        }
        $sku_cost_arr = $this->get_sku_cost(array($sub['sku']), $sub['sell_record_code']);
        $key = $sub['sku'].','.$sub['sell_record_code'];
        if (isset($sku_cost_arr[$key]) && $sku_cost_arr[$key] > 0) {
            $sku_cost = $sku_cost_arr[$key]['cost_price'];
        } else {
            $sku_cost_price_arr = load_model('goods/SkuCModel')->get_sku_info($sub['sku'], array('cost_price'));
            $sku_cost = isset($sku_cost_price_arr['cost_price']) ? $sku_cost_price_arr['cost_price'] : 0;
        }
        $sql = "update oms_sell_return_detail set recv_num = :recv_num , cost_price=:cost_price where sell_return_detail_id = {$sub['sell_return_detail_id']}";
        $ret = $this->db->query($sql, array(':recv_num' => $find_num, 'cost_price' => $sku_cost));
        return $ret;
    }

    //添加退货单明细
    function add_return_detail($pack_mx,$return_detail,$record) {
        $detail_arr = array();
        foreach($return_detail as $key => $value) {
            $detail_arr[] = $value['sku'];
        }
        $flag = TRUE;
        foreach ($pack_mx as $mx){
            if($mx['num'] != 0){
                $flag = FALSE;
                break;  
            }
        }
        //维护商品成本价，优先取订单明细成本价，再取商品档案成本价
        $sku_arr = array_column($pack_mx,'sku');
        $sku_arr = array_unique(array_merge($sku_arr,$detail_arr));
        $sku_cost_arr = $this->get_sku_cost($sku_arr,$record['sell_record_code']);
        foreach($pack_mx as $key => $value) {
            if(in_array($value['sku'],$detail_arr)) {
                continue;
            }
            $key_sku = $value['sku'].','.$record['sell_record_code'];
            if(isset($sku_cost_arr[$key_sku]) && $sku_cost_arr[$key_sku] > 0){
                $sku_cost = $sku_cost_arr[$key_sku]['cost_price'];
            }else{
                $sku_cost_price_arr = load_model('goods/SkuCModel')->get_sku_info($value['sku'],array('cost_price'));
                $sku_cost = isset($sku_cost_price_arr['cost_price']) ? $sku_cost_price_arr['cost_price'] : 0;
            }
            $params['sell_return_code'] = $record['sell_return_code'];
            $params['sell_record_code'] = $record['sell_record_code'];
            $params['deal_code'] = $record['deal_code'];
            $params['goods_code'] = $value['goods_code'];
            $params['sku_id'] = oms_tb_val('goods_sku','sku_id',array('sku' => $value['sku']));
            $params['sku'] = $value['sku'];
            $params['cost_price'] = $sku_cost;
            $params['goods_price'] = $this->get_goods_price($value['sku'],$record['sell_record_code'],$value['goods_code']);
            $params['note_num'] = $value['apply_num'];
            if($flag == TRUE) {
                $params['recv_num'] = $value['apply_num'];
            } else {
                $params['recv_num'] = $value['num'];
            }
            $params['avg_money'] = $params['goods_price'] * $params['note_num'];
            $data[]=$params;
        }
        if(empty($data)) {
            return $this->format_ret('1','','没有不匹配的商品');
        }
        $update_str = "note_num=VALUES(note_num),recv_num=VALUES(recv_num),cost_price=VALUES(cost_price)";
        $ret = $this->insert_multi_duplicate('oms_sell_return_detail', $data, $update_str);
        return $ret;
    }
    function get_goods_price($sku,$sell_record_code,$goods_code) {
        //查询关联的订单有没有该商品，有就取订单均摊金额
        $sql = "SELECT avg_money,num FROM oms_sell_record_detail WHERE sku =:sku AND sell_record_code =:sell_record_code";
        $money = $this->db->get_row($sql, array(':sku' => $sku, ':sell_record_code' => $sell_record_code));
        if (!empty($money) && $money['avg_money'] > 0) {
            $goods_price = $money['avg_money'] / $money['num'];
            $goods_price = round($goods_price,2);
            return $goods_price;
        }
        //查询sku级商品价格
        $sql = "SELECT price FROM goods_sku WHERE sku = :sku";
        $goods_price = $this->db->get_row($sql, array(':sku' => $sku));
        if(!empty($goods_price) && $goods_price['price'] > 0) {
            return $goods_price['price'];
        }
        //查询商品级价格
        $sql = "SELECT sell_price FROM base_goods WHERE goods_code = :goods_code";
        $goods_price = $this->db->get_row($sql, array(':goods_code' => $goods_code));
        if(!empty($goods_price) && $goods_price['sell_price']) {
            return $goods_price['sell_price'];
        }
        return 0;
    }
    function check_express_code_and_no($params){
        $sql="SELECT return_package_id FROM {$this->table} where  return_express_no=:return_express_no";
        $ret=$this->db->get_row($sql, array(':return_express_no'=>$params['return_express_no']));
        if(empty($ret)){
            return true;
        }else{
            return false;
        }
    }
    function add($params){
        foreach($params as &$v){
            $v = @trim($v);
        }
        $country = oms_tb_val('base_area', 'name', array('id' => $params['receiver_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $params['receiver_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $params['receiver_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $params['receiver_district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $params['receiver_street']));
	$params['receiver_mobile'] = isset($params['receiver_mobile']) ? $params['receiver_mobile'] : '';
	$data = array(
            'return_type' => 1,
            'return_order_status' => 0,
            'create_time' => date('Y-m-d H:i:s'),
            'deal_code' => $params['deal_code'],
            'return_package_code' => $params['return_package_code'],
            'sell_return_code' => $params['sell_return_code'],
            'stock_date' => $params['stock_date'],
            'store_code' => $params['store_code'],
            'shop_code' => $params['shop_code'],
            'tag' => $params['tag'] == 1 ? 2 : 0,
            'return_express_code' => $params['return_express_code'],
            'return_express_no' => $params['return_express_no'],
            'return_name' => empty($params['return_name']) ? '' : $params['return_name'],
            'return_mobile' => $params['return_mobile'],
            'return_express_no' => $params['return_express_no'],
            'return_country' => $params['receiver_country'],
            'return_province' => $params['receiver_province'],
            'return_city' => $params['receiver_city'],
            'return_district' => $params['receiver_district'],
            'return_street' => $params['receiver_street'],
            'return_addr' => $params['receiver_addr'],
            'receiver_mobile' => $params['receiver_mobile'],
            'remark' => $params['remark'],
            'buyer_name' => $params['buyer_name'],
            'is_exchange_goods' => 0
        );
        if(!empty($params['sell_return_code'])){
            $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($params['sell_return_code']);
            $shop_code = $this->db->get_value("select shop_code from oms_sell_return where sell_return_code = :sell_return_code",array(":sell_return_code" => $params['sell_return_code']));
            $data['shop_code'] = !empty($shop_code)?$shop_code:(isset($return_record['shop_code']) ? $return_record['shop_code'] : '');
            $data['customer_code'] = isset($return_record['customer_code']) ? $return_record['customer_code'] : '';
        }
        //过滤换行
        $data['return_address'] = str_replace(array("\r\n", "\r", "\n"), "", $data['return_address']);   
        $data['return_addr'] = str_replace(array("\r\n", "\r", "\n"), "", $data['return_addr']); 
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), "", $data['remark']);  
        
             $customer_address_array['address'] = $data['return_addr'];
                $customer_address_array['country'] = $data['return_country'];
                $customer_address_array['province'] = $data['return_province'];
                $customer_address_array['city'] = $data['return_city'];
                $customer_address_array['district'] = $data['return_district'];
                $customer_address_array['street'] = $data['return_street'];
                $customer_address_array['tel'] = $data['return_mobile'];
                $customer_address_array['home_tel'] = $data['return_phone'];
                $customer_address_array['name'] = $data['return_name'];
                $customer_address_array['buyer_name'] = $data['buyer_name'];
                 $customer_address_array['shop_code'] = $data['shop_code'];
                if(isset($data['customer_code'])){
                    $customer_address_array['customer_code'] = $data['customer_code'];
                    $ret_create = load_model('crm/CustomerOptModel')->create_customer_address($customer_address_array);
                    if($ret_create['status']<1){
                         return $ret_create;
                    }
                    $data['customer_address_id'] = $ret_create['data']['customer_address_id'];
                }else if(!empty($data['buyer_name'])){
                    $customer_address_array['shop_code'] = $data['shop_code'];
                    $customer_address_array['customer_name'] = $data['buyer_name'];
                    $customer_address_array['source'] = $this->db->get_value("select sale_channel_code from base_shop where shop_code=:shop_code",array(':shop_code'=>$data['shop_code']));
                    $ret_create = load_model('crm/CustomerOptModel')->handle_customer($customer_address_array) ;
                    $data['customer_address_id'] = $ret_create['data']['customer_address_id'];
                    $data['customer_code'] = $ret_create['data']['customer_code'];
                }
                if(!empty($data['customer_address_id'])){
                    $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($data['customer_address_id']);
                    $data['return_addr'] = empty($customer_address['address']) ? '' : $customer_address['address'];
                    $data['return_phone'] = empty($customer_address['home_tel']) ? '' : $customer_address['home_tel'];
                    $data['return_name'] = empty($customer_address['name']) ? '' : $customer_address['name'];
                    $data['return_mobile'] = empty($customer_address['tel']) ? '' : $customer_address['tel'];
                    $data['buyer_name'] = load_model('crm/CustomerOptModel')-> get_customer_name( $data['customer_code']);
                }
                $data['return_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $data['return_addr'];
        if(empty($data['buyer_name'])){
            unset($data['buyer_name']);
        }
            
        $ret = M('oms_return_package')->insert($data);
        if($ret['status'] == 1){
            $this->insert_package_log_action($params['return_package_code'], "创建退货包裹单", "手工增加退货包裹单");
            $ret['data'] = $params['return_package_code'];
        }
	return $ret;
    }

    public function edit_action($data, $where) {
        foreach ($data as &$v){
            $v = @trim($v);
        }
        if (!isset($where['return_package_code'])) {
            return $this->format_ret('-1', '', '包裹单不存在');
        }
        $record = $this->get_return_package_by_code($where['return_package_code']);

        $customer_code = $record['customer_code'];
        $country = oms_tb_val('base_area', 'name', array('id' => $data['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $data['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $data['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $data['district']));
   
            if(!empty($customer_code)){
                $customer_address_array['address'] = $data['return_addr'];
                $customer_address_array['country'] = $data['country'];
                $customer_address_array['province'] = $data['province'];
                $customer_address_array['city'] = $data['city'];
                $customer_address_array['district'] = $data['district'];
               // $customer_address_array['street'] = $data['return_street'];
                $customer_address_array['tel'] = $data['return_mobile'];
                $customer_address_array['home_tel'] = $data['return_phone'];
                $customer_address_array['name'] = $data['return_name'];
                $customer_address_array['customer_code'] = $record['customer_code'];
                $customer_address_array['shop_code'] = $record['shop_code'];
                $buyer_name =  load_model('crm/CustomerOptModel')->get_buyer_name_by_code($customer_address_array['customer_code'],$record['customer_address_id']);
             
                if($buyer_name===false){
                     return $this->format_ret(-1,'','暂时不能修改，安全解密异常！');
                }
                $customer_address_array['buyer_name'] = $buyer_name;
 
                $ret_create = load_model('crm/CustomerOptModel')->create_customer_address($customer_address_array);

                if($ret_create['status']<1){
                     return $ret_create;
                }
                
                $data['customer_address_id'] = $ret_create['data']['customer_address_id'];
    
                $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($data['customer_address_id']);
                $data['return_addr'] = $customer_address['address'];
                $data['return_phone'] = $customer_address['home_tel'];
                $data['return_name'] = $customer_address['name'];
                $data['return_mobile'] = $customer_address['tel'];
            }

        $data['return_country'] = $data['country'];
        $data['return_province'] = $data['province'];
        $data['return_city'] = $data['city'];
        $data['return_district'] = $data['district'];
        $data['return_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $data['return_addr'];
        
        
      //  $ret = M('oms_return_package')->update($data, $where);
        $old_data=$this->get_row(array('return_package_code'=>$where['return_package_code']));
        $package_data=$old_data['data'];
        $log_msg = '';
        if ($package_data['stock_date'] != $data['stock_date']) {
            $log_msg.=" 业务日期由" . $package_data['stock_date'] . "改为" . $data['stock_date'];
        }
        if ($package_data['store_code'] != $data['store_code']) {
            $pack_store_name=oms_tb_val('base_store','store_name',array('store_code'=>$package_data['store_code']));
            $new_store_name=oms_tb_val('base_store','store_name',array('store_code'=>$data['store_code']));
            $log_msg.=" 退货仓库由" . $pack_store_name . "改为" . $new_store_name;
        }
        if ($package_data['return_name'] != $data['return_name']) {
            $log_msg.=" 退货人姓名由" . $package_data['return_name'] . "改为" . $data['return_name'];
        }
        if ($package_data['return_mobile'] != $data['return_mobile']) {
            $log_msg.=" 退货人手机由" . $package_data['return_mobile'] . "改为" . $data['return_mobile'];
        }
        if ($package_data['return_address'] != $data['return_address']) {
            $log_msg.=" 退货地址由" . $package_data['return_address'] . "改为" . $data['return_address'];
        }
        if ($package_data['shop_code'] != $data['shop_code']) {
            $shop_name=oms_tb_val('base_shop','shop_name',array('shop_code'=>$package_data['shop_code']));
            $new_shop_name=oms_tb_val('base_shop','shop_name',array('shop_code'=>$data['shop_code']));
            $log_msg.=" 退货店铺由" . $shop_name . "改为" . $new_shop_name;
        }
        if ($package_data['return_express_code'] != $data['return_express_code']) {
            $pack_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $package_data['return_express_code']));
            $new_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $data['return_express_code']));
            $log_msg.=" 配送方式由" . $pack_express_name . "改为" . $new_express_name;
        }
        if ($package_data['return_express_no'] != $data['return_express_no']) {
            $log_msg.=" 快递单号由" . $package_data['return_express_no'] . "改为" . $data['return_express_no'];
        }
        if ($package_data['sell_return_code'] != $data['sell_return_code']) {
            $log_msg.=" 关联退单号由" . $package_data['sell_return_code'] . "改为" . $data['sell_return_code'];
        }
        if ($package_data['sell_record_code'] != $data['sell_record_code']) {
            $log_msg.=" 关联订单号由" . $package_data['sell_record_code'] . "改为" . $data['sell_record_code'];
        }
        if ($package_data['deal_code'] != $data['deal_code']) {
            $log_msg.=" 交易号由" . $package_data['deal_code'] . "改为" . $data['deal_code'];
        }
        if ($package_data['remark'] != $data['remark']) {
            $log_msg.=" 备注由" . $package_data['remark'] . "改为" . $data['remark'];
        }
        $ret=$this->db->update('oms_return_package',$data,$where);
        $row=$this->affected_rows();
        if ($ret && $row != 0) {
            //添加日志
            $this->insert_package_log_action($where['return_package_code'], "修改", $log_msg);
        }
        //更新主表数据
        return $ret;
    }

    public function is_exists($value,$field){
        return $this->db->get_row("select * from oms_return_package where {$field} = :field",array(':field' => $value));
    }

    public function is_detail_exists($return_package_code,$sku){
        $ret = $this->db->get_row("select * from oms_return_package_detail where return_package_code = :return_package_code and sku =:sku",
                array(':return_package_code' => $return_package_code, ':sku' => $sku));
        return $ret;
    }

    function get_detail_by_page($filter){
        $sql_values = array();
        $sql_join = " LEFT JOIN base_goods rr ON rl.goods_code = rr.goods_code ";
        $sql_main = "FROM {$this->detail_table} rl $sql_join WHERE 1 ";

        if(isset($filter['code_name'])&&$filter['code_name']!=''){
            $sql_main .= " AND rl.goods_code like :code_name";
            $sql_values[':code_name'] = "%".$filter['code_name']."%";
        }

        if(isset($filter['return_package_code'])&&$filter['return_package_code']!=''){
            $sql_main .= " AND rl.return_package_code = :return_package_code";
            $sql_values[':return_package_code'] = $filter['return_package_code'];
        }


        $select = 'rl.*,rr.goods_thumb_img';
        $sql_main .= " ORDER BY return_package_detail_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //库位
        $sql = " SELECT r2.sku,r4.shelf_name FROM oms_return_package r1 
                LEFT JOIN oms_return_package_detail r2 ON r1.return_package_code = r2.return_package_code 
                LEFT JOIN goods_shelf r3 ON r1.store_code = r3.store_code AND r3.sku = r2.sku 
                LEFT JOIN base_shelf r4 ON r3.store_code = r4.store_code AND r3.shelf_code = r4.shelf_code where r1.return_package_code = :return_package_code  ";
        $sql_val[':return_package_code'] = $filter['return_package_code'];
        $ret = $this->db->get_all($sql, $sql_val);
        foreach ($ret as $value) {
            $shelf[$value['sku']][]= $value['shelf_name'];
        }
        foreach ($shelf as $key => $value) {
            $shelf_name[$key] = implode(',', $value);
        }
        foreach($data['data'] as $key=>&$value){
            $key_arr = array('spec1_name','spec2_name','barcode','goods_name');
            $value['shelf_name'] = $shelf_name[$value['sku']];
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $data['data'][$key] = array_merge($data['data'][$key],$sku_info);
            $value['goods_thumb_img_src'] = "<img src = ".$value['goods_thumb_img']." style='width:48px; height:48px;'>";
        }
        return $this->format_ret(1, $data);
    }
    
    /**
     * 退货包裹单批次数据查询
     * @param array $filter 条件
     * @return array 数据集
     */
    function get_lof_detail_by_page($filter){
        $sql_values = array();
        $sql_join = "INNER JOIN oms_sell_record_lof AS rl ON rl.record_code=rd.return_package_code AND rl.sku=rd.sku 
                     LEFT JOIN base_goods rr ON rl.goods_code = rr.goods_code ";
        $sql_main = "FROM {$this->detail_table} rd $sql_join WHERE rl.record_type=2 ";

        if(isset($filter['code_name'])&&$filter['code_name']!=''){
            $sql_main .= " AND rd.goods_code like :code_name";
            $sql_values[':code_name'] = "%".$filter['code_name']."%";
        }

        if(isset($filter['return_package_code'])&&$filter['return_package_code']!=''){
            $sql_main .= " AND rd.return_package_code = :return_package_code";
            $sql_values[':return_package_code'] = $filter['return_package_code'];
        }


        $select = 'rd.return_package_detail_id,rd.return_package_code,rd.goods_code,rd.spec1_code,rd.spec2_code,rd.sku,rd.barcode,rd.apply_num,rl.lof_no,rl.production_date,rl.num,rr.goods_thumb_img';
        $sql_main .= " ORDER BY return_package_detail_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach($data['data'] as $key=>&$value){
            $key_arr = array('spec1_name','spec2_name','barcode','goods_name');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $data['data'][$key] = array_merge($data['data'][$key],$sku_info);
            $value['goods_thumb_img_src'] = "<img src = ".$value['goods_thumb_img']." style='width:48px; height:48px;'>";
        }
        return $this->format_ret(1, $data);
    }

    function get_by_sell_return_code($sku,$return_package_code){
        $recv_num = 0; //完成数默认为0
        $package = $this->get_return_package_by_code($return_package_code);
        if(!empty($package['sell_return_code'])){
            $sell_return = $this->db->get_row("select * from oms_sell_return_detail where sell_return_code = '{$package['sell_return_code']}' and sku='{$sku}'");
            if(!empty($sell_return)){
                $recv_num = $sell_return['recv_num'];
            }
        }
        return $recv_num;
    }

    public function add_detail_goods($id,$data){ 
         //判断主单据是否存在
        $record = $this->is_exists($id, 'return_package_id');
        if (empty($record)) {
            return $this->format_ret(false,array(),'退货包裹单明细所关联的主单据不存在!');
        }
        
        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code('is_allowed_exceed');
        //开启参数，退货商品数，不允许超过退单商品数(绑定退单)
        if($is_allowed_exceed['is_allowed_exceed'] == 1 && !empty($record['sell_return_code'])) {
            $ret = $this->record_check_num($data,$record['sell_return_code']);
            if($ret['status'] < 0) {
                return $ret;
            }
        }

        $log = '增加明细：';
        $lof_data = load_model("prm/GoodsLofModel")->get_sys_lof();
        $lof_data = $lof_data['data'];
            
        $this->begin_trans();
        try {
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1,'','批次档案维护失败');
            }

            foreach ($data as $ary_detail) {
                if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                    continue;
                }

                $ins_arr[] = array(
                    'return_package_code' => $record['return_package_code'],
                    'goods_code' => $ary_detail['goods_code'],
                    'spec1_code' => $ary_detail['spec1_code'],
                    'spec2_code' => $ary_detail['spec2_code'],
                    'sku' => $ary_detail['sku'],
                    'barcode' => $ary_detail['barcode'],
                    'apply_num' => $ary_detail['num'],
                    'num' => $this->is_api === 1 ? $ary_detail['num'] : 0,
                );
                if ($this->is_api === 1) {
                    $sub_ins_lof_arr = array(
                        'record_type' => 2,
                        'record_code' => $record['return_package_code'],
                        'deal_code' => $record['deal_code'],
                        'store_code' => $record['store_code'],
                        'goods_code' => $ary_detail['goods_code'],
                        'spec1_code' => $ary_detail['spec1_code'],
                        'spec2_code' => $ary_detail['spec2_code'],
                        'sku' => $ary_detail['sku'],
                        'barcode' => $ary_detail['barcode'],
                        'lof_no' => $ary_detail['lof_no'],
                        'production_date' => $ary_detail['production_date'],
                        'num' => $ary_detail['num'],
                        'stock_date' => date('Y-m-d'),
                        'occupy_type' => 0,
                        'create_time' => time()
                    );

                    if (empty($sub_ins_lof_arr['lof_no'])) {
                        $sql = "select lof_no,production_date from oms_sell_record_lof WHERE record_code = '{$record['return_package_code']}' AND sku='{$ary_detail['sku']}'";

                        $sub_lof = $this->db->getRow($sql);

                        if (!empty($sub_lof)) {
                            $sub_ins_lof_arr['lof_no'] = $sub_lof['lof_no'];
                            $sub_ins_lof_arr['production_date'] = $sub_lof['production_date'];
                        } else {
                            $sub_ins_lof_arr['lof_no'] =$lof_data['lof_no'];
                            $sub_ins_lof_arr['production_date'] = $lof_data['production_date'];
                        }
                    }

                    $ins_lof_arr[] = $sub_ins_lof_arr;
                }
                $log .= "barcode:{$ary_detail['barcode']},数量:{$ary_detail['num']};";
            }
            $update_str = "apply_num = VALUES(apply_num)";

            $package_detail = M('oms_return_package_detail')->insert_multi_duplicate('oms_return_package_detail', $ins_arr, $update_str);
            if ($package_detail['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '保存明细出错');
            }

            if ($this->is_api === 1) {
                $lof_package_ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof', $ins_lof_arr, 'num = VALUES(num)');

                if ($lof_package_ret['status'] < 0) {
                    $this->rollback();
                    return $lof_package_ret;
                }
            }

            $this->insert_package_log_action($record['return_package_code'], "增加明细", $log);

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);

    }
    function record_check_num($data, $sell_return_code) {
        $message = '';
        //查询订单商品
        $sql = "SELECT sku,note_num,barcode FROM oms_sell_return_detail WHERE sell_return_code = :sell_return_code";
        $record_detail_arr = $this->db->get_all($sql, array(':sell_return_code' => $sell_return_code));
        $detail_arr = load_model('util/ViewUtilModel')->get_map_arr($record_detail_arr, 'sku');
        foreach ($data as $val) {
            if (isset($detail_arr[$val['sku']]) && $detail_arr[$val['sku']]['note_num'] < $val['num']) {
                $message = '条码为：' . $val['barcode'] . '的数量超过退单数量。';
                break;
            }
        }
        if (empty($message)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', $message);
        }
    }

    function get_package_action_by_page($filter){
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM oms_return_package_action rl $sql_join WHERE 1 ";

        if(isset($filter['return_package_code'])&&$filter['return_package_code']!=''){
            $sql_main .= " AND rl.return_package_code = :return_package_code";
            $sql_values[':return_package_code'] = $filter['return_package_code'];
        }

        $select = 'rl.*';
        $sql_main .= " ORDER BY return_package_action_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $value['order_status'] = isset($value['order_status']) ? $value['order_status'] : '';
        foreach($data['data'] as $key=>&$value){
            $value['status_name'] = $value['order_status'] == 1 ? '已收货' : '未收货';
        }
        return $this->format_ret(1, $data);
    }

    /**
     * @todo 新增日志
     */
    function insert_package_log_action($code, $action_name, $action_note, $status = 0) {
        $data = array();
        $data['return_package_code'] = $code;
        $data['action_name'] = $action_name;
        $data['action_note'] = $action_note;
        $data['order_status'] = $status;
        if ($this->is_api != 1) {
            $data['user_code'] = CTX()->get_session('user_code');
            $data['user_name'] = CTX()->get_session('user_name');
            if (empty($data['user_code']) || empty($data['user_name'])) {
                return $this->format_ret(-1, '', '用户登录数据错误');
            }
        } else {
            $data['user_code'] = 'admin';
            $data['user_name'] = '系统管理员';
        }

        $ret = M('oms_return_package_action')->insert($data);

        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '保存日志出错');
        }
        return $this->format_ret(1);
    }

    function do_package_detail_delete($data){
        $detail = $this->db->get_row("select * from oms_return_package_detail where return_package_detail_id = {$data['return_package_detail_id']}");
        if(empty($detail)){
            return $this->format_ret("-1",'','明细信息不存在');
        }
        $record = $this->db->get_row("select * from oms_return_package where return_package_code = '{$data['return_package_code']}'");
        if($record['return_order_status'] == 1){
            return $this->format_ret("-1",'','单据已验收入库');
        }
        $ret = $this->db->create_mapper('oms_return_package_detail')->delete(array('return_package_detail_id' => $data['return_package_detail_id']));
        $this->db->create_mapper('oms_sell_record_lof')->delete(array('record_code' => $detail['return_package_code'],'sku'=>$detail['sku']));

        if ($ret) {
            $this->insert_package_log_action($detail['return_package_code'], "删除明细", "删除明细,barcode:{$detail['barcode']}");
            return $this -> format_ret("1", $ret, 'delete_success');
        } else {
            return $this -> format_ret("-1", '', 'delete_error');
        }
    }

    public function update_detail_num($sell_return_code,$sku,$recv_num=0) {
        $sql = "select return_package_code,store_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
        $pack_row = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));
        if (!empty($pack_row)){
            $data = array();
            $return_package_code = $pack_row['return_package_code'];
            $pack_detail = $this->db->get_row("select * from oms_return_package_detail where return_package_code = :return_package_code and sku = :sku", array(":return_package_code" => $return_package_code,":sku" => $sku));

            $data['num'] = $pack_detail['num'] + 1;
            $num = $recv_num != 0 ? $recv_num : 'num+1';
            $sql = "update oms_return_package_detail set num = {$num} where return_package_code = '{$return_package_code}' and sku = '{$sku}'";
            $this->db->query($sql);
            return $this->format_ret(1,$pack_detail['barcode']);
        }
        return $this->format_ret(-1,$pack_row);
    }
    function is_return_code($param) {
        $sql = "SELECT shop_code,buyer_name FROM oms_sell_return WHERE sell_return_code = '{$param['sell_return_code']}';";
        $data = $this->db->get_row($sql);
        if(empty($data)) {
            return $this->format_ret(-1,'','不是有效的退单号');
        }
        return $this->format_ret(1, $data);
    }

    /**
     * @todo 通过退货包裹单号获取明细
     */
    function get_detail_list_by_return_package_code($return_package_code){
        $sql = "select store_code from {$this->table} where return_package_code =:return_package_code ";
        $store_code = $this->db->get_value($sql,array(':return_package_code'=>$return_package_code));

        $sql = "SELECT rl.*,rr.goods_thumb_img FROM {$this->detail_table} rl LEFT JOIN base_goods rr ON rl.goods_code = rr.goods_code WHERE rl.return_package_code=:return_package_code";
        $values = array(":return_package_code" => $return_package_code);
        $data = $this->db->get_all($sql, $values);
        foreach ($data as $key => &$value){
            $key_arr = array('sepc1_code', 'spec1_name', 'sepc2_code','spec2_name','barcode','goods_name');
            $sku_info =  load_model('goods/SkuCModel')->get_sku_info($value['sku'],$key_arr);
            $data[$key] = array_merge($data[$key], $sku_info);
            $value['note_num'] = $value['apply_num'];
            $value['recv_num'] = $value['num'];
            $value['goods_thumb_img_src'] = "<img src = ".$value['goods_thumb_img']." style='width:48px; height:48px;'>";

            $shelf_info = $this->db->get_all("select distinct bs.shelf_name from base_shelf bs left join goods_shelf gs on bs.shelf_code = gs.shelf_code where gs.store_code = :store_code and gs.sku = :sku AND bs.store_code=:store_code", array(':store_code' => $store_code, ':sku' => $value['sku']));
            $shelf_name = '';
            foreach ($shelf_info as $val){
                $shelf_name .= $val['shelf_name'] . ',';
            }
            $shelf_name = rtrim($shelf_name, ',');
            $value['shelf_name'] = isset($shelf_name) ? $shelf_name : '';
        }
        return $data;
    }

    function get_return_package_code($sell_return_code){
        $sql = "select return_package_code,store_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
        $pack_row = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));
        return empty($pack_row) ? '' : $pack_row['return_package_code'];

    }

    /**
     * 方法名        api_return_package_create
     * 功能描述      API新增无名退货包裹单
     * @author      BaiSon PHP R&D
     * @date        2016-03-09
     * @param       array $param
     *              array(
     *                  必选: 'init_code', 'store_code','express_code','express_no',
     *                        barcode_list['barcode','recv_num']
     *                  可选：'sell_record_code','return_name', 'return_mobile','return_memo','remark',
     *                        'shop_code','province','city','district','street','addr','address','buyer_name'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","data":"","message":"操作成功"}
     */
    public function api_return_package_create($param) {
        if (!isset($param['barcode_list']) || empty($param['barcode_list'])) {
            return $this->format_ret(-10001, array('barcode_list'), 'API_RETURN_MESSAGE_10001');
        }
        $package_detail = $param['barcode_list'];
        unset($param['barcode_list']);
        $package_record = $param;
        $ret = $this->api_check_params($package_record, $package_detail);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $record = $ret['data']['record'];
        $detail = $ret['data']['detail'];
        $this->begin_trans();
        $status = '-1';
        $data_msg = array();
        try {
            $this->api_deal_address($record);
            $sell_record = array();
            if (isset($record['sell_record_code']) && !empty($record['sell_record_code'])) {
                $sql = 'SELECT shop_code,buyer_name,receiver_name AS return_name,receiver_address AS return_address,receiver_addr AS return_addr,receiver_province AS return_province,receiver_city AS return_city,receiver_district AS return_district,receiver_street AS return_street,receiver_mobile AS return_mobile,receiver_name AS reutrn_name FROM oms_sell_record WHERE sell_record_code=:code';
                $sell_record = $this->db->get_row($sql, array(':code' => $record['sell_record_code']));
            }
            if (!empty($sell_record)) {
                $record = array_merge($sell_record, $record);
            }

            //生成退货包裹单号
            $record['return_package_code'] = 'BG' . load_model('util/CreateCode')->get_code('oms_return_package');
            $record['return_type'] = 1;
            $record['tag'] = 2;
            $record['return_express_code'] = isset($record['express_code']) ? $record['express_code'] : '';
            $record['return_express_no'] = isset($record['express_no']) ? $record['express_no'] : '';
            $record['return_order_status'] = 0;
            $record['return_country'] = 1;
            $record['create_time'] = date('Y-m-d H:i:s');
            $record['stock_date'] = date('Y-m-d');
            $record['remark'] = isset($record['remark']) && !empty($record['remark']) ? '备注：' . $record['remark'] : '';
            $record['remark'] .= isset($record['return_memo']) && !empty($record['return_memo']) ? ';退货原因：' . $record['return_memo'] : '';
            $record['return_address'] = empty($record['return_address']) ? '' : $record['return_address'];
            $record['return_addr'] = empty($record['return_addr']) ? $record['return_address'] : $record['return_addr'];
            if (!empty($record['shop_id'])) {
                $shop = $this->db->get_row('SELECT shop_code FROM base_shop WHERE shop_id=:shop_id', [':shop_id' => $record['shop_id']]);
                if (!empty($shop['shop_code'])) {
                    $record['shop_code'] = $shop['shop_code'];
                } else {
                    $record['remark'] .= ';店铺ID：' . $record['shop_id'];
                }
            }
            unset($record['express_code'], $record['express_no']);

            //创建主单据
            $ret = $this->insert($record);
            if ($ret['status'] != 1) {
                throw new Exception('创建失败');
            }
            $package_id = $ret['data']; //包裹单id
            $this->is_api = 1;
            $this->insert_package_log_action($record['return_package_code'], "API创建无名退货包裹单", "API增加退货包裹单");

            //添加明细
            $ret = $this->add_detail_goods($package_id, $detail);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message']);
            }

            M('api_open_logs')->insert_dup(array('key_id' => $record['init_code']));
            $aff_row = $this->affected_rows();
            if ($aff_row != 1) {
                $status = '-10003';
                $data_msg['init_code'] = $record['init_code'];
                throw new Exception('退货包裹单已存在');
            }
            $this->commit();
            return $this->format_ret(1, array('return_package_code' => $record['return_package_code']), '操作成功');
            $this->is_api = 0;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * API-处理地址数据
     */
    private function api_deal_address(&$record) {
        $addr_arr = array('province', 'city', 'district', 'street');
        if (!isset($record['return_address'])) {
            $address = '';
            foreach ($addr_arr as $v) {
                $address .= isset($record[$v]) ? $record[$v] : '';
            }
            $address .= isset($record['return_addr']) ? $record['return_addr'] : '';
            if (!empty($address)) {
                $record['return_address'] = '中国' . $address;
            }
        }
        foreach ($record as $key => &$val) {
            if (in_array($key, $addr_arr)) {
                $record['return_' . $key] = $this->api_match_area($key, $val);
                unset($record[$key]);
                continue;
            }
        }
    }

    /**
     * API-匹配地址ID
     */
    private function api_match_area($filed, $value) {
        $type = array('province' => 2, 'city' => 3, 'district' => 4, 'street' => 5);
        $sql = "SELECT id FROM base_area WHERE type='{$type[$filed]}' AND name LIKE '{$value}%'";
        $res = $this->db->get_value($sql);
        return $res;
    }

    /**
     * @todo 校验接口传入参数
     */
    private function api_check_params($record, $detail) {
        $key_required = array(
            's' => array('init_code', 'store_code', 'express_code', 'express_no'),
        );
        //可选字段
        $key_option = array(
            's' => array('sell_record_code', 'return_name', 'return_mobile', 'return_memo', 'remark','shop_code','province','city','district','street','addr','address','buyer_name','shop_id','buyer_name','return_address')
        );
        $detail_required = array(
            's' => array('barcode'),
            'i' => array('recv_num')
        );
        $r_required = array();
        $r_option = array();
        $status = '-1';
        $data_msg = array();
        try {
            //验证必选字段是否为空并提取必选字段数据
            $ret_required = valid_assign_array($record, $key_required, $r_required, TRUE);
            if ($ret_required['status'] != TRUE) {
                $status = '-10001';
                $data_msg = $ret_required['req_empty'];
                throw new Exception('API_RETURN_MESSAGE_10001');
            }

            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($record, $key_option, $r_option);
            $new_record = array_merge($r_required, $r_option); //主单据数据
            if(isset($new_record['sell_record_code'])){
                $new_record['sell_record_code'] = trim($new_record['sell_record_code']);
            }
            //校验原单号是否存在
            $check_code = $this->is_exists($record['init_code'], 'init_code');
            if (!empty($check_code)) {
                $status = '-10003';
                $data_msg['init_code'] = $record['init_code'];
                throw new Exception('退货包裹单已存在');
            }
            unset($r_required, $r_option, $record);

            //校验仓库是否存在
            $sql = 'SELECT s.shop_store_code,wms_params FROM wms_config c
                    INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
                    WHERE s.p_type=1 AND s.outside_type=1 AND s.outside_code=:code';
            $check_store = $this->db->get_row($sql, array(':code' => $new_record['store_code']));
            if (empty($check_store['shop_store_code'])) {
                $status = '-10002';
                $data_msg['store_code'] = $new_record['store_code'];
                throw new Exception('仓库不存在');
            } else {
                $new_record['store_code'] = $check_store['shop_store_code'];
            }

            $detail = json_decode($detail, true);
            $d_required = array();
            $new_detail = array();
            
            //获取wms配置参数
            $wms_params = (array)json_decode($check_store['wms_params'],true);
            $sql = "SELECT bg.goods_code,sk.spec1_code,sk.spec2_code,sk.sku,sk.barcode FROM base_goods AS bg
                    INNER JOIN goods_sku AS sk ON bg.goods_code=sk.goods_code WHERE ";
            
            $sql .= $wms_params['goods_upload_type'] == 1 ?  ' sk.sku=:barcde_or_sku ' : ' sk.barcode=:barcde_or_sku ';
            foreach ($detail as $d) {
                $ret_d_required = valid_assign_array($d, $detail_required, $d_required, TRUE);
                if ($ret_d_required['status'] != TRUE) {
                    $status = '-10001';
                    $data_msg = $ret_d_required['req_empty'];
                    throw new Exception('API_RETURN_MESSAGE_10001');
                }
                if ($d_required['recv_num'] <= 0 || !is_int($d_required['recv_num'] * 1)) {
                    $status = '-1';
                    $data_msg = $d;
                    throw new Exception('实际收货数无效(应为大于0的整数)');
                }
                $ret_barcode = $this->db->get_row($sql, array(':barcde_or_sku' => $d_required['barcode']));
                if (empty($ret_barcode)) {
                    $status = '-10001';
                    $data_msg['barcode'] = $d_required['barcode'];
                    throw new Exception('barcode记录不存在');
                }
                $ret_barcode['num'] = $d_required['recv_num'];
                $new_detail[] = $ret_barcode;
            }

            $data = array();
            $data['record'] = $new_record;
            $data['detail'] = $new_detail;
            return $this->format_ret(1, $data);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    function do_edit_package_detail($params){
        $sql ="SELECT * FROM oms_return_package WHERE return_package_code = '{$params['return_package_code']}' ";
        $record = $this->db->get_row($sql);
        if(empty($record)) {
            return $this->format_ret('-1','','主单据不存在');
        }
        if($record['return_order_status'] == 1) {
            return $this->format_ret('-1','','商品已验收入库');
        }
        //查询售后服务单明细
        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code('is_allowed_exceed');
        if($is_allowed_exceed['is_allowed_exceed'] == 1 && !empty($record['sell_return_code'])) {
            $sql_detail = "select note_num from oms_sell_return_detail where sell_return_code = :sell_return_code and sku = :sku";
            $return_data = $this->db->get_row($sql_detail, array(':sell_return_code' => $record['sell_return_code'], ':sku' => $params['sku']));
            if($params['num'] > $return_data['note_num']) {
                return $this->format_ret(-1,'',$params['barcode'].'条码，实际入库数不允许超过退单申请入库数');
            }
        }
        
        $sql = "SELECT * FROM oms_return_package_detail WHERE return_package_code = :return_package_code AND sku = :sku ";
        $ary_detail = $this->db->get_row($sql,array(':return_package_code' => $params['return_package_code'],':sku' => $params['sku']));
        if(empty($ary_detail)) {
            return $this->format_ret('-1','','明细不存在');
        }
        if($ary_detail['num'] == $params['num']) {
            return $this->format_ret(1);
        }
        try {
        $this->begin_trans();
//            if(!empty($params["sell_return_code"])) {
//                //修改售后服务单完成数量
//                $this->db->update('oms_sell_return_detail', array('recv_num' => $params['num']),array('sell_return_code' => $params['sell_return_code'],'sku' => $params['sku']));
//                $ret = $this->affected_rows();
//                if($ret != 1) {
//                    $this->rollback();
//                    return $this->format_ret('-1','','操作出错');
//                }
//            }

            //修改包裹单入库数量
            $this->db->update('oms_return_package_detail', array('num' => $params['num']), array('sku' => $params['sku'],'return_package_code' => $params['return_package_code']));
            $ret = $this->affected_rows();
            if($ret != 1) {
                $this->rollback();
                return $this->format_ret('-1','','操作出错');
            }
//            if($params['num'] > 0) {
//                //如果数量大于零，就维护oms_sell_record_lof表
//                $record_lof_ret = $this->insert_sell_record_lof($record,$ary_detail,$params['sell_return_code'],$params['num']);
//                if($record_lof_ret['status'] != 1){
//                    $this->rollback();
//                    return $record_lof_ret;
//                }
//            }
//            //如果修改的数量等于零，就删除oms_sell_record_lof表数据
//            if($params['num'] == 0) {
//                $r = $this->delete_exp('oms_sell_record_lof', array('sku'=>$params['sku'],'record_code'=>$params['return_package_code']));
//            }
            $log = '修改明细：barcode:'.$params['barcode'].',数量修改为:'.$params['num'];
            $r = $this->insert_package_log_action($params['return_package_code'], "修改明细", $log);
            $this->commit();
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function insert_sell_record_lof($record,$ary_detail,$sell_return_code,$num) {
        $sub_ins_lof_arr = array(
                'record_type' => 2,
                'record_code' => $record['return_package_code'],
                'deal_code' => $record['deal_code'],
                'store_code' => $record['store_code'],
                'goods_code' => $ary_detail['goods_code'],
                'spec1_code' => $ary_detail['spec1_code'],
                'spec2_code' => $ary_detail['spec2_code'],
                'sku' => $ary_detail['sku'],
//                'barcode' => $ary_detail['barcode'],
                'num' => $num,
//                'stock_date' => date('Y-m-d'),
                'occupy_type' => 0,
                'create_time' => time()
            );
            //是否开启批次
            $is_lof = load_model('sys/SysParamsModel')->get_val_by_code('lof_status');
            $lof_no = load_model('sys/SysParamsModel')->get_val_by_code('default_lof_no');
            $production_date = load_model('sys/SysParamsModel')->get_val_by_code('default_lof_production_date');

            $sub_ins_lof_arr['lof_no'] = $lof_no['default_lof_no'];
            $sub_ins_lof_arr['production_date'] = $production_date['default_lof_production_date'];

            if(!empty($sell_return_code) && $is_lof['lof_status'] == 1){
                $sell_record_code = $this->db->get_value("SELECT sell_record_code FROM oms_sell_return WHERE sell_return_code = '{$sell_return_code}'");
                $sql = "SELECT lof_no,production_date FROM oms_sell_record_lof WHERE record_type = '1' AND sku = '{$ary_detail['sku']}' AND record_code = '{$sell_record_code}';";
                $data = $this->db->get_all($sql);
                //无论有没有相同的批次都去第一个
                $sub_ins_lof_arr['lof_no'] = $data[0]['lof_no'];
                $sub_ins_lof_arr['production_date'] = $data[0]['production_date'];
            }
            $ins_lof_arr[] = $sub_ins_lof_arr;
            $lof_package_ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof',$ins_lof_arr,'num = VALUES(num)');
            if ($lof_package_ret['status'] != 1) {
                return $this->format_ret(-1, '', '保存库位表信息出错');
            }
            return $this->format_ret(1);
    }
    
    //验证入库数量
    function get_by_sku_id($return_package_code,$sku,$select = '*') {
        $sql = "SELECT {$select} FROM oms_return_package_detail WHERE return_package_code = '{$return_package_code}' AND sku = '{$sku}'";
        return $this->db->get_row($sql);
    }

    /**
     * 获取sku的成本价
     * @param $detail_arr
     * @param $record
     * @return mixed
     */
    public function get_sku_cost($sku_arr,$sell_record_code){
        $detail_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$detail_values);
        if(!is_array($sell_record_code)){
            $sell_record_code = explode(',',$sell_record_code);
        }
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_code,'sell_record_code',$detail_values);
        $detail_sql = 'select sku,cost_price,sell_record_code from oms_sell_record_detail where sell_record_code in('.$sell_record_str.') and sku in('.$sku_str.') group by sku ';
        $sku_cost_arr = $this->db->get_all($detail_sql,$detail_values);
        return load_model('util/ViewUtilModel')->get_map_arr($sku_cost_arr,'sku,sell_record_code');
    }
    /**
     * 记录日志
     * @param $data
     */
    public function log($data) {

        $filepath = ROOT_PATH . 'logs/day_record_api_' . date('Ymd') . '.log';
        file_put_contents($filepath, "time:" . date('Y-m-d H:i:s'), FILE_APPEND);
        file_put_contents($filepath, var_export($data, TRUE) . "\n", FILE_APPEND);
    }
}
