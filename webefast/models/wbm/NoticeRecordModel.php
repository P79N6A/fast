<?php

/**
 * 批发销货相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('stm');

class NoticeRecordModel extends TbModel {

    /**
     * @var array 打印通知单模板所需字段
     */
    public $print_fields_default = array(
        'record' => array(
            '单据编号' => 'record_code',
            '关联单号' => 'relation_code',
            '原单号' => 'init_code',
            '下单时间' => 'order_time',
            '分销商' => 'distributor_name',
            '仓库' => 'store_name',
            '业务日期' => 'record_time',
            '总数' => 'num',
            '总金额' => 'money',
            '备注' => 'remark',
            '折扣' => 'rebate',
            '完成数' => 'finish_num',
            '快递单号' => 'express_no',
            '配送方式' => 'express_name',
            '添加人' => 'is_add_person',
            '添加时间' => 'is_add_time',
            '完成人' => 'is_finish_person',
            '完成时间' => 'is_finish_time',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '联系人' => 'name',
            '联系电话' => 'tel',
            '地址' => 'address',
        ),
        'detail' => array(
            array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '规格1' => 'spec1_name',
                '规格1代码' => 'spec1_code',
                '规格2' => 'spec2_name',
                '规格2代码' => 'spec2_code',
                '单价' => 'price',
                '参考价' => 'refer_price',
                '批发价' => 'pf_price',
                '折扣' => 'rebate',
                '数量' => 'num',
                '金额' => 'money',
                '完成数' => 'finish_num',
                '商品条形码' => 'barcode',
                '商品重量' => 'weight',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '吊牌价' => 'dp_price',
                '库位' => 'shelf_code',
                '批次' => 'lof_no',
            ),
        ),
    );

    /**
     * 打印
     */
    public function print_data_default($id) {
        $r = array();
        $ret = $this->get_by_id($id);
        $r['record'] = $ret['data'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $sql = "select d.pid
                ,d.goods_code,l.num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code,l.lof_no
                from wbm_notice_record_detail d
                left JOIN base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                INNER JOIN  b2b_lof_datail l  ON l.sku = r3.sku AND d.record_code=l.order_code AND l.order_type='wbm_notice'
                where d.pid=:pid";
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.finish_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code 
                from wbm_notice_record_detail d
                left JOIN base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
        }
        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));

        // 数据替换
        $this->print_data_escape($r['record'], $r['detail']);
        $d = array('record' => array(), 'detail' => array());
        foreach ($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v;
        }
        foreach ($r['detail'] as $k1 => $v1) {
            // 键值对调
            foreach ($v1 as $k => $v) {
                $nk = array_search($k, $this->print_fields_default['detail'][0]);
                $nk = $nk === false ? $k : $nk;
                $d['detail'][$k1][$nk] = is_null($v) ? '' : $v;
            }
        }
        //更新打印状态
        $this->update_print_status($id);
        return $d;
    }

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 批发销货通知单新版打印（更改打印配件）
     */
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $ret = $this->get_by_id($id);
        $r['record'] = $ret['data'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['record_code']);
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.finish_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code 
                from wbm_notice_record_detail d
                left JOIN base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
            $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
            $sku_array = array();
            $shelf_code_arr = array();
            foreach ($r['detail'] as $key => $detail) {//合并同一sku
                $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['store_code']);
                $shelf_code_arr[] = $detail['shelf_code'];
                $detail['shelf_name'] = $this->get_shelf_name($detail['sku'], $r['record']['store_code']);
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);
                if (in_array($detail['sku'], $sku_array)) {
                    $exist_key = array_keys($sku_array, $detail['sku']);
                    $r['detail'][$exist_key[0]]['num'] += $detail['num'];
                    unset($r['detail'][$key]);
                } else {
                    $sku_array[$key] = $detail['sku'];
                }
            }
        }
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail']);
        //更新打印状态
        $this->update_print_status($id);
        return $r;
    }

    //更新打印状态
    public function update_print_status($notice_record_id) {
        $sql = "SELECT is_print_record FROM wbm_notice_record WHERE notice_record_id = :notice_record_id ";
        $data = $this->db->get_row($sql, array(':notice_record_id' => $notice_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '批发销货通知单不存在');
        }
        //添加日志
        $this->add_print_log($notice_record_id);
        //更新状态
        $this->update_exp('wbm_notice_record', array('is_print_record' => 1), array('notice_record_id' => $notice_record_id));
    }

    //打印日志记录
    public function add_print_log($notice_record_id) {
        //日志
        $sql = "SELECT is_sure,is_execute,is_finish,is_stop FROM wbm_notice_record WHERE notice_record_id = :notice_record_id  ";
        $data = $this->db->get_row($sql, array(':notice_record_id' => $notice_record_id));
        $sure_status = $data['is_sure'] == 1 ? '确认' : '未确认';
        if ($data['is_execute'] == 0 && $data['is_stop'] == 0) {
            $finish_status = '未出库';
        } else if ($data['is_execute'] == 1 && $data['is_stop'] == 0) {
            $finish_status = $data['is_finish'] == 1 ? '已完成' : '未完成';
        } else if ($data['is_stop'] == 1) {
            $finish_status = '终止';
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '打印', 'module' => "wbm_notice_record", 'pid' => $notice_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * 替换打印字段
     */
    public function print_data_escape(&$record, &$detail) {
        $record['distributor_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $record['distributor_code']));
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $record['express_code']));
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        $record['finish_num'] = isset($record['finish_num']) ? $record['finish_num'] : 0;
        $record['num'] = 0;
        $i = 1;
        foreach ($detail as $k => &$v) {
            $v['pf_price'] = $v['price'] * $v['rebate'];
            $v['money'] = $v['price'] * $v['rebate'] * $v['num'];
            $v['shelf_code'] = $this->get_shelf_code($v['sku'], $record['store_code']);
            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $v['brand_code']));
            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $v['season_code']));
            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $v['year']));
            $v['barcode'] = oms_tb_val('goods_sku', 'barcode', array('barcode' => $v['barcode']));
            $record['num'] += $v['num'];
        }
