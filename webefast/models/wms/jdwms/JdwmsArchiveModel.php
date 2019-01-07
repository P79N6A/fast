<?php

/**
 * sync_mode = incr 增量 | all 全量 | fix 上传出错的再次上传
 */
require_model('wms/WmsBaseModel');

class JdwmsArchiveModel extends WmsBaseModel {

    var $sync_mode = '';

    function __construct($efast_store_code) {
        parent::__construct();
        $this->get_wms_cfg($efast_store_code);
    }

    function sync() {
        $this->sync_barocde();
    }

    function sync_barocde($sync_success = 1) {
        $barocde_num = $this->get_barocde_num();
        $limit = 20;
        $page = 1;
        $this->set_spec_name();
        $page_count = ceil($barocde_num / $limit) + 1;
        while ($page < $page_count) {
            $new_log = array();
            $data = $this->get_barocde($page, $limit, $sync_success);
            $up_data = array();
            $log_data = array();
            foreach ($data as $barcode_info) {
                $this->set_upload_info($barcode_info, $up_data, $log_data);
                $ret = $this->biz_req('jingdong.eclp.goods.transportGoodsInfo', $up_data);
                if ($ret['status'] < 0) {
                    
                } else {
                    $sub_log = $log_data[$barcode_info['barcode']];
                    $sub_log['api_code'] = !empty($ret['data']['goodsNo']) ? $ret['data']['goodsNo'] : '';
                    $sub_log['is_success'] = 1;
                    $new_log[] = $sub_log;
                }
            }
            if (!empty($new_log)) {
                $this->insert_multi_duplicate('wms_archive', $new_log, " tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ");
            }
            if (count($data) < $limit) {
                break;
            }
            $page ++;
        }
        if ($sync_success == 1) {
            $this->sync_barocde(0);
        }
    }

