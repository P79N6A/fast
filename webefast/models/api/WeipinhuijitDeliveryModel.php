<?php

require_model('tb/TbModel');

class WeipinhuijitDeliveryModel extends TbModel {

    protected $table = "api_weipinhuijit_delivery";
    protected $detail_table = "api_weipinhuijit_delivery_detail";
    public $carrier_list = array(
        'YTO' => array('code' => '1200000558', 'name' => '圆通(入库)'),
        'YUNDA' => array('code' => '1200000559', 'name' => '韵达(入库)'),
        'ZTO' => array('code' => '1200000557', 'name' => '申通(入库)'),
        'SF' => array('code' => '1200000540', 'name' => '顺丰(入库)'),
        'STO' => array('code' => '1200000557', 'name' => '申通(入库)'),
        'HTKY' => array('code' => '1200000560', 'name' => '汇通(入库)'),
        // 'TTKDEX'=>array('code'=>'2000000062','name'=>'天天快递'),
        'DBL' => array('code' => '1200000536', 'name' => '德邦(入库)'),
        //'GTO'=>array('code'=>'2000000067','name'=>'国通快递'),
        'QFKD' => array('code' => '1200000561', 'name' => '全峰(入库)'),
        'ZJS' => array('code' => '1200000564', 'name' => '宅急送(入库)'),
        'UC' => array('code' => '1200000544', 'name' => '优速(入库)'),
        'CRE' => array('code' => '1200000545', 'name' => '中铁(入库)'),
        // 'FAST'=>array('code'=>'2000000083','name'=>'快捷快递'),
        // 'SURE'=>array('code'=>'2000000061','name'=>'速尔快递'),
        'EMS' => array('code' => '1200000563', 'name' => '邮政(入库)'),
        'KYE' => array('code' => '1200000538', 'name' => '跨越(入库)'),
        'CNEX' => array('code' => '1200000556', 'name' => '佳吉(入库)'),
        'YHX' => array('code' => '120000842', 'name' => '永和迅(入库)'),
        'pinjun express' => array('code' => '120000831', 'name' => '品骏航空(入库)'), //极地火
        'PINJUN' => array('code' => '120000831', 'name' => '品骏航空(入库)'),
        'DHWL' => array('code' => '120000846', 'name' => '大鸿(入库)'),
        'JIANAN' => array('code' => '120000963', 'name' => '迦南物流(入库)'),
        'MEIT' => array('code' => '1200000581', 'name' => '美通(入库)')
    );

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = '';
        $select = '*';
        if ($filter['ctl_type'] == 'export') {
            $sql_join = ' INNER JOIN api_weipinhuijit_delivery_detail AS dd ON wd.delivery_id=dd.delivery_id INNER JOIN goods_sku AS gs ON dd.sku=gs.sku ';
            $select = "wd.shop_code,wd.delivery_id,wd.storage_no,wd.warehouse,wd.delivery_method,wd.express,wd.arrival_time,wd.insert_time,wd.amount AS goods_amount,wd.brand_code,wd.is_delivery,dd.goods_code,gs.barcode,gs.spec1_name,gs.spec2_name,dd.record_code,dd.pick_no,dd.amount AS goods_num";
        }