//        排序

        $detail = array_orderby($detail, 'shelf_code', SORT_ASC, SORT_STRING, 'goods_code', SORT_ASC, SORT_STRING, 'barcode', SORT_ASC, SORT_STRING);

        foreach ($detail as $k => &$v) {
            $v['sort_num'] = $i;
            $i++;
        }
    }

    //获取打印状态
    public function check_is_print_record($notice_record_id) {
        $sql = "SELECT is_print_record FROM wbm_notice_record WHERE notice_record_id = :notice_record_id ";
        $data = $this->db->get_row($sql, array(':notice_record_id' => $notice_record_id));
        if ($data['is_print_record']) {
            return $this->format_ret(-1, '', '重复打印批发销货通知单，是否继续打印？');
        } else {
            return $this->format_ret(1, '', '');
        }
    }

    /**
     * 读取库位代码
     * @param $sku
     * @param $store_code
     * @return string
     */
    public function get_shelf_code($sku, $store_code) {
        // and b.lof_no = a.batch_number
        $sql = "select a.shelf_code from goods_shelf a
                where a.store_code = :store_code and a.sku = :sku";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    function get_table() {
        return 'wbm_notice_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        if ((isset($filter['code_name']) && $filter['code_name'] != '' ) || (isset($filter['barcord']) && $filter['barcord'] != '') || (isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'notice_record_list_detail')) {
            $sql_join .= " LEFT JOIN wbm_notice_record_detail r2 on rl.record_code = r2.record_code"
                    . " INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code";
            if (isset($filter['barcord']) && $filter['barcord'] != '') {
                $sql_join .= " INNER JOIN goods_sku r4 on r4.sku = r2.sku";
            }
        }
        $sql_main = "FROM {$this->table} rl {$sql_join} WHERE 1";

        $sql_values = array();

        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('rl.distributor_code', $filter_custom_code);
        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //经销订货单编号
        if (isset($filter['jx_code']) && $filter['jx_code'] != '') {
            $sql_main .= " AND (rl.jx_code LIKE :jx_code )";
            $sql_values[':jx_code'] = '%' . $filter['jx_code'] . '%';
        }
        //是否生成销货单
        if (isset($filter['is_execute']) && $filter['is_execute'] != '') {
            $sql_main .= " AND (rl.is_execute = :is_execute )";
            $sql_values[':is_execute'] = $filter['is_execute'];
        }
        //店铺
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ({$str}) ";
        }
        // 单据状态
        if (isset($filter['is_stop']) && $filter['is_stop'] != '') {
            $arr = explode(',', $filter['is_stop']);
            $str = $this->arr_to_in_sql_value($arr, 'is_stop', $sql_values);
            $sql_main .= " AND rl.is_stop in ({$str}) ";
        }
        //类型
        if (isset($filter['record_type_code']) && $filter['record_type_code'] != '') {
            $arr = explode(',', $filter['record_type_code']);
            $str = $this->arr_to_in_sql_value($arr, 'record_type_code', $sql_values);
            $sql_main .= " AND rl.record_type_code in ( " . $str . " ) ";
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . " 00:00:00";
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . " 23:59:59";
        }
        //下单日期
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'] . " 00:00:00";
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'] . " 23:59:59";
        }
        if (isset($filter['is_relation']) && $filter['is_relation'] != '') {
            $sql_main .= " AND (rl.is_relation = :is_relation )";
            $sql_values[':is_relation'] = $filter['is_relation'];
        }
        //是否完成
        if (isset($filter['is_finish']) && $filter['is_finish'] != '') {
            $sql_main .= " AND (rl.is_finish = :is_finish )";
            $sql_values[':is_finish'] = $filter['is_finish'];
        }
        //是否确认
        if (isset($filter['is_sure']) && $filter['is_sure'] != '') {
            $sql_main .= " AND (rl.is_sure = :is_sure )";
            $sql_values[':is_sure'] = $filter['is_sure'];
        }

        //商品
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        //商品条形码
        if (isset($filter['barcord']) && $filter['barcord'] != '') {
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcord'] . '%';
        }
        //是否有差异单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (rl.num != rl.finish_num )";
            } else {
                $sql_main .= " AND (rl.num = rl.finish_num )";
            }
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND rl.remark LIKE :remark ";
            $sql_values[':remark'] = "%{$filter['remark']}%";
        }
        //导出明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'notice_record_list_detail') {
            $sql_main .= " order by record_time desc";
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }

        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc, record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        foreach ($data['data'] as $key => $value) {
            //$data['data'][$key]['money'] = round($value['money'], 3);
            $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code', $value['record_type_code'], 'record_type_name');
            $data['data'][$key]['type_name'] = $data_name['data']['record_type_name'];
            $data['data'][$key]['diff_num'] = $value['num'] - $value['finish_num'];
            $wms_system_code = load_model('sys/ShopStoreModel')->is_wms_store($value['store_code']);
            if ($wms_system_code !== FALSE) {
                $data['data'][$key]['is_wms'] = '1';
            } else {
                $data['data'][$key]['is_wms'] = '0';
            }

            //是否为jit绑定的通知单
            $sql = "select count(1) from api_weipinhuijit_store_out_record as api, wbm_store_out_record as wbm where api.store_out_record_no = wbm.record_code and api.notice_record_no = '{$value['record_code']}' ";
            $count = $this->db->getOne($sql);
            if ($count > 0) {
                $data['data'][$key]['jit_relation'] = '1';
            } else {
                $data['data'][$key]['jit_relation'] = '0';
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'distributor_code|custom', 'distributor_code|custom', 'record_type_code|record_type'));
        //dump($data,1);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.record_code,rl.order_time,rl.name,rl.tel,rl.address,rl.distributor_code,rl.relation_code,rl.rebate,rl.store_code,rl.record_time,r2.sku,r2.price,r2.finish_num,r2.num,r3.goods_code,rl.remark";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        $store_code = array();
        $sku = array();
        foreach ($ret_data['data'] as $key => $value) {
            //查询分销商
            $arr = load_model('base/StoreModel')->table_get_by_field('base_custom', 'custom_code', $value['distributor_code'], 'custom_name');
            $ret_data['data'][$key]['custom_name'] = isset($arr['data']['custom_name']) ? $arr['data']['custom_name'] : '';
            //查询仓库名称
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $ret_data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2/商品名称/条形码/吊牌价
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            //吊牌价、库位
            $ret_data['data'][$key]['sell_price'] = $sku_info['sell_price'];
            $store_code[] = $value['store_code'];
            $sku[] = $value['sku'];
            //计算批发单价
            $ret_data['data'][$key]['price1'] = $value['price'] * $value['rebate'];
            $ret_data['data'][$key]['price1'] = round($ret_data['data'][$key]['price1'], 3);
            //计算金额
            $ret_data['data'][$key]['money'] = $ret_data['data'][$key]['price1'] * $value['num'];
            $ret_data['data'][$key]['money'] = round($ret_data['data'][$key]['money'], 3);
        }
        $ret = load_model('prm/GoodsShelfModel')->get_shelf_name(array_unique($store_code), array_unique($sku));
        $shelf_arr = $ret['data'];
        foreach ($ret_data['data'] as $key => &$val) {
            $ret_data['data'][$key]['shelf_name'] = isset($shelf_arr[$val['store_code'] . ',' . $val['sku']]['shelf_name']) ? $shelf_arr[$val['store_code'] . ',' . $val['sku']]['shelf_name'] : '';
            //获取扩展属性
            if ($property_power) {
                $goods_property = load_model('prm/GoodsModel')->get_export_property($val['goods_code']);
                $val = $goods_property != -1 && is_array($goods_property) ? array_merge($val, $goods_property) : $val;
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('notice_record_id' => $id));
        $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code', $data['data']['return_type_code'], 'record_type_name');
        $data['data']['record_type_name'] = $data_name['data']['record_type_name'];
        /*
          $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier','supplier_code', $data['data']['supplier_code'], 'supplier_name');
          $data['data']['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
          $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type','record_type_code', $data['data']['record_type_code'], 'record_type_name');
          $data['data']['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
          $arr = load_model('base/StoreModel')->table_get_by_field('sys_user','user_code', $data['data']['user_code'], 'user_name');
          $data['data']['user_name'] = isset($arr['data']['user_name']) ? $arr['data']['user_name'] : '';
          $arr = load_model('base/StoreModel')->table_get_by_field('base_store','store_code', $data['data']['store_code'], 'store_name');
          $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
         */
        return $data;
    }

    function get_by_code($notice_code) {
        $data = $this->get_row(array('record_code' => $notice_code));
        return $data;
    }

    function check_status($out_notice_record) {
        $record_detail = load_model('wbm/NoticeRecordDetailModel')->is_exists($out_notice_record['record_code'], 'record_code');
        if (empty($out_notice_record) || empty($record_detail)) {
            return $this->format_ret(-1, '', '销货通知单信息不存在！');
        }
        if ($out_notice_record['is_sure'] == 0) {
            return $this->format_ret(-1, '', '未确认销货通知单不能生成退单！');
        }
        if ($out_notice_record['is_finish'] == 1) {
            return $this->format_ret(-1, '', '已完成销货通知单不能生成销货单！');
        }
        if ($out_notice_record['is_stop'] == 1) {
            return $this->format_ret(-1, '', '已终止的销货通知单不能生成销货单！');
        }
        return $this->format_ret(1, $record_detail);
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

    /*
     * 删除记录
     * */

    function delete($notice_record_id, $is_jx = 'no_jx_unsettlement') {
        $sql = "select * from {$this->table} where notice_record_id = :notice_record_id";
        $data = $this->db->get_row($sql, array(":notice_record_id" => $notice_record_id));
        if ($data['is_sure'] == 1 && $is_jx == 'no_jx_unsettlement') {//不是经销订单取消结算，校验是否确认
            return $this->format_ret('-1', array(), '单据已经确认，不能删除！');
        }
        $ret = parent::delete(array('notice_record_id' => $notice_record_id));
        $this->db->create_mapper('wbm_notice_record_detail')->delete(array('pid' => $notice_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $notice_record_id, 'order_type' => 'wbm_notice'));
        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret('-1', '', $status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
//        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
        if ($stock_adjus['province'] == null) {
            $stock_adjus['province'] = '';
        }
        if ($stock_adjus['city'] == null) {
            $stock_adjus['city'] = '';
        }
        if ($stock_adjus['district'] == null) {
            $stock_adjus['district'] = '';
        }
        if ($stock_adjus['street'] == null) {
            $stock_adjus['street'] = '';
        }
        return parent::insert($stock_adjus);
    }

    public function is_exists($value, $field_name = 'record_code') {

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['record_code']) || !valid_input($data['record_code'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    /**
     * 新增一条库存调整单记录
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-10-23
     * @param array $ary_main 主单据数组
     * @return array 返回新增结果
     */
    public function add_action($ary_main) {
        //校验参数
        if (!isset($ary_main['store_code']) || !valid_input($ary_main['store_code'], 'required')) {
            return RECORD_ERROR_STORE_CODE;
        }
        //插入主单据
        //生成调整单号
        if (!isset($ary_main['record_code']) && empty($ary_main['record_code'])) {
            $ary_main['record_code'] = $this->create_fast_bill_sn();
        }
        $ary_main['is_add_time'] = date('Y-m-d H:i:s');
        $ret = $this->insert($ary_main);
        //返回结果
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select notice_record_id from {$this->table} order by notice_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['notice_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "PFTZ" . date("Ymd") . add_zero($djh);
        return $jdh;
    }

    /**
     * 编辑一条采购入库单记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-12
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit_action($data, $where) {
        if (isset($data['remark'])) {
            $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        }
        if (!isset($where['notice_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_ID_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', '', 'RECORD_ERROR');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }
        //修改批次相应仓库
        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    function update_check_record_code($active, $field, $record_code, $type = '') {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $record = $this->get_row(array('record_code' => $record_code));
        $details = load_model('wbm/NoticeRecordDetailModel')->get_all(array('record_code' => $record_code));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($record['data']['notice_record_id'], 'wbm_notice');
        //释放库存
        $this->begin_trans();
        if ($type != 'store_out_record') {
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);

            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
        }
        $ret = parent:: update(array($field => $active), array('record_code' => $record_code));
        $this->commit(); //事务提交
        return $ret;
    }

    //更新字段
    function update_check($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $details = load_model('wbm/NoticeRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        $ret = parent:: update(array($field => $active), array('notice_record_id' => $id));
        return $ret;
    }

    //
    function out_relation($id) {
        $record = $this->get_row(array('notice_record_id' => $id));
        $record_code = $record['data']['record_code'];
        $sql = " select count(*) as cnt from wbm_store_out_record where relation_code = :record_code AND is_store_out = '0' ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        //print_r($data);
        if (isset($data[0]['cnt']) && $data[0]['cnt'] > 0) {
            return $this->format_ret('-1', '', '存在未出库的批发销货单，是否继续');
        }
        return $this->format_ret('1');
    }

    //终止
    function update_stop($active, $field, $id, $check_priv = 1) {
        //#############权限
        if ($check_priv == 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_stop')) {
                return array(
                    'status' => '-1',
                    'data' => '',
                    'message' => '无权访问'
                );
            }
        }

        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $record = $this->get_row(array('notice_record_id' => $id));
        if ($record['data']['is_sure'] == '0' || $record['data']['is_finish'] == '1' || $record['data']['is_stop'] == '1') {
            return $this->format_ret('-1', '', '未确认或者已终止或已完成不允许终止');
        }
        $record_code = $record['data']['record_code'];
        $details = load_model('wbm/NoticeRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        $sql = " select count(*) as cnt from wbm_store_out_record where relation_code = :record_code AND is_store_out = '0' ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        //print_r($data);
        if (isset($data[0]['cnt']) && $data[0]['cnt'] > 0) {
            return $this->format_ret('-1', '', '存在未出库的批发销货单，不允许终止');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_notice');
        //释放锁定库存
        require_model('prm/InvOpModel');
        $invobj = new InvOpModel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);
        $this->begin_trans();
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $ret = parent:: update(array($field => $active), array('notice_record_id' => $id));


        //是否为jit绑定的通知单
        $sql_check = "select pick_no from api_weipinhuijit_store_out_record  where notice_record_no=:notice_record_no ";
        $pick_no_all = $this->db->get_all($sql_check, array(':notice_record_no' => $record_code));
        if (!empty($pick_no_all)) {
            $pick_no_arr = array_column($pick_no_all, 'pick_no');

            $pick_no_str = "'" . implode("','", $pick_no_arr) . "'";
            $sql_detail = "select pick_no,barcode,stock-delivery_stock as num from api_weipinhuijit_pick_goods  where pick_no in ({$pick_no_str}) AND stock>delivery_stock ";
            $barcode_data = $this->db->get_all($sql_detail);
            foreach ($barcode_data as $val_b) {
                $sql_up = "update api_weipinhuijit_order_detail set amount='{$val_b['num']}', status=1  where  barcode=:barcode AND pick_no=:pick_no ";
                $this->db->query($sql_up, array(':barcode' => $val_b['barcode'], ':pick_no' => $val_b['pick_no']));
            }
        }



        if (isset($record['data']['jx_code']) && !empty($record['data']['jx_code'])) { // 绑定经销订单，将经销订单的资金明细退款，用通知单已完成数生成资金明细
            $jx_data = load_model('fx/PurchaseRecordModel')->get_by_code($record['data']['jx_code'], 'purchaser_record_id,custom_code,express_money,sum_money');
            $record_detail = load_model('wbm/NoticeRecordDetailModel')->get_by_record_code($record['data']['record_code']);
            $is_finish = true;
            //计算商品总金额
            $money = array_map(function($val) {
                return $val['finish_num'] * $val['price'];
            }, $record_detail['data']);
            $sum_money = array_sum($money);

            foreach ($record_detail['data'] as $val) {
                if ($val['num'] != $val['finish_num']) {
                    $is_finish = FALSE;
                    break;
                }
            }
            if ($is_finish == FALSE) { //完成数与通知数不相等，将经销订单的资金明细退款，用通知单已完成数生成资金明细
                $jx_data['record_code'] = $record['data']['jx_code'];
                $ret1 = load_model('fx/PurchaseRecordModel')->js_income_pay($jx_data, 'notice_record_stop_refund');
                if ($ret1['status'] < 0) {
                    $this->rollback(); //事务回滚
                    return $this->format_ret(-1, '', '生成资金账户退款失败');
                }
                if ($sum_money != 0) { //总金额为零不生成资金明细
                    $jx_data['sum_money'] = $sum_money;
                    $ret1 = load_model('fx/PurchaseRecordModel')->js_income_pay($jx_data, 'notice_record_stop_payment');
                    if ($ret1['status'] < 0) {
                        $this->rollback(); //事务回滚
                        return $this->format_ret(-1, '', '订单重新付款失败');
                    }
                    $data = array('is_deliver' => 2); //部分入库
                } else {
                    $data = array('is_deliver' => 1); //已入库
                }
                //修改经销采购单入库状态
                $ret1 = $this->update_exp('fx_purchaser_record', $data, array('record_code' => $jx_data['record_code']));
                if ($ret1['status'] < 0) {
                    $this->rollback(); //事务回滚
                    return $this->format_ret(-1, '', '修改经销订单出库状态失败');
                }
                //日志
                $finish_status = $data['is_deliver'] == 1 ? '出库' : '部分出库';
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => $finish_status, 'action_name' => "终止", 'module' => "fx_purchase_record", 'pid' => $jx_data['purchaser_record_id'], 'action_note' => '批发通知单终止导致经销订单' . $finish_status);
                load_model('pur/PurStmLogModel')->insert($log);
            }
        }



        $this->commit(); //事务提交
        return $ret;
    }

    //确认/取消确认，锁定库存/释放锁定 针对移出仓
    function update_sure($active, $field, $id) {
        //#############权限

        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_sure')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }


        $record = $this->get_row(array('notice_record_id' => $id));
        if ($active == 0) {
            if ($record['data']['is_execute'] == '1' || $record['data']['is_finish'] == '1' || $record['data']['is_stop'] == '1') {
                return $this->format_ret('-1', '', '已经生成销货单或者已终止或已完成不允许取消确认');
            }
            if (isset($record['data']['jx_code']) && !empty($record['data']['jx_code'])) {
                return $this->format_ret('-1', '', '经销订单生成的通知单不允许取消确认');
            }
        }
        $details = load_model('wbm/NoticeRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_notice');
        //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id,'shift');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }

        $ret_store = load_model('base/StoreModel')->get_by_code($record['data']['store_code']);

        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        if ($allow_negative_inv == 0 && $active == 1) {
            $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            $ret = $this->count_short_stock($ret_lof_details['data'], $lof_status['lof_status']);
            if (!empty($ret)) {
                $file_name = $this->create_import_fail_files($ret, 'check_goods_inv_lof');
//                $msg .= "确认失败，失败信息<a target = \"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name, array('export_name' => 'error'));
                $msg .= "确认失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
                return $this->format_ret('-1', '', $msg);
            }
        }

        //库存操作锁库存
        require_model('prm/InvOpModel');
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        //todo：需要提前解决批次切换问题
        $this->begin_trans();
        if ($active == 1) {
            $invobj = new InvOpModel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code'], 1, $ret_lof_details['data']);

            //无开启批次，强制锁定库存
            if ($lof_manage['lof_status'] == 0) {
                $invobj->lock_allow_negative_inv(1);
            }

            $ret = $invobj->adjust();

            if ($ret['status'] < 0) {//锁定批次库存不足
                $this->rollback();
                return $ret;
//                $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
//                if (!empty($invobj->check_data['adjust_record_info']) && $lof_manage['lof_status'] == 0) {//关闭掉批次情况
//                    $this->rollback();
//                    $ret = $invobj->adjust_lock_record($invobj->check_data['adjust_record_info']); //调整锁定批次
//                    if ($ret['status'] != 1) {
//                        return $ret;
//                    }
//                    $this->begin_trans();
//                    $ret = $invobj->adjust(); //重新提交
//                } else {
//                    $this->rollback();
//                    return $ret;
//                }
            }

            if ($ret['status'] > 0 && $field == 'is_sure') {
                $ret = load_model('wms/WmsEntryModel')->add($record['data']['record_code'], 'wbm_notice', $record['data']['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                }
            }
        }
        if ($active == 0) {
            $invobj = new InvOpModel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);
            //$this->begin_trans();
            $ret = $invobj->adjust();

            if ($ret['status'] > 0 && $field == 'is_sure') {
                $ret = load_model('wms/WmsEntryModel')->cancel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                } else {
                    $ret['status'] = 1;
                }
            }
        }


        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        $ret = parent:: update(array($field => $active), array('notice_record_id' => $id));
        //$ret= parent:: update(array('is_store_out' => 1), array('return_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交
        return $ret;
    }

    //判断完成
    function update_finish($record_code) {
        $sql = " select count(*) as cnt  from wbm_notice_record_detail where  record_code = :record_code AND   finish_num >= num  ";
        $arr = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $arr);
        $sql = "select count(*) as cnt  from wbm_notice_record_detail where  record_code = :record_code  ";
        $data1 = $this->db->get_all($sql, $arr);
        if (isset($data[0]['cnt']) && isset($data1[0]['cnt']) && ( $data[0]['cnt'] == $data1[0]['cnt'] )) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '';

            return $ret;
        } else {
            $ret['status'] = '0';
            $ret['data'] = '';
            $ret['message'] = '有未完成明细';

            return $ret;
        }
    }

    //计算缺库存数
    function count_short_stock($ret, $loft_status) {
        $err_arr = array();
        foreach ($ret as $value) {
            $store_code = $value['store_code'];
            $num = $value['init_num'];
            $sku = $value['sku'];
            $sql2 = "select barcode from goods_sku where sku = :sku ";
            $sql_val[':sku'] = $sku;
            $data2 = $this->db->get_row($sql2, $sql_val);
            $barcode = $data2['barcode'];
            if ($loft_status == 1) {
                $lof_no = $value['lof_no'];
                $sql4 = "select stock_num,lock_num from goods_inv_lof where sku = :sku and store_code = :store_code and lof_no = :lof_no ";
                $sql_val3[':sku'] = $sku;
                $sql_val3[':store_code'] = $store_code;
                $sql_val3[':lof_no'] = $lof_no;
                $data4 = $this->db->get_row($sql4, $sql_val3);
                $can_use_inv = $data4['stock_num'] - $data4['lock_num'];
                $short_inv = $num >= $can_use_inv ? $num - $can_use_inv : 0;
                if ($short_inv > 0) {
                    $err_arr[$barcode] = $short_inv;
                }
            } else {
                $sql3 = "select stock_num,lock_num from goods_inv where sku = :sku and store_code = :store_code ";
                $sql_val4[':sku'] = $sku;
                $sql_val4[':store_code'] = $store_code;
                $data3 = $this->db->get_row($sql3, $sql_val4);
                $can_use_inv = $data3['stock_num'] - $data3['lock_num'];
                $short_inv = $num >= $can_use_inv ? $num - $can_use_inv : 0;
                if ($short_inv > 0) {
                    $err_arr[$barcode] = $short_inv;
                }
            }
        }
        return $err_arr;
    }

    function check_goods_inv_lof($ret_lof_details) {
        $short_error = '';
        foreach ($ret_lof_details['data'] as $key => $detail) {
            $sku = $detail['sku'];
            $num = $detail['num'];
            $sql = "select stock_num,lock_num,barcode from goods_inv_lof LEFT JOIN goods_sku ON goods_inv_lof.sku = goods_sku.sku where
    				goods_inv_lof.sku = '{$detail['sku']}'
    				and store_code = '{$detail['store_code']}'
    				and lof_no = '{$detail['lof_no']}'";
            $inv_lof = $this->db->get_row($sql);
            if (empty($inv_lof)) {
                $sql = "SELECT barcode FROM goods_sku WHERE sku = '{$sku}'";
                $inv_lof = $this->db->get_row($sql);
            }
            $allow_inv_num = $num - ($inv_lof['stock_num'] - $inv_lof['lock_num']);
            if ($allow_inv_num > 0) {
                $short_error .= '商品条形码：' . $inv_lof['barcode'] . '库存不足，缺货数为：' . $allow_inv_num . ';';
            }
        }
        return $short_error;
    }

    function create_import_fail_files($msg, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $file_str .= "库存不足的商品条形码：{$key}; 缺货数量:{$val}\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    private function get_print_info_with_lof_no($record_code) {
        $sql = "select  rl.*, rr.trade_price as price from b2b_lof_datail rl "
                . "left join base_goods rr on rl.goods_code = rr.goods_code "
                . " where rl.order_code = :order_code and rl.order_type = 'wbm_notice'";
        $lof_details = $this->db->get_all($sql, array(":order_code" => $record_code));
        if (empty($lof_details)) {
            return array();
        }

        foreach ($lof_details as $key => $detail) {
            $lof_details[$key]['shelf_code'] = $this->get_shelf_code_lof($detail['sku'], $detail['lof_no'], $detail['store_code']);
        }
        return $lof_details;
    }

    private function get_shelf_code_lof($sku, $lof_no, $store_code) {
        $sql = "select shelf_code from goods_shelf where store_code = :store_code and sku = :sku and batch_number =:batch_number";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku, ':batch_number' => $lof_no));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    function create_pftz($record) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '查询不到单据信息');
        }
        $remark = isset($record['remark']) && !empty($record['remark']) ? $record['remark'] . '经销采购单号：' . $record['record_code'] : '经销采购单号：' . $record['record_code'];
        $params = array();
        $params['record_code'] = $this->create_fast_bill_sn();
        $params['distributor_code'] = $record['custom_code'];
        $params['order_time'] = $record['order_time'];
        $params['record_time'] = $record['record_time'];
        $params['store_code'] = $record['store_code'];
        $params['rebate'] = 1;
        $params['relation_code'] = $record['record_code'];
        $params['num'] = $record['num'];
        $params['finish_num'] = $record['finish_num'];
        $params['sum_money'] = $record['money'];
        $params['is_add_time'] = date("Y-m-d H:i:s");
        $params['remark'] = $remark;
        $params['express_code'] = $record['express_code'];
        $params['express_no'] = $record['express_no'];
        $params['jx_code'] = $record['record_code'];
        $params['address'] = $record['address'];
        $params['tel'] = $record['mobile'];
        $params['name'] = $record['contact_person'];
        $params['country'] = $record['country'];
        $params['province'] = $record['province'];
        $params['city'] = $record['city'];
        $params['destrict'] = $record['destrict'];
        $params['is_settlement'] = $record['is_settlement'];

        $this->begin_trans();
        try {
            $result = $this->insert($params);
            if ($result['status'] < 0) {
                $this->rollback();
                return $result;
            }
            $id = $result['data'];
            $ret = $this->create_pftz_detail($record, $id);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            //批发通知单自动确认
            $ret1 = $this->update_sure(1, 'is_sure', $id);
            if ($ret1['status'] < 0) {
                $this->rollback();
                return $ret1;
            }
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '创建', 'module' => "wbm_notice_record", 'pid' => $result['data'], 'action_note' => '由经销采购订单' . $record['record_code'] . '生成');
            load_model('pur/PurStmLogModel')->insert($log);

            $log2 = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '确认', 'module' => "wbm_notice_record", 'pid' => $id);
            load_model('pur/PurStmLogModel')->insert($log2);
            $return_arr = array('id' => $result['data'], 'record_code' => $params['record_code']);

            $this->commit();
            return $this->format_ret(1, $return_arr);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function create_pftz_detail($record, $id) {
        $details = $this->db->get_all("select * from fx_purchaser_record_detail where record_code = :record_code", array(":record_code" => $record['record_code']));
        if (empty($details)) {
            return $this->format_ret(-1, '', '明细信息不存在，添加失败');
        }
        foreach ($details as &$detail) {
            $detail['trade_price'] = $detail['price'];
        }
        $sql = "select goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num from b2b_lof_datail where order_type = 'fx_purchase' and order_code = :order_code";
        $details_lof = $this->db->get_all($sql, array(":order_code" => $record['record_code']));
        if (empty($details_lof)) {
            return $this->format_ret(-1, '', '批次信息不存在，添加失败');
        }
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $record['store_code'], 'wbm_notice', $details_lof);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('wbm/NoticeRecordDetailModel')->add_detail_action($id, $details);
        return $ret;
    }

    function delete_pftz($record) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '查询不到单据信息');
        }
        //如果经销采购订单可以多次生成批发通知单 改成get_all
        $pft_record = $this->db->get_row("select record_code,notice_record_id from wbm_notice_record where relation_code = :relation_code", array(":relation_code" => $record['record_code']));
        if (empty($pft_record)) {
            return $this->format_ret(-1, '', '批发通知单不存在无法删除');
        }
        $sql = "SELECT sum(finish_num) FROM fx_purchaser_record_detail WHERE record_code = :record_code ";
        $js_detail_finish_num = $this->db->get_value($sql, array(':record_code' => $record['record_code']));
        if ($js_detail_finish_num == 0) { //实际出库数为零，确认也可删除
            $this->begin_trans();
            //取消锁定
            $ret = $this->unlock($pft_record['notice_record_id'], 'is_sure');
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
            $result = $this->delete($pft_record['notice_record_id'], 'jx_unsettlement');
            if ($result['status'] != 1) {
                $this->rollback(); //事务回滚
                return $result;
            }
            //删除关联的销货单
            $sql = "SELECT store_out_record_id FROM wbm_store_out_record WHERE jx_code = :jx_code";
            $store_out_record = $this->db->get_all($sql, array(':jx_code' => $record['record_code']));
            if (!empty($store_out_record)) {
                foreach ($store_out_record as $v) {
                    load_model('wbm/StoreOutRecordModel')->delete($v['store_out_record_id']);
                }
            }
            $this->commit();
            return $result;
        } else {
            return $this->format_ret(-1, '', '已有实际出库数，不能删除');
        }
    }

    /**
     * 释放调整单锁定库存
     * params id 通知单id
     * reutrn ret
     */
    function unlock($id, $field) {
        $record = $this->get_row(array('notice_record_id' => $id));
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_notice');
        require_model('prm/InvOpModel');
        $this->begin_trans();
        $invobj = new InvOpModel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code'], 0, $ret_lof_details['data']);
        $ret = $invobj->adjust();

        if ($ret['status'] > 0 && $field == 'is_sure') {
            $ret = load_model('wms/WmsEntryModel')->cancel($record['data']['record_code'], 'wbm_notice', $record['data']['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            } else {
                $ret['status'] = 1;
            }
        }
        $this->commit();
        return $ret;
    }

    function api_notice_record_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code', 'is_check', 'custom_code', 'is_finish'),
            'i' => array('page', 'page_size')
        );
        $arr_option = array();
        valid_assign_array($param, $key_option, $arr_option);
        $arr_deal = $arr_option;
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        //清空无用数据
        unset($arr_option);
        unset($param);
        $select = "r1.record_code,r1.distributor_code as custom_code,r1.record_time,r1.store_code,r1.rebate,r1.num,r1.finish_num,r1.money,r1.order_time,r1.remark,r1.name,r1.tel as telephone,r1.country,r1.province,r1.city,r1.district,r1.street,r1.address,r1.record_type_code as type_code,rr.brand_code,rr.express_code,rr.express as express_no,r2.delivery_method,r2.arrival_time,r3.warehouse_name,r1.tel,rr.delivery_id,rr.storage_no,rr.po_no";
        $sql_values = array();
        $sql_join = " left join api_weipinhuijit_store_out_record rr on r1.record_code=rr.notice_record_no left join api_weipinhuijit_delivery r2 on rr.delivery_id=r2.delivery_id left join api_weipinhuijit_warehouse r3 on rr.warehouse=r3.warehouse_code ";
        $sql_main = " from wbm_notice_record r1 {$sql_join} where 1";
        $sql_values[":is_check"] = 1;
        $sql_values[":is_finish"] = 0;
        $sql_values[":start_time"] = date("Y-m-d") . " 00:00:00";
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'start_time') {
                    $sql_values[":{$key}"] = $val;
                }
                if ($key == 'end_time') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.lastchanged <=:{$key}";
                }
                if ($key == 'store_code') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.store_code =:{$key}";
                }
                if ($key == 'is_check') {
                    $sql_values[":{$key}"] = $val;
                }
                if ($key == 'custom_code') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND r1.distributor_code =:{$key}";
                }
                if ($key == 'is_finish') {
                    $sql_values[":{$key}"] = $val;
                }
            }
        }
        $sql_main .= " AND r1.is_sure =:is_check";
        $sql_main .= " AND r1.is_finish =:is_finish";
        $sql_main .= " AND r1.lastchanged >=:start_time";
        $sql_main .= ' group by record_code ';
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select, true);
        if (empty($ret['data'])) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }

        filter_fk_name($ret['data'], array('custom_code|custom', 'type_code|record_type'));
        foreach ($ret['data'] as $key => &$v) {
            $ShippingStyle = array('1' => '汽运', '2' => '空运');
            $v['delivery_method'] = $ShippingStyle[$v['delivery_method']];
            $sql = "select * from api_weipinhuijit_store_out_record where notice_record_no=:notice_record_no ";
            $sql_value = array(
                ':notice_record_no' => $v['record_code'],
            );
            $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
            if (!empty($weipinhuijit_info)) {
                $v['is_vip'] = 1;
            } else {
                $v['is_vip'] = 0;
            }

            if ($v['is_vip'] != 1) {
                $del_key = array(
                    'delivery_method', 'arrival_time', 'warehouse_name', 'delivery_id', 'storage_no', 'tel', 'brand_code', 'express_code', 'express_no', 'po_no'
                );
                foreach ($del_key as $value) {
                    if (array_key_exists($value, $v)) {
                        unset($v[$value]);
                    }
                }
            }
            $v['country'] = oms_tb_val('base_area', 'name', array('id' => $v['country']));
            $v['province'] = oms_tb_val('base_area', 'name', array('id' => $v['province']));
            $v['city'] = oms_tb_val('base_area', 'name', array('id' => $v['city']));
            $v['district'] = oms_tb_val('base_area', 'name', array('id' => $v['district']));
            $v['street'] = oms_tb_val('base_area', 'name', array('id' => $v['street']));

            $v['custom_name'] = $v['custom_code_name'];
            $v['type_name'] = $v['type_code_name'];
            unset($v['custom_code_code'], $v['custom_code_name'], $v['type_code_name'], $v['type_code_code']);
        }

        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $ret);
    }

    /**
     * 扫描页面确认
     * @param $request
     * @return array
     */
    function scan_do_check($request) {
        $record = $this->get_row(array('record_code' => $request['record_code']));
        if ($record['status'] != 1) {
            return $this->format_ret('-1', '', '单据不能存在！');
        }
        $record_data = $record['data'];
        if ($record_data['is_sure'] != 0) {
            return $this->format_ret('-1', '', '单据状态异常！');
        }
        $ret = $this->update_sure(1, 'is_sure', $record_data['notice_record_id']);
        //日志
        if ($ret['status'] == '1') {
            $action_name = '扫描确认';
            $sure_status = '扫描确认';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => '未出库', 'action_name' => $action_name, 'module' => "wbm_notice_record", 'pid' => $record_data['notice_record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    public function add_detail($param) {
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($param['record_id'], $param['detail']);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($param['record_id'], $param['store_code'], 'wbm_notice', $param['detail']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('wbm/NoticeRecordDetailModel')->add_detail_action($param['record_id'], $param['detail']);
        if ($ret['status'] == '1') {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "wbm_notice_record", 'pid' => $param['record_id']);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }

        return $ret;
    }
    public function check_is_print_record_clothing($notice_record_id){
        //特性有没有开启
        $status = load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print'];
        if($status != 1){
            return $this->format_ret(-2,'','单据颜色、尺码层商品打印未开启');
        }
        $data = $this->get_by_id($notice_record_id);
        $ret = load_model('wbm/StoreOutRecordModel')->get_detail_by_relation($data['data']['record_code']);
        $out_record_code_id = array_unique(array_column($ret['data'],'store_out_record_id'))[0];
        $size_ret_arr = array_unique(array_column($ret['data'],'spec2_name'));
        $ret = $this->is_in_size($size_ret_arr);
        if($ret['status'] < 1){
            return $ret;
        }
        return $this->format_ret(1,array('out_record_code'=>$out_record_code_id));
    }
    //判断尺码是否在尺码曾中
    public function is_in_size($size_ret_arr){
        $size_layer = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $size_layer_arr = json_decode($size_layer,true);
        $size_arr = array();
        foreach ($size_layer_arr as $val){
            $size_arr = array_merge($size_arr,$val);
        }
        $error = array();
        foreach ($size_ret_arr as $val){
            if(!in_array($val,$size_arr)){
                $error[] = $val;
            }
        }
        if(!empty($error)) return $this->format_ret(-2,'','尺码'.implode('，',$error).'不在尺码层中');
        else return $this->format_ret(1);
    }

}
