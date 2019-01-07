<?php

/**
 * 批发退货单相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class ReturnRecordModel extends TbModel {

    private $is_api = 0;

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
            '总退货数' => 'num',
            '总金额' => 'money',
            '备注' => 'remark',
            '总退货通知数' => 'enotice_num_all',
//            '快递单号' => 'express',
//            '配送方式' => 'express_name',
//            '运费' => 'express_money',
//            '联系人' => 'name',
//            '联系电话' => 'tel',
//            '地址' => 'address',
            //  '总通知金额' => 'enotice_money',
//            '总差异数' => 'diff_num',
//            '仓库寄件人' => 'shop_contact_person',
//            '仓库联系人' => 'contact_person',
//            '仓库联系电话' => 'sender_phone',
//            '仓库店铺留言' => 'message',
//            '仓库店铺留言2' => 'message2',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
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
                '实际退货数' => 'num',
                '通知数' => 'enotice_num',
//                '差异数' => 'diff_num',
                '金额' => 'money',
                '商品条形码' => 'barcode',
//                '库位' => 'shelf_code',
//                '商品分类' => 'category_name',
//                '商品品牌' => 'brand_name',
//                '商品季节' => 'season_name',
//                '商品年份' => 'year_name',
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
//        $country = oms_tb_val('base_area', 'name', array('id' => $ret['data']['country']));
//        $province = oms_tb_val('base_area', 'name', array('id' => $ret['data']['province']));
//        $city = oms_tb_val('base_area', 'name', array('id' => $ret['data']['city']));
//        $district = oms_tb_val('base_area', 'name', array('id' => $ret['data']['district']));
//        $ret['data']['address'] = $country . $province . $city . $district . $ret['data']['address'];
        $r['record'] = $ret['data'];
        $r['record']['enotice_num_all'] = load_model('wbm/ReturnRecordDetailModel')->get_enotice_num_all($r['record']['record_code']);
        if ($is_lof == 1) {
            $sql = "select d.pid
                ,d.goods_code,d.num,l.init_num as enotice_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,l.lof_no,r3.spec1_code,r3.spec2_code
                from wbm_return_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code
                left JOIN goods_sku r3 on r3.sku = d.sku
                INNER JOIN  b2b_lof_datail l  ON l.sku = r3.sku AND d.record_code=l.order_code AND l.order_type='wbm_return'
                where d.pid=:pid";
        } else {
            $sql = "select d.pid
                ,d.goods_code,d.num,d.enotice_num,d.rebate,d.sku,d.refer_price,d.price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code
                from wbm_return_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code

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
        $this->update_print_status($id);
        return $d;
    }
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
                ,d.goods_code,d.num,d.enotice_num,d.rebate,d.sku,d.refer_price,d.price as pf_price,d.money
                ,g.goods_name,g.goods_short_name,g.goods_produce_name,g.sell_price as dp_price
                ,g.category_code,g.brand_code,g.season_code,g.year_code as year,g.weight
                ,r3.spec1_name,r3.spec2_name,r3.barcode,r3.spec1_code,r3.spec2_code
                from wbm_return_record_detail d
                left join base_goods g  ON d.goods_code = g.goods_code

                left JOIN goods_sku r3 on r3.sku = d.sku
                where d.pid=:pid";
            $r['detail'] = $this->db->get_all($sql, array(':pid' => $id));           
            //获取单价 应为批发价*折扣          
            foreach ($r['detail'] as $key => $value) {
                $r['detail'][$key]['price'] = $r['detail'][$key]['pf_price'] * $r['detail'][$key]['rebate'];  
                $r['detail'][$key]['price'] = round($r['detail'][$key]['price'], 3);
            }          
            $sku_array = array();
            $shelf_code_arr = array();
            foreach ($r['detail'] as $key => $detail) {//合并同一sku
                $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['store_code']);
                $shelf_code_arr[] = $detail['shelf_code'];
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);              
            }
        }
        $r['record']['page_no'] ='<span class="page"></span>';
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape_new($r['record'], $r['detail']);
        $trade_data = array($r['record']);
        $this->update_print_status($id);
        return $r;
    }
    function print_data_escape_new(&$record, &$detail){
            $record['enotice_num_all'] = load_model('wbm/ReturnRecordDetailModel')->get_enotice_num_all($record['record_code']);
            $record['distributor_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code'=>$record['distributor_code']));
            $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code'=>$record['store_code']));
            $record['print_time'] = date('Y-m-d H:i:s');
            $record['print_user'] = CTx()->get_session('user_name');
            $i = 1;
            foreach($detail as &$v){
                $v['sort_num'] = $i;
                $i++;
            }   
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

    function get_table() {
        return 'wbm_return_record';
    }
    
    //更新打印状态
    public function update_print_status ($return_record_id) {
        $sql = "SELECT is_print_record FROM wbm_return_record WHERE return_record_id = :return_record_id ";
        $data = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        if (empty($data)) {
            return array('status' => '-1', 'message' => '批发退货单不存在');
        }
        //添加日志
        $this->add_print_log($return_record_id);
        //更新状态
        $this->update_exp( 'wbm_return_record', array('is_print_record' => 1 ), array('return_record_id' => $return_record_id ));
        
    }
    
    //打印日志记录
    public function add_print_log($return_record_id) {
        // 增加打印日志
        $sql = "SELECT is_sure,is_store_in FROM wbm_return_record WHERE return_record_id = :return_record_id  ";
        $d = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        $params = array();
        $params['sure_status'] = $d['is_sure'] == 1 ? "已确认" : "未确认";
        $params['finish_status'] = $d['is_store_in'] == 0 ? '未验收' : '验收';
        $params['module'] = 'wbm_return_record';
        $params['action_name'] = '打印';
        $params['id'] = $return_record_id;
        $this->add_log($params);
    }
    
    //检查是否打印
    public function check_is_print ($return_record_id) {
        $sql = "SELECT is_print_record FROM wbm_return_record WHERE return_record_id = :return_record_id ";
        $data = $this->db->get_row($sql, array(':return_record_id'=>$return_record_id));
        $ret = $data['is_print_record'] == 1 ? $this->format_ret(-1, '', '重复打印批发退货单，是否继续打印？') : $this->format_ret(1, '', '');
        return $ret;
        
    }
    
    
    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        if ((isset($filter['code_name']) && $filter['code_name'] != '' ) || (isset($filter['goods_name']) && $filter['goods_name'] != '' ) || (isset($filter['goods_code']) && $filter['goods_code'] != '') || (isset($filter['barcode']) && $filter['barcode'] != '') || $filter['ctl_export_conf'] == 'return_record_list_detail') {
            $sql_join .= " LEFT JOIN wbm_return_record_detail r2 on rl.record_code = r2.record_code LEFT JOIN base_goods r3 on r3.goods_code = r2.goods_code ";
            if (isset($filter['barcode']) && $filter['barcode'] != '' && $filter['ctl_export_conf'] != 'return_record_list_detail') {
                $sql_join .= " LEFT JOIN goods_sku r4 on r4.sku = r2.sku";
            }
        }
        $sql_main = " FROM {$this->table} rl {$sql_join} WHERE 1 ";

        $sql_values = array();
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('custom_power'));
        if($ret_cfg['custom_power'] == 1) { //开启分销商业务权限
            $custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
            $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('rl.distributor_code', $custom_code);
        } else {
            if(isset($filter['distributor_code']) && $filter['distributor_code'] != '') {
                $arr = explode(',', $filter['distributor_code']);
                $str = $this->arr_to_in_sql_value($arr, 'distributor_code', $sql_values);
                $sql_main .= " AND rl.distributor_code in ({$str}) ";
            }
        }

        // 单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        // 通知编号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (rl.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = $filter['relation_code'] . '%';
        }
        // 原单号
        if (isset($filter['init_code']) && $filter['init_code'] != '') {
            $sql_main .= " AND (rl.record_code LIKE :record_code )";
            $sql_values[':record_code'] = $filter['record_code'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ({$str}) ";
        }
        // 单据状态
        if (isset($filter['is_store_in']) && $filter['is_store_in'] != '') {
            $arr = explode(',', $filter['is_store_in']);
            $str = $this->arr_to_in_sql_value($arr, 'is_store_in', $sql_values);
            $sql_main .= " AND rl.is_store_in in ({$str}) ";
        }

        //业务类型
        if (isset($filter['type_code']) && $filter['type_code'] != '') {
            			        $arr = explode(',', $filter['type_code']);
            $str = $this->arr_to_in_sql_value($arr, 'record_type_code', $sql_values);
			$sql_main .= " AND rl.record_type_code in ( " . $str. " ) ";
        }
        //商品
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //备注
        if (isset($filter['remark']) && $filter['remark'] != '') {
            $sql_main .= " AND  rl.remark LIKE :remark";
            $sql_values[':remark'] = '%' . $filter['remark'] . '%';
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
            if($filter['time_type']=='order_time'){
                //下单日期
                if (isset($filter['time_start']) && $filter['time_start'] != '') {
                    $sql_main .= " AND (rl.order_time >= :time_start )";
                    $sql_values[':time_start'] = $filter['time_start'];
                }
                if (isset($filter['time_end']) && $filter['time_end'] != '') {
                    $sql_main .= " AND (rl.order_time <= :time_end )";
                    $sql_values[':time_end'] = $filter['time_end'];
                }
            }else if($filter['time_type']=='is_store_in_time'){
                //验收时间
                if (isset($filter['time_start']) && $filter['time_start'] != '') {
                    $sql_main .= " AND (rl.is_store_in_time >= :time_start )";
                    $sql_values[':time_start'] = $filter['time_start'];
                }
                if (isset($filter['time_end']) && $filter['time_end'] != '') {
                    $sql_main .= " AND (rl.is_store_in_time <= :time_end )";
                    $sql_values[':time_end'] = $filter['time_end'];
                }
            }
        }

        // 商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'return_record_list_detail') {
                $sql_main .= $this->get_sku_by_record_code($filter['barcode']);
            } else {
                $sql_main .= " AND (r4.barcode LIKE :barcode )";
                $sql_values[':barcode'] = $filter['barcode'] . '%';
            }
        }

        //$select = 'rl.*,r2.goods_name,r2.goods_name,r2.weight';
        //判断是否是导出明细
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'return_record_list_detail') {
            $select = "rl.is_store_in,rl.record_code,rl.relation_code,rl.order_time,rl.record_time,rl.distributor_code,rl.store_code,r3.goods_name,r3.goods_code,r2.enotice_num,r2.num,r2.price,r2.money,r2.rebate,r2.spec1_code,r2.spec2_code,r2.sku,rl.remark";
            $sql_main .= " order by rl.order_time desc ";
        } else {
            $select = 'rl.*';
            //  $sql_main .= " group by rl.record_code order by record_time desc,record_code desc";
            $sql_main .= " group by rl.record_code order by rl.order_time desc,record_code desc";
        }
//        var_dump($sql_main,$sql_values);die;
        // echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        /*
          foreach ($data['data'] as $key => $value) {
          //

          } */
        //判断是否是导出明细
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'return_record_list_detail') {
            foreach ($data['data'] as $key => &$value) {
                $value['price1'] = $value['price'] * $value['rebate'];
                $value['diff_num'] = $value['enotice_num'] - $value['num'];
            }
        }
        filter_fk_name($data['data'], array('store_code|store', 'distributor_code|custom', 'distributor_code|custom', 'record_type_code|record_type', 'spec1_code|spec1_code', 'spec2_code|spec2_code', 'sku|barcode'));
            foreach($data['data'] as &$val){
            $data_name = load_model('base/RecordTypeModel')->get_by_field('record_type_code',$val['record_type_code'],'record_type_name');
            $val['type_name'] = $data_name['data']['record_type_name'];
            $val['is_store_code'] = ($val['is_store_in'] == 1) ? "已验收" : "未验收";
            $val['remark'] = $val['remark']."\t";
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_sku_by_record_code($barcode) {
        $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
        if (empty($sku_arr)) {
            return " AND  1=2 ";
        } else {
            $sku_str = "'" . implode("','", $sku_arr) . "'";
            $sql = "select record_code from wbm_return_record_detail where sku in({$sku_str}) ";
            $record_code_arr = $this->db->get_all($sql);
            $new_arr = array();
            foreach ($record_code_arr as $code) {
                $new_arr[] = $code['record_code'];
            }
            $record_code_str = "'" . implode("','", $new_arr) . "'";
            return " AND r2.record_code in({$record_code_str}) ";
        }
    }

    function get_by_id($id) {
        $data = $this->get_row(array('return_record_id' => $id));

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
        if ($data['is_store_in'] == 1) {
            return $this->format_ret('-1', array(), '单据已经入库，不能删除！');
        }
        $ret = parent::delete(array('return_record_id' => $return_record_id));
        $this->db->create_mapper('wbm_return_record_detail')->delete(array('pid' => $return_record_id));
        $this->db->create_mapper('b2b_lof_datail')->delete(array('pid' => $return_record_id, 'order_type' => 'wbm_return'));
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "确认", 'finish_status' => '未入库', 'action_name' => "删除", 'module' => "wbm_return_record", 'pid' => $return_record_id);
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
        if (!isset($where['return_record_id']) && !isset($where['record_code'])) {
            return $this->format_ret(false, array(), '修改时缺少主单据ID或code!');
        }
        $result = $this->get_row($where);
        if ($result['data']['is_store_in'] !=0 ) {
            foreach ($data as $key => $value) {
                if ($key != 'remark') {
                    unset($data[$key]);
                }
            }
        }
        if (1 != $result['status']) {
            return $this->format_ret(false, array(), '没找到单据!');
        }
//        if(1==$result['data']['is_check_and_accept']){
//            return $this->format_ret(false,array(),'单据已经验收,不能修改!');
//        }
        if (isset($data['store_code'])) {
            $ret = load_model('stm/GoodsInvLofRecordModel')->modify_store_code($data['store_code'], $result['data']['record_code']);
        }
            //日志
            if($result['data']['is_sure'] == 1){
               $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '已验收', 'action_name' => '修改', 'module' => "wbm_return_record", 'pid' => $where['return_record_id']);
            }else{
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未验收', 'action_name' => '修改', 'module' => "wbm_return_record", 'pid' => $where['return_record_id']);
            }
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        //更新主表数据
        return parent::update($data, $where);
    }

    //确认/取消确认，
    function update_sure($active, $field, $id) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/return_record/do_sure')) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //###########
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }


        $record = $this->get_row(array('return_record_id' => $id));
        $details = load_model('wbm/ReturnRecordDetailModel')->get_all(array('pid' => $id));
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL_EMPTY');
        }
        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_return');
        //$ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id,'shift');
        if (empty($ret_lof_details['data'])) {
            return $this->format_ret('-1', '', 'RECORD_ERROR_DETAIL');
        }


        $ret = parent:: update(array($field => $active), array('return_record_id' => $id));
        //$ret= parent:: update(array('is_store_out' => 1), array('return_record_id' => $id));
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
        $jdh = "PT" . date("Ymd") . add_zero($djh);
        return $jdh;
    }
    
    //检查差异数
    function check_diff_num ($record_code) {
        $record = $this->get_row(array('record_code' => $record_code));
        $id = $record['data']['return_record_id'];
        if (isset($record['data']['is_store_in']) && 1 == $record['data']['is_store_in']) {
            return $this->format_ret(-1, array(), '该单据已经入库!');
        }
        if ($record['data']['relation_code'] == '') {
            return $this->format_ret(1, array(), '');
        }
        $data = $this->db->get_row("SELECT SUM(num) AS finish_num,SUM(enotice_num) AS enotice_num FROM `wbm_return_record_detail` WHERE pid=:pid", array(':pid'=>$id));
        if ($data['enotice_num']>=0 && $data['finish_num'] == 0) {
            return $this->format_ret(2, array(), '是否确认整单验收？ ');
        }
        $ret = $this->db->get_row("SELECT record_code FROM wbm_return_record_detail WHERE pid = :pid AND num != enotice_num", array(':pid'=>$id));
        if (!empty($ret['record_code'])) {
            return $this->format_ret(2, array(), '是否确认差异验收？ ');
        }
        if ($data['enotice_num'] == $data['finish_num']) {
            return $this->format_ret(1, array(), '');
        }
    }
    
    /**
     * 批发退货单，验收
     *
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since  2014-11-11
     * @param int $id
     * @return array
     */
    function shift_in($record_code) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('wbm/return_record/do_shift_in') && $this->is_api != 1) {
            return $this->format_ret(-1, array(), '无权访问');
        }
        //###########
        //检查调整单状态是否为已验收
        $record = $this->get_row(array('record_code' => $record_code));
        $id = $record['data']['return_record_id'];
        $details = load_model('wbm/ReturnRecordDetailModel')->get_all(array('pid' => $id));
        if (isset($record['data']['is_store_in']) && 1 == $record['data']['is_store_in']) {
            return $this->format_ret(false, array(), '该单据已经入库!');
        }
        //检查明细是否为空
        if (empty($details['data'])) {
            return $this->format_ret(false, array(), '单据明细不能为空!');
        } else {
            $sql = "SELECT SUM(num) FROM wbm_return_record_detail WHERE record_code = :record_code";
            $sum_num = $this->db->getOne($sql, array(':record_code' => $record_code));

            if ($sum_num == 0) {
                $sql = "UPDATE wbm_return_record_detail SET num = enotice_num WHERE record_code =:record_code";
                $this->db->query($sql, array(':record_code' => $record_code));
            }
            $money_sql = "UPDATE wbm_return_record_detail SET money = price * rebate * num WHERE record_code = :record_code";
            $this->db->query($money_sql, array(':record_code' => $record_code));
            $details = load_model('wbm/ReturnRecordDetailModel')->get_all(array('pid' => $id));
        }

        $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->get_by_pid($id, 'wbm_return');
        if (empty($ret_lof_details['data'])) {
            //在没有明细的情况下,自动维护批次库存数据

            $ret_lof_details = load_model('stm/GoodsInvLofRecordModel')->update_lof_record(
                    'wbm_return', 0, $record['data']['store_code'], $id, $record['data']['record_code'], date('Y-m-d', strtotime($record['data']['order_time'])), $details['data']);
        }
        $this->begin_trans();
        $up_data = array('is_store_in' => 1, 'is_store_in_time' => date('Y-m-d H:i:s'), 'is_sure' => 1, 'record_time' => date('Y-m-d'));
        if (empty($record['data']['record_time']) || $record['data']['record_time'] == '1970-01-01') {
            $up_data['record_time'] = date('Y-m-d H:i:s');
        }

        $ret = parent:: update($up_data, array('return_record_id' => $id));
        require_model('prm/InvOpModel');

        $invobj = new InvOpModel($record['data']['record_code'], 'wbm_return', $record['data']['store_code'], 3, $ret_lof_details['data']);

        $ret = $invobj->adjust();
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }
        //回写完成数
        if (!empty($record['data']['relation_code'])) {
            $ret = $this->update_finish_num($record_code, $record['data']['relation_code']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }


        $jx_return_code = '';
        if (!empty($record['data']['relation_code'])) {
            //是否是经销退货单
            $sql = "SELECT jx_return_code FROM wbm_return_notice_record WHERE return_notice_code = '{$record['data']['relation_code']}';";
            $jx_return_code = $this->db->get_value($sql);
            foreach ($details['data'] as $detail) {
                if ($detail['num'] != 0) {
                    $finish_num = $detail['num'];
                    //回写批发通知退货单完成数
                    $sql = "update wbm_return_notice_detail_record set finish_num = finish_num+" . $finish_num . " where return_notice_code='" . $record['data']['relation_code'] . "' and sku='" . $detail['sku'] . "'";
                    $ret2 = $this->db->query($sql);
                    if (!empty($jx_return_code)) {
                        //回写经销采购退货单完成数
                        $sql = "UPDATE fx_purchaser_return_record_detail fx SET fx.finish_num = fx.finish_num + {$finish_num} WHERE fx.record_code = (SELECT wr.jx_return_code FROM wbm_return_notice_record wr WHERE wr.return_notice_code = '{$record['data']['relation_code']}') AND fx.sku = '{$detail['sku']}'; ";
                        $ret4 = $this->db->query($sql);
                    }
                }
            }
            load_model('wbm/ReturnNoticeDetailRecordModel')->mainWriteBackfinish($record['data']['relation_code']);
            if (!empty($jx_return_code)) {
                //回写经销采购退货单主表
                load_model('fx/PurchaseReturnRecordDetailModel')->mainWriteBackfinish($record['data']['relation_code']);
            }
        }
        //回写主单完成数量 num money

        $wbm_return_record_details = load_model('wbm/ReturnNoticeDetailRecordModel')->get_all_details($record['data']['relation_code']);
        $is_finsih = 1;
        foreach ($wbm_return_record_details['data'] as $detail) {
            if ($detail['finish_num'] < $detail['num']) {
                $is_finsih = 0;
                break;
            }
        }
        if ($is_finsih == 1) {
            $ret3 = load_model('wbm/ReturnNoticeRecordModel')->do_finish($record['data']['relation_code']);
        }
        if ($ret['status'] != 1) {
            $this->rollback(); //事务回滚
            return $ret;
        }

        load_model('wbm/ReturnRecordDetailModel')->mainWriteBack($record['data']['return_record_id']);

                  //推送到中间表
            $ret = load_model('mid/MidBaseModel')->set_mid_record('receiving', $record_code, 'wbm_return', $record['data']['store_code']);
            if ($ret['status'] < 0) {
                $this->rollback(); //事务回滚
                return $ret;
            }
        $this->commit(); //事务提交
        return $this->format_ret(1, $id, '验收成功!');
    }


    function update_finish_num($record_code,$relation_record_code) {
        //是否有货采购退单生成的批发退货单
        $yoho_store_ret = load_model('api/YohoReturnModel')->get_data_by_relation($relation_record_code);
        if (!empty($yoho_store_ret)) {
            $ret_detail = load_model('wbm/ReturnRecordDetailModel')->get_by_record_code($record_code);
            $detail_data = array();
            foreach ($ret_detail['data'] as $val) {
                $detail_data[$val['sku']] = $val;
            }
            foreach ($yoho_store_ret as $yoho_in_store_info) {
                if (!empty($detail_data)) {
                    $ret_update = $this->update_yoho_goods($yoho_in_store_info, $detail_data);
                    if ($ret_update['status'] < 1) {
                        return $ret_update;
                    }
                }
            }
        }
        return $this->format_ret('1', '', '');
    }


    public function update_yoho_goods(&$yoho_in_store_info, &$detail_data) {
        $purchase_no = $yoho_in_store_info['purchase_no'];
        $purchase_goods_sql = "select * from api_youhuo_return_detail where purchase_no=:purchase_no";
        $p_sql_values = array(':purchase_no' => $purchase_no);
        $purchase_goods = $this->db->get_all($purchase_goods_sql, $p_sql_values);

        $sku_arr = array();
        foreach ($purchase_goods as $val) {
            $sku = $val['sku'];
            $in_num = 0;
            $wait_in_num = $val['numbers'] - $val['store_in_num'];
            $shift_in_num = isset($detail_data[$sku]) ? $detail_data[$sku]['num'] : 0;
            if ($shift_in_num > 0) {
                $in_num = ($shift_in_num > $wait_in_num) ? $wait_in_num : $shift_in_num;
                $detail_data[$sku]['num'] -= $in_num;
            }
            if ($in_num > 0 && (!isset($sku_arr[$sku]) || $sku_arr[$sku] == 0)) {
                $sku_arr[$sku] = $wait_in_num - $in_num;
                $sql = "UPDATE api_youhuo_return_detail
                        SET store_in_num=store_in_num+{$in_num}
                        WHERE purchase_no=:purchase_no  AND sku=:sku AND store_in_num=:store_in_num ";
                $sql_value = array(':purchase_no' => $val['purchase_no'], ':sku' => $sku, ':store_in_num' => $val['store_in_num']);
                $status = $this->db->query($sql, $sql_value);
                //$num = $this->affected_rows();
                if ($status == false) {
                    return $this->format_ret(-1, '', '数据出现处理异常');
                 //   break;
                }
            }
            if ($detail_data[$sku]['num'] == 0) {
                unset($detail_data[$sku]);
            }
        }

        //更新主单
        $sql = "update api_youhuo_return p,(select sum(store_in_num)"
            . " as store_in_num,purchase_no from api_youhuo_return_detail where purchase_no='{$purchase_no}') as tmp"
            . " set p.store_in_num=tmp.store_in_num where p.purchase_no=tmp.purchase_no and p.purchase_no='{$purchase_no}'";
        $ret = $this->query($sql);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '回写有货采购退单验收数失败!');
        }
        return $this->format_ret('1', '', '回写成功！');
    }

    public function create_return_record($return_notice_record, $type = "create_return_unfinish") {
        if($return_notice_record['jx_return_code'] != '') { //经销退货单生成的通知单只能生成一次退货单
            $return_record = $this->get_by_field('relation_code',$return_notice_record['return_notice_code']);
            if(!empty($return_record['data'])) {
                return $this->format_ret(-1,'','经销退货单生成的通知单只能生成一张退货单');
            }
        }
        $record_code = $this->create_fast_bill_sn();
        $return_record = array();
        //主表信息
        // 		$ret = load_model('wbm/ReturnRecordModel')->insert($stock_adjus);
        $return_record['record_code'] = isset($return_notice_record['record_code']) ? $return_notice_record['record_code'] : $record_code;
        $return_record['order_time'] = $return_notice_record['order_time'];
        $return_record['relation_code'] = $return_notice_record['return_notice_code'];
        $return_record['distributor_code'] = $return_notice_record['custom_code'];
        $return_record['store_code'] = $return_notice_record['store_code'];
        $return_record['rebate'] = $return_notice_record['rebate'];
        $return_record['remark'] = $return_notice_record['remark'];
        $return_record['record_type_code'] = $return_notice_record['return_type_code'];
        if (isset($return_notice_record['record_time'])) {
            $return_record['record_time'] = $return_notice_record['record_time'];
        }else{
            $return_record['record_time'] = date('Y-m-d H:i:s');
        }
        if (isset($return_notice_record['init_code'])) {
            $return_record['init_code'] = $return_notice_record['record_time'];
        }
        $opt_user_code = $this->is_api == 1 ? 'admin' : CTX()->get_session('user_code');
        $opt_user_id = $this->is_api == 1 ? '1' : CTX()->get_session('user_id');
        $this->begin_trans();
        try {
            $ret = $this->insert($return_record);
            $pid = $ret['data'];
            //接口调用LOG
            $api_log = array('user_id' => '1', 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '新增', 'module' => "wbm_return_record", 'pid' => $pid ,'action_note' => 'API调用生成');
            //按未完成数生成
            if ($type == "create_return_unfinish") {
                $sql = "select goods_code,spec1_code,spec2_code,barcode,sku,trade_price,sell_price,rebate,price,num,finish_num from wbm_return_notice_detail_record
					where return_notice_code = '{$return_notice_record['return_notice_code']}'";
                $data = $this->db->get_all($sql);
                //批次档案维护

                $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $data);
                if ($ret['status'] != 1) {
                    throw new Exception('批次档案维护失败');
                }
                //如果是经销退货单生成的退货通知单，就给退货单添加批次信息
                /*if ($return_notice_record['jx_return_code'] != '') {
                    $sql = "SELECT goods_code,spec1_code,spec2_code,sku,num,init_num,lof_no,production_date FROM b2b_lof_datail WHERE order_code = '{$return_notice_record['jx_return_code']}';";
                    $lof_data = $this->db->get_all($sql);
                    foreach ($lof_data as &$val) {
                        $val['barcode'] = oms_tb_val('goods_sku', 'barcode', array('sku' => $val['sku']));
                        $val['num'] = 0;
                    }
                    //单据批次添加
                    $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($pid, $return_notice_record['store_code'], 'wbm_return', $lof_data);
                    if ($ret['status'] != 1) {
                        throw new Exception('单据批次添加失败');
                    }
                }*/
                
                //通知单的数量生成退单时 为通知数
                foreach ($data as $key => $return_info) {
                    if ($return_info['num'] <= $return_info['finish_num']) {
                        unset($data[$key]);
                        continue;
                    }
                    $data[$key]['enotice_num'] = $return_info['num'] - $return_info['finish_num'];
                    unset($data[$key]['num']);
                    unset($data[$key]['finsih_num']);
                }
                if (empty($data)) {
                    throw new Exception('退单已经全部生成 ，不可再生成退单');
                } else {
                    //日志
                    $log = array('user_id' => $opt_user_id, 'user_code' => $opt_user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '新增', 'module' => "wbm_return_record", 'pid' => $pid ,'action_note' => "批发退货通知单({$return_notice_record['return_notice_code']})生成");
                    if($this->is_api == 1){ //接口调用
                        $log = $api_log;
                    }
                    $ret1 = load_model('pur/PurStmLogModel')->insert($log);
                }
                $ret = load_model('wbm/ReturnRecordDetailModel')->add_detail_action($pid, $data);
                if ($ret['status'] != 1) {
                    throw new Exception('退单明细保存失败');
                } else {
                    //日志
                    $log1 = array('user_id' => $opt_user_id, 'user_code' => $opt_user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "wbm_return_record", 'pid' => $pid);
                    if($this->is_api == 1){ //接口调用
                        $log1 = array('user_id' => 1, 'user_code' => 'OPENAPI', 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "wbm_return_record", 'pid' => $pid);
                    }
                    $ret1 = load_model('pur/PurStmLogModel')->insert($log1);
                }
            } else {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '新增', 'module' => "wbm_return_record", 'pid' => $pid ,'action_note' => "批发退货通知单({$return_notice_record['return_notice_code']})生成");
                 if($this->is_api == 1){ //接口调用
                        $log = $api_log;
                    }
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }

            $this->commit();
            return array('status' => 1, 'message' => '更新成功', 'data' => $pid);
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    function imoprt_detail($id, $file, $is_lof = 0) {
        $ret = $this->get_row(array('return_record_id' => $id));
        $store_code = $ret['data']['store_code'];
        $relation_code = $ret['data']['relation_code'];
        $barcode_arr = $barcode_num = array();
        $error_msg = '';
        $err_num = 0;
        //未开启批次导入库存方法
        if ($is_lof == '1') {
            $num = $this->read_csv_lof($file, $barcode_arr, $barcode_num);
        } else {
            $num = $this->read_csv_sku($file, $barcode_arr, $barcode_num);
            //没有开启批次的 查询默认批次 如果批次表里 没有，在批次表里添加goods_lof
            $barcode_str = implode("','", $barcode_arr);
            $sql = "select g.barcode,g.sku,l.lof_no,l.production_date,l.type from goods_lof l "
                    . "INNER JOIN goods_sku g ON g.sku=l.sku  where"
                    . " l.sku in ('$barcode_str') group by l.sku";
            $sku_data = $this->db->get_all($sql);
            $sql_moren = "select lof_no,production_date from goods_lof  where type=1";
            $moren = $this->db->get_row($sql_moren);
            $lof_data_new = array();
            foreach ($sku_data as $lof_data) {
                $lof_data_new[$lof_data['barcode']]['production_date'] = $lof_data['production_date'];
                $lof_data_new[$lof_data['barcode']]['lof_no'] = $lof_data['lof_no'];
                $lof_data_new[$lof_data['barcode']]['sku'] = $lof_data['sku'];
            }
            $new_barcode_num = $barcode_num;
            $barcode_num = array();
            foreach ($barcode_arr as $barcode) {
                if (array_key_exists($barcode, $lof_data_new)) {
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['num'] = $new_barcode_num[$barcode]['num'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['trade_price'] = $new_barcode_num[$barcode]['trade_price'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['lof_no'] = $lof_data_new[$barcode]['lof_no'];
                    $barcode_num[$barcode][$lof_data_new[$barcode]['lof_no']]['production_date'] = $lof_data_new[$barcode]['production_date'];
                } else {
                    $barcode_num[$barcode][$moren['lof_no']]['num'] = $new_barcode_num[$barcode]['num'];
                    $barcode_num[$barcode][$moren['lof_no']]['trade_price'] = $new_barcode_num[$barcode]['trade_price'];
                    $barcode_num[$barcode][$moren['lof_no']]['lof_no'] = $moren['lof_no'];
                    $barcode_num[$barcode][$moren['lof_no']]['production_date'] = $moren['production_date'];
                }
            }
        }

        $sku_count = count($barcode_arr);
        $check_ret = $this->check_pftzd($relation_code, $barcode_arr, $barcode_num, $err_num);
        if (!empty($check_ret)) {
            $error_msg = $check_ret;
        }

        if (!empty($barcode_num) && !empty($barcode_arr)) {
            $sql_values = array();
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,b.sku,g.price,g.trade_price,g.sell_price  from
		    		goods_sku b
		    		inner join  base_goods g ON g.goods_code = b.goods_code
		    		where b.barcode in({$barcode_str}) GROUP BY b.barcode";
            $detail_data = $this->db->get_all($sql,$sql_values); //sell_price
            //  var_dump($barcode_arr,$detail_data);die;
            $detail_data_lof = array();
            $temp = array();
            foreach ($detail_data as $key => $val) {
                foreach ($barcode_num[$val['barcode']] as $k1 => $v1) {
                    if (intval($v1['num']) > 0) {
                        $val['num'] = $v1['num'];
                        $val['lof_no'] = $v1['lof_no'];
                        $val['production_date'] = $v1['production_date'];
                        //$val['pici_lof'] = '1';
                        $val['trade_price'] = !empty($v1['trade_price']) ? $v1['trade_price'] : $val['trade_price'];
                        $detail_data_lof[] = $val;
                        unset($barcode_num[$val['barcode']]);
                    } else {
                        $error_msg[] = array($val['barcode'] => '数量不能为空');
                        $err_num ++;
                        unset($barcode_num[$val['barcode']]);
                    }
                }
            }

            //批次档案维护
            $ret = load_model('prm/GoodsLofModel')->add_detail_action($id, $detail_data_lof);
            //单据批次添加
            $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'wbm_return', $detail_data_lof);
            if ($ret['status']<1) {
                return $ret;
            } 
            //入库单明细添加
            $ret = load_model('wbm/ReturnRecordDetailModel')->add_detail_action($id, $detail_data_lof);

            if ($ret['status'] == '1') {
                //日志
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => '未验收', 'action_name' => '导入明细', 'module' => "wbm_return_record", 'pid' => $id);
                $ret1 = load_model('pur/PurStmLogModel')->insert($log);
            }
            $ret['data'] = '';
        }


        if (!empty($barcode_num)) {
            $sku_error = array_keys($barcode_num);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息');
                $err_num ++;
            }
        }
        $success_num = $sku_count - $err_num;
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

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("wbm_return_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function check_pftzd($record_code, &$sku_arr, &$sku_num, &$err_num) {
        $err_data = '';
        if ($record_code) {
            $sql = "select * from wbm_return_notice_detail_record where return_notice_code = :record_code";
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
                        if ($sku_num[$key]['num'] > $new_record[$key]) {
                            $err_data[] = array($key => '添加商品数量不能超过批发退货通知单通知数！');
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                        $err_data[] = array($key => '关联批发退货通知单中不存在该条码！');
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
        // var_dump($sku_arr,$sku_num);die;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('gbk', 'utf-8', $val);  中文转码后变false
//                $val = mb_convert_encoding($val,'utf-8','gbk'); 中文转码后变乱码
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }

    function add_detail_goods($id, $data, $store_code) {
        $ret = $this->get_row(array('return_record_id' => $id));
        $record_code = $ret['data']['relation_code'];
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
        $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($id, $store_code, 'wbm_return', $data);
        if ($ret['status']<1) {
             return $ret;
         } 
        //增加明细
        $ret = load_model('wbm/ReturnRecordDetailModel')->add_detail_action($id, $data);
        if ($ret['status'] == '1') {
            $opt_user_code = $this->is_api == 1 ? 'admin' : CTX()->get_session('user_code');
            $opt_user_id = $this->is_api == 1 ? '1' : CTX()->get_session('user_id');
            //日志
            $log = array('user_id' => $opt_user_id, 'user_code' => $opt_user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'finish_status' => '未验收', 'action_name' => '增加明细', 'module' => "wbm_return_record", 'pid' => $id);
            $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        }
        return $ret;
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


        $i = 1;
//        $enotice_money = 0;
        foreach ($detail as $k => &$v) {
            $v['pf_price'] = $v['price'];
            $v['price'] = $v['price'] * $v['rebate'];
//            $enotice_money = $v['price'] * $v['enotice_num'];
//            $v['diff_num'] = abs($v['enotice_num'] - $v['num']);
            $v['sort_num'] = $i;

//            $v['category_name'] = oms_tb_val('base_category', 'category_name', array('category_code' => $v['category_code']));
//            $v['brand_name'] = oms_tb_val('base_brand', 'brand_name', array('brand_code' => $v['brand_code']));
//            $v['season_name'] = oms_tb_val('base_season', 'season_name', array('season_code' => $v['season_code']));
//            $v['year_name'] = oms_tb_val('base_year', 'year_name', array('year_code' => $v['year']));
            $i++;
        }

        foreach ($detail as $k => &$v) {

        }
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user_name'] = CTX()->get_session('user_name');
        //差异数
        $record['diff_num'] = $record['enotice_num'] - $record['num'];
//        $record['enotice_money'] = $enotice_money;
    }

    /**
     * @todo 新增日志
     */
    function add_log($param) {
        $opt_user_id = $this->is_api == 1 ? 1 : CTX()->get_session('user_id');
        $opt_user_code = $this->is_api == 1 ? 'admin' : CTX()->get_session('user_code');
        $suer_status = isset($param['sure_status']) ? $param['sure_status'] : '确认';
        $finish_status = isset($param['finish_status']) ? $param['finish_status'] : '未完成';
        $log = array('user_id' => $opt_user_id, 'user_code' => $opt_user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $suer_status, 'finish_status' => $finish_status, 'action_name' => $param['action_name'], 'module' => $param['module'], 'pid' => $param['id']);
        $ret = load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * 功能描述     根据批发退货通知单创建批发退货单
     * @author     Baota
     * @date       2016-08-12
     * @param      array $param
     *              array(
     *                  必选: 'relation_code','init_code','store_code'
     *                  可选: 'record_time','remark'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","data":"""message":"保存成功"}
     */
    public function api_return_record_create($param) {
        $key_required = array(
            's' => array('relation_code', 'store_code'),
        );
        //可选字段
        $key_option = array(
            's' => array('record_time', 'remark', 'init_code')
        );

        $r_required = array();
        $r_option = array();
        $status = '-1';
        $data_msg = array();
        try {
            $this->begin_trans();
            //验证必选字段是否为空并提取必选字段数据
            $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
            if ($ret_required['status'] != TRUE) {
                $status = '-10001';
                $data_msg = $ret_required['req_empty'];
                throw new Exception('API_STM_MESSAGE_10001');
            }

            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $r_option);
            $record = array_merge($r_required, $r_option); //主单据数据
            unset($r_required, $r_option, $param);

            //判断批发退货通知单是否存在
            $return_notice_record = load_model('wbm/ReturnNoticeRecordModel')->is_exists($record['relation_code']);
            $return_notice_record = $return_notice_record['data'];
            //检查是否会生成重复的批发退货单
            $ret = $this->check_duplicate_wbm($record['relation_code'], $record['init_code']);
            if (!empty($ret)) {
                //若创建唯品会批发退货单, 直接返回退货单号(已生成)
                $sql = "select return_record_no from api_weipinhuijit_return_record where notice_record_no=:notice_record_no ";
                $sql_value = array(':notice_record_no' => $record['relation_code']);
                $weipinhuijit_info = $this->db->get_row($sql, $sql_value);
                if ($record['store_code'] != $return_notice_record['store_code']) {
                    $status = '0';
                    $data_msg['store_code'] = $record['store_code'];
                    throw new Exception('该仓库代码与对应的批发退货通知单中的仓库代码不一致');
                }
                if (!empty($weipinhuijit_info)){
                    $this->rollback();
                    return $this->format_ret(1, array('record_code' => $weipinhuijit_info['return_record_no']), '操作成功');
                }
                $status = '-20001';
                $data_msg = $ret;
                throw new Exception('原单号已生成批发退货单');
            }

            //创建业务日期
            if (empty($record['record_time'])) {
                $record['record_time'] = date('Y-m-d');
            } else if (strtotime($record['record_time']) == false) {
                $status = '0';
                $data_msg['record_time'] = $record['record_time'];
                throw new Exception('日期格式错误');
            }


            if (empty($return_notice_record) || $return_notice_record['is_finish'] == '1') {
                $status = '-10002';
                $data_msg['relation_code'] = $record['relation_code'];
                throw new Exception('该批发退货通知单不存在或已完成');
            } else {
                if ($record['store_code'] != $return_notice_record['store_code']) {
                    $status = '0';
                    $data_msg['store_code'] = $record['store_code'];
                    throw new Exception('该仓库代码与对应的批发退货通知单中的仓库代码不一致');
                }
            }

            $record = array_merge($return_notice_record, $record);
            $record['record_code'] = $this->create_fast_bill_sn();
            $this->is_api = 1;

            $log_data['action_name'] = '确认';
            $log_data['id'] = $record['return_notice_record_id'];
            $log_data['module'] = 'wbm_return_notice_record';

            //通知单确认
            $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check(1, 'is_check', $record['return_notice_code']);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message']);
            } else {
                $this->add_log($log_data);
            }

            //生成退单
            $ret = $this->create_return_record($record, 'create_return_unfinish');
            if ($ret['status'] == 1) {
                //回写通知单生成退单状态
                $sql = 'UPDATE wbm_return_notice_record SET is_return = 1 WHERE return_notice_code=:return_notice_code';
                $this->query($sql, array(':return_notice_code' => $record['return_notice_code']));
                $log_data['action_name'] = '生成退单';
                $this->add_log($log_data);
            } else {
                throw new Exception($ret['message']);
            }
            $this->commit();
            return $this->format_ret(1, array('record_code' => $record['record_code']), '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * 功能描述     更新批发退货单（明细提交）
     * @author     Baota
     * @date       2016-08-13
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
    public function api_return_detail_update($param) {
        $status = '-1';
        $data_msg = array();
        try {
            if (!isset($param['record_code']) || empty($param['record_code'])) {
                $status = '-10001';
                $data_msg = array('record_code');
                throw new Exception('批发退货单号为必填项');
            }
            $check_key = array('商品条码' => 'barcode', '实际入库数' => 'num');
            $lof_status = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
            if ($lof_status['lof_status'] == 1) {
                $check_key = array('商品条码' => 'barcode', '实际入库数' => 'num', '批次' => 'lof_no', '生产日期' => 'production_date');
            }
            $return_detail = json_decode($param['barcode_list'], true);
            if (empty($return_detail)) {
                $status = '-10005';
                throw new Exception('明细格式错误');
            }
            //检查明细是否为空
            $find_data = $this->api_check_detail($return_detail, $check_key);
            if ($find_data['status'] != 1) {
                $status = '-10001';
                $data_msg = $find_data['data'];
                throw new Exception($find_data['message']);
            }
            $this->is_api = 1;
            $return_record = $this->get_by_field('record_code', $param['record_code']);
            $return_record_id = $return_record['data']['return_record_id'];
            $store_code = $return_record['data']['store_code'];
            $record_code = $return_record['data']['record_code'];
            $data = $this->api_get_goods_detail($return_detail, $record_code);
            if (isset($data['status']) && $data['status'] != 1) {
                return $data;
            }
            $ret = $this->add_detail_goods($return_record_id, $data, $store_code);
            if ($ret['status'] != 1) {
                throw new Exception($ret['message']);
            }
            return $this->format_ret(1, '更新成功');
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

    /**
     * 功能描述     验收批发退货单（影响库存）
     * @author     Baota
     * @date       2016-08-13
     * @param      array $param
     *              array(
     *                  必选: 'record_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_return_record_accept($param) {
        if (!isset($param['record_code']) || empty($param['record_code'])) {
            return $this->format_ret(-10001, '', '批发销货单号为必填项');
        }
        $record_code_arr = json_decode($param['record_code'], true);
        if (!is_array($record_code_arr)) {
            $record_code_arr = array($param['record_code']);
        }
        $this->is_api = 1;
        $log_data['sure_status'] = '';
        $log_data['finish_status'] = '验收';
        $log_data['action_name'] = '验收';
        $log_data['module'] = 'wbm_return_record';
        $msg = array();
        foreach ($record_code_arr as $record_code) {
            $ret = $this->shift_in($record_code);
            if ($ret['status'] == '1') {
                $msg[] = array('status' => 1, 'data' => $record_code, 'message' => $ret['message']);
                $log_data['id'] = $ret['data'];
                $this->add_log($log_data);
            } else {
                $msg[] = array('status' => $ret['status'], 'data' => array('record_code' => $record_code), 'message' => $ret['message']);
            }
        }
        return $this->format_ret(1, $msg);
    }

    /**
     * 检查是否生成重复的批发退货单
     * @param string $relation_code 批发退货通知单号
     * @param string $init_code 原单号
     * @return array
     */
    private function check_duplicate_wbm($relation_code, $init_code) {
        $sql = "SELECT record_code,relation_code,init_code FROM wbm_return_record WHERE relation_code=:relation_code";
        $sql_values = array(':relation_code' => $relation_code);
        if (!empty($init_code)){
            $sql .= " AND init_code=:init_code";
            $sql_values[':init_code'] = $init_code;
        }
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
    private function api_get_goods_detail($return_detail, $record_code) {
        $data = array();
        foreach ($return_detail as $key => $val) {
            $sql = "SELECT b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode FROM goods_sku b
                    WHERE b.barcode =:barcode";
            $goods_data = $this->db->get_row($sql, array(':barcode' => $val['barcode']));
            if (empty($goods_data)) {
                return $this->format_ret(-10002, array($val['barcode']), '条码不存在');
            }
            $sql = "SELECT goods_code,spec1_code,spec2_code,sku,price,refer_price,rebate,money FROM wbm_return_record_detail WHERE record_code=:record_code AND sku=:sku";
            $detail_data = $this->db->get_row($sql, array(':record_code' => $record_code, ':sku' => $goods_data['sku']));
            $data[] = array_merge($goods_data, $detail_data, $return_detail[$key]);
        }
        return $data;
    }

    public function add_detail($param) {
        $ret = $this->add_detail_goods($param['record_id'], $param['detail'], $param['store_code']);
        return $ret;
    }

}
