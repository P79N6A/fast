<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");
require_lib('util/oms_util', true);
class ValueModel extends TbModel {

    function get_table() {
        return 'osp_valueserver';
        //数据库修改了value_num的字段类型，记得提交
    }

    /*
     * 获取增值服务信息方法
     */

    function get_valueserver($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        $sql_value = array();
        //关联增值产品搜索条件
        if (isset($filter['value_cp_id']) && $filter['value_cp_id'] != '') {
            $sql_main .= " AND value_cp_id = " . $filter['value_cp_id'];
        }
        //产品版本
        if (isset($filter['value_cp_version']) && $filter['value_cp_version'] != '') {
            $sql_main .= " AND value_cp_version = " . $filter['value_cp_version'];
        }
        
        //类别
        if (isset($filter['value_cat']) && $filter['value_cat'] != '') {
            $sql_main .= " AND value_cat = " . $filter['value_cat'];
        }
        //发布状态
        if (isset($filter['value_publish_status']) && $filter['value_publish_status'] != '') {
            $sql_main .= " AND value_publish_status =:value_publish_status ";
            $sql_value[':value_publish_status'] = $filter['value_publish_status'];
        }
        //状态
        if (isset($filter['value_enable']) && $filter['value_enable'] != '') {
            $sql_main .= " AND value_enable = " . $filter['value_enable'];
        }
        $select = '* ';
        $data = $this->get_page_from_sql($filter, $sql_main,$sql_value, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        //处理关联代码表
        filter_fk_name($ret_data['data'], array('value_cat|osp_valueserver_cat', 'value_cp_id|osp_chanpin', 'value_require_version|osp_chanpin_version'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('value_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('value_cp_id|osp_chanpin', 'value_require_version|osp_chanpin_version'));

        return $this->format_ret($ret_status, $data);
    }

    //更新状态（启用/禁用）
    function update_value_enable($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('value_enable' => $active), array('value_id' => $id));
        return $ret;
    }

    /*
     * 添加增值服务
     */

    function insert($values) {
        $status = $this->valid($values);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($values['value_name'], 'value_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');

        $ret = $this->is_exists($values['value_code'], 'value_code');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');
                //处理LOGO
        $logofile = json_decode($values['pic_path'],true);
        if (!empty($logofile)) {
            $values['pic_path'] = $logofile[0][0];
        }else{
            $values['pic_path'] = '';
        }
        return parent::insert($values);
    }

    /*
     * 修改增值信息。
     */

    function update($values, $id) {
        $status = $this->valid($values, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('value_id' => $id));
        $ret = parent::update($values, array('value_id' => $id));
        return $ret;
    }

    /*
     * 服务器端验证提交的数据是否重复
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['value_code']) || !valid_input($data['value_code'], 'required')))
            return VL_ERROR_CODE;
        if (!isset($data['value_name']) || !valid_input($data['value_name'], 'required'))
            return VL_ERROR_NAME;
        return 1;
    }

    private function is_exists($value, $field_name = 'value_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    //获取平台店铺类型
    function getvalue_type($cpid) {
        $sql_main = "SELECT vc_id,vc_name from osp_valueserver_category where vc_cp_id=:cp_id";
        $sql_values[':cp_id'] = $cpid;
        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //获取增值订购明细
    function get_valueserver_detail($id) {
        $sql_main = "FROM osp_valueserver_detail  WHERE 1";
        if (isset($id['value_id']) && $id['value_id'] != '') {
            $sql_main .= " AND value_id = '" . $id['value_id'] . "'";
        }
        //构造排序条件
        $select = '*';
        $data = $this->get_page_from_sql('', $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //获取增值服务明细
    function get_value_func($id) {
        $params = array('vd_id' => $id);
        $data = $this->db->create_mapper('osp_valueserver_detail')->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //基础数据-平台列表-编辑平台店铺类型
    function update_vfunc($vfunc, $id) {
        if (isset($vfunc)) {
//            $sql_main = "update osp_valueserver_detail set vd_busine_id='" . $vfunc['vd_busine_id'] . "',"
//                    . "vd_busine_code='" . $vfunc['vd_busine_code'] . "' ,"
//                    . "vd_busine_type='" . $vfunc['vd_busine_type'] . "' WHERE vd_id=:id";
//            $sql_values[':id'] = $id;
//            $this->db->query($sql_main, $sql_values);

            $detail_update = array();
            $detail_update['vd_busine_id'] = $vfunc['vd_busine_id'];
            $detail_update['vd_busine_code'] = $vfunc['vd_busine_code'];
            $detail_update['vd_busine_type'] = $vfunc['vd_busine_type'];
            $detail_update['remark'] = $vfunc['remark'];
            $ret = $this->update_exp('osp_valueserver_detail', $detail_update, array('vd_id' => $id));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '更新失败！');
            }
            return $this->format_ret("1", '', '更新成功');
        } else {
            return $this->format_ret("-1", '', '更新失败');
        }
    }

    //基础数据-平台列表-添加平台店铺类型
    function insert_vfunc($vfunc) {
        if (isset($vfunc)) {
//            $sql_main = "insert into osp_valueserver_detail (value_id,vd_busine_id,vd_busine_code,vd_busine_type) "
//                    . "value  ('{$vfunc['value_id']}','{$vfunc['vd_busine_id']}','{$vfunc['vd_busine_code']}','{$vfunc['vd_busine_type']}')";
//            $this->db->query($sql_main);

            $detail = array();
            $detail['value_id'] = $vfunc['value_id'];
            $detail['vd_busine_id'] = $vfunc['vd_busine_id'];
            $detail['vd_busine_code'] = $vfunc['vd_busine_code'];
            $detail['vd_busine_type'] = $vfunc['vd_busine_type'];
            $detail['remark'] = $vfunc['remark'];
            $ret = $this->insert_exp('osp_valueserver_detail', $detail);
            if ($ret['status'] != 1) {
                return $this->format_ret("1", '', '更新失败!');
            }
            return $this->format_ret("1", '', '更新成功');
        } else {
            return $this->format_ret("-1", '', '更新失败');
        }
    }

    function delete_vfunc($id) {
        if (isset($id)) {
            $sql_main = "delete from osp_valueserver_detail  WHERE vd_id=:id";
            $sql_values[':id'] = $id;
            $this->db->query($sql_main, $sql_values);
            return $this->format_ret("1", $data, '删除成功');
        } else {
            return $this->format_ret("-1", '', '删除失败');
        }
    }

    /**
     * 服务订购查询接口数据
     * @param $filter
     * @return array
     */
    function get_value_by_page($filter) {
        foreach ($filter as $key => $value) {
            $filter[$key] = trim($value);
        }
        $sql_main = "FROM {$this->table}  WHERE 1 AND value_publish_status=1 AND value_enable=1 ";
        $sql_value = array();
        //标签类型
        if (empty($filter['tabs_type'])) {
            $filter['tabs_type'] = 'tabs_all';
        }
        if (isset($filter['tabs_type']) && $filter['tabs_type'] != '') {
            $vc_code = '';
            switch ($filter['tabs_type']) {
                case 'tabs_all':
                    break;
                case 'tabs_source':
                    $vc_code = '001';
                    break;
                case 'tabs_store':
                    $vc_code = '002';
                    break;
                case 'tabs_erp':
                    $vc_code = '003';
                    break;
            }
            if ($vc_code != '') {
                $value_cat = oms_tb_val('osp_valueserver_category', 'vc_id', array('vc_code' => $vc_code));
                $sql_main .= ' AND value_cat=:value_cat ';
                $sql_value[':value_cat'] = $value_cat;
            }
        }
        //平台名称
        if (isset($filter['value_name']) && $filter['value_name'] != '') {
            $key = 'value_name';
            $value_name = $this->arr_to_like_sql_value(array($filter['value_name']), $key, $sql_value);
            $sql_main .= " AND {$value_name} ";
        }
        //接口类别
        if (isset($filter['value_cat']) && $filter['value_cat'] != '') {
            $sql_main .= " AND value_cat = :value_cat ";
            $sql_value[':value_cat'] = $filter['value_cat'];
        }
        $sql_main .= " ORDER BY value_sort_order DESC";//发布顺序
        $select = '* ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        foreach ($data['data'] as &$value) {
            if (!empty($value['pic_path'])) {
                $value['pic_path_img'] = "<img width='48px' height='48px' src='{$value['pic_path']}' />";
            } else {
                $value['pic_path_img'] = "";
            }
            //是否订购，是否到期
            $expired_status=$this->server_expired_status($value['value_id'],$filter['kh_id']);
            if (!$expired_status) {
                $value['operate_status'] = "<button class='print_type_btn' onclick=add_server_order(" . $value['value_id'] . ")>立即订购</button>" . "<button class='print_type_btn' onclick=add_shopping_cat(" . $value['value_id'] . ")>加入购物车</button>";
            } else {
                $value['operate_status'] = '已订购';
            }
            if (empty($value['function_application'])) {
                $value['dock_function'] = $value['value_desc'];
            } else {
                $value['function_application']=$this->deal_path($value['function_application']);
                $value['dock_function'] = $value['value_desc']."，详细请点击<a target='_blank' href='" . $value['function_application'] . "'>这里</a>";
            }
            if(!empty($value['source_path'])){
                $value['source_path']=$this->deal_path($value['source_path']);
                $value['value_name']="<a target='_blank' href='" . $value['source_path'] . "'>".$value['value_name']."</a>";
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 添加购物车
     * @param $params
     * @return array
     */
    function add_shopping_cart($params) {
        $value_info = $this->get_row(array('value_id' => $params['value_id']));
        if($value_info['status'] != 1){
            return $this->format_ret('-1','','添加失败，无服务信息！');
        }
        $server_data=$value_info['data'];
        //查询购物车是否已添加服务
        $sql_shopping="SELECT 1 FROM server_shopping_cart WHERE kh_id=:kh_id AND value_code=:value_code";
        $sql_value[':kh_id']=$params['kh_id'];
        $sql_value[':value_code']=$server_data['value_code'];
        $shopping_cart=$this->db->get_row($sql_shopping,$sql_value);
        if(!empty($shopping_cart)){
            return $this->format_ret('-1','','该服务已添加购物车！');
        }
        //检查该服务是否含有未付款的订单
        $ret = $this->check_server_order_status($params['kh_id'], $server_data);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $params['price'] = (empty($server_data['value_price'])) ? 0.00 : $server_data['value_price'];
        //插入数据
        $insert_data = array(
            'value_code' => $server_data['value_code'],
            'num' => $params['num'],
            'price' => $server_data['value_price'],
            'money' => $params['num']*$params['price'],
            'create_person' => $params['user_code'],
            'kh_id' => $params['kh_id'],
            'create_time' => date('Y-m-d H:i:s'),
        );
        //更新
        $update_str = "money = (num +VALUES(num))*price,num = num +VALUES(num)";
        $ret = $this->insert_multi_duplicate('server_shopping_cart', array($insert_data), $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '添加失败！');
        }
        return $this->format_ret('1', '', '添加成功！');
    }

    /**
     * 立即订购
     * @param $params
     * @return array
     */
    function add_server_order($params) {
        $value = $this->get_row(array('value_id' => $params['value_id']));
        if ($value['status'] != 1) {
            return $this->format_ret('-1', '', '增值服务不存在！');
        }
        $value_server = $value['data'];
        //检查是否含有未付款的订单
        $ret=$this->check_server_order_status($params['kh_id'],$value_server);
        if($ret['status']!=1){
            return $ret;
        }
        $new_order_code = create_fast_bill_sn('ZZDGBH');
        $this->begin_trans();
        try {
            //插入订单主表
            $order_main = array(
                'order_code' => $new_order_code,
                'server_num' => 1,
                'server_money' => $value_server['value_price']-$value_server['discount'],
                'order_money'=>$value_server['value_price'],
                'discount'=>$value_server['discount'],
                'val_cp_id'=>'21',
                'kh_id' => $params['kh_id'],
                'val_channel_id'=>'86'//销售渠道默认自营
            );
            //插入订单明细表
            $order_detail = array(
                'val_kh_id' => $params['kh_id'],
                'val_cp_id' => $value_server['value_cp_id'],
                'val_serverid' => $value_server['value_id'],
                'val_standard_price' => $value_server['value_price'],
                'val_cheap_price' => $value_server['discount'],
                'val_actual_price' => $value_server['value_price']-$value_server['discount'],
                'val_hire_limit' => $value_server['value_cycle'],
                'server_num' => 1,
                'server_code' => $value_server['value_code'],
                'val_num' => create_fast_bill_sn('ZZDGBH'),
                'order_code' => $new_order_code,
                'val_channel_id'=>'86'//销售渠道默认自营
            );
            //生成订单
            $ret = $this->add_order_info($order_main, array($order_detail));
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            //日志
            load_model('market/ValueorderMainLogModel')->log($params['user_code'], $ret['data'], $new_order_code, $params['kh_id'], '新增', '未支付','',0);
            $this->commit();
      //      return $this->format_ret('1', '', '添加成功！');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加失败:' . $e->getMessage());
        }
         //支付宝充值
        $pay_params = array(
            'get_url' => $params['get_url'],
            'id' => $ret['data'],
            'kh_id' => $params['kh_id'],
            'user_code' => $params['user_code'],
        );
        $pay_ret = load_model('market/ValueorderModel')->server_order_ali_pay($pay_params);
        if ($pay_ret['status'] != 1) {
            return $this->format_ret(-1, '', '已生成订单，支付宝支付失败！');
        }
        return $pay_ret;
    }

    /**验证是否满足支付条件
     * @param $kh_id
     * @param $value_server
     */
    function check_server_order_status($kh_id, $value_server) {
        $sql = "SELECT r1.order_code from osp_valueorder_main r1 INNER JOIN  osp_valueorder r2 ON r1.order_code=r2.order_code  
                WHERE r2.val_serverid=:val_serverid  AND r1.kh_id=:kh_id  AND r1.pay_status=0 ";
        $old_order_code = $this->db->get_row($sql, array(':val_serverid' => $value_server['value_id'], ':kh_id' => $kh_id));
        if (!empty($old_order_code['order_code'])) {
            return $this->format_ret('-1', '', '该服务已生成订单，请先付款：' . $old_order_code['order_code']);
        }
        return $this->format_ret('1', '', '');
    }



    /**获取购物车数据
     * @param $params
     * @return mixed
     */
    function get_shopping_cart($params) {
        $sql_value = array();
        $sql = "SELECT * FROM server_shopping_cart WHERE kh_id=:kh_id ";
        $sql_value[':kh_id'] = $params['kh_id'];
        $result = $this->db->get_all($sql, $sql_value);
        if(empty($result)){
            return $this->format_ret('-1','','购物车为空！');
        }
        //付款总计
        $money_all = 0;
        //优惠总计
        $discount_all = 0;
        //实际付款总金额
        $pay_money_all = 0;
        foreach ($result as &$val) {
            $server_info = $this->get_row(array('value_code' => $val['value_code']));
            $server_data = $server_info['data'];
            $money_all += $val['money'];
            $val['discount'] = empty($server_data['discount']) ? '0.00' : $server_data['discount'];
            $val['pay_money'] = $val['money'] - $val['discount'];
            $discount_all += $val['discount'];
            $val['value_name'] = $server_data['value_name'];
            $val['value_cycle']=$server_data['value_cycle'];
        }
        $pay_money_all=$money_all-$discount_all;
        $shopping_cart_info['cart_data'] = $result;
        $shopping_cart_info['money_all'] = $money_all;
        $shopping_cart_info['discount_all'] = $discount_all;
        $shopping_cart_info['pay_money_all'] = $pay_money_all;
        return $this->format_ret('1',$shopping_cart_info,'');
    }

    /**购物车立即支付(下单，支付)
     * @return array
     */
    function immediate_pay($params) {
        $sql_value = array();
        //处理购物车数据
        $sql_shopping = "SELECT * FROM server_shopping_cart WHERE kh_id=:kh_id";
        $sql_value[':kh_id'] = $params['kh_id'];
        $shopping_info = $this->db->get_all($sql_shopping, $sql_value);
        if(empty($shopping_info)){
            return $this->format_ret(-1, '', '请添加购物车！');
        }
        //订单总数量
        $server_num_all = 0;
        //订单应付金额（总金额-优惠）
        $order_money_all=0;
        $discount_all=0;
        //服务订购编号
        $new_order_code = create_fast_bill_sn('ZZDGBH');
        $error_msg=array();
        $expired_msg=array();
        foreach ($shopping_info as $key => $val) {
            //服务总数量
            $server_num_all += $val['num'];
            //服务信息
            $server = $this->get_row(array('value_code' => $val['value_code']));
            $server_data = $server['data'];
            $ret = $this->check_server_order_status($params['kh_id'], $server_data);
            if ($ret['status'] != 1) {
                $error_msg[] = $server_data['value_name'];
                continue;
            }
            //购买没有到期,不需要购买
            $ret = $this->server_expired_status($server_data['value_id'], $params['kh_id']);
            if ($ret) {
                $expired_msg[] = $server_data['value_name'];
                continue;
            }
            $server_money = $val['money'] - $server_data['discount'];
            $order_money_all+=$val['money'];
            $discount_all+=$server_data['discount'];
            //订单明细数据
            $order_detail[] = array(
                'val_kh_id' => $params['kh_id'],
                'val_cp_id' => $server_data['value_cp_id'],
                'val_serverid' => $server_data['value_id'],
                'val_standard_price' => $val['money'],
                'val_cheap_price' => $server_data['discount'],
                'val_actual_price' => $server_money,
                'val_hire_limit' => $server_data['value_cycle'],
                'server_num' => $val['num'],
                'server_code' => $val['value_code'],
                'val_num' => create_fast_bill_sn('ZZDGBH'),
                'order_code' => $new_order_code,
                'val_channel_id'=>'86'//销售渠道默认自营
            );
        }
        if (!empty($error_msg)) {
            $msg = implode(',', $error_msg);
            return $this->format_ret('-1','',$msg.' 已生成未支付订单！');
        }
        if (!empty($expired_msg)) {
            $msg = implode(',', $expired_msg);
            return $this->format_ret('-1','',$msg.' 服务没有到期，无需重新购买！');
        }
        $this->begin_trans();
        try {
            $order_main = array(
                'order_code' => $new_order_code,
                'server_num' => $server_num_all,
                'server_money' => $order_money_all-$discount_all,
                'kh_id' => $params['kh_id'],
                'server_remind' => $params['server_remind'],
                'val_channel_id'=>'86',//销售渠道默认自营
                'order_money'=>$order_money_all,
                'discount'=>$discount_all,
                'val_cp_id'=>'21',
            );
            //生成订单
            $ret = $this->add_order_info($order_main, $order_detail);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            $pid=$ret['data'];
            //清除购物车数据
            $ret = $this->delete_exp('server_shopping_cart', array('kh_id' => $params['kh_id']));
            if (!$ret) {
                $this->rollback();
                return $this->format_ret(-1, '', '清除购物车失败！');
            }
            //日志
            load_model('market/ValueorderMainLogModel')->log($params['user_code'], $pid, $new_order_code, $params['kh_id'], '新增', '未支付','',0);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加失败:' . $e->getMessage());
        }
        //支付宝充值
        $pay_params = array(
            'get_url' => $params['get_url'],
            'id' => $pid,
            'kh_id' => $params['kh_id'],
            'user_code' => $params['user_code']
        );
        $ret = load_model('market/ValueorderModel')->server_order_ali_pay($pay_params);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '已生成订单，支付宝支付失败！');
        }
        return $ret;
    }

    /**插入订单主表，明细表
     * @param $order_main
     * @param $order_detail
     * @return array
     */
    function add_order_info($order_main, $order_detail) {
        $order_date=date('Y-m-d H:i:s');
        //插入订单主表
        $main_order = array(
            'order_code' => $order_main['order_code'],
            'server_num' => $order_main['server_num'],
            'server_money' => $order_main['server_money'],
            'kh_id' => $order_main['kh_id'],
            'val_channel_id' => $order_main['val_channel_id'],
            'server_remind' => isset($order_main['server_remind']) ? $order_main['server_remind'] : 1,
            'val_orderdate' => $order_date,
            'order_money'=>$order_main['order_money'],
            'discount'=>$order_main['discount'],
            'val_cp_id'=>$order_main['val_cp_id']
        );
        $ret = $this->insert_exp('osp_valueorder_main', $main_order);
        $pid=$ret['data'];
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入服务订购主表失败！');
        }
        foreach ($order_detail as $detail) {
            $detail_order[] = array(
                'pid'=>$pid,
                'val_kh_id' => $detail['val_kh_id'],
                'val_cp_id' => $detail['val_cp_id'],
                'val_serverid' => $detail['val_serverid'],
                'val_standard_price' => $detail['val_standard_price'],
                'val_cheap_price' => $detail['val_cheap_price'],
                'val_actual_price' => $detail['val_actual_price'],
                'server_num' => $detail['server_num'],
                'server_code' => $detail['server_code'],
                'val_num' => $detail['val_num'],
                'order_code' => $detail['order_code'],
                'val_orderdate' => $order_date,
                'val_hire_limit'=>$detail['val_hire_limit'],
                'val_channel_id' => $detail['val_channel_id'],
            );
        }
        //插入明细表
        $ret = $this->insert_multi_exp('osp_valueorder', $detail_order);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入服务订购明细表失败！');
        }
        return  $this->format_ret('1', $pid, '');
    }

