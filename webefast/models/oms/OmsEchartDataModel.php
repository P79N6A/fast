<?php
require_model('tb/TbModel');

class OmsEchartDataModel extends TbModel {
    protected $table = 'oms_echart_data';
    
    /**
     * 获取店铺的订单数据
     * @param string $shop_code
     * @return array
     */
    public function getData($shop_code,$date_type=0) {
        $shop_arr = array();
        if ($shop_code == '0') {
            $shop_arr = load_model('base/ShopModel')->get_purview_shop();
            $shop_arr = array_column($shop_arr, 'shop_code');
        } else {
            $shop_arr = array($shop_code);
        }
        $sql_values = array();
        $sql = "SELECT SUM(`order_num`) AS order_num FROM `{$this->table}` WHERE 1 ";
        if(empty($shop_arr)) {   
            return array();
        }
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
        $sql .= " AND `shop_code` IN({$shop_str}) ";
        //统计前一天的数据
        if ($date_type == 1) {
            $pre_date = date('Y-m-d', strtotime("-1 days"));
            $sql .= " AND lastchanged>=:start AND  lastchanged<=:end ";
            $sql_values[':start'] = $pre_date . ' 00:00:00';
            $sql_values[':end'] = $pre_date . ' 23:59:59';
        }
        $sql .=" GROUP BY order_type ORDER BY id ";
        $data = $this->db->get_all($sql,$sql_values);
        return array_column($data, 'order_num');
    }


    /**
     * 储存数据
     * @param string $shop_code
     * @param array $data
     */
    public function saveData($shop_code, $data){
        $map1 = array(
            'total_order',
            'change_done',
            'confirm_done',
            'pick_done',
            'delivery_done',
            'back_done'
        );
        foreach($data['up'] as $key => $num) {
            $save[] = array(
                'shop_code' => $shop_code,
                'order_type' => $map1[$key],
                'order_num' => $num,
                'lastchanged' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
            );
        }
        $map2 = array(
            'total_money',
            'change_todo',
            'confirm_todo',
            'pick_todo',
            'delivery_todo',
            'back_todo'
        );
        foreach($data['down'] as $key => $num) {
            $save[] = array(
                'shop_code' => $shop_code,
                'order_type' => $map2[$key],
                'order_num' => $num,
                'lastchanged' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
            );
        }
        $record_rigth = array(
            'fail_num',
            'chec_timeout',
            'overtime',
            'write_fail',
            'problem',
            'out_store',
            'pending'
        );
        foreach($data['right'] as $key => $num) {
            $save[] = array(
                'shop_code' => $shop_code,
                'order_type' => $record_rigth[$key],
                'order_num' => $num,
                'lastchanged' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
            );
        }
        
        $this->insert_multi_duplicate('oms_echart_data', $save, 'order_num = VALUES(order_num)');
    }
}
