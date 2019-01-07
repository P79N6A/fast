<?php
require_model('tb/TbModel');
require_lib('util/oms_util', true);
class BserpInvSyncModel extends TbModel{
    function get_table() {
        return 'api_bserp_item_quantity';
    }
    
    function get_by_page($filter){
    	$sql_values = array();
        $sql_main = " FROM {$this->table} a WHERE 1 ";
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $store_arr = explode(",", $filter['store_code']);
            $efast_arr = array();
            $erp_arr = array();
            foreach ($store_arr as $val) {
                $arr = explode("_", $val);
                $efast_arr[] = $arr[0];
                $erp_arr[] = $arr[1];
            }
            $efast_str = $this->arr_to_in_sql_value($efast_arr,'efast_store_code',$sql_values);
            $rep_str = $this->arr_to_in_sql_value($erp_arr,'CKDM',$sql_values);
            $sql_main .= " AND a.efast_store_code in ({$efast_str}) AND a.CKDM in ({$rep_str})";
        }
        //商品
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND a.SPDM LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //条形码
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
            $sql_main .= " AND a.barcode LIKE :goods_barcode ";
            $sql_values[':goods_barcode'] = '%' . $filter['goods_barcode'] . '%';
        }
        
        //波次号
        if (isset($filter['update_status']) && $filter['update_status'] != '') {
            $sql_main .= " AND a.update_status in({$filter['update_status']}) ";
        }
        
        //库存获取时间
        if (!empty($filter['updated_start'])) {
            $sql_main .= " AND a.updated >= :updated_start ";
            $sql_values[':updated_start'] = $filter['updated_start'] . ' 00:00:00';
        }
        if (!empty($filter['updated_end'])) {
            $sql_main .= " AND a.updated <= :updated_end ";
            $sql_values[':updated_end'] = $filter['updated_end'] . ' 23:59:59';
        }
        //库存更新时间
        if (!empty($filter['efast_update_start'])) {
            $sql_main .= " AND a.efast_update >= :efast_update_start ";
            $sql_values[':efast_update_start'] = $filter['efast_update_start'] . ' 00:00:00';
        }
        if (!empty($filter['efast_update_end'])) {
            $sql_main .= " AND a.efast_update <= :efast_update_end ";
            $sql_values[':efast_update_end'] = $filter['efast_update_end'] . ' 23:59:59';
        }

    	$select = ' a.*';
    	$sql_main .= " order by update_status desc ";
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['update_status_name'] = $row['update_status'] == 1?'已更新':'未更新';
            $row['efast_store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $row['efast_store_code']));
            $row['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $row['SPDM']));
        }
//    	filter_fk_name($data['data'], array('shop_code|shop','store_code|store', ));
    	$ret_data = $data;
    	
    	return $this->format_ret(1, $ret_data);
    }
    
     
    
    function get_inv_and_update($data){
        $fun = 'bserp_api/sku_quantity_sync';
        $params = array('id'=>$data['id'],'erp_config_id' =>$data['erp_config_id']);
        $result = load_model('sys/EfastApiModel')->request_api($fun, $params);
        if($result['resp_data']['code'] == '0'){
                $ret['status'] = '1';
                $ret['message'] = '获取ERP库存更新成功';
        }else{
                $ret['status'] = '-1';
                $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }
}