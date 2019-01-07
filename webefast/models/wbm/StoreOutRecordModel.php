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

class StoreOutRecordModel extends TbModel {

    /**
     * @var array 打印发货单模板所需字段
     */
    public $print_fields_default = array(
        'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '通知单号' => 'relation_code',
            '下单时间' => 'order_time',
            '分销商' => 'distributor_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '总出库数' => 'num',
            '总金额' => 'money',
            '备注' => 'remark',
            '总通知数' => 'enotice_num',
            '快递单号' => 'express',
            '配送方式' => 'express_name',
            '运费' => 'express_money',
            '联系人' => 'name',
            '联系电话' => 'tel',
            '地址' => 'address',
            //  '总通知金额' => 'enotice_money',
            '总差异数' => 'diff_num',
            '仓库寄件人' => 'shop_contact_person',
            '仓库联系人' => 'contact_person',
            '仓库联系电话' => 'sender_phone',
            '仓库店铺留言' => 'message',
            '仓库店铺留言2' => 'message2',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '拣货单号' => 'pick_no',
        ),
        'detail' => array(
            array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '出厂名称' => 'goods_produce_name',
                '规格1' => 'spec1_name',
                '规格1代码' => 'spec1_code',
                '规格2' => 'spec2_name',
                '规格2代码' => 'spec2_code',
                '单价' => 'price',
                '折扣' => 'rebate',
                '批发价' => 'pf_price',
                '实际出库数' => 'num',
                '通知数' => 'enotice_num',
                '差异数' => 'diff_num',
                '金额' => 'money',
                '商品条形码' => 'barcode',
                '库位' => 'shelf_code',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品重量' => 'weight',
                '吊牌价' => 'dp_price',
                '批次' => 'lof_no',
            ),
        ),
    );

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default($id, $is_lof = 0) {
        $r = array();

        $ret = $this->get_by_id($id);
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
        $ret['data']['address'] = $country . $province . $city . $district . $ret['data']['address'];
        $r['record'] = $ret['data'];

        if ($is_lof == 1) {
            $sql = "select d.pid
                ,d.goods_code,l.num,l.init_num as enotice_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,l.lof_no,r3.spec1_code,r3.spec2_code
                from wbm_store_out_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                INNER JOIN  b2b_lof_datail l  ON l.sku = r3.sku AND d.record_code=l.order_code AND l.order_type='wbm_store_out'
                where d.pid=:pid";
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.enotice_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code
                from wbm_store_out_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left join goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
            $sql2 = "select  w.pick_no			
                    from wbm_store_out_record_detail d 
                    LEFT JOIN api_weipinhuijit_store_out_record w on d.record_code = w.store_out_record_no
                    where d.pid = :pid  GROUP BY pick_no;";
        }

        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id)); //获取商品详情
        $ret = $this->db->get_all($sql2, array(':pid' => $id)); //获取拣货单号
        $store_code = $r['record']['store_code'];
