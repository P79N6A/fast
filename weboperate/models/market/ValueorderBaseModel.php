<?php
require_model('tb/TbModel');
require_lib("comm_util");

class ValueorderBaseModel extends TbModel {

    /**
     * 表名
     */
    public function get_table() {
        return 'osp_valueorder_base';
    }

    /*
     * 获取增值服务订购查询
     */
    public function valueorderList($filter) {
        $sql_main = "FROM {$this->table} a  WHERE 1";
        /** 根据客户名称查询客户id start*/
        if(!empty($filter['customer'])) {
            $filter['customer'] = addslashes($filter['customer']);
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE '%{$filter['customer']}%'";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $this->db->get_all($sql));
        }
        /** 根据客户名称查询客户id end*/
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= ' AND `val_kh_id` IN ("'.implode('","', $filter['kh_id']).'")';
        }
        //关联产品搜索条件
        if (isset($filter['val_cp_id']) && $filter['val_cp_id'] != '') {
           $sql_main .= " AND `val_cp_id` =" . $filter['val_cp_id'];
        }
        //排序条件
        $sql_main .= " ORDER BY `val_orderdate` DESC";
        $ret_status = OP_SUCCESS;
        $ret_data = $this->get_page_from_sql($filter, $sql_main, '`a`.*');
        filter_fk_name($ret_data['data'], array('val_kh_id|osp_kh','val_channel_id|org_channel','val_cp_id|osp_chanpin'));
        $ret_data['data'] = array_map(function ($row) {
            $status = array('已下单', '已付款', '已审核', '已作废');
            $row['val_status'] = $status[$row['val_status']];
            empty($row['val_paydate']) and $row['val_paydate'] = '未付款';
            empty($row['val_checkdate']) and $row['val_checkdate'] = '未审核';
            return $row;
        }, $ret_data['data']);
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /**
     * 添加订单
     * @param array $order
     */
    public function valueorderAdd($order)
    {
        $order['val_num'] = create_fast_bill_sn('ZZDGBH');
        $order['val_standard_price'] = $order['val_cheap_price'] = $order['val_actual_price'] = 0;
        $order['val_orderdate'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $ret = $this->insert($order);
        $ret['order'] = $order;
        return $ret;
    }
    
    /**
     * 订单明细变更
     * @param array $order
     */
    public function valueorderEditDetail($detail)
    {
        $sql1 = "SELECT SUM(`vs_cheap_price` + `vs_actual_price`) AS `total` "
              . "FROM `osp_valueorder_valueserver` "
              . "WHERE `vs_val_num` = '{$detail['vs_val_num']}'";
        $get = array_map('floatval', $this->db->getRow($sql1));
        $sql2 = "UPDATE `{$this->table}` "
             . "SET `val_standard_price` = '{$get['total']}' "
             . "WHERE `val_num` = '{$detail['vs_val_num']}'";
        $this->query($sql2);
    }
    
    /**
     * 通过订单编号获取订单数据
     * @param string $id
     */
    public function getOrderById($id)
    {
        $data = $this->create_mapper($this->table)
                     ->where(array('val_num' => $id))
                     ->find_by();
        return $data;
    }
    
    /**
     * 通过订单编号获取订单数据的特定属性
     * @param string $id
     * @param string $field
     */
    public function getOrderField($id, $field)
    {
        $data = $this->getOrderById($id);
        return $data[$field];
    }
    
    /**
     * 编辑订单
     */
    public function valueorderEdit($order)
    {
        return $this->update($order, array('val_num' => $order['val_num']));
    }
}
