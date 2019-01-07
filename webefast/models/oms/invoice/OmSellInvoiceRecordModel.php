<?php

require_model('tb/TbModel');

class OmSellInvoiceRecordModel extends TbModel {

    function __construct() {
        parent::__construct('oms_sell_invoice_record');
    }

    /**
     * 
     * @param type $sell_record_code
     * @param type 开发类型，正票0，红票
     */
    function create_invoice($sell_record_code, $remark = '', $type = 0, $chyy = '') {
        $invoice_record = load_model('oms/invoice/OmsSellInvoiceModel')->get_sell_invoice($sell_record_code);
        $record_info = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $ret_check = load_model('oms/invoice/JsFapiaoModel')->check_invoice_amount($record_info['shop_code'], $invoice_record['invoice_type'], $invoice_record['invoice_amount']);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }
        if ($invoice_record['is_invoice'] == 1 || $invoice_record['is_red'] == 1) {
            return $this->format_ret(-1, '', '已经在正在开票，不能重复开票');
        }
        if ($type == 1 && empty($chyy)) {
            return $this->format_ret(-1, '', '冲红原因不能为空');
        }

        if ($type == 0 && $invoice_record['is_invoice'] != 0) {
            return $this->format_ret(-1, '', '正票已开，不能重复开票');
        }

        if ($type == 1 && $invoice_record['is_red'] != 0) {
            return $this->format_ret(-1, '', '正票已开，不能重复开票');
        }
        $ret_i = load_model('oms/invoice/JsFapiaoModel')->get_invoice_param_by_shop_code($record_info['shop_code']);
        if (empty($ret_i['data'])) {
            return $this->format_ret(-1, '', '找不到对应店铺发票配置！');
        }
        $invoice_param = $ret_i['data'];
        $config_type_arr = array(1, 2);
        if (!in_array($invoice_param['config_type'], $config_type_arr)) {
            return $this->format_ret(-1, '', '对应店铺发票配置类型异常！');
        }

        $invoce_key = array('sell_record_code', 'deal_code_list', 'invoice_title', 'invoice_amount', 'invoice_type');
        $invoice_record_info = array();
        foreach ($invoce_key as $k) {
            $invoice_record_info[$k] = $invoice_record[$k];
        }
        $invoice_record_info['invoice_remark'] = isset($remark) ? $remark : '';
        $invoice_record_info['is_red'] = $type;
        $invoice_record_info['invoice_time'] = date('Y-m-d H:i:s');
        $invoice_record_info['shop_code'] = $record_info['shop_code'];
        $invoice_record_info['record_time'] = $record_info['delivery_time'];
        $invoice_record_info['invoice_person'] = CTX()->get_session('user_name', true);
        $invoice_record_info['chyy'] = $chyy;
        $invoice_record_info['status'] = 0;

        $this->begin_trans();

        $ret = $this->save_invoice_record($invoice_record_info);



        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        $up_invoice_record = array('status' => 1);
        $up_invoice_record['invoice_time'] = date('Y-m-d H:i:s');
        $up_invoice_record['is_success'] = 1;
        if ($type == 0) {
            $up_invoice_record['is_invoice'] = 1;
        } else {
            $up_invoice_record['is_red'] = 1;
        }
        $this->update_exp('oms_sell_invoice', $up_invoice_record, array('sell_record_code' => $sell_record_code));
        if ($invoice_param['config_type'] == 2) {
            $ret_api = load_model('oms/invoice/JsFapiaoModel')->create_invoice($ret['data']);
        } else if ($invoice_param['config_type'] == 1) {
            $ret_api = load_model('oms/invoice/AlibabaEinvoiceModel')->create_invoice($ret['data']);
        }

        if ($ret_api['status'] < 1) {//发起失败
            if ($type == 0) {
                $up_invoice_record['is_invoice'] = 0;
            } else {
                $up_invoice_record['is_red'] = 0;
            }
            $up_invoice_record['status'] = 1;
            $up_invoice_record['is_success'] = 2;
            $this->update_exp('oms_sell_invoice', $up_invoice_record, array('sell_record_code' => $sell_record_code));
            $this->update_exp('oms_sell_invoice_record', array('status' => -1), array('id' => $ret['data']));
        }
        return $ret_api;

