<?php

/**
 * 商品条码管理相关业务
 *
 * @author dfr
 *
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('prm');
require_lang('api');
class GoodsUniqueCodeLogModel extends TbModel {

    function get_table() {
        return 'goods_unique_code_log';
    }

    function get_log_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->table} r1 
                    LEFT JOIN  base_goods r2  on r1.goods_code = r2.goods_code 
                    WHERE 1 ";
//	$sql_main = " FROM (SELECT r1.*,r2.goods_name,r3.deal_code_list,r3.buyer_name,r3.receiver_name,r3.receiver_mobile,r3.receiver_address 
//                FROM goods_unique_code_log AS r1 
//                LEFT JOIN  base_goods r2  on r1.goods_code = r2.goods_code 
//                LEFT JOIN  oms_sell_record r3 on r1.record_code=r3.sell_record_code 
//                WHERE r1.record_type = 'sell_record'
//                UNION ALL
//                SELECT r1.*,r2.goods_name,r3.deal_code AS deal_code_list,r3.buyer_name,r3.return_name AS receiver_name,r3.return_mobile AS receiver_mobile,r3.return_address AS receiver_address 
//                FROM goods_unique_code_log AS r1 
//                LEFT JOIN  base_goods r2  on r1.goods_code = r2.goods_code 
//                LEFT JOIN  oms_sell_return r3 on r1.record_code=r3.sell_return_code 
//                WHERE r1.record_type = 'sell_return') AS r1 WHERE 1 ";			
        //商品编号
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r1.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r1.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (r2.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r1.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //唯一码
        if (isset($filter['unique_code']) && $filter['unique_code'] != '') {
            $sql_main .= " AND  (r1.unique_code LIKE :unique_code) ";
            $sql_values[':unique_code'] = $filter['unique_code'] . '%';
        }
        //会员昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] != '') {
            $sql_main .= $this->get_sql_wh($filter['buyer_name'], 'buyer_name');
        }
        //手机号
        if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] != '') {
            $record_code = $this->get_record_return_code($filter['receiver_mobile'], 'receiver_mobile', 'oms_sell_record', 'sell_record_code');
            $return_code = $this->get_record_return_code($filter['receiver_mobile'], 'return_mobile', 'oms_sell_return', 'sell_return_code');
            $record_arr = array_merge($record_code,$return_code);
            if(!empty($record_arr)) {
                $sell_record_str = $this->arr_to_in_sql_value($record_arr, 'record_code', $sql_values);
                $sql_main .= "AND record_code in ({$sell_record_str})";
    //            $sql_values[':record_code'] = $record_code_str;
            } else {
                $sql_main .= "AND 1 != 1";
            }
        }
        //收货人
        if (isset($filter['receiver_name']) && $filter['receiver_name'] != '') {
            $record_code = $this->get_record_return_code($filter['receiver_name'], 'receiver_name', 'oms_sell_record', 'sell_record_code');
            $return_code = $this->get_record_return_code($filter['receiver_name'], 'return_name', 'oms_sell_return', 'sell_return_code');
            $record_arr = array_merge($record_code,$return_code);
            if(!empty($record_arr)) {
                $sell_record_str = $this->arr_to_in_sql_value($record_arr, 'record_code', $sql_values);
                $sql_main .= "AND record_code in ({$sell_record_str})";
    //            $sql_values[':record_code'] = $record_code_str;
            } else {
                $sql_main .= "AND 1 != 1";
            }
        }
        //交易号
        if (isset($filter['deal_code_list']) && $filter['deal_code_list'] != '') {
            $record_code = $this->get_record_return_code($filter['deal_code_list'], 'deal_code_list', 'oms_sell_record', 'sell_record_code');
            $return_code = $this->get_record_return_code($filter['deal_code_list'], 'deal_code', 'oms_sell_return', 'sell_return_code');
//            $record_code_str = "'" . implode("','", $record_code) . "','" . implode("','", $return_code) . "'";
            $record_arr = array_merge($record_code,$return_code);
            if(!empty($record_arr)) {
                $sell_record_str = $this->arr_to_in_sql_value($record_arr, 'record_code', $sql_values);
                $sql_main .= "AND record_code in ({$sell_record_str})";
    //            $sql_values[':record_code'] = $record_code_str;
            } else {
                $sql_main .= "AND 1 != 1";
            }
        }
        //操作时间
        if (isset($filter['action_time_start']) && $filter['action_time_start'] !== '') {
            $sql_main .= " AND r1.action_time >= :action_time_start ";
            $sql_values[':action_time_start'] = $filter['action_time_start'] . ' 00:00:00';
        }
        if (isset($filter['action_time_end']) && $filter['action_time_end'] !== '') {
            $sql_main .= " AND r1.action_time <= :action_time_end ";
            $sql_values[':action_time_end'] = $filter['action_time_end'] . ' 23:59:59';
        }
        $sql_main .= " ORDER BY r1.action_time DESC ";
        $select = 'r1.*,r2.goods_name';
//        var_dump($select,$sql_main,$sql_values);
//        ,r2.goods_name,r3.deal_code_list,r3.buyer_name,r3.receiver_name,r3.receiver_mobile,r3.receiver_address
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop'));
        $sell_record_code_arr = array();
        $sell_return_code_arr = array();
        foreach ($data['data'] as $key => &$value) {
            $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
            $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));

            if ($value['record_type'] == 'sell_record') {
                $sell_record_code_arr[$value['record_code']][] = $key;
                $value['record_type_name'] = '销售订单';
            } elseif ($value['record_type'] == 'sell_return') {
                $sell_return_code_arr[$value['record_code']][] = $key;
                $value['record_type_name'] = '销售退单';
            } else {
                $value['record_type_name'] = '';
            }
            if ($value['action_name'] == 'sell_out') {
                $value['action_name_name'] = '销售出库';
            } elseif ($value['action_name'] == 'return_storage') {
                $value['action_name_name'] = '退货入库';
            } else {
                $value['action_name_name'] = '';
            }
        }
        if (!empty($sell_record_code_arr)) {
            $this->get_return_record_code_info($data, $sell_record_code_arr,'record');
        }
        if(!empty($sell_return_code_arr)) {
            $this->get_return_record_code_info($data,$sell_return_code_arr,'return');
        }
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as $key => &$value) {
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $value['receiver_mobile'] = $this->phone_hidden($value['receiver_mobile']);
                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_sql_wh($data, $field) {
       
        
        $record_code = $this->get_record_return_code($data, $field, 'oms_sell_record', 'sell_record_code');
        $return_code = $this->get_record_return_code($data, $field, 'oms_sell_return', 'sell_return_code');
        $record_code_str = "'" . implode("','", $record_code) . "','" . implode("','", $return_code) . "'";
        $sql_main = "AND record_code in ({$record_code_str})";
        return $sql_main;
    }

    function get_record_return_code($where, $value, $table, $data) {

        $field_arr = array('buyer_name', 'receiver_name', 'return_name', 'receiver_mobile', 'return_mobile');
        if (!in_array($value, $field_arr)) {
            $sql = "SELECT {$data} FROM {$table} WHERE {$value} LIKE :where;";
            $code_arr = $this->db->get_col($sql, array(':where' => $where . '%'), FALSE);
        } else {
            $sql_values = array();
            $sql = "SELECT {$data} FROM {$table} WHERE 1 ";
            if ($value != 'buyer_name') {
                $type = ($value == 'receiver_mobile' || $value == 'return_mobile') ? 'tel' : 'name';

                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($where, $type);
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql .= " AND ( {$value} = :{$value} OR customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_values[':' . $value] = $where;
                } else {
                    $sql .= " AND {$value} = :$value ";
                    $sql_values[':' . $value] = $where;
                }
            } else {
                $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($where);
                if (!empty($customer_code_arr)) {

                        $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                        $sql .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                } else {
                    $sql .= " AND buyer_name LIKE :buyer_name ";
                    $sql_values[':buyer_name'] = '%' . $where . '%';
                }
            }
            $code_arr = $this->db->get_col($sql, $sql_values, FALSE);
        }


        return $code_arr;
    }

    //获取订单或退单表的信息 ，增加查询收发货仓库，配送方式，快递单号
    function get_return_record_code_info(&$data, $code_arr,$type) {
        if($type == 'record') {
            $sell_record_key = array_keys($code_arr);
            $sell_record_str = $this->arr_to_in_sql_value($sell_record_key, 'sell_record_code', $sql_value);
            $sql = "SELECT r1.sell_record_code AS record_code,r1.deal_code_list,r1.buyer_name,r1.receiver_name,r1.receiver_mobile,r1.receiver_address,r1.express_no,r2.store_name,r3.express_name 
                    FROM oms_sell_record r1 
                    LEFT JOIN base_store r2 ON r1.store_code = r2.store_code
                    LEFT JOIN base_express r3 ON r1.express_code = r3.express_code
                    WHERE r1.sell_record_code in ({$sell_record_str});";        
            $record = $this->db->get_all($sql,$sql_value);
        } else {
            $sell_return_key = array_keys($code_arr);
            $sell_return_str = $this->arr_to_in_sql_value($sell_return_key, 'sell_rerurn_code', $sql_val);
            $sql = "SELECT r1.sell_return_code AS record_code,r1.deal_code AS deal_code_list,r1.buyer_name,r1.return_name AS receiver_name,r1.return_mobile AS receiver_mobile,r1.return_address AS receiver_address,r1.return_express_no AS express_no,r2.store_name,r3.express_name 
                    FROM oms_sell_return r1
                    LEFT JOIN base_store r2 ON r1.store_code = r2.store_code
                    LEFT JOIN base_express r3 ON r1.return_express_code = r3.express_code
                    WHERE r1.sell_return_code in ({$sell_return_str});"; 
            $record = $this->db->get_all($sql,$sql_val);
        }
        
        foreach ($record as $val) {
            $key = $code_arr[$val['record_code']];
            if(is_array($key)) {
                for($i=0; $i<count($key); $i++){
                    $data['data'][$key[$i]]['deal_code_list'] = $val['deal_code_list'];
                    $data['data'][$key[$i]]['buyer_name'] = $val['buyer_name'];
                    $data['data'][$key[$i]]['receiver_name'] = $val['receiver_name'];
                    $data['data'][$key[$i]]['receiver_mobile'] = $val['receiver_mobile'];
                    $data['data'][$key[$i]]['receiver_address'] = $val['receiver_address'];
                    $data['data'][$key[$i]]['express_no'] = $val['express_no'];
                    $data['data'][$key[$i]]['store_name'] = $val['store_name'];
                    $data['data'][$key[$i]]['express_name'] = $val['express_name'];
                }
            } else {
                $data['data'][$key]['deal_code_list'] = $val['deal_code_list'];
                $data['data'][$key]['buyer_name'] = $val['buyer_name'];
                $data['data'][$key]['receiver_name'] = $val['receiver_name'];
                $data['data'][$key]['receiver_mobile'] = $val['receiver_mobile'];
                $data['data'][$key]['receiver_address'] = $val['receiver_address'];
                $data['data'][$key]['express_no'] = $val['express'];
                $data['data'][$key]['store_name'] = $val['store_name'];
                $data['data'][$key]['express_name'] = $val['express_name'];
            }
        }
    }

    /**
     * 判断是否存在
     * @param $value
     * @param string $field_name
     * @return array
     */
    function is_exists($value, $field_name = 'barcode_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    function unique_code_log($data) {
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
        $unique_status = $unique_arr['unique_status'];
        if ($unique_status != 1) {
            return array('status' => '1', 'data' => '', 'message' => '');
        }
        $sku_array = load_model('oms/UniqueCodeScanTemporaryLogModel')->get_unique_code_by_record_code($data['record_code']);
        if (empty($sku_array)) {
            return array('status' => '-1', 'data' => '', 'message' => '临时表中唯一码为空');
        }
        $sku_str = implode("','", $sku_array);
        $sql = "select r1.sku,r2.barcode,r1.unique_code,r2.goods_code,r2.spec1_code,r2.spec2_code from goods_unique_code r1
    		INNER JOIN goods_sku r2 ON r1.sku = r2.sku where r1.unique_code in ('{$sku_str}')";

        $unique_code = $this->db->get_all($sql);
        //判断是订单还是退单
        if (isset($data['record_type']) && $data['record_type'] == 'sell_record') {
            //获取该订单的店铺编号        
            $shop_code = oms_tb_val('oms_sell_record', 'shop_code', array('sell_record_code' => $data['record_code']));
        } else {
            //获取退单的店铺编号和退货仓库代码
            $sql1 = "SELECT shop_code,store_code FROM `oms_sell_return` WHERE sell_return_code = :sell_return_code";
            $list = $this->db->getRow($sql1, array('sell_return_code' => $data['record_code']));
            //$shop_code = oms_tb_val('oms_sell_return', 'shop_code', array('sell_return_code' => $data['record_code']));
            $shop_code = $list['shop_code'];
        }
        $params = array();
        if (!empty($unique_code)) {
            foreach ($unique_code as $key => $unique) {
                $p = array();
                $p['record_type'] = $data['record_type'];
                $p['record_code'] = $data['record_code'];
                $p['action_name'] = $data['action_name'];
                $p['barcode'] = $unique['barcode'];
                $p['unique_code'] = $unique['unique_code'];
                $p['sku'] = $unique['sku'];
                $p['goods_code'] = $unique['goods_code'];
                $p['spec1_code'] = $unique['spec1_code'];
                $p['spec2_code'] = $unique['spec2_code'];
                $p['action_time'] = date("Y-m-d H:i:s");
                $p['shop_code'] = $shop_code;
                array_push($params, $p);
            }
            $this->begin_trans();
            try {
                if ($data['record_type'] == 'sell_record') {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $sql = "update goods_unique_code set status = '{$status}' where unique_code in ('{$sku_str}')";
                $ret = $this->db->query($sql);
                
                $sql2= "update goods_unique_code_tl set status = '{$status}' where unique_code in ('{$sku_str}')";
                if($data['record_type'] == 'sell_return'){
                    $sql2= "update goods_unique_code_tl set status = '{$status}',store_code = '{$list['store_code']}' where unique_code in ('{$sku_str}')";
                }
                $this->db->query($sql2);
                
                if ($ret == true) {
                    $ret = $this->insert($params);
                    if ($ret['status'] == 1) {
                        $ret = load_model('oms/UniqueCodeScanTemporaryLogModel')->delete(array('sell_record_code' => $data['record_code']));
                        if ($ret['status'] == 1) {
                            $this->commit();
                            return array('status' => '1', 'data' => '', 'message' => '唯一码扫描出库成功');
                        }
                    }
                }
                $this->rollback();
                return $ret;
            } catch (Exception $e) {
                $this->rollback();
                return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
            }
            return $this->format_ret(1, '', '插入唯一码日志成功');
        }
    }

    function insert($data) {
        $ret = $this->insert_multi($data, true);
        ;
        return $ret;
    }

    function get_goods_unique_code($unique_code, $record_type) {
        $sql = "select DISTINCT record_code from goods_unique_code_log  where unique_code=:unique_code AND record_type=:record_type ";

        $data = $this->db->get_all($sql, array(':unique_code' => $unique_code, ':record_type' => $record_type));
        $record_code_arr = array();
        foreach ($data as $val) {
            $record_code_arr[] = $val['record_code'];
        }
        return $record_code_arr;
    }
    
    /*
     * 唯一码跟踪查询接口
     */
    public function api_unique_log_get($param) {
        //验证可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'shop_code','record_code','unique_code','deal_code_list'),
            'i' => array('page', 'page_size')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);
        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');//单页数据超限
        }
        if(isset($arr_option['unique_code']) && !empty($arr_option['unique_code'])){
            $res = $this->check('unique_code','goods_unique_code',$arr_option['unique_code']);
            if(empty($res)){
                return $this->format_ret(-10003, (object) array(), '唯一码不存在');
            }
        }
        if(isset($arr_option['shop_code']) && !empty($arr_option['shop_code'])){
            $res = $this->check('shop_code','base_shop',$arr_option['shop_code']);
            if(empty($res)){
                return $this->format_ret(-10004, (object) array(), '店铺不存在');
            }
        }
        $sql_values = array();
        $sql_wh = 'AND 1 = 1';
        if(isset($arr_option['deal_code_list']) && !empty($arr_option['deal_code_list'])){
            $record_code = $this->get_sell_recode($arr_option['deal_code_list'], 'deal_code_list', 'oms_sell_record', 'sell_record_code');
            $return_code = $this->get_sell_recode($arr_option['deal_code_list'], 'deal_code', 'oms_sell_return', 'sell_return_code');
            $record_arr = array_merge($record_code,$return_code);
            if(!empty($record_arr)) {
                $sell_record_str = "'".implode("','", $record_arr)."'";
                //$sell_record_str = $this->arr_to_in_sql_value($record_arr, 'record_code', $sql_values);
                 $sql_wh= "AND gu.record_code in ({$sell_record_str})";
            } else {
                return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');//数据不存在
            }
        } 
        $select = 'gu.`record_code`,gu.`unique_code`,gu.`record_type`,gu.`shop_code`,gu.`goods_code`,gu.`barcode`,gu.`action_time`,bg.`goods_name`,bs.`shop_name`';
        $sql_join = 'INNER JOIN base_goods AS bg ON bg.goods_code = gu.goods_code INNER JOIN base_shop AS bs on bs.shop_code = gu.shop_code';
        $sql_main = " FROM {$this->table} AS gu {$sql_join} WHERE 1";
        $this->get_record_sql_where($arr_option, $sql_main, $sql_values, 'gu.',$sql_wh);
        $sql_main .= ' ORDER BY gu.action_time DESC';
        //获取主信息
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);
        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');//数据不存在
        }
       foreach($data as &$v){
           if($v['record_type'] == 'sell_record'){
               $v['deal_code_list'] = $this->get_deal_code_list('deal_code_list','oms_sell_record',$v['record_code']);
           }else{
               $v['deal_code_list'] = $this->get_deal_code_return('deal_code','oms_sell_return',$v['record_code']);
           }
       }
        
        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
        
    }
      /**
     * 生成单据查询sql条件语句
     * @param array $filter 参数条件
     * @param string $sql_main sql主体
     * @param string $sql_values sql映射值
     * @param string $ab 表别名
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values, $ab,$sql_wh) {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '') {
                continue;
            }
            if ($key == 'start_time') {
                $sql_main .= " AND {$ab}action_time>=:{$key}";
            } else if ($key == 'end_time') {
                $sql_main .= " AND {$ab}action_time<=:{$key}";
            } else if($key == 'deal_code_list'){
                $sql_main .= " $sql_wh";
            }else{
                 $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            if($key != 'deal_code_list'){
                $sql_values[":{$key}"] = $val;
            }
            
        }

        if (!isset($filter['start_time'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("-1 days"));
            $sql_main .= " AND {$ab}action_time >= :start_time";
            $sql_values[':start_time'] = $start_time;
        }
        if (!isset($filter['end_time'])) {
            $end_time = date("Y-m-d H:i:s", strtotime("now"));
            $sql_main .= " AND {$ab}action_time <= :end_time";
            $sql_values[':end_time'] = $end_time;
        }
    }
    /**
     * 
     * @param type $tb 表名
     * @param type $wh where 条件
     * @return type 交易号
     */
    private function get_deal_code_list($field,$tb,$wh) {
        $sql = "SELECT {$field} from {$tb} where sell_record_code = :sell_record_code";
        $deal =  $this->db->getRow($sql,array(':sell_record_code' => $wh));
        return $deal[$field];
    }
    
    private function get_deal_code_return($field,$tb,$wh) {
        $sql = "SELECT {$field} from {$tb} where sell_return_code = :sell_return_code";
        $deal =  $this->db->getRow($sql,array(':sell_return_code' => $wh));
        return $deal[$field];
    }
    
    private function get_sell_recode($where, $value, $table, $data){
        $sql = "SELECT {$data} FROM {$table} WHERE {$value} LIKE :where";
        $code_arr= $this->db->getRow($sql, array(':where' => $where));
        return $code_arr;
    }
    
     private function check($field,$tb,$val){
         $sql = "SELECT {$field} from {$tb} where {$field} = :{$field}";
         $res = $this->db->getAll($sql, array(":{$field}" => $val));
         return $res;
     }
    
}
