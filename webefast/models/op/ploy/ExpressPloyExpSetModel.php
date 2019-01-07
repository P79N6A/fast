<?php

require_model('tb/TbModel');

/**
 * 快递策略-快递配置
 * @author WMH
 */
class ExpressPloyExpSetModel extends TbModel {

    protected $table = 'op_express_ploy_express_set';
    protected $area_table = 'op_express_ploy_area';
    protected $freight_table = 'op_express_ploy_freight';

    public function get_by_page($filter) {
        $sql_join = "LEFT JOIN `{$this->area_table}` AS pa ON es.`express_set_id`=pa.`pid` 
                    LEFT JOIN `{$this->freight_table}` AS pf ON es.`express_set_id`=pf.`pid`";
        $sql_main = "FROM `{$this->table}` AS es {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'es.`express_set_id`,es.`express_set_name`,es.`pid`,es.`ploy_code`,COUNT(pf.`ploy_freight_id`) AS freight_cout';
        //策略快递id
        if (isset($filter['ploy_express_id']) && $filter['ploy_express_id'] != '') {
            $sql_main .= ' AND es.`pid`=:ploy_express_id ';
            $sql_values[':ploy_express_id'] = $filter['ploy_express_id'];
        } else {
            return array();
        }

        if (isset($filter['express_set_id']) && $filter['express_set_id'] != '') {
            $sql_main .= ' AND es.`express_set_id`=:express_set_id ';
            $sql_values[':express_set_id'] = $filter['express_set_id'];
        }

        $sql_main .= 'GROUP BY es.`express_set_id`';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);

        foreach ($data['data'] as &$row) {
            $row['area'] = $this->get_area_by_pid($row['express_set_id']);
            $row['area'] .= empty($row['area']) ? '' : '...';
            $row['is_set'] = $row['freight_cout'] > 0 ? 1 : 0;
            unset($row['freight_cout']);
        }

