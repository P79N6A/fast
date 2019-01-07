<?php
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '256M');
require_model('tb/TbModel');
require_model('base/RegionModel');

abstract class TradeTransferModel extends TbModel {

    protected $source = 0;

    protected $tables = array();

    protected $trade = array();

    protected $tradeItems = array();

    protected $order = array();

    protected $orderItems = array();

    protected $priceType = '';

    /**
     * 初始化函数
     */
    public function __construct() {
        parent::__construct();
    }

    function return_value($status, $message = '', $data = '') {
        return array('status' => $status, 'message' => $message, 'data' => $data);
    }

    /**
     * 读取交易
     * @param $recordId
     */
    abstract protected function get_trade($recordId);

    /**
     * 读取交易明细
     * @param $recordId
     */
    abstract protected function get_items($recordId);

    /**
     * 过滤掉已经退款成功的明细
     */
    abstract protected function remove_refund_items();

    /**
     * 处理各个trade到order的差异部分
     */
    abstract protected function transfer_trade_custom();

    /**
     * 处理各个trade到order的差异部分
     */
    abstract protected function transfer_item_custom(&$orderItem, $tradeItem);

    /**
     * 处理价格金额
     */
    abstract protected function transfer_trade_price();

    /**
     * 获得trade的参数 {deal_code,deal_note,shipping_type,province,city,district,shop_id}
     * @return mixed
     */
    abstract protected function transfer_trade_express();

    /**
     * 保存订单结算信息
     * @param $sell_record_id
     * @return mixed
     */
    abstract protected function transfer_trade_settlement($sell_record_id);

    /**
     * 其他需要处理的转入
     * @param $sell_record_id
     * @return mixed
     */
    abstract protected function transfer_other($sell_record_id);


    /**
     * 转单入口
     * @param int $record_id
     * @return array
     */
    public function transfer_by_id($record_id) {
        //交易
        $this->get_trade($record_id);
        if (empty($this->trade)) {
            return $this->return_value(-1, '待转入交易不存在');
        }
        elseif (2 == $this->trade['is_change']) {
            return $this->return_value(-1, '交易正在转入中');
        }
        elseif (1 == $this->trade['is_change']) {
            return $this->return_value(-1, '交易已经转入,不能重复操作');
        }

        //交易明细
        $this->get_items($record_id);
        if (empty($this->tradeItems)) {
            return $this->return_value(-1, '交易明细不存在');
        }

        //订单已经退款完成的话,直接设置为转入成功
        $this->remove_refund_items();
        if (empty($this->tradeItems)) {
            $this->db->update($this->tables['trade'], array('is_change' => '1'), array('record_id' => $record_id));
            return $this->return_value(-1, '交易已经全部退货');
        }

        try {
            //锁定记录，表示此记录正在转入中
            $this->db->update($this->tables['trade'], array('is_change' => '2'), array('record_id' => $record_id, 'is_change' => 0));

            //$result = $this->_do_trade2order_process();
            $result = $this->transfer();

            //转入成功的话，标记为已完成；转入失败的话，把记录还原
            if (1 == $result['status']) {
                $sell_record_id = $result['data']['sell_record_id'];
                $this->db->update($this->tables['trade'], array('is_change' => '1', 'trade_parse' => ''), array('record_id' => $record_id, 'is_change' => 2));

                //处理转单的额外逻辑(品牌特卖作废)
                $this->transfer_other($sell_record_id);

                // 处理免审规则
                try {
                    //先预分配库存,如果不能预分配库存（缺货）的订单不能进行合并

                    //如果预分配成功的话,再去合并订单

                    //为合并单预分配库存

                    //只有预分配成功有库存的情况下，免审才有执行的必要
                    //不管是合并单还是原始订单都处理免审
                    //load_model('tactic/free_check')->free_check($sell_record_id, true);
                }
                catch (Exception $e) {
                    //print_r($e);
                }
            }
            else{
                throw new Exception($result['message']);
            }

            return $result;
        }
        catch (Exception $e) {
            $this->db->update($this->tables['trade'], array('is_change' => '0'), array('record_id' => $record_id, 'is_change' => 2));
            return $this->return_value(-1, $e->getMessage());
        }
    }

