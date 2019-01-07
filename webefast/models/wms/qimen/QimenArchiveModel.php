<?php

/**
 * sync_mode = incr 增量 | all 全量 | fix 上传出错的再次上传
 */
require_model('wms/WmsBaseModel');

class QimenArchiveModel extends WmsBaseModel {

    var $sync_mode = '';
    var $sync_success = 0;
    var $goods_spec;

    function __construct($efast_store_code) {
        parent::__construct();
        $this->get_wms_cfg($efast_store_code);
    }

    function sync() {
        $this->sync_barocde();
    }

    function sync_barocde($sync_success = 1) {
        $this->sync_success = $sync_success;
        $barocde_num = $this->get_barocde_num();
        $limit = 20;
        $page = 1;
        $this->set_spec_name();
        $page_count = ceil($barocde_num / $limit) + 1;
        while ($page < $page_count) {
            $data = $this->get_barocde($page, $limit, $sync_success);
            $new_log = array();
            foreach ($data as $barcode_info) {
                $up_data = array();
                $log_data = array();
                $this->set_upload_info($barcode_info, $up_data, $log_data);
                $ret = $this->biz_req('taobao.qimen.singleitem.synchronize', $up_data);
                if ($ret['status'] < 0) {
                    
                } else {
                    $sub_log = $log_data[$barcode_info['barcode']];
                    $sub_log['api_code'] = !empty($ret['data']['itemId']) ? $ret['data']['itemId'] : '';
                    $sub_log['is_success'] = 1;
                    $new_log[] = $sub_log;
                }
            }
            if (!empty($new_log)) {
                $this->insert_multi_duplicate('wms_archive', $new_log, " tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ,api_code = VALUES(api_code),sys_code = VALUES(sys_code) ");
            }
            if (count($data) < $limit) {
                break;
            }
            $page++;
        }

        $this->sync_success = $this->sync_success - 1;
        if ($this->sync_success > -1) {
            $this->sync_barocde($this->sync_success);
        }
    }

