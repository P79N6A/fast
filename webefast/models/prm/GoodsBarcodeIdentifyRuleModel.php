<?php

/**
 * 条码识别方案相关业务
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('prm');

class GoodsBarcodeIdentifyRuleModel extends TbModel {

    function get_table() {
        return 'goods_barcode_identify_rule';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {

        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();

        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //$url = 'http://'.$_SERVER['HTTP_HOST'].'/webapp/uploads/';

        foreach ($data['data'] as $key => $value) {
            $arr1 = explode(',', $value['rule_content1']);
            if ($value['rule_content1'] <> '') {
                $data['data'][$key]['rule_content1'] = '去掉前' . $arr1[0] . '位，去掉后' . $arr1[1] . '位，剩余中间字符作为条码';
            }
            $arr2 = explode('|', $value['rule_content2']);
            if ($value['rule_content2'] <> '') {
                $con2 = '';
                foreach ($arr2 as $v) {
                    $arr2_2 = explode(',', $v);
                    $con2 .= '从第' . $arr2_2[0] . '位开始，截取' . $arr2_2[1] . '位，';
                }
                $data['data'][$key]['rule_content2'] = $con2 . '组合作为条码';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {

        return $this->get_row(array('rule_id' => $id));
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    public function yanzheng($barcode, $priority) {
        $sql = "select *  from {$this->table} where  priority = :priority ";
        $arr = array(':priority' => $priority);
        $data = $this->db->get_row($sql, $arr);
        $mix_priority = $this->db->get_value("select priority from {$this->table} order by priority desc");
        if ($data) {
            $fangan_new_str = '';
            if ($data['rule_sort'] == '2') {
                $fangan2_arr = explode('|', $data['rule_content2']);
                foreach ($fangan2_arr as $key => $value) {
                    $arr = explode(',', $value);
                    $fangan_new_str .= substr($barcode, $arr[0], $arr[1]);
                }
            }
            if ($data['rule_sort'] == '1') {
                $fangan1_arr = explode(',', $data['rule_content1']);

                $sub_length = strlen($barcode) - intval($fangan1_arr[0]) - intval($fangan1_arr[1]);
                $fangan_new_str .= substr($barcode, intval($fangan1_arr[0]), $sub_length);
            }
            $sql2 = "select * from goods_barcode where  barcode = :barcode ";
            $arr2 = array(':barcode' => $fangan_new_str);
            $data2 = $this->db->get_row($sql2, $arr2);
            if ($data2) {
                //$msg = 'SP001[针织衫]，规格1为Y001[红色]，尺码为C001[M]';
                $msg = $data2['goods_code'] . '，规格1为' . $data2['spec1_code'] . '，规格2为' . $data2['spec2_code'];
                return $this->format_ret(true, $data2, $msg);
            }else{
                $sql3 = "select * from goods_barcode_child where  barcode = :barcode ";
                $arr3 = array(':barcode' => $fangan_new_str);
                $data3 = $this->db->get_row($sql3, $arr3);
                if(!empty($data3)){
                    $msg = $data3['goods_code'] . '，规格1为' . $data3['spec1_code'] . '，规格2为' . $data3['spec2_code'];
                    return $this->format_ret(true, $data3, $msg);    
                }
                
            }
        }
        $priority = intval($priority) + 1;
        if ($priority <= $mix_priority) {
           return $this->yanzheng($barcode, $priority);
        } else {
            return $this->format_ret(-1, '', '识别失败，没有商品或商品没有生成条形码');
        }
    }

    /*
     * 添加新纪录
     */

    function insert($data) {
        $status = $this->valid($data);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        return parent::insert($data);
    }

    /*
     * 删除记录
     * */

    function delete($brand_id) {

        $ret = parent::delete(array('rule_id' => $brand_id));
        return $ret;
    }

    /*
     * 修改纪录
     */

    function update($data, $rule_id) {
        $status = $this->valid($data, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('rule_id' => $rule_id));
        $ret = parent::update($data, array('rule_id' => $rule_id));
        return $ret;
    }

    //最后一条记录的
    function last_record() {
        $sql = "select priority  from {$this->table} order by rule_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        return $data;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {

        if (!isset($data['rule_name']) || !valid_input($data['rule_name'], 'required'))
            return 'RULE_ERROR_NAME';

        return 1;
    }

    function is_exists($value, $field_name = 'brand_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

}
