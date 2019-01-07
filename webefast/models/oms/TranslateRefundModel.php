<?php

require_model('tb/TbModel');

class TranslateRefundModel extends TbModel {

    public $shop_code_priv_arr;
    public $express_code_priv_arr;
    public $sell_return_data = array();
    public $sell_return_mx_data = array();
    public $import_flag = 0;
    protected $api_refund_data = array();
    protected $sell_data = array();
    private $refund_id_arr = array();
    public $refund_all_flag = 0;
    public $is_full_refund = 0;
    private $sys_param = array();
    private $ag_shop = array();

    function __construct() {
        parent::__construct();
        //$this->get_sys_param_cfg();
       // $this->get_sys_ag_shop();
    }

    /**
     *系统参数
     */
    function get_sys_param_cfg() {
        $param_code = array(
            'aligenius_enable',
            'aligenius_sendgoods_cancel',
            'aligenius_refunds_check',
            'aligenius_warehouse_update',
            'aligenius_deliver_refunds_check',
        );
        $this->sys_param = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
    }

    /**
     * 获取开启ag的店铺
     */
    function get_sys_ag_shop() {
        $sql = "SELECT shop_code FROM base_shop_ag";
        $this->ag_shop = $this->db->get_all_col($sql);
    }

    /**
     * 转退单主方法
     * @param type $tids 可以是单个交易号，也可以是数组
     */
    function translate_refund($tids) {
        $err_arr = array();
        if (empty($tids)) {
            return $this->format_ret(-1, '', '请指定要转退单的退单号');
        }
        if (is_array($tids)) {
            $tids_arr = $tids;
        } else {
            $tids_arr = array($tids);
        }


//        $sql = "select refund_id,tid,source,is_change,status,refund_fee,refund_desc,refund_express_code,refund_express_no,refund_id,order_last_update_time,has_good_return from api_refund where tid in($tids_list) and is_change <= 0 and status = 1";
//        $db_refund = ctx()->db->get_all($sql);
//        $sql = "select refund_id,tid,sum(num) as num,goods_barcode from api_refund_detail where tid in($tids_list) group by tid,goods_barcode";
//        $db_refund_detail = ctx()->db->get_all($sql);


        foreach ($tids_arr as $tid) {

            $r_data = $this->get_refund_data_by_tid($tid);

            if (empty($r_data['data'])) {
                if ($r_data['status'] == -4 ){
                    $err_arr[] = array('status' => -3, 'message' => $tid . ' 单据已转单');
                }else{
                    $err_arr[] = array('status' => -3, 'message' => $tid . ' 找不到此退单');
                }
                continue;
            }


            $api_data = $r_data['data'];
            $api_data['mx'] = $r_data['data_mx'];
            $this->refund_all_flag = $this->check_refund_all($api_data);
            $this->set_api_data($api_data);

            $sell_data = $this->get_sell_record($api_data['tid']);
            $this->set_sell_data($sell_data);


            $ret = $this->translate_refund_by_data_act();
            $this->api_refund_data = array();
            $this->sell_data = array();


            if ($ret['status'] < 0) {
                $err_arr[] = $ret;
                // $ret_status = $this->set_tran_result($tid,$ret['status'],'',$ret['message']);
            } else {
                //$ret_status =  $this->set_tran_result($tid,1,$ret['data']);
                $success_arr[] = $ret;
            }
        }
        if (is_array($tids)) {
            $result = array('success' => $success_arr, 'err' => $err_arr);
        } else {
            if (empty($err_arr)) {
                $result = $success_arr[0];
            } else {
                $result = $err_arr[0];
            }
        }
        return $result;
    }

    private function check_refund_mx(&$detail_mx, $sell_record_code, $deal_code) {
        $is_all_return = false;
        //

        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('is_all_return_contain_gift'));
        //参数判断
        if ($sys_param['is_all_return_contain_gift'] == 0) {
            return $is_all_return;
        }

        $new_detail_by_sku = array();
        foreach ($detail_mx as $sub) {
            $barcode = $sub['goods_barcode'];
            $barcode_row = load_model("prm/GoodsBarcodeModel")->get_data_by_barcode($barcode);
            $sub['sku'] = $barcode_row['sku'];
            $new_detail_by_sku[$sub['sku']] = $sub;
        }
        $sql = "SELECT * from oms_sell_record_detail where sell_record_code =:sell_record_code  AND deal_code=:deal_code AND is_delete=0 ";
        $sql_values = array(':sell_record_code' => $sell_record_code, ':deal_code' => $deal_code);
        $data = $this->db->get_all($sql, $sql_values);
        $is_gift = 0;
        foreach ($data as $k => $val) {
            if ($val['is_gift'] == 1) {
                $is_gift = 1;
                unset($data[$k]);
                continue;
            }
            $sku = $val['sku'];
            $no_return_num = $val['num'] - $val['return_num'];
            if (isset($new_detail_by_sku[$sku]) && ( $new_detail_by_sku[$sku]['num'] == $val['num'] || $new_detail_by_sku[$sku]['num'] == $no_return_num )) {
                unset($data[$k]);
            }
        }

