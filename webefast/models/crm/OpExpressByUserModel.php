<?php
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class OpExpressByUserModel extends TbModel {
    
    /**
     * 指定数据表
     * @return string
     */
    public function get_table() {
        return 'op_express_by_user';
    }
    
    /**
     * 适配策略
     * @param  string $buyer_name
     * @return string
     */
    public function parse($buyer_name)
    {
        static $data;
        if (is_null($data)) {
            $data = $this->db->get_all('SELECT customer_name, express_code FROM '.$this->table);
            list($temp, $data) = array($data, array());
            while($pop = array_pop($temp)) {
                $data[$pop['customer_name']] = $pop['express_code'];
            }
        }
        if (array_key_exists($buyer_name, $data)) {
            return $this->format_ret('1', $data[$buyer_name]);
        } else {
            return $this->format_ret('-1');
        }
    }
    
    /**
     * 获取指定商品列表
     * @param type $filter
     * @return type
     */
    public function get_by_page($filter) {
        if (empty($filter['express_code'])) {
//            $sql_main   = "FROM {$this->table} WHERE 1=1 AND express_code IS NULL ";
//            $data       = $this->get_page_from_sql($filter, $sql_main);
            $data = array();
        } else {
            $sql_values = array(':express_code' => $filter['express_code']);
            $sql_main   = "FROM {$this->table} WHERE 1=1 AND express_code = :express_code ";
            $data       = $this->get_page_from_sql($filter, $sql_main, $sql_values);
        }
        $status     = OP_SUCCESS;
        return $this->format_ret($status, $data);
    }
    
    public function do_update_express($request)
    {
//        print_r($request);
//        exit();
        $this->begin_trans();
        $ret = parent::update(
            array('express_code' => $request['new_express_code']),
            array('express_code' => $request['express_code'])
        );
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $this->commit();
        return $ret;
    }
    
    /**
     * 获取xls文件，将数据导入
     * @param type $file
     * @param type $express_code
     * @return type
     */
    public function import_data($file, $express_code){
        $data = array();
        $file = fopen($file, "r");
        $row_type = array('buyer_name', 'tel');
        $i =0 ;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $row = $this->tran_csv($row, $row_type);
                if (!empty($row['buyer_name'])) {
                    $data[] = array(
                        'customer_name' => $row['buyer_name'],
                        'mobile'        => $row['tel'],
                        'express_code'  => $express_code,
                        'lastchanged'   => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
                    );
                }
            }
            $i++;
        }
        $update_str = " mobile = VALUES(mobile), express_code = VALUES(express_code) ";
        $this->insert_multi_duplicate('op_express_by_user', $data, $update_str);
        return $this->format_ret(1, count($data));
    }

    /**
     * 一键清空
     * @param Array $where
     * @return Array
     */
    public function delete_all_users($where = array())
    {
        $ret = count($where) > 0 ? parent::delete($where) : parent::delete();
        return $ret;
    }
    
    /**
     * csv文件转数组
     * @param type $row
     * @param type $row_type
     * @return type
     */
    private function tran_csv(&$row, $row_type){
        $new_row = array();
        if(!empty($row)){
            foreach($row as $key => $val){
                $val = str_replace('"', '', $val);
                if(isset($row_type[$key])){
                    $new_key = $row_type[$key];
                    $new_row[$new_key] = $val;
                }
            }
        }
        return $new_row;
    }    
}

