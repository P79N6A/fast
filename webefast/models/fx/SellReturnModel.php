<?php

/**
 * 退货模型类
 * 2015/1/5
 * @author jia.ceng
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_model('oms/SellRecordModel');
require_lang('fx');

class SellReturnModel extends TbModel {

    public $return_log_check = 1;
    public $return_type = array(
        1 => '退款单',
        2 => '退货单',
        3 => '退款退货单',
    );
    public $return_order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废'
    );
    public $return_shipping_status = array(
        0 => '待收货',
        1 => '已确认收货',
        2 => '待收货'
    );
    public $finance_check_status = array(
        0 => '待财审',
        1 => '已财审',
        2 => '待财审',
        3 => '财务退回'
    );
    public $is_package_out_stock = array(
        0 => '包裹未出库',
        1 => '包裹已出库',
    );
    public $is_shipping_status = array(
        'all' => '全部',
        1 => '未通知仓库收货',
        2 => '已通知仓库收货',
        3 => '已确认收货',
    );
    //put your code here
    protected $table = 'oms_sell_return';
    protected $pk = 'sell_return_code';
    protected $detail_table = 'oms_sell_return_detail';
    protected $action_table = 'oms_sell_return_action';

    function get_mdl_kv($fld, $k) {
        $v = $this->$fld[$k];
        return $v;
    }

    /**
     * 获取退单列表
     * @param type $filter
     * @return type
     */
    function get_after_service_list($filter) {
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        if (isset($filter['is_remark']) && $filter['is_remark'] !== '') {
            $filter[$filter['is_remark']] = $filter['is_remark_value'];
        }
        if (isset($filter['remark']) && $filter['remark'] !== '') {
            $filter[$filter['remark']] = $filter['remark_value'];
        }
        $sql_values = array();
        $sql_join = "";

        $sql_main = "FROM {$this->table} r1 $sql_join WHERE 1 AND (is_fenxiao = 1 OR is_fenxiao = 2) ";

        $sql_detail_join = '';
        $sql_sub = "select sell_return_code from {$this->detail_table} rr $sql_detail_join where 1 ";
        $sub_values = array();
        
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        //商店仓库权限
        //$login_type = CTX()->get_session('login_type');
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
//            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code, 'get_fx_store');
            //获取当前登录的分销商code，根据code查询分销商code
            //$user_code = CTX()->get_session('user_code');            
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if(!empty($custom)) {
            $sql_main .= " AND r1.fenxiao_code = :fenxiao_code";
            $sql_values[':fenxiao_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
            //店铺
            if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
                    $arr = explode(',', $filter['shop_code']);
                    $str = $this->arr_to_in_sql_value($arr,'shop_code',$sql_values);
                $sql_main .= " AND r1.shop_code in ( {$str} ) ";
            }
            //仓库
            if (isset($filter['store_code']) && $filter['store_code'] !== '') {
                    $arr = explode(',', $filter['store_code']);
                    $str = $this->arr_to_in_sql_value($arr,'store_code',$sql_values);
                $sql_main .= " AND r1.store_code in ({$str}) ";
            }
        } else {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);
            $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
            $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
        }

        //确认状态
        if (isset($filter['confirm_status']) && $filter['confirm_status'] != 'all') {
            $sql_main .= " AND r1.return_order_status = :return_order_status ";
            $sql_values[':return_order_status'] = $filter['confirm_status'];
        }

        //唯一码 // 开启唯一码之后 唯一码查询条件
        if (isset($filter['unique_code']) && $filter['unique_code'] !== '') {

            //  $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
            // $sql_join .= " left join goods_unique_code_log r2 on r2.record_code = r1.sell_return_code ";
            $un_data = load_model('prm/GoodsUniqueCodeLogModel')->get_goods_unique_code($filter['unique_code'], 'sell_return');
            if (!empty($un_data)) {
                $sql_main .= " AND r1.sell_return_code in('" . implode("','", $un_data) . "') ";
            }
        }
        //通知仓库收货&&确认收货
        if ((isset($filter['notice_store']) && $filter['notice_store'] != 'all') || (isset($filter['return_shipping_status']) && $filter['return_shipping_status'] != 'all')) {
            $notice_arr = array(0 => array(0, 1), 1 => array(2));
            $return_arr = array(0 => array(0, 2), 1 => array(1));
            $notice_temp = array();
            $return_temp = array();
            $deal_arr = array();
            if (isset($filter['notice_store']) && $filter['notice_store'] != 'all') {
                $notice_temp = $notice_arr[$filter['notice_store']];
            }
            if (isset($filter['return_shipping_status']) && $filter['return_shipping_status'] != 'all') {
                $return_temp = $return_arr[$filter['return_shipping_status']];
            }
            $sign = 0;
            if (!empty($notice_temp) && !empty($return_temp)) {
                $deal_arr = array_intersect($notice_temp, $return_temp);
                if (empty($deal_arr)) {
                    $sign = 1;
                }
            } else {
                $deal_arr = array_merge($notice_temp, $return_temp);
            }
            if (empty($deal_arr) && $sign > 0) {
                $sql_main .= " AND r1.return_shipping_status>2";
            } else {
                $sql_main .= " AND (";
                foreach ($deal_arr as $v) {
                    $sql_main .= " r1.return_shipping_status={$v} OR";
                }
                $sql_main = substr($sql_main, 0, -3);
                $sql_main .= ") ";
            }
        }
        //通知财务退款
        if (isset($filter['notice_finance']) && $filter['notice_finance'] != 'all') {
            if ($filter['notice_finance'] == '0') {
                $sql_main .= " AND r1.finance_check_status = 0";
            } else {
                $sql_main .= " AND r1.finance_check_status = 2";
            }
        }
        //确认退款
        if (isset($filter['confirm_return']) && $filter['confirm_return'] != 'all') {
            if ($filter['confirm_return'] == '0') {
                $sql_main .= " AND r1.finance_check_status <> 1";
            } else {
                $sql_main .= " AND r1.finance_check_status = 1";
            }
        }
        //确认收货
        /* if (isset($filter['return_shipping_status']) && $filter['return_shipping_status'] != 'all') {
          if ($filter['return_shipping_status'] == '0') {
          $sql_main .= " AND (r1.return_shipping_status = 0 OR r1.return_shipping_status = 2)";
          //} else if ($filter['return_shipping_status'] == '2') {
          //    $sql_main .= " AND r1.return_shipping_status = 2";
          } else {
          $sql_main .= " AND r1.return_shipping_status = 1";
          }
          } */
        //有无换货单
        if (isset($filter['is_change']) && $filter['is_change'] != '') {
            $sql_main .= " AND r1.is_exchange_goods = :is_change";
            $sql_values[':is_change'] = $filter['is_change'];
        }
        //差异收货
        if (isset($filter['is_differ']) && $filter['is_differ'] != '') {
            if ($filter['is_differ'] == 1) {
                $sql_main .= " AND r1.return_shipping_status = 1 AND r1.note_num <> r1.recv_num";
            } else {
                $sql_main .= " AND r1.return_shipping_status = 1 AND r1.note_num = r1.recv_num";
            }
        }

        //是否有买家退单说明
        if (isset($filter['is_return_buyer_memo']) && $filter['is_return_buyer_memo'] != 'all') {
            if ($filter['is_return_buyer_memo'] == '0') {
                $sql_main .= " AND (r1.return_buyer_memo ='' or r1.return_buyer_memo is null )";
            } else {
                $sql_main .= " AND r1.return_buyer_memo <>''";
            }
        }
        //是否有卖家退单备注
        if (isset($filter['is_return_remark']) && $filter['is_return_remark'] != 'all') {
            if ($filter['is_return_remark'] == '0') {
                $sql_main .= " AND (r1.return_remark ='' or r1.return_remark is null )";
            } else {
                $sql_main .= " AND r1.return_remark <>''";
            }
        }
        //买家退单说明
        if (isset($filter['return_buyer_memo']) && !empty($filter['return_buyer_memo'])) {
            $sql_main .= " AND r1.return_buyer_memo like :return_buyer_memo ";
            $sql_values[':return_buyer_memo'] = "%" . $filter['return_buyer_memo'] . "%";
        }
        //卖家退单备注
        if (isset($filter['return_remark']) && !empty($filter['return_remark'])) {
            $sql_main .= " AND r1.return_remark like :return_remark ";
            $sql_values[':return_remark'] = "%" . $filter['return_remark'] . "%";
        }

        //退单号
        if (isset($filter['sell_return_code']) && !empty($filter['sell_return_code'])) {
                      $arr = explode(',', $filter['sell_return_code']);
                    $str = $this->arr_to_in_sql_value($arr,'sell_return_code',$sql_values);
            $sql_main .= " AND r1.sell_return_code in (" .$str . ") ";
        }
        //原单号
        if (isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])) {
                      $arr = explode(',', $filter['sell_record_code']);
                    $str = $this->arr_to_in_sql_value($arr,'sell_record_code',$sql_values);
            $sql_main .= " AND r1.sell_record_code in (" . $str . ") ";
        }
        //交易号
        if (isset($filter['deal_code']) && !empty($filter['deal_code'])) {
            $sql_main .= " AND r1.deal_code like :deal_code ";
            $sql_values[':deal_code'] = "%" . $filter['deal_code'] . "%";
        }
        //买家昵称
        if (isset($filter['buyer_name']) && !empty($filter['buyer_name'])) {
            $sql_main .= " AND r1.buyer_name like :buyer_name ";
            $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
        }
        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
                     $arr = explode(',',$filter['source']);
            $str = $this->arr_to_in_sql_value($arr, 'source', $sql_values);
            $sql_main .= " AND r1.sale_channel_code in ({$str}) ";
        }
        //店铺
