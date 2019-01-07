<?php
/**
* 店铺短信配置
*/
require_model('tb/TbModel');

class SmsShopConfigModel extends TbModel {
    public function __construct() {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'op_sms_config_shop';
    }

    function get_data_list($fld = 'id,tpl_name') {
        $sql = "select $fld from {$this->table} where is_active = 1";
        $arr = $this->db->get_all($sql);
        return $arr;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        trim_array($filter);
        $sql_values = array();
        $sql_join = " LEFT JOIN {$this->table} r2 ON r2.shop_code = rl.shop_code";
        if (isset($filter['shop_channel_code']) && $filter['shop_channel_code'] == 'B') {
            $sql_join .= ' inner join base_shop_api r3 on rl.shop_code = r3.shop_code';
        }
        $sql_main = "FROM base_shop rl {$sql_join} WHERE rl.shop_type=0  AND rl.is_active= 1";
        //是否分销商登录
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom['custom_code'])) {
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1=0";
            }
        }
        //店铺名称
        if (isset($filter['shop_name']) && $filter['shop_name'] != '') {
            $sql_main .= " AND rl.shop_name LIKE :shop_name";
            $sql_values[':shop_name'] = '%' . $filter['shop_name'] . '%';
        }   
        //是否启用
        if (isset($filter['is_active']) && $filter['is_active'] != '') {
            if (1 == $filter['is_active']){
                $sql_main .= " AND r2.is_active= 1";
            } else{
                $sql_main .= " AND (r2.is_active = 0 OR r2.is_active is null)";
            }
        }
        //销售渠道
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sale_channel_id_arr = explode(',', $filter['sale_channel_code']);
            if (!empty($sale_channel_id_arr)) {
                $sql_main .= " AND (";
                foreach ($sale_channel_id_arr as $key => $value) {
                    $sale_channel = 'sale_channel' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.sale_channel_code = :{$sale_channel} ";
                    } else {
                        $sql_main .= " or rl.sale_channel_code = :{$sale_channel} ";
                    }
                    $sql_values[':' . $sale_channel] = $value;
                }
                $sql_main .= ")";
            }
        }

        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        
        $select = 'rl.shop_name,rl.sale_channel_code,r2.*,rl.shop_code';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
                //列表数据加工
        $tbl_cfg = array(
            'op_sms_tpl' => array('fld' => 'tpl_name', 'relation_fld' => 'id+delivery_notice_tpl_id'),
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        $arr_channel = load_model('base/SaleChannelModel')->get_data_code_map();
        foreach ($data['data'] as &$val) {
            $val['sale_channel_name'] = isset($arr_channel[$val['sale_channel_code']]['1']) ? $arr_channel[$val['sale_channel_code']]['1'] : '';
            $val['delivery_notice_status_text'] = $val['delivery_notice_status'];
            $val['is_active_text'] = $val['is_active'];
            $val['send_time'] = empty($val['send_start_time']) ? '' : $val['send_start_time'].'-'.$val['send_end_time'];
        }
        return $this->format_ret(OP_SUCCESS, $data);
    }
    
    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }
    function get_by_shop_code($shop_code) {
        $arr = $this->get_row(array('shop_code' => $shop_code));
        return $arr;
    }
     /**
     * 修改纪录
     */
    public function update($param, $where='') {
        $ret_valid = $this->valid($param);
        if ($ret_valid['status'] != 1) {
            return $ret_valid;
        }
        $ret = parent::update($param, array('shop_code' => $param['shop_code']));
        return $ret;
    }

   public function is_exists($value, $field_name = 'tpl_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
 	/**
     * 添加新纪录
     */
    function insert($param) {
        $ret_valid = $this->valid($param);
        if ($ret_valid['status'] != 1) {
            return $ret_valid;
        }
        $param['create_time'] = date('Y-m-d H:i:s');
        return parent::insert($param);
    }

    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
    /**
     * 店铺短信设置启用状态更新
     * @param type $active
     * @param type $shop_code
     * @return type
     */
    function update_active($active, $shop_code) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        if (1 == $active){
            $ret = $this->is_exists($shop_code, 'shop_code');
            $data = isset($ret['data']) ? $ret['data'] : array();
            $ret = $this->check_active_allow($data);
            if (1 != $ret['status']) {
                return $ret;
            }
        }
        $ret = parent::update(array('is_active' => $active), array('shop_code' => $shop_code));
        return $ret;
    }
    /**
     * 是否允许启用
     * @param type $data
     */
    public function check_active_allow($data) {
        if (!isset($data['order_type']) || empty($data['order_type'])){
            return $this->format_ret(-1, '', '启用失败，请选择发送订单类型！');
        }
        return $this->format_ret(1);
    }
    /**
     *  验证表单字段
     * @param type $param
     */
    public function valid(&$param) {
        if (!isset($param['shop_code']) || empty($param['shop_code'])){
            return $this->format_ret(-1, '', '店铺参数缺失');
        }
        if (isset($param['enable_time']) && empty($param['enable_time'])){
             return $this->format_ret(-1, '', '请选择启用时间');
        }
        $preg_time = '/^([01][0-9]|2[0-3]):([0-5][0-9])$/';
        if (isset($param['send_start_time']) && $param['send_start_time'] != '' && !preg_match($preg_time, $param['send_start_time'])){
             return $this->format_ret(-1, '', '请输入正确的发送时间，如08:08');
        }
        if (isset($param['send_end_time']) && $param['send_end_time'] != '' && !preg_match($preg_time, $param['send_end_time'])){
             return $this->format_ret(-1, '', '请输入正确的发送时间，如08:08');
        }
        if ((isset($param['send_start_time']) && $param['send_start_time'] != '' && empty($param['send_end_time'])) || 
                (isset($param['send_end_time']) && $param['send_end_time'] != '' && empty($param['send_start_time']))){
             return $this->format_ret(-1, '', '发送时间错误, 起始时间和结束时间必须同时设置');
        }
        if (isset($param['send_start_time']) && $param['send_start_time'] != '' && isset($param['send_end_time']) && $param['send_end_time'] != '' && $param['send_end_time'] <= $param['send_start_time']){
             return $this->format_ret(-1, '', '发送时间错误, 结束时间必须大于起始时间');
        }
        if (isset($param['order_type']) && is_array($param['order_type'])){
            $param['order_type'] = implode(',', $param['order_type']);
        }
        if(!isset($param['order_type'])){
            $param['order_type'] = '';
        }
        if (isset($param['delivery_notice_status']) && 1 == $param['delivery_notice_status'] && empty($param['delivery_notice_tpl_id'])){
            return $this->format_ret(-1, '', '请选择发货短信模板！');
        }
        return $this->format_ret(1);
    }
}
    