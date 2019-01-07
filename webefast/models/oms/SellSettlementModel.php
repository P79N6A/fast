<?php

require_model('tb/TbModel');
require_lang('oms');
require_lib('util/oms_util', true);

/**
 * Description of SellSettlementModel
 *
 * @author user
 */
class SellSettlementModel extends TbModel {

    //put your code here
    protected $table = "oms_sell_settlement_record";
    protected $total_table = "oms_sell_settlement";
    protected $detail_table = "oms_sell_settlement_detail";
    //转单状态
    public $order_attr = array(
        1 => '销售',
        2 => '退货',
        3 => '调整'
    );
    public $check_accounts_status = array(
        '0' => '未核销',
        '10' => '已核销',
        '20' => '部分核销',
        '30' => '虚拟核销',
        '40' => '人工核销',
        '50' => '核销失败',
    );
    //结算类别：1 2 3
    public $settle_type = array(
        1 => '商品',
        2 => '邮费',
        3 => '补差',
        4 => '调整',
    );
    //1 2  3 4  5 6 100  101  102
    public $order_type = array(
        1 => '系统单',
        2 => '手工单',
        3 => '复制单',
        4 => '合并单',
        5 => '拆分单',
        6 => '换货单',
        100 => '仅退款',
        101 => '退款退货',
        102 => '仅退货',
    );
    //定义不需要更新“零售结算订单表”数组
    private $is_not_up_arr = array();
    //换货状态
    public $order_change_status = array(
        0 => '无',
        1 => '有'
    );

