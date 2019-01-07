<?php

require_model('tb/TbModel');

/**
 * WMS商品档案
 * @author WMH
 */
class WmsItemModel extends TbModel {

    /**
     * 获取WMS商品档案
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_by_page($filter) {
        $empty_data = ['filter' => ['record_count' => 0], 'data' => []];
        if (empty($filter['wms_config_id'])) {
            return $this->format_ret(1, $empty_data);
        }
        $wms_config = load_model('sys/WmsConfigModel')->get_by_id($filter['wms_config_id']);
        $wms_config = $wms_config['data'];
        if (empty($wms_config)) {
            return $this->format_ret(1, $empty_data);
        }
        if (isset($filter['keyword_value']) && $filter['keyword_value'] != '') {
            $filter[$filter['keyword']] = trim($filter['keyword_value']);
        }
        
        $select = 'gs.`sku_id` AS id, gs.`goods_code`,gs.`sku`, gs.`barcode`, gs.`lastchanged` AS sys_lastchanged, wa.`id` AS archive_id,wa.`api_code`, wa.`is_success` AS upload_status, wa.`tbl_changed` AS last_upload_time, wa.`msg`';
        $sql_join = '';
        if ($wms_config['wms_system_code'] == 'qimen') {
            //指定商品下发参数
            $params = load_model('sys/SysParamsModel')->get_val_by_code('wms_split_goods_source');
            if ($params['wms_split_goods_source'] == 1) {
                $sql_join = 'INNER JOIN `wms_custom_goods_sku` AS cs ON cs.`wms_config_id`=:_config_id AND gs.`sku`=cs.`sku`';
            }
        }
        $sql_main = " FROM `goods_sku` AS gs {$sql_join} LEFT JOIN `wms_archive` AS wa ON wa.`wms_config_id`=:_config_id AND wa.type='goods_barcode' AND gs.`sku`=wa.`code` WHERE 1";

        $sql_values = array();
        $sql_values[':_config_id'] = (int) $filter['wms_config_id'];
//        if ($filter['upload_status'] !== '') {
//            switch ($filter['upload_status']) {
//                case 'uploading': //未上传
//                    $sql_main .= ' AND (wa.id IS NULL OR wa.is_success=0)';
//                    break;
//                case 'uploaded': //已上传
//                    $sql_main .= ' AND wa.id IS NOT NULL AND wa.is_success=1';
//                    break;
//                case 'renew': //待更新
//                    $sql_main .= ' AND wa.id IS NOT NULL AND wa.is_success=1 AND gs.lastchanged>wa.tbl_changed';
//                    break;
//                case 'uperror': //上传失败
//                    $sql_main .= ' AND wa.id IS NOT NULL AND wa.is_success=-1';
//                    break;
//                default :
//                    break;
//            }
//        }
        //商品条形码
        if (!empty($filter['barcode'])) {
            $sql_main .= ' AND gs.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }
        
        if (isset($filter['status']) && $filter['status'] !== '') {
            $wms_upload_attr_arr = explode(',', $filter['status']);
            $sql_attr_arr = array();
            foreach ($wms_upload_attr_arr as $attr) {
                if ($attr == '1') {//未上传
                    $sql_attr_arr[] = " (wa.id IS NULL OR wa.is_success=0) ";
                }
                if ($attr == '2') {
                    $sql_attr_arr[] = " (wa.id IS NOT NULL AND wa.is_success=1 AND gs.lastchanged>wa.tbl_changed) ";
                }
                if ($attr == '3') {
                    $sql_attr_arr[] = " (wa.id IS NOT NULL AND wa.is_success=1) ";
                }
                if ($attr == '4') {
                    $sql_attr_arr[] = " (wa.id IS NOT NULL AND wa.is_success=-1) ";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }
        //上传时间
                if (isset($filter['upload_time_start']) && !empty($filter['upload_time_start'])) {
                    $sql_main .= " AND gs.lastchanged >= :upload_time_start ";
                    $upload_time_start = strtotime(date("Y-m-d", strtotime($filter['upload_time_start'])));
                    if ($upload_time_start == strtotime($filter['upload_time_start'])) {
                        $sql_values[':upload_time_start'] = $filter['upload_time_start'];
                    } else {
                        $sql_values[':upload_time_start'] = $filter['upload_time_start'];
                    }
                }
                
                if (isset($filter['upload_time_end']) && !empty($filter['upload_time_end'])) {
                    $sql_main .= " AND gs.lastchanged <= :upload_time_end ";
                    $upload_time_end = strtotime(date("Y-m-d", strtotime($filter['upload_time_end'])));
                    if ($upload_time_end == strtotime($filter['upload_time_end'])) {
                        $sql_values[':upload_time_end'] = $filter['upload_time_end'];
                    } else {
                        $sql_values[':upload_time_end'] = $filter['upload_time_end'];
                    }
                }

        //商品名称转为编码查询
        if (!empty($filter['goods_name'])) {
            $sql = 'SELECT goods_code FROM base_goods WHERE goods_name LIKE :goods_name';
            $filter['goods_code'] = $this->db->get_all_col($sql, [':goods_name' => "%{$filter['goods_name']}%"]);
            if (empty($filter['goods_code'])) {
                return $this->format_ret(1, $empty_data);
            }
        }
        if (!empty($filter['goods_code'])) {
            $goods_code_arr = $filter['goods_code'];
            if (!is_array($filter['goods_code'])) {
                $goods_code_arr = [$filter['goods_code']];
            }
            $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
            $sql_main .= " AND gs.`goods_code` IN({$goods_code_str})";
        }
        //wms商品id
        if (!empty($filter['api_code'])) {
            $sql_main .= ' AND wa.`api_code`=:api_code ';
            $sql_values[':api_code'] = $filter['api_code'];
        }
        $order_by = " ORDER BY (CASE WHEN (wa.id IS NULL OR wa.is_success=0) THEN 10 WHEN (wa.id IS NOT NULL AND wa.is_success=1 AND gs.lastchanged>wa.tbl_changed) THEN 30 WHEN (wa.id IS NOT NULL AND wa.is_success=1) THEN 20 WHEN (wa.id IS NOT NULL AND wa.is_success=-1) THEN 40 ELSE 10 END) ASC,gs.lastchanged DESC ";
        $sql_main .= $order_by;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $empty_data);
        }
        filter_fk_name($data['data'], array('goods_code|goods_code'));
        foreach ($data['data'] as &$row) {
            $row['wms_config_id'] = $wms_config['wms_config_id'];
            $row['wms_config_name'] = $wms_config['wms_config_name'];
            $row['wms_system_code'] = $wms_config['wms_system_code'];
            $row['goods_name'] = $row['goods_code_name'];
            unset($row['goods_code_code'], $row['goods_code_name']);
            //上传状态
            if (empty($row['upload_status'])) {
                $row['upload_status'] = 0;
                $row['upload_status_txt'] = '未上传';
            } else if ($row['upload_status'] == -1) {
                $row['upload_status_txt'] = '上传失败';
            } else if (empty($row['archive_id']) && $row['sys_lastchanged'] > $row['last_upload_time']) {

            } else if ($row['upload_status'] == 1) {
                $row['upload_status_txt'] = '已上传';
            } else if (empty($row['archive_id']) && $row['sys_lastchanged'] > $row['last_upload_time']) {
                $row['upload_status'] = 2;
                $row['upload_status_txt'] = '待更新';
            }
        }
        return $this->format_ret(1, $data);
    }
    
  /**
   * 获取上传失败的总数量
   * @param type $filter
   * @return type
   */

