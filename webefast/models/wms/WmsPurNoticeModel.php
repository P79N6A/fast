<?php

require_model('wms/WmsRecordModel');

class WmsPurNoticeModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 采购通知单入库处理
     * @param string $record_code 通知单号
     * @param date $record_time 业务时间
     * @param array $order_mx 回传单据明细
     * @return array 处理结果
     */
    function order_shipping($record_code, $record_time, $order_mx) {
        $sql = "select order_record_id,record_code,supplier_code,user_code,record_time,store_code,rebate,init_code,order_time,in_time,relation_code,brand_code,is_check,is_execute,is_finish,pre_sale,pur_type_code,remark from pur_order_record where record_code = :record_code";
        $notice_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($notice_info)) {
            return $this->format_ret(-1, '', $record_code . '单据不存在');
        }
        if ($notice_info['is_check'] == 0) {
            return $this->format_ret(-1, '', $record_code . '单据未确认');
        }

        $new_data = $this->check_is_more($record_code);

        $issuccess = 1;
        $ret_pur = array();
        if ($new_data === false) {
            $this->begin_trans();
            $ret_pur = $this->create_pur_record($notice_info, $record_code, $record_time, $order_mx);
            if ($ret_pur['status'] < 1) {
                $issuccess = 0;
                $this->rollback();
            } else {
                $this->commit();
            }
        } else {
            if (!empty($new_data)) {
                foreach ($new_data as $key => $order_detail) {
                    $this->begin_trans();
                    $ret_pur = $this->create_pur_record($notice_info, $record_code, $record_time, $order_detail);
                    if ($ret_pur['status'] > 0) {
                        list($api_record_code, $item_type) = explode(",", $key);
                        $status = $this->update_create_status($record_code, $api_record_code, $item_type);
                        if ($status === false) {
                            $issuccess = 0;
                            $this->rollback();
                            $ret_pur = $this->format_ret(-1, '', '单据异常，或已经生成入库单');
                        } else {
                            $this->commit();
                        }
                    } else {
                        $issuccess = 0;
                        $this->rollback();
                    }
                }
            } else {
                $issuccess = 0;
                $ret_pur = $this->format_ret(-1, '', '数据为空，暂时无法处理');
            }
        }

        //上面创建退货单是循环内事务,此处不能加事务,后续需完善
        $sql = 'SELECT COUNT(1) FROM pur_purchaser_record WHERE relation_code=:_code';
        $c = $this->db->get_value($sql, [':_code' => $record_code]);
        if ($c > 0) {
            $ret = $this->update_exp('pur_order_record', ['is_execute' => 1], ['order_record_id' => $notice_info['order_record_id']]);
        }
        if ($ret_pur['status'] == 1 && $issuccess === 1) {
            $ret = $this->update_exp('pur_order_record', ['is_finish' => 1], ['order_record_id' => $notice_info['order_record_id']]);
        }


        return $ret_pur;
    }

    function update_create_status($record_code, $api_record_code, $item_type) {
        $up_data = array('is_create' => 1);
        $where = "record_code='{$record_code}' AND record_type='pur_notice'  AND new_record_code='{$api_record_code}' AND  item_type='{$item_type}'  AND is_create=0 ";
        $this->db->update('wms_b2b_order_detail', $up_data, $where);
        $num = $this->db->affected_rows();
        if ($num == 0) {
            return false;
        }
        return true;
    }

    function check_is_more($record_code) {
        $sql = "select api_product from  wms_b2b_trade where record_code=:record_code AND record_type=:record_type";
        $api_product = $this->db->get_value($sql, array(':record_code' => $record_code, ':record_type' => 'pur_notice'));
        $product_arr = array('ydwms', 'qimen', 'shunfeng');
        if (!in_array($api_product, $product_arr)) {
            return false;
        }
        $sql = "select * from wms_b2b_order_detail where record_code=:record_code AND record_type=:record_type AND is_create=:is_create AND wms_sl>0 ";
        $sql_values = array(':record_code' => $record_code, ':record_type' => 'pur_notice', ':is_create' => 0);
        $data = $this->db->get_all($sql, $sql_values);
        if (!empty($data)) {
            $new_data = array();
            foreach ($data as $val) {
                $key = $val['new_record_code'] . ',' . $val['item_type'];
                $val['num'] = $val['wms_sl'];
                $new_data[$key][] = $val;
            }
            return $new_data;
        } else {
            return array();
        }
    }

    private function create_pur_record($notice_info, $record_code, $record_time, $order_mx, $new_record_code = '') {
        $is_create_code = false;
        if (empty($new_record_code)) {
            $new_record_code = load_model('pur/PurchaseRecordModel')->create_fast_bill_sn();
            $is_create_code = true;
        }
        $sql = "select sku,price,rebate,refer_price,num from pur_order_record_detail where record_code = :record_code";
        $db_dj_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        $dj_mx = load_model('util/ViewUtilModel')->get_map_arr($db_dj_mx, 'sku');


        $ret = load_model('util/ViewUtilModel')->append_mx_info_by_barcode($order_mx);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $order_mx = $ret['data'];
        $append_info = array('record_code' => $new_record_code);
        $order_mx = load_model('util/ViewUtilModel')->set_arr_el_val($order_mx, $append_info);
        $ins_mx = array();
        $ins_lof_mx = array();

        $store_code = $notice_info['store_code'];

        if (isset($order_mx[0]['item_type']) && $order_mx[0]['item_type'] == 0) {
            //切换成次品仓库
            $new_store_code = $this->get_cp_store_code($store_code);
            $store_code = empty($new_store_code) ? $store_code : $new_store_code;
        }


        $this->get_wms_cfg($store_code);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        $order_type = 'purchase';
        $sys_lof_data = array();
        foreach ($order_mx as $k => $sub_mx) {
            $find_dj_mx = isset($dj_mx[$sub_mx['sku']]) ? $dj_mx[$sub_mx['sku']] : null;
            $dj_price = !empty($find_dj_mx) ? $find_dj_mx['price'] : 0;
            $dj_rebate = !empty($find_dj_mx) ? $find_dj_mx['rebate'] : 0;
            $dj_refer_price = !empty($find_dj_mx) ? $find_dj_mx['refer_price'] : 0;
            $sub_mx['money'] = $dj_price * $dj_rebate * $sub_mx['num'];
            $sub_mx['rebate'] = $dj_rebate;
            $sub_mx['notice_num'] = isset($find_dj_mx['num']) ? $find_dj_mx['num'] : 0;
            $sub_mx['price'] = $dj_price;
            $sub_mx['refer_price'] = $dj_refer_price;
            $ins_mx[] = $sub_mx;
            $ins_lof_mx[] = array(
                'order_code' => $new_record_code,
                'order_type' => 'purchase',
                'store_code' => $store_code,
                'goods_code' => $sub_mx['goods_code'],
                'spec1_code' => $sub_mx['spec1_code'],
                'spec2_code' => $sub_mx['spec2_code'],
                'sku' => $sub_mx['sku'],
                'lof_no' => $this->default_lof_no,
                'production_date' => $this->default_lof_production_date,
                'num' => $sub_mx['num'],
                'occupy_type' => 0,
            );
            $sys_lof_data[] = array(
                'sku' => $sub_mx['sku'],
                'lof_no' => empty($sub_mx['lof_no']) ? $this->default_lof_no : $sub_mx['lof_no'],
                'production_date' => empty($sub_mx['production_date']) ? $this->default_lof_production_date : $sub_mx['production_date'],
                'type' => 0,
            );
        }
        if ($is_lof == 1) {
            $sql = "select '{$new_record_code}' as order_code,'{$order_type}' as order_type,'{$store_code}' as store_code ,"
                    . " s.goods_code,s.spec1_code,s.spec2_code,s.sku,l.lof_no,l.production_date,l.wms_sl as num,0 as occupy_type "
                    . " from wms_b2b_order_lof l"
                    . " INNER JOIN goods_sku s ON l.barcode=s.barcode"
                    . " where record_code=:record_code AND record_type=:record_type";
            $sql_value = array(':record_code' => $record_code, ':record_type' => 'pur_notice'); //$record_code
            $ins_lof_mx = $this->db->get_all($sql, $sql_value);
        }

        if (!empty($sys_lof_data)) {
            $this->insert_multi_exp('goods_lof', $sys_lof_data, true);
        }


        $ins_info = load_model('util/ViewUtilModel')->copy_arr_by_fld($notice_info, 'record_type_code,distributor_code,org_code,user_code,store_code,rebate,init_code,brand_code');
        $ins_info['record_code'] = $new_record_code;
        $ins_info['relation_code'] = $notice_info['record_code'];
        $ins_info['supplier_code'] = $notice_info['supplier_code'];
        $ins_info['is_check'] = 1;
        $ins_info['is_sure'] = 1;
        $ins_info['record_time'] = date('Y-m-d', strtotime($record_time));
        $ins_info['is_add_time'] = $record_time;
        $ins_info['bill_time'] = $record_time;
        $ins_info['enter_time'] = $record_time;
        $ins_info['order_time'] = $record_time;
        $ins_info['record_type_code'] = $notice_info['pur_type_code'];
        $ins_info['store_code'] = $store_code;
        $ins_info['remark'] = $notice_info['remark'];

        $ret = $this->insert_exp('pur_purchaser_record', $ins_info);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $record_code . ' 生成采购入库单失败.');
        }
        $ins_info['purchaser_record_id'] = $ret['data'];
        //日志
        $user_id = empty(CTX()->get_session('user_id')) ? 1 : CTX()->get_session('user_id');
        $user_code = empty(CTX()->get_session('user_code')) ? 'admin' : CTX()->get_session('user_code');
        $log = array('user_id' => $user_id, 'user_code' => $user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '已确认', 'finish_status' => '已完成', 'action_name' => '创建', 'module' => "purchase_record", 'pid' => $ins_info['purchaser_record_id'], 'action_note' => "对接WMS，由采购通知单< {$notice_info['record_code']} >自动生成、自动验收");
        load_model('pur/PurStmLogModel')->insert($log);


        $append_info = array('pid' => $ins_info['purchaser_record_id']);
        $ins_mx = load_model('util/ViewUtilModel')->set_arr_el_val($ins_mx, $append_info);
        $ret = $this->insert_multi_duplicate('pur_purchaser_record_detail', $ins_mx, 'num = values(num),notice_num = values(notice_num)');
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $record_code . ' 生成采购入库单明细失败.');
        }
        //生成批次
        foreach ($ins_lof_mx as $k => $sub_mx) {
            $ins_lof_mx[$k]['pid'] = $ins_info['purchaser_record_id'];
            $ins_lof_mx[$k]['create_time'] = time();
        }

        $update_str = "num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate('b2b_lof_datail', $ins_lof_mx, $update_str);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', '保存扫描数据失败');
        }
        //验收单据
        $ret = load_model('pur/PurchaseRecordModel')->checkin($new_record_code, 0, array('data' => $ins_info), array('data' => $ins_mx));
        if ((int) $ret['status'] <= 0) {
            return $this->format_ret(-1, '', $record_code . ' 生成采购入库单验收失败.' . $ret['message']);
        }

        return $this->format_ret(1, $new_record_code);
    }

    function order_cancel($msg = '') {
        $record = load_model("oms/SellReturnOptModel")->get_record_by_code($sell_return_code);
        $ret = $this->biz_unconfirm($record, $msg);
        return $ret;
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $record_code 采购通知单号
     * @return array 数据集
     */
    function get_record_info($record_code) {
        $sql = "select record_code,supplier_code,user_code,record_time,store_code,rebate,init_code,order_time,in_time,relation_code,brand_code,num,money,is_check,is_execute,is_add_person,is_add_time,remark,pre_sale,pur_type_code from pur_order_record where record_code  = :record_code";
        $info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到采购通知单');
        }

        $sql = "select supplier_name,area_code,user_code,status,contact_person,email,phone,weixin,mobile,tel,province,city,district,street,address,zipcode from base_supplier where supplier_code = :supplier_code";
        $info['supplier'] = ctx()->db->get_row($sql, array(':supplier_code' => $info['supplier_code']));
        if (empty($info['supplier'])) {
            return $this->format_ret(-1, '', '找不到供应商信息');
        }
        $chk_fld_arr = explode(',', 'supplier_name,contact_person,mobile,address');
        foreach ($chk_fld_arr as $_fld) {
            if (empty($info['supplier'][$_fld])) {
                return $this->format_ret(-1, '', '供应商档案的联系人，电话，地址信息请补充完整');
            }
        }

        $sql = "select sku,num from pur_order_record_detail where record_code = :record_code";
        $info['goods'] = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($info['goods'])) {
            return $this->format_ret(-1, '', '找不到采购通知单明细');
        }
        $ret = $this->append_mx_barcode_by_sku($info['goods']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info['goods'] = $ret['data'];
        return $this->format_ret(1, $info);
    }

}