    /**
     * 转单
     * @throws Exception
     * @return array
     */
    private function transfer(){
        $less = '';
        $shop = $this->db->get_row("select * from base_shop where shop_code = :code", array('code'=>$this->trade['shop_code']));
        if(empty($shop)){
            throw new Exception('商店不存在, 请完善商店api参数nick字段');
        }

        $this->priceType = $shop['sell_price_type'];

        //第一步, 初始化主表基本数据,后续会用到
        $this->order['record_code'] = load_model('oms/SellRecordModel')->new_code();
        $this->order['shop_id'] = $shop['shop_id'];
        $this->order['shop_code'] = $shop['shop_code'];
        $this->order['store_code'] = $shop['send_store_code'];
        $this->order['express_code'] = $shop['express_code'];
        $this->order['source'] = $this->source;
        //$this->order['sku_num'] = count($this->tradeItems); //sku种类数
        //$this->order['org_id'] = $this->trade['org_id'];
        //$this->order['org_code'] = $this->trade['org_code'];
        $this->order['pay_code'] = (isset($this->trade['pay_code']) && !empty($this->trade['pay_code'])) ? $this->trade['pay_code'] : '';
        $this->order['pay_status'] = 0;
        $this->order['shipping_status'] = 0;
        //$this->order['num'] = $this->trade['num'];
        $this->order['goods_weigh'] = 0;
        $this->order['num'] = 0;
        $this->order['trans_time'] = date('Y-m-d H:i:s');
        $this->order['is_problem'] = 0;
        $this->order['is_problem_type'] = 0;
        $this->order['is_problem_reason'] = array();

        //转单时间
        $this->order['change_time'] = date('Y-m-d H:i:s');
        /*
        $this->order['goods_money'] = 0;  //商品总额,SUM（实际售价*数量）
        $this->order['express_money'] = 0; // 配送快递费
        $this->order['delivery_money'] = 0; // 配送手续费，货到付款算在买家身上
        $this->order['order_money'] = 0; //订单总金额
        $this->order['order_pay_money'] = 0; //订单应付金额
        $this->order['goods_rebate_money'] = 0; //商品折让总额
        $this->order['other_rebate_money'] = 0; //其他折让总额
        */

        //第二步, 初始化主表各平台特有字段
        $this->transfer_trade_custom();
        //echo '<hr/>order<xmp>'.var_export($this->order,true).'</xmp>';//die;

        //第三步, 创建会员
        $cust_info = array();
        $cust_info['source'] = $this->order['source'];
        $cust_info['shop_code'] = $this->order['shop_code'];
        $cust_info['customer_name'] = $this->order['receiver_name'];
        $cust_info['tel'] = $this->order['receiver_mobile'];
       
        $ret =  load_model('crm/CustomerModel')->add_customer($cust_info);
        if($ret['status']<0){
            throw new Exception($ret['message']);
        }
        $customer_code = $ret['data']['customer_code'];

        $cust_info_addr = array();
        $cust_info_addr['customer_code'] = $customer_code;

        $cust_info_addr['address'] = $this->order['receiver_address'];
        $cust_info_addr['country'] = $this->order['receiver_country'];
        $cust_info_addr['province'] = $this->order['receiver_province'];
        $cust_info_addr['city'] = $this->order['receiver_city'];
        $cust_info_addr['district'] = $this->order['receiver_district'];
        $cust_info_addr['street'] = $this->order['receiver_street'];

        $cust_info_addr['mobile'] = $this->order['receiver_mobile'];
        $cust_info_addr['tel'] = $this->order['receiver_phone'];
        $cust_info_addr['name'] = $this->order['receiver_name'];
        load_model('crm/CustomerModel')->insert_customer_address($cust_info_addr);
        //die;

        //第四步, 初始化明细
        $this->transfer_items();

        $question_by_area = 1; //loadModel('sys_config_model', 'package/sys_config')->get_sys_config('question_by_area');
        if (1 == $question_by_area && isset($this->order["is_problem_type"]) && 0 == $this->order['is_problem_type']) {
            //省市区为空订单设为问题单
            if (empty($this->order['receiver_province']) || empty($this->order['receiver_city'])) {
                $this->order['is_problem'] = 1;
                $this->order['is_problem_type'] = 3;
                $this->order['is_problem_reason'][] = "省市区信息错误";
            }
        }

        //第五步, 整理价格,金额字段
        $this->transfer_trade_price();

        //第六步, 识别快递公司
        //$this->express_adapter();

        //第七步, 保存
        $this->begin_trans();
        try {
            $result = $this->db->get_row(
                "SELECT deal_code FROM oms_sell_record WHERE deal_code = :deal_code",
                array('deal_code' => $this->order['deal_code'])
            );
            if(!empty($result)){
                throw new Exception('已存在相同交易号的订单');
            }

            //合并问题单描述
            $this->order['is_problem_reason'] = implode('|', $this->order['is_problem_reason']);

            $result = $this->db->insert('oms_sell_record', $this->order);
            if ($result !== true) {
                throw new Exception('保存订单出错');
            }
            $sell_record_id = $this->db->insert_id();

            $problem = array(
                'is_problem'=>'0',
                'is_problem_type'=>'0',
                'is_problem_reason'=>'',
            );
            foreach ($this->orderItems as $key => $item) {
                $item['pid'] = $sell_record_id;
                $item['record_code'] = $this->order['record_code'];
                $item['deal_code'] = $this->order['deal_code'];
                $item['lock_num'] = $item['num'];
                $item['lock_inv_status'] = '1';

                $order_goods_result = $this->db->insert('oms_sell_record_detail', $item);
                if ($order_goods_result !== true) {
                    throw new Exception('插入订单详明细出错');
                }

                //库存不足 记录
                /*if(!empty($item['goods_code']) && !empty($item['size_code']) && !empty($item['color_code'])){
                   $arr = load_model('goods/goods_inv')->get_stock($item['goods_code'], $item['color_code'], $item['size_code'], $shop['store_code']);
                   $goods_stock = $arr['stock_num'];
                   $goods_lock  = $arr['lock_num'];
                   $less = '';
                   if($item['num'] > ($goods_stock - $goods_lock)){
                       $less .= '<br />SKU：'.$item['outer_sku_id'].'库存不足';
                   }
                }*/

                if(empty($this->order['is_problem'])) {
                    //锁定库存
                    /*$info = array(
                        'price_type' => 'sell_price',
                        'money' => $item['count_money'],
                        'object_code' => $this->order['shop_code'],
                        'relation_code' => $this->order['record_code'],
                        'type' => '14',
                        'remark' => '订单锁定库存',
                    );
                    $ret = load_model('prm/InvModel')->adjust(
                        $this->order['store_code'],
                        $item['sku'],
                        array('lock_num'=>$item['num']),
                        $this->order['record_time'],
                        $info
                    );
                    if($ret['status'] != '1'){
                        $problem['is_problem'] = '1';
                        $problem['is_problem_type'] = '4';
                        $problem['is_problem_reason'] .= '|商品库存不足:'.$item['sku'];
                    }*/
                }
            }

            if($problem['is_problem'] == '1'){
                $ret = $this->db->update('oms_sell_record', $problem, array('sell_record_id'=>$sell_record_id));
                if($ret !== true){
                    throw new Exception('订单更新失败');
                }
            }

            //保存结算信息
            //$this->transfer_trade_settlement($sell_record_id);

            //处理订单价格信息
            load_model('oms/SellRecordModel')->refresh_record_price($sell_record_id);

            //合并库存锁定失败而产生的问题原因
            $this->order['is_problem_reason'] .= $problem['is_problem_reason'];
            if(empty($this->order['is_problem_reason'])){
                $msg = '转入成功，订单编号为:' . $this->order['record_code'];
            } else {
                $msg = '适配失败，问题:'.$this->order['is_problem_reason'];
            }

            //记录订单转入日志
            load_model('oms/SellRecordActionModel')->add_action($sell_record_id, '转入', $msg);
            load_model('oms/SellRecordActionModel')->add_action_to_api($this->order['sale_channel_code'], $this->order['shop_code'], $this->order['deal_code_list'], 'convert');

            $this->commit();

            return $this->return_value(1, $msg, array('sell_record_id' => $sell_record_id));
        }
        catch (Exception $e) {
            $this->rollback();

            return $this->return_value(-1, $e->getMessage());
        }
    }


