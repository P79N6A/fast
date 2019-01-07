<?php

/**
 * 装箱单单相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('stm');
require_lib('util/oms_util', true);

class BoxRecordModel extends TbModel {

    public $print_fields_default = array(
        'record' => array(
            '箱号' => 'record_code',
            '装箱任务号' => 'task_code',
            '扫描人' => 'scan_user',
            '关联单号' => 'relation_code',
            '装箱时间' => 'create_time',
            '关联单类型' => 'record_type_name',
            '仓库' => 'store_code_name',
            //'折扣' => 'rebate',
            '总数量' => 'num',
            '总金额' => 'money',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '销货单备注' => 'remark',
            '唯品会送货仓' => 'warehouse_name',
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
                '数量' => 'num',
                '商品条形码' => 'barcode',
                '库位' => 'shelf_code',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品重量' => 'weight',
                '单价' => 'price',
                '吊牌价' => 'sell_price',
                '成本价' => 'cost_price',
                '批发价' => 'trade_price',
                '进货价' => 'purchase_price',
                '金额' => 'money',
                '折扣' => 'rebate',
            ),
        ),
    );

    function get_task_code() {
        $params = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
        preg_match("/&task_code=(\w+)/", $params, $mats);
        $task_code = @$mats[1];
        return $task_code;
    }

    function get_table() {
        return 'b2b_box_record';
    }

    //根据装箱单获取批发单信息
    private function get_pf_info($record) {
        $sql = "select w.*
                from wbm_store_out_record w
                inner join b2b_box_task b
                on w.record_code = b.relation_code
                where b.task_code = :task_code";
        $pf_record = $this->db->get_row($sql, array(":task_code" => $record['task_code']));
        return $this->format_ret(1, $pf_record);
    }

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default($request) {
        $r = array();
        $id = $request['record_ids'];
        $ret = $this->get_by_id($id);

        $r['record'] = $ret;
        if ($r['record']['record_type'] == 'wbm_store_out') {
            $sql1 = "select storage_no,po_no,warehouse from api_weipinhuijit_store_out_record where store_out_record_no='{$r['record']['relation_code']}';";
            $v1 = $this->db->get_row($sql1);
            foreach ($v1 as $key => $value) {
                $r['record'][$key] = $value;                
            }
            //唯品会发货仓
            $sql = "SELECT warehouse_name FROM api_weipinhuijit_warehouse WHERE warehouse_code = :warehouse_code ";
            $r['record']['warehouse_name'] = $this->db->get_value($sql,array(':warehouse_code' => $v1['warehouse']));
            $sql2 = "select pick_no from api_weipinhuijit_store_out_record where store_out_record_no='{$r['record']['relation_code']}';";
            $v2 = $this->db->get_all($sql2);
            $p = array();
            foreach ($v2 as $key => $value) {
                $p[] = $value['pick_no'];
            }
            $r['record']['pick_no'] = implode(',', $p);
        }
        $r['record']['custom_name'] = '';
        $pf_record = $this->get_pf_info($ret);
        if (!empty($pf_record['data'])) {
            $r['record']['custom_name'] = $this->db->get_value("select custom_name from base_custom where custom_code = :custom_code", array(":custom_code" => $pf_record['data']['distributor_code']));
            $r['record']['remark'] = $pf_record['data']['remark'];
        }
        $sql = "select d.goods_code,d.num,d.sku,
    	g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price,g.cost_price,g.trade_price,g.purchase_price
    	,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
    	,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code,r3.gb_code
    	from b2b_box_record_detail d
    	left join base_goods g  ON d.goods_code = g.goods_code
    	left JOIN goods_sku r3 on r3.sku = d.sku
    	where d.record_code=:record_code";
        $r['detail'] = $this->db->get_all($sql, array(':record_code' => $r['record']['record_code']));
        $store_code = $r['record']['store_code'];
//    	left join goods_price gp  ON d.goods_code = gp.goods_code
//    	left join base_spec1 s1 ON d.spec1_code = s1.spec1_code
//    	left join base_spec2 s2 ON d.spec2_code = s2.spec2_code
//    	left JOIN goods_barcode r3 on r3.sku = d.sku
        foreach ($r['detail'] as &$val) {
            $shelf_code_arr = load_model('prm/GoodsShelfModel')->get_goods_shelf($store_code, $val['sku']);
            $val['shelf_code'] = implode(",", $shelf_code_arr);
            $shelf_code_arr = load_model('prm/GoodsBarcodeChildModel')->get_child_barcode_info($val['sku'], 'sku', 'barcode');
            $barcode_child_arr = array_column($shelf_code_arr, 'barcode');
            $val['barcode_child'] = implode(",", $barcode_child_arr);
        }
        //页码
        $r['record']['page_no'] ='<span class="page"></span>';
        // 数据替换
        $this->print_data_escape($r['record'], $r['detail']);
        /*
          $d = array('record'=>array(), 'detail'=>array());
          foreach($r['record'] as $k => $v) {
          // 键值对调
          $nk = array_search($k, $this->print_fields_default['record']);
          $nk = $nk === false ? $k : $nk;
          $d['record'][$nk] = is_null($v) ? '' : $v; //$v; //
          }
          foreach($r['detail'] as $k1 => $v1) {
          // 键值对调
          foreach($v1 as $k => $v){
          $nk = array_search($k, $this->print_fields_default['detail'][0]);
          $nk = $nk === false ? $k : $nk;
          $d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
          }
          } */

        // 更新状态
        $this->print_update_status($ret['record_code']);
        //print_r($d);exit;
        return $r;
    }

    /**
     * 装箱汇总单打印数据
     */
    public function get_print_aggr_box_data($request) {
        $record_code = $request['record_code'];
        $data = array();
        //单据主信息
        $sql = "SELECT sr.store_out_record_id,bt.task_code,sr.record_code,sr.distributor_code AS custom_code,sr.store_code,COUNT(br.id) AS box_num ,bt.num,COUNT(br.money) AS money FROM b2b_box_task bt
                INNER JOIN b2b_box_record br ON bt.task_code=br.task_code
                INNER JOIN wbm_store_out_record sr ON bt.relation_code=sr.record_code WHERE bt.relation_code=:code";
        $record = $this->db->get_row($sql, array(':code' => $record_code));
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        $record['custom_name'] = get_custom_name_by_code($record['custom_code']);
        $record['store_name'] = get_store_name_by_code($record['store_code']);
        $task_code = $record['task_code'];
        unset($record['custom_code'], $record['store_code'], $record['task_code']);

        //单据明细
        $sql = "SELECT br.box_order,bg.brand_name,bg.goods_name,bg.goods_code,gs.barcode,gs.spec1_name,gs.spec2_name,gs.spec1_code,gs.spec2_code,rd.num,sr.price,sr.rebate,rd.sku FROM b2b_box_record br
                INNER JOIN b2b_box_record_detail rd ON br.record_code=rd.record_code
                INNER JOIN base_goods bg ON rd.goods_code=bg.goods_code
                INNER JOIN goods_sku gs ON rd.sku=gs.sku
                INNER JOIN wbm_store_out_record_detail sr ON sr.sku=rd.sku
                WHERE br.task_code=:task_code AND sr.record_code=:code";
        $detail = $this->db->get_all($sql, array(':task_code' => $task_code, ':code' => $record_code));
        $sku_arr = array();
        foreach ($detail as $key => &$val) {
            $val['price'] = $val['price'] * $val['rebate'];
            $detail[$key]['money'] = $val['price'] * $val['num'];
            unset($detail[$key]['rebate']);
            $sku_arr[$val['sku']] = $val['sku'];
        }
        $record['sku_num'] = count($sku_arr);
        $data['record'] = $record;
        $data['detail'] = $detail;
        //更新状态
        load_model('wbm/StoreOutRecordModel')->update_print_status($record['store_out_record_id'],'box');
        return $data;
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail) {


        $i = 1;
        $enotice_money = 0;
        foreach ($detail as $k => &$v) {
            $v['sort_num'] = $i;
            $i++;

            switch ($record['record_type']) {
                case 'wbm_store_out':

                    $sql = "select a.price,a.rebate from wbm_store_out_record_detail a
    				where a.record_code = :record_code  and a.sku = :sku";

                    $danjudetail = $this->db->get_row($sql, array('record_code' => $record['relation_code'], 'sku' => $v['sku']));

                    $v['price'] = $danjudetail['price'];
                    $v['rebate'] = $danjudetail['rebate'];
                    $v['money'] = $v['price'] * $v['rebate'] * $v['num'];
                    $v['price'] = $danjudetail['price'] * $v['rebate'];
                    break;
                case 'pur_purchaser':
                    break;
            }
            $v['shelf_code'] = $this->get_shelf_code($record['store_code'], $v['sku']);

            $record['money'] += $v['money'];
            //$record['num'] += $v['num'];
            /*
              $v['diff_num'] = abs($v['enotice_num']-$v['num']);
             */
            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $v['brand_code']));
            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $v['season_code']));
            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $v['year']));
        }

        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        //print_r($record);
        //print_r($detail);
        //差异数
        //$record['diff_num'] = $record['enotice_num'] - $record['num'];
        //$record['enotice_money'] = $enotice_money ;
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

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
     //   $task_code = $this->get_task_code();
        // if (empty($filter['task_code'])){
        //$filter['task_code'] = $task_code;
        //}
        //$sql_join = "";
        $sql_main_table = "FROM {$this->table} rl
    	LEFT JOIN b2b_box_task r2 on rl.task_code = r2.task_code";
        $select = 'rl.*,r2.relation_code,r2.record_type,r2.is_check_and_accept AS is_accept';
        if(!isset($filter['do_list_tab']) || $filter['do_list_tab'] == ''){
            $filter['do_list_tab'] = 'tabs_all';
        }
        //订单波次打印tab页面
        if (isset($filter['do_list_tab']) && $filter['do_list_tab'] !== '') {
            //唯品会jit
            if ($filter['do_list_tab'] == 'tabs_jit') {
                $sql_main_table .= " INNER JOIN api_weipinhuijit_store_out_record r5 on r5.store_out_record_no = r2.relation_code LEFT JOIN api_weipinhuijit_warehouse r6 on r6.warehouse_code = r5.warehouse";
                $select .= ',r5.store_out_record_no,r6.warehouse_name';
                } elseif($filter['do_list_tab'] == 'tabs_all') {
                $sql_main_table .= " LEFT JOIN api_weipinhuijit_store_out_record r5 on r5.store_out_record_no = r2.relation_code
                               LEFT JOIN api_weipinhuijit_warehouse r6 on r6.warehouse_code = r5.warehouse";
                $select .= ',r5.store_out_record_no,r6.warehouse_name';
            }else{
                $sql_main_table .=" LEFT JOIN api_weipinhuijit_store_out_record r5 on r5.store_out_record_no = r2.relation_code";
                $sql_general=' AND r5.store_out_record_no IS NULL';
                $select .= ',r5.store_out_record_no ';
            }
        }

        $sql_values = array();
        $sql_main = " WHERE 1";
        $sql_table = '';
        // 箱号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        // 装箱任务号
        if (isset($filter['task_code']) && $filter['task_code'] != '') {
            $sql_main .= " AND (rl.task_code LIKE :task_code )";
            $sql_values[':task_code'] = $filter['task_code'] . '%';
        }
        // 关联单号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (r2.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        // 创建人
        if (isset($filter['create_user']) && $filter['create_user'] != '') {
            $sql_main .= " AND (rl.create_user LIKE :create_user )";
            $sql_values[':create_user'] = $filter['create_user'] . '%';
        }
        // 验收
        if (isset($filter['is_check_and_accept']) && $filter['is_check_and_accept'] != '' && $filter['is_check_and_accept'] != 'all') {
            $sql_main .= " AND (rl.is_check_and_accept = :is_check_and_accept )";
            $sql_values[':is_check_and_accept'] = $filter['is_check_and_accept'];
        }
        // 商品条形码
        if (isset($filter['barcord']) && $filter['barcord'] != '') {
            $sql_table = " LEFT JOIN b2b_box_record_detail r3 on r3.record_code = rl.record_code "
                    . " LEFT JOIN goods_sku r4 on r4.sku = r3.sku ";
            $sql_main .= " AND (r4.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcord'] . '%';
        }
        // 商品码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_table = " LEFT JOIN b2b_box_record_detail r3 on r3.record_code = rl.record_code "
                    . " LEFT JOIN goods_sku r4 on r4.sku = r3.sku ";
            $sql_main .= " AND (r3.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        // 转换
        if (isset($filter['is_change']) && $filter['is_change'] != '' && $filter['is_change'] != 'all') {
            $sql_main .= " AND (rl.is_change = :is_change )";
            $sql_values[':is_change'] = $filter['is_change'];
        }
        // 打印状态
        $filter['is_print'] = !isset($filter['is_print']) ? '0' : $filter['is_print'];
        if (isset($filter['is_print']) && $filter['is_print'] != '' && $filter['is_print'] != 'all') {
            $sql_main .= " AND (rl.is_print = :is_print )";
            $sql_values[':is_print'] = $filter['is_print'];
        }

        // 关联单类型
        /*
          if (isset($filter['record_type']) && $filter['record_type'] != '') {
          $arr = explode(',',$filter['record_type']);
          $str = "'".join("','",$arr)."'";
          $sql_main .= " AND r2.record_type in ({$str}) ";
          }
         */
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (rl.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (rl.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //下单日期
        if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
            $sql_main .= " AND (rl.create_time >= :create_time_start )";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . " 00:00:00";
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
            $sql_main .= " AND (rl.create_time <= :create_time_end )";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . " 23:59:59";
        }
        //送货仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            if ($filter['do_list_tab'] != 'tabs_general') {
                $warehouse = deal_strs_with_quote($filter['warehouse']);
                $sql_main .= " AND warehouse in({$warehouse}) ";
            }else{
                 $sql_main .= " AND 1=2 ";
            }
        }
        //拣货单号
        if (isset($filter['pick_no']) && $filter['pick_no'] != '') {
            if ($filter['do_list_tab'] != 'tabs_general') {
                $sql_main .= " AND (r5.pick_no LIKE :pick_no )";
                $sql_values[':pick_no'] = $filter['pick_no'] . '%';
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->box_record_search_csv($sql_main, $sql_values, $filter,$sql_main_table);
        }
        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        // $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";
        // $sql_main .= "  order by record_time  desc";
        $sql_main=$sql_main_table.$sql_table.$sql_main;
        if (isset($sql_general)) {
            $sql_main.=$sql_general;
        }
        $sql_main .= " group by rl.record_code order by rl.create_time desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$value) {
            if (!empty($data['data'][$key]['store_out_record_no'])) {
                $relation_code = $data['data'][$key]['relation_code'];
                $pick_no = $this->get_pick_no($relation_code);
                $p = array();
                foreach ($pick_no as $v) {
                    $p[] = $v['pick_no'];
                }
                $value['pick_no'] = implode(',', $p);
            }
        }

        //filter_fk_name($data['data'], array('store_code|store','distributor_code|custom','distributor_code|custom','record_type_code|record_type'));
        //dump($data,1);
        // print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //导出
    public function box_record_search_csv($sql_main, $sql_values, $filter,$sql_main_table) {
        $sql_main=$sql_main_table.' LEFT JOIN b2b_box_record_detail r3 on r3.record_code = rl.record_code
                                    LEFT JOIN goods_sku r4 on r4.sku = r3.sku '.$sql_main;
        $select = 'rl.*,r2.relation_code,r2.record_type,r3.spec1_code,r3.spec2_code,r3.goods_code,r3.num as num_mx,r4.barcode,r4.spec1_name,r4.spec2_name';
        if($filter['do_list_tab'] != 'tabs_general'){
          $select.=',r6.warehouse_name';
        }
        $sql_main .= " group by rl.record_code,r3.sku order by rl.create_time desc"; // group by rl.record_code
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach ($data['data'] as $key => &$value) {
            $relation_code = $data['data'][$key]['relation_code'];
            $pick_no = $this->get_pick_no($relation_code);
            $p = array();
            foreach ($pick_no as $v) {
                $p[] = $v['pick_no'];
            }
            $value['pick_no'] = implode(',', $p);
            $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_pick_no($relation_code) {
        $sql = "select pick_no from api_weipinhuijit_store_out_record where store_out_record_no=:relation_code;";
        $sql_value[':relation_code'] = $relation_code;
        $data = $this->db->get_all($sql, $sql_value);
        return $data;
    }

    function get_by_id($id) {
        $sql = "select r1.*,r2.relation_code,r2.record_type from b2b_box_record r1
				INNER JOIN b2b_box_task r2 on r2.task_code = r1.task_code
				where r1.id = '{$id}'
    	        ";
        $data = $this->db->get_row($sql);
        filter_fk_name($data, array('store_code|store'));
        switch ($data['record_type']) {
            case 'wbm_store_out':
                $data['record_type_name'] = '批发销货单';
                break;
            case 'pur_purchaser':
                $data['record_type_name'] = '采购入库单';
                break;
        }
        return $data;
    }

    /**
     * @param api_goods_sku_id
     * @param array $filter
     * @return array
     */
    function get_list_by_item_id($record_code, $filter = array()) {

        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_code,r4.spec2_code,r4.spec1_name,r4.spec2_name';
        $sql_main = " FROM b2b_box_record_detail r2
    				  INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		              INNER JOIN goods_sku r4 on r2.sku = r4.sku
		              WHERE record_code='{$record_code}'
    				";

        $data = $this->get_page_from_sql($filter, $sql_main, array(), $select);

        $ret_status = OP_SUCCESS;
        filter_fk_name($data['data'], array('store_code|store'));
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    public function get_record_print_page($filter) {
        $sql_main = "FROM {$this->table} rl
    	INNER JOIN b2b_box_task r2 on rl.task_code = r2.task_code
    	INNER JOIN api_weipinhuijit_store_out_record rr on rr.store_out_record_no = r2.relation_code
    	INNER JOIN api_weipinhuijit_delivery r3 on r3.delivery_id = rr.delivery_id where 1";

        $sql_values = array();
        $filter['page_size'] = !isset($filter['page_size']) ? 30 : intval($filter['page_size']); //默认一次30张快递单据

        if (isset($filter['ids']) && !empty($filter['ids'])) {
            $sql_main .= " AND rl.id in ({$filter['ids']}) ";
        }
        $sql_main .= " GROUP BY rl.record_code ";
        $select = "rl.box_order,rl.record_code,r3.delivery_id, r3.brand_code,r3.warehouse,r3.storage_no,r3.arrival_time,r3.express_code,r3.carrier_name,r3.express as delivery_no ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $warehouse_name = load_model("api/WeipinhuijitPickModel")->weipinhui_warehouse(0);
        foreach ($data['data'] as &$_order) {
            $_order['warehouse_name'] = $warehouse_name[$_order['warehouse']]['name'];
            $_order['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $_order['brand_code']));
            $_order['storage_no_code'] = $_order['storage_no'];
            $_order['record_code_code'] = $_order['record_code'];
            $_order['delivery_no_code'] = $_order['delivery_no'];
            $_order['arrival_time_code'] = $_order['arrival_time'];
            $_order['carrier_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $_order['express_code']));
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 获取普通箱唛打印数据
     */
    public function get_general_box_print_page($filter) {
        $sql_main = "FROM {$this->table} br
                    INNER JOIN b2b_box_task bt ON br.task_code = bt.task_code AND bt.record_type='wbm_store_out'
                    INNER JOIN wbm_store_out_record sr ON sr.record_code = bt.relation_code
                    INNER JOIN b2b_box_record_detail rd ON br.record_code = rd.record_code WHERE 1";

        $sql_values = array();
        $filter['page_size'] = !isset($filter['page_size']) ? 30 : intval($filter['page_size']); //默认一次30张快递单据

        if (isset($filter['ids']) && !empty($filter['ids'])) {
            $sql_main .= " AND br.id in ({$filter['ids']}) ";
        }
        $sql_main .= " GROUP BY br.record_code ";
        $select = " br.box_order,br.num,sr.record_code,sr.distributor_code as custom_code,sr.remark,sr.`name`,sr.tel,sr.address,COUNT(rd.sku) as sku_num ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $custom_name = get_custom_name_by_code($data['data'][0]['custom_code']);
        foreach ($data['data'] as &$val) {
            $val['destination_city'] = $val['address'];
            $val['custom_name'] = $custom_name;
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 更新打印状态
     * @param $id
     * @param $type
     * @return array
     * @throws Exception
     */
    public function print_update_status($record_code) {
        $this->db->update('b2b_box_record', array('is_print' => 1), array('record_code' => $record_code));
    }

    /**
     * 修改明细数量，回写装箱单主单据
     * @param string $record_code 装箱单号
     * @return array 回写结果
     */
    public function mainWriteBack($record_code) {
        //回写数量和金额
        $sql = 'UPDATE b2b_box_record AS br SET
                  br.num = (SELECT SUM(rd.num) FROM b2b_box_record_detail AS rd WHERE rd.record_code=:record_code)
                WHERE br.record_code=:record_code';
        $res = $this->query($sql, array(':record_code' => $record_code));
        return $res;
    }

}