    /**删除购物车数据
     * @param $where
     * @return array
     */
    function delete_shopping_cart($params) {
        $ret = $this->delete_exp('server_shopping_cart', array('shopping_id' => $params['shopping_id']));
        if ($ret) {
            return $this->format_ret('1', '', '删除成功！');
        }
        return $this->format_ret('-1', '', '删除失败！');
    }

    /**更新产品发布
     * @param $type
     * @param $value_id
     * @return array
     */
    function update_value_publish($type, $value_id) {
        $value = array(
            'value_publish_status' => $type,
            'value_publish_data'=>date('Y-m-d H:i:s')//发布时间
        );
        $ret = parent::update($value,array('value_id'=>$value_id));
        if($ret['status']!=1){
            return $this->format_ret('-1','','修改失败！');
        }
        return $this->format_ret('1','','修改成功！');
    }

    /**判断服务是否到期
     * @param $server_code
     * @param $kh_id
     * @return bool
     */
    function server_expired_status($server_id, $kh_id) {
        $sql = "SELECT vra_enddate FROM osp_valueorder_auth WHERE vra_server_id=:vra_server_id AND vra_kh_id=:vra_kh_id ";
        $sql_value[':vra_server_id'] = $server_id;
        $sql_value[':vra_kh_id'] = $kh_id;
        $ret = $this->db->get_row($sql, $sql_value);
        if (empty($ret)) {
            return false;
        }
        $enddate = $ret['vra_enddate'];
        $now = date('Y-m-d H:i:s', time());
        $status = ($now < $enddate) ? true : false;
        return $status;
    }


