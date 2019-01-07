<?php

/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');

class PurchaseRecordModel extends TbModel {

    private $is_check_notice_num = 1;

    function get_table() {
        return 'pur_purchaser_record';
    }

    /**
     * @var array 打印发货单模板所需字段
     */
    public $print_fields_default = array(
        'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '通知单号' => 'relation_code',
            '下单时间' => 'order_time',
            '供应商' => 'supplier_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '总入库数' => 'sum_num',
            '总金额' => 'sum_money',
            '总通知数' => 'notice_num',
            '备注' => 'remark',
            '总差异数' => 'diff_num',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '采购类型' => 'record_type_name',
        /* '发货商店' => 'sender_shop_name',

          '发货方联系手机' => 'sender_mobile',//
          '发货地址（无省市区）' => 'sender_addr',
          '发货地址（含省市区）' => 'sender_address',
          '发货邮编' => 'sender_zip',
          '发货街道' => 'senderr_street',
          '发货区/县' => 'senderr_district',
          '发货市' => 'sender_city',//新增
          '发货省' => 'sender_province',//新增
          '发货时间' => 'sender_date',//新增
          '发货打单员' => 'sender_operprint',//新增 */
        ),
        'detail' => array(
            array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '出厂名称' => 'goods_produce_name',
                '规格1' => 'spec1_name',
                '规格2' => 'spec2_name',
                '单价' => 'price',
                '折扣' => 'rebate',
                '批发价' => 'pf_price',
                '实际入库数' => 'num',
                '金额' => 'money',
                '通知数' => 'notice_num',
                '差异数' => 'diff_num',
                '商品条形码' => 'barcode',
                '库位' => 'shelf_code',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品重量' => 'weight',
                '吊牌价' => 'dp_price',
            ),
        ),
    );

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default($id) {
        $r = array();
        $ret = $this->get_by_id($id);
        $r['record'] = $ret['data'];
        $r['record']['print_time'] = date('Y-m-d H:i:s');
        $r['record']['print_user_name'] = CTX()->get_session('user_name');
        $r['record']['record_type_name'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $r['record']['record_type_code']));
        $sql = "select d.pid
                ,d.goods_code,d.num,d.rebate,d.sku,if(r3.price=0,g.sell_price,r3.price) as dp_price,(d.price*d.rebate) as price,d.notice_num,( d.notice_num - d.num) as diff_num,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.trade_price as pf_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code,g.weight,g.year_name,g.brand_name,g.season_name,g.category_name
                ,r3.spec1_name,r3.spec2_name,r3.barcode
                from pur_purchaser_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku

                where d.pid=:pid";