        if (empty($data) && $is_gift == 1) {
            $is_all_return = TRUE;
        }
        return $is_all_return;
    }

    private function get_refund_data_by_tid($tid) {
        $sql = "select * from api_refund where tid= :tid AND status =1 AND is_change <> 1";
        $data = $this->db->get_all($sql, array(':tid' => $tid));

        if (empty($data)){
            return array('data'=>array(),'data_mx'=>array(), 'msg'=>'单据已转单','status'=> -4 );
        }

        $refund_data = array();
        $refund_data_mx = array();
        $refund_id_arr = array();
        $has_good_return = -1;
        if (!empty($data)) {
            if (count($data) > 1) {
                foreach ($data as $val) {
                    if (empty($refund_data)) {
                        $refund_data = $val;
                        $has_good_return = $val['has_good_return'];
                        $refund_id_arr[] = $val['refund_id'];
                    } else if ($has_good_return == $val['has_good_return']) {
                        $refund_id_arr[] = $val['refund_id'];

                        $refund_data['has_good_return'] = ($val['has_good_return'] == 1) ? 1 : $val['has_good_return'];
                        $refund_data['refund_fee'] += $val['refund_fee'];
                        if (!empty($val['refund_desc'])) {
                            $refund_data['refund_desc'] .= "," . $val['refund_desc'];
                        }
                        if (!empty($val['refund_express_no']) && strpos($refund_data['refund_express_no'],$val['refund_express_no']) === false) {
                            $refund_data['refund_express_no'] .= "," . $val['refund_express_no'];
                        }
                    }
                }
            } else {
                $refund_data = $data[0];
                $has_good_return = $refund_data['has_good_return'];
                $refund_id_arr[] = $refund_data['refund_id'];
            }
            if (!empty($refund_id_arr)) {
                $refund_ids = "'" . implode("','", $refund_id_arr) . "'";
                $sql = "select goods_barcode,sum(num) as num,sum(refund_price) as refund_price,tid  from api_refund_detail where refund_id in ({$refund_ids}) group by goods_barcode,tid ";
                $refund_data_mx = $this->db->get_all($sql);
            }
            $this->refund_id_arr[$tid] = $refund_id_arr;
        }


        return array('data' => $refund_data, 'data_mx' => $refund_data_mx);
    }

    protected function set_api_data(&$api_data) {
        if (!empty($api_data['mx'])) {
            $this->check_barcode($api_data['mx']);
        }
        $this->api_refund_data = $api_data;
    }

    function check_barcode(&$api_data_mx) {
        //条码转成小写
        array_walk($api_data_mx, function(&$val) {
            $val['goods_barcode'] = strtolower($val['goods_barcode']);
        });
        //合并明细中相同条码的数据
        $api_mx = array(); //已存在的明细条码
        foreach ($api_data_mx as $mx) {
            $goods_barcode = $mx['goods_barcode'];
            if (isset($api_mx[$goods_barcode])) {
                $api_mx[$goods_barcode]['num'] += $mx['num'];
            } else {
                $api_mx[$goods_barcode] = $mx;
            }
        }

        //获取条码识别后的二维数组，平台条码为键
        $barcode_list = array_column($api_data_mx, 'goods_barcode');
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_list);
        $sku_arr = $sku_data['data'];
        //明细处理
        $barcode_arr = array();
        foreach ($sku_arr as $key => $row) {
            $row_barcode = strtolower($row['barcode']); //转换后的系统条形码
            $k = 0;
            foreach ($api_mx as $key1 => $row1) {
                //若平台条码不同，但转换后的系统条码在明细中存在，合并数据后删掉平台订单明细中的识别前的条码数据$key（国标码、子条码）
                if ($key != $key1 && $row_barcode == $row1['goods_barcode']) {
                    $k = 1;
                    $api_mx[$key1]['num'] = $api_mx[$key]['num'] + $api_mx[$key1]['num'];
                }
            }

            if ($k == 0) {
                //识别后明细中还是不存在的条码数据
                $barcode['goods_barcode'] = $row_barcode;
                $barcode['num'] = $api_mx[$key]['num'];
                $barcode['tid'] = $api_mx[$key]['tid'];
                $api_mx[$key] = array_merge($api_mx[$key], $barcode);
            } else if ($k == 1) {
                //识别后明细中存在则合并数据，然后删除识别前的条码数据
                unset($api_mx[$key]);
            }
            $barcode_arr[$row_barcode] = $row_barcode;
        }

        if (count($api_mx) > count($barcode_arr)) {
            $barcode_arr = array_diff(array_column($api_mx, 'goods_barcode'), array_keys($barcode_arr));
            if(!empty($barcode_arr)){
                $sql_values = array();
                $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);

                $sql = "SELECT barcode FROM goods_combo_barcode WHERE barcode  IN({$barcode_str}) ";
                $data = $this->db->get_all($sql, $sql_values);
                if (!empty($data)) {//存在赠品
                    load_model('prm/GoodsComboOpModel')->set_refund_mx($api_mx, $data);
                }
            }
        }
        $api_data_mx = $api_mx;
    }

    protected function set_sell_data(&$sell_data) {
        $this->sell_data = $sell_data;
    }
    
    function translate_refund_qn($api_data) {
        $this->refund_id_arr[$api_data['tid']] = $api_data['refund_id'];
        //是否整单退
        $this->refund_all_flag = $this->check_refund_all($api_data);
        $this->set_api_data($api_data);
        $sell_row = $this->get_sell_record($api_data['tid']);
        $this->set_sell_data($sell_row);

        $ret = $this->translate_refund_by_data_act('qianniu');
        $this->api_refund_data = array();
        $this->sell_data = array();
        return $ret;
    }
    
    //api转退单
    function translate_refund_api($id) {

        $api_data = $this->get_refund_api($id);
        if (empty($api_data)) {
            return $this->format_ret(-1, '', '没找到指定数据');
        }
        if ($api_data['sta'] == -4) {
            return $this->format_ret(-1, '', '单据已转单');
        }
        if ($api_data['status'] != 1) {
            return $this->format_ret(-1, '', '不允许转退单');
        }

        if ($api_data['is_change'] == 1) {
            return $this->format_ret(1, '', '退单已经转单');
        }
//        if (empty($api_data['mx'])) {
//            return $this->format_ret(-1, array(), '找不到退单明细');
//        }
        //是否整单退
        $this->refund_all_flag = $this->check_refund_all($api_data);
        $this->set_api_data($api_data);

        $sell_row = $this->get_sell_record($api_data['tid']);
        $this->set_sell_data($sell_row);

        $ret = $this->translate_refund_by_data_act();
        $this->api_refund_data = array();
        $this->sell_data = array();
        return $ret;
    }

    function translate_refund_by_deal_code($deal_code) {
        $sql = " select id from  api_refund   where  tid=:deal_code AND  status=1 AND is_change<>1";
        $data = $this->db->get_all($sql, array(':deal_code' => $deal_code));
        if (!empty($data)) {
            foreach ($data as $val) {
                $this->translate_refund_api($val['id']);
            }
            return true;
        }
        return false;
    }
    
    function translate_fx_refund_by_deal_code($deal_code) {
        $sql = "SELECT sub_order_id FROM api_taobao_fx_refund WHERE purchase_order_id = :purchase_order_id AND is_change <> 1 ";
        $data = $this->db->get_all($sql, array(':purchase_order_id' => $deal_code));
        if (!empty($data)) {
            foreach ($data as $val) {
                $this->translate_fx_refund($val['sub_order_id']);
            }
            return true;
        }
        return false;
    }
    //订单判断是部分退还是整单退
    /* function is_full_refund($api_data) {
      if (empty($api_data['mx'])) {
      //整单退
      return 1;
      }
      $sql = "select goods_barcode,num from api_order_detail where tid='{$api_data['tid']}'";
      $api_order_detail_ret = $this->db->getAll($sql);
      $api_order_detail = array();
      foreach ($api_order_detail_ret as $detail_row) {
      $api_order_detail[] = $detail_row['goods_barcode'] . '_' . $detail_row['num'];
      }
      $sql = "select goods_barcode,num from api_refund_detail where tid='{$api_data['tid']}'";
      $api_refund_detail_ret = $this->db->getAll($sql);
      $api_refund_detail = array();
      foreach ($api_refund_detail_ret as $refund_detail_row) {
      $api_refund_detail[] = $refund_detail_row['goods_barcode'] . '_' . $refund_detail_row['num'];
      }
      if (count($api_order_detail) == count($api_refund_detail)) {
      $diff = array_diff($api_order_detail, $api_refund_detail);
      if (empty($diff)) {
      return 1;
      }
      }
      //部分退
      return 2;
      } */

    //是否整单退
    function check_refund_all($api_data) {
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('refund_all_cancel_order'));
        if ($cfg['refund_all_cancel_order'] == 0) {
            return 0;
        }
        $sql = "select sell_record_code,order_status,shipping_status from oms_sell_record where deal_code='{$api_data['tid']}' ";
        $sell_record = $this->db->getRow($sql);
        if (empty($sell_record) || $sell_record['order_status'] == 3) {
            return 0;
        }
        if (empty($api_data['mx'])) {
            //整单退
            return 1;
        }
        $sql = "select goods_barcode,num from api_order_detail where tid='{$api_data['tid']}'";
        $api_order_detail_ret = $this->db->getAll($sql);
        $api_order_detail = array();
        foreach ($api_order_detail_ret as $detail_row) {
            $api_order_detail[] = strtoupper($detail_row['goods_barcode']) . '_' . $detail_row['num'];
        }
        $sql = "select goods_barcode,num from api_refund_detail where tid='{$api_data['tid']}'";
        $api_refund_detail_ret = $this->db->getAll($sql);
        $api_refund_detail = array();
        foreach ($api_refund_detail_ret as $refund_detail_row) {
            $api_refund_detail[] = strtoupper($refund_detail_row['goods_barcode']) . '_' . $refund_detail_row['num'];
        }
        if (count($api_order_detail) == count($api_refund_detail)) {
            $diff = array_diff($api_order_detail, $api_refund_detail);
            if (empty($diff)) {
                return 1;
            }
        }
        return 0;
    }

    function get_sell_record($tid) {
        $sql = "select DISTINCT  t1.sell_record_code,t1.shipping_status,t1.pay_type, t1.waves_record_id,t1.order_status,t1.store_code,t2.sku,t2.num,t1.is_fenxiao,t1.is_fx_settlement from oms_sell_record t1,
        oms_sell_record_detail t2 where t1.sell_record_code = t2.sell_record_code and t2.deal_code = '{$tid}'  AND  t1.order_status<>3
        and t1.is_change_record = 0 order by t1.delivery_time desc ";
        $data = ctx()->db->get_all($sql);
        $ret_data = array();

        if (!empty($data)) {
            if (count($data) > 1) {
                $barcode_arr = $this->get_refund_goods_barcode();
                $sku_arr = $this->get_sku_by_barcode($barcode_arr);
                if (!empty($sku_arr)) {
                    $sku_str = "'" . implode("','", $sku_arr) . "'";
                    $sql = "select DISTINCT  t1.sell_record_code,t1.shipping_status,t1.pay_type, t1.waves_record_id,t1.order_status,t1.store_code,t1.is_fenxiao,t1.is_fx_settlement from oms_sell_record t1,
                    oms_sell_record_detail t2 where t1.sell_record_code = t2.sell_record_code and t2.deal_code = '{$tid}'
                    and t1.order_status<>3 and t1.is_change_record = 0 AND t2.sku in({$sku_str})  order by t1.delivery_time desc ";
                    $row = ctx()->db->get_row($sql); //暂时不考虑多条情况
                    if (!empty($row)) {
                        $ret_data = $row;
                    } else {
                        $ret_data = $data[0];
                    }
                } else {
                    $ret_data = $data[0];
                }
            } else {
                $ret_data = $data[0];
            }
        } else {
            //取作废单据
            $sql = "select DISTINCT  t1.sell_record_code,t1.shipping_status,t1.pay_type, t1.waves_record_id,t1.order_status,t1.store_code from oms_sell_record t1,
            oms_sell_record_detail t2 where t1.sell_record_code = t2.sell_record_code and t2.deal_code = '{$tid}'  AND  t1.order_status=3
            and t1.is_change_record = 0 order by t1.delivery_time desc ";
            $ret_data = ctx()->db->get_row($sql);
        }
        //判断是整单退还是部分退
        $this->is_full_refund = $this->is_full_return($tid, $ret_data);

        return $ret_data;
    }

    //退单判断整单退还是部分退
    function is_full_return($tid, $ret_data) {
        $refund_detail = $this->api_refund_data['mx'];
        //整单退
        if (empty($refund_detail)) {
            return 1;
        }
        $this->check_barcode($refund_detail);
        $api_refund_detail = array();
        foreach ($refund_detail as $key => $val) {
            $sql = "SELECT sku FROM goods_sku WHERE barcode = '{$val['goods_barcode']}'";
            $sku = $this->db->get_row($sql);
            $refund_detail[$key]['sku'] = $sku['sku'];
            $api_refund_detail[] = $val['tid'] . '_' . $sku['sku'] . '_' . $val['num'];
        }

        $sql = "SELECT deal_code,sku,num,is_gift,api_refund_num FROM oms_sell_record_detail WHERE sell_record_code = '{$ret_data['sell_record_code']}'";
        $record_detail = $this->db->get_all($sql);
        $sell_record_detail = array();
        foreach ($record_detail as $detail_row) {
            if ($detail_row['is_gift'] == 1) {
                continue;
            }
            if (($detail_row['num'] - $detail_row['api_refund_num']) <= 0) {
                continue;
            }
            $detail_row['num'] = $detail_row['num'] - $detail_row['api_refund_num'];
            $sell_record_detail[] = $detail_row['deal_code'] . '_' . $detail_row['sku'] . '_' . $detail_row['num'];
        }
        if (count($sell_record_detail) == count($api_refund_detail)) {
            $diff = array_diff($sell_record_detail, $api_refund_detail);
            if (empty($diff)) {
                return 1;
            } else {
                return 2;
            }
        } else {
            return 2;
        }
    }

    function get_refund_goods_barcode() {
        $barcode_arr = array();
        if (!empty($this->api_refund_data['mx'])) {
            foreach ($this->api_refund_data['mx'] as $val) {
                $barcode_arr[] = $val['goods_barcode'];
            }
        }
        return $barcode_arr;
    }

    function get_sku_by_barcode($barcode_arr) {
        $sku_arr = array();
        if(empty($barcode_arr)) {
            return $sku_arr;
        }
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "select sku from goods_barcode where barcode in ({$sku_str})";
        $data = $this->db->get_all($sql,$sql_values);
        foreach ($data as $val) {
            $sku_arr[] = $val['sku'];
        }
        return $sku_arr;
    }

    function get_refund_api($id) {
        $sql = "select tid from api_refund where id = '{$id}'";
        $row = ctx()->db->get_row($sql);
        $r_data = $this->get_refund_data_by_tid($row['tid']);
        //  return array('data' => $refund_data, 'data_mx' => $refund_data_mx);
        $api_data = array();
        if (!empty($r_data['data'])) {
            $api_data = $r_data['data'];
            $api_data['mx'] = $r_data['data_mx'];
        }
        if ($r_data['status']==-4) {
            $api_data['sta'] = -4;
        }
        return $api_data;
    }

    function set_tran_result_by_tid($tid, $is_change, $sell_return_code = '', $change_remark = '') {

        if (isset($this->refund_id_arr[$tid])) {
            foreach ($this->refund_id_arr[$tid] as $refund_id) {
                $status = $this->set_tran_result($refund_id, $is_change, $sell_return_code, $change_remark);
                if ($status === false) {
                    return $status;
                }
            }
        }
        return true;
    }

    //设置转单结果
    function set_tran_result($refund_id, $is_change, $sell_return_code = '', $change_remark = '') {

        $up = array(
            'change_remark' => $change_remark,
            'is_change' => $is_change,
        );
        if (!empty($sell_return_code)) {
            $up['refund_record_code'] = $sell_return_code;
        }



        //  $this->api_refund_data

        if ($this->api_refund_data['refund_id'] == $refund_id) {
            $is_change_old = empty($this->api_refund_data['is_change']) ? 0 : $this->api_refund_data['is_change'];
        } else {
            $is_change_old = -10; //特殊处理
        }




        if ($is_change == '-1') {
            $order_last_update_time = strtotime($this->api_refund_data['order_last_update_time']);
            $order_last_update_time = ($order_last_update_time > 0) ? $order_last_update_time : strtotime($this->api_refund_data['order_first_insert_time']);
            $pre_time = strtotime("-3 days");
            $check_time = strtotime("-1 year"); //防止为空
            if ($pre_time > $order_last_update_time && $order_last_update_time > $check_time) {
                $up['change_remark'] = '连续3天，转退单未成功，系统自动将退单置为已转单';
                $up['is_change'] = 1;
            }
        }
        $tb = 'api_refund';
        $where_data = array('refund_id' => $refund_id);
        if ($is_change_old > -10) {
            $where_data['is_change'] = $is_change_old;
        }

        if ($this->is_fx) {
            $tb = 'api_taobao_fx_refund';
            $where_data = array('sub_order_id' => $refund_id); //, 'is_change' => $is_change_old
        } else {
            $up['lastchanged'] = date('Y-m-d H:i:s');
        }

        $this->update_exp($tb, $up, $where_data);

        $run_num = $this->affected_rows();
        $ret_status = ($run_num > 0) ? true : false;
        return $ret_status;
    }

    function set_is_change_old($tid, $is_change_old) {
        $this->is_change_arr[$tid] = $is_change_old;
    }

    function get_is_change_old($tid, $is_change_old) {
        return $this->is_change_arr[$tid];
    }

    //订单信息预检查
    function check_api_data($api_data) {
        $check_refund = array(
            'tid' => '交易号',
            'refund_fee' => '应退款'
        );
        $check_refund_detail = array(
            'tid' => '交易号',
            'num' => '商品数量',
            'goods_barcode' => '商品条形码',
        );
        $err_arr = array();
        foreach ($check_refund as $_fld => $_fld_name) {
            $_t = !empty($api_data[$_fld]) ? $api_data[$_fld] : null;
            if (empty($_t)) {
                $err_arr[] = $_fld_name;
            }
        }
        if (!empty($api_data['mx'])) {
            foreach ($check_refund_detail as $_fld => $_fld_name) {
                foreach ($api_data['mx'] as $sub_mx) {
                    $_t = !empty($sub_mx[$_fld]) ? $sub_mx[$_fld] : null;
                    if (empty($_t)) {
                        $err_arr[] = $_fld_name;
                    }
                }
            }
        }
        $err_msg = '';
        if (!empty($err_arr)) {
            $err_msg .= join(',', array_unique($err_arr)) . '不能为空';
        }
        if (empty($err_msg)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-20, '', $err_msg);
        }
    }

    //验证当前用户是否有这个店铺的转单权限
    function check_user_shop_priv($shop_code) {
        if (!isset($this->shop_code_priv_arr)) {
            $ret = load_model('base/ShopModel')->get_purview_shop();
            $shop_code_arr = array();
            foreach ($ret as $sub_ret) {
                $shop_code_arr[] = $sub_ret['shop_code'];
            }
            $this->shop_code_priv_arr = $shop_code_arr;
        }
        if (in_array($shop_code, $this->shop_code_priv_arr)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-20, '', "店铺代码{$shop_code}不存在或没权限");
        }
    }

    //快递匹配
    function match_express($api_data) {
        $ret = $this->check_api_data($api_data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ($api_data['refund_express_code'] == '') {
            return $this->format_ret(1);
        }
        $sql = "select company_code,company_name from base_express_company";
        $db_ex = ctx()->db->get_all($sql);
        $ex_map = load_model('util/ViewUtilModel')->get_map_arr($db_ex, 'company_code');
        $find_ex = isset($ex_map[$api_data['refund_express_code']]) ? $ex_map[$api_data['refund_express_code']] : '';
        if (empty($find_ex)) {
            return $this->format_ret(1, $api_data['refund_express_code'], '找不到匹配数据');
        }
        //$find_ex['refund_express_code'] = isset($find_ex['refund_express_code']) ? $find_ex['refund_express_code'] : '';
        return $this->format_ret(1, $api_data['refund_express_code']);
    }

    /**
     * 转退单主方法
     * 不用
     */
    function translate_refund_by_data($api_data) {
//        $sql = "select DISTINCT  t1.sell_record_code,t1.shipping_status,t1.pay_type from oms_sell_record t1,
//        oms_sell_record_detail t2 where t1.sell_record_code = t2.sell_record_code and t2.deal_code = '{$api_data['tid']}'
//        and t1.order_status<>3 and t1.is_change_record = 0";
//        $db_sell = ctx()->db->get_all($sql);
//        if (empty($db_sell)) {
//            return $this->format_ret(-1, '', '找不到对应的订单或订单已作废');
//        }
//
//        $new_sell_return_code_arr = array();
//        $err_arr = array();
//        //  $this->begin_trans();
//        foreach ($db_sell as $sub_sell) {
//            $ret = $this->translate_refund_by_data_act($api_data, $sub_sell);
//            if ($ret['status'] < 0) {
//                //$this->rollback();
//                $err_arr[] = $ret['status'];
//            } else {
//                $new_sell_return_code_arr[] = $ret['data'];
//            }
//        }
//
//        $new_sell_return_code = join(',', $new_sell_return_code_arr);
//        if (!empty($err_arr)) {
//
//            return $ret;
//        } else {
//
//            //  $this->commit();
//            return $this->format_ret(1, $new_sell_return_code);
//        }
    }
    function intercept_all_record($tid) {
        $sql = "select DISTINCT r.*  from oms_sell_record r
            INNER JOIN oms_sell_record_detail d
            ON r.sell_record_code=d.sell_record_code
            where d.deal_code=:deal_code AND  r.order_status<>3 AND r.shipping_status<>4  ";
        $data = $this->db->get_all($sql, array(':deal_code' => $tid));
        $msg_arr = array();
        foreach ($data as $sell_row) {
            //默认部分退
            $_refund_type = 2;
            $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sell_row, $_refund_type, '发现存在退单，拦截');
            $operate_xq = $tid . "存在退单拦截{$sell_row['sell_record_code']}";
            $operate_xq.= $ret['status']<0?$ret['message']:'';
            $msg_arr[] = array(
                'table_name' => 'oms_sell_record',
                'table_id' => $sell_row['sell_record_code'],
                'module' => '网络订单',
                'yw_code' => '交易号存在退单，订单拦截',
                'user_id' => '0',
                'user_code' => '',
                'add_time'=>date('Y-m-d H:i:s'),
                'operate_xq' => $operate_xq,
                'operate_type' => '订单拦截',
            );
        }
        if(!empty($msg_arr)){
            $this->insert_multi_exp('sys_operate_log', $msg_arr);
        }
    }


    function translate_refund_by_data_act($type = '') {
        //$api_data,

        $api_data = &$this->api_refund_data;
        $sell_row = &$this->sell_data;
        
        $this->intercept_all_record($api_data['tid']);
        
//refund_desc
        if (empty($this->sell_data)) {
            $ret_status = $this->set_tran_result_by_tid($api_data['tid'], -1, '', '找不到对应订单');
            return $this->format_ret(-1, '', '找不到对应订单');
        }
        //重新加载数据
        $sell_row = load_model('oms/SellRecordModel')->get_record_by_code($this->sell_data['sell_record_code']);
        if ($sell_row['order_status'] == 3) {
            $ret_status = $this->set_tran_result_by_tid($api_data['tid'], 1, '', '订单为作废订单，系统自动将退单置为已转单');
            return $this->format_ret(-1, '', '订单为作废订单，系统自动将退单置为已转单');
        }

        $sell_record_code = $sell_row['sell_record_code'];
        $shipping_status = $sell_row['shipping_status'];
        $pay_type = $sell_row['pay_type'];

        if ($shipping_status < 4) {//订单未发货
            //整单退
            if ($this->refund_all_flag == 1) {
                if($sell_row['is_fenxiao'] == 2 && $sell_row['is_fx_settlement'] == 1) {//分销订单已结算，拦截再作废
                    $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sell_row);
                    if ($ret['status'] < 0) {
                        return $ret;
                    }
                }
                $ret = load_model('oms/SellRecordOptModel')->opt_cancel($sell_record_code, 10, 'refund_all_cancel', 1);
                if ($ret['status'] == 1) {
                    $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('direct_cancel'));
                    if ($cfg['direct_cancel'] == 1 && $sell_row['is_fenxiao'] != 2) {
                        $ret = load_model('oms/SellReturnOptModel')->create_return_record_by_cancel($sell_record_code, 'direct_cancel');
                        if ($ret['status'] < 0) {
                            return $ret;
                        }
                    }
                    if($type != 'qianniu') {
                        $ret_status = $this->set_tran_result_by_tid($api_data['tid'], 1, $ret['data'], '订单已作废，系统自动将退单置为已转单');
                    }
                    //拦截成功，AG推送
                    //$this->taobao_ag_send_goods($api_data, $sell_record_code);
                }

                return $ret;
            }

            if($this->is_full_refund == 1){
                //如果为整单退，先清除之前部分退的标识
                $this->query("DELETE FROM oms_sell_record_tag WHERE tag_type='problem' AND tag_v='REFUND' AND sell_record_code='{$sell_record_code}'" );
            }
            
            $ret = load_model('oms/SellRecordOptModel')->biz_intercept($sell_row, $this->is_full_refund, $api_data['refund_desc']);
            
            if ($ret['status'] < 0) {
                $action_name = '订单拦截失败';
                $action_note = '存在退单,发货前进行订单拦截失败.' . $ret['message'];
            } else {

                $action_name = '订单拦截';
                $action_note = '存在退单,发货前进行订单拦截成功.';
                //拦截成功置为已转单
                //拦截成功，增加退单SKU标识
                load_model('oms/SellRecordOptModel')->intercept_refund_sku($sell_record_code, $api_data['mx']);

                if($type != 'qianniu') {
                    $ret_status = $this->set_tran_result_by_tid($api_data['tid'], 1, '', '订单已设为问题单，系统自动将退单置为已转单');
                }
                //拦截成功，AG推送
                //$this->taobao_ag_send_goods($api_data, $sell_record_code);

            }
            //订单日志
            load_model('oms/SellRecordModel')->add_action($sell_record_code, $action_name, $action_note);

            return $ret;
        } else {
            $this->begin_trans();
            $ret = $this->match_express($api_data);
            if ($ret['status'] < 0) {
                $this->rollback();
                $this->set_tran_result_by_tid($api_data['tid'], -1, '', $ret['data']);
                return $this->format_ret(-1, '', $ret['message']);
            }
            $params_info = array();
            $params_info['mx'] = array();

            $is_all_return = $this->check_refund_mx($api_data['mx'], $sell_record_code, $api_data['tid']);

            if ($is_all_return === TRUE) {
                $sell_record_detail_data = $this->get_sell_record_detail($sell_record_code, $api_data['tid']);
                $params_info['mx'] = $sell_record_detail_data['detail'];
            }


            if (empty($api_data['mx'])) {
                //设置成
                if ($sell_row['shipping_status'] == 4) {
                    $sell_record_detail_data = $this->get_sell_record_detail($sell_record_code, $api_data['tid']);
                    $params_info['mx'] = $sell_record_detail_data['detail'];
                    if ($api_data['refund_fee'] == 0) {
                        $api_data['refund_fee'] = $sell_record_detail_data['avg_money'];
                    }

                    //整单据退
                } else {
                    $this->rollback();
                    $ret_status = $this->set_tran_result_by_tid($api_data['tid'], -1, '', '找不到退单明细数据');
                    return $this->format_ret(-1, '', '找不到退单明细数据');
                }
            }
            $return_express_code = $ret['data'];

            $params_info['adjust_money'] = 0; //手工调整金额
            $params_info['seller_express_money'] = 0; //卖家承担运费金额
            $params_info['compensate_money'] = 0; //赔付金额

            $return_reason_code = 0;
            if (!empty($api_data['refund_reason'])) {
                $return_reason = load_model('base/ReturnReasonModel')->get_return_reason_code($api_data['refund_reason']);
                if ($return_reason['status'] == 1 && !empty($return_reason['data'])) {
                    $return_reason_code = $return_reason['data'];
                }
            }
 
            $params_info['tid'] = $api_data['tid']; //交易号
            $params_info['return_reason_code'] = $return_reason_code; //退货原因CODE
            $params_info['return_remark'] = ''; //退单备注
            $params_info['return_buyer_memo'] = $api_data['refund_desc']; //退单说明
            $params_info['return_pay_code'] = 'bank'; //退款方式
            $params_info['return_express_code'] = $return_express_code; //买家退货快递公司
            $params_info['return_express_no'] = isset($api_data['refund_express_no']) ? $api_data['refund_express_no'] : ''; //买家退货快递单号
            $params_info['refund_id'] = $api_data['refund_id']; //平台退单号
            $params_info['is_fenxiao'] = isset($api_data['is_fenxiao']) ? $api_data['is_fenxiao'] : 0;
            if ($api_data['has_good_return'] == 0) {
                $params_info['return_type_money'] = 1;
            }
            if (empty($params_info['mx']) && !empty($api_data['mx'])) {

                $goods_barcode_list = load_model('util/ViewUtilModel')->get_arr_val_by_key($api_data['mx'], 'goods_barcode', 'string', 'string');
                $sql = "select sku,barcode from goods_barcode where barcode in($goods_barcode_list)";
                $db_barcode = ctx()->db->get_all($sql);
                $barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_barcode, 'barcode', 0, 'sku', 1);

                foreach ($api_data['mx'] as $sub_mx) {
                    $_row = array();
                    $_row['deal_code'] = $api_data['tid'];
                    $s_goods_barcode = strtolower($sub_mx['goods_barcode']);
                    $b_goods_barcode = strtoupper($sub_mx['goods_barcode']);

                    $_sku = isset($barcode_arr[$s_goods_barcode]) ? $barcode_arr[$s_goods_barcode] : null;
                    $_sku = isset($barcode_arr[$b_goods_barcode]) ? $barcode_arr[$b_goods_barcode] : $_sku;



                    if (empty($_sku)) {
                        $this->rollback();
                        $ret_status = $this->set_tran_result_by_tid($api_data['tid'], -1, '', $sub_mx['goods_barcode'] . '条码在系统中找不到');
                        return $this->format_ret(-1, '', $sub_mx['goods_barcode'] . '条码在系统中找不到');
                    }
                    $_row['sku'] = $_sku;
                    $_row['return_num'] = $sub_mx['num'];
                    $_row['return_price'] = $sub_mx['refund_price'];
                    $params_info['mx'][] = $_row;
                }
            }

            $return_type = $pay_type == 'cod' ? 2 : 3;
            $ret = load_model('oms/SellReturnOptModel')->create_return($params_info, $sell_record_code, $return_type, null, $api_data['refund_fee'], $api_data['order_first_insert_time']);
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if (isset($api_data['has_good_return']) && $api_data['has_good_return'] == 0) {
                $sql = "SELECT return_label_name FROM  base_return_label WHERE return_label_code = 'SYS002';";
                $name = $this->db->getOne($sql);
                $data = array();
                $data['sell_return_code'] = $ret['data'];
                $data['tag_type'] = 'return_tag';
                $data['tag_v'] = 'SYS002';
                $data['tag_desc'] = $name;
                M('oms_sell_return_tag')->insert($data);
            }
            $new_sell_return_code = $ret['data'];
            if ($ret['status'] < 0) {
                $this->rollback();
                if ($ret['status'] == -2) {
                    $ret_status = $this->set_tran_result_by_tid($api_data['tid'], 1, '', $ret['message'] . '系统自动置为已转单');
                } else {
                    $ret_status = $this->set_tran_result_by_tid($api_data['tid'], -1, '', $ret['message']);
                }

                return $ret;
            }

            // if($ret_status===false){
            // $this->rollback();
            //  return $this->format_ret(-1,array(),'删除异常，转单状态已经变更!');
            //}
            $ret_status = $this->set_tran_result_by_tid($api_data['tid'], 1, $new_sell_return_code, '');
            if ($ret_status === false) {
                $this->rollback();
                return $this->format_ret(-1, array(), '删除异常，转单状态已经变更!');
            }
            $this->commit();

            return $this->format_ret(1, $new_sell_return_code);
        }
    }


    function get_sell_record_detail($sell_record_code, $deal_code) {
        $sql = "select deal_code,sku,num,return_num,avg_money from oms_sell_record_detail where sell_record_code=:sell_record_code AND deal_code=:deal_code AND is_delete=0 ";
        $data = $this->db->get_all($sql, array(':sell_record_code' => $sell_record_code, ':deal_code' => $deal_code));
        $avg_money = 0;
        $return_detail = array();
        foreach ($data as $val) {
            $avg_money +=$val['avg_money'];
            $sku = $val['sku'];
            $val['num'] = $val['num'] - $val['return_num'];
            if (!isset($return_detail[$sku])) {
                $return_detail[$sku] = array('deal_code' => $val['deal_code'], 'sku' => $val['sku'], 'return_num' => $val['num'], 'refund_price' => $val['avg_money']);
            } else {
                $return_detail[$sku]['return_num']+=$val['num'];
                $return_detail[$sku]['refund_price']+=$val['avg_money'];
            }
        }

        return array('avg_money' => $avg_money, 'detail' => array_values($return_detail));
    }

    /**
     * 自动转退单定时器
     * @todo 优先转未转单退单，再转转单失败退单
     */
    function cli_trans() {
        //未转单退单
        $this->cli_trans_deal('0');
        //转单失败退单
        $this->cli_trans_deal('-1');
    }

    /**
     * 转退单
     */
    function cli_trans_deal($type = 0) {
        $page_size = 3000;
        $wh = $type == '0' ? ' AND is_change = 0 ' : ' AND is_change < 0';
        $sql = "SELECT COUNT(1) FROM api_refund WHERE status = 1 {$wh}";
        $c = $this->db->getOne($sql);
        $page_num = ceil($c / $page_size);
        for ($page_no = 1; $page_no <= $page_num; $page_no++) {
            $this->cli_trans_each($page_size, $wh);
        }
    }

    function cli_trans_each($batch_num, $wh) {
        $sql = "SELECT refund_id,tid,lastchanged,is_change FROM api_refund WHERE status = 1 {$wh}";
        $sql .= " ORDER BY order_first_insert_time DESC LIMIT {$batch_num}";
        $db_refund = $this->db->get_all($sql);
        if (empty($db_refund)) {
            return false;
        }

        foreach ($db_refund as $sub_refund) {
            $_refund_id = $sub_refund['refund_id'];

            $ret = $this->translate_refund($sub_refund['tid']);
            if ($ret['status'] < 0) {
                echo "{$_refund_id} 转退单失败 {$ret['message']}\n";
            } else {
                echo "{$_refund_id} 转退单成功 \n";
            }
        }
        return true;
    }

    private $is_fx = false;

    function set_is_fx() {
        $this->is_fx = true;
    }

    function cli_trans_each_fx($batch_num = 100) {
        $sql = "select sub_order_id from api_taobao_fx_refund where is_change <= 0";
        $sql .= " order by refund_create_time asc limit {$batch_num}";
        $db_refund = ctx()->db->get_all($sql);
        if (empty($db_refund)) {
            return false;
        }
        //$processed_tid_arr = array();

        foreach ($db_refund as $sub_refund) {
            $_refund_id = $sub_refund['sub_order_id'];
            $ret = $this->translate_fx_refund($_refund_id);
            if ($ret['status'] < 0) {
                echo "{$_refund_id} 转退单失败 {$ret['message']}\n";
            } else {
                echo "{$_refund_id} 转退单成功 \n";
            }
        }
        return true;
    }

    function translate_fx_refund($sub_order_id) {
        $this->set_is_fx();
        $sql = "select * from api_taobao_fx_refund  where sub_order_id=:sub_order_id ";
        $data = $this->db->get_row($sql, array(':sub_order_id' => $sub_order_id));
        $sum_refund_data = $this->get_fx_refund_data_by_tid($data['purchase_order_id']);
        $data = $sum_refund_data['data'];
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到数据');
        }

        if ($data['is_change'] == 1) {
            return $this->format_ret(-1, '', '单据已经转单');
        }
        $refund_data = array();
        $refund_data['refund_id'] = $data['sub_order_id'];
        $refund_data['tid'] = $data['purchase_order_id'];
        $refund_data['source'] = 'taobao';
        $refund_data['is_change'] = $data['is_change'];
        $refund_data['status'] = 1;
        $refund_data['refund_fee'] = $data['refund_fee'];
        $refund_data['refund_desc'] = $data['refund_desc'];
        $refund_data['refund_express_code'] = '';
        $refund_data['order_last_update_time'] = $data['modified'];
        $refund_data['has_good_return'] = empty($data['is_return_goods']) ? 0 : 1;
        $refund_data['is_fenxiao'] = 1; //淘宝分销标识
        $refund_data['mx'] = $sum_refund_data['data_mx'];
        //是否整单退
        $this->refund_all_flag = $this->check_fx_refund_all($refund_data);
        
        $this->set_api_data($refund_data);
        
        $sell_data = $this->get_sell_record($refund_data['tid']);
        //$this->refund_id_arr[$refund_data['tid']] = array($refund_data['refund_id']);
        $this->set_sell_data($sell_data);
        $ret = $this->translate_refund_by_data_act();
        return $ret;
    }
    //汇总同一交易号的退单
    function get_fx_refund_data_by_tid($fenxiao_id) {
        $sql = "SELECT * FROM api_taobao_fx_refund WHERE purchase_order_id = :tid AND is_change<>1 ";
        $data = $this->db->get_all($sql, array(':tid' => $fenxiao_id));
        $sql = "SELECT trade_type FROM api_taobao_fx_trade WHERE fenxiao_id = :fenxiao_id ";
        $record_data = $this->db->get_row($sql ,array(':fenxiao_id' => $fenxiao_id));
        $refund_data = array();
        $refund_data_mx = array();
        $refund_id_arr = array();
        if (!empty($data)) {
            if (count($data) > 1) {
                foreach ($data as $val) {
                    if (empty($refund_data)) {
                        $refund_data = $val;
                        $refund_id_arr[] = $val['sub_order_id'];
                    } else {
                        $refund_data['refund_fee'] += $val['refund_fee'];
                        if (!empty($val['refund_desc'])) {
                            $refund_data['refund_desc'] .= "," . $val['refund_desc'];
                        }
                        $refund_id_arr[] = $val['sub_order_id'];
                    } 
                }
            } else {
                $refund_data = $data[0];
                $refund_id_arr[] = $refund_data['sub_order_id'];
            }
            if (!empty($refund_id_arr)) {
                $sql_values = array();
                $refund_ids = $this->arr_to_in_sql_value($refund_id_arr,'sub_order_id',$sql_values);
                $sql = "SELECT sku_outer_id AS goods_barcode,sum(num) as num,fenxiao_id AS tid,sum(distributor_payment) as money FROM api_taobao_fx_order WHERE fenxiao_oid in ({$refund_ids}) GROUP BY sku_outer_id,fenxiao_id";
                $refund_data_mx = $this->db->get_all($sql,$sql_values);
                foreach($refund_data_mx as &$value) {
                    $value['refund_price'] = $value['money'] / $value['num'];
                }
            }
            $this->refund_id_arr[$fenxiao_id] = $refund_id_arr;
        }

        return array('data' => $refund_data, 'data_mx' => $refund_data_mx); 
    }
    //淘分销是否整单退,整单退款作废订单
    function check_fx_refund_all($api_data) {
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('refund_all_cancel_order'));
        if ($cfg['refund_all_cancel_order'] == 0) {
            return 0;
        }
        $sql = "SELECT sell_record_code,order_status,shipping_status FROM oms_sell_record WHERE deal_code = :deal_code ";
        $sell_record = $this->db->get_row($sql,array(':deal_code' => $api_data['tid']));
        if (empty($sell_record) || $sell_record['order_status'] == 3) {
            return 0;
        }
        if (empty($api_data['mx'])) {
            //整单退
            return 1;
        }
        //淘分销明细的子采购订单id
        $sql = "SELECT fenxiao_oid FROM api_taobao_fx_order WHERE fenxiao_id = :fenxiao_id ";
        $fenxiao_oid_arr = $this->db->get_all_col($sql,array(':fenxiao_id' => $api_data['tid']));
        
        //查询淘分销退单的子采购单id
        $sql = "SELECT sub_order_id FROM api_taobao_fx_refund WHERE purchase_order_id = :purchase_order_id ";
        $sub_order_id_arr = $this->db->get_all_col($sql,array(':purchase_order_id' => $api_data['tid']));
        
        if (count($fenxiao_oid_arr) == count($sub_order_id_arr)) {
            $diff = array_diff($fenxiao_oid_arr, $sub_order_id_arr);
            if (empty($diff)) {
                return 1;
            }
        }
        return 0;
    }

}
