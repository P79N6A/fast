<?php

/**
 * Description of OrderDetailDzModel
 *
 * @author Ethan
 */
require_model('tb/TbModel');

class OrderExpressDzModel extends TbModel {

    protected $table = 'order_express_dz';
    protected $detail_tbale = 'order_express_dz_detail';

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} r1 WHERE 1";
        $sql_values = array();
        // 账期
        if (isset($filter['dz_month']) && $filter['dz_month'] != '') {
            $sql_main .= " AND (r1.dz_month=:dz_month )";
            $sql_values[':dz_month'] = $filter['dz_month'];
        }
        
        $select = 'r1.*';
        $sql_main .= " ORDER BY dz_month desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as &$value) {
            $store_name = load_model('base/StoreModel')->get_store_by_code_arr($value['store_code']);
            $value['store_name'] = implode('，', $store_name);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    function view($dz_code) {
        $sql = "SELECT express_dz_id,dz_code,store_code,dz_month,express_cost,create_time FROM {$this->table} WHERE dz_code=:dz_code";
        $data = $this->db->get_row($sql, [':dz_code' => $dz_code]);
        if (!empty($data)) {
            $data['store_name'] = get_store_name_by_code($data['store_code']);
        }
        return $data;
    }
    /**
     * @todo 新增快递对账单插入数据
     */
    function insert_dz_data($data) {
        $cur_date = date('Y-m', time());
        if ($data['dz_month'] > $cur_date) {
            return $this->format_ret(-1, '', '不能创建当前月之后的快递运费对账单');
        }
        $this->begin_trans();
        $ret = $this->insert($data); //添加主单据
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $ret['message']);
        }
        
        $ret_detail = load_model('acc/OrderExpressDzDetailModel')->create_detail($data);
        if ($ret_detail['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', $ret_detail['message']);
        }
        $this->main_write_back($data['dz_code'], $data['store_code']); //回写主单据
        $this->commit();
        return $this->format_ret(1,$data['dz_code'],'');
    }

    /**
     * @todo 添加快递运费对账主单据
     */
    public function insert($data) {
        $ret = $this->is_exists_month_store($data['dz_month'], $data['store_code']);
        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', "您选择的仓库已生成该月快递运费对账单");
        }
        
        $res = $this->is_exists($data['dz_code']);
        if (!empty($res['data'])) {
            return $this->format_ret('-1', '', '快递运费对账单号已存在');
        }
        
        $data['create_time'] = date('Y-m-d H:i:s');
        return parent::insert($data); 
    }

    /**
     * @todo 生成对账编号
     */
    function create_dz_num() {
        $sql = "select express_dz_id  from {$this->table} order by express_dz_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['express_dz_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "YFHX" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /**
     * @todo 根据年月和仓库判断快递运费对账单是否存在
     */
    public function is_exists_month_store($dz_month, $store_code) {
        $sql = "SELECT dz_code FROM {$this->table} WHERE dz_month=:dz_month AND store_code=:store_code";
        $sql_value = array(':dz_month' => $dz_month, ':store_code' => $store_code);
        $ret = $this->get_limit($sql, $sql_value, 1);
        return $ret;
    }

    /**
     * @todo 判断单据是否存在
     */
    public function is_exists($value, $field_name = 'dz_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    /**
     * @todo 生成对账明细后回写系统运费合计
     */
    function main_write_back($dz_code,$store_code){
        $sql = "UPDATE {$this->table}
                    SET express_cost = 
                        (SELECT
                            SUM(weigh_express_money)
                        FROM
                            {$this->detail_tbale}
                        WHERE
                            dz_code =:dz_code
                        AND store_code =:store_code)
                        WHERE
                            dz_code =:dz_code";
        $sql_value = array(":dz_code" => $dz_code, ":store_code" => $store_code);
        return $this->db->query($sql, $sql_value);
    }
}
