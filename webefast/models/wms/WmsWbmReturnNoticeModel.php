<?php

require_model('wms/WmsRecordModel');

class WmsWbmReturnNoticeModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 批发退货通知单入库处理
     * @param string $record_code 通知单号
     * @param date $record_time 业务时间
     * @param array $order_mx 回传单据明细
     * @return array 处理结果
     */
    function order_shipping($record_code, $record_time, $order_mx) {
        $sql = "select * from wbm_return_notice_record where return_notice_code = :record_code";
        $notice_info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($notice_info)) {
            return $this->format_ret(-1, '', $record_code . '单据不存在');
        }
        if ($notice_info['is_check'] == 0) {
            return $this->format_ret(-1, '', $record_code . '单据未确认');
        }

        if ($notice_info['is_finish'] > 0) {
            return $this->format_ret(-1, '', $record_code . '单据已完成');
        }

        $sql = "select sku,price,rebate,trade_price from wbm_return_notice_detail_record where return_notice_code = :record_code";
        $db_dj_mx = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        $dj_mx = load_model('util/ViewUtilModel')->get_map_arr($db_dj_mx, 'sku');
        $new_record_code = load_model('wbm/ReturnRecordModel')->create_fast_bill_sn();
        $ret = load_model('util/ViewUtilModel')->append_mx_info_by_barcode($order_mx);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $order_mx = $ret['data'];
        $append_info = array('record_code' => $new_record_code);
        $order_mx = load_model('util/ViewUtilModel')->set_arr_el_val($order_mx, $append_info);
        $ins_mx = array();
        foreach ($order_mx as $k => $sub_mx) {
            $find_dj_mx = isset($dj_mx[$sub_mx['sku']]) ? $dj_mx[$sub_mx['sku']] : null;
            $dj_price = !empty($find_dj_mx) ? $find_dj_mx['price'] : 0;
            $dj_rebate = !empty($find_dj_mx) ? $find_dj_mx['rebate'] : 0;
            $dj_refer_price = !empty($find_dj_mx) ? $find_dj_mx['refer_price'] : 0;
            $sub_mx['money'] = $dj_price * $dj_rebate * $sub_mx['num'];
            $sub_mx['enotice_num'] = $sub_mx['num'];
            $sub_mx['price'] = $dj_price;
            $sub_mx['refer_price'] = $dj_refer_price;
            $ins_mx[] = $sub_mx;
        }
        $ins_info = load_model('util/ViewUtilModel')->copy_arr_by_fld($notice_info, 'record_type_code,distributor_code,org_code,user_code,store_code,rebate,init_code,brand_code');
        $ins_info['record_code'] = $new_record_code;
        $ins_info['distributor_code'] = $notice_info['custom_code'];
        $ins_info['relation_code'] = $notice_info['return_notice_code'];
        $ins_info['is_sure'] = 1;
        $ins_info['record_time'] = date('Y-m-d', strtotime($record_time));
        $ins_info['order_time'] = $record_time;
        $this->begin_trans();
        $ret = $this->insert_exp('wbm_return_record', $ins_info);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $record_code . '生成批发退货单失败.');
        }
        $ins_info['return_record_id'] = $ret['data'];
        //日志
        $user_id = empty(CTX()->get_session('user_id')) ? 1 : CTX()->get_session('user_id');
        $user_code = empty(CTX()->get_session('user_code')) ? 'admin' : CTX()->get_session('user_code');
        $finish_status = '已验收';
        $sure_status = $ins_info['is_sure'] == 1 ? "已确认" : "未确认";
        $log = array('user_id' => $user_id, 'user_code' => $user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '创建', 'module' => "wbm_return_record", 'action_note' => "对接WMS，由批发退货通知单< {$notice_info['return_notice_code']} >自动生成、自动验收", 'pid' => $ins_info['return_record_id']);
        load_model('pur/PurStmLogModel')->insert($log);
        
        //生成批发退货单明细
        $append_info = array('pid' => $ins_info['return_record_id']);
        $ins_mx = load_model('util/ViewUtilModel')->set_arr_el_val($ins_mx, $append_info);
        $ret = $this->insert_multi_duplicate('wbm_return_record_detail', $ins_mx, 'num = values(num),enotice_num = values(enotice_num)');
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $record_code . '生成批发退货单明细失败.');
        }

        $this->create_mx_lof($ins_info, $ins_mx, $new_record_code, $record_code);
        $ret = load_model('wbm/ReturnRecordModel')->shift_in($new_record_code);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $record_code . '生成批发退货单验收失败.' . $ret['message']);
        }
        $sql = "select is_finish from wbm_return_notice_record where return_notice_code = '{$record_code}'";
        $is_finish = ctx()->db->getOne($sql);
        //自动中止通知单
        if ($is_finish == 0) {
            $ret = load_model('wbm/ReturnNoticeRecordModel')->do_finish($record_code, 0);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        $sql = " update wbm_return_notice_record  set is_return=1 WHERE  return_notice_code = :record_code ";

        $this->db->query($sql, array(':record_code' => $record_code));

        $this->commit();

        return $this->format_ret(1, $new_record_code);
    }

    private function create_mx_lof(&$ins_info, &$ins_mx, $new_record_code, $record_code) {
        $store_code = $ins_info['store_code'];
        $this->get_wms_cfg($store_code);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        $order_type = 'wbm_return';
        if ($is_lof == 0) {
            foreach ($ins_mx as $sub_mx) {
                $ins_lof_mx[] = array(
                    'order_code' => $new_record_code,
                    'order_type' => $order_type,
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
            }
        } else {
            $sql = "select '{$new_record_code}' as order_code,'{$order_type}' as order_type,'{$store_code}' as store_code ,"
                    . " s.goods_code,s.spec1_code,s.spec2_code,s.sku,l.lof_no,l.production_date,l.wms_sl as num,0 as occupy_type "
                    . " from wms_b2b_order_lof l"
                    . " INNER JOIN goods_sku s ON l.barcode=s.barcode"
                    . " where l.record_code=:record_code AND l.record_type=:record_type AND l.wms_sl>0";
            $sql_value = array(':record_code' => $record_code, ':record_type' => 'wbm_return_notice');
            $ins_lof_mx = $this->db->get_all($sql, $sql_value);
        }

        $append_info = array('pid' => $ins_info['return_record_id'], 'create_time' => time());
        $ins_lof_mx = load_model('util/ViewUtilModel')->set_arr_el_val($ins_lof_mx, $append_info);

        $update_str = " num= VALUES(num)";
        $ret = $this->insert_multi_duplicate('b2b_lof_datail', $ins_lof_mx, $update_str);
        $ret = M('b2b_lof_datail')->get_all(array('order_code' => $new_record_code));
        if ($is_lof == 1) {
            $ret = load_model('prm/GoodsLofModel')->add_detail_action(0, $ins_lof_mx);
        }
    }

    function order_cancel($msg = '') {
        
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $record_code 批发退货通知单号
     * @return array 数据集
     */
    function get_record_info($record_code) {
        $sql = "select * from wbm_return_notice_record where return_notice_code  = :return_notice_code";
        $info = ctx()->db->get_row($sql, array(':return_notice_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到批发通知单');
        }

        $sql = "select custom_name,email,phone,weixin,qq,mobile,tel,address,province,city,rebate,price_type,remark from base_custom where custom_code = :custom_code";
        $info['distributor'] = ctx()->db->get_row($sql, array(':custom_code' => $info['custom_code']));
        if (empty($info['distributor'])) {
            return $this->format_ret(-1, '', '找不到分销商信息');
        }
        $chk_fld_arr = explode(',', 'custom_name,mobile,address');
        foreach ($chk_fld_arr as $_fld) {
            if (empty($info['distributor'][$_fld])) {
                return $this->format_ret(-1, '', '分销商档案的名称，电话，地址信息请补充完整');
            }
        }

        $sql = "select sku,num,price,rebate,money from wbm_return_notice_detail_record where return_notice_code  = :record_code";
        $info['goods'] = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($info['goods'])) {
            return $this->format_ret(-1, '', '找不到批发退货通知单明细');
        }
        $ret = $this->append_mx_barcode_by_sku($info['goods'], 1, 'price,rebate,money');
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info['goods'] = $ret['data'];
        return $this->format_ret(1, $info);
    }

}
