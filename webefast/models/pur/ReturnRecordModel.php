<?php

/**
 * 采购入库单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');
require_lib('util/oms_util', true);
class ReturnRecordModel extends TbModel {

    function get_table() {
        return 'pur_return_record';
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
            '总退货数' => 'sum_num', //通知退货数、#实际退货数
            '总金额' => 'sum_money',
            '总退货通知数' => 'enotice_num',
            '备注' => 'remark',
            '总差异数' => 'diff_num',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '供应商联系人' => 'contact_person',
            '供应商手机' => 'mobile',
            '供应商电话' => 'tel',
            '供应商地址' => 'address',
            '退货类型' => 'record_type_name',
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
        //#、#、#、#商品属性、#生成周期、#商品描述、#、#蓝位号、#库位代码、#库位、
        'detail' => array(
            array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '出厂名称' => 'goods_produce_name',
                '规格1' => 'spec1_name',
                '规格2' => 'spec2_name',
                '标准进价' => 'price',
                '单价' => 'dp_price', //需要完善
                '折扣' => 'rebate',
                '批发价' => 'pf_price',
                '实际退货数' => 'num',
                '金额' => 'money',
                '通知退货数' => 'enotice_num',
                '差异数' => 'diff_num',
                '商品条形码' => 'barcode',
                '库位' => 'shelf_code',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品重量' => 'weight',
                '吊牌价' => 'goods_dp_price',
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
        $r['record']['record_type_name'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code'=>$r['record']['record_type_code']));
        $sql = "select d.pid
                ,d.goods_code,d.num,d.rebate,d.sku,if(r3.price=0,g.sell_price,r3.price) as goods_dp_price,(d.price*d.rebate) as dp_price,d.price,d.enotice_num,( d.enotice_num - d.num) as diff_num,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.trade_price as pf_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code,g.weight,g.year_name,g.brand_name,g.season_name,g.category_name
                ,r3.spec1_name,r3.spec2_name,r3.barcode
                from pur_return_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
        $store_code = $r['record']['store_code'];
        $i = 1;


        foreach ($r['detail'] as &$val) {
            $val['sort_num'] = $i;
            $val['price'] = number_format($val['price'], 3);
            $val['dp_price'] = number_format($val['dp_price'], 3);
            $shelf_code_arr = load_model('prm/GoodsShelfModel')->get_goods_shelf($store_code, $val['sku']);
            $val['shelf_code'] = implode(",", $shelf_code_arr);
            $i++;
        }

        // 数据替换
        //   $this->print_data_escape($r['record'], $r['detail']);
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
        //更新状态
        $this->update_print_status($id);
        // 更新状态
        //$this->print_update_status($r['record']['deliver_record_id'], 'deliver');
        return $d;
    }
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $ret = $this->get_by_id($id);
        $r['record'] = $ret['data'];
        $r['record']['print_time'] = date('Y-m-d H:i:s');
        $r['record']['print_user_name'] = CTX()->get_session('user_name');
        $r['record']['record_type_name'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code'=>$r['record']['record_type_code']));
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['record_code']);
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.rebate,d.sku,if(r3.price=0,g.sell_price,r3.price) as dp_price,(d.price*d.rebate) as price_1,d.price,d.enotice_num,( d.enotice_num - d.num) as diff_num,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.trade_price as pf_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code,g.weight,g.year_name,g.brand_name,g.season_name,g.category_name
                ,r3.spec1_name,r3.spec2_name,r3.barcode
                from pur_return_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
            $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));
            $sku_array = array();
            $shelf_code_arr = array();
            $i = 1;
            $money = 0;
            foreach ($r['detail'] as $key => &$detail) {//合并同一sku
                $detail['sort_num'] = $i;
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
            $i =1;
            $record['supplier_name'] = oms_tb_val('base_supplier', 'supplier_name', array('supplier_code'=>$record['supplier_code']));
            $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$record['store_code']));
            $record['diff_num'] = $record['enotice_num'] - $record['num'];
            $record['print_time'] = date('Y-m-d H:i:s');
            $record['print_user'] = CTx()->get_session('user_name');
            $record['contact_person'] = oms_tb_val('base_supplier', 'contact_person', array('supplier_code'=>$record['supplier_code']));
            $record['mobile'] = oms_tb_val('base_supplier', 'mobile', array('supplier_code'=>$record['supplier_code']));
            $record['tel'] = oms_tb_val('base_supplier', 'tel', array('supplier_code'=>$record['supplier_code']));
            $record['address'] = oms_tb_val('base_supplier', 'address', array('supplier_code'=>$record['supplier_code']));
            $record['record_type_name'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code'=>$record['record_type_code']));
            foreach ($detail as &$v) {
                    $v['sort_num'] = $i;
                    $v['price'] = number_format($v['price'], 3);
                    $v['price_1'] = number_format($v['price_1'], 3);
//                    $v['dp_price'] = number_format(($v['price']*$v['rebate']), 3);
                    $v['diff_num'] = $v['enotice_num'] - $v['num'];
                    $i++;
            }
    }
    
    //更新打印状态
    public function update_print_status ($return_record_id) {
        $sql = "SELECT is_print_record FROM pur_return_record WHERE return_record_id = :return_record_id ";
        $data = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '采购退货单不存在');
        }
        //添加日志
        $this->add_print_log($return_record_id);
        //更新状态
        $this->update_exp( 'pur_return_record', array('is_print_record' => 1 ), array('return_record_id' => $return_record_id ));
    }
    
    //打印日志记录
    public function add_print_log($return_record_id) {
        // 增加打印日志
        $sql = "SELECT is_check,is_store_out FROM pur_return_record WHERE return_record_id = :return_record_id  ";
        $d = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        $sure_status = $d['is_check'] == 1 ? "确认" : "未确认";
        $finish_status = $d['is_store_out'] == 0 ? '未出库' : '出库';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '打印', 'module' => "return_record", 'pid' => $return_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }
    
    //检查是否打印
    public function check_is_print ($return_record_id) {
        $sql = "SELECT is_print_record FROM pur_return_record WHERE return_record_id = :return_record_id ";
        $data = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印采购退货单，是否继续打印？') : $this->format_ret(1, '', '');
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
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //$sql_join = "";
        $sql_main = "FROM {$this->table} rl  LEFT JOIN pur_return_record_detail r2 on rl.record_code = r2.record_code LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code  WHERE 1";
        $sql_values = array();
        $filter_supplier_code = isset($filter['supplier_code']) ? $filter['supplier_code'] : null;
        $sql_main .= load_model('base/SupplierModel')->get_sql_purview_supplier('rl.supplier_code', $filter_supplier_code);
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);

        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND (rl.remark LIKE :remark )";
            $sql_values[':remark'] = '%'.$filter['remark'] . '%';
        }
        // 退货通知单号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        // 原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.init_code LIKE :init_code )";
            $sql_values[':init_code'] = $filter['init_code'] . '%';
        }
        // 下单时间
        if (isset($filter['order_time_start']) && $filter['order_time_start'] != '') {
            $sql_main .= " AND (rl.order_time >= :order_time_start )";
            $sql_values[':order_time_start'] = $filter['order_time_start'];
        }
        if (isset($filter['order_time_end']) && $filter['order_time_end'] != '') {
            $sql_main .= " AND (rl.order_time <= :order_time_end )";
            $sql_values[':order_time_end'] = $filter['order_time_end'];
        }
        // 出库时间
        if (isset($filter['out_time_start']) && $filter['out_time_start'] != '') {
            $sql_main .= " AND (rl.out_time >= :out_time_start )";
            $sql_values[':out_time_start'] = $filter['out_time_start'];
        }
        if (isset($filter['out_time_end']) && $filter['out_time_end'] != '') {
            $sql_main .= " AND (rl.out_time <= :out_time_end )";
            $sql_values[':out_time_end'] = $filter['out_time_end'];
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }
        //退货类型
        if (isset($filter['record_type_code']) && $filter['record_type_code'] !== '') {
            $sql_main .= " AND (rl.record_type_code = :record_type_code )";
            $sql_values[':record_type_code'] = $filter['record_type_code'];
        }
        // 单据状态
        if (isset($filter['is_store_out']) && $filter['is_store_out'] != '') {
            $store_code_arr = explode(',', $filter['is_store_out']);
            if (!empty($store_code_arr)) {
                $sql_main .= " AND (";
                foreach ($store_code_arr as $key => $value) {
                    $param_store = 'param_store' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.is_store_out = :{$param_store} ";
                    } else {
                        $sql_main .= " or rl.is_store_out = :{$param_store} ";
                    }

                    $sql_values[':' . $param_store] = $value;
                }
                $sql_main .= ")";
            }
        }
        //商品
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //导出明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'return_record_list') {
            //$sql_main .= " order by record_time desc";
            //$select = "rl.*,r2.refer_price,r2.price,"
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$filter['user_id']);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['money'] = round($value['money'], 2);
            $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
            $data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
            $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $value['supplier_code'], 'supplier_name');
            $data['data'][$key]['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
            $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $value['record_type_code'], 'record_type_name');
            $data['data'][$key]['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
            if ($status['status'] != 1) {
                $data['data'][$key]['money'] = '****';
            }
        }
//        filter_fk_name($data['data'], array('store_code|store','adjust_type|record_type'));
        //dump($data,1);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('return_record_id' => $id));
        $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier', 'supplier_code', $data['data']['supplier_code'], 'supplier_name,contact_person,mobile,tel,address');

        $data['data']['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
        $data['data']['contact_person'] = isset($arr['data']['contact_person']) ? $arr['data']['contact_person'] : '';
        $data['data']['mobile'] = isset($arr['data']['mobile']) ? $arr['data']['mobile'] : '';
        $data['data']['tel'] = isset($arr['data']['tel']) ? $arr['data']['tel'] : '';
        $data['data']['address'] = isset($arr['data']['address']) ? $arr['data']['address'] : '';

        $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $data['data']['record_type_code'], 'record_type_name');
        $data['data']['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('sys_user', 'user_code', $data['data']['user_code'], 'user_name');
        $data['data']['user_name'] = isset($arr['data']['user_name']) ? $arr['data']['user_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_store', 'store_code', $data['data']['store_code'], 'store_name');
        $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';

        $data['data']['sum_num'] = 0;
        $data['data']['sum_money'] = 0;
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        $result = load_model('pur/ReturnRecordDetailModel')->get_by_record_code($data['data']['record_code']);
        if (!empty($result)) {
            $sum_num = 0;
            $sum_money = 0;
            $sum_differ = 0;
            $notice_num = 0;
            foreach ($result['data'] as $k => $v) {
                $differ = $v['enotice_num'] - $v['num'];
                $sum_num += $v['num'];
                $sum_money += $v['money'];
                $sum_differ += $differ;
                $notice_num +=$v['enotice_num'];
            }
            $data['data']['sum_num'] = round($sum_num, 2);
            $data['data']['sum_money'] = number_format($sum_money, 3);
            $data['data']['diff_num'] = $sum_differ;
            $data['data']['enotice_num'] = $notice_num;
        }
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

    function delete($return_record_id) {
        $sql = "select * from {$this->table} where return_record_id = :return_record_id";
        $data = $this->db->get_row($sql, array(":return_record_id" => $return_record_id));
        if ($data['is_store_out'] == 1) {
            return $this->format_ret('-1', array(), '单据已经出库，不能删除！');
        }
        $ret = parent::delete(array('return_record_id' => $return_record_id));
        $this->db->create_mapper('pur_return_record_detail')->delete(array('pid' => $return_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $return_record_id, 'order_type' => 'pur_return'));
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
        $sql = "select return_record_id  from {$this->table}   order by return_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['return_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "TH" . date("Ymd") . add_zero($djh);
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
        $data['remark'] = str_replace(array("\r\n", "\r", "\n"), '', $data['remark']);
        if (!isset($where['return_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_ID_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', '', 'RECORD_ERROR');
        }
        //在未刷新单据验收状态时，编辑信息会传递record_time参数，所以根据record_time作为判断依据。如果只修改了备注则不报错。
        if(1==$result['data']['is_store_out']&&isset($data['record_time'])){
            $check_field=array('record_time','supplier_code','store_code','record_type_code');
            foreach($check_field as $val){
                if($result['data'][$val]!==$data[$val]){
                    return $this->format_ret(false,array(),'单据已经验收,不能修改!');
                }
            }
        }
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
        $ret = parent:: update(array('is_store_out' => $active), array('return_record_id' => $id));
        return $ret;
    }

    function update_check_new($active, $field, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }

        $details = load_model('pur/ReturnRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'pur_return');
     
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }
 
        $ret = parent:: update(array($field => $active), array('return_record_id' => $id));
        return $ret;
    }
    
    //检查差异数
    function check_diff_num ($record_code) {
        $record = $this->get_row(array('record_code' => $record_code));
        $id = $record['data']['return_record_id'];
        //检查是否出库
        if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
            return $this->format_ret(false, array(), 'RETURN_RECORD_ERROR_STORE_OUT');
        }
        if ($record['data']['relation_code'] == '') {
            return $this->format_ret(1, array(), '');
        }
        $data = $this->db->get_row("SELECT SUM(num) AS finish_num,SUM(enotice_num) AS enotice_num FROM `pur_return_record_detail` WHERE pid=:pid", array(':pid'=>$id));
        if ($data['enotice_num']>=0 && $data['finish_num'] == 0) {
            return $this->format_ret(2, array(), '是否确认整单验收？ ');
        }
        $ret = $this->db->get_row("SELECT record_code FROM pur_return_record_detail WHERE pid = :pid AND num != enotice_num", array(':pid'=>$id));
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
     * $accept_type 1:按业务日期验收
     * @return array
     */
    function checkout($record_code, $record = array(), $details = array(), $ret_lof_details = array(), $check, $force_negative_inv = 0, $accept_type = 0) {
        //检查调整单状态是否为已出库
        if (empty($record)) {
            $record = $this->get_row(array('record_code' => $record_code));
        }
        $id = $record['data']['return_record_id'];
        if (empty($details)) {
            $details = load_model('pur/ReturnRecordDetailModel')->get_all(array('pid' => $id));
        }
        if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
            return $this->format_ret(false, array(), 'RETURN_RECORD_ERROR_STORE_OUT');
        }

        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        $this->begin_trans();
        require_model('prm/InvOpModel');
        if (!$record['data']['relation_code']) {
            $sql = "select * from b2b_lof_datail where order_code=:order_code AND order_type=:order_type";
            $ret_lof_details['data'] = $this->db->get_all($sql,array(':order_code'=>$record_code,':order_type'=>'pur_return'));
     
            if (empty($ret_lof_details['data'])) {
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
            }
        } else {
            //释放通知单锁定
            $ret_set_lof = load_model('stm/GoodsInvLofRecordModel')->set_lof_datail($record['data']['relation_code'], 'pur_return_notice', $details['data'], $check);
            if ($ret_set_lof['status'] < 0) {
                $this->rollback();
                return $this->format_ret('-1', '', '找不到通知单指定商品信息');
            }
            $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid_num($id, 'pur_return');
            if (empty($ret_lof_details['data'])) {
                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $record['data']['store_code'], 'pur_return', $ret_set_lof['data']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }

            //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'pur_return');
            $sql = "select * from b2b_lof_datail where order_code=:order_code AND order_type=:order_type";
            $ret_lof_details['data'] = $this->db->get_all($sql,array(':order_code'=>$record_code,':order_type'=>'pur_return'));
     
            if (empty($ret_lof_details['data'])) {
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
            }
            $record_tz = load_model('pur/ReturnNoticeRecordModel')->get_row(array('record_code' => $record['data']['relation_code']));
            if ($record_tz['status'] <> 'op_no_data') {
                //释放锁定通知单库存
                $ret_lof_details_tz = $ret_lof_details['data'];
                foreach ($ret_lof_details_tz as $k => $v) {
                    $ret_lof_details_tz[$k]['occupy_type'] = '1';
                    if ($v['num'] == 0) {
                        unset($ret_lof_details_tz[$k]);
                    }
                }
                if (empty($ret_lof_details_tz)) {
                    $this->rollback();
                    return $this->format_ret('-1', '', '数据不能为空');
                }

                $invobj = new InvOpModel($record['data']['relation_code'], 'pur_return_notice', $record_tz['data']['store_code'], 5, $ret_lof_details_tz);
                $ret = $invobj->adjust();

                if ($ret['status'] != 1) {
                    $this->rollback(); //事务回滚
                    return $ret;
                }
                //实际退货数都是0 以通知数代替 否则就以有实际退货数的商品验收
                $i = 0;
                foreach ($details['data'] as $value) {
                    $i += $value['num'];
                }
                foreach ($details['data'] as $k=>$detail) {
                    if ($i == 0) {
                        $details['data'][$k]['num'] = $detail['enotice_num'];
                    }elseif ($i > 0 && $detail['num']==0) {
                        unset($details['data'][$k]);
                    }
                }
            } else {
                return $this->format_ret('-1', '', '没有对应通知数据');
            }
        }
        if ($force_negative_inv == 0) {
            require_model('prm/InvOpModel');
            //校验可用库存
            $invobj = new InvOpModel($record['data']['record_code'], 'pur_return', $record['data']['store_code'], 0, $ret_lof_details['data']);
            //获取inv不足的sku
            $insufficient_sku = $invobj->get_usable_inv($ret_lof_details['data']);
            $err_msg = array();
            if (!empty($ret_lof_details['data'])) {
                foreach ($details['data'] as $val) {
                    if (array_key_exists($val['sku'], $insufficient_sku)) {
                        $barcode = load_model('goods/SkuCModel')->get_barcode($val['sku']);
                        $spec1_name = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=>$val['spec1_code']));
                        $spec2_name = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=>$val['spec2_code']));
                        $short_inv = $val['num'] - $insufficient_sku[$val['sku']];
                        $err_msg[] = array('barcode'=>$barcode,'spec1_name'=>$spec1_name,'spec2_name'=>$spec2_name,'short_inv'=>$short_inv);
                    }
                }
                if (!empty($err_msg)) {
                    $arr = array('goods_spec1', 'goods_spec2');
                    $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
                    $goods_spec1_rename = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '规格1';
                    $goods_spec2_rename = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '规格2';
                    $fail_top = array('商品条码', $goods_spec1_rename, $goods_spec2_rename,'库存差异数量');
                    $message = "商品可用库存不足，验收失败";
                    //点扫描验收 原来参数控制不下载信息，现在开放
//                    if ($check == 0) {
//                        return $this->format_ret('-1', '', $message);
//                    }
                    $file_name = $this->create_import_fail_file($fail_top, $err_msg);
//                    $message .= "，<br /><a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >导出错误数据</a><br/> ";
                    $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                    $message .= "，<br /><a target=\"_blank\" href=\"{$url}\" >导出错误数据</a><br/> ";
                    return $this->format_ret('-1', '', $message);
                }
            }
        }
        foreach ($ret_lof_details['data'] as $k => $v) {
            if ($v['num'] == 0) {
                unset($ret_lof_details['data'][$k]);
            }
        }
        if (empty($ret_lof_details['data'])) {
            $this->rollback();
            return $this->format_ret('-1', '', '数据不能为空');
        }
        //扣减库存
        $invobj = new InvOpModel($record['data']['record_code'], 'pur_return', $record['data']['store_code'], 2, $ret_lof_details['data']);
        if ($force_negative_inv == 1) {
            $invobj->force_negative_inv(); //强制允许负库存
        }
        $ret = $invobj->adjust();

        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        //出库时间
        $out_time = date('Y-m-d H:i:s');
        $record_time = ($accept_type == 1) ? $record['data']['record_time'] : date('Y-m-d');
        $ret = parent:: update(array('is_store_out' => 1,'is_check' => 1,'out_time' =>$out_time, 'record_time' =>$record_time), array('record_code' => $record_code));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        //按业务日期验收
        if ($accept_type == 1) {
            $ret = $this->update_exp('b2b_lof_datail', array('order_date' => $record['data']['record_time']), array('order_code' => $record_code, 'order_type' => 'pur_return'));
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $this->format_ret(-1, '', '更新失败！');
            }
        }
        $status = $this->update_finish_num($id, $record['data']['record_code'], $ret_lof_details['data']);
        //计算金额
        $this->set_money($id);
        //回写主表数量金额
        $ret = load_model('pur/ReturnRecordDetailModel')->mainWriteBack($id);
        if (isset($record['data']['relation_code']) && $record['data']['relation_code'] <> '' && isset($ret_lof_details['data'])) {
            //回写采购订单完成数
            $ret = load_model('pur/ReturnNoticeRecordDetailModel')->update_finish_num($record['data']['relation_code'], $ret_lof_details['data']);
        }
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交
        return $this->format_ret(1, array('return_record_id'=>$id), 'SUCCESS_YS_STORE_OUT');
        //return $this->format_ret('SUCCESS_STORE_OUT');
    }

    //设置完成数量
    function update_finish_num($id, $recode_code, $lof_details_data) {
        $sql = "update pur_return_record_detail,(select sku,sum(num) as num from b2b_lof_datail where order_code='{$recode_code}' and order_type='pur_return' GROUP BY sku )
    	as sku_num ";
        $sql.= " set pur_return_record_detail.num = sku_num.num";
        $sql.= " where  pur_return_record_detail.sku=sku_num.sku and pur_return_record_detail.pid = {$id}";
        $ret = $this->db->query($sql);
        return $ret;
    }

    //计算金额
    function set_money($record_id) {
        $sql = " update pur_return_record_detail set money = price * rebate * num  where pid ='{$record_id}'";
        return $this->db->query($sql);
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        //     	var_dump($id);var_dump($file);var_dump($is_lof);exit;
        $ret = $this->get_row(array('return_record_id' => $id));
        if (empty($ret)) {
            return array(-1, '', '采购退货单不存在！');
        }
        $store_code = $ret['data']['store_code'];
        $relation_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $error_msg = array();
        $err_num = 0;
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $is_lof = $lof_manage['lof_status'];
        //未开启批次导入库存方法
        $barcode_arr = array();
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $barcode_arr, $sku_num);
        } else {
            $num = $this->read_csv_sku($file, $barcode_arr, $sku_num);

            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $barcode_str = implode("','", $barcode_arr);
            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
                    . " g.sku in ('$barcode_str') group by l.sku";
            $sku_data = $this->db->get_all($sql);

            $sql_moren = "select lof_no,production_date from goods_lof order by id ASC";
            $moren = $this->db->get_row($sql_moren);
            $sku_data_new = array();
            foreach ($sku_data as $key2 => $data) {
                $sku_data_new[$data['barcode']]['production_date'] = $data['production_date'];
                $sku_data_new[$data['barcode']]['lof_no'] = $data['lof_no'];
            }
            $new_sku_num = $sku_num;
            $sku_num = array();
            foreach ($barcode_arr as $barcode) {
                if (array_key_exists($barcode, $sku_data_new)) {
                    $sku_num[$barcode][$sku_data_new[$sku]['lof_no']]['num'] = $new_sku_num[$barcode]['num'];
                    $sku_num[$barcode][$sku_data_new[$sku]['lof_no']]['purchase_price'] = $new_sku_num[$barcode]['purchase_price'];
                    $sku_num[$barcode][$sku_data_new[$sku]['lof_no']]['lof_no'] = $sku_data_new[$barcode]['lof_no'];
                    $sku_num[$barcode][$sku_data_new[$sku]['lof_no']]['production_date'] = $sku_data_new[$barcode]['production_date'];
                } else {
                    $sku_num[$barcode][$moren['lof_no']]['num'] = $new_sku_num[$barcode]['num'];
                    $sku_num[$barcode][$moren['lof_no']]['purchase_price'] = $new_sku_num[$barcode]['purchase_price'];
                    $sku_num[$barcode][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    $sku_num[$barcode][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }
        $import_count = count($barcode_arr);
        $check_ret = $this->check_cgttzd($relation_code, $barcode_arr, $sku_num, $err_num);
        if (!empty($check_ret)) {
            $error_msg = $check_ret;
        }
        if (!empty($sku_num) && !empty($barcode_arr)) {
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.purchase_price,g.sell_price from
    		goods_sku b
    		inner join base_goods g ON g.goods_code = b.goods_code
    		where b.barcode in({$barcode_str}) ";
            $detail_data = $this->db->get_all($sql,$sql_values); //sell_price
            $detail_data_lof = array();
            $temp = array();
            //     	if($is_lof == '1'){
            foreach ($detail_data as $key => $val) {
                foreach ($sku_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        $val['pici_lof'] = '1';
                        if ($v1['purchase_price']) {
                            $val['purchase_price'] = $v1['purchase_price'];
                        }
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
//                $import_count = count($detail_data_lof);
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'pur_return', $detail_data_lof);
            if ($ret['status']<1) {
                    return $ret;
             }
            //入库单明细添加
            $ret = load_model('pur/ReturnRecordDetailModel')->add_detail_action($id, $detail_data_lof);

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "return_record", 'pid' => $id);
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
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }
    
    //错误信息详情导出
    function create_import_fail_file($fail_top, $error_msg) { //var_dump($fail_top, $error_msg);die;
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
//            $key = array_keys($val);
            $val_data = array($val['barcode']."\t" , $val['spec1_name']."\t" ,$val['spec2_name']."\t" ,$val['short_inv']);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("store_out_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array("\t" . $key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("store_out_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
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
                    //     				$keypi = $row[0].'_'.$row[2];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[3]]['purchase_price'] = $row[2];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[3];
                    //	$sku_num[$row[0]][$row[3]]['production_date'] = $row[4];
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
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }

    function check_cgttzd($pftzd_code, &$sku_arr, &$sku_num, &$err_num) {
        $err_data = '';
        if ($pftzd_code) {
            $sql = "select * from pur_return_notice_record_detail where record_code = :record_code";
            $pftzd_record = $this->db->get_all($sql, array(":record_code" => $pftzd_code));
            if (!empty($pftzd_record)) {
                $new_pftzd = array();
                foreach ($pftzd_record as $record) {
                    $barcode = $this->db->get_value("select barcode from goods_sku where sku = :sku", array(':sku' => $record['sku']));
                    $new_pftzd[$barcode] = $record['num'];
                }
                foreach ($sku_num as $key => $sku) {
                    $flag = true;
                    if (array_key_exists($key, $new_pftzd)) {
                        if ($sku_num[$key]['num'] > $new_pftzd[$key]) {
                            $err_data[] = array($key => '添加商品数量不能超过采购退货通知单通知数！');
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                        $err_data[] = array($key => '关联采购退货通知单中不存在该条码！');
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

    function add_detail_goods($id, $data, $store_code ,$is_add_log = 1 ) {
        $ret = $this->get_row(array('return_record_id' => $id));
        $pftzd_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $err_num = 0;
        foreach ($data as $d) {
            if ($d['num'] > 0) {
                $sku_num[$d['barcode']]['num'] = $d['num'];
                $sku_arr[] = $d['barcode'];
            }
        }
        $check_ret = $this->check_cgttzd($pftzd_code, $sku_arr, $sku_num, $err_num);
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'pur_return', $data);
         if ($ret['status']<1) {
                return $ret;
         }
        //增加明细
        $ret = load_model('pur/ReturnRecordDetailModel')->add_detail_action($id, $data);
        if ($ret['status'] == '1' && $is_add_log == 1) {
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "return_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    function create_return_record($data) {
        //获取采购计划订单主数据
        if (empty($data['selections'][0])) {
            return $this->format_ret(-1, '', '采购退货单id为空');
        }
        $return_notice_id = $this->db->get_value("select pid from pur_return_notice_record_detail where return_notice_record_detail_id = {$data['selections'][0]}");
        $ret = load_model('pur/ReturnNoticeRecordModel')->get_by_id($return_notice_id);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', '查询不到采购通知单');
        }
        $relation_code = $ret['data']['record_code'];
        $pici_arr = array();
        $this->begin_trans();
        try {
            $bill_sn = $this->create_fast_bill_sn();
            //添加采购订单主表
            $ret['data']['relation_code'] = $relation_code;
            $ret['data']['record_code'] = $bill_sn;
            $ret['data']['record_time'] = date('Y-m-d H:i:s');
            unset($ret['data']['is_sure'], $ret['data']['num'], $ret['data']['money']);
            $ret = load_model('pur/ReturnRecordModel')->insert($ret['data']);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $return_record_id = $ret['data'];

            $ret_detail = load_model('pur/ReturnNoticeRecordDetailModel')->get_select_ids($data['selections']);
            $data['selections'] = $ret_detail['data'];

            foreach ($data['selections'] as $key => $value) {
                if ($value['diff_num'] == 0) {
                    unset($data['selections'][$key]);
                    continue;
                }

                $data['selections'][$key]['pid'] = $return_record_id;
                $data['selections'][$key]['relation_code'] = $data['selections'][$key]['record_code'];
                $data['selections'][$key]['record_code'] = $bill_sn;
                $data['selections'][$key]['purchase_price'] = $data['selections'][$key]['price'];
                unset($data['selections'][$key]['lastchanged'], $data['selections'][$key]['id'], $data['selections'][$key]['notice_record_detail_id'], $data['selections'][$key]['is_finish'], $data['selections'][$key]['is_sure']);
                switch ($data['exe_type']) {
                    case "1":
                        $data['selections'][$key]['num'] = $data['selections'][$key]['diff_num']; //入库数

                        $pici = load_model('stm/GoodsInvLofRecordModel')->get_all(array(
                            'pid' => $return_notice_id,
                            'order_type' => 'pur_return_notice',
                            'sku' => $value['sku']
                        ));

                        $data['selections'][$key]['pice'] = $pici['data'];
                        //$request['selections'][$key]['num_flag'] = '1';//入库为0标志
                        $data['selections'][$key]['enotice_num'] = $value['diff_num']; //通知数
                        $data['selections'][$key]['money'] = $value['num'] * $value['price'] * $value['rebate']; //金额
                        break;
                    case "2":
                        $data['selections'][$key]['num'] = 0; //入库数
                        $pici = load_model('stm/GoodsInvLofRecordModel')->get_row(array(
                            'pid' => $return_notice_id,
                            'order_type' => 'pur_return_notice',
                            'sku' => $value['sku']
                        ));
                        $data['selections'][$key]['lof_no'] = $pici['data']['lof_no'];
                        $data['selections'][$key]['production_date'] = $pici['data']['production_date'];
                        //$request['selections'][$key]['money'] = 0 ;
                        $data['selections'][$key]['num_flag'] = '1'; //入库为0标志
                        $data['selections'][$key]['enotice_num'] = $value['diff_num']; //通知数
                        break;
                }
            }
            if ($data['exe_type'] == '1') {
                //$pici_arr = $request['selections'];
                //转化成批次数据
                $pici_arr = array();
                foreach ($data['selections'] as $key1 => $value1) {
                    $num = $value1['num'];
                    $p = $value1['pice'];
                    unset($value1['pice']);
                    foreach ($p as $v) {

                        if ($num >= intval($v['init_num'])) {
                            $value1['num'] = 0;
                            $value1['diff_num'] = $v['init_num'];
                        } else {
                            $value1['num'] = 0;
                            $value1['diff_num'] = $num;
                        }
                        $value1['lof_no'] = $v['lof_no'];
                        $value1['production_date'] = $v['production_date'];
                        $value1['occupy_type'] = 0;
                        $pici_arr[] = $value1;
                        $num = $num - $v['init_num'];
                        if ($num <= 0) {
                            break;
                        }
                    }
                }
            }
            if ($data['exe_type'] == '2') {
                $pici_arr = $data['selections'];
            }
            $ret = load_model('pur/ReturnRecordDetailModel')->add_detail_action($return_record_id, $pici_arr);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            //回写执行状态
            $ret1 = load_model('pur/ReturnNoticeRecordModel')->update_check('1', 'is_execute', $return_notice_id);
            if ($ret1['status'] < 1) {
                $this->rollback();
                return $ret1;
            }
            //日志
            $action_note = "由采购退货通知单" . $relation_code . "生成";
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '通知单生成采购退货单', 'module' => "return_record", 'action_note' => $action_note, 'pid' => $return_record_id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
            return $this->format_ret(1, array('return_record_id' => $return_record_id, 'bill_sn' => $bill_sn), '');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => '保存失败:' . $e->getMessage());
        }
    }

    public function create_return_record_new($return_notice_record, $type = "create_return_unfinish") {
        $record_code = $this->create_fast_bill_sn();
        $return_record = array();
        $return_record['record_code'] = $record_code;
        $return_record['order_time'] = date('Y-m-d H:i:s');
        $return_record['record_time'] = date('Y-m-d H:i:s');
        $return_record['relation_code'] = $return_notice_record['record_code'];
        $return_record['record_type_code'] = $return_notice_record['record_type_code'];
        $return_record['supplier_code'] = $return_notice_record['supplier_code'];
        $return_record['store_code'] = $return_notice_record['store_code'];
        $return_record['rebate'] = $return_notice_record['rebate'];
        $return_record['remark'] = $return_notice_record['remark'];
        $this->begin_trans();
        try {
            $ret = $this->insert($return_record);
            $pid = $ret['data'];
            //按未完成数生成
            if ($type == "create_return_unfinish") {
                $data = load_model('pur/ReturnNoticeRecordDetailModel')->get_by_record_code($return_notice_record['record_code']);
                $data = $data['data'];

                $detail_info = array();
                $b2b_detail = array();
                //通知单的数量生成退单时 为通知数
                foreach ($data as $key => $return_info) {
                    $pici_info = load_model('stm/GoodsInvLofRecordModel')->get_all(array(
                        'pid' => $return_notice_record['return_notice_record_id'],
                        'order_type' => 'pur_return_notice',
                        'sku' => $return_info['sku']
                    ));
                    $sku_arr = array();
                    foreach ($pici_info['data'] as $pici) {
                        if ($pici['init_num'] <= $pici['fill_num']) {
                            continue;
                        }
                        unset($pici['id'], $pici['fill_num'], $pici['pid'], $pici['order_type'], $pici['order_date'], $pici['create_time'], $pici['lastchanged']);
                        $pici['init_num'] = $pici['num'];
                        $pici['num'] = 0;
                        $b2b_detail[] = $pici;
                        $sku_str = $pici['sku'];
                        if (in_array($sku_str, $sku_arr)) {
                            $detail_info[$sku_str]['enotice_num'] += $pici['init_num'];
                        } else {
                            $pici['enotice_num'] = $pici['init_num'];
                            $pici['refer_price'] = $return_info['refer_price'];
                            $pici['purchase_price'] = $return_info['price'];
                            $detail_info[$sku_str] = $pici;
                        }
                        $sku_arr[] = $pici['sku'];
                    }
                }
                if (empty($detail_info)) {
                    throw new Exception('退单已经全部生成 ，不可再生成退单');
                }
                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $return_notice_record['store_code'], 'pur_return', $b2b_detail);
                if ($ret['status'] != 1) {
                    throw new Exception('单据批次添加失败');
                }
                $ret = load_model('pur/ReturnRecordDetailModel')->add_detail_action($pid, $detail_info);
                if ($ret['status'] != 1) {
                    throw new Exception('退单明细保存失败');
                }
            }
            //日志
            $action_note = "由采购退货通知单" . $return_notice_record['record_code'] . "生成";
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '新增', 'module' => "return_record", 'action_note' => $action_note, 'pid' => $pid);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            $this->commit();
            return array('status' => 1, 'message' => '更新成功', 'data' => $pid);
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    function get_detail_by_record_code($record_code, $lof_status, $goods_code) {
        $sql = "SELECT
                    pr.*,
                    bs.store_name,
                    su.supplier_name,
                    bt.record_type_name
                FROM
                    pur_return_record pr
                LEFT JOIN base_store bs on pr.store_code=bs.store_code
                LEFT JOIN base_supplier su on pr.supplier_code=su.supplier_code
                 LEFT JOIN base_record_type bt ON pr.record_type_code=bt.record_type_code
                WHERE record_code=:record_code";
        $sql_value = array(":record_code" => $record_code);
        $data = $this->db->get_row($sql, $sql_value);
        $data['is_store_out'] = ($data['is_store_out'] == 1) ? "已验收" : "未验收";
        $pid = $data['return_record_id'];
        if ($lof_status == 1) {
            $select = "SELECT pd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode,bd.lof_no,bd.production_date";
            $sql_join = "LEFT JOIN goods_sku gs ON pd.sku = gs.sku
                         LEFT JOIN base_goods bg ON pd.goods_code = bg.goods_code
                         LEFT JOIN b2b_lof_datail bd ON pd.pid=bd.pid";
        } else {
            $select = "SELECT pd.*,gs.spec1_name,gs.spec2_name,bg.goods_name,gs.barcode";
            $sql_join = "LEFT JOIN goods_sku gs ON pd.sku = gs.sku
                         LEFT JOIN base_goods bg ON pd.goods_code = bg.goods_code";
        }
        $where = (!empty($goods_code)) ? " WHERE pd.pid=:pid AND pd.goods_code LIKE :goods_code" : "WHERE pd.pid=:pid";
        $detail_sql = "{$select} FROM pur_return_record_detail pd {$sql_join} {$where}
                        GROUP BY sku";
        $detail_value = (!empty($goods_code)) ? array(":pid" => $pid, ":goods_code" => "%" . $goods_code . "%") : array(":pid" => $pid);
        $detail_data = $this->db->get_all($detail_sql, $detail_value);
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price');
        foreach ($detail_data as &$value) {
            $value['is_store_out'] = $data['is_store_out'];
            $value['record_code'] = $record_code;
            $value['relation_code'] = $data['relation_code'];
            $value['supplier_name'] = $data['supplier_name'];
            $value['order_time'] = $data['order_time'];
            $value['store_name'] = $data['store_name'];
            $value['rebate'] = $data['rebate'];
            $value['record_type_name'] = $data['record_type_name'];
            $value['refer_price'] = round($value['refer_price'], 3);
            $value['price'] = round($value['price'], 3);
            $value['rebate'] = round($value['rebate'], 3);
            $value['per_price'] = $value['price'] * $value['rebate'];
            $value['money'] = round($value['money'], 3);
            $value['num_differ'] = $value['enotice_num'] - $value['num'];
            if ($status['status'] != 1) {
                $value['price'] = '****';
                $value['per_price'] = '****';
                $value['money'] = '****';
                $value['refer_price'] = "****";
            }
        }
        return $detail_data;
    }
    //导出明细
    private function sell_record_search_csv($sql_main, $sql_values, $filter) {
        $select = "rl.is_store_out,rl.record_code,rl.rebate,rl.store_code,rl.relation_code,rl.order_time,rl.record_time,rl.record_type_code,rl.supplier_code,rl.remark,r3.goods_code,r3.barcode,r2.refer_price,r2.price,r2.money,r2.num,r2.enotice_num,r2.sku,r2.spec1_code,r2.spec2_code,r2.rebate";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $user_id = $this->db->get_row("SELECT user_id FROM sys_user WHERE user_code = :user_code ", array(':user_code'=>$filter['__t_user_code']));
        $status = load_model('sys/RoleManagePriceModel')->get_user_permission_price('purchase_price',$user_id['user_id']);
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
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            //计算差异
            $ret_data['data'][$key]['diff_num'] = $value['enotice_num'] - $value['num'];
            //计算进货单价
            $ret_data['data'][$key]['price1'] = $value['price'] * $value['rebate'];
            if ($status['status'] != 1) {
                $ret_data['data'][$key]['price1'] = '****';
                $ret_data['data'][$key]['price'] = '****';
                $ret_data['data'][$key]['money'] = '****';
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    public function add_detail($param){
        $ret = $this->add_detail_goods($param['record_id'],$param['detail'],$param['store_code']);
        return $ret;
    }
}