//        if (isset($filter['shop_code']) && !empty($filter['shop_code'])) {
//            $sql_main .= " AND r1.shop_code in ('" . str_replace(",", "','", $filter['shop_code']) . "') ";
//        }
        //商品编码
        if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
            $sql_sub .= " AND  rr.goods_code = :goods_code";
            $sub_values[':goods_code'] = $filter['goods_code'];
        }

        //商品条形码
        $is_sku = FALSE;
        if (isset($filter['barcode']) && !empty($filter['barcode'])) {
//            $sql_sub .= " AND  rr.barcode LIKE :barcode";
//            $sub_values[':barcode'] = "%" . $filter['barcode'] . "%";
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_sub .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $sql_sub .= " AND rr.sku in({$sku_str}) ";
            }

            $is_sku = TRUE;
        }
        //退单类型
        if (isset($filter['return_type']) && !empty($filter['return_type'])) {
                     $arr = explode(',',$filter['return_type']);
            $str = $this->arr_to_in_sql_value($arr, 'return_type', $sql_values);
            $sql_main .= " AND  r1.return_type in (" .$str. ")";
        }

        //退单类型tab页面
        if (isset($filter['after_service_list_tab']) && $filter['after_service_list_tab'] !== '') {
            //待确认
            if ($filter['after_service_list_tab'] == 'tabs_confirm') {
                $sql_main .= " and r1.return_order_status = 0";
            }
            //待收货
            if ($filter['after_service_list_tab'] == 'tabs_receive_goods') {
                $sql_main .= " and r1.return_order_status = 1 and r1.return_shipping_status != 1 and r1.return_type != 1";
            }
            //待退款
            if ($filter['after_service_list_tab'] == 'tabs_return_money') {
                $sql_main .= " and r1.return_order_status = 1 and (r1.return_shipping_status = 1 or r1.return_type = 1) and r1.finance_check_status != 1";
            }

            if ($filter['after_service_list_tab'] == 'tabs_wait_finish') {
                $sql_main .= " and r1.return_order_status = 1 and (r1.finance_check_status = 1 or r1.return_shipping_status = 1) and finsih_status = 0";
            }

            //已完成
            if ($filter['after_service_list_tab'] == 'tabs_finish') {
                $sql_main .= " and r1.return_order_status = 1 and r1.finance_check_status = 1 and finsih_status = 1 and (r1.return_shipping_status = 1 or return_type = 1)";
            }
            //已作废
            if ($filter['after_service_list_tab'] == 'tabs_void') {
                $sql_main .= " and r1.return_order_status = 3";
            }
        }
        //退单状态
        if (isset($filter['return_order_status']) && !empty($filter['return_order_status'])) {
            $sql_main .= " AND  r1.return_type in (:return_order_status)";
            $sql_values[':return_order_status'] = str_replace(",", "','", $filter['return_order_status']);
        }

        //退货原因
        if (isset($filter['return_reason_code']) && !empty($filter['return_reason_code'])) {
            $sql_main .= " AND  r1.return_reason_code in (:return_reason_code)";
            $sql_values[':return_reason_code'] = str_replace(",", "','", $filter['return_reason_code']);
        }
        //退款方式
        if (isset($filter['return_pay_code']) && !empty($filter['return_pay_code'])) {
            $code_arr = explode(',', $filter['return_pay_code']);
            if (!empty($code_arr)) {
                $sql_main .= " AND (";
                foreach ($code_arr as $key => $value) {
                    $param_code = 'param_code' . $key;
                    if ($key == 0) {
                        $sql_main .= " return_pay_code = :{$param_code} ";
                    } else {
                        $sql_main .= " or return_pay_code = :{$param_code} ";
                    }

                    $sql_values[':' . $param_code] = $value;
                }
                $sql_main .= ")";
            }
        }
        //有无买家退货物流单号
        if (isset($filter['return_express_no']) && !empty($filter['return_express_no'])) {

        }
        //财务退款状态
        if (isset($filter['finance_reject_reason']) && !empty($filter['finance_reject_reason'])) {
            $sql_main .= " AND  r1.finance_reject_reason in (:finance_reject_reason)";
            $sql_values[':finance_reject_reason'] = str_replace(",", "','", $filter['finance_reject_reason']);
        }

        //需求确认：入库状态与确认收货冲突去除
        //入库状态
        /* if (isset($filter['return_shipping_status']) && !empty($filter['return_shipping_status']) && $filter['return_shipping_status'] != 'all') {
          if ($filter['return_shipping_status'] == 1) {
          $sql_main .= " AND r1.return_shipping_status = 0 AND r1.return_order_status=1 and r1.return_type in (2,3) ";
          } elseif ($filter['return_shipping_status'] == 2) {
          $sql_main .= " AND r1.return_shipping_status = 2";
          } elseif ($filter['return_shipping_status'] == 3) {
          $sql_main .= " AND r1.return_shipping_status = 1";
          }
          } */
        //退货人
        if (isset($filter['return_name']) && !empty($filter['return_name'])) {
            $sql_main .= " AND r1.return_name like :return_name ";
            $sql_values[':return_name'] = '%' . $filter['return_name'] . '%';
        }
        //确认人
        if (isset($filter['confirm_person']) && !empty($filter['confirm_person'])) {
            $sql_main .= " AND r1.confirm_person like :confirm_person ";
            $sql_values[':confirm_person'] = '%' . $filter['confirm_person'] . '%';
        }

        //退货人手机
        if (isset($filter['return_mobile']) && !empty($filter['return_mobile'])) {
            $sql_main .= " AND r1.return_mobile like :return_mobile ";
            $sql_values[':return_mobile'] = '%' . $filter['return_mobile'] . '%';
        }
        //物流单号
        if (isset($filter['return_express_no']) && !empty($filter['return_express_no'])) {
            $sql_main .= " AND r1.return_express_no like :return_express_no ";
            $sql_values[':return_express_no'] = '%' . $filter['return_express_no'] . '%';
        }
        //退货仓库
        if (isset($filter['store_code']) && !empty($filter['store_code'])) {
              $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND r1.store_code in (" . $str . ") ";
        }
        //退货客服
        if (isset($filter['service_code']) && !empty($filter['service_code'])) {
            $sql_main .= " AND r1.service_code = :service_code ";
            $sql_values[':service_code'] = $filter['service_code'];
        }
        //退货包裹单号
        if (isset($filter['sell_return_package_code']) && !empty($filter['sell_return_package_code'])) {
            $sql_main .= " AND r1.sell_return_package_code=:sell_return_package_code";
            $sql_values[':sell_return_package_code'] = $filter['sell_return_package_code'];
        }
        //退货物流单号
        if (isset($filter['return_express_no']) && !empty($filter['return_express_no'])) {
            $sql_main .= " AND r1.return_express_no = :return_express_no ";
            $sql_values[':return_express_no'] = $filter['return_express_no'];
        }
        $old_sql = "select sell_record_code from oms_sell_record ro where 1 ";
        $old_values = array();
        //原单配送方式
        if (isset($filter['express_code']) && !empty($filter['express_code'])) {
            $old_sql .= " AND ro.express_code in (:express_code) ";
            $old_values[':express_code'] = str_replace(",", "','", $filter['express_code']);
        }
        //原单物流单号
        if (isset($filter['express_no']) && !empty($filter['express_no'])) {
            $old_sql .= " AND ro.express_no LIKE :express_no ";
            $old_values[':express_no'] = "%" . $filter['express_no'] . "%";
        }
        //退单创建时间
        if (isset($filter['create_time_start']) && !empty($filter['create_time_start'])) {
            $sql_main .= " AND r1.create_time >= :create_time_start ";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . ' 00:00:00';
        }
        if (isset($filter['create_time_end']) && !empty($filter['create_time_end'])) {
            $sql_main .= " AND r1.create_time <= :create_time_end ";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . ' 23:59:59';
        }
        //退单确认时间
        if (isset($filter['confirm_time_start']) && !empty($filter['confirm_time_start'])) {
            $sql_main .= " AND r1.confirm_time >= :confirm_time_start ";
            $sql_values[':confirm_time_start'] = $filter['confirm_time_start'] . ' 00:00:00';
        }
        if (isset($filter['confirm_time_end']) && !empty($filter['confirm_time_end'])) {
            $sql_main .= " AND r1.confirm_time <= :confirm_time_end ";
            $sql_values[':confirm_time_end'] = $filter['confirm_time_end'] . ' 23:59:59';
        }
        //信息审核时间
        if (isset($filter['check_time_start']) && !empty($filter['check_time_start'])) {
            $sql_main .= " AND r1.check_time >= :check_time_start ";
            $sql_values[':check_time_start'] = $filter['check_time_start'] . ' 00:00:00';
        }
        if (isset($filter['check_time_end']) && !empty($filter['check_time_end'])) {
            $sql_main .= " AND r1.check_time <= :check_time_end ";
            $sql_values[':check_time_end'] = $filter['check_time_end'] . ' 23:59:59';
        }
        //验收入库时间
        if (isset($filter['store_in_time_start']) && !empty($filter['store_in_time_start'])) {
            $sql_main .= " AND r1.receive_time >= :store_in_time_start ";
            $sql_values[':store_in_time_start'] = $filter['store_in_time_start'] . ' 00:00:00';
        }
        if (isset($filter['store_in_time_end']) && !empty($filter['store_in_time_end'])) {
            $sql_main .= " AND r1.receive_time <= :store_in_time_end ";
            $sql_values[':store_in_time_end'] = $filter['store_in_time_end'] . ' 23:59:59';
        }
        //同意退款时间
        if (isset($filter['sure_time_start']) && !empty($filter['sure_time_start'])) {
            $sql_main .= " AND r1.agreed_refund_time >= :sure_time_start ";
            $sql_values[':sure_time_start'] = $filter['sure_time_start'] . ' 00:00:00';
        }
        if (isset($filter['sure_time_end']) && !empty($filter['sure_time_end'])) {
            $sql_main .= " AND r1.agreed_refund_time <= :sure_time_end ";
            $sql_values[':sure_time_end'] = $filter['sure_time_end'] . ' 23:59:59';
        }
        //业务日期
        if (isset($filter['record_time_start']) && !empty($filter['record_time_start'])) {
            $sql_main .= " AND r1.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (isset($filter['record_time_end']) && !empty($filter['record_time_end'])) {
            $sql_main .= " AND r1.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //退单标签
        if (isset($filter['return_label_name']) && $filter['return_label_name'] !== '') {
            $tag_arr = explode(',', $filter['return_label_name']);
            $tag_record = load_model('oms/SellReturnTagModel')->get_sell_return_by_tag($tag_arr);
            if (!empty($tag_record)) {
                   $arr = explode(',',$tag_record);
                 $str = $this->arr_to_in_sql_value($arr, 'sell_return_code', $sql_values);
                $sql_main .= " AND r1.sell_return_code in ({$str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //原单下单时间
        if (isset($filter['order_time_start']) && !empty($filter['order_time_start'])) {
            $old_sql .= " AND ro.record_time >= :order_time_start ";
            $old_values[':order_time_start'] = $filter['order_time_start'] . ' 00:00:00';
        }
        if (isset($filter['order_time_end']) && !empty($filter['order_time_end'])) {
            $old_sql .= " AND ro.record_time <= :order_time_end ";
            $old_values[':order_time_end'] = $filter['order_time_end'] . ' 23:59:59';
        }
        //原单支付时间
        if (isset($filter['pay_time_start']) && !empty($filter['pay_time_start'])) {
            $old_sql .= " AND ro.pay_time >= :pay_time_start ";
            $old_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (isset($filter['pay_time_end']) && !empty($filter['pay_time_end'])) {
            $old_sql .= " AND ro.pay_time <= :pay_time_end ";
            $old_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }

        $select = 'r1.*';
        if (!empty($sub_values) || $is_sku || $is_differ) {
            $sql_main .= " AND r1.sell_return_code in ({$sql_sub})";
            $sql_values = array_merge($sub_values, $sql_values);
        }
        if (!empty($old_values)) {
            $sql_main .= " AND r1.sell_record_code in ({$old_sql})";
            $sql_values = array_merge($old_values, $sql_values);
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('r1.sale_channel_code');
        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND r1.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }

        $sql_main .= " ORDER BY sell_return_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as $key => &$value) {
            $value['return_order_status_txt'] = $this->return_order_status_exp($value, '<br/>');
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            //原单快递公司
            $value['express_code'] = oms_tb_val("oms_sell_record", 'express_code', array('sell_record_code' => $value['sell_record_code']));
            $value['express_name'] = oms_tb_val("base_express", 'express_name', array('express_code' => $value['express_code']));
            //退单快递公司
            $value['return_express_name'] = oms_tb_val("base_express", 'express_name', array('express_code' => $value['return_express_code']));
            //退单说明
            $value['return_buyer_memo'] = $value['return_buyer_memo'] == '' ? '' : $value['return_buyer_memo'];
            $ok = get_theme_url('images/ok.png');
            $no = get_theme_url('images/no.gif');

            if ($value['is_exchange_goods'] == 1) {
                $is_finish_src = $ok;
            } else {
                $is_finish_src = $no;
            }
            $value['change_record_img'] = "<img src='{$is_finish_src}'>";
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $value['return_name'] = $this->name_hidden($value['return_name']);
                $value['return_mobile'] = $this->phone_hidden($value['return_mobile']);
                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
            }
        }
        //拼装导出数据(数据格式：将退单和退换货详细列表做成多行数据)
        if (isset($filter['ctl_type']) && $filter['ctl_type'] == 'export') {
            $temp_data = $data['data'];
            unset($data['data']);
            $data['data'] = array();
            $i = 0;
            foreach ($temp_data as $key => $val) {
                $sell_return_code = $val['sell_return_code'];
                $return_details = $this->get_detail_list_by_return_code($sell_return_code);
                $is_detail = false;
                $val['return_order_status_txt'] = str_replace('<br/>', '|', $val['return_order_status_txt']);
                $val['deal_code'] = str_replace(',', '|', $val['deal_code']);
                if (!empty($return_details)) {
                    foreach ($return_details as $k => &$v) {
                        $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
                        $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $key_arr);
                        $v['goods_name'] = $sku_info['goods_name'];
                        $v['spec1_name'] = $sku_info['spec1_name'];
                        $v['spec2_name'] = $sku_info['spec2_name'];
                        $v['barcode'] = $sku_info['barcode'];
                        $v['data_type'] = 0;
                        $v['num'] = '';
                        $v['return_avg_money'] = $v['avg_money'];
                        $v['change_avg_money'] = '';
                        unset($v['deal_code']);
                        unset($v['avg_money']);
                        $data['data'][$i] = array_merge($val, $v);
                        $i++;
                        $is_detail = true;
                    }
                }
                if ($val['is_exchange_goods'] == 1) {
                    $change_detail = $this->get_change_detail_list_by_return_code($sell_return_code);
                    if (!empty($change_detail)) {
                        foreach ($change_detail as $k => &$v) {
                            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
                            $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $key_arr);
                            $v['goods_name'] = $sku_info['goods_name'];
                            $v['spec1_name'] = $sku_info['spec1_name'];
                            $v['spec2_name'] = $sku_info['spec2_name'];
                            $v['barcode'] = $sku_info['barcode'];
                            $v['data_type'] = 1;
                            $v['note_num'] = '';
                            $v['recv_num'] = '';
                            $v['return_avg_money'] = '';
                            $v['change_avg_money'] = $v['avg_money'];
                            unset($v['deal_code']);
                            unset($v['avg_money']);
                            $data['data'][$i] = array_merge($val, $v);
                            $i++;
                            $is_detail = true;
                        }
                    }
                }
                if ($is_detail === false) {
                    $data['data'][$i] = $val;
                    $i++;
                }
            }
        }
        return $this->format_ret(1, $data);
    }

    function return_order_status_exp($data, $tag = ' ') {
        $return_order_status_txt = $this->return_order_status[$data['return_order_status']];
        if ($data['return_type'] > 1) {
            $return_order_status_txt .= $tag . ($data['return_shipping_status'] == 1 ? '已收货' : '待收货');
        }
        $return_order_status_txt .= $tag . ($data['finance_check_status'] == 1 ? '已退款' : '待退款');
        return $return_order_status_txt;
    }

    /**
     * 退单详情
     * @param type $sell_return_code
     * @param type $types
     * @return type
     */
    public function component($sell_return_code, $types) {
        $info = array();

        //读取订单
        $result = $this->get_by_pk($sell_return_code);
        if ($result['status'] == 'op_no_data') {
            return $info = $this->format_ret(-1);
        } else {
            $info['record'] = $result['data'];
        }

        $sell_return_code = $info['record']['sell_return_code'];
        //取出关联订单信息
        $info['relation_record'] = load_model("oms/SellRecordModel")->get_record_by_code($info['record']['sell_record_code']);
        //取商品明细时, 读取详情数据
        $info['detail_list'] = $this->get_detail_list_by_return_code($sell_return_code);

        //售后只赔付的标识
        if (empty($info['detail_list']) && $info['record']['is_compensate'] > 0) {
            $sell_after_is_compensate = 1;
        } else {
            $sell_after_is_compensate = 0;
        }

        $response = array();
        //状态信息
        $status_info = array();
        if (in_array("status_info", $types)) {
            //下单时间
            $status_info[1] = array('time' => explode(" ", $info['record']['create_time']));
            //确认时间
            if ($info['record']['return_order_status'] == 1) {
                $status_info[2] = array('time' => explode(" ", $info['record']['confirm_time']));
            }
            $is_WMS = load_model('sys/ShopStoreModel')->is_wms_store($info['record']['store_code']);
            $response['is_wms'] = !empty($is_WMS) ? 1 : -1;
            $wms_trade = array();
            //WMS订单
            if (FALSE !== $is_WMS) {
                $ret = load_model('wms/WmsTradeModel')->check_exists_by_condition(array('record_code' => $info['record']['sell_return_code'], 'record_type' => 'sell_return'));
                if (1 == $ret['status']) {
                    $wms_trade = $ret['data'];
                }
            }
            //已上传
            if (!empty($wms_trade) && $wms_trade['upload_response_flag'] == 10) {
                $status_info[3] = array('time' => explode(" ", $wms_trade['upload_request_time']));
            }
            if ($info['record']['return_type'] != 1) {
                //收货时间
                if ($info['record']['return_shipping_status'] == 1) {
                    if (!empty($wms_trade)) {
                        $status_info[5] = array('time' => explode(" ", $info['record']['receive_time']));
                    } else {
                        $status_info[4] = array('time' => explode(" ", $info['record']['receive_time']));
                    }
                }
            }

            //退款时间
            if ($info['record']['finance_check_status'] == 1) {
                $status_info[6] = array('time' => explode(" ", $info['record']['agreed_refund_time']));
            }
            //完成时间
            if ($info['record']['return_type'] == 1) {
                if ($info['record']['finance_check_status'] == 1 && $info['record']['finsih_status'] == 1) {
                    $status_info[7] = array('time' => explode(" ", $info['record']['lastchanged']));
                }
            } else {
                if ($info['record']['finance_check_status'] == 1 && $info['record']['return_shipping_status'] == 1 && $info['record']['finsih_status'] == 1) {
                    $status_info[7] = array('time' => explode(" ", $info['record']['lastchanged']));
                }
            }

            //作废时间
            if ($info['record']['return_order_status'] == 3) {
                $response['is_invalid'] = 1;
                if ($info['record']['confirm_time'] != '0000-00-00 00:00:00') {
                    $status_info[2] = array('time' => explode(" ", $info['record']['confirm_time']));
                }
                if ($info['record']['receive_time'] != '0000-00-00 00:00:00') {
                    if (!empty($wms_trade)) {
                        $status_info[5] = array('time' => explode(" ", $info['record']['receive_time']));
                    } else {
                        $status_info[4] = array('time' => explode(" ", $info['record']['receive_time']));
                    }
                }
                if ($info['record']['agreed_refund_time'] != '0000-00-00 00:00:00') {
                    $status_info[6] = array('time' => explode(" ", $info['record']['agreed_refund_time']));
                }
                $status_info[8] = array('time' => explode(" ", $info['record']['cancel_time']));
            }

            ksort($status_info);
            $response['status_info'] = $status_info;
        }
        //基本信息
        if (in_array("baseinfo", $types)) {
            $data = $info['record'];
            //退单状态
            $data['return_order_status_txt'] = $this->return_order_status_exp($data);
            $data['sell_record_code_txt'] = "<a href=\"javascript:openPage('订单详情','?app_act=oms/sell_record/view&sell_record_code={$data['sell_record_code']}&ref=do','订单详情')\">{$data['sell_record_code']}</a> ({$info['relation_record']['deal_code_list']})";


            $data['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $info['record']['shop_code']));

            $data['sell_record_pay_code'] = $info['relation_record']['pay_code'];
            $data['buyer_name'] = $info['relation_record']['buyer_name'];
            $data['receiver_name'] = $info['relation_record']['receiver_name'];
            $data['receiver_mobile'] = $info['relation_record']['receiver_mobile'];
            $data['shipping_status'] = $info['relation_record']['shipping_status'];
            $data['receive_time'] = $data['receive_time'] == '0000-00-00 00:00:00' ? '' : $data['receive_time'];
            //关联订单支付方式  快递公司 物流单号
            $sql = "select pay_type_name from base_pay_type where pay_type_code = '{$info['relation_record']['pay_code']}'";
            $data['sell_record_pay_name'] = ctx()->db->getOne($sql);
            $sql = "select express_name from base_express where express_code = :express_code";
            $data['sell_record_express_name'] = ctx()->db->getOne($sql, array(':express_code' => $info['relation_record']['express_code']));
            $data['sell_record_express_no'] = $info['relation_record']['express_no'];
            //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';die;

            $data['return_type_txt'] = $this->return_type[$info['record']['return_type']];

            $data['is_package_out_stock_txt'] = $this->is_package_out_stock[$info['record']['is_package_out_stock']];

            //关联订单发货状态
            $data['sell_record_shipping_status_txt'] = load_model('oms/SellRecordModel')->shipping_status[$data['relation_shipping_status']] . "  " . $data['is_package_out_stock_txt'];
            $data['store_code_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $info['record']['store_code']));
            $data['sell_record_checkpay_status_name'] = ($info['record']['sell_record_checkpay_status'] == 0) ? '未确认' : '已确认';
            $data['return_pay_name'] = oms_tb_val('base_refund_type', 'refund_type_name', array('refund_type_code' => $info['record']['return_pay_code']));
            $data['return_reason_name'] = oms_tb_val('base_return_reason', 'return_reason_name', array('return_reason_code' => $info['record']['return_reason_code']));
            if ($info['record']['return_order_status'] == 0) {
                $data['type_confirm'] = 0;
            } else if ($info['record']['return_order_status'] == 1 && $info['record']['return_shipping_status'] != 1 && $info['record']['return_type'] != 1) {
                $data['type_confirm'] = 1;
            } else {
                $data['type_confirm'] = 2;
            }
            $response['baseinfo'] = $data;
            //echo '<hr/>$arr<xmp>'.var_export($data,true).'</xmp>';die;
        }
        //退单人信息
        if (in_array("return_person", $types)) {
            /*
              $data['return_name'] = $info['record']['return_name'];
              $data['return_email'] = $info['record']['return_email'];
              $data['return_address'] = $info['record']['return_address'];
              $data['return_zip_code'] = $info['record']['return_zip_code'];
              $data['return_phone'] = $info['record']['return_phone'];
              $data['return_mobile'] = $info['record']['return_mobile'];
              $data['return_express_code'] = $info['record']['return_express_code'];
              $data['return_express_no'] = $info['record']['return_express_no'];
              $data['express_code'] = $info['relation_record']['express_code'];
              $data['express_no'] = $info['relation_record']['express_no']; */
            $response['return_person'] = $info['record'];
        }
        //退单信息
        if (in_array("return_order", $types)) {
            $data['change_record'] = $info['record']['change_record'];
            $data['store_code'] = $info['record']['store_code'];
            $data['return_reason_code'] = $info['record']['return_reason_code'];
            $data['return_buyer_memo'] = $info['record']['return_buyer_memo'];
            $data['return_pay_code'] = $info['record']['return_pay_code'];
            $data['service_code'] = $info['record']['service_code'];
            $data['return_remark'] = $info['record']['return_remark'];
            $data['stock_date'] = $info['record']['stock_date'];
            $response['return_order'] = $data;
        }
        //退单金额信息
        if (in_array("return_money", $types)) {
            $data['compensate_money'] = $info['record']['compensate_money'];
            $data['seller_express_money'] = $info['record']['seller_express_money'];
            $data['adjust_money'] = $info['record']['adjust_money'];
            $data['change_avg_money'] = $info['record']['change_avg_money'];
            $data['return_avg_money'] = $info['record']['return_avg_money'];
            $data['total_return_money'] = $data['return_avg_money'] + $data['seller_express_money'] + $data['compensate_money'] + $data['adjust_money'];
            $data['should_return_money'] = $data['total_return_money'] - 0;
            $data['stock_date'] = $info['record']['stock_date'];
            $response['return_money'] = $data;
        }
        //换单基本信息
        if (in_array("change_baseinfo", $types)) {
            $data = $info['record'];
            $data['province_city_district'] = $info['record']['change_province'] . $info['record']['change_city'] . $info['record']['change_district'];
            /*
              $data['change_name'] = $info['record']$info['record'];['change_name'];
              $data['change_province'] = $info['record']['change_province'];
              $data['change_city'] = $info['record']['change_city'];
              $data['change_district'] = $info['record']['change_district'];
              $data['change_address'] = $info['record']['change_address'];
              $data['change_phone'] = $info['record']['change_phone'];
              $data['change_mobile'] = $info['record']['change_mobile'];
              $data['change_express_code'] = $info['record']['change_express_code']; */
            if (empty($data['change_store_code'])) {
                $data['change_store_code'] = $info['relation_record']['store_code'];
            }
            $response['change_baseinfo'] = $data;
            //如果换货单未处理则快递公司采用原单快递
            $response['change_baseinfo']['relation_record_express_code'] = $info['relation_record']['express_code'];
        }

        //spec1别名
        $arr = array('goods_spec1', 'goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';

        //退单商品信息
        $response['detail_list'] = array();
        if (in_array("return_goods", $types)) {
            $info['detail_list'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($info['detail_list']);
            $info['relation_detail_list'] = load_model("oms/SellRecordModel")->get_detail_by_sell_record_code($info['record']['sell_record_code']);
            foreach ($info['detail_list'] as $key => &$value) {
                if (!isset($value['relation_num'])) {
                    $value['relation_num'] = '0';
                }
                $value['__type__'] = '';
                foreach ($info['relation_detail_list'] as $k => $v) {
                    if ($value['sku'] == $v['sku']) {
                        $value['relation_num'] += $v['num'];
                        $value['is_gift'] = $v['is_gift'];
                    }
                }
                $response['detail_list'][$value['sell_return_detail_id']] = $value;
            }
        }

        //换货单商品信息
        if (in_array("change_goods", $types)) {
            $info['change_detail_list'] = $this->get_change_detail_list_by_return_code($sell_return_code);
            $info['change_detail_list'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($info['change_detail_list']);
            foreach ($info['change_detail_list'] as $key => &$value) {
                $value['__type__'] = '';
                $response['change_detail_list'][$value['sell_change_detail_id']] = $value;
            }
        }

        //取操作日志时, 读取操作日志数据
//        if (array_search('action', $types) !== false) {
//            $response['action_list'] = $this->get_action_list_by_return_code($sell_return_code);
//            foreach ($response['action_list'] as &$action) {
//                $action['return_order_status'] = $this->return_order_status[$action['return_order_status']];
//                $action['return_shipping_status'] = $this->return_shipping_status[$action['return_shipping_status']];
//                $action['finance_check_status'] = $this->finance_check_status[$action['finance_check_status']];
//            }
//        }

        $response['sell_return_code'] = $sell_return_code;
        $response['sell_after_is_compensate'] = $sell_after_is_compensate;
        //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
        return $this->format_ret(1, $response);
    }

    public function get_record_by_code($sell_return_code, $fld = '*') {
        $result = $this->db->get_row("select {$fld} from oms_sell_return where sell_return_code = :sell_return_code", array('sell_return_code' => $sell_return_code));
        return $result;
    }

    /**
     * 通过code获取明细
     * @param type $sell_return_code
     * @return type
     */
    public function get_detail_list_by_return_code($sell_return_code, $map_key = '', $add_goods_info = 0) {
        $data = $this->db->get_all("select * from oms_sell_return_detail where sell_return_code = :sell_return_code", array(':sell_return_code' => $sell_return_code));

        $result = array();
        $map_key_arr = array();
        if (!empty($map_key)) {
            $map_key_arr = explode(',', $map_key);
        }

        foreach ($data as $key => $sub_data) {
            // $sub_data['barcode'] =  load_model('goods/SkuCModel')->get_barcode($sub_data['sku']);
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);

            $sub_data = array_merge($sub_data, $sku_info);
            $data[$key] = $sub_data;
            if (!empty($map_key_arr)) {
                $ks = '';
                foreach ($map_key_arr as $_k) {
                    $ks .= $sub_data[$_k] . ',';
                }
                $result[substr($ks, 0, -1)] = $sub_data;
            }
        }



        if (empty($map_key_arr)) {
            $result = &$data;
        }


        return $result;
    }

    public function get_return_by_return_code($sell_return_code) {
        $data = ctx()->db->get_row("select * from oms_sell_return where sell_return_code = :sell_return_code", array(':sell_return_code' => $sell_return_code));

        return $data;
    }

    public function get_action_list_by_return_code($sell_return_code) {
        $data = ctx()->db->get_all("select * from oms_sell_return_action where sell_return_code = :sell_return_code order by sell_return_action_id desc", array(':sell_return_code' => $sell_return_code));
        return $data;
    }

    /**
     * 更新明细
     * @param type $data
     * @param type $where
     * @return type
     */
    public function update_detail($data, $where) {
        $data = $this->db->create_mapper($this->detail_table)->update($data, $where);
        if ($data) {
            return $this->format_ret("1", $data, 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    /**
     * 删除退单明细
     * @param type $where
     * @return type
     */
    public function delete_detail($where) {
        $row = $this->get_detail_by_detail_id($where['sell_return_detail_id']);
        $data = $this->db->create_mapper($this->detail_table)->delete($where);
        if ($data) {
            load_model("oms/SellReturnOptModel")->reflush_return_info($row['sell_return_code']);
            return $this->format_ret("1", $data, 'delete_success');
        } else {
            return $this->format_ret("-1", '', 'delete_error');
        }
    }

    /**
     * 删除换货单明细
     * @param type $where
     * @return type
     */
    public function delete_change_detail($where) {

        $record1 = $this->db->get_row("select * from oms_sell_change_detail where sell_change_detail_id = :sell_change_detail_id", array(':sell_change_detail_id' => $where['sell_change_detail_id']));
        if (empty($record1)) {
            return $this->format_ret("-1", '', '明细不存在');
        }

        $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($record1['sell_return_code']);





        $record_update = array('change_avg_money' => $return_record['change_avg_money'] - $record1['avg_money']);


        $this->begin_trans();
        //  if($return_record['return_order_status']==1){
        $sql = "select * from oms_sell_record_lof where record_type=3 AND sku=:sku AND record_code=:record_code";
        $detail_lof = $this->db->get_row($sql, array(':sku' => $record1['sku'], 'record_code' => $record1['sell_return_code']));


        if (!empty($detail_lof)) {
            $store_code = $detail_lof['store_code'];
            require_model('prm/InvOpModel');
            $invobj = new InvOpModel($return_record['sell_return_code'], 'oms_change', $store_code, 0, array($detail_lof));
            $ret = $invobj->adjust();
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
        }
        //  }


        $data = $this->db->create_mapper('oms_sell_change_detail')->delete($where);

        $this->update($record_update, array('sell_return_code' => $record1['sell_return_code']));

        //查询退单有无换货商品，如果没有就修改是否换货状态
        $sql = "SELECT * FROM oms_sell_change_detail WHERE sell_return_code = '{$return_record['sell_return_code']}';";
        $ret = $this->db->get_row($sql);
        if (empty($ret)) {
            $this->db->update('oms_sell_return', array('is_exchange_goods' => '0'), array('sell_return_code' => $return_record['sell_return_code']));
        }


        if ($data) {
            $this->add_action($return_record, "换货明细删除", "(sku:{$record1['sku']},数量:{$record1['num']})");
            $this->commit();
            return $this->format_ret("1", $data, 'delete_success');
        } else {
            $this->rollback();
            return $this->format_ret("-1", '', 'delete_error');
        }
    }

    /**
     * 写入退单日志
     * @param $sellReturnCode
     * @param $actionName
     * @param string $actionNote
     * @param bool $isDeamon
     * @throws Exception
     */
    function add_action($record, $actionName, $actionNote = '', $isDeamon = false) {
        $log = array();
        $log['sell_return_code'] = $record['sell_return_code'];
        $log['return_order_status'] = $record['return_order_status'];
        $log['return_shipping_status'] = $record['return_shipping_status'];
        $log['finance_check_status'] = $record['finance_check_status'];
        $log['action_name'] = $actionName;
        $log['action_note'] = $actionNote;

        if ($isDeamon == false && CTX()->app['mode'] == 'cli') {
            $isDeamon = true;
        }

        if ($isDeamon) {
            $log['user_code'] = '计划任务';
            $log['user_name'] = '计划任务';
        } else {
            $log['user_code'] = CTx()->get_session('user_code');
            $log['user_name'] = CTx()->get_session('user_name');
        }
        if (isset($this->return_log_check) && $this->return_log_check != 0) {
            if (empty($log['user_code']) || empty($log['user_name'])) {
                return $this->format_ret(-1, '', '用户登录数据错误');
            }
        } else {
            $log['user_code'] = 'admin';
            $log['user_name'] = '系统管理员';
        }
        $ret = M('oms_sell_return_action')->insert($log);

        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '保存日志出错');
        }
        return $this->format_ret(1);
    }

    /**
     * 通过code获取明细
     * @param type $sell_return_code
     * @return type
     */
    public function get_change_detail_list_by_return_code($sell_return_code, $map_key = '') {
        $data = $this->db->get_all("select * from oms_sell_change_detail where sell_return_code = :sell_return_code", array(':sell_return_code' => $sell_return_code));
        if (!empty($map_key)) {
            $result = array();
            $map_key_arr = explode(',', $map_key);
            foreach ($data as $sub_data) {
                $ks = '';
                foreach ($map_key_arr as $_k) {
                    $ks .= $sub_data[$_k] . ',';
                }
                $result[substr($ks, 0, -1)] = $sub_data;
            }
        } else {
            $result = &$data;
        }
        return $result;
    }

    function add_return_goods($request) {
        $sell_return_code = $request['sell_return_code'];
        $deal_code = $request['deal_code'];
        $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($sell_return_code);
        if (empty($return_record)) {
            return $this->format_ret(-1, '', $sell_return_code . ' 退单不存在');
        }
        $log = '';
        $sku_arr = array();
        foreach ($request['data'] as $k => $v) {
            if (!is_array($v) || empty($v['num']) || $v['num'] < 0)
                continue;
            $sku_arr[$v['sku']] = $v['num'];
            $log .= "(sku:{$v['sku']},数量:{$v['num']})";
        }
        if (empty($sku_arr)) {
            return $this->format_ret(-1, '', '没有找到添加的明细');
        }
        $sku_list = "'" . join("','", array_keys($sku_arr)) . "'";
        $sql = "SELECT
					g.goods_code,
					g.sell_price,
					g.cost_price,
					s.sku
				FROM
					goods_sku s
				LEFT JOIN base_goods g ON s.goods_code = g.goods_code
				WHERE
					s.sku IN ($sku_list)";
        //echo $sql;
        $db_goods = ctx()->db->get_all($sql);
        $goods = array();
        foreach ($db_goods as $sub_db) {
            $goods[$sub_db['sku']] = $sub_db;
        }

        $ins_arr = array();
        foreach ($sku_arr as $sku => $sl) {
            if (!isset($goods[$sku])) {
                return $this->format_ret(-1, '', 'sku ' . $sku . ' 在系统中不存在');
            }
            $find_row = $goods[$sku];
            if (!isset($find_row['goods_code'])) {
                return $this->format_ret(-1, '', 'sku ' . $sku . ' 对应商品信息在系统中不存在');
            }
            $avg_money = $sl * (float) @$find_row['sell_price'];
            $ins_arr1 = array(
                'sell_return_code' => $sell_return_code,
                'sell_record_code' => $return_record['sell_record_code'],
                'deal_code' => $deal_code,
                'goods_code' => $find_row['goods_code'],
                'sku' => $find_row['sku'],
                //      'barcode' => $find_row['barcode'],
                'goods_price' => (float) @$find_row['sell_price'],
                'note_num' => $sl,
                'recv_num' => 0,
                'avg_money' => $avg_money
            );
            if($return_record['is_fenxiao'] == 1) { //淘分销退单
                $ins_arr1['fx_amount'] = $avg_money;
                $ins_arr1['trade_price'] = $avg_money / $sl;
            } else if($return_record['is_fenxiao'] == 2) { //普通分销退单
                $data = load_model('fx/GoodsManageModel')->get_record_fx_money($return_record['sell_record_code'],$find_row,$return_record['fenxiao_code'],$sl,$return_record['create_time']);
                $ins_arr1['fx_amount'] = $data['fx_amount'];
                $ins_arr1['trade_price'] = $data['trade_price'];
            }
            $ins_arr[] = $ins_arr1;
        }
        $update_str = "note_num = VALUES(note_num),recv_num = VALUES(recv_num)";
        //echo '<hr/>$ins_arr<xmp>'.var_export($ins_arr,true).'</xmp>';die;

        $this->begin_trans();
        try {
            $ret = M('oms_sell_return_detail')->insert_multi_duplicate('oms_sell_return_detail', $ins_arr, $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '保存明细出错');
            }
            $this->add_action($return_record, "新增退单明细", $log);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        load_model("oms/SellReturnOptModel")->reflush_return_info($sell_return_code);
        return $this->format_ret(1);
    }

    function add_change_goods($request) {
        $sell_return_code = $request['sell_return_code'];
        $deal_code = $request['deal_code'];
        $return_record = load_model('oms/SellReturnModel')->get_return_by_return_code($sell_return_code);
        if (empty($return_record)) {
            return $this->format_ret(-1, '', $sell_return_code . ' 退单不存在');
        }
        $log = '';
        $sku_arr = array();
        $sku_money_arr = array();       
        foreach ($request['data'] as $k => $v) {
            if (!is_array($v) || empty($v['num']) || $v['num'] < 0)
                continue;
            $sku_arr[$v['sku']] = $v['num'];
            $sku_money_arr[$v['sku']]['avg_money'] = $v['avg_money'];
            $sku_money_arr[$v['sku']]['fx_amount'] = $v['fx_amount'];
            $log .= "(sku:{$v['sku']},数量:{$v['num']})";
        } 
        if (empty($sku_arr)) {
            return $this->format_ret(-1, '', '没有找到添加的明细');
        }
        $sku_list = "'" . join("','", array_keys($sku_arr)) . "'";
        $sql = "SELECT
                    g.goods_code,
                    g.sell_price,
                    g.cost_price,
                    s.sku
                FROM
                    goods_sku s
                LEFT JOIN base_goods g ON s.goods_code = g.goods_code
                WHERE
                    s.sku IN ($sku_list)";
        $db_goods = ctx()->db->get_all($sql);
        $goods = array();
        foreach ($db_goods as $sub_db) {
            $goods[$sub_db['sku']] = $sub_db;
        }
        $ins_arr = array();
        foreach ($sku_arr as $sku => $sl) {
            if (!isset($goods[$sku])) {
                return $this->format_ret(-1, '', 'sku ' . $sku . ' 在系统中不存在');
            }
            $find_row = $goods[$sku];
            if (!isset($find_row['goods_code'])) {
                return $this->format_ret(-1, '', 'sku ' . $sku . ' 对应商品信息在系统中不存在');
            }
            if (isset($sku_money_arr[$sku])) {//换货单改款保留金额
                $avg_money = $sku_money_arr[$sku]['avg_money'];
                $fx_amount = $sku_money_arr[$sku]['fx_amount'];
            } else {
                $avg_money = $sl * (float) @$find_row['sell_price'];
                $fx_amount = 0;
            }
            $ins_arr[] = array(
                'sell_return_code' => $sell_return_code,
                'sell_record_code' => $return_record['sell_record_code'],
                'deal_code' => $deal_code,
                'goods_code' => $find_row['goods_code'],
                'sku' => $find_row['sku'],
                'goods_price' => (float) @$find_row['sell_price'],
                'num' => $sl,
                'avg_money' => $avg_money,
                'fx_amount' => $fx_amount,
            );
        }
        $update_str = "num = VALUES(num)";
        $this->begin_trans();
        try {
            $ret = M('oms_sell_change_detail')->insert_multi_duplicate('oms_sell_change_detail', $ins_arr, $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '保存明细出错');
            }
            $ret = $this->lock_oms_change_sku($return_record);
            //润米调用接口
            $kh_id = CTX()->saas->get_saas_key();
            if ($kh_id == 2289) {
                $return_detail = $this->get_detail_list_by_return_code($sell_return_code);
                foreach ($ins_arr as $val) {
                    $barcode = load_model('goods/SkuCModel')->get_barcode($val['sku']);
                    $sql = "SELECT goods_from_id FROM api_goods_sku WHERE goods_barcode=:barcode";
                    $data = $this->db->get_row($sql, array(':barcode' => $barcode));
                    foreach ($return_detail as $detail) {
                        $params = array();
                        $params['new_barcode'] = $barcode;
                        $params['barcode'] = $detail['barcode'];
                        $params['deal_code'] = $val['deal_code'];
                        $params['goods_from_id'] = $data['goods_from_id'];
                        $result = load_model('sys/EfastApiModel')->request_api('xiaomi/refund_change_sync', $params);
                    }
                }
            }
            $this->add_action($return_record, "新增换货明细", $log);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);
    }

    function lock_oms_change_sku($return_record) {
        $sql = "select sku,num from oms_sell_record_lof where record_type=3 AND record_code=:record_code";
        $sql_values = array(':record_code' => $return_record['sell_return_code']);
        $lof_detail = $this->db->get_all($sql, $sql_values);
        $lof_sku_data = array();
        if (!empty($lof_detail)) {
            foreach ($lof_detail as $_lof_val) {
                $lof_sku_data[$_lof_val['sku']]['num'] = isset($lof_sku_data[$_lof_val['sku']]) ? $lof_sku_data[$_lof_val['sku']]['mum'] + $_lof_val['num'] : $_lof_val['num'];
            }
        }

        $sql = "select * from oms_sell_change_detail where sell_return_code=:record_code";
        $sku_detail = $this->db->get_all($sql, $sql_values);
        $sell_reocrd_info = load_model('oms/SellRecordModel')->get_record_by_code($return_record['sell_record_code'], 'store_code');

        if (empty($return_record['change_store_code'])) {
            $store_code = $sell_reocrd_info['store_code'];
        } else {
            $store_code = $return_record['change_store_code'];
        }
        foreach ($sku_detail as &$val) {
            if (isset($lof_sku_data[$val['sku']])) {
                $val['num'] -= $lof_sku_data[$val['sku']]['num'];
            }
            $val['store_code'] = $store_code;
            $val['sell_record_code'] = $return_record['sell_return_code'];
        }
        $invobj = new InvOpModel($return_record['sell_return_code'], 'oms_change', $store_code, 1, $sku_detail);

        $ret = $invobj->adjust();
        return $ret;
    }

    function get_detail_by_detail_id($id) {
        $sql = "select * from {$this->detail_table} where sell_return_detail_id=:sell_return_detail_id";
        return $this->db->get_row($sql, array(":sell_return_detail_id" => $id));
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

    //获取入库状态
    function getReturnShippingStatus() {
        $shipping_status = $this->is_shipping_status;
        $new_shipping_status = array();
        $i = 0;
        foreach ($shipping_status as $key => $status) {
            $new_shipping_status[$i]['shipping_code'] = $key;
            $new_shipping_status[$i]['shipping_name'] = $status;
            $i++;
        }
        return $new_shipping_status;
    }

    /**
     *
     * 方法名                               api_order_return_list_get
     *
     * 功能描述                           获取已发货订单信息
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  可选: 'page', 'page_size',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_return_list_get($param) {
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'receive_time_start', 'receive_time_end'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);

        //开放字段
        $select = '
            `sell_return_code`, `sell_record_code`, `store_code`, `shop_code`, `customer_code`, `buyer_name`, `return_name`,
            `return_country`, `return_province`, `return_city`, `return_district`, `return_street`, `return_address`,
            `return_addr`, `return_zip_code`, `return_mobile`, `return_phone`, `return_email`, `return_express_code`, `return_express_no`,
            `refund_total_fee`, `create_time`, `receive_time`, `return_reason_code`, `return_buyer_memo` , `buyer_express_money` , `seller_express_money`
        ';


        //查询SQL
        $sql_main = "FROM {$this->table} sr WHERE (sr.return_type=:return_type OR sr.return_type=:return_type1) AND sr.return_order_status=:return_order_status AND sr.return_shipping_status=:return_shipping_status";
        //绑定数据
        $sql_values = array(':return_type' => 2, ':return_type1' => 3, ':return_order_status' => 1, ':return_shipping_status' => 1);
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'receive_time_start') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.receive_time>=:{$key}";
                } else if ($key == 'receive_time_end') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.receive_time<=:{$key}";
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
                //提取订单明细
                $order_detail = $this->get_detail_list_by_return_code($order['sell_return_code']);

                //检测是否为空
                if (empty($order_detail)) {
                    $order_list[$key]['detail_list'] = array();
                } else {
                    //不开放字段
                    $del_key = array(
                        'sell_return_detail_id', 'sell_record_code', 'deal_code', 'sub_deal_code', 'sku_id', 'sku', 'barcode',
                        'note_num', 'lastchanged'
                    );

                    //获取产品名称、规格1和2的名称 | 剔除不开放字段
                    foreach ($order_detail as $k => &$value) {
                        //     $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
                        //     $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
                        //    $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));

                        $key_arr = array('goods_name', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name');

                        $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);

                        $value = array_merge($value, $sku_info);


                        foreach ($del_key as $v) {
                            if (array_key_exists($v, $value)) {
                                unset($value[$v]);
                            }
                        }
                    }

                    //将订单详细信息压入订单数组中
                    $order_list[$key]['detail_list'] = $order_detail;
                    unset($order_detail);
                }
            }

            //返回数据给请求方
            return $this->format_ret(1, $ret);
        } else {
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
    }

    /**
     *
     * 方法名                               api_order_return_list
     *
     * 功能描述                           退单查询
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  可选: 'page', 'page_size',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_return_list($param) {
        require_lib('comm_util', true);
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'store_code', 'return_type', 'return_order_status', 'return_shipping_status', 'start_time', 'end_time', 'start_confirm_time', 'end_confirm_time', 'start_refund_time', 'end_refund_time', 'shop_code', 'sell_return_code', 'refund_id', 'deal_code', 'start_lastchanged', 'end_lastchanged')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);

        //开放字段
        $select = '
            sr.`sell_return_code`, sr.`sell_record_code`, sr.`refund_id`,sr.`deal_code`, sr.`return_order_status`, sr.`return_type`,tg.`tag_v` AS return_tag, tg.`tag_desc`, sr.`return_shipping_status`, sr.`store_code`, sr.`shop_code`, sr.`customer_code`, sr.`buyer_name`, sr.`return_name`,sr.`return_country`, sr.`return_province`, sr.`return_city`, sr.`return_district`, sr.`return_street`, sr.`return_address`, sr.`return_addr`, sr.`return_zip_code`, sr.`return_mobile`, sr.`return_phone`, sr.`return_email`, sr.`return_express_code`, sr.`return_express_no`,sr.`refund_total_fee`,(sr.`refund_total_fee`+sr.`compensate_money`+sr.`adjust_money`+sr.`seller_express_money`) AS refund_fee,sr.`return_avg_money`,sr.`compensate_money`,sr.`adjust_money`,sr.`seller_express_money`, sr.`create_time`, sr.`receive_time`, sr.`confirm_time`, rr.`return_reason_name`,sr.`return_buyer_memo` , sr.`buyer_express_money` , sr.`seller_express_money`,sr.`lastchanged`';

        //查询SQL
        $sql_main = "FROM {$this->table} sr LEFT JOIN base_return_reason rr ON sr.return_reason_code=rr.return_reason_code LEFT JOIN oms_sell_return_tag tg ON sr.sell_return_code=tg.sell_return_code WHERE 1=1 ";

        if ((!isset($arr_deal['return_type']) || (empty($arr_deal['return_type']) && $arr_deal['return_type'] != 0)) || (isset($arr_deal['return_type']) && !empty($arr_deal['return_type'] && $arr_deal['return_type'] == 1))) {
            $sql_main .= ' AND (sr.return_type = 2 OR sr.return_type = 3)';
            unset($arr_deal['return_type']);
        }
        if (isset($arr_deal['return_type']) && $arr_deal['return_type'] == 0) {
            $arr_deal['return_type'] = 1;
        }
        if (isset($arr_deal['sell_return_code']) && !empty($arr_deal['sell_return_code'])) {
            $sql_main .= ' AND sr.sell_return_code = :sell_return_code';
            $sql_values[':sell_return_code'] = $arr_deal['sell_return_code'];
        } else if (isset($arr_deal['refund_id']) && !empty($arr_deal['refund_id'])) {
            $sql_main .= ' AND sr.refund_id = :refund_id';
            $sql_values[':refund_id'] = $arr_deal['refund_id'];
        } else if (isset($arr_deal['deal_code']) && !empty($arr_deal['deal_code'])) {
            $sql_main .= ' AND sr.deal_code = :deal_code';
            $sql_values[':deal_code'] = $arr_deal['deal_code'];
        } else {
            $this->create_sql_where($arr_deal, $sql_values, $sql_main);
            $this->create_default_time($arr_deal, $sql_main, $sql_values);
        }
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
        if (count($ret['data']) > 0) {
            $order_list = &$ret['data'];
            foreach ($order_list as $key => &$order) {
                deal_special_char($order);
                //提取订单明细
                $order_detail = $this->get_detail_list_by_return_code($order['sell_return_code']);

                //检测是否为空
                if (empty($order_detail)) {
                    $order['detail_list'] = array();
                } else {
                    deal_special_char($order_detail);
                    //不开放字段
                    $del_key = array(
                        'sell_return_detail_id', 'sell_record_code', 'deal_code', 'sub_deal_code', 'sku_id', 'sku', 'lastchanged'
                    );

                    //获取产品名称、规格1和2的名称 | 剔除不开放字段
                    foreach ($order_detail as $k => &$value) {
                        $key_arr = array('goods_name', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name');

                        $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);

                        $value = array_merge($value, $sku_info);
                        foreach ($del_key as $v) {
                            if (array_key_exists($v, $value)) {
                                unset($value[$v]);
                            }
                        }
                    }

                    //将订单详细信息压入订单数组中
                    $order['detail_list'] = $order_detail;
                    unset($order_detail);
                }
            }

            //返回数据给请求方
            return $this->format_ret(1, $ret);
        } else {
            return $this->format_ret(-10002, '', 'API_RETURN_MESSAGE_10002');
        }
    }

    /**
     * @todo API-根据入参创建sql条件语句
     */
    private function create_sql_where($arr_deal, &$sql_values, &$sql_main) {
        //时间字段映射关系
        $time_fld = array(
            'start_time' => 'create_time',
            'end_time' => 'create_time',
            'end_confirm_time' => 'confirm_time',
            'start_confirm_time' => 'confirm_time',
            'start_refund_time' => 'agreed_refund_time',
            'end_refund_time' => 'agreed_refund_time',
            'start_lastchanged' => 'lastchanged',
            'end_lastchanged' => 'lastchanged',
        );
        foreach ($arr_deal as $key => $val) {
            if ($key == 'page' || $key == 'page_size') {
                continue;
            }
            if (!array_key_exists($key, $time_fld)) {
                $sql_main .= " AND sr.{$key}=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'start_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}>=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'end_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}<=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
        }
    }

    /**
     * @todo API-退单查询接口时间处理
     * @param array $arr_deal
     * @param string $sql_main
     * @param string $sql_values
     */
    private function create_default_time($arr_deal, &$sql_main, &$sql_values) {
        $start_time = date("Y-m-d H:i:s", strtotime("today"));
        $end_time = date("Y-m-d H:i:s", strtotime("today +1 days -1 seconds"));
        $time_arr = array(
            'create_time' => array('start_time', 'end_time'),
            'confirm_time' => array('start_confirm_time', 'end_confirm_time'),
            'agreed_refund_time' => array('start_refund_time', 'end_refund_time'),
            'lastchanged' => array('start_lastchanged', 'end_lastchanged'),
        );

        $flag = 1; //time_arr时间全部为空标识
        foreach ($time_arr as $key => $val) {
            if (!isset($arr_deal[$val[0]]) && !isset($arr_deal[$val[1]])) {
                continue;
            }
            $flag = 0;
            if (!isset($arr_deal[$val[0]])) {
                $sql_main .= " AND sr.{$key} >= :{$val[0]}";
                $sql_values[":{$val[0]}"] = $start_time;
            }
            if (!isset($arr_deal[$val[1]])) {
                $sql_main .= " AND sr.{$key} <= :{$val[1]}";
                $sql_values[":{$val[1]}"] = $end_time;
            }
        }

        if ($flag == 1) {
            if (!isset($arr_deal['start_time'])) {
                $sql_main .= " AND sr.create_time >= :start_time";
                $sql_values[":start_time"] = $start_time;
            }
            if (!isset($arr_deal['end_time'])) {
                $sql_main .= " AND sr.create_time <= :end_time";
                $sql_values[":end_time"] = $end_time;
            }
        }
    }

    /**
     *
     * 方法名                               api_order_return_detail_get
     *
     * 功能描述                           获取已收货退货订单明细
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  必选: 'sell_return_code',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_return_detail_get($param) {
        $key_required = array(
            's' => array('sell_return_code')
        );
        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            //合并数据
            $arr_deal = $arr_required;
            //清空无用数据
            unset($arr_required);
            unset($param);

            //提取订单明细
            $ret = $this->get_detail_list_by_return_code($arr_deal['sell_return_code']);
            if (empty($ret)) {
                return $this->format_ret("-10002", $param, "API_RETURN_MESSAGE_10002");
            } else {
                $del_key = array(
                    'sell_return_detail_id', 'sell_record_code', 'deal_code', 'sub_deal_code', 'sku_id', 'sku', 'barcode',
                    'note_num', 'lastchanged'
                );
                foreach ($ret as $key => &$value) {
//                    $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
//                    $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
//                    $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
                    foreach ($del_key as $v) {
                        if (array_key_exists($v, $value)) {
                            unset($value[$v]);
                        }
                    }
                }
                return $this->format_ret("1", $ret, "API_RETURN_MESSAGE_10003");
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
        return $arr_required;
    }

    /**
     *
     * 方法名       get_return_record_confirm_list
     *
     * 功能描述     已'确认收货'的退款退货单，3天后，系统将自动确认退款
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     */
    public function get_return_record_confirm_list($three_days_ago_time) {
        $sql = "SELECT sell_return_code FROM {$this->table} WHERE receive_time<:receive_time AND return_type=3 AND return_shipping_status=1";
        $data = $this->db->get_all($sql, array(':receive_time' => date('Y-m-d H:i:s', $three_days_ago_time)));
        return $data;
    }

    public function scan_barcode($sell_return_code, $barcode, $param = array(), $return_package_code) {
        $unique_code = '';
        $record = $this->get_return_by_return_code($sell_return_code);
        $order_detail = $this->get_detail_list_by_return_code($sell_return_code);
        $sysuser = isset($param['recv_num']) ? 'open_api' : '';
        $check = load_model("oms/SellReturnOptModel")->opt_return_shipping_check($record, $order_detail, 0, $sysuser);
        if ($check['status'] != '1') {
            return $check;
        }
        $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
        if ($unique_arr['unique_status'] == 1) {
            $sell_record_code = $record['sell_record_code'];
            $sql = "select * from goods_unique_code_log where record_code = :sell_record_code and record_type = 'sell_record'";
            $unique_log_arr = $this->db->get_all($sql, array(':sell_record_code' => $sell_record_code));
            $unique_lof = array();
            if (!empty($unique_log_arr)) {
                foreach ($unique_log_arr as $key => $val) {
                    $unique_log[$val['unique_code']] = $val;
                }
                if (isset($unique_log[$barcode])) {
                    if ($unique_log[$barcode]['is_scan'] == 1) {
                        return $this->format_ret("-1", '', "唯一码已扫描");
                    } else {
                        $sql = "UPDATE goods_unique_code_log SET is_scan = '1' WHERE record_type = 'sell_record' AND record_code = :sell_record_code AND unique_code = :unique_code";
                        $v = $this->db->query($sql, array(':sell_record_code' => $unique_log[$barcode]['record_code'], ':unique_code' => $barcode));
                        $unique_code = $barcode;
                        $barcode = $unique_log[$barcode]['barcode'];
                    }
                }
//                else{
//                    return $this->format_ret("-1", '', "唯一码不存在");
//                }
            }
        }
        $sql = "select gb.barcode from goods_barcode_child gbc left join goods_sku gb on gb.sku = gbc.sku where gbc.barcode = :barcode";
        $child_barcode = $this->db->get_row($sql, array(':barcode' => $barcode));
        if (!empty($child_barcode)) {
            $barcode = $child_barcode['barcode'];
        } else {
            $barcode_rule_ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($barcode, 1);
            if ($barcode_rule_ret['status'] > 0) {
                $barcode = $barcode_rule_ret['data']['barcode'];
            }
        }

        $sku = $this->db->get_value("select sku from goods_sku where barcode='{$barcode}'");
        if (empty($sku)) {
            return $this->format_ret("-1", '', "订单明细中不存在扫描的条码");
        }
        $sql_detail = "select * from oms_sell_return_detail where sell_return_code = :sell_return_code and sku = :sku";
        $ret = $this->db->get_row($sql_detail, array(':sell_return_code' => $sell_return_code, ':sku' => $sku));
        //查询退货包裹单是否有明细
        $sql_package_detail = "SELECT * FROM oms_return_package_detail WHERE return_package_code = :return_package_code AND sku = :sku;";
        $package_ret = $this->db->get_row($sql_package_detail, array(':return_package_code' => $return_package_code, ':sku' => $sku));

        $recv_num = isset($param['recv_num']) ? $param['recv_num'] : 1;
        $return_code = !empty($return_package_code) ? $return_package_code : $record['sell_return_package_code'];
        if (empty($ret) && empty($package_ret)) {
            //插入操作
            $insert_detail_ret = $this->insert_detail($record, $sku, $recv_num);
            load_model('oms/SellRecordLofModel')->update_num_by_code_sku($return_code, $sku, $recv_num);
            //添加操作日志
            $action_note = '商品入库：barcode:' . $barcode;
            load_model('oms/ReturnPackageModel')->insert_package_log_action($return_code, '商品入库', $action_note);
            return $insert_detail_ret;
        } else {
            $data = array();
            $data['recv_num'] = (int) $package_ret['num'] + 1;
            $ret = load_model('oms/ReturnPackageModel')->update_detail_num($sell_return_code, $sku, $data['recv_num']);
            load_model('oms/SellRecordLofModel')->create_num_by_code_sku($return_code, $sku, $data['recv_num']);
//            $ret = $this->update_detail($data, array('sell_return_code' => $sell_return_code, 'sku' => $sku));
            if ($ret['status'] == 1) {
                //添加操作日志
                $action_note = '商品入库：barcode:' . $barcode;
                $ret = load_model('oms/ReturnPackageModel')->insert_package_log_action($return_code, '商品入库', $action_note);
                if (isset($unique_code) && $unique_code != '') {
                    $data = array();
                    $data['sell_record_code'] = $sell_return_code;
                    $data['unique_code'] = $unique_code;
                    $data['barcode_type'] = 'unique_code';
                    load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($data);
                    return $this->format_ret("2", $barcode, "");
                }
                return $this->format_ret("1", $barcode, "");
            } else {
                return $ret;
            }
        }
    }

    /**
     * @todo 未关联退单号是条码扫描
     */
    function scan_barcode_no_return_code($params) {
        //查询退货包裹单主表数据
        $sql = "SELECT * FROM oms_return_package WHERE return_package_code = '{$params['return_package_code']}'";
        $record = $this->db->get_row($sql);
        if ($record['return_order_status']) {
            return $this->format_ret('-1', '', '已收货包裹单不能操作');
        }

        $sql = "select gb.barcode from goods_barcode_child gbc left join goods_sku gb on gb.sku = gbc.sku where gbc.barcode = :barcode";
        $child_barcode = $this->db->get_row($sql, array(':barcode' => $params['scan_barcode']));
        if (!empty($child_barcode)) {
            $barcode = $child_barcode['barcode'];
        } else {
            $barcode_rule_ret = load_model('prm/GoodsBarcodeIdentifyRuleModel')->yanzheng($params['scan_barcode'], 1);
            if ($barcode_rule_ret['status'] > 0) {
                $barcode = $barcode_rule_ret['data']['barcode'];
            }
        }
        $sku = $this->db->get_value("select sku from goods_sku where barcode='{$params['scan_barcode']}'");
        if (empty($sku)) {
            return $this->format_ret("-1", '', "订单明细中不存在扫描的条码");
        }
        //查询退货包裹单是否有明细
        $sql_package_detail = "SELECT * FROM oms_return_package_detail WHERE return_package_code = :return_package_code AND sku = :sku;";
        $package_ret = $this->db->get_all($sql_package_detail, array(':return_package_code' => $params['return_package_code'], ':sku' => $sku));
        $this->begin_trans();
        //没有明细就新增
        if (empty($package_ret)) {
            $num = 1;
            //插入操作
            $insert_detail_ret = $this->no_return_code_insert_detail($params['return_package_code'], $sku, $num);
            load_model('oms/SellRecordLofModel')->update_num_by_code_sku($params['return_package_code'], $sku, $num);
            //添加操作日志
            $action_note = '商品入库：barcode:' . $barcode;
            load_model('oms/ReturnPackageModel')->insert_package_log_action($params['return_package_code'], '商品入库', $action_note);
            $this->commit();
            return $insert_detail_ret;
        } else {
            $barcode = !empty($barcode) ? $barcode : $params['scan_barcode'];
            $num = $this->db->get_value("select num from oms_return_package_detail where sku='{$sku}' AND return_package_code = '{$params['return_package_code']}'");
            $num += 1;
            load_model('oms/SellRecordLofModel')->update_num_by_code_sku($params['return_package_code'], $sku, $num);
            $sql = "UPDATE oms_return_package_detail SET num=num+1 WHERE barcode=:barcode AND return_package_code=:return_package_code";
            $values = array(':barcode' => $barcode, ':return_package_code' => $params['return_package_code']);
            $this->query($sql, $values);
            $ret = $this->affected_rows();
            if ($ret != 1) {
                $this->rollback();
                return $this->format_ret(-1);
            }
            //添加操作日志
            $action_note = '商品入库：barcode:' . $barcode;
            $ret = load_model('oms/ReturnPackageModel')->insert_package_log_action($params['return_package_code'], '商品入库', $action_note);
            $this->commit();
            return $this->format_ret(1, $barcode, "");
        }
    }

    //没关联退单号添加明细
    function no_return_code_insert_detail($return_package_code, $sku, $recv_num = 1) {
        $key_arr = array('goods_code', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'sell_price', 'sku_id');
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
        $this->begin_trans();
        try {
            //添加包裹单明细
            $pack_sql = "select store_code from oms_return_package where return_package_code =:return_package_code and return_order_status != 2";
            $pack_row = ctx()->db->get_row($pack_sql, array(':return_package_code' => $return_package_code));
            if (!empty($pack_row)) {
                $package_detail = array();
                $package_detail['sku'] = $sku;
                $package_detail['num'] = $recv_num;
                $package_detail['apply_num'] = 0;
                $package_detail['return_package_code'] = $return_package_code;
                $package_detail['goods_code'] = $sku_info['goods_code'];
                $package_detail['spec1_code'] = $sku_info['spec1_code'];
                $package_detail['spec2_code'] = $sku_info['spec2_code'];
                $package_detail['barcode'] = $sku_info['barcode'];

                $this->insert_exp('oms_return_package_detail', $package_detail);
                $package_ret = $this->affected_rows();
                if ($package_ret != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '保存退货包裹单明细出错');
                }
                $lof_moren = load_model('prm/GoodsLofModel')->is_exists('1', 'type');
                $lof_arr = array();
                $lof_arr[0] = array(
                    'record_type' => 2,
                    'record_code' => $return_package_code,
                    'store_code' => $pack_row['store_code'],
                    'goods_code' => $sku_info['goods_code'],
                    'spec1_code' => $sku_info['spec1_code'],
                    'spec2_code' => $sku_info['spec2_code'],
                    'sku' => $sku,
                    'barcode' => $sku_info['barcode'],
                    'lof_no' => $lof_moren['data']['lof_no'],
                    'production_date' => $lof_moren['data']['production_date'],
                    'num' => $recv_num,
                    'stock_date' => date('Y-m-d'),
                    'occupy_type' => 0,
                    'create_time' => time()
                );
                $lof_update_str = "num = VALUES(num)";
                M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof', $lof_arr, $lof_update_str);
                $lof_package_ret = $this->affected_rows();
                if ($lof_package_ret != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '保存退货包裹单默认批次出错');
                }
                $this->commit();
                $sku_info['num'] = $recv_num;
                $sku_info['sku'] = $sku;
                $sku_info['apply_num'] = 0;
                return $this->format_ret(3, $sku_info);
            } else {
                $this->rollback();
                return $this->format_ret(-1, '', '退货包裹单不存在');
            }
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //扫描条码如果明细中不存在进行插入操作
    function insert_detail($return_record, $sku, $recv_num = 1) {
        $key_arr = array('goods_code', 'spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'sell_price', 'sku_id');
        $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
        //$sub_data = array_merge($sub_data,$sku_info);
        $this->begin_trans();
        try {
//            $return_detail = array();
//            $return_detail[0]['sell_return_code'] = $return_record['sell_return_code'];
//            $return_detail[0]['sell_record_code'] = $return_record['sell_record_code'];
//            $return_detail[0]['deal_code'] = $return_record['deal_code'];
//            $return_detail[0]['goods_code'] = $sku_info['goods_code'];
//            $return_detail[0]['spec1_code'] = $sku_info['spec1_code'];
//            $return_detail[0]['spec2_code'] = $sku_info['spec2_code'];
//            $return_detail[0]['sku'] = $sku;
//            $return_detail[0]['sku_id'] = $sku_info['sku_id'];
//            $return_detail[0]['barcode'] = $sku_info['barcode'];
//            $return_detail[0]['goods_price'] = $sku_info['sell_price'];
//            $return_detail[0]['note_num'] = 0;
//            $return_detail[0]['recv_num'] = $recv_num;
//            $return_detail[0]['avg_money'] = $sku_info['sell_price']*1;
//
//            $sell_update_str = "recv_num = VALUES(recv_num)";
//            $ret = M('oms_sell_return_detail')->insert_multi_duplicate("oms_sell_return_detail", $return_detail, $sell_update_str);
//            if ($ret['status'] < 1) {
//                $this->rollback();
//                return $this->format_ret(-1, '', '保存退单明细出错');
//            }


            $pack_sql = "select return_package_code from oms_return_package where sell_return_code =:sell_return_code and return_order_status != 2";
            $pack_row = ctx()->db->get_row($pack_sql, array(':sell_return_code' => $return_record['sell_return_code']));
            if (!empty($pack_row)) {
                $package_detail = array();
                $package_detail[0]['sku'] = $sku;
                $package_detail[0]['num'] = $recv_num;
                $package_detail[0]['apply_num'] = 0;
                $package_detail[0]['return_package_code'] = $pack_row['return_package_code'];
                $package_detail[0]['goods_code'] = $sku_info['goods_code'];
                $package_detail[0]['spec1_code'] = $sku_info['spec1_code'];
                $package_detail[0]['spec2_code'] = $sku_info['spec2_code'];
                $package_detail[0]['barcode'] = $sku_info['barcode'];
                $update_str = " num = VALUES(num) ";
                $package_ret = M('oms_return_package_detail')->insert_multi_duplicate('oms_return_package_detail', $package_detail, $update_str);
                if ($package_ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '保存退货包裹单明细出错');
                }
                $lof_moren = load_model('prm/GoodsLofModel')->is_exists('1', 'type');
                $lof_arr = array();
                $lof_arr[0] = array(
                    'record_type' => 2,
                    'record_code' => $pack_row['return_package_code'],
                    'deal_code' => $return_record['deal_code'],
                    'store_code' => $return_record['store_code'],
                    'goods_code' => $sku_info['goods_code'],
                    'spec1_code' => $sku_info['spec1_code'],
                    'spec2_code' => $sku_info['spec2_code'],
                    'sku' => $sku,
                    'barcode' => $sku_info['barcode'],
                    'lof_no' => $lof_moren['data']['lof_no'],
                    'production_date' => $lof_moren['data']['production_date'],
                    'num' => $recv_num,
                    'stock_date' => date('Y-m-d'),
                    'occupy_type' => 0,
                    'create_time' => time()
                );
                $lof_update_str = "num = VALUES(num)";
                $lof_package_ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof', $lof_arr, $lof_update_str);
                if ($lof_package_ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '保存退货包裹单默认批次出错');
                }
                $this->commit();
                $sku_info['num'] = $recv_num;
                $sku_info['sku'] = $sku;
                $sku_info['apply_num'] = 0;
                return $this->format_ret(3, $sku_info);
            } else {
                $this->rollback();
                return $this->format_ret(-1, '', '退货包裹单不存在');
            }




            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }

        $sku_info['sku'] = $sku;
        $sku_info['num'] = $recv_num;
        $sku_info['apply_num'] = 0;
        $sku_info['return_package_code'] = $return_package_code;
        M('oms_return_package_detail')->insert($sku_info);
        $sku_info['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $sku_info['spec1_code']));
        $sku_info['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $sku_info['spec2_code']));
        $sku_info['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $sku_info['goods_code']));
        return $this->format_ret(3, $sku_info);
    }

    //
    function check_return_goods($sell_return_code) {
        $sql = "select sell_record_code from oms_sell_return where sell_return_code='{$sell_return_code}'";
        $sell_record_code = $this->db->getOne($sql);
        //退单商品
        $sql = "select sku from oms_sell_return_detail where sell_return_code='{$sell_return_code}'";
        $return_skus = $this->db->get_all_col($sql);
        //订单商品
        $sql = "select sku from oms_sell_record_detail where sell_record_code='{$sell_record_code}'";
        $sell_skus = $this->db->get_all_col($sql);
        $not_exist = array();
        foreach ($return_skus as $return_sku) {
            if (!in_array($return_sku, $sell_skus)) {
                $not_exist[] = $return_sku;
            }
        }
        if (!empty($not_exist)) {
            $no_exist_sku = "'" . implode("','", $not_exist) . "'";
            $sql = "select barcode from goods_sku where sku in($no_exist_sku)";
            $not_exist_barcode = $this->db->get_all_col($sql);
            $no_exist_msg = implode(',', $not_exist_barcode);
            return $this->format_ret(-1, '', $no_exist_msg);
        }
        return $this->format_ret(1, '');
    }

    /**
     * 默认打印数据 new
     * @param $id
     * @return array
     * 发货单新版打印（更改打印配件）
     */
    public function print_data_default($request) {
        $sell_return_code = $request['record_ids'];
        $r = array();
        $r['record'] = $this->db->get_row("select * from oms_sell_return where sell_return_code = :sell_return_code", array(':sell_return_code' => $sell_return_code));

        $sql = "select d.goods_code,d.note_num,d.sku,d.recv_num
            ,d.goods_price,d.avg_money
            from oms_sell_return_detail d
            where d.sell_return_code=:sell_return_code";
        $r['detail'] = $this->db->get_all($sql, array(':sell_return_code' => $sell_return_code));

        foreach ($r['detail'] as $key => $detail) {//合并同一sku
            $detail['shelf_code'] = $this->get_shelf_code($detail['sku'], $r['record']['store_code']);
            $key_arr = array('goods_name', 'goods_short_name', 'spec1_name', 'spec2_name', 'barcode', 'category_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $r['detail'][$key] = array_merge($detail, $sku_info);
        }
        $this->print_data_escape($r['record'], $r['detail'], 'new');

        $trade_data = array($r['record']);
        load_model('common/TBlLogModel')->set_log_multi($trade_data, 'print');

        return $r;
    }

    private function get_shelf_code($sku, $store_code) {
        $sql = "select a.shelf_code from goods_shelf a
        where a.store_code = :store_code and a.sku = :sku";
        $l = $this->db->get_all($sql, array(':store_code' => $store_code, ':sku' => $sku));
        $arr = array();
        foreach ($l as $_v) {
            $arr[] = $_v['shelf_code'];
        }
        return implode(',', $arr);
    }

    /**
     * 替换待打印数据字段
     * @param $record
     * @param $detail
     */
    public function print_data_escape(&$record, &$detail) {
        $record['return_country'] = oms_tb_val('base_area', 'name', array('id' => $record['return_country']));
        $record['return_province'] = oms_tb_val('base_area', 'name', array('id' => $record['return_province']));
        $record['return_district'] = oms_tb_val('base_area', 'name', array('id' => $record['return_district']));
        $record['return_street'] = oms_tb_val('base_area', 'name', array('id' => $record['return_street']));

        $record['print_time'] = date('Y-m-d H:i:s');
        $record['print_user'] = CTx()->get_session('user_name');

        $record['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $record['shop_code']));
        $record['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
        $record['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $record['sale_channel_code']));
        //换货人信息
        $record['change_country'] = oms_tb_val('base_area', 'name', array('id' => $record['change_country']));
        $record['change_province'] = oms_tb_val('base_area', 'name', array('id' => $record['change_province']));
        $record['change_city'] = oms_tb_val('base_area', 'name', array('id' => $record['change_city']));
        $record['change_district'] = oms_tb_val('base_area', 'name', array('id' => $record['change_district']));
        $record['change_street'] = oms_tb_val('base_area', 'name', array('id' => $record['change_street']));
        $record['return_express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $record['return_express_name']));
        $record['change_express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $record['change_express_name']));
        $record['pay_code'] = $this->get_pay_name($record['sell_record_code']);
        $record['return_type'] = $this->return_type[$record['return_type']];
    }

    function get_pay_name($sell_record_code) {
        $sql = "select pay_type_name from base_pay_type r left join oms_sell_record rr on rr.pay_code = r.pay_type_code where rr.sell_record_code= '{$sell_record_code}'";
        return $this->db->get_value($sql);
    }

    /**
     * @todo 今日已申请的退单笔数
     */
    function ro_num($start_time, $end_time) {
        $sql = "select count(refund_id) from api_refund where order_first_insert_time >= '{$start_time}' and order_first_insert_time <= '{$end_time}'";
        $ro_num = ctx()->db->getOne($sql);
        //SELECT * from api_taobao_fx_refund where  is_change<1 AND refund_create_time>=''

        $sql = "select count(1) from api_taobao_fx_refund where refund_create_time >= '{$start_time}' and is_change<1";
        $ro_num2 = ctx()->db->getOne($sql);
        return $ro_num + $ro_num2;
    }

    /**
     * @todo 今日已转退单数
     */
    function tran_ro_num($start_time, $end_time) {
        $sql = "select count(refund_id) from api_refund where order_first_insert_time >= '{$start_time}' and order_first_insert_time <= '{$end_time}' and is_change=1";
        $tran_ro_num = ctx()->db->getOne($sql);

        $sql = "select count(1) from api_taobao_fx_refund where refund_create_time >= '{$start_time}' and is_change=1";
        $tran_ro_num2 = ctx()->db->getOne($sql);
        return $tran_ro_num + $tran_ro_num2;
    }

    /**
     * @todo 待确认退单数
     */
    function unconfirm_ro_num() {
        $sql = "select count(sell_return_code) from oms_sell_return where return_order_status=0";
        $unconfirm_ro_num = ctx()->db->getOne($sql);
        return $unconfirm_ro_num;
    }

    /**
     * @todo 待收货退单数
     */
    function unreceipt_ro_num() {
        $sql = "select count(sell_return_code) from oms_sell_return where return_shipping_status=2";
        $unreceipt_ro_num = ctx()->db->getOne($sql);
        return $unreceipt_ro_num;
    }

    /**
     * @todo 待退款退单数
     */
    function unrefund_ro_num() {
        $sql = "select count(sell_return_code) from oms_sell_return where finance_check_status=2";
        $unrefund_ro_num = ctx()->db->getOne($sql);
        return $unrefund_ro_num;
    }

    /**
     * @todo 今日收到包裹数
     */
    function receipt_ro_num($start_time, $end_time) {
        $sql = "select count(sell_return_code) from oms_sell_return where receive_time >= '{$start_time}' and receive_time <= '{$end_time}' and return_shipping_status=1";
        $tran_ro_num = ctx()->db->getOne($sql);
        return $tran_ro_num;
    }

    /**
     * @todo 今日退款退单数
     */
    function refund_ro_num($start_time, $end_time) {
        //oms_sell_return表，按照sell_return_code &  & agree_refund_time=当天（00:00:00~23:59:59）汇总数量
        $sql = "select count(sell_return_code) from oms_sell_return where agreed_refund_time >= '{$start_time}' and agreed_refund_time <= '{$end_time}' and finance_check_status=1";
        $tran_ro_num = ctx()->db->getOne($sql);
        return $tran_ro_num;
    }

    function communicate_log($data) {
        if (!empty($data['sell_record_code'])) {
            $return_record = $this->get_return_by_return_code($data['sell_record_code']);
            return $this->add_action($return_record, "沟通日志", $data['communicate_log']);
        } else {
            return $this->format_ret(-1, '', '退单号不存在');
        }
    }

    /**
     * 检查是否生成换货单
     * @param type $sell_return_code
     * @return type     /
     */
    function check_change_record($sell_return_code) {

        $result = $this->get_return_by_return_code($sell_return_code);
        if (!empty($result['change_record'])) {
            return $this->format_ret(-1);
        }
        return $this->format_ret(1);
    }

    //退单修改调整金额
    function update_abjust_money($params) {
        $record = load_model('oms/SellReturnModel')->get_return_by_return_code($params['sell_return_code']);
        $ret = $this->update_exp('oms_sell_return', array('adjust_money' => $params['adjust_money']), array('sell_return_code' => $params['sell_return_code']));
        //添加操作日志
        $this->add_action($record, '强制改价', '修改手工调整金额' . $record['adjust_money'] . '元为' . $params['adjust_money'] . '元');
        return $ret;
    }

}
