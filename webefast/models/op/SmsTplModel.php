<?php
/**
* 短信模板 相关业务
*/
require_model('tb/TbModel');

class SmsTplModel extends TbModel {
    public function __construct() {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'op_sms_tpl';
    }
    //短信模板类型
    public $sms_tpl_type = array(
         'delivery_notice' => '发货通知模板',
//        'confirm_order' => '确认订单短信通知模版',
//        'shipping_success' => '发货通知模板',
//        'delivery_success' => '网单回写成功短信通知模版',
//        'confirm_receiving' => '客户确认收货短信通知模版',
//        'batch_send_msg' => '会员群发短信模版',
//        'custom_msg' => '自定义短信模版',
    );
    //短信模板变量
    public $sms_tpl_var = array(
        '{@店铺名称}' => 'shop_name',
        '{@会员昵称}' => 'buyer_name',
        '{@平台交易号}' => 'deal_code_list',
        '{@快递公司名称}' => 'express_name',
        '{@快递单号}' => 'express_no',
        '{@收货人}' => 'receiver_name',
//        '{@当前时间}' => 'current_time',
    );
    /**
     * 店铺设置详情页模板下拉列表
     * @return array
     */
    function get_select_list() {
        $sql = "select id,tpl_type,tpl_name from {$this->table} where 1 = 1";
        $select_list = array();
        $ret = $this->db->get_all($sql);
        if (empty($ret)){
            return $select_list;
        }
        foreach ($ret as $val) {
            $index = $val['tpl_type'];
            if (!isset($select_list[$index])){
                $select_list[$index] = array(''=>'请选择');
            }
            $select_list[$index][$val['id']] = $val['tpl_name'];
        }
        return $select_list;
    }
    /**
     * 根据条件查询数据
     * @param type $filter
     * @return type
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        //模板类型
        if (isset($filter['tpl_type']) && $filter['tpl_type'] != '') {
            $sql_main .= " AND tpl_type = :tpl_type";
            $sql_values[':tpl_type'] = $filter['tpl_type'];
        }
        //模板名称
        if (isset($filter['tpl_name']) && $filter['tpl_name'] != '') {
            $sql_main .= " AND tpl_name LIKE :tpl_name";
            $sql_values[':tpl_name'] = '%' . $filter['tpl_name'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$val) {
            $val['tpl_type_name'] = isset($this->sms_tpl_type[$val['tpl_type']]) ? $this->sms_tpl_type[$val['tpl_type']] : '';
            $val['sms_info_sub'] = mb_substr_replace($val['sms_info'], '...', 60);//替换字符串的子串
        }

        return $this->format_ret(OP_SUCCESS, $data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('id' => $id));
        return $arr;
    }
    /**
    * 修改纪录
    */
    public function update($params, $where='') {
        trim_array($params);
        $data = get_array_vars($params, array('id', 'tpl_name', 'sms_sign','sms_info', 'remark'));
        if (empty($data['id'])){
            return $this->format_ret(-1, '', '参数缺失');
        }
        $id = $data['id'];
        $ret = $this->_valid($data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $ret = parent::update($data, array('id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'tpl_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
 	/**
     * 添加新纪录
     */
    function insert($params) {
        trim_array($params);
        $data = get_array_vars($params, array('tpl_type', 'tpl_name', 'sms_sign','sms_info', 'remark'));
        $data['create_time'] = date('Y-m-d H:i:s');
        $ret = $this->_valid($data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        return parent::insert($data);
    }
    /**
     * 验证表数据
     * @param type $param
     * @return type
     */
    private function _valid($param) {
        return $this->format_ret(1);
    }

    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
    
    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('id' => $id));
        return $ret;
    }
    /**
     * 替换模板变量
     * @param type $sms_info
     * @param type $data
     * @return type
     */
    public function replace_tpl_var($sms_info, $data) {
        $tpl_var_arr = array();
        preg_match_all("/{@.*?}/", $sms_info, $tpl_var_arr);
        if (isset($tpl_var_arr[0]) && !empty($tpl_var_arr[0])){
            foreach ($tpl_var_arr[0] as $val) {
                if (isset($this->sms_tpl_var[$val])){
                    $data_key = $this->sms_tpl_var[$val];
                    $var_value = isset($data[$data_key]) ? $data[$data_key] : '';
                    $sms_info = str_replace($val, $var_value, $sms_info);
                }
            }
        }
        return $sms_info;
    }
    /**
     * 拼接短信签名
     * @param type $sms_sign
     * @param string $sms_info
     * @return string
     */
    public function join_sms_sign($sms_sign, $sms_info) {
        if (isset($sms_sign) && '' != $sms_sign){
            $sms_info = '【' . $sms_sign . '】' . $sms_info;
        }
        return $sms_info;
    }
    /**
     * 模板发送测试
     * @param type $mobile
     * @param type $tpl_id
     * @return type
     */
    public function send_test($filter) {
        if (!preg_match("/^1\d{10}$/", $filter['tel'])){
            return $this->format_ret(-1, '','请输入正确的手机号');
        }
        if (empty($filter['sms_info'])){
            return $this->format_ret(-1, '','请输入短信内容');
        }
        
        $filter['sms_type'] = 'send_test';
        //创建短信任务
        $ret = load_model('op/SmsQueueModel')->insert($filter);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '','创建短信任务失败');
        }
        return $this->format_ret(1,'','发送成功');
    }
}
    