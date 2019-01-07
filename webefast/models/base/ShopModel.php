<?php

/**
 * 商店 相关业务
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('privilege_util', true);
require_lib('util/oms_util', true);

class ShopModel extends TbModel {

    private $user_id = 0;
    private $is_manage = -1;
    private $entity_type = array(
        0 => '直营',
        1 => '加盟',
        2 => '分销'
    );

    function get_table() {
        return 'base_shop';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = '';
        if (isset($filter['shop_channel_code']) && $filter['shop_channel_code'] == 'B') {
            $sql_join .= 'inner join base_shop_api r2 on rl.shop_code = r2.shop_code';
        }
        $sql_main = "FROM {$this->table} rl $sql_join WHERE shop_type=0 ";
        //是否分销商登录
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom['custom_code'])) {
                $sql_main .= " AND rl.custom_code = :custom_code";
                $sql_values[':custom_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
        }

        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND rl.shop_code LIKE :shop_code";
            $sql_values[':shop_code'] = '%' . $filter['shop_code'] . '%';
        }
        //店铺名称
        if (isset($filter['shop_name']) && $filter['shop_name'] != '') {
            $sql_main .= " AND rl.shop_name LIKE :shop_name";
            $sql_values[':shop_name'] = '%' . $filter['shop_name'] . '%';
        }
        //店铺昵称
        if (isset($filter['shop_user_nick']) && $filter['shop_user_nick'] != '') {
            $sql_main .= " AND rl.shop_user_nick LIKE :shop_user_nick";
            $sql_values[':shop_user_nick'] = '%' . $filter['shop_user_nick'] . '%';
        }
        //店铺id
        if (isset($filter['shop_id']) && $filter['shop_id'] != '') {
            $sql_main .= " AND rl.shop_id LIKE :shop_id";
            $sql_values[':shop_id'] = '%' . $filter['shop_id'] . '%';
        }
        //分销商代码
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $custom_code = deal_strs_with_quote($filter['custom_code']);
            $sql_main .= " AND rl.custom_code IN({$custom_code})";
        }

        //是否启用
        if (isset($filter['is_active']) && $filter['is_active'] != '') {
            $sql_main .= " AND rl.is_active= :is_active";
            $sql_values[':is_active'] = $filter['is_active'];
        }
        //启用淘分销
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao'] != '') {
            $sql_main .= " AND rl.fenxiao_status= :is_fenxiao";
            $sql_values[':is_fenxiao'] = $filter['is_fenxiao'];
        }
        //店铺性质
        if (isset($filter['entity_type']) && $filter['entity_type'] != '') {
            $sql_main .= " AND rl.entity_type= :entity_type";
            $sql_values[':entity_type'] = $filter['entity_type'];
        }
        //查询天猫店铺
        if (isset($filter['shop_channel_code']) && $filter['shop_channel_code'] == 'B') {
            $sql_main .= " AND r2.tb_shop_type= :tb_shop_type";
            $sql_values[':tb_shop_type'] = $filter['shop_channel_code'];
        }
        //销售渠道
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sale_channel_id_arr = explode(',', $filter['sale_channel_code']);
            if (!empty($sale_channel_id_arr)) {
                $sql_main .= " AND (";
                foreach ($sale_channel_id_arr as $key => $value) {
                    $sale_channel = 'sale_channel' . $key;
                    if ($key == 0) {
                        $sql_main .= " rl.sale_channel_code = :{$sale_channel} ";
                    } else {
                        $sql_main .= " or rl.sale_channel_code = :{$sale_channel} ";
                    }

                    $sql_values[':' . $sale_channel] = $value;
                }
                $sql_main .= ")";
            }
        }

        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');

        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }
        if (isset($filter['is_api']) && $filter['is_api'] !== '') {
            $sql_main .= " order by rl.lastchanged desc ";
        }
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        $authorize_type1 = array('taobao', 'jingdong', 'yihaodian', 'suning');
        $authorize_type2 = array('meilishuo', 'jumei');
        $arr_channel = load_model('base/SaleChannelModel')->get_data_code_map();
        foreach ($ret_data['data'] as $k => $row) {
            if ($row['authorize_state'] == '1') {
                $authorize_state = '已授权';
                $link_txt = '重新授权';
            } else {
                $authorize_state = '未授权';
                $link_txt = '授权';
            }
            if (in_array($row['sale_channel_code'], $authorize_type2)) {
                $authorize_state .= " <a href='###' onclick=\"authorize('{$row['shop_id']}')\">[" . $link_txt . "]</a>";
            } else if (in_array($row['sale_channel_code'], $authorize_type1)) {
                $authorize_state .= " <a href='###' onclick=\"pre_authorize('{$row['shop_code']}')\">[" . $link_txt . "]</a>";
            }
            $ret_data['data'][$k]['authorize_state'] = $authorize_state;
            $ret_data['data'][$k]['is_active_text'] = $row['is_active'];
            $api_params = $this->get_shop_api_param($row['shop_code']);
            $alipay_order = '';
            if (!empty($api_params)) {
                $api_param = json_decode($api_params['api'], true);
                $shop_type = isset($api_param['shop_type']) ? $api_param['shop_type'] : '';
                //$ret_data['data'][$k]['alipay_order'] = $api_param['shop_type'];
                $app_key = isset($api_param['app_key']) ? $api_param['app_key'] : '';
                if ($shop_type == 'B' && $row['alipay_order_status'] == 0) {
                    $alipay_order = " 支付宝(未订购)<a href='###' onclick=\"alipay_order('{$app_key}','{$row['shop_code']}')\">订购</a>";
                } else if ($shop_type == 'B' && $row['alipay_order_status'] == 1) {
                    $alipay_order = " 支付宝(已订购)<a href='###' onclick=\"alipay_order('{$app_key}','{$row['shop_code']}')\">重新订购</a>";
                }
            }
            $ret_data['data'][$k]['entity_name'] = $this->entity_type[$row['entity_type']];
            $ret_data['data'][$k]['alipay_order'] = $alipay_order;
            $ret_data['data'][$k]['sale_channel_name'] = isset($arr_channel[$row['sale_channel_code']]['1']) ? $arr_channel[$row['sale_channel_code']]['1'] : '';
            $ret_data['data'][$k]['is_active_export'] = $row['is_active'] == 1 ? '是' : '否';
            $ret_data['data'][$k]['authorize_state_export'] = $row['authorize_state'] == 1 ? '已授权' : '未授权';
        }

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 根据条件查询实体店铺数据
     */
    function get_entity_by_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $sql_main = "FROM {$this->table} rl $sql_join WHERE shop_type=1 ";
        //是否启用
        if (isset($filter['is_active']) && $filter['is_active'] != '') {
            $sql_main .= " AND rl.is_active= :is_active";
            $sql_values[':is_active'] = $filter['is_active'];
        }
        //关键字
        if (isset($filter['shop_key']) && $filter['shop_key'] != '') {
            $sql_main .= " AND (rl.shop_code LIKE :shop_key OR shop_name LIKE :shop_key OR shop_user_nick LIKE :shop_key)";
            $sql_values[':shop_key'] = '%' . $filter['shop_key'] . '%';
        }
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 1) {
            $sql_main .= ' AND rl.shop_code=:shop_code';
            $sql_values[':shop_code'] = CTX()->get_session('oms_shop_code');
        }

        $sql_main .= " ORDER BY rl.shop_user_nick DESC,create_time DESC ";
        $select = 'rl.shop_id,rl.shop_code,rl.shop_name,rl.entity_type,rl.open_time,rl.create_person,'
                . 'rl.tel,rl.province,rl.city,rl.district,rl.street,rl.address,rl.remark,rl.lastchanged,'
                . 'rl.shop_user_nick,rl.is_active,rl.shop_desc,rl.create_time';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $k => $find_addr) {
            $province = oms_tb_val('base_area', 'name', array('id' => $find_addr['province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $find_addr['city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $find_addr['district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $find_addr['street']));
//            $data['data'][$k]['address1'] = $country . $province . $city . $district . $find_addr['address'];
            $data['data'][$k]['total_address'] = $province . $city . $district . $street . $find_addr['address'];
            $data['data'][$k]['province_name'] = $province;
            $data['data'][$k]['city_name'] = $city;
            $data['data'][$k]['district_name'] = $district;
            $data['data'][$k]['street_name'] = $street;
            $data['data'][$k]['is_active_text'] = $find_addr['is_active'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //通过shop_code 查询 店铺参数信息
    public function get_shop_api_param($shop_code) {
        $sql = "select shop_code,source,api from base_shop_api where shop_code=:shop_code";
        return $this->db->get_row($sql, [':shop_code' => $shop_code]);
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('shop_id' => $id));
        return $arr;
    }

    function get_by_code($shop_code) {
        $arr = $this->get_row(array('shop_code' => $shop_code));
        return $arr;
    }

    /**
     * 检查店铺启用状态
     * @param string $shop_code 店铺代码
     */
    function check_shop_active($shop_code) {
        $sql = "SELECT is_active FROM {$this->table} WHERE shop_code=:shop_code";
        $ret = $this->db->get_value($sql, array(':shop_code' => $shop_code));
        if ($ret == 1) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 添加新纪录
     */
    function insert($shop) {
        $status = $this->valid($shop);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($shop['shop_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('SHOP_ERROR_UNIQUE_CODE');

        $this->begin_trans();
        //分销商表shop_code字段只有淘分销使用
//        if (!empty($shop['custom_code'])) {
//            //维护分销商表的shop_code字段
//            $sql = "UPDATE base_custom SET shop_code = concat(shop_code,',{$shop['shop_code']}') WHERE custom_code = {$shop['custom_code']}";
//            $this->query($sql);
//            $ret = $this->affected_rows();
//            if ($ret != 1) {
//                $this->rollback();
//                return $ret;
//            }
//        }
        $ret = parent :: insert($shop);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        //添加系统日志
        if ($shop['shop_type'] == "1") {
            $module = '实体店铺'; //模块名称
            $operate_type = '新增'; //操作类型
            $log_xq = '新增店铺名称为:' . $shop['shop_name'] . "的实体店铺;";
        } else {
            $module = '网络店铺'; //模块名称
            $operate_type = '新增'; //操作类型
            $log_xq = '新增店铺名称为:' . $shop['shop_name'] . '的网络店铺;';
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);
        return $ret;
    }

    /**
     * 创建门店，同时创建门店仓
     */
    function insert_entity($shop) {
        $ret = $this->is_exists($shop['shop_name'], 'shop_name');
        if (!empty($ret['data'])) {
            return $this->format_ret(-1, '', '店铺名称已存在');
        }
        $this->begin_trans();
        $shop['create_time'] = date('Y-m-d h:i:s');
        $shop['create_person'] = CTX()->get_session('user_name');
        $shop['send_store_code'] = $shop['shop_code'];
        $shop['refund_store_code'] = $shop['shop_code'];
        $shop['stock_source_store_code'] = $shop['shop_code'];
        $shop['shop_type'] = 1;
        $ret = $this->insert($shop);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建门店失败');
        }
        $store['store_code'] = $shop['shop_code'];
        $store['store_name'] = $shop['shop_name'];
        $store['shop_name'] = $shop['shop_name'];
        $store['country'] = $shop['country'];
        $store['province'] = $shop['province'];
        $store['city'] = $shop['city'];
        $store['district'] = $shop['district'];
        $store['street'] = $shop['street'];
        $store['address'] = $shop['address'];
        $store['contact_phone'] = $shop['tel'];
        $store['store_property'] = 1;
        $ret = load_model('base/StoreModel')->insert($store);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建门店仓库失败');
        }
        $this->commit();
        return $this->format_ret(1, '', '创建门店成功');
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        if ($active == 1) { //启用店铺
            $sql = "SELECT store_code,p.* FROM base_shop p, base_store t WHERE p.send_store_code=t.store_code AND t.status=1 AND p.shop_id = :shop_id";
            $sql_value = array(":shop_id" => $id);
            $store_data = $this->db->get_row($sql, $sql_value);
            if (empty($store_data)) {
                return $this->format_ret(-1, '', '发货仓库不存在或未启用');
            }
            if (empty($store_data['send_store_code'])) {
                return $this->format_ret(-1, '', '选择发货仓库之后才能启用');
            }
            if ($store_data['entity_type'] == 2 && empty($store_data['custom_code'])) {
                return $this->format_ret(-1, '', '选择分销商之后才能启用');
            }
        }
        $ret = parent :: update(array('is_active' => $active), array('shop_id' => $id));
        if ($active == 1 && $ret['status'] == 1) {
            //添加系统日志
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_id' => $id));
            //$yw_code = $store_data['shop_code'];
            $module = '网络店铺'; //模块名称
            $operate_type = '修改'; //操作类型
            $log_xq = '网络店铺:' . $shop_name . '由停用改为启用;';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
        } else {
            //添加系统日志
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_id' => $id));
            //$yw_code = $store_data['shop_code'];
            $module = '网络店铺'; //模块名称
            $operate_type = '修改'; //操作类型
            $log_xq = '网络店铺:' . $shop_name . '由启用改为停用;';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * 实体店铺营业状态更新，校验店铺点数
     * @param type $active
     * @param type $id
     * @return type
     */
    function update_entity_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        if ($active == 1) {
            $sql = "SELECT count(*) AS sum FROM base_shop WHERE is_active = 1 AND shop_type=1";
            $res = $this->db->getRow($sql);

            $sql = "select value from sys_auth where code = 'shop_num'";
            $arr = $this->db->getRow($sql);
            if ($res['sum'] >= $arr['value']) {
                return $this->format_ret(-1, '', '门店点数不足');
            }
        }

        $this->begin_trans();
        $ret = parent :: update(array('is_active' => $active), array('shop_id' => $id));
        if ($ret['status'] != 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $ret;
        }

        $sql = "SELECT st.store_id FROM base_shop AS bs INNER JOIN base_store AS st ON bs.stock_source_store_code=st.store_code WHERE shop_id=:shop_id";
        $store_id = $this->db->get_value($sql, array(':shop_id' => $id));
        $ret = load_model('base/StoreModel')->update_active($store_id, $active);
        if ($ret['status'] != 1) {
            $this->rollback();
        } else {
            $this->commit();
            //添加系统日志
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_id' => $id));
            //$yw_code = $shop_code;
            $module = '实体店铺'; //模块名称
            if ($active == 1) {
                $operate_type = '开启'; //操作类型
                $log_xq = '实体店铺:' . $shop_name . '由暂停营业状态设置为开始营业状态;';
            } else {
                $operate_type = '关闭'; //操作类型
                $log_xq = '实体店铺:' . $shop_name . '由开始营业状态设置为暂停营业状态;';
            }
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
        }
        return $ret;
    }

    //取出最后一天记录id号
    function get_last() {
        $sql = "select shop_id FROM {$this->table} order by shop_id desc limit 1  ";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    //列表数据
    function get_list() {
        $sql = "select * FROM {$this->table} where  is_active=1";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    //更新授权状态
    function update_auth_shop($shop_code, $shop) {
        $ret = parent :: update($shop, array('shop_code' => $shop_code));
        $ret = $this->update_fenxiao_shop_authorize_state($shop_code);
        return $ret;
    }

    /**
     * 修改纪录
     */
    function update($shop, $id) {
        $status = $this->valid($shop, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('shop_id' => $id));
        $old_data = $ret['data'];
        if (isset($shop['shop_name']) && isset($ret['data']['shop_name']) && ($shop['shop_name'] != $ret['data']['shop_name'])) {
            $ret = $this->is_exists($shop['shop_name'], 'shop_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('SHOP_ERROR_UNIQUE_NAME');
        }
        $this->begin_trans();
        if (!empty($shop['custom_code'])) {
            //维护分销商表的shop_code字段
            $ret = $this->update_exp('base_custom', array('shop_code' => $ret['data']['shop_code']), array('custom_code' => $shop['custom_code']));
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }

        $ret = parent :: update($shop, array('shop_id' => $id));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        //添加系统日志
        if ($old_data['shop_type'] == "1") {
            $log_xq = '实体店铺:' . $old_data['shop_name'];
            if ($old_data['shop_name'] !== $shop['shop_name']) {
                $log_xq .= "店铺名称由" . $old_data['shop_name'] . "修改为" . $shop['shop_name'] . ";";
            }
            if ($old_data['shop_user_nick'] !== $shop['shop_user_nick']) {
                $log_xq .= "店铺助记符由" . $old_data['shop_user_nick'] . "修改为" . $shop['shop_user_nick'] . ";";
            }
            if ($old_data['tel'] !== $shop['tel']) {
                if (empty($old_data['tel'])) {
                    $old_data['tel'] = '空';
                }
                $log_xq .= "联系电话由" . $old_data['tel'] . "修改为" . $shop['tel'] . ";";
            }
            $old_address_str = $old_data['province'] . $old_data['city'] . $old_data['district'] . $old_data['street'] . $old_data['address'];
            $new_address_str = $shop['province'] . $shop['city'] . $shop['district'] . $shop['street'] . $shop['address'];
            if ($old_address_str !== $new_address_str) {
                $old_address_arr[] = $old_data['province'];
                $old_address_arr[] = $old_data['city'];
                $old_address_arr[] = $old_data['district'];
                $old_address_arr[] = $old_data['street'];
                $new_address_arr[] = $shop['province'];
                $new_address_arr[] = $shop['city'];
                $new_address_arr[] = $shop['district'];
                $new_address_arr[] = $shop['street'];
                $old_address = '';
                $new_address = '';
                foreach ($old_address_arr as $val) {
                    $old_address_name = oms_tb_val("base_area", "name", array('id' => $val));
                    $old_address .= $old_address_name;
                }
                $old_address = $old_address . $old_data['address'];
                foreach ($new_address_arr as $v) {
                    $new_address_name = oms_tb_val("base_area", "name", array('id' => $v));
                    $new_address .= $new_address_name;
                }
                $new_address = $new_address . $shop['address'];
                $log_xq .= "店铺地址由" . $old_address . "修改为" . $new_address . ";";
            }
            if ($old_data['open_time'] !== $shop['open_time']) {
                $log_xq .= "营业时间由" . $old_data['open_time'] . "修改为" . $shop['open_time'] . ";";
            }
            if ($old_data['remark'] !== $shop['remark']) {
                if (empty($old_data['remark'])) {
                    $old_data['remark'] = '空';
                }
                $log_xq .= "备注由" . $old_data['remark'] . "修改为" . $shop['remark'] . ";";
            }

            if ($log_xq != '实体店铺:' . $old_data['shop_name']) {
                $module = '实体店铺'; //模块名称
                $operate_type = '编辑'; //操作类型
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
                load_model('sys/OperateLogModel')->insert($log);
            }
        } else {
            $log_xq = '网络店铺:' . $old_data['shop_name'];
            $button_status = array(
                '关闭', '启用'
            );
            if ($old_data['shop_name'] !== $shop['shop_name']) {
                $log_xq .= "店铺名称由" . $old_data['shop_name'] . "修改为" . $shop['shop_name'] . ";";
            }
            if ($old_data['fenxiao_status'] !== $shop['fenxiao_status']) {
                $log_xq .= "淘分销状态由" . $button_status[$old_data['fenxiao_status']] . "修改为" . $button_status[$shop['fenxiao_status']] . ";";
            }
            if ($old_data['send_store_code'] !== $shop['send_store_code']) {
                $old_send_store = oms_tb_val('base_store', "store_name", array('store_code' => $old_data['send_store_code']));
                $new_send_store = oms_tb_val('base_store', "store_name", array('store_code' => $shop['send_store_code']));
                $log_xq .= "发货仓库由" . $old_send_store . '修改为' . $new_send_store . ";";
            }
            if ($old_data['refund_store_code'] !== $shop['refund_store_code']) {
                $old_return_store = oms_tb_val('base_store', "store_name", array('store_code' => $old_data['refund_store_code']));
                $new_return_store = oms_tb_val('base_store', "store_name", array('store_code' => $shop['refund_store_code']));
                $log_xq .= "退货仓库由" . $old_return_store . '修改为' . $new_return_store . ";";
            }
            if ($old_data['express_code'] !== $shop['express_code']) {
                $old_express_name = oms_tb_val('base_express', "express_name", array('express_code' => $old_data['express_code']));
                $new_express_name = oms_tb_val('base_express', "express_name", array('express_code' => $shop['express_code']));
                $log_xq .= "默认配送方式由" . $old_express_name . "修改为" . $new_express_name . ";";
            }
            if ($old_data['days'] !== $shop['days']) {
                $log_xq .= "承诺发货天数由" . $old_data['days'] . "天修改为" . $shop['days'] . "天;";
            }
            if ($old_data['express_data'] !== $shop['express_data']) {
                $old_express_arr = json_decode($old_data['express_data'], true);
                $new_express_arr = json_decode($shop['express_data'], true);
                $old_express_str = '';
                $new_express_str = '';
                foreach ($old_express_arr as $val) {
                    $old_express_name = oms_tb_val('base_express', "express_name", array('express_code' => $val));
                    $old_express_str .= $old_express_name . ",";
                }
                $old_express_str = trim($old_express_str, ',');
                foreach ($new_express_arr as $v) {
                    $new_express_name = oms_tb_val('base_express', "express_name", array('express_code' => $v));
                    $new_express_str .= $new_express_name . ",";
                }
                $new_express_str = trim($new_express_str, ',');
                $log_xq .= "配送方式由" . $old_express_str . "修改为" . $new_express_str . ";";
            }
            if ($old_data['contact_person'] !== $shop['contact_person']) {
                $log_xq .= "联系人由" . $old_data['contact_person'] . "修改为" . $shop['contact_person'] . ";";
            }
            if ($old_data['tel'] !== $shop['tel']) {
                $log_xq .= "联系电话由" . $old_data['tel'] . "修改为" . $shop['tel'] . ";";
            }
            $old_address_str = $old_data['province'] . $old_data['city'] . $old_data['district'] . $old_data['street'] . $old_data['address'];
            $new_address_str = $shop['province'] . $shop['city'] . $shop['district'] . $shop['street'] . $shop['address'];
            if ($old_address_str !== $new_address_str) {
                $old_address_arr[] = $old_data['province'];
                $old_address_arr[] = $old_data['city'];
                $old_address_arr[] = $old_data['district'];
                $old_address_arr[] = $old_data['street'];
                $new_address_arr[] = $shop['province'];
                $new_address_arr[] = $shop['city'];
                $new_address_arr[] = $shop['district'];
                $new_address_arr[] = $shop['street'];
                $old_address = '';
                $new_address = '';
                foreach ($old_address_arr as $val) {
                    $old_address_name = oms_tb_val("base_area", "name", array('id' => $val));
                    $old_address .= $old_address_name;
                }
                $old_address = $old_address . $old_data['address'];
                foreach ($new_address_arr as $v) {
                    $new_address_name = oms_tb_val("base_area", "name", array('id' => $v));
                    $new_address .= $new_address_name;
                }
                $new_address = $new_address . $shop['address'];
                $log_xq .= "发货地址由" . $old_address . "修改为" . $new_address . ";";
            }
            if ($old_data['inv_syn'] != $shop['inv_syn']) {
                $log_xq .= "商品无库存记录以0库存同步由" . $button_status[$old_data['inv_syn']] . "修改为" . $button_status[$shop['inv_syn']] . "状态;";
            }
            if ($log_xq != '网络店铺:' . $old_data['shop_name']) {
                $module = '网络店铺'; //模块名称
                $operate_type = '编辑'; //操作类型
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
                load_model('sys/OperateLogModel')->insert($log);
            }
        }


        return $ret;
    }

    function update_express($express, $shop_code) {
        $ret = parent :: update($express, array('shop_code' => $shop_code));
        return $ret;
    }

    /**
     * 判断角色代码是否唯一
     */
    private function is_unique($shop_code) {
        $ret = $this->get_row(array('shop_code' => $shop_code));

        $status = $ret['status'] == 1 ? SHOP_ERROR_UNIQUE_CODE : $ret['status'];

        return $this->format_ret($status);
    }

    /**
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['shop_code']) || !valid_input($data['shop_code'], 'required')))
            return 'SHOP_ERROR_CODE';
        if (!isset($data['shop_name']) || !valid_input($data['shop_name'], 'required'))
            return 'SHOP_ERROR_NAME';

        return 1;
    }

    public function is_exists($value, $field_name = 'shop_code') {
        $ret = parent :: get_row(array($field_name => $value));

        return $ret;
    }

    /**
     * 验证授权状态
     * @param $authorize_state
     * @param $shop_name
     * @return array
     */
    function update_authorize_state($shop_code) {
        $sql = "select authorize_state from base_shop where shop_code = :shop_code";
        $authorize_state = ctx()->db->getOne($sql, array(':shop_code' => $shop_code));
        if ($authorize_state == 1) {
            //添加系统日志
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $shop_code));
            //$yw_code = $shop_code;
            $module = '网络店铺'; //模块名称
            $operate_type = '(重新)授权'; //操作类型
            $log_xq = '网络店铺:' . $shop_name . '完成(重新)授权;';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret(1, '', '授权成功');
        } else {
            return $this->format_ret(-1, '', '授权失败');
        }
    }

    /**
     * 验证订购状态
     * @param $authorize_state
     * @param $shop_name
     * @return array
     */
    function update_alipay_order_status($shop_code) {
        $ret = load_model('common/ServiceModel')->check_is_auth_by_value('alipay');
        if ($ret == true) {
            $sql = "update base_shop set alipay_order_status = 1 where shop_code = :shop_code";
            $ret = $this->db->query($sql, array(':shop_code' => $shop_code));
        }

        if ($ret == true) {
            return $this->format_ret(1, '', '授权成功');
        } else {
            return $this->format_ret(-1, '', '授权失败');
        }
    }

    //取出sap对应的店铺
    function get_sap_shop() {
        $sql_values = array();
        $sql = "SELECT efast_shop_code FROM sap_config";
        $data = $this->db->get_row($sql);
        $efast_shop_code_arr = explode(",", $data['efast_shop_code']);
        $shop_code_str = $this->arr_to_in_sql_value($efast_shop_code_arr, 'shop_code', $sql_values);

        $sql = "select shop_code,shop_name FROM {$this->table} where shop_code in ({$shop_code_str})";
        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    /**
     * 取出有权限的商店
     * @return array()
     */
    function get_purview_shop($fld = 'shop_code,shop_name', $filter_where = '') {
        $this->set_user_manage();
        $sql_values = array();
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql = "select $fld FROM {$this->table} t where  is_active=1 AND shop_type = 0";
        if ($filter_where == 'filter_fx') { //过滤普通分销
            $sql .= " AND (entity_type != 2) ";
        }
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code']);
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {
                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }


        $rs = $this->db->get_all($sql, $sql_values);

        return $rs;
    }

    //取出有权限的商店(包括停用的)-目前用于实物锁定明细查询
    function get_purview_shop_new($fld = 'shop_code,shop_name', $filter_where = '') {
        $this->set_user_manage();
        $sql_values = array();
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql = "SELECT $fld FROM {$this->table} t WHERE shop_type = 0";
        if ($filter_where == 'filter_fx') { //过滤普通分销
            $sql .= " AND (entity_type != 2) ";
        }
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code']);
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {
                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }


        $rs = $this->db->get_all($sql, $sql_values);

        return $rs;
    }

    //获取店铺
    function get_shop($fld = 'shop_code,shop_name') {
        $sql = "select $fld FROM {$this->table} t where  is_active=1 AND shop_type = 0";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    /**
     * 取出分销的店铺(普通分销、淘分销、所有分销)
     * $type 分销类型 pt_fx 、 tb_fx 、 all_fx
     * @return array()
     */
    function get_purview_ptfx_shop($type = 'pt_fx', $fld = 'shop_code,shop_name') {
        $this->set_user_manage();
        $sql_values = array();
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql = "select $fld FROM {$this->table} t where is_active=1 AND shop_type = 0 ";
        if ($type == 'pt_fx') {
            $sql .= " AND custom_code != '' AND entity_type = 2 ";
        } else if ($type == 'all_fx') {
            $sql .= " AND (entity_type = 2 OR fenxiao_status = 1)";
        } else if ($type == 'tb_fx') {
            $sql .= " AND fenxiao_status = 1";
        }
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_code = load_model('base/CustomModel')->get_custom_by_user_code($user_code, 'custom_code,custom_name,shop_code');
            if (!empty($custom_code)) {
                if ($type == 'pt_fx' || $type = 'all_fx') {
                    $sql .= " AND custom_code = :custom_code ";
                    $sql_values[':custom_code'] = $custom_code['custom_code'];
                } else if ($type == 'tb_fx') {
                    $sql .= " AND shop_code = :shop_code ";
                    $sql_values[':shop_code'] = $custom_code['shop_code'];
                }
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {
                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }
        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    /**
     * 取出是淘宝分销并且有权限的商店
     * @return array()
     */
    function get_purview_tbfx_shop($fld = 'shop_code,shop_name') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql_values = array();
        $sql = "SELECT {$fld} FROM {$this->table} t WHERE t.is_active = 1 AND ((t.sale_channel_code = 'taobao' AND t.fenxiao_status = 1) OR (t.sale_channel_code = 'fenxiao')) ";
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code'], 'taobao');
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND t.shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {

                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }

        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    //获取淘宝已开启店铺
    function get_purview_tb_shop($fld = 'shop_code,shop_name') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql_values = array();
        $sql = "SELECT {$fld} FROM {$this->table} t WHERE t.is_active = 1 AND t.sale_channel_code = 'taobao' ";
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code'], 'taobao');
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND t.shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {

                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }

        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    //按淘宝或者淘分销来取出店铺值
    function get_purview_tbfx_shop_channel($fld = 'shop_code,shop_name', $sale_channel_code = 'taobao') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql_values = array();
        $sql = "SELECT {$fld} FROM {$this->table} t WHERE t.is_active = 1 AND (t.sale_channel_code = '{$sale_channel_code}' AND (t.fenxiao_status = 1 OR t.sale_channel_code = 'fenxiao')) ";
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code']);
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND t.shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {

                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }

        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    function get_purview_shop_by_sale_channel_code($sale_channel_code) {
        //   $is_manage = CTX()->get_session('is_manage');
        $this->set_user_manage();

        $sql_values = array();
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql = "select shop_code,shop_name,shop_id FROM {$this->table} t where  is_active=1 and sale_channel_code='{$sale_channel_code}'";
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) { //分销商登录
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            $shop_code = $this->get_by_custom_code($custom_data['custom_code']);
            $shop_arr = array_column($shop_code, 'shop_code');
            if (!empty($shop_arr)) {
                $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
                $sql .= " AND t.shop_code in ({$shop_str}) ";
            } else {
                $sql .= " AND 1 != 1 ";
            }
        }
        if ((int) $this->is_manage == 0 && $shop_power == 1 && $login_type != 2) {

            //    $shop_code = CTX()->get_session('shop_code');
            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }


            if (empty($shop_code)) {
                return array();
            } else {
                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and shop_code in ({$str})";
            }
        }

        $rs = $this->db->get_all($sql, $sql_values);

        return $rs;
    }

    function get_wepinhuijit_shop() {
        $shop_arr = $this->get_purview_shop_by_sale_channel_code('weipinhui');
        if (empty($shop_arr)) {
            return array();
        }

        //过滤非JIT店铺
        $shop_arr = array_column($shop_arr, 'shop_code', 'shop_name');
        $shop_str = deal_array_with_quote($shop_arr);
        $sql = "SELECT shop_code,api FROM base_shop_api WHERE shop_code in({$shop_str}) AND source='weipinhui'";

        $api_data = $this->db->get_all($sql);
        $shop_arr_new = array();
        foreach ($api_data as $val) {
            $api = json_decode($val['api'], true);
            if (isset($api['type']) && $api['type'] != 'JIT') {
                continue;
            }
            $shop = array();
            $shop['shop_code'] = $val['shop_code'];
            $shop['shop_name'] = array_search($val['shop_code'], $shop_arr);
            $shop_arr_new[] = $shop;
        }
        return $shop_arr_new;
    }

    /**
     * 取出有权限的商店 拼装SQL时用
     * $fld sql字段名 多表查的要传参如r1.shop_code ,
     * $req_code 客户端传来的shop_code（要去掉客户端传来没权限的shop_code）
     * @return array()
     */
    function get_sql_purview_shop($fld = 'shop_code', $req_code = null, $fun = 'get_purview_shop') {
//        $this->set_user_manage();
//        if ((int) $this->is_manage == 1 && empty($req_code)) {
//            return '';
//        }

        $req_store_code_arr = array();
        if (!empty($req_code)) {
            $req_store_code_arr = explode(',', $req_code);
        }
        $req_shop_code_arr = array();
        if (!empty($req_code)) {
            $req_shop_code_arr = explode(',', $req_code);
        }
        $ret = $this->$fun();
        $shop_code_arr = array();
        foreach ($ret as $sub_ret) {
            $shop_code_arr[] = $sub_ret['shop_code'];
        }
        if (empty($shop_code_arr)) {
            $str = " and 1!=1 ";
        } else {
            if (!empty($req_shop_code_arr)) {
                $shop_code_arr = array_intersect($shop_code_arr, $req_shop_code_arr);
            }
            if (empty($shop_code_arr)) {
                $str = " and 1!=1 ";
            } else {
                $str = ' and ' . $fld . ' in ("' . join('","', $shop_code_arr) . '")';
            }
        }
        return $str;
    }

    function save_stock_source($shop_id, $store_code_list) {

        $sql = "select stock_source_store_code from {$this->table} where shop_id=:shop_id ";
        $store_data = $this->db->get_row($sql, array(":shop_id" => $shop_id));
        $old_store_code_list = $store_data['stock_source_store_code'];
        $ret = M('base_shop')->update(array('stock_source_store_code' => $store_code_list), array('shop_id' => $shop_id));
        //设置一次全量库存同步
        $this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");
        //添加系统日志
        if ($old_store_code_list != $store_code_list) {
            $old_store_arr = explode(',', $old_store_code_list);
            $new_store_arr = explode(',', $store_code_list);
            $old_store_name = '';
            $new_store_name = '';
            foreach ($old_store_arr as $val) {
                $old_store_name .= oms_tb_val('base_store', 'store_name', array('store_code' => $val)) . ',';
            }
            foreach ($new_store_arr as $v) {
                $new_store_name .= oms_tb_val('base_store', 'store_name', array('store_code' => $v)) . ',';
            }
            $old_store_name = trim($old_store_name, ',');
            $new_store_name = trim($new_store_name, ',');
            $shop_data = $this->get_by_id($shop_id);
            $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $shop_data['data']['shop_code']));
            //$yw_code = $shop_data['data']['shop_code'];
            $module = '网络店铺'; //模块名称
            $operate_type = '修改'; //操作类型
            $log_xq = '网络店铺:' . $shop_name . '的库存来源仓库由' . $old_store_name . '修改为' . $new_store_name . ';';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
            load_model('sys/OperateLogModel')->insert($log);
        }


        return $ret;
    }

    function set_user_manage() {
        if ($this->is_manage < 0) {
            if (CTX()->is_in_cli()) {
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                if (!empty($user_code)) {
                    $sql_user = "select user_id,is_manage from sys_user where user_code=:user_code";
                    $sql_values = array(':user_code' => $user_code);
                    $user_row = $this->db->get_row($sql_user, $sql_values);
                    $this->user_id = $user_row['user_id'];
                    $this->is_manage = 0;
                    $sql_role = "select r.role_code from  sys_role r
                        INNER JOIN sys_user_role u ON r.role_id=u.role_id
                        where r.role_code='manage' AND u.user_id=:user_id ";
                    $sql_values2 = array(':user_id' => $this->user_id);
                    $role_row = $this->db->get_row($sql_role, $sql_values2);
                    if (!empty($role_row)) {
                        $this->is_manage = 1;
                    }
                }
            } else {
                $this->is_manage = CTX()->get_session('is_manage');
            }
        }
    }

    public function get_send_info($shop_code, $store_code) {
        $shop_info = $this->get_row(array('shop_code' => $shop_code));
        $send_info = $shop_info['data'];
        if (empty($send_info['contact_person']) && empty($send_info['address'])) {
            $send_info = $this->db->get_row("select contact_person,contact_phone as tel,zipcode,address,province,city,district,street from base_store where store_code = :store_code", array(":store_code" => $store_code));
        }
        $send_info['contact_person'] = !empty($send_info['contact_person']) ? $send_info['contact_person'] : '';
        $send_info['contact_tel'] = !empty($send_info['tel']) ? $send_info['tel'] : '';
        $send_info['province'] = !empty($send_info['province']) ? $send_info['province'] : '';
        $send_info['city'] = !empty($send_info['city']) ? $send_info['city'] : '';
        $send_info['district'] = !empty($send_info['district']) ? $send_info['district'] : '';
        $send_info['street'] = !empty($send_info['street']) ? $send_info['street'] : '';
        $send_info['address'] = !empty($send_info['address']) ? $send_info['address'] : '';
        $send_info['zipcode'] = !empty($send_info['zipcode']) ? $send_info['zipcode'] : '';

        return $this->format_ret(1, $send_info);
    }

    /**
     * 获取实体店铺数据
     */
    function get_shop_entity() {
        $login_type = CTX()->get_session('login_type');
        $wh = '';
        $sql_values = [];
        if ($login_type > 0) {
            $wh .= " AND shop_code=:shop_code";
            $sql_values[':shop_code'] = CTX()->get_session('oms_shop_code');
        }
        $sql = "SELECT shop_code,shop_name FROM {$this->table} WHERE shop_type=1 {$wh}";
        $rs = $this->db->get_all($sql, $sql_values);
        return $rs;
    }

    /**
     * 取出门店，供页面选择使用
     */
    function get_select_entity($type = 0) {
        $data = $this->get_shop_entity();
        if ($type == 1) {
            $data = array_merge(array(array('', '全部')), $data);
        } else if ($type == 2) {
            $data = array_merge(array(array('', '请选择')), $data);
        } else if ($type == 3) {
            $data = array_merge(array(array('', '...')), $data);
        }
        return $data;
    }

    /**
     * 供库存策略选择店铺使用
     */
    function get_shop_select($filter) {
//        if (isset($filter['select_type']) && $filter['select_type'] == 'all') {
//            $wh = ' 1 ';
//        } else {
//            $wh = ' bs.shop_code NOT IN(SELECT `code` FROM op_inv_sync_ss_relation WHERE `type`=1) ';
//        }
        $sql_values = array();
        $sql_join = 'LEFT JOIN base_sale_channel sc on bs.sale_channel_code = sc.sale_channel_code';
        $sql_main = "FROM {$this->table} bs $sql_join WHERE bs.is_active=1 ";
        //销售平台
        if (isset($filter['sale_channel']) && $filter['sale_channel'] != '') {
            $sale_channel_arr = explode(',', $filter['sale_channel']);
            if (!empty($sale_channel_arr)) {
                $sale_channel_str = deal_array_with_quote($sale_channel_arr);
                $sql_main .= " AND sc.sale_channel_code in($sale_channel_str) ";
            }
        }

        if (isset($filter['shop_name']) && $filter['shop_name'] != '') {
            $sql_main .= " AND bs.shop_name LIKE :shop_name ";
            $sql_values[':shop_name'] = "%{$filter['shop_name']}%";
        }

        $select = 'bs.shop_code,bs.shop_name,bs.sale_channel_code,sc.sale_channel_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 门店添加商品-选择店铺数据
     */
    function get_oms_store($filter) {
        $sql_main = "FROM {$this->table} bs WHERE 1";
        $sql_values = array();
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] != '') {
            $sql_main .= ' AND bs.sale_channel_code=:sale_channel_code ';
            $sql_values[':sale_channel_code'] = $filter['sale_channel_code'];
        }
        //店铺类型
        if (isset($filter['shop_type']) && $filter['shop_type'] != '') {
            $sql_main .= ' AND bs.shop_type=:shop_type ';
            $sql_values[':shop_type'] = $filter['shop_type'];
        }
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (bs.shop_code LIKE :code_name OR bs.shop_name LIKE :code_name)";
            $sql_values[':code_name'] = '%' . $filter['code_name'] . '%';
        }

        $select = 'bs.shop_id,bs.shop_code,bs.shop_name';
        $sql_main .= " ORDER BY bs.shop_code";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 供BUI下拉选择店铺使用
     */
    public function get_bui_select_shop($first_val = 'all', $first_type = 1) {
        $shop_arr = $this->get_purview_shop('shop_code,shop_name,sale_channel_code');
        filter_fk_name($shop_arr, array('sale_channel_code|sale_channel'));
        $first_txt = '';
        switch ($first_type) {
            case 1:
                $first_txt = '全部';
                break;
            case 2:
                $first_txt = '请选择';
                break;
            default:
                break;
        }
        if ($first_type != 0) {
            $data[] = array('text' => "&nbsp;{$first_txt}", 'value' => $first_val);
        }
        foreach ($shop_arr as $val) {
            $arr = array();
            $arr['text'] = "[<b>{$val['sale_channel_code_name']}</b>]&nbsp;{$val['shop_name']}";
            $arr['value'] = $val['shop_code'];
            $data[] = $arr;
        }
        return $data;
    }

    /**
     * 取出天猫的商店
     * @return array()
     */
    function get_purview_shop_tianmao($fld = 't.shop_code,t.shop_name') {
        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('shop_power'));
        $shop_power = $ret_cfg['shop_power'];
        $sql_values = array();
        $sql = "select $fld FROM {$this->table} t LEFT JOIN base_shop_api r ON t.shop_code=r.shop_code where  is_active=1 AND shop_type = 0 AND r.tb_shop_type = 'B'";
        if ((int) $this->is_manage == 0 && $shop_power == 1) {

            if (CTX()->is_in_cli()) {
                $shop_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 1);
                $shop_code = '';
                if (!empty($shop_code_arr)) {
                    $shop_code = implode(",", $shop_code_arr);
                }
            } else {
                $shop_code = CTX()->get_session('shop_code');
            }
            if (empty($shop_code)) {
                return array();
            } else {
                $shop_code_arr = explode(',', $shop_code);
                $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
                $sql .= " and t.shop_code in ({$str})";
            }
        }

        $rs = $this->db->get_all($sql, $sql_values);

        return $rs;
    }

    //根据店铺获取分销商信息
    function get_shop_by_custom($shop_code) {
        $shop_data = $this->get_by_code($shop_code);
        if (isset($shop_data['data']['custom_code']) && !empty($shop_data['data']['custom_code'])) {
            $custom = load_model('base/CustomModel')->get_by_code($shop_data['data']['custom_code']);
            return $this->format_ret(1, $custom['data'], '');
        } else {
            return $this->format_ret(1, '', '');
        }
    }

    /*
     * 方法名       api_sale_channel_shop_by_page                        
     *
     * 功能描述     通过平台获取店铺信息
     *
     * @author      F.ling
     * @date        2017.02.17
     * @param       $param
     *              array(
     *                  可选: 'page', 'page_size','source'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     *
     */

    function api_shop_get($param) {
        //可选字段
        $key_option = array(
            's' => array('source', 'page', 'page_size')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }
        //清空无用数据
        unset($arr_option);
        unset($param);
        $select = 'rl.shop_code,rl.shop_name,rr.sale_channel_name';
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl inner join base_sale_channel rr on rl.sale_channel_code=rr.sale_channel_code WHERE 1 and rl.is_active=1";
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'source') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND rl.sale_channel_code=:{$key}";
                }
            }
        }

        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select, true);
        if (count($ret['data']) == 0) {
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $ret);
    }

    //获取分销商已启用的店铺
    function get_by_custom_code($custom_code, $fx_type = '') {
        $sql = "SELECT * FROM base_shop WHERE is_active = 1 AND custom_code = :custom_code ";
        if ($fx_type == 'taobao') { // 获取淘分销店铺
            $sql .= " AND fenxiao_status = 1 ";
        }
        $data = $this->db->get_all($sql, array(":custom_code" => $custom_code));
        return $data;
    }

    function get_all_record_code_num($shop_code) {
        $sql = "SELECT count(sell_record_code) FROM oms_sell_record WHERE shop_code = :shop_code";
        $num = $this->db->get_value($sql, array('shop_code' => $shop_code));
        return $num;
    }

    function get_taobao_list() {
        $sql = "select shop_code,shop_name,shop_id from {$this->table} where sale_channel_code='taobao' and fenxiao_status=0 and authorize_state=1";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    function erp_shop_select_action($filter) {
        $sql_values = array();
        $sql_join = 'LEFT JOIN base_sale_channel sc on bs.sale_channel_code = sc.sale_channel_code';
        $sql_main = "FROM {$this->table} bs $sql_join WHERE bs.is_active=1 ";
        if (isset($filter['sale_channel']) && $filter['sale_channel'] != '') {
            $sale_channel_arr = explode(',', $filter['sale_channel']);
            if (!empty($sale_channel_arr)) {
                $sale_channel_str = deal_array_with_quote($sale_channel_arr);
                $sql_main .= " AND sc.sale_channel_code in($sale_channel_str) ";
            }
        }
        if (isset($filter['shop_name']) && $filter['shop_name'] != '') {
            $sql_main .= " AND bs.shop_name LIKE :shop_name ";
            $sql_values[':shop_name'] = "%{$filter['shop_name']}%";
        }
        $sql = "SELECT join_sys_code FROM mid_api_join where param_val1=2 AND join_sys_type =0";
        $erp_store_data = $this->db->get_all($sql);
        $erp_store_data_column = array_column($erp_store_data, 'join_sys_code');
        $store_str = $this->arr_to_in_sql_value($erp_store_data_column, 'shop_code', $sql_values);
        $sql_main .= " AND bs.shop_code IN ({$store_str})";
        $select = 'bs.shop_code,bs.shop_name,bs.sale_channel_code,sc.sale_channel_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function update_fenxiao_shop($shop_code) {
        $sql = "select sale_channel_code from base_shop where shop_code=:shop_code";
        $sale_channel = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        if (!in_array($sale_channel['sale_channel_code'], ['taobao', 'fenxiao'])) {
            return $this->format_ret(-1, '', '暂只支持淘宝和淘分销平台');
        }
        $sql = "select shop_code from base_shop where taobao_shop_code=:shop_code and sale_channel_code='fenxiao'";
        $fenxiao_shop = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        if (empty($fenxiao_shop)) {
            return $this->format_ret(-1, '', '分销店铺不存在');
        }
        $extra_data = load_model('base/ShopApiModel')->get_shop_extra_params($shop_code);
        $kh_id = CTX()->saas->get_saas_key();
        foreach ($fenxiao_shop as $val) {
            $ret = load_model('base/ShopApiModel')->save_shop_extra_params($val['shop_code'], $extra_data['data'], $kh_id);
            if ($ret['status'] == 1) {
                parent::update(array('authorize_state' => 1), array('shop_code' => $val['shop_code']));
            }
        }
        return $ret;
    }

    function update_fenxiao_shop_authorize_state($shop_code) {
        $sql = "select sale_channel_code,authorize_state,authorize_date from base_shop where shop_code=:shop_code";
        $sale_channel = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        if (!in_array($sale_channel['sale_channel_code'], ['taobao', 'fenxiao'])) {
            return $this->format_ret(-1, '', '暂只支持淘宝和淘分销平台');
        }
        $sql = "select shop_code from base_shop where taobao_shop_code=:shop_code and sale_channel_code='fenxiao'";
        $fenxiao_shop = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        if (empty($fenxiao_shop)) {
            return $this->format_ret(-1, '', '分销店铺不存在');
        }
        $sql = "update base_shop r1,base_shop r2 set r1.authorize_state=r2.authorize_state,r1.authorize_date=r2.authorize_date where r1.taobao_shop_code=r2.shop_code and r2.shop_code=:shop_code";
        $ret = $this->db->query($sql, [':shop_code' => $shop_code]);
        return $ret;
    }

    function do_delete($shop_id) {
        $ret = $this->get_by_id($shop_id);
        $shop_data = $ret['data'];
        $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $shop_data['shop_code']));
        if (empty($shop_data)) {
            return $this->format_ret(-1, '', '店铺不存在');
        }
        if ($shop_data['is_active'] == 1) {
            return $this->format_ret(-1, '', '店铺已启用');
        }
        $api_shop_data = $this->get_shop_api_param($shop_data['shop_code']);

        //订单数据
        $sql = "SELECT count(shop_code) FROM oms_sell_record WHERE shop_code = :shop_code ";
        $oms_num = $this->db->get_value($sql, array(':shop_code' => $shop_data['shop_code']));
        // api数据
        $api_num = 0;
        if (!empty($api_shop_data)) {
            $sql = "SELECT count(shop_code) FROM api_order WHERE shop_code = :shop_code ";
            $api_num = $this->db->get_value($sql, array(':shop_code' => $shop_data['shop_code']));
        }
        if ($oms_num != 0 || $api_num != 0) {
            return $this->format_ret(-1, '', '店铺已关联订单数据不能删除');
        }
        // 平台商品数据
        $sql = "SELECT count(shop_code) FROM api_goods WHERE shop_code = :shop_code";
        $goods_num = $this->db->get_value($sql, array(':shop_code' => $shop_data['shop_code']));
        if (0 != $goods_num) {
            return $this->format_ret(-1, '', '店铺已关联平台商品不能删除');
        }
        $this->begin_trans();
        $ret = $this->delete(array('shop_id' => $shop_id));
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败');
        }
        if (!empty($api_shop_data)) {
            $ret = $this->delete_exp('base_shop_api', array('shop_code' => $shop_data['shop_code']));
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败');
            }
        }

        $this->commit();
        //添加系统日志
        //$yw_code = $shop_data['shop_code'];
        $module = '网络店铺'; //模块名称
        $operate_type = '刪除'; //操作类型
        $log_xq = '删除店铺名称为:' . $shop_name . '的网络店铺;';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);

        return $this->format_ret(1, '', '删除成功');
    }

    /**
     * 保存店铺与唯品会仓库关系
     * @param $shop_code
     * @param $params
     */
    function save_weipinhui_shop_relation_warehouse($shop_code, $params, $co_mode) {
        $this->begin_trans();
        $sql = "DELETE FROM api_weipinhuijit_shop_warehouse WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $shop_code;
        $ret = $this->query($sql, $sql_value);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '历史数据删除失败！');
        }
        if ($co_mode == '分销JIT') {
            $this->commit();
            return $this->format_ret('1', '', '');
        }
        $insert_parmas = array();
        for ($i = 0; $i < count($params) / 2; $i++) {
            $sync_val = $params['sync_val_' . $i];
            $warehouse_code = $params['warehouse_code_' . $i];
            if (empty($warehouse_code)) {
                continue;
            }
            //$check=load_model('stm/StockLockRecordModel')->check_inv_num($sync_val);
            //if ($check['status'] != 1) {
            //    continue;
            //}
            if ($sync_val < 0 || $sync_val > 100) {
                continue;
            }
            $insert_parmas[$i]['shop_code'] = $shop_code;
            $insert_parmas[$i]['warehouse_code'] = $warehouse_code;
            $insert_parmas[$i]['sync_val'] = $sync_val;
        }
        $update_str = " sync_val = VALUES(sync_val)";
        if (empty($insert_parmas)) {
            $this->rollback();
            return $this->format_ret('1', '', '');
        }
        $ret = $this->insert_multi_duplicate('api_weipinhuijit_shop_warehouse', $insert_parmas, $update_str);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '保存失败！');
        }
        $this->commit();
        return $this->format_ret('1', '', '保存成功！');
    }

    function get_warehouse_by_shop($shop_code) {
        $sql = "SELECT shop_code,warehouse_code,sync_val FROM api_weipinhuijit_shop_warehouse WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $shop_code;
        $ret = $this->db->get_all($sql, $sql_value);
        if (!empty($ret)) {
            foreach ($ret as &$value) {
                $value['warehouse_name'] = oms_tb_val('api_weipinhuijit_warehouse', 'warehouse_name', array('warehouse_code' => $value['warehouse_code']));
            }
        }
        return $ret;
    }

    /**
     * 获取开启ag的店铺
     * @return array
     */
    function get_taobao_ag_shop() {
        $sql = "SELECT shop_code FROM base_shop_ag";
        $shop_code_arr = $this->db->get_all_col($sql);
        return $shop_code_arr;
    }

    /**
     * 设置淘宝AG店铺
     * @param $shop_code_arr
     * @return array
     */
    function set_taobao_ag_shop($shop_code_arr) {
        $del_sql = "TRUNCATE base_shop_ag";
        $ret = $this->query($del_sql);
        if (empty($shop_code_arr)) {
            return $this->format_ret(1);
        }
        $insert_params = array();
        foreach ($shop_code_arr as $shop_code) {
            $insert_params[] = array(
                'shop_code' => $shop_code,
            );
        }
        $ret = $this->insert_multi_exp('base_shop_ag', $insert_params);
        return $ret;
    }

    /**
     * 取出有权限的店铺
     * @return type
     */
    function get_view_select() {
        $rs = $this->get_purview_shop();
        $shop_arr = array();
        foreach ($rs as $val) {
            $shop_arr[$val['shop_code']] = $val['shop_name'];
        }
        return json_encode(bui_bulid_select($shop_arr));
    }

    /**
     * 验证店铺昵称
     * 处理不同的店铺有相同商品的情况
     * @param $shop_code
     * @param $shop_nick
     * @param $sale_channel_code
     * @return array
     */
    function shop_nick_check($shop_code, $shop_nick, $sale_channel_code) {
        $sql = "SELECT shop_code,shop_name FROM base_shop WHERE shop_code<>:shop_code AND sale_channel_code=:sale_channel_code AND shop_user_nick=:shop_user_nick";
        $sql_value[':shop_code'] = $shop_code;
        $sql_value[':shop_user_nick'] = $shop_nick;
        $sql_value[':sale_channel_code'] = $sale_channel_code;
        $shop_info = $this->db->get_row($sql, $sql_value); //var_dump($shop_info);exit;
        if (!empty($shop_info)) {
            return $this->format_ret(-1, '', '店铺昵称在系统中已存在');
        }
        parent::update(array('shop_user_nick' => $shop_nick), array('shop_code' => $shop_code));
        return $this->format_ret(1);
    }

}
