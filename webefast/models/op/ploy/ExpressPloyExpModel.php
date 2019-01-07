<?php

require_model('tb/TbModel');

/**
 * 快递策略-快递配置
 * @author WMH
 */
class ExpressPloyExpModel extends TbModel {

    protected $table = 'op_express_ploy_express';

    /**
     * 获取策略配置快递
     * @param array $filter 筛选条件
     * @return array 结果集
     */
    public function get_express_by_page($filter) {
        $sql_main = "FROM `{$this->table}` AS pe INNER JOIN `base_express` AS be ON pe.`express_code`=be.`express_code` WHERE 1";
        $sql_values = array();
        $select = 'pe.`ploy_express_id`,pe.`ploy_code`,pe.`express_code`,be.`express_name`,pe.`express_status`,pe.`express_level`,pe.`express_ratio`,pe.`insert_time`';
        //快递策略编码
        if (isset($filter['ploy_code']) && $filter['ploy_code'] != '') {
            $ploy_info = load_model('op/ploy/ExpressPloyModel')->exists_ploy($filter['ploy_code']);
            if (empty($ploy_info)) {
                return array();
            }
            $ploy_status = $ploy_info['ploy_status'];

            $sql_main .= ' AND pe.`ploy_code`=:ploy_code ';
            $sql_values[':ploy_code'] = $filter['ploy_code'];
        } else {
            return array();
        }

        $sql_main .= ' ORDER BY pe.`insert_time` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['insert_time'] = empty($row['insert_time']) ? '' : date('Y-m-d H:i:s', $row['insert_time']);
            $row['express_ratio'] = $row['express_ratio'] * 100;
            $row['ploy_status'] = $ploy_status;
        }

