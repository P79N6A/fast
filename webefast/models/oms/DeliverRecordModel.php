<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lib('apiclient/TaobaoClient');

class DeliverRecordModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_deliver_record';
    protected $detail_table = 'oms_deliver_record_detail';

    /**
     * @var array 支持淘宝云栈电子面单快递方式
     */
    public $shipping_wlb_waybill = array('ems', 'eyb', 'sf', 'yto');
    public $is_print_express = array(
        0 => '快递单未打印',
        1 => '快递单已打印',
    );
    public $is_print_sellrecord = array(
        0 => '发货单未打印',
        1 => '发货单已打印',
    );
    public $is_print_invoice = array(
        0 => '发票未打印',
        1 => '发票已打印',
    );
    public $is_deliver = array(
        0 => '未发货',
        1 => '已发货',
    );
    public $is_back = array(
        0 => '未回写',
        1 => '已回写',
        2 => '本地回写',
    );
    public $package_data = array();
    private $asyn_cancle_cainiao_data = array();
    public $cloud_print_waybill_code = array(); //云打印电子面单号数组
    public $success_num = 0; //获取云打印可打印数据成功数量
    public $fail_num = 0; //获取云打印可打印数据失败数量
    public $fail_sell_record_code = array();

    /**
     * @var array 打印发货单模板所需字段
     */
    public $print_fields_default = array(
        'record' => array(
            '买家留言' => 'buyer_remark',
            '商家留言' => 'seller_remark',
            '仓库留言' => 'store_remark',
            '订单备注' => 'order_remark',
            '快递单号' => 'express_no',
            '交易号' => 'deal_code_list',
            '订单号' => 'sell_record_code',
            '下单时间' => 'record_time',
            '付款时间' => 'pay_time',
            '打印时间' => 'print_time',
            '商品总数量' => 'goods_num',
            '商品总重量' => 'goods_weigh',
            '商品实际重量' => 'real_weigh',
            '商品总金额' => 'goods_money',
            '应收金额' => 'payable_money',
            '实付金额' => 'paid_money',
            '运费' => 'express_money',
            '蓝位号' => 'sort_no',
            '波次单号' => 'waves_record_code',
            '买家昵称' => 'buyer_name',
            '收货人姓名' => 'receiver_name',
            '收货人手机' => 'receiver_mobile',
            '收货人电话' => 'receiver_phone',
            '收货省' => 'receiver_province',
            '收货市' => 'receiver_city',
            '收货区/县' => 'receiver_district',
            '收货街道' => 'receiver_street',
            '收货地址（无省市区）' => 'receiver_addr',
            '收货地址（含省市区）' => 'receiver_address',
            '收收货邮编' => 'receiver_zip_code',
            '收货目的地' => 'receiver_top_address',
            '收货集包地' => 'package_center_name',
            '发货仓库' => 'store_code_name',
            '发货商店' => 'sender_shop_name',
            '发货方联系人' => 'sender',
            '发货方联系手机' => 'sender_mobile', //
            '发货方联系电话' => 'sender_phone', //
            '发货地址（无省市区）' => 'sender_addr',
            '发货地址（含省市区）' => 'sender_address',
            '发货邮编' => 'sender_zip',
            '发货街道' => 'senderr_street',
            '发货区/县' => 'senderr_district',
            '发货市' => 'sender_city', //新增
            '发货省' => 'sender_province', //新增
            //'发货时间' => 'sender_date',//新增
            '发货打单员' => 'sender_operprint', //新增
            '支付方式' => 'pay_code',
            '发票抬头' => 'invoice_title',
            '发票内容' => 'invoice_content',
            '配送方式' => 'express_name',
            '商品均摊总金额' => 'all_avg_money',
            '店铺联系人' => 'store_person', //新增
            '店铺联系电话' => 'store_tel', //新增
            '店铺联系地址（含省市区）' => 'store_address', //新增
            '店铺联系地址（不含省市区）' => 'store_addr'//新增
        ),
        'detail' => array(
            array(
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '商品套餐条形码'=>'combo_barcode',
                '规格1' => 'spec1_name',
                '规格2' => 'spec2_name',
                '规格1代码' => 'spec1_code',
                '规格2代码' => 'spec2_code',
                '单价' => 'goods_price',
                '数量' => 'num',
                '均摊金额' => 'avg_money',
                '库位' => 'shelf_code',
                '条形码' => 'barcode',
                '平台规格' => 'platform_spec',
                '批次号' => 'lof_no',
                '平台商品名称' => 'platform_name', //新增
                '是否赠品' => 'is_gift',
            ),
        ),
    );
    public $settable_goods_info = array(
        'goods_name' => '商品名称',
        'goods_code' => '商品编码',
        'goods_short_name' => '商品简称',
        'goods_barcode' => '商品条形码',
        'spec1_name' => '规格1',
        'spec2_name' => '规格2',
        'num' => '数量',
        'shelf_code' => '商品库位',
        'platform_spec' => '平台规格',
        'sku_remark' => '条码备注',
        'sku_weight' => '商品理论重量'
    );
    protected $package_data_num = 20;

    function __construct() {
        parent::__construct();

        //设置包裹
        for ($i = 1; $i <= $this->package_data_num; $i++) {
            $this->package_data[$i] = "包裹" . $i;
        }
    }

    public function get_sound() {
        $r = array('success' => '', 'error' => '');
        $sql = "select value from sys_params where param_code = 'scan_voice_ok'";
        $r['success'] = $this->db->get_value($sql);
        $sql = "select value from sys_params where param_code = 'scan_voice_fail'";
        $r['error'] = $this->db->get_value($sql);
        return $r;
    }

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 发货单新版打印（更改打印配件）
     */
    public function print_data_default_new($request) {
        $id = $request['record_ids'];
        $r = array();
        $r['record'] = $this->db->get_row("select * from oms_deliver_record where deliver_record_id = :id", array('id' => $id));
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($r['record']['sell_record_code']);
        $r['record'] = array_merge($r['record'], $record_decrypt_info);
        $arr = array('lof_status');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        if ($ret_arr['lof_status'] == 1) {
            $r['detail'] = $this->get_print_info_with_lof_no($r['record']['sell_record_code']);
        } else {
            $sql = "select d.sell_record_code,d.combo_sku
                    ,d.goods_code,d.num,d.sku
                    ,d.goods_price,d.avg_money,d.platform_spec,d.platform_name,d.is_gift
                    from oms_sell_record_detail d
                    where d.sell_record_code=:sell_record_code";
            $r['detail'] = $this->db->get_all($sql, array(':sell_record_code' => $r['record']['sell_record_code']));
            $sku_array = array();
            $shelf_code_arr = array();
            foreach ($r['detail'] as $key => $detail) {//合并同一sku
                $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['store_code']);
                $detail['combo_barcode']=load_model('oms/SellRecordModel')->get_combo_barcode_by_sell_code($detail['combo_sku']);
                $shelf_code_arr[] = $detail['shelf_code'];
                $detail['shelf_name'] = $this->get_shelf_name($detail['sku'], $r['record']['store_code']);
                $key_arr = array('goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode', 'category_name', 'remark');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
                $r['detail'][$key] = array_merge($detail, $sku_info);
                $r['detail'][$key]['is_gift'] = $detail['is_gift'] == 1 ? '是' : '否';
                if (in_array($detail['sku'].'_'.$detail['is_gift'], $sku_array)) {
                     $exist_key = array_keys($sku_array, $detail['sku'].'_'.$detail['is_gift']);
                     $r['detail'][$exist_key[0]]['num'] += $detail['num'];
                     $r['detail'][$exist_key[0]]['avg_money'] += $detail['avg_money'];
                     unset($r['detail'][$key]);
                 } else {
                     $sku_array[$key] = $detail['sku'].'_'.$detail['is_gift'];
                 }
            }
        }

        //页码
        $r['record']['page_no'] = '<span class="page"></span>';
        array_multisort($shelf_code_arr, SORT_ASC, $r['detail']);
        $this->print_data_escape($r['record'], $r['detail'], 'new');
        $this->print_update_status($r['record']['deliver_record_id'], 'deliver');

        $trade_data = array($r['record']);
        load_model('common/TBlLogModel')->set_log_multi($trade_data, 'print');
        //var_dump($r);diel;
        return $r;
    }

    private function get_print_info_with_lof_no($record_code) {

        $sql = "select  rl.*, rr.sell_price as goods_price from oms_sell_record_lof rl "
                . "left join base_goods rr on rl.goods_code = rr.goods_code "
                . " where rl.record_code = :order_code and rl.record_type = '1'";

        $lof_details = $this->db->get_all($sql, array(":order_code" => $record_code));
        if (empty($lof_details)) {
            return array();
        }
        $shelf_code_arr = array();
        foreach ($lof_details as $key => $detail) {
            $detail['combo_barcode']=load_model('oms/SellRecordModel')->get_combo_barcode_by_sell_code($detail['record_code'],$detail['sku']);
            $detail['shelf_code'] = $this->get_shelf_code_lof($detail['sku'], $detail['lof_no'], $detail['store_code']);
            $shelf_code_arr[] = $detail['shelf_code'];
            $detail['shelf_name'] = $this->get_shelf_name_lof($detail['sku'], $detail['lof_no'], $detail['store_code']);
            $detail['out_num'] = $detail['num'];
            $detail['avg_money'] = $detail['num'] * $detail['goods_price'];
            $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'barcode', 'category_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $lof_details[$key] = array_merge($detail, $sku_info);
        }
        array_multisort($shelf_code_arr, SORT_ASC, $lof_details);

        return $lof_details;
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

    public function get_shelf_name($sku, $store_code) {
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code and a.sku = :sku order by a.sku,a.shelf_code asc";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        foreach ($l as $_v) {
//            $arr[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code']));
            $arr[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code'], 'store_code' => $store_code));
//            $arr[] = $_v['shelf_name'];
        }
        return implode(',', $arr);
    }

    private function get_shelf_code_lof($sku, $lof_no, $store_code) {
        $sql = "select shelf_code from goods_shelf where store_code = :store_code and sku = :sku and batch_number =:batch_number order by sku,shelf_code asc";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku, ':batch_number' => $lof_no));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    private function get_shelf_name_lof($sku, $lof_no, $store_code) {
        $sql = "select shelf_code from goods_shelf where store_code = :store_code and sku = :sku and batch_number =:batch_number order by sku,shelf_code asc";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku, ':batch_number' => $lof_no));
        $arr = array();
        foreach ($l as $_v) {
//            $arr[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code']));
            $arr[] = oms_tb_val('base_shelf', 'shelf_name', array('shelf_code' => $_v['shelf_code'], 'store_code' => $store_code));
//            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default($id, $print_type = NULL) {
        $r = array();

        $r['record'] = $this->db->get_row("select * from oms_deliver_record where deliver_record_id = :id", array('id' => $id));
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($r['record']['sell_record_code']);
        $r['record'] = array_merge($r['record'], $record_decrypt_info);
        $sql = "select d.sell_record_code,d.combo_sku
                ,d.goods_code,d.num,d.sku
                ,d.goods_price,d.avg_money,d.platform_spec,d.platform_name,d.is_gift
                from oms_sell_record_detail d 
                where d.sell_record_code=:sell_record_code";
        $r['detail'] = $this->db->get_all($sql, array(':sell_record_code' => $r['record']['sell_record_code']));
        //todo:new_sku_cache
        $sku_array = array();
        foreach ($r['detail'] as $key => $detail) {//合并同一sku
            $detail['combo_barcode']=load_model('oms/SellRecordModel')->get_combo_barcode_by_sell_code($detail['combo_sku']);
            $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'spec1_code', 'spec2_code', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $r['detail'][$key] = array_merge($detail, $sku_info);
            $r['detail'][$key]['is_gift'] = $detail['is_gift'] == 1 ? '是' : '否';
                if (in_array($detail['sku'].'_'.$detail['is_gift'], $sku_array)) {
                    $exist_key = array_keys($sku_array, $detail['sku'].'_'.$detail['is_gift']);
                    $r['detail'][$exist_key[0]]['num'] += $detail['num'];
                    $r['detail'][$exist_key[0]]['avg_money'] += $detail['avg_money'];
                    unset($r['detail'][$key]);
                } else {
                    $sku_array[$key] = $detail['sku'].'_'.$detail['is_gift'];
                }
        }
        // 数据替换
        $this->print_data_escape($r['record'], $r['detail']);
        $d = array('record' => array(), 'detail' => array());
        foreach ($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v;
            foreach (ds_get_select("pay_type") as $pay_code) {
                if ($r['record']['pay_code'] == $pay_code['pay_type_code']) {
                    $d['record']["支付方式"] = $pay_code['pay_type_name'];
                }
            }
        }
        unset($d['record']['express_data']);
        foreach ($r['detail'] as $k1 => $v1) {
            $v1['lof_no'] = $this->get_lof_no($v1['sell_record_code'], $v1['sku']);
            $v1['lof_no'] = str_replace('<br />', '', $v1['lof_no']);
            // 键值对调
            foreach ($v1 as $k => $v) {
                $nk = array_search($k, $this->print_fields_default['detail'][0]);
                $nk = $nk === false ? $k : $nk;
                $d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
                $d['detail'][$k1]['库位'] = $this->get_shelf_code($v1['sku'], $r['record']['store_code']);
            }
        }
        // 更新状态
        if (empty($print_type)) {
            $this->print_update_status($r['record']['deliver_record_id'], 'deliver');
        }
        $d['detail'] = array_values($d['detail']);
        return $d;
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail, $need_detail = 1) {
        $record['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $record['receiver_province']));
        $record['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $record['receiver_city']));
        $record['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $record['receiver_district']));
        $record['receiver_street'] = oms_tb_val('base_area', 'name', array('id' => $record['receiver_street']));
        $record['waves_record_code'] = oms_tb_val('oms_waves_record', 'record_code', array('waves_record_id' => $record['waves_record_id']));
        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user'] = CTx()->get_session('user_name');
        $record['receiver_addr'] = $record['receiver_street'] . $record['receiver_addr'];
        $record['receiver_address'] = $record['receiver_province'] . $record['receiver_city'] . $record['receiver_district'] . $record['receiver_addr'];
        //商品均摊总金额
        $record['all_avg_money'] = $record['payable_money'] - $record['express_money'] - $record['delivery_money'];
        // 大头笔
        $record['receiver_top_address'] = '';
        if ($record['sale_channel_code'] == 'taobao') { //淘宝
            $d = json_decode($record['express_data'], true);
            if ($d != null && isset($d['short_address'])) {
                $record['receiver_top_address'] = $d['short_address'];
            }
        }

        //寄送人信息
        $sql = "select * from base_store where store_code = :store_code";
        $store = $this->db->get_row($sql, array('store_code' => $record['store_code']));
        $record['sender'] = $store['contact_person'];
        $record['sender_mobile'] = $store['contact_tel'];
        $record['sender_phone'] = $store['contact_phone'];
        $record['sender_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $record['shop_code']));
        $record['store_code_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $record['express_code']));
        $record['waves_record_code'] = oms_tb_val('oms_waves_record', 'record_code', array('waves_record_id' => $record['waves_record_id']));
        $country = oms_tb_val('base_area', 'name', array('id' => $store['country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $store['province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $store['city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $store['district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $store['street']));
        $record['sender_country'] = $country;
        $record['sender_province'] = $province;
        $record['sender_city'] = $city;
        $record['sender_district'] = $district;
        $record['sender_street'] = $street;
        $record['sender_addr'] = $street . ' ' . $store['address'];
        $record['sender_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $store['address'];
        $record['sender_zip'] = $store['zipcode'];
        $record['shop_contact_person'] = $store['shop_contact_person'];
        //2017-12-06 task#1906 新增：店铺联系人、联系电话、店铺联系地址(含省市区)、店铺联系地址(不含省市区)
        $shop_sql = "select contact_person,street,province,city,district,address,tel from base_shop where shop_code = :shop_code";
        $shop_data = $this->db->get_row($shop_sql, array('shop_code' => $record['shop_code']));
        $record['store_person'] = $shop_data['contact_person'];//店铺联系人
        $record['store_tel'] = $shop_data['tel'];//店铺联系电话
        $row = oms_tb_val('base_area', 'name', array('id' => $shop_data['street']));
        $provinces = oms_tb_val('base_area', 'name', array('id' => $shop_data['province']));
        $citys = oms_tb_val('base_area', 'name', array('id' => $shop_data['city']));
        $districts = oms_tb_val('base_area', 'name', array('id' => $shop_data['district']));
        $address = $shop_data['address']; 
        $record['store_addr'] = $row . ' ' . $address;
        $record['store_address'] = $provinces . ' ' . $citys . ' ' . $districts . ' ' . $row . ' ' . $address;

        if ($need_detail == 1) {
            $record['goods_num'] = 0;
            foreach ($detail as &$v) {
                $record['goods_num'] += $v['num'];
            }
        }
    }

    /**
     * 更新打印状态
     * @param $id
     * @param $type
     * @return array
     * @throws Exception
     */
    public function print_update_status($id, $type) {
        if ($type == 'express') {
            $field = 'is_print_express';
            $status = 'X_LOGISTICS_PRINTED';
            $msg = '快递单打印';
        } else if ($type == 'deliver') {
            $field = 'is_print_sellrecord';
            $status = 'X_SEND_PRINTED';
            $msg = '发货单打印';
        } else {
            return array('status' => '-1', 'message' => '类型不正确');
        }

        $record = $this->db->get_row("select * from oms_deliver_record where deliver_record_id = :id", array('id' => $id));
        if (empty($record)) {
            return array('status' => '-1', 'message' => '发货单不存在');
        }
        //发货单打印日志
        load_model('oms/SellRecordModel')->add_action($record['sell_record_code'], $msg);
        // 更新发货单表的快递单打印状态
        $this->update(array($field => 1), array('deliver_record_id' => $record['deliver_record_id']));
        // 更新订单表的快递单打印状态
        $this->db->update('oms_sell_record', array($field => 1), array('sell_record_code' => $record['sell_record_code']));

        if ($record['waves_record_id'] > 0) {
            //判断是否全部打印完成
            $sql = "select count(*) from oms_deliver_record where waves_record_id = :id and $field = 0 and is_cancel=0";
            $count = $this->db->get_value($sql, array('id' => $record['waves_record_id']));
            if ($count == 0) {
                //更新波次单的快递单打印状态----全部打印
                $this->db->update('oms_waves_record', array($field => 2), array('waves_record_id' => $record['waves_record_id']));
            } else {
                $sql = "select count(*) from oms_deliver_record where waves_record_id = :id and $field = 1 and is_cancel=0";
                $new_count = $this->db->get_value($sql, array('id' => $record['waves_record_id']));
                if ($new_count == 0) {
                    //全部未打印
                    $this->db->update('oms_waves_record', array($field => 0), array('waves_record_id' => $record['waves_record_id']));
                } else {
                    //部分打印
                    $this->db->update('oms_waves_record', array($field => 1), array('waves_record_id' => $record['waves_record_id']));
                }
            }
        }

        load_model('oms/SellRecordActionModel')->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code'], $status);


        return array('status' => '1', 'message' => '更新完成');
    }

    /**
     * @param $status
     * @param string $message
     * @param string $data
     * @return array
     */
    function return_value($status, $message = '', $data = '') {
        $message = $status == 1 && $message == '' ? '操作成功' : $message;

        return array('status' => $status, 'message' => $message, 'data' => $data);
    }

    /**
     * 根据条件查询数据
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} a
         LEFT JOIN oms_sell_record c ON a.sell_record_code = c.sell_record_code
         LEFT JOIN oms_deliver_record_detail b ON a.deliver_record_id = b.deliver_record_id
         WHERE 1 ";

        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('a.store_code', $filter_store_code);
        //回写状态
        if (isset($filter['is_back']) && $filter['is_back'] <> '') {
            $is_back_arr = explode(',', $filter['is_back']);
            $is_back_str = $this->arr_to_in_sql_value($is_back_arr, 'is_back', $sql_values);
            $sql_main .= " AND c.is_back IN ( {$is_back_str} ) ";
        }
        //波次号
        if (!empty($filter['waves_record_id'])) {
            $sql_main .= " AND a.waves_record_id = :waves_record_id ";
            $sql_values[':waves_record_id'] = $filter['waves_record_id'];
        }
        //单据编号
        if (!empty($filter['record_code'])) {
            $sql_main .= " AND a.record_code LIKE :record_code ";
            $sql_values[':record_code'] = '%' . $filter['record_code'] . '%';
        }
        //业务日期
        if (!empty($filter['record_time'])) {
            $sql_values['record_time'] = date('Y-m-d', strtotime($filter['record_time']));
        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND ( a.sell_record_code LIKE :sell_record_code OR a.deal_code LIKE :deal_code or a.buyer_name LIKE :buyer_name or a.receiver_name LIKE :receiver_name or a.express_no LIKE :express_no or b.goods_code LIKE :deliver_goods_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':deal_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':express_no'] = '%' . $filter['sell_record_code'] . '%';


            $sql_values[':buyer_name'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':receiver_name'] = '%' . $filter['sell_record_code'] . '%';


            $sql_values[':deliver_goods_code'] = '%' . $filter['sell_record_code'] . '%';


            $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['sell_record_code']);
            if (!empty($customer_code_arr)) {
                $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                $sql_main .= "  OR a.customer_code in ({$customer_code_str})  ";
            }

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['sell_record_code'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= "  OR a.customer_address_id in ({$customer_address_id_str}) ";
                $sql_values[':receiver_name'] = '%' . $filter['sell_record_code'] . '%';
            }
            $sql_main .= " )";
        }
//        //交易号
//        if (!empty($filter['deal_code'])) {
//            $sql_main .= " AND a.deal_code LIKE :deal_code ";
//            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
//        }
        //商品
        if (!empty($filter['goods_code'])) {
            $sql_main .= " AND b.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //验收
        if (isset($filter['is_accept'])) {
            $sql_main .= " AND a.is_accept = :is_accept ";
            $sql_values[':is_accept'] = $filter['is_accept'];
        }
        //仓库
        if (isset($filter['store_code'])) {
            $sql_main .= " AND a.store_code = :store_code ";
            $sql_values[':store_code'] = $filter['store_code'];
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND a.express_code = :express_code ";
            $sql_values[':express_code'] = $filter['express_code'];
        }
        //是否已发货
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND a.is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != '') {
            $sql_main .= " AND a.is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != '') {
            $sql_main .= " AND a.is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        //是否打印发票
        if (isset($filter['is_print_invoice']) && $filter['is_print_invoice'] != '') {
            $sql_main .= " AND a.is_print_invoice = :is_print_invoice ";
            $sql_values[':is_print_invoice'] = $filter['is_print_invoice'];
        }

        //是否打印商品
        if (isset($filter['is_print_goods'])) {
            $sql_main .= " AND a.is_print_goods = :is_print_goods ";
            $sql_values[':is_print_goods'] = $filter['is_print_goods'];
        }
//        //是否打印快递单
//        if (isset($filter['is_print_express'])) {
//            $ids = explode(',', $filter['is_print_express']);
//            if(count($ids) > 0 && $ids[0] != ''){
//                $idStr = implode(',', $ids);
//                $sql_main .= " AND a.is_print_express in ($idStr) ";
//                //$sql_values[':is_print_express'] = $filter['is_print_express'];
//            }
//        }
//        //是否打印订单
//        if (isset($filter['is_print_sellrecord'])) {
//            $ids = explode(',', $filter['is_print_sellrecord']);
//            if(count($ids) > 0 && $ids[0] != ''){
//                $idStr = implode(',', $ids);
//                $sql_main .= " AND a.is_print_sellrecord in ($idStr) ";
//                //$sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
//            }
//        }
        //制单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND a.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND a.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->waves_record_search_csv($sql_main, $sql_values, $filter);
        }
        $select = 'a.*,c.is_back';
        $sql_main .= " GROUP BY a.deliver_record_id ORDER BY a.deliver_record_id";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //打印发票增值
        $auth_print_invoice = load_model('common/ServiceModel')->check_is_auth_by_value('print_invoice');

        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package', 'safety_control'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';

        $express_arr = array_unique(array_column($data['data'], 'express_code'));
        $express = load_model('base/ArchiveSearchModel')->get_archives_map('express',$express_arr,2,'print_type');

        foreach ($data['data'] as &$value) {
            $value['sort_no'] .= $this->get_deliver_record_tag_img($value);

            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));

            $value['checkbox_html'] = "<input type='checkbox' name='ckb_record_id' value='{$value['deliver_record_id']}'>";

            $url = "?app_act=oms/sell_record/view&sell_record_code={$value['sell_record_code']}";
            $_url = base64_encode($url);
            $value['sell_record_code_href'] = "<a href=\"javascript:openPage('{$_url}', '{$url}', '订单详情')\">" . $value['sell_record_code'] . "</a>";
            $value['is_cancel'] = $value['is_cancel'] > 0 ? 1 : 0;
            if ($value['is_cancel'] == 1) {
                $value['readed'] = TRUE;
                $value['sell_record_code_href'] = "<a href=\"javascript:openPage('{$_url}', '{$url}', '订单详情')\">" . $value['sell_record_code'] . '(已取消)' . "</a>";
            }
            $value['print_status'] = $this->is_print_express[$value['is_print_express']];
            $value['print_status'] .= '<br>' . $this->is_print_sellrecord[$value['is_print_sellrecord']];
            $value['print_status'] .= "<a href=\"javascript:view_send_print_link({$value['deliver_record_id']})\">" . '(预览)' . "</a>";
            if ($auth_print_invoice == true) {
                $value['print_status'] .= '<br>' . $this->is_print_invoice[$value['is_print_invoice']];
            }
            $value['deliver_status'] = $this->is_deliver[$value['is_deliver']];
            $value['is_back'] = $value['is_back'] == 0 || $value['is_back'] == -1 ? 0 : ($value['is_back'] = $value['is_back'] == 1 ? 1 : 2);
            $value['deliver_status'] .= '<br>' . $this->is_back[$value['is_back']];
            if ($is_more_deliver_package == 1 && $value['package_no'] > 0) {
                $express_data = $this->get_package_express_no_by_package_no($value['sell_record_code'], $value['waves_record_id'], $value['package_no']);
                if (!empty($express_data)) {
                    $value['express_no'] = $express_data['express_no'];
                }
            }
            if ($ret_pararm['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
            }

            $value['print_type'] = $express[$value['express_code']]['print_type'];
        }

        return $this->format_ret(1, $data);
    }

    function get_deliver_record_tag_img($row) {
        $tag_arr = array();
        if ($row['invoice_status'] > 0) {
            $tag_arr[] = array('piao', '有发票');
        }
        if ($row['pay_type'] == 'cod') {
            $tag_arr[] = array('cod', '货到付款');
        }


        $html_arr = array();
        foreach ($tag_arr as $_tag) {
            $html_arr[] = "<img src='assets/img/state_icon/{$_tag[0]}_icon.png' title='{$_tag[1]}'/>";
        }
        return join('', $html_arr);
    }

    public function get_record_list_by_items($filter) {
        $sql_main = " from oms_deliver_record a
        		LEFT JOIN oms_sell_record c ON a.sell_record_code = c.sell_record_code
        		 where 1 and is_cancel = 0 ";
        $sql_values = array();
        $ret = $this->search_terms($filter, $sql_main, $sql_values);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_values'];
        $sql = "select  a.* " . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, $data, '未匹配到需要打印的单据');
        }
        //var_dump($filter, $sql, $sql_values, $data);


        return $data;
    }

    public function get_record_list_by_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array();
        }

        return $this->db->get_all("select * from oms_deliver_record where deliver_record_id in ($str) and is_cancel = 0 ");
    }

    public function get_records_by_range($id, $min = 0, $max = 0) {

        $sql = "select * from oms_deliver_record where waves_record_id = :waves_record_id AND sort_no >= :min AND sort_no <= :max and is_cancel = 0";



        $data = $this->db->get_all($sql, array('waves_record_id' => $id, 'min' => $min, 'max' => $max));

        $r = array();
        foreach ($data as $val) {
            $r[] = $val['deliver_record_id'];
        }

        return $r;
    }

    function search_terms($filter, $sql_main, $sql_values, $type = 0) {
        if (isset($filter['is_back']) && $filter['is_back'] <> '') {
            $is_back_arr = explode(',', $filter['is_back']);
            $is_back_str = $this->arr_to_in_sql_value($is_back_arr, 'is_back', $sql_values);
            $sql_main .= " AND c.is_back IN ( {$is_back_str} ) ";
        }
        if (isset($filter['waves_record_ids']) || isset($filter['waves_record_ids_'])) {
            if ($filter['waves_record_ids']) {
                $sql_main .= " AND a.waves_record_id in({$filter['waves_record_ids']})";
            } else {
                $sql_main .= " AND a.waves_record_id in({$filter['waves_record_ids_']})";
            }
        }
        if (isset($filter['deliver_record_ids']) || isset($filter['deliver_record_ids_'])) {
            if ($filter['deliver_record_ids']) {
                $sql_main .= " AND a.deliver_record_id in({$filter['deliver_record_ids']}) ";
            } else {
                $sql_main .= " AND a.deliver_record_id in({$filter['deliver_record_ids_']}) ";
            }
        }
        if (isset($filter['min'])) {
            $sql_main .= " AND a.sort_no >= :min ";
            $sql_values[':min'] = $filter['min'];
        }
        if (isset($filter['max'])) {
            $sql_main .= " AND a.sort_no <= :max ";
            $sql_values[':max'] = $filter['max'];
        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_join = '';
            if ($type == 1) {
                $sql_join = " OR b.goods_code LIKE :deliver_goods_code ";
                $sql_values[':deliver_goods_code'] = '%' . $filter['sell_record_code'] . '%';
            }
            $sql_main .= " AND ( a.sell_record_code LIKE :sell_record_code OR a.deal_code LIKE :deal_code or a.buyer_name LIKE :buyer_name or a.receiver_name LIKE :receiver_name or a.express_no LIKE :express_no {$sql_join})";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':deal_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':express_no'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':buyer_name'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':receiver_name'] = '%' . $filter['sell_record_code'] . '%';
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND a.express_code = :express_code ";
            $sql_values[':express_code'] = $filter['express_code'];
        }

        if (isset($filter['express_no'])) {
            $sql_main .= " AND a.express_no = :express_no ";
            $sql_values[':express_no'] = $filter['express_no'];
        }
        //是否已发货
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND a.is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != '') {
            $sql_main .= " AND a.is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != '') {
            $sql_main .= " AND a.is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        return array('sql_main' => $sql_main, 'sql_values' => $sql_values);
    }

    public function get_templates_type($waves_record_id) {
        $sql = "select pay_type from {$this->table} where waves_record_id = :waves_record_id and is_cancel = 0"; //因 快递方式都一样 所以只查第一个
        $pay_type = $this->db->get_value($sql, array(':waves_record_id' => $waves_record_id));
        return $pay_type;
    }

    public function check_record_express($filter) {
        $sql_main = " from oms_deliver_record a
        		LEFT JOIN oms_sell_record c ON a.sell_record_code = c.sell_record_code
        		 where 1 and is_cancel = 0 ";
        $sql_values = array();
        $ret = $this->search_terms($filter, $sql_main, $sql_values);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_values'];
        $sql = "select DISTINCT a.express_code " . $sql_main;
        $data = $this->db->get_all($sql, $sql_values);

        if (empty($data)) {
            return $this->format_ret(-1, $data, '未找打需要打印的单据');
        }
        //var_dump($filter, $sql, $sql_values, $data);
        if (count($data) > 1) {
            return $this->format_ret(-1, $data, '配送方式不统一');
        }
        $express_code = $data[0]['express_code'];

        /* $data = $this->db->get_all("select sell_record_code " . $sql_main . " AND express_no='' ", $sql_values);
          if (count($data)) {
          return $this->format_ret(-2, $data, '存在快递单号为空单据');    //打印过程单据锁定
          } */

        return $this->format_ret(1, $express_code);
    }

    /**
     * 发货单打印数据
     * @param $filter
     * @return array
     * @throws Exception
     */
    public function get_print_deliver_record_data($filter) {
        //$waves_record_id
        $sql_main = "from oms_deliver_record where 1 ";

        $sql_values = array();
        $filter['page_size'] = !isset($filter['page_size']) ? 30 : intval($filter['page_size']); //默认一次30张快递单据

        if (isset($filter['waves_record_ids'])) {
            $sql_main .= " AND waves_record_id in({$filter['waves_record_ids']}) ";
        }
        if (isset($filter['deliver_record_ids'])) {
            $sql_main .= " AND deliver_record_id in({$filter['deliver_record_ids']}) ";
        }
        if (isset($filter['min'])) {
            $sql_main .= " AND sort_no >= :min ";
            $sql_values[':min'] = $filter['min'];
        }
        if (isset($filter['max'])) {
            $sql_main .= " AND sort_no <= :max ";
            $sql_values[':max'] = $filter['max'];
        }

        $select = "*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $print_ids = array();
        $print_codes = array();
        $qll = array();
        foreach ($data['data'] as &$_order) {
            if ($_order['is_print_sellrecord'] != 1) {
                $print_ids[] = $_order['deliver_record_id'];
                $print_codes[] = $_order['sell_record_code'];
                $qll[] = array('sale_channel_code' => $_order['sale_channel_code'], 'shop_code' => $_order['shop_code'], 'deal_code' => $_order['deal_code']);
            }
            //省，市，区
            if ($_order['receiver_province']) {
                $_order['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_province']));
            }

            if ($_order['receiver_city']) {
                $_order['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_city']));
            }

            if ($_order['receiver_district']) {
                $_order['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_district']));
            }

            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($_order['sell_record_code'], 'receiver_addr,receiver_name,receiver_mobile');
            $_order = array_merge($_order, $record_decrypt_info);

            $adds_data = $this->get_join_addr($_order);
            $_order['receiver_address'] = $adds_data['receiver_province'] . $adds_data['receiver_city'] . $adds_data['receiver_district'] . $adds_data['receiver_addr'];



            //寄送人信息
            $sql = "select * from base_store where store_code = :store_code";
            $store = $this->db->get_row($sql, array('store_code' => $_order['store_code']));
            $_order['sender'] = $store['contact_person'];
            $_order['sender_tel'] = $store['contact_tel'];
            $_order['sender_phone'] = $store['contact_phone'];
            $_order['sender_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $_order['shop_code']));
            $country = oms_tb_val('base_area', 'name', array('id' => $store['country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $store['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $store['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $store['district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $store['street']));
            $_order['sender_country'] = $country;
            $_order['sender_province'] = $province;
            $_order['sender_city'] = $city;
            $_order['sender_district'] = $district;
            $_order['sender_street'] = $street;
            $_order['sender_addr'] = $street . ' ' . $store['address'];
            $_order['sender_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $store['address'];
            $_order['sender_zip'] = $store['zipcode'];
            $_order['print_time'] = date('Y-m-d H:i:s');
            $_order['express_no_pack_no'] = $_order['express_no'] . '-1-1-'; //京东pack_no
            //过滤$_order数组内的值为空，导致打印快递单出错
            foreach ($_order as $_key => $_order_value) {
                if (!$_order_value) {
                    $_order[$_key] = '';
                }
            }
//            $sql = "select d.goods_code,d.goods_price  ,d.avg_money
//                from oms_deliver_record_detail d
//                where d.deliver_record_id=:deliver_record_id";
//            $_order['detail'] = $this->db->get_all($sql, array(':deliver_record_id' => $_order['deliver_record_id']));
////                    d.barcode,d.num,goods_name,g.goods_short_name,s1.spec1_name,s2.spec2_name
            $detail_key_arr = array('goods_code', 'goods_price', 'avg_money', 'barcode', 'num', 'goods_name', 'goods_short_name', 'spec1_name', 'spec2_name');
            $_order['detail'] = $this->get_deliver_detail($_order['deliver_record_id'], $detail_key_arr);


            $detail_content = '';
            $ret_tpl = load_model("sys/SendTemplatesModel")->get_templates_by_id(3); //FIXME: 暂时测试
            $_order['print_templates_id'] = $ret_tpl['data']['id'];

            if (isset($ret_tpl['data']['template_val']['detail_val']) && !empty($ret_tpl['data']['template_val']['detail_val'])) {
                $template_val = &$ret_tpl['data']['template_val'];
                $detail_tpl_key = $ret_tpl['data']['template_val']['detail_val'];
                $id = 1;
                foreach ($_order['detail'] as $detail_val) {


                    $detail_val['id'] = $id;
                    $detail_content .= "<tr>";
                    foreach ($detail_tpl_key as $item) {
                        $detail_content .= '<td>' . $detail_val[$item] . '</td>';
                    }
                    $detail_content .= "</tr>";
                    $id++;
                }
                $template_val['detail']['html'] = str_replace('<!--$detail_list-->', $detail_content, $template_val['detail']['html']);
                $_order['detail_tpl'] = $template_val['detail'];
            }
        }

        // 必须存在波次单号的前提下才更新, 注意入参必须传入waves_record_ids
        if (isset($filter['waves_record_ids'])) {
            // 更新发货单表的快递单打印状态
            if (!empty($print_ids)) {
                $this->update(array("is_print_sellrecord" => 1), " deliver_record_id in (" . implode(",", $print_ids) . ")");
            }
            if (!empty($print_codes)) {
                // 更新订单表的快递单打印状态
                $this->db->update('oms_sell_record', array("is_print_sellrecord" => 1), " sell_record_code in ('" . implode("','", $print_codes) . "')");
            }
            if (isset($filter['deliver_record_ids']) || (isset($filter['min']) && isset($filter['max']))) {
                //判断是否全部打印完成
                $sql = "select count(*) from oms_deliver_record where waves_record_id = :id and is_print_sellrecord = 0";
                $count = $this->db->get_value($sql, array('id' => $filter['waves_record_ids']));
                if ($count == 0) {
                    //更新波次单的快递单打印状态
                    $this->db->update('oms_waves_record', array("is_print_sellrecord" => 2), array('waves_record_id' => $filter['waves_record_ids']));
                } else {
                    $this->db->update('oms_waves_record', array("is_print_sellrecord" => 1), array('waves_record_id' => $filter['waves_record_ids']));
                }
            } else {
                $waves_record_ids = explode(',', $filter['waves_record_ids']);
                // 直接更新波次单下所有发货单和订单的快递单打印状态
                $this->db->update('oms_waves_record', array("is_print_sellrecord" => 2), " waves_record_id in ('" . implode("','", $waves_record_ids) . "')");
            }

            foreach ($qll as $k => $v) {
                load_model('oms/SellRecordActionModel')->add_action_to_api($v['sale_channel_code'], $v['shop_code'], $v['deal_code'], 'X_SEND_PRINTED');
            }
        }

        return $this->format_ret(1, $data);
    }

    public function check_is_print_express($filter) {

        $sql_main = " from oms_deliver_record where 1  ";

        $sql_values = array();

        if (isset($filter['waves_record_ids'])) {
            $sql_main .= " AND waves_record_id in({$filter['waves_record_ids']}) ";
        }
        if (isset($filter['deliver_record_ids'])) {
            if (empty($filter['deliver_record_ids'])){
                return $this->format_ret(-2);
            }
            $sql_main .= " AND deliver_record_id in({$filter['deliver_record_ids']}) ";
        }
        if (isset($filter['min'])) {
            $sql_main .= " AND sort_no >= :min ";
            $sql_values[':min'] = $filter['min'];
        }
        if (isset($filter['max'])) {
            $sql_main .= " AND sort_no <= :max ";
            $sql_values[':max'] = $filter['max'];
        }

        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND ( sell_record_code LIKE :sell_record_code OR deal_code LIKE :deal_code)";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':deal_code'] = '%' . $filter['sell_record_code'] . '%';
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND express_code = :express_code ";
            $sql_values[':express_code'] = $filter['express_code'];
        }
        //是否已发货
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != '') {
            $sql_main .= " AND is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != '') {
            $sql_main .= " AND is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }

        $sql = "select deliver_record_id,sell_record_code,is_print_express,is_cancel  " . $sql_main . "  ";
        $data = $this->db->get_all($sql, $sql_values);
        //已打印的
        $print_data = array();
        //未取消的
        $deliver_record_arr = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                if ($val['is_print_express'] > 0 && $val['is_cancel'] == 0 ) {
                    $print_data[] = $val['sell_record_code'];
                }
                if ($val['is_cancel'] == 0) {
                    $deliver_record_arr[] = $val['deliver_record_id'];
                }
            }
        }
        $ret_status = empty($print_data) ? 1 : -1;
        if ($ret_status === -1) {
            $fail_top = array('错误信息');
            $print_num = count($print_data);
            $ret_data['print_data'] = "共{$print_num}张订单";
            $file_name = $this->create_import_fail_file($fail_top, $print_data,'express');
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $ret_data['print_data'] .= "，<a target=\"_blank\" href=\"{$url}\" >下载订单号</a><br/> ";
        }
        $ret_status = empty($deliver_record_arr) ? -2 : $ret_status;

        $ret_data['deliver_record_ids'] = implode(',', $deliver_record_arr);

        return $this->format_ret($ret_status, $ret_data);
    }
    
    public function check_is_print_sellrecord($filter) {
        $sql_main = " from oms_deliver_record where 1  ";
        $sql_values = array();
        if (isset($filter['waves_record_ids'])) {
            $sql_main .= " AND waves_record_id in({$filter['waves_record_ids']}) ";
        }
        if (isset($filter['deliver_record_ids']) && $filter['deliver_record_ids'] != '' ) {
            $sql_main .= " AND deliver_record_id in({$filter['deliver_record_ids']}) ";
        } else if(isset($filter['deliver_record_ids'])) {
            $sql_main .= " AND deliver_record_id in('{$filter['deliver_record_ids']}') ";
        }
        if (isset($filter['min'])) {
            $sql_main .= " AND sort_no >= :min ";
            $sql_values[':min'] = $filter['min'];
        }
        if (isset($filter['max'])) {
            $sql_main .= " AND sort_no <= :max ";
            $sql_values[':max'] = $filter['max'];
        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND ( sell_record_code LIKE :sell_record_code OR deal_code LIKE :deal_code)";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':deal_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND express_code = :express_code ";
            $sql_values[':express_code'] = $filter['express_code'];
        }
        //是否已发货
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != '') {
            $sql_main .= " AND is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != '') {
            $sql_main .= " AND is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        $sql = "select deliver_record_id,sell_record_code,is_print_sellrecord,is_cancel  " . $sql_main . "  ";
        $data = $this->db->get_all($sql, $sql_values);
        //已打印的
        $print_data = array();
        //未取消的
        $deliver_record_arr = array();
        $sell_record_arr = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                if ($val['is_print_sellrecord'] > 0 && $val['is_cancel'] == 0 ) {
                    $print_data[] = $val['sell_record_code'];
                }
                if ($val['is_cancel'] == 0) {
                    $deliver_record_arr[] = $val['deliver_record_id'];
                    $sell_record_arr [] = $val['sell_record_code'];
                }
            }
        }
        $ret_status = empty($print_data) ? 1 : -1;
        if ($ret_status === -1) {
            $fail_top = array('错误信息');
            $print_num = count($print_data);
            $ret_data['print_data'] = "共{$print_num}张订单";
            $file_name = $this->create_import_fail_file($fail_top, $print_data,'sellrecord');
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $ret_data['print_data'] .= "，<a target=\"_blank\" href=\"{$url}\" >下载订单号</a><br/> ";
        }
        $ret_status = empty($deliver_record_arr) ? -2 : $ret_status;
        $ret_data['deliver_record_ids'] = implode(',', $deliver_record_arr);
        $ret_data['sell_record_code'] = implode(',', $sell_record_arr);
        return $this->format_ret($ret_status, $ret_data);
    }
    
    //错误信息详情导出
    public function create_import_fail_file($fail_top, $error_msg, $type) {
        $file_str = implode(",", $fail_top) . "\n";
        $err_str = $type === 'sellrecord' ? '已经打印发货单' : '已经打印快递单';
        foreach ($error_msg as $val) {
            $file_str .= "订单号：".$val.$err_str."\r\n";
        }
        $filename = md5("order_refund" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    public function get_record_print_page($filter) {
        //$waves_record_id
        // 查询不可带取消，处理中带取消，否则影响打印过程 数据顺序变化
        $sql_main = " from oms_deliver_record where 1 ";

        $sql_values = array();
        $filter['page_size'] = !isset($filter['page_size']) ? 30 : intval($filter['page_size']); //默认一次30张快递单据

        if (isset($filter['waves_record_ids'])) {
            $sql_main .= " AND waves_record_id in({$filter['waves_record_ids']}) ";
        }
        if (isset($filter['deliver_record_ids'])) {
            $sql_main .= " AND deliver_record_id in({$filter['deliver_record_ids']}) ";
        }
        if (isset($filter['min'])) {
            $sql_main .= " AND sort_no >= :min ";
            $sql_values[':min'] = $filter['min'];
        }
        if (isset($filter['max'])) {
            $sql_main .= " AND sort_no <= :max ";
            $sql_values[':max'] = $filter['max'];
        }

        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND ( sell_record_code LIKE :sell_record_code OR deal_code LIKE :deal_code)";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
            $sql_values[':deal_code'] = '%' . $filter['sell_record_code'] . '%';
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND express_code = :express_code ";
            $sql_values[':express_code'] = $filter['express_code'];
        }
        //是否已发货
        if (isset($filter['is_deliver']) && $filter['is_deliver'] != '') {
            $sql_main .= " AND is_deliver = :is_deliver ";
            $sql_values[':is_deliver'] = $filter['is_deliver'];
        }
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != '') {
            $sql_main .= " AND is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != '') {
            $sql_main .= " AND is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        $select = "*";
        $page = isset($filter['page']) ? (int) $filter['page'] : 1;
        if ($filter['is_print_express'] == '0' && $page > 1) {
            $filter['page'] = 1;
            $page = 1;
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        if ($page > (int) $data['filter']['page_count']) {
            $data['data'] = array();
        }


        $print_ids = array();
        $print_codes = array();
        //sender_operprint
        $qll = array();
        $isLof = $this->db->get_value("select `value` FROM sys_params where param_code = 'lof_status'");
        $sender_operprint = CTX()->get_session('user_name');
        $is_cainiao_print = -1;
        $express_code = '';
        $waves_ids_arr = array();
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package', 'order_by_goods_sprice'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        // AND is_cancel=0
        $this->begin_trans();
        foreach ($data['data'] as $o_key => &$_order) {
            //取消的不打印
            if ($_order['is_cancel'] > 0) {
                unset($data['data'][$o_key]);
                continue;
            }

            $express_code = empty($express_code) ? $_order['express_code'] : $express_code;

            if ($express_code != $_order['express_code']) {
                return $this->format_ret(-1, '', '选择单据配送方式必须相同！');
            }
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($_order['sell_record_code']);
            $_order = array_merge($_order, $record_decrypt_info);

            $_order['sender_operprint'] = $sender_operprint;
            $_order['sell_money'] = $_order['goods_money'];
            $_order['deal_code'] = $_order['deal_code_list'];

            $_order['receiver_mobile'] = empty($_order['receiver_mobile']) ? $_order['receiver_phone'] : $_order['receiver_mobile'];


            if ($_order['is_print_express'] != 1) {
                $print_ids[] = $_order['deliver_record_id'];
                $print_codes[] = $_order['sell_record_code'];
                $waves_ids_arr[$_order['waves_record_id']] = $_order['waves_record_id'];
                $qll[] = array('sale_channel_code' => $_order['sale_channel_code'], 'shop_code' => $_order['shop_code'], 'deal_code' => $_order['deal_code']);
            }
            if (!empty($_order['pay_code'])) {
                $_order['pay_code'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $_order['pay_code']));
            }
            //省，市，区
            if ($_order['receiver_province']) {
                $_order['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_province']));
            }

            if ($_order['receiver_city']) {
                $_order['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_city']));
            }

            if ($_order['receiver_district']) {
                $_order['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_district']));
            } else {
                $_order['receiver_district'] = '';
            }

            if ($_order['receiver_street']) {
                $_order['receiver_street'] = oms_tb_val('base_area', 'name', array('id' => $_order['receiver_street']));
            } else {
                $_order['receiver_street'] = '';
            }

            $_order['receiver_addr'] = $_order['receiver_addr'];
            $adds_data = $this->get_join_addr($_order);
            $_order['receiver_address'] = $adds_data['receiver_province'] . $adds_data['receiver_city'] . $adds_data['receiver_district'] . $adds_data['receiver_street'] . $adds_data['receiver_addr'];

            if ($is_more_deliver_package == 1 && $_order['package_no'] > 1) {
                $express_data = $this->get_package_express_no_by_package_no($_order['sell_record_code'], $_order['waves_record_id'], $_order['package_no']);
                if (!empty($express_data)) {
                    $_order['express_no'] = $express_data['express_no'];
                    $_order['express_data'] = $express_data['express_data'];
                }
            }

            if ($_order['pay_type'] == 'cod' && $_order['sale_channel_code'] == 'jingdong') {
                $payable_money = $this->get_jd_cod_payable_money($_order['deal_code']);
                if ($payable_money !== false) {
                    $_order['payable_money'] = $payable_money;
                }
            }

            //当当货到付款
            if ($_order['pay_type'] == 'cod' && $_order['sale_channel_code'] == 'dangdang') {
                $dangdang_row = $this->get_dangdang_print($_order['deal_code']);
                $_order['payable_money'] = $dangdang_row['totalBarginPrice'];
                $_order['shopID'] = $dangdang_row['shopID'];
                $_order['shopName'] = $dangdang_row['shopName'];
                $_order['sendGoodsTime'] = $dangdang_row['sendGoodsTime'];
            }
            // 大头笔
            $_order['receiver_top_address'] = '';
            $order_cainiao_print = 0;
            $cainiao_check_data = array();
            if (!empty($_order['express_data'])) {
                $d = json_decode($_order['express_data'], true);
                if (!empty($d) && isset($d['print_config'])) {//云栈
                    $_order['receiver_top_address'] = isset($d['short_address']) ? $d['short_address'] : '';
                    $_order['package_center_name'] = isset($d['package_center_name']) ? $d['package_center_name'] : '';
                    $_order['package_center_code'] = isset($d['package_center_code']) ? $d['package_center_code'] : '';
                    $_order['print_config'] = $d['print_config'];
                    $_order['shipping_branch_name'] = $d['shipping_branch_name'];
                    $real_user_id = $d['trade_order_info']['real_user_id'];
                    //服务列表（代收货款）
                    if (isset($d['trade_order_info']['logistics_service_list'])) {
                        $service_list = $d['trade_order_info']['logistics_service_list']['logistics_service'];
                        $_order['logistics_service_list'] = $service_list;
                        if ($_order['pay_type'] == 'cod') {
                            foreach ($service_list as $service) {
                                if ($service['service_code'] == 'SVC-COD') {
                                    $service_val = json_decode($service['service_value4_json'], true);
                                    $_order['ali_waybill_serv_cod_amount'] = $service_val['value'];
                                }
                            }
                        }
                    }

                    $order_cainiao_print = 1;
                }
                if (!empty($d) && isset($d['j_custid'])) {//顺丰直连电子面单
                    $_order['originCode'] = $d['originCode'];
                    $_order['destCode'] = $d['destCode'];
                    $_order['j_custid'] = $d['j_custid'];
                    $_order['express_type'] = $d['express_type'];
                    //应收
                    //货到付款订单
                    if ($_order['pay_code'] == 'cod') {
                        $_order['daishou'] = $_order['payable_money'] - $_order['paid_money'];
                    } else {
                        $_order['daishou'] = "";
                    }
                }
                if(!empty($d) && isset($d['bigShotCode'])){//无界
                    //大头笔
                    $_order['add_code'] = $d['bigShotName'];//顺风作为目的地代码
                    $_order['maker'] = $d['bigShotName'];
                    if($d['secondSectionCode'] != '')   $_order['maker'] .= '-'.$d['secondSectionCode'];
                    if($d['thirdSectionCode'] != '')   $_order['maker'] .= '-'.$d['thirdSectionCode'];
                    //集包地
                    $_order['jbd_name'] = $d['gatherCenterName'];
                    //集包地编码
                    $_order['jbd_barcode'] = $d['gatherCenterCode'];
                    //面单号
                    $_order['waybill_code'] = $d['waybillCode'];
                    //logo,二维码，服务信息，产品类型 为空
                    $_order['card_no']='';//'卡号';
                    $_order['month_account']='';//'月结账号';
                    if($express_code != ''){
                        $sql = 'select j_custid from  shunfeng_rm_config where express_code = :express_code';
                        $arr = $this->db->get_all($sql,array(':express_code'=>$express_code));
                        $_order['month_account'] = implode(',',array_column($arr,'j_custid'));
                    }
                    $_order['express_log'] = '';
                    $_order['pr_express_log'] = '';
                    $_order['category_name'] = '';
                    $_order['service_info'] = '';
                    $_order['sale_qr_code'] = '';
                    $_order['express_qr_code'] = '';
                    $_order['express_type']='';//'电商专配';

                    $_order['order_money']='';//'金额';
                    $_order['receiver_company_name']='';//'XX公司';
                    $_order['sender_type']='';//'定时派送';
                    $_order['sender_get_type']='';//'自寄 自取';
                    $_order['c_recerver']='';//'收件员';
                    $_order['sender_date']='';//'寄件日期';
                    $_order['c_sender']='';//'派件员';
                    $_order['other_add']='';//'第三方地区';

                    $_order['time_to_sender']='';//'定时派送时间';
                    $_order['protect_fee']='';//'保价费用';
                    $_order['state_price']='';//'声明价值';
                    $_order['price_weight']='';//'计费重量';
                    $_order['package_money']='';//'包装费用';
                }
            }
            //是否为菜鸟
            $is_cainiao_print = $is_cainiao_print < 0 ? $order_cainiao_print : $is_cainiao_print;

            if ($is_cainiao_print != $order_cainiao_print) {

                return $this->format_ret(-1, '', '选择单据物流单号异常(' . $_order['express_no'] . ')，不是同一类型，不能打印！');
            }

            //寄送人信息
            $sql = "select * from base_store where store_code = :store_code";
            $store = $this->db->get_row($sql, array('store_code' => $_order['store_code']));

            $express_data_arr = array();
            if (!empty($_order['express_data'])) {
                $express_data_arr = json_decode($_order['express_data'], true);
            }
            $_order['initial'] = isset($express_data_arr['sourcetSortCenterName']) ? $express_data_arr['sourcetSortCenterName'] : '';
            $_order['end_point'] = isset($express_data_arr['targetSortCenterName']) ? $express_data_arr['targetSortCenterName'] : '';
            $_order['shop_id_jd'] = isset($express_data_arr['customerCode']) ? $express_data_arr['customerCode'] : '';
            $_order['initial_code'] = isset($express_data_arr['originalCrossCode']) && isset($express_data_arr['originalTabletrolleyCode']) ? $express_data_arr['originalCrossCode'] . '-' . $express_data_arr['originalTabletrolleyCode'] : '';
            $_order['end_point_code'] = isset($express_data_arr['destinationCrossCode']) && isset($express_data_arr['destinationTabletrolleyCode']) ? $express_data_arr['destinationCrossCode'] . '-' . $express_data_arr['destinationTabletrolleyCode'] : '';
            $_order['destination_site'] = isset($express_data_arr['siteName']) ? $express_data_arr['siteName'] : '';
            $_order['road_area'] = isset($express_data_arr['road']) ? $express_data_arr['road'] : '';
            $_order['initial_order'] = '1/1';

            $send_address = '';
            //若店铺有维护联系人，联系地址，优先取，若未维护再取仓库联系人、联系地址 task#462 2016.7.14 FBB
            $send_info = load_model('base/ShopModel')->get_send_info($_order['shop_code'], $_order['store_code']);
            $_order['sender'] = $send_info['data']['contact_person'];
            $_order['sender_mobile'] = $send_info['data']['contact_tel'];
            $_order['sender_phone'] = $send_info['data']['contact_tel'];
            $province = oms_tb_val('base_area', 'name', array('id' => $send_info['data']['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $send_info['data']['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $send_info['data']['district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $send_info['data']['street']));
            $send_address = $send_info['data']['address'];
            if (empty($send_address)) {
                $_order['sender'] = $store['contact_person'];
                $_order['sender_mobile'] = $store['contact_tel'];
                $_order['sender_phone'] = $store['contact_phone'];
                $province = oms_tb_val('base_area', 'name', array('id' => $store['province']));
                $city = oms_tb_val('base_area', 'name', array('id' => $store['city']));
                $district = oms_tb_val('base_area', 'name', array('id' => $store['district']));
                $street = oms_tb_val('base_area', 'name', array('id' => $store['street']));
                $send_address = $store['address'];
            }

            $_order['sender_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $_order['shop_code']));
            $_order['waves_record_code'] = oms_tb_val('oms_waves_record', 'record_code', array('waves_record_id' => $_order['waves_record_id']));
            $country = oms_tb_val('base_area', 'name', array('id' => $store['country']));

            $_order['sender_country'] = $country;
            $_order['sender_province'] = $province;
            $_order['sender_city'] = $city;
            $_order['sender_district'] = $district;
            $_order['sender_street'] = $street;
            $_order['sender_addr'] = $street . ' ' . $send_address;
            $_order['sender_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $send_address;
            $_order['sender_zip'] = $send_info['data']['zipcode'];
            //当当货到付款
            if ($_order['pay_type'] == 'cod' && $_order['sale_channel_code'] == 'dangdang') {
                $_order['sender'] = $dangdang_row['consignerName'];
                $_order['sender_mobile'] = $dangdang_row['consignerTel'];
                $_order['sender_phone'] = $dangdang_row['consignerTel'];
            }
            $_order['print_time'] = date('Y-m-d H:i:s');
            $_order['print_time2'] = date('m/d/Y');
            $_order['express_no_pack_no'] = $_order['express_no'] . '-1-1-'; //京东pack_no
            //过滤$_order数组内的值为空，导致打印快递单出错
            foreach ($_order as $_key => $_order_value) {
                if (is_null($_order_value)) {
                    $_order[$_key] = '';
                }
            }

            $_order['payable_money'] = sprintf("%.2f", round($_order['payable_money'], 2));

            $_order['big_payable_money'] = $this->convert_num_to_cn($_order['payable_money']);
            //$storeCode = $_order['store_code'];
//            $sql = "select d.goods_code,d.barcode,d.num,d.platform_spec,goods_name,g.goods_short_name,s1.spec1_name,s2.spec2_name, d.sku,s.remark as sku_remark
//                from oms_deliver_record_detail d
//                inner join base_goods g  ON d.goods_code = g.goods_code
//                inner join base_spec1 s1 ON d.spec1_code = s1.spec1_code
//                inner join base_spec2 s2 ON d.spec2_code = s2.spec2_code
//            	inner join goods_sku s ON d.sku = s.sku
//                where d.deliver_record_id=:deliver_record_id";
//            $_order['detail'] = $this->db->get_all($sql, array(':deliver_record_id' => $_order['deliver_record_id']));
            //    d.goods_code,d.barcode,d.num,d.platform_spec,goods_name,g.goods_short_name,s1.spec1_name,s2.spec2_name, d.sku,s.remark as sku_remark
            // $detail_key_arr = array('goods_code','barcode','num','platform_spec','goods_name','goods_short_name','spec1_name','spec2_name','sku','remark');
            $detail_key = array('goods_code', 'num', 'platform_spec', 'sku', 'deal_code', 'goods_price');
            $_order['detail'] = $this->get_deliver_detail($_order['deliver_record_id'], $detail_key, false);


            $sku_array = array();
            foreach ($_order['detail'] as $key => $detail) {//合并同一sku
                $sku_key_arr = array('barcode', 'goods_name', 'goods_short_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'weight', 'remark','category_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $sku_key_arr);

                $detail['sku_remark'] = $sku_info['remark'];
                $detail = array_merge($detail, $sku_info);
                $_order['detail'][$key] = $detail;

                if (in_array($detail['sku'], $sku_array)) {
                    $exist_key = array_keys($sku_array, $detail['sku']);
                    $_order['detail'][$exist_key[0]]['num'] += $detail['num'];
                    unset($_order['detail'][$key]);
                } else {
                    $sku_array[$key] = $detail['sku'];
                }
            }
            $lof = '';
            if ($isLof == '1') { //启用批次
                $lof = 'and b.lof_no = a.batch_number';
            }
            $shelf_code_arr = array();
            $goods_price_arr = array();
            foreach ($_order['detail'] as $k => &$v) {
                $sql = "select a.shelf_code,bs.shelf_name from goods_shelf a
                inner join base_shelf bs on a.shelf_code=bs.shelf_code
            	inner join oms_sell_record_lof b on b.store_code = a.store_code and b.sku = a.sku $lof
            	where b.record_code = :record_code and bs.store_code=b.store_code and b.record_type = '1' and b.sku = :sku order by a.sku,a.shelf_code asc";
                $l = $this->db->get_all($sql, array('record_code' => $_order['sell_record_code'], 'sku' => $v['sku']));
                $v['shelf_code'] = implode(',', array_column($l, 'shelf_code'));
                $v['shelf_name'] = implode(',', array_column($l, 'shelf_name'));
                $shelf_code_arr[] = $v['shelf_code'];
                //获取打印单上商品重量数据,默认获取sku级商品重量,没有数据,或者数据为0则获取商品级重量
                if (!empty($v['weight']) && $v['weight'] != 0) {
                    $v['weigh'] = sprintf("%.3f", ($v['weight']) / 1000) . "Kg";
                } else {
                    $weigh_sql = "SELECT weight FROM base_goods WHERE goods_code=:goods_code";
                    $goods_weigh = $this->db->get_value($weigh_sql, array(":goods_code" => $v['goods_code']));
                    $v['weigh'] = (isset($goods_weigh) && $goods_weigh != 0) ? sprintf("%.3f", ($goods_weigh) / 1000) . "Kg" : "0.000Kg";
                }
                //吊牌价
                $goods_price_arr[] = $v['goods_price'];
                $goods_code_arr[]=$v['goods_code'];
                $barcode_arr[]=$v['barcode'];
                //如果库位为空，设置00000000为库位排序依据（希望没有库位的排在最前面）
                if($v['shelf_code']==''){
                    $new_arr[]='00000000'.$v['goods_code'].$v['barcode'];
                    $v['test']='00000000'.$v['goods_code'].$v['barcode'];
                }else{
                    $new_arr[]=$v['shelf_code'].$v['goods_code'].$v['barcode'];
                    $v['test']=$v['shelf_code'].$v['goods_code'].$v['barcode'];
                }

            }

            //参数开启，按吊牌价排序。关闭，按库位排序
            /*if ($ret_pararm['order_by_goods_sprice'] == 1) {
                array_multisort($goods_price_arr, SORT_DESC, $_order['detail']);
            } else {
                array_multisort($shelf_code_arr, SORT_ASC, $_order['detail']);
            }*/
            switch($ret_pararm['order_by_goods_sprice']){
                case 0:
                    array_multisort($new_arr, SORT_ASC, SORT_STRING ,$_order['detail']);
                    /*if(count(array_unique($shelf_code_arr))!=1){
                        array_multisort($shelf_code_arr, SORT_ASC, $_order['detail']);
                    }elseif (count(array_unique($goods_code_arr))!=1){
                        array_multisort($goods_code_arr, SORT_ASC, $_order['detail']);
                    }else{
                        array_multisort($barcode_arr, SORT_ASC, $_order['detail']);
                    }*/
                    break;
                case 1:
                    array_multisort($goods_price_arr, SORT_ASC, $_order['detail']);
                    break;
                case 2:
                    array_multisort($goods_price_arr, SORT_DESC, $_order['detail']);
                    break;
            }
            $_order['real_weigh'] = (isset($_order['real_weigh']) && $_order['real_weigh'] != 0) ? sprintf("%.3f", ($_order['real_weigh'])) . "Kg" : "0.000Kg";
            if ($is_cainiao_print == 1) {
                $receiver_addr = empty($_order['receiver_addr']) ? $_order['receiver_street'] : $_order['receiver_addr'];


                $cainiao_check_data_row = array(
                    'consignee_name' => $_order['receiver_name'], //收件人姓名  是
                    'send_phone' => $_order['receiver_mobile'], //发件人联系方式  否
                    'waybill_code' => $_order['express_no'],
                    'consignee_address' => array('province' => $_order['receiver_province'], 'city' => $_order['receiver_city'], 'area' => $_order['receiver_district'], 'town' => $_order['receiver_street'], 'address_detail' => $receiver_addr), //是
                    'send_name' => $_order['sender'], //否
                    'short_address' => $_order['receiver_top_address'], //否
                    'consignee_phone' => $_order['receiver_mobile'],
                    'shipping_address' => array('province' => $_order['sender_province'], 'city' => $_order['sender_city'], 'area' => $_order['sender_district'], 'town' => $_order['sender_street'], 'address_detail' => $store['address']), //是
                    'package_center_code' => $_order['package_center_code'], //目的地中心代码
                    'package_center_name' => $_order['package_center_name'], //集包地
                    'print_config' => $_order['print_config'],
                    'real_user_id' => $real_user_id,
                        // 'logistics_service_list'=>$_order['logistics_service_list'],
                );
                if (isset($_order['logistics_service_list'])) {
                    $cainiao_check_data_row['logistics_service_list'] = $_order['logistics_service_list'];
                }
                $cainiao_check_data[] = $cainiao_check_data_row;
            }


            //快递单打印日志
            load_model('oms/SellRecordModel')->add_action($_order['sell_record_code'], '快递单打印');
        }
        //此处去掉打印效验接口（支持多地址打印）, task#462, 2016.7.14, FBB
        // 必须存在波次单号的前提下才更新, 注意入参必须传入waves_record_ids

        if (isset($filter['waves_record_ids']) && !empty($print_ids) && !empty($print_codes)) {

            // 更新发货单表的快递单打印状态
            $this->update(array("is_print_express" => 1), " deliver_record_id in (" . implode(",", $print_ids) . ")");

            // 更新订单表的快递单打印状态
            $this->db->update('oms_sell_record', array("is_print_express" => 1), " sell_record_code in ('" . implode("','", $print_codes) . "')");

            if (isset($waves_ids_arr)) {//$waves_ids_arr
                //判断是否全部打印完成
                $sql = "select waves_record_id,count(*) as print_num from oms_deliver_record where  is_print_express = 0 AND   is_cancel=0 ";
                $sql .= " AND waves_record_id in('" . implode("','", $waves_ids_arr) . "') GROUP BY waves_record_id ";
                $print_express_data = $this->db->get_all($sql);

                foreach ($print_express_data as $p_val) {
                    $update_data = array("is_print_express" => 1);

                    $this->db->update('oms_waves_record', $update_data, array('waves_record_id' => $p_val['waves_record_id']));
                    unset($waves_ids_arr[$p_val['waves_record_id']]);
                }
                if (!empty($waves_ids_arr)) {
                    $this->db->update('oms_waves_record', array("is_print_express" => 2), "waves_record_id in('" . implode("','", $waves_ids_arr) . "') ");
                }
            }

            foreach ($qll as $k => $nv) {
                load_model('oms/SellRecordActionModel')->add_action_to_api($nv['sale_channel_code'], $nv['shop_code'], $nv['deal_code'], 'X_LOGISTICS_PRINTED');
            }
        }
        $this->commit();

        return $this->format_ret(1, $data);
    }

    public function get_jd_cod_payable_money($deal_code) {
        $sql = "select order_payment from api_jingdong_trade where order_id=:order_id ";
        $row = $this->db->get_row($sql, array(':order_id' => $deal_code));
        if (empty($row)) {
            return false;
        }
        return $row['order_payment'];
    }

    private function get_dangdang_print($deal_code) {
        $sql = "select * from api_dangdang_print where orderID=:orderID ";
        $row = $this->db->get_row($sql, array(':orderID' => $deal_code));
        if (empty($row)) {
            return false;
        }
        return $row;
    }

    // 标记发货单打印状态
    public function mark_print($wavesRecordIds, $deliverRecordIds = '', $min = 0, $max = 0) {
        if (empty($wavesRecordIds)) {
            return array('status' => '-1', 'message' => '波次单不存在');
        }

        $wavesRecordIds = trim($wavesRecordIds, ',');
        $sql = "select * from oms_deliver_record where waves_record_id in ($wavesRecordIds) ";

        if (!empty($deliverRecordIds)) {
            $deliverRecordIds = trim($deliverRecordIds, ',');
            $sql .= " and deliver_record_id in ($deliverRecordIds) ";
        } else if (!empty($min) && !empty($max)) {
            $sql .= " and sort_no >= $min and sort_no <= $max ";
        }

        $ids = array();
        $codes = array();
        $qll = array();

        $data = $this->db->get_all($sql);
        foreach ($data as $row) {
            $ids[] = $row['deliver_record_id'];
            $codes[] = $row['sell_record_code'];
            $qll[] = array('sale_channel_code' => $row['sale_channel_code'], 'shop_code' => $row['shop_code'], 'deal_code' => $row['deal_code']);
        }

        // 更新发货单表的快递单打印状态
        $this->update(array("is_print_sellrecord" => 1), " deliver_record_id in (" . implode(",", $ids) . ")");

        // 更新订单表的快递单打印状态
        $this->db->update('oms_sell_record', array("is_print_sellrecord" => 1), " sell_record_code in ('" . implode("','", $codes) . "')");

        if (!empty($deliverRecordIds) || (!empty($min) && !empty($max))) {
            //判断是否全部打印完成
            $sql = "select count(*) from oms_deliver_record where waves_record_id = :id and is_print_sellrecord = 0";
            $count = $this->db->get_value($sql, array('id' => $wavesRecordIds));
            if ($count == 0) {
                //更新波次单的快递单打印状态
                $this->db->update('oms_waves_record', array("is_print_sellrecord" => 2), array('waves_record_id' => $wavesRecordIds));
            } else {
                $this->db->update('oms_waves_record', array("is_print_sellrecord" => 1), array('waves_record_id' => $wavesRecordIds));
            }
        } else {
            // 直接更新波次单下所有发货单和订单的快递单打印状态
            $this->db->update('oms_waves_record', array("is_print_sellrecord" => 2), " waves_record_id in ('" . $wavesRecordIds . "')");
        }

        foreach ($qll as $k => $v) {
            load_model('oms/SellRecordActionModel')->add_action_to_api($v['sale_channel_code'], $v['shop_code'], $v['deal_code'], 'X_SEND_PRINTED');
        }
    }

    /**
     * 供波次单打印使用的方法, 请勿随意修改
     * @param $deliverRecordId
     * @return array|bool
     */
    public function get_record_by_id($deliverRecordId) {
        $data = $this->db->get_row("select * from oms_deliver_record where deliver_record_id = :id", array('id' => $deliverRecordId));

        $data['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $data['store_code']));
        return $data;
    }

    public function get_detail_list_by_pid($deliverRecordId) {
        $sql = "select a.* from oms_deliver_record_detail a
        where a.deliver_record_id = :id ";
        $data = $this->db->get_all($sql, array('id' => $deliverRecordId));

        foreach ($data as $key => &$value) {
            $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
            $value['goods_short_name'] = oms_tb_val('base_goods', 'goods_short_name', array('goods_code' => $value['goods_code']));
            $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
            $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
        }
        return $data;
    }

    public function get_detail_list_by_waves_id($wavesRecordId) {
        $sql = "select b.* from oms_deliver_record a
        inner join oms_deliver_record_detail b on b.deliver_record_id = a.deliver_record_id
        where a.waves_record_id = :id and a.is_cancel = 0  and is_deliver = 0 ";
        $data = $this->db->get_all($sql, array('id' => $wavesRecordId));

        foreach ($data as $key => &$value) {

            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'goods_short_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
        }
        return $data;
    }

    public function get_detail_list_by_detailids($detailids) {
        $sql = "select b.* from oms_deliver_record a
        inner join oms_deliver_record_detail b on b.deliver_record_id = a.deliver_record_id
        where  a.is_cancel = 0 and b.deliver_record_detail_id in({$detailids})";
        $data = $this->db->get_all($sql, array());
        if (empty($data)) {
            return $this->format_ret(-1);
        }

        return $this->format_ret(1, $data);
    }

    public function get_record_for_weigh($express_no) {
        $r['status'] = '1';
        $sql = "select * from oms_deliver_record where express_no = :express_no and is_cancel = 0 and is_deliver = 1";
        $r['record'] = $this->db->get_row($sql, array('express_no' => $express_no));
        if (empty($r['record'])) {
            return array('status' => '-1', 'data' => '', 'message' => '订单不存在');
        }
        if ($r['record']['is_weigh'] == '1') {
            return array('status' => '-1', 'data' => '', 'message' => '订单已称重');
        }
        $r['record']['goods_weigh'] = number_format($r['record']['goods_weigh'], 2, '.', '');
        return $r;
    }

    /**
     * @param $express_no
     * @param $no_is_duplicate
     * @return array
     * @throws Exception
     */
    public function get_record_by_express_no($express_no, $no_is_duplicate, $tl = '') {
        // 判断物流单号是否符合规则，
        // 判断物流单号是否有对应的订单，
        // 判断物流单号是否已发货，
        // 判断物流单对应订单是否为待发货扫描状态（订单是否作废等），
        // TODO: 判断订单是否有平台退单，
        // 对于这几种判断，有一个错误即提示，均无错误则将物流单关联的订单的信息显示在下方
        $r['status'] = '1';
        if (!empty($no_is_duplicate)) { //校验物流单是否重复
            $sql = "select COUNT(*) from oms_deliver_record where express_no = :express_no and is_cancel = 0";
            $c = $this->db->get_value($sql, array('express_no' => $express_no));
            if ($c > 1) {
                return array('status' => '-1', 'data' => '', 'message' => '存在相同的物流单号');
            }
        }
        // 物流单号无对应订单时，提示‘未匹配到任何订单’
        //
        //9、物流单号对应订单已发货时，提示‘订单已发货’
        //
        //10、物流单号对应订单有退单时，提示‘订单已发生退货’
        //
        //11、物流单对应订单不是待扫描发货状态（即订单对应拣货单已验收且订单为作废）时，提示‘订单已作废或未完成配货’
        //（判断订单是否已生成波次且已验收，由是否必须要波次验收参数控制）

        $sql = "select * from oms_deliver_record where express_no = :express_no and is_cancel = 0";
        $r['record'] = $this->db->get_row($sql, array(':express_no' => $express_no));
        if (empty($r['record'])) {
            return array('status' => '-1', 'data' => '', 'message' => '未匹配到任何订单'); //and is_cancel = 0 and is_deliver = 0
        }

        $status = load_model('mid/MidBaseModel')->check_is_mid('scan', 'sell_record', $r['record']['store_code']);
        if ($status !== false) {
            return $this->format_ret(-1, '', '仓库对接' . $status . '，不允许手工发货');
        }

        if ($r['record']['is_deliver'] == 1) {
            return array('status' => '-1', 'data' => '', 'message' => '订单已发货'); //and is_cancel = 0 and is_deliver = 0
        }
        $sql = "select count(*) from oms_sell_return where sell_record_code = :sell_record_code and return_order_status<>3 ";
        $return_check = $this->db->get_value($sql, array(':sell_record_code' => $r['record']['sell_record_code']));
        if ($return_check > 0) {
            return array('status' => '-1', 'data' => '', 'message' => '订单已发生退货'); //and is_cancel = 0 and is_deliver = 0
        }
        $sql = "select shipping_status from oms_sell_record where sell_record_code = :sell_record_code AND order_status <> 3 ";
        $shipping_status = $this->db->get_value($sql, array(':sell_record_code' => $r['record']['sell_record_code']));
        if (empty($shipping_status)) {
            return array('status' => '-1', 'data' => '', 'message' => '订单已作废'); //and is_cancel = 0 and is_deliver = 0
        }
        if ($shipping_status != 3) {
            return array('status' => '-1', 'data' => '', 'message' => '订单未完成配货');
        }

        // 对应波次单是否已验收
        if (!empty($r['record']['waves_record_id'])) {
            $sql = "select * from oms_waves_record where waves_record_id = :waves_record_id";
            $w = $this->db->get_row($sql, array('waves_record_id' => $r['record']['waves_record_id']));
            if (empty($w)) {
                return array('status' => '-1', 'data' => '', 'message' => '对应波次单不存在');
            }
            if ($w['is_accept'] != 1) {
                return array('status' => '-1', 'data' => '', 'message' => '波次单未验收');
            }
        }

        $sql = "select * from oms_deliver_record_detail where deliver_record_id = :deliver_record_id";
        $r['detail_list'] = $this->db->get_all($sql, array('deliver_record_id' => $r['record']['deliver_record_id']));
        if (empty($r['detail_list'])) {
            return array('status' => '-1', 'data' => '', 'message' => '订单明细不存在');
        }
        //var_dump($r['detail_list']);die;
        $r['record']['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $r['record']['express_code']));

        foreach ($r['detail_list'] as $key => &$val) {
            //$tl为1时，表示是通灵扫描发货

            $key_arr = array('goods_code', 'barcode', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $val = array_merge($val, $sku_info);

            $val['lof_no'] = $this->get_lof_no($val['sell_record_code'], $val['sku']);
        }
        $r['lof_status'] = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        // var_dump($r);die;
        return $r;
    }

    public function get_lof_no($sell_record_code, $sku, $record_type = 1) {
        $lof_sql = "select lof_no from oms_sell_record_lof where record_code = :record_code and record_type = :record_type and sku =:sku";
        $lof_arr = $this->db->get_all($lof_sql, array('record_code' => $sell_record_code, 'record_type' => $record_type, 'sku' => $sku));
        $lof_no = array();
        if (!empty($lof_arr)) {
            foreach ($lof_arr as $lof) {
                $lof_no[] = $lof['lof_no'];
            }

            return implode(",", $lof_no);
        } else {
            return '';
        }
    }

    public function edit_express($sellRecordCode, $arr, $type = '') {

        $sql = "select * from oms_sell_record where sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('sell_record_code' => $sellRecordCode));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '订单不存在');
        }
        if ($arr['express_code'] != $record['express_code'] && $type != 'import') {
            $arr['express_no'] = '';
        }


        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        if ($is_more_deliver_package == 1 && $arr['express_code'] != $record['express_code']) {
            $ret = $this->is_more_package_edit($record['sell_record_code'], $record['waves_record_id']);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        //发货包裹表信息
        $package_data = array();
        //判断多包裹判断有没有关联波次单号
        if ($is_more_deliver_package == 1 && $record['waves_record_id'] > 0) {
            //查询发货单信息
            $deliver_record = $this->db->get_row("select waves_record_id,package_no from oms_deliver_record where sell_record_code=:sell_record_code AND is_cancel=:is_cancel", array(':sell_record_code' => $sellRecordCode, ':is_cancel' => 0));
            if (empty($deliver_record['package_no'])) {
                //如果没有包裹号就比较订单
                $package_data = $record;
            } else {
                //查询发货包裹表
                $sql_values = array(':sell_record_code' => $sellRecordCode, ':waves_record_id' => $deliver_record['waves_record_id'], ':package_no' => $deliver_record['package_no']);
                $sql = "select express_code,express_no from oms_deliver_record_package where sell_record_code=:sell_record_code AND waves_record_id=:waves_record_id AND package_no=:package_no";
                $package_data = $this->db->get_row($sql, $sql_values);
                if ($package_data['express_code'] == $arr['express_code'] && $package_data['express_no'] == $arr['express_no']) {
                    return $this->format_ret(2);
                }
            }
        } else {
            if ($record['express_code'] == $arr['express_code'] && $record['express_no'] == $arr['express_no']) {
                return $this->format_ret(2);
            }
        }

        $this->begin_trans();
        try {
            if ($record['waves_record_id'] > 0) {
                $this->save_sell_record_express_no($sellRecordCode, $arr);
            } else {
                // 同时更新订单表
                $sql = "update oms_sell_record set express_code = :express_code, express_no = :express_no
                where sell_record_code = :sell_record_code";
                $this->query($sql, array(
                    'express_code' => $arr['express_code'],
                    'express_no' => $arr['express_no'],
                    'sell_record_code' => $sellRecordCode)
                );
                // 同时更新发货单表
                $sql = "update oms_deliver_record set express_code = :express_code, express_no = :express_no
                where sell_record_code = :sell_record_code";
                $this->query($sql, array(
                    'express_code' => $arr['express_code'],
                    'express_no' => $arr['express_no'],
                    'sell_record_code' => $sellRecordCode)
                );
            }


            //同时更新发货表
            if ($record['shipping_status'] == 4) {
                $company_code = $this->get_express_company($arr['express_code']);

                $sql = "update api_order_send set fail_num=0,express_code = :express_code,company_code = :company_code,express_no = :express_no
                where sell_record_code = :sell_record_code";
                $this->query($sql, array(
                    'express_code' => $arr['express_code'],
                    'express_no' => $arr['express_no'],
                    'company_code' => $company_code,
                    'sell_record_code' => $sellRecordCode)
                );
            }
            //添加日志
            $this->deliver_add_action($record, $package_data, $is_more_deliver_package, $arr);
            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    public function deliver_add_action($record, $package_data, $is_more_deliver_package, $arr) {
        if ($is_more_deliver_package == 1) {
            if ($package_data['express_code'] != $arr['express_code'] && $package_data['express_no'] != $arr['express_no']) {
                $action = '修改快递方式和快递单号';
                $msg = '快递方式由' . $package_data['express_code'] . '修改为' . $arr['express_code'] . '，快递单号由' . $package_data['express_no'] . '修改为' . $arr['express_no'];
            } else {
                if ($package_data['express_code'] != $arr['express_code']) {
                    $action = '修改快递方式';
                    $msg = '快递方式由' . $package_data['express_code'] . '修改为' . $arr['express_code'];
                } elseif ($package_data['express_no'] != $arr['express_no']) {
                    $action = '修改快递单号';
                    $msg = '快递单号由' . $package_data['express_no'] . '修改为' . $arr['express_no'];
                }
            }
        } else {
            if ($record['express_code'] != $arr['express_code'] && $record['express_no'] != $arr['express_no']) {
                $action = '修改快递方式和快递单号';
                $msg = '快递方式由' . $record['express_code'] . '修改为' . $arr['express_code'] . '，快递单号由' . $record['express_no'] . '修改为' . $arr['express_no'];
            } else {
                if ($record['express_code'] != $arr['express_code']) {
                    $action = '修改快递方式';
                    $msg = '快递方式由' . $record['express_code'] . '修改为' . $arr['express_code'];
                } else if ($record['express_no'] != $arr['express_no']) {
                    $action = '修改快递单号';
                    $msg = '快递单号由' . $record['express_no'] . '修改为' . $arr['express_no'];
                }
            }
        }
        if (!empty($action) && !empty($msg)) {
            $ret = load_model('oms/SellRecordActionModel')->add_action($record['sell_record_code'], $action, $msg);
        } else {
            $ret = $this->format_ret(2);
        }
        return $ret;
    }

    public function edit_receiver($id, $arr) {
        $sql = "select * from oms_deliver_record where deliver_record_id = :deliver_record_id";
        $record = $this->db->get_row($sql, array('deliver_record_id' => $id));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }
        $ret = $this->is_more_package_edit($record['sell_record_code'], $record['waves_record_id']);
        if ($ret['status'] < 0) {
            return $ret;
        }


        $customer_address_array['address'] = $arr['receiver_addr'];
        $customer_address_array['country'] = $arr['receiver_country'];
        $customer_address_array['province'] = $arr['receiver_province'];
        $customer_address_array['city'] = $arr['receiver_city'];
        $customer_address_array['district'] = $arr['receiver_district'];
        $customer_address_array['street'] = $arr['receiver_street'];
        $customer_address_array['tel'] = $arr['receiver_mobile'];
        $customer_address_array['home_tel'] = $arr['receiver_phone'];
        $customer_address_array['name'] = $arr['receiver_name'];
        $customer_address_array['customer_code'] = $record['customer_code'];
        $customer_address_array['shop_code'] = $record['shop_code'];
        $buyer_name = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($record['customer_code'], $record['customer_address_id']);
        if ($buyer_name === false) {
            return $this->format_ret(-1, '', '暂时不能修改，安全解密异常！');
        }
        $customer_address_array['buyer_name'] = $buyer_name;
        $ret_create = load_model('crm/CustomerOptModel')->create_customer_address($customer_address_array);
        if ($ret_create['status'] < 1) {
            return $ret_create;
        }
        $arr['customer_address_id'] = $ret_create['data']['customer_address_id'];

        $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($arr['customer_address_id']);
        $arr['receiver_addr'] = $customer_address['address'];
        $arr['receiver_phone'] = $customer_address['home_tel'];
        $arr['receiver_name'] = $customer_address['name'];
        $arr['receiver_mobile'] = $customer_address['tel'];

        $country = oms_tb_val('base_area', 'name', array('id' => $arr['receiver_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $arr['receiver_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $arr['receiver_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $arr['receiver_district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $arr['receiver_street']));
        $arr['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $arr['receiver_addr'];


        $this->begin_trans();
        try {
            $this->db->update('oms_deliver_record', $arr, array('deliver_record_id' => $id));
            $r = $this->db->update('oms_sell_record', $arr, array('sell_record_code' => $record['sell_record_code']));

            //修改云栈信息地址
            $filer = array('deliver_record_id' => $id);
            $ret = $this->fullupdate_tb_wlb_waybil($filer);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * @param $deliverRecordId
     * @param $expressNo
     * @param $check_the_no
     * @return array
     */
    function edit_express_no($deliverRecordId, $expressNo, $check_the_no) {
        require_model('oms/SellRecordOptModel');
        $m = new SellRecordOptModel();

        $record = $this->get_record_by_id($deliverRecordId);
        //$detail = array(); //$this->get_detail_list_by_pid($sellRecordId);
        //$sys_user = $m->sys_user();
        //$check = $m->edit_express_no_check($record, $detail, $sys_user);
        //if($check['status'] != '1'){
        //    return $check;
        //}
        if ($check_the_no) {
            $s = $m->check_express_no($record['express_code'], $expressNo);
            if ($s == false) {
                return $m->return_value(-1, "快递单号不合法: " . $expressNo);
            }
        }
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';

        $this->begin_trans();
        try {
            $is_update = false;
            //开启多包裹，更新多包裹表
            if ($is_more_deliver_package == 1) {
                $package_no = isset($record['package_no']) && !empty($record['package_no']) ? $record['package_no'] : 1;
                if ($package_no == 1) { //包裹号为1，修改订单、发货单表
                    $is_update = true;
                }
                $package_data = $this->get_package_express_no_by_package_no($record['sell_record_code'], $record['waves_record_id'], $package_no);
                if (!empty($package_data)) { // 有多包裹信息，更新包裹表
                    $sql = "update oms_deliver_record_package set express_no = :express_no where sell_record_code = :code AND package_no = :package_no AND waves_record_id = :waves_record_id";
                    $ret = $this->query($sql, array(':express_no' => $expressNo, ':code' => $record['sell_record_code'], ':package_no' => $package_no, ':waves_record_id' => $record['waves_record_id']));
                } else { //没有信息新增包裹表信息
                    $params = array(
                        'sell_record_code' => $record['sell_record_code'],
                        'express_code' => $record['express_code'],
                        'package_no' => $package_no,
                        'waves_record_id' => $record['waves_record_id'],
                        'express_no' => $expressNo
                    );
                    $this->save_package_data($params);
                }
            } else { //没开多包裹更新订单、发货单表
                $is_update = true;
            }
            if ($is_update == true) {
                //同时更新订单表
                $sql = "update oms_sell_record set express_no = :express_no where sell_record_code = :code";
                $this->query($sql, array(':express_no' => $expressNo, ':code' => $record['sell_record_code']));

                $this->update(array('express_no' => $expressNo), array('deliver_record_id' => $deliverRecordId));
                //更新api_order_send表
                $sql = "update api_order_send set express_no = :express_no where sell_record_code = :code";
                $this->query($sql, array(':express_no' => $expressNo, ':code' => $record['sell_record_code']));
            }

            $this->commit();
            load_model('oms/SellRecordModel')->add_action($record['sell_record_code'], '自动匹配物流单号', $record['express_no'] . '修改为' . $expressNo);
            return array('status' => '1', 'data' => '', 'message' => '生成成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    function edit_express_code($deliverRecordId, $expressCode) {
        $record = $this->get_record_by_id($deliverRecordId);
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }
        if ($record['express_code'] == $expressCode) {
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        }

        $this->begin_trans();
        try {
            //存在快递数据，判断是否为云栈，若为云栈，用云栈取消面单号方法，若不为云栈，直接处理包裹单数据
            if (isset($record['express_data']) && !empty($record['express_data'] && !empty($record['express_no']))) {
                $d = json_decode($record['express_data'], true);
                if (!empty($d) && !isset($d['print_config']) && !isset($d['object_id'])) {//不是云栈
                    $this->handle_deliver_package_data($record);
                }
            }
            //不存在快递数据，直接处理包裹单数据
            if (!empty($record['express_no']) && empty($record['express_data'])) {
                $this->handle_deliver_package_data($record);
            }
            if ($record['is_deliver'] == 0) {
                load_model('oms/DeliverLogisticsModel')->cancel_waybill($record);
            }

            $sql = "update oms_deliver_record set express_code = :express_code, express_no = '',express_data='',is_print_express = 0
            where deliver_record_id = :id";
            $this->query($sql, array('express_code' => $expressCode, 'id' => $deliverRecordId));

            // 同时更新订单表
            $sql = "update oms_sell_record set express_code = :express_code, express_no = '', is_print_express = 0
            where sell_record_code = :code";
            $this->query($sql, array('express_code' => $expressCode, 'code' => $record['sell_record_code']));
            //更新api_order_send表
            $company_code = $this->get_express_company($expressCode);
            $sql = "update api_order_send set express_code = :express_code,company_code = :company_code,express_no = '' where sell_record_code = :code";
            $this->query($sql, array('express_code' => $expressCode, ':company_code' => $company_code, 'code' => $record['sell_record_code']));

            $this->commit();
            load_model('oms/SellRecordModel')->add_action($record['sell_record_code'], '修改配送方式', $record['express_code'] . '修改为' . $expressCode);
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * 处理包裹单表数据
     */
    function handle_deliver_package_data($deliver_data) {
        $package_sql = 'SELECT express_no, express_data FROM oms_deliver_record_package WHERE sell_record_code=:sell_record_code AND package_no = :package_no';
        $package_sql_value = array(":sell_record_code" => $deliver_data['sell_record_code'], ":package_no" => $deliver_data['package_no']);
        $package_data = $this->db->get_all($package_sql, $package_sql_value);
        $update_flag = 0;
        if (!empty($package_data)) {
            foreach ($package_data as $data) {
                if (!empty($data['express_no']) || !empty($data['express_data'])) {
                    $update_flag = 1;
                }
            }
        }
        if ($update_flag === 1) {
            $this->db->query("update oms_deliver_record_package set express_no='',express_data='' where sell_record_code='{$deliver_data['sell_record_code']}' AND package_no='{$deliver_data['package_no']}'");
        }
    }

    function cancel_by_code($w, $c) {
        $sql = "select * from oms_deliver_record
        where waves_record_id = :waves_record_id and sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('waves_record_id' => $w, 'sell_record_code' => $c));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '订单不存在');
        }

        return $this->_cancel($record);
    }

    /**
     * Set cancel status
     * @param $deliverRecordId
     * @return array
     */
    function cancel($deliverRecordId, $remark = '') {
        $sql = "select * from oms_deliver_record where deliver_record_id = :id";
        $record = $this->db->get_row($sql, array('id' => $deliverRecordId));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '订单不存在');
        }
        if (!empty($remark)) {
            $record['remark'] = $remark;
        } else {
            $record['remark'] = "";
        }
        return $this->_cancel($record);
    }

    function _cancel($record) {
        if ($record['is_cancel'] == '1') {
            return array('status' => '-1', 'data' => '', 'message' => '发货单已取消');
        }
        if (empty($record['waves_record_id'])) {
            return array('status' => '-1', 'data' => '', 'message' => '订单未关联波次单');
        }

        $sql = "select * from oms_waves_record where waves_record_id = :id";
        $waves = $this->db->get_row($sql, array('id' => $record['waves_record_id']));
        if (empty($waves)) {
            return array('status' => '-1', 'data' => '', 'message' => '波次单不存在');
        }
        if ($waves['is_accept'] == 1) {
            return array('status' => '-1', 'data' => '', 'message' => '波次单已验收');
        }

        //波次单取消 调取订单拦截 并进行设问
        $ret = load_model('oms/SellRecordOptModel')->deliver_cancel_and_set_problem($record['sell_record_code']);
        $log = parent::update_exp('oms_sell_record_action', array('action_note' => $record['remark']), array('sell_record_code' => $record['sell_record_code'], 'action_name' => '波次单取消'));


        return $ret;
    }

    /**
     * Out of stock.
     * @param $deliverRecordId
     * @return array
     */
    function outofstock($deliverRecordId) {
        $sql = "select * from oms_deliver_record where deliver_record_id = :id";
        $record = $this->db->get_row($sql, array('id' => $deliverRecordId));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }
        if (empty($record['waves_record_id'])) {
            return array('status' => '-1', 'data' => '', 'message' => '订单未关联波次单');
        }

        $sql = "select * from oms_waves_record where waves_record_id = :id";
        $waves = $this->db->get_row($sql, array('id' => $record['waves_record_id']));
        if (empty($waves)) {
            return array('status' => '-1', 'data' => '', 'message' => '波次单不存在');
        }
        if ($waves['is_accept'] == 1) {
            return array('status' => '-1', 'data' => '', 'message' => '波次单已验收');
        }

        $this->begin_trans();
        try {
            $sql = "update oms_deliver_record set is_stock_out = 1 where deliver_record_id = :id";
            $this->query($sql, array('id' => $deliverRecordId));

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * check.
     * @param $deliverRecordId
     * @return array
     */
    function check($deliverRecordId, $is_record = 1, $is_tl = 0) {
        $sql = "select sell_record_code from oms_deliver_record_detail where deliver_record_id = :deliver_record_id";
        $sell_record_code = $this->db->getOne($sql, array('deliver_record_id' => $deliverRecordId));
        if (empty($sell_record_code)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单明细不存在');
        }
        $sell_obj = load_model('oms/SellRecordOptModel');
        $record = $sell_obj->get_record_by_code($sell_record_code);
        $detail = $sell_obj->get_detail_list_by_code($sell_record_code);
        $sys_user = $sell_obj->sys_user();
        $ret = $sell_obj->sell_record_send($record, $detail, $sys_user, 'scan', 1, $is_record);
        if ($is_tl == 1) {
            $unique_data = array('record_code' => $sell_record_code, 'record_type' => 'sell_record', 'action_name' => 'sell_out');
            load_model('prm/GoodsUniqueCodeLogModel')->unique_code_log($unique_data);
        }
        if ($ret['status'] >= 1) {
            $ret['message'] = "发货成功";
        }
        return $ret;
    }

    /**
     * @param $deliverRecordId
     * @param $realWeight
     * @param $realFee
     * @return array
     */
    function weigh($deliverRecordId, $realWeight, $realFee) {
        if (empty($realWeight)) {
            return array('status' => '-1', 'data' => '', 'message' => '重量不正确');
        }

        $sql = "select * from oms_deliver_record where deliver_record_id = :id";
        $record = $this->db->get_row($sql, array('id' => $deliverRecordId));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }
        /* if ($record['is_deliver'] != '1') {
          return array('status' => '-1', 'data' => '', 'message' => '发货单未发货');
          } */

        $sql = "select * from oms_sell_record where record_code = :record_code";
        $sellRecord = $this->db->get_row($sql, array('record_code' => $record['record_code']));
        if (empty($sellRecord)) {
            return array('status' => '-1', 'data' => '', 'message' => '订单不存在');
        }

        $this->begin_trans();
        try {
            $data = array(
                'real_weigh' => $realWeight,
                'weigh_express_money' => $realFee,
                'is_weigh' => '1',
                'weigh_time' => time(),
            );
            $r = $this->db->update('oms_deliver_record', $data, array('deliver_record_id' => $deliverRecordId));
            if (!$r) {
                throw new Exception('更新订单出错');
            }

            $r = $this->db->update('oms_sell_record', $data, array('sell_record_id' => $sellRecord['sell_record_id']));
            if (!$r) {
                throw new Exception('更新订单出错');
            }

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * 扫描商品明细
     * @param $deliverRecordId
     * @param $deliverRecordDetailId
     * @param $barcode
     * @return array
     */
    function scan_detail($deliverRecordId, $deliverRecordDetailId, $barcode, $is_unique = 0) {
        $sql = "select * from oms_deliver_record where deliver_record_id = :id";
        $record = $this->db->get_row($sql, array('id' => $deliverRecordId));
        if (empty($record)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }

        $sql = "select * from oms_deliver_record_detail where deliver_record_detail_id = :deliver_record_detail_id";
        $detail = $this->db->get_row($sql, array('deliver_record_detail_id' => $deliverRecordDetailId));
        if (empty($detail)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单明细不存在');
        }
        if ($detail['scan_num'] + 1 > $detail['num']) {
            return array('status' => '-1', 'data' => '', 'message' => '超出商品数量');
        }

        require_model('oms/SellRecordOptModel');
        $mdlSell = new SellRecordOptModel();

        $this->begin_trans();
        try {
            $mdlSell->add_action($record['sell_record_code'], '扫描出库', '扫描条码:' . $barcode);

            $data = array(
                'scan_num' => $detail['scan_num'] + 1, // 每次扫描一件
            );
            $r = $this->db->update('oms_deliver_record_detail', $data, array('deliver_record_detail_id' => $deliverRecordDetailId));
            if (!$r) {
                throw new Exception('更新发货单明细出错');
            }

            if ($is_unique == 1) {//添加唯一吗记录
                $unique_data = array();
                $sql = "select unique_code from goods_unique_code_tl where unique_code = :unique_code";
                $ret = $this->db->getRow($sql, array(':unique_code' => $barcode));
                if (empty($ret)) {
                    $unique_data['unique_code'] = '';
                }
                $unique_data['sell_record_code'] = $record['sell_record_code'];
                $unique_data['unique_code'] = $barcode;
                $unique_data['barcode_type'] = 'unique_code';
                load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($unique_data);
            }

            $this->commit();
            $sql = "select sum(num) as all_num,sum(scan_num) as scan_num  from oms_deliver_record_detail where deliver_record_id=:deliver_record_id ";  
            $scan_data  = $this->db->get_row($sql,array(':deliver_record_id'=>$deliverRecordId));
            
            return array('status' => '1', 'data' => $scan_data, 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    /**
     * Refreshing value of oms_waves_record.
     * @param $wavesRecordId
     * @return array
     * @throws Exception
     */
    function RefreshWavesRecord($wavesRecordId) {
        $sql = "select * from oms_deliver_record where waves_record_id = :id and is_cancel = 0";
        $recordList = $this->db->get_all($sql, array('id' => $wavesRecordId));
        if (empty($recordList)) {
            return array('status' => '1', 'message' => '无有效发货单');
        }

        $ids = '';
        $idsArr = array();
        foreach ($recordList as $v) {
            $idsArr[] = $v['deliver_record_id'];
        }
        $ids = implode(',', $idsArr);

        $sql = "select * from oms_deliver_record_detail where deliver_record_id IN ($ids)";
        $detailList = $this->db->get_all($sql);
        if (empty($detailList)) {
            return array('status' => '-1', 'message' => '订单明细不存在');
        }

        $sql = "select sum(a.num) as num from oms_deliver_record_detail a
        inner join oms_deliver_record b on b.deliver_record_id = a.deliver_record_id
        where b.waves_record_id = :id AND b.is_cancel = 1";
        $cancelled_goods_count = $this->db->get_value($sql, array('id' => $wavesRecordId));

        $total_amount = 0;
        foreach ($recordList as $key => $value) {
            $total_amount += $value['paid_money'];
        }

        $goods_count = 0;
        foreach ($detailList as $key => $value) {
            $goods_count += $value['num'];
        }

        $this->begin_trans();
        try {
            //更新波次单
            $d = array(
                'sell_record_count' => count($recordList),
                'goods_count' => $goods_count,
                'cancelled_goods_count' => (int) $cancelled_goods_count,
                'valide_goods_count' => $goods_count,
                'total_amount' => $total_amount,
            );
            $r = $this->db->update('oms_waves_record', $d, array('waves_record_id' => $wavesRecordId));
            if (!$r) {
                throw new Exception('保存波次单失败');
            }

            $this->commit();
            return array('status' => '1', 'data' => '', 'message' => '刷新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'data' => '', 'message' => $e->getMessage());
        }
    }

    function update_print_status($id, $status) {
        $print_status = array(1 => "is_print_express", 2 => "is_print_sellrecord");
        $qll_status = array(1 => "X_LOGISTICS_PRINTED", 2 => "X_SEND_PRINTED");
        $record = $this->db->get_row("select * from oms_deliver_record where deliver_record_id=:deliver_record_id", array(":deliver_record_id" => $id));
        if (!empty($record)) {
            $this->update(array($print_status[$status] => 1), array("deliver_record_id" => $id));
            load_model('oms/SellRecordModel')->update(array($print_status[$status] => 1), array("sell_record_code" => $record['sell_record_code']));
            load_model('oms/SellRecordActionModel')->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code'], $qll_status[$status]);
        }
    }

    function scan_clear($id) {
        $sql = "SELECT sell_record_code FROM oms_deliver_record_detail WHERE deliver_record_id = '{$id}' ";
        $record_code = $this->db->get_row($sql);
        if (empty($record_code['sell_record_code'])) {
            return array('status' => '-1', 'data' => '', 'message' => '');
        }
        $sql = "DELETE FROM unique_code_scan_temporary_log WHERE barcode_type = 'unique_code' AND sell_record_code = '{$record_code['sell_record_code']}' ";
        $this->query($sql);
        $sql = "update oms_deliver_record_detail set scan_num = 0 where deliver_record_id = {$id}";

        return $this->query($sql);
    }

    function tb_wlb_waybill_get_detail($deliver_record_id) {
        $sql = "select d.sku,d.num from oms_deliver_record_detail d

            WHERE d.deliver_record_id=:deliver_record_id";
        $data = $this->db->get_all($sql, array('deliver_record_id' => $deliver_record_id));
//        $sql = "select g.goods_name,s1.spec1_name,s2.spec2_name,d.num from oms_deliver_record_detail d
//                    INNER JOIN base_goods g ON d.goods_code=g.goods_code
//                    INNER JOIN base_spec1 s1 ON s1.spec1_code=d.spec1_code
//                    INNER JOIN base_spec2 s2 ON s2.spec2_code=d.spec2_code

        $_goods_data = array();
        if (!empty($data)) {
            foreach ($data as $val) {
                $key_arr = array('spec1_name', 'spec2_name', 'goods_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
                $goods_name = $sku_info['goods_name'] . "[{$sku_info['spec1_name']},{$sku_info['spec2_name']}]";
                $_goods_data[] = array('goods_name' => $goods_name, 'num' => $val['num']);
            }
        }

        return $_goods_data;
    }

    /**
     * 获取京东面单号
     * @param $waves_record_id
     * @param array $idList 拣货单明细主键 pick_up_record_record_id
     * @return array
     */
    function jd_etms_waybillcode_get($waves_record_id, $idList, $guarantee_money = '') {
        $idStr = implode(',', $idList);
        //读取拣货单, 根据拣货单编号
        $sql = "select * from oms_waves_record
        where is_cancel = 0 and waves_record_id = :waves_record_id";
        $pickup = $this->db->get_row($sql, array('waves_record_id' => $waves_record_id));
        if (empty($pickup)) {
            return return_value(-1, '拣货单不存在');
        }

        //读取订单
        $sql = "select a.* from oms_deliver_record a
        where a.is_cancel = 0 and a.waves_record_id = :waves_record_id and a.deliver_record_id IN ($idStr)
        and a.express_no = '' ";
        //and a.express_code IN ($supportExpressStr)";
        $sellRecordAll = $this->db->get_all($sql, array('waves_record_id' => $pickup['waves_record_id']));
        if (empty($sellRecordAll)) {
            return array('status' => -1, 'message' => '请选择支持京东面单的未匹配订单');
        }

        //按商店排序
        $sortList = array();
        $record_arr = array();
        $data = '';
        if (!empty($guarantee_money)) {
            $data = array(
                'guaranteeValue' => 1,
                'guaranteeValueAmount' => sprintf("%.2f", $guarantee_money)
            );
            $data = json_encode($data);
        }
        foreach ($sellRecordAll as $sellRecord) {
            //$sortList[$sellRecord['shop_code']][] = $sellRecord;
            $company_code = $this->db->getOne("select company_code from  base_express where express_code='{$sellRecord['express_code']}' ");
            if (strtolower($company_code) != 'jd') {
                return array('status' => -1, 'message' => '请选择配送方式为京东快递的订单');
            }

            $sql = "select rm_shop_code from base_express where express_code = :express_code";
            $rm_shop_code = $this->db->get_value($sql, array(':express_code' => $sellRecord['express_code']));
            if (!empty($rm_shop_code) && $rm_shop_code != $sellRecord['shop_code']) {
                //unset($sortList[$sellRecord['shop_code']]);
                $sortList[$rm_shop_code][] = $sellRecord;
            } else {
                $sortList[$sellRecord['shop_code']][] = $sellRecord;
            }

            if ($sellRecord['pay_type'] == '1') {
                //TODO: 货到付款判断是否可达
            }
        }

        $conf = require_conf('api_url');
        $url = $conf['api'] . 'api/order/order_print_num_download';
        require_lib('apiclient/Validation');
        $customercode_arr = array();
        //按商店获取面单号
        foreach ($sortList as $shop_code => $sellRecordList) {
            if (!isset($customercode_arr[$shop_code])) {
                $shop_info = $this->db->get_row("select shop_id,api from base_shop b INNER JOIN  base_shop_api a ON b.shop_code=a.shop_code where  b.shop_code='{$shop_code}'");
                $extra_params = $shop_info['api'];
                if (empty($extra_params)) {
                    return array('status' => -1, 'message' => '店铺参数异常');
                }
                $extra_params = strtolower($extra_params);
                $params_arr = json_decode($extra_params, true);
                if (!isset($params_arr['customercode']) || empty($params_arr['customercode'])) {
                    return array('status' => -1, 'message' => '店铺参数异常customercode不存在');
                }
                $customercode_arr[$shop_code] = $params_arr['customercode'];
            }
            $error_sell_code = array();
            $express = array();
            foreach ($sellRecordList as $key => $sellRecord) {
                $express_company_code = $this->get_express_company($sellRecord['express_code']);
                $pay_type = strtoupper($sellRecord['pay_type']);
                if ($express_company_code == 'JD' && $pay_type != 'COD') {
                    $params_api = array();
                    $params_api['shop_code'] = $shop_code;
                    $params_api['sell_record_code'] = $sellRecord['sell_record_code'];
                    $ret_check_api = load_model('sys/EfastApiModel')->request_api("jingdong_api/jingdong_etms_range_check", $params_api);
                    $etms_api_info = $ret_check_api['resp_data']['jingdong_etms_range_check_responce']['resultInfo'];
                    if ($etms_api_info['rcode'] != 100) {
                        if (strpos($ret_check_api['resp_data']['msg'], '进入人工预分拣') !== FALSE) {
                            $error_sell_code[] = $sellRecord['sell_record_code'];
                            unset($sellRecordList[$key]);
                        } else {
                            return return_value(-1, $sellRecord['sell_record_code'] . $ret_check_api['resp_data']['msg']);
                        }
                    }

                    $express[$sellRecord['sell_record_code']]['express_data'] = json_encode($etms_api_info);
                }
                //$this->save_sell_record_express_no($sellRecord['sell_record_code'],$express);     //$express_data
            }
            $listCount = count($sellRecordList);
            $size = $listCount > 100 ? 100 : $listCount; //京东接口一次最多获取100个单号
            $index = 0;

            do {
                $list = array_slice($sellRecordList, $index, $size);

                $params = array();
                $apiName = 'jingdong_api/jingdong_etms_waybillcode_get';
                $params['pre_num'] = $size;
                $params['shop_code'] = $shop_code; //店铺code

                $ret_api = load_model('sys/EfastApiModel')->request_api($apiName, $params);
                $ret_info = &$ret_api['resp_data']['jingdong_etms_waybillcode_get_responce']['resultInfo'];
                if (empty($ret_api['resp_data']['jingdong_etms_waybillcode_get_responce']) || !isset($ret_api['resp_data']['jingdong_etms_waybillcode_get_responce'])) {
                    $msg = $ret_api['resp_data']['msg'];
                } else {
                    $msg = $ret_info['msg'];
                    $msg .= $ret_api['resp_data']['msg'];
                }
                if ($ret_info['code'] != 100) {
                    if ($ret_info['code'] == 200) {
                        return return_value(-1, $sellRecordList['sell_record_code'] . '订单京东快递不到，请发其他快递');
                    }
                    return return_value(-1, '店铺' . $shop_code . '的京东面单号获取失败:' . $msg);
                }

                //更新订单面单号
                //TODO: 避免浪费面单号
                $i = 0;
                foreach ($list as $sellRecord) {
                    $this->begin_trans();
                    try {
                        $params_api = array();
                        $params_api['shop_code'] = $shop_code;
                        $params_api['sell_record_code'] = $sellRecord['sell_record_code'];
                        $express_info = array();
                        $express_info['express_no'] = $ret_info['deliveryIdList'][$i];
                        $express_info['express_data'] = $express[$sellRecord['sell_record_code']]['express_data'];

//                        if($sellRecord['express_code'] == 'JD'){
//                            $ret_check_api = load_model('sys/EfastApiModel')->request_api("jingdong_api/jingdong_etms_range_check", $params_api);
//                            if ($ret_check_api['resp_data']['code'] != 100) {
//                                return return_value(-1, $sellRecord['sell_record_code']  . $ret_check_api['resp_data']['msg']);
//                            }
//                            $etms_api_info = $ret_check_api['resp_data']['jingdong_etms_range_check_responce']['resultInfo'];
//                            $express['express_data'] = json_encode($etms_api_info);
//                            $express['express_code'] = $sellRecord['express_code'];
//                        }
                        $this->save_sell_record_express_no($sellRecord['sell_record_code'], $express_info, 'jdrm');     //$express_data
                        //设置了保价，更新保价信息
                        $ret = $this->update_exp('oms_sell_record', array('express_data' => $data), array('sell_record_code' => $sellRecord['sell_record_code']));
                        if ($ret['status'] != 1) {
                            return $this->format_ret(-1, '', '更新保价信息失败');
                        }
                        $this->commit();
                    } catch (Exception $e) {
                        $this->rollback();
                    }
                    $i++;
                }
                $index = $index + $size;
            } while ($index < $listCount);
        }
        if (!empty($error_sell_code)) {
            //$msg = '订单号：'.implode('<br>', $error_sell_code).'进入人工预分拣,请15分钟后重新获取！';

            $msg = '订单号：';
            $splite_codes = array_chunk($error_sell_code, 5);
            foreach ($splite_codes as $splite_code) {
                $msg .= implode(',', $splite_code) . '<br>';
            }
            $msg = rtrim($msg, '<br>') . '进入人工预分拣,请15分钟后重新获取！';
            return return_value(-1, $msg);
        }
        return return_value(1, '获取成功');
    }

    //提交打印接口
    function print_tb_wlb_waybil($data, $express_code) {

        $ret_shipping = load_model('base/ShippingModel')->get_row(array('express_code' => $express_code));




        if (empty($ret_shipping['data']['rm_shop_code'])) {
            return $this->format_ret(-1, '', '找不到配送方式绑定的店铺');
        }

        //判断是否云栈模版，不是走默认
//         $sql = "select is_buildin FROM sys_print_templates  where print_templates_id=:print_templates_id";
//         $is_buildin =  $this->db->get_value($sql,array(':print_templates_id'=>$ret_shipping['data']['rm_id']));
//
//         if($is_buildin!=2){
//              return $this->format_ret(1);
//         }

        $shop_code = &$ret_shipping['data']['rm_shop_code'];
        $client = new TaobaoClient($shop_code);
        $param = array(
            'cp_code' => $ret_shipping['data']['company_code'],
            'print_check_info_cols' => $data
        );
        $ret = $client->taobaoWlbWaybillIPrint($param);
        if (!empty($ret['error_response'])) {
            return $this->format_ret(-1, '', $ret['error_response']['sub_msg']);
        }
        return $this->format_ret(1);
    }

    //取消物流单号
    function cancle_tb_wlb_waybil($filer) {

        //$record_code,$deliver_record_id,$waves_record_id

        $sql = "select deliver_record_id, sell_record_code,is_deliver,express_code,sale_channel_code,express_no,express_data,deal_code_list from oms_deliver_record where 1 ";
        $sql_values = array();
        if (!empty($filer['sell_record_code'])) {
            $sql .= " AND sell_record_code=:sell_record_code ";
            $sql_values[':sell_record_code'] = $filer['sell_record_code'];
        }
//            if(!empty($filer['deliver_record_id'])){
//                $sql.=" AND deliver_record_id=:deliver_record_id ";
//                 $sql_values[':deliver_record_id'] = $filer['deliver_record_id'];
//            }
        if (!empty($filer['waves_record_id'])) {
            $sql .= " AND waves_record_id=:waves_record_id ";
            $sql_values[':waves_record_id'] = $filer['waves_record_id'];
        }
        $sql .= "  order by deliver_record_id desc ";
        $row = $this->db->get_row($sql, $sql_values);

        $ret = $this->cancle_tb_wlb_waybil_action($row);



        return $ret;
    }

    /**
     * 订单未生成波次时取消云栈
     * @param $record
     */
    public function cancle_cainiao_wlb_waybil_by_sell_record($record) {
        return $this->cancle_tb_wlb_waybil_action($record);
    }

    private function cancle_tb_wlb_waybil_action($row) {
        if (isset($row['express_data']) && !empty($row['express_data']) && !empty($row['express_no'])) {
            $d = json_decode($row['express_data'], true);
            //print_config为云栈二期，object_id为菜鸟云打印打印数据
            if (!empty($d) && (isset($d['print_config']) || isset($d['object_id']))) {//云栈
                //如果是菜鸟智选物流则使用系统配置的店铺，
                if(isset($d['get_type']) && strtolower($d['get_type']) == 'cainiao_intelligent_delivery'){
                    //获取智能店铺
                    $param_code = array('cainiao_intelligent_shop');
                    $sys_params = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
                    if(!empty($sys_params['cainiao_intelligent_shop'])){
                        $shop_code = $sys_params['cainiao_intelligent_shop'];
                    }else{
                        return $this->format_ret(1, '', '智能店铺未配置');
                    }
                }else{
                    $ret_shipping = load_model('base/ShippingModel')->get_row(array('express_code' => $row['express_code']));
                    if (empty($ret_shipping['data']['rm_shop_code'])) {
                        return $this->format_ret(1, '', '找不到配送方式绑定的店铺');
                    }

                    $shop_code = &$ret_shipping['data']['rm_shop_code'];
                }

                $param = array(
                    'real_user_id' => $d['trade_order_info']['real_user_id'],
                    'trade_order_list' => $row['sell_record_code'],
                    'waybill_code' => $row['express_no'],
                );
                if (isset($row['deal_code_list']) && $row['sale_channel_code'] == 'taobao') {
                    $deal_code_list_arr = explode(",", $row['deal_code_list']);
                    foreach ($deal_code_list_arr as &$deal_code) {
                        $deal_code = (string) $deal_code;
                    }
                    $param['trade_order_list'] = $deal_code_list_arr;
                }

                $param['cp_code'] = $this->get_express_company($row['express_code']);

                if (isset($d['trade_order_info']['package_id'])) {
                    $param['package_id'] = $d['trade_order_info']['package_id'];
                }
                $param['waybill_type'] = isset($d['object_id']) && !empty($d['object_id']) ? 'cainiao_cloud' : '';

                if (isset($row['deliver_record_id'])) {
                    $this->db->query("update oms_deliver_record set express_no='',express_data='' where deliver_record_id='{$row['deliver_record_id']}' ");
                }
                $this->db->query("update oms_sell_record set express_no='' where sell_record_code='{$row['sell_record_code']}'");
                if (isset($row['package_no'])) {
                    $this->handle_deliver_package_data($row);
                }

                //接口取消快递
                $this->taobao_wlbwaybillicancle($shop_code, $row['sell_record_code'], $param);
            }
        }
        
        return $this->format_ret(1);
    }

    public function taobao_wlbwaybillicancle_after($param) {

        $this->taobao_wlbwaybillicancle($param['shop_code'], $param['sell_record_code'], $param['param']);
    }

    function taobao_wlbwaybillicancle($shop_code, $sell_record_code, $param) {

        //事务内存储不处理
        if ($this->is_transaction() === true) {
            $after_param = array(
                'shop_code' => $shop_code,
                'sell_record_code' => $sell_record_code,
                'param' => $param,
            );

            $this->add_transaction_after('oms/DeliverRecordModel', 'taobao_wlbwaybillicancle_after', $after_param);
            return $this->format_ret(1);
        }



        $client = new TaobaoClient($shop_code);
        if (isset($param['waybill_type']) && $param['waybill_type'] == 'cainiao_cloud') {
            $ret = $client->cloudWlbWaybillCancel($param['cp_code'], $param['waybill_code']);
        } else {
            $ret = $client->taobaoWlbWaybillICancel($param);
        }
        if (isset($ret['cancel_result']) && $ret['cancel_result'] === true) {
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '取消云栈物流单号');
        } else {
            $msg = '';
            if (!empty($ret['error_response'])) {
                $msg = $ret['error_response']['sub_msg'];
            }
            load_model('oms/SellRecordModel')->add_action($sell_record_code, '取消云栈物流失败:' . $msg);
            return $this->format_ret(1, '取消失败');
        }
        return $this->format_ret(1);
    }
    //修改单据信息
    function fullupdate_tb_wlb_waybil($filer) {
        $sql = "select * from oms_deliver_record where 1 ";
        $sql_values = array();
        if (!empty($filer['record_code'])) {
            $sql .= " AND sell_record_code=:sell_record_code ";
            $sql_values[':sell_record_code'] = $filer['record_code'];
        }
        if (!empty($filer['deliver_record_id'])) {
            $sql .= " AND deliver_record_id=:deliver_record_id ";
            $sql_values[':deliver_record_id'] = $filer['deliver_record_id'];
        }
        if (!empty($filer['waves_record_id'])) {
            $sql .= " AND waves_record_id=:waves_record_id ";
            $sql_values[':waves_record_id'] = $filer['waves_record_id'];
        }
        $sql .= "  order by deliver_record_id desc ";

        $row = $this->db->get_row($sql, $sql_values);

        if (isset($row['express_data']) && !empty($row['express_data'])) {
            $express_data = json_decode($row['express_data'], true);
            if (!empty($express_data) && isset($express_data['print_config'])) {//云栈
                $ret_shipping = load_model('base/ShippingModel')->get_row(array('express_code' => $row['express_code']));
                if (empty($ret_shipping['data']['rm_shop_code'])) {
                    return $this->format_ret(1, '', '找不到配送方式绑定的店铺');
                }

                //判断是否云栈模版，不是走默认
//                    $sql = "select is_buildin FROM sys_print_templates  where print_templates_id=:print_templates_id";
//                    $is_buildin =  $this->db->get_value($sql,array(':print_templates_id'=>$ret_shipping['data']['rm_id']));
//
//                    if($is_buildin!=2){
//                         return $this->format_ret(1);
//                    }
//


                $shop_code = &$ret_shipping['data']['rm_shop_code'];
                $client = new TaobaoClient($shop_code);

                $param_data = array();
                $param_data['consignee_name'] = $row['receiver_name'];
                $param_data['trade_order_list'] = $row['sell_record_code'];
                $param_data['cp_code'] = $row['express_code'];
                $param_data['waybill_code'] = $row['express_no'];
                $param_data['product_type'] = 'STANDARD_EXPRESS'; //后续需要改造，调用接口，模版选择


                $param_data['order_channels_type'] = $this->get_cainiao_sale_channel($row['sale_channel_code'], $row['shop_code']);

                $param_data['real_user_id'] = $client->get_api_user_id();


                $goods_list = $this->tb_wlb_waybill_get_detail($row['deliver_record_id']);
                foreach ($goods_list as $val) {
                    $param_data['package_items'][] = array('item_name' => $val['goods_name'], 'count' => $val['num']);
                }
                //


                $row['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $row['receiver_province']));

                $row['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $row['receiver_city']));

                if (!empty($row['receiver_district'])) {
                    $row['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $row['receiver_district']));
                } else {
                    $row['receiver_district'] = '';
                }

                if (!empty($row['receiver_street'])) {
                    $row['receiver_street'] = oms_tb_val('base_area', 'name', array('id' => $row['receiver_street']));
                } else {
                    $row['receiver_street'] = '';
                }


                $param_data['consignee_address'] = array('province' => $row['receiver_province'], 'city' => $row['receiver_city'], 'area' => $row['receiver_district'], 'town' => $row['receiver_street'], 'address_detail' => $row['receiver_addr']);

                $param_data['consignee_phone'] = !empty($row['receiver_mobile']) ? $row['receiver_mobile'] : $row['receiver_phone'];
                //$param_data['package_id'] = "";// $row['sell_record_code'];

                $ret = $client->taobaoWlbWaybillIFullupdate($param_data);
                //  var_dump($ret);die;
                if (isset($ret['waybill_code'])) {
                    $express_data = array_merge($express_data, $ret);
                    $express_data_str = json_encode($express_data);
                    $ret = $this->update(array('express_data' => $express_data_str), "deliver_record_id='{$row['deliver_record_id']}'");
                    return $ret;
                } else {
                    if (!empty($ret['error_response'])) {
                        return $this->format_ret(-1, '', $ret['error_response']['sub_msg']);
                    } else {
                        return $this->format_ret(-1, '', '接口异常');
                    }
                }
            }
        }

        return $this->format_ret(1);
    }

    function get_cainiao_sale_channel($sale_channel_code, $shop_code) {
        static $sale_channel_shop = null;
        //tb_shop_type
        if (!isset($sale_channel_shop[$shop_code])) {
            $cainiao_sale_channel = require_conf('cainiao/cainiao_sale_channel');
            if ($sale_channel_code == 'taobao') {
                $tb_shop_type = $this->db->get_value("select tb_shop_type from base_shop_api where shop_code =:shop_code", array(':shop_code' => $shop_code));
                if ($tb_shop_type == 'C') {//天猫店铺
                    $sale_channel_code = 'tmall';
                }
            }
            $sale_channel_shop[$shop_code] = isset($cainiao_sale_channel[$sale_channel_code]) ? $cainiao_sale_channel[$sale_channel_code] : 'OTHERS';
        }
        return $sale_channel_shop[$shop_code];
    }

    function tb_wlb_waybill_get_all($waves_record_id, $print_type = NULL) {
        $wave_data = load_model('oms/WavesRecordModel')->get_record_by_id($waves_record_id);

        $ret_list = $this->get_deliver_id_list_by_waves_code($wave_data['record_code']);

        $list_all = array_chunk($ret_list['data'], 100);
        $status = 1;
        $msg = '';
        foreach ($list_all as $id_list) {
            if ($print_type == '2') {
                $ret_wlb = $this->cn_wlb_waybill_get($waves_record_id, $id_list);
            } else {
                $ret_wlb = $this->tb_wlb_waybill_get($waves_record_id, $id_list);
            }
            if ($ret_wlb['status'] < 0) {
                $status = -1;
                $msg .= $ret_wlb['message'];
            }
        }
        return $this->format_ret($status, '', $msg);
    }

    /**
     * 列表批量获取云栈物流
     * @param $waves_record_id_arr
     * @param int $print_type
     * @return array
     */
    function tb_wlb_waybill_get_multi($waves_record_id_arr, $print_type = 2) {
        $error_msg = array();
        $waves_cancel = 0; //波次单整单作废
        foreach ($waves_record_id_arr as $waves_record_id) {
            $err_info = $this->tb_wlb_waybill_get_action($waves_record_id, $print_type);
            if ($err_info['status'] == '-1') {
                $error_msg = array_merge($error_msg, $err_info['data']);
            }
            if ($err_info['status'] == '-2') {
                $waves_cancel = 1;
            }
        }
        //组装错误信息
        if (!empty($error_msg)) {
            $errs = count($error_msg);
            $record_sum = $this->get_sum_record_by_waves($waves_record_id_arr);
            $success = $record_sum - $errs;
            $msg = $this->create_fail_file('错误信息', $error_msg, 'get_taobao_wlb_waybill_multi');
            return $this->format_ret(-1, '', '获取成功:  ' . $success . '条,  失败:' . $errs . '条!' . $msg);
        }
        if ($waves_cancel == 1) {
            return $this->format_ret(-1, '', '获取成功:  ' . 0 . '条,  失败:' . 0 . '条!');
        }
        return $this->format_ret(1, '', '获取成功！');
    }

    /**
     * 调用云栈三期，四期
     * @param $waves_record_id
     * @param null $print_type  '2':四期
     * @return array
     */
    function tb_wlb_waybill_get_action($waves_record_id, $print_type = NULL) {
        $wave_data = load_model('oms/WavesRecordModel')->get_record_by_id($waves_record_id);
        $ret_list = $this->get_deliver_id_list_by_waves_code($wave_data['record_code']);
        $error_msg = array();
        //整个波次单都作废
        if (empty($ret_list['data'])) {
            return $this->format_ret(-2);
        }
        //分页处理
        $list_all = array_chunk($ret_list['data'], 100);
        foreach ($list_all as $id_list) {
            //非四期订单给出提醒
            $no_deliver_rm = $this->check_print_type_by_deliver($id_list);
            if (!empty($no_deliver_rm)) {
                $deliver_record_id_arr = array();
                foreach ($no_deliver_rm as $deliver_record_id => $sell_record_code) {
                    $deliver_record_id_arr[] = $deliver_record_id;
                    $error_msg[] = "订单" . $sell_record_code . '没有绑定云栈四期模板';
                }
                $id_list = array_diff($id_list, $deliver_record_id_arr);
                if (empty($id_list)) {
                    continue;
                }
            }

            if ($print_type == '2') {
                $ret_wlb = $this->cn_wlb_waybill_get($waves_record_id, $id_list);
            } else {
                $ret_wlb = $this->tb_wlb_waybill_get($waves_record_id, $id_list);
            }
            if ($ret_wlb['status'] != 1) {
                if (!empty($ret_wlb['data'])) {
                    $error_msg = array_merge($error_msg, $ret_wlb['data']);
                } else {
                    $sell_record_arr = $this->get_sell_record_by_deliver($id_list);
                    $record_error = array();
                    foreach ($sell_record_arr as $sell_record_code) {
                        $record_error[] = "订单" . $sell_record_code . $ret_wlb['message'];
                    }
                    $error_msg = array_merge($error_msg, $record_error);
                }
            }
        }
        if (!empty($error_msg)) {
            return $this->format_ret(-1, $error_msg);
        }
        return $this->format_ret(1);
    }

    /**
     * 获取非云栈四期的订单
     * @param $deliver_record_id_arr
     * @return array
     */
    function check_print_type_by_deliver($deliver_record_id_arr) {
        $no_deliver_rm = array();
        //校验快递公司是否是否绑定云栈四期模板
        $sql_values = array();
        $deliver_record_id_str = $this->arr_to_in_sql_value($deliver_record_id_arr, 'deliver_record_id', $sql_values);
        $sql = "SELECT r1.deliver_record_id,r1.sell_record_code,r2.print_type,r2.rm_id FROM oms_deliver_record  AS r1 LEFT JOIN base_express AS r2 ON r1.express_code=r2.express_code WHERE r1.deliver_record_id IN ({$deliver_record_id_str})";
        $deliver_info = $this->db->get_all($sql, $sql_values);
        foreach ($deliver_info as $key => $value) {
            if ($value['print_type'] != 2) {
                unset($deliver_info[$key]);
                $no_deliver_rm[$value['deliver_record_id']] = $value['sell_record_code'];
                continue;
            }
            if (empty($value['rm_id'])) {
                unset($deliver_info[$key]);
                $no_deliver_rm[$value['deliver_record_id']] = $value['sell_record_code'];
                continue;
            }
        }
        //判断快递绑定的模板是否为云栈四期
        if (!empty($deliver_info)) {
            $rm_id_arr = array_unique(array_column($deliver_info, 'rm_id'));
            $sql_values = array();
            $print_templates_id_str = $this->arr_to_in_sql_value($rm_id_arr, 'print_templates_id', $sql_values);
            $sql = "SELECT print_templates_id FROM sys_print_templates WHERE is_buildin=3 AND print_templates_id IN ({$print_templates_id_str})";
            $print_templates_id_arr = $this->db->get_all_col($sql, $sql_values);
            foreach ($deliver_info as $value) {
                if (!in_array($value['rm_id'], $print_templates_id_arr)) {
                    $no_deliver_rm[$value['deliver_record_id']] = $value['sell_record_code'];
                }
            }
        }

        return $no_deliver_rm;
    }

    /**
     * 获取波次单关联的订单
     * @param $waves_record_id
     * @return array
     */
    function get_sell_record_by_deliver($deliver_record_id_arr) {
        $sql_value = array();
        $deliver_record_id_str = $this->arr_to_in_sql_value($deliver_record_id_arr, 'deliver_record_id', $sql_value);
        $sql = "SELECT sell_record_code FROM oms_deliver_record WHERE deliver_record_id IN ({$deliver_record_id_str})";
        $record_info = $this->db->get_all_col($sql, $sql_value);
        return $record_info;
    }

    /**
     * 获取波次单的订单总数
     * @param $waves_record_id_arr
     * @return bool|mixed
     */
    function get_sum_record_by_waves($waves_record_id_arr) {
        $sql_value = array();
        $waves_record_id_str = $this->arr_to_in_sql_value($waves_record_id_arr, 'waves_record_id', $sql_value);
        $sql = "SELECT COUNT(1) FROM oms_deliver_record WHERE waves_record_id IN ({$waves_record_id_str}) AND is_cancel=0"; //
        $record_sum = $this->db->get_value($sql, $sql_value);
        return $record_sum;
    }

    public function cn_wlb_waybill_get($waves_record_id, $deliver_id_arr, $opt_type = '') {
        $sql = "SELECT COUNT(1) FROM oms_waves_record WHERE is_cancel = 0 AND waves_record_id = :waves_record_id";
        $sql_values = array(':waves_record_id' => $waves_record_id);
        $pickup_num = $this->db->get_value($sql, $sql_values);
        if ($pickup_num < 1) {
            return $this->format_ret(-1, '', '波次拣货单不存在');
        }

        //是否开启多包裹
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_package = isset($ret_arr['is_more_deliver_package']) ? $ret_arr['is_more_deliver_package'] : 0;

        //读取订单
        $deliver_id_arr = is_array($deliver_id_arr) ? $deliver_id_arr : array($deliver_id_arr);
        $deliver_id_str = $this->arr_to_in_sql_value($deliver_id_arr, 'deliver_record_id', $sql_values);
        $sql_wh = " AND dr.is_cancel=0 AND dr.waves_record_id=:waves_record_id AND dr.deliver_record_id IN($deliver_id_str)";
        if ($is_more_package == 0) {
            $sql = "SELECT dr.* FROM oms_deliver_record AS dr WHERE dr.express_no='' {$sql_wh}";
            $sell_record_arr = $this->db->get_all($sql, $sql_values);
            if (empty($sell_record_arr)) {
                return $this->format_ret(-1, '', '请选择支持云栈面单的未匹配订单');
            }
        } else {
            $sql = "SELECT dr.*, rp.express_no AS package_express_no FROM oms_deliver_record AS dr LEFT JOIN oms_deliver_record_package AS rp 
                    ON dr.sell_record_code=rp.sell_record_code AND dr.waves_record_id=rp.waves_record_id AND dr.package_no=rp.package_no
                    WHERE 1=1 {$sql_wh}";
            $sell_record_arr = $this->db->get_all($sql, $sql_values);
            if (empty($sell_record_arr) || !empty($sell_record_arr['package_express_no'])) {
                return $this->format_ret(-1, '', '请选择支持云栈面单的未匹配订单');
            }
        }

        $_receiver_ids = array();
        $params = array();
        foreach ($sell_record_arr as $key => $row) {
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($row['sell_record_code']);
            if (empty($record_decrypt_info)) {
                return $this->format_ret(-1, '', '数据解密失败订单：' . $row['sell_record_code'] . '，稍后尝试！');
            }

            $row = array_merge($row, $record_decrypt_info);

            //收货人地址
            $row['receiver_addr'] = addslashes($row['receiver_addr']);
            //获取商品信息
            $row['goods_list'] = $this->tb_wlb_waybill_get_detail($row['deliver_record_id']);
            //电话
            $row['receiver_mobile'] = empty($row['receiver_mobile']) ? $row['receiver_phone'] : $row['receiver_mobile'];
            //获取发货人信息
            $sql = "SELECT * FROM base_store WHERE store_code = :store_code";
            $store = $this->db->get_row($sql, array('store_code' => $row['store_code']));

            $store_sender_info = $store;
            $store_sender_info['tel'] = $store['contact_phone'];
            $sql = "SELECT * FROM base_shop WHERE shop_code = :shop_code";
            $shop = $this->db->get_row($sql, array('shop_code' => $row['shop_code']));
            if (!empty($shop['province']) && !empty($shop['city']) && !empty($shop['contact_person']) && !empty($shop['tel']) && !empty($shop['address'])) {
                $store_sender_info = $shop;
            }
            $row['sender_province'] = $store_sender_info['province'];
            $row['sender_city'] = $store_sender_info['city'];
            $row['sender_district'] = $store_sender_info['district'];
            $row['sender_addr'] = $store_sender_info['address'];
            $row['sender_street'] = $store_sender_info['street'];
            $_receiver_ids[] = $store_sender_info['province'];
            $_receiver_ids[] = $store_sender_info['city'];
            $_receiver_ids[] = $store_sender_info['district'];
            $_receiver_ids[] = $store_sender_info['street'];
            $row['contact_phone'] = $store_sender_info['tel'];
            $row['contact_person'] = $store_sender_info['contact_person'];

            //收货地址
            $_receiver_ids[] = $row['receiver_province'];
            $_receiver_ids[] = $row['receiver_city'];
            $_receiver_ids[] = $row['receiver_district'];
            $_receiver_ids[] = $row['receiver_street'];

            //淘宝参数长度限制
            $row['receiver_addr'] = mb_substr($row['receiver_addr'], 0, 100, 'UTF-8');
            $row['receiver_tel'] = substr($row['receiver_mobile'], 0, 20);

            $row['order_channels_type'] = $this->get_cainiao_sale_channel($row['sale_channel_code'], $row['shop_code']);

            //京东货到付款
            if ($row['pay_type'] == 'cod' && $row['sale_channel_code'] == 'jingdong') {
                $payable_money = $this->get_jd_cod_payable_money($row['deal_code_list']);
                if ($payable_money !== false) {
                    $row['payable_money'] = $payable_money;
                }
            }

            //当当货到付款
            if ($row['pay_type'] == 'cod' && $row['sale_channel_code'] == 'dangdang') {
                $dangdang_row = $this->get_dangdang_print($row['deal_code_list']);
                if ($dangdang_row !== false) {
                    $row['payable_money'] = $dangdang_row['totalBarginPrice'];
                }
                $row['contact_person'] = !empty($dangdang_row['consignerName']) ? $dangdang_row['consignerName'] : $row['contact_person'];
                $row['contact_phone'] = !empty($dangdang_row['consignerTel']) ? $dangdang_row['consignerTel'] : $row['contact_phone'];
            }

            $params[$row['deliver_record_id']] = $row;
            $express_code_data[] = $row['express_code'];
        }

        //获取配送方式的标准模板url
        $express_code_str = deal_array_with_quote($express_code_data);
        $template_sql = "SELECT bs.express_code, bs.company_code, spt.template_body_default FROM base_express bs, sys_print_templates spt WHERE bs.rm_id=spt.print_templates_id AND bs.print_type=2 AND bs.rm_id!='' AND bs.express_code IN ({$express_code_str}) AND spt.is_buildin = 3";
        $template_data = $this->db->get_all($template_sql);
        $print_template_data = array();
        $company_code_arr = array();
        foreach ($template_data as $value) {
            $print_template_data[$value['express_code']] = $value['template_body_default'];
            $company_code_arr[$value['express_code']] = $value['company_code'];
        }

        //收货地址
        $_new_receiver_ids = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_new_receiver_ids}')");
        foreach ($_region_data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }

        foreach ($params as $key => $sellRecord) {
            $params[$key]['sender_province_name'] = isset($_receiver_data[$sellRecord['sender_province']]) ? $_receiver_data[$sellRecord['sender_province']] : '';
            $params[$key]['sender_district_name'] = isset($_receiver_data[$sellRecord['sender_district']]) ? $_receiver_data[$sellRecord['sender_district']] : '';
            $params[$key]['sender_city_name'] = isset($_receiver_data[$sellRecord['sender_city']]) && !in_array($_receiver_data[$sellRecord['sender_city']], array('省直辖县级行政区划', '自治区直辖县级行政区划')) ? $_receiver_data[$sellRecord['sender_city']] : '';
            $params[$key]['sender_street_name'] = isset($_receiver_data[$sellRecord['sender_street']]) && !in_array($_receiver_data[$sellRecord['sender_city']], array('区直辖村模拟镇')) ? $_receiver_data[$sellRecord['sender_street']] : '';

            $params[$key]['receiver_province_name'] = isset($_receiver_data[$sellRecord['receiver_province']]) ? $_receiver_data[$sellRecord['receiver_province']] : '';
            $params[$key]['receiver_city_name'] = isset($_receiver_data[$sellRecord['receiver_city']]) && !in_array($_receiver_data[$sellRecord['receiver_city']], array('省直辖县级行政区划', '自治区直辖县级行政区划')) ? $_receiver_data[$sellRecord['receiver_city']] : '';
            $params[$key]['receiver_district_name'] = isset($_receiver_data[$sellRecord['receiver_district']]) ? $_receiver_data[$sellRecord['receiver_district']] : '';
            $params[$key]['receiver_street_name'] = isset($_receiver_data[$sellRecord['receiver_street']]) && !in_array($_receiver_data[$sellRecord['receiver_street']], array('区直辖村模拟镇')) ? $_receiver_data[$sellRecord['receiver_street']] : '';

            $params[$key]['sender_name'] = $sellRecord['contact_person'];
            $params[$key]['sender_phone'] = $sellRecord['contact_phone'];
        }
        $listCount = count($params);
        $size = 1;
        $index = 0;
        $errs = array();
        $success = 0;
        do {
            $list = array_slice($params, $index, $size, true);
            $index += $size;
            // FIXME: 此处待优化
            $express_code = '';
            $sell_record_code = '';
            foreach ($list as $_k => &$_v) {
                $express_code = $_v['express_code'];
                $sell_record_code = $_v['sell_record_code'];
                $_v['template_url'] = $print_template_data[$express_code];
                $_v['express_code'] = $company_code_arr[$express_code];
            }
            // FIXME: 此处待优化
            $sql = "select rm_shop_code from base_express where express_code = :express_code";
            $rm_shop_code = $this->db->get_value($sql, array('express_code' => $express_code));
            if (empty($rm_shop_code)) {
                $errs[] = "订单" . $sell_record_code . ": 获取热敏单号挂靠店铺不存在";
                continue;
            }
            $client = new TaobaoClient($rm_shop_code);
            $code = $client->cloudWlbWaybillPrint($list);

            //更新订单面单号
            //TODO: 避免浪费面单号
            foreach ($code as $sellRecordId => $waybill) {
                if ($waybill['status'] != 1) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $waybill['message'];
                    continue;
                }
                if (!isset($waybill['data'][0]['waybill_code'])) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $waybill['message'];
                    continue;
                }
                $dat = json_encode($waybill['data'][0]);
                if ($dat == null) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": json解析失败";
                    continue;
                }
                $this->begin_trans();
                try {
                    $express_data = array('express_no' => $waybill['data'][0]['waybill_code'], 'express_data' => $dat);
                    $this->save_sell_record_express_no($params[$sellRecordId]['sell_record_code'], $express_data, $type = 'yzrm');
                    $this->commit();
                    $success++;
                } catch (Exception $e) {
                    $this->rollback();
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $e->getMessage();
                }
            }
        } while ($index < $listCount);

        if ($opt_type == 'api') {
            if ($success == 0) {
                return $this->format_ret(-1, (object) array(), $errs[0]);
            }
            return $this->format_ret(1, array('express_code' => $express_code, 'express_no' => $express_data['express_no'], 'print_data' => $express_data['express_data']), '获取成功');
        } else {
            //if (empty($success)) {
            //    return $this->format_ret(-1, $errs, '获取失败');
            //}

            if (count($errs) > 0) {
                $msg = $this->create_fail_file('错误信息', $errs);
                return $this->format_ret(-1, $errs, '获取成功:' . $success . ', 失败:' . count($errs) . $msg);
            }
        }
        return $this->format_ret(1, '', '获取成功');
    }

    /**
     * 错误信息下载
     * @param $fail_top
     * @param $error_msg
     * @return string
     */
    function create_fail_file($fail_top, $error_msg, $name = 'get_taobao_wlb_waybill') {
        $file_name = $this->api_create_import_fail_files($fail_top, $error_msg, $name);
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $message = "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    /**
     * 写入错误信息
     * @param $fail_top 表头
     * @param $error_msg  内容
     * @return string
     */
    function api_create_import_fail_files($fail_top, $error_msg, $name = 'get_taobao_wlb_waybill') {
        $file_str = is_array($fail_top) ? implode(",", $fail_top) . "\n" : $fail_top . "\n";
        foreach ($error_msg as $key => $val) {
            $val_data = is_array($val) ? implode("\t,", $val) : $val;
            $file_str .= $val_data . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 云栈面单
     * @param $waves_record_id
     * @param $idList
     * @return array
     * @throws Exception
     */
    function tb_wlb_waybill_get($waves_record_id, $idList) {
//        $errs = array('this is error');
//        $num_a = rand(0,12);$num_b = rand(0,16);
//            return return_value(-1, '获取成功:  ' . $num_b . '   条,  失败:   '.$num_a.'  条!', $errs);
        $idStr = implode(',', $idList);
        //读取拣货单, 根据拣货单编号
        $sql = "select * from oms_waves_record
        where is_cancel = 0 and waves_record_id = :waves_record_id";
        $pickup = $this->db->get_row($sql, array('waves_record_id' => $waves_record_id));
        if (empty($pickup)) {
            return return_value(-1, '拣货单不存在');
        }



        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_arr['is_more_deliver_package']) ? $ret_arr['is_more_deliver_package'] : '0';

        if ($is_more_deliver_package == 0) {
            //读取订单
            $sql = "select a.* from oms_deliver_record a
            where a.is_cancel = 0 and a.waves_record_id = :waves_record_id and a.deliver_record_id IN ($idStr)
            and a.express_no = '' ";
            //and a.express_code IN ($supportExpressStr)";
            $sellRecordAll = $this->db->get_all($sql, array('waves_record_id' => $pickup['waves_record_id']));
            if (empty($sellRecordAll)) {
                return array('status' => -1, 'data' => '', 'message' => '请选择支持云栈面单的未匹配订单');
            }
        } else {
            //读取订单
            $sql = "select a.*,p.express_no as package_express_no  from oms_deliver_record a
             LEFT JOIN  oms_deliver_record_package p ON a.sell_record_code=p.sell_record_code AND a.waves_record_id=p.waves_record_id  AND  a.package_no= p.package_no
            where a.is_cancel = 0 and a.waves_record_id = :waves_record_id and a.deliver_record_id IN ($idStr)
           ";
            $sellRecordAll = $this->db->get_all($sql, array('waves_record_id' => $pickup['waves_record_id']));
            if (empty($sellRecordAll) || !empty($sellRecordAll['package_express_no'])) {
                return array('status' => -1, 'data' => '', 'message' => '请选择支持云栈面单的未匹配订单');
            }
        }



        $_receiver_ids = array();
        $params = array();

        foreach ($sellRecordAll as $key => $sellRecord) {
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sellRecord['sell_record_code']);
            if (empty($record_decrypt_info)) {
                return $this->format_ret(-1, '', '数据解密失败订单：' . $sellRecord['sell_record_code'] . '，稍后尝试！');
            }
            $sellRecord = array_merge($sellRecord, $record_decrypt_info);


            $sellRecord['receiver_addr'] = addslashes($sellRecord['receiver_addr']);
            //商品名称
            $sellRecord['goods_list'] = $this->tb_wlb_waybill_get_detail($sellRecord['deliver_record_id']);

            //来源类型
            //$sellRecord['source_code'] = get_status(84, $sellRecord['source']);
            //电话
            $sellRecord['receiver_mobile'] = empty($sellRecord['receiver_mobile']) ? $sellRecord['receiver_phone'] : $sellRecord['receiver_mobile'];

            $sql = "select * from base_store where store_code = :store_code";
            $store = $this->db->get_row($sql, array('store_code' => $sellRecord['store_code']));

            // 发货地址
            $sellRecord['sender_province'] = $store['province'];
            $sellRecord['sender_city'] = $store['city'];
            $sellRecord['sender_district'] = $store['district'];
            $sellRecord['sender_addr'] = $store['address'];
            $sellRecord['sender_street'] = $store['street'];
            $_receiver_ids[] = $store['province'];
            $_receiver_ids[] = $store['city'];
            $_receiver_ids[] = $store['district'];
            $_receiver_ids[] = $store['street'];


            //收货地址
            $_receiver_ids[] = $sellRecord['receiver_province'];
            $_receiver_ids[] = $sellRecord['receiver_city'];
            $_receiver_ids[] = $sellRecord['receiver_district'];
            $_receiver_ids[] = $sellRecord['receiver_street'];
            /* if (1 == $sellRecord['is_split_new']) {//拆分订单
              $sellRecord['deal_code'] = $sellRecord['sell_record_id'] . '_' . $sellRecord['deal_code'];
              }
              if (1 == $sellRecord['is_copy']) {//复制订单
              $sellRecord['deal_code'] = $sellRecord['sell_record_id'] . '_' . $sellRecord['deal_code'];
              } */

            //淘宝参数长度限制
            $sellRecord['goods_list'] = $sellRecord['goods_list'];
            $sellRecord['receiver_addr'] = mb_substr($sellRecord['receiver_addr'], 0, 100, 'UTF-8');
            $sellRecord['receiver_tel'] = substr($sellRecord['receiver_mobile'], 0, 20);
            $sellRecord['deal_code'] = substr($sellRecord['deal_code'], 0, 20);

            $sellRecord['order_channels_type'] = $this->get_cainiao_sale_channel($sellRecord['sale_channel_code'], $sellRecord['shop_code']);

            $params[$sellRecord['deliver_record_id']] = $sellRecord;
        }

        //收货地址
        $_receiver_ids = implode("','", array_unique($_receiver_ids));
        $_region_data = $this->db->get_all("SELECT id region_id, name region_name FROM base_area WHERE id IN('{$_receiver_ids}')");
        foreach ($_region_data as $_region) {
            $_receiver_data[$_region['region_id']] = $_region['region_name'];
        }
        foreach ($params as $key => $sellRecord) {
            $params[$key]['sender_province_name'] = isset($_receiver_data[$sellRecord['sender_province']]) ? $_receiver_data[$sellRecord['sender_province']] : '';
            $params[$key]['receiver_province_name'] = isset($_receiver_data[$sellRecord['receiver_province']]) ? $_receiver_data[$sellRecord['receiver_province']] : '';
            $params[$key]['sender_city_name'] = isset($_receiver_data[$sellRecord['sender_city']]) ? $_receiver_data[$sellRecord['sender_city']] : '';
            $params[$key]['receiver_city_name'] = isset($_receiver_data[$sellRecord['receiver_city']]) ? $_receiver_data[$sellRecord['receiver_city']] : '';
            $params[$key]['sender_district_name'] = isset($_receiver_data[$sellRecord['sender_district']]) ? $_receiver_data[$sellRecord['sender_district']] : '';
            $params[$key]['receiver_district_name'] = isset($_receiver_data[$sellRecord['receiver_district']]) ? $_receiver_data[$sellRecord['receiver_district']] : '';
            $params[$key]['sender_street_name'] = isset($_receiver_data[$sellRecord['sender_street']]) ? $_receiver_data[$sellRecord['sender_street']] : '';
            $params[$key]['receiver_street_name'] = isset($_receiver_data[$sellRecord['receiver_street']]) ? $_receiver_data[$sellRecord['receiver_street']] : '';
        }


        //$client = new TaobaoClient('001');

        $listCount = count($params);
        //$size = $listCount > 50 ? 50 : $listCount; //更高性能
        $size = 1; //FIXME: 因为区分快递方式挂靠店铺, 此处性能待优化
        $index = 0;

        $errs = array();
        $success = 0;
        do {
            $list = array_slice($params, $index, $size, true);
            $index = $index + $size;

            // FIXME: 此处待优化
            $express_code = '';
            $sell_record_code = '';
            foreach ($list as $_k => $_v) {
                $express_code = $_v['express_code'];
                $sell_record_code = $_v['sell_record_code'];
                $list[$_k]['company_express_code'] = $this->get_express_company($express_code);
            }
            // FIXME: 此处待优化
            $sql = "select rm_shop_code from base_express where express_code = :express_code";
            $rm_shop_code = $this->db->get_value($sql, array('express_code' => $express_code));
            if (empty($rm_shop_code)) {
                $errs[] = "订单" . $sell_record_code . ": 获取热敏单号挂靠店铺不存在";
                continue;
            }

            $client = new TaobaoClient($rm_shop_code);
            $code = $client->multiWlbWaybillGet($list);

            //更新订单面单号
            //TODO: 避免浪费面单号
            foreach ($code as $sellRecordId => $waybill) {
                if ($waybill['status'] != 1) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $waybill['message'];
                    continue;
                }
                if (!isset($waybill['data'][0]['waybill_code'])) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $waybill['message'];
                    continue;
                }

                $dat = json_encode($waybill['data'][0]);
                if ($dat == null) {
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": json解析失败";
                    continue;
                }

                $this->begin_trans();
                try {

                    $express_data = array('express_no' => $waybill['data'][0]['waybill_code'], 'express_data' => $dat);
                    $this->save_sell_record_express_no($params[$sellRecordId]['sell_record_code'], $express_data, $type = 'yzrm');


                    $this->commit();
                    $success++;
                } catch (Exception $e) {
                    $this->rollback();
                    $errs[] = "订单" . $params[$sellRecordId]['sell_record_code'] . ": " . $e->getMessage();
                }
            }
        } while ($index < $listCount);

        //if (empty($success)) {
        //    return return_value(-1, '获取失败', $errs);
        //}

        if (count($errs) > 0) {
            $msg = $this->create_fail_file('错误信息', $errs);
            return return_value(-1, '获取成功:  ' . $success . '   条,  失败:   ' . count($errs) . '  条!' . $msg, $errs);
        }

        return return_value(1, '获取成功');
    }

    function get_express_company($express_code) { //base_express_company
        static $express_company = NULL;
        if (!isset($express_company[$express_code])) {
            $express_company[$express_code] = $this->db->get_value("select company_code from base_express WHERE  express_code=:express_code ", array(':express_code' => $express_code));
        }
        return $express_company[$express_code];
    }

    function check_refund($data) {
        $deal_code_arr = array();
        foreach ($data['detail_list'] as $sub_data) {
            $deal_code_arr['deal_code'] = $sub_data['deal_code'];
        }
        if (empty($deal_code_arr)) {
            return $this->format_ret(-1, '', '缺少订单商品明细');
        }
        $deal_code_list = "'" . join("','", $deal_code_arr) . "'";
        $sql = "select tid from api_refund where tid  in({$deal_code_list}) and status = 1 and is_change = 0";
        $tid_arr = ctx()->db->get_all_col($sql);
        if (!empty($tid_arr)) {
            $tid_list = join(',', $tid_arr);
            return $this->format_ret(-1, '', "交易号{$tid_list}存在未处理的退单");
        }
        //淘宝货到付款关闭状态校验拦截
        $ret = load_model('oms/SellRecordOptModel')->check_trade_closed($data['record']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    public function get_sku_by_sub_barcode($id, $b, $tl = '') {
        $unique_flag = 0;
        $sku = '';
        $sku_data = array();
        if ($tl != 1) {//通灵走唯一吗
            $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($b, 1, 1);
        }

        if (empty($sku_data)) {
            //查询是否开启唯一码
            $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
            if ($unique_arr['unique_status'] == 1) {
                //开启唯一码查询:扫描条码是否 是唯一码 为1是通灵
                if ($tl != 1) {
                    $sql = "select sku,status from goods_unique_code where unique_code = :unique_code";
                } else {
                    $sql = "select * from goods_unique_code_tl where unique_code = :unique_code";
                }

                $unique_ret = $this->db->get_row($sql, array('unique_code' => $b));
                if (empty($unique_ret)) {
                    return array('status' => '-1', 'message' => '系统中不存在该唯一码！');
                } elseif ($unique_ret['status'] == 1) {
                    return array('status' => '-1', 'message' => '系统已开启唯一码，且唯一码为不可用状态！');
                } else {
                    //判断条码是否使用
                    $sql = "SELECT sell_record_code FROM oms_deliver_record_detail WHERE deliver_record_id = '{$id}';";
                    $record_code = $this->db->get_row($sql);
                    $sql = "SELECT * FROM unique_code_scan_temporary_log WHERE barcode_type = 'unique_code' AND unique_code = '{$b}' AND sell_record_code = '{$record_code['sell_record_code']}';";
                    $unique = $this->db->get_all($sql);
                    if (!empty($unique)) {
                        return array('status' => '-1', 'message' => '该唯一码已被使用');
                    } else {
                        $sku = $unique_ret['sku'];
                    }
                }
                $unique_flag = 1;
            } else {
                return array('status' => '-1', 'message' => '系统中不存在该条码！');
            }
        } else {
            $sku = $sku_data['sku'];
        }

        $sql = "SELECT sku,deliver_record_detail_id FROM oms_deliver_record_detail WHERE deliver_record_id=:deliver_record_id AND sku=:sku AND num>scan_num";
        $ret = $this->db->get_all($sql, array(':deliver_record_id' => $id, ':sku' => $sku));
        if (empty($ret)) {
            return $this->format_ret(-1, '', '条码不存在该订单中或已超出扫描范围');
        }
        if ($tl == 1) {
            $key_arr = array('goods_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($unique_ret['sku'], $key_arr);
            $unique_ret = array_merge($unique_ret, $sku_info);
            $unique_ret['deliver_record_detail_id'] = $ret[0]['deliver_record_detail_id'];
            $ret = $unique_ret;
        }
        return array('status' => '1', 'data' => $ret, 'length' => count($ret), 'unique_flag' => $unique_flag, 'message' => '成功');
    }

    //通灵
    function get_sku_by_barcode($id, $barcode) {
        $type = 0;
        $unique_flag = 0;
        $sku = '';
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
        if ($unique_arr['unique_status'] == 1) {
            $sql = "select t.*,g.goods_code,bg.goods_name as goods_code_name from goods_unique_code_tl t inner join goods_sku g on t.sku = g.sku inner join base_goods bg on g.goods_code = bg.goods_code where unique_code = :unique_code";
            $unique_ret = $this->db->getRow($sql, array(':unique_code' => $barcode));

            if (empty($unique_ret)) { //唯一码不存在时,走条码
                $sql = "select * from goods_sku where barcode = :barcode";
                $sku_detail = $this->db->getRow($sql, array(':barcode' => $barcode));
                if (empty($sku_detail)) {
                    return array('status' => '-1', 'message' => '该条码不存在！');
                }
                $sql = "select r1.*,r3.goods_name as goods_code_name,r3.goods_code from oms_deliver_record_detail r1 inner join goods_sku r2 on r1.sku = r2.sku inner join base_goods r3 on r2.goods_code = r3.goods_code having deliver_record_id = :id and sku = :sku";
                $unique_ret = $this->db->getRow($sql,array('id' => $id,'sku' => $sku_detail['sku']));

                if (empty($unique_ret)) {
                    return $this->format_ret(-1, '', '条码不存在该订单中');
                }

                if ($unique_ret['is_gift'] == '0') {
                    return array('status' => '-1', 'message' => '系统中不存在该唯一码,且该商品不是礼品！');
                }

                $sku = $unique_ret['sku'];

                $type = 1;
            } elseif ($unique_ret['status'] == 1) {
                return array('status' => '-1', 'message' => '系统已开启唯一码，且唯一码为不可用状态！');
            } else {
                //判断唯一码是否使用
                $sql = "SELECT sell_record_code FROM oms_deliver_record_detail WHERE deliver_record_id = :id";
                $record_code = $this->db->get_row($sql,array('id' => $id));
                //验证仓库
                $store_ret = $this->check_store($record_code['sell_record_code'],$barcode);
                if($store_ret['status'] < 0){
                    return $store_ret;
                }
                $sql = "SELECT * FROM unique_code_scan_temporary_log WHERE barcode_type = 'unique_code' AND unique_code = :unique_code AND sell_record_code = :sell_record_code";
                $unique = $this->db->get_all($sql,array('unique_code' => $barcode,'sell_record_code' => $record_code['sell_record_code']));
                if (!empty($unique)) {
                    return array('status' => '-1', 'message' => '该唯一码已被使用');
                } else {
                    $sku = $unique_ret['sku'];
                    $type = 2;
                }
            }
            $unique_flag = 1;
        } else {
            return array('status' => '-1', 'message' => '系统中不存在该条码！');
        }

        $sql = "SELECT sku,deliver_record_detail_id FROM oms_deliver_record_detail WHERE deliver_record_id=:deliver_record_id AND sku=:sku AND num>scan_num";
        $ret = $this->db->get_all($sql, array(':deliver_record_id' => $id, ':sku' => $sku));
        if (empty($ret)) {
            return $this->format_ret(-1, '', '条码不存在该订单中或已超出扫描范围');
        }
        $unique_ret['deliver_record_detail_id'] = $ret[0]['deliver_record_detail_id'];
        $ret = $unique_ret;
        return array('status' => '1', 'data' => $ret, 'length' => count($ret), 'unique_flag' => $unique_flag, 'type' => $type, 'message' => '成功');
    }
    
    /**
     * 判断唯一码档案和订单中的仓库是否一致
     * @param type $sell_record_code
     * @param type $unique_code
     * @return type
     */
    function check_store($sell_record_code,$unique_code) {
        $sell_store = oms_tb_val('oms_sell_record', 'store_code', array('sell_record_code' => $sell_record_code));
        $unique_store = oms_tb_val('goods_unique_code_tl', 'store_code', array('unique_code' => $unique_code));
        if($sell_store != $unique_store){
            return $this->format_ret(-1, '', '唯一码档案中的仓库和订单中的不同');
        }
        return $this->format_ret(1);
    }
    

    
    //波次单订单是否全部验收或取消
    public function deliver_list($waves_record_id) {
        $sql_main = "select a.is_deliver,a.is_cancel,a.waves_record_id FROM oms_deliver_record a
    	LEFT JOIN oms_deliver_record_detail b ON a.deliver_record_id = b.deliver_record_id
    	WHERE 1  AND a.waves_record_id = :waves_record_id  GROUP BY a.deliver_record_id ORDER BY a.deliver_record_id";
        $sql_values[':waves_record_id'] = $waves_record_id;
        $rs = $this->db->get_all($sql_main, $sql_values);
        $is_ok = 1;
        $is_emp = 0;
        foreach ($rs as $key => $value) {
            $is_emp = 1;
            if ($value['is_deliver'] == '0' && $value['is_cancel'] == '0') {
                $is_ok = 2;
                break;
            }
        }
        if ($is_ok == 1 && $is_emp == 1) {
            $d = array(
                'is_accept' => '1',
                'accept_time' => date('Y-m-d H:i:s'),
                'accept_user' => CTX()->get_session('user_name'),
            );
            $this->db->create_mapper('oms_waves_record')->update($d, array('waves_record_id' => $waves_record_id));
            //$r = $this->update($d, array('waves_record_id'=>$waves_record_id));
        }
        return $rs;
    }

    public function get_order_num($waves_record_id) {
        $sql = "select count(*) as cnt from oms_deliver_record where waves_record_id = :waves_record_id";
        $data = $this->db->get_row($sql, array('waves_record_id' => $waves_record_id));
        return $data;
    }

    //是否含有楚楚街订单
    public function is_channel_chuchu($waves_record_id) {
        $sql = "select sell_record_code from oms_sell_record where waves_record_id = :waves_record_id and is_back <> '1' and sale_channel_code = 'chuchujie'";
        $data = $this->db->get_row($sql, array('waves_record_id' => $waves_record_id));
        if (empty($data)) {
            return 0;
        } else {
            return 1;
        }
        //return $data;
    }

    function get_scan_lof_data($filer) {

        $sku = $this->db->get_value("select sku from goods_sku where barcode=:barcode", array(':barcode' => $filer['barcode']));
        $data = $this->get_recode_by_sku($filer['record_code'], $sku);
        $lof_info = array();
        $sku_num = 0;
        $store_code = '';
        if (count($data) > 0) {
            foreach ($data as $_val) {
                $key = $_val['lof_no'] . $_val['production_date'];
                $sku_num += $_val['num'];
                $lof_info[$key] = $_val;
            }
            $store_code = $data[0]['store_code'];
        }
        $ret = load_model('prm/InvOpLofModel')->get_lof_data_by_num($store_code, $sku, $sku_num);
        $record_data = array();
        if (!empty($ret['data'])) {
            $is_self_lof = FALSE;
            foreach ($ret['data'] as &$val) {
                $key = $val['lof_no'] . $val['production_date'];
                $val['select'] = 0;
                if (isset($lof_info[$key])) {
                    $val['select'] = 1;
                    $is_self_lof = true;
                }
            }
            $record_data = &$ret['data'];
            if ($is_self_lof === TRUE && count($record_data) == 1) {
                $record_data = array();
            }
        }
        $ret_data = array('record' => $record_data, 'results' => count($record_data));
        return $this->format_ret(1, $ret_data);
    }

    function get_record_lof_info($record_code, $sku) {
        $data = $this->get_recode_by_sku($record_code, $sku);
        $lof_info = array();
        if (count($data) > 1) {
            foreach ($data as $val) {
                $key = $val['lof_no'] . $val['production_date'];
                $lof_info[$key] = $val;
            }
        }
        return $lof_info;
    }

    function get_recode_by_sku($record_code, $sku) {

        $sql = "select * from oms_sell_record_lof
                where record_type=:record_type AND  record_code=:record_code AND sku=:sku";
        $sql_values = array(':record_type' => 1, ':record_code' => $record_code, ':sku' => $sku);
        return $this->db->get_all($sql, $sql_values);
    }

    function set_select_barcode_lof($filer) {
        $arr = array('barcode', 'record_code', 'lof_no', 'production_date');
        foreach ($arr as $val) {
            if (!isset($filer[$val])) {
                return $this->format_ret(-1, '', '缺少参数' . $val);
            }
        }

        $sku = $this->db->get_value("select sku from goods_sku where barcode=:barcode", array(':barcode' => $filer['barcode']));
        $data = $this->get_recode_by_sku($filer['record_code'], $sku);
        $lof_no_info = array('lof_no' => $filer['lof_no'], 'production_date' => $filer['production_date']);
        $this->begin_trans();
        foreach ($data as $record_lof) {
            $ret = load_model('prm/InvOpLofModel')->change_lof_inv($record_lof, $lof_no_info);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit();
        return $ret;
    }

    function get_package_express_no($sell_record_code, $package_no) {
        $record = $this->db->get_row("select * from oms_deliver_record where sell_record_code=:sell_record_code AND is_cancel=:is_cancel", array(':sell_record_code' => $sell_record_code, ':is_cancel' => 0));
        if (empty($record['express_no'])) {
            return $this->format_ret(-1, '', '请先设置当前包裹快递单号');
        }


        if (!empty($record['express_no']) && $record['package_no'] == 0) {
            $record_package_data = array(
                'sell_record_code' => $sell_record_code,
                'express_code' => $record['express_code'],
                'express_no' => $record['express_no'],
                'express_data' => $record['express_data'],
                'package_no' => 1,
                'waves_record_id' => $record['waves_record_id'],
            );
            $this->save_package_data($record_package_data);
        }
        $this->db->update('oms_deliver_record', array('package_no' => $package_no), array('sell_record_code' => $sell_record_code, 'is_cancel' => 0));
        $express_no = '';
        $express_data = $this->get_package_express_no_by_package_no($sell_record_code, $record['waves_record_id'], $package_no);
        if (!empty($express_data)) {
            $express_no = $express_data['express_no'];
        }
        return $this->format_ret(1, $express_no);
    }

    private function get_package_express_no_by_package_no($sell_record_code, $waves_record_id, $package_no) {
        $sql_values = array(':sell_record_code' => $sell_record_code, ':waves_record_id' => $waves_record_id, ':package_no' => $package_no);
        $sql = "select * from oms_deliver_record_package where sell_record_code=:sell_record_code AND waves_record_id=:waves_record_id AND package_no=:package_no";
        $data = $this->db->get_row($sql, $sql_values);
        return $data;
    }

    function save_sell_record_express_no($sell_record_code, $express_data, $type = '') {
        //todo: 加包裹快递单号日志

        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_arr['is_more_deliver_package']) ? $ret_arr['is_more_deliver_package'] : '0';
        $record = $this->db->get_row("select * from oms_deliver_record where sell_record_code=:sell_record_code AND is_cancel=:is_cancel", array(':sell_record_code' => $sell_record_code, ':is_cancel' => 0));

        if ($is_more_deliver_package == 1) {

            if (empty($record['package_no'])) {
                $record['package_no'] = 1;
                $express_data['package_no'] = 1;
            }

            $package_data = $this->get_package_express_no_by_package_no($sell_record_code, $record['waves_record_id'], $record['package_no']);
            if (!empty($package_data)) {
                $record = array_merge($record, $package_data);
            }
            if ($record['package_no'] == 1) {
                $this->update_express_no($sell_record_code, $express_data, $record, $type);
            }

            $record_package_data = array(
                'sell_record_code' => $sell_record_code,
                'express_code' => $record['express_code'],
                'package_no' => $record['package_no'],
                'waves_record_id' => $record['waves_record_id'],
            );
            $record_package_data = array_merge($record_package_data, $express_data);

            $this->save_package_data($record_package_data);
            $sql_values = array(':sell_record_code' => $sell_record_code, ':waves_record_id' => $record['waves_record_id']);
            $sql="update oms_sell_record set package_num = (select count(*) from oms_deliver_record_package where  sell_record_code=:sell_record_code AND waves_record_id=:waves_record_id) where sell_record_code=:sell_record_code AND waves_record_id=:waves_record_id";
            $this->query($sql,$sql_values);
        } else {
            $this->update_express_no($sell_record_code, $express_data, $record, $type);
        }
        return $this->format_ret(1);
    }

    private function save_package_data($record_package_data) {

        $update_str = " express_no= VALUES(express_no),express_code= VALUES(express_code),express_data= VALUES(express_data) ";
        $this->insert_multi_duplicate('oms_deliver_record_package', array($record_package_data), $update_str);
    }

    public function cancel_express_no_all($sell_record_code, $waves_record_id) {
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        $ret = $this->format_ret(1);

        $sql = "select * from oms_deliver_record  where sell_record_code=:sell_record_code AND  waves_record_id=:waves_record_id ";
        $sql_values = array(':sell_record_code' => $sell_record_code, ':waves_record_id' => $waves_record_id);
        $record = $this->db->get_row($sql, $sql_values);
        if ($is_more_deliver_package == 1) {
            $sql = "select * from oms_deliver_record_package  where sell_record_code=:sell_record_code AND  waves_record_id=:waves_record_id ";
            $package_data = $this->db->get_all($sql, $sql_values);
            if (!empty($package_data)) {
                foreach ($package_data as $row) {
                    $new_record = array_merge($record, $row);
                    $ret = load_model('oms/DeliverLogisticsModel')->cancel_waybill($new_record);
                }
            } else {
                $ret = load_model('oms/DeliverLogisticsModel')->cancel_waybill($record);
            }
        } else {
            $ret = load_model('oms/DeliverLogisticsModel')->cancel_waybill($record);
        }

        return $ret;
    }

    private function update_express_no($sell_record_code, $express_data, $record, $type = '') {
        // order_status= 1 and  shipping_status=4
        //未发货的修改单号是云栈要取消单号
        if ($record['is_deliver'] == 0 && $express_data['express_no'] != $record['express_no']) {
            load_model('oms/DeliverLogisticsModel')->cancel_waybill($record);
        }

        $this->db->update('oms_deliver_record', $express_data, array('sell_record_code' => $sell_record_code, 'is_cancel' => 0));
        //已发货订单 需要将值同步更新到api_order_send表中，否则回写失败
        if ($record['is_deliver'] == 1) {
            $this->db->update('api_order_send', array('express_no' => $express_data['express_no']), array('sell_record_code' => $sell_record_code));
        }

//        if (isset($express_data['express_data'])) {
//            unset($express_data['express_data']);
//        }
        //确认状态且通知配货后的所有状态下才更新订单表的物流数据，防止并发 FBB 2017.06.07
        $this->db->update('oms_sell_record', $express_data, "sell_record_code='{$sell_record_code}' AND order_status=1 AND shipping_status>0");
        $affected_rows = $this->db->affected_rows();
        if ($affected_rows != 1) {
            return $this->format_ret(-1, '获取物流信息失败，请检查订单状态');
        }

        if ($type == 'yzrm') {
            $action = '获取云栈热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        if ($type == 'jdrm') {
            $action = '获取京东热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        if ($type == 'sfrm') {
            $action = '获取顺丰热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
        if ($type == 'alpha') {
            $action = '获取无界热敏物流';
            $msg = '快递单号由' . $record['express_no'] . '修改为' . $express_data['express_no'];
            load_model('oms/SellRecordActionModel')->add_action($sell_record_code, $action, $msg);
        }
    }

    function is_more_package_edit($sell_record_code, $waves_record_id) {
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        $is_more_deliver_package = isset($ret_pararm['is_more_deliver_package']) ? $ret_pararm['is_more_deliver_package'] : '0';
        $ret_status = 1;
        $message = '';
        if ($is_more_deliver_package == 1) {
            $sql_values = array(':sell_record_code' => $sell_record_code, ':waves_record_id' => $waves_record_id);
            $sql = "select express_no from oms_deliver_record_package where sell_record_code=:sell_record_code AND waves_record_id=:waves_record_id";
            $data = $this->db->get_all($sql, $sql_values);
            foreach ($data as $val) {
                if (!empty($val['express_no'])) {
                    $ret_status = -1;
                    $message = '已使用多包裹，必须删除所有包裹物流单号才可修改！';
                    break;
                }
            }
        }

        return $this->format_ret($ret_status, '', $message);
    }

    function get_package_by_page($filter) {

        $sql_values = array();
        $sql_main = "FROM oms_sell_record r
         INNER JOIN oms_deliver_record_package p ON r.sell_record_code = p.sell_record_code
         WHERE 1 AND p.express_no<>''  ";
        
         //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r.store_code', $filter_store_code);
        
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        if (isset($filter['express_no']) && $filter['express_no'] <> '') {
            $sql_main .= " AND  p.express_no = :express_no  ";
            $sql_values[':express_no'] = $filter['express_no'];
        }
        if (isset($filter['deal_code']) && $filter['deal_code'] <> '') {
            $sql_main .= " AND r.deal_code  like :deal_code   ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] <> '') {
            $sql_main .= " AND r.sell_record_code  like :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        if (isset($filter['receiver_name']) && $filter['receiver_name'] <> '') {

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( r.receiver_name LIKE :receiver_name  OR r.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }

//            $sql_main .= " AND r.receiver_name  like :receiver_name   ";
//            $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
        }
        
        	 //仓库
         if (isset($filter['store_code']) && !empty($filter['store_code'])) {
                    $arr = explode(',', $filter['store_code']);
                    $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
                    $sql_main .= " AND r.store_code in (" . $str . ") ";
         }
        
        
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r.shop_code in ( " . $str . " ) ";
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
            $arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND p.express_code in ( " . $str . " ) ";
        }
        //发货时间
        if (!empty($filter['delivery_time_start'])) {
            $sql_main .= " AND r.delivery_time >= :send_time_start ";
            $sql_values[':send_time_start'] = $filter['delivery_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['delivery_time_end'])) {
            $sql_main .= " AND r.delivery_time <= :send_time_end ";
            $sql_values[':send_time_end'] = $filter['delivery_time_end'] . ' 23:59:59';
        }
        //包裹数量
        if(isset($filter['package_num'])&&$filter['package_num']!=''){
            if($filter['package_num']=='0'){
                $sql_main.=" AND r.package_num = :package_num";
                $sql_values[':package_num']=1;
            }else{
                $sql_main.=" AND r.package_num > :package_num";
                $sql_values[':package_num']=1;
            }
        }
        //排序
        $sql_main .= " ORDER BY p.sell_record_code,p.package_no";
        $select = 'r.store_code,r.sell_record_code,r.receiver_name,r.receiver_address,r.receiver_addr, r.shop_code,r.delivery_time,p.express_no,p.package_no,p.express_code';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        filter_fk_name($data['data'], array('shop_code|shop', 'express_code|express'));
        foreach ($data['data'] as &$val) {
            if (empty($val['package_no'])) {
                $val['package_no'] = 1;
            }
            $val['package_name'] = $this->package_data[$val['package_no']];

            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $val['receiver_name'] = load_model('oms/SellRecordModel')->name_hidden($val['receiver_name']);
                $val['receiver_address'] = str_replace($val['receiver_addr'], '*****', $val['receiver_address']);
            }
            $val['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $val['store_code']));
        }
                
        return $this->format_ret(1, $data);
    }

    function get_deliver_detail_by_waves_id($waves_record_id) {
        $sql = "select * from oms_deliver_record_detail where waves_record_id = :waves_record_id";
        return $this->db->get_all($sql, array(':waves_record_id' => $waves_record_id));
    }

    function get_deliver_detail_id($waves_record_id) {
        $deliver_details = $this->get_deliver_detail_by_waves_id($waves_record_id);
        $deliver_detail_ids = array();
        foreach ($deliver_details as $detail) {
            $deliver_detail_ids[] = $detail['deliver_record_id'];
        }
        return implode(",", $deliver_detail_ids);
    }

    function waves_record_search_csv($sql_main, $sql_values, $filter) {
        $sql_main = " FROM oms_deliver_record a
                    LEFT JOIN oms_deliver_record_detail b ON a.sell_record_code = b.sell_record_code
                    LEFT JOIN oms_sell_record c ON a.sell_record_code = c.sell_record_code
                    LEFT JOIN oms_sell_record_detail d ON c.sell_record_code = d.sell_record_code AND b.sku = d.sku AND b.deal_code = d.deal_code AND b.is_gift = d.is_gift 
                    WHERE 1 AND is_cancel = 0 ";
        $sql_values = array();

        $ret = $this->search_terms($filter, $sql_main, $sql_values, 1);
        $sql_main = $ret['sql_main'];
        $sql_values = $ret['sql_values'];
        if (isset($filter['waves_record_ids'])) {
            $sql_main .= " AND b.waves_record_id in({$filter['waves_record_ids']})";
        }
        $sql = "SELECT  a.*,b.num,b.goods_price,b.avg_money,b.sku,b.goods_weigh,c.is_fenxiao,d.fx_amount " . $sql_main;
        $sql .= " ORDER BY a.deliver_record_id";
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, $data, '导出数据为空');
        }
        //判断是否开启导出子条码参数
        $goods_sub_barcode = oms_tb_val('sys_params', 'value', array('param_code' => 'goods_sub_barcode'));
        foreach ($data as &$value) {
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($value['sell_record_code']);
            $value = array_merge($value, $record_decrypt_info);
            //,b.goods_code,b.barcode,b.spec1_code,b.spec2_code
            $key_arr = array('goods_code', 'barcode', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'goods_name', 'price', 'weight');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
            $sql = " select *  from  oms_sell_return  where  sell_record_code = '" . $value['sell_record_code'] . "'";
            $result = ctx()->db->get_all($sql);
            $value['weight'] = $value['weight'] * $value['num'] / 1000;
            $value['is_return'] = empty($result) ? "无" : "有";
            $value['is_change_record_status'] = ($value['is_change_record'] == 1) ? "是" : "否";
            $value['is_deliver_status'] = ($value['is_deliver'] == 1) ? "已发货" : "未发货";
            //$value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=> $value['spec1_code']));
            //$value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=> $value['spec2_code']));
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            //省，市，区
            if ($value['receiver_province']) {
                $value['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_province']));
            }
            if ($value['receiver_city']) {
                $value['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_city']));
            }
            if ($value['receiver_district']) {
                $value['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_district']));
            }
            $value['goods_self_name'] = $this->get_goods_self_by_sku($value['sku'], $value['store_code']);
            //商品子条码
            if ($goods_sub_barcode == 1) {
                $sql = "SELECT barcode FROM goods_barcode_child WHERE sku = '{$value['sku']}'";
                $barcode_arr = $this->db->get_col($sql);
                $value['barcodez'] = implode(',', $barcode_arr);
            }
            //结算金额
            if ($value['is_fenxiao'] !=1 && $value['is_fenxiao'] != 2) {
                $value['fx_amount'] = $value['avg_money'];
            }
//$value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
            //$value['price'] = oms_tb_val('base_goods', 'price', array('goods_code'=> $value['goods_code']));
        }
        $ret_data['data'] = $data;
        return $this->format_ret(1, $ret_data);
    }

    function get_goods_self_by_sku($sku, $store_code) {
        $data = $this->db->get_all("select b.shelf_name from base_shelf b INNER JOIN goods_shelf s ON b.shelf_code=s.shelf_code and b.store_code = s.store_code where s.sku=:sku AND b.store_code=:store_code ", array(':sku' => $sku, ':store_code' => $store_code));
        foreach ($data as $val) {
            $goods_shelf[] = "\t" . $val['shelf_name'];
        }
        if (!empty($goods_shelf)) {
            return implode(',', $goods_shelf);
        }
        return '';
    }

    function imoprt_detail($id, $import_data, $check_express_no) {
        $record = $this->db->get_all("select deliver_record_id from oms_deliver_record where waves_record_id = :id", array(':id' => $id));
        if (empty($record)) {
            return $this->format_ret(false, array(), '波次订单明细不存在！');
        }

        $ret['data'] = array();
        $err_message = array();
        $err_num = 0;
        foreach ($import_data as $key => $val) {
            $express_code = oms_tb_val('base_express', 'express_code', array('express_name' => $val['express_name']));
            if (empty($express_code)) {
                $err_message[$key] = $val['express_name'] . '不存在;';
                $err_num ++;
                continue;
            }
            if ($check_express_no == 'true') {
                require_model('oms/SellRecordOptModel');
                $m = new SellRecordOptModel();
                $s = $m->check_express_no($express_code, $val['express_no']);
                if ($s == false) {
                    $err_message[$key] = "快递单号不合法: " . $val['express_no'];
                    $err_num ++;
                    continue;
                }
            }

            $ret = $this->edit_express($key, array('express_code' => $express_code, 'express_no' => $val['express_no']), 'import');
            if ($ret['status'] < 0) {
                $err_message[$key] = "订单号" . $key . "更新快递号失败，" . $ret['message'];
                $err_num ++;
                continue;
            }
        }
        $success_num = count($import_data) - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0) {
            $message .= ',' . '失败数量:' . $err_num;
            $file_name = $this->create_import_fail_files($err_message);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function read_csv_recode_sku($file, &$sku_arr, &$data) {
        $file = fopen($file, "r");
        $row_type = array('0' => 'sell_record_code', '1' => 'express_name', '2' => 'express_no');
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $row = $this->tran_csv($row, $row_type);
                if (!empty($row['sell_record_code'])) {
                    $sku_arr[] = $row['sell_record_code'];
                    $data[$row['sell_record_code']] = $row;
                }
            }
            $i++;
        }
        fclose($file);
    }

    private function tran_csv(&$row, $row_type) {
        $new_row = array();
        if (!empty($row)) {
            foreach ($row as $key => $val) {
//     			$val = iconv('gbk','utf-8',$val);//转化后乱码
                $val = trim(str_replace('"', '', $val));
                $new_key = isset($row_type[$key]) ? $row_type[$key] : $key;
                $new_row[$new_key] = $val;
            }
        }
        return $new_row;
    }

    function create_import_fail_files($msg) {
        $fail_top = array('订单号', '错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("deliver_record_fail" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function exist_empty_express($waves_record_id) {
        $sql = "select express_no,is_print_express from {$this->table} where waves_record_id = :waves_record_id";
        $record = $this->db->get_all($sql, array(':waves_record_id' => $waves_record_id));
        $is_exist_express_no = false;
        $is_print_express = false;
        foreach ($record as $r) {
            if (!isset($r['express_no']) || empty($r['express_no'])) {
                $is_exist_express_no = true;
            }
            if ($r['is_print_express'] != 1) {
                $is_print_express = true;
            }
        }
        return array('no_exist_express_no' => $is_exist_express_no, 'no_print_express' => $is_print_express);
    }

    function get_deliver_detail($deliver_record_id, $key_arr = array(), $is_sku = true) {
        $sql = "select * from oms_deliver_record_detail where  deliver_record_id=:deliver_record_id order by sku";
        $data = $this->db->get_all($sql, array(':deliver_record_id' => $deliver_record_id));
        foreach ($data as $key => &$val) {
            if ($is_sku === TRUE) {
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku']);
                $val = array_merge($sku_info, $val);
            }
            if (!empty($key_arr)) {
                $data[$key] = $this->get_key_data($val, $key_arr);
            }
        }
        return $data;
    }

    function get_key_data($data, $key_arr) {
        $new_data = array();
        foreach ($key_arr as $key) {
            $new_data[$key] = isset($data[$key]) ? $data[$key] : '';
        }
        return $new_data;
    }

    function get_deliver_id_list_by_waves_code($waves_code) {
        $sql = "select deliver_record_id from
            oms_waves_record w
            INNER JOIN   oms_deliver_record d ON w.waves_record_id=d.waves_record_id
           where  record_code=:record_code AND d.is_cancel=0";
        $sql_value = array(':record_code' => $waves_code);
        $data = $this->db->get_all($sql, $sql_value);
        $id_list = array();
        foreach ($data as $val) {
            $id_list[] = $val['deliver_record_id'];
        }
        return $this->format_ret(1, $id_list);
    }

    function get_deliver_id_list_by_waves_id($waves_id) {
        $sql = "SELECT deliver_record_id FROM oms_deliver_record WHERE waves_record_id=:waves_record_id AND is_cancel=0";
        $sql_value = array(':waves_record_id' => $waves_id);
        $data = $this->db->get_all($sql, $sql_value);
        $id_list = array();
        foreach ($data as $val) {
            $id_list[] = $val['deliver_record_id'];
        }
        return $id_list;
    }

    /**
     * 验证是否获取过云栈
     * @param $waves_record_id_arr
     * @return array 返回获取过云栈的单号
     */
    function check_all_status_multi($waves_record_id_arr) {
        $deliver_record_id_arr = array();
        foreach ($waves_record_id_arr as $waves_record_id) {
            $id_list = $this->get_deliver_id_list_by_waves_id($waves_record_id);
            $deliver_record_id_arr = array_merge($deliver_record_id_arr, $id_list);
        }
        $deliver_record_id_str = "'" . implode("','", $deliver_record_id_arr) . "'";
        $check = $this->check_status($deliver_record_id_str);
        return $check;
    }

    /**
     * @todo 阿拉伯数字转打字中文数字
     */
    function convert_num_to_cn($num) {
        if (!is_numeric($num)) {
            return '';
        }
        $char = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        $unit = array('', '拾', '佰', '仟', '', '万', '亿', '兆');
        $retval = '元';
        //小数部分
        $pos = strpos($num, '.');
        if ($pos) {
            $sub = substr($num, $pos + 1, 3);
            for ($i = 0; $i < 3; $i++) {
                $decs[] = intval(mb_substr($sub, $i, 1));
            }
            $retval .= ($decs['0'] == 0 && $decs['1'] == 0 && $decs['2'] == 0) ? " " : "{$char[$decs['0']]}角{$char[$decs['1']]}分{$char[$decs['2']]}厘";
        }
        //整数部分
        $str = strrev(intval($num));
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            $out[$i] = $char[$str[$i]];
            $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
            if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                $out[$i] = '';
            }
            if ($i % 4 == 0) {
                $out[$i] .= $unit[4 + floor($i / 4)];
            }
        }
        $retval = join('', array_reverse($out)) . $retval;
        return $retval;
    }

    /**
     * @todo 检测订单是否获取了云栈热敏
     */
    function check_status($ids) {
        $sql = "SELECT sell_record_code,express_data FROM oms_deliver_record WHERE deliver_record_id IN($ids)";
        $ret = $this->db->get_all($sql);
        foreach ($ret as $value) {
            if (!empty($value['express_data'])) {
                $data[] = $value['sell_record_code'];
            }
        }
        if (empty($data)) {
            return $this->format_ret(1);
        } else {
            foreach ($data as $key => &$value) {
                if ($key % 4 == 0 && $key != 0) {
                    $value = '<br>' . $value;
                }
            }
            $data = implode('，', $data);
            return $this->format_ret(-1, $data);
        }
    }

    /**
     * @todo 查询波次单中订单对应快递是否使用云打印
     * @param string $record_ids
     * @param int $id_type
     */
    function check_express_type($record_ids, $id_type) {
        if ($id_type == 1) {
            $wave_data = load_model('oms/WavesRecordModel')->get_record_by_id($record_ids);
            $ret_list = $this->get_deliver_id_list_by_waves_code($wave_data['record_code']);
            $ids = deal_array_with_quote($ret_list['data']);
            //var_dump($ids);die;
        } else {
            $ids = $record_ids;
        }
        $sql = "SELECT DISTINCT r2.express_code,r2.rm_id FROM oms_deliver_record r1,base_express r2
                WHERE r1.express_code = r2.express_code AND r1.deliver_record_id IN ({$ids}) AND r2.print_type=2";
        $ret = $this->db->get_all($sql);
        //var_dump($ret);die;
        if (empty($ret)) { //未启用云栈热敏
            return $this->format_ret(-1, '未启用云栈热敏');
        }
        foreach ($ret as $rm) {
            $r[] = $rm['rm_id'];
        }
        $_r = deal_array_with_quote($r);
        //var_dump($_r);die;
        $template_sql = "SELECT COUNT(1) FROM sys_print_templates WHERE print_templates_id IN({$_r}) AND is_buildin = 3";
        $template_count = $this->db->get_value($template_sql);
        //var_dump($template_count);die;
        //var_dump(count($ret));var_dump($template_count);die;
        if (count($ret) != $template_count) {

            $waybill_template_sql = "SELECT COUNT(1) FROM sys_print_templates WHERE print_templates_id IN({$_r}) AND is_buildin = 2";
            if (count($ret) != $template_count) {
                return $this->format_ret(-1, 'oldcloud');
            } else {
                return $this->format_ret(-1, '');
            }
        } else {
            return $this->format_ret(1, 'cloud');
        }
    }

    /**
     * 修改扫描验货商品数量
     */
    function update_goods_scan_num($filter) {
        //查询发货单明细
        $sql = "SELECT sell_record_code,num,scan_num,sku FROM oms_deliver_record_detail WHERE deliver_record_detail_id = :deliver_record_detail_id ";
        $data = $this->db->get_row($sql, array(':deliver_record_detail_id' => $filter['deliver_record_detail_id']));
        if ($data['scan_num'] == $filter['scan_num']) {
            return $this->format_ret(1);
        }
        $surplus_num = $data['num'] - $filter['scan_num'];
        if ($surplus_num < 0) {
            return $this->format_ret(-1, '', '扫描数量大于商品数量');
        }
        require_model('oms/SellRecordOptModel');
        $mdlSell = new SellRecordOptModel();

        //修改明细表扫描数量
        $sql = "UPDATE oms_deliver_record_detail SET scan_num = :scan_num WHERE deliver_record_detail_id = :deliver_record_detail_id";
        $ret = $this->query($sql, array(':scan_num' => $filter['scan_num'], ':deliver_record_detail_id' => $filter['deliver_record_detail_id']));
        if ($ret['status'] != 1) {
            return $ret;
        }
        $data['barcode'] = oms_tb_val('goods_sku', 'barcode', array('sku' => $data['sku']));
        $mdlSell->add_action($data['sell_record_code'], '扫描出库,修改扫描数量', '条码：' . $data['barcode'] . '，扫描数量由' . $data['scan_num'] . '修改为' . $filter['scan_num']);
        return $this->format_ret(1);
    }

    /**
     * @todo 通过判断是否开启多包裹来获取标准打印数据
     */
    function get_standard_print_data($deliver_record_ids, $fields = "*", $print_time = 0, $key_required = 0) {
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        //未开启多包裹
        if ($ret_pararm['is_more_deliver_package'] == 0) {
            return $this->get_deliver_record_by_ids_in_field($deliver_record_ids, $fields, $print_time, $key_required);
        } else {
            //开启多包裹，通过订单号、配送方式、包裹号获取快递单标准数据
            $deliver_data = $this->get_deliver_record_by_ids_in_field($deliver_record_ids, 'deliver_record_id, sell_record_code, express_code, package_no', 0, 1);
            $this->get_package_data($deliver_data);
            return $deliver_data;
        }
    }

    function get_package_data(&$deliver_data) {
        foreach ($deliver_data as &$data) {
            $sql = "SELECT express_no, express_data FROM oms_deliver_record_package where sell_record_code = :sell_record_code AND express_code = :express_code AND package_no = :package_no AND express_no <> '' AND express_data <> ''";
            $sql_value = array(":sell_record_code" => $data['sell_record_code'], ":express_code" => $data['express_code'], ":package_no" => $data['package_no']);
            $package_data = $this->db->get_row($sql, $sql_value);
            $data['express_no'] = $package_data['express_no'];
            $data['express_data'] = $package_data['express_data'];
        }
    }

    /**
     * @todo 处理最后的打印数据
     */
    function handle_return_data($standard_data, $custom_print_data = NULL) {
        $combine_print_data = array();
        if (!empty($standard_data)) {
            foreach ($standard_data as $deliver_record_id => $print_data) {
                $express_data = json_decode($print_data['express_data'], TRUE);
                $combine_print_data[$deliver_record_id]['documentID'] = $express_data['waybill_code'];
                $combine_print_data[$deliver_record_id]['contents'][0] = json_decode($express_data['print_data'], TRUE);
                if (!empty($custom_print_data)) {
                    $combine_print_data[$deliver_record_id]['contents'][1] = $custom_print_data[$deliver_record_id];
                }
                $this->cloud_print_waybill_code[] = $express_data['waybill_code'];
                $this->success_num++;
            }
            $print_data_json = json_encode(array_values($combine_print_data), JSON_BIGINT_AS_STRING, 512);
        } else {
            $print_data_json = json_encode(array());
        }
        $return_data = array('print_data_json' => $print_data_json);
        return $return_data;
    }

    /**
     * @todo 分批获取打印数据
     */
    function get_cloud_print_express_data($deliver_record_ids) {
        $this->success_num = 0;
        $this->fail_num = 0;
        $chunk_deliver_record_id_arr = array_chunk($deliver_record_ids, 10);
        foreach ($chunk_deliver_record_id_arr as $deliver_record_ids) {
            $print_data[] = $this->get_combine_print_data($deliver_record_ids);
        }
        return array('print_data' => $print_data, 'express_no' => $this->cloud_print_waybill_code, 'success_num' => $this->success_num, 'fail_num' => $this->fail_num, 'fail_sell_record_code' => $this->fail_sell_record_code);
    }

    /**
     * @todo 获取合并的打印数据
     */
    function get_combine_print_data($deliver_record_ids) {
        $custom_print_template = $this->get_print_templates_by_deliver_record_id($deliver_record_ids[0]);
        //无自定义区域需要打印
        if (empty($custom_print_template['template_val']) || empty($custom_print_template['template_body'])) {
            //标准打印数据
            $ret = $this->get_standard_print_data($deliver_record_ids, "deliver_record_id, sell_record_code, express_code, express_data, express_no", '', 1);
            $standard_data = $this->get_standard_area_data($ret);
            $print_data = $this->handle_return_data($standard_data);
            return $print_data;
        }
        //所有需要被打印的打印项key
        $total_custom_area_key = $custom_print_template['template_val'];
        //是否需要打印商品信息
        //初始化传值变量
        $print_goods_info_data = array();
        $single_goods_a_line = '';
        if (preg_match('/goods_infos/i', $total_custom_area_key) == 1 && !empty($custom_print_template['template_body_replace_key']) && !empty($custom_print_template['template_body_replace'])) {
            $selected_printr_setting = json_decode($custom_print_template['template_body_replace_key'], TRUE);
            $single_goods_a_line = $selected_printr_setting['single_goods_a_line']; //每行一个商品
            $print_goods_info = $selected_printr_setting['goods_info']; //配置的需要打印的商品信息
            $input_goods_info = $custom_print_template['template_body_replace']; //客户配置的打印样式
            //获取商品信息
            $print_goods_info_data = $this->get_custome_area_goods_info($deliver_record_ids, $print_goods_info, $input_goods_info);
        }
        //处理商品信息key，仅保留基本打印项
        $basic_custom_area_key = $this->deal_template_val($total_custom_area_key);
        //获取打印项的数据
        $filp_record = array_values($this->print_fields_default['record']);
        $filp_detail = array_values($this->print_fields_default['detail'][0]);
        foreach ($basic_custom_area_key as $custom_key) {
            if (in_array($custom_key, $filp_record)) {
                $record_key[] = $custom_key;
            }
            if (in_array($custom_key, $filp_detail)) {
                $detail_key[] = $custom_key;
            }
        }
        $total_print_data = $this->get_print_data_new($deliver_record_ids, $record_key, $detail_key);
        $record = !empty($total_print_data['main_record']) ? $total_print_data['main_record'] : $total_print_data['deliver_record_ids'];
        $custom_print_data = $this->get_custome_print_data($record, $custom_print_template['template_body'], $print_goods_info_data, $single_goods_a_line);
        $combine_print_data = $this->combine_print_data($deliver_record_ids, $custom_print_data);
        return $combine_print_data;
    }

    /**
     * @todo 获取自定义区域的打印数据
     */
    function get_custome_print_data($record, $custom_template_url, $print_goods_info = NULL, $single_goods_a_line = NULL) {
        $custom_print_data = array();
        foreach ($record as $deliver_record_id => $value) {
            $custom_print_data[$deliver_record_id]['templateURL'] = $custom_template_url;
            $custom_print_data[$deliver_record_id]['data'] = !empty($value) && is_array($value) ? $value : '';
            if (!empty($print_goods_info)) {
                if ($single_goods_a_line == 1) {
                    foreach ($print_goods_info[$deliver_record_id] as $k => $v) {
                        $custom_print_data[$deliver_record_id]['data']['goods_infos'][]['goods_info'] = $v;
                    }
                } else {
                    $custom_print_data[$deliver_record_id]['data']['goods_infos'][0]['goods_info'] = implode(' ', $print_goods_info[$deliver_record_id]);
                }
            }
        }
        return $custom_print_data;
    }

    /**
     * @todo 合并自定义区域和标准区域打印数据
     */
    function combine_print_data($deliver_record_ids, $custom_print_data) {
        $ret = $this->get_standard_print_data($deliver_record_ids, "deliver_record_id, sell_record_code, express_code, express_data,express_no", '', 1);
        $standard_data = $this->get_standard_area_data($ret);
        $return_data = $this->handle_return_data($standard_data, $custom_print_data);
        return $return_data;
    }

    /**
     * @todo 获取自定义区域非商品信息的打印数据
     */
    function get_print_data_new($deliver_record_ids, $record_key, $detail_key) {
        if (!empty($record_key)) {
            if (in_array('print_time', $record_key)) {
                $print_time = 1;
                $flip = array_flip($record_key);
                unset($record_key[$flip['print_time']]);
            } else {
                $print_time = 0;
            }
            if (in_array('sender_shop_name', $record_key)) {
                $record_key = array_merge(array('shop_code'), $record_key);
                $flip = array_flip($record_key);
                unset($record_key[$flip['sender_shop_name']]);
            }
            $record_key_str = implode(',', array_merge(array('deliver_record_id'), $record_key));
            $main_record = $this->get_deliver_record_by_ids_in_field($deliver_record_ids, $record_key_str, $print_time, 1);
        }
        if (!empty($detail_key)) {
            $detail_key_str = implode(',', array_merge(array('deliver_record_id'), $detail_key));
            $main_detail = $this->get_deliver_detail_by_ids_in_field($deliver_record_ids, 'deliver_record_id', $detail_key_str, 1);
        }
        return array('main_record' => $main_record, 'main_detail' => $main_detail, 'deliver_record_ids' => array_flip($deliver_record_ids));
    }

    /**
     * @todo 通过deliver_record_id获取发货表指定字段的数据
     */
    function get_deliver_record_by_ids_in_field($deliver_record_ids, $fields = "*", $print_time = 0, $key_required = 0) {
        $deliver_record_id_str = deal_array_with_quote($deliver_record_ids);
        $time_sql = $print_time == 1 ? ",NOW() AS print_time " : '';
        //过滤waves_record_code
        $fields_arr = explode(',', $fields);
        if (in_array('waves_record_code', $fields_arr)) {
            $fields_arr = array_merge(array('waves_record_id'), $fields_arr);
        }
        $new_fields_arr = array_filter($fields_arr, function($v, $k) {
            return $v != 'waves_record_code';
        });
        $fields = implode(',', $new_fields_arr);
        $sql = "SELECT {$fields}{$time_sql} FROM {$this->table} WHERE deliver_record_id IN ({$deliver_record_id_str})";
        $ret = $this->db->get_all($sql);
        if ($key_required === 1) {
            foreach ($ret as &$value) {
                if (isset($value['shop_code']) && !empty($value['shop_code'])) {
                    $value['sender_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                    unset($value['shop_code']);
                }
                if (in_array('waves_record_code', $fields_arr)) {
                    $value['waves_record_code'] = oms_tb_val('oms_waves_record', 'record_code', array('waves_record_id' => $value['waves_record_id']));
                }
                if (isset($value['pay_code']) && !empty($value['pay_code'])) {
                    $value['pay_code'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $value['pay_code']));
                }
                $new_ret[$value['deliver_record_id']] = $value;
            }
            return $new_ret;
        }
        return $ret;
    }

    /**
     * @todo 通过deliver_record_id获取发货明细表指定字段的数据
     */
    function get_deliver_detail_by_ids_in_field($deliver_record_ids, $ids_type = "deliver_record_id", $fields = "*", $key_required = 0) {
        $deliver_ids_str = deal_array_with_quote($deliver_record_ids);
        $sql = "SELECT {$fields} FROM {$this->detail_table} WHERE {$ids_type} IN ({$deliver_ids_str})";
        $ret = $this->db->get_all($sql);
        if ($key_required === 1) {
            foreach ($ret as $value) {
                $new_ret[$value['deliver_record_id']] = $value;
            }
            return $new_ret;
        }
        return $ret;
    }

    /**
     * @todo 处理商品信息
     */
    function deal_template_val($total_custom_area_key) {
        $basic = str_replace('goods_infos[i].goods_info', '', $total_custom_area_key);
        $dealed_arr = array_filter(explode(',', $basic));
        return $dealed_arr;
    }

    /**
     * @todo 获取标准页面的打印信息
     */
    function get_standard_area_data($ret) {
        $standard_print_data = array();
        foreach ($ret as $key => $value) {
            if (empty($value['express_data'])) {
                $this->fail_sell_record_code[$this->fail_num] = $value['sell_record_code'];
                $this->fail_num++;
                continue;
            } else {
                $express_data = json_decode($value['express_data'], true);
            }
            if (empty($express_data['object_id']) || $express_data['waybill_code'] != $value['express_no'] || $value['express_no'] == '') {
                $this->fail_sell_record_code[$this->fail_num] = $value['sell_record_code'];
                $this->fail_num++;
                continue;
            }
            $standard_print_data[$key]['express_data'] = json_encode($express_data, JSON_UNESCAPED_SLASHES);
        }
        return $standard_print_data;
    }

    /**
     * @todo 获取自定义区域的商品信息
     */
    function get_custome_area_goods_info($deliver_record_ids, $print_goods_info, $input_goods_info) {
        $field = 'deliver_record_id, goods_code, sku, barcode AS goods_barcode, goods_price, num, platform_spec';
        $detail_data = $this->get_deliver_detail_by_ids_in_field($deliver_record_ids, 'deliver_record_id', $field);
        $record_data = $this->get_deliver_record_by_ids_in_field($deliver_record_ids, 'deliver_record_id, store_code');
        $new_detail = array();
        foreach ($record_data as $key => $record) {
            $new_record[$record['deliver_record_id']] = $record['store_code'];
        }
        foreach ($detail_data as $key => $detail) {
            $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'barcode', 'remark', 'weight','category_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $detail['goods_barcode'] = isset($sku_info['barcode']) && !empty($sku_info['barcode']) ? $sku_info['barcode'] : $detail['goods_barcode'];
            $new_detail[$detail['deliver_record_id']][$key] = array_merge($detail, $sku_info);
            $new_detail[$detail['deliver_record_id']][$key]['shelf_code'] = $this->get_shelf_name($detail['sku'], $new_record[$detail['deliver_record_id']]);
            $new_detail[$detail['deliver_record_id']][$key]['sort_shelf_code'] = $this->get_shelf_code($detail['sku'], $new_record[$detail['deliver_record_id']]);
            $shelf_code_arr[$detail['deliver_record_id']][]['shelf_code'] = $new_detail[$detail['deliver_record_id']][$key]['shelf_code'];
            $goods_code_arr[$detail['deliver_record_id']][]['goods_code'] = $new_detail[$detail['deliver_record_id']][$key]['goods_code'];
            $goods_barcode_arr[$detail['deliver_record_id']][]['goods_barcode'] = $new_detail[$detail['deliver_record_id']][$key]['goods_barcode'];
if($new_detail[$detail['deliver_record_id']][$key]['sort_shelf_code']==''){
    $new_detail[$detail['deliver_record_id']][$key]['test_sort']='00000000'.$new_detail[$detail['deliver_record_id']][$key]['goods_code'].$new_detail[$detail['deliver_record_id']][$key]['goods_barcode'];
}else{
    $new_detail[$detail['deliver_record_id']][$key]['test_sort']=$new_detail[$detail['deliver_record_id']][$key]['sort_shelf_code'].$new_detail[$detail['deliver_record_id']][$key]['goods_code'].$new_detail[$detail['deliver_record_id']][$key]['goods_barcode'];
}

        }
        //快递单打印，商品信息按照吊牌价大小排序 不然按照库位排序
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code('order_by_goods_sprice');

        /*if ($cfg['order_by_goods_sprice'] == 1) {
            foreach ($new_detail as &$detail) {
                $goods_price = array();
                foreach ($detail as $d) {
                    $goods_price[] = $d['goods_price'];
                }
                array_multisort($goods_price, SORT_DESC, $detail);
            }
        } else {
            foreach ($new_detail as $deliver_record_id => &$detail) {
                array_multisort($shelf_code_arr[$deliver_record_id], SORT_ASC, $goods_code_arr[$deliver_record_id], SORT_ASC, $goods_barcode_arr[$deliver_record_id], SORT_ASC, $detail);
            }
        }*/
        switch($cfg['order_by_goods_sprice']){
            case 0:
                foreach ($new_detail as  &$detail) {
                    $shelfcode_goodscode_arr = array();
                    foreach ($detail as $d) {
                        $shelfcode_goodscode_arr[] = $d['test_sort'];
                    }
                    array_multisort($shelfcode_goodscode_arr,SORT_ASC,SORT_STRING , $detail);
                }
                break;
            case 1:
                foreach ($new_detail as &$detail) {
                    $goods_price = array();
                    foreach ($detail as $d) {
                        $goods_price[] = $d['goods_price'];
                    }
                    array_multisort($goods_price, SORT_ASC, $detail);
                }
                break;
            case 2:
                foreach ($new_detail as &$detail) {
                    $goods_price = array();
                    foreach ($detail as $d) {
                        $goods_price[] = $d['goods_price'];
                    }
                    array_multisort($goods_price, SORT_DESC, $detail);
                }
                break;
        }
        foreach ($new_detail as &$detail) {
            $arr = array();
            foreach ($detail as $d) {
                if (!isset($arr[$d['sku']])) {
                    $arr[$d['sku']] = $d;
                } else {
                    $arr[$d['sku']]['num'] += $d['num'];
                }
            }
            $detail = $arr;
        }
        $output_goods_info = $this->deal_print_goods_info_str($new_detail, $print_goods_info, $input_goods_info);
        return $output_goods_info;
    }

    /**
     * @todo 处理商品打印信息字符串
     */
    function deal_print_goods_info_str($new_detail, $print_goods_info, $input_goods_info) {
        $output_goods_info = array();
        $print_goods_info_arr = explode(',', $print_goods_info);
        foreach ($new_detail as $deliver_record_id => $detail) {
            foreach ($detail as $key => $single_goods_info) {
                $output_goods_info[$deliver_record_id][$key] = $input_goods_info;
                foreach ($single_goods_info as $goods_info_key => $goods_info) {
                    $goods_info_key = $goods_info_key == 'remark' ? 'sku_remark' : $goods_info_key;
                    $goods_info_key = $goods_info_key == 'weight' ? 'sku_weight' : $goods_info_key;
                    if (!in_array($goods_info_key, $print_goods_info_arr)) {
                        continue;
                    }
                    $output_goods_info[$deliver_record_id][$key] = preg_replace("/{" . $goods_info_key . "}/", $goods_info, $output_goods_info[$deliver_record_id][$key]);
                }
            }
        }
        return $output_goods_info;
    }

    /**
     * @todo 更新云打印打印状态
     */
    function update_cloud_print_express_status($express_no_array, $fail_num) {
        foreach ($express_no_array['printStatus'] as $value) {
            $total_express_no[] = $value['documentID'];
            if ($value['status'] == 'success') {
                $success_express_no[] = $value['documentID'];
            }
        }
        $total_express_no_num = count($total_express_no);
        if (empty($success_express_no)) {
            $message = '打印完成，打印成功0单，打印失败' . $total_express_no_num + $fail_num . '单';
            $data = array('success' => 0, 'fail' => $total_express_no_num + $fail_num);
            return $this->format_ret(1, $data, $message);
        }
        $success_express_no_str = deal_array_with_quote($success_express_no);
        $ret_pararm = load_model('sys/SysParamsModel')->get_val_by_code(array('is_more_deliver_package'));
        //未开启多包裹
        if ($ret_pararm['is_more_deliver_package'] == 0) {
            $sql = "SELECT deliver_record_id FROM oms_deliver_record WHERE express_no IN({$success_express_no_str}) AND (express_data <> '' OR express_data <> NULL)";
            $success_deliver_record_id = $this->db->get_all($sql);
        } else {
            $sql = "SELECT sell_record_code FROM oms_deliver_record_package WHERE express_no IN({$success_express_no_str})";
            $success_sell_record_code = $this->db->get_all($sql);
            foreach ($success_sell_record_code as $code) {
                $code_arr[] = $code['sell_record_code'];
            }
            $success_sell_record_code_str = deal_array_with_quote($code_arr);
            $sql = "SELECT deliver_record_id FROM oms_deliver_record WHERE sell_record_code IN({$success_sell_record_code_str}) AND (express_no <> '' OR express_no <> NULL) AND (express_data <> '' OR express_data <> NULL)";
            $success_deliver_record_id = $this->db->get_all($sql);
        }
        if (empty($success_deliver_record_id)) {
            $message = '打印完成，打印成功0单，打印失败' . $total_express_no_num + $fail_num . '单';
            $data = array('success' => 0, 'fail' => $total_express_no_num + $fail_num);
            return $this->format_ret(1, $data, $message);
        }
        foreach ($success_deliver_record_id as $deliver_record_id) {
            $this->print_update_status($deliver_record_id['deliver_record_id'], 'express');
        }
        $success_express_no_num = count($success_express_no);
        $fail_express_num = $total_express_no_num - $success_express_no_num + $fail_num;
        $message = '打印完成，打印成功' . $success_express_no_num . '单，打印失败' . $fail_express_num . '单';
        $data = array('success' => $success_express_no_num, 'fail' => $fail_express_num);
        return $this->format_ret(1, $data, $message);
    }

    /**
     * @todo 通过发货单id获取模板id
     */
    function get_print_templates_by_deliver_record_id($deliver_record_id, $field = "*") {
        //获取模板id
        $sql = "SELECT
                        r2.rm_id
                FROM
                        oms_deliver_record r1,
                        base_express r2
                WHERE
                        r1.express_code = r2.express_code
                AND deliver_record_id = :deliver_record_id";
        $sql_value = array(":deliver_record_id" => $deliver_record_id);
        $ret = $this->db->get_row($sql, $sql_value);
        //获取模板数据
        $template_sql = "SELECT {$field} FROM sys_print_templates WHERE print_templates_id = :print_templates_id";
        $template_sql_value = array(":print_templates_id" => $ret['rm_id']);
        $ret = $this->db->get_row($template_sql, $template_sql_value);
        return $ret;
    }

    /**
     * @todo 获取快递默认绑定的打印机
     */
    function check_printer($deliver_record_id_str) {
        $deliver_record_id_arr = explode(',', $deliver_record_id_str);
        $ret = $this->get_print_templates_by_deliver_record_id($deliver_record_id_arr[0], "print_templates_id, printer");
        if ($ret['printer'] == '' || $ret['printer'] == '无') {
            return $this->format_ret(-1, $ret['print_templates_id']);
        } else {
            return $this->format_ret(1, $ret['printer']);
        }
    }

    /**
     * 扫描订单号或波次单号
     * @param string $record_waves_code 订单号|波次单号
     * @param int $is_check_deliver 是否检查订单号
     * @return array 波次数据
     */
    function get_record_by_record_waves($record_waves_code, $is_check_deliver = 1) {
        if ($is_check_deliver == 1) {
            //是否是发货单
            $sql = "SELECT * FROM oms_deliver_record WHERE sell_record_code = :sell_record_code";
            $deliver_data = $this->db->get_row($sql, array(':sell_record_code' => $record_waves_code));
            if (!empty($deliver_data)) {
                if ($deliver_data['is_cancel'] == 1) {
                    return $this->format_ret(-1, '', '订单已取消');
                }
                //根据订单号查询波次号
                $sql = "SELECT record_code FROM oms_waves_record WHERE waves_record_id = :waves_record_id";
                $record_waves_code = $this->db->get_value($sql, array('waves_record_id' => $deliver_data['waves_record_id']));
            }
        }
        //是否是波次单
        $sql = "SELECT * FROM oms_waves_record WHERE record_code = :record_code";
        $waves_data = $this->db->get_row($sql, array(':record_code' => $record_waves_code));
        if (empty($waves_data)) {
            $msg = $is_check_deliver == 1 ? '订单或波次单不存在' : '波次单不存在';
            return $this->format_ret(-1, '', $msg);
        }
        if ($waves_data['is_cancel'] == 1) {
            return $this->format_ret(-1, '', '波次单已取消');
        }
        if ($waves_data['is_deliver'] == 1) {
            return $this->format_ret(-1, '', '波次单已发货');
        }
        //查询拣货总数量
        $sum_picking_num = $this->get_by_detail_sum_num($waves_data);
        if ($waves_data['valide_goods_count'] == $sum_picking_num) {
            return $this->format_ret(-1, array('record_code' => $record_waves_code), '波次单:' . $record_waves_code . '二次拣货完毕');
        }
        $waves_data['sum_picking_num'] = $sum_picking_num;
        return $this->format_ret(1, $waves_data);
    }

    //查询拣货总数量
    function get_by_detail_sum_num($waves_data) {
        $sql = "SELECT sum(picking_num) AS sum_picking_num FROM oms_deliver_record dr,oms_deliver_record_detail rd 
                WHERE dr.deliver_record_id=rd.deliver_record_id AND dr.is_cancel=0 AND dr.waves_record_id = :waves_record_id";
        $sum_picking_num = $this->db->get_value($sql, array(':waves_record_id' => $waves_data['waves_record_id']));
        return $sum_picking_num;
    }

    function get_check_barcode_data($data) {
        //解析条码
        $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($data['barcode']);
        if (empty($sku_data)) {
            return $this->format_ret(-1, '', '系统中不存在该条码');
        }

        $waves_record_code = $data['record_code'];
        //波次单
        $sql = "SELECT * FROM oms_waves_record WHERE record_code = :record_code";
        $waves_data = $this->db->get_row($sql, array(':record_code' => $waves_record_code));
        if ($waves_data['is_cancel'] == 1) {
            return $this->format_ret(-1, '', '波次单已取消');
        }
        if ($waves_data['is_deliver'] == 1) {
            return $this->format_ret(-1, '', '波次单已发货');
        }
        //发货订单明细
        $sql = "SELECT rd.* FROM oms_deliver_record dr,oms_deliver_record_detail rd WHERE dr.deliver_record_id=rd.deliver_record_id AND dr.is_cancel=0 AND dr.waves_record_id = :waves_record_id AND rd.sku = :sku";
        $record_detail_data = $this->db->get_all($sql, array(':waves_record_id' => $waves_data['waves_record_id'], ':sku' => $sku_data['sku']));
        $deliver_record_detail = '';
        $message = '';
        //循环出符合条件的sku，并更新
        foreach ($record_detail_data as $val) {
            if ($val['num'] == $val['picking_num']) {
                $message = $data['barcode'] . '条码，该商品已扫描完毕';
                continue;
            } else {
                $message = '';
            }
            $deliver_record_detail = $val;
            //修改发货订单拣货数量
            $data = array(
                'picking_num' => $val['picking_num'] + 1
            );
            $this->update_exp('oms_deliver_record_detail', $data, array('deliver_record_detail_id' => $val['deliver_record_detail_id']));
            break;
        }
        if (empty($deliver_record_detail)) {
            if (empty($message)) {
                return $this->format_ret('-1', array('barcode' => $data['barcode']), $data['barcode'] . '条码，没有找到该商品');
            } else {
                return $this->format_ret('-1', array('barcode' => $data['barcode']), $message);
            }
        }
        //拣货总数量
        $sum_picking_num = $this->get_by_detail_sum_num($waves_data);
        //查询发货单的序号
        $sql = "SELECT sort_no FROM oms_deliver_record WHERE deliver_record_id = :deliver_record_id";
        $deliver_data = $this->db->get_row($sql, array(':deliver_record_id' => $deliver_record_detail['deliver_record_id']));
        $deliver_data['sum_picking_num'] = $sum_picking_num;
        $deliver_data['valide_goods_count'] = $waves_data['valide_goods_count'];
        $deliver_data['record_code'] = $waves_data['record_code'];
        $deliver_data['barcode'] = $sku_data['barcode'];
        return $this->format_ret(1, $deliver_data, '');
    }

    function get_by_detail_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = " LEFT JOIN oms_deliver_record a ON a.deliver_record_id = b.deliver_record_id ";
        $sql_main = "FROM oms_deliver_record_detail b {$sql_join} WHERE b.waves_record_id = :waves_record_id AND a.is_cancel = 0 ";
        $sql_values[':waves_record_id'] = $filter['waves_record_id'];

        //订单编号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $sql_main .= " AND a.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND b.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND b.barcode LIKE :barcode ";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        //是否有差异
        if (isset($filter['keywords']) && $filter['keywords'] == 'yes') {
            $sql_main .= " AND b.num <> b.picking_num ";
        }
        //是否有差异
        if (isset($filter['keywords']) && $filter['keywords'] == 'no') {
            $sql_main .= "  AND b.num = b.picking_num ";
        }
        $select = " b.*,a.sort_no,a.store_code";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
//        filter_fk_name($data['data'], array('goods_code|goods_code', 'spec1_code|spec1_code', 'spec2_code|spec2_code'));
        //获取规格1规格2 别名
        $arr_spec1 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec1');
        $arr_spec2 = load_model('sys/SysParamsModel')->get_val_by_code('goods_spec2');
        //根据sku和store_code获取库位(开始)
        $shelf_arr = array();
        if (!empty($data['data'])) {
            $sku_arr = array_unique(array_column($data['data'], 'sku'));
            $store_arr = array_unique(array_column($data['data'], 'store_code'));
            if (!empty($sku_arr) && !empty($store_arr)) {
                $shelf_arr_ret = load_model('prm/GoodsShelfModel')->get_shelf_name($store_arr, $sku_arr, 'store_code,sku');
                if ($shelf_arr_ret['status'] == 1) {
                    $shelf_arr = $shelf_arr_ret['data'];
                }
            }
        }
        //(结束)
        foreach ($data['data'] as $key => &$val) {
            $sku_arr = array('spec1_name', 'spec2_name', 'barcode', 'goods_name');
            $goods_data = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $sku_arr);
            $val['spec1_name'] = $goods_data['spec1_name'];
            $val['spec2_name'] = $goods_data['spec2_name'];
            $val['barcode'] = $goods_data['barcode'];
            $val['goods_name'] = $goods_data['goods_name'];
            $val['spec_data'] = $arr_spec1['goods_spec1'] . ':' . $val['spec1_name'] . ';' . $arr_spec2['goods_spec2'] . ':' . $val['spec2_name'];
            $key = $val['store_code'] . ',' . $val['sku'];
            $val['shelf_name'] = isset($shelf_arr[$key]) ? $shelf_arr[$key]['shelf_name'] : '';
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //修改二次拣货数量
    function edit_picking_num($filter) {
        //发货订单明细
        $sql = "SELECT * FROM oms_deliver_record_detail WHERE deliver_record_detail_id = :deliver_record_detail_id AND sku = :sku";
        $detail = $this->db->get_row($sql, array(':deliver_record_detail_id' => $filter['deliver_record_detail_id'], 'sku' => $filter['sku']));
        if ($detail['num'] < $filter['picking_num']) {
            return $this->format_ret(-1, '', '商品拣货数不能大于商品数量');
        }
        if ($filter['picking_num'] == $detail['picking_num']) {
            return $this->format_ret(2, '', '');
        }
        $ret = $this->update_exp('oms_deliver_record_detail', array('picking_num' => $filter['picking_num']), array('deliver_record_detail_id' => $filter['deliver_record_detail_id']));
        return $ret;
    }

    //拼接地址
    function get_join_addr($data) {
        $receiver_city = isset($data['receiver_city']) && !empty($data['receiver_city']) ? $data['receiver_city'] : '';
        $receiver_street = isset($data['receiver_street']) && !empty($data['receiver_street']) ? $data['receiver_street'] : '';
        if (in_array($receiver_city, array('省直辖县级行政区划', '自治区直辖县级行政区划'))) {
            $data['receiver_city'] = '';
        } else if (in_array($receiver_street, array('区直辖村模拟镇'))) {
            $data['receiver_street'] = '';
        }
        return $data;
    }

    //验证详细地址 去掉直辖市
    function check_address($name) {
        $check_arr = array(
            '省直辖县级行政区划',
            '自治区直辖县级行政区划',
            '区直辖村模拟镇'
        );
        foreach ($check_arr as $val) {
            if (stripos($name, $val) !== false) {
                $name = str_ireplace($val, '', $name);
            }
        }
        return $name;
    }

    function get_unique_code_scan_temporary_log($sell_record_code) {
//        $sql = "select * from oms_sell_record_detail where sell_record_code=:sell_record_code";
//        $ret = $this->db->get_all($sql,array(':sell_record_code'=>$sell_record_code));
//        $detail = array();
//        foreach ($ret as $key => $value) {
//            if($value['is_gift'] == 1){
//                $sku_arr = array( 'goods_code','goods_name');
//                $detail[$key] = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $sku_arr);
//                 $detail[$key]['num'] = $value['num'];
//            }
//        }
        $sql = "select t.* from unique_code_scan_temporary_log l
                INNER JOIN goods_unique_code_tl t on t.unique_code=l.unique_code
                 where l.sell_record_code=:sell_record_code";

        $data = $this->db->get_all($sql, array(':sell_record_code' => $sell_record_code));
        foreach ($data as &$val) {
            $sku_arr = array('goods_code', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $sku_arr);
            $val = array_merge($val, $sku_info);
        }
        return $data;
    }

}