        $sql_main = "FROM {$this->table} AS wd {$sql_join} WHERE 1";
        $sql_s_values = array();
        $sql_values = array();
        //店铺
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('wd.shop_code', $filter_shop_code);
        //出库状态
        if (isset($filter['is_delivery']) && $filter['is_delivery'] != '') {
            if ($filter['is_delivery'] == 1) {
                $sql_main .= " AND wd.is_delivery = 1 ";
            } else {
                $sql_main .= " AND (wd.is_delivery = 0 or wd.is_delivery is null) ";
            }
        }
        //送货仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            $warehouse = deal_strs_with_quote($filter['warehouse']);
            $sql_main .= " AND wd.warehouse in({$warehouse}) ";
        }
        //出库单号
        if (isset($filter['delivery_id']) && $filter['delivery_id'] != '') {
            $sql_main .= " AND wd.delivery_id = :delivery_id ";
            $sql_values[':delivery_id'] = $filter['delivery_id'];
        }
        //入库单号
        if (isset($filter['storage_no']) && $filter['storage_no'] != '') {
            $sql_main .= " AND wd.storage_no = :storage_no ";
            $sql_values[':storage_no'] = $filter['storage_no'];
        }
        if (isset($filter['delivery_no']) && $filter['delivery_no'] != '') {
            $sql_main .= " AND wd.delivery_no = :delivery_no ";
            $sql_values[':delivery_no'] = $filter['delivery_no'];
        }
        //快递单号
        if (isset($filter['express']) && $filter['express'] != '') {
            $sql_main .= " AND wd.express = :express ";
            $sql_values[':express'] = $filter['express'];
        }
        //创建时间-开始
        if (isset($filter['insert_time_start']) && $filter['insert_time_start'] !== '') {
            $sql_main .= " AND wd.insert_time >=:insert_time_start ";
            $sql_values[':insert_time_start'] = $filter['insert_time_start'];
        }
        //创建时间-结束
        if (isset($filter['insert_time_end']) && $filter['insert_time_end'] !== '') {
            $sql_main .= " AND wd.insert_time <=:insert_time_end ";
            $sql_values[':insert_time_end'] = $filter['insert_time_end'];
        }
        //出库时间-开始
        if (isset($filter['delivery_time_start']) && $filter['delivery_time_start'] !== '') {
            $sql_main .= " AND wd.delivery_time >=:delivery_time_start ";
            $sql_values[':delivery_time_start'] = $filter['delivery_time_start'];
        }
        //出库时间-结束
        if (isset($filter['delivery_time_end']) && $filter['delivery_time_end'] !== '') {
            $sql_main .= " AND wd.delivery_time <=:delivery_time_end ";
            $sql_values[':delivery_time_end'] = $filter['delivery_time_end'];
        }
        $sql_s = "select distinct(a.id) FROM {$this->table} a,{$this->detail_table} b,base_goods c where a.delivery_id=b.delivery_id and b.goods_code=c.goods_code ";
        $s_wh = "";
        //批发销货单
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $s_wh .= " and b.record_code=:record_code ";
            $sql_s_values[':record_code'] = $filter['record_code'];
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $s_wh .= " and c.goods_name like :goods_name ";
            $sql_s_values[':goods_name'] = "%{$filter['goods_name']}%";
        }
        //商品代码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $s_wh .= " and c.goods_code like '%{$filter['goods_code']}%' ";
            $sql_s_values[':goods_code'] = "%{$filter['goods_code']}%";
        }
        //条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $s_wh .= " and b.barcode like '%{$filter['barcode']}%' ";
            $sql_s_values[':barcode'] = "%{$filter['barcode']}%";
        }
        //拣货单
        if (isset($filter['pick_no']) && $filter['pick_no'] != '') {
            $s_wh .= " and b.pick_no=:pick_no ";
            $sql_s_values[':pick_no'] = $filter['pick_no'];
        }
        if (!empty($s_wh)) {
            $sql_s .= $s_wh;
            $pid_col = $this->db->get_col($sql_s, $sql_s_values);
            if (empty($pid_col)) {
                $sql_main .= " and 1=2";
            } else {
                $pid_str = implode(',', $pid_col);
                $sql_main .= " and wd.id in($pid_str)";
            }
        }

        $sql_main .= " order by wd.insert_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $warehouse_arr = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse(0);
        foreach ($data['data'] as &$row) {
            $row['warehouse_name'] = $warehouse_arr[$row['warehouse']]['name'];
            if ($row['delivery_method'] == 1) {
                $row['delivery_method'] = '汽运';
            } else if ($row['delivery_method'] == 2) {
                $row['delivery_method'] = '空运';
            }
        }
        $match = array('shop_code|shop', 'brand_code|brand_code');
        if ($filter['ctl_type'] == 'export') {
            $match[] = 'goods_code|goods_code';
        }
        filter_fk_name($data['data'], $match);
        $ret_data = $data;
        return $this->format_ret(1, $ret_data);
    }

    function get_goods_by_page($filter) {
        $sql_main = "FROM {$this->detail_table} a,base_goods b  WHERE a.goods_code=b.goods_code ";

        $sql_values = array();

        //id
        if (isset($filter['id']) && $filter['id'] != '') {
            $sql_main .= " AND pid = :id ";
            $sql_values[':id'] = $filter['id'];
        }
        if (isset($filter['delivery_id']) && $filter['delivery_id'] != '') {
            $sql_main .= " AND delivery_id = :delivery_id ";
            $sql_values[':delivery_id'] = $filter['delivery_id'];
        }
        if (isset($filter['pick_no']) && $filter['pick_no'] != '') {
            $sql_main .= " AND pick_no = :pick_no ";
            $sql_values[':pick_no'] = $filter['pick_no'];
        }
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (a.goods_code =:goods_code or a.barcode = :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        $select = 'a.*,b.goods_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }

    /**
     * 通过field_name查询
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

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    public function get_by_field_order_all($field_name, $value, $select = "*") {
        $sql = "select {$select} from {$this->detail_table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_all($sql, array(":{$field_name}" => $value));
        return $data;
    }

    //创建出库单
    function create_delivery($out_params) {
        $store_out_record_no = $out_params['store_out_record_no'];
        $sql = "select b.warehouse,b.po_no,b.shop_code,a.express,a.express_code,a.store_code,a.name,a.tel from wbm_store_out_record a,api_weipinhuijit_store_out_record b where a.record_code=b.store_out_record_no and a.record_code='{$store_out_record_no}'";
        $out_ret = $this->db->get_row($sql);

        $params['po_no'] = $out_ret['po_no']; //档期
        $params['delivery_no'] = empty($out_params['express']) ? $store_out_record_no : $out_params['express'];
        $warehouse_arr = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse();
        $params['warehouse'] = $warehouse_arr[$out_ret['warehouse']]['val']; //送货仓库
        $params['arrival_time'] = $out_params['arrival_time']; //预计到货时间
        $params['delivery_method'] = $out_params['delivery_method']; //配送模式
        if (empty($out_ret['express_code'])) {
            return $this->format_ret(-1, '', '批发销货单的配送方式不能为空');
        }

        if (isset($this->carrier_list[$out_ret['express_code']])) {
            $carrier_name = $this->carrier_list[$out_ret['express_code']]['name'];
            $carrier_code = $this->carrier_list[$out_ret['express_code']]['code'];
            $params['carrier_name'] = $carrier_name; //承运商名称
            $params['carrier_code'] = $carrier_code; //承运商编码
        }
        if (!isset($params['carrier_name'])) {
            return $this->format_ret(-1, '', '配送方式不能为空');
        }

        if (empty($out_ret['tel'])) {
            return $this->format_ret(-1, '', '批发销货单的联系电话不能为空');
        }
        //$params['driver'] = $out_ret['name'];//	司机姓名
        $params['driver_tel'] = $out_ret['tel']; //司机联系电话
        $params['shop_code'] = $out_ret['shop_code'];

        //调用唯品会创建出仓单接口
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/createDelivery', $params);

        if (!isset($result['resp_data']['delivery_id'])) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }
        $delivery_id = $result['resp_data']['delivery_id'];
        $storage_no = $result['resp_data']['storage_no'];

        $check_wms = load_model('api/WeipinhuijitPickModel')->check_is_wms_store_code($out_ret['store_code']);

        if ($check_wms === true) {
            //对接仓储 设置隐藏 作废状态
            $wms_data = array(
                'delivery_id' => $storage_no,
            );
            $where = " store_out_record_no='{$store_out_record_no}' ";
            $this->db->update('api_weipinhuijit_wms_info', $wms_data, $where);
        }

        $delivery_row['delivery_id'] = $delivery_id; //出库单id
        $delivery_row['storage_no'] = $storage_no; //入库编号
        $delivery_row['express'] = $out_ret['express']; //快递单号
        $delivery_row['express_code'] = $out_ret['express_code']; //配送方式code
        $delivery_row['carrier_name'] = $params['carrier_name']; //承运商
        $delivery_row['arrival_time'] = $params['arrival_time']; //预计到货时间
        $delivery_row['delivery_method'] = $params['delivery_method']; //配送模式
        $delivery_row['warehouse'] = $out_ret['warehouse']; //送货仓库
        $delivery_row['delivery_no'] = $params['delivery_no']; //送货编号
        $delivery_row['driver_tel'] = $params['driver_tel']; //送货编号
        $delivery_row['po_no'] = $out_ret['po_no']; //档期
        $delivery_row['shop_code'] = $out_ret['shop_code']; //店铺编码
        $delivery_row['insert_time'] = date('Y-m-d H:i:s');
        $delivery_row['brand_code'] = $out_params['brand_code'];
        $delivery_ret = $this->insert($delivery_row);

        $id = $delivery_ret['data'];
        //更新关系表
        $this->update_relation($delivery_id, $store_out_record_no);
        return $this->format_ret(1, '');
    }

    //更新关系表
    function update_relation($delivery_id, $store_out_record_no) {
        $delivery_ret = $this->get_by_field('delivery_id', $delivery_id);
        $delivery_row = $delivery_ret['data'];
        $sql = "update api_weipinhuijit_store_out_record a,wbm_store_out_record b set a.express='{$delivery_row['express']}',a.express_code='{$delivery_row['express_code']}',a.delivery_no='{$delivery_row['delivery_no']}',delivery_id='{$delivery_row['delivery_id']}',storage_no='{$delivery_row['storage_no']}' where a.store_out_record_no=b.record_code and b.record_code='{$store_out_record_no}';";
        $this->db->query($sql);
        return $this->format_ret(1, '');
    }

    //出库单是否可以确认出库
    function check_confirm($delivery_id) {
        //关联的批发销货单必须全部验收
        $sql = "select b.record_code from api_weipinhuijit_store_out_record a,wbm_store_out_record b where a.delivery_id='{$delivery_id}' and a.store_out_record_no=b.record_code  and is_store_out=0 ";
        $record_codes = $this->db->getCol($sql);
        if (empty($record_codes)) {
            return $this->format_ret(1, '');
        }
        $out_str = join(',', $record_codes);
        return $this->format_ret(-1, '', '批发销货单' . $out_str . '还未出库');
    }

    function create_delivery_detail($delivery_id) {
        //获取出库单明细
        $sql = "SELECT * FROM {$this->detail_table} where delivery_id=:delivery_id AND goods_delivery_status=0";
        $sql_values = array(':delivery_id' => $delivery_id);
        $jit_details = $this->db->get_all($sql, $sql_values);
        if (empty($jit_details)) {
            return $this->format_ret(1);
        }
        //按拣货单汇总明细
        $delivery_detail = [];
        foreach ($jit_details as $row) {
            $pick_no = $row['pick_no'];
            $_key = $row['po_no'] . "_" . $pick_no . "_" . $row['barcode'];
            if (isset($delivery_detail[$pick_no][$_key])) {
                $delivery_detail[$pick_no][$_key]['amount'] += $row['amount'];
                continue;
            }
            $temp_row = array();
            $temp_row['vendor_type'] = 'COMMON';
            $temp_row['box_no'] = $row['box_no'];
            $temp_row['pick_no'] = $row['pick_no'];
            if (!empty($row['po_no'])) {
                $temp_row['po_no'] = $row['po_no'];
            }
            $temp_row['amount'] = (int) $row['amount'];
            $temp_row['barcode'] = $row['barcode'];
            $delivery_detail[$pick_no][$_key] = $temp_row;
        }

        //获取出库单主信息
        $delivery_data = $this->get_by_field('delivery_id', $delivery_id, 'delivery_id,shop_code,storage_no');
        $delivery_data = $delivery_data['data'];

        //获取拣货单关联的PO
        $sql_values = [];
        $pick_no_arr = array_keys($delivery_detail);
        $pick_no_str = $this->arr_to_in_sql_value($pick_no_arr, 'pick_no', $sql_values);
        $po_arr = $this->db->get_all("SELECT pick_no,po_no FROM api_weipinhuijit_pick WHERE pick_no IN({$pick_no_str})", $sql_values);
        $po_arr = array_column($po_arr, 'po_no', 'pick_no');

        //导入出库单明细-接口
        foreach ($delivery_detail as $pick_no => $detail) {
            $delivery_data['po_no'] = $po_arr[$pick_no];
            $delivery_data['pick_no'] = $pick_no;
            $detail = array_values($detail);
            $ret = $this->import_delivery_detail($delivery_data, $detail);
            if ($ret['status'] < 1) {
                return $ret;
            }
        }

        return $this->format_ret(1, '');
    }

    private function import_delivery_detail($delivery_data, $pri_details) {
        $new_jit_details = array_chunk($pri_details, 100);
        //调用唯品会导入出库明细接口
        foreach ($new_jit_details as $jit_detail) {
            $detail = array();
            $detail['shop_code'] = $delivery_data['shop_code'];
            $detail['po_no'] = $delivery_data['po_no']; //档期
            $detail['storage_no'] = $delivery_data['storage_no']; //入库单号
            $detail['delivery_list'] = json_encode($jit_detail); //出仓单明细

            $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/importMultiPoDeliveryDetail', $detail);
            if ($result['resp_data']['code'] !== 0) {
                $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/importMultiPoDeliveryDetail', $detail);
                if ($result['resp_data']['code'] !== 0) {
                    return $this->format_ret(-1, '', $result['resp_data']['msg']);
                }
            }

            //更新商品导入状态
            $barcode_arr = array_column($jit_detail, 'barcode');
            if (!empty($barcode_arr) && !empty($delivery_data['pick_no'])) {
                $sql_values = ['delivery_id' => $delivery_data['delivery_id'], 'pick_no' => $delivery_data['pick_no']];
                $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
                $sql = "UPDATE {$this->detail_table} SET goods_delivery_status = 1 WHERE delivery_id =:delivery_id AND pick_no=:pick_no AND barcode IN({$barcode_str})";
                $this->db->query($sql,$sql_values);
            }
        }

        return $this->format_ret(1);
    }

    //出库
    function confirm($delivery_id) {
        $ret = $this->check_is_delivery($delivery_id);
        if($ret['status'] < 0){
            return $ret;
        }
        //控制并发
        $import_detail_status = time();
        $sql = "update api_weipinhuijit_delivery set import_detail_status='{$import_detail_status}' where delivery_id='{$delivery_id}' AND import_detail_status<{$import_detail_status}-60";
        $ret = $this->query($sql);
        $rows = $this->affected_rows();
        if ($ret['status'] != 1 || $rows != 1) {
            return $this->format_ret('-1', '', '多人不能同时操作！');
        }
        //校验是否可以出库
        $ret = $this->check_confirm($delivery_id);
        if ($ret['status'] != 1) {
            //变成初始状态
            $this->update(array('import_detail_status' => 0), array('delivery_id' => $delivery_id));
            return $ret;
        }
        //导入出库单明细
        $ret = $this->create_delivery_detail($delivery_id);
        if ($ret['status'] != 1) {
            $this->update(array('import_detail_status' => 0), array('delivery_id' => $delivery_id));
            return $ret;
        }
        
        //确认物流信息
        $ret = $this->confirm_delivery_info($delivery_id);
        if ($ret['status'] != 1) {
            $this->update(array('import_detail_status' => 0), array('delivery_id' => $delivery_id));
            return $ret;
        }
        if ($ret['message'] == '唯品会后台已出库') {
            return $ret;
        }


        //出库单确认出库
        $ret = $this->confirm_delivery($delivery_id);
        if ($ret['status'] != 1) {
            $this->update(array('import_detail_status' => 0), array('delivery_id' => $delivery_id));
        }
        return $ret;
    }

    //出库单确认出库
    function confirm_delivery($delivery_id) {
        $delivery_row = $this->get_by_field('delivery_id', $delivery_id);
        $params['storage_no'] = $delivery_row['data']['storage_no'];
        $params['shop_code'] = $delivery_row['data']['shop_code'];

        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/confirmDelivery', $params);
        $is_delivery = strpos($result['resp_data']['msg'], '已出仓');
        if ($result['resp_data']['code'] !== 0 && $is_delivery === FALSE) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }
        $msg = '出库成功';
        if ($is_delivery !== FALSE) {
            $msg = '唯品会后台已出库';
        }
        //回写出库单出库状态
        $sql = "UPDATE {$this->table} SET is_delivery=1,delivery_time=now() WHERE delivery_id=:delivery_id";
        $this->db->query($sql, array(':delivery_id' => $delivery_id));

        return $this->format_ret(1, '', $msg);
    }

    //确认物流信息
    function confirm_delivery_info($delivery_id) {
        $delivery_row = $this->get_by_field('delivery_id', $delivery_id);
        $params['storage_no'] = $delivery_row['data']['storage_no']; //入库单号

        $express_code = $delivery_row['data']['express_code'];
        $carrier_code = $this->carrier_list[$express_code]['code'];
        $params['carrier_code'] = $carrier_code; //承运商编码
        $params['shop_code'] = $delivery_row['data']['shop_code'];
        $params['delivery_no'] = $delivery_row['data']['express']; //物流单号
        $params['delivery_method'] = $delivery_row['data']['delivery_method']; //配送方式(0=默认值,1=汽运,2=空运)
        //单独客户试点
        //$kh_id = CTX()->saas->get_saas_key();
        //if ($kh_id == '2150') {
        $params['arrival_time'] = $delivery_row['data']['arrival_time'];
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/editMultiPoDelivery', $params);
        //} else {
        //    $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/confirmDeliveryInfo', $params);
        //}
        //识别已出库状态
        if ($result['resp_data']['code'] !== 0 && strpos($result['resp_data']['msg'], '已出仓') !== FALSE) {
            //回写出库单出库状态
            $sql = "UPDATE {$this->table} SET is_delivery=1,delivery_time=now() WHERE delivery_id=:delivery_id";
            $this->db->query($sql, array(':delivery_id' => $delivery_id));
            return $this->format_ret(1, '', '唯品会后台已出库');
        }
        if ($result['resp_data']['code'] !== 0) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }
        return $this->format_ret(1, '');
    }

    /*
     * 保存出库单
     */

    function insert($delivery_row) {
        return parent::insert($delivery_row);
    }

    //保存出库单明细
    function add_detail_multi($delivery_details) {
        return parent::insert_multi_exp('api_weipinhuijit_delivery_detail', $delivery_details);
    }

    function get_all_delivery($request = array(), $jit_version = 1) {
        $sql_values = array();
        //是否出库
        $sql_main = '';
        if (isset($request['is_delivery'])) {
            if ($request['is_delivery'] == 1) {
                $sql_main .= " AND is_delivery = 1 ";
            } else {
                $sql_main .= " AND (is_delivery = 0 or is_delivery is null) ";
            }
        }
        //送货仓库
        if (isset($request['warehouse']) && $request['warehouse'] != '') {
            $sql_main .= " AND warehouse = :warehouse ";
            $sql_values[":warehouse"] = $request['warehouse'];
        }

        //档期
        if (isset($request['po_no']) && $request['po_no'] != '') {
            if ($jit_version == 2) {
                $sql_main .= " AND po_no = :po_no ";
                $sql_values[":po_no"] = $request['po_no'];
            } else {
                $po_no_arr = explode(",", $request['po_no']);
                $po_no_str = "'" . implode("','", $po_no_arr) . "'";
                $sql_main .= " AND  po_no in({$po_no_str})";
            }
        }


        $sql = 'select * from api_weipinhuijit_delivery where 1 ';
        $sql_main .= " order by insert_time desc ";
        $sql .= $sql_main;
        $ret = $this->db->get_all($sql, $sql_values);
        foreach ($ret as &$val) {
            $time_arr = explode(' ', $val['arrival_time']);
            $val['arrival_time'] = $time_arr[0];
            $val['time_slot'] = $time_arr[1];
        }
        return $ret;
    }

    /**
     * 创建出库单 2.0
     */
    function create_delivery2($out_params) {
        $store_out_record_no = $out_params['store_out_record_no'];
        $sql = "SELECT b.warehouse,b.po_no,b.shop_code,a.express,a.express_code,a.store_code,a.name,a.tel FROM wbm_store_out_record a,api_weipinhuijit_store_out_record b WHERE a.record_code=b.store_out_record_no AND a.record_code='{$store_out_record_no}'";
        $out_ret = $this->db->get_row($sql);

        $params['po_no'] = $out_ret['po_no']; //档期
        $params['delivery_no'] = empty($out_params['express']) ? $store_out_record_no : $out_params['express'];
        $warehouse_arr = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse();
        $params['warehouse'] = $warehouse_arr[$out_ret['warehouse']]['val']; //送货仓库
        $params['arrival_time'] = $out_params['arrival_time']; //预计到货时间
        $params['delivery_method'] = $out_params['delivery_method']; //配送模式
        if (empty($out_ret['express_code'])) {
            return $this->format_ret(-1, '', '批发销货单的配送方式不能为空');
        }

        if (isset($this->carrier_list[$out_ret['express_code']])) {
            $carrier_name = $this->carrier_list[$out_ret['express_code']]['name'];
            $carrier_code = $this->carrier_list[$out_ret['express_code']]['code'];
            $params['carrier_code'] = $carrier_code; //承运商编码
        }
        if (!isset($carrier_name)) {
            return $this->format_ret(-1, '', '配送方式不能为空');
        }
        if (empty($out_ret['tel'])) {
            return $this->format_ret(-1, '', '批发销货单的联系电话不能为空');
        }

        $params['shop_code'] = $out_ret['shop_code'];

        //调用唯品会创建出仓单接口
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/createMultiPoDelivery', $params);
        if (isset($result['resp_data']['msg'])) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }

        $storage_no = $result['resp_data'];

        $check_wms = load_model('api/WeipinhuijitPickModel')->check_is_wms_store_code($out_ret['store_code']);
        if ($check_wms === true) {
            //对接仓储 设置隐藏 作废状态
            $wms_data = array(
                'delivery_id' => $storage_no,
            );
            $where = " store_out_record_no='{$store_out_record_no}' ";
            $this->db->update('api_weipinhuijit_wms_info', $wms_data, $where);
        }

        //更新出库到wms中间表

        $delivery_row['delivery_id'] = $storage_no;
        $delivery_row['storage_no'] = $storage_no; //入库编号
        $delivery_row['express'] = $out_ret['express']; //快递单号
        $delivery_row['express_code'] = $out_ret['express_code']; //配送方式code
        $delivery_row['carrier_name'] = $carrier_name; //承运商
        $delivery_row['arrival_time'] = $params['arrival_time']; //预计到货时间
        $delivery_row['delivery_method'] = $params['delivery_method']; //配送模式
        $delivery_row['warehouse'] = $out_ret['warehouse']; //送货仓库
        $delivery_row['delivery_no'] = $params['delivery_no']; //送货编号
        $delivery_row['driver_tel'] = $out_ret['tel'];
        $delivery_row['po_no'] = $out_ret['po_no']; //档期
        $delivery_row['shop_code'] = $out_ret['shop_code']; //店铺编码
        $delivery_row['insert_time'] = date('Y-m-d H:i:s');
        $delivery_row['brand_code'] = $out_params['brand_code'];
        $delivery_ret = $this->insert($delivery_row);
        if ($delivery_ret['status'] != 1) {
            return $this->format_ret(-1, '', '创建出库单出错');
        }
        //更新关系表
        $this->update_relation($storage_no, $store_out_record_no);
        return $this->format_ret(1);
    }

    /**
     * 检查出库单出库状态
     * @param type $delivery_id
     * @return type
     */
    function check_is_delivery($delivery_id) {
        $sql = "SELECT is_delivery FROM {$this->table} WHERE delivery_id=:delivery_id";
        $ret = $this->db->get_value($sql, array(':delivery_id' => $delivery_id));
        if ($ret == 1) {
            return $this->format_ret(-1, '', "选择的出库单[{$delivery_id}]已出库");
        }
        return $this->format_ret(1);
    }

    /*
     * 获取出库单号
     */

    function get_storage_no_by_delivery_id($delivery_id) {
        $sql = "select storage_no from  {$this->table}  WHERE delivery_id=:delivery_id";
        return $this->db->get_value($sql, array(':delivery_id' => $delivery_id));
    }

    /**
     * 修改配送方式，到货时间
     * @param array $out_params
     */
    function edit_deliver_action($out_params) {
        $id = $out_params['id'];
        $deliver = $this->get_by_id($id);
        if ($deliver['status'] != 1) {
            return $this->format_ret('-1', '', '出库单信息不存在！');
        }
        unset($out_params['id']);

        $deliver_data = $deliver['data'];
        $is_change = 0;
        $up_data = array();
        foreach ($out_params as $k => $v) {
            if ($v != $deliver_data[$k]) {
                $is_change = 1;
                $up_data[$k] = $v;
            }
        }
        if ($is_change == 0) {
            return $this->format_ret(1);
        }

        $this->begin_trans();
        $ret = $this->update($up_data, array('id' => $id));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改失败');
        }
        if (!empty($up_data['express_code']) || !empty($up_data['express'])) {
            $sql = 'SELECT store_out_record_no AS record_code FROM api_weipinhuijit_store_out_record WHERE delivery_id=:delivery_id';
            $record_code_arr = $this->db->get_all_col($sql, array('delivery_id' => $deliver_data['delivery_id']));
            $sql_values = array(':express_code' => $out_params['express_code'], ':express' => $out_params['express']);
            $record_code_str = $this->arr_to_in_sql_value($record_code_arr, 'record_code', $sql_values);
            $sql = "UPDATE wbm_store_out_record SET express_code=:express_code,express=:express WHERE record_code IN({$record_code_str})";
            $ret = $this->query($sql, $sql_values);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '修改失败');
            }
        }
        $this->commit();

        return $ret;
    }

    /**
     * 获取档期关联的出库单
     * @param $params
     */
    function get_delivery_by_multi_po($params) {
        $sql = "SELECT * FROM api_weipinhuijit_delivery WHERE po_no<>:po_no AND warehouse=:warehouse AND is_delivery=0";
        $sql_value[':po_no'] = $params['po_no'];
        $sql_value[':warehouse'] = $params['warehouse'];
        $delivery_arr = array();
        $ret = $this->db->get_all($sql, $sql_value);
        if (!empty($ret)) {
            $pick_po_arr = explode(',', $params['po_no']);
            $pick_po_num = count($pick_po_arr);
            foreach ($ret as $value) {
                $deliver_po_arr = explode(',', $value['po_no']);
                $intersection = array_intersect($pick_po_arr, $deliver_po_arr);
                if (count($intersection) == $pick_po_num) {
                    $time_arr = explode(' ', $value['arrival_time']);
                    $value['arrival_time'] = $time_arr[0];
                    $value['time_slot'] = $time_arr[1];
                    $delivery_arr[] = $value;
                }
            }
        }
        return $delivery_arr;
    }

}
