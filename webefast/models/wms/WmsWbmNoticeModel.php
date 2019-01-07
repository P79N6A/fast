<?php

require_model('wms/WmsRecordModel');

class WmsWbmNoticeModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    /**
     * 批发销货通知单出库处理
     * @param string $record_code 通知单号
     * @param date $record_time 业务时间
     * @param array $order_mx 回传单据明细
     * @return array 处理结果
     */
    function order_shipping($record_code, $record_time, $order_mx) {
        $record_type = 'wbm_notice';
        //检查并获取通知单数据
        $ret = $this->check_record($record_code, $record_type);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $notice_info = $ret['data'];
        //获取通知单明细
        $notice_detail = $this->get_notcie_detail($record_code, $record_type);
        //生成批发销货单号
        $new_record_code = load_model('wbm/StoreOutRecordModel')->create_fast_bill_sn();

        $util_obj = load_model('util/ViewUtilModel');

        $this->begin_trans();

        //获取快递数据
        $sql = "SELECT wms_order_time, express_code,express_no FROM wms_b2b_trade WHERE record_code=:record_code AND record_type=:record_type ";
        $wms_record_data = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        //是否关联JIT单据，销货单处理
        $ret_jit = load_model('api/WeipinhuijitPickModel')->create_jit_out_record_by_wms($record_code);
        if ($ret_jit['status'] < 0) {
            $this->rollback();
            return $ret_jit;
        } else if ($ret_jit['status'] == 2) {
            //不是JIT单据，直接生成销货单
            $ins_info = $util_obj->copy_arr_by_fld($notice_info, 'record_type_code,distributor_code,org_code,user_code,store_code,rebate,init_code,brand_code');
            $ins_info['record_code'] = $new_record_code;
            $ins_info['relation_code'] = $notice_info['record_code'];
            //经销采购订单编号
            if (isset($notice_info['jx_code'])) {
                $ins_info['jx_code'] = $notice_info['jx_code'];
            }
            $ins_info['is_sure'] = 1;
            $ins_info['record_time'] = date('Y-m-d', strtotime($record_time));
            $ins_info['order_time'] = $record_time;
            if (!empty($wms_record_data['express_no'])) {
                $ins_info['express_code'] = $wms_record_data['express_code'];
                $ins_info['express'] = $wms_record_data['express_no'];
            }

            $ret = $this->insert_exp('wbm_store_out_record', $ins_info);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', $record_code . '生成批发销货单失败.');
            }
            $ins_info['store_out_record_id'] = $ret['data'];
            //日志
            $user_id = empty(CTX()->get_session('user_id')) ? 1 : CTX()->get_session('user_id');
            $user_code = empty(CTX()->get_session('user_code')) ? 'admin' : CTX()->get_session('user_code');
            $finish_status = $ins_info['is_store_out'] == 1 ? '已出库' : '未出库';
            $sure_status = $ins_info['is_sure'] == 1 ? "已确认" : "未确认";
            $log = array('user_id' => $user_id, 'user_code' => $user_code, 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '创建', 'module' => "store_out_record", 'pid' => $ins_info['store_out_record_id'], 'action_note' => "对接WMS，由批发销货通知单< {$notice_info['record_code']} >自动生成、自动验收");
            load_model('pur/PurStmLogModel')->insert($log);

        } else {
            $new_record_code = $ret_jit['data'];
            $sql_record = "SELECT * FROM wbm_store_out_record WHERE record_code=:record_code";
            $ins_info = $this->db->get_row($sql_record, array(':record_code' => $new_record_code));
            $detail_update_str = ' num = VALUES(num) ';

            $update_data = array('is_cancel' => 0);
            if (!empty($wms_record_data['express_no'])) {
                $update_data['express_code'] = $wms_record_data['express_code'];
                $update_data['express'] = $wms_record_data['express'];
            }
            $this->db->update('wbm_store_out_record', $update_data, array('record_code' => $new_record_code));
        }

        //回传明细中补充SKU数据
        $ret = $util_obj->append_mx_info_by_barcode($order_mx);
        if ($ret['status'] < 0) {
             $this->rollback();
            return $ret;
        }
        $order_mx = $ret['data'];

        //添加销货单明细
        $ins_mx = array(); //需要添加的销货单明细数据
        $ins_lof_mx = array(); //需要添加的销货单批次明细
        foreach ($order_mx as $sub_mx) {
            $find_dj_mx = isset($notice_detail[$sub_mx['sku']]) ? $notice_detail[$sub_mx['sku']] : null;
            $dj_price = !empty($find_dj_mx) ? $find_dj_mx['price'] : 0;
            $dj_rebate = !empty($find_dj_mx) ? $find_dj_mx['rebate'] : 0;
            $dj_refer_price = !empty($find_dj_mx) ? $find_dj_mx['refer_price'] : 0;
            $sub_mx['money'] = $dj_price * $dj_rebate * $sub_mx['num'];
            $sub_mx['enotice_num'] = $sub_mx['num'];
            $sub_mx['price'] = $dj_price;
            $sub_mx['rebate'] = $dj_rebate;
            $sub_mx['refer_price'] = $dj_refer_price;
            $sub_mx['pid'] = $ins_info['store_out_record_id'];
            $sub_mx['record_code'] = $new_record_code;
            $ins_mx[] = $sub_mx;

            $ins_lof_mx[] = array(
                'pid' => $ins_info['store_out_record_id'],
                'order_code' => $new_record_code,
                'order_type' => 'pur_return',
                'store_code' => $notice_info['store_code'],
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
        $detail_update_str = isset($detail_update_str) ? $detail_update_str : 'num = VALUES(num),enotice_num = VALUES(enotice_num)';
        $ret = $this->insert_multi_duplicate('wbm_store_out_record_detail', $ins_mx, $detail_update_str);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $record_code . '生成批发销货单明细失败.');
        }
        $force_negative_inv = 0;

        //批次处理
        $this->get_wms_cfg($notice_info['store_code']);
        $is_lof = isset($this->wms_cfg['is_lof']) ? $this->wms_cfg['is_lof'] : 0;
        if ($is_lof == 1) {
            //批次切换
            if (in_array($this->api_product, array('qimen'))) {
                $ret = load_model('wms/WmsSwitchLofModel')->switch_lof_lock($record_code, $record_type);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '批次处理失败');
                }
                $force_negative_inv = 1;
            }
            $lof_data = $this->get_lof_data($record_code, $record_type, $new_record_code, $notice_info['store_code']);
            $append_info = array('pid' => $ins_info['store_out_record_id'], 'create_time' => time());
            $lof_data = load_model('util/ViewUtilModel')->set_arr_el_val($lof_data, $append_info);
        }
        //添加销货单批次明细
        $ins_lof_mx = empty($lof_data) ? $ins_lof_mx : $lof_data;
        $update_str = "num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate('b2b_lof_datail', $ins_lof_mx, $update_str);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存批次数据失败');
        }
        if ($is_lof == 1) {
            //更新非批次通知数量
            $sql = "UPDATE wbm_store_out_record_detail AS rd,(SELECT sku,SUM(init_num) AS enotice_num FROM b2b_lof_datail WHERE order_code=:code AND order_type='wbm_store_out' GROUP BY sku) AS ld SET rd.enotice_num=ld.enotice_num WHERE rd.record_code=:code AND rd.sku=ld.sku";
            $this->db->query($sql, array(':code' => $new_record_code));

            $sql = "UPDATE wbm_store_out_record AS sr,(SELECT SUM(enotice_num) AS enotice_num FROM wbm_store_out_record_detail WHERE record_code=:code) AS rd SET sr.enotice_num=rd.enotice_num WHERE sr.record_code=:code";
            $this->db->query($sql, array(':code' => $new_record_code));
        }

        //更新通知单执行状态
        $this->db->update('wbm_notice_record', array('is_execute' => 1), array('record_code' => $record_code));
        //销货单验收
        $ret = load_model('wbm/StoreOutRecordModel')->do_sure_and_shift_out($new_record_code, 0, 0, array('data' => $ins_info), array('data' => $ins_mx), $force_negative_inv);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', $record_code . '生成批发销货单验收失败.' . $ret['message']);
        }

        //通知单未完成，自动完成通知单
        $sql = "SELECT is_finish FROM wbm_notice_record WHERE record_code = :record_code";
        $is_finish = ctx()->db->getOne($sql, array(':record_code' => $record_code));
        //自动中止通知单
        if ($is_finish == 0) {
            $ret = load_model('wbm/NoticeRecordModel')->update_stop(1, 'is_stop', $notice_info['notice_record_id'], 0);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
        }

        $this->commit();
        return $this->format_ret(1, $new_record_code);
    }

    function order_cancel($msg = '') {
        $record = load_model("oms/SellReturnOptModel")->get_record_by_code($sell_return_code);
        $ret = $this->biz_unconfirm($record, $msg);
        return $ret;
    }

    /**
     * 获取上传中间表的单据数据
     * @param string $record_code 批发通知单号
     * @return array 数据集
     */
    function get_record_info($record_code) {
        $sql = "select record_code,record_type_code,distributor_code,org_code,user_code,order_time,record_time,store_code,rebate,init_code,out_time,relation_code,brand_code,num,money,is_add_person,is_add_time,remark,name as receiver_name,tel as receiver_tel,province,city,district,address from wbm_notice_record where record_code  = :record_code";
        $info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到批发通知单');
        }

        $sql = "select contact_person,contact_phone as mobile,province,city,district,street,address from base_store where store_code = :store_code";
        $info['store'] = ctx()->db->get_row($sql, array(':store_code' => $info['store_code']));
        if (empty($info['store'])) {
            return $this->format_ret(-1, '', '找不到仓库信息');
        }
        $chk_fld_arr = explode(',', 'contact_person,mobile,province,city,address');
        foreach ($chk_fld_arr as $_fld) {
            if (empty($info['store'][$_fld])) {
                return $this->format_ret(-1, '', '仓库档案的联系人，手机号，地址信息请补充完整');
            }
        }

        $sql = "select custom_name,custom_code,contact_person,email,phone,weixin,qq,mobile,tel,province,city,district,address,rebate,price_type,remark,contact_person from base_custom where custom_code = :custom_code";
        $info['distributor'] = ctx()->db->get_row($sql, array(':custom_code' => $info['distributor_code']));
        if (empty($info['distributor'])) {
            return $this->format_ret(-1, '', '找不到分销商信息');
        }
        $chk_fld_arr = explode(',', 'contact_person,mobile,address');
        foreach ($chk_fld_arr as $_fld) {
            if (empty($info['distributor'][$_fld])) {
                return $this->format_ret(-1, '', '分销商档案的联系人、手机号、地址信息请补充完整');
            }
        }

        $sql = "select sku,num,price,rebate,money from wbm_notice_record_detail where record_code  = :record_code";
        $goods = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($goods)) {
            return $this->format_ret(-1, '', '找不到采购通知单明细');
        }

        $ret = $this->incr_lof_info($record_code, 'wbm_notice', $info['store_code'], $goods);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $info['goods'] = array_values($ret['data']);
        return $this->format_ret(1, $info);
    }

}
