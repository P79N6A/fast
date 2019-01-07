<?php

/**
 * 订单标签相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class QuestionLabelModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }

    function get_table() {
        return 'base_question_label';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $sql_main = "FROM {$this->table} $sql_join WHERE 1";
        if (isset($filter['question_label_code']) && $filter['question_label_code'] != '') {
            $sql_main .= " AND question_label_code = :question_label_code";
            $sql_values[':question_label_code'] = $filter['question_label_code'];
        }

        if (isset($filter['question_label_name']) && $filter['question_label_name'] != '') {
            $sql_main .= " AND question_label_name LIKE :question_label_name";
            $sql_values[':question_label_name'] = $filter['question_label_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        foreach ($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['question_label_img_htm'] = ""; //"<img src='assets/img/question_label/{$row['question_label_img']}'/>";
            if ($row['is_sys'] == '1') {
                $ret_data['data'][$k]['is_sys_name'] = '系统内置';
            } else {
                $ret_data['data'][$k]['is_sys_name'] = '自定义';
            }
            if ($row['is_active'] == '1') {
                $ret_data['data'][$k]['is_active'] = "<button class='print_type_btn' style ='background:#1695ca; color:#FFF;'>开启</button><button class='print_type_btn' onclick='change_status(this,0," . $row['question_label_id'] . ")'>关闭</button>";
            } else {
                $ret_data['data'][$k]['is_active'] = "<button class='print_type_btn'   onclick='change_status(this,1," . $row['question_label_id'] . ")' >开启</button><button class='print_type_btn' style ='background:#1695ca; color:#FFF;'>关闭</button>";
            }
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 修改纪录
     */
    function update($question_label, $id) {
        $status = $this->valid($question_label, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('question_label_id' => $id));
        $ret = parent::update($question_label, array('question_label_id' => $id));
        return $ret;
    }

    function update_active($type, $id) {

        $ret = parent::update(array('is_active' => $type), array('question_label_id' => $id));
        return $ret;
    }

    function get_map_data($is_active = 1) {
        $sql = "select question_label_code,question_label_name from base_question_label where is_active = 1";
        $db_arr = ctx()->db->get_all($sql);
        $map = array();
        foreach ($db_arr as $sub_arr) {
            $map[$sub_arr['question_label_code']] = $sub_arr['question_label_name'];
        }
        return $map;
    }
    
    function get_data(){
        static $map = null;
        if(empty($map)){
            $sql = "select question_label_code,question_label_name from base_question_label ";
            $db_arr = ctx()->db->get_all($sql);
            $map = array();
            foreach ($db_arr as $sub_arr) {
                $map[$sub_arr['question_label_code']] = $sub_arr['question_label_name'];
            }
        }
        return $map;
    }




    /**
     * 根据 id 查询数据
     */
    function get_by_id($id) {
        $ret = parent::get_row(array('question_label_id' => $id));
        return $ret;
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret = parent::delete(array('question_label_id' => $id));
        return $ret;
    }

    /**
     * 添加新纪录
     */
    function insert($question) {
        $status = $this->valid($question);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($question['question_label_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', 'QUESTIONLABEL_ERROR_UNIQUE_CODE');

        return parent::insert($question);
    }

    public function get_is_active_value($question_label_code) {
        $sql = "SELECT is_active from base_question_label where question_label_code = '$question_label_code'";
        return $this->db->getOne($sql);
    }

    private function is_exists($value, $field_name = 'question_label_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }


    /**
     * 更新订单设问重量
     * @param $params
     * @return array
     */
    function update_over_weight($params) {
        if (empty($params['content'])) {
            return $this->format_ret('-1', '', '重量不能为空！');
        }
        $weight= floor($params['content']*1000)/1000;
        $ret = parent::update(array('content' => $weight), array('question_label_code' => 'SELL_RECORD_OVERWEIGHT'));
        return $ret;
    }
}
