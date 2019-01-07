<?php

require_model('tb/TbModel');

/**
 * 企业版快递策略业务
 * @author WMH
 */
class ExpressPloyModel extends TbModel {

    protected $table = 'op_express_ploy';

    /**
     * 获取策略列表
     * @param array $filter 过滤条件
     * @return array 数据集
     */
    public function get_ploy_by_page($filter) {
        $sql_join = ' LEFT JOIN `op_express_ploy_shop` AS ps ON ep.`ploy_code`=ps.`ploy_code`';
        $sql_main = "FROM `{$this->table}` AS ep {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'ep.`ploy_id`,ep.`ploy_code`,ep.`ploy_name`,ep.`ploy_status`,ep.`send_store`,ep.`order_pay_type`,ep.`default_express`,ep.`min_freight_judge`,ep.`send_adapt_ratio`,ep.`create_time`,GROUP_CONCAT(ps.shop_code) AS ploy_shop';

        if (isset($filter['ploy_code']) && $filter['ploy_code'] != '') {
            $sql_main .=' AND ep.`ploy_code`=:ploy_code ';
            $sql_values[':ploy_code'] = $filter['ploy_code'];
        }
        $sql_main .= ' GROUP BY ep.`ploy_code` ';
        $sql_main .= ' ORDER BY ep.`create_time` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);

        if (!empty($data['data'])) {
            $ploy_code_arr = array_unique(array_column($data['data'], 'ploy_code'));
            $shop_arr = $this->get_ploy_shop_by_code($ploy_code_arr, '；');
            $shop_arr = array_column($shop_arr, 'ploy_shop_name', 'ploy_code');

            $store_data = $this->db->get_all('SELECT store_code,store_name FROM base_store');
            $store_data = array_column($store_data, 'store_code', 'store_name');
        }

        foreach ($data['data'] as &$row) {
            $row['create_time'] = empty($row['create_time']) ? '' : date('Y-m-d H:i:s', $row['create_time']);
            $row['ploy_shop'] = $this->cc_msubstr($shop_arr[$row['ploy_code']], 24);

            $store_exists = array_intersect($store_data, explode(',', $row['send_store']));
            $send_store = implode('；', array_keys($store_exists));
            $row['send_store'] = $this->cc_msubstr($send_store, 24);
        }

        return $this->format_ret(1, $data);
    }

    function cc_msubstr($str, $length, $start = 0, $replace = ' ...', $charset = "utf-8") {
        $str_cut = '';
        $str_len = mb_strwidth($str, $charset);
        if (($length - 3) < $str_len) {
            $str_cut = mb_strimwidth($str, $start, $length, $replace, $charset);
            $str = "<span class='tip' title='{$str}'>{$str_cut}</span>";
        }
        return $str;
    }

    /**
     * 获取快递策略关联店铺
     * @param string $ploy_code_arr 预售编码集合
     * @return array 数据集
     */
    public function get_ploy_shop_by_code($ploy_code_arr, $separator = ',') {
        $sql_values = array();
        $ploy_code_str = $this->arr_to_in_sql_value($ploy_code_arr, 'ploy_code', $sql_values);
        $sql = "SELECT GROUP_CONCAT(bs.`shop_name` SEPARATOR '{$separator}') AS ploy_shop_name,
                GROUP_CONCAT(bs.`shop_code` SEPARATOR '{$separator}') AS ploy_shop_code,ps.`ploy_code` 
                FROM `op_express_ploy_shop` AS ps INNER JOIN `base_shop` AS bs ON ps.`shop_code`=bs.`shop_code`
                WHERE ps.`ploy_code` IN({$ploy_code_str}) GROUP BY ps.`ploy_code`";
        $shop_arr = $this->db->get_all($sql, $sql_values);

        return $shop_arr;
    }

    /**
     * 策略编辑，获取策略信息
     * @param string $ploy_code 策略编码
     * @return array 数据集
     */
    public function get_ploy_info_by_code($ploy_code) {
        if (empty($ploy_code)) {
            return array();
        }
        $data = $this->get_row(array('ploy_code' => $ploy_code));
        if (empty($data['data'])) {
            return array();
        }
        $data = $data['data'];

        $shop = $this->get_ploy_shop_by_code(array($ploy_code));
        $data['shop_code'] = $shop[0]['ploy_shop_code'];
        $data['shop_name'] = $shop[0]['ploy_shop_name'];

        $sql_values = array();
        $store_str = $this->arr_to_in_sql_value(explode(',', $data['send_store']), 'store_code', $sql_values);
        $sql = "SELECT GROUP_CONCAT(store_name) FROM base_store WHERE store_code IN({$store_str})";
        $data['store_name'] = $this->db->get_value($sql, $sql_values);
        $data['store_code'] = $data['send_store'];
        unset($data['send_store']);

        return $data;
    }