    function get_count_by_status($filter){
        $empty_data = ['filter' => ['record_count' => 0], 'data' => []];
        if (empty($filter['wms_config_id'])) {
            return $this->format_ret(1, $empty_data);
        }
        $wms_config = load_model('sys/WmsConfigModel')->get_by_id($filter['wms_config_id']);
        $wms_config = $wms_config['data'];
        if (empty($wms_config)) {
            return $this->format_ret(1, $empty_data);
        }
        if (isset($filter['keyword_value']) && $filter['keyword_value'] != '') {
            $filter[$filter['keyword']] = trim($filter['keyword_value']);
        }
        
        $select = ' count(*) as num ';
        $sql_join = '';
        if ($wms_config['wms_system_code'] == 'qimen') {
            //指定商品下发参数
            $params = load_model('sys/SysParamsModel')->get_val_by_code('wms_split_goods_source');
            if ($params['wms_split_goods_source'] == 1) {
                $sql_join = 'INNER JOIN `wms_custom_goods_sku` AS cs ON cs.`wms_config_id`=:_config_id AND gs.`sku`=cs.`sku`';
            }
        }
        $sql_main = " FROM `goods_sku` AS gs {$sql_join} LEFT JOIN `wms_archive` AS wa ON wa.`wms_config_id`=:_config_id AND wa.type='goods_barcode' AND gs.`sku`=wa.`code` WHERE 1";

        $sql_values = array();
        $sql_values[':_config_id'] = (int) $filter['wms_config_id'];
        $sql_main .= ' AND wa.id IS NOT NULL AND wa.is_success=-1';
        //商品条形码
        if (!empty($filter['barcode'])) {
            $sql_main .= ' AND gs.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }
        //上传时间
                if (isset($filter['upload_time_start']) && !empty($filter['upload_time_start'])) {
                    $sql_main .= " AND gs.lastchanged >= :upload_time_start ";
                    $upload_time_start = strtotime(date("Y-m-d", strtotime($filter['upload_time_start'])));
                    if ($upload_time_start == strtotime($filter['upload_time_start'])) {
                        $sql_values[':upload_time_start'] = $filter['upload_time_start'];
                    } else {
                        $sql_values[':upload_time_start'] = $filter['upload_time_start'];
                    }
                }
                
                if (isset($filter['upload_time_end']) && !empty($filter['upload_time_end'])) {
                    $sql_main .= " AND gs.lastchanged <= :upload_time_end ";
                    $upload_time_end = strtotime(date("Y-m-d", strtotime($filter['upload_time_end'])));
                    if ($upload_time_end == strtotime($filter['upload_time_end'])) {
                        $sql_values[':upload_time_end'] = $filter['upload_time_end'];
                    } else {
                        $sql_values[':upload_time_end'] = $filter['upload_time_end'];
                    }
                }
        //商品名称转为编码查询
        if (!empty($filter['goods_name'])) {
            $sql = 'SELECT goods_code FROM base_goods WHERE goods_name LIKE :goods_name';
            $filter['goods_code'] = $this->db->get_all_col($sql, [':goods_name' => "%{$filter['goods_name']}%"]);
            if (empty($filter['goods_code'])) {
                return $this->format_ret(1, $empty_data);
            }
        }
        if (!empty($filter['goods_code'])) {
            $goods_code_arr = $filter['goods_code'];
            if (!is_array($filter['goods_code'])) {
                $goods_code_arr = [$filter['goods_code']];
            }
            $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
            $sql_main .= " AND gs.`goods_code` IN({$goods_code_str})";
        }
        //wms商品id
        if (!empty($filter['api_code'])) {
            $sql_main .= ' AND wa.`api_code`=:api_code ';
            $sql_values[':api_code'] = $filter['api_code'];
        }        
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $empty_data);
        }
        return $this->format_ret(1, $data['data'][0]['num']);
    }
    /**
     * 根据配置ID获取失败数量
     * @param type $status
     * @param type $wms_config_id
     * @return type
     */
    function get_fail_num_by_id($status,$wms_config_id) {
        $sql = "SELECT
                        count(*)
                FROM
                        `goods_sku` AS gs
                INNER JOIN `wms_custom_goods_sku` AS cs ON cs.`wms_config_id` =:wms_config_id
                AND gs.`sku` = cs.`sku`
                LEFT JOIN `wms_archive` AS wa ON wa.`wms_config_id` =:wms_config_id
                AND wa.type = 'goods_barcode'
                AND gs.`sku` = wa.`code`
                WHERE
                        1
                AND wa.id IS NOT NULL
                AND wa.is_success =:is_success";
         $num = $this->db->get_value($sql, array('is_success' => $status,'wms_config_id' => $wms_config_id));
         return $num;
    }
}
