<?php

/**
 * 商店 相关业务
 *
 */
require_model('tb/TbModel');
require_lang('base');

class ExpressCompanyModel extends TbModel {

    function get_table() {
        return 'base_express_company';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1 ";

        //关键字
        if (isset($filter['company_code']) && $filter['company_code'] != '') {
            $sql_main .= " AND company_code LIKE :company_code ";
            $sql_values[':company_code'] = $filter['company_code'] . '%';
        }
        if (isset($filter['company_name']) && $filter['company_name'] != '') {
            $sql_main .= " AND company_name LIKE :company_name ";
            $sql_values[':company_name'] = '%' . $filter['company_name'] . '%';
        }
        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_data_from_api() {
        $sql = "SELECT shop_code FROM base_shop_api WHERE source = 9";
        $shop_code = CTX()->db->getOne($sql);

        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($shop_code);
        $ret = $o_biz->taobao_logistics_companies_get();
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        $insert_data = array();
        foreach ($ret['logistics_companies']['logistics_company'] as $sub_ret) {
            $insert_data[] = array(
                'company_code' => $sub_ret['code'],
                'company_name' => $sub_ret['name'],
                'rule' => $sub_ret['reg_mail_no'],
                'sys' => 1,
            );
        }
        $sql = "TRUNCATE table base_express_company";
        CTX()->db->query($sql);
        M('base_express_company')->insert($insert_data);
        return $this->format_ret(1);
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('company_id' => $id));
        return $ret;
    }

    function get_api_content($company_code) {
        $sql = "select api_content from base_express_company where company_code=:company_code";
        $api_content = $this->db->get_value($sql, array(':company_code' => $company_code));
        $data = array();
        $api_content_arr = array();
        if (!empty($api_content)) {
            $api_content_arr = json_decode($api_content, true);
        }
        $express_company_conf = require_conf('sys/express_company');
        if (isset($express_company_conf[$company_code])) {
            $data = &$express_company_conf[$company_code];
            foreach ($data as $key => &$val) {
                $val['val'] = isset($api_content_arr[$key]) ? $api_content_arr[$key] : '';
            }
        }

        return $this->format_ret(1, $data);
    }

    function save_api_content($param) {
        $company_code = $param['company_code'];

        $express_company_conf = require_conf('sys/express_company');
        $conf = $express_company_conf[$company_code];
        $api_content_arr = array();
        foreach ($conf as $key => $val) {
            $api_content_arr[$key] = isset($param[$key]) ? $param[$key] : '';
        }
        $api_content = json_encode($api_content_arr);

        $where = array('company_code' => $company_code);
        return $this->update(array('api_content' => $api_content), $where);
    }
    
    function get_view_select() {
        $rs = $this->db->get_all("SELECT company_code,company_name FROM base_express_company");
        $express_arr = array();
        foreach ($rs as $val) {
            $express_arr[$val['company_code']] = $val['company_name'];
        }
        return json_encode(bui_bulid_select($express_arr));
    }

}