    function update_is_success($barcode_arr) {
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'code', $sql_values);
        $sql = "update wms_archive  set is_success=1  where efast_store_code = '{$this->wms_cfg['efast_store_code']}'  AND type ='goods_barcode' AND  code in({$barcode_str})  ";
        $this->db->query($sql, $sql_values);
    }

    function get_tbl_changed($type) {
        static $tbl_changed = null;
        if (empty($tbl_changed)) {
            $sql = "select tbl_changed from wms_archive where efast_store_code = :efast_store_code and type = :type order by tbl_changed desc";
            $tbl_changed = ctx()->db->getOne($sql, array(':efast_store_code' => $this->wms_cfg['efast_store_code'], ':type' => $type));
            $tbl_changed = empty($tbl_changed) ? '0000-00-00 00:00:00' : $tbl_changed;
        }
        return $tbl_changed;
    }

    function get_barocde($page = 1, $limit = 10, $sync_success = 1) {
        $sql_values = array();
        $type = 'goods_barcode';
        $join_sql = $cfg_sql = '';
        //是否按照已配置的商品进行上传
        if ($this->wms_cfg['wms_split_goods_source'] == 1) {
            $join_sql .= " INNER JOIN wms_custom_goods_sku AS wms ON b.sku=wms.sku ";
            $cfg_sql .= " AND wms.wms_config_id={$this->wms_cfg['wms_config_id']} ";
        }
        if ($sync_success == 1) {
            $tbl_changed = $this->get_tbl_changed($type);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code,wa.api_code,b.remark,wa.sys_code as old_barcode from goods_sku b ";
            $sql .= " INNER JOIN   wms_archive wa ON wa.code = b.sku" . $join_sql;
            $sql .= " WHERE 1 AND wa.efast_store_code='{$this->efast_store_code}' AND b.lastchanged>'{$tbl_changed}' and wa.type ='{$type}' AND b.barcode is not NULL AND b.barcode<>'' " . $cfg_sql;
        } else if ($sync_success == 0) {
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code,b.remark from goods_sku b {$join_sql}
                  WHERE b.sku  not in( SELECT code from wms_archive  where type=:type AND efast_store_code='{$this->efast_store_code}'  ) AND b.barcode is not NULL AND b.barcode<>'' " . $cfg_sql;
            $sql_values = array(':type' => $type);
        }

        $start = ($page - 1) * $limit;
        $sql .= " LIMIT {$start},$limit ";
        return $this->db->get_all($sql, $sql_values);
    }

    function get_barocde_num() {
        //是否按照已配置的商品获取条码数量
        $sql_values = array();
        $table = $this->wms_cfg['wms_split_goods_source'] == 1 ? "wms_custom_goods_sku" : 'goods_sku';
        $sql = "SELECT COUNT(1) FROM {$table} WHERE 1=1 ";
        if ($this->wms_cfg['wms_split_goods_source'] == 1) {
            $sql .= " AND wms_config_id=:wms_config_id";
            $sql_values['wms_config_id'] = $this->wms_cfg['wms_config_id'];
        }
        return $this->db->get_value($sql, $sql_values);
    }

    function set_upload_info($barocde_info, &$up_data, &$log_data) {
        $goods_info = $this->get_goods_info($barocde_info['goods_code']);
        $spec1_info = $this->get_spec1_info($barocde_info['spec1_code']);
        $spec2_info = $this->get_spec2_info($barocde_info['spec2_code']);
        if ($this->sync_success == 0) {
            $up_data['actionType'] = 'add';
        } else {
            $up_data['actionType'] = 'update';
        }

        if ($up_data['actionType'] == 'update' && !empty($barocde_info['api_code'])) {
            $info['itemId'] = $barocde_info['api_code'];
        }

        //更改条码需要添加
        if (isset($barocde_info['old_barcode']) && (empty($info['itemId']) || $info['itemId'] == $barocde_info['old_barcode']) && $barocde_info['old_barcode'] != $barocde_info['barcode']) {
            $up_data['actionType'] = 'add';
        }
        $kh_id = CTX()->saas->get_saas_key();

        if (in_array($kh_id, ['2349', '2295', '2380', '842', '749'])) {
            if ($this->wms_cfg['product_type'] === 'cainiao' && isset($barocde_info['old_barcode']) && $barocde_info['old_barcode'] != $barocde_info['barcode']) {
                $up_data['actionType'] = 'add';
            }
        }

        $up_data['warehouseCode'] = $this->wms_cfg['wms_store_code'];  //warehouse_code
        $up_data['ownerCode'] = $this->wms_cfg['owner_code'];

        $info['itemCode'] = $barocde_info['barcode'];
        $info['itemId'] = isset($info['itemId']) ? $info['itemId'] : '';

        $info['goodsCode'] = $barocde_info['goods_code'];
        $info['itemName'] = $goods_info['goods_name'];
        $info['shortName'] = $goods_info['goods_short_name'];
        $info['itemType'] = 'ZC';
        $info['color'] = $spec1_info['spec1_name'];
        $info['size'] = $spec2_info['spec2_name'];
        $info['skuProperty'] = $spec1_info['spec1_name'] . "," . $spec2_info['spec2_name'];

        $info['categoryId'] = $goods_info['category_code'];
        $info['categoryName'] = $goods_info['category_name'];
        $info['seasonCode'] = $goods_info['season_code'];
        $info['seasonName'] = $goods_info['season_name'];
        $info['brandCode'] = $goods_info['brand_code'];
        $info['brandName'] = $goods_info['brand_name'];

        $info['tagPrice'] = $goods_info['sell_price'];
        $info['retailPrice'] = $goods_info['sell_price'];
        $info['costPrice'] = $goods_info['cost_price'];
        $info['purchasePrice'] = $goods_info['purchase_price'];
        $info['remark'] = $barocde_info['remark'];


        $child_data = $this->get_child_barcode_by_barcode($barocde_info['barcode']);
        //需求说明子条码放首位，接着国标码，然后是商品条形码 2017.7.18 FBB
        foreach ($child_data as $val) {
            $bar_arr[] = $val['barcode'];
        }
        $kh_id = CTX()->saas->get_saas_key();
        //力宏特殊处理
        if ($kh_id != 2061) {
            if (!empty($barocde_info['gb_code'])) {
                $bar_arr[] = $barocde_info['gb_code'];
            }

            $bar_arr[] = $barocde_info['barcode'];
        }
        $info['barCode'] = implode(';', $bar_arr);


        $up_data['item'] = $info;
        $log_data[$barocde_info['barcode']] = array('api_product' => $this->wms_cfg['api_product'], 'wms_config_id' => $this->wms_cfg['wms_config_id'], 'efast_store_code' => $this->wms_cfg['efast_store_code'], 'type' => 'goods_barcode', 'code' => $barocde_info['sku'], 'sys_code' => $barocde_info['barcode'], 'tbl_changed' => $barocde_info['lastchanged'], 'is_success' => 0);
    }

    function get_child_barcode_by_barcode($barcode) {
        $sql = "select c.barcode from goods_barcode_child c
                    INNER JOIN goods_sku p ON c.sku=p.sku
                    where p.barcode='{$barcode}'";
        return $this->db->get_all($sql);
    }

    function get_goods_info($goods_code) {
        static $goods_arr = null;

        if (!isset($goods_arr[$goods_code])) {
            $sql = "select g.goods_code,g.goods_name,g.goods_short_name, g.category_code,g.category_name,g.brand_code,g.brand_name,g.season_code,g.season_name,g.purchase_price,g.sell_price,g.cost_price 
                                    from base_goods g

                                    WHERE goods_code='{$goods_code}'";
            $goods_arr[$goods_code] = $this->db->get_row($sql);
        }
        return $goods_arr[$goods_code];
    }

    function get_spec1_info($spec1_code) {
        static $spec1_arr = null;
        if (!isset($spec1_arr[$spec1_code])) {
            $spec1_arr[$spec1_code] = $this->db->get_row("select spec1_code,spec1_name from base_spec1 WHERE spec1_code='{$spec1_code}' ");
        }
        return $spec1_arr[$spec1_code];
    }

    function get_spec2_info($spec2_code) {
        static $spec2_arr = null;
        if (!isset($spec2_arr[$spec2_code])) {
            $spec2_arr[$spec2_code] = $this->db->get_row("select spec2_code,spec2_name from base_spec2 WHERE spec2_code='{$spec2_code}' ");
        }
        return $spec2_arr[$spec2_code];
    }

    function set_spec_name() {
        $this->goods_spec = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
    }

    function sync_batch($type, $skus) {
        if (strpos($this->wms_cfg['wms_address'], 'http://') === FALSE && strpos($this->wms_cfg['wms_address'], 'https://') === FALSE) {
            return $this->format_ret(-1, '', '接口地址配置有误');
        }
        $this->sync_success = $type === 'add' ? 0 : 1;

        $sku_arr = explode(',', $skus);
        $sql_values = [];
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code,wa.api_code,b.remark,wa.sys_code as old_barcode,wa.lastchanged AS wms_lastchanged FROM goods_sku b LEFT JOIN wms_archive wa ON wa.type='goods_barcode' AND wa.efast_store_code=:store_code AND b.sku=wa.`code` WHERE b.sku IN({$sku_str})";
        $sql_values[':store_code'] = $this->efast_store_code;
        $data = $this->db->get_all($sql, $sql_values);

        $bar_error = [];
        $new_log = array();
        foreach ($data as $barcode_info) {
            $up_data = array();
            $log_data = array();
            $this->set_upload_info($barcode_info, $up_data, $log_data);

            $ret = $this->biz_req('taobao.qimen.singleitem.synchronize', $up_data);
            $sub_log = $log_data[$barcode_info['barcode']];
            if ($ret['status'] < 0) {
                $bar_error[$barcode_info['barcode']] = "条码[{$barcode_info['barcode']}]，错误信息：{$ret['message']}";
                $sub_log['is_success'] = -1;
                $sub_log['msg'] = $ret['message'];
                $sub_log['api_code'] = $barcode_info['api_code'];
                $sub_log['lastchanged'] = $barcode_info['wms_lastchanged'];
            } else {
                $sub_log['api_code'] = !empty($ret['data']['itemId']) ? $ret['data']['itemId'] : '';
                $sub_log['is_success'] = 1;
                $sub_log['msg'] = '';
            }
            $new_log[] = $sub_log;
        }
        if (!empty($new_log)) {
            $this->insert_multi_duplicate('wms_archive', $new_log, " tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ,api_code = VALUES(api_code),sys_code = VALUES(sys_code),msg = VALUES(msg) ");
        }

        if (!empty($bar_error)) {
            $barcode = implode(',', array_keys($bar_error));
            $msg = implode(';', $bar_error);
            return $this->format_ret(-1, $barcode, '同步失败，' . $msg);
        }

        return $this->format_ret(1, '', '同步成功');
    }

}