    /**查询服务
     * @param $filter
     * @return array
     */
    function get_service_goods($filter) {
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        $sql_value = array();
        $sql_main = " FROM {$this->table} rl  WHERE value_publish_status = 1 AND value_enable=1 ";
        //增值类别
        if (isset($filter['value_cat']) && $filter['value_cat'] != '') {
            $sql_main .= " AND (rl.value_cat=:value_cat )";
            $sql_values[':value_cat'] = $filter['value_cat'];
        }
        //服务名称
        if (isset($filter['value_name']) && $filter['value_name'] != '') {
            $sql_main .= " AND (rl.value_name LIKE :value_name )";
            $sql_values[':value_name'] = '%'.$filter['value_name'].'%';
        }
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $ret=$this->check_server_status($filter['kh_id']);
            if($ret['status']==1){
                if(!empty($ret['data'])){
                    $server_id_str=$this->arr_to_in_sql_value($ret['data'],'server_id',$sql_values);
                    $sql_main .= " AND (rl.value_id NOT IN ({$server_id_str}))";
                }
            }
        }
        $select = "rl.*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        foreach ($data['data'] as &$value) {
            if (empty($value['function_application'])) {
                $value['dock_function'] = $value['value_desc'];
            } else {
                $value['function_application']=$this->deal_path($value['function_application']);
                $value['dock_function'] = $value['value_desc'] . "，详细请点击<a target='_blank' href='" . $value['function_application'] . "'>这里</a>";
            }
            if (!empty($value['source_path'])) {
                $value['source_path']=$this->deal_path($value['source_path']);
                $value['value_name'] = "<a target='_blank' href='" . $value['source_path'] . "'>" . $value['value_name'] . "</a>";
            }
        }
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

