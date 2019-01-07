<?php

/**
 * 快递公司相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');
require_lib('util/oms_util', true);

class ShippingModel extends TbModel {

    var $print_vars = array(
        'record_code' => '订单编号',
        'money' => '订单金额',
        'sender' => '发货人',
        'sender_tel' => '发货人电话',
        'sender_mobile' => '发货人手机',
        'sender_shop_name' => '店铺名称',
        'sender_address' => '发货地址',
        'sender_zip' => '发货邮编',
        'receiver_name' => '收件人',
        'buy_name' => '买家昵称',
        'receiver_tel' => '收件人电话',
        'receiver_province' => '省',
        'receiver_city' => '市',
        'receiver_district' => '区',
        'receiver_address' => '收件人地址',
        'receiver_zip_code' => '收件人邮编',
        'num' => '商品数量',
        'buy_remark' => '买家备注',
        'sell_remark' => '卖家备注',
    );
    var $tpl_val = array();
    private $print_type_arr = [0 => '普通热敏', 1 => '直联热敏', 2 => '云栈热敏', 3 => '无界热敏'];

    public function __construct() {
        parent :: __construct('base_express');
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();

        $sql_main = "FROM {$this->table} e INNER JOIN base_express_company ec ON e.company_code = ec.company_code WHERE 1";
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
            $sql_main .= " AND express_code = :express_code";
            $sql_values[':express_code'] = $filter['express_code'];
        }

        if (isset($filter['express_name']) && $filter['express_name'] !== '') {
            $sql_main .= " AND express_name LIKE :express_name";
            $sql_values[':express_name'] = $filter['express_name'] . '%';
        }

        if (isset($filter['status']) && $filter['status'] !== '') {
            $sql_main .= " AND status = :status";
            $sql_values[':status'] = $filter['status'];
        }
        $select = 'ec.company_name,ec.company_code,e.express_id,e.express_code,e.express_name,e.`status`,e.print_type,e.pt_id,e.df_id,e.rm_id,e.rm_shop_code,e.remark';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        //判断批发快递单模板是否开启
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(['pur_express_print']);
        $pur_express_print = isset($ret_arr['pur_express_print']) ? $ret_arr['pur_express_print'] : '';
        $sql = "select print_templates_id _id,print_templates_name _name,is_buildin _type from sys_print_templates  where type = 1 ";
        if ($pur_express_print == 0) {
            $sql .= " AND is_buildin <> 4 ";
        }
        $templates = $this->db->get_all($sql);
        $templates = load_model('util/ViewUtilModel')->get_map_arr($templates, '_id');
        foreach ($data['data'] as &$row) {
            $print_type = (int) $row['print_type'];
            $express_id = (int) $row['express_id'];
            $html_pt = '<option value="" ></option>';
            $html_df = '<option value="" ></option>';
            $html_rm = '<option value="" ></option>';
            $row['pt_id_html'] = isset($templates[$row['pt_id']]) ? $templates[$row['pt_id']]['_name'] : '';
            $row['df_id_html'] = isset($templates[$row['df_id']]) ? $templates[$row['df_id']]['_name'] : '';
            $row['rm_id_html'] = isset($templates[$row['rm_id']]) ? $templates[$row['rm_id']]['_name'] : '';
            foreach ($templates as $t_id => $t_val) {
                $t_html = "<option value=\"{$t_id}\" >{$t_val['_name']}</option>";
                $t_html_s = "<option value=\"{$t_id}\" selected>{$t_val['_name']}</option>";
                if ($print_type === 0 && in_array($t_val['_type'], [0, 1, 3, 4])) {
                    $html_pt .= $row['pt_id'] != $t_id ? $t_html : $t_html_s;
                    $html_df .= $row['df_id'] != $t_id ? $t_html : $t_html_s;
                } else if (($print_type === 1 && in_array($t_val['_type'], [0, 1])) || ($print_type === 2 && in_array($t_val['_type'], [2, 3])) || ($print_type === 3 && $t_val['_type'] == 5)) {
                    $html_rm .= $row['rm_id'] != $t_id ? $t_html : $t_html_s;
                }
            }
            //打印类型html
            $row['print_type_html'] = $this->get_print_type_html($print_type, $express_id);

            //模板选择select-html
            if ($print_type === 0) {
                $row['pt_id_html'] = "<select id=\"pt_html\" onchange=\"changeTpl({$express_id},this.value,'pt_id')\" style =\"width:99%;\" name=\"pt_html\" >{$html_pt}</select>";
                $row['df_id_html'] = "<select id=\"df_html\" onchange=\"changeTpl({$express_id},this.value,'df_id')\" style =\"width:99%;\" name=\"df_html\" >{$html_df}</select>";
            }
            if (in_array($print_type, [1, 2, 3])) {
                $row['rm_id_html'] = "<select id=\"rm_html\" onchange=\"changeTpl({$express_id},this.value,'rm_id')\" style =\"width:99%;\" name=\"rm_html\" >{$html_rm}</select>";
            }

            $row['company_html'] = $row['company_name'] . "(" . $row['company_code'] . ")";
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    private function get_print_type_html($print_type, $express_id) {
        $html = '';
        foreach ($this->print_type_arr as $_type => $_name) {
            $selected = $_type == $print_type ? 'print_type_select' : '';
            $onclick = $_type == $print_type ? '' : "onclick=\"changeType({$express_id},{$_type})\"";
            $html .= "<button class=\"print_type_btn {$selected}\" {$onclick}>{$_name}</button>";
        }
        return $html;
    }

    public function get_express_template_select($_type) {
        $buildin_str = '';
        switch ($_type) {
            case 0://普通、到付
                $buildin_str = '0, 1, 3, 4';
                break;
            case 1://直连
                $buildin_str = '0, 1';
                break;
            case 2://云栈
                $buildin_str = '2,3';
                break;
            case 3://无界
                $buildin_str = '5';
                break;
            default:
                break;
        }
        $where = empty($buildin_str) ? '' : " AND is_buildin IN({$buildin_str})";
        $sql = 'SELECT print_templates_id,print_templates_name FROM sys_print_templates WHERE `type`=1' . $where;
        return $this->db->get_all($sql);
    }

    public function get_express_company_select($_type) {
        $sql_join = '';
        if ($_type == 3) {
            $sql_join = 'INNER JOIN base_express_jd AS bj ON bc.company_code=bj.company_code';
        }
        $sql = "SELECT bc.company_code,bc.company_name FROM base_express_company AS bc {$sql_join}";
        return $this->db->get_all($sql);
    }

    public function get_express_shop_select($_type) {
        $where = '';
        if ($_type === 'jd') {
            $where = 'AND sale_channel_code=\'jingdong\' AND is_active = 1';
        }
        $sql = 'SELECT shop_code,shop_name FROM base_shop WHERE 1 ' . $where;
        return $this->db->get_all($sql);
    }

    /**
     * 获取快递信息-加载详情页面
     * @param int $express_id 系统快递id
     */
    public function get_express_info($express_id) {
        $express = $this->get_by_id($express_id);
        $express = $express['data'];
        if ($express['print_type'] == 3) {
            //无界热敏
            $jd_provider = load_model('remin/WujieModel')->get_provider_by_company($express['company_code']);
            $express = array_merge($express, $jd_provider['data']);
        }

        return $express;
    }

    /**
     * 方法名  base.shipping.list.get
     * 功能描述  配送方式查询
     */
    public function api_get_by_page($param) {
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);
        $select = '*';
        $sql_values = array();
        $sql_main = "FROM {$this->table} e,base_express_company ec WHERE e.company_code = ec.company_code";
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                $sql_values[":{$key}"] = $val;
                $sql_main .= " AND e.{$key}=:{$key}";
            }
        }

        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $ret);
    }

    /**
     * @param $express_id
     * @return array
     */
    function get_by_id($express_id) {
        $arr = $this->get_row(array('express_id' => $express_id));
        return $arr;
    }

    /**
     * 更新
     * @param $data
     * @param $express_id
     * @return array
     */
    function update_by_id($data, $express_id) {
        $ret = parent::update($data, array('express_id' => $express_id));
        return $ret;
    }

    function update_by_code($data, $express_code) {
        $ret = parent::update($data, array('express_code' => $express_code));
        return $ret;
    }

    //列表数据
    function get_list($is_active = null) {
        $sql = "select express_code,express_name,express_id FROM {$this->table} where 1=1";
        if (!empty($is_active)) {
            $sql .= " and status = " . (int) $is_active;
        }
        //echo $sql;die;
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    /**
     * 添加新纪录
     */
    function insert($express) {
        $status = $this->valid($express);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($express['express_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', '代码已存在');

        $ret = $this->is_exists($express['express_name'], 'express_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', '出错');

        return parent :: insert($express);
    }

    /**
     * 修改纪录
     */
    function update($express, $id) {
        $data = $this->is_exists($id, 'express_id');
        if ($data['status'] < 1) {
            return $this->format_ret(-1, '', '配送方式不存在,请检查');
        }
        $data = $data['data'];
        if ((!empty($express['company_code']) && $express['company_code'] != $data['company_code']) || (!empty($express['rm_shop_code']) && $express['rm_shop_code'] != $data['rm_shop_code'])) {
            $express['sign_id'] = NULL;
        }
        $ret = parent :: update($express, array('express_id' => $id));
        return $ret;
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret = parent :: delete(array('express_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'express_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('status' => $active), array('express_id' => $id));
        return $ret;
    }

    function update_type($print_type, $express_id) {
        if (!in_array($print_type, array(0, 1, 2, 3))) {
            return $this->format_ret('error_params');
        }
        $print_type = (int) $print_type;
        $updata_data = array('print_type' => $print_type);

        if (in_array($print_type, [2, 3])) {
            $is_buildin = $print_type === 2 ? '2,3' : 5;
            $updata_data['rm_id'] = $this->get_relation_template($express_id, $is_buildin);
        }

        $ret = parent::update($updata_data, array('express_id' => $express_id));
        return $ret;
    }

    function get_relation_template($express_id, $is_buildin) {
        $sql = "SELECT s.print_templates_id FROM sys_print_templates s INNER JOIN base_express e ON s.company_code=e.company_code WHERE e.express_id =:express_id AND s.is_buildin IN({$is_buildin})";
        $sql_values = [':express_id' => $express_id];
        $print_templates_id = $this->db->get_value($sql, $sql_values);
        if (empty($print_templates_id)) {
            $print_templates_id = 0;
        }
        return $print_templates_id;
    }

    function update_tpl($type, $express_id, $value) {

        $ret = parent::update(array($type => $value), array('express_id' => $express_id));
        return $ret;
    }

    function get_shipping_tpl($express_code) {
        //$express_code
        $ret = $this->get_row(array('express_code' => $express_code));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1);
        }
        $tpl_data = array();
        $shop_code = &$ret['data']['rm_shop_code'];
        if ($ret['data']['print_type'] == 0) {
            if (empty($ret['data']['df_id']) && empty($ret['data']['pt_id'])) {
                return $this->format_ret(-2, '', '未设置对应打印模版');
            } else {
                if (!empty($ret['data']['pt_id'])) {
                    $ret1 = load_model('sys/PrintTemplatesModel')->get_templates_by_id($ret['data']['pt_id']);
                    $tpl_data['pt'] = $ret1['data'];
                }
                if (!empty($ret['data']['df_id'])) {
                    $ret = load_model('sys/PrintTemplatesModel')->get_templates_by_id($ret['data']['df_id']);
                    $tpl_data['df'] = $ret['data'];
                }
            }
        } else if ($ret['data']['print_type'] > 0) {
            if (empty($ret['data']['rm_id'])) {
                return $this->format_ret(-2, '', '未设置对应打印模版');
            } else {
                $ret = load_model('sys/PrintTemplatesModel')->get_templates_by_id($ret['data']['rm_id']);
                if ($ret['data']['is_buildin'] == 2) {//云栈热敏
                    $tpl_data['shop_info'] = load_model('sys/PrintTemplatesModel')->get_shop_key_by_shop_code($shop_code);
                    if (!empty($ret['data']['template_val']['itemkey'])) {
                        $tpl_data['itemkey'] = explode(",", $ret['data']['template_val']['itemkey']);
                    } else {
                        $tpl_data['itemkey'] = array();
                    }
                    if (!empty($ret['data']['template_val']['detail'])) {
                        $detail_arr = explode("|", $ret['data']['template_val']['detail']);
                        foreach ($detail_arr as $detail_val) {
                            list($detail, $item) = explode(':', $detail_val);
                            $tpl_data['detail'][] = $item;
                        }
                        $tpl_data['detail_row'] = $ret['data']['template_val']['detail_row'];
                        $tpl_data['detail_val'] = $ret['data']['template_val']['detail_val'];
                        $tpl_data['detail_key'] = $ret['data']['template_val']['detail'];
                    } else {
                        $tpl_data['detail'] = array();
                    }
                }
                $tpl_data['rm'] = $ret['data'];
            }
        } else {
            
        }
        return $this->format_ret(1, $tpl_data);
    }

    /**
     * 选择表通过field_name查询
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function table_get_by_field($table, $field_name, $value, $select = "*") {
        $sql = "select {$select} from {$table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));
        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * 供BUI下拉选择配送方式使用
     */
    public function get_bui_select_shipping($first_type = '', $first_val = '') {
        $sql = "SELECT express_code,express_name FROM {$this->table} WHERE status=1";
        $shipping = $this->db->get_all($sql);
        $first_txt = '';
        switch ($first_type) {
            case 1:
                $first_txt = '全部';
                break;
            case 2:
                $first_txt = '请选择';
                break;
            default:
                break;
        }
        if (!empty($first_type)) {
            $data[] = array('text' => "&nbsp;{$first_txt}", 'value' => $first_val);
        }
        foreach ($shipping as $val) {
            $arr = array();
            $arr['text'] = $val['express_name'];
            $arr['value'] = $val['express_code'];
            $data[] = $arr;
        }
        return $data;
    }

    /**
     * 公共选择快递数据源
     * @param array $filter 过滤条件
     * @return array 数据集
     */
    public function get_select_comm_express_data($filter) {
        $sql_main = "FROM `base_express` AS be WHERE 1";
        $select = 'be.`express_code`,be.`express_name`';
        $sql_values = array();
        if (!empty($filter['ploy_code']) && $filter['data_type'] == 'ploy_express') {
            $exists_express = load_model('op/ploy/ExpressPloyExpModel')->get_express_by_ploy($filter['ploy_code']);
            if (!empty($exists_express)) {
                $exists_express = array_column($exists_express, 'express_code');
                $express_str = $this->arr_to_in_sql_value($exists_express, 'express_code', $sql_values);
                $sql_main .= " AND be.`express_code` NOT IN({$express_str}) ";
            }
        }

        if (isset($filter['express']) && $filter['express'] != '') {
            $sql_main .= ' AND (be.`express_code` LIKE :express OR be.`express_name` LIKE :express) ';
            $sql_values[':express'] = "%{$filter['express']}%";
        }

        $sql_main .= ' ORDER BY be.`express_id` ASC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        return $this->format_ret(1, $data);
    }

}