        return $this->format_ret(1, $data);
    }

    /**
     * 根据策略代码获取策略快递
     * @param string $ploy_code 策略代码
     * @return arrray 数据集
     */
    public function get_express_by_ploy($ploy_code) {
        $sql = "SELECT express_code FROM {$this->table} WHERE ploy_code=:ploy_code";
        return $this->db->get_all($sql, array(':ploy_code' => $ploy_code));
    }

    /**
     * 根据ID判断策略是否存在某快递
     * @param int $ploy_express_id id
     * @return arrray 数据集
     */
    public function exists_express_by_id($ploy_express_id) {
        $sql = "SELECT ploy_express_id,ploy_code,express_code,express_status,express_level,express_ratio FROM {$this->table} WHERE ploy_express_id=:ploy_express_id";
        return $this->db->get_row($sql, array(':ploy_express_id' => $ploy_express_id));
    }

    /**
     * 新增策略快递
     * @param array $data 源数据
     * @return array 结果
     */
    public function express_add($data) {
        if (empty($data['express'])) {
            return $this->format_ret(-1, '', '未选择快递');
        }
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($data['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $ins_data = array();
        $temp = array(
            'express_status' => 0,
            'express_level' => 1,
            'express_ratio' => 0,
            'insert_time' => time(),
        );
        foreach ($data['express'] as $val) {
            $temp['ploy_code'] = $data['ploy_code'];
            $temp['express_code'] = $val['express_code'];
            $ins_data[] = $temp;
        }
        $ret = $this->insert_multi($ins_data, TRUE);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '新增快递失败');
        }
        $express_name = implode('；', array_column($data['express'], 'express_name'));
        load_model('op/ploy/ExpressPloyLogModel')->insert_log($data['ploy_code'], '新增快递配置', $express_name);

        return $this->format_ret(1, '', '新增快递成功');
    }

    /**
     * 删除快递
     * @param array $params 参数
     * @return array 结果
     */
    public function express_delete($params) {
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($params['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $ret = $this->check_express($params['ploy_express_id']);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $this->begin_trans();
        $expSetObj = load_model('op/ploy/ExpressPloyExpSetModel');
        $ploy_area = $expSetObj->get_express_set_by_where('pid', $params['ploy_express_id']);
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
            //删除快递配置
            $ret = parent::delete_exp('op_express_ploy_express_set', array('pid' => $params['ploy_express_id']));
            if ($ret != TRUE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除快递配置失败');
            }
        }
        //删除快递
        $ret = parent::delete(array('ploy_express_id' => $params['ploy_express_id']));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除快递主信息失败');
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($params['ploy_code'], '删除快递配置', "{$params['express_name']}");

        $this->commit();
        return $this->format_ret(1, '', '删除快递成功');
    }

    /**
     * 更新快递状态
     * @param array $params 参数
     * @return array 结果
     */
    public function express_active_update($params) {
        if (!in_array($params['active'], array(0, 1)) || empty($params['ploy_express_id'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($params['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $express_info = $this->exists_express_by_id($params['ploy_express_id']);
        if (empty($express_info)) {
            return $this->format_ret(-1, '', '策略不存在该快递，请返回列表检查');
        }

        $msg = $params['active'] == 1 ? '启用' : '停用';
        if ($express_info['express_status'] == $params['active']) {
            return $this->format_ret(2, '', "快递已{$msg}，不能重复操作");
        }

        $ret = $this->update(array('express_status' => $params['active']), array('ploy_express_id' => $params['ploy_express_id']));
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            return $this->format_ret(-1, '', "快递{$msg}失败");
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($params['ploy_code'], '更新快递状态', "{$msg}< {$params['express_name']} >");
        return $this->format_ret(1, '', "快递{$msg}成功");
    }

    /**
     * 编辑快递信息
     * @param array $data 数据
     * @return array 编辑结果
     */
    public function express_edit($data) {
        if (empty($data['ploy_express_id'])) {
            return $this->format_ret(-1, '', '数据有误，请刷新页面重试');
        }
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($data['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $express_info = $this->exists_express_by_id($data['ploy_express_id']);
        if (empty($express_info)) {
            return $this->format_ret(-1, '', '策略不存在该快递，请返回列表检查');
        }

        $log_msg = '';
        $up_data = array();
        if ($data['express_level'] != '') {
            if (!(is_int($data['express_level'] / 1) && $data['express_level'] > 0)) {
                return $this->format_ret(2, '', '优先级必须为正整数');
            }
            if ($express_info['express_level'] == $data['express_level']) {
                return $this->format_ret(2, '', '数据未变更');
            }
            $up_data['express_level'] = $data['express_level'];
            $log_msg .= "优先级由 {$express_info['express_level']} 改为 {$data['express_level']} ；";
        }
        if ($data['express_ratio'] != '') {
            if ($data['express_ratio'] < 0) {
                return $this->format_ret(2, '', '快递占比必须大于0');
            }
            $express_ratio_old = $express_info['express_ratio'] * 100;
            if ($express_ratio_old == $data['express_ratio']) {
                return $this->format_ret(2, '', '数据未变更');
            }
            $up_data['express_ratio'] = $data['express_ratio'] / 100;
            $log_msg .= "占比由 {$express_ratio_old} 改为 {$data['express_ratio']}；";
        }

        $ret = $this->update($up_data, array('ploy_express_id' => $data['ploy_express_id']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新失败');
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($data['ploy_code'], '更新快递信息', "< {$data['express_name']} >{$log_msg}");
        return $this->format_ret(1, '', '更新成功');
    }

    /**
     * 检查快递
     * @param int ploy_express_id 快递ID
     * @return array 数据
     */
    public function check_express($ploy_express_id) {
        $express_info = $this->exists_express_by_id($ploy_express_id);
        if (empty($express_info)) {
            return $this->format_ret(-1, '', '策略不存在该快递，请返回列表检查');
        }
        if ($express_info['express_status'] == 1) {
            return $this->format_ret(2, '', '快递已启用， 不能操作');
        }
        return $this->format_ret(1, $express_info);
    }

}