//处理网址
    function deal_path($path) {
        if (preg_match("/^(http:\/\/|https:\/\/|HTTP:\/\/|HTTPS:\/\/).*$/", $path)) {
            return $path;
        } else {
            return 'http://' . $path;
        }
    }


    function check_server_status($kh_id) {
        //已生成订单未支付
        $sql = "SELECT val_serverid FROM osp_valueorder WHERE 1 AND val_kh_id=:val_kh_id AND val_pay_status=0 ";
        $sql_value[':val_kh_id'] = $kh_id;
        $ret = $this->db->get_all($sql, $sql_value);
        $server_id_arr = array();
        if (!empty($ret)) {
            $server_id_arr = array_column($ret, 'val_serverid');
        }
        //已支付未到期
        $sql = "SELECT vra_server_id,vra_enddate FROM osp_valueorder_auth WHERE vra_kh_id=:val_kh_id";
        $ret = $this->db->get_all($sql, $sql_value);
        if (!empty($ret)) {
            $time = date('Y-m-d H:i:s', time());
            foreach ($ret as $value) {
                if ($time < $value['vra_enddate']) {
                    $server_id_arr[] = $value['vra_server_id'];
                }
            }
        }
        $server_id_arr=array_filter($server_id_arr);//去空
        if (empty($server_id_arr)) {
            return $this->format_ret('-1', '', '');
        }
        $server_id_arr = array_unique($server_id_arr);
        return $this->format_ret('1', $server_id_arr, '');
    }


}
