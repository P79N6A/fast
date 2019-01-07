<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');

class RecordTemplatesModel extends TbModel {

    protected $table = 'sys_print_templates';

    /**
     * 根据条件查询数据
     * @param array $filter
     * @return array
     */
    function get_by_page($filter = array()) {
        $arr = array('clodop_print');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $clodop = isset($ret_arr['clodop_print']) ? $ret_arr['clodop_print'] : 0;
        $templates = require_conf('tprint/record_template');
        $templates_url = $templates['data_source'];
        $templates_code = $templates['template_code'];
//        var_dump($templates);exit;

        $sql_values = array();
        $sql_main = " FROM {$this->table} WHERE 1 ";

        if (isset($filter['print_templates_name']) && $filter['print_templates_name'] != '') {
            $sql_main .= " AND print_templates_name LIKE :print_templates_name";
            $sql_values[':print_templates_name'] = $filter['print_templates_name'] . '%';
        }
        if($clodop == 0){
            $sql_main .= " AND new_old_type != 2";
        }else if($clodop == 1){
            $sql_main .= " AND new_old_type != 1";
        }
        $arr = $templates_code;
        $templates_code_str = $this->arr_to_in_sql_value($arr, 'print_templates_code', $sql_values);
        $sql_main .= " AND print_templates_code in ({$templates_code_str}) GROUP BY print_templates_code";
        $select = 'distinct print_templates_code,print_templates_name,print_templates_id';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        foreach ($data['data'] as $key => &$value) {
            if (in_array($value['print_templates_code'], $templates_code)) {
                $value['url'] = "'" . $templates_url[$value['print_templates_code']] . "'";
                if ($value['print_templates_code'] == 'weipinhuijit_box_print') {
                    $value['print_templates_name'] = '箱唛模板';
                    $value['template_name'] = "'箱唛模板'";
                    $value['print_templates_code'] = "'" . $value['print_templates_code'] . "'";
                } else {
                    $value['template_name'] = "'" . $value['print_templates_name'] . "'";
                    $value['print_templates_code'] = "'" . $value['print_templates_code'] . "'";
                }
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