    /**
     * 根据编码判断策略是否存在
     */
    public function exists_ploy($ploy_code) {
        $sql = "SELECT ploy_code,ploy_status,ploy_name,send_adapt_ratio FROM {$this->table} WHERE ploy_code=:ploy_code";
        return $this->db->get_row($sql, array(':ploy_code' => $ploy_code));
    }

    /**
     * 更新策略状态
     * @param array $params 参数
     * @return array 结果
     */
    public function ploy_update_active($params) {
        if (!in_array($params['active'], array(1, 0)) || empty($params['ploy_code'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ploy_info = $this->exists_ploy($params['ploy_code']);
        if (empty($ploy_info)) {
            return $this->format_ret(-1, '', '策略不存在，请返回列表检查');
        }

        $msg = $params['active'] == 1 ? '启用' : '停用';
        if ($ploy_info['ploy_status'] == $params['active']) {
            return $this->format_ret(2, '', "策略已{$msg}，不能重复操作");
        }

        $ret = $this->update(array('ploy_status' => $params['active']), array('ploy_code' => $params['ploy_code']));
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            return $this->format_ret(-1, '', "策略{$msg}失败");
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($params['ploy_code'], '更新策略状态', "{$msg}策略< {$params['ploy_code']} >");
        return $this->format_ret(1, '', "策略{$msg}成功");
    }

    /**
     * 新增策略
     * @param array $data 源数据
     * @return array 结果
     */
    public function ploy_add($data) {
        $data['ploy_code'] = $this->create_ploy_code();
        $ins_data = $this->get_filter_data($data);
        if ($ins_data['status'] != 1) {
            return $ins_data;
        }
        $ins_data['data']['create_time'] = time();

        $shop_arr = explode(',', $data['shop_code']);
        $shop_exists = $this->check_shop_exists($shop_arr, $data['ploy_code']);
        if ($shop_exists['status'] < 1) {
            return $shop_exists;
        }

        $this->begin_trans();
        //添加策略主信息
        $ret = $this->insert($ins_data['data']);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '新增失败');
        }

        //添加策略关联店铺信息
        $ploy_shop = array();
        foreach ($shop_arr as $k => $s) {
            $ploy_shop[$k]['ploy_code'] = $data['ploy_code'];
            $ploy_shop[$k]['shop_code'] = $s;
        }
        $ret = $this->insert_multi_exp('op_express_ploy_shop', $ploy_shop, TRUE);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '新增失败');
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($data['ploy_code'], '新增策略', '策略编码：' . $data['ploy_code']);
        $this->commit();
        return $this->format_ret(1, $data['ploy_code'], '新增成功');
    }

    /**
     * 编辑策略
     * @param array $data 源数据
     * @return array 结果
     */
    public function ploy_update($data) {
        $ret = $this->check_ploy($data['ploy_code']);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $up_data = $this->get_filter_data($data);
        if ($up_data['status'] != 1) {
            return $up_data;
        }
        $shop_arr = explode(',', $data['shop_code']);
        $shop_exists = $this->check_shop_exists($shop_arr, $data['ploy_code']);
        if ($shop_exists['status'] < 1) {
            return $shop_exists;
        }

        $this->begin_trans();
        //更新策略主信息
        $ret = $this->update($up_data['data'], array('ploy_code' => $data['ploy_code']));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新失败');
        }