    function get_list_by_page($filter) {
        $sql_values = array();
        $sql = "FROM {$this->table} rl WHERE 1 ";
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $select = 'rl.*';
        $filter['check_tab'] = empty($filter['check_tab']) ? 'no_check' : $filter['check_tab'];
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //选项卡
        if ($filter['check_tab'] == 'no_check') {
            $sql_main .= " AND rl.check_accounts_status IN (0,50)";
        } elseif ($filter['check_tab'] == 'part_check') {
            $sql_main .= " AND rl.check_accounts_status=20";
        } elseif ($filter['check_tab'] == 'have_check') {
            $sql_main .= " AND rl.check_accounts_status IN (10,40)";
        } elseif ($filter['check_tab'] == 'dummy_check') {
            $sql_main .= " AND rl.check_accounts_status=30";
        }
        //交易号
        if (isset($filter['deal_code']) && !empty($filter['deal_code'])) {
            $sql_main .= " AND rl.deal_code = :deal_code ";
            $sql_values[':deal_code'] = $filter['deal_code'];
        }
        //订单号
        if (isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])) {
            $sql_main .= " AND rl.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
            $sql_main .= "AND rl.order_attr = 1 ";
        }
        //单据性质
        if (isset($filter['order_attr']) && !empty($filter['order_attr'])) {
            $sql_main .= " AND rl.order_attr in (:order_attr) ";
            $sql_values[':order_attr'] = explode(',', $filter['order_attr']);
        }
        //退单号
        if (isset($filter['sell_return_code']) && !empty($filter['sell_return_code'])) {
            $sql_main .= " AND rl.sell_record_code = :sell_return_code ";
            $sql_values[':sell_return_code'] = $filter['sell_return_code'];
            $sql_main .= "AND rl.order_attr = 2 ";
        }
        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
            $sql_main .= " AND rl.sale_channel_code in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
        }
        //支付方式
        if (isset($filter['pay_type']) && $filter['pay_type'] !== '') {
            $arr = explode(',', $filter['pay_type']);
            $str = $this->arr_to_in_sql_value($arr, 'pay_type', $sql_values);
            $sql_main .= " AND rl.pay_code in ( " . $str . " ) ";
        }
        //创建时间
        if (isset($filter['create_time_start']) && $filter['create_time_start'] !== '') {
            $sql_main .= " AND rl.create_time >= :create_time_start ";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . ' 00:00:00';
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] !== '') {
            $sql_main .= " AND rl.create_time <= :create_time_end ";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . ' 23:59:59';
        }
        //核销时间
        if (isset($filter['check_accounts_time_start']) && $filter['check_accounts_time_start'] !== '') {
            $sql_main .= " AND rl.check_accounts_time >= :check_accounts_time_start ";
            $sql_values[':check_accounts_time_start'] = $filter['check_accounts_time_start'] . ' 00:00:00';
        }
        if (isset($filter['check_accounts_time_end']) && $filter['check_accounts_time_end'] !== '') {
            $sql_main .= " AND rl.check_accounts_time <= :check_accounts_time_end ";
            $sql_values[':check_accounts_time_end'] = $filter['check_accounts_time_end'] . ' 23:59:59';
        }
        if ($filter['ctl_type'] == 'export') {
            return $this->sellsettlement_export_csv($sql_main, $sql_values, $filter);
        }
        $sql_main = $sql . $sql_main;
        $sql_main .= " ORDER BY id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data)) {
            return $this->format_ret(1, $data);
        }

        $archives = load_model('base/ArchiveSearchModel')->get_all_archives_map(['shop', 'express']);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as &$row) {
            $row['order_attr_name'] = $this->order_attr[$row['order_attr']];
            $row['check_accounts_status_name'] = $this->check_accounts_status[$row['check_accounts_status']];
            $row['order_type_name'] = $this->order_type[$row['order_type']];
            $row['settle_type_name'] = $this->settle_type[$row['settle_type']];
            $row['shop_name'] = $archives['shop'][$row['shop_code']];
            $row['express_name'] = $archives['shop'][$row['express_code']];
            $row['delivery_time'] = oms_tb_val('oms_sell_record', 'delivery_time', array('sell_record_code' => $row['sell_record_code']));
            if (2 == $row['order_attr']) {
                $row['pay_name'] = oms_tb_val('base_refund_type', 'refund_type_name', array('refund_type_code' => $row['pay_code']));
            } else {
                $row['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $row['pay_code']));
            }
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $row['receiver_name'] = $this->name_hidden($row['receiver_name']);
                $row['receiver_mobile'] = $this->phone_hidden($row['receiver_mobile']);
                $row['receiver_address'] = $this->address_hidden($row['receiver_address']);
            }
        }

        return $this->format_ret(1, $data);
    }

    //零售结算明细导出
    function sellsettlement_export_csv($sql_main, $sql_values, $filter) {
        $select = " rl.sale_channel_code,rl.sell_settlement_code,rl.order_attr,rl.settle_type,rl.deal_code,rl.sell_record_code,rl.order_type,rl.shop_code,rl.create_time,rl.alipay_no,rl.je,rl.receiver_name,rl.receiver_address,rl.receiver_mobile,rl.express_code,rl.express_no,rl.check_accounts_status,rl.check_accounts_user_code,rl.check_accounts_time,od.goods_code,od.num,od.avg_money,od.sku,rl.account_month ";
        $sql = "FROM oms_sell_settlement_record AS rl LEFT JOIN oms_sell_settlement_detail AS od ON rl.sell_record_code=od.sell_record_code AND rl.deal_code=od.deal_code and rl.order_attr=od.order_attr WHERE 1 ";
        $sql_main = $sql . $sql_main;

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $data);
        }

        $archives = load_model('base/ArchiveSearchModel')->get_all_archives_map(['shop', 'express']);
        $sku_key_arr = array('goods_name', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode');
        $sku_data = [];
        foreach ($data['data'] as &$row) {
            $settle_type = $row['settle_type'];
            $row['order_attr'] = $this->order_attr[$row['order_attr']];
            $row['settle_type'] = $this->settle_type[$row['settle_type']];
            $row['order_type_name'] = $this->order_type[$row['order_type']];
            $row['check_accounts_status'] = $this->check_accounts_status[$row['check_accounts_status']];
            $row['shop_name'] = $archives['shop'][$row['shop_code']];
            $row['express_name'] = $archives['express'][$row['express_code']];
            if ($settle_type != 1) {
                unset($row['goods_code'], $row['num'], $row['avg_money'], $row['sku']);
                continue;
            }
            if (!isset($sku_data[$row['sku']])) {
                $sku_data[$row['sku']] = load_model('goods/SkuCModel')->get_sku_info($row['sku'], $sku_key_arr);
            }
            $row = array_merge($row, $sku_data[$row['sku']]);
            $row['spec'] = "规格1:" . $row['spec1_name'] . ",规格2:" . $row['spec2_name'];
        }

        return $this->format_ret(1, $data);
    }

    //根据交易号查询零售结算汇总
    function get_sell_settlement_by_deal_code($deal_code) {
        $sql = "select * from oms_sell_settlement where deal_code=:deal_code";
        $sql_values = array(':deal_code' => $deal_code);
        return $this->db->get_row($sql, $sql_values);
    }

    //根据交易号查询零售结算汇总
    function get_record_by_deal_code($deal_code, $sell_record_code, $order_attr, $settle_type) {
        return $this->get_row(array('deal_code' => $deal_code, 'sell_record_code' => $sell_record_code, 'order_attr' => $order_attr, 'settle_type' => $settle_type));
    }

    /**
     *
     * 方法名                               api_sell_settlement_list_get
     *
     * 功能描述                           获取零售结算明细
     *
     * @param       array $param
     *              array(
     *                  必选: 'sell_record_code',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_sell_settlement_list_get($param) {
        $key_required = array(
            's' => array(
                'page', 'page_size', 'create_time_start', 'create_time_end', 'shop_code',
            )
        );
        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据

        $ret_required = valid_assign_array($param, $key_required, $arr_required);
        //必填项检测通过
        if (TRUE !== $ret_required['status']) {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
        //合并数据
        $arr_deal = $arr_required;
        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        //清空无用数据
        unset($arr_required);
        unset($param);
        //开放字段
        $select = '
            `sell_settlement_code`, `order_attr`, `deal_code`, `sell_record_code`, `shop_code`, `alipay_no`, `settle_type`,
            `express_money`, `je`, `receiver_name`, `receiver_address`, `receiver_mobile`, `express_code`, `express_no`,`create_time`
        ';
        //查询SQL
        $sql_main = "FROM {$this->table} sr WHERE 1=1";
        //绑定数据
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'create_time_start') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.create_time>=:{$key}";
                } else if ($key == 'create_time_end') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.create_time<=:{$key}";
                } else {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.{$key}=:{$key}";
                }
            }
        }
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
        if (count($ret['data']) > 0) {
            $order_list = &$ret['data'];
            foreach ($order_list as $key => $order) {
                $order_list[$key]['order_attr'] = $this->order_attr[$order['order_attr']];
                $order_list[$key]['settle_type'] = $this->settle_type[$order['settle_type']];
                $order_detail = array();
                if ($order['order_attr'] == 1) {
                    //提取订单明细
                    $order_detail = $this->get_detail_by_deal_code($order['deal_code'], $order['order_attr'], $order['sell_record_code']);
                }
                //检测是否为空
                if (empty($order_detail)) {
                    $order_list[$key]['detail_list'] = array();
                } else {
                    //将订单详细信息压入订单数组中
                    $order_list[$key]['detail_list'] = $order_detail;
                    unset($order_detail);
                }
            }

            //返回数据给请求方
            return $this->format_ret(1, $ret);
        } else {
            //返回数据给请求方
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
        return $arr_required;
    }

    function get_total_list_by_page($filter) {
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->total_table} rl $sql_join WHERE 1 ";
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code like :deal_code ";
            $sql_values[':deal_code'] = "%" . $filter['deal_code'] . "%";
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
            $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( " . $str . " ) ";
        }
        //支付宝流水号
        if (isset($filter['alipay_no']) && $filter['alipay_no'] !== '') {
            $sql_main .= " AND rl.alipay_no like :alipay_no ";
            $sql_values[':alipay_no'] = "%" . $filter['alipay_no'] . "%";
        }
        //交易有无收入
        if (isset($filter['is_ali_in_amount']) && $filter['is_ali_in_amount'] !== '') {
            if ($filter['is_ali_in_amount'] == '0') {
                $sql_main .= " AND rl.ali_in_amount = 0 ";
            }
            if ($filter['is_ali_in_amount'] == '1') {
                $sql_main .= " AND rl.ali_in_amount <> '0.00' ";
            }
        }
        //更新时间
        if (!empty($filter['lastchanged_min'])) {
            $sql_main .= " AND rl.lastchanged >= :lastchanged_min ";
            $sql_values[':lastchanged_min'] = $filter['lastchanged_min'] . ' 00:00:00';
        }
        if (!empty($filter['lastchanged_max'])) {
            $sql_main .= " AND rl.lastchanged <= :lastchanged_max ";
            $sql_values[':lastchanged_max'] = $filter['lastchanged_max'] . ' 23:59:59';
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //付款时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }
        //总应收款
        if (isset($filter['total_fee_min']) && $filter['total_fee_min'] !== '') {
            $sql_main .= " AND rl.total_fee >= :total_fee_min ";
            $sql_values[':total_fee_min'] = $filter['total_fee_min'];
        }
        if (isset($filter['total_fee_max']) && $filter['total_fee_max'] !== '') {
            $sql_main .= " AND rl.total_fee <= :total_fee_max ";
            $sql_values[':total_fee_max'] = $filter['total_fee_max'];
        }
        //实际收入
        if (isset($filter['ali_in_amount_min']) && $filter['ali_in_amount_min'] !== '') {
            $sql_main .= " AND rl.ali_in_amount >= :ali_in_amount_min ";
            $sql_values[':ali_in_amount_min'] = $filter['ali_in_amount_min'];
        }
        if (isset($filter['ali_in_amount_max']) && $filter['ali_in_amount_max'] !== '') {
            $sql_main .= " AND rl.ali_in_amount <= :ali_in_amount_max ";
            $sql_values[':ali_in_amount_max'] = $filter['ali_in_amount_max'];
        }
        //实际支出
        if (isset($filter['ali_out_amount_min']) && $filter['ali_out_amount_min'] !== '') {
            $sql_main .= " AND rl.ali_out_amount >= :ali_out_amount_min ";
            $sql_values[':ali_out_amount_min'] = $filter['ali_out_amount_min'];
        }
        if (isset($filter['ali_out_amount_max']) && $filter['ali_out_amount_max'] !== '') {
            $sql_main .= " AND rl.ali_out_amount <= :ali_out_amount_max ";
            $sql_values[':ali_out_amount_max'] = $filter['ali_out_amount_max'];
        }
        //商品均摊总金额
        if (isset($filter['sell_record_avg_money_min']) && $filter['sell_record_avg_money_min'] !== '') {
            $sql_main .= " AND rl.sell_record_avg_money >= :sell_record_avg_money_min ";
            $sql_values[':sell_record_avg_money_min'] = $filter['sell_record_avg_money_min'];
        }
        if (isset($filter['sell_record_avg_money_max']) && $filter['sell_record_avg_money_max'] !== '') {
            $sql_main .= " AND rl.sell_record_avg_money <= :sell_record_avg_money_max ";
            $sql_values[':sell_record_avg_money_max'] = $filter['sell_record_avg_money_max'];
        }
        //商品退货金额
        if (isset($filter['sell_return_avg_money_min']) && $filter['sell_return_avg_money_min'] !== '') {
            $sql_main .= " AND rl.sell_return_avg_money >= :sell_return_avg_money_min ";
            $sql_values[':sell_return_avg_money_min'] = $filter['sell_return_avg_money_min'];
        }
        if (isset($filter['sell_return_avg_money_max']) && $filter['sell_return_avg_money_max'] !== '') {
            $sql_main .= " AND rl.sell_return_avg_money <= :sell_return_avg_money_max ";
            $sql_values[':sell_return_avg_money_max'] = $filter['sell_return_avg_money_max'];
        }
        //额外赔付金额
        if (isset($filter['compensate_money_min']) && $filter['compensate_money_min'] !== '') {
            $sql_main .= " AND rl.compensate_money >= :compensate_money_min ";
            $sql_values[':compensate_money_min'] = $filter['compensate_money_min'];
        }
        if (isset($filter['compensate_money_max']) && $filter['compensate_money_max'] !== '') {
            $sql_main .= " AND rl.compensate_money <= :compensate_money_max ";
            $sql_values[':compensate_money_max'] = $filter['compensate_money_max'];
        }

        $select = 'rl.*';
        $sql_main .= " ORDER BY id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            //查找交易号是否有换货
            $is_change = 0;
            $tem_arr = load_model('oms/SellRecordModel')->get_record_by_deal_code($value['deal_code']);
            if (!empty($tem_arr)) {
                $is_change = array_sum(array_map(function($v) {
                            return $v['is_change_record'];
                        }, $tem_arr['data']));
                unset($tem_arr);
            }
            $value['is_change'] = $this->order_change_status[($is_change > 0 ? 1 : 0)];
        }
        return $this->format_ret(1, $data);
    }

    function get_detail_by_deal_code($deal_code, $order_attr, $sell_record_code) {
        $sql = "
            select s.*,g.goods_name 
            from oms_sell_settlement_detail s,base_goods g 
            where s.deal_code = :deal_code and s.order_attr= :order_attr and s.sell_record_code=:sell_record_code and s.goods_code = g.goods_code";
        $data = $this->db->get_all($sql, array(":deal_code" => $deal_code, ':order_attr' => $order_attr, ':sell_record_code' => $sell_record_code));
        foreach ($data as $key => $d) {
//        	$spec1_name = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code'=> $d['spec1_code']));
//        	$spec2_name = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code'=> $d['spec2_code']));
//        	$data[$key]['spec1_name'] = $spec1_name;
//        	$data[$key]['spec2_name'] = $spec2_name;
            $key_arr = array('spec1_code', 'spec2_code', 'spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($d['sku'], $key_arr);
            $data[$key] = array_merge($d, $sku_info);
            $data[$key]['spec'] = "规格1:" . $sku_info['spec1_name'] . ",规格2:" . $sku_info['spec2_name'];
        }
        return $data;
    }

    function get_list_by_deal_code($filter) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE deal_code=:deal_code AND order_attr=:order_attr";
        $sql_values[':deal_code'] = $filter['deal_code'];
        $sql_values[':order_attr'] = $filter['order_attr'];
        $select = 'rl.*';
        $sql_main .= " ORDER BY id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['sale_channel_name'] = load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 新增结算, 仅限调整类型
     * @param $sellRecordCode
     * @return array
     */
    function new_settlement_adjust($request) {
        $oms_sell_settlement = $this->get_sell_settlement_by_deal_code($request['deal_code']);
        if (empty($oms_sell_settlement)) {
            return $this->format_ret(-1, '', '交易号为' . $request['deal_code'] . '的零售结算单系统中不存在');
        }
        $sell_settlement_record_ret = $this->get_record_by_deal_code($request['deal_code'], $request['sell_record_code'], 3, 4);
        if (empty($sell_settlement_record_ret['data'])) {
            $sell_settlement_cod = $this->generate_settlement_code();
        } else {
            $sell_settlement_cod = $sell_settlement_record_ret['data']['sell_settlement_code'];
            if ($sell_settlement_record_ret['data']['check_accounts_status']) {
                return $this->format_ret(-1, '', '不能调整已对账过的零售结算明细');
            }
        }

        $data = array(
            'sell_settlement_code' => $sell_settlement_cod,
            'sale_channel_code' => $request['sale_channel_code'],
            'shop_code' => $request['shop_code'],
            'deal_code' => $request['deal_code'],
            'alipay_no' => $request['alipay_no'],
            'sell_record_code' => $request['sell_record_code'],
            'je' => $request['adjust_money'],
            'remark' => $request['adjust_remark'],
            'order_attr' => 3,
            'settle_type' => 4,
            'create_time' => date('Y-m-d H:i:s'),
        );
        $ret = $this->insert_dup($data, 'UPDATE', 'je');
        if ($ret['status'] == -1) {
            return $ret;
        }
        //重新汇总
        $this->count_settlement($request['deal_code']);
        return $this->format_ret(1, '', '新增成功');
    }

    /**
     * 新增结算, 仅限订单
     * @param $sellRecordCode
     * @return array
     */
    public function new_settlement_sell($sellRecordCode) {
        $sql = "SELECT * FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('sell_record_code' => $sellRecordCode));

        $sql = "SELECT * FROM oms_sell_record_detail WHERE sell_record_code = :sell_record_code";
        $detail = $this->db->get_all($sql, array('sell_record_code' => $sellRecordCode));

        $dealCodeList = array();
        $detailList = array();

        foreach ($detail as $k => $v) {
            $dealCodeList[$v['deal_code']] = $v['deal_code'];
            $detailList[$v['deal_code']][] = $v;
        }

        $this->begin_trans();
        try {
            foreach ($dealCodeList as $k => $v) {
                // 汇总
                // oms_sell_settlement
                $sql = "SELECT * FROM oms_sell_settlement WHERE deal_code = :deal_code";
                $r1 = $this->db->get_row($sql, array('deal_code' => $v));
                if (empty($r1)) {
                    $d = array(
                        'order_attr' => '1',
                        'deal_code' => $v,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'shop_code' => $record['shop_code'],
                        'alipay_no' => $record['alipay_no'],
                        //'total_fee' => $record['total_fee'],
                        //'express_money' => $record['express_money'],
                        //'point_fee' => $record['point_fee'],
                        //'ali_in_amount' => $record['ali_in_amount'],
                        //'ali_out_amount' => $record['ali_out_amount'],
                        //'commission_fee' => $record['commission_fee'],
                        // 'num' => $record['num'], ## Count late.
                        //'return_num' => $record['return_num'],
                        // 'sell_record_avg_money' => $record['sell_record_avg_money'], ## Count late.
                        //'sell_return_avg_money' => $record['sell_return_avg_money'],
                        //'compensate_money' => $record['compensate_money'],
                        'create_time' => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('oms_sell_settlement', $d);
                }

                // 商品
                // oms_sell_settlement_record
                $sql = "SELECT * FROM oms_sell_settlement_record WHERE settle_type = 1 and deal_code = :deal_code AND sell_record_code = :sell_record_code";
                $r2 = $this->db->get_row($sql, array('deal_code' => $v, 'sell_record_code' => $record['sell_record_code']));
                if (empty($r2)) {
                    $t = '1';
                    if ($record['is_handwork'] == '1')
                        $t = 2;
                    if ($record['is_copy'] == '1')
                        $t = 3;
                    if ($record['is_combine_new'] == '1')
                        $t = 4;
                    if ($record['is_split_new'] == '1')
                        $t = 5;
                    if ($record['is_change_record'] == '1')
                        $t = 6;

                    $d = array(
                        'sell_settlement_code' => $this->generate_settlement_code(),
                        'order_attr' => '1',
                        'deal_code' => $v,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'sell_record_code' => $record['sell_record_code'],
                        'shop_code' => $record['shop_code'],
                        'pay_code' => $record['pay_code'],
                        'alipay_no' => $record['alipay_no'],
                        'order_type' => $t,
                        'settle_type' => '1', // 结算类别：1商品 2邮费 3补差
                        //'point_fee' => $record['point_fee'],
                        //'express_money' => $record['express_money'],
                        //'compensate_money' => '',
                        //'num' => $num, // Count late.
                        //'je' => $record['goods_money'], // Count late.
                        'receiver_name' => $record['receiver_name'],
                        'receiver_address' => $record['receiver_address'],
                        'receiver_mobile' => $record['receiver_mobile'],
                        //'express_company_code' => $record['express_company_code'],
                        'express_code' => $record['express_code'],
                        'express_no' => $record['express_no'],
                        'create_time' => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('oms_sell_settlement_record', $d);

                    // 商品
                    // oms_sell_settlement_detail
                    foreach ($detailList[$v] as $kk => $vv) {
                        $d = array(
                            'order_attr' => '1',
                            'deal_code' => $v,
                            'sale_channel_code' => $record['sale_channel_code'],
                            'sell_record_code' => $record['sell_record_code'],
                            'goods_code' => $vv['goods_code'],
//                            'spec1_code' => $vv['spec1_code'],
//                            'spec2_code' => $vv['spec2_code'],
                            'sku' => $vv['sku'],
//                            'barcode' => $vv['barcode'],
                            'num' => $vv['num'],
                            'avg_money' => $vv['avg_money'],
                        );
                        $this->db->insert('oms_sell_settlement_detail', $d);
                    }
                }

                // 运费
                // oms_sell_settlement_record
                $sql = "SELECT * FROM oms_sell_settlement_record WHERE settle_type = 2 and deal_code = :deal_code";
                $r2 = $this->db->get_row($sql, array('deal_code' => $v));
                if (empty($r2)) {
                    $sql = "select express_money, integral_change_money, commission_fee from api_order where shop_code =:shop_code and tid = :tid ";
                    $express = $this->db->get_row($sql, array('shop_code' => $record['shop_code'], 'tid' => $v));
                    if (!empty($express)) {
                        $express['integral_change_money'] = empty($express['integral_change_money']) ? 0 : $express['integral_change_money'];
                        $express['commission_fee'] = empty($express['commission_fee']) ? 0 : $express['commission_fee'];
                        $d = array(
                            'sell_settlement_code' => $this->generate_settlement_code(),
                            'order_attr' => '1',
                            'deal_code' => $v,
                            'sale_channel_code' => $record['sale_channel_code'],
                            'sell_record_code' => $record['sell_record_code'],
                            'shop_code' => $record['shop_code'],
                            'settle_type' => '2', // 结算类别：1商品 2邮费 3补差
                            'point_fee' => $express['integral_change_money'],
                            'commission_fee' => $express['commission_fee'],
                            'je' => $express['express_money'],
                            'create_time' => date('Y-m-d H:i:s'),
                        );
                        if ($express['express_money'] > 0)
                            $this->db->insert('oms_sell_settlement_record', $d);
                    } else {
                        //如果api_order表没有运费则取原单运费
                        $d = array(
                            'sell_settlement_code' => $this->generate_settlement_code(),
                            'order_attr' => '1',
                            'deal_code' => $v,
                            'sale_channel_code' => $record['sale_channel_code'],
                            'sell_record_code' => $record['sell_record_code'],
                            'shop_code' => $record['shop_code'],
                            'settle_type' => '2', // 结算类别：1商品 2邮费 3补差
                            'je' => $record['express_money'],
                            'create_time' => date('Y-m-d H:i:s'),
                        );
                        if ($record['express_money'] > 0)
                            $this->db->insert('oms_sell_settlement_record', $d);
                    }
                }
            }

            // count
            foreach ($dealCodeList as $k => $v) {
                $this->count_settlement($v);
                $this->count_settlement_record($v, $record['sell_record_code']);
            }

            $this->commit();
            return array('status' => '1', 'message' => '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'message' => $e->getMessage());
        }
    }

    /**
     * 新增结算, 仅限退单
     * @param $sellReturnCode
     * @return array
     */
    public function new_settlement_return($sellReturnCode) {
        $sql = "SELECT * FROM oms_sell_return WHERE sell_return_code = :sell_return_code";
        $record = $this->db->get_row($sql, array('sell_return_code' => $sellReturnCode));

        $sql = "SELECT * FROM oms_sell_return_detail WHERE sell_return_code = :sell_return_code";
        $detail = $this->db->get_all($sql, array('sell_return_code' => $sellReturnCode));

        $dealCodeList = array();
        $detailList = array();

        foreach ($detail as $k => $v) {
            $dealCodeList[$v['deal_code']] = $v['deal_code'];
            $detailList[$v['deal_code']][] = $v;
        }

        // 退
        $this->begin_trans();
        try {
            foreach ($dealCodeList as $k => $v) {
                // 汇总(退)
                // oms_sell_settlement
                $sql = "SELECT * FROM oms_sell_settlement WHERE deal_code = :deal_code";
                $r1 = $this->db->get_row($sql, array('deal_code' => $v));
                if (empty($r1)) {
                    $d = array(
                        'order_attr' => '2',
                        'deal_code' => $v,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'shop_code' => $record['shop_code'],
                        //'alipay_no' => $record['alipay_no'],
                        //'total_fee' => $record['total_fee'],
                        //'express_money' => $record['express_money'],
                        //'point_fee' => $record['point_fee'],
                        //'ali_in_amount' => $record['ali_in_amount'],
                        //'ali_out_amount' => $record['ali_out_amount'],
                        //'commission_fee' => $record['commission_fee'],
                        // 'num' => $record['num'], ## Count late.
                        //'return_num' => $record['return_num'],
                        // 'sell_record_avg_money' => $record['sell_record_avg_money'], ## Count late.
                        //'sell_return_avg_money' => $record['sell_return_avg_money'],
                        //'compensate_money' => $record['compensate_money'],
                        'create_time' => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('oms_sell_settlement', $d);
                }

                // 商品(退)
                // oms_sell_settlement_record
                $sql = "SELECT * FROM oms_sell_settlement_record WHERE settle_type = 1 and deal_code = :deal_code AND sell_record_code = :sell_return_code";
                $r2 = $this->db->get_row($sql, array('deal_code' => $v, 'sell_return_code' => $record['sell_return_code']));
                if (empty($r2)) {
                    $t = '1';
                    // 1仅退款 2仅退货 3退款退货
                    // 6换货单 100 仅退款 101 退款退货 102 仅退货
                    if ($record['return_type'] == '1')
                        $t = 100;
                    if ($record['return_type'] == '2')
                        $t = 102;
                    if ($record['return_type'] == '3')
                        $t = 101;

                    $d = array(
                        'sell_settlement_code' => $this->generate_settlement_code(),
                        'order_attr' => '2',
                        'deal_code' => $v,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'sell_record_code' => $record['sell_return_code'],
                        'shop_code' => $record['shop_code'],
                        'pay_code' => $record['return_pay_code'],
                        // 'alipay_no' => $record['alipay_no'],
                        'order_type' => $t,
                        'settle_type' => '1', // 结算类别：1商品 2邮费 3补差
                        //'point_fee' => $record['point_fee'],
                        //'express_money' => $record['express_money'],
                        //'compensate_money' => '',
                        //'num' => $num, // Count late.
                        'je' => $record['refund_total_fee'], // Count late.
                        'receiver_name' => (string) @$record['return_name'],
                        'receiver_address' => (string) @$record['return_address'],
                        'receiver_mobile' => (string) @$record['return_mobile'],
                        //'express_company_code' => $record['return_express_code'],
                        'express_code' => (string) @$record['return_express_code'],
                        'express_no' => (string) @$record['return_express_no'],
                        'create_time' => date('Y-m-d H:i:s'),
                    );
                    $this->db->insert('oms_sell_settlement_record', $d);

                    // 商品(退)
                    $money = 0.00;
                    // oms_sell_settlement_detail
                    foreach ($detailList[$v] as $kk => $vv) {
                        $d = array(
                            'order_attr' => '2',
                            'deal_code' => $v,
                            'sale_channel_code' => $record['sale_channel_code'],
                            'sell_record_code' => $record['sell_return_code'],
                            'goods_code' => $vv['goods_code'],
//                            'spec1_code' => $vv['spec1_code'],
//                            'spec2_code' => $vv['spec2_code'],
                            'sku' => $vv['sku'],
//                            'barcode' => $vv['barcode'],
                            'num' => $vv['recv_num'],
                            'avg_money' => $vv['avg_money'],
                        );
                        $this->db->insert('oms_sell_settlement_detail', $d);
                        $money += $vv['avg_money'];
                    }

                    // 补差(退)
                    // oms_sell_settlement_record
                    if ($money < $record['refund_total_fee']) {
                        $sql = "SELECT * FROM oms_sell_settlement_record WHERE settle_type = 3 and deal_code = :deal_code";
                        $r2 = $this->db->get_row($sql, array('deal_code' => $v));
                        if (empty($r2)) {
                            $d = array(
                                'sell_settlement_code' => $this->generate_settlement_code(),
                                'order_attr' => '2',
                                'deal_code' => $v,
                                'sale_channel_code' => $record['sale_channel_code'],
                                //'sell_record_code' => $record['sell_record_code'],
                                'shop_code' => $record['shop_code'],
                                'settle_type' => '3', // 结算类别：1商品 2邮费 3补差
                                'je' => $record['refund_total_fee'] - $money,
                                'create_time' => date('Y-m-d H:i:s'),
                            );
                            $this->db->insert('oms_sell_settlement_record', $d);
                        } else {
                            $d = array('je' => $record['refund_total_fee'] - $money + $r2['je']);
                            $this->db->update('oms_sell_settlement_record', $d, array('id' => $r2['id']));
                        }
                    }
                }
            }

            // count
            foreach ($dealCodeList as $k => $v) {
                $this->count_settlement($v);
                $this->count_settlement_record($v, $record['sell_record_code']);
            }

            $this->commit();
            return array('status' => '1', 'message' => '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => '-1', 'message' => $e->getMessage());
        }
    }

    /**
     * 重新计算金额/数量
     * @param $dealCode
     */
    public function count_settlement($dealCode) {
        // 运费, 积分兑换金额, 佣金
        $sql = "SELECT sum(je) express_money, sum(point_fee) point_fee, sum(commission_fee) commission_fee
        FROM oms_sell_settlement_record WHERE deal_code = :deal_code AND order_attr = 1 AND settle_type = 2";
        $r1 = $this->db->get_row($sql, array('deal_code' => $dealCode));
        if (empty($r1)) {
            $r1 = array(
                'express_money' => 0,
                'point_fee' => 0,
                'commission_fee' => 0,
            );
        }

        // 订单商品明细
        $sql = "SELECT * FROM oms_sell_settlement_detail WHERE deal_code = :deal_code";
        $r2 = $this->db->get_all($sql, array('deal_code' => $dealCode));

        // 补差金额
        $sql = "SELECT sum(je) je
        FROM oms_sell_settlement_record WHERE deal_code = :deal_code AND order_attr = 2 AND settle_type = 3";
        $r3 = $this->db->get_value($sql, array('deal_code' => $dealCode));
        //调整金额
        $sql = "SELECT sum(je) je
        FROM oms_sell_settlement_record WHERE deal_code = :deal_code AND order_attr = 3 AND settle_type = 4";
        $r4 = $this->db->get_value($sql, array('deal_code' => $dealCode));
        //买家使用积分
        $sql = "SELECT real_point_fee from api_taobao_trade where 1=1 ";
        $real_point_fee = $this->db->get_value($sql, array('tid' => $dealCode));
        $num = 0;
        $numReturn = 0;
        $je = 0.00;
        $jeReturn = 0.00;

        foreach ($r2 as $k => $v) {
            if ($v['order_attr'] == '1') {
                $num += $v['num'];
                $je += $v['avg_money'];
            }
            if ($v['order_attr'] == '2') {
                $numReturn += $v['num'];
                $jeReturn += $v['avg_money'];
            }
        }

        $d = array(
            'total_fee' => $je + $r1['express_money'] - $jeReturn - $r3 + $r4, // 销售+退货+调整
            'express_money' => $r1['express_money'],
            'adjust_money' => $r4,
            // 'point_fee' => $r1['point_fee'],
            'real_point_fee' => $real_point_fee / 100,
            //'ali_in_amount' => $record['ali_in_amount'],
            //'ali_out_amount' => $record['ali_out_amount'],
            //  'commission_fee' => $r1['commission_fee'],
            'num' => $num,
            'return_num' => $numReturn,
            'sell_record_avg_money' => $je,
            'sell_return_avg_money' => $jeReturn,
            'compensate_money' => $r3,
        );

        $this->db->update('oms_sell_settlement', $d, array('deal_code' => $dealCode));
    }

    /**
     * 重新计算金额/数量
     * @param $dealCode
     * @param $sellRecordCode
     */
    public function count_settlement_record($dealCode, $sellRecordCode) {
        //$sql = "SELECT * FROM oms_sell_settlement_record WHERE deal_code = :deal_code AND sell_record_code = :sell_record_code";
        //$r1 = $this->db->get_row($sql, array('deal_code'=>$dealCode, 'sell_record_code'=>$sellRecordCode));

        $sql = "SELECT * FROM oms_sell_settlement_detail WHERE deal_code = :deal_code AND sell_record_code = :sell_record_code";
        $r2 = $this->db->get_all($sql, array('deal_code' => $dealCode, 'sell_record_code' => $sellRecordCode));

        $num = 0;
        $je = 0.00;
        foreach ($r2 as $k => $v) {
            $num += $v['num'];
            $je += $v['avg_money'];
        }

        $d = array(
            'num' => $num,
            'je' => $je,
        );

        $this->db->update('oms_sell_settlement_record', $d, array('deal_code' => $dealCode, 'sell_record_code' => $sellRecordCode, 'settle_type' => 1));
    }

    /**
     *
     * 方法名       generate_settlement_code
     *
     * 功能描述     生成单据编号
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-30
     * @param       
     * @return      string
     */
    private function generate_settlement_code() {
        $prefix = 'JSMX';
        return $prefix . load_model('oms/SellRecordModel')->new_code();
    }

    /**
     *
     * 方法名       generate_settlement_data
     *
     * 功能描述     生成零售结算单数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       array &$record      [订|退单信息]
     * @param       array &$detail      [订|退单明细列表信息]
     * @param       string $record_code [订|退单单号]
     * @param       string $order_attr  [单据类型]
     * @return      string
     */
    private function get_record_and_detail(array &$record, array &$detail, $record_code, $order_attr) {
        if ($order_attr == 1) {
            $filter = array('sell_record_code' => $record_code);
            $oms_sell_record_obj = load_model('oms/SellRecordModel');
            $ret = $oms_sell_record_obj->check_exists_by_condition($filter);
            if (1 == $ret['status']) {
                $record = $ret['data'];
            } else {
                return array('status' => '-1', 'data' => '', 'message' => lang('op_error_query'));
            }
            $order_type = $oms_sell_record_obj->get_record_by_code($record_code, 'is_fenxiao');
            $record['express_money'] = $order_type['is_fenxiao'] == 1 || $order_type['is_fenxiao'] == 2 ? $record['fx_express_money'] : $record['express_money'];
            $detail = $oms_sell_record_obj->get_detail_list_group_by_code($record_code, $order_type['is_fenxiao']);
        } else if ($order_attr == 2) {
            $filter = array('sell_return_code' => $record_code);
            $oms_sell_return_obj = load_model('oms/SellReturnModel');
            $ret = $oms_sell_return_obj->check_exists_by_condition($filter);
            if (1 == $ret['status']) {
                $record = $ret['data'];
            } else {
                return array('status' => '-1', 'data' => '', 'message' => lang('op_error_query'));
            }
            if (1 == $record['return_type']) {
                //仅退款【return_type=1】：商品详细列表为原单商品列表
                $oms_sell_record_obj = load_model('oms/SellRecordModel');
                $detail = $oms_sell_record_obj->get_detail_list_group_by_code($record['sell_record_code']);
            } else {
                $detail = $oms_sell_return_obj->get_detail_list_by_return_code($record_code);
            }
        } else {
            return array('status' => '-1', 'data' => '', 'message' => lang('op_error_params'));
        }
        return array('status' => '1', 'data' => '', 'message' => lang('op_success'));
    }

    /**
     *
     * 方法名       get_each_deal_code_express
     *
     * 功能描述     获取每个交易号的运费
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       array &$express_by_deal_code_arr    [各交易号的运费信息]
     * @param       array $deal_code_list               [交易号数组]
     * @param       string $detail_list                 [按交易号分类的商品列表]
     * @param       array $record                       [订单信息]
     * @return      string
     */
    private function get_each_deal_code_express(array &$express_by_deal_code_arr, $deal_code_list, $detail_list, $record) {
        //平台中间表中无运费的产品数量
        $no_api_express_num = 0;
        //平台交易按交易号商品数量进行排序的字段数据
        $sort_arr = array();
        //获取api_order的模型
        $api_order_model = load_model('api/OrderModel');
        foreach ($deal_code_list as $kc => $dc) {
            //查询零售结算表是否存在运费
            $filter = array('deal_code' => $dc, 'settle_type' => 2);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement_record');
            if (1 == $ret['status']) {
                continue;
            }

            //从订单中间表检测运费是否存在，存在则获取运费
            //$filter = array('shop_code' => $record['shop_code'], 'tid' => $dc);
            $filter = array('tid' => $dc);
            $ret = $api_order_model->check_exists_by_condition($filter);
            if (1 == $ret['status']) {
                $api_ex = $ret['data'];
                $num = $api_ex['num'];
                $express_by_deal_code_arr['h' . $dc] = array(
                    'is_api' => 1,
                    'num' => $num,
                    'point_fee' => empty($api_ex['integral_change_money']) ? 0 : $api_ex['integral_change_money'],
                    'commission_fee' => empty($api_ex['commission_fee']) ? 0 : $api_ex['commission_fee'],
                    'express_money' => empty($api_ex['express_money']) ? 0 : $api_ex['express_money']
                );
                if ($express_by_deal_code_arr['h' . $dc]['express_money'] <= 0) {
                    //$no_api_express_num += $num;
                    //$express_by_deal_code_arr['h' . $dc]['is_api'] = 1;
                }
            } else {
                $num = array_sum(array_map(function($v) {
                            return $v['num'];
                        }, $detail_list[$dc]));
                $express_by_deal_code_arr['h' . $dc] = array(
                    'is_api' => 0,
                    'num' => $num,
                    'point_fee' => 0,
                    'commission_fee' => 0,
                    'express_money' => 0
                );
                $no_api_express_num += $num;
            }
            $sort_arr['h' . $dc] = $num;
            unset($api_ex);
        }
        //对运费按每交易号商品数量递减排序
        array_multisort($sort_arr, SORT_NUMERIC, SORT_DESC, $express_by_deal_code_arr);

        //处理平台中间表是没有运费的交易号：【方法：将订单运费均分到每个交易号内】
        if ($no_api_express_num > 0 && $record['express_money'] > 0) {
            //将订单的运费整分到平台中间表无运费的每个产品上
            $mod_express = $this->float_amout_round($record['express_money'], $no_api_express_num);
            //循环起始确认值
            $temp_num = 0;
            //按交易号处理每个交易号的运费
            foreach ($express_by_deal_code_arr as $dc => &$express_arr) {
                if ($express_arr['is_api'] == 0) {
                    $express_arr['express_money'] = $mod_express['lcm'] * $express_arr['num'];
                    //将多余的运费加到商品数量最多的一单交易号中
                    if ($temp_num == 0) {
                        $express_arr['express_money'] += $mod_express['mod'];
                    }
                    $temp_num ++;
                }
            }
        }
        //若修改过运费 可能会出现多余的运费
        $express_money = 0;
        $first_dc = "";
        foreach ($express_by_deal_code_arr as $dc => $express_arr) {
            $express_money += $express_arr['express_money'];
            if (empty($first_dc)) {
                $first_dc = $dc;
            }
        }
        $record['express_money'] = isset($record['express_money']) ? $record['express_money'] : 0;
        if ($express_money < $record['express_money']) {
            $express_by_deal_code_arr[$first_dc]['express_money'] += $record['express_money'] - $express_money;
        }
    }

    /**
     *
     * 方法名       get_each_deal_code_compensate
     *
     * 功能描述     获取每个交易号的补差
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       array &$compensate_by_deal_code_arr [各交易号的补差信息]
     * @param       array $deal_code_list               [交易号数组]
     * @param       string $detail_list                 [按交易号分类的商品列表]
     * @param       array $record                       [订单信息]
     * @param       array $original_detail              [原单详细信息]
     * @return      string
     */
    private function get_each_deal_code_compensate(array &$compensate_by_deal_code_arr, $deal_code_list, $detail_list, $record, $original_detail) {
        //补差金额
        $je = $record['compensate_money'] + $record['seller_express_money'] + $record['adjust_money'];
        //参入补差均分的产品数量
        $compensate_num = 0;
        //按交易号商品数量进行排序的字段数据
        $sort_arr = array();
        $temp_arr = array();
        if (!empty($detail_list)) {
            $temp_arr = $detail_list;
        } else {
            $temp_arr = $original_detail;
        }
        foreach ($deal_code_list as $kc => $dc) {
            $num = array_sum(array_map(function($v) {
                        return isset($v['recv_num']) ? $v['recv_num'] : $v['num'];
                    }, $temp_arr[$dc]));
            $compensate_by_deal_code_arr['h' . $dc] = array(
                'num' => $num,
                'je' => 0
            );
            $compensate_num += $num;
            $sort_arr['h' . $dc] = $num;
            unset($api_ex);
        }

        //将补差均分到每个交易号内
        if ($compensate_num > 0 && $je != 0) {
            $mod_compensate = $this->float_amout_round($je, $compensate_num);
            $temp_num = 0;
            //对补差金额按每交易号商品数量递减排序
            array_multisort($sort_arr, SORT_NUMERIC, SORT_DESC, $compensate_by_deal_code_arr);
            //按交易号处理每个交易号的补差
            foreach ($compensate_by_deal_code_arr as $dc => &$compensate_arr) {
                $compensate_arr['je'] = $mod_compensate['lcm'] * $compensate_arr['num'];
                //将多余的补差金额加到商品数量最多的一单交易号中
                if ($temp_num == 0) {
                    $compensate_arr['je'] += $mod_compensate['mod'];
                }
                $temp_num ++;
            }
        }
    }

    /**
     *
     * 方法名       generate_settlement_sell
     *
     * 功能描述     生成订单零售结算单数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       array $deal_code_list               [交易号数组]
     * @param       string $detail_list                 [按交易号分类的商品列表]
     * @param       array $record                       [订单信息]
     * @param       string $order_attr                  [单据类型]
     * @return      array
     */
    private function generate_settlement_sell($deal_code_list, $detail_list, $record, $order_attr) {
        //计算交易号运费
        $express_by_deal_code_arr = array();
        //查找平台中间表的订单运费
        $this->get_each_deal_code_express($express_by_deal_code_arr, $deal_code_list, $detail_list, $record);

        foreach ($deal_code_list as $key => $val) {
            //检查“零售结算单”数据是否存在
            $filter = array('deal_code' => $val);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement');
            if (1 != $ret['status']) {
                $deal_arr = array(
                    'order_attr' => $order_attr,
                    'deal_code' => $val,
                    'sale_channel_code' => $record['sale_channel_code'],
                    'shop_code' => $record['shop_code'],
                    'alipay_no' => (isset($record['alipay_no']) && !empty($record['alipay_no']) ? $record['alipay_no'] : ''),
                    'create_time' => $record['delivery_time'],
                    'record_time' => $record['record_time'],
                    'pay_time' => $record['pay_time'],
                );
                //插入“零售结算单”数据
                $ret = $this->insert_exp('oms_sell_settlement', $deal_arr);
                unset($deal_arr);
                if (1 != $ret['status']) {
                    return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                }
            }
            //更新最新发货时间
            $u = array(
                'sell_month' => $record['delivery_date'],
                'sell_month_ym' => date('Y-m', strtotime($record['delivery_date'])),
            );
            $this->db->update('oms_sell_settlement', $u, $filter);
            //检查“零售结算订单表”商品数据是否存在
            $filter = array('deal_code' => $val, 'settle_type' => 1, 'sell_record_code' => $record['sell_record_code']);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement_record');
            if (1 != $ret['status']) {
                $order_type = 1;
                if ($record['is_handwork'] == '1')
                    $order_type = 2;
                if ($record['is_copy'] == '1')
                    $order_type = 3;
                if ($record['is_combine_new'] == '1')
                    $order_type = 4;
                if ($record['is_split_new'] == '1')
                    $order_type = 5;
                if ($record['is_change_record'] == '1')
                    $order_type = 6;
                $deal_arr = array(
                    'sell_settlement_code' => $this->generate_settlement_code(),
                    'order_attr' => $order_attr,
                    'deal_code' => $val,
                    'sale_channel_code' => $record['sale_channel_code'],
                    'sell_record_code' => $record['sell_record_code'],
                    'shop_code' => $record['shop_code'],
                    'pay_code' => $record['pay_code'],
                    'alipay_no' => $record['alipay_no'],
                    'order_type' => $order_type,
                    'settle_type' => '1', // 结算类别：1商品 2邮费 3补差
                    'receiver_name' => $record['receiver_name'],
                    'receiver_address' => $record['receiver_address'],
                    'receiver_mobile' => $record['receiver_mobile'],
                    //'express_company_code' => $record['express_company_code'],
                    'express_code' => $record['express_code'],
                    'express_no' => $record['express_no'],
                    'create_time' => $record['delivery_time']
                );
                //插入“零售结算订单表”商品数据是否存在
                $ret = $this->insert_exp('oms_sell_settlement_record', $deal_arr);
                unset($deal_arr);
                if (1 != $ret['status']) {
                    return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                }

                //插处“零售结算明细表”数据
                foreach ($detail_list[$val] as $detail_key => $detail_arr) {
                    $deal_arr = array(
                        'order_attr' => $order_attr,
                        'deal_code' => $val,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'sell_record_code' => $record['sell_record_code'],
                        'goods_code' => $detail_arr['goods_code'],
                        'spec1_code' => $detail_arr['spec1_code'],
                        'spec2_code' => $detail_arr['spec2_code'],
                        'sku' => $detail_arr['sku'],
                        'barcode' => $detail_arr['barcode'],
                        'num' => $detail_arr['num'],
                        'avg_money' => $detail_arr['avg_money'],
                    );
                    $ret = $this->insert_exp('oms_sell_settlement_detail', $deal_arr);
                    unset($deal_arr);
                    if (1 != $ret['status']) {
                        return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                    }
                }
            }

            //运费处理
            //$filter = array('deal_code' => $val, 'settle_type' => 2, 'sell_record_code' => $record['sell_record_code']);
            $filter = array('deal_code' => $val, 'settle_type' => 2);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement_record');
            if (1 != $ret['status']) {
                if ($express_by_deal_code_arr['h' . $val]['express_money'] > 0) {
                    $deal_arr = array(
                        'sell_settlement_code' => $this->generate_settlement_code(),
                        'order_attr' => $order_attr,
                        'deal_code' => $val,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'sell_record_code' => $record['sell_record_code'],
                        'shop_code' => $record['shop_code'],
                        'pay_code' => $record['pay_code'],
                        'alipay_no' => $record['alipay_no'],
                        'order_type' => $order_type,
                        'settle_type' => '2', // 结算类别：1商品 2邮费 3补差
                        'receiver_name' => $record['receiver_name'],
                        'receiver_address' => $record['receiver_address'],
                        'receiver_mobile' => $record['receiver_mobile'],
                        //'express_company_code' => $record['express_company_code'],
                        'express_code' => $record['express_code'],
                        'express_no' => $record['express_no'],
                        'point_fee' => $express_by_deal_code_arr['h' . $val]['point_fee'],
                        'commission_fee' => $express_by_deal_code_arr['h' . $val]['commission_fee'],
                        'je' => $express_by_deal_code_arr['h' . $val]['express_money'],
                        'create_time' => $record['delivery_time']
                    );
                    $ret = $this->insert_exp('oms_sell_settlement_record', $deal_arr);
                    unset($deal_arr);
                    if (1 != $ret['status']) {
                        return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                    }
                }
            }
            //更新最新发货时间
            $u = array(
                'sell_month' => $record['delivery_date'],
                'sell_month_ym' => date('Y-m', strtotime($record['delivery_date'])),
            );
            $this->db->update('oms_sell_settlement_record', $u, array('deal_code' => $val, 'sell_record_code' => $record['sell_record_code']));
        }
        return array('status' => '1', 'data' => '', 'message' => lang('op_success'));
    }

    /**
     *
     * 方法名       generate_settlement_return
     *
     * 功能描述     生成退单零售结算单数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       array $deal_code_list               [交易号数组]
     * @param       string $detail_list                 [按交易号分类的商品列表]
     * @param       array $record                       [订单信息]
     * @param       string $order_attr                  [单据类型]
     * @return      array
     */
    private function generate_settlement_return($deal_code_list, $detail_list, $record, $order_attr) {
        //获取原单信息
        $original_record = load_model('oms/SellRecordModel')->get_record_by_code($record['sell_record_code']);
        $original_status = $original_record['shipping_status'];
        //获取原单商品列表
        $original_detail_temp = load_model('oms/SellRecordModel')->get_detail_list_by_code($record['sell_record_code']);
        $original_detail = array();
        foreach ($original_detail_temp as $k => $v) {
            $original_detail[$v['deal_code']][] = $v;
        }
        unset($original_detail_temp);

        //计算各交易号补差
        $compensate_by_deal_code_arr = array();
        //查找平台中间表的订单运费
        $this->get_each_deal_code_compensate($compensate_by_deal_code_arr, $deal_code_list, $detail_list, $record, $original_detail);
        $deal_arr = array();
        foreach ($deal_code_list as $key => $val) {
            //检查“零售结算单”数据是否存在
            $filter = array('deal_code' => $val);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement');
            if (1 != $ret['status']) {
                $deal_arr = array(
                    'order_attr' => $order_attr,
                    'deal_code' => $val,
                    'sale_channel_code' => $record['sale_channel_code'],
                    'shop_code' => $record['shop_code'],
                    'alipay_no' => (isset($record['alipay_no']) && !empty($record['alipay_no']) ? $record['alipay_no'] : ''),
                    'create_time' => $record['receive_time']
                );
                //插入“零售结算单”数据
                $ret = $this->insert_exp('oms_sell_settlement', $deal_arr);
                $deal_arr = array();
                if (1 != $ret['status']) {
                    return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                }
            }

            //检查“零售结算订单表”退单商品数据是否存在
            $filter = array('deal_code' => $val, 'settle_type' => 1, 'sell_record_code' => $record['sell_return_code']);
            $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement_record');
            if (1 != $ret['status']) {
                $order_type = 1;
                //return_type: 1仅退款 2仅退货 3退款退货
                //order_type: 100 仅退款 102 仅退货 101 退款退货
                if ($record['return_type'] == '1')
                    $order_type = 100;
                if ($record['return_type'] == '2')
                    $order_type = 102;
                if ($record['return_type'] == '3')
                    $order_type = 101;
                //“零售结算订单表”是否生成商品数据
                $is_create_goods = 1;
                //“零售结算订单表”是否生成补差
                $is_create_compensate = 1;
                //“零售结算明细表”是否生成商品细列表
                $is_create_detail = 1;
                //业务创建时间
                $create_time = date('Y-m-d H:i:s');
                //补差金额
                $je = $record['compensate_money'] + $record['seller_express_money'] + $record['adjust_money'];

                //仅退款业务
                if (100 == $order_type) {
                    //仅退款已发货
                    if (4 == $original_status) {
                        $is_create_goods = 0;
                        $is_create_detail = 0;
                        $this->is_not_up_arr[$val] = 1;
                    }
                    //$create_time = $record['agree_refund_time'];
                    //仅退货业务
                } else if (102 == $order_type) {
//                    $this->is_not_up_arr[$val] = 1;
                    $is_create_compensate = 0;
                    //仅退货发货后
                    if (4 == $original_status) {
                        $create_time = $record['receive_time'];
                    }

                    //退款退货业务
                } else if (101 == $order_type) {
                    $create_time = $record['receive_time'];

                    //其他直接返回
                } else {
                    return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                }

                //处理“零售结算订单表”商品数据
                if ($is_create_goods > 0) {
                    $deal_arr = array(
                        'sell_settlement_code' => $this->generate_settlement_code(),
                        'order_attr' => $order_attr,
                        'deal_code' => $val,
                        'sale_channel_code' => $record['sale_channel_code'],
                        'sell_record_code' => $record['sell_return_code'],
                        'shop_code' => $record['shop_code'],
                        'pay_code' => $record['return_pay_code'],
                        'order_type' => $order_type,
                        'settle_type' => '1', // 结算类别：1商品 2邮费 3补差
                        'receiver_name' => $record['return_name'],
                        'receiver_address' => $record['return_address'],
                        'receiver_mobile' => $record['return_mobile'],
                        //'express_company_code' => $record['return_express_code'],
                        'express_code' => $record['return_express_code'],
                        'express_no' => $record['return_express_no'],
                        'create_time' => $create_time
                    );
                    $ret = $this->insert_exp('oms_sell_settlement_record', $deal_arr);
                    $deal_arr = array();
                    if (1 != $ret['status']) {
                        return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                    }
                }

                //处理“零售结算明细表”数据
                if ($is_create_detail > 0) {
                    if (!empty($detail_list[$val])) {
                        foreach ($detail_list[$val] as $detail_key => $detail_arr) {
                            $deal_arr = array(
                                'order_attr' => $order_attr,
                                'deal_code' => $val,
                                'sale_channel_code' => $record['sale_channel_code'],
                                'sell_record_code' => $record['sell_return_code'],
                                'goods_code' => $detail_arr['goods_code'],
                                'spec1_code' => $detail_arr['spec1_code'],
                                'spec2_code' => $detail_arr['spec2_code'],
                                'sku' => $detail_arr['sku'],
                                'barcode' => $detail_arr['barcode'],
                                'num' => $detail_arr['recv_num'],
                                'avg_money' => $detail_arr['avg_money'],
                            );
                            $ret = $this->insert_exp('oms_sell_settlement_detail', $deal_arr);
                            $deal_arr = array();
                            if (1 != $ret['status']) {
                                return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                            }
                        }
                    } else {
                        //说明：“仅退款”的“发货前”
                        foreach ($original_detail[$val] as $detail_key => $detail_arr) {
                            $deal_arr = array(
                                'order_attr' => $order_attr,
                                'deal_code' => $val,
                                'sale_channel_code' => $record['sale_channel_code'],
                                'sell_record_code' => $record['sell_return_code'],
                                'goods_code' => $detail_arr['goods_code'],
                                'spec1_code' => $detail_arr['spec1_code'],
                                'spec2_code' => $detail_arr['spec2_code'],
                                'sku' => $detail_arr['sku'],
                                'barcode' => $detail_arr['barcode'],
                                'num' => $detail_arr['num'],
                                'avg_money' => $detail_arr['avg_money'],
                            );
                            $ret = $this->insert_exp('oms_sell_settlement_detail', $deal_arr);
                            $deal_arr = array();
                            if (1 != $ret['status']) {
                                return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                            }
                        }
                    }
                }

                //补差（退）数据
                if ($is_create_compensate > 0 && $je != 0) {
                    $filter = array('deal_code' => $val, 'settle_type' => 3, 'sell_record_code' => $record['sell_return_code']);
                    unset($ret);
                    $ret = $this->check_exists_by_condition($filter, 'oms_sell_settlement_record');
                    if (1 != $ret['status']) {
                        if ($compensate_by_deal_code_arr['h' . $val]['je'] != 0) {
                            $deal_arr = array(
                                'sell_settlement_code' => $this->generate_settlement_code(),
                                'order_attr' => $order_attr,
                                'deal_code' => $val,
                                'sale_channel_code' => $record['sale_channel_code'],
                                'sell_record_code' => $record['sell_return_code'],
                                'shop_code' => $record['shop_code'],
                                'settle_type' => '3', // 结算类别：1商品 2邮费 3补差
                                'je' => $compensate_by_deal_code_arr['h' . $val]['je'],
                                'create_time' => $create_time
                            );
                            $ret = $this->insert_exp('oms_sell_settlement_record', $deal_arr);
                            $deal_arr = array();
                            if (1 != $ret['status']) {
                                return array('status' => '-1', 'data' => '', 'message' => lang('name_error_nodata'));
                            }
                        }
                    }
                }
            }
        }
        //return array('status' => '-1', 'data' => $deal_arr, 'message' => 'test');

        return array('status' => '1', 'data' => $deal_arr, 'message' => lang('op_success'));
    }

    /**
     *
     * 方法名       generate_settlement_data
     *
     * 功能描述     生成零售结算单数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-18
     * @param       string $record_code [订|退单单号]
     * @param       int $type           [单据性质：1销售 2退货
     * @return      string
     */
    public function generate_settlement_data($record_code, $order_attr) {
        //检查“参数”否为空
        if (empty($record_code) || empty($order_attr)) {
            return array('status' => '-1', 'data' => '', 'message' => lang('op_error_params'));
        }

        //根据“数据处理类”进行数据提取[订|退单数据 && 订|退单详细列表]
        $record = array();
        $detail = array();
        $ret = $this->get_record_and_detail($record, $detail, $record_code, $order_attr);
        unset($ret);

        //检测订退单数据是否存在
        if (empty($record) || empty($detail)) {
            return array('status' => '-1', 'data' => '', 'message' => lang('op_no_data'));
        }

        //按deal_code来组划分入库数据
        $deal_code_list = array();
        $detail_list = array();
        foreach ($detail as $k => $v) {
            $deal_code_list[$v['deal_code']] = $v['deal_code'];
            $detail_list[$v['deal_code']][] = $v;
        }

        //涉及多库操作启动事务
        $this->begin_trans();

        //结算数据生成类型选择器
        switch ((int) $order_attr) {
            //生成订单零售结算单数据【订单商品 | 邮费】
            case 1:
                $ret = $this->generate_settlement_sell($deal_code_list, $detail_list, $record, $order_attr);
                break;

            //生成退单单零售结算单数据【退单商品 | 补差】
            case 2:
                $ret = $this->generate_settlement_return($deal_code_list, $detail_list, $record, $order_attr);
                break;

            //异常处理
            default:
                return array('status' => '-1', 'data' => '', 'message' => lang('op_no_data'));
                break;
        }

        if (1 == $ret['status']) {
            //刷新“零售结算订单表”和“零售结算单”数据
            foreach ($deal_code_list as $key => $val) {
                //此执行选择器：【解决：“仅退货”|“仅退款(发货后)”两种状态下商品详细信息里的商品均摊金额不需要计算到“零售结算订单表”】
                if (!isset($this->is_not_up_arr[$val])) {
                    //刷新“零售结算订单表”
                    $this->count_settlement_record($val, $record_code);
                }
                //刷新“零售结算单”数据
                $this->count_settlement($val);
            }
            $this->commit();
            $ret = array('status' => '1', 'data' => '', 'message' => lang('op_success'));
        } else {
            $this->rollback();
        }
        return $ret;
    }

    /**
     *
     * 方法名       float_amout_round
     *
     * 功能描述     浮点数整分方法
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-20
     * @param       float $price [运费]
     * @param       int $divisor [运费需要均摊的商品个数]
     * @param       int $mulriple [放大倍数（尽量给出10的整数倍）]
     * @return      string
     */
    private function float_amout_round($price, $divisor, $mulriple = 1000) {
        $price = $price * $mulriple;
        $mod = fmod(floatval($price), $divisor);
        $mod = $mod / $mulriple;
        $lcm = intval($price / $divisor) / $mulriple;
        return array('price' => $price, 'mod' => $mod, 'lcm' => $lcm, 'divisor' => $divisor);
    }

}