    /**
     * 转入订单明细
     */
    private function transfer_items(){
        //TODO:套餐适配
        foreach ($this->tradeItems as $key => $tradeItem) {
            ;
        }

        //整理明细
        $skuArr = array();
        foreach ($this->tradeItems as $key => $tradeItem) {
            $orderItem = array();
            $orderItem['is_problem'] = 0;
            $orderItem['is_problem_type'] = 0;
            $orderItem['is_problem_reason'] = array();

            $this->transfer_item_custom($orderItem, $tradeItem);
            if (empty($orderItem['outer_sku_id'])) {
                $orderItem['outer_sku_id'] = '';
                $orderItem['is_problem'] = 1;
                $orderItem['is_problem_type'] = 1;
                $orderItem['is_problem_reason'] = "商家编码{$orderItem['outer_sku_id']}不存在";
            }

            $orderItem['barcode'] = $orderItem['outer_sku_id'];

            //$sku = parse_sku($orderItem['outer_sku_id']);
            $sql = "select * from goods_sku where barcode = :barcode";
            $barcode = $this->db->get_row($sql, array('barcode'=>$orderItem['outer_sku_id']));

            $sku_code = $orderItem['outer_sku_id'];
	        if (!empty($barcode)) {
                $sku_code = $barcode['sku'];
	        }
            $sql = "select * from goods_sku where sku = :sku";
            $sku = $this->db->get_row($sql, array('sku'=>$sku_code));

            //最后找不到设置为问题单
            if (empty($sku) || !isset($sku['sku_id']) || !isset($sku['sku'])) {
                $orderItem['goods_weigh'] = 0;

                $orderItem['size_id'] = 0;
                $orderItem['sku_id'] = 0;
                $orderItem['sku'] = '';

                $orderItem['goods_code'] = '';
                $orderItem['sort_code'] = '';

                $orderItem['is_problem'] = 1;
                $orderItem['is_problem_type'] = 1;//商品信息错误
                $orderItem['is_problem_reason'] = "商家编码{$orderItem['outer_sku_id']}不存在";

                $this->order['is_problem'] = 1;
                if(empty($this->order['is_problem_type'])) $this->order['is_problem_type'] = 1;//商品信息错误
                $this->order['is_problem_reason'][] = $orderItem['is_problem_reason'];
            }
            else{
                $sql = "select a.*
                from base_goods a

                where a.goods_code = :goods_code";
                $goods = $this->db->get_row($sql, array('goods_code'=>$sku['goods_code']));

                $orderItem['cost_price'] = $goods['cost_price'];
                $orderItem['refer_price'] = $goods['sell_price'];
                $orderItem['goods_weigh'] = isset($goods['weight']) ? $goods['weight'] : 0;
//                $orderItem['spec1_id'] = $sku['spec1_id'];
//                $orderItem['spec2_id'] = $sku['spec2_id'];
                $orderItem['sku_id'] = $sku['sku_id'];
                $orderItem['sku'] = $sku['sku'];
                //$orderItem['goods_id'] = $sku['goods_id'];
//                $orderItem['spec1_code'] = $sku['spec1_code'];
//                $orderItem['spec2_code'] = $sku['spec2_code'];
                $orderItem['goods_code'] = $sku['goods_code'];
                //$orderItem['sort_code'] = get_sort_code_by_goods_id($sku['goods_id']);

                $skuArr[] = $sku['sku_id'];
            }

            $this->order['num'] += $orderItem['num'];
            $this->order['goods_weigh'] += $orderItem['goods_weigh'] * $orderItem['num'];

            $this->orderItems[$key] = $orderItem;
        }

        //sku种类数
        $skuArr = array_unique($skuArr);
        $this->order['sku_num'] = count($skuArr);
    }

    /**
     * 适配订单快递方式
     */
    protected function express_adapter(){
        $params = $this->transfer_trade_express();
        $region = array(
            'receiver_country' => 1,
            'receiver_province' => $this->order['receiver_province'],
            'receiver_city' => $this->order['receiver_city'],
            'receiver_district' => $this->order['receiver_district'],
        );
        $params = array_merge($params, $region);

        $params['goods_sort_arr'] = array();
        foreach ($this->orderItems as $_goods ){
            if(isset($_goods['sort_code']) && !empty($_goods['sort_code'])){
                $params['goods_sort_arr'][] = $_goods['sort_code'];
            }
        }

        $params['express_code'] = $this->trade['express_code'];//中间表可能已经解析过的快递

        $result = load_model('tactic/order_express_adapt')->adapt_order($params,1);

        if (1 == $result['status']) {
            $express = $result['data']['express'];
            $this->order['express_id'] = (isset($express['express_id']) && !empty($express['express_id'])) ? $express['express_id'] : 0;
            $this->order['express_code'] = (isset($express['express_code']) && !empty($express['express_code'])) ? $express['express_code'] : '';
            $this->order['express_name'] = (isset($express['express_name']) && !empty($express['express_name'])) ? $express['express_name'] : '';
        }
        else{
            //问题单
            $this->order['is_problem'] = 1;
            $this->order['is_problem_reason'][] = $result['message'];

            //快递公司不可到达
            if (empty($this->order['is_problem_type'])) $this->order['is_problem_type'] = 2; //'EXPRESS_OUT_REGION';
        }
    }
}
