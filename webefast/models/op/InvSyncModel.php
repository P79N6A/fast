<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

/**
 * 库存同步策略业务
 */
class InvSyncModel extends TbModel {

    private $relation_table = 'op_inv_sync_ss_relation';

    function __construct() {
        parent::__construct('op_inv_sync');
    }

    /**
     * @todo 策略列表检索
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} os WHERE 1";
        $sql_values = array();
        //策略名称
        if (isset($filter['sync_name']) && $filter['sync_name'] != '') {
            $sql_main .= " AND (os.sync_name LIKE :sync_name )";
            $sql_values[':sync_name'] = '%' . $filter['sync_name'] . '%';
        }

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_code = deal_strs_with_quote($filter['shop_code']);
            $sql_sync = "SELECT sync_code FROM {$this->relation_table} WHERE code in({$shop_code}) AND `type`=1";
            $sync_code_arr = $this->db->get_all_col($sql_sync);
            if (empty($sync_code_arr)) {
                $sql_main .= " AND 1=2";
            } else {
                $sync_code_str = deal_array_with_quote($sync_code_arr);
                $sql_main .= " AND os.sync_code IN({$sync_code_str})";
            }
        }
        //是否启用
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND os.status= :status";
            $sql_values[':status'] = $filter['status'];
        }
        $select = 'os.sync_id,os.sync_code,os.sync_name,os.sync_mode,os.is_road,os.is_safe,os.`status`,os.lastchanged';
        $sql_main .= " ORDER BY os.lastchanged DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $able_auth = load_model('sys/PrivilegeModel')->check_priv('op/inv_sync/delete');
        foreach ($data['data'] as &$val) {
            $ss_name = $this->get_ss_name_by_code($val['sync_code'], 0);
            $val = array_merge($val, $ss_name);
            $val['able_auth'] = $able_auth == '' ? 0 : 1;
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 获取策略关联的店铺名称和仓库名称
     * @param string $sync_code 策略代码
     * @param int $type =0店铺和仓库；=1店铺；=2仓库
     */
    function get_ss_name_by_code($sync_code, $type = 0) {
        if ($type == 0) {
            $select = ' GROUP_CONCAT(sp.shop_name) AS shop_name,GROUP_CONCAT(sr.store_name) AS store_name,GROUP_CONCAT(sp.shop_code) AS shop_code, GROUP_CONCAT(sr.store_code) AS store_code ';
            $sql_join = ' LEFT JOIN base_shop sp ON sp.shop_code=ss.`code` AND ss.`type`=1
                         LEFT JOIN base_store sr ON sr.store_code=ss.`code` AND ss.`type`=2 ';
        } else if ($type == 1) {
            $select = 'GROUP_CONCAT(sp.shop_name) AS shop_name,GROUP_CONCAT(sp.shop_code) AS shop_code';
            $sql_join = ' LEFT JOIN base_shop sp ON sp.shop_code=ss.`code` AND ss.`type`=1 ';
        } else if ($type == 2) {
            $select = 'GROUP_CONCAT(sr.store_name) AS store_name, GROUP_CONCAT(sr.store_code) AS store_code';
            $sql_join = ' LEFT JOIN base_store sr ON sr.store_code=ss.`code` AND ss.`type`=2 ';
        }
        $sql = "SELECT {$select} FROM {$this->relation_table} ss {$sql_join} WHERE ss.sync_code=:sync_code";
        $ss_name_arr = $this->db->get_row($sql, array(':sync_code' => $sync_code));

        return $ss_name_arr;
    }

    /**
     * @todo 根据条件获取关联表信息
     */
    function get_relation($params) {
        $sql_wh = '';
        foreach ($params as $key => $val) {
            $sql_wh .= " AND {$key} in('{$val}')";
        }
        $sql = "SELECT `sync_code`,`code`,`type` FROM {$this->relation_table} WHERE 1 {$sql_wh}";
        $ret = $this->db->get_all($sql);
        return $ret;
    }

    /**
     * @todo 编辑策略，获取差异店铺或仓库
     * @param string $sync_code 策略代码
     * @param string $code 店铺或仓库代码
     * @param int $type 1-店铺，2-仓库
     */
    function get_diff_shop_store($sync_code, $code, $type) {
        $sql = "SELECT `code` FROM {$this->relation_table} WHERE sync_code=:sync_code AND `type`=:type";
        $ret = $this->db->get_all($sql, array(':sync_code' => $sync_code, ':type' => $type));
        $old_code = array_column($ret, 'code');
        $new_code = explode(',', $code);
        $add_code = array_diff($new_code, $old_code);
        $less_code = array_diff($old_code, $new_code);
        $res = array('add_code' => $add_code, 'less_code' => $less_code);
        return $res;
    }

    /**
     * @todo 判断店铺是否存在策略
     * @param string $shop_code 店铺代码
     * @param int $type 判断类型：1-新增判断，2-编辑判断
     * @param string $sync_code 策略代码
     */
    function is_exist_policy($shop_code, $type, $sync_code = '') {
        if ($type == 2) {
            $sql = "SELECT `code` FROM op_inv_sync_ss_relation WHERE sync_code=:sync_code AND `type`=:type";
            $shop_exist = $this->db->get_all($sql, array(':sync_code' => $sync_code, ':type' => 1));
            if (!empty($shop_exist)) {
                $shop_exist = array_column($shop_exist, 'code');
                $shop_arr = explode(',', $shop_code);
                $shop_diff = array_diff($shop_arr, $shop_exist);
                if (empty($shop_diff)) {
                    return $this->format_ret(-1, '', '没有新增店铺');
                }
                $shop_code_str = deal_array_with_quote($shop_diff);
            }
        } else {
            $shop_code_str = deal_strs_with_quote($shop_code);
        }

        $sql = "SELECT os.sync_code,os.sync_name,ss.`code` AS shop_code,bs.shop_name FROM op_inv_sync os
                INNER JOIN op_inv_sync_ss_relation ss ON ss.sync_code=os.sync_code
                INNER JOIN base_shop bs ON bs.shop_code=ss.`code`
                WHERE ss.`type`=1 AND ss.`code` in({$shop_code_str})";
        $ret = $this->db->get_all($sql);
        $status = -1;
        $msg = '店铺不存在库存同步策略';
        if (!empty($ret)) {
            $status = 1;
            $exist_shop = array_column($ret, 'shop_name');
            $exist_shop = implode('，', $exist_shop);
            $msg = $exist_shop . '店铺已存在库存同步策略';
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * @todo 更新启用状态
     */
    function update_active($params) {
        $active = array('enable' => 1, 'disable' => 0);
        if (!isset($active[$params['active']])) {
            return $this->format_ret(-1, '', '参数有误');
        }
        $status = $active[$params['active']];
        $ret = parent::update(array('status' => $status), array('sync_id' => $params['id']));
        //启用时执行全量库存同步
        if ($status == 1) {
            $this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");
        }

        $info = $status == 1 ? '启用' : '停用';
        $log_info = '策略' . $info . ';';
        $sync_code = $this->db->get_value("select sync_code from {$this->table} where sync_id = :sync_id", array(':sync_id' => $params['id']));

        if ($log_info) {
            $log = array('sync_code' => $sync_code, 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'baseinfo', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
            $ret = load_model('op/InvSyncLogModel')->insert($log);
        }
        return $ret;
    }

    /**
     * @todo 基本信息页签
     */
    function get_baseinfo($sync_code) {
        $sync = $this->get_row(array('sync_code' => $sync_code));
        $ss_name = $this->get_ss_name_by_code($sync_code, 0);
        $info = array_merge($sync['data'], $ss_name);
        return $info;
    }

    /**
     * @todo 设置策略
     */
    function set_baseinfo($request) {
        $request['type'] = isset($request['type']) ? $request['type'] : '';
        if ($request['type'] != 'anti_oversold') {
            //每个店铺只能有一个策略代码
            $type = ($request['act'] == 'do_add') ? 1 : 2;
            $ret_exist = $this->is_exist_policy($request['shop_code'], $type, $request['sync_code']);
            if ($ret_exist['status'] == 1) {
                return $this->format_ret(-1, '', $ret_exist['message']);
            }
            $request['sync_code'] = (empty($request['sync_code'])) ? $this->create_sync_code() : $this->check_sync_info($request);
        }
        //新增策略
        if ($request['act'] == 'do_add') {
            $request['create_time'] = date('Y-m-d H:i:s');
            $request['create_person'] = CTX()->get_session('user_name');
            $request['warn_goods_deliver_day'] = 15;
            $request['is_safe'] = $request['is_safe'] == 'on' ? 1 : 0 ;
            $request['is_road'] = $request['is_road'] == 'on' ? 1 : 0 ;
        }
        //防超卖预警页面
        if ($request['type'] == 'anti_oversold') {
            $request['warn_goods_sell_shop'] = isset($request['shop_code']) ? $request['shop_code'] : '';
        }

        $this->begin_trans();
        $old_sync = $this->get_baseinfo($request['sync_code']);
        $ret = ($request['act'] == 'do_edit' || $request['type'] == 'anti_oversold') ? parent::update($request, array('sync_code' => $request['sync_code'])) : parent::insert($request);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        if ($request['type'] == 'anti_oversold') {
            $this->commit();
            $log_info = '';
            if ($request['warn_goods_val'] != $old_sync['warn_goods_val']) {
                $log_info .= '防超卖商品警戒值由[' . $old_sync['warn_goods_val'] . ']修改为[' . $request['warn_goods_val'] . '];';
            }
            if ($request['shop_code'] != $old_sync['warn_goods_sell_shop']) {
                $sql = "select shop_name from base_shop where shop_code = :shop_code ";
                $sql_val = array(':shop_code' => $request['shop_code']);
                $shop_name = $this->db->get_value($sql, $sql_val);
                $old_val = array(':shop_code' => $old_sync['warn_goods_sell_shop']);
                $old_shop_name = $this->db->get_value($sql, $old_val);
                $log_info .= '防超卖商品销售店铺由[' . $old_shop_name . ']修改为[' . $shop_name . '];';
            }
            if ($log_info) {
                $log = array('sync_code' => $request['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'anti_oversold', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
                $ret = load_model('op/InvSyncLogModel')->insert($log);
            }

            return $ret;
        }

        $shop_data = $this->deal_data($request['sync_code'], $request['shop_code'], 1);
        $store_data = $this->deal_data($request['sync_code'], $request['store_code'], 2);
        $detail = array_merge($shop_data, $store_data);
        if ($request['act'] == 'do_edit') {
            $diff_shop = $this->get_diff_shop_store($request['sync_code'], $request['shop_code'], 1);
            $diff_store = $this->get_diff_shop_store($request['sync_code'], $request['store_code'], 2);
            if (!empty($diff_shop['less_code']) || !empty($diff_store['less_code'])) {
                $r = load_model('op/InvSyncRatioModel')->delete_diff_data($request['sync_code'], $diff_shop, $diff_store);
            }
            $this->delete_exp($this->relation_table, array('sync_code' => $request['sync_code']));
        }
        $update_str = "sync_code = VALUES(sync_code), `code` = VALUES(`code`), `type` = VALUES(`type`) ";
        $res = $this->insert_multi_duplicate($this->relation_table, $detail, $update_str);
        if ($res['status'] != 1) {
            $this->rollback();
            return $res;
        }
        $this->commit();
        //增加店铺比例配置数据和商品比例配置数据
        $r = load_model('op/InvSyncRatioModel')->insert_by_multi($shop_data, $store_data, $request['sync_mode']);
        if ($r['status'] == 1) {
            $log_info = '';
            if ($request['sync_name'] != $old_sync['sync_name'] && $old_sync['sync_name']) {
                $log_info .= '策略名称由[' . $old_sync['sync_name'] . ']修改为[' . $request['sync_name'] . '];';
            }
            if ($request['shop_name'] != $old_sync['shop_name'] && $old_sync['shop_name']) {
                $log_info .= '店铺名称由[' . $old_sync['shop_name'] . ']修改为[' . $request['shop_name'] . '];';
            }
            if ($request['store_name'] != $old_sync['store_name'] && $old_sync['store_name']) {
                $log_info .= '仓库名称由[' . $old_sync['store_name'] . ']修改为[' . $request['store_name'] . '];';
            }
            if ($request['sync_mode'] != $old_sync['sync_mode'] && $old_sync['sync_mode']) {
                $sync_mode = $request['sync_mode'] == 1 ? '全局模式' : '仓库模式';
                $old_mode = $old_sync['sync_mode'] == 1 ? '全局模式' : '仓库模式';
                $log_info .= '策略模式由[' . $old_mode . ']修改为[' . $sync_mode . '];';
            }
            if ($request['is_road'] != $old_sync['is_road'] && $old_sync['is_road']) {
                $is_road = $request['is_road'] == 1 ? '启用' : '停用';
                $old_road = $old_sync['is_road'] == 1 ? '启用' : '停用';
                $log_info .= '启用在途库存由[' . $old_road . ']修改为[' . $is_road . '];';
            }
            if ($request['is_safe'] != $old_sync['is_safe'] && $old_sync['is_safe']) {
                $is_safe = $request['is_safe'] == 1 ? '启用' : '停用';
                $old_safe = $old_sync['is_safe'] == 1 ? '启用' : '停用';
                $log_info .= '启用安全库存由[' . $old_safe . ']修改为[' . $is_safe . '];';
            }
            if ($log_info) {
                $log = array('sync_code' => $request['sync_code'], 'user_code' => CTX()->get_session('user_code'), 'user_ip' => gethostbyname($_SERVER["SERVER_NAME"]), 'tab_type' => 'baseinfo', 'log_info' => $log_info, 'log_time' => date('Y-m-d H:i:s'));
                $ret = load_model('op/InvSyncLogModel')->insert($log);
            }

            return $this->format_ret(1, $request['sync_code'], '保存成功！');
        } else {
            return $this->format_ret(-1, '', '保存失败！');
        }
    }

    function check_sync_info($params) {
        $sql = "SELECT sync_mode FROM {$this->table} WHERE sync_code=:sync_code";
        $sql_value = array('sync_code' => $params['sync_code']);
        $sync_mode = $this->db->get_value($sql, $sql_value);
        if ($sync_mode != $params['sync_mode']) {
            parent::delete_exp('op_inv_sync_shop_ratio', array('sync_code' => $params['sync_code']));
            parent::delete_exp('op_inv_sync_goods_ratio', array('sync_code' => $params['sync_code']));
        }
        return $params['sync_code'];
    }

    function deal_data($sync_code, $code, $type) {
        $code = explode(',', $code);
        $data = array();
        if (!empty($code)) {
            foreach ($code as $key => $value) {
                $data[$key]['sync_code'] = $sync_code;
                $data[$key]['code'] = $value;
                $data[$key]['type'] = $type;
            }
        }
        return $data;
    }

    /**
     * @todo 生成库存策略代码
     */
    function create_sync_code() {
        $sql = "select sync_code  from {$this->table} ORDER BY sync_code DESC";
        $data = $this->db->get_limit($sql, array(), 1);
        if ($data) {
            $matches = array();
            preg_match_all('/(\d)|(\w)/', $data[0]['sync_code'], $matches);
            $numbers = implode($matches[1]);
            $code = intval($numbers) + 1;
        } else {
            $code = 1;
        }
        require_lib('comm_util', true);
        $sync_code = "KCCL" . add_zero($code, 3);
        return $sync_code;
    }

    function delete($sync_code) {
        $sql = "select count(1) from op_inv_sync where sync_code = :sync_code";
        $data = $this->db->get_value($sql, array(':sync_code' => $sync_code));
        if ($data == 0) {
            return $this->format_ret(-1, '', '策略不存在！');
        }
        $ret = parent::delete(array('sync_code' => $sync_code));
        $ret = parent::delete_exp('op_inv_sync_goods_ratio', array('sync_code' => $sync_code));
        $ret = parent::delete_exp('op_inv_sync_shop_ratio', array('sync_code' => $sync_code));
        $ret = parent::delete_exp('op_inv_sync_ss_relation', array('sync_code' => $sync_code));
        $ret = parent::delete_exp('op_inv_sync_warn_goods', array('sync_code' => $sync_code));
        //删除预警条码
        $ret = parent::delete_exp('op_inv_sync_warn_sku', array('sync_code' => $sync_code));
        //删除日志
        $ret = parent::delete_exp('inv_sync_log', array('sync_code' => $sync_code));
        return $this->format_ret(1, '', '删除成功！');
    }

    /**
     * @todo 判断店铺是否存在策略|是否启用
     */
    function get_policy_by_shop($shop_code) {
        $sql = "SELECT os.sync_code,os.sync_name,os.status,ss.code AS shop_code FROM {$this->table} os LEFT JOIN {$this->relation_table} ss ON os.sync_code=ss.sync_code WHERE type=1 AND code=:shop_code";
        return $this->db->get_row($sql, array(':shop_code' => $shop_code));
    }

    /**
     * 检查库存同步策略开启状态
     * 锁定单使用
     * @param string $params
     * @return array
     */
    function check_inv_sync_policy($params) {
        $sync_status = load_model('sys/SysParamsModel')->get_val_by_code('inv_sync');
        if ($sync_status['inv_sync'] != 1) {
            return $this->format_ret(-2, '', '库存同步策略未开启');
        }

        $policy = $this->get_policy_by_shop($params['shop_code']);
        if (empty($policy) || $policy['status'] != 1) {
            return $this->format_ret(-3, '', '店铺未关联库存同步策略或关联的策略未启用');
        }

        $sql = "SELECT code FROM {$this->relation_table} WHERE type=2 AND sync_code=:sync_code";
        $policy_store = $this->db->get_col($sql, array('sync_code' => $policy['sync_code']));
        if(!in_array($params['store_code'],$policy_store)){
            return $this->format_ret(-3, '', '不存在对应店铺仓库的策略');
        }
        return $this->format_ret(1, $policy['sync_code']);
    }

}
