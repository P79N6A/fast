<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
require_lib('apiclient/TaobaoClient');

class TaobaoAgModel extends TbModel {

    //ag单据相关状态
    public $ag_status = array(
        '1' => '退单待转单',
        '3' => '待推送处理结果',
        '4' => '待推送审核状态',
        '5' => '完成',
        '6' => '强制完成',
    );

    //订单推送值
    public $sell_record_push_val = array(
        'SUCCESS' => '取消成功',
        'FAIL' => '取消失败',
    );

    //退单推送值
    public $sell_return_push_val = array(
        'SUCCESS' => '已入库',
        //'FAIL' => '取消失败',
    );

    public $sys_param = array();

    function get_table() {
        return 'api_taobao_ag';
    }

    function __construct() {
        parent::__construct();
        $this->get_sys_param_cfg();
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
            'aligenius_upload_check'
        );
        $this->sys_param = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
    }

    /**
     * 列表查询
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }

        $sql_main = "FROM {$this->table} rl  WHERE 1 ";
        $sql_values = array();
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //退单编号
        if (isset($filter['refund_id']) && $filter['refund_id'] != '') {
            $sql_main .= " AND rl.refund_id LIKE :refund_id ";
            $sql_values[':refund_id'] = $filter['refund_id'] . '%';
        }
        //买家昵称
        if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {
            if (!empty($filter['shop_code'])) {
                $shop_code_arr = explode(',', $filter['shop_code']);
                $buyer_nick_arr = array();
                foreach ($shop_code_arr as $shop_code) {
                    $buyer_nick = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($filter['buyer_nick'], 'buyer_nick', $shop_code);
                    $buyer_nick_arr[] = str_replace('~1~', '', $buyer_nick);
                }
                $buyer_nick_arr = array_unique(array_filter($buyer_nick_arr));
                if (!empty($buyer_nick_arr)) {
                    $buyer_nick_str = $this->arr_to_like_sql_value($buyer_nick_arr, 'buyer_nick', $sql_values,'rl.');
                    $sql_main .= " AND {$buyer_nick_str}";
                } else {
                    $sql_main .= ' AND 1=2 ';
                }
            } else {
                $shop_info = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
                if (!empty($shop_info)) {
                    $shop_code_arr = array_column($shop_info, 'shop_code');
                    foreach ($shop_code_arr as $shop_code) {
                        $buyer_nick = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($filter['buyer_nick'], 'buyer_nick', $shop_code);
                        $buyer_nick_arr[] = str_replace('~1~', '', $buyer_nick);
                    }
                    $buyer_nick_arr = array_unique(array_filter($buyer_nick_arr));
                    if (!empty($buyer_nick_arr)) {
                        $buyer_nick_str = $this->arr_to_like_sql_value($buyer_nick_arr, 'buyer_nick', $sql_values, 'rl.');
                        $sql_main .= " AND {$buyer_nick_str}";
                    } else {
                        $sql_main .= ' AND 1=2 ';
                    }
                }
            }
        }

        //销售平台
        if (isset($filter['source']) && $filter['source'] <> '') {
            $arr = explode(',', $filter['source']);
            $str = $this->arr_to_in_sql_value($arr, 'source', $sql_values);
            $sql_main .= " AND rl.source  in ({$str}) ";
        }
        //申请时间
        if (isset($filter['order_first_start']) && $filter['order_first_start'] != '') {
            $sql_main .= " AND (rl.order_first_insert_time >= :order_first_start )";
            $sql_values[':order_first_start'] = $filter['order_first_start'];
        }
        if (isset($filter['order_first_end']) && $filter['order_first_end'] != '') {
            $sql_main .= " AND (rl.order_first_insert_time <= :order_first_end )";
            $sql_values[':order_first_end'] = $filter['order_first_end'];
        }

        //单据状态
        if (isset($filter['ag_status_tab']) && $filter['ag_status_tab'] != '') {
            switch ($filter['ag_status_tab']) {
                case 'wait_process'://待处理
                    $sql_main .= " AND rl.ag_status IN (1,2) ";
                    break;
//                case 'processing'://待处理
//                    $sql_main .= " AND rl.ag_status=2 ";
//                    break;
                case 'wait_sync'://待同步
                    $sql_main .= " AND rl.ag_status=3 ";
                    break;
                case 'wait_check'://待审核
                    $sql_main .= " AND rl.ag_status=4 ";
                    break;
                case 'completed'://完成
                    $sql_main .= " AND rl.ag_status IN (5,6) ";
                    break;
            }
        }

        if (isset($filter['ag_status']) && $filter['ag_status'] != '') {
            $sql_main .= " AND rl.ag_status=:ag_status ";
            if($filter['ag_status'] == 2.1 || $filter['ag_status'] == 2.2) {
                $filter['ag_status'] = 2;
            }
            $sql_values[':ag_status'] = $filter['ag_status'];
        }

        //交易号
        if (isset($filter['tid']) && $filter['tid'] != '') {
            $sql_main .= " AND rl.tid LIKE :tid ";
            $sql_values[':tid'] = $filter['tid'] . '%';
        }
        $select = 'rl.*';

        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $sql_main .= ' ORDER BY order_first_insert_time DESC';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //$cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as &$value) {
//            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
//                $value['buyer_nick'] = $this->name_hidden($value['buyer_nick']);
//            }
            if($value['ag_status'] == 2) { //状态为2，有两种场景
                $value['ag_status_name'] =  $value['ag_record_type'] == 1 ? '退货未入库' : '原单未全部作废';
            } else {
                $value['ag_status_name'] = $this->ag_status[$value['ag_status']];                
            }
            if ($value['ag_record_type'] == 1) {
                $value['push_val_name'] = $this->sell_return_push_val[$value['push_val']];
            } else if ($value['ag_record_type'] == 2) {
                $value['push_val_name'] = $this->sell_record_push_val[$value['push_val']];
            }
            //$value['buyer_nick'] = load_model('sys/security/CustomersSecurityModel')->decrypt_shop_text($value['buyer_nick'], 'buyer_nick', $value['shop_code']);
        }

        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 定时器
     */
    function aligenius_cli() {
        $this->record_check();
        $this->update_ag_sell_record();
        $this->update_sell_record_type();
        $this->update_sell_return_type();
        return $this->format_ret(1);
    }


    /**
     * 对初始单据进行检查
     * 将单据由未处理变成已处理
     * @return array
     */
    function record_check() {
        //对待处理的退单检查
        $sql = "SELECT ar.refund_id FROM api_taobao_ag AS ata INNER JOIN api_refund AS ar ON ata.refund_id=ar.refund_id WHERE ar.is_change=1 AND ata.ag_status=1 ";
        $refund = $this->db->get_all($sql);
        if (empty($refund)) {
            return $this->format_ret(-1, '', '无平台退单！');
        }

        $this->update_ag_sell_record($refund);
        return $this->format_ret(1);
    }

    /**
     * 关联订单全部作废，有效订单全部未发货或部分发货调用取消接口
     * 关联订单全部发货调用退货入库接口
     * @param array $refund_sell_record
     * @return array
     */
    function update_ag_sell_record($refund_sell_record = array()) {
        //查询数据
        $sql_value = array();
        $sql = "SELECT r1.order_status,r1.sell_record_code,r2.refund_id FROM oms_sell_record AS r1 INNER JOIN oms_sell_record_detail AS r3 ON r1.sell_record_code=r3.sell_record_code
                INNER JOIN api_taobao_ag AS r2 ON r3.deal_code=r2.tid WHERE 1  ";
        if (!empty($refund_sell_record)) {
            $refund_id_arr = array_column($refund_sell_record, 'refund_id');
            $refund_id_str = $this->arr_to_in_sql_value($refund_id_arr, 'refund_id', $sql_value);
            $sql .= " AND (r2.refund_id IN ({$refund_id_str}) AND r2.ag_status=1)";//待处理的单子
        }else{
            $sql .= " AND (r2.ag_status=2 AND r2.ag_record_type=2 AND r2.cancel_status=1)";//订单没有全部作废
        }
        $sell_record = $this->db->get_all($sql, $sql_value);
        if (empty($sell_record)) {
            return $this->format_ret(-1, '', '无订单！');
        }

        //组装更新数据
        $refund_record = array();
        foreach ($sell_record as $value) {
            $refund_record[$value['refund_id']]['sell_record_code'][] = $value['sell_record_code'];
        }

        $deliver_refund_id_arr = array();//订单全部发货的refund_id
        //更新ag中间表
        foreach ($refund_record as $refund_id => $record_info) {
            $sell_record_code_arr = array_unique($record_info['sell_record_code']);
            $sell_record_code_count = count($sell_record_code_arr);//关联订单总数
            $sql_value = array();
            $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_value);
            //关联订单作废数量
            $sql = "SELECT COUNT(1) FROM oms_sell_record WHERE sell_record_code IN ({$sell_record_code_str}) AND order_status=3";
            $sell_record_code_cancel = $this->db->get_value($sql, $sql_value);
            if ($sell_record_code_cancel == $sell_record_code_count) {//关联订单全部作废
                $this->update_ag_by_sell_record_cancel($refund_id, $sell_record_code_arr, 2);
                continue;
            }
            if(!empty($refund_sell_record)){
                //关联有效订单全部发货
                $sql = "SELECT COUNT(1) FROM oms_sell_record WHERE sell_record_code IN ({$sell_record_code_str}) AND order_status<>3 AND shipping_status<4";
                $sell_record_code_no_deliver = $this->db->get_value($sql, $sql_value);
                if ($sell_record_code_no_deliver == 0) {
                    $deliver_refund_id_arr[] = $refund_id;
                    continue;
                }
            }
            //关联订单全部未发货或部分发货
            $this->update_ag_by_sell_record_cancel($refund_id, $sell_record_code_arr, 1);
        }

        if (!empty($deliver_refund_id_arr)) {//处理全部发货的单子
            $this->update_ag_by_sell_record_all_deliver($deliver_refund_id_arr);
        }

        return $this->format_ret(1);
    }


    /**
     * 关联订单全部作废，全部未发货，部分发货
     * @param $refund_id
     * @param $sell_record_code_arr
     * @param $cancel_status
     * @return array
     */
    function update_ag_by_sell_record_cancel($refund_id, $sell_record_code_arr, $cancel_status) {
        $sql_value = array(
            ':sell_record_code' => implode(',', $sell_record_code_arr),
            ':cancel_status' => $cancel_status,//1 部分作废 2 全部作废
            ':ag_status' => 2,
            ':ag_record_type' => 2,//关联单据类型 1 退单 2 订单
            ':refund_id' => $refund_id,
            ':process_status' => '拦截取消'
        );
        $sql = "UPDATE api_taobao_ag SET sell_record_code=:sell_record_code,cancel_status=:cancel_status,ag_status=:ag_status,ag_record_type=:ag_record_type,process_status=:process_status WHERE refund_id=:refund_id AND (ag_status=1 OR (ag_status=2 AND ag_record_type=2 AND cancel_status=1))";
        $ret = $this->query($sql, $sql_value);
        $rows = $this->affected_rows();
        if ($ret['status'] == 1 && $rows > 0) {
            $action_note = ($cancel_status == 1) ? "关联订单部分作废" : "关联订单全部作废";
            $action_note .= ",更新状态为处理中";
            //日志
            $log = array(
                'user_code' => '自动服务',
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '变更状态',
                'record_status' => '处理中',
                'action_note' => $action_note,
                'refund_id' => $refund_id
            );
            load_model('oms/TaobaoAgLogModel')->insert($log);
        }
        return $this->format_ret(1);
    }


    /**
     * 关联订单全部发货
     * @param $refund_id
     * @param $sell_record_code_arr
     * @param $cancel_status
     * @return array
     */
    function update_ag_by_sell_record_all_deliver($refund_id_arr) {
        $sql_value = array();
        $refund_id_str = $this->arr_to_in_sql_value($refund_id_arr, 'refund_id', $sql_value);
        $sql = "SELECT refund_id,refund_record_code FROM api_refund WHERE refund_id IN ({$refund_id_str})";
        $api_refund = $this->db->get_all($sql, $sql_value);
        $ag_refund = array_column($api_refund, 'refund_record_code', 'refund_id');
        foreach ($ag_refund as $refund_id => $refund_record_code) {
            $refund_record_code = !empty($refund_record_code) ? $refund_record_code : '';
            $ag_update = array(
                'refund_record_code' => $refund_record_code,
                'ag_status' => 2,
                'ag_record_type' => 1,
                'process_status' => '退货入库'
            );
            $ag_where = array(
                'refund_id' => $refund_id,
                'ag_status' => 1
            );
            $ret = $this->update_exp($this->table, $ag_update, $ag_where);
            $rows = $this->affected_rows();
            if ($ret['status'] == 1 && $rows > 0) {
                $log = array(
                    'user_code' => '自动服务',
                    'action_time' => date('Y-m-d H:i:s'),
                    'action_name' => '变更状态',
                    'record_status' => '处理中',
                    'action_note' => '关联订单全部发货，更新状态为处理中',
                    'refund_id' => $refund_id
                );
                load_model('oms/TaobaoAgLogModel')->insert($log);
            }
        }

        return $this->format_ret(1);
    }


    /**
     * ag关联的订单已全部作废
     * 将ag状态更新为待推送
     * 将推送值改为取消成功
     */
    function update_sell_record_type() {
        //关联订单，并且订单已全部作废
        $sql = "SELECT refund_id FROM api_taobao_ag WHERE ag_status=2 AND ag_record_type=2 AND cancel_status=2 ";
        $refund_id_arr = $this->db->get_all_col($sql);
        if (empty($refund_id_arr)) {
            return $this->format_ret(1);
        }
        $sql_values = array();
        $refund_id_str = $this->arr_to_in_sql_value($refund_id_arr, 'refund_id', $sql_values);
        $sql = "UPDATE api_taobao_ag SET ag_status=:ag_status,push_val=:push_val WHERE refund_id IN ({$refund_id_str}) AND ag_status=2 AND cancel_status=2";
        $sql_values[':ag_status'] = 3;
        $sql_values[':push_val'] = 'SUCCESS';
        $this->query($sql, $sql_values);
        //日志
        $log_arr = array();
        foreach ($refund_id_arr as $refund_id) {
            $log_arr[] = array(
                'user_code' => '自动服务',
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '变更状态',
                'record_status' => '已处理 待推送',
                'action_note' => '关联订单均为作废状态，更新状态为已处理，更新推送值 取消成功',
                'refund_id' => $refund_id
            );
        }
        $this->insert_multi_exp('api_taobao_ag_log', $log_arr);

        return $this->format_ret(1);
    }

    /**
     * 关联的售后服务单已验收入库
     * @return array
     */
    function update_sell_return_type() {
        //售后服务单已验收入库并且不是换货退单
        $sql = "SELECT r1.refund_id FROM api_taobao_ag AS r1 INNER JOIN oms_sell_return AS r2 ON r1.refund_record_code=r2.sell_return_code WHERE r1.ag_status=2 AND r1.ag_record_type=1 AND r2.return_shipping_status=1 AND r2.is_exchange_goods = 0 ";
        $refund_id_arr = $this->db->get_all_col($sql);
        if (empty($refund_id_arr)) {
            return $this->format_ret(1);
        }
        $sql_values = array();
        $refund_id_str = $this->arr_to_in_sql_value($refund_id_arr, 'refund_id', $sql_values);
        $sql = "UPDATE api_taobao_ag SET ag_status=3,push_val=:push_val WHERE refund_id IN ({$refund_id_str}) AND ag_status=2 ";
        $sql_values[':push_val'] = 'SUCCESS';
        $this->query($sql, $sql_values);
        //日志
        $log_arr = array();
        foreach ($refund_id_arr as $refund_id) {
            $log_arr[] = array(
                'user_code' => '自动服务',
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '变更状态',
                'record_status' => '已处理 待推送',
                'action_note' => '关联退单已验收入库，更新状态为已处理，更新推送值 入库成功',
                'refund_id' => $refund_id
            );
        }
        $this->insert_multi_exp('api_taobao_ag_log', $log_arr);

        return $this->format_ret(1);
    }

    /**
     * 获取单据信息
     * @param $refund_id
     * @param string $field
     * @return array|bool|mixed
     */
    function get_ag_by_refund_id($refund_id, $field = '*') {
        $sql = "SELECT {$field} FROM api_taobao_ag WHERE refund_id=:refund_id";
        $sql_values[':refund_id'] = $refund_id;
        $ret = $this->db->get_row($sql, $sql_values);
        return $ret;
    }


    /**
     * 设为已处理
     * @param $params
     * @return array
     */
    function do_set_process($params) {
        if (!in_array($params['ag_record_type'], array(1, 2))) {
            return $this->format_ret(-1, '', '关联单据类型异常');
        }
        $refund_info = $this->get_ag_by_refund_id($params['refund_id']);
        if (empty($refund_info)) {
            return $this->format_ret(-1, '', '退单不存在！');
        }
        if ($refund_info['ag_status'] != 2) {
            return $this->format_ret(-1, '', '单据不是处理中的状态！');
        }
        $ag_update = array(
            'push_val' => $params['push_val'],
            'ag_status' => 3,
        );
        $ag_where = array(
            'refund_id' => $params['refund_id'],
            'ag_status' => 2,
        );
        $ret = $this->update_exp($this->table, $ag_update, $ag_where);
        $rows = $this->affected_rows();
        if ($rows != 1 || $ret['status'] != 1) {
            return $this->format_ret(-1, '', '设置失败！单据状态可能已变更');
        }
        if ($params['ag_record_type'] == 2) {
            $action_note = $params['push_val'] == "SUCCESS" ? '关联订单设置为推送成功' : '关联订单设置为推送失败';
        } else {
            $action_note = '关联退单设置为已入仓';
        }
        //日志
        $log = array(
            'user_code' => CTX()->get_session('user_name'),
            'action_time' => date('Y-m-d H:i:s'),
            'action_name' => '变更状态',
            'record_status' => '已处理，待推送',
            'action_note' => $action_note,
            'refund_id' => $params['refund_id']
        );
        load_model('oms/TaobaoAgLogModel')->insert($log);
        return $this->format_ret(1, '', '设置成功！');
    }

    /**
     *强制完成
     * @param $refund_id
     * @return array
     */
    function enforce_complete($refund_id) {
        $refund_info = $this->get_ag_by_refund_id($refund_id);
        if (empty($refund_info)) {
            return $this->format_ret(-1, '', '单据不存在！');
        }
        if ($refund_info['ag_status'] > 4) {
            return $this->format_ret(-1, '', '单据已终止！');
        }
        $ret = $this->update_exp($this->table, array('ag_status' => 6), array('refund_id' => $refund_id));
        $rows = $this->affected_rows();
        if ($ret['status'] != 1 || $rows != 1) {
            return $this->format_ret('-1', '', '更改失败，单据状态可能已变更');
        }
        //日志
        $log = array(
            'user_code' => CTX()->get_session('user_name'),
            'action_time' => date('Y-m-d H:i:s'),
            'action_name' => '强制完成',
            'record_status' => '完成',
            'action_note' => '手动强制完成',
            'refund_id' => $refund_id
        );
        load_model('oms/TaobaoAgLogModel')->insert($log);
        return $this->format_ret(1, '', '更改成功！');
    }

    /**
     * 同步
     * @param $refund_id
     * @return array
     */
    function do_sync($refund_id) {
        $refund_info = $this->get_ag_by_refund_id($refund_id);
        if (empty($refund_info)) {
            return $this->format_ret(-1, '', '单据不存在！');
        }
        if ($refund_info['ag_status'] != 3) {
            return $this->format_ret(-1, '', '当前单据不是待推送状态！');
        }
        if (!in_array($refund_info['ag_record_type'], array(1, 2))) {
            return $this->format_ret(-1, '', '关联单据类型异常！');
        }
        if (empty($refund_info['shop_code'])) {
            return $this->format_ret(-1, '', '无ag店铺！');
        }
        if ($refund_info['ag_record_type'] == 2) {
            $ret = $this->sell_record_sync($refund_info); //订单取消发货接口
        } else {
            $ret = $this->sell_return_sync($refund_info); //退单的确认入库接口
        }
        return $ret;

    }

    /**
     * 订单取消接口
     * @param $refund_info
     * @return array
     */
    function sell_record_sync($refund_info) {
        $out_param = array(
            'oid' => $refund_info['oid'],
            'refund_id' => $refund_info['refund_id'],
            'operate_time' => date('Y-m-d H:i:s'),
            'status' => $refund_info['push_val'],
            'tid' => $refund_info['tid'],
        );
        $push_val = $refund_info['push_val'] == "SUCCESS" ? "取消成功" : "取消失败";
        $obj = new TaobaoClient($refund_info['shop_code']);
        $api_result = $obj->taobao_rdc_aligenius_sendgoods_cancel(array($out_param));
        foreach ($api_result as $api_refund_id => $ret) {
            if ($ret['status'] == 1) {
                $ag_update = array(
                    'ag_status' => $this->sys_param['aligenius_refunds_check'] == 1 ? 4 : 5,
                    'push_status' => '推送成功',
                    'push_log' => '推送处理结果成功！',
                );
                //同步成功
                $action_note = "推送“{$push_val}”信息至AG，接口调用成功！";
                if ($this->sys_param['aligenius_refunds_check'] != 1) {
                    $action_note .= "未开启审核参数，将单据更新成完成状态";
                }
                $record_status = "已推送";
            } else {
                $ag_update = array(
                    'push_status' => '推送失败',
                    'push_log' => '推送处理结果失败！接口返回：' . $ret['message'],
                );
                $action_note = "推送“{$push_val}”信息至AG，接口调用失败！错误信息：{$ret['message']}";
                $record_status = "已处理，待推送";
            }
            $this->update_exp($this->table, $ag_update, array('refund_id' => $api_refund_id, 'ag_status' => 3));
            //日志
            $log = array(
                'user_code' => CTX()->get_session('user_name'),
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '推送处理结果',
                'record_status' => $record_status,
                'action_note' => $action_note,
                'refund_id' => $api_refund_id
            );
            load_model('oms/TaobaoAgLogModel')->insert($log);


            if ($ret['status'] != 1) {
                $message = "同步失败,错误信息：" . $ret['message'];
                return $this->format_ret(-1, '', $message);
            }
        }
        return $this->format_ret(1, '', '同步完成！');
    }

    /**
     * 退货入仓接口
     * @param $refund_info
     * @return array
     */
    function sell_return_sync($refund_info) {
        $api_params = array(
            'refund_id' => $refund_info['refund_id'],
            'warehouse_status' => 1,
        );
        $obj = new TaobaoClient($refund_info['shop_code']);
        $api_result = $obj->taobao_nextone_logistics_warehouse_update(array($api_params));
        foreach ($api_result as $api_refund_id => $ret) {
            if ($ret['status'] == 1) {
                $ag_update = array(
                    'ag_status' => $this->sys_param['aligenius_refunds_check'] == 1 ? 4 : 5,
                    'push_status' => '已入库',
                    'push_log' => '推送处理结果成功！',
                );
                $action_note = "推送退货入仓信息至AG，接口调用成功！";
                if ($this->sys_param['aligenius_refunds_check'] != 1) {
                    $action_note .= "未开启审核参数，将单据更新成完成状态";
                }
                $record_status = "已处理，待推送";
            } else {
                $ag_update = array(
                    'push_status' => '未入库',
                    'push_log' => '推送处理结果失败！接口返回：' . $ret['message'],
                );
                $action_note = "推送退货入仓信息至AG，接口调用失败！错误信息：{$ret['message']}";
                $record_status = "已推送";
            }
            $this->update_exp($this->table, $ag_update, array('refund_id' => $api_refund_id, 'ag_status' => 3));
            //日志
            $log = array(
                'user_code' => CTX()->get_session('user_name'),
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '推送处理结果',
                'record_status' => $record_status,
                'action_note' => $action_note,
                'refund_id' => $api_refund_id
            );
            load_model('oms/TaobaoAgLogModel')->insert($log);

            if ($ret['status'] != 1) {
                $message = "同步失败,错误信息：" . $ret['message'];
                return $this->format_ret(-1, '', $message);
            }
        }
        return $this->format_ret(1, '', '同步完成！');
    }

    /**
     * 审核
     * @param $refund_id
     * @return array
     */
    function do_check($refund_id) {
        if ($this->sys_param['aligenius_refunds_check'] != 1) {
            return $this->format_ret(-1, '', '未开启审核参数！');
        }
        $refund_info = $this->get_ag_by_refund_id($refund_id);
        if (empty($refund_info)) {
            return $this->format_ret(-1, '', '单据不存在！');
        }
        if ($refund_info['ag_status'] != 4) {
            return $this->format_ret(-1, '', '当前单据不是已推送状态！');
        }
        if (!in_array($refund_info['ag_record_type'], array(1, 2))) {
            return $this->format_ret(-1, '', '关联单据类型异常！');
        }
        if (empty($refund_info['shop_code'])) {
            return $this->format_ret(-1, '', '无ag店铺！');
        }

        if ($refund_info['ag_record_type'] == 2) {//关联订单类型
            $msg = $refund_info['push_val'] == 'SUCCESS' ? "取消发货成功" : "取消发货失败";
        } else {//关联退单类型
            $msg = "退货入库";
        }
        //组装参数
        $out_param = array(
            'refund_id' => $refund_info['refund_id'],
            'tid' => $refund_info['tid'],
            'oid' => $refund_info['oid'],
            'status' => 'SUCCESS',
            'msg' => $msg,
            'operate_time' => date('Y-m-d H:i:s'),
        );
        $obj = new TaobaoClient($refund_info['shop_code']);
        $api_result = $obj->taobao_rdc_aligenius_refunds_check(array($out_param));
        foreach ($api_result as $api_refund_id => $ret) {
            if ($ret['status'] == 1) {
                $ag_update = array(
                    'ag_status' => 5,
                    'push_log' => '推送审核状态成功',
                );
                $record_status = '已审核';
                $action_note = "推送审核状态接口，接口调用结果成功，退单处理完成";
            } else {
                $ag_update = array(
                    'push_log' => '推送审核状态失败,接口返回：' . $ret['message'],
                );
                $record_status = '已推送';
                $action_note = "推送审核状态接口，接口调用结果失败，接口返回：" . $ret['message'];
            }
            $this->update_exp($this->table, $ag_update, array('refund_id' => $api_refund_id, 'ag_status' => 4));
            //日志
            $log = array(
                'user_code' => CTX()->get_session('user_name'),
                'action_time' => date('Y-m-d H:i:s'),
                'action_name' => '推送审核状态',
                'record_status' => $record_status,
                'action_note' => $action_note,
                'refund_id' => $api_refund_id
            );
            load_model('oms/TaobaoAgLogModel')->insert($log);
            if ($ret['status'] != 1) {
                $message = "审核失败,错误信息：" . $ret['message'];
                return $this->format_ret(-1, '', $message);
            }
        }

        return $this->format_ret(1, '', '审核完成！');
    }
    
    /**
     * 定时器上传未发货订单取消结果 
     */
    function cli_aligenius_sendgoods_cancel() {
        $sql = "SELECT * FROM api_taobao_ag WHERE ag_status = 3 AND ag_record_type = 2 ";
        //增值服务
        $sql .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $refund_cancel = $this->db->get_all($sql);
        foreach($refund_cancel as $val) {
            if (empty($val['shop_code'])) {
                return $this->format_ret(-1, '', '无ag店铺！');
            }
            $ret = $this->sell_record_sync($refund_info); //订单取消发货接口
        }
        return $this->format_ret(1);
    }
    
    /**
     * 定时器上传已发货退货入库结果 
     */
    function cli_aligenius_warehouse_update() {
        $sql = "SELECT * FROM api_taobao_ag WHERE ag_status = 3 AND ag_record_type = 1 ";
        //增值服务
        $sql .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $refund_cancel = $this->db->get_all($sql);
        foreach($refund_cancel as $val) {
            if (empty($val['shop_code'])) {
                return $this->format_ret(-1, '', '无ag店铺！');
            }
            $ret = $this->sell_return_sync($refund_info); //退单的确认入库接口
        }
        return $this->format_ret(1);
    }
    /**
     * 定时器上传审核信息 
     */
    function cli_aligenius_upload_check() {
        if ($this->sys_param['aligenius_upload_check'] != 1) {
            return $this->format_ret(-1, '', '未开启自动审核参数！');
        }
        $sql = "SELECT refund_id FROM api_taobao_ag WHERE ag_status = 4 ";
        //增值服务
        $sql .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $refund_check = $this->db->get_all($sql);
        foreach($refund_check as $val) {
            $this->do_check($val['refund_id']);
        }
        return $this->format_ret(1);
    }

}
