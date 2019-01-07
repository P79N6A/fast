<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('base');
require_lib("comm_util");

class ValueorderServerModel extends TbModel {

    function get_table() {
        return 'osp_valueorder_auth';
    }

    function get_kh_server($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} r1 INNER JOIN osp_valueserver r2 ON r1.vra_server_id=r2.value_id WHERE 1 AND r2.value_enable=1 AND r2.value_publish_status=1 ";
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND r1.vra_kh_id =:kh_id";
            $sql_values[':kh_id'] = $filter['kh_id'];
        } else {
            $sql_main.=" AND 1=2 ";
        }
        $select = 'r1.*,r2.value_name,r2.function_application,r2.value_cat';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $now = date("Y-m-d H:i:s");
        foreach ($data['data'] as &$value) {
            if (!empty($value['function_application'])) {
                $value['function_application'] = load_model('market/ValueModel')->deal_path($value['function_application']);
                $value['user_help'] = "<a target='_blank' href='" . $value['function_application'] . "'>使用帮助</a>";
            } else {
                $value['user_help'] = '';
            }
            $value['value_category'] = oms_tb_val('osp_valueserver_category', 'vc_name', array('vc_id' => $value['value_cat']));
            if ($value['score'] > 0) {
                for ($i = 0; $i < $value['score']; $i++) {
                    $value['score_gread'].="<img src='assets/images/startScore/starsy.png' >"; //height='20' width='29'
                }
            } else {
                $value['score_gread'] = '';
            }
            //到期前30天显示续费
            $date = $this->check_end_date($value['vra_enddate'], 30);
            $value['renew'] = ($now < $date) ? 0 : 1;
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 前端续费
     */
    function renew_ali_pay($params) {
        $url_params = array(
            'value_id' => $params['vra_server_id'],
            'kh_id' => $params['kh_id'],
            'get_url' => $params['get_url'],
            'user_code' => $params['user_code'],
        );
        $pay_ret = load_model('market/ValueModel')->add_server_order($url_params);
        return $pay_ret;
    }

    /**
     * 获取$days之前的日期
     */
    function check_end_date($end_date, $days) {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} day", strtotime($end_date)));
        return $date;
    }

}
