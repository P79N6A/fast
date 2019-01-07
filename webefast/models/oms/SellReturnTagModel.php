<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

/**
 * Description of SellReturnTagModel
 *
 * @author user
 */
class SellReturnTagModel extends TbModel {

    //put your code here
    protected $table = 'oms_sell_return_tag';

    function get_list_by_code($filter) {
        $sql_main = "FROM {$this->table} rl WHERE rl.sell_return_code=:sell_return_code";
        $sql_values['sell_return_code'] = $filter['sell_return_code'];
        $sql_values['tag_type'] = $filter['tag_type'];
        $select = "*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$v) {
            $v['tag_name'] = oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $v['tag_v']));
        }
        return $this->format_ret(1, $data);
    }

    function add_return_tag($sell_return_code, $tag_arr) {
        $tag_str = "'" . implode("','", $tag_arr) . "'";
        $sql = "select return_label_code,return_label_name from base_return_label where return_label_code in({$tag_str}) ";
        $data = $this->db->get_all($sql);
        $tag_data = array();
        $tag_type = 'return_tag';
        $ret = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $tag_data[] = array('sell_return_code' => $sell_return_code, 'tag_type' => $tag_type,
                    'tag_v' => $val['return_label_code'], 'tag_desc' => $val['return_label_name']);
            }
            $ret = $this->insert_multi($tag_data, TRUE);
        } else {
            $ret = $this->format_ret(-1, '', '未找到指定标签');
        }
        return $ret;
    }

    function get_sell_return_by_tag($tag_arr, $type = 'return_tag') {
        $tag_str = "'" . implode("','", $tag_arr) . "'";
        $sql = "select sell_return_code from {$this->table} where tag_type=:tag_type AND tag_v IN({$tag_str})  ";
        $data = $this->db->get_all($sql, array(':tag_type' => $type));
        $ret_data = array();
        foreach ($data as $val) {
            $ret_data[] = $val['sell_return_code'];
        }
        return $ret_data;
    }
    
    /**
     * 获取所有退单标签表中的退单号
     * @return type
     */
    function get_sell_return_code() {
        $sql = "SELECT sell_return_code  FROM `oms_sell_return_tag`";
        $data = $this->db->getAll($sql);
        $tag_record_arr = array_column($data, 'sell_return_code');
        return $tag_record_arr;
    }

}
