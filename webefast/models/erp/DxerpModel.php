<?php
require_model('tb/TbModel');
require_model('mid/dxerp/DxerpSellRecordModel');
require_model('mid/dxerp/DxerpSellReturnModel');

class DxerpModel extends TbModel {

    public $table = "api_dxerp_trade";
    public $erp_config = array();
    public $upload_status = array(
        '0' => '未上传',
        '1' => '上传成功',
        '2' => '上传失败',
    );
    public $SellRecordModel = NULL;
    public $SellReturnModel = NULL;

    function __construct() {
        parent::__construct();
        $this->get_erp_config();

    }

    function set_record_obj(){
        $this->SellRecordModel = new DxerpSellRecordModel($this->erp_config);
    }

    function set_return_obj(){
        $this->SellReturnModel = new DxerpSellReturnModel($this->erp_config);
    }


    /**
     * 获取配置参数
     */
    function get_erp_config() {
        $sql = "SELECT * FROM dxerp_config WHERE erp_config_code=:erp_config_code";
        $sql_value[':erp_config_code'] = 'dxerp_record_upload';
        $this->erp_config = $this->db->get_row($sql, $sql_value);
    }


    /**
     * 列表查询
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        $where = '';
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $where .= " AND shop_code in ({$str}) ";
        }
        //单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] <> '') {
            $arr = explode(',', $filter['sell_record_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
            $where .= " AND sell_record_code in ({$str})";
        }
        //上线时间
        if (isset($this->erp_config['online_time']) && $this->erp_config['online_time'] != '0000-00-00') {
            $where .= " AND delivery_time>=:delivery_time ";
            $sql_values[':delivery_time'] = $this->erp_config['online_time'] . ' 00:00:00';
        } else {
            $where .= " AND 1=2 ";
        }

        $record_sql = "select om.sell_record_code,'销售' as order_name,1 as order_type,om.delivery_time,om.payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status ,bs.upload_time,bs.upload_msg from oms_sell_record om left join api_dxerp_trade bs on om.sell_record_code=bs.sell_record_code and bs.order_type=1 where om.shipping_status=4 ";
        $return_sql = "select om.sell_return_code as sell_record_code,'退货' as order_name,2 as order_type,om.receive_time as delivery_time,om.refund_total_fee as payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status,bs.upload_time,bs.upload_msg from oms_sell_return om left join api_dxerp_trade bs on om.sell_return_code=bs.sell_record_code and  bs.order_type=2 where om.return_shipping_status=1 and om.return_type!=1 ";
        $sql_main = "FROM ($record_sql union all $return_sql) as tmp WHERE 1 $where";

        $select = '*';
        $sql_main .= " order by delivery_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$row) {
            $row['upload_status_name'] = $this->upload_status[$row['upload_status']];
        }
        filter_fk_name($data['data'], array('shop_code|shop', 'store_code|store',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }


    /**
     * 单据上传
     * @param $record_code 单号
     * @param $order_type  单据类型 1:订单,2:退单
     * @return array
     */
    function record_upload($record_code, $order_type) {
        if (!in_array($order_type, array('1', '2'))) {
            return $this->format_ret('-1', '', '该单据不是订单或退单！');
        }
        if (empty($record_code)) {
            return $this->format_ret(-1, '', '单据编号为空！');
        }
        $dxerp_trade = $this->get_row(array('sell_record_code' => $record_code, 'order_type' => $order_type));
        if ($dxerp_trade['status'] == 1) {
            $dxerp_trade_info = $dxerp_trade['data'];
            if ($dxerp_trade_info['upload_status'] == 1) {
                return $this->format_ret(-1, '', '该单据已经上传成功！');
            }
        }
        if ($order_type == 1) {
            if (empty($this->SellRecordModel)) {
                $this->set_record_obj();
            }
            $client = $this->SellRecordModel->create_api($this->erp_config);
            if ($client['status'] != 1) {
                return $client;
            }
            $ret = $this->SellRecordModel->upload($record_code);
        } else {
            if (empty($this->SellReturnModel)) {
                $this->set_return_obj();
            }
            $client = $this->SellReturnModel->create_api($this->erp_config);
            if ($client['status'] != 1) {
                return $client;
            }
            $ret = $this->SellReturnModel->upload($record_code);
        }
        return $ret;
    }

    /**
     * 批量单据上传
     * @param $params
     * @return array
     */
    function record_upload_multi($params) {
        $params_arr = explode(',', $params);
        $error = array();
        foreach ($params_arr as $record) {
            $record_params = explode('_', $record);
            $record_code = $record_params[0];
            $order_type = $record_params[1];
            $order_name = $order_type == 1 ? '订单' : '退单';
            $ret = $this->record_upload($record_code, $order_type);
            if ($ret['status'] != 1) {
                $error[] = array($record_code, $order_name, $ret['message']);
            }
        }

        if (!empty($error)) {
            $sum = count($params_arr);
            $error_num = count($error);
            $success = $sum - $error_num;
            $fail_top = array('订/退单编号', '类型', '错误信息');
            $msg = load_model('oms/DeliverRecordModel')->create_fail_file($fail_top, $error);
            return $this->format_ret(-1, '', '上传成功:' . $success . ', 失败:' . $error_num . $msg);
        }
        return $this->format_ret(1, '', '上传成功！');
    }

    /**
     * 定时器单据上传
     * @return array
     */
    function do_upload_cli() {
        //校验增值服务
        $check = load_model('common/ServiceModel')->check_is_auth_by_value('DAOXUN_ERP_UPLOAD');
        if ($check != true) {
            return $this->format_ret(-1, '', '没有开通增值服务！');
        }
        $record_sql = "select om.sell_record_code,'销售' as order_name,1 as order_type,om.delivery_time,om.payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status ,bs.upload_time,bs.upload_msg from oms_sell_record om left join api_dxerp_trade bs on om.sell_record_code=bs.sell_record_code and bs.order_type=1 where om.shipping_status=4 ";
        $return_sql = "select om.sell_return_code as sell_record_code,'退货' as order_name,2 as order_type,om.receive_time as delivery_time,om.refund_total_fee as payable_money,om.store_code,om.shop_code,IFNULL(bs.upload_status,0) as upload_status,bs.upload_time,bs.upload_msg from oms_sell_return om left join api_dxerp_trade bs on om.sell_return_code=bs.sell_record_code and  bs.order_type=2 where om.return_shipping_status=1 and om.return_type!=1 ";
        $sql_main = "SELECT sell_record_code,order_type FROM ($record_sql union all $return_sql) as tmp WHERE 1 AND tmp.upload_status<>1";
        //上线时间
        if (isset($this->erp_config['online_time']) && $this->erp_config['online_time'] != '0000-00-00') {
            $sql_main .= " AND delivery_time>=:delivery_time ";
            $sql_values[':delivery_time'] = $this->erp_config['online_time'] . ' 00:00:00';
        } else {
            $sql_main .= " AND 1=2 ";
        }
        $record_info = $this->db->get_all($sql_main, $sql_values);
        if (empty($record_info)) {
            return $this->format_ret(-1, '', '无上传单据！');
        }
        foreach ($record_info as $value) {
            $this->record_upload($value['sell_record_code'], $value['order_type']);
        }
        return $this->format_ret(1);
    }

}