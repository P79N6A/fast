<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');

class CustomGradesModel extends TbModel {

    function get_table() {
        return 'fx_custom_grades';
    }

    public $custom_type = array(
        'tb_fx' => '淘宝分销',
        'pt_fx' => '普通分销'
    );

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = " LEFT JOIN fx_custom_grades_detail r2 on rl.grade_code = r2.grade_code";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (rl.grade_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        $select = 'rl.*';
        $sql_main .= " group by grade_code";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_detail_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM fx_custom_grades_detail rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['grade_code']) && $filter['grade_code'] != '') {
            $sql_main .= " AND rl.grade_code = :grade_code";
            $sql_values[':grade_code'] = $filter['grade_code'];
        }
        $select = 'rl.*';

        //$data =  $this->get_page_from_sql($filter, $sql_main, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            if (!empty($value['custom_type'])) {
                $value['custom_type_name'] = $this->custom_type[$value['custom_type']];
            } else {
                $value['custom_type_name'] = '';
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    /*
     * 添加新纪录
     */

    function insert($custom_grade_info) {
        $ret = $this->valid($custom_grade_info);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->is_exists($custom_grade_info['grade_name'], 'grade_name');
        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', '分销商等级名称已存在');
        }
        return parent::insert($custom_grade_info);
    }

    /*
     * 删除记录
     * */

    function delete_grades($grade_code) {
        $this->begin_trans();
        try {
            $ret = parent::delete(array('grade_code' => $grade_code));
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $sql = "delete from fx_custom_grades_detail where grade_code = :grade_code";
            $delete_ret = $this->db->query($sql, array(":grade_code" => $grade_code));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除明细失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }

        return $ret;
    }
    
    function delete_grade_detail($id){
        $data = $this->get_by_detail($id, 'id');
        $this->begin_trans();
        //回写分销商档案
        $ret = $this->update_exp('base_custom', array('custom_grade' => ''), array('custom_code' => $data[0]['custom_code']));
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除明细失败！');
        }
        
        $sql = "delete from fx_custom_grades_detail where id = :id";
        $delete_ret = $this->db->query($sql, array(":id" => $id));
        if ($delete_ret == FALSE) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除明细失败！');
        }
        $this->commit();
        return $this->format_ret(1,'','删除成功');
    }

    /*
     * 修改纪录
     */

    function update($custom_grade_info, $grade_id) {
        $ret = $this->valid($custom_grade_info);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->db->get_row("select * from {$this->table} where grade_name = :grade_name and grade_id != :grade_id",array(":grade_name" => $custom_grade_info['grade_name'],":grade_id" => $grade_id));
        if (!empty($ret)) {
            return $this->format_ret(-1, '', '分销商等级名称已存在,修改失败');
        }
        $ret = parent::update($custom_grade_info, array('grade_id' => $grade_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data) {
        if (!isset($data['grade_name']) || !valid_input($data['grade_name'], 'required')) {
            return $this->format_ret(-1, '', '等级名称不能为空');
        }
        return 1;
    }

    function is_exists($value, $field_name = 'grade_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select grade_code  from {$this->table} order by grade_code desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['grade_code']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = add_zero($djh, 3);
        return $jdh;
    }

    public function insert_custom($data) {
        if (empty($data['grade_code'])) {
            return $this->format_ret(-1, '', '分销商等级编号为空!设置失败');
        }
        if(empty($data['data'])) {
            return $this->format_ret(1, '', '没有分销商');
        }
        $params = array();
        $custom_arr = array();
        foreach ($data['data'] as $val) {
            $p = array();
            $p['grade_code'] = $data['grade_code'];
            $p['custom_type'] = $val['custom_type'];
            $p['custom_code'] = $val['custom_code'];
            $p['custom_name'] = $val['custom_name'];
            $custom_arr[] = $val['custom_code'];
            $params[] = $p;
        }
        $this->begin_trans();
        try {
            if(!empty($custom_arr)) {
                $sql_values = array();
                $custom_str = $this->arr_to_in_sql_value($custom_arr,'custom_code',$sql_values);
                $sql = "DELETE FROM fx_custom_grades_detail WHERE custom_code IN ({$custom_str}) ";
                $ret = $this->query($sql, $sql_values);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                //回写分销商档案
                $sql_values = array();
                $sql_values[':custom_grade'] = $data['grade_code'];
                $custom_str = $this->arr_to_in_sql_value($custom_arr,'custom_code',$sql_values);
                $sql1 = "UPDATE base_custom SET custom_grade = :custom_grade WHERE custom_code IN ({$custom_str})";
                $ret = $this->query($sql1, $sql_values);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                
            }
            $ret = M('fx_custom_grades_detail')->insert_multi($params, true);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $update_ret = $this->update_custom_num($data['grade_code']);
            if ($update_ret) {
                $this->commit();
                return $this->format_ret(1);
            } else {
                $this->rollback();
                return $this->format_ret(-1, '', '更新分销数量失败！');
            }
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }



        return $ret;
    }

    function delete_custom($data) {
        if (empty($data['grade_code'])) {
            return $this->format_ret(-1, '', '分销商等级编号为空!设置失败');
        }
        $grade_detail = $this->get_by_detail($data['grade_code']);
        $custom_arr = array_column($grade_detail, 'custom_code');
        $this->begin_trans();
        try {
            //回写分销商档案
            if(!empty($custom_arr)) {
                $sql_values = array();
                $custom_str = $this->arr_to_in_sql_value($custom_arr,'custom_code',$sql_values);
                $sql = "UPDATE base_custom SET custom_grade = '' WHERE custom_code IN ({$custom_str}) ";
                $ret = $this->query($sql, $sql_values);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '回写分销商档案失败！');
                }
            }
            $sql = "delete from fx_custom_grades_detail where grade_code = :grade_code";
            $delete_ret = $this->db->query($sql, array(":grade_code" => $data['grade_code']));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $update_ret = $this->update_custom_num($data['grade_code']);
            if ($update_ret == false) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新分销数量失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }

        return $this->format_ret(-1, '', '删除失败');
    }

    function update_custom_num($grade_code) {
        $sql = "select count(grade_code) from fx_custom_grades_detail where grade_code = :grade_code";
        $count = $this->db->get_value($sql, array(":grade_code" => $grade_code));
        $update_sql = "update fx_custom_grades set custom_num = {$count} where grade_code = :grade_code";
        $ret = $this->db->query($update_sql, array(":grade_code" => $grade_code));
        return $ret;
    }

    function get_all_grades($type = 1) {
        $sql = "select grade_code,grade_name from {$this->table}";
        $grades = $this->db->get_all($sql);
        if($type == 1) {
            $data = array_merge(array(array('', '请选择')), $grades);
        } else if($type == 2) {
            //多选
            $data = $grades;
        }
        return $data;
    }

    function get_by_id($id) {
        return $this->get_row(array('grade_id' => $id));
    }
    
     function get_by_code($code,$select = '*') {
        $sql = "SELECT {$select} FROM fx_custom_grades WHERE grade_code = :grade_code";
        return $this->db->get_row($sql,array(':grade_code' => $code));
    }
    function get_by_detail($filter, $where = 'grade_code', $select = '*') {
        $sql = "SELECT {$select} FROM fx_custom_grades_detail WHERE {$where} = :{$where}";
        return $this->db->get_all($sql,array(':' . $where => $filter));
    }

}