        //更新策略关联店铺信息
        $ploy_shop = array();
        foreach ($shop_arr as $k => $s) {
            $ploy_shop[$k]['ploy_code'] = $data['ploy_code'];
            $ploy_shop[$k]['shop_code'] = $s;
        }
        $ret = $this->insert_multi_exp('op_express_ploy_shop', $ploy_shop, TRUE);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新失败');
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($data['ploy_code'], '编辑策略', '策略编码：' . $data['ploy_code']);
        $this->commit();
        return $this->format_ret(1, $data['ploy_code'], '更新成功');
    }

    /**
     * 新增\编辑整理过滤数据
     * @param array $data 整理前数据
     * @return array 整理后数据
     */
    private function get_filter_data($data) {
        //校验数据空值
        $check_fld = array('ploy_name' => '策略名称', 'shop_code' => '适配店铺', 'store_code' => '发货仓库', 'order_pay_type' => '订单支付类型', 'default_express' => '默认配送方式', 'min_freight_judge' => '最低运费判断', 'send_adapt_ratio' => '配送适配比例');
        if ($data['send_adapt_ratio'] == 1) {
            $adapt_fld = array('adapt_days' => '配送适配比例天数', 'order_status' => '配送适配比例订单状态', 'order_num' => '配送适配比例订单数量');
            $check_fld = array_merge($adapt_fld, $adapt_fld);
        }
        $ret_check = $this->check_params_empty($data, $check_fld);
        $up_data = array();
        if ($ret_check['status'] == 1) {
            //组装策略主信息
            $up_data = array(
                'ploy_code' => $data['ploy_code'],
                'ploy_name' => $data['ploy_name'],
                'ploy_status' => 0,
                'send_store' => $data['store_code'],
                'order_pay_type' => is_array($data['order_pay_type']) ? implode(',', $data['order_pay_type']) : $data['order_pay_type'],
                'default_express' => $data['default_express'],
                'min_freight_judge' => $data['min_freight_judge'],
                'send_adapt_ratio' => $data['send_adapt_ratio']
            );
            //开启配送适配比例时才添加订单配置
            if ($data['send_adapt_ratio'] == 1) {
                $send_adapt_ratio = array(
                    'adapt_days' => $data['adapt_days'],
                    'order_status' => $data['order_status'],
                    'order_num' => $data['order_num']
                );
            } else {
                //未开启配送适配比例，将订单配置置为-1
                $send_adapt_ratio = array(
                    'adapt_days' => -1,
                    'order_status' => -1,
                    'order_num' => -1
                );
            }
            $up_data = array_merge($up_data, $send_adapt_ratio);
        }
        $ret_check['data'] = $up_data;
        return $ret_check;
    }

    private function check_shop_exists($shop_arr, $ploy_code) {
        $sql_values = array(':ploy_code' => $ploy_code);
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
        $sql = "SELECT bs.shop_name FROM op_express_ploy_shop AS ps,base_shop AS bs WHERE ps.shop_code=bs.shop_code AND ps.ploy_code<>:ploy_code AND ps.shop_code IN({$shop_str})";
        $exists_shop = $this->db->get_col($sql, $sql_values);
        if (!empty($exists_shop)) {
            $exists_shop = implode('，', $exists_shop);
            return $this->format_ret(-1, '', "店铺： {$exists_shop} 在其他策略中已存在");
        }
        return $this->format_ret(1);
    }

    /**
     * 策略删除
     * @param string $ploy_code 策略代码
     * @return array 结果
     */
    public function ploy_delete($ploy_code) {
        if (empty($ploy_code)) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ret = $this->check_ploy($ploy_code);
        if ($ret['status'] < 1) {
            return $ret;
        }

        $this->begin_trans();
        //删除策略日志
        $ret = parent::delete_exp('op_express_ploy_log', array('ploy_code' => $ploy_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '清除日志失败');
        }
        $expSetObj = load_model('op/ploy/ExpressPloyExpSetModel');
        $ploy_area = $expSetObj->get_express_set_by_where('ploy_code', $ploy_code);
        if (!empty($ploy_area)) {
            //删除快递区域配置失败
            $express_set_id_arr = array_column($ploy_area, 'express_set_id');
            $ret = $expSetObj->delete_express_area_by_pid($express_set_id_arr);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除快递区域配置失败');
            }
            //删除快递区域运费配置失败
            $ret = $expSetObj->delete_area_freight_by_pid($express_set_id_arr);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除快递运费配置失败');
            }
            //删除策略快递配置
            $ret = parent::delete_exp('op_express_ploy_express_set', array('ploy_code' => $ploy_code));
            if ($ret != TRUE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除策略快递配置数据失败');
            }
        }

        //删除策略关联快递
        $ret = parent::delete_exp('op_express_ploy_express', array('ploy_code' => $ploy_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除策略快递失败');
        }
        //删除策略关联店铺
        $ret = parent::delete_exp('op_express_ploy_shop', array('ploy_code' => $ploy_code));
        if ($ret != TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除策略适配店铺失败');
        }
        //删除策略主信息
        $ret = parent::delete(array('ploy_code' => $ploy_code));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除策略失败');
        }

        $this->commit();
        return $this->format_ret(1, '', '删除策略成功');
    }

    /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段 array('字段'=>'字段名')
     * @return array 检查结果
     */
    public function check_params_empty($params, $field_arr = array()) {
        if (empty($params) || empty($field_arr)) {
            return $this->format_ret(-1, '', '系统参数错误');
        }
        $status = '1';
        $msg = '';
        foreach ($field_arr as $k => $v) {
            if (!isset($params[$k]) || $params[$k] == '') {
                $status = '-1';
                $msg = $v . ' 未设置';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * 检查策略
     * @param string $ploy_code 策略编码
     * @return array 数据
     */
    public function check_ploy($ploy_code) {
        $ploy_info = $this->exists_ploy($ploy_code);
        if (empty($ploy_info)) {
            return $this->format_ret(-1, '', '策略不存在，请返回列表检查');
        }
        if ($ploy_info['ploy_status'] == 1) {
            return $this->format_ret(2, '', '策略已启用， 不能操作');
        }
        return $this->format_ret(1, $ploy_info);
    }

    /**
     * 生成单据号
     */
    public function create_ploy_code() {
        $sql = "SELECT ploy_id FROM {$this->table} ORDER BY ploy_id DESC";
        $ploy_id = $this->db->get_value($sql);
        if ($ploy_id !== FALSE) {
            $code = $ploy_id + 1;
        } else {
            $code = 1;
        }
        require_lib('comm_util', true);
        $code = "KDCL" . add_zero($code, 3);
        return $code;
    }

}