//                left join goods_price gp  ON d.goods_code = gp.goods_code
//                left join base_spec1 s1 ON d.spec1_code = s1.spec1_code
//                left join base_spec2 s2 ON d.spec2_code = s2.spec2_code
        $shelf_sort_arr = array();
        foreach ($r['detail'] as &$val) {
            $shelf_code_arr = load_model('prm/GoodsShelfModel')->get_goods_shelf($store_code, $val['sku']);
            $val['shelf_code'] = implode(",", $shelf_code_arr);
            $shelf_sort_arr[] = $val['shelf_code'];
//            $r['record']['pick_no'] = $val['pick_no'];
        }
        //按照库存排序
        array_multisort($shelf_sort_arr, SORT_ASC, $r['detail']);
        //拣货单号合并
        foreach ($ret as &$val) {
            $r['record']['pick_no'] .= $val['pick_no'] . ',';
        }
        //去除右边的逗号
        $r['record']['pick_no'] = rtrim($r['record']['pick_no'], ',');
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
        $this->update_print_status($id, 'record');
        return $d;
    }

    public function get_record_goods_ids($record) {
        $sql = "select r2.sku,r1.store_out_record_id from wbm_store_out_record r1 inner join wbm_store_out_record_detail r2 on r1.store_out_record_id=r2.pid where r1.record_code='{$record}'";
        $data = $this->db->get_all($sql);
        foreach ($data as $val) {
            $r['sku'][] = $val['sku'];
            $r['id'][] = $val['store_out_record_id'];
        }
        return $r;
    }

    public function print_data_goods_new($request) {
        $id = $request['record_ids'];
        $sku = $request['sku'];
        $r = array();
        $r['record'] = $this->db->get_row("select p.*,GROUP_CONCAT(w.pick_no) pick_no_total,w.warehouse from wbm_store_out_record p left join api_weipinhuijit_store_out_record w on w.store_out_record_no=p.record_code where p.store_out_record_id = :id", array('id' => $id));
        $po_record = load_model('api/WeipinhuijitStoreOutRecordModel')->get_by_out_record_no($r['record']['record_code']);
        $po = "";
        foreach ($po_record['data'] as $v) {
            if (strpos($po, $v['po_no']) === false) {
                $po[] = $v['po_no'];
            }
        }
        $po_str = implode(',', $po);
        $r['record']['PO_record'] = $po_str;
        $sql = "select *
                    from wbm_store_out_record_detail d 
                    where d.pid=:pid and d.sku=:sku";
        $r['detail'] = $this->db->get_all($sql, array(':pid' => $id, ':sku' => $sku));
        $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
        $r['detail'][0] = array_merge($r['detail'][0], $sku_info);
        $r['detail'][0]['order_time'] = $r['record']['order_time'];
        $r['record']['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $r['record']['store_code']));
        $r['record']['warehouse_name'] = oms_tb_val('api_weipinhuijit_warehouse', 'warehouse_name', array('warehouse_code' => $r['record']['warehouse']));
        return $r;
    }

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 销货单新版打印（更改打印配件）
     */
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $ret = $this->get_by_id($id);
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
        $ret['data']['address'] = $country . $province . $city . $district . $ret['data']['address'];
        $r['record'] = $ret['data'];
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['wbm_store_out_record']);
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.enotice_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code
                from wbm_store_out_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code

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

        $r['record']['page_no'] = '<span class="page"></span>';
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail']);
        $this->update_print_status($id, 'record');
        $trade_data = array($r['record']);
        return $r;
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail) {
        $record['distributor_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $record['distributor_code']));
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['print_time'] = date('Y-m-d H:i:s');
        //寄送人信息
        $sql = "select * from base_store where store_code = :store_code";
        $store = $this->db->get_row($sql, array('store_code' => $record['store_code']));
        $record['shop_contact_person'] = $store['shop_contact_person'];
        $record['contact_person'] = $store['contact_person'];
        $record['sender_phone'] = $store['contact_phone'];
        $record['message'] = $store['message'];
        $record['message2'] = $store['message2'];

        /* $record['sender_mobile'] = $store['contact_tel'];
          $record['sender_country'] = oms_tb_val('base_area', 'name', array('id' => $store['country']));
          $record['sender_province'] = oms_tb_val('base_area', 'name', array('id' => $store['province']));
          $record['sender_city'] = oms_tb_val('base_area', 'name', array('id' => $store['city']));
          $record['sender_district'] = oms_tb_val('base_area', 'name', array('id' => $store['district']));
          $record['sender_street'] = oms_tb_val('base_area', 'name', array('id' => $store['street']));
          $record['sender_addr'] = $street . ' ' . $store['address'];
          $record['sender_address'] = $record['sender_country'] . ' ' . $record['sender_province'] . ' ' . $record['sender_city'] . ' ' . $record['sender_district'] . ' ' . $record['sender_street'] . ' ' . $store['address'];
          $record['sender_zip'] = $store['zipcode']; */

        $record['enotice_num'] = 0;
        $i = 1;
        $enotice_money = 0;
        foreach ($detail as $k => &$v) {
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], array('sell_price'));
            $v['dp_price'] = $sku_info['sell_price'];
            $v['pf_price'] = $v['price'];
            $v['price'] = $v['price'] * $v['rebate'];
            $enotice_money = $v['price'] * $v['enotice_num'];
            // $v['price'] =  $v['price'] ;
            // ,d.num,d.enotice_num
            $v['shelf_code'] = $this->get_shelf_code($record['store_code'], $v['sku']);
            $record['enotice_num'] += $v['enotice_num'];
            $v['diff_num'] = abs($v['enotice_num'] - $v['num']);

            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $v['brand_code']));
            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $v['season_code']));
            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $v['year']));
            $v['price'] = sprintf('%.3f', $v['price']);
        }
        //排序
        $detail = array_orderby($detail, 'shelf_code', SORT_ASC, SORT_STRING, 'goods_code', SORT_ASC, SORT_STRING);
        foreach ($detail as $k => &$v) {
            $v['sort_num'] = $i;
            $i++;
        }
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        //差异数
        $record['diff_num'] = $record['enotice_num'] - $record['num'];
        $record['enotice_money'] = $enotice_money;
    }
    public function print_data_escape_clothing(&$record, &$detail){
        $record['distributor_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $record['distributor_code']));
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['print_time'] = date('Y-m-d H:i:s');
        //寄送人信息
        $sql = "select * from base_store where store_code = :store_code";
        $store = $this->db->get_row($sql, array('store_code' => $record['store_code']));
        $record['shop_contact_person'] = $store['shop_contact_person'];
        $record['contact_person'] = $store['contact_person'];
        $record['sender_phone'] = $store['contact_phone'];
        $record['message'] = $store['message'];
        $record['message2'] = $store['message2'];

        $record['enotice_num'] = 0;
        $enotice_money = 0;
        foreach ($detail as $k => &$v) {
            $record['enotice_num'] += $v['enotice_num'];
            $v['diff_num'] = abs($v['enotice_num'] - $v['num']);
            $shelf_arr = array();
            foreach (explode(',',$v['shelf_code']) as $val){
                $arr = $this->db->get_row('select shelf_name,shelf_code from base_shelf where shelf_code = :shelf_code and store_code = :store_code ',array(':shelf_code'=>$val,':store_code'=>$record['store_code']));
                if(!empty($arr)) $shelf_arr[] = $arr['shelf_name'].'('.$arr['shelf_code'].')';
            }
            if (empty($shelf_arr)) {
                $v['shelf_name'] = '';
            } else {
                $v['shelf_name'] = implode(',',array_unique($shelf_arr));
            }
        }
        //排序
        $detail = array_orderby($detail, 'shelf_code', SORT_ASC, SORT_STRING, 'goods_code', SORT_ASC, SORT_STRING);
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        //差异数
        $record['diff_num'] = $record['enotice_num'] - $record['num'];
        $record['enotice_money'] = $enotice_money;
    }

    //更新打印状态
    public function update_print_status($store_out_record_id, $type) {
        $sql = "SELECT is_print_record,is_print_box FROM wbm_store_out_record WHERE store_out_record_id = :store_out_record_id ";
        $data = $this->db->get_row($sql, array(':store_out_record_id' => $store_out_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '批发销货单不存在');
        }
        //添加日志
        $this->add_print_log($store_out_record_id, $type);
        //更新状态
        if ($type == 'record') {
            $this->update_exp('wbm_store_out_record', array('is_print_record' => 1), array('store_out_record_id' => $store_out_record_id));
        } else {
            $this->update_exp('wbm_store_out_record', array('is_print_box' => 1), array('store_out_record_id' => $store_out_record_id));
        }
    }

    //打印日志记录
    public function add_print_log($store_out_record_id, $type) {
        $sql = "SELECT is_sure,is_store_out FROM wbm_store_out_record WHERE store_out_record_id = :store_out_record_id  ";
        $d = $this->db->get_row($sql, array(':store_out_record_id' => $store_out_record_id));
        $sure_status = $d['is_sure'] == 1 ? "已确认" : "未确认";
        $finish_status = $d['is_store_out'] == 0 ? '未出库' : '出库';
        $action_name = $type === 'record' ? '打印' : '打印汇总单';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => $action_name, 'module' => "store_out_record", 'pid' => $store_out_record_id);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    //检查是否打印
    public function check_is_print($store_out_record_id, $type) {
        $sql = "SELECT is_print_record,is_print_box FROM wbm_store_out_record WHERE store_out_record_id = :store_out_record_id ";
        $data = $this->db->get_row($sql, array(':store_out_record_id' => $store_out_record_id));
        if ($type === 'record') {
            $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印批发销货单，是否继续打印？') : $this->format_ret(1, '', '');
            return $ret;
        } else {
            $ret = $data['is_print_box'] == 1 ? $this->format_ret(-1, '', '重复打印汇总单，是否继续打印？') : $this->format_ret(1, '', '');
            return $ret;
        }
    }

    /**
     * 读取库位代码
     * @param $recordCode
     * @param $sku
     * @return string
     */
    public function get_shelf_code($store_code, $sku) {
        // and b.lof_no = a.batch_number
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code  and a.sku = :sku";
        $l = $this->db->get_all($sql, array('store_code' => $store_code, 'sku' => $sku));

        $arr = array();
        foreach ($l as $_k => $_v) {
            $arr[] = $_v['shelf_code'];
        }

        return implode(',', $arr);
    }
    /**
     * 读取库位代码
     * @param $recordCode
     * @param $sku
     * @return string
     */
    public function get_shelf_code_by_arr($store_code, $sku_arr) {
        // and b.lof_no = a.batch_number
        if(empty($sku_arr)) return '';
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sql_values);
        $sql_values[':store_code'] = $store_code;
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code  and a.sku = {$sku_str}";
        $l = $this->db->get_all($sql, $sql_values);
        $arr = array();
        foreach ($l as $_k => $_v) {
            $arr[] = $_v['shelf_code'];
        }

        return implode(',', array_unique($arr));
    }

    function get_table() {
        return 'wbm_store_out_record';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        if ((isset($filter['goods_name']) && $filter['goods_name'] != '' ) || (isset($filter['goods_code']) && $filter['goods_code'] != '') || (isset($filter['barcode']) && $filter['barcode'] != '') || (isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'store_out_record_list_detail')) {
            $sql_join .= "  LEFT JOIN wbm_store_out_record_detail r2 on rl.record_code = r2.record_code"
                    . " INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code";
            if (isset($filter['barcode']) && $filter['barcode'] != '') {
                $sql_join .= " INNER JOIN goods_sku r4 on r4.sku = r2.sku";
            }
        }
        $sql_main = "FROM {$this->table} rl {$sql_join} WHERE 1";

        //jit 推送WMS问题完善 整洁取消不显示问题
        $sql_main .= " AND is_cancel=0 ";

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
        // 物流单号
        if (isset($filter['express']) && $filter['express'] != '') {
            $sql_main .= " AND (rl.express LIKE :express )";
            $sql_values[':express'] = $filter['express'] . '%';
        }
        // 批发通知单编号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        //店铺
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ({$str}) ";
        }
        // 单据状态
        if (isset($filter['is_store_out']) && $filter['is_store_out'] != '') {
            $arr = explode(',', $filter['is_store_out']);
            $str = $this->arr_to_in_sql_value($arr, 'is_store_out', $sql_values);
            $sql_main .= " AND rl.is_store_out in ({$str}) ";
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND  rl.remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
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
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        if (isset($filter['time_type']) && $filter['time_type'] !== '') {
            if ($filter['time_type'] == 'order_time') {
                //下单日期
                if (isset($filter['time_start']) && $filter['time_start'] != '') {
                    $sql_main .= " AND (rl.order_time >= :time_start )";
                    $sql_values[':time_start'] = $filter['time_start'];
                }
                if (isset($filter['time_end']) && $filter['time_end'] != '') {
                    $sql_main .= " AND (rl.order_time <= :time_end )";
                    $sql_values[':time_end'] = $filter['time_end'];
                }
            } else if ($filter['time_type'] == 'is_store_out_time') {
                //验收时间
                if (isset($filter['time_start']) && $filter['time_start'] != '') {
                    $sql_main .= " AND (rl.is_store_out_time >= :time_start )";
                    $sql_values[':time_start'] = $filter['time_start'];
                }
                if (isset($filter['time_end']) && $filter['time_end'] != '') {
                    $sql_main .= " AND (rl.is_store_out_time <= :time_end )";
                    $sql_values[':time_end'] = $filter['time_end'];
                }
            }
        }

        //商品
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND  r3.goods_name LIKE :goods_name";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }

        //商品
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :goods_code)";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        //是否有差异单
        if (isset($filter['difference_models']) && $filter['difference_models'] != '') {
            if ($filter['difference_models'] == 1) {
                $sql_main .= " AND (rl.num != rl.enotice_num )";
            } else {
                $sql_main .= " AND (rl.num = rl.enotice_num )";
            }
        }
        //导出明细
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'store_out_record_list_detail') {
            $sql_main .= " order by record_time desc";
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        $select = 'rl.*';
        $is_group = false;
        if (!empty($sql_join)) {
            $sql_main .= "   group by rl.record_code ";
            $is_group = true;
        }
        $sql_main .= "  order by record_code desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $is_group);
        /*
          foreach ($data['data'] as $key => $value) {
          $data['data'][$key]['money'] = round($value['money'], 2);
          $arr = load_model('base/StoreModel')->get_by_field('store_code', $value['store_code'], 'store_name');
          $data['data'][$key]['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';
          $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier','supplier_code', $value['supplier_code'], 'supplier_name');
          $data['data'][$key]['supplier_name'] = isset($arr['data']['supplier_name']) ? $arr['data']['supplier_name'] : '';
          $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type','record_type_code', $value['record_type_code'], 'record_type_name');
          $data['data'][$key]['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';

          } */
        //快递公司
        if (!empty($data['data'])) {
            $express = load_model('base/ArchiveSearchModel')->get_archives_map('express');
        }
        foreach ($data['data'] as &$val) {
            $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code', $val['record_type_code'], 'record_type_name');
            $val['type_name'] = $data_name['data']['record_type_name'];
            //差异数
            $val['num_differ'] = $val['enotice_num'] - $val['num'];
            $val['is_store_code'] = ($val['is_store_out'] == 1) ? ' 已验收' : '未验收';
            $val['remark'] = $val['remark'] . "\t";
            if (!empty($val['express_code'])) {
                $val['express_name'] = $express[$val['express_code']];
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
        $select = "rl.enotice_num as all_enotice_num,rl.record_code,rl.order_time,rl.is_store_out,rl.name,rl.tel,rl.express,rl.express_code,rl.express_money,rl.express,rl.address,rl.distributor_code,rl.relation_code,rl.rebate,rl.store_code,rl.record_time,r2.sku,r2.price,r2.enotice_num,r2.num,r3.goods_code,rl.remark,r3.brand_name";
        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
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
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'sell_price', 'goods_short_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            //商品简称
            $ret_data['data'][$key]['goods_short_name'] = $sku_info['goods_short_name'];
            $ret_data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $ret_data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $ret_data['data'][$key]['goods_name'] = $sku_info['goods_name'];
            $ret_data['data'][$key]['barcode'] = $sku_info['barcode'];
            //计算差异数
            $ret_data['data'][$key]['diff_num'] = $value['enotice_num'] - $value['num'];
            //计算批发单价
            $ret_data['data'][$key]['price1'] = $value['price'] * $value['rebate'];
            $ret_data['data'][$key]['price1'] = round($ret_data['data'][$key]['price1'], 3);
            //计算金额
            $ret_data['data'][$key]['money'] = $ret_data['data'][$key]['price1'] * $value['num'];
            $ret_data['data'][$key]['money'] = round($ret_data['data'][$key]['money'], 3);
            //查询快递公司
            $ret_data['data'][$key]['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            //吊牌价、库位
            $ret_data['data'][$key]['sell_price'] = $sku_info['sell_price'];
            $store_code[] = $value['store_code'];
            $sku[] = $value['sku'];
        }
        $ret = load_model('prm/GoodsShelfModel')->get_shelf_name(array_unique($store_code), array_unique($sku));
        $shelf_arr = $ret['data'];
        foreach ($ret_data['data'] as $key => $value) {
            $ret_data['data'][$key]['shelf_name'] = isset($shelf_arr[$value['store_code'] . ',' . $value['sku']]['shelf_name']) ? $shelf_arr[$value['store_code'] . ',' . $value['sku']]['shelf_name'] : '';
        }
        return $this->format_ret(1, $ret_data);
    }

    function get_by_id($id) {
        $data = $this->get_row(array('store_out_record_id' => $id));
        $ret_distributor = load_model('base/CustomModel')->get_by_code($data['data']['distributor_code']);
        $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code', $data['data']['return_type_code'], 'record_type_name');
        $data['data']['record_type_name'] = $data_name['data']['record_type_name'];
        $data['data']['distributor_name'] = $ret_distributor['data']['custom_name'];
//        $arr = load_model('base/StoreModel')->table_get_by_field('base_supplier','supplier_code', $data['data']['supplier_code'], 'supplier_name');
//            $data['data']['distributor_code'] = isset($arr['data']['distributor_name']) ? $arr['data']['distributor_name'] : '';

        $arr = load_model('base/StoreModel')->table_get_by_field('base_record_type', 'record_type_code', $data['data']['record_type_code'], 'record_type_name');
        $data['data']['record_type_name'] = isset($arr['data']['record_type_name']) ? $arr['data']['record_type_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('sys_user', 'user_code', $data['data']['user_code'], 'user_name');
        $data['data']['user_name'] = isset($arr['data']['user_name']) ? $arr['data']['user_name'] : '';
        $arr = load_model('base/StoreModel')->table_get_by_field('base_store', 'store_code', $data['data']['store_code'], 'store_name');
        $data['data']['store_name'] = isset($arr['data']['store_name']) ? $arr['data']['store_name'] : '';

        $arr = load_model('base/ShippingModel')->table_get_by_field('base_express', 'express_code', $data['data']['express_code'], 'express_name');
        $data['data']['express_name'] = isset($arr['data']['express_name']) ? $arr['data']['express_name'] : '';





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

    function delete($store_out_record_id) {
        $sql = "select * from {$this->table} where store_out_record_id = :store_out_record_id";
        $data = $this->db->get_row($sql, array(":store_out_record_id" => $store_out_record_id));
        if ($data['is_store_out'] == 1) {
            return $this->format_ret('-1', array(), '单据已经出库，不能删除！');
        }
        //判断是否含有装箱单
        $sql = "SELECT r2.* FROM b2b_box_task AS r1 LEFT JOIN b2b_box_record AS r2 ON  r1.task_code=r2.task_code WHERE r1.relation_code=:relation_code";
        $sql_value = array();
        $sql_value[':relation_code'] = $data['record_code'];
        $box_info = $this->db->get_all($sql, $sql_value);
        if (!empty($box_info)) {
            return $this->format_ret('-1', array(), '存在装箱单，不能删除！');
        }
        $ret = parent::delete(array('store_out_record_id' => $store_out_record_id));
        $this->db->create_mapper('wbm_store_out_record_detail')->delete(array('pid' => $store_out_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $store_out_record_id, 'order_type' => 'wbm_store_out'));

        //若关联唯品会JIT拣货单，则回写拣货单生成销货单状态
        $pick_data = load_model('api/WeipinhuijitStoreOutRecordModel')->get_by_out_record_no($data['record_code']);
        if (!empty($pick_data['data'])) {
            $pick_no_arr1 = array_column($pick_data['data'], 'pick_no');
            $pick_no_str1 = deal_array_with_quote($pick_no_arr1);
            $sql = "DELETE FROM api_weipinhuijit_store_out_record WHERE pick_no IN({$pick_no_str1}) AND store_out_record_no='{$data['record_code']}'";
            $this->query($sql);

            $sql = "SELECT pick_no FROM api_weipinhuijit_store_out_record WHERE pick_no IN({$pick_no_str1})";
            $pick = $this->db->get_all($sql);
            $pick_no_arr2 = array_column($pick, 'pick_no');
            $pick_no_arr = array_diff($pick_no_arr1, $pick_no_arr2);
            if (!empty($pick_no_arr)) {
                $pick_no_str2 = deal_array_with_quote($pick_no_arr);
                $sql = "UPDATE api_weipinhuijit_pick SET is_execute=0 WHERE pick_no in({$pick_no_str2})";
                $this->query($sql);
            }
        }

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
        $sql = "select  store_out_record_id from {$this->table}   order by store_out_record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['store_out_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "PF" . date("Ymd") . add_zero($djh);
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
        if (!isset($where['store_out_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_ID_CODE');
        }
        $result = $this->get_row($where);
        if (1 != $result['status']) {
            return $this->format_ret('-1', '', 'RECORD_ERROR');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }
        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
        $ret = parent::update($data, $where);
        if ($ret['status'] == 1) {
            //更新成功后 如果是关联了唯品会 需要修改唯品会批发销的 配送方式 和快递单号
            $wph_record = load_model('api/WeipinhuijitStoreOutRecordModel')->get_row(array('store_out_record_no' => $result['data']['record_code']));
            if ($wph_record['status'] == 1) {
                load_model('api/WeipinhuijitStoreOutRecordModel')->update_express_code($result['data']['record_code']);
            }
        }
        //更新主表数据
        return $ret;
    }

    //确认/取消确认，锁定库存/释放锁定 针对移出仓
    function update_sure($active, $field, $id) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_sure')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }

        $record = $this->get_row(array('store_out_record_id' => $id));
        if ($active == 0) {
            $record_code = $record['data']['record_code'];
            require_model('sys/RecordScanBoxModel');
            $wbm_obj = new RecordScanBoxModel('wbm_store_out');
            $ret = $wbm_obj->cancel_box_task($record_code);
            if ($ret['status'] < 0) {
                return $this->format_ret(-1, '', $ret['message']);
            }

            $sql = "select count(*) from wbm_store_out_record_detail where record_code = :record_code and num>0";
            $c = ctx()->db->getOne($sql, array(':record_code' => $record_code));
            if ($c > 0) {
                return $this->format_ret(-1, '', '单据存在已扫描的明细，无法取消');
            }
        }
        $details = load_model('wbm/StoreOutRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_store_out');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }
//    	$details = load_model('wbm/StoreOutRecordDetailModel')->get_all(array('pid' => $id));
//    	//检查明细是否为空
//    	if (empty($details['data'])) {
//    		return $this->format_ret('-1', '','RECORD_ERROR_DETAIL_EMPTY');
//    	}
        /*
          $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_store_out');
          //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id,'shift');
          if (empty($ret_lof_details['data'])) {
          return $this->format_ret('-1', '','RECORD_ERROR_DETAIL');
          }
         */
        /* 去掉库存锁定
          //库存操作锁库存
          require_model('prm/InvOpModel');

          if($active == 1){
          $invobj = new InvOpModel( $record['data']['record_code'],'wbm_store_out', $record['data']['store_code'],1,$ret_lof_details['data']);
          $this->begin_trans();
          $ret = $invobj->adjust();
          if($ret['status']==-10){//锁定批次库存不足
          $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
          if(!empty($invobj->check_data['adjust_record_info'])&&$lof_manage['lof_status']==0){//关闭掉批次情况
          $this->rollback();//
          $ret = $invobj->adjust_lock_record($invobj->check_data['adjust_record_info']);//调整锁定批次
          if($ret['status']!=1){
          return $ret;
          }
          $this->begin_trans();
          $ret = $invobj->adjust(); //重新提交
          }
          }
          }
          if($active == 0){
          $invobj = new InvOpModel( $record['data']['record_code'],'wbm_store_out', $record['data']['store_code'],0,$ret_lof_details['data']);
          $this->begin_trans();
          $ret = $invobj->adjust();
          }




          if($ret['status']!=1){
          $this->rollback(); //事务回滚
          return $ret;
          }
         */
        $ret = parent:: update(array($field => $active), array('store_out_record_id' => $id));
        //$ret= parent:: update(array('is_store_out' => 1), array('return_record_id' => $id));
        /*
          if($ret['status']!=1){
          $this->rollback(); //事务回滚
          return $ret;
          }
          $this->commit(); //事务提交
         */
        return $ret;
    }

    //检查差异数
    function check_diff_num($record_code) {
        $record = $this->get_row(array('record_code' => $record_code));
        $id = $record['data']['store_out_record_id'];
        if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
            return $this->format_ret(false, array(), '单据已验收');
        }
        if ($record['data']['relation_code'] == '') {
            return $this->format_ret(1, array(), '');
        }
        $data = $this->db->get_row("SELECT SUM(num) AS finish_num,SUM(enotice_num) AS enotice_num FROM `wbm_store_out_record_detail` WHERE pid=:pid", array(':pid'=>$id));
        if ($data['enotice_num']>=0 && $data['finish_num'] == 0) {
            return $this->format_ret(2, array(), '是否确认整单验收？ ');
        }
        $ret = $this->db->get_row("SELECT record_code FROM wbm_store_out_record_detail WHERE pid = :pid AND num != enotice_num", array(':pid'=>$id));
        if (!empty($ret['record_code'])) {
            return $this->format_ret(2, array(), '是否确认差异验收？ ');
        }
        if ($data['enotice_num'] == $data['finish_num']) {
            return $this->format_ret(1, array(), '');
        }
    }

    /**
     * 批发销货单验收
     * @param $record_code
     * @param int $check
     * @param int $check_priv
     * @param array $record
     * @param array $details
     * @param int $force_negative_inv
     * @param int $accept_type 验收类型 1:按业务日期验收
     * @return array|mixed
     */
    function do_sure_and_shift_out($record_code, $check = 1, $check_priv = 1, $record = array(), $details = array(), $force_negative_inv = 0, $accept_type = 0) {
        //#############权限
        if ($check_priv == 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_shift_out')) {
                return $this->format_ret(-1, array(), '无权访问');
            }
        }
        if (empty($record)) {
            $record = $this->get_row(array('record_code' => $record_code));
        }
        $id = $record['data']['store_out_record_id'];
        if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
            return $this->format_ret(false, array(), '单据已验收');
        }
        if (empty($details)) {
            $details = load_model('wbm/StoreOutRecordDetailModel')->get_all(array('pid' => $id));
        }

        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        require_model('prm/InvOpModel');
        $this->begin_trans();
        if (!$record['data']['relation_code']) {
            $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_store_out');
            if (empty($ret_lof_details['data'])) {
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
            }
            require_model('prm/InvOpModel');
            //未绑定批发通知单时：校验可用库存
            $invobj = new InvOpModel($record['data']['record_code'], 'wbm_store_out', $record['data']['store_code'], 0, $ret_lof_details['data']);
            //获取inv不足的sku

            $insufficient_sku = $invobj->get_usable_inv($ret_lof_details['data']);
            $err_msg = array();
            if (!empty($insufficient_sku)) {
                foreach ($details['data'] as $detail) {
                    if (array_key_exists($detail['sku'], $insufficient_sku)) {
                        $barcode = load_model('goods/SkuCModel')->get_barcode($detail['sku']);
                        $err_msg[] = array($barcode => '可用库存不足！');
                    }
                }
                if (!empty($err_msg)) {
                    $fail_top = array('商品条码', '错误信息');
                    $message = "商品可用库存不足，验收失败";
                    if ($check == 0) {
                        return $this->format_ret('-1', '', $message);
                    }
                    $file_name = $this->create_import_fail_files($fail_top, $err_msg);
//                    $message .= "，请选择处理方式<br /><a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >导出错误数据</a><br/> ";
                    $url = set_download_csv_url($file_name, array('export_name' => 'error'));
                    $message .= "，请选择处理方式<br /><a target=\"_blank\" href=\"{$url}\" >导出错误数据</a><br/> ";
                    $message .= "<input type='radio' name='handle_type' value='delete_short_inv' id='delete_short_inv'>一键删除库存不足的商品<br/> ";
                    $message .= "<input type='radio' name='handle_type' value='import_available_inv' id='import_available_inv'>一键以商品可用库存数更新商品数量<br/> ";
                    return $this->format_ret('-1', '', $message);
                }
            }
        } else {
            $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_order_num($record_code, 'wbm_store_out');
            $check_detail = $check == 1 && !empty($ret_lof_details['data']) ? 0 : $check;
            $ret_set_lof = load_model('stm/GoodsInvLofRecordModel')->set_lof_datail($record['data']['relation_code'], 'wbm_notice', $details['data'], $check_detail);
            if ($ret_set_lof['status'] < 0) {
                $this->rollback();
                return $this->format_ret('-1', '', '通知单中不存在指定商品或指定商品出库总数超过通知单的通知数');
            }

            //单据批次添加

            if (empty($ret_lof_details['data'])) {
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record['data']['store_out_record_id'], $record['data']['store_code'], 'wbm_store_out', $ret_set_lof['data']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }

            $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_store_out');
            if (empty($ret_lof_details['data'])) {
                $this->rollback();
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
            }

            $record_tz = load_model('wbm/NoticeRecordModel')->get_row(array('record_code' => $record['data']['relation_code']));
            if ($record_tz['status'] == 1) {
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
                $invobj = new InvOpModel($record['data']['relation_code'], 'wbm_notice', $record_tz['data']['store_code'], 5, $ret_lof_details_tz);

                $ret = $invobj->adjust();
                if ($ret['status'] != 1) {
                    $this->rollback(); //事务回滚
                    return $ret;
                }
                foreach ($details['data'] as &$detail) {
                    if ($detail['num'] == 0 && $detail['enotice_num'] != 0) {
                        $detail['num'] = $detail['enotice_num'];
                    }
                }
            } else {
                return $this->format_ret('-1', '', '没有对应通知数据');
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
        $invobj = new InvOpModel($record['data']['record_code'], 'wbm_store_out', $record['data']['store_code'], 2, $ret_lof_details['data']);
        if ($force_negative_inv == 1) {
            $invobj->force_negative_inv(); //强制允许负库存
        }
        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $record_time = ($accept_type == 0) ? date('Y-m-d') : $record['data']['record_time'];
        $ret = parent:: update(array('is_sure' => 1, 'record_time' => $record_time), array('store_out_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $ret = parent:: update(array('is_store_out' => 1, 'is_store_out_time' => date('Y-m-d H:i:s')), array('store_out_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        //按业务日期验收
        if ($accept_type == 1) {
            $ret = $this->update_exp('b2b_lof_datail', array('order_date' => $record['data']['record_time']), array('order_code' => $record_code, 'order_type' => 'wbm_store_out'));
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $this->format_ret(-1, '', '更新失败！');
            }
        }
        //更新完成数
        $status = $this->update_finish_num($id, $record['data']['record_code'], $ret_lof_details['data']);
        if ($status['status'] != 1) {
            return $status;
        }
        //计算金额
        $this->set_money($id);
        //回写主表数量金额
        $ret = load_model('wbm/StoreOutRecordDetailModel')->mainWriteBack($id);
        if (isset($record['data']['relation_code']) && $record['data']['relation_code'] <> '' && isset($ret_lof_details['data'])) {
            //回写采购订单完成数
            $ret = load_model('wbm/NoticeRecordDetailModel')->update_finish_num($record['data']['relation_code'], $ret_lof_details['data'], 'store_out_record');
        }
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        if (!empty($record['data']['jx_code'])) {
            $ret_back_purchaser_detail = load_model('fx/PurchaseRecordDetailModel')->update_finish_num($record['data']['jx_code'], $ret_lof_details['data'], 'fx_purchase');
            if ($ret_back_purchaser_detail['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
        }

        //日志
        $action_name = ($accept_type == 0) ? '验收' : '按业务日期验收';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '出库', 'action_name' => $action_name, 'module' => "store_out_record", 'pid' => $id);
        load_model('pur/PurStmLogModel')->insert($log);


        //推送到中间表
        $ret = load_model('mid/MidBaseModel')->set_mid_record('send', $record_code, 'wbm_store_out', $record['data']['store_code']);
        if ($ret['status'] < 0) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        //回写有货
        if (!empty($record['data']['relation_code'])) {
            $ret = $this->update_youhuo_finish($record_code, $record['data']['relation_code']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit(); //事务提交
        return $this->format_ret(1, array(), '验收成功');
    }

    //出库
    function shift_out($record_code, $check = 1, $check_priv = 1, $record = array(), $details = array(), $is_lof = 0) {

        //#############权限
        if ($check_priv == 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_shift_out')) {
                return $this->format_ret(-1, array(), '无权访问');
            }
        }
        //###########
        //检查调整单状态是否为已出库
        if (empty($record)) {
            $record = $this->get_row(array('record_code' => $record_code));
        }
        $id = $record['data']['store_out_record_id'];
        if (empty($details)) {
            $details = load_model('wbm/StoreOutRecordDetailModel')->get_all(array('pid' => $id));
        }

        if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
            return $this->format_ret(false, array(), '销货出库错误');
            //		return $this->format_ret('RETURN_RECORD_ERROR_STORE_OUT');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }

        //通知数，完成数
        $tz_num = 0;
        $fini_num = 0;
        foreach ($details['data'] as $k => $v) {
            $tz_num += intval($v['enotice_num']);
            $fini_num += intval($v['num']);
        }

        if ($check == 1 && $fini_num > 0) {
            return $this->format_ret(false, array(), '不能强制出库,请扫描出库!');
        }
        if ($check == 1 && $tz_num == 0) {
            return $this->format_ret('-1', '', '通知数为0不能出库');
        }
        if ($check == 0 && $fini_num == 0) {
            return $this->format_ret('-1', '', '完成数为0不能出库');
        }
        //##########生成批次表
        //转化成批次数据
        //
        //未开启批次
        if ($is_lof == 0) {
            $this->begin_trans();
            $ret_set_lof = load_model('stm/GoodsInvLofRecordModel')->set_lof_datail($record['data']['relation_code'], 'wbm_notice', $details['data'], $check);
            if ($ret_set_lof['status'] < 0) {
                $this->rollback();
                return $this->format_ret('-1', '', '找不到通知单指定商品信息');
            }
            //print_r($pici_arr);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($record['data']['store_out_record_id'], $record['data']['store_code'], 'wbm_store_out', $ret_set_lof['data']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }

        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_store_out');
        if (empty($ret_lof_details['data'])) {
            $this->rollback();
            return $this->format_ret('-1', '', '数据不能为空');
        }


        require_model('prm/InvOpModel');
        $record_tz = load_model('wbm/NoticeRecordModel')->get_row(array('record_code' => $record['data']['relation_code']));


        if ($record_tz['status'] <> 'op_no_data') {

            //$ret_lof_details_tz = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($record_tz['data']['notice_record_id'], 'wbm_notice');
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
            $invobj = new InvOpModel($record['data']['relation_code'], 'wbm_notice', $record_tz['data']['store_code'], 5, $ret_lof_details_tz);
            //$this->begin_trans();
            $ret = $invobj->adjust();
            if ($ret['status'] != 1) {
                $this->rollback(); //事务回滚
                return $ret;
            }
        } else {
            return $this->format_ret('-1', '', '没有对应通知数据');
        }

        //库存扣减
        //print_r($ret_lof_details);
        //锁定库存减用 2 其他用3
        //过滤
        foreach ($ret_lof_details['data'] as $k => $v) {
            if ($v['num'] == 0) {
                unset($ret_lof_details['data'][$k]);
            }
        }
        if (empty($ret_lof_details['data'])) {
            $this->rollback();
            return $this->format_ret('-1', '', '数据不能为空');
        }
        $invobj = new InvOpModel($record['data']['record_code'], 'wbm_store_out', $record['data']['store_code'], 2, $ret_lof_details['data']);

        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        $ret = parent:: update(array('is_store_out' => 1), array('store_out_record_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $ret_up = $this->update_finish_num($id, $record['data']['record_code'], $ret_lof_details['data']);
        if ($ret_up['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret_up;
        }
        //计算金额
        $this->set_money($id);
        //回写主表数量金额
        $ret = load_model('wbm/StoreOutRecordDetailModel')->mainWriteBack($id);
        if (isset($record['data']['relation_code']) && $record['data']['relation_code'] <> '' && isset($ret_lof_details['data'])) {
            //回写采购订单完成数
            $ret = load_model('wbm/NoticeRecordDetailModel')->update_finish_num($record['data']['relation_code'], $ret_lof_details['data']);
        }

        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        $this->commit(); //事务提交
        return $this->format_ret(1, array(), 'SUCCESS_STORE_OUT');
        //return $this->format_ret('SUCCESS_STORE_OUT');
    }

    public function create_out_record($out_notice_record, $type = "create_out_unfinish") {
        $record_code = $this->create_fast_bill_sn();
        $store_out_record = array();
        //主表信息
        // 		$ret = load_model('wbm/ReturnRecordModel')->insert($stock_adjus);
        $store_out_record['record_code'] = $record_code;
        $store_out_record['order_time'] = $out_notice_record['order_time'];
        $store_out_record['record_time'] = $out_notice_record['record_time'];
        $store_out_record['relation_code'] = $out_notice_record['record_code'];
        $store_out_record['distributor_code'] = $out_notice_record['distributor_code'];
        $store_out_record['store_code'] = $out_notice_record['store_code'];
        $store_out_record['rebate'] = $out_notice_record['rebate'];
        $store_out_record['remark'] = $out_notice_record['remark'];
        $store_out_record['init_code'] = !empty($out_notice_record['init_code']) ? $out_notice_record['init_code'] : '';
        $store_out_record['express_code'] = !empty($out_notice_record['express_code']) ? $out_notice_record['express_code'] : '';
        $store_out_record['express_money'] = !empty($out_notice_record['express_money']) ? $out_notice_record['express_money'] : '';
        $store_out_record['express'] = !empty($out_notice_record['express_no']) ? $out_notice_record['express_no'] : '';
        $store_out_record['jx_code'] = !empty($out_notice_record['jx_code']) ? $out_notice_record['jx_code'] : '';
        $store_out_record['name'] = $out_notice_record['name'];
        $store_out_record['tel'] = $out_notice_record['tel'];
        $store_out_record['address'] = $out_notice_record['address'];
        $store_out_record['province'] = $out_notice_record['province'];
        $store_out_record['city'] = $out_notice_record['city'];
        $store_out_record['district'] = $out_notice_record['district'];
        $store_out_record['record_type_code'] = $out_notice_record['record_type_code'];
        if (isset($out_notice_record['is_settlement']) && $out_notice_record['is_settlement'] == 1) {
            $store_out_record['is_settlement'] = $out_notice_record['is_settlement'];
            $store_out_record['pay_status'] = 2;
        }
        $this->begin_trans();
        try {
            $ret = $this->insert($store_out_record);
            //$ret = load_model('wbm/StoreOutRecordModel')->get_row(array('relation_code' => $out_notice_record['record_code']));
            $pid = $ret['data'];
            //按未完成数生成
            if ($type == "create_out_unfinish") {
                $sql = "select goods_code,spec1_code,spec2_code,sku,rebate,price,num,finish_num from wbm_notice_record_detail where record_code = '{$out_notice_record['record_code']}'";
                $data = $this->db->get_all($sql);

                $sql = "select  goods_code,spec1_code,spec2_code,sku,init_num-fill_num as init_num,lof_no,production_date  from b2b_lof_datail where order_code = '{$out_notice_record['record_code']}' AND order_type='wbm_notice' ";
                $lof_data = $this->db->get_all($sql);

                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $out_notice_record['store_code'], 'wbm_store_out', $lof_data);
                if ($ret['status'] != 1) {
                    throw new Exception('单据批次添加失败');
                }
                //通知单的数量生成销货单时 为通知数
                foreach ($data as $key => $store_out_info) {
                    if ($store_out_info['num'] <= $store_out_info['finish_num']) {
                        unset($data[$key]);
                        continue;
                    }
                    $data[$key]['trade_price'] = $store_out_info['price'];
                    $data[$key]['enotice_num'] = $store_out_info['num'] - $store_out_info['finish_num'];
                    $data[$key]['num'] = 0;
                    //unset($data[$key]['num']);
                    unset($data[$key]['finsih_num']);
                    $data[$key]['num_flag'] = 1;
                }
                if (empty($data)) {
                    throw new Exception('销货单已经全部生成 ，不可再生成销货单');
                }

                $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($pid, $data);
                if ($ret['status'] != 1) {
                    throw new Exception('销货单明细保存失败');
                }
            }

            $this->commit();
            return array('status' => 1, 'message' => '生成成功', 'data' => $pid);
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    //设置完成数量
    function update_finish_num($id, $recode_code, $lof_details_data) {

//        $sql = "update wbm_store_out_record_detail,(select sku,sum(num) as num from b2b_lof_datail where order_code='{$recode_code}' and order_type='wbm_store_out' GROUP BY sku )
//as sku_num  ";
//        $sql.= " set wbm_store_out_record_detail.num = sku_num.num";
//        $sql.= " where  wbm_store_out_record_detail.sku=sku_num.sku and wbm_store_out_record_detail.pid = {$id}";
//        $this->db->query($sql);
//
        $sql_detail = "select sum(num) from  wbm_store_out_record_detail where record_code='{$recode_code}' ";
        $detail_num = $this->db->get_value($sql_detail);
        $sql_lof = "select sum(num) from  b2b_lof_datail where order_code='{$recode_code}' and order_type='wbm_store_out' ";
        $lof_num = $this->db->get_value($sql_lof);
        if ($detail_num != $lof_num) {
            $sql_up1 = "update wbm_store_out_record_detail set  num=0 where record_code='{$recode_code}'  ";
            $this->db->query($sql_up1);
            $sql_up2 = "update wbm_store_out_record_detail,b2b_lof_datail
                set wbm_store_out_record_detail.num = b2b_lof_datail.num+wbm_store_out_record_detail.num
                where wbm_store_out_record_detail.sku = b2b_lof_datail.sku
               AND wbm_store_out_record_detail.record_code = b2b_lof_datail.order_code AND  b2b_lof_datail.order_type='wbm_store_out'
               AND wbm_store_out_record_detail.record_code ='{$recode_code}'
                ";
            $this->db->query($sql_up2);
        }




        //是否jit拣货单生成的批发销货单
        $jit_out_store_ret = load_model('api/WeipinhuijitStoreOutRecordModel')->get_by_out_record_no($recode_code);
//insert_time
        //api_weipinhuijit_pick
        if (!empty($jit_out_store_ret['data'])) {
            $ret_detail = load_model('wbm/StoreOutRecordDetailModel')->get_by_record_code($recode_code);
            $detail_data = array();
            foreach ($ret_detail['data'] as $val) {
                $detail_data[$val['sku']] = $val;
            }
            foreach ($jit_out_store_ret['data'] as $jit_out_store_info) {

                if (!empty($detail_data)) {
                    $ret_update = $this->update_pick_goods($jit_out_store_info, $detail_data);
                    if ($ret_update['status'] < 1) {
                        return $ret_update;
                    }
                }
            }
            //更新配送方式信息
            load_model('api/WeipinhuijitStoreOutRecordModel')->update_express_code($recode_code);
        }

        return $this->format_ret(1, '', '');
    }

    /**
     * 回写有货的完成数
     * @param $record_code
     * @param $relation_record_code
     */
    function update_youhuo_finish($recode_code, $relation_record_code) {
        //有货采购单生成的销货单
        $yoho_out_store_ret = load_model('api/YohoStoreOutRecordModel')->get_by_out_record_no($relation_record_code);
        if (!empty($yoho_out_store_ret['data'])) {
            $ret_detail = load_model('wbm/StoreOutRecordDetailModel')->get_by_record_code($recode_code);
            $detail_data = array();
            foreach ($ret_detail['data'] as $val) {
                $detail_data[$val['sku']] = $val;
            }
            foreach ($yoho_out_store_ret['data'] as $yoho_out_store_info) {
                if (!empty($detail_data)) {
                    $ret_update = $this->update_yoho_goods($yoho_out_store_info, $detail_data, $recode_code);
                    if ($ret_update['status'] < 1) {
                        return $ret_update;
                    }
                }
            }
            //更新配送方式信息
            $ret = load_model('api/YohoStoreOutRecordModel')->update_express_code($recode_code);
            if ($ret['status'] != 1) {
                return $ret;
            }
            //仓库没有对接wms，终止通知单
            $reocrd_data = $this->get_row(array('record_code' => $recode_code));
            $is_wms = load_model('api/WeipinhuijitPickModel')->check_is_wms_store_code($reocrd_data['data']['store_code']);
            if ($is_wms != TRUE) {
                $sql = "SELECT notice_record_id,is_finish FROM wbm_notice_record WHERE record_code=:record_code";
                $sql_value[':record_code'] = $relation_record_code;
                $notice_record_info = $this->db->get_row($sql, $sql_value);
                if ($notice_record_info['is_finish'] == 0) {
                    $ret = load_model('wbm/NoticeRecordModel')->update_stop(1, 'is_stop', $notice_record_info['notice_record_id'], 0);
                    if ($ret['status'] != 1) {
                        return $ret;
                    }
                }
            }
        }
        return $this->format_ret('1', '', '');
    }

    private function update_pick_goods(&$jit_out_store_info, &$detail_data) {
        $pick_no = $jit_out_store_info['pick_no'];
        $pick_goods_sql = "select * from api_weipinhuijit_pick_goods where pick_no=:pick_no AND stock>delivery_stock ";
        $p_sql_values = array(':pick_no' => $pick_no);
        $pick_goods = $this->db->get_all($pick_goods_sql, $p_sql_values);
        $ret = $this->format_ret(1);


        $delivery_id = $jit_out_store_info['delivery_id'];
        $delivery_data = load_model('api/WeipinhuijitDeliveryModel')->get_by_field('delivery_id', $delivery_id, 'id');
        if (empty($delivery_data['data'])) {
            return $this->format_ret(-1, '', '唯品会JIT出库单不存在');
        }
        $delivery_data = $delivery_data['data'];
        $delivery_detail = array();
        $sku_arr = array();
        foreach ($pick_goods as $val) {
            $sku = $val['sku'];
            $delivery_num = 0;
            $wait_delivery_num = $val['stock'] - $val['delivery_stock'];
            $out_num = isset($detail_data[$sku]) ? $detail_data[$sku]['num'] : 0;
            if ($out_num > 0) {
                $delivery_num = ($out_num > $wait_delivery_num) ? $wait_delivery_num : $out_num;
                $detail_data[$sku]['num'] -= $delivery_num;
            }
            if ($delivery_num > 0 && (!isset($sku_arr[$sku]) || $sku_arr[$sku] == 0)) {
                $sku_arr[$sku] = $wait_delivery_num - $delivery_num;

                $sql = "UPDATE api_weipinhuijit_pick_goods
                        SET delivery_stock=delivery_stock+{$delivery_num}
                        WHERE id=:id";
                $sql_value = array(':id' => $val['id']);
                $status = $this->db->query($sql, $sql_value);
                $num = $this->affected_rows();
                if ($status === false || $num != 1) {
                    $ret = $this->format_ret(1, '', '数据出现处理异常');
                    break;
                }

                $detail = array(
                    'pid' => $delivery_data['id'],
                    'sku' => $sku,
                    'barcode' => $val['barcode'],
                    'record_code' => $jit_out_store_info['store_out_record_no'],
                    'box_no' => $jit_out_store_info['store_out_record_no'],
                    'pick_no' => $pick_no,
                    'po_no' => $val['po_no'],
                    'amount' => $delivery_num,
                    'vendor_type' => 'COMMON',
                    'delivery_id' => $delivery_id,
                );
                $key_arr = array('goods_code', 'spec1_code', 'spec2_code');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
                $delivery_detail[] = array_merge($detail, $sku_info);
            }


            if ($detail_data[$sku]['num'] == 0) {
                unset($detail_data[$sku]);
            }
        }


        $this->insert_multi_exp('api_weipinhuijit_delivery_detail', $delivery_detail);

        $sql = "update api_weipinhuijit_pick p,(select sum(delivery_stock)"
                . " as delivery_stock,pick_no from api_weipinhuijit_pick_goods where pick_no='{$pick_no}') as tmp"
                . " set p.delivery_num=tmp.delivery_stock where p.pick_no=tmp.pick_no and p.pick_no='{$pick_no}'";
        $this->db->query($sql);

        $sql = "UPDATE api_weipinhuijit_delivery SET amount=(SELECT SUM(amount) FROM api_weipinhuijit_delivery_detail WHERE delivery_id=:delivery_id) WHERE delivery_id=:delivery_id";

        $this->db->query($sql, array(':delivery_id' => $delivery_id));

        return $ret;
    }

    //计算金额
    function set_money($record_id) {
        $sql = " update wbm_store_out_record_detail set money = price * rebate * num  where pid ='{$record_id}'  ";

        return $this->db->query($sql);
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        $ret = $this->get_row(array('store_out_record_id' => $id));
        if (empty($ret)) {
            return array(-1, '', '批发销货单不存在！');
        }
        $store_code = $ret['data']['store_code'];
        $relation_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $error_msg = array();
        $err_num = 0;
        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $sku_arr, $sku_num);
            $sku_count = count($sku_arr);
            $check_ret = $this->check_pftzd($relation_code, $sku_arr, $sku_num, $err_num);
        } else {
            $num = $this->read_csv_sku($file, $sku_arr, $sku_num);
            $sku_count = count($sku_arr);
            $check_ret = $this->check_pftzd($relation_code, $sku_arr, $sku_num, $err_num);
            if (!empty($sku_num) && !empty($sku_arr)) {
                //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
                $sku_str = implode("','", $sku_arr);
                $sql = "select sku,lof_no,production_date,type from goods_lof where sku in ('$sku_str') group by sku";
                $sku_data = $this->db->get_all($sql);

                $sql_moren = "select lof_no,production_date from goods_lof order by id ASC";
                $moren = $this->db->get_row($sql_moren);
                $sku_data_new = array();
                foreach ($sku_data as $key2 => $data) {
                    $sku_data_new[$data['sku']]['production_date'] = $data['production_date'];
                    $sku_data_new[$data['sku']]['lof_no'] = $data['lof_no'];
                }
                $new_sku_num = $sku_num;
                $sku_num = array();
                foreach ($sku_arr as $key1 => $sku) {
                    if (array_key_exists($sku, $sku_data_new)) {
                        $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['num'] = $new_sku_num[$sku]['num'];
                        $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['trade_price'] = $new_sku_num[$sku]['trade_price'];
                        $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['lof_no'] = $sku_data_new[$sku]['lof_no'];
                        $sku_num[$sku][$sku_data_new[$sku]['lof_no']]['production_date'] = $sku_data_new[$sku]['production_date'];
                    } else {
                        $sku_num[$sku][$moren['lof_no']]['num'] = $new_sku_num[$sku]['num'];
                        $sku_num[$sku][$moren['lof_no']]['trade_price'] = $new_sku_num[$sku]['trade_price'];
                        $sku_num[$sku][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                        $sku_num[$sku][$moren['lof_no']]['production_date'] = $moren['production_date'];
                    }
                }
            }
        }

        if (!empty($check_ret)) {
            $error_msg = $check_ret;
        }
        if (!empty($sku_num) && !empty($sku_arr)) {
            $sql_values = array();
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.trade_price,g.sell_price from
		    		goods_sku b
		    		inner join base_goods g ON g.goods_code = b.goods_code

		    		where b.barcode in({$sku_str}) ";
            $detail_data = $this->db->get_all($sql, $sql_values); //sell_price
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
                        if ($v1['trade_price']) {
                            $val['trade_price'] = $v1['trade_price'];
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
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'wbm_store_out', $detail_data_lof);
            if ($ret['status'] < 1) {
                return $ret;
            }
            //入库单明细添加
            $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($id, $detail_data_lof);

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未入库', 'action_name' => '增加明细', 'module' => "purchase", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }

        if (!empty($sku_num)) {
//     		if (!empty($sku_num)){
            $sku_error = array_keys($sku_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
//     		}
        }

        $success_num = $sku_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode("\t,", $val_data) . "\r\n";
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
                    $sku_num[$row[0]]['trade_price'] = $row[2];
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
                    $sku_arr[] = $row[0];
//     				$keypi = $row[0].'_'.$row[2];
                    $sku_num[$row[0]][$row[3]]['num'] = $row[1];
                    $sku_num[$row[0]][$row[3]]['trade_price'] = $row[2];
                    $sku_num[$row[0]][$row[3]]['lof_no'] = $row[3];
                    $sku_num[$row[0]][$row[3]]['production_date'] = $row[4];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
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

    function check_pftzd($pftzd_code, &$sku_arr, &$sku_num, &$err_num) {
        $err_data = '';
        if ($pftzd_code) {
            $sql = "select * from wbm_notice_record_detail where record_code = :record_code";
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
                            $err_data[] = array($key => '添加商品数量不能超过批发通知单通知数！');
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                        $err_data[] = array($key => '关联批发通知单中不存在该条码！');
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

    function add_detail_goods($id, $data, $store_code, $api_update_status = NULL) {
        $ret = $this->get_row(array('store_out_record_id' => $id));
        $pftzd_code = $ret['data']['relation_code'];
        $sku_arr = $sku_num = array();
        $err_num = 0;
        foreach ($data as $d) {
            if ($d['num'] > 0) {
                $sku_num[$d['barcode']]['num'] = $d['num'];
                $sku_arr[] = $d['barcode'];
            }
        }
        $check_ret = $this->check_pftzd($pftzd_code, $sku_arr, $sku_num, $err_num);
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'wbm_store_out', $data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //增加明细
        $ret = load_model('wbm/StoreOutRecordDetailModel')->add_detail_action($id, $data, $api_update_status);
        if ($ret['status'] == '1') {
            //日志
            if((CTX()->get_session('user_id'))){
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "store_out_record", 'pid' => $id);
            }else{
                $log = array('user_id' => 1, 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未出库', 'action_name' => '增加明细', 'module' => "store_out_record", 'pid' => $id);
            }
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    function err_handle_type($handle_type, $record_code) {
        //delete_short_inv import_available_inv
        if ($handle_type && $record_code) {
            $record = $this->get_row(array('record_code' => $record_code));
            $id = $record['data']['store_out_record_id'];
            if (isset($record['data']['is_store_out']) && 1 == $record['data']['is_store_out']) {
                return $this->format_ret(false, array(), '销货出库错误');
            }
            $details = load_model('wbm/StoreOutRecordDetailModel')->get_all(array('pid' => $id));
            //检查明细是否为空
            if (empty($details['data'])) {
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
            }
            require_model('prm/InvOpModel');
            $ret_lof_details = $this->db->get_all("select * from b2b_lof_datail where pid = :pid and order_type = :order_type", array(':pid' => $id, ':order_type' => 'wbm_store_out'));

            if (empty($ret_lof_details)) {
                return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
            }
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($record['data']['record_code'], 'wbm_store_out', $record['data']['store_code'], 0, $ret_lof_details);
            //获取inv不足的sku
            $insufficient_sku = $invobj->get_usable_inv($ret_lof_details);
            if (!empty($insufficient_sku)) {
                if ($handle_type == 'delete_short_inv') {
                    $this->begin_trans();
                    try {
                        foreach ($ret_lof_details as $d) {
                            if (array_key_exists($d['sku'], $insufficient_sku)) {
                                $ret = load_model("wbm/StoreOutRecordDetailModel")->delete_lof($d['id']);
                                if ($ret['status'] < 1) {
                                    return $this->format_ret(-1, '', '删除失败');
                                }
                            }
                        }

                        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未验收', 'action_name' => '删除商品', 'module' => "store_out_record", 'pid' => $id, 'action_note' => '一键删除库存不足的商品');
                        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                        $this->commit();
                        return $this->format_ret(1, '', '操作成功！');
                    } catch (Exception $e) {
                        $this->rollback();
                        return array('status' => -1, 'message' => $e->getMessage());
                    }
                }
                if ($handle_type == 'import_available_inv') {
                    $this->begin_trans();
                    try {
                        foreach ($ret_lof_details as $key => $d) {
                            if (array_key_exists($d['sku'], $insufficient_sku)) {
                                $num = $insufficient_sku[$d['sku']];
                                if ($num > 0) {
                                    $ret = $this->db->update('wbm_store_out_record_detail', array('num' => $num), array('record_code' => $record_code, 'sku' => $d['sku']));
                                    if ($ret !== true) {
                                        throw new Exception('以商品可用库存数更新商品数量失败！');
                                    }
                                    $ret = $this->db->update('b2b_lof_datail', array('num' => $num), array('order_code' => $record_code, 'sku' => $d['sku'], 'order_type' => 'wbm_store_out', 'lof_no' => $d['lof_no']));
                                    if ($ret !== true) {
                                        throw new Exception('以商品可用库存数更新商品数量失败！');
                                    }
                                } else {
                                    $ret = load_model("wbm/StoreOutRecordDetailModel")->delete_lof($d['id']);
                                    if ($ret['status'] < 1) {
                                        throw new Exception('可以库存数量小于0，删除商品明细失败！');
                                    }
                                }
                            }
                        }
                        $this->commit();
                        //日志
                        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未验收', 'action_name' => '更新', 'module' => "store_out_record", 'pid' => $id, 'action_note' => '一键以商品可用库存数更新商品数量');
                        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                        return array('status' => 1, 'message' => '更新成功');
                    } catch (Exception $e) {
                        $this->rollback();
                        return array('status' => -1, 'message' => $e->getMessage());
                    }
                }
            }
        } else {
            return $this->format_ret(-1, '', '选择处理方式有误');
        }
    }

    function check_box_record($pid, $sku) {
        if (empty($pid) || empty($sku)) {
            return $this->format_ret(-1, '', '删除失败，请刷新页面重新操作！');
        }
        $data = $this->get_row(array('store_out_record_id' => $pid));
        $box_task = load_model('b2b/BoxTaskModel')->get_row(array('relation_code' => $data['data']['record_code'], 'record_type' => 'wbm_store_out'));
        if ($box_task['status'] == 1) {
            $sql = "select * from b2b_box_record_detail where task_code=:task_code and sku=:sku";
            $record_detail = $this->db->get_row($sql, array(":task_code" => $box_task['data']['task_code'], ":sku" => $sku));
            if (!empty($record_detail)) {
                return $this->format_ret(1, $record_detail, 'have_box_task');
            }
        }
        return $this->format_ret(2, '', 'no_box_task');
    }

    function check_box_record_lof($id) {
        $lof_data = $this->db->get_row("select * from b2b_lof_datail where id=:id", array(':id' => $id));
        if (empty($lof_data)) {
            return $this->format_ret(false, array(), '该批次不存在!不能删除');
        }
        return $this->check_box_record($lof_data['pid'], $lof_data['sku']);
    }

    /**
     * 功能描述     根据批发销货通知单创建批发销货单
     * @author     FBB
     * @date       2016-08-05
     * @param      array $param
     *              array(
     *                  必选: 'relation_code','init_code','store_code'
     *                  可选: 'record_time','remark'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_store_out_record_create($param) {
        if (!isset($param['relation_code']) || empty($param['relation_code'])) {
            return $this->format_ret(-10001, '', '批发通知单号为必填项');
        }
        if (!isset($param['store_code']) || empty($param['store_code'])) {
            return $this->format_ret(-10001, '', '仓库代码为必填项');
        }
        //判断批发通知单号是否存在
        $out_notice_record = $this->check_order_notice_code($param['relation_code']);
        if (empty($out_notice_record) || $out_notice_record['is_finish'] == '1') {
            return $this->format_ret(-10002, $param['relation_code'], '该批发通知单不存在或已完成');
        }
        if ($param['store_code'] != $out_notice_record['store_code']) {
            return $this->format_ret(0, $param['store_code'], '该仓库代码与对应的批发销货通知单中的仓库代码不一致');
        }

        $ret = array();
        if (!empty($param['init_code'])) {
            $ret = $this->check_duplicate_wbm($param['relation_code'], $param['init_code']);
            if (!empty($ret)) {
                return $this->format_ret(-20001, array('record_code' => $ret['record_code']), "原单号：{$param['init_code']},已生成批发销货单");
            }
        } else {
            $param['init_code'] = '';
        }


        $sql = "select * from api_weipinhuijit_store_out_record where notice_record_no=:notice_record_no ";
        $sql_value = array(
            ':notice_record_no' => $param['relation_code'],
        );
        $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
        if (!empty($weipinhuijit_info)) {
            $sql = "SELECT record_code,relation_code,init_code FROM wbm_store_out_record WHERE relation_code=:relation_code ";
            $sql_values = array(':relation_code' => $param['relation_code']);
            if (!empty($param['init_code'])) {
                $sql .= "AND init_code=:init_code ";
                $sql_values[':init_code'] = $param['init_code'];
            }
            $ret = $this->db->get_row($sql, $sql_values);

            if (!empty($ret)) {
                return array('status' => 1, 'message' => '生成成功', 'data' => $ret['record_code']);
            }
        }
        //检查是否会生成重复的批发销货单

        $store_out_record = array();
        $store_out_record['init_code'] = $param['init_code'];
        $store_out_record['record_code'] = $out_notice_record['record_code'];
        $store_out_record['order_time'] = $out_notice_record['order_time'];
        $store_out_record['distributor_code'] = $out_notice_record['distributor_code'];
        $store_out_record['store_code'] = $out_notice_record['store_code'];
        $store_out_record['rebate'] = $out_notice_record['rebate'];
        $store_out_record['jx_code'] = !empty($out_notice_record['jx_code']) ? $out_notice_record['jx_code'] : '';
        $store_out_record['remark'] = (isset($param['remark']) && $param['remark'] != '') ? trim($param['remark']) : '';
        $store_out_record['express_code'] = (isset($param['express_code']) && $param['express_code'] != '') ? trim($param['express_code']) : '';
        $store_out_record['express_no'] = (isset($param['express_no']) && $param['express_no'] != '') ? trim($param['express_no']) : '';
        $store_out_record['express_money'] = (isset($param['express_money']) && $param['express_money'] != '') ? trim($param['express_money']) : '';
        $store_out_record['name'] = !empty($out_notice_record['name']) ? $out_notice_record['name'] : '';
        $store_out_record['tel'] = !empty($out_notice_record['tel']) ? $out_notice_record['tel'] : '';
        $store_out_record['address'] = !empty($out_notice_record['address']) ? $out_notice_record['address'] : '';
        //创建业务日期
        if (empty($param['record_time'])) {
            $store_out_record['record_time'] = date('Y-m-d');
        } else if (strtotime($param['record_time']) == false) {
            return $this->format_ret(0, '', '日期格式错误');
        } else {
            $store_out_record['record_time'] = $param['record_time'];
        }

        $purchase = $this->create_out_record($store_out_record, 'crete_out_by_null');
        if ($purchase['status'] == 1) {
            parent::update_exp('wbm_notice_record', array('is_execute' => 1), array('record_code' => $param['relation_code']));
            $log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '确认', 'finish_status' => '未出库', 'action_name' => '新增', 'module' => "store_out_record", 'pid' => $purchase['data'], 'action_note' => "API调用生成");
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        $data = $this->get_by_field('store_out_record_id', $purchase['data'], 'record_code');
        return array_merge($purchase, array('record_code' => $data['data']['record_code']));
    }

    /**
     * 功能描述     更新批发销货单（明细提交）
     * @author     FBB
     * @date       2016-08-05
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
    public function api_store_out_detail_update($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', '批发销货单号为必填项');
        }
        $check_key = array('商品条码' => 'barcode', '实际出库数' => 'num');
        $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        if ($lof_status['lof_status'] == 1) {
            $check_key = array('条码' => 'barcode', '实际出库数' => 'num', '批次' => 'lof_no', '生产日期' => 'production_date');
        }
        $store_out_detail = json_decode($param['barcode_list'], true);
        if (empty($store_out_detail)) {
            return $this->format_ret(-10005, '', '明细格式错误');
        }
        //检查明细是否为空
        $find_data = $this->api_check_detail($store_out_detail, $check_key);
        if ($find_data['status'] != 1) {
            return $find_data;
        }

        $store_out_record = $this->get_by_field('record_code', $param['record_code']);
        $store_out_record_id = $store_out_record['data']['store_out_record_id'];
        $store_code = $store_out_record['data']['store_code'];
        $relation_code = $store_out_record['data']['relation_code'];
        $data = $this->api_get_goods_detail($store_out_detail, $relation_code);
        if (isset($data['status']) && $data['status'] != 1) {
            return $data;
        }
        $ret = $this->add_detail_goods($store_out_record_id, $data, $store_code, 'no_update_record');
        if ($ret['status'] == '1') {
            //添加操作日志日志
            $log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '添加明细', 'module' => "store_out_record", 'pid' => $store_out_record_id);
            load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * 功能描述     验收批发销货单（影响库存）
     * @author     FBB
     * @date       2016-08-04
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_store_out_record_accept($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', '批发销货单号为必填项');
        }
        $record_code_arr = json_decode($param['record_code'], true);
        if (!is_array($record_code_arr)) {
            $record_code_arr = array($param['record_code']);
        }
        $msg = array();
        foreach ($record_code_arr as $record_code) {
            $ret = $this->do_sure_and_shift_out($record_code, 1, 0);
            if ($ret['status'] == '1') {
                $msg[] = array('status' => 1, 'data' => $record_code, 'message' => $ret['message']);
                //日志
                $log = array('user_id' => '1', 'user_code' => '系统管理员', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '验收', 'action_name' => '验收入库', 'module' => "store_out_record");
                $ret2 = load_model('pur/PurStmLogModel')->insert($log);
            } else {
                if ($ret['status'] === FALSE) {
                    $ret['status'] = 2;
                }
                $msg[] = array('status' => $ret['status'], 'data' => $record_code, 'message' => $ret['message']);
            }
        }
        return $this->format_ret(1, $msg);
    }

    /**
     * 检查批发通知单是否存在
     * @param string $relation_code 批发通知单号
     * @return array
     */
    private function check_order_notice_code($record_code) {
        $sql = "SELECT * FROM wbm_notice_record WHERE record_code=:record_code";
        $sql_values = array(':record_code' => $record_code);
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }

    /**
     * 检查是否生成重复的批发销货单
     * @param string $relation_code 批发通知单号
     * @param string $init_code 原单号
     * @return array
     */
    private function check_duplicate_wbm($relation_code, $init_code) {
        $sql = "SELECT record_code,relation_code,init_code FROM wbm_store_out_record WHERE relation_code=:relation_code AND init_code=:init_code";
        $sql_values = array(':relation_code' => $relation_code, 'init_code' => $init_code);
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($store_out_detail, $check_key) {
        $err_data = array();
        foreach ($store_out_detail as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$v])) {
                    $err_data[$key][$k] = $v;
                }
            }
        }
        if (!empty($err_data)) {
            return $this->format_ret(-10001, $err_data, "明细数据不能为空");
        }
        return $this->format_ret(1);
    }

    /**
     * 获取商品明细信息
     */
    private function api_get_goods_detail($purchase_detail, $relation_code) {
        $data = array();
        foreach ($purchase_detail as $key => $val) {
            $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.purchase_price,g.trade_price FROM goods_sku b
                    INNER JOIN  base_goods g ON g.goods_code = b.goods_code
                    WHERE b.barcode ='{$val['barcode']}' GROUP BY b.barcode ";
            $goods_data = $this->db->get_row($sql);
            if (empty($goods_data)) {
                return $this->format_ret(-10002, array($val['barcode']), '条码不存在');
            }
            $sql1 = "SELECT goods_code,spec1_code,spec2_code,sku,price,price AS trade_price,rebate,money FROM wbm_notice_record_detail WHERE record_code='{$relation_code}' AND sku='{$goods_data['sku']}'";
            $detail_data = $this->db->get_row($sql1);
            if (!empty($detail_data)) {
                unset($goods_data['trade_price']);
            }
            $data[] = array_merge($goods_data, $detail_data, $purchase_detail[$key]);
        }
        return $data;
    }

    /**
     * 获取快递单数据
     */
    public function get_print_express_data($filter) {
        $sql_main = " FROM wbm_store_out_record WHERE 1 ";
        $sql_values = array();
        //默认一次30张快递单据
        $filter['page_size'] = !isset($filter['page_size']) ? 30 : intval($filter['page_size']);
        $select = "*";
        $page = isset($filter['page']) ? (int) $filter['page'] : 1;
        if ($filter['is_print_express'] == '0' && $page > 1) {
            $filter['page'] = 1;
            $page = 1;
        }
        $sql_main .= " AND record_code = :record_code ";
        $sql_values[':record_code'] = $filter['record_code'];

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
        if ($page > (int) $data['filter']['page_count']) {
            $data['data'] = array();
        }
        foreach ($data['data'] as $o_key => &$_order) {
            //订单信息
            $_order['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $_order['store_code']));
            $_order['print_time'] = date('Y-m-d H:i:s');

            //收货人信息
            $custom_info = load_model('base/CustomModel')->get_by_field('custom_code', $_order['distributor_code']);
            $_order['receiver_name'] = $_order['name'];
            $_order['receiver_mobile'] = $_order['tel'];
            $_order['receiver_phone'] = $custom_info['data']['tel'];
            $_order['receiver_province'] = empty($_order['province']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $_order['province']));
            $_order['receiver_city'] = empty($_order['city']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $_order['city']));
            $_order['receiver_district'] = empty($_order['district']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $_order['district']));
            $_order['receiver_street'] = $_order['address'];
            $_order['custom_name'] = $custom_info['data']['custom_name'];
            $_order['receiver_company_name'] = $custom_info['data']['company_name'];
            $_order['receiver_zip_code'] = $custom_info['data']['zipcode'];
            $_order['receiver_fax'] = $custom_info['data']['fax'];

            //发货信息
            $sender_info = load_model('base/StoreModel')->get_by_field('store_code', $_order['store_code']);
            $_order['sender'] = $sender_info['data']['shop_contact_person'];
            $_order['contact_person'] = $sender_info['data']['contact_person'];
            $_order['sender_phone'] = $sender_info['data']['contact_phone'];
            $_order['sender_province'] = empty($sender_info['data']['province']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $sender_info['data']['province']));
            $_order['sender_city'] = empty($sender_info['data']['city']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $sender_info['data']['city']));
            $_order['sender_district'] = empty($sender_info['data']['district']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $sender_info['data']['district']));
            $_order['sender_street'] = empty($sender_info['data']['street']) ? ' ' : oms_tb_val('base_area', 'name', array('id' => $sender_info['data']['street']));
            $_order['sender_addr'] = $sender_info['data']['address'];
            $_order['sender_zip'] = $sender_info['data']['zipcode'];

            //商品信息
            $detail = load_model('wbm/StoreOutRecordDetailModel')->get_by_record_code($filter['record_code']);
            $_order['detail'] = $detail['data'];
            foreach ($_order['detail'] as $key => &$detail_info) {
                $sku_key_arr = array('barcode', 'goods_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail_info['sku'], $sku_key_arr);
                $detail_info = array_merge($detail_info, $sku_info);
                $detail_info['shelf_name'] = load_model('oms/DeliverRecordModel')->get_shelf_name($detail_info['sku'], $_order['store_code']);
                $detail_info['pf_price'] = $detail_info['price'];
                $detail_info['pf_money'] = $detail_info['money'];
            }

            //日志
            $sure_status = $_order['is_sure'] == 0 ? '未确认' : '已确认';
            $finish_status = $_order['is_store_out'] == 0 ? '未出库' : '已出库';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '打印快递单', 'module' => "store_out_record", 'pid' => $_order['store_out_record_id']);
            $ret = load_model('pur/PurStmLogModel')->insert($log);
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 获取校验打印模板
     */
    public function check_template($request) {
        $sql_1 = "SELECT * FROM `base_express` WHERE `express_code` = :express_code";
        $express_info = $this->db->get_row($sql_1, array(':express_code' => $request['express_code']));
        // 查看模板配置，如果不是普通打印提醒用户
        if ($express_info['print_type'] != 0) {
            $message = '请打开“基础数据”，点击“配送方式”，将' . $express_info['express_name'] . '”设置为“普通打印”';
            return $this->format_ret(-1, '', $message);
        }
        if (empty($express_info['pt_id'])) {
            return $this->format_ret(-1, '', '请选择批发快递单模板!');
        }
        $sql_2 = "SELECT is_buildin FROM `sys_print_templates` WHERE `print_templates_id` = '{$express_info['pt_id']}'";
        $is_buildin = $this->db->get_value($sql_2);
        if ($is_buildin != 4) {
            return $this->format_ret(-1, '', '请选择批发快递单模板!');
        }
        return $this->format_ret(1, '', '');
    }

    /**
     * 回写有货采购单
     * @param $jit_out_store_info
     * @param $detail_data
     * @return array
     */
    public function update_yoho_goods(&$yoho_out_store_info, &$detail_data, $store_record_code) {
        $purchase_no = $yoho_out_store_info['purchase_no'];
        $purchase_goods_sql = "select * from api_youhuo_purchase_record_detail where purchase_no=:purchase_no AND numbers>deliver_num ";
        $p_sql_values = array(':purchase_no' => $purchase_no);
        $purchase_goods = $this->db->get_all($purchase_goods_sql, $p_sql_values);

        $delivery_no = $yoho_out_store_info['delivery_no'];
        $delivery_detail = array();
        $sku_arr = array();
        foreach ($purchase_goods as $val) {
            $sku = $val['sku'];
            $delivery_num = 0;
            $wait_delivery_num = $val['numbers'] - $val['deliver_num'];
            $out_num = isset($detail_data[$sku]) ? $detail_data[$sku]['num'] : 0;
            if ($out_num > 0) {
                $delivery_num = ($out_num > $wait_delivery_num) ? $wait_delivery_num : $out_num;
                $detail_data[$sku]['num'] -= $delivery_num;
            }
            if ($delivery_num > 0 && (!isset($sku_arr[$sku]) || $sku_arr[$sku] == 0)) {
                $sku_arr[$sku] = $wait_delivery_num - $delivery_num;
                $sql = "UPDATE api_youhuo_purchase_record_detail
                        SET deliver_num=deliver_num+{$delivery_num}
                        WHERE purchase_no=:purchase_no  AND sku=:sku AND deliver_num=:deliver_num ";
                $sql_value = array(':purchase_no' => $val['purchase_no'], ':sku' => $sku, ':deliver_num' => $val['deliver_num']);
                $status = $this->db->query($sql, $sql_value);
                $num = $this->affected_rows();
                if ($status === false || $num != 1) {
                    $ret = $this->format_ret(1, '', '数据出现处理异常');
                    break;
                }
                $delivery_detail[] = array(
                    'sku' => $sku,
                    'factory_code' => $val['factory_code'],
                    'store_out_record_code' => $store_record_code, //批发销货单
                    'delivery_no' => $yoho_out_store_info['delivery_no'], //出库单
                    'numbers' => $delivery_num,
                );
            }
            if ($detail_data[$sku]['num'] == 0) {
                unset($detail_data[$sku]);
            }
        }
        //生成出库明细
        $ret = $this->insert_multi_exp('api_youhuo_deliver_detail', $delivery_detail);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $sql = "update api_youhuo_purchase_record p,(select sum(deliver_num)"
                . " as deliver_num,purchase_no from api_youhuo_purchase_record_detail where purchase_no='{$purchase_no}') as tmp"
                . " set p.deliver_num=tmp.deliver_num where p.purchase_no=tmp.purchase_no and p.purchase_no='{$purchase_no}'";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            return $this->format_ret('-1', '', '回写有货采购单发货数失败!');
        }
        //更新有货出库单
        $sql = "UPDATE api_youhuo_deliver SET numbers=(SELECT SUM(numbers) FROM api_youhuo_deliver_detail WHERE delivery_no=:delivery_no) WHERE delivery_no=:delivery_no";
        $ret = $this->query($sql, array(':delivery_no' => $delivery_no));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '回写有货出库单数量失败！');
        }
        return $this->format_ret('1', '', '回写成功！');
    }

    /**
     * 判断唯品会生成的批发销货单是否差异出库
     * @param $record_code
     * @return array
     */
    function do_shift_out_weipinhui_check($record_code) {
        $sql = "SELECT 1 FROM api_weipinhuijit_store_out_record WHERE store_out_record_no=:record_code";
        $sql_value = array();
        $sql_value[':record_code'] = $record_code;
        $ret = $this->db->get_row($sql, $sql_value);
        if (empty($ret)) {
            return $this->format_ret(1, '', '非唯品会生成的批发销货单！');
        }
        //判断是否差异出库
        $sql = "SELECT 1 FROM wbm_store_out_record_detail WHERE record_code=:record_code AND num<>enotice_num";
        $ret = $this->db->get_row($sql, $sql_value);
        if (!empty($ret)) {
            return $this->format_ret('-1', '', '唯品会jit销货单未按照通知数量出库，极易出现超卖，请务必确认后再验收！');
        }
        return $this->format_ret(1);
    }

    //根据条件获取装箱任务信息
    public function get_row_by_id($relation_code) {
        $sql_value = array(':relation_code' => $relation_code);
        $num = $this->db->get_row('select count(id) as total from b2b_box_task where record_type = "wbm_store_out" and relation_code = :relation_code', $sql_value);
        /* if($num['total']){
          return $this->format_ret(0,'','装箱单验收请去装箱扫描界面任务验收');
          }else{
          return $this->format_ret(1,'','非装箱单验收可以验收');
          } */
        return $this->format_ret(1, '', '非装箱单验收可以验收');
    }

    public function add_detail($param) {
       $ret =  $this->add_detail_goods($param['record_id'], $param['detail'], $param['store_code']);
       return $ret;
    }

    public function print_data_default_clothing($request){
        $id = $request['record_ids'];
        $r = array();
        $ret = $this->get_by_id($id);
        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
        $ret['data']['address'] = $country . $province . $city . $district . $ret['data']['address'];
        $r['record'] = $ret['data'];
        $sql = "select d.pid,d.goods_code,sum(d.num) num,sum(d.enotice_num) enotice_num,sum(d.money) money,g.category_code,g.goods_name,r3.spec1_name,r3.spec1_code,group_concat(r3.spec2_name,'<|>',d.num) spec2_name,group_concat(d.sku) as sku
                from wbm_store_out_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.record_code=:record_code GROUP by d.goods_code,r3.spec1_name ";
        $r['detail'] = $this->db->get_all($sql, array(':record_code' => $ret['data']['record_code']));
        foreach ($r['detail'] as $key => &$detail) {//合并同一sku
            $shelf_code_arr = array();
            foreach (explode(',',$detail['sku']) as $val){
                $shelf_code = oms_tb_all('goods_shelf', array('store_code' => $r['record']['store_code'], 'sku' => $val));
                $shelf_code_arr = array_merge($shelf_code_arr,array_unique(array_column($shelf_code,'shelf_code')));
            }
            $detail['shelf_code'] = implode(',',$shelf_code_arr);
            $detail['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $detail['category_code']));

        }
        $r['record']['page_no'] = '<span class="page"></span>';
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape_clothing($r['record'], $r['detail']);
        $this->update_print_status($id, 'record');
        return $r;
    }


    public function get_detail_by_record($record_code_id){
        $sql = "select r3.spec2_name
                from wbm_store_out_record wso
                inner join wbm_store_out_record_detail d on d.record_code = wso.record_code
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where wso.store_out_record_id =:store_out_record_id ";
        $ret = $this->db->get_all($sql,array(':store_out_record_id'=>$record_code_id));
        return $this->format_ret(1,array_unique(array_column($ret,'spec2_name')));
    }
    public function get_detail_by_relation($relation_code){
        $sql = "select r3.spec2_name,wsr.store_out_record_id
                from wbm_store_out_record wsr
                INNER JOIN wbm_store_out_record_detail d on d.record_code = wsr.record_code
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                where wsr.relation_code=:relation_code ";
        $ret = $this->db->get_all($sql,array(':relation_code'=>$relation_code));
        return $this->format_ret(1,$ret);
    }
    public function check_is_print_record_clothing($record_code){
        //特性有没有开启
        $status = load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print'];
        if($status != 1){
            return $this->format_ret(-2,'','单据颜色、尺码层商品打印未开启');
        }
        $ret = $this->get_detail_by_record($record_code);
        $size_layer = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $size_layer_arr = json_decode($size_layer,true);
        $size_arr = array();
        foreach ($size_layer_arr as $val){
            $size_arr = array_merge($size_arr,$val);
        }
        foreach ($ret['data'] as $val){
            if(!in_array($val,$size_arr)){
                return $this->format_ret(-2,'',$val.'尺码不在尺码层中');
            }
        }
        return $this->format_ret(1);
    }

}
