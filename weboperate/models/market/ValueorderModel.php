<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_lib('apiclient/AlipaymClient', true);
require_lang('base');
require_model('tb/TbModel');
require_lib("comm_util");
require_lib('util/oms_util', true);

class ValueorderModel extends TbModel {

    private $pay_status = array(
        '0' => '未支付',
        '1' => '已支付',
    );
    private $complete_status = array(
        '0' => '未完成',
        '1' => '已完成',
    );

    function get_table() {
        return 'osp_valueorder';
    }

    /*
     * 获取增值服务信息方法
     */

    function get_valorder_info($filter) {
        $sql_main = "FROM {$this->table} a LEFT JOIN osp_valueserver b ON a.val_serverid = b.value_id WHERE 1";

        /** 根据客户名称查询客户id start */
        if (!empty($filter['customer'])) {
            global $context;
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE \"%{$filter['customer']}%\"";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $context->db->get_all($sql));
        }
        /** 根据客户名称查询客户id end */
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            if(is_array($filter['kh_id'])){
                $sql_main .= ' AND a.val_kh_id IN ("' . implode('","', $filter['kh_id']) . '")';
            }else{
                $sql_main .= ' AND a.val_kh_id=:val_kh_id';
                $sql_value[':val_kh_id'] = $filter['kh_id'];
            }
        }

        //关联产品搜索条件
        if (isset($filter['val_cp_id']) && $filter['val_cp_id'] != '') {
            $sql_main .= " AND a.val_cp_id =" . $filter['val_cp_id'];
        }
        //订单号
        if (isset($filter['order_code']) && $filter['order_code'] != '') {
            $sql_main .= " AND a.order_code =:order_code";
            $sql_value[':order_code'] = $filter['order_code'];
        }
        //类别
        if (isset($filter['value_cat']) && $filter['value_cat'] != '') {
            $sql_main .= " AND b.value_cat = " . $filter['value_cat'];
        }
        //pid
       if (isset($filter['pid']) && $filter['pid'] != '') {
            $sql_main .= " AND a.pid =:pid";
            $sql_value[':pid'] = $filter['pid'];
        }
        //排序条件
        $sql_main .= " order by a.val_num desc";
        $select = 'a.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        $i = 1;
        foreach ($data['data'] as &$value) {
            $value['order_sort'] = $i++;
//            if ($value['val_pay_status'] == 1) {
//                $value['end_time'] = date('Y-m-d H:i:s', strtotime("+" . $value['val_hire_limit'] . 'month', strtotime($value['val_paydate'])));
//            } else {
//                $value['end_time'] = '';
//            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('val_kh_id|osp_kh', 'val_serverid|osp_valueserver', 'val_channel_id|org_channel', 'val_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('val_num' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('val_kh_id|osp_kh', 'val_serverid|osp_valueserver', 'val_channel_id|org_channel', 'val_seller|osp_user_id'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加增值订购
     */

    function insert($vorders) {
        if (isset($vorders)) {
            $vorders['val_num'] = create_fast_bill_sn('ZZDGBH');
            return $ret = parent::insert($vorders);
        }
    }

    /*
     * 添加增值订购
     */

    public function insertWithoutNum($vorders) {
        return $ret = parent::insert($vorders);
    }

    /**
     * 订单明细变更
     * @param array $order
     */
    public function valueorderEditDetail($detail) {
        $sql1 = "SELECT SUM(`vs_cheap_price`) AS `cheap`, SUM(`vs_actual_price`) AS `actual` "
                . "FROM `osp_valueorder_valueserver` "
                . "WHERE `vs_val_num` = '{$detail['vs_val_num']}'";
        $get = array_map('floatval', $this->db->getRow($sql1));
        $total = $get['cheap'] + $get['actual'];
        $sql = "UPDATE `{$this->table}` "
                . "SET `val_standard_price` = {$total}, "
                . "`val_cheap_price` = {$get['cheap']}, "
                . "`val_actual_price` = {$get['actual']} "
                . "WHERE `val_num` = '{$detail['vs_val_num']}'";
        $this->query($sql);
    }

    /*
     * 修改增值订购信息。
     */

    function update($vorders, $DJBH) {
        if (isset($vorders) && isset($DJBH)) {
            $ret = parent::update($vorders, array('val_num' => $DJBH));
            return $ret;
        }
    }

    /*
     * 服务器端验证提交的数据是否重复
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['val_num']) || !valid_input($data['val_num'], 'required')))
            return PRO_ERROR_CODE;
        return 1;
    }

    private function is_exists($value, $field_name = 'val_num') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 获取订购服务订单
     * @param $filter
     * @return array
     */

    function get_server_order_list($filter) {
        $sql_main = "FROM osp_valueorder_main WHERE 1";
        $sql_value = array();
        if (empty($filter['tabs_type'])) {
            $filter['tabs_type'] = 'tabs_pay';
        }
        if (isset($filter['tabs_type']) && $filter['tabs_type'] != '') {
            switch ($filter['tabs_type']) {
                case 'tabs_all':
                    break;
                case 'tabs_pay':
                    $sql_main .= ' AND pay_status =0 ';
                    break;
                case 'tabs_complete':
                    $sql_main .= ' AND complete_status =1 ';
                    break;
                case 'tabs_remark':
                    $sql_main .= ' AND pay_status =1 AND complete_status =0 ';
                    break;
            }
        }
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= ' AND kh_id =:kh_id';
            $sql_value[':kh_id'] = $filter['kh_id'];
        } else {
            $sql_main .= ' 1=2 ';
        }
        $sql_main .= ' ORDER BY val_orderdate DESC';
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        foreach ($data['data'] as &$value) {
            $value['type'] = '订购';
            $value['status'] = $this->pay_status[$value['pay_status']] . ' ' . $this->complete_status[$value['complete_status']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 支付宝充值
     * @param $id
     * @param $get_url
     * @return array
     */

    function server_order_ali_pay($order_params) {
        $get_url = $order_params['get_url'];
        $sql = "SELECT * FROM osp_valueorder_main WHERE id=:id";
        $sql_value[':id'] = $order_params['id'];
        //获取单据信息
        $order_info = $this->db->get_row($sql, $sql_value);
        if (empty($order_info['server_money'])) {
            return $this->format_ret('-1', '', '无支付金额！');
        }
        if ($order_info['pay_status'] != 0) {
            return $this->format_ret('-1', '', '单据已支付！');
        }
        //支付流水号
        $out_trade_no = date('YmdHis') . mt_rand(000000, 999999);
        //支付宝支付配置文件
        $ali_params = require_conf('ali_pay');
        $pid = $ali_params['pid'];
        $key = $ali_params['key'];
        $p = new AlipaymClient($pid, $key);
        $return_url = $get_url . 'server_return_url.php';
        $notify_url = $get_url . 'server_notify_url.php';
        $param = array(
            'out_trade_no' => $out_trade_no,
            'subject' => '服务订购',
            'total_fee' => $order_info['server_money'],
            'payment_type' => 1,
            'body' => '支付宝支付',
            'return_url' => $return_url,
            'notify_url' => $notify_url,
        );
        //返回支付URL
        $url = $p->create_direct_pay_by_user($param);
        //插入支付流水表
        $pay_params = array(
            'pay_out_trade_no' => $out_trade_no,
            'pay_total_fee' => $order_info['server_money'],
            'pay_begin_time' => date('Y-m-d H:i:s'),
            'order_code' => $order_info['order_code'],
            'kh_id' => $order_params['kh_id'],
            'user_code' => $order_params['user_code']
        );
        $ret = $this->insert_exp('server_pay_serial', $pay_params);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入支付流水表失败！');
        }
        return $this->format_ret('1', $url, $out_trade_no);
    }

    /**
     * 处理支付结果
     * @param $filter
     * @return array
     */
    function server_pay_handle_info($filter) {
        $sql = "SELECT * FROM server_pay_serial WHERE pay_out_trade_no=:pay_out_trade_no";
        $sql_value[':pay_out_trade_no'] = $filter['out_trade_no']; //流水号
        $serial_data = $this->db->get_row($sql, $sql_value);
        //支付宝支付配置文件
        $ali_params = require_conf('ali_pay');
        $pid = $ali_params['pid'];
        $key = $ali_params['key'];
        $p = new AlipaymClient($pid, $key);
        //验证
        $status = $p->check_notify_data($filter);
        if ($status) {
            $this->begin_trans();
            try {
                $arr = array(
                    'alipay_trade_no' => $filter['trade_no'], //支付宝交易号
                    'buyer_email' => $filter['buyer_email'],
                    'seller_email' => $filter['seller_email'],
                    'pay_status' => 1,
                    'pay_time' => $filter['notify_time'],
                );
                //更新支付流水表
                $this->update_exp('server_pay_serial', $arr, array('pay_out_trade_no' => $filter['out_trade_no']));
                $affect = $this->affected_rows();
                if ($affect != 1) {
                    $this->rollback();
                    return $this->format_ret('-1', '', '更新流水表失败！');
                }
                //更新单据支付状态，并且审核
                $order = array(
                    'order_code' => $serial_data['order_code'],
                    'kh_id' => $serial_data['kh_id']
                );
                $ret = $this->updata_order_pay_status($order);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $ret;
                }
                $order_main = load_model('market/ValueorderMainModel')->get_row(array('order_code' => $serial_data['order_code'], 'kh_id' => $serial_data['kh_id']));
                //日志
                $remark = '支付流水：' . $filter['trade_no'] . ',支付金额：' . $order_main['data']['server_money'];
                load_model('market/ValueorderMainLogModel')->log($serial_data['user_code'], $order_main['data']['id'], $serial_data['order_code'], $serial_data['kh_id'], '支付', '已支付', $remark, 0);
                $this->commit();
                return $this->format_ret('1', '', '更新成功！');
            } catch (Exception $e) {
                $this->rollback();
                return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
            }
        } else {
            return $this->format_ret('-1', '', '验证失败！');
        }
    }

    /**
     * 验证支付状态
     * @param $filter
     */
    function check_pay_status($filter) {
        $sql = "SELECT pay_status FROM server_pay_serial WHERE pay_out_trade_no = :pay_out_trade_no";
        $ali_pay = $this->db->get_row($sql, array(':pay_out_trade_no' => $filter['pay_out_trade_no']));
        if ($ali_pay['pay_status'] == 1) {
            return $this->format_ret('1', '', '支付成功!');
        }
        return $this->format_ret('-1', '', '支付失败!');
    }

    /**
     * 更新主单，明细支付状态，同时审核明细
     * @param $order
     * @return array
     */

    function updata_order_pay_status($order,$pay_date) {
        $pay_date = (empty($pay_date)) ? date('Y-m-d H:i:s') : $pay_date;
        $main_data = array(
            'pay_status' => 1,
            'pay_date' => $pay_date,
        );
        //更新主单
        $ret = $this->update_exp('osp_valueorder_main', $main_data, array('order_code' => $order['order_code'], 'kh_id' => $order['kh_id']));
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '更新主单失败！');
        }
        //审核
        $sql = "SELECT * FROM osp_valueorder WHERE order_code=:order_code AND val_kh_id=:kh_id";
        $sql_value[':order_code'] = $order['order_code'];
        $sql_value[':kh_id'] = $order['kh_id'];
        $order_detail = $this->db->get_all($sql, $sql_value);
        foreach ($order_detail as $detail) {
            $sql_value=array();
            $sql = "SELECT vra_enddate FROM osp_valueorder_auth WHERE vra_kh_id=:vra_kh_id AND vra_server_id=:vra_server_id";
            $sql_value[':vra_kh_id'] = $detail['val_kh_id'];
            $sql_value[':vra_server_id'] = $detail['val_serverid'];
            $kh_server = $this->db->get_row($sql, $sql_value);
            $val_enddate = date('Y-m-d H:i:s', strtotime("+" . $detail['val_hire_limit'] . 'month', strtotime($pay_date)));
            if (!empty($kh_server)) {
                if ($pay_date < $kh_server['vra_enddate']) {
                    //从上次结束时间算
                    $val_enddate = date('Y-m-d H:i:s', strtotime("+" . $detail['val_hire_limit'] . 'month', strtotime($kh_server['vra_enddate'])));
                }
            }
            //更新明细表
            $update_datail_data = array(
                'val_pay_status' => 1,
                'val_paydate' => $pay_date,
                'val_enddate' => $val_enddate,//到期时间
            );
            $ret = $this->update_exp('osp_valueorder', $update_datail_data, array('val_num' => $detail['val_num']));
            if ($ret['status'] != 1) {
                return $this->format_ret('-1', '', '更新明细表失败！');
            }
            $check_update['val_check_status'] = 1;
            $check_update['val_checkdate'] = date('Y-m-d H:i:s');
            $check_status = $this->update($check_update, $detail['val_num']);
        }
        //维护客户已订购服务关系
        $ret = $this->update_kh_buy_server($order_detail, $pay_date);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //更新客户数据
        load_model('basedata/RdsDataModel')->update_kh_data($order['kh_id'], '0', 'osp_valueorder_auth');
        return $this->format_ret('1', '', '更新成功！');
    }

    /**
     * 订单评价
     * @param $params
     */

    function add_order_remark($params) {
        $update_data = array(
            'complete_status' => 1,
            'remark' => $params['remark'],
        );
        $this->begin_trans();
        $ret = $this->update_exp('osp_valueorder_main', $update_data, array('id' => $params['id']));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '评价失败！');
        }
        $detail = $this->get_all(array('pid' => $params['id']));
        $kh_id = $detail['data'][0]['val_kh_id'];
        $server_id_arr = array_column($detail['data'], 'val_serverid');
        //更新客户服务关系
        $ret = $this->update_kh_server($kh_id, $server_id_arr, $params['score']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '评价失败！');
        }
        //日志
        load_model('market/ValueorderMainLogModel')->log($params['user_code'],$detail['data'][0]['pid'], $detail['data'][0]['order_code'], $detail['data'][0]['val_kh_id'], '评价', '已完成','评价:'.$params['score'].'颗星',0);
        $this->commit();
        return $this->format_ret('1', '', '评价成功！');
    }

    /**
     * 获取订单明细
     * @param $filter
     * @return array|bool
     */

    function get_order_detail($filter) {
        $sql = "SELECT * FROM osp_valueorder WHERE val_kh_id=:kh_id AND order_code=:order_code";
        $sql_value[':kh_id'] = $filter['kh_id'];
        $sql_value[':order_code'] = $filter['order_code'];
        $ret = $this->db->get_all($sql, $sql_value);
        $i = 1;
        foreach ($ret as &$value) {
            $value['sort'] = $i++;
            $value['value_name'] = oms_tb_val('osp_valueserver', 'value_name', array('value_code' => $value['server_code']));
//            if($value['val_pay_status']==1){
//                $value['end_time']=date('Y-m-d H:i:s', strtotime("+" . $value['val_hire_limit'] . 'month', strtotime($value['val_paydate'])));
//            }else{
//                $value['end_time']='';
//            }
        }
        return $ret;
    }

    /**
     * 删除订单
     * @param type $filter
     * @return type
     */
    function do_order_delete($filter) {
        $sql = "SELECT * FROM osp_valueorder_main WHERE id=:id";
        $sql_value[':id'] = $filter['id'];
        $result = $this->db->get_row($sql, $sql_value);
        if (empty($result)) {
            return $this->format_ret('-1', '', '单据不存在!');
        }
        if ($result['pay_status'] == 1) {
            return $this->format_ret('-1', '', '订单已付款，不能删除!');
        }
        $this->begin_trans();
        try {
            $ret = $this->delete_exp('osp_valueorder_main', array('id' => $filter['id']));
            if (!$ret) {
                $this->rollback();
                return $this->format_ret('-1', '', '删除主单失败');
            }
            $where = array(
                'val_kh_id' => $result['kh_id'],
                'order_code' => $result['order_code']
            );
            $ret = $this->delete($where);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '删除明细失败！');
            }
            $this->commit();
            return $this->format_ret('1', '', '删除成功！');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败:' . $e->getMessage());
        }
    }

    function get_valorder_main_info($filter) {
        $sql_main = "FROM osp_valueorder_main a 
                LEFT JOIN osp_valueorder b ON a.order_code = b.order_code
                LEFT JOIN osp_valueserver c ON b.val_serverid = c.value_id
                WHERE 1";
        $sql_value=array();
        /** 根据客户名称查询客户id start */
        if (!empty($filter['customer'])) {
            global $context;
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE \"%{$filter['customer']}%\"";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $context->db->get_all($sql));
        }
        /** 根据客户名称查询客户id end */
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= ' AND a.kh_id IN ("' . implode('","', $filter['kh_id']) . '")';
        }
        //订单编号
        if (isset($filter['order_code']) && $filter['order_code'] != '') {
            $sql_main .= ' AND a.order_code=:order_code';
            $sql_value[':order_code'] = $filter['order_code'];
        }
        //类别
        if (isset($filter['value_cat']) && $filter['value_cat'] != '') {
            $sql_main .= " AND c.value_cat = " . $filter['value_cat'];
        }
        //排序条件
        $sql_main .= " order by a.val_orderdate desc";
        $select = 'a.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        $ret_status = OP_SUCCESS;
        foreach ($data['data'] as &$value) {
            $value['status'] = $this->pay_status[$value['pay_status']] . '<br/ >' . $this->complete_status[$value['complete_status']];
        }
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('kh_id|osp_kh', 'val_serverid|osp_valueserver', 'val_channel_id|org_channel', 'val_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 后端支付
     * @param $id
     * @return array
     */
    function update_order_pay($id,$pay_date='') {
        $sql = "SELECT * FROM osp_valueorder_main WHERE id=:id";
        $sql_value[':id'] = $id;
        $order_info = $this->db->get_row($sql, $sql_value);
        if (empty($order_info)) {
            return $this->format_ret('-1', '', '主单信息不存在！');
        }
        if ($order_info['pay_status'] != 0) {
            return $this->format_ret('-1', '', '单据已支付！');
        }
        $order_detail = $this->get_all(array('pid' => $id));
        if ($order_detail['status'] != 1) {
            return $this->format_ret('-1', '', '请添加明细！');
        }
        $this->begin_trans();
        try {
            $order = array(
                'order_code' => $order_info['order_code'],
                'kh_id' => $order_info['kh_id']
            );
            $ret = $this->updata_order_pay_status($order,$pay_date);
            if($ret['status']!=1){
                $this->rollback();
                return $ret;
            }
            //日志
            load_model('market/ValueorderMainLogModel')->log(CTX()->get_session("user_code"),$id, $order_info['order_code'], $order_info['kh_id'], '付款', '已支付');
            $this->commit();
            return $this->format_ret('1', '', '支付成功！');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '支付失败:' . $e->getMessage());
        }
    }

    /**
     * 删除明细
     * @param type $val_num
     */
    function delete_detail($val_num, $operate_type = '1') {
        $detail = $this->get_row(array('val_num' => $val_num));
        if ($detail['status'] != 1) {
            return $this->format_ret('-1', '', '明细不存在！');
        }
        if ($detail['data']['val_pay_status'] != 0) {
            return $this->format_ret('-1', '', '订单已付款，不能删除！');
        }
        $this->begin_trans();
        $ret = $this->delete(array('val_num' => $val_num));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '删除失败！');
        }
        //回写
        $ret = $this->mainWriteBack($detail['data']['pid']);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '回写失败！');
        }
        if ($operate_type == 1) {
            $ret = load_model('market/ValueorderMainLogModel')->log(CTX()->get_session("user_code"), $detail['data']['pid'], $detail['data']['order_code'], $detail['data']['val_kh_id'], '删除明细', '未支付');
        }
        $this->commit();
        return $this->format_ret('1', '', '删除成功！');
    }

    //回写数量和金额
    public function mainWriteBack($pid) {
        $sql = "update osp_valueorder_main set
                  osp_valueorder_main.server_num = (select sum(server_num) from osp_valueorder where pid = :id),
                  osp_valueorder_main.server_money = (select sum(val_actual_price) from osp_valueorder where pid = :id),
                  osp_valueorder_main.order_money = (select sum(val_standard_price) from osp_valueorder where pid = :id),
                  osp_valueorder_main.discount = (select sum(val_cheap_price) from osp_valueorder where pid = :id)
                where osp_valueorder_main.id = :id ";
        $ret = $this->query($sql, array(':id' => $pid));
        return $ret;
    }

    /**
     * 插入明细
     * @param type $params
     * @return type
     */
    function add_detail_action($params, $pid,$operator_type=1) {
        $order_main = load_model('market/ValueorderMainModel')->get_row(array('id' => $pid));
        if ($order_main['status'] != 1) {
            return $this->format_ret('-1', '', '主单不存在！');
        }
        if ($order_main['data']['pay_status'] != 0) {
            return $this->format_ret('-1', '', '该单据已付款，不能添加明细！');
        }
        $detail = array();
        $this->begin_trans();
        foreach ($params as $key => $value) {
            $detail[$key] = array(
                'val_num' => create_fast_bill_sn('ZZDGBH'),
                'val_kh_id' => $order_main['data']['kh_id'],
                'val_cp_id' => $value['value_cp_id'],
                'val_serverid' => $value['value_id'],
                'val_standard_price' => empty($value['value_price']) ? '0.00' : $value['value_price'],
                'val_cheap_price' => empty($value['discount']) ? '0.00' : $value['discount'],
                'val_actual_price' => $value['value_price'] - $value['discount'],
                'val_hire_limit' => $value['value_cycle'],
                'val_orderdate' => date('Y-m-d H:i:s'),
                'server_num' => 1,
                'server_code' => $value['value_code'],
                'order_code' => $order_main['data']['order_code'],
                'pid' => $order_main['data']['id']
            );
        }
        $update_str = "server_num = VALUES(server_num),val_standard_price = VALUES(val_standard_price),val_cheap_price = VALUES(val_cheap_price),val_cheap_price = VALUES(val_cheap_price),val_hire_limit = VALUES(val_hire_limit)";
        $ret = $this->insert_multi_duplicate('osp_valueorder', $detail, $update_str);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '插入失败！');
        }
        //回写主单
        $ret = $this->mainWriteBack($pid);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '回写主单失败！');
        }
        if ($operator_type == 1) {
            load_model('market/ValueorderMainLogModel')->log(CTX()->get_session("user_code"), $pid, $order_main['data']['order_code'], $order_main['data']['kh_id'], '新增明细', '未支付');
        }
        $this->commit();
        //添加日志
        return $this->format_ret(1, '', '插入成功！');
    }

    /**
     * 前端删除订单明细
     */
    function do_delete_order_detail($param) {
        $detail = $this->get_row(array('val_num' => $param['val_num']));
        $ret = $this->delete_detail($param['val_num'], 0);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //日志
        load_model('market/ValueorderMainLogModel')->log($param['user_code'], $detail['data']['pid'], $detail['data']['order_code'], $detail['data']['val_kh_id'], '删除明细', '未支付', '',0);
        return $ret;
    }

    /**
     * 前端添加明细
     */
    function front_add_detail_action($params) {
        $order = load_model('market/ValueorderMainModel')->get_row(array('id' => $params['id']));
        if ($order['status'] != 1) {
            return $this->format_ret('-1', '', '单据不存在！');
        }
        if ($order['data']['pay_status'] != 0) {
            return $this->format_ret('-1', '', '该单据已付款，不能添加明细！');
        }
        $this->begin_trans();
        $detail_params=$params['data'];
        $server_name=  array_column($detail_params, 'value_name');
        $server_name_str = implode(',', $server_name);
        $ret = $this->add_detail_action($detail_params, $params['id'], 0);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //日志
        $remark='添加服务：'.$server_name_str;
        load_model('market/ValueorderMainLogModel')->log($params['user_code'], $order['data']['id'], $order['data']['order_code'], $order['data']['kh_id'], '新增明细', '未支付', $remark, 0);
        $this->commit();
        return $ret;
    }
    
    /**
     * 客户已订购服务评价
     * @param type $kh_id
     * @param type $server_code_arr
     * @param type $score
     */
    function update_kh_server($kh_id, $server_id_arr, $score) {
        $server_id_arr = is_array($server_id_arr) ? $server_id_arr : array($server_id_arr);
        $sql_value = array();
        $server_id_str = $this->arr_to_in_sql_value($server_id_arr, 'server_id', $sql_value);
        $sql_value[':kh_id'] = $kh_id;
        $sql = "UPDATE osp_valueorder_auth SET score={$score} WHERE vra_kh_id=:kh_id AND vra_server_id IN ({$server_id_str})";
        $ret = $this->query($sql, $sql_value);
        return $ret;
    }

    
    function update_kh_buy_server($order_detail, $pay_date) {
        $order_detail = is_array($order_detail) ? $order_detail : array($order_detail);
        $val_server = array();
        foreach ($order_detail as $value) {
            $sql_value=array();
            $sql = "SELECT vra_id,vra_enddate FROM osp_valueorder_auth WHERE vra_kh_id=:vra_kh_id AND vra_server_id=:vra_server_id";
            $sql_value[':vra_kh_id'] = $value['val_kh_id'];
            $sql_value[':vra_server_id'] = $value['val_serverid'];
            $kh_server = $this->db->get_row($sql, $sql_value);
            if (!empty($kh_server)) {
                if ($pay_date < $kh_server['vra_enddate']) {
                    //从上次结束时间算
                    $vra_enddate = date('Y-m-d H:i:s', strtotime("+" . $value['val_hire_limit'] . 'month', strtotime($kh_server['vra_enddate'])));
                    $sql_value_auth = array();
                    $sql = "UPDATE osp_valueorder_auth SET score=0,vra_enddate='{$vra_enddate}' WHERE vra_id=:vra_id";
                    $sql_value_auth[':vra_id'] = $kh_server['vra_id'];
                    $ret = $this->query($sql, $sql_value_auth);
                    continue;
                }
            }
            //服务未订购或者已订购过期，插入
            $val_server[] = array(
                'vra_kh_id' => $value['val_kh_id'],
                'vra_cp_id' => $value['val_cp_id'],
                'vra_server_id' => $value['val_serverid'],
                'server_code' => $value['server_code'],
                'score' => 0,
                'vra_startdate' => $pay_date,
                'vra_enddate' => date('Y-m-d H:i:s', strtotime("+" . $value['val_hire_limit'] . 'month', strtotime($pay_date))),
                'val_orderdate' => $value['val_orderdate'],
                'vra_state' => '1',
                'vra_bz'=>$value['val_desc'],
            );
        }
        if (!empty($val_server)) {
            $update_str = "vra_startdate = VALUES(vra_startdate),score = VALUES(score),vra_enddate = VALUES(vra_enddate),server_code = VALUES(server_code)";
            $ret = $this->insert_multi_duplicate('osp_valueorder_auth', $val_server, $update_str);
            if ($ret['status'] != 1) {
                return $this->format_ret('-1', '', '维护客户订购服务失败！');
            }
        }
        return $this->format_ret('1', '', '维护客户订购服务成功！');
    }

}