    function update_is_success($barcode_arr) {
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'code', $sql_values);
        $sql = "update wms_archive  set is_success=1  where efast_store_code = '{$this->wms_cfg['efast_store_code']}'  AND type ='goods_barcode' AND  code in({$barcode_str})  ";
        $this->db->query($sql, $sql_values);
        //echo $sql;die;
    }

    function get_tbl_changed($type) {
        // static $tbl_changed = null;
        static $tbl_arr = NULL;
        if (!isset($tbl_arr[$this->wms_cfg['efast_store_code']][$type])) {
            $sql = "select tbl_changed from wms_archive where efast_store_code = :efast_store_code and type = :type order by tbl_changed desc";
            $tbl_changed = ctx()->db->getOne($sql, array(':efast_store_code' => $this->wms_cfg['efast_store_code'], ':type' => $type));
            $tbl_arr[$this->wms_cfg['efast_store_code']][$type] = empty($tbl_changed) ? '0000-00-00 00:00:00' : $tbl_changed;
        }
        return $tbl_arr[$this->wms_cfg['efast_store_code']][$type];
    }

    function get_barocde($page = 1, $limit = 10, $sync_success = 1) {
        $sql_values = array();
        $type = 'goods_barcode';
        if ($sync_success == 1) {
            $tbl_changed = $this->get_tbl_changed($type);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code  from goods_sku b ";
            $sql .= " WHERE 1 AND b.lastchanged>'{$tbl_changed}' ";
        } else {
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.lastchanged,b.gb_code from goods_sku b
                LEFT JOIN   wms_archive wa ON wa.code = b.barcode  
                WHERE 1 AND wa.efast_store_code =:efast_store_code and wa.type =:type AND  is_success=0";
            $sql_values = array(':efast_store_code' => $this->wms_cfg['efast_store_code'], ':type' => $type);
        }

        //$sql.=" AND wa.code='101012002' ";
        $start = ($page - 1) * $limit;
        $sql .= " LIMIT {$start},$limit ";
        //  echo $sql;
        return $this->db->get_all($sql, $sql_values);
    }

    function get_barocde_num() {
        return $this->db->get_value("select count(1) from goods_barcode");
    }

    function set_upload_info($barocde_info, &$up_data, &$log_data) {
        $goods_info = $this->get_goods_info($barocde_info['goods_code']);
        $spec1_info = $this->get_spec1_info($barocde_info['spec1_code']);
        $spec2_info = $this->get_spec2_info($barocde_info['spec2_code']);
        //$up_data = array('CompanyCode' => $this->wms_cfg['company']);


        $up_data['deptNo'] = $this->wms_cfg['deptNo'];
        $up_data['isvGoodsNo'] = !empty($barocde_info['barcode']) ? $barocde_info['barcode'] : '';
        $up_data['barcodes'] = !empty($barocde_info['barcode']) ? $barocde_info['barcode'] : '';
        // $up_data['thirdCategoryNo'] = !empty($goods_info['category_name']) ? $goods_info['category_name'] : '';
        $up_data['thirdCategoryNo'] = $this->wms_cfg['thirdCategoryNo'];
        $up_data['goodsName'] = !empty($goods_info['goods_name']) ? $goods_info['goods_name'] : '';
        $up_data['brandNo'] = !empty($goods_info['brand_code']) ? $goods_info['brand_code'] : '';
        $up_data['brandName'] = !empty($goods_info['brand_name']) ? $goods_info['brand_name'] : '';
        $up_data['color'] = !empty($spec1_info['spec1_name']) ? $spec1_info['spec1_name'] : '';
        $up_data['size'] = !empty($spec2_info['spec2_name']) ? $spec2_info['spec2_name'] : '';

        $up_data['goodsName'] = $up_data['brandName'] . '/' . $up_data['goodsName'] . '/' . $up_data['color'] . '/' . $up_data['size'];

        $up_data['grossWeight'] = !empty($goods_info['weight']) ? $goods_info['weight'] : '';
        //  $up_data['netWeight'] = !empty($spec2_info['spec2_name']) ? $spec2_info['spec2_name'] :'';
        $up_data['safeDays'] = 0; //保质期天数(非保质期为0)
        //$up_data['size'] = !empty($spec2_info['spec2_name']) ? $spec2_info['spec2_name'] : '';
        $log_data[$barocde_info['barcode']] = array('api_product' => $this->wms_cfg['api_product'], 'wms_config_id' => $this->wms_cfg['wms_config_id'], 'efast_store_code' => $this->wms_cfg['efast_store_code'], 'type' => 'goods_barcode', 'code' => $barocde_info['barcode'], 'sys_code' => $barocde_info['barcode'], 'tbl_changed' => $barocde_info['lastchanged'], 'is_success' => 0);
    }

    function get_child_barcode_by_barcode($barcode) {
        $sql = "select c.barcode from goods_barcode_child c
                    INNER JOIN goods_sku p ON c.sku=p.sku
                    where p.barcode='{$barcode}'";
        return $this->db->get_all($sql);
    }

    function get_goods_info($goods_code) {
        static $goods_arr;
        if (!isset($goods_arr[$goods_code])) {
            $sql = "select g.goods_code,g.goods_name,g.brand_code,g.weight,c.category_name,b.brand_name,s.season_name,y.year_name from base_goods g
                                    LEFT JOIN base_category c ON g.category_code = c.category_code
                                    LEFT JOIN base_brand b ON g.brand_code = b.brand_code
                                    LEFT JOIN base_season s ON g.season_code = s.season_code
                                    LEFT JOIN base_year y on g.year_code = y.year_code 
                                    WHERE goods_code='{$goods_code}'";
            $goods_arr[$goods_code] = $this->db->get_row($sql);
        }
        return $goods_arr[$goods_code];
    }

    function get_spec1_info($spec1_code) {
        static $spec1_arr;
        if (!isset($spec1_arr[$spec1_code])) {
            $spec1_arr[$spec1_code] = $this->db->get_row("select spec1_code,spec1_name from base_spec1 WHERE spec1_code='{$spec1_code}' ");
        }
        return $spec1_arr[$spec1_code];
    }

    function get_spec2_info($spec2_code) {
        static $spec2_arr;
        if (!isset($spec2_arr[$spec2_code])) {
            $spec2_arr[$spec2_code] = $this->db->get_row("select spec2_code,spec2_name from base_spec2 WHERE spec2_code='{$spec2_code}' ");
        }
        return $spec2_arr[$spec2_code];
    }

    function set_spec_name() {
        $this->goods_spec = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
    }

    function get_wms_storage_template() {
        static $wms_storage_template = NULL;
        if (empty($wms_storage_template)) {
            $kh_id = CTX()->saas->get_saas_key();
            if (isset($this->wms_cfg['wms_storage_template'][$kh_id])) {
                $wms_storage_template = $this->wms_cfg['wms_storage_template'][$kh_id];
            } else {
                $wms_storage_template = $this->wms_cfg['wms_storage_template'][0];
            }
        }
        return $wms_storage_template;
    }

    function get_shpper() {
        $ret = $this->biz_req('jingdong.eclp.master.queryShipper', array());
        return $ret;
    }

    function get_source() {
        $ret = $this->biz_req('jingdong.eclp.master.querySpSource', array());
        return $ret;
    }

    function sync_api_barcode($param) {
        if(strpos($this->wms_cfg['wms_address'],'http://') === FALSE && strpos($this->wms_cfg['wms_address'], 'https://') === FALSE) {
            return $this->format_ret(-1, '', '接口地址配置有误');
        }

        $barcode_arr = explode(",", $param['barcode']);
        $barcode_arr = array_map(function($val) {
            return strtolower(trim($val));
        }, $barcode_arr);
        $sql_values = array();
        $str = $this->arr_to_in_sql_value($barcode_arr, "bar", $sql_values);
        $sql = "select sku,barcode from goods_sku where barcode in ({$str}) ";
        $barcode_data = $this->db->get_all($sql, $sql_values);
        $new_barcode = array();
        $barcode_diff = array();
        foreach ($barcode_data as $val) {
            $barcode = strtolower($val['barcode']);
            $sku_data[$barcode] = $val['sku'];
            $new_barcode[] = $val['barcode'];
            $barcode_diff[] = strtolower($val['barcode']);
        }
        $barcode_diff = array_diff($barcode_arr, $barcode_diff);
        if (empty($new_barcode) || !empty($barcode_diff)) {
            $barcode_err = implode(',', $barcode_diff);
            return $this->format_ret(-1, ['error_barcode' => $barcode_err], '以下输入的条码在系统中未找到');
        }

        $default_row = array('api_product' => $this->wms_cfg['api_product'], 'wms_config_id' => $this->wms_cfg['wms_config_id'], 'efast_store_code' => $this->wms_cfg['efast_store_code'], 'type' => 'goods_barcode', 'is_success' => 1);

        $api_parram = array('queryType' => 1, 'pageSize' => 50, 'deptNo' => $this->wms_cfg['deptNo']);
        $api_parram['barcodes'] = implode(",", $new_barcode);

        $ret = $this->biz_req('jingdong.eclp.goods.queryGoodsInfo', $api_parram);

        if ($ret['status'] < 1) {
            return $ret;
        }

        $data = array();
        $tbl_changed = date('Y-m-d H:i:s');
        foreach ($ret['data']['goodsInfoList'] as $val) {
            $default_row['tbl_changed'] = $tbl_changed;
            $barcode = strtolower($val['barcodes']);
            $default_row['code'] = $sku_data[$barcode];
            $default_row['sys_code'] = $val['barcodes'];
            $default_row['api_code'] = $val['goodsNo'];

            $data[] = $default_row;
        }
        if (!empty($data)) {
            $this->insert_multi_duplicate('wms_archive', $data, "sys_code = VALUES(sys_code), api_code = VALUES(api_code), tbl_changed = VALUES(tbl_changed),is_success = VALUES(is_success) ");
        }
        return $this->format_ret(1);
    }

}