        //调用接口
    }

    function save_invoice_record($invoice_record_info) {
        $sql = "select id from oms_sell_invoice_record where sell_record_code=:sell_record_code AND status=-1 AND is_red=:is_red ";
        $id = $this->db->get_value($sql, array(
            ':sell_record_code' => $invoice_record_info['sell_record_code'],
            ':is_red' => $invoice_record_info['is_red'],
        ));
        if (empty($id)) {
            $ret = $this->insert($invoice_record_info);
        } else {
            $this->update($invoice_record_info, " id='{$id}' ");
            $ret = $this->format_ret(1, $id);
        }

        return $ret;
    }

    function get_invoice_record($id) {
        $ret = $this->get_row(array('id' => $id));
        return $ret['data'];
    }

    function get_invoice_record_zheng($sell_record_code) {


        $sql = "SELECT * FROM oms_sell_invoice_record WHERE sell_record_code=:sell_record_code  AND status='1' AND is_red='0' ORDER BY id DESC";
        return $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
    }

    function get_invoice_result($id, $is_force = 0) {
        $ret_invoice = $this->get_invoice_record($id);
        if ($ret_invoice['status'] == 1 && $is_force == 0) {
            return $this->format_ret(1);
        }
         //$ret = load_model('oms/invoice/JsFapiaoModel')->get_api_invocie($ret_invoice);
        $ret_i = load_model('oms/invoice/JsFapiaoModel')->get_invoice_param_by_shop_code($ret_invoice['shop_code']);
        if (empty($ret_i['data'])) {
            return $this->format_ret(-1, '', '找不到对应店铺发票配置！');
        }
        $invoice_param = $ret_i['data'];
        if ($invoice_param['config_type'] == 2) {
            $ret = load_model('oms/invoice/JsFapiaoModel')->get_api_invocie($ret_invoice);
        } else {
            $ret = load_model('oms/invoice/AlibabaEinvoiceModel')->get_api_invocie($ret_invoice);
            if($ret['stauts']==-10){
                $r_data = array('status'=>-1);
                $this->update_exp('oms_sell_invoice_record', $r_data, array('id'=>$id));
                $i_data = array('is_invoice'=>0);
                if ($ret_invoice['is_red'] == 0) {
                    $i_data = array('is_red' => 0);
                }
              $this->update_exp('oms_sell_invoice', $i_data, array('sell_record_code'=>$ret_invoice['sell_record_code']));
            }
        }
  
        //获取订单中的发票号
        $sql = "SELECT invoice_number FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $sell_invoice_number = $this->db->getRow($sql, ['sell_record_code' => $ret_invoice['sell_record_code']]);
        if(empty($sell_invoice_number['invoice_number'])){
            $sell_invoice_number['invoice_number'] = '无';
        }
        
        if ($ret['status'] > 0) {
            $ret_data = &$ret['data'];
            $ret_data['status'] = 1;
            $this->update_exp('oms_sell_invoice_record', $ret_data, "id = {$id}");
            $sell_no = array(); //更新订单表中的发票号
            $sell_no['invoice_number'] = $ret_data['invoice_no'];
            if($ret_invoice['is_red'] == 1){ //红票
                $sell_no['invoice_number'] = '';
            }
            $this->update_exp('oms_sell_record', $sell_no, array('sell_record_code' => $ret_invoice['sell_record_code']));
            $up_data = array();
            if($ret_invoice['is_red'] == 1){ //红票
                $log = '获取发票结果时，订单发票号被红冲：由 '.$sell_invoice_number['invoice_number'].' 修改成无';
            }else{    //正票
                $log = '获取发票结果时，订单发票号：由 '.$sell_invoice_number['invoice_number'].' 修改为：' . $sell_no['invoice_number'];
            }
            $ret_invoice['is_red'] == 0 ? $up_data['is_invoice'] = 2 : $up_data['is_red'] = 2;
            $this->update_exp('oms_sell_invoice', $up_data, array('sell_record_code' => $ret_invoice['sell_record_code']));
            //添加日志
            load_model('oms/SellRecordModel')->add_action($ret_invoice['sell_record_code'], "获取开票结果修改发票号", $log);
        }
        return $ret;
    }

    /*
     * 合并开票用，暂时取消
     */

    function check_invoice_record($sell_record_arr) {
        $sql_values = array();
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
        $sql = "select * from oms_sell_invoice where  sell_record_code ({$sell_record_str}) ";
        $data = $this->db->get_all($sql, $sql_values);
        $invoice_amount = 0;

        $invoice_one = array();
        $key_arr = array('shop_code' => '店铺', 'invoice_content' => '内容', 'invoice_title' => '抬头', 'invoice_type' => '类型');
        $error = array();
        $deal_code_arr = array();
        foreach ($data as $val) {
            $invoice_amount += $val['invoice_amount'];
            $deal_code_arr[] = $val['deal_code_list'];
            if (empty($invoice_one)) {
                $invoice_one = $val;
                continue;
            } foreach ($key_arr as $k => $c) {
                if ($val[$k] != $invoice_one[$k]) {
                    $error[] = $c;
                }
            }
        }
        if (!empty($error)) {
            return $this->format_ret(-1, '', '发票：' . implode(',', $error) . "不一致");
        }
        $invoice_one['invoice_amount'] = $invoice_amount;

        $invoice_one['deal_code_list'] = implode(',', $deal_code_arr);
        $invoice_one['sell_record_code'] = implode(',', $sell_record_arr);

        //$invoice_amount 缺少金额比较

        return $this->format_ret(1, $invoice_one);
    }

    function create_invoice_record($sell_record_code, $type = 0) {
        $invoice_info = load_model('oms/invoice/OmsSellInvoiceModel')->get_sell_invoice($sell_record_code);

//          `sell_record_code` varchar(128) DEFAULT NULL,
//  `deal_code_list` varchar(500) DEFAULT NULL,
//  `invoice_no` varchar(128) DEFAULT NULL,
//  `invoice_class` tinyint(3) DEFAULT '0' COMMENT '发票类型0,电子，1纸',
//  `invoice_type` tinyint(3) DEFAULT '1' COMMENT '1电子，2纸质',
//  `invoice_title` varchar(128) DEFAULT NULL,
//  `invoice_amount` decimal(20,2) DEFAULT NULL,
//  `invoice_time` datetime DEFAULT NULL,
//  `invoice_person` varchar(64) DEFAULT NULL,
//  `shop_code` varchar(128) DEFAULT NULL,
//  `record_time` datetime DEFAULT NULL,
//  `status` tinyint(4) DEFAULT '0' COMMENT '0发起开票，1开票成功，2开票失败',
//  `error_message` varchar(128) DEFAULT '' COMMENT '错误日志',
    }

    //根据条件查询

    function get_by_page($filter) {

        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter ['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_main = "FROM {$this->table}  WHERE 1 AND status>=0 ";
        //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $filter['sell_record_code'] = trim($filter['sell_record_code'], ',');
            $filter['sell_record_code'] = trim($filter['sell_record_code'], '，');
            if (strpos($filter['sell_record_code'], ',') || strpos($filter['sell_record_code'], '，')) {
                $filter['sell_record_code'] = str_replace('，', ',', $filter['sell_record_code']);
                $arr = explode(',', $filter['sell_record_code']);
                foreach ($arr as $key => $val) {
                    if (!empty($val)) {
                        $notnullarr[] = $val;
                    }
                }
                $filter['sell_record_code'] = implode(',', $notnullarr);
                $sql_main .= " AND sell_record_code in ({$filter['sell_record_code']})";
                //$sql_values[':sell_record_code'] = '('. $filter['sell_record_code'] .')';
            } else {
                $sql_main .= " AND sell_record_code LIKE :sell_record_code";
                $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            }
        }

        //发票号
        if (isset($filter['invoice_no']) && $filter['invoice_no'] != '') {
            $sql_main .= " AND invoice_no = :invoice_no";
            $sql_values[':invoice_no'] = $filter['invoice_no'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND shop_code in ( " . $str . " ) ";
        }


        //开票时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $sql_main .= " AND invoice_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND invoice_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            };
        }
        //发票类型
        if (isset($filter['invoice_type']) && $filter['invoice_type'] != '') {
            $sql_main .= " AND invoice_type = :invoice_type";
            $sql_values[':invoice_type'] = $filter['invoice_type'];
        }
        //发票性质
        if (isset($filter['is_red']) && $filter['is_red'] != '') {
            $sql_main .= " AND is_red = :is_red";
            $sql_values[':is_red'] = $filter['is_red'];
        }
        //发票状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND status = :status";
            $sql_values[':status'] = $filter['status'];
        }
        $sql_main .= " ORDER BY invoice_time DESC ";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $shop_list = load_model('oms/invoice/OmsSellInvoiceModel')->get_shop_list();
        foreach ($data['data'] as $key => &$value) {
            $value['shop_name'] = $shop_list[$value['shop_code']];
            $value['invoice_type'] = $value['invoice_type'] == 1 ? '电子' : '普通纸质';
            $value['is_red'] = $value['is_red'] == 1 ? '红票' : '正票';
            if ($value['status'] == 1) {
                $value['status'] = '已开票';
            } elseif ($value['status'] == 2) {
                $value['status'] = '开票失败';
            } elseif ($value['status'] == 0){
                $value['status'] = '开票中';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //获取对应店铺代码的店铺名称
    function get_shop_name($shop_code) {
        $sql = "select shop_name from base_shop where shop_code = :shop_code";
        return $this->db->get_row($sql, array(':shop_code' => $shop_code));
    }

    /**
     * 获取已开票金额
     */
//    function get_invoiced_money($sell_record_code) {
//        $sql = "SELECT invoice_amount FROM oms_sell_invoice_record WHERE sell_record_code = :sell_record_code";
//    }
}