        return $this->format_ret(1, $data);
    }

    private function get_area_by_pid($pid) {
        $sql = 'SELECT ba.name FROM op_express_ploy_area AS pa INNER JOIN base_area AS ba ON pa.area_id=ba.id WHERE pa.pid=:pid ORDER BY ba.id';
        $area_name = $this->db->get_col($sql, array(':pid' => $pid), 5);
        return implode('；', $area_name);
    }

    /**
     * 根据条件获取快递配置信息
     * @param string $fld 条件字段
     * @param string $value 条件值
     * @return array 数据集
     */
    public function get_express_set_by_where($fld, $value) {
        $sql = "SELECT express_set_id,express_set_name,pid,ploy_code,express_code FROM {$this->table} WHERE {$fld}=:{$fld}";
        return $this->db->get_all($sql, array(":{$fld}" => $value));
    }

    private function get_freight_by_set_id($express_set_id, $fld = '*') {
        $sql = "SELECT {$fld} FROM {$this->freight_table} WHERE pid=:pid";
        return $this->db->get_all($sql, array(':pid' => $express_set_id));
    }

    public function delete_express_area_by_pid($pid_arr) {
        $sql_values = array();
        $pid_str = $this->arr_to_in_sql_value($pid_arr, 'pid', $sql_values);
        $sql = "DELETE FROM {$this->area_table} WHERE pid IN({$pid_str})";
        return $this->query($sql, $sql_values);
    }

    public function delete_area_freight_by_pid($pid_arr) {
        $sql_values = array();
        $pid_str = $this->arr_to_in_sql_value($pid_arr, 'pid', $sql_values);
        $sql = "DELETE FROM {$this->freight_table} WHERE pid IN({$pid_str})";
        return $this->query($sql, $sql_values);
    }

    //取得子类
    function get_child($p_code, $express_set_id, $ploy_express_id) {
        if ($p_code != '') {
            $no_select = array('820000', '810000', '710000');
            $other_area = array('441900000000', '442000000000'); //东莞市 ，中山市
            $no_select_where = implode(',', $no_select);
            $sql = "select id,name,parent_id,type FROM base_area WHERE parent_id = '$p_code' AND id not in({$no_select_where}) ";

            $rs = $this->db->get_all($sql);
            $data = array();
            $checked_arr = array();
            $type = $rs[0]['type'];
            $check_data = $this->get_no_checked_node($p_code, $type, $express_set_id, $ploy_express_id);

            foreach ($rs as $k => $v) {
                $is_check = 0;
                if (isset($check_data['checked'][$v['id']])) {
                    $data[$k]['checked'] = true;
                    $is_check = 1;
                }

                if ($is_check == 0 && isset($check_data['no_checked'][$v['id']])) {
                    $is_check = 1;
                    $data[$k]['checked'] = false;
                }

                $data[$k]['id'] = $v['id'];
                $data[$k]['text'] = $v['name'];
                if ($v['type'] < 4) {
                    $data[$k]['leaf'] = false;
                }
                if (in_array($v['id'], $other_area)) {
                    $checked_arr = $this->get_other_node($v['id'], $express_set_id);
                    if (!empty($checked_arr)) {
                        $data[$k] = array_merge($data[$k], $checked_arr);
                    }
                    unset($data[$k]['leaf']);
                }
            }
        }

        return $data;
    }

    function get_other_node($area_id, $express_set_id) {
        $sql = "  select pid from {$this->area_table} where  area_id=:area_id";
        $sql_values = array(':area_id' => $area_id);
        $data = $this->db->get_row($sql, $sql_values);
        $check_arr = array();
        if (empty($data)) {
            $check_arr['checked'] = FALSE;
        } else {
            if ($express_set_id == $data['pid']) {
                $check_arr['checked'] = TRUE;
            }
        }

        return $check_arr;
    }

    function get_no_checked_node($area_id, $type, $express_set_id, $ploy_express_id) {

        $area_sql[2] = array(
            'checked' => "SELECT DISTINCT parent_id from  base_area a
                                ,(select DISTINCT  parent_id as id from base_area  WHERE  id in(
                                select area_id from {$this->area_table} where pid='{$express_set_id}'
                                ) AND type=4  ) as b
                    where a.id=b.id",
            'no_checked' => "SELECT DISTINCT a.parent_id from  base_area  a
                            ,( select  DISTINCT parent_id as id from base_area
                            WHERE  id not in(
                                     select area_id from {$this->area_table} where ploy_express_id='{$ploy_express_id}'
                                ) AND type=4    ) b
                            where a.id=b.id ",
        );

        $area_sql[3] = array(
            'checked' => " select DISTINCT  parent_id from base_area  
                 WHERE parent_id in( select id from base_area where parent_id='{$area_id}'  )  
                   AND  id in(  select area_id from {$this->area_table} where pid='{$express_set_id}')",
            'no_checked' => " select DISTINCT  parent_id from base_area 
                   WHERE parent_id in( select id from base_area where parent_id='{$area_id}'  )  
                   AND  id not in( select area_id from {$this->area_table}  where ploy_express_id='{$ploy_express_id}') AND type=4           ",
        );

        $area_sql[4] = array(
            'checked' => " select id as parent_id  from base_area  
                 WHERE  parent_id='{$area_id}' 
                   AND  id in(  select area_id from {$this->area_table} where pid={$express_set_id}  )   ",
            'no_checked' => " select  id as parent_id  from base_area 
                   WHERE  parent_id='{$area_id}' 
                   AND  id not in( select area_id from {$this->area_table} where ploy_express_id={$ploy_express_id}  ) AND TYPE=4           ",
        );
        $ret_data = array();

        foreach ($area_sql[$type] as $key => $sql) {
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                $ret_data[$key][$val['parent_id']] = $val['parent_id'];
            }
        }

        $other_area = array('441900000000', '442000000000'); //东莞市 ，中山市 440000
        foreach ($other_area as $key => $val) {
            $check_arr = $this->get_other_node($val, $express_set_id);
            if (!empty($check_arr)) {
                if ($check_arr['checked']) {
                    $ret_data['checked']['440000'] = '440000';
                } else {
                    $ret_data['no_checked']['440000'] = '440000';
                }
            }
        }

        return $ret_data;
    }

    function get_freight($express_set_id) {
        $sql = "SELECT ploy_freight_id,pid,first_weight,first_weight_price,added_weight,added_weight_price,added_weight_rule,free_quota,rebate FROM {$this->freight_table} WHERE pid=:pid";
        return $this->db->get_all($sql, array(':pid' => $express_set_id));
    }

    /**
     * 保存快递配置
     * @param type $data
     * @return type
     */
    function save_express_set($data) {
        $ploy_express_id = $data['ploy_express_id'];
        $express_set_id = $data['express_set_id'];
        $express_info = load_model('op/ploy/ExpressPloyExpModel')->exists_express_by_id($ploy_express_id);
        if (empty($express_info)) {
            return $this->format_ret(-1, '', '策略不存在该快递，请返回列表检查');
        }
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($express_info['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }
        if ($express_set_id == 0 && empty($data['area_data'])) {
            return $this->format_ret(2, '', '请选择目的区域');
        }
        $msg = '更新';
        $this->begin_trans();
        //保存配置主表数据
        if ($express_set_id == 0) {
            $express_set_data = array(
                'express_set_name' => $data['express_set_name'],
                'pid' => $ploy_express_id,
                'ploy_code' => $express_info['ploy_code'],
                'express_code' => $express_info['express_code'],
            );
            $ret = $this->insert($express_set_data);
            if ($ret['status'] < 1 || $this->affected_rows() <> 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '新增配置主信息失败');
            }
            $express_set_id = $ret['data'];
            $msg = '新增';
        }
        $this->update(array('express_set_name' => $data['express_set_name']), array('express_set_id' => $express_set_id));

        $is_change = 0;
        if (!empty($data['area_data'])) {
            //保存区域数据
            $ret = $this->save_area_set($data['area_data'], $ploy_express_id, $express_set_id);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $is_change = 1;
        }
        //保存区域运费数据
        if (!empty($data['freight_data'])) {
            $ret = $this->save_freight_set($data['freight_data'], $express_set_id);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '区域运费处理失败');
            }
            $is_change = 1;
        }
        if ($is_change == 1) {
            load_model('op/ploy/ExpressPloyLogModel')->insert_log($express_info['ploy_code'], $msg . '配置', '配置名称：' . $data['express_set_name']);
        }
        $msg = $is_change == 1 ? $msg . '配置成功' : '未变更数据';
        $this->commit();
        return $this->format_ret(1, $express_set_id, $msg);
    }

    private function save_area_set($area_data, $ploy_express_id, $express_set_id) {
        $data = array();
        foreach ($area_data as $val) {
            $data[$val['id']] = $val;
        }
        foreach ($data as $val) {
            $area = $this->get_area_data_by_type($val, $ploy_express_id, $express_set_id);
            if ($val['checked'] == 1) {
                $ret = $this->insert_multi_exp($this->area_table, $area, TRUE);
            } else if (!empty($area)) {
                $sql_values = array(':ploy_express_id' => $ploy_express_id, ':pid' => $express_set_id);
                $del_area_id = array_column($area, 'area_id');
                $del_area_id_str = $this->arr_to_in_sql_value($del_area_id, 'area_id', $sql_values);
                $sql = "DELETE FROM {$this->area_table} WHERE area_id IN({$del_area_id_str}) AND ploy_express_id=:ploy_express_id AND pid=:pid";
                $ret = $this->db->query($sql, $sql_values);
            }
            if (isset($ret['status']) && $ret['status'] < 1 || $ret === FALSE) {
                return $this->format_ret(-1, '', '区域处理失败');
            }
        }
        return $this->format_ret(1);
    }

    private function save_freight_set($freight_data, $express_set_id) {
        $key_required = array(
            'i' => array('first_weight', 'first_weight_price', 'added_weight', 'added_weight_price', 'added_weight_rule', 'free_quota', 'rebate'),
        );
        $freight_id_arr = array();
        foreach ($freight_data as $k => &$val) {
            $d_required = array();
            $ret_required = valid_assign_array($val, $key_required, $d_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                unset($freight_data[$k]);
                continue;
            }
            $val['pid'] = $express_set_id;
            if (!empty($val['ploy_freight_id'])) {
                $freight_id_arr[] = $val['ploy_freight_id'];
            } else {
                $val['ploy_freight_id'] = '';
            }
        }
        if (!empty($freight_data)) {
            //获取已存在的运费配置id
            $freight_exists = $this->get_freight_by_set_id($express_set_id, 'ploy_freight_id');
            $freight_exists = array_column($freight_exists, 'ploy_freight_id');
            $freight_diff = array_diff($freight_exists, $freight_id_arr);
            if (!empty($freight_diff)) {
                $sql_values = array();
                $freight_diff_str = $this->arr_to_in_sql_value($freight_diff, 'ploy_freight_id', $sql_values);
                $sql = "DELETE FROM {$this->freight_table} WHERE ploy_freight_id IN({$freight_diff_str})";
                $this->query($sql, $sql_values);
            }
            $update_str = 'first_weight=VALUES(first_weight),first_weight_price=VALUES(first_weight_price),added_weight=VALUES(added_weight),added_weight_price=VALUES(added_weight_price),added_weight_rule=VALUES(added_weight_rule),free_quota=VALUES(free_quota),rebate=VALUES(rebate)';
            $ret = $this->insert_multi_duplicate($this->freight_table, $freight_data, $update_str);
            return $ret;
        }
        return $this->format_ret(1);
    }

    function get_area_data_by_type($param, $ploy_express_id, $express_set_id) {
        $area_id = $param['id'];
        if ($param['type'] == 4) {
            $data = array(array('area_id' => $area_id, 'ploy_express_id' => $ploy_express_id, 'pid' => $express_set_id));
            return $data;
        }

        $no_select = array('820000', '810000', '710000');
        $other_area = array('441900000000', '442000000000'); //东莞市 ，中山市 广东440000
        $no_select_where = implode(',', $no_select);

        $sql = "";
        $type = $param['type'];
        switch ($type) {
            case 1:
                $sql.= " AND parent_id IN(SELECT id FROM base_area WHERE parent_id IN(SELECT id FROM base_area WHERE parent_id='{$area_id}') AND id NOT IN({$no_select_where}) ) ";
                break;
            case 2:
                $sql.= " AND parent_id in(select id FROM base_area where parent_id='{$area_id}') ";
                break;
            case 3:
                $sql.= in_array($area_id, $other_area) ? " AND id='{$area_id}' " : " AND parent_id='{$area_id}' ";
                break;
            default :
        }

        if ($type == 1 || $area_id == '440000') {
            $sql = " AND ( ({$sql}) OR ( id IN('" . implode("','", $other_area) . "') ) )";
        }

        $sql_values = array(':ploy_express_id' => $ploy_express_id);
        if ($param['checked'] == 1) {
            $sql.= "AND id NOT IN(SELECT area_id FROM {$this->area_table} WHERE ploy_express_id=:ploy_express_id)";
        } else {
            $sql.= "AND id IN(SELECT area_id FROM {$this->area_table} WHERE ploy_express_id=:ploy_express_id AND pid=:pid )";
            $sql_values[':pid'] = $express_set_id;
        }

        $sql = "SELECT id AS area_id,'{$ploy_express_id}' AS ploy_express_id,'{$express_set_id}' AS pid FROM base_area WHERE 1 {$sql}";

        return $this->db->get_all($sql, $sql_values);
    }

    /**
     * 删除配置
     * @param array $params 参数
     * @return array 结果
     */
    public function delete_express_set($params) {
        if (empty($params['ploy_express_id']) || empty($params['express_set_id'])) {
            return $this->format_ret(-1, '', '参数错误，请刷新页面重试');
        }
        $express_set_id = $params['express_set_id'];
        $set_info = $this->get_express_set_by_where('express_set_id', $express_set_id);
        if (empty($set_info)) {
            return $this->format_ret(-1, '', '配置项不存在，请返回列表检查');
        }
        $express_info = load_model('op/ploy/ExpressPloyExpModel')->exists_express_by_id($params['ploy_express_id']);
        if (empty($express_info)) {
            return $this->format_ret(-1, '', '策略不存在该快递，请返回列表检查');
        }
        $ret = load_model('op/ploy/ExpressPloyModel')->check_ploy($express_info['ploy_code']);
        if ($ret['status'] != 1) {
            return $ret;
        }


        $this->begin_trans();
        $ret = $this->delete_express_area_by_pid(array($express_set_id));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除区域配置失败');
        }
        //删除区域运费配置失败
        $ret = $this->delete_area_freight_by_pid(array($express_set_id));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除运费配置失败');
        }
        //删除配置
        $ret = parent::delete(array('express_set_id' => $express_set_id));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除配置失败');
        }

        load_model('op/ploy/ExpressPloyLogModel')->insert_log($express_info['ploy_code'], '删除配置', $set_info['express_set_name']);
        $this->commit();
        return $this->format_ret(1, $express_set_id, '删除配置成功');
    }

}
