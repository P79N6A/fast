<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class OrderGiftModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record';
    protected $detail_table = 'oms_sell_record_detail';
    //转单状态explode
    public $tran_status = array(
        0 => '未转单',
        1 => '已转单',
    );
    //订单状态
    public $order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废',
        5 => '已完成',
    );
    //付款状态
    public $pay_status = array(
        0 => '未付款',
        2 => '已付款',
    );
    //发货状态
    public $shipping_status = array(
        0 => '未发货',
        1 => '已通知配货',
        2 => '拣货中',
        3 => '已完成拣货',
        4 => '已发货',
    );
    //支付类型
    public $pay_type = array(
        'secured' => '担保交易',
        'nosecured' => '在线支付',
        'cod' => '货到付款',
    );
    //拣货状态
    public $waves_record_status = array(
        '0' => '未生成波次单',
        '1' => '已生成波次单',
    );
    //有无赠品
    public $is_gift_status = array(
        '0' => '无',
        '1' => '有',
    );
    public $is_back = array(
        0 => '未回写',
        1 => '回写成功',
        2 => '回写失败',
    );
    public $sale_channel_data_map;

    function get_sale_channel_name_by_code($code) {
        if (!isset($this->sale_channel_data_map)) {
            $this->sale_channel_data_map = load_model('base/SaleChannelModel')->get_data_map();
        }
        $ret_v = isset($this->sale_channel_data_map[$code]) ? $this->sale_channel_data_map[$code] : '';
        return $ret_v;
    }

    function get_select_is_back() {
        $is_back_t = $this->is_back;
        $is_back = array();
        foreach ($is_back_t as $k => $back_status) {
            $is_back[] = array($k, $back_status);
        }
        return $is_back;
    }

    function td_list_by_page($filter) {
        $filter['is_problem'] = '1';
        return $this->get_by_page($filter);
    }

    /**
     * 去除数组元素空格
     * @param array $array
     * @return array
     */
    function trim_array($array) {
        if (!is_array($array)) {
            return trim($array);
        }
        return array_map(array($this, 'trim_array'), $array);
    }

    function do_list_by_page($filter) {
        $filter['ref'] = 'do';
        //$filter['is_problem'] = '0';
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $keyword = trim($filter['keyword']);
            if ($filter['keyword_type'] == 'deal_code') {
                $keyword = preg_split('/[,，]+/', $keyword);
                $keyword = $this->trim_array($keyword);
            }
            $filter[$filter['keyword_type']] = $keyword;
        }
        if (isset($filter['is_gift']) && $filter['is_gift'] == 'all') {
            unset($filter['is_gift']);
        }
        if (isset($filter['pay_type']) && $filter['pay_type'] == 'all') {
            unset($filter['pay_type']);
        }
        if (isset($filter['notice_flag']) && $filter['notice_flag'] == 'all') {
            unset($filter['notice_flag']);
        }
        if (isset($filter['shipping_flag']) && $filter['shipping_flag'] == 'all') {
            unset($filter['shipping_flag']);
        }
        if (isset($filter['cancel_flag']) && $filter['cancel_flag'] == 'all') {
            unset($filter['cancel_flag']);
        }
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao'] == 'all') {
            unset($filter['is_fenxiao']);
        }
        
        return $this->get_by_page($filter);
    }

    function shipped_list_by_page($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        return $this->get_by_page($filter);
    }

    function shipped_count($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        // 汇总
        $sqlArr = $this->get_by_page($filter, true);
        $sqlArr = $sqlArr['data'];

        $sql = " select sum(rl.paid_money) paid_money
        , sum(rl.express_money) express_money
        , sum(rl.goods_num) goods_num
        , count(rl.sell_record_code) record_count ";
        $sql .= $sqlArr['from'];

        return $this->db->get_row($sql, $sqlArr['params']);
    }

    function ex_list_by_page($filter) {
        $filter['ref'] = 'ex';
        //$filter['is_problem'] = '0';

        $tab = empty($filter['ex_list_tab']) ? 'tabs_pay' : $filter['ex_list_tab'];

        switch ($tab) {
            case 'tabs_all'://全部
                ;
                break;
            case 'tabs_pay'://待付款
                $filter['order_status'] = 0;
                $filter['pay_status'] = 0;
                $filter['pay_type_td'] = 'cod';
                break;
            case 'tabs_confirm'://待确认
                $filter['order_status'] = 0;
                $filter['must_occupy_inv'] = 1;
                break;
            case 'tabs_notice_shipping'://待通知配货
                $filter['order_status'] = 1;
                $filter['shipping_status'] = 0;
                break;
        }
        return $this->get_by_page($filter);
    }

    /**
     * 根据条件查询数据<br>
     * 注意: 此方法为核心公共方法, 受多处调用, 请慎重修改.
     * @param $filter
     * @param $onlySql
     * @param $select
     * @return array
     */
    function get_by_page($filter, $onlySql = false, $select = 'rl.*') {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $keyword = trim($filter['keyword']);
            if ($filter['keyword_type'] == 'deal_code') {
                $keyword = preg_split('/[,，]+/', $keyword);
                $keyword = $this->trim_array($keyword);
            }
            $filter[$filter['keyword_type']] = $keyword;
        }
                
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 AND (rl.pay_status=2 OR rl.pay_type='cod') AND rl.order_status = 0 AND rl.waves_record_id = ''";
        //订单赠品工具
        if(isset($filter['list_type']) && $filter['list_type'] == 'order_gift_do_list') {
            $sql_main .= " AND (is_fenxiao <> 2 OR (is_fenxiao = 2 && is_fx_settlement = 0))";
        }

        $bak_sql_main = $sql_main;
        $sql_one_main_arr = array();
        $sql_one_values = array();

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        if (isset($filter['action_type']) && $filter['action_type'] !== '') {
            $sql_main = "FROM {$this->table} rl left join  {$this->detail_table} rr on  rl.sell_record_code = rr.sell_record_code  $sql_join WHERE 1 ";
        }
        $sub_values = array();
        $sql_sub = "SELECT rr.sell_record_code FROM {$this->detail_table} rr WHERE 1";

        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " rl.is_lock = 1";
                }
                if ($attr == 'attr_pending') {
                    $sql_attr_arr[] = " rl.is_pending = 1";
                }
                if ($attr == 'attr_problem') {
                    $sql_attr_arr[] = " rl.is_problem = 1";
                }
                if ($attr == 'attr_bf_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 2)";
                }
                if ($attr == 'attr_all_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 0)";
                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " rl.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " rl.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " rl.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " rl.is_replenish = 1";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }
        //echo '<hr/>$sql_main<xmp>'.var_export($sql_main,true).'</xmp>';die;  
        //订单状态
        // if (isset($filter['order_status']) && $filter['order_status'] <> '') {
        //         if($filter['order_status'] == 0){
        //          $sql_main .= " AND rl.order_status = :order_status AND rl.shipping_status<>1";
        //          $sql_values[':order_status'] = $filter['order_status'];
        //         }
        //         if($filter['order_status'] == '1'){
        //         $sql_main .= " AND rl.order_status = :order_status AND rl.shipping_status<>1";
        //         $sql_values[':order_status'] = $filter['order_status'];
        //         }
        //         if($filter['order_status'] == '2'){
        //         $sql_main .= " AND rl.order_status = :order_status AND rl.shipping_status = 1";
        //         $sql_values[':order_status'] = '1';
        //         }
        // }   
        //货到付款
        if (isset($filter['pay_type']) && $filter['pay_type'] !== '') {
            if ($filter['pay_type'] == '0') {
                $sql_main .= " AND rl.pay_type != :pay_type ";
                $sql_values[':pay_type'] = 'cod';
            } else {
                $sql_main .= " AND rl.pay_type = :pay_type ";
                $sql_values[':pay_type'] = $filter['pay_type'];
            }
        }

        //是否已通知配货
        if (isset($filter['notice_flag'])) {
            if ($filter['notice_flag'] == '0') {
                $sql_main .= " AND rl.shipping_status = 0 ";
            } elseif ($filter['notice_flag'] == '1') {
                $sql_main .= " AND rl.shipping_status >= 1 ";
            }
        }
        //是否已发货
        if (isset($filter['shipping_flag'])) {
            if ($filter['shipping_flag'] == '0') {
                $sql_main .= " AND rl.shipping_status < 4 ";
            } elseif ($filter['shipping_flag'] == '1') {
                $sql_main .= " AND rl.shipping_status = 4 ";
            }
        }
        //是否分销单 
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao']!='') {
            if($filter['is_fenxiao'] == 1) {
                $sql_main .= " AND (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
            } else if($filter['is_fenxiao'] == 0) {
                $sql_main .= " AND (rl.is_fenxiao = 0) ";
            }
        }
        //是否作废
        if (isset($filter['cancel_flag'])) {
            if ($filter['cancel_flag'] == '0') {
                $sql_main .= " AND rl.order_status <> 3 ";
            } elseif ($filter['cancel_flag'] == '1') {
                $sql_main .= " AND rl.order_status = 3 ";
            }
        }
        //sell_record_code
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
             $arr = explode(',',$filter['sell_record_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_one_values);
            $sql_one_main_arr['sell_record_code'] = " AND rl.sell_record_code in ( " .$str . " ) ";
        }
        //正常单或非正常单
        if (isset($filter['is_normal']) && $filter['is_normal'] !== '') {
            $is_normal = '';
            if ($filter['is_normal'] == '2') {//非正常
                //缺货
                $is_normal .= " (rl.must_occupy_inv = '1' AND (rl.lock_inv_status <> '1' ))";
                //挂起
                $is_normal .= " OR rl.is_pending = '1' ";
                //设问
                $is_normal .= " OR rl.is_problem = '1' ";
            }
            if ($filter['is_normal'] == '1') {//正常
                //缺货
                $is_normal .= " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1') ";
                //挂起
                $is_normal .= " AND rl.is_pending = '0' ";
                //设问
                $is_normal .= " AND rl.is_problem = '0' ";
            }
            $sql_main .= " AND ({$is_normal})";
        }
        //is_my_lock
        //是否是我锁定的
        if (isset($filter['is_my_lock']) && $filter['is_my_lock'] == '1') {
            $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
            $sql_main .= " AND rl.is_lock = 1 AND rl.is_lock_person = :user_code";
            $sql_values[':user_code'] = $sys_user['user_code'];
        }
        //交易号
        if (isset($filter['deal_code_list']) && $filter['deal_code_list'] !== '') {
//            
//            $deal_code_list = '';
//            if(!is_array($filter['deal_code_list'])){
//                $filter['deal_code_list'] = str_replace('，',',',$filter['deal_code_list']);
//                $filter['deal_code_list'] = explode(',',$filter['deal_code_list']);
//            }
//            foreach ($filter['deal_code_list'] as $k => $v) {
//                if (!empty($v)){
//                    if ($k == 0) {
//                        $deal_code_list = "rl.deal_code_list like '%{$v}%'";
//                    } else {
//                        $deal_code_list .= " or rl.deal_code_list like '%{$v}%'";
//                    }
//                }
//                
//            }
            $arr = explode(',',$filter['deal_code_list']);
            $deal_code_list = $this->arr_to_like_sql_value($arr, 'deal_code_list', $sql_one_values,'rl.');
            if (!empty($deal_code_list)){
                //$sql_one_main_arr['deal_code_list'] = " AND {$deal_code_list} ";
                $sql_one_main_arr['deal_code_list'] = " AND {$deal_code_list} ";
            }
            
            //$sql_one_values[':deal_code'] = $deal_code;
        }
        //交易号
        if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            $sql_one_main_arr['express_no'] = " AND rl.express_no like :express_no ";
            $sql_one_values[':express_no'] = "%" . $filter['express_no'] . "%";
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
             $arr = explode(',',$filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( " .$str . " ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
        $arr = explode(',',$filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " .$str . " ) ";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
    $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        //支付宝交易号
        if (isset($filter['alipay_no']) && $filter['alipay_no'] !== '') {
            if ($filter['alipay_no'] == '0') {
                $sql_main .= " AND rl.alipay_no = '' ";
            } else {
                $sql_main .= " AND rl.alipay_no <> '' ";
            }
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {

         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){
                       $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                       $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";    
              
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }    
//            $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = "%" . $filter['buyer_name'] . "%";
        }

        //买家留言
        if (isset($filter['is_buyer_remark']) && $filter['is_buyer_remark'] != '') {
            if ($filter['is_buyer_remark'] == '1') {
                $sql_main .= " AND rl.buyer_remark <> ''";
            } else {
                $sql_main .= " AND rl.buyer_remark = ''";
            }
        }
        //商家留言
        if (isset($filter['is_seller_remark']) && $filter['is_seller_remark'] != '') {
            if ($filter['is_seller_remark'] == '1') {
                $sql_main .= " AND rl.seller_remark <> ''";
            } else {
                $sql_main .= " AND rl.seller_remark = ''";
            }
        }

		//优化，将子查询带入状态，未确认=0，减少范围
		$sql_sub = '';
        
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_sub .= " AND oms_sell_record_detail.goods_code = :goods_code ";
            $sub_values[':goods_code'] = $filter['goods_code'];
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $goods_name_sql = "select goods_code from base_goods where goods_name like :goods_name";
            $goods_name_sql_values = array(':goods_name' => '%'.$filter['goods_name'].'%');
            $goods_code_arr = $this->db->get_all_col($goods_name_sql, $goods_name_sql_values);
            if (empty($goods_code_arr)) {
                $sql_sub .= " AND 1=2 ";
            } else {
                $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
                $sql_sub .= " AND oms_sell_record_detail.goods_code in({$goods_code_str}) ";
            }
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_sub .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_sub .= " AND oms_sell_record_detail.sku in({$sku_str}) ";
            }
        }

        //增加套餐查询
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $combo_sku_arr = load_model('prm/GoodsComboModel')->get_combo_sku_by_barcode(trim($filter['combo_barcode']));

            if (!empty($combo_sku_arr)) {
      
            $combo_sku_str = $this->arr_to_in_sql_value($combo_sku_arr, 'combo_sku', $sql_values);
                $sql_sub .= " AND oms_sell_record_detail.combo_sku in ({$combo_sku_str}) ";
            } else {
                $sql_sub .= " AND 1=2 ";
            }
            
        }
        //有无赠品
        
        if (isset($filter['is_gift'])) {
        	
            if ($filter['is_gift'] != '0') {
                
                $sql_sub .= " AND oms_sell_record_detail.is_gift = 1 ";
                
            } else {
           	
				//业务特殊，无法纳入统一的子查询，独立出来
            	$sql_sub_have_gift = " select oms_sell_record.sell_record_code from oms_sell_record , oms_sell_record_detail 
where oms_sell_record.sell_record_code = oms_sell_record_detail.sell_record_code and 
oms_sell_record.order_status = 0 and oms_sell_record_detail.is_gift = 1 ";
            	
            	 $sql_main .= " AND rl.sell_record_code not in (" . $sql_sub_have_gift . ")";
            $sql_values = array_merge($sql_values, $sub_values);
            }
        }
        
		//查看是否有子查询，带入条件
        if(!empty($sql_sub))
        {
        	$sql_sub_main = " select oms_sell_record.sell_record_code from oms_sell_record , oms_sell_record_detail 
where oms_sell_record.sell_record_code = oms_sell_record_detail.sell_record_code and 
oms_sell_record.order_status = 0 ";
        	
        	$sql_sub = $sql_sub_main . $sql_sub;
        	
        	$sql_main .= " AND rl.sell_record_code in (" . $sql_sub . ")";
            $sql_values = array_merge($sql_values, $sub_values);
        }

        //换货单
        if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
            $sql_main .= " AND rl.is_change_record = :is_change_record ";
            $sql_values[':is_change_record'] = $filter['is_change_record'];
        }
        //商家留言
        if (isset($filter['seller_remark']) && $filter['seller_remark'] !== '') {
            $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
            $sql_values[':seller_remark'] = "%" . $filter['seller_remark'] . "%";
        }
        //买家留言
        if (isset($filter['buyer_remark']) && $filter['buyer_remark'] !== '') {
            $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
            $sql_values[':buyer_remark'] = "%" . $filter['buyer_remark'] . "%";
        }
        //订单备注
        if (isset($filter['order_remark']) && $filter['order_remark'] !== '') {
            $sql_main .= " AND rl.order_remark LIKE :order_remark ";
            $sql_values[':order_remark'] = "%" . $filter['order_remark'] . "%";
        }
        //收货人
        if (isset($filter['receiver_name']) && $filter['receiver_name'] !== '') {
//            $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//            $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
        }
        //手机号
        if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] !== '') {
                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'],'tel');
                if(!empty($customer_address_id)){
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_one_main_arr['receiver_mobile'] = " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_one_values[':receiver_mobile'] = $filter['receiver_mobile'];
                }else{
                        $sql_one_main_arr['receiver_mobile'] = " AND rl.receiver_mobile = :receiver_mobile ";
                        $sql_one_values[':receiver_mobile'] = $filter['receiver_mobile']; 
                }
//            $sql_one_main_arr['receiver_mobile'] = " AND rl.receiver_mobile LIKE :receiver_mobile ";
//            $sql_one_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
               $arr = explode(',',$filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code in ( " . $str . " ) ";
        }
        //快递单号
        if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            $sql_one_main_arr['express_no'] = " AND rl.express_no LIKE :express_no ";
            $sql_one_values[':express_no'] = '%' . $filter['express_no'] . '%';
        }
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $sql_main .= " AND rl.receiver_country = :country ";
            $sql_values[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND rl.receiver_province = :province ";
            $sql_values[':province'] = $filter['province'];
        }
        //城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $sql_main .= " AND rl.receiver_city = :city ";
            $sql_values[':city'] = $filter['city'];
        }
        //地区
        if (isset($filter['district']) && $filter['district'] !== '') {
            $sql_main .= " AND rl.receiver_district = :district ";
            $sql_values[':district'] = $filter['district'];
        }
        //详细地址

        if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
            $filter['receiver_addr'] = str_replace('，', ',', $filter['receiver_addr']);
            $_addr_like_arr = explode(',', $filter['receiver_addr']);
            $_addr_sql = array();
            foreach ($_addr_like_arr as $k => $_like_v) {
                $_addr_sql[] = "rl.receiver_address LIKE :receiver_addr" . $k;
                $sql_values[':receiver_addr' . $k] = '%' . trim($_like_v) . '%';
            }
            $sql_main .= " and (" . join(' or ', $_addr_sql) . ")";
        }

        //缺货状态
        if (isset($filter['is_stock_out']) && $filter['is_stock_out'] !== '') {
            $sql_main .= " AND must_occupy_inv=1 AND lock_inv_status in (:is_stock_out) ";
            $sql_values[':is_stock_out'] = $filter['is_stock_out'];
        }
        //预售单
        if (isset($filter['sale_mode']) && ($filter['sale_mode'] == '0' || $filter['sale_mode'] == '1')) {
            $sale_mode = 'stock';
            if ($filter['sale_mode'] == '1') {
                $sale_mode = 'presale';
            } elseif ($filter['sale_mode'] == '0') {
                $sale_mode = 'stock';
            }
            $sql_sub .= " AND sale_mode = :sale_mode ";
            $sql_values[':sale_mode'] = $sale_mode;
        }
        //锁定人
        if (isset($filter['is_lock_person']) && $filter['is_lock_person'] !== '') {
            $s_sql = "select user_code from sys_user where user_name = :user_name";
            $is_lock_person = ctx()->db->getOne($s_sql, array(':user_name' => $filter['is_lock_person']));
            if (empty($is_lock_person)) {
                $sql_main .= " and 1 != 1";
            } else {
                $sql_main .= " AND rl.is_lock_person = :is_lock_person ";
                $sql_values[':is_lock_person'] = $is_lock_person;
            }
        }
        //锁定单
        if (isset($filter['is_lock']) && $filter['is_lock'] !== '') {
            $sql_main .= " AND rl.is_lock = :is_lock ";
            $sql_values[':is_lock'] = $filter['is_lock'];
        }
        //挂起单
        if (isset($filter['is_pending']) && $filter['is_pending'] !== '') {
            $sql_main .= " AND rl.is_pending = :is_pending ";
            $sql_values[':is_pending'] = $filter['is_pending'];
        }
        //问题单
        if (isset($filter['is_problem']) && $filter['is_problem'] !== '') {
            $sql_main .= " AND rl.is_problem = :is_problem ";
            $sql_values[':is_problem'] = $filter['is_problem'];
        }
        //手工单
        if (isset($filter['is_handwork']) && $filter['is_handwork'] !== '') {
            $sql_main .= " AND rl.is_handwork = :is_handwork ";
            $sql_values[':is_handwork'] = $filter['is_handwork'];
        }
        //合并单
        if (isset($filter['is_combine']) && $filter['is_combine'] !== '') {
            $sql_main .= " AND rl.is_combine = :is_combine ";
            $sql_values[':is_combine'] = $filter['is_combine'];
        }
        //拆分单
        if (isset($filter['is_split']) && $filter['is_split'] !== '') {
            $sql_main .= " AND rl.is_split = :is_split ";
            $sql_values[':is_split'] = $filter['is_split'];
        }
        //复制单
        if (isset($filter['is_copy']) && $filter['is_copy'] !== '') {
            $sql_main .= " AND rl.is_copy = :is_copy ";
            $sql_values[':is_copy'] = $filter['is_copy'];
        }
        //商品数量
        if (isset($filter['num_start']) && $filter['num_start'] !== '') {
            $sql_main .= " AND rl.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (isset($filter['num_end']) && $filter['num_end'] !== '') {
            $sql_main .= " AND rl.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //有无发票
        if (isset($filter['is_invoice']) && ($filter['is_invoice'] == '0' || $filter['is_invoice'] == '1')) {
            if ($filter['is_invoice'] == '0') {
                $sql_main .= " AND rl.invoice_title = '' ";
            } else {
                $sql_main .= " AND rl.invoice_title <> '' ";
            }
        }
        //发票抬头
        if (isset($filter['invoice_title']) && $filter['invoice_title'] !== '') {
            $sql_main .= " AND rl.invoice_title LIKE :invoice_title ";
            $sql_values[':invoice_title'] = '%' . $filter['invoice_title'] . '%';
        }
        //是否含运费
        $contain_express_money = 0;
        if (isset($filter['contain_express_money']) && $filter['contain_express_money'] == '1') {
            $contain_express_money = 1;
        }
        //订单价格
        if (isset($filter['money_start']) && $filter['money_start'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money>= :money_start";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money >= :money_start";
            }
            $sql_values[':money_start'] = $filter['money_start'];
        }
        if (isset($filter['money_end']) && $filter['money_end'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money<= :money_end";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money <= :money_end";
            }
            $sql_values[':money_end'] = $filter['money_end'];
        }

        //下单时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            //赠品工具页面 下单时间 支付时间  时间格式有时分秒所以不需要加后缀
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            }
        }
        //支付时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['pay_time_start'])) {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            } else {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            } else {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            }
        }
        //订单标签 -- 赠品工具
        //添加标签查询 order_tag
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $tag_record = load_model('oms/SellRecordTagModel')->get_sell_record_by_tag($tag_arr);
            if (!empty($tag_record)) {
                $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ({$tag_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //发货时间
        if (!empty($filter['send_time_start'])) {
            $sql_main .= " AND rl.delivery_time >= :send_time_start ";
            $sql_values[':send_time_start'] = $filter['send_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['send_time_end'])) {
            $sql_main .= " AND rl.delivery_time <= :send_time_end ";
            $sql_values[':send_time_end'] = $filter['send_time_end'] . ' 23:59:59';
        }
        //计划发货时间
        if (!empty($filter['plan_send_time_start'])) {
            $sql_main .= " AND rl.plan_send_time >= :plan_send_time_start ";
            $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['plan_send_time_end'])) {
            $sql_main .= " AND rl.plan_send_time <= :plan_send_time_end ";
            $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'] . ' 23:59:59';
        }

        //确认时间
        if (!empty($filter['check_time_start'])) {
            $sql_main .= " AND rl.check_time >= :check_time_start ";
            $sql_values[':check_time_start'] = $filter['check_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['check_time_end'])) {
            $sql_main .= " AND rl.check_time <= :check_time_end ";
            $sql_values[':check_time_end'] = $filter['check_time_end'] . ' 23:59:59';
        }
        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND rl.is_notice_time >= :is_notice_time_start ";
            $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND rl.is_notice_time <= :is_notice_time_end ";
            $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'] . ' 23:59:59';
        }

        //订单重量
        if (isset($filter['weight_start']) && $filter['weight_start'] !== '') {
            $sql_main .= " AND rl.goods_weigh >= :weight_start ";
            $sql_values[':weight_start'] = $filter['weight_start'];
        }
        if (isset($filter['weight_end']) && $filter['weight_end'] !== '') {
            $sql_main .= " AND rl.goods_weigh <= :weight_end ";
            $sql_values[':weight_end'] = $filter['weight_end'];
        }
        //淘宝卖家备注旗帜
        if (isset($filter['seller_flag']) && $filter['seller_flag'] !== '') {
            $_seller_flag = array_map("intval", explode(',', $filter['seller_flag']));
            ;
            $sell_flag_list = join(',', $_seller_flag);
            $sql_main .= " AND rl.seller_flag in({$sell_flag_list})";
        }

        //增值服务 //优化，去掉增值服务渠道项，机制有问题
        //$sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        $order_by = " ORDER BY plan_send_time,pay_time";
        //var_dump($filter,$select,$sql_main,$sql_values);

        if (!empty($sql_one_main_arr)) {
            foreach ($sql_one_main_arr as $k => $v) {
                $sql_one_main_first = $v;
                $sql_one_main_kk = $k;
                break;
            }
            $sql_values = $sql_one_values;
            $sql_main = $bak_sql_main . $sql_one_main_first;
          //  $sql_values = array();
//            if (isset($sql_one_values[':' . $sql_one_main_kk])) {
//                $sql_values[':' . $sql_one_main_kk] = $sql_one_values[':' . $sql_one_main_kk];
//            }
            if (isset($filter['ex_list_tab']) && $filter['ex_list_tab'] !== '') {
                if ($filter['ex_list_tab'] == 'tabs_pay') {
                    $sql_main .= " and rl.order_status = 0 and rl.pay_status = 0";
                }
                if ($filter['ex_list_tab'] == 'tabs_confirm') {
                    $sql_main .= " and rl.order_status = 0 and rl.must_occupy_inv = 1";
                }
                if ($filter['ex_list_tab'] == 'tabs_notice_shipping') {
                    $sql_main .= " and rl.order_status = 1 and rl.shipping_status = 0";
                }
            }
            //商品数量
            if (isset($filter['num_start']) && $filter['num_start'] !== '') {
                $sql_main .= " AND rl.goods_num >= :num_start ";
                $sql_values[':num_start'] = $filter['num_start'];
            }
            if (isset($filter['num_end']) && $filter['num_end'] !== '') {
                $sql_main .= " AND rl.goods_num <= :num_end ";
                $sql_values[':num_end'] = $filter['num_end'];
            }

            //订单价格
            if (isset($filter['money_start']) && $filter['money_start'] !== '') {
                if ($contain_express_money == 1) {
                    $sql_main .= " AND rl.payable_money>= :money_start";
                } else {
                    $sql_main .= " AND rl.payable_money - rl.express_money >= :money_start";
                }
                $sql_values[':money_start'] = $filter['money_start'];
            }
            if (isset($filter['money_end']) && $filter['money_end'] !== '') {
                if ($contain_express_money == 1) {
                    $sql_main .= " AND rl.payable_money<= :money_end";
                } else {
                    $sql_main .= " AND rl.payable_money - rl.express_money <= :money_end";
                }
                $sql_values[':money_end'] = $filter['money_end'];
            }

        }

        if ($onlySql) {
            $sql = array('select' => $select, 'from' => $sql_main, 'params' => $sql_values);
            return array('status' => '1', 'data' => $sql, 'message' => '仅返回SQL');
        }

        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }

        $sql_main .= $group_by;
        $sql_main .= $order_by;

        if (isset($filter['action_type']) && $filter['action_type'] !== '') {
            $select = ' rl.*,rr.* ';
            $sql = "select " . $select . $sql_main;
            $data = load_model('common/BaseModel')->get_all($sql, $sql_values);
        } else {
			$select = ' rl.sale_channel_code,rl.sell_record_code,rl.shop_code,rl.store_code,rl.deal_code_list,rl.record_time,rl.pay_time,rl.payable_money ';
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        }

        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+store_code')
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();

		//优化，将原有每单查询是否有赠品，调整为批量查询
		$sell_record_code_list = array();
		foreach ($data['data'] as $key => &$value) {
            $sell_record_code_list [] = $value['sell_record_code'];
        }
        
        $sell_record_code_list_str = "'".implode("','",$sell_record_code_list)."'";
        
        $sql = "select rr.sell_record_code,rr.is_gift from oms_sell_record_detail rr where rr.sell_record_code IN ({$sell_record_code_list_str}) AND rr.is_gift = 1";
        $details = ctx()->db->get_all($sql);

        foreach ($data['data'] as $key => &$value) {
            $value['status_text'] = $this->get_sell_record_tag_img($value, $sys_user);
            $url = "?app_act=oms/sell_record/view&sell_record_code={$value['sell_record_code']}&ref={$filter['ref']}";
            $_url = base64_encode($url);
            $u = "javascript:openPage('{$_url}', '{$url}', '订单详情')";

            $value['sell_record_code_href'] = "<a href=\"{$url}\">" . $value['sell_record_code'] . "</a>";
            $value['payable_money'] = sprintf("%.2f", $value['payable_money']);
            if ($value['pay_type'] == 'cod') {
                $value['pay_time'] = '';
            }
            
			$value['is_gift_status'] = $this->is_gift_status['0'];
            
            foreach ($details as $detail)
            {
            	if($value['sell_record_code'] == $detail['sell_record_code'])
            	{
            		$value['is_gift_status'] = $this->is_gift_status['1'];
            	}
            }

        }

        return $this->format_ret(1, $data);
    }

    /**
     * Conditional Counting
     * @param $t
     * @return array
     */
    public function count_by($t) {
        $w = ' 1 ';

        switch ($t) {
            case 'all': //Orders recently created within 24 hours
                $time = time() - 60 * 60 * 24;
                $w .= " AND is_problem = 0 AND record_time >= $time";
                break;
            case 'pay':
                $w .= " AND order_status = 0 AND is_problem = 0 AND pay_status = 0 ";
                break;
            case 'confirm':
                $w .= " AND order_status = 0 AND is_problem = 0 AND pay_status = 2 ";
                break;
            case 'print':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 2 AND is_print_express = 0 ";
                break;
            case 'send':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 2 AND is_print_express = 1 AND is_print_sellrecord = 1 ";
                break;
            case 'back':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 7 ";
                break;
        }

        return $this->db->get_value("select count(*) from oms_sell_record where $w");
    }

    /**
     * 保存平台订单外部编码
     * @param $b
     * @return array
     */
    public function td_save($b) {
        $this->begin_trans();
        try {
            foreach ($b as $id => $barcode) {
                $sql = "select * from goods_sku where barcode = :barcode";
                $sku = $this->db->get_row($sql, array('barcode' => $barcode));

                if (empty($sku)) {
                    throw new Exception('保存失败,条码不存在: ' . $barcode);
                }

                $r = $this->db->update('oms_sell_record_detail', array('barcode' => $barcode), array('sell_record_detail_id' => $id));

                if ($r !== true) {
                    throw new Exception('保存失败');
                }
            }

            $this->commit();
            return array('status' => 1, 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    public function component($sellRecordCode, $types) {
        $response = array();

        //读取订单
        $response['record'] = $this->get_record_by_code($sellRecordCode);
        if (empty($response['record'])) {
            return $response = array();
        }

        //整理订单状态
        $status = '';
        if (isset($response['record']['is_pending']) && $response['record']['is_pending'] == 1) {
            $response['record']['status'] = '已挂起';
        } else {
            $response['record']['status'] = $this->order_status[$response['record']['order_status']];
        }
        $response['record']['status'] .= ' ' . $this->shipping_status[$response['record']['shipping_status']];
        $response['record']['status'] .= ' ' . $this->pay_status[$response['record']['pay_status']];

        $response['record']['is_back_txt'] = ' ' . $this->is_back[$response['record']['is_back']];

        $response['record']['sale_channel_name'] = $this->get_sale_channel_name_by_code($response['record']['sale_channel_code']);
        $response['record']['pay_type_name'] = $this->pay_type[$response['record']['pay_type']];

        $response['record']['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $response['record']['shop_code']));
        $response['record']['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $response['record']['store_code']));
        $response['record']['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $response['record']['pay_code']));
        $response['record']['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $response['record']['express_code']));
        $ql_arr = load_model('base/QuestionLabelModel')->get_map_data();

        if ($response['record']['is_problem'] > 0) {
            $problem_ret = $this->get_problem_desc($response['record']['sell_record_code']);
            if (isset($problem_ret['tag_v'])) {
                foreach ($problem_ret['tag_v'] as $v) {
                    $problem_ret['tag_name'][] = $ql_arr[$v];
                }
                $response['record']['problem_desc'] = join(' | ', $problem_ret['tag_name']);
            }
        }
        if ($response['record']['is_pending'] > 0) {
            $response['record']['is_pending_desc'] = oms_tb_val("base_suspend_label", "suspend_label_name", array('suspend_label_code' => $response['record']['is_pending_code']));
        }
        if ($response['record']['is_lock'] > 0) {
            $sql = "select user_name from sys_user where user_code = '{$response['record']['is_lock_person']}'";
            $response['record']['is_lock_person_name'] = ctx()->db->getOne($sql);
        }

        //关联的退单
        $sql = "select sell_return_code from oms_sell_return where return_order_status<>3 and sell_record_code = :sell_record_code";
        $sell_return_codes = ctx()->db->get_col($sql, array(':sell_record_code' => $response['record']['sell_record_code']));
        $response['record']['sell_return_codes'] = $sell_return_codes;


        //取商品明细时, 读取详情数据
        if (array_search('detail', $types) !== false) {
            $response['detail_list'] = $this->get_detail_by_sell_record_code($sellRecordCode, 1);
            if (!empty($response['detail_list'])) {
                foreach ($response['detail_list'] as $key => &$value) {
                    $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
                    $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
                    $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
                }
            }
        }
        $status_info = array();
        $status_info[1] = array('time' => explode(" ", $response['record']['record_time']));
        if ($response['record']['pay_status'] > 0) {
            $status_info[2] = array('time' => explode(" ", $response['record']['pay_time']));
        }
        if ($response['record']['order_status'] > 0) {
            $status_info[3] = array('time' => explode(" ", $response['record']['check_time']));
        }
        if ($response['record']['shipping_status'] > 0) {
            $status_info[4] = array('time' => explode(" ", $response['record']['check_time']));
        }
        $deliver_record_data = array();
        if ($response['record']['waves_record_id'] > 0) {
            $deliver_record_data = $this->db->get_row("select deliver_record_id,is_deliver from oms_deliver_record where sell_record_code='{$response['record']['sell_record_code']}' and waves_record_id='{$response['record']['waves_record_id']}' and is_cancel=0 ");
            //$is_scan = $this->db->getOne("select count(1) from oms_deliver_record_detail where deliver_record_id='{$deliver_record_id}' and scan_num=num");
            if (isset($deliver_record_data['is_deliver']) && $deliver_record_data['is_deliver'] == 1) {
                $status_info[6] = array('time' => explode(" ", $response['record']['delivery_time']));
            }
        }

        if ($response['record']['waves_record_id'] > 0 && !empty($deliver_record_data)) {
            $ret_waves = load_model('oms/WavesRecordModel')->get_record_by_id($response['record']['waves_record_id']);
            $status_info[5] = array('time' => explode(" ", $ret_waves['record_time']));
        }
        if ($response['record']['shipping_status'] == 4) {
            if (isset($deliver_record_data['is_deliver']) && $deliver_record_data['is_deliver'] == 1) {
                $status_info[6] = array('time' => explode(" ", $response['record']['delivery_time']));
            } else {
                $status_info[8] = array('time' => explode(" ", $response['record']['delivery_time']));
            }
        }


        if ($response['record']['is_back'] == 1) {
            $status_info[7] = array('time' => explode(" ", $response['record']['is_back_time']));
            $status_index++;
        }
        // var_dump($status_info);die;
        $response['status_info'] = $status_info;
        //取操作日志时, 读取操作日志数据
        if (array_search('action', $types) !== false) {
            $response['action_list'] = $this->get_action_list_by_sell_record_code($sellRecordCode);

            foreach ($response['action_list'] as &$action) {
                $action['order_status'] = $this->order_status[$action['order_status']];
                $action['shipping_status'] = $this->shipping_status[$action['shipping_status']];
                $action['pay_status'] = $this->pay_status[$action['pay_status']];
            }
        }

        return $response;
    }

    //保存收货地址
    public function save_component_ship($sell_record_code, $type, $req_data = array()) {
        /*
          print_r($sell_record_code);
          print_r($type);
          print_r($req_data);
         */
        unset($req_data['express_code'], $req_data['express_no'], $req_data['store_code'], $req_data['order_remark'], $req_data['store_remark']);
        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
        $sql = "select is_lock,is_lock_person,order_status,shipping_status from oms_sell_record where sell_record_code = :sell_record_code";
        $record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(-1, '', '已发货的订单不能操作');
        }
        if (isset($req_data['receiver_country'])) {
            $country = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_street']));

            $req_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $req_data['receiver_addr'];
        }
        $record = $this->get_record_by_code($sell_record_code);
        //订单‘确认’、或‘通知配货’后，除了‘订单备注’、‘仓库留言’可编辑外，其它项都不能编辑

        $ret = $this->update($req_data, array('sell_record_code' => $sell_record_code));
        return $ret;
    }

    public function save_component($sell_record_code, $type, $req_data = array()) {

        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
        $sql = "select is_lock,is_lock_person,order_status,shipping_status from oms_sell_record where sell_record_code = :sell_record_code";
        $record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));

        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
            return $this->format_ret(-1, '', '已锁定的订单不能操作');
        }
        if ($record['order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的订单不能操作');
        }
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(-1, '', '已发货的订单不能操作');
        }

        if ($type == 'money') {
            $upd = array('express_money' => $req_data['express_money']);
            $ret = $this->update($upd, array('sell_record_code' => $sell_record_code));
            if ($ret['status'] == -1) {
                return $ret;
            }
            $ret = $this->refresh_record_price($sell_record_code);
            return $ret;
        }
        if (isset($req_data['receiver_country'])) {
            $country = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_street']));

            $req_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $req_data['receiver_addr'];
        }
        $record = $this->get_record_by_code($sell_record_code);
        //订单‘确认’、或‘通知配货’后，除了‘订单备注’、‘仓库留言’可编辑外，其它项都不能编辑
        if ($type == 'shipping' && ($record['order_status'] == '1' || $record['shipping_status'] >= 1)) {
            $req_data = array(
                "order_remark" => $req_data['order_remark'],
                "store_remark" => $req_data['store_remark'],
            );

            //更新到发货单
            $deliver_record = load_model("oms/DeliverRecordModel")->get_row(array("sell_record_code" => $sell_record_code));
            if ($deliver_record['status'] == '1') {
                $ret_deliver = load_model("oms/DeliverRecordModel")->update($req_data, array("sell_record_code" => $sell_record_code));
            }
        }

        $detail = $this->get_detail_list_by_code($sell_record_code);
        //是否重新锁定 如果原先是 需要锁定的 在改仓库的情况 下要重新锁定
        $is_relock_lof = 0;
        if (!empty($req_data['store_code']) && $req_data['store_code'] != $record['store_code'] && $record['must_occupy_inv'] == 1 && $record['order_status'] != 3) {
            $is_relock_lof = 1;
        }
        //$is_relock_lof = 1;

        $this->begin_trans();

        if ($is_relock_lof) {
            $ret_1 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 0, 1); //释放锁定
            if ($ret_1['status'] < 1) {
                $this->rollback();
                return $ret_1;
            }
        }
        $ret = $this->update($req_data, array('sell_record_code' => $sell_record_code));

        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        if ($is_relock_lof) {
            $old_store = get_store_name_by_code($record['store_code']);
            $record['store_code'] = $req_data['store_code'];
            foreach ($detail as &$dd) {
                $dd['lock_num'] = 0;
            }

            $ret_2 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 1, 1); //重新锁定
            if ($ret_2['status'] < 1) {
                $this->rollback();
                return $ret_2;
            }
            if ($type != "store_code") {
                $this->add_action($sell_record_code, "修改仓库", $old_store . "修改为" . get_store_name_by_code($req_data['store_code']));
            }
        }

        $this->commit();
        return $ret;
    }

    /**
     * 读取单个订单, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_record_list_by_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array();
        }

        return $this->db->get_all("select * from oms_sell_record where sell_record_code in ($str)");
    }

    /**
     * 更新订单 物流信息
     * @param $sell_record_code
     * @return
     */
    public function update_express($sell_record_code) {

        $data = array('express_no' => '', 'is_print_express' => '0');
        $ret = $this->update($data, array('sell_record_code' => $sell_record_code));
    }

    /**
     * 读取单个订单, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_detail_by_id($sellRecordDetailId) {
        return $this->db->get_row("select * from oms_sell_record_detail where sell_record_detail_id = :id", array('id' => $sellRecordDetailId));
    }

    /**
     * 读取订单所有明细, 根据订单ID
     * @param $sellRecordCode
     * @param bool $is_td
     * @param int $source
     * @return mixed
     */
    public function get_detail_list_by_sell_record_code($sellRecordCode, $is_td = false, $sale_channel_code = '') {
        return $this->format_ret(-1, '', '此方法已作废');
        $data = $this->db->get_all("select * from oms_sell_record_detail where sell_record_code = :sell_record_code", array('sell_record_code' => $sellRecordCode));

        if ($is_td) { //根据当前设计, 通过is_problem来判断是否平台订单列表
            switch ($sale_channel_code) {
                case 'taobao': //淘宝
                    foreach ($data as &$detail) {
                        $sql = "select * from oms_taobao_record_detail where tid = :tid and oid = :oid";
                        $d = $this->db->get_row($sql, array('tid' => $detail['deal_code'], 'oid' => $detail['sub_deal_code']));
                        if (!empty($d)) {
                            $detail['pic_path'] = $d['pic_path'];
                            $detail['goods_name'] = $d['title'];
                            $detail['sku_properties_name'] = $d['sku_properties_name'];
                            $detail['num_iid'] = $d['num_iid'];
                        }
                    }
                    break;
            }
        }


        return $data;
    }

    /**
     * 读取订单所有操作日志, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_action_list_by_sell_record_code($sellRecordCode) {
        return $this->db->get_all("select * from oms_sell_record_action where sell_record_code = :sell_record_code order by sell_record_action_id desc", array('sell_record_code' => $sellRecordCode));
    }

    /**
     * 重新处理订单价格
     * @param $sell_record_code
     * @return array
     * @throws Exception
     */
    public function refresh_record_price($sell_record_code) {

        $record = $this->get_record_by_code($sell_record_code);

        if (empty($record)) {
            throw new Exception('执行refresh_order_price失败:查询订单信息失败');
        }

        $sql = "SELECT SUM(goods_price*num) AS goods_money";
        $sql .= ",SUM(num-return_num) AS num";
        $sql .= ",COUNT(DISTINCT(sku)) AS sku_num";
        $sql .= ",SUM(goods_weigh*(num-return_num)) AS goods_weigh";
        $sql .= ",SUM(avg_money) AS avg_money";
        $sql .= " FROM oms_sell_record_detail";
        $sql .= " WHERE sell_record_code= :sell_record_code and num>return_num";

        $record_new = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));

        //订单应付款
        $info['payable_money'] = $record_new['avg_money'] + $record['express_money'] + $record['delivery_money'];
        //商品总额
        $info['goods_money'] = $record_new['goods_money'];
        //商品总数量
        $info['num'] = $record_new['num'];
        //商品SKU数量
        $info['sku_num'] = $record_new['sku_num'];
        //商品重量
        $info['goods_weigh'] = $record_new['goods_weigh'];

        $result = $this->db->update('oms_sell_record', $info, array("sell_record_code" => $sell_record_code));

        return $this->format_ret(1, $info);
    }

    /**
     * 计算订单中商品的均摊金额
     * @param $sell_record_code
     * @param $order_item
     * @param $goods_item
     * @param $cal_item
     */
    public function _cal_order_info_share_price($sell_record_code, $order_item, $goods_item, $cal_item) {

        //获取总的均摊金额
        $sql = "SELECT `{$order_item}` FROM oms_sell_record WHERE sell_record_code= :id";
        $item_amount = $this->db->get_value($sql, array(':id' => $sell_record_code));

        $sql = "SELECT sell_record_detail_id, sell_record_code,`{$goods_item}`,`{$cal_item}` FROM oms_sell_record_detail WHERE sell_record_code= :id AND is_gift=0";
        $goods = $this->db->get_all($sql, array(':id' => $sell_record_code));

        $share_amount = $rest_share_amount = 0;

        //计算均摊比率
        foreach ((array) $goods as $_goods) {
            $share_amount += $_goods[$goods_item];
        }
        $rest_share_amount = $item_amount;


        foreach ((array) $goods as $_key => $_goods) {
            if ($share_amount > 0)
                $_goods[$cal_item] = round(($_goods[$goods_item] / $share_amount * $item_amount), 2);
            else
                $_goods[$cal_item] = 0;
            $rest_share_amount -= $_goods[$cal_item];
            $goods[$_key] = $_goods;
        }
        if ($i = count($goods) > 0) {
            $goods[$i - 1][$cal_item] += $rest_share_amount;
            $goods[$i - 1][$cal_item] = round($goods[$i - 1][$cal_item], 2);
        }

        foreach ((array) $goods as $_goods) {
            $sql = "UPDATE oms_sell_record_detail SET `{$cal_item}`='{$_goods[$cal_item]}' WHERE sell_record_detail_id='{$_goods['sell_record_detail_id']}'";
            $this->db->query($sql);
        }
    }

    function return_value($status, $message = '', $data = '') {
        $message = $status == 1 && $message == '' ? '操作成功' : $message;

        return array('status' => $status, 'message' => $message, 'data' => $data);
    }

    function add_time() {
        return date("Y-m-d H:i:s");
    }

    /**
     * create new sell_record code
     * @return string
     */
    function new_code() {
        $num = $this->db->get_seq_next_value('oms_sell_record_seq');
        $time = date('ymd', time());

        $num = sprintf('%06s', $num);
        $length = strlen($num);
        $num = substr($num, $length - 6, 6);
        $str = $time . $num;

        $str = $this->barcode_check_code($str);
        return $str;
    }

    /**
     * 获得13位barcode的校验码
     * @param string $code
     * @return string|string
     */
    function barcode_check_code($code) {
        $ncode = $code;
        $length = strlen($ncode);
        $lsum = $rsum = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($i % 2) {
                $lsum += intval($ncode[$i]);
            } else {
                $rsum += intval($ncode[$i]);
            }
        }
        $tsum = $lsum * 3 + $rsum;
        $code .= (10 - ($tsum % 10)) % 10;
        return $code;
    }

    /**
     * 写入订单日志
     * @param $sellRecordCode
     * @param $actionName
     * @param string $actionNote
     * @param bool $isDeamon
     * @throws Exception
     */
    function add_action($sellRecordCode, $actionName, $actionNote = '', $isDeamon = false) {
        load_model('oms/SellRecordActionModel')->add_action($sellRecordCode, $actionName, $actionNote, $isDeamon);
    }

    function add_action_to_api($channel, $shopCode, $dealCode, $status) {
        load_model('oms/SellRecordActionModel')->add_action_to_api($channel, $shopCode, $dealCode, $status);
    }

    /**
     * 验证快递单号合法性
     * @param $express_code
     * @param $express_no
     * @return bool
     * @throws Exception
     */
    function check_express_no($express_code, $express_no) {
        $rule = '';

        $sql = "select * from base_express where express_code = :code";
        $express = $this->db->get_row($sql, array(":code" => $express_code));
        if (empty($express)) {
            return true;
        }

        if (empty($express['reg_mail_no'])) {
            $sql = "select rule from base_express_company where company_code = :company_code";
            $rule = $this->db->get_value($sql, array(":company_code" => $express['company_code']));
            if (empty($rule)) {
                return true;
            }
        } else {
            $rule = $express['reg_mail_no'];
        }

        if (preg_match('/' . $rule . '/', $express_no)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 快递单号连续匹配计算
     */
    function get_next_express_no($express_no, $express_code) {
        $first_word = '';

        if (strtolower($express_code) == 'yto') {   //圆通
            //如果是圆通 判断是否已字母开头 进行特殊处理
            $tmp_str = substr($express_no, 0, 1); //    第一位字符
            if (ord($tmp_str) >= 65 && ord($first_word) <= 122) {
                //  是字母
                $express_no = substr($express_no, 1);
                $first_word = $tmp_str;
            }
        } else if (strtolower($express_code) == 'sf') { //顺丰
            if (strlen($express_no) > 0) {
                $num4 = 0;
                $num = trim($express_no + 10);
                //单号开头为0的情况
                if (strlen($num) == 11) {
                    $num = '0' . $num;
                }
                for ($ii = 1; $ii <= 8; $ii++) {
                    $num1 = substr($num, 12 - $ii - 1, 1);
                    $num2 = $num1 * ($ii * 2 - 1);
                    $num3 = (int) ($num2 / 10) + ($num2 % 10);
                    $num4 = $num4 + $num3;
                }

                $num5 = -floor(-$num4 / 10) * 10 - $num4;
                //$num5 =10 - substr($num4,strlen($num4)-1,1);
                // $num5 =substr($num5,strlen($num5)-1,1);
                $num6 = ($num5 % 10);
                $express_no = substr($num, 0, 11) . ($num6);
                return $express_no;
            }
            return '';
        } else if (in_array(strtolower($express_code), array('ems', 'eyb', 'postb'))) { //EMS
            $first = substr($express_no, 0, 2);
            $str = substr($express_no, 2, 8);
            $lsst = substr($express_no, 11, 2);
            $str = $str + 1;

            while (strlen($str) < 8) {
                $str = '0' . $str;
            }

            $f1 = substr($str, 0, 1) * 8 + substr($str, 1, 1) * 6 + substr($str, 2, 1) * 4 + substr($str, 3, 1) * 2 + substr($str, 4, 1) * 3 + substr($str, 5, 1) * 5 + substr($str, 6, 1) * 9 + substr($str, 7, 1) * 7;

            if (11 - ($f1 % 11) == 11) {
                $check_no = 5;
            } else {
                if (11 - ($f1 % 11) == 10) {
                    $check_no = 0;
                } else {
                    $check_no = 11 - ($f1 % 11);
                }
            }

            return $first . $str . $check_no . $lsst;
        } else if (strtolower($express_code) == 'gzlt') {   //飞远快递
            if (strlen($express_no) > 0) {
                $str_1 = substr($express_no, 0, 1); //  第一位字符
                $str_2 = substr($express_no, 1, 10);
                $str_3 = $str_2 + 1;

                $str = $str_1 . $str_3;
                return $str;
            }
            return '';
        } else if (strtolower($express_code) == 'qfkd') {   //全峰快递
            if (strlen($express_no) > 0) {
                $str_3 = $express_no + 1;
                return strval($str_3);
            }
            return '';
        } else if (strtolower($express_code) == 'zjs') {  //宅急送
            if (strlen($express_no) > 0) {
                $str_1 = substr($express_no, -1, 1); // 最后一位数字
                if ($str_1 == 6 || $str_1 == 7 || $str_1 == 8 || $str_1 == 9) {
                    $str_1 = 0;
                } else {
                    $str_1 = $str_1 + 1;
                }

                $str_2 = substr($express_no, 0, -1);
                $str_2 = $str_2 + 1;

                $str = $str_2 . $str_1;

                return $str;
            }
            return '';
        }

        $length = strlen($express_no);
        //排除已经使用的快单单号
        do {
            $ret = number_format(++$express_no, 0, '', '');
            $is_exist = $this->check_express_no_exist($express_code, $ret);
        } while ($is_exist === true);

        $ret_len = strlen($ret);
        if ($ret_len < $length) {
            // 比原来位数少(转型计算后 前置0消失) 自动布足0
            for ($ii = 1; $ii <= $length - $ret_len; $ii++) {
                $ret = '0' . $ret;
            }
        }

        return $first_word . $ret;
    }

    /**
     * 检查快递单号是否重复
     * @param $express_code
     * @param $express_no
     * @param string $id
     * @return bool
     * @throws Exception
     */
    function check_express_no_exist($express_code, $express_no, $id = '') {
        $sql = "select sell_record_code from oms_sell_record where express_code = :express_code and express_no = :express_no";
        $arr = array(':express_code' => $express_code, ':express_no' => $express_no);
        if ($id !== '') {
            $sql .= ' and sell_record_code != :sell_record_code';
            $arr[':sell_record_code'] = $id;
        }
        $data = $this->db->get_value($sql, $arr);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    //新增订单
    function add($request) {
        $shop = $this->db->get_row("select * from base_shop where shop_code = :code", array('code' => $request['shop_code']));
        if (empty($shop)) {
            return $this->format_ret(-1, '', '商店不存在, 请完善商店api参数nick字段');
        }

//        $request['deal_code'] = load_model("oms/SellRecordOptModel")->get_guid_deal_code($request['deal_code']);

        $country = oms_tb_val('base_area', 'name', array('id' => $request['receiver_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $request['receiver_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $request['receiver_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $request['receiver_district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $request['receiver_street']));

        //会员信息
        $customer = array(
            'customer_name' => $request['buyer_name'],
            'shop_code' => $request['shop_code'],
            'address' => $request['receiver_addr'],
            'country' => $request['receiver_country'],
            'province' => $request['receiver_province'],
            'city' => $request['receiver_city'],
            'district' => $request['receiver_district'],
            'street' => $request['receiver_street'],
            'zipcode' => $request['receiver_zip_code'],
            'tel' => $request['receiver_mobile'],
            'home_tel' => $request['receiver_phone'],
            'customer_sex' => 3,
            'name' => $request['receiver_name'],
            'is_add_time' => date('Y-m-d H:i:s'),
        );
        $deal_code = load_model('oms/SellRecordOptModel')->get_guid_deal_code($request['deal_code']);
        $new_sell_record_code = $this->new_code();
        $data = array(
            'sell_record_code' => $new_sell_record_code,
            'deal_code' => $deal_code,
            'deal_code_list' => $request['deal_code'],
            'sale_channel_code' => $request['sale_channel_code'],
            'order_status' => '0',
            'shipping_status' => '0',
            'store_code' => $request['store_code'],
            'shop_code' => $request['shop_code'],
            'pay_type' => $request['pay_type'],
            'must_occupy_inv' => $request['pay_type'] == 'cod' ? 1 : 0,
            'lock_inv_status' => $request['pay_type'] == 'cod' ? 1 : 0,
            'pay_code' => $request['pay_code'],
            'pay_status' => '0',
            'customer_code' => $request['buyer_name'],
            'buyer_name' => $request['buyer_name'],
            'receiver_name' => $request['receiver_name'],
            'receiver_country' => $request['receiver_country'],
            'receiver_province' => $request['receiver_province'],
            'receiver_city' => $request['receiver_city'],
            'receiver_district' => $request['receiver_district'],
            'receiver_street' => $request['receiver_street'],
            'receiver_address' => $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $request['receiver_addr'],
            'receiver_addr' => $request['receiver_addr'],
            'receiver_zip_code' => $request['receiver_zip_code'],
            'receiver_mobile' => $request['receiver_mobile'],
            'receiver_phone' => $request['receiver_phone'],
            'express_code' => $request['express_code'],
            'express_money' => $request['express_money'],
            'order_remark' => $request['order_remark'],
            'record_time' => $request['record_time'],
            'create_time' => date('Y-m-d H:i:s'),
            'is_handwork' => '1',
            'is_lock' => 1, //建手工单后，默认，订单自动锁定；通知配货后，自动解锁
            'is_lock_person' => ctx()->get_session('user_code'),
            'payable_money' => $request['express_money'],
            'store_remark' => $request['store_remark'],
        );

        $this->begin_trans();
        try {
            /*
              $result = $this->db->get_row(
              "SELECT deal_code FROM oms_sell_record WHERE deal_code = :deal_code",
              array('deal_code' => $request['deal_code'])
              );
              if(!empty($result)){
              return $this->format_ret(-1,'','已存在相同交易号的订单');
              } */

            $result = $this->db->insert('oms_sell_record', $data);
            if ($result !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '保存订单出错');
            }
            $sell_record_id = $this->db->insert_id();
            $sell_record_code = $data['sell_record_code'];

            //记录订单转入日志
            $this->add_action($sell_record_code, '新增');
            $this->add_action_to_api($data['sale_channel_code'], $data['shop_code'], $data['deal_code'], 'convert');

            //添加用户信息
            $ret = load_model("crm/CustomerModel")->handle_customer($customer);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        $ret_plan_send_time = load_model('oms/SellRecordOptModel')->set_sell_plan_send_time($sell_record_code);
        if ($ret_plan_send_time['status'] < 0) {
            return $ret_plan_send_time;
        }
        return $this->format_ret(1, $new_sell_record_code);
    }

    public function spec_list_by_goods($sellRecordCode, $goodsCode) {
        $spec1List = array();
        $spec2List = array();
        $skuList = array();

        $sql = "select * from base_goods where goods_code = :code";
        $goods = $this->db->get_row($sql, array('code' => $goodsCode));
        if (empty($goods)) {
            return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList, 'message' => '商品不存在');
        }

        $sql = "select * from goods_sku where goods_code = :code";
        $skus = $this->db->get_all($sql, array('code' => $goodsCode));
        if (empty($skus)) {
            return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList, 'message' => 'SKU不存在');
        }

        foreach ($skus as $sku) {
            $sql = "select * from base_spec1 where spec1_code = :code";
            $spec1 = $this->db->get_row($sql, array('code' => $sku['spec1_code']));
            $spec1Name = empty($spec1) ? $sku['spec1_code'] : $spec1['spec1_name'];
            $spec1List[$sku['spec1_code']] = $spec1Name;

            $sql = "select * from base_spec2 where spec2_code = :code";
            $spec2 = $this->db->get_row($sql, array('code' => $sku['spec2_code']));
            $spec2Name = empty($spec2) ? $sku['spec2_code'] : $spec2['spec2_name'];
            $spec2List[$sku['spec2_code']] = $spec2Name;

            $skuList[$sku['spec1_code'] . '-' . $sku['spec2_code']] = $sku['sku'];
        }

        return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList);
    }

    /**
     * 读取单个订单, 根据订单sn
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_record_by_code($sell_record_code, $fld = '*') {
        $result = $this->db->get_row("select {$fld} from oms_sell_record where sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        return $result;
    }

    public function get_detail_list_by_code($sell_record_code, $id_map = '', $fld = '*') {
        $result = $this->db->get_all("select {$fld} from oms_sell_record_detail where is_delete =0 and sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        $result_2 = array();
        if ($id_map != '') {
            $id_map_arr = explode(',', $id_map);
            foreach ($result as $sub_result) {
                $_tk_arr = array();
                foreach ($id_map_arr as $t_id) {
                    $_tk_arr[] = $sub_result[$t_id];
                }
                $_tk = join(',', $_tk_arr);
                $result_2[$_tk] = $sub_result;
            }
            return $result_2;
        }
        return $result;
    }

    //刷新商家备注
    function seller_remark_flush($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_get($record['deal_code']);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        if ($record['seller_remark'] <> $ret['seller_memo'] && isset($ret['seller_memo'])) {
            M('oms_sell_record')->update(array('seller_remark' => $ret['seller_memo']), array('sell_record_code' => $sell_record_code));
        }
        return $this->format_ret(1, $ret['seller_memo']);
    }

    //上传商家备注
    function seller_remark_upload($sell_record_code, $seller_remark) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_memo_update($record['deal_code'], $seller_remark);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        return $this->format_ret(1, $seller_remark);
    }

    //刷新客户留言
    function buyer_remark_flush($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_get($record['deal_code']);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        if ($record['buyer_remark'] <> $ret['buyer_memo'] && isset($ret['buyer_memo'])) {
            M('oms_sell_record')->update(array('buyer_remark' => $ret['buyer_memo']), array('sell_record_code' => $sell_record_code));
        }
        return $this->format_ret(1, $ret['seller_memo']);
    }

    //根据问题类型获取数量
    function get_count_by_problem_type($problem_type) {
        $sql = "select count(*) from oms_sell_record_tag where tag_type='problem' and tag_v = :problem_type";
        $sql_value[':problem_type'] = $problem_type;
        return $num = $this->db->get_value($sql, $sql_value);
    }

    //获取列表(问题列表、缺货列表、合并列表、已发货列表共用)
    function get_list_by_page($filter) {
        $detail_table = "oms_sell_record_detail";
        $sql_values = array();
        $sql_join = "";
        $sub_sql = "select sell_record_code from {$detail_table} rr where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //缺货单
        if (isset($filter['stock_out_status']) && $filter['stock_out_status'] == '0') {
            //已付款未确认的缺货单
            $sql_main .= " AND rl.lock_inv_status in (0,2,3) and rl.must_occupy_inv = 1 and rl.pay_status = 2";
        }

        if (isset($filter['search_mode']) && $filter['search_mode'] == 'problem_order') {
            $sql_main .= " AND rl.order_status !=3 and is_problem=1";
        }

        //问题单
        if (isset($filter['is_problem'])) {
            $sql_main .= " AND rl.is_problem = :is_problem ";
            $sql_values[':is_problem'] = $filter['is_problem'];
        }
        //合并单
        if (isset($filter['merge_status']) && $filter['merge_status'] == '1') {
            $sql_main .= " and (select count(*) from {$this->table} t where t.receiver_name = rl.receiver_name and t.receiver_mobile = rl.receiver_mobile and t.pay_status = 2 and t.order_status = 0)>1 and rl.pay_status = 2 and rl.order_status = 0";
        }
        //已发货订单
        if (isset($filter['shipping_status']) && !empty($filter['shipping_status'])) {
            $sql_main .= " and rl.shipping_status = :shipping_status";
            $sql_values[':shipping_status'] = $filter['shipping_status'];
        }
        ###############################################################################
        //缺货状态
        if (isset($filter['is_stock_out']) && $filter['is_stock_out'] != 'all') {
            $sql_main .= " AND rl.lock_inv_status = :is_stock_out ";
            $sql_values[':is_stock_out'] = $filter['is_stock_out'];
        }
        //预售状态
        if (isset($filter['is_persale']) && $filter['is_persale'] != 'all') {
            //TODO
        }
        //已生成采购计划单
        if (isset($filter['is_purchase']) && $filter['is_purchase'] != 'all') {
            //TODO
        }
        ################################################################################
        //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
                        $arr = explode(',',$filter['sell_record_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
            $sql_main .= " AND rl.sell_record_code in ( " .$str . " ) ";
        } else {
            //交易号
            if (isset($filter['deal_code']) && !empty($filter['deal_code'])) {
                $sql_main .= " AND rl.deal_code_list like :deal_code ";
                $sql_values[':deal_code'] = "%" . $filter['deal_code'] . "%";
            } else {
                //销售平台
                if (isset($filter['sale_channel_code']) && !empty($filter['sale_channel_code'])) {
                                 $arr = explode(',',$filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
                    $sql_main .= " AND rl.sale_channel_code in (" .$str . ") ";
                }

                //问题类型
                if (isset($filter['is_problem_type']) && !empty($filter['is_problem_type'])) {
                         $arr = explode(',',$filter['is_problem_type']);
            $str = $this->arr_to_in_sql_value($arr, 'is_problem_type', $sql_values);
                    $sql_main .= " AND rl.sell_record_code in (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_v in (" . $str . ") and rt.tag_type='problem') ";
                }
                //订单问题描述
                if (isset($filter['is_problem_reason']) && !empty($filter['is_problem_reason'])) {
                    $sql_main .= " AND rl.sell_record_code in (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_desc like :is_problem_reason and rt.tag_type='problem') ";
                    $sql_values[':is_problem_reason'] = '%' . $filter['is_problem_reason'] . '%';
                }
                //下单时间
                if (isset($filter['record_time_start']) && !empty($filter['record_time_start'])) {
                    $sql_main .= " AND rl.record_time >= :record_time_start ";
                    $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
                }
                if (isset($filter['record_time_end']) && !empty($filter['record_time_end'])) {
                    $sql_main .= " AND rl.record_time <= :record_time_end ";
                    $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
                }
                //付款时间
                if (isset($filter['pay_time_start']) && !empty($filter['pay_time_start'])) {
                    $sql_main .= " AND rl.is_pay_time >= :pay_time_start ";
                    $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
                }
                if (isset($filter['pay_time_end']) && !empty($filter['pay_time_end'])) {
                    $sql_main .= " AND rl.is_pay_time <= :pay_time_end ";
                    $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
                }
                //发货时间
                if (isset($filter['send_time_start']) && !empty($filter['send_time_start'])) {
                    $sql_main .= " AND rl.delivery_time >= :send_time_start ";
                    $sql_values[':send_time_start'] = $filter['send_time_start'] . ' 00:00:00';
                }
                if (isset($filter['send_time_end']) && !empty($filter['send_time_end'])) {
                    $sql_main .= " AND rl.delivery_time <= :send_time_end ";
                    $sql_values[':send_time_end'] = $filter['send_time_end'] . ' 23:59:59';
                }
                //收货人
                if (isset($filter['receiver_name']) && !empty($filter['receiver_name'])) {
//                    $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';

                    $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
                    if (!empty($customer_address_id)) {
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                    } else {
                        $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                        $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                    }
                }
                //手机号
                if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] !== '') {

                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'],'tel');
                if(!empty($customer_address_id)){
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_main .= " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                }else{
                         $sql_main .= " AND rl.receiver_mobile = :receiver_mobile ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile']; 
                }

//                    $sql_main .= " AND rl.receiver_mobile LIKE :receiver_mobile ";
//                    $sql_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
                }
                //国家
                if (isset($filter['country']) && $filter['country'] !== '') {
                    $sql_main .= " AND rl.receiver_country = :country ";
                    $sql_values[':country'] = $filter['country'];
                }
                //省
                if (isset($filter['province']) && $filter['province'] !== '') {
                    $sql_main .= " AND rl.receiver_province = :province ";
                    $sql_values[':province'] = $filter['province'];
                }
                //城市
                if (isset($filter['city']) && $filter['city'] !== '') {
                    $sql_main .= " AND rl.receiver_city = :city ";
                    $sql_values[':city'] = $filter['city'];
                }
                //地区
                if (isset($filter['district']) && $filter['district'] !== '') {
                    $sql_main .= " AND rl.receiver_district = :district ";
                    $sql_values[':district'] = $filter['district'];
                }
                //详细地址
                if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
                    $sql_main .= " AND rl.receiver_addr LIKE :receiver_addr ";
                    $sql_values[':receiver_addr'] = '%' . $filter['receiver_addr'] . '%';
                }
                //收货地址
                if (isset($filter['receiver_address']) && !empty($filter['receiver_address'])) {
                    $sql_main .= " AND rl.receiver_address LIKE :receiver_address ";
                    $sql_values[':receiver_address'] = '%' . $filter['receiver_address'] . '%';
                }
                //仓库
                if (isset($filter['store_code']) && !empty($filter['store_code'])) {
                                           $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
                    $sql_main .= " AND rl.store_code in (" .$str. ") ";
                }
                //快递公司
                if (isset($filter['express_company']) && !empty($filter['express_company'])) {
                    $sql_main .= " AND rl.express_code in (select express_code from base_express where company_code in (:express_company)) ";
                    $sql_values[':express_company'] = $filter['express_company'];
                }
                //配送方式
                if (isset($filter['express_code']) && !empty($filter['express_code'])) {
                    $arr = explode(',',$filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
                    $sql_main .= " AND rl.express_code in (" .$str . ") ";
                }
                //客户留言
                if (isset($filter['buyer_remark']) && !empty($filter['buyer_remark'])) {
                    $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
                    $sql_values[':buyer_remark'] = '%' . $filter['buyer_remark'] . '%';
                }
                //买家昵称
                if (isset($filter['buyer_name']) && !empty($filter['buyer_name'])) {

         $customer_code_arr= load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if(!empty($customer_code_arr)){

                       $customer_code_str = "'".implode("','", $customer_code_arr)."'";
                       $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";  
                }else{
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                }    
//                    $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
//                    $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
                }
                //商家留言
                if (isset($filter['seller_remark']) && !empty($filter['seller_remark'])) {
                    $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
                    $sql_values[':seller_remark'] = '%' . $filter['seller_remark'] . '%';
                }
                //商品编码
                if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
                    $sub_sql .= " AND rr.goods_code = :goods_code ";
                    $sub_values[':goods_code'] = $filter['goods_code'];
                }
                //条码
                if (isset($filter['barcode']) && !empty($filter['barcode'])) {

//                    
//                    $sub_sql .= " AND rr.barcode like :barcode ";
//                    $sub_values[':barcode'] = "%" . $filter['barcode'] . "%";
//              
                    $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
                    if (empty($sku_arr)) {
                        $sub_sql .= " AND 1=2 ";
                    } else {
                        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                        $sub_sql .= " AND rr.sku in({$sku_str}) ";
                    }
                }
                //平台规则
                if (isset($filter['platform_spec']) && !empty($filter['platform_spec'])) {
                    $sub_sql .= " AND rr.platform_spec LIKE :platform_spec ";
                    $sub_values[':platform_spec'] = '%' . $filter['platform_spec'] . '%';
                }
                //支付方式
                if (isset($filter['pay_code']) && !empty($filter['pay_code'])) {
                                 $arr = explode(',',$filter['pay_code']);
            $str = $this->arr_to_in_sql_value($arr, 'pay_code', $sql_values);
                    $sql_main .= " AND rl.pay_code in (" .$str . ") ";
                }
                //货到付款
                if (isset($filter['pay_type']) && !empty($filter['pay_type'])) {
                    if ($filter['pay_type'] == '1') {
                        $sql_main .= " AND rl.pay_type = 1";
                    } else {
                        $sql_main .= " AND rl.pay_type <> 1";
                    }
                }
                //换货单
                if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
                    $sql_main .= " AND rl.is_change_record = :is_change_record ";
                    $sql_values[':is_change_record'] = $filter['is_change_record'];
                }
                //发票
                if (isset($filter['is_invoice']) && ($filter['is_invoice'] == '0' || $filter['is_invoice'] == '1')) {
                    if ($filter['is_invoice'] == '0') {
                        $sql_main .= " AND rl.invoice_title = '' ";
                    } else {
                        $sql_main .= " AND rl.invoice_title <> '' ";
                    }
                }
                //快递交接
                if (isset($filter['is_e_handover']) && !empty($filter['is_e_handover'])) {
                    $sql_main .= " AND rl.is_e_handover = :is_e_handover ";
                    $sql_values[':is_e_handover'] = $filter['is_e_handover'];
                }
                //回写状态
                if (isset($filter['is_back']) && !empty($filter['is_back'])) {
                    $sql_main .= " AND rl.is_back = :is_back ";
                    $sql_values[':is_back'] = $filter['is_back'];
                }
                //快递号
                if (!empty($filter['express_no'])) {
                    $sql_main .= " AND rl.express_no LIKE :express_no ";
                    $sql_values[':express_no'] = '%' . $filter['express_no'] . '%';
                }
            }
        }
        //子查询合并
        if (!empty($sub_values)) {
            $sql_main .= " AND rl.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        $select = 'rl.*';
        $sql_main .= " ORDER BY sell_record_code DESC ";
        //var_dump($sql_main, $sql_values, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['status_text'] = $this->get_status_text($value);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
//            $value['is_problem_type'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $value['is_problem_type']));
            $value['sale_channel_code'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['express_code_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            if ($value['is_back'] == 1)
                $value['is_back_html'] = '是';
            else
                $value['is_back_html'] = '否';
            if ($value['pay_type'] == 'cod')
                $value['pay_type_html'] = '是';
            else
                $value['pay_type_html'] = '否';

            if (isset($value['is_problem']) && $value['is_problem'] == 1) {
                $tag = load_model("oms/SellRecordTagModel")->get_all(array('sell_record_code' => $value['sell_record_code'], 'tag_type' => 'problem'));
                if ($tag['status'] == '1') {
                    $value['problem_html'] = '';
                    foreach ($tag['data'] as $t) {
                        $value['problem_html'] .= oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $t['tag_v']));
                        if ($t['tag_desc'] != '') {
                            $value['problem_html'] .= "," . $t['tag_desc'];
                        }
                        $value['problem_html'] .= ";<br/>";
                    }
                }
            }
        }

        return $this->format_ret(1, $data);
    }

    //发货订单列表
    function get_deliver_by_page($filter) {
        $detail_table = "oms_sell_record_detail";
        $sql_values = array();
        $sql_join = "";
        $sub_sql = "select sell_record_code from {$detail_table} r2
         inner join base_goods r3 on r3.goods_code = r2.goods_code
         where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} r1 $sql_join WHERE r1.is_fenxiao = 0 and r1.order_status = 1 and r1.shipping_status > 0 ";

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
        //是否打印快递单
        if (isset($filter['is_print_express']) && $filter['is_print_express'] != 'all') {
            $sql_main .= " AND r1.is_print_express = :is_print_express ";
            $sql_values[':is_print_express'] = $filter['is_print_express'];
        }
        //是否打印订单
        if (isset($filter['is_print_sellrecord']) && $filter['is_print_sellrecord'] != 'all') {
            $sql_main .= " AND r1.is_print_sellrecord = :is_print_sellrecord ";
            $sql_values[':is_print_sellrecord'] = $filter['is_print_sellrecord'];
        }
        //是否称重
        if (isset($filter['is_weigh']) && $filter['is_weigh'] != 'all') {
            $sql_main .= " AND r1.is_weigh = :is_weigh ";
            $sql_values[':is_weigh'] = $filter['is_weigh'];
        }
        //生产波次
        if (isset($filter['waves_record_id']) && $filter['waves_record_id'] != 'all') {
            if ($filter['waves_record_id'] == '0') {
                $sql_main .= " AND r1.waves_record_id < 1";
            } else {
                $sql_main .= " AND r1.waves_record_id > 0";
            }
        }
        if (!isset($filter['waves_record_id'])) {
            $sql_main .= " AND r1.waves_record_id <= 1";
        }
        //发货状态
        if (!empty($filter['shipping_status']) && $filter['shipping_status'] != 'all') {
            $sql_main .= " AND r1.shipping_status in (:shipping_status) ";
            $sql_values[':shipping_status'] = explode(',', $filter['shipping_status']);
        }
        if (!isset($filter['shipping_status'])) {
            $sql_main .= " and r1.shipping_status in (1,2,3) ";
        }

        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND r1.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code'])) {
            $sql_main .= " AND r1.deal_code_list LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND r1.store_code in (:store_code) ";
            $sql_values[':store_code'] = explode(',', $filter['store_code']);
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND r1.express_code in (:express_code) ";
            $sql_values[':express_code'] = explode(',', $filter['express_code']);
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sub_sql .= " AND r2.goods_code LIKE :goods_code";
            $sub_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (!empty($filter['barcode'])) {
//            $sub_sql .= " AND r2.barcode LIKE :barcode ";
//            $sub_values[':barcode'] = '%' . $filter['barcode'] . '%';
//            
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sub_sql .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sub_sql .= " AND r2.sku in({$sku_str}) ";
            }
        }

        //包含SKU
        if (!empty($filter['sku'])) {
            $sub_sql .= " AND r2.sku LIKE :sku";
            $sub_values[':sku'] = '%' . $filter['sku'] . '%';
        }
        //排除商品编码
        if (!empty($filter['goods_code_exp'])) {
            $sub_sql .= " AND r2.goods_code NOT LIKE :goods_code_exp";
            $sub_values[':goods_code_exp'] = '%' . $filter['goods_code_exp'] . '%';
        }
        //付款类型
        if (!empty($filter['pay_type'])) {
            $sql_main .= " AND r1.pay_type = :pay_type";
            $sql_values[':pay_type'] = $filter['pay_type'];
        }

        //sku种类数
        if (!empty($filter['sku_num'])) {
            $sql_main .= " AND r1.sku_num = :sku_num ";
            $sql_values[':sku_num'] = $filter['sku_num'];
        }
        //商品数量
        if (!empty($filter['num_start'])) {
            $sql_main .= " AND r1.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (!empty($filter['num_end'])) {
            $sql_main .= " AND r1.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //发票
        if (!empty($filter['invoice_type'])) {
            if ($filter['invoice_type'] == '1') {
                $sql_main .= " AND r1.invoice_type <> ''";
            } else {
                $sql_main .= " AND r1.invoice_type = ''";
            }
        }
        //销售平台
        if (!empty($filter['source'])) {
            $sql_main .= " AND r1.sale_channel_code in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
        }
        //店铺
        if (!empty($filter['shop_code'])) {
            $sql_main .= " AND r1.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = explode(',', $filter['shop_code']);
        }
        //买家留言
        if (isset($filter['buyer_remark']) && $filter['buyer_remark'] != '') {
            if ($filter['buyer_remark'] == '1') {
                $sql_main .= " AND r1.buyer_remark <> ''";
            } else {
                $sql_main .= " AND r1.buyer_remark = ''";
            }
        }

        //商家留言
        if (isset($filter['seller_remark']) && $filter['seller_remark'] != '') {
            if ($filter['seller_remark'] == '1') {
                $sql_main .= " AND r1.seller_remark <> ''";
            } else {
                $sql_main .= " AND r1.seller_remark = ''";
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $sub_sql .= " AND r3.brand_code in (:brand_code) ";
            $sub_values[':brand_code'] = explode(',', $filter['brand_code']);
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $sub_sql .= " AND r3.season_code in (:season_code) ";
            $sub_values[':season_code'] = explode(',', $filter['season_code']);
        }

        //付款时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND r1.pay_time >= :pay_time_start ";
            $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND r1.pay_time <= :pay_time_end ";
            $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }

        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND r1.is_notice_time >= :is_notice_time_start ";
            $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND r1.is_notice_time <= :is_notice_time_end ";
            $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'] . ' 23:59:59';
        }
        //计划发货时间
        if (!empty($filter['plan_time_start'])) {
            $sql_main .= " AND r1.plan_send_time >= :plan_time_start ";
            $sql_values[':plan_time_start'] = $filter['plan_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['plan_time_end'])) {
            $sql_main .= " AND r1.plan_send_time <= :plan_time_end ";
            $sql_values[':plan_time_end'] = $filter['plan_time_end'] . ' 23:59:59';
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND r1.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND r1.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }

        //子查询合并
        if (!empty($sub_values)) {
            $sql_main .= " AND r1.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }

        $select = 'r1.*';
        $sql_main .= " ORDER BY  ";

        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            $sql_main .= "r1." . $filter['is_sort'] . " DESC ";
            $sql_main .= ",r1.sell_record_code DESC  ";
        } else {
            $sql_main .= "r1.sell_record_code DESC  ";
        }
        //var_dump($select,$sql_main,$sql_values);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => &$value) {
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            $value['is_waves'] = $value['waves_record_id'] > 0 ? '1' : '0';
        }
        return $this->format_ret(1, $data);
    }

    function get_detail_by_sell_record_code($sell_record_code, $process_refund = 0, $keep_key = 0) {
        $sql = "select * from oms_sell_record_detail where sell_record_code = :sell_record_code";
        $data = $this->db->get_all($sql, array("sell_record_code" => $sell_record_code));
        //  filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'sku|barcode', 'goods_code|goods_code'));
        $result = array();
        $_del_mx = array();
        foreach ($data as $sub_data) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);
            $sub_data = array_merge($sub_data, $sku_info);
            $ks = $sub_data['deal_code'] . ',' . $sub_data['sku'];
            if ($sub_data['is_delete'] > 0) {
                $_del_mx[$ks][$sub_data['is_delete']] = $sub_data;
            } else {
                $result[$ks] = $sub_data;
            }
        }
        //echo '<hr/>$process_refund<xmp>'.var_export($process_refund,true).'</xmp>';
        if ($process_refund == 0) {
            if ($keep_key == 0) {
                return array_values($result);
            } else {
                return $result;
            }
        }

        foreach ($result as $ks => $sub_result) {
            $_find_del_row = isset($_del_mx[$ks]) ? $_del_mx[$ks] : null;
            if (empty($_find_del_row)) {
                continue;
            }
            ksort($_find_del_row);
            $_find_del_first_row = array_shift($_find_del_row);
            //原始订单数量
            $result[$ks]['source_num'] = $_find_del_first_row['num'];
            //已退数量
            $result[$ks]['refund_num'] = $_find_del_first_row['num'] - $sub_result['num'];
        }

        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
        if ($keep_key == 0) {
            return array_values($result);
        } else {
            return $result;
        }
    }

    function remove_short($sell_record_code) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/remove_short')) {
            return $this->format_ret(-1, '', "无权访问");
        }
        //###########
        $record = $this->get_record_by_code($sell_record_code);
        $detail = $this->get_detail_list_by_code($sell_record_code);
        $ret = load_model('oms/SellRecordOptModel')->lock_detail($record, $detail, 1);
        if ($ret['data']< 1) {
            return $this->format_ret(-1, '', "解除缺货失败,库存不足。");
        } else {
            return $this->format_ret(1, '', '解除缺货成功');
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
    }

    //一键解除缺货
    function remove_a_key() {
        //从库存表获取有缺货信息的商品
        $inv_record_list = load_model("prm/InvModel")->get_short_record();
        if ($inv_record_list['status'] == '1') {
            $ids = array();
            foreach ($inv_record_list['data'] as $inv_record) {
                //如果有多的库存且缺货单正好缺此商品则解除缺货
                if ($inv_record['stock_num'] - $inv_record['lock_num'] > 0) {
                    $sql = "select t1.sell_record_code from oms_sell_record t1 left join oms_sell_record_detail t2 on t1.sell_record_code = t2.sell_record_code where t2.is_real_stock_out>0 and t2.sku = :sku and t1.store_code = :store_code group by t1.sell_record_code";
                    $sql_value[':sku'] = $inv_record['sku'];
                    $sql_value[':store_code'] = $inv_record['store_code'];
                    $sell_record_code = $this->db->get_all($sql, $sql_value);
                    foreach ($sell_record_code as $id) {
                        $ids[] = $id['sell_record_code'];
                    }
                }
            }
            array_flip(array_flip($ids));
            return $this->remove_short($ids);
        } else {
            return $this->format_ret(1);
        }
    }

    function get_sell_problem_map() {
        $sql = "select sell_problem_code,sell_problem_name from base_sell_problem where is_active = 1";
        $db_arr = ctx()->db->getAll($sql);
        $arr = array();
        foreach ($db_arr as $sub_arr) {
            $arr[$sub_arr['sell_problem_code']] = $sub_arr['sell_problem_name'];
        }
        return $arr;
    }

    function get_problem_desc($sell_record_codes) {
        if (is_array($sell_record_codes)) {
            $sell_record_code_list = "'" . join("','", $sell_record_codes) . "'";
        } else {
            $sell_record_code_list = "'" . $sell_record_codes . "'";
        }
        $sql = "select sell_record_code,tag_v,tag_desc from oms_sell_record_tag where tag_type='problem' and sell_record_code in($sell_record_code_list)";
        $db_arr = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_codes));
        $desc_arr = array();
        foreach ($db_arr as $sub_arr) {
            $desc_arr[$sub_arr['sell_record_code']]['tag_v'][] = $sub_arr['tag_v'];
            $desc_arr[$sub_arr['sell_record_code']]['tag_desc'][] = $sub_arr['tag_desc'];
        }
        if (is_array($sell_record_codes)) {
            return $desc_arr;
        } else {
            return @$desc_arr[$sell_record_codes];
        }
    }

    function get_pending_list($filter) {
        $sql_values = array();
        $sql_join = "";
        $sub_sql = "select sell_record_code from {$this->detail_table} rr where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} rl $sql_join WHERE is_fenxiao = 0 and  is_pending = 1 and order_status<>3 ";

        //是否是我锁定的
        if (isset($filter['is_my_lock']) && $filter['is_my_lock'] == '1') {
            $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
            $sql_main .= " AND rl.is_lock = 1 AND rl.is_lock_person = :user_code";
            $sql_values[':user_code'] = $sys_user['user_code'];
        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND rl.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code'])) {
            $sql_main .= " AND rl.deal_code_list LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //销售平台
        if (!empty($filter['sale_channel_code'])) {
            $arr = explode(',', $filter['sale_channel_code']);   
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code IN ({$str}) ";
        }
        //店铺
        if (!empty($filter['shop_code'])) {
             $arr = explode(',', $filter['shop_code']);   
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code IN ({$str}) ";
        }
        //挂起原因
        if (!empty($filter['is_pending_code'])) {
            $arr = explode(',', $filter['is_pending_code']);
            $str = $this->arr_to_in_sql_value($arr, 'is_pending_code', $sql_values);
            $sql_main .= " AND rl.is_pending_code IN ({$str}) ";
        }
        //挂起备注
        if (!empty($filter['is_pending_memo'])) {
            $sql_main .= " AND rl.is_pending_memo LIKE :is_pending_memo ";
            $sql_values[':is_pending_memo'] = '%' . $filter['is_pending_memo'] . '%';
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sub_sql .= " AND rr.goods_code LIKE :goods_code";
            $sub_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (!empty($filter['barcode'])) {

//            $sub_sql .= " AND rr.barcode LIKE :barcode ";
//            $sub_values[':barcode'] = '%' . $filter['barcode'] . '%';
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sub_sql .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sub_sql .= " AND rr.sku in({$sku_str}) ";
            }
        }
        //配送方式
        if (!empty($filter['express_code'])) {
            $arr = explode(',', $filter['express_code']);

            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code IN ({$str}) ";
        }
        //客户留言
        if (!empty($filter['buyer_remark'])) {
            $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
            $sql_values[':buyer_remark'] = '%' . $filter['buyer_remark'] . '%';
        }
        //商家留言
        if (!empty($filter['seller_remark'])) {
            $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
            $sql_values[':seller_remark'] = '%' . $filter['seller_remark'] . '%';
        }
        //换货单
        if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
            $sql_main .= " AND rl.is_change_record = :is_change_record ";
            $sql_values[':is_change_record'] = $filter['is_change_record'];
        }
        //收货人
        if (!empty($filter['receiver_name'])) {
//            $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//            $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
//            
            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
        }
        //手机号码
        if (!empty($filter['receiver_mobile'])) {

                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'],'tel');
                if(!empty($customer_address_id)){
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_main .= " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                }else{
                         $sql_main .= " AND rl.receiver_mobile = :receiver_mobile ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile']; 
                }

//            $sql_main .= " AND rl.receiver_mobile LIKE :receiver_mobile ";
//            $sql_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
        }
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $sql_main .= " AND rl.receiver_country = :country ";
            $sql_values[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND rl.receiver_province = :province ";
            $sql_values[':province'] = $filter['province'];
        }
        //城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $sql_main .= " AND rl.receiver_city = :city ";
            $sql_values[':city'] = $filter['city'];
        }
        //地区
        if (isset($filter['district']) && $filter['district'] !== '') {
            $sql_main .= " AND rl.receiver_district = :district ";
            $sql_values[':district'] = $filter['district'];
        }
        //详细地址
        if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
            $sql_main .= " AND rl.receiver_addr LIKE :receiver_addr ";
            $sql_values[':receiver_addr'] = '%' . $filter['receiver_addr'] . '%';
        }
        //发票
        if (isset($filter['is_invoice']) && ($filter['is_invoice'] == '0' || $filter['is_invoice'] == '1')) {
            if ($filter['is_invoice'] == '0') {
                $sql_main .= " AND rl.invoice_title = '' ";
            } else {
                $sql_main .= " AND rl.invoice_title <> '' ";
            }
        }
        //仓库
        if (!empty($filter['store_code'])) {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code IN ({$str}) ";
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
        //支付时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }
        //解挂时间
        if (!empty($filter['unpsending_time_start'])) {
            $sql_main .= " AND rl.is_unpending_time >= :unpsending_time_start ";
            $sql_values[':unpsending_time_start'] = $filter['unpsending_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['unpsending_time_end'])) {
            $sql_main .= " AND rl.is_unpending_time <= :unpsending_time_end ";
            $sql_values[':unpsending_time_end'] = $filter['unpsending_time_end'] . ' 23:59:59';
        }
        //子查询合并
        if (!empty($sub_values)) {
            $sql_main .= " AND rl.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');

        $select = 'rl.*';
        $sql_main .= " ORDER BY sell_record_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => &$value) {
            $value['status_text'] = $this->get_status_text($value);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['is_pending_name'] = oms_tb_val("base_suspend_label", "suspend_label_name", array('suspend_label_code' => $value['is_pending_code']));
            $value['is_unpending_time'] = $value['is_unpending_time'] == '0000-00-00 00:00:00' ? '' : $value['is_unpending_time'];
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 转单时新增订单到oms_sell_record
     * @param type $record 主单据
     * @param type $detail 商品
     */
    function add_api_order($record, $detail, $is_add_action_to_api = 0, $log_msg = '') {
        $customer = array(
            'customer_name' => $record['buyer_name'],
            'shop_code' => $record['shop_code'],
            'address' => $record['receiver_addr'],
            'country' => $record['receiver_country'],
            'province' => $record['receiver_province'],
            'city' => $record['receiver_city'],
            'district' => $record['receiver_district'],
            'street' => $record['receiver_street'],
            'zipcode' => $record['receiver_zip_code'],
            'tel' => $record['receiver_mobile'],
            'home_tel' => $record['receiver_phone'],
            'name' => $record['receiver_name'],
            'is_add_time' => date('Y-m-d H:i:s'),
        );
        $this->db->begin_trans();
        try {
            $result = $this->db->insert('oms_sell_record', $record);
            if ($result !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
            }
            //记录订单转入日志
            $this->add_action($record['sell_record_code'], '新增', '转单生成' . $log_msg);
            if ($is_add_action_to_api == 1) {
                $this->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code'], 'convert');
            }

            //添加商品明细
            $ret = $this->add_detail($detail);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            //计算计划发货时间
            $ret = load_model("oms/SellRecordOptModel")->set_sell_plan_send_time($record['sell_record_code']);
            //添加用户信息
            $ret = load_model("crm/CustomerModel")->handle_customer($customer);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1, $record['sell_record_code']);
    }

    /**
     * 添加商品明细（可批量）
     * @param type $detail
     */
    function add_detail($detail) {
        $ret = $this->db->insert($this->detail_table, $detail);
        if ($ret) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
        }
    }

    /**
     * 对用户输入的逗号分隔的字符串进行处理
     * @param type $str 要处理的字符串
     * @param type $quote 是否为每个加上引号 0：不加，1：加
     * @param type $caps 是否转换大小写，0：不转换，1：转小写，2：转大写
     */
    function deal_strs($str, $quote = 1, $caps = 0) {
        $str = str_replace("，", ",", $str); //将中文逗号转成英文逗号
        $str = str_replace(" ", "", $str); //去掉空格
        $str = trim($str, ','); //去掉前后多余的逗号
        if ($quote = 1) {
            $str = "'" . str_replace(",", "','", $str) . "'";
        }
        if ($caps = 1) {
            $str = strtolower($str);
        } elseif ($caps = 2) {
            $str = strtoupper($str);
        }
        return $str;
    }

    /**
     * 一键确认：已付款未确认、被我锁定或未被锁定、的正常单（正常单：非缺货、非设问、非挂起）；
     */
    function a_key_confirm($request) {
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $sql = "select * from oms_sell_record where order_status=0 and pay_status=2 and (is_lock=0 or (is_lock=1 and is_lock_person='{$sys_user['user_code']}')) and is_pending=0 and (must_occupy_inv=1 and lock_inv_status=1) and is_problem=0";
        $data = $this->db->get_row($sql);
        $msg = '';
        if ($data) {
            $is_lock = false;
            //未锁定的订单先锁定
            if ($data['is_lock'] == '0') {
                $is_lock = true;
            }
            if ($is_lock) {
                $ret = load_model("oms/SellRecordOptModel")->opt_lock($data['sell_record_code']);
                if ($ret['status'] != '1') {
                    $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
                }
            }
            $ret = load_model("oms/SellRecordOptModel")->opt_confirm($data['sell_record_code']);
            if ($ret['status'] != '1') {
                $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
            }
            if ($is_lock) {
                $ret = load_model("oms/SellRecordOptModel")->opt_unlock($data['sell_record_code']);
                if ($ret['status'] != '1') {
                    $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
                }
            }
        } else {
            $response['status'] = 1;
        }
        if (!empty($msg)) {
            $task_id = load_model('common/TaskModel')->get_task_id($request);
            load_model('common/TaskModel')->save_log($task_id, $msg);
            $response['status'] = 100;
        }
    }

    function get_status_text($value) {
        $order_status_text = require_conf("sys/order_status_text");
        $status_text = "";
        if ($value['is_problem'] == '1') {
            $status_text .= @$order_status_text['question'];
        }
        if ($value['is_pending'] == '1') {
            $status_text .= $order_status_text['pending'];
        }
        if ($value['must_occupy_inv'] == '1' && $value['lock_inv_status'] != '1') {
            $status_text .= @$order_status_text['short'];
        }
        return $status_text;
    }

    //发票 问提 复制 缺货 换货 拆单 挂起 合单 手工单 锁定 piao wen fu que huan cai gua he shou shuo
    function get_sell_record_tag_img($row, $sysuser) {
        $tag_arr = array();
        if ($row['invoice_status'] > 0) {
            $tag_arr[] = array('piao', '有发票');
        }
        if ($row['is_problem'] > 0) {
            $tag_arr[] = array('wen', '问提单');
        }
        if ($row['is_copy'] > 0) {
            $tag_arr[] = array('fu', '复制单');
        }
        if ($row['shipping_status'] == 0 && $row['order_status'] <> 3 && $row['must_occupy_inv'] == 1 && $row['lock_inv_status'] <> 1) {
            $tag_arr[] = array('que', '缺货单');
        }
        if ($row['is_change_record'] > 0) {
            $tag_arr[] = array('huan', '换货单');
        }
        if ($row['is_split_new'] > 0) {
            $tag_arr[] = array('cai', '拆单');
        }
        if ($row['is_pending'] > 0) {
            $tag_arr[] = array('gua', '挂起');
        }
        if ($row['is_combine_new'] > 0) {
            $tag_arr[] = array('he', '合单');
        }
        if ($row['is_handwork'] > 0) {
            $tag_arr[] = array('shou', '手工单');
        }
        if ($row['is_lock'] > 0 && $sysuser['user_code'] != $row['is_lock_person']) {
            $tag_arr[] = array('shuo', '锁定');
        }
        $html_arr = array();
        foreach ($tag_arr as $_tag) {
            $html_arr[] = "<img src='assets/img/state_icon/{$_tag[0]}_icon.png' title='{$_tag[1]}'/>";
        }
        return join('', $html_arr);
    }

    function a_key_confirm_create_task() {
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $is_lock_person = $sys_user['user_code'];
        $obj_task = load_model('common/TaskModel');

        $task_data = array();
        $task_data['code'] = 'oms_a_key_confirm';
        $task_data['start_time'] = time();
        $ret = $obj_task->save_task($task_data);
        if ($ret === false) {
            return $ret;
        }
        $task_id = (int) $ret['data'];
        if ($task_id == 0) {
            return $this->format_ret(-1, '', '生成主任务失败');
        }

        $page_num = 1;
        while (1) {
            $ret = $this->a_key_confirm_create_taskeach($task_id, $is_lock_person, $page_num);
            if ($ret['status'] < 0) {
                $msg = '分发任务失败：' . $ret['message'];
                return $this->format_ret(-1, '', $msg);
            }
            if ($ret['status'] == 100) {
                break;
            }
            $page_num++;
        }

        return $this->format_ret(1, $task_id);
    }

    function a_key_confirm_create_taskeach($task_id, $is_lock_person, $page_num = 1, $page_size = 2) {
        $page_start = ($page_num - 1) * $page_size;

        $wh = '';
        $wh .= load_model('base/StoreModel')->get_sql_purview_store();
        $wh .= load_model('base/ShopModel')->get_sql_purview_shop();
        $wh .= " order by pay_time,sell_record_id limit {$page_start},{$page_size}";

        $sql = "select sell_record_id from oms_sell_record where order_status=0 and pay_status=2 and (is_lock=0 or (is_lock=1 and is_lock_person='{$is_lock_person}')) and is_pending=0 and (must_occupy_inv=1 and lock_inv_status=1) and is_problem=0 " . $wh;
        //echo $sql."<br/>";
        $sell_record_id_arr = ctx()->db->get_all_col($sql, array(), $page_size);

        //echo '<hr/>$sell_record_id_arr<xmp>'.var_export($sell_record_id_arr,true).'</xmp>';die;

        if (empty($sell_record_id_arr)) {
            return $this->format_ret(100, '', '分发任务完成');
        }
        $request = array();
        $request['app_fmt'] = 'json';
        $request['app_act'] = 'oms/sell_record/start_confirm';
        $request['id'] = join(',', $sell_record_id_arr);

        $obj_task = load_model('common/TaskModel');
        $ret = $obj_task->save_task_process(array('task_id' => $task_id), $request);
        $response = $obj_task->save_log($task_id, "当前确认ID=" . join(',', $sell_record_id_arr));

        return $ret;
    }

    /**
     * Get oms_deliver_record list, according to waves_record_id.
     * @param $ids
     * @return array|bool
     */
    public function get_deliver_record_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array('status' => '-1', 'data' => '', 'message' => '传入参数不正确');
        }

        $recordList = $this->db->get_all("select b.deliver_record_id
        from oms_sell_record a
        inner join oms_deliver_record b on b.waves_record_id = a.waves_record_id and b.sell_record_code = a.sell_record_code
        where a.sell_record_id in ($str)");
        if (empty($recordList)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }

        $idList = array();
        foreach ($recordList as $row) {
            $idList[] = $row['deliver_record_id'];
        }
        return array('status' => '1', 'data' => $idList, 'message' => '验收成功');
    }

    public function shipped_import($sellRecordCode, $expressCode, $expressNo) {
        if (empty($expressCode) && empty($expressNo)) {
            return array('status' => '-1', 'message' => '快递方式和快递单号为空');
        }

        //var_dump($sellRecordCode, $expressCode, $expressNo);

        $arr = array();

        if (!empty($expressCode)) {
            $r = $this->db->get_row("select  from base_express where express_code = :express_code and status = 1", array('express_code' => $expressCode));
            if (empty($r)) {
                return array('status' => '-1', 'message' => '快递方式不存在或者未启用: ' . $expressCode);
            }

            $arr['express_code'] = $expressCode;
        }

        if (!empty($expressNo))
            $arr['express_no'] = $expressNo;

        return load_model('oms/DeliverRecordModel')->edit_express($sellRecordCode, $arr);
    }

    function import_trade_action() {
        require_model('util/CsvImport');
        $import_obj = new CsvImport();

        $ret = $import_obj->get_upload();
        if ($ret['status'] < 0) {
            return $ret;
        }
        $file_name = $ret['data'];

        //$file_name = 'import_sell_record_551d02b9ebd97.csv';
        $ret = $import_obj->get_csv_data($file_name);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = $ret['data']['data'];

        $sql = "select shop_code,sale_channel_code from base_shop";
        $db_channel = ctx()->db->get_all($sql);
        $sale_channel_arr = array();
        foreach ($db_channel as $sub_channel) {
            $sale_channel_arr[$sub_channel['shop_code']] = $sub_channel['sale_channel_code'];
        }

        $fld_map = array(
            '下单日期*' => 'order_first_insert_time',
            '店铺代码*' => 'shop_code',
            '交易号*' => 'tid',
            '会员昵称*' => 'buyer_nick',
            '收货人*' => 'receiver_name',
            '手机号*' => 'receiver_mobile',
            '固定电话' => 'receiver_phone',
            '收货地址*' => 'receiver_address',
            '邮编' => 'receiver_zip_code',
            '是否货到付款*' => 'is_cod',
            '付款日期' => 'pay_time',
            '仓库代码' => 'store_code',
            '配送方式代码' => 'express_code',
            '运费' => 'express_money',
            '买家留言' => 'buyer_remark',
            '商家留言' => 'seller_remark',
            '商品条形码*' => 'goods_barcode',
            '单价' => 'price',
            '数量' => 'num',
        );

        $record_fld = 'order_first_insert_time,shop_code,tid,buyer_nick,receiver_name,receiver_mobile,receiver_phone,receiver_address,receiver_zip_code,pay_time,store_code,express_code,express_money,buyer_remark,seller_remark,pay_type,pay_code,receiver_province,receiver_city,receiver_district,receiver_addr,status,source,receiver_country';
        $record_fld .= ',receiver_email,express_no,sku_num,num,goods_weigh,seller_flag,delivery_money,alipay_no,invoice_type,invoice_title,invoice_content,invoice_money';
        $record_mx_fld = 'tid,goods_barcode,price,num,avg_money';
        $record_mx_fld .= ',oid,sku_id';

        $util_obj = load_model('util/ViewUtilModel');

        $err_arr = array();
        $success_arr = array();

        foreach ($data as $data_row) {
            $_row = array();
            foreach ($fld_map as $k => $v) {
                $_row[$v] = $data_row[$k];
            }
            if (empty($_row['tid'])) {
                continue;
            }
            $_row['avg_money'] = $_row['price'] * $_row['num'];
            $_row['pay_type'] = $_row['is_cod'] == '是' ? '1' : '0';
            $_row['pay_code'] = $_row['pay_type'] == '1' ? 'cod' : 'bank';
            if (!empty($_row['order_first_insert_time'])) {
                $_row['order_first_insert_time'] = date('Y-m-d H:i:s', strtotime($_row['order_first_insert_time']));
            }
            if (!empty($_row['pay_time'])) {
                $_row['pay_time'] = date('Y-m-d H:i:s', strtotime($_row['pay_time']));
            }

            $_row['receiver_address'] = str_replace('  ', '', $_row['receiver_address']);
            $_addr = explode(' ', $_row['receiver_address']);
            $_row['receiver_province'] = $_addr[0];
            $_row['receiver_city'] = $_addr[1];
            $_row['receiver_district'] = $_addr[2];
            $_row['receiver_country'] = '中国';

            $_addr_str = "{$_addr[0]} {$_addr[1]} {$_addr[2]}";
            $_row['receiver_addr'] = str_replace($_addr_str, '', $_row['receiver_address']);
            $_row['source'] = isset($sale_channel_arr[$_row['shop_code']]) ? $sale_channel_arr[$_row['shop_code']] : null;
            if (empty($_row['source'])) {
                $err_arr[] = $tid . '找不到订单来源';
            }

            $trade_data[$_row['tid']]['record'][] = $util_obj->copy_arr_by_fld($_row, $record_fld, 0, 1);
            $trade_data[$_row['tid']]['mx'][] = $util_obj->copy_arr_by_fld($_row, $record_mx_fld, 0, 1);
            //break;
        }
        //echo '<hr/>$trade_data<xmp>'.var_export($trade_data,true).'</xmp>';
        $api_data = array();
        foreach ($trade_data as $tid => $sub_tid) {
            $pre_v = '';
            $record_err_tag = 0;
            foreach ($sub_tid['record'] as $record_row) {
                $cur_v = join(',', $record_row);
                if ($pre_v != '' && $pre_v != $cur_v) {
                    $record_err_tag = 1;
                    break;
                }
                $pre_v = $cur_v;
            }
            if ($record_err_tag == 1) {
                $err_arr[] = $tid . '订单信息不匹配';
            }
            $api_data[$tid] = $sub_tid['record'][0];
            $_order_money = 0;
            foreach ($sub_tid['mx'] as $kk => $_row) {
                $_order_money += $_row['avg_money'];
                $_row['tid'] = $tid;
                $_barcode = "{$_row['goods_barcode']}";
                if (isset($trade_data[$tid]['data'][$_barcode])) {
                    $api_data[$tid]['mx'][$_barcode]['num'] += $_row['num'];
                    $api_data[$tid]['mx'][$_barcode]['avg_money'] += $_row['avg_money'];
                } else {
                    $_row['sku_properties'] = '';
                    $api_data[$tid]['mx'][$_barcode] = $_row;
                }
            }
            $_order_money += $api_data[$tid]['express_money'];
            $api_data[$tid]['order_money'] = $_order_money;
        }
        //echo '<hr/>$api_data<xmp>'.var_export($api_data,true).'</xmp>';die;
        $obj = load_model('oms/TranslateOrderModel');
        $obj->import_flag = 1;
        foreach ($api_data as $tid => $sub_api_data) {
            $ret = $obj->translate_order_by_data($sub_api_data);
            if ($ret['status'] < 0) {
                $err_arr[] = $tid . $ret['message'];
            } else {
                $success_arr[] = $tid . '导入成功';
            }
        }
        $ret_msg = '';
        if (!empty($err_arr)) {
            $ret_msg .= "<div style='color:red'>导入失败的订单：<br/>" . join('<br/>', $err_arr) . "</div>";
        }
        if (!empty($success_arr)) {
            $ret_msg .= "<hr/><div>导入成功的订单：<br/>" . join('<br/>', $success_arr) . "</div>";
        }
        return $ret_msg;
    }

    //今日已付款订单数
    function pay_num($date) {

        $sql = "select count(*) from api_order where pay_time >= '" . $date . "' and pay_type = '0' ";
        $pay_num1 = ctx()->db->getOne($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and pay_type = '1' and status = 1";
        $pay_num2 = ctx()->db->getOne($sql);

        return $pay_num1 + $pay_num2;
    }

    //今日已付款订单数(明细)
    function category_num($date) {
        $sql = "select count(*) as num  , shop_code  from  api_order  where pay_time > '" . $date . "' and pay_type = '0'group by shop_code ";
        $category_num1 = ctx()->db->getAll($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and  pay_type = '1' and status = 1  group by shop_code ";
        $category_num2 = ctx()->db->getOne($sql);
        $category_num_arr = array();

        foreach ($category_num1 as $key => $value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $category_num_arr[$value['shop_name']] = $value;
        }

        foreach ($category_num2 as $key => $value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            if (isset($category_num_arr[$value['shop_name']])) {
                $category_num_arr[$value['shop_name']]['num'] += $value['num'];
            } else {
                $category_num_arr[$value['shop_name']] = $value;
            }
        }
        foreach ($category_num_arr as $key => $value) {

            $value['short_name'] = substr($value['shop_name'], 0, 6);
        }

        return $category_num_arr;
    }

    //今日已转单数
    function transform_num($date) {
        $sql = "select count(*) from api_order where pay_time >= '" . $date . "' and pay_type ='0' and is_change=1";
        $transform_num1 = ctx()->db->getOne($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and pay_type = '1' and status = 1 and is_change=1";
        $transform_num2 = ctx()->db->getOne($sql);

        return $transform_num1 + $transform_num2;
    }

    //待确认订单数
    function unconfirm_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 0 and pay_status=2 and  shipping_status=0 and is_fenxiao=0";
        $unconfirm_num = ctx()->db->getOne($sql);
        return $unconfirm_num;
    }

    //待确认订单数(挂起)
    function pending_num() {
        $sql = "select count(*) as sum from oms_sell_record where is_fenxiao = 0 and  is_pending = 1 and is_fenxiao=0";
        $pending_num = ctx()->db->getOne($sql);
        return $pending_num;
    }

    //待确认订单数(问题)
    function problem_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status !=3 and is_problem=1 and is_fenxiao=0";
        $problem_num = ctx()->db->getOne($sql);
        return $problem_num;
    }

    //待确认订单数(缺货)
    function stockout_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 0 and must_occupy_inv=1  and lock_inv_status in (0,2,3) and is_fenxiao=0";
        $stockout_num = ctx()->db->getOne($sql);
        return $stockout_num;
    }

    //待通知配货订单数
    function unnotice_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 1 and shipping_status = 0 and is_fenxiao=0";
        $unnotice_num = ctx()->db->getOne($sql);
        return $unnotice_num;
    }

    //待拣货订单数
    function unpick_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 1 and shipping_status = 1 and waves_record_id=0 and is_fenxiao=0";
        $unpick_num = ctx()->db->getOne($sql);
        return $unpick_num;
    }

    function normal_num() {
        $sql = "select count(*) as  sum from oms_sell_record where  (pay_status = 2 or pay_type ='cod') and  order_status = 0 and  is_problem = 0 and is_pending=0 and lock_inv_status=1  and shipping_status = 0  and is_fenxiao=0";
        $back_error_num = ctx()->db->getOne($sql);
        return $back_error_num;
    }

    //待扫描订单数
    function unscan_num() {
        $sql = "select count(*) as sum from oms_deliver_record d INNER JOIN oms_waves_record w ON w.waves_record_id = d.waves_record_id
        where w.is_accept=1 and d.is_deliver = 0 and d.is_cancel = 0 ";

        $unscan_num = ctx()->db->getOne($sql);
        return $unscan_num;
    }

    //今日已发货订单数
    function deliver_num($date) {

        $sql = "select count(*) as sum  from oms_sell_record where shipping_status = 4 AND delivery_date = '" . $date . "' and is_fenxiao=0";
        $deliver_num = ctx()->db->getOne($sql);
        return $deliver_num;
    }

    //今日网单回写订单数
    function back_num($date) {
        $sql = "select count(*) as sum  from api_order_send where  status=1 and  upload_time > '" . $date . "' ";
        $back_num = ctx()->db->getOne($sql);
        return $back_num;
    }

    //回写失败订单数
    function back_error_num() {
        $sql = "select count(*) as  sum from api_order_send where    status <0 ";
        $back_error_num = ctx()->db->getOne($sql);
        return $back_error_num;
    }
    
    function add_order_gift ($data){
        
       $sql = "SELECT 
base_goods.status,
goods_sku.sku,
goods_sku.goods_code,
goods_sku.spec1_code,
goods_sku.barcode,base_goods.goods_prop,
goods_sku.spec2_code FROM base_goods,goods_sku 
WHERE goods_sku.barcode=:barcode AND base_goods.goods_code = goods_sku.goods_code ";
 
    	$goods = $this->db->get_row($sql,array(':barcode'=>$data['sku']));
        if(empty($goods)){
            return $this->format_ret(-1,'','该商品不存在');
        }
        if($goods['status']==1){
            return $this->format_ret(-1,'','该商品未启用');
        }else{
            $is_only_gift = load_model('sys/SysParamsModel')->get_val_by_code('is_only_gift');
            if($is_only_gift['is_only_gift'] == 1){
                if($goods['goods_prop'] != 2){
                    return $this->format_ret(-1,'','订单赠品工具参数开启，只能添加商品属性为‘赠品’的商品');
                }
            }
         
            $id_arr = explode(',', $data['id']);
            
            $sell_record_code_list_str = "'".implode("','",$id_arr)."'";
            
            $sql = "select store_code,deal_code_list,sell_record_code,order_status from oms_sell_record where sell_record_code IN (".$sell_record_code_list_str.")";
 
            $orders = $this->db->get_all($sql);

            $sku_data = $goods;
            $msg = "";

			foreach ($orders as $order)
            {
            	$result['data'][0]['num'] = $data['num'];//每个礼品的数量
                $result['data'][0]['is_gift'] = 1;//礼品标记
               	$result['data'][0] = array_merge($result['data'][0],$sku_data);
                
                $result['sell_record_code'] = $order['sell_record_code'];
                $result['order_status'] = $order['order_status'];
                $result['store_code'] = $order['store_code'];
                
                $deal_code_list = explode(',', $order['deal_code_list']);
                $result['deal_code'] = $deal_code_list[0];
                
                $result['action_log'] = "赠品条码：{$data['sku']} 数量：{$data['num']};";
                
                $ret = load_model('oms/SellRecordOptModel')->opt_new_multi_detail($result,1);
                
                if($ret['status'] != 1){
                    $msg .= $id.$ret['message']."<br />";
                }
            }

            $status = 1;
            if (!empty($msg)){
                $status = -1;
            }
            return $this->format_ret($status,'',$msg);
        }	
        
        
    }

}
