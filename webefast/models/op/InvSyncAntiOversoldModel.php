<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 库存同步策略-防超卖预警业务
 */
class InvSyncAntiOversoldModel extends TbModel {

    function __construct() {
        parent::__construct('op_inv_sync_warn_goods');
    }

    /**
     * @todo 预警商品查询
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }

        $sql_join = 'INNER JOIN op_inv_sync AS os ON wg.sync_code=os.sync_code 
                     INNER JOIN base_goods AS bg ON wg.goods_code=bg.goods_code 
                     INNER JOIN goods_sku AS gk ON wg.sku=gk.sku';
        $sql_main = "FROM {$this->table} AS wg {$sql_join} WHERE 1";
        $sql_values = array();

        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (wg.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (wg.barcode LIKE :barcode )";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }

        $select = 'wg.warn_goods_id,wg.sync_code,os.sync_name,wg.goods_code,bg.goods_name,wg.sku,os.warn_goods_sell_shop,gk.barcode,gk.spec1_name,gk.spec2_name,bg.category_name,bg.brand_name';
        $sql_main .= " ORDER BY wg.sync_code";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    
    function add_warn_goods($data){
        
    }

    /**
     * 条码预警
     * @param $data
     */
    function save_warn_sku($data) {
        //按单店铺逻辑做
        $this->delete_exp('op_inv_sync_warn_sku', array('sync_code' => $data['sync_code'], 'sku' => $data['sku']));
        $insert_data = array(
            'sync_code' => $data['sync_code'],
            'warn_sku_val' => $data['warn_sku_val'],
            'sku' => $data['sku'],
            'shop_code' => $data['shop_code'],
        );
        $update_str = "warn_sku_val = VALUES(warn_sku_val)";
        $ret = $this->insert_multi_duplicate('op_inv_sync_warn_sku', array($insert_data), $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '保存失败！');
        }
        //日志
        $barcode = oms_tb_val('goods_sku', 'barcode', array('sku' => $data['sku']));
        $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $data['shop_code']));
        $log_info = "条码:{$barcode},店铺:{$shop_name},设置预警库存{$data['warn_sku_val']}";
        $log = array('sync_code' => $data['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'goods_ratio', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
        $res = load_model('op/InvSyncLogModel')->insert($log);
        return $this->format_ret('1', '', '保存成功！');
    }


    //条码预警
    function get_warn_sku_info($sync_code, $sku, $shop_code) {
        $sql = "SELECT * FROM op_inv_sync_warn_sku WHERE sync_code=:sync_code AND sku=:sku AND shop_code=:shop_code";
        $sql_value[':sync_code'] = $sync_code;
        $sql_value[':sku'] = $sku;
        $sql_value[':shop_code'] = $shop_code;
        $ret = $this->db->get_row($sql, $sql_value);
        if (empty($ret)) {
            return $this->format_ret('-1', '', '');
        }
        return $this->format_ret('1', $ret, '');
    }


    function get_warn_info_all($sync_code, $sku, $shop_code = '') {
        $sql = "SELECT * FROM op_inv_sync_warn_sku WHERE sync_code=:sync_code AND sku=:sku ";
        $sql_value[':sync_code'] = $sync_code;
        $sql_value[':sku'] = $sku;
        if (!empty($shop_code)) {
            $sql .= " AND shop_code=:shop_code";
            $sql_value[':shop_code'] = $shop_code;
        }
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }

}