//base_year year_code
        //base_brand brand_code
        //base_season season_code
        //base_category

        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
        $store_code = $r['record']['store_code'];
        $i = 1;
        $sum_money = 0;
        foreach ($r['detail'] as &$val) {
            $val['sort_num'] = $i;
            $val['price'] = number_format($val['price'], 3);
            $sum_money += $val['money'];
            $shelf_code_arr = load_model('prm/GoodsShelfModel')->get_goods_shelf($store_code, $val['sku']);
            $val['shelf_code'] = implode(",", $shelf_code_arr);
            $i++;
        }
        $r['record']['sum_money'] = $sum_money;
        // 数据替换
        $this->print_data_escape($r['record'], $r['detail']);
        $d = array('record' => array(), 'detail' => array());
        foreach ($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v; //$v; //
        }
        foreach ($r['detail'] as $k1 => $v1) {
            // 键值对调
            foreach ($v1 as $k => $v) {
                $nk = array_search($k, $this->print_fields_default['detail'][0]);
                $nk = $nk === false ? $k : $nk;
                $d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
            }
        }
        // 更新状态
        //$this->print_update_status($r['record']['deliver_record_id'], 'deliver');
        $this->update_print_status($id);
        return $d;
    }
    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 入库单新版打印（更改打印配件）
     */
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $ret = $this->get_by_id($id);
        $r['record'] = $ret['data'];
        $r['record']['print_time'] = date('Y-m-d H:i:s');
        $r['record']['print_user_name'] = CTX()->get_session('user_name');
        $r['record']['record_type_name'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $r['record']['record_type_code']));
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['record_code']);
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.rebate,d.sku,if(r3.price=0,g.sell_price,r3.price) as dp_price,(d.price*d.rebate) as price,d.notice_num,( d.notice_num - d.num) as diff_num,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.trade_price as pf_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code,g.weight,g.year_name,g.brand_name,g.season_name,g.category_name
                ,r3.spec1_name,r3.spec2_name,r3.barcode
                from pur_purchaser_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku

                where d.pid=:pid";
            $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
            $sku_array = array();
            $shelf_code_arr = array();
            $i = 1;
            $money = 0;
            foreach ($r['detail'] as $key => &$detail) {
                $detail['sort_num'] = $i;//合并同一sku
                $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['store_code']);
                $shelf_code_arr[] = $detail['shelf_code'];
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);
                
                $money =$money + $detail['money'];
                $i++;
            }
            $r['record']['sum_money']=$money;
        }
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail']);
        $trade_data = array($r['record']);
        //更新状态
        $this->update_print_status($id);
        return $r;
    }
    function print_data_escape(&$record, &$detail){
            $record['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code'=>$record['supplier_code']));
            $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$record['store_code']));
            $record['diff_num'] = $record['num'] - $record['finish_num'];
    }
    
    //更新打印状态
    public function update_print_status ($purchaser_record_id) {
        $sql = "SELECT is_print_record FROM pur_purchaser_record WHERE purchaser_record_id = :purchaser_record_id ";
        $data = $this->db->get_row($sql, array(':purchaser_record_id'=>$purchaser_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '采购入库单不存在');
        }
        //添加日志
        $this->add_print_log($purchaser_record_id);
        //更新状态
        $this->update_exp( 'pur_purchaser_record', array('is_print_record' => 1 ), array('purchaser_record_id' => $purchaser_record_id ));
    }
    
    //打印日志记录
    public function add_print_log($purchaser_record_id) {
        // 增加打印日志
        $sql = "SELECT is_check,is_check_and_accept FROM pur_purchaser_record WHERE purchaser_record_id = :purchaser_record_id  ";
        $d = $this->db->get_row($sql, array(':purchaser_record_id'=>$purchaser_record_id));
        $sure_status = $d['is_check'] == 1 ? "确认" : "未确认";
        $finish_status = $d['is_check_and_accept'] == 0 ? '未验收' : '验收';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '打印', 'module' => "purchase_record", 'pid' => $purchaser_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }
    
    //检查是否打印
    public function check_is_print ($purchaser_record_id) {
        $sql = "SELECT is_print_record FROM pur_purchaser_record WHERE purchaser_record_id = :purchaser_record_id ";
        $data = $this->db->get_row($sql, array(':purchaser_record_id'=>$purchaser_record_id));
        $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印采购入库单，是否继续打印？') : $this->format_ret(1, '', '');
        return $ret;
    }
    
    private function get_shelf_code($sku, $store_code) {
        
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code and a.sku = :sku order by a.sku,a.shelf_code asc";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }
    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        // 采购单号查询联表
        if ($filter['keyword_type'] == 'planned_code' && $filter['keyword'] != '') {
            $sql_join = " LEFT JOIN pur_order_record r5 ON rl.relation_code = r5.record_code LEFT JOIN pur_planned_record r6 ON r5.relation_code = r6.record_code ";
        }
        $sql_main = "FROM {$this->table} rl {$sql_join} LEFT JOIN pur_purchaser_record_detail r2 on rl.record_code = r2.record_code LEFT JOIN pur_order_record pr ON rl.relation_code = pr.record_code  LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code ";
//        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'purchase_record_list') {
//            $sql_main .= "LEFT JOIN b2b_lof_datail r4 ON r2.record_code = r4.order_code AND r2.sku = r4.sku AND r4.order_type = 'purchase' ";
//        }
        $sql_main .= ' WHERE 1 ';
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        // 通知单号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        // 采购单号
        if (isset($filter['planned_code']) && $filter['planned_code'] != '') {
            $sql_main .= " AND (r6.record_code LIKE :planned_code ) ";
            $sql_values[':planned_code'] = $filter['planned_code'] . '%';
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }

        // 原单号
//        if (isset($filter['init_code']) && $filter['init_code'] != '') {
//            $sql_main .= " AND (rl.init_code LIKE :init_code )";
//            $sql_values[':init_code'] = $filter['init_code'] . '%';
//        }

        // 入库类型
        if (isset($filter['record_type_code']) && $filter['record_type_code'] != '') {
            $sql_main .= " AND (rl.record_type_code = :record_type_code )";
            $sql_values[':record_type_code'] = $filter['record_type_code'];
        }
        // 单据状态
        if (isset($filter['is_check_and_accept']) && $filter['is_check_and_accept'] != '') {
            $sql_main .= " AND (rl.is_check_and_accept = :is_check_and_accept )";
            $sql_values[':is_check_and_accept'] = $filter['is_check_and_accept'];
        }
        //商品编码、名称
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //制单日期
        if (isset($filter['bill_time_start']) && $filter['bill_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :bill_time_start )";
            $sql_values[':bill_time_start'] = $filter['bill_time_start'];
        }
        if (isset($filter['bill_time_end']) && $filter['bill_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :bill_time_end )";
            $sql_values[':bill_time_end'] = $filter['bill_time_end'];
        }

        //入库日期
        if (isset($filter['enter_time_start']) && $filter['enter_time_start'] != '') {
            $sql_main .= " AND (rl.enter_time >= :enter_time_start )";
            $sql_values[':enter_time_start'] = $filter['enter_time_start'];
        }
        if (isset($filter['enter_time_end']) && $filter['enter_time_end'] != '') {
            $sql_main .= " AND (rl.enter_time < :enter_time_end )";
            $date = new DateTime($filter['enter_time_end']);
            $date->add(new DateInterval('P1D'));
            $sql_values[':enter_time_end'] = $date->format('Y-m-d');
        }
        //下单时间order_time_start
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
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
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = "%{$filter['remark']}%";
        }
        //导出明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'purchase_record_list') {
            $sql_main .= " order by r2.record_code,r2.sku desc";
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*,pr.in_time';
        $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($data['data'] as $key => $value) {
//            $data['data'][$key]['money'] = round($value['money'], 2);
            $data['data'][$key]['yanshou'] = $value['is_check_and_accept'] == 1 ? '是' : '否';
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $value['supplier_code'], 'supplier_name');
            $data['data'][$key]['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $value['record_type_code'], 'record_type_name');
            $data['data'][$key]['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
            $data['data'][$key]['sum_num'] = 0;
            $data['data'][$key]['sum_money'] = 0;
            $data['data'][$key]['sum_differ'] = 0;
            $data['data'][$key]['diff_num'] = $data['data'][$key]['num'] - $data['data'][$key]['finish_num'];
            $result = load_model('pur/PurchaseRecordDetailModel')->get_by_record_code($value['record_code']);
            //var_dump($result);die;
            if (!empty($result)) {
//	            $sum_num = 0;
                $sum_money = 0;
//	            $sum_differ =0;
                $notice_num = 0;
                foreach ($result['data'] as $k => $v) {
//		            $differ = $v['notice_num'] - $v['num'];
//		            $sum_num += $v['num'];
                    $sum_money += $v['money'];
//		            $sum_differ += $differ;
                    $notice_num += $v['notice_num'];
                }

//	            $data['data'][$key]['sum_num'] = round($sum_num, 2);
                $data['data'][$key]['sum_money'] = $sum_money;
//	            $data['data'][$key]['diff_num'] = $sum_differ;
                $data['data'][$key]['notice_num'] = $notice_num;
            }
            if ($status['status'] != 1) {
                $data['data'][$key]['sum_money'] = '****';
            }
        }
        // print_r($data);
//        filter_fk_name($data['data'], array('store_code|store','adjust_type|record_type'));
        //dump($data,1);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.is_check_and_accept,rl.record_code,rl.enter_time,rl.rebate,rl.store_code,rl.relation_code,rl.order_time,rl.record_time,rl.record_type_code,rl.supplier_code,rl.remark,pr.in_time,r3.goods_code,r3.barcode,r3.price as dj,r2.price,r2.money,r2.num,r2.notice_num,r2.sku,r2.spec1_code,r2.spec2_code,r2.rebate";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //价格管控判断
        $status_pur_price = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price', $filter['user_id']);
        foreach ($ret_data['data'] as $key => $value) {                    
            //查询入库类型
            $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $value['record_type_code'], 'record_type_name');
            $ret_data['data'][$key]['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
            //查询供应商名称
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $value['supplier_code'], 'supplier_name');
            $ret_data['data'][$key]['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            //查询仓库名称
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $ret_data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            //查询规格1/规格2
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode','goods_short_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            //商品简称
            $ret_data['data'][$key]['goods_short_name'] = $sku_info['goods_short_name'];
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            //计算差异
            $ret_data['data'][$key]['diff_num'] = $value['notice_num'] - $value['num'];

            //进货价管控
            if ($status_pur_price['status'] != 1) {
                $ret_data['data'][$key]['price'] = '****';
                $ret_data['data'][$key]['price1'] = '****';
                $ret_data['data'][$key]['money'] = '****';
            } else {
                $ret_data['data'][$key]['price1'] = $value['price'] * $value['rebate'];
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('purchaser_record_id' => $id));
        $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $data['data']['supplier_code'], 'supplier_name');
        $data['data']['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $data['data']['record_type_code'], 'record_type_name');
        $data['data']['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('sys_user', 'user_code', $data['data']['user_code'], 'user_name');
        $data['data']['user_name'] = isset($arr['data']['user_name']) ? $arr['data']['user_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_store', 'store_code', $data['data']['store_code'], 'store_name');
        $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
        $data['data']['sum_num'] = 0;
        $data['data']['sum_money'] = 0;

        $result = load_model('pur/PurchaseRecordDetailModel')->get_by_record_code($data['data']['record_code']);
        if (!empty($result)) {
            $sum_num = 0;
            $sum_money = 0;
            $sum_differ = 0;
            $notice_num = 0;
            foreach ($result['data'] as $k => $v) {
                $differ = $v['notice_num'] - $v['num'];
                $sum_num += $v['num'];
                $sum_money += $v['money'];
                $sum_differ += $differ;
                $notice_num += $v['notice_num'];
            }

            $data['data']['sum_num'] = round($sum_num, 2);
            $data['data']['sum_money'] = str_replace(',','',number_format($sum_money, 3)); //处理千位情况
            $data['data']['diff_num'] = $sum_differ;
            $data['data']['notice_num'] = $notice_num;
        }
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        if ($status['status'] != 1) {
            $data['data']['sum_money'] = '****';
        }
        return $data;
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

    function delete($purchaser_record_id) {
        $sql = "select * from {$this->table} where purchaser_record_id = :purchaser_record_id";
        $data = $this->db->get_row($sql, array(":purchaser_record_id" => $purchaser_record_id));
        if ($data['is_check_and_accept'] == 1) {
            return $this->format_ret('-1', array(), '单据已经入库，不能删除！');
        }
        $ret = parent::delete(array('purchaser_record_id' => $purchaser_record_id));
        $this->db->create_mapper('pur_purchaser_record_detail')->delete(array('pid' => $purchaser_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $purchaser_record_id, 'order_type' => 'purchase'));
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => '未验收', 'action_name' => "删除", 'module' => "purchase_record", 'pid' => $purchaser_record_id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        return $ret;
    }

    /*
     * 添加新纪录
     */

    function insert($stock_adjus) {
        $status = $this->valid($stock_adjus);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($stock_adjus['record_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', 'RECORD_ERROR_UNIQUE_CODE1');
//        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');
        $stock_adjus['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $stock_adjus['remark']);
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
     * 编辑一条采购入库单记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-12
     * @param array $data
     * @param array $where
     * @return array
     */
    public function edit_action($data, $where) {
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['purchaser_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret(false, array(), '没找到单据!');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }

        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
        //更新主表数据
        return parent::update($data, $where);
    }

    function update_check($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'error_params');
        }
        $ret = parent:: update(array('is_check_and_accept' => $active), array('purchaser_record_id' => $id));
        return $ret;
    }

    function update_check_new($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        /*
          $details = load_model('pur/PurchaseRecordDetailModel')->get_all(array('pid' => $id));
          //检查明细是否为空
          if (empty($details['data'])) {
          return $this->format_ret('-1','','RECORD_ERROR_DETAIL_EMPTY');
          } */
        $ret = parent:: update(array($field => $active), array('purchaser_record_id' => $id));
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn($djh = 0) {
        if ($djh > 0) {
            $sql = "select purchaser_record_id  from {$this->table}   order by purchaser_record_id desc limit 1 ";
            $data = $this->db->get_all($sql);
            if ($data) {
                $djh = intval($data[0]['purchaser_record_id']) + 1;
            } else {
                $djh = 1;
            }
        } else {
            $djh = $djh + 1;
        }
        require_lib('comm_util', true);
        $new_jdh = "JH" . date("Ymd") . add_zero($djh);
        $sql = "select purchaser_record_id  from {$this->table}  where record_code=:record_code ";
        $sql_value = array(':record_code' => $new_jdh);
        $id = $this->db->get_value($sql, $sql_value);
        if (empty($id) || $id === false) {
            return $new_jdh;
        } else {
            return $this->create_fast_bill_sn($djh);
        }
    }
    
    //检查差异数
    function check_diff_num ($record_code) {
        $record = $this->get_row(array('record_code' => $record_code));
        $id = $record['data']['purchaser_record_id'];
        if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
            return $this->format_ret(-1, array(), '该单据已经入库!');
        }
        if ($record['data']['relation_code'] == '') {
            return $this->format_ret(1, array(), '');
        }
        $data = $this->db->get_row("SELECT SUM(num) AS finish_num,SUM(notice_num) AS enotice_num FROM `pur_purchaser_record_detail` WHERE pid=:pid", array(':pid'=>$id));
        if ($data['enotice_num']>=0 && $data['finish_num'] == 0) {
            return $this->format_ret(2, array(), '是否确认整单验收？ ');
        }
        $ret = $this->db->get_row("SELECT record_code FROM pur_purchaser_record_detail WHERE pid = :pid AND num != notice_num", array(':pid'=>$id));
        if (!empty($ret['record_code'])) {
            return $this->format_ret(2, array(), '是否确认差异验收？ ');
        }
        if ($data['enotice_num'] == $data['finish_num']) {
            return $this->format_ret(1, array(), '');
        }
    }
    
    /**
     * 根据调整单ID来验收调整单, 验收后生成库存流水, 并更改库存帐表
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-11
     * @param int $id
     * $accept_type 验收类型 1:按业务日期验收
     * @return array
     */
    function checkin($record_code, $check = 1, $record = array(), $details = array(), $accept_type = 0) {
        //检查调整单状态是否为已验收
        //$record = $this->get_row(array('purchaser_record_id' => $id));
        if (empty($record)) {
            $record = $this->get_row(array('record_code' => $record_code));
        }
        $id = $record['data']['purchaser_record_id'];
        if (empty($details)) {
            $details = load_model('pur/PurchaseRecordDetailModel')->get_all(array('pid' => $id));
        }

        if (isset($record['data']['is_check_and_accept']) && 1 == $record['data']['is_check_and_accept']) {
            return $this->format_ret(false, array(), '该单据已经入库!');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret(-1, array(), '单据明细不能为空!');
        }
        //num>0不能强制入库
        //通知数，完成数
        $tz_num = 0;
        $fini_num = 0;
        foreach ($details['data'] as $k => $v) {
            $tz_num += intval($v['notice_num']);
            $fini_num += intval($v['num']);
        }
        //print_r($pici_arr);
        //计算金额
        //$this->set_money($id);
        //exit;
//         if($check==1 && $fini_num > 0){
//         	return $this->format_ret(false, array(), '不能强制入库,请扫描入库!');
//         }
        //单据批次添加
        if ($check == 1 && $fini_num == 0) {
            foreach ($details['data'] as $key => $value) {
                $details['data'][$key]['num'] = $value['notice_num'];
            }
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record['data']['purchaser_record_id'], $record['data']['store_code'], 'purchase', $details['data']);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'purchase');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret(-1, array(), '数据不能为空!');
        }

        $init_num = 0;
        $num = 0;
        //foreach($ret_lof_details['data'] as &$val){
        foreach ($ret_lof_details['data'] as $key => $val) {
            $init_num += $val['init_num'];
            $num += $val['num'];

            //if($check==1){
            // $val['num'] = $val['init_num'];
            // }
            if ($val['num'] == 0) {
                unset($ret_lof_details['data'][$key]);
            }
        }

        if ($init_num == 0 && $num == 0) {
            return $this->format_ret(-1, array(), '入库数量为0，入库失败!');
        }

        //if($check==1){
        // load_model('stm/GoodsInvLofRecordModel')->set_init_num($record['data']['record_code'],'purchase');
        //}
        //赋值入库数量

        $this->begin_trans();
        require_model('prm/InvOpModel');

        $invobj = new InvOpModel($record['data']['record_code'], 'purchase', $record['data']['store_code'], 3, $ret_lof_details['data']);

        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        //按业务日期验收
        if ($accept_type == 1) {
            //更新业务日期
            $ret = $this->update_exp('b2b_lof_datail', array('order_date' => $record['data']['record_time']), array('order_code' => $record['data']['record_code'], 'order_type' => 'purchase'));
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '更新失败！');
            }
        }

        $data = array(
            'is_check' => 1,
            'is_check_and_accept' => 1, 
            'enter_time' => date('Y-m-d H:i:s'),
            'record_time' => ($accept_type == 0) ? date('Y-m-d') : $record['data']['record_time'],
        );
        
        if(!empty($record['data']['relation_code'])) { //绑定通知单号
            $planned_record = load_model('pur/AccountsPayableModel')->get_planned_code($record['data']['relation_code']);
            // 是否已通知财务付款
            if($record['data']['is_notify_payment'] == 1 && !empty($planned_record)) {//已通知财务付款，采购订单绑定入库单号
                $purchaser_record_code_str = !empty($planned_record['purchaser_record_code']) ? $planned_record['purchaser_record_code'] . "," .$record['data']['record_code'] : $record['data']['record_code'];
                $ret = $this->update_exp('pur_planned_record', array('purchaser_record_code' => $purchaser_record_code_str), array('planned_record_id' => $planned_record['planned_record_id']));
                if ($ret['status'] != 1) {
                    $this->rollback(); //事务回滚
                    return $this->format_ret(-1,'','绑定采购订单关联关系失败');
                }
            } 
            if ($record['data']['is_notify_payment'] == 0 && !empty($planned_record)){ //未通知财务付款，入库单存储采购订单号
                $data['planned_record_code'] = $planned_record['record_code'];
            }
        }

        $ret = parent:: update($data, array('purchaser_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $this->format_ret(-1, '', '修改入库单状态失败');
        }

        //回写明细表数量金额
        // $ret = load_model('pur/PurchaseRecordDetailModel')->mainWriteBack($id);
        $this->update_finish_num($id, $record['data']['record_code'], $ret_lof_details['data']);
        //计算金额
        $this->set_money($id);
        //回写主表入库数量和金额
        $this->record_finish_num($record['data']['record_code']);
        //回写采购订单完成数
        if (isset($record['data']['relation_code']) && $record['data']['relation_code'] <> '' && isset($details['data'])) {
            //回写采购订单完成数
            $ret = load_model('pur/OrderRecordDetailModel')->update_finish_num($record['data']['relation_code'], $ret_lof_details['data']);
             //采购单验收入库，减在途
            load_model('prm/InvOpRoadModel')->update_road_inv($record['data']['record_code']);
        }
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        
        $this->commit(); //事务提交
        return $this->format_ret(1, $id, '验收成功!');
    }

    //设置完成数量
    function update_finish_num($id, $recode_code, $lof_details_data) {

        $sql = "update pur_purchaser_record_detail,(select sku,sum(num) as num from b2b_lof_datail where order_code='{$recode_code}' and order_type='purchase' GROUP BY sku )
as sku_num  ";
        $sql .= " set pur_purchaser_record_detail.num = sku_num.num";
        $sql .= " where  pur_purchaser_record_detail.sku=sku_num.sku and pur_purchaser_record_detail.pid = {$id}";
        return $this->db->query($sql);
    }

    //设置入库单主表入库数量、金额
    function record_finish_num($recode_code) {
        $sql = "UPDATE pur_purchaser_record p,(SELECT record_code,sum(num) as total_finish_num,SUM(notice_num) as total_notice_num,sum(money) AS money from  pur_purchaser_record_detail GROUP BY record_code) as tmp set p.num=tmp.total_notice_num,p.finish_num=tmp.total_finish_num,p.money = tmp.money WHERE p.record_code=tmp.record_code and p.record_code='{$recode_code}';";
        return $this->db->query($sql);
    }

    //计算金额
    function set_money($record_id) {
        $sql = " update pur_purchaser_record_detail set money = price * rebate * num  where pid ='{$record_id}'  ";
        //echo $sql;
        return $this->db->query($sql);
    }

    function get_barcode_by_sku(&$barcode_arr, &$barcode_num) {
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "select sku,barcode from goods_sku where barcode in({$barcode_str}) ";
        $data = $this->db->get_all($sql,$sql_values);
        $sku_num = array();
        $sku_arr = array();
        $new_barcode = array();

        foreach ($data as $val) {
            $sku_arr[] = $val['sku'];
            $sku_num[$val['sku']] = $barcode_num[$val['barcode']];
            $new_barcode[] = $val['barcode'];
        }

        if (count($barcode_arr) != count($new_barcode)) {
            $result = array_diff($barcode_arr, $new_barcode);
            return $this->format_ret(-1, '', "找不到对应条码" . implode(",", $result));
        }


        $barcode_arr = $sku_arr;
        $barcode_num = $sku_num;

        return $this->format_ret(1);
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        $record = $this->get_row(array('purchaser_record_id' => $id));
        $store_code = $record['data']['store_code'];
        $relation_code = $record['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $error_msg = array();
        $err_num = 0;
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];
        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $sku_arr, $sku_num);
            $import_count = count($sku_arr);
        } else {
            $num = $this->read_csv_sku($file, $sku_arr, $sku_num);
            $import_count = count($sku_arr);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            if (!empty($sku_num) && !empty($sku_arr)) {
                $barcode_str = implode("','", $sku_arr);
                $sql = "select sku,lof_no,production_date,type from goods_lof where sku in ('$barcode_str') group by sku";
                $sku_data = $this->db->get_all($sql);
                $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
                $moren = $this->db->get_row($sql_moren);
                $lof_data_new = array();
                foreach ($sku_data as $lof_data) {
                    $lof_data_new[$lof_data['sku']]['production_date'] = $lof_data['production_date'];
                    $lof_data_new[$lof_data['sku']]['lof_no'] = $lof_data['lof_no'];
                }
                $new_barcode_num = $sku_num;
                $sku_num = array();
                foreach ($sku_arr as $sku) {
                    if (array_key_exists($sku, $lof_data_new)) {
                        $sku_num[$sku][$lof_data_new[$sku]['lof_no']]['num'] = $new_barcode_num[$sku]['num'];
                        $sku_num[$sku][$lof_data_new[$sku]['lof_no']]['purchase_price'] = $new_barcode_num[$sku]['purchase_price'];
                        $sku_num[$sku][$lof_data_new[$sku]['lof_no']]['lof_no'] = $lof_data_new[$sku]['lof_no'];
                        $sku_num[$sku][$lof_data_new[$sku]['lof_no']]['production_date'] = $lof_data_new[$sku]['production_date'];
                    } else {
                        $sku_num[$sku][$moren['lof_no']]['num'] = $new_barcode_num[$sku]['num'];
                        $sku_num[$sku][$moren['lof_no']]['purchase_price'] = $new_barcode_num[$sku]['purchase_price'];
                        $sku_num[$sku][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                        $sku_num[$sku][$moren['lof_no']]['production_date'] = $moren['production_date'];
                    }
                }
            }
        }

        if (!empty($sku_num) && !empty($sku_arr)) {
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.trade_price,g.sell_price,g.purchase_price from
		    		goods_sku b
		    		inner join base_goods g ON g.goods_code = b.goods_code

		    		where b.barcode in({$barcode_str}) ";
            $detail_data = $this->db->get_all($sql,$sql_values); //sell_price
            $detail_data_lof = array();
            $temp = array();
            foreach ($detail_data as $key => $val) {
                foreach ($sku_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        //$val['pici_lof'] = '1';
                        $val['purchase_price'] = !empty($v1['purchase_price']) ? $v1['purchase_price'] : $val['purchase_price'];
                        $detail_data_lof[] = $val;
                        unset($sku_num[$val['barcode']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '数量不能为空');
                        $err_num ++;
                        unset($sku_num[$val['barcode']]);
                    }
                }
            }
            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
            //单据批次添加
//            $import_count = count($detail_data_lof);
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'purchase', $detail_data_lof);
           if ($ret['status']<1) {
                return $ret;
            }
            //入库单明细添加
            $ret = load_model('pur/PurchaseRecordDetailModel')->add_detail_action($record['data']['record_code'], $detail_data_lof);

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "purchase", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }


        if (!empty($sku_num)) {
            $sku_error = array_keys($sku_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function check_pftzd($record_code, &$sku_arr, &$sku_num, &$err_num) {
        $err_data = '';
        if ($record_code) {
            $sql = "select * from pur_order_record_detail where record_code = :record_code";
            $record = $this->db->get_all($sql, array(":record_code" => $record_code));
            if (!empty($record)) {
                $new_record = array();
                foreach ($record as $r) {
                    $barcode = $this->db->get_value("select barcode from goods_barcode where sku = :sku", array(':sku' => $r['sku']));
                    $new_record[$barcode] = $r['num'];
                }
                foreach ($sku_num as $key => $sku) {
                    $flag = true;
                    if (array_key_exists($key, $new_record)) {
                        if ($sku_num[$key]['num'] > $new_record[$key] && $this->is_check_notice_num == 1) {
                            $err_data[] = array($key => '添加商品数量不能超过批发通知单通知数！');
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                        $err_data[] = array($key => '关联采购通知单中不存在该条码！');
                    }
                    if ($flag == false) {
                        if (array_search($key, $sku_arr) !== false) {
                            unset($sku_arr[array_search($key, $sku_arr)]);
                        }
                        $err_num ++;
                        unset($sku_num[$key]);
                    }
                }
            }
        }
        return $err_data;
    }

    function read_csv_sku($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = $row[0];
                    $sku_num[$row[0]]['num'] = $row[1];
                    $sku_num[$row[0]]['purchase_price'] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
        // var_dump($sku_arr,$sku_num);die;
    }

    function read_csv_lof($file, &$sku_arr, &$sku_num) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[$row[0]] = $row[0];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[3]]['purchase_price'] = $row[2];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[3];
                    $production_date = load_model('prm/GoodsLofModel')->get_lof_production_date($row[3], $row[0]);
                    $sku_num[$row[0]][$row[3]]['production_date'] = !empty($production_date) ? $production_date : $row[4];
                }
            }
            $i++;
        }
        fclose($file);
        if (!empty($sku_arr)) {
            $sku_arr = array_values($sku_arr);
        }

        return $i;
        // var_dump($sku_arr,$sku_num);die;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('gbk', 'utf-8', $val); 中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }

    function add_detail_goods($id, $data, $store_code, $is_add_log = 1) {
        $record = $this->get_row(array('purchaser_record_id' => $id));
        $record_code = $record['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $err_num = 0;
        foreach ($data as $d) {
            if ($d['num'] > 0) {
                $sku_num[$d['barcode']]['num'] = $d['num'];
                $sku_arr[] = $d['barcode'];
            }
        }
        $check_ret = $this->check_pftzd($record_code, $sku_arr, $sku_num, $err_num);
        if (!empty($check_ret)) {
            $str = '';
            foreach ($check_ret as $err) {
                foreach ($err as $k => $v) {
                    $str .= "条码" . $k . $v . "<br>";
                }
            }
            return $this->format_ret(-1, '', $str);
        }
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $data);
        //单据批次添加
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'purchase', $data);
        if ($ret['status']<1) {
            return $ret;
        }
        //增加明细
        $ret = load_model('pur/PurchaseRecordDetailModel')->add_detail_action($record['data']['record_code'], $data);
        if ($ret['status'] == '1' && $is_add_log == 1) {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "purchase_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * 采购通知单生成采购入库单
     */
    public function create_purchase_record($order_record, $type = "create_return_unfinish") {
        $record_code = $this->create_fast_bill_sn();
        $purchase_record = array();

        $purchase_record['record_code'] = $record_code;
        $purchase_record['relation_code'] = $order_record['record_code'];
        $purchase_record['supplier_code'] = $order_record['supplier_code'];
        $purchase_record['store_code'] = $order_record['store_code'];
        $purchase_record['rebate'] = $order_record['rebate'];
        $purchase_record['record_type_code'] = $order_record['pur_type_code'];
        $purchase_record['order_time'] = date('Y-m-d H:i:s');
        $purchase_record['record_time'] = date('Y-m-d H:i:s');
        $purchase_record['is_add_time'] = $order_record['is_add_time'];
        $purchase_record['is_notify_payment'] = $order_record['is_notify_payment'];
        $purchase_record['remark'] = $order_record['remark'];
        $this->begin_trans();
        try {
            $ret = $this->insert($purchase_record);
            $pid = $ret['data'];
            //按未完成数生成
            if ($type == "create_return_unfinish") {
                $sql = "select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,rebate,num,finish_num from pur_order_record_detail where record_code = '{$order_record['record_code']}'";
                $data = $this->db->get_all($sql);

                //通知单的数量生成退单时 为通知数
                foreach ($data as $key => $return_info) {
                    if ($return_info['num'] <= $return_info['finish_num']) {
                        unset($data[$key]);
                        continue;
                    }
                    $data[$key]['notice_num'] = $return_info['num'] - $return_info['finish_num'];
                    unset($data[$key]['finsih_num'], $data[$key]['num']);
                }
                if (empty($data)) {
                    throw new Exception('采购订单已经全部生成 ，不可再采购通知单');
                }
                $ret = load_model('pur/PurchaseRecordDetailModel')->add_detail_action($record_code, $data);
                if ($ret['status'] != 1) {
                    throw new Exception('退单明细保存失败');
                }
            }
            $this->update_num($purchase_record['record_code']);

            $this->commit();
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '新增', 'module' => "purchase_record", 'pid' => $pid, 'action_note' => "由采购通知单{$order_record['record_code']}生成");
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            return array('status' => 1, 'message' => '更新成功', 'data' => $pid);
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    //添加入库单数量
    function update_num($record_code) {
        //回写入库单主表通知数
        $sql = "UPDATE pur_purchaser_record p,(SELECT record_code,SUM(notice_num) AS total_notice_num FROM pur_purchaser_record_detail WHERE record_code = '{$record_code}') AS tmp SET p.num = tmp.total_notice_num WHERE p.record_code = tmp.record_code AND p.record_code = '{$record_code}';";
        return $this->db->query($sql);
    }

    //添加单据批次明细
    function add_detail_goods_inv($data) {
        foreach ($data as $value) {
            $pur_record_data = $this->get_by_field('record_code', $value['record_code']);
            $store_code = $pur_record_data['data']['store_code'];
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($value['pid'], $store_code, 'purchase', $data);
        }
        return $ret;
    }

    /**
     * 检查采购通知单是否存在
     * @param string $record_code
     * @return array
     */
    private function check_order_notice_code($record_code) {
        $sql = "SELECT * FROM pur_order_record WHERE record_code=:record_code";
        $sql_values = array(':record_code' => $record_code);
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }

    /**
     * 检查是否生成重复的入库单
     * @param string $record_code
     * @return array
     */
    private function check_repeat_pur($relation_code, $init_code) {
        $sql = "SELECT record_code,relation_code,init_code FROM pur_purchaser_record WHERE relation_code=:relation_code AND init_code=:init_code";
        $sql_values = array(':relation_code' => $relation_code, 'init_code' => $init_code);
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }

    /**
     * 功能描述     根据采购通知单创建采购入库单
     * @author     BaiSon PHP
     * @date       2016-03-10
     * @param      array $param
     *              array(
     *                  必选: 'relation_code','init_code','store_code'
     *                  可选: 'record_time','remark'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_pur_record_create($param) {
        if (!isset($param['relation_code']) || empty($param['relation_code'])) {
            return $this->format_ret(-10001, array('relation_code'), '缺少必填参数或必填参数为空');
        }
        if (!isset($param['init_code']) || empty($param['init_code'])) {
            return $this->format_ret(-10001, array('init_code'), '缺少必填参数或必填参数为空');
        }

        //判断采购通知单号是否存在
        $ret = $this->check_order_notice_code($param['relation_code']);
        if (empty($ret) || $ret['is_finish'] == '1') {
            return $this->format_ret('-1', $param['relation_code'], '该采购通知单不存在或已完成');
        }
        $ret1 = $this->check_repeat_pur($param['relation_code'], $param['init_code']);
        if (!empty($ret1)) {
            return $this->format_ret(-20001, $ret1['record_code'], "原单号：{$param['init_code']},已创建入库单");
        }

        //采购入库主单据信息
        $record = array();
        $record['order_record_id'] = $ret['order_record_id'];
        $record['relation_code'] = $param['relation_code'];
        $record['init_code'] = $param['init_code'];
        if (isset($param['remark']) && $param['remark'] != '') {
            $record['remark'] = trim($param['remark']);
        } else {
            $record['remark'] = '';
        }

        //创建业务日期
        if (empty($param['record_time'])) {
            $record['record_time'] = date('Y-m-d');
        } else if (strtotime($param['record_time']) == false) {
            return $this->format_ret(-1, array('record_time' => $param['record_time']), '日期格式错误');
        } else {
            $record['record_time'] = $param['record_time'];
        }

        $purchase = $this->api_create_purchase_record($record);
        return $purchase;
    }

    /**
     * 创建采购入库单
     * @param array $record 主单据信息
     * @param array $data
     * @return type
     */
    private function api_create_purchase_record($record) {
        $ret = load_model('pur/OrderRecordModel')->get_by_id($record['order_record_id']);
        $this->begin_trans();
        try {
            if (isset($ret['status']) && $ret['status'] == 1) {
                $bill_sn = $this->create_fast_bill_sn();
                //添加采购入库单主单据
                $ret['data']['relation_code'] = $record['relation_code'];
                $ret['data']['record_code'] = $bill_sn;
                $ret['data']['record_time'] = $record['record_time'];
                $ret['data']['record_type_code'] = $ret['data']['pur_type_code'];
                $ret['data']['init_code'] = isset($record['init_code']) ? $record['init_code'] : '';
                $ret['data']['remark'] = $record['remark'];
                unset($ret['data']['is_check']);
                $ret = $this->insert($ret['data']);
                if ($ret['status'] < 1) {
                    $this->rollback();
                    return $ret;
                }
                //回写执行状态
                $ret1 = load_model('pur/OrderRecordModel')->update_check('1', 'is_execute', $record['order_record_id']);
                if ($ret1['status'] < 1) {
                    $this->rollback();
                    return $ret1;
                }
                $log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未验收', 'action_name' => '新增', 'module' => "purchase_record", 'pid' => $ret['data'], 'action_note' => 'API调用生成');
                load_model('pur/PurStmLogModel')->insert($log);
                $this->commit();
                return $this->format_ret(1, $bill_sn, '创建成功');
            }
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => '创建失败:' . $e->getMessage());
        }
    }

    /**
     * 功能描述     更新采购入库单（明细提交）
     * @author     BaiSon PHP
     * @date       2016-03-14
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *                  必选: 'barcode_list'=>array(
     *                        'barcode','num','lof_no','production_date'
     *                         )
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_pur_record_update($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, array('record_code'), '缺少必填参数或必填参数为空');
        }
        $check_key = array('barcode', 'num');
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 1) {
            $check_key = array('barcode', 'num', 'lof_no', 'production_date');
        }
        $purchase_detail = json_decode($param['barcode_list'], true);
        if (empty($purchase_detail) || !is_array($purchase_detail)) {
            return $this->format_ret(-10005, '', '明细数据解析失败');
        }
        //检查明细是否为空
        $find_data = $this->api_check_detail($purchase_detail, $check_key);
        if ($find_data['status'] != 1) {
            return $find_data;
        }

        $purchase_record = $this->get_by_field('record_code', $param['record_code']);
        $purchaser_record_id = $purchase_record['data']['purchaser_record_id'];
        $store_code = $purchase_record['data']['store_code'];
        $relation_code = $purchase_record['data']['relation_code'];
        $data = $this->api_get_goods_detail($purchase_detail, $relation_code);
        $this->is_check_notice_num = 0;

        $ret = $this->add_detail_goods($purchaser_record_id, $data, $store_code);
        if ($ret['status'] != 1) {
            $ret['status'] = -1;
        } else {
            $ret['message'] = '更新成功';
        }
        $ret['data'] = '';
        return $ret;
    }

    /**
     * 获取商品明细信息
     */
    private function api_get_goods_detail($purchase_detail, $relation_code) {
        $data = array();
        foreach ($purchase_detail as $key => $val) {
            $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.purchase_price FROM goods_sku b
                    INNER JOIN  base_goods g ON g.goods_code = b.goods_code
                    WHERE b.barcode ='{$val['barcode']}' GROUP BY b.barcode ";
            $goods_data = $this->db->get_row($sql);
            $sql1 = "SELECT goods_code,spec1_code,spec2_code,sku,price,rebate,money FROM pur_order_record_detail WHERE record_code='{$relation_code}' AND sku='{$goods_data['sku']}'";
            $detail_data = $this->db->get_row($sql1);
            $data[] = array_merge($goods_data, $detail_data, $purchase_detail[$key]);
        }
        return $data;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($purchase_detail, $check_key) {
        $err_data = array();
        foreach ($purchase_detail as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$v])) {
                    $err_data[$key][] = $v;
                }
            }
        }
        if (!empty($err_data)) {
            return $this->format_ret(-1, $err_data, "明细数据不能为空");
        }
        return $this->format_ret(1);
    }

    /**
     * 功能描述     验收采购入库单（影响库存）
     * @author     BaiSon PHP
     * @date       2016-03-14
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_pur_record_accept($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', 'API_RETURN_MESSAGE_10001');
        }
        $record_code_arr = json_decode($param['record_code'], true);
        if (!is_array($record_code_arr)) {
            $record_code_arr = array($param['record_code']);
        }
        $msg = array();
        foreach ($record_code_arr as $val) {
            $ret = $this->checkin($val);
            if ($ret['status'] == 1) {
                $ret['message'] = '验收成功';
                //日志
                $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '验收入库', 'module' => "purchase_record", 'pid' => $ret['data']);
                load_model('pur/PurStmLogModel')->insert($log);
            } else {
                $ret['status'] = -1;
            }

            $msg[] = array('status' => $ret['status'], 'data' => $val, 'message' => $ret['message']);
        }
        return $this->format_ret(1, $msg);
    }
    
    //获取单据号
    function get_record_code($id){
        $sql = "select record_code from pur_purchaser_record where purchaser_record_id = {$id}";
        return $this->db->get_row($sql);
    }

    public function add_detail($param){
        $ret = $this->add_detail_goods($param['record_id'],$param['detail'],$param['store_code']);
        return $ret;
    }
}
