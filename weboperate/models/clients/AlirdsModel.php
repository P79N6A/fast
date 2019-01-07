<?php

/**
 * 客户中心-云数据库相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class AlirdsModel extends TbModel {

    function get_table() {
        return 'osp_aliyun_rds';
    }

    /*
     * 获取rds信息方法
     */

    function get_rds_info($filter) {
        $sql_join = "left join osp_kehu kh on r.kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} r $sql_join  WHERE 1";

        //客户名称搜索条件
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['client_name'] . "%'";
        }
        //平台类型搜索条件dbname
        if (isset($filter['dbtype']) && $filter['dbtype'] != '') {
            $sql_main .= " AND r.rds_dbtype ='{$filter['dbtype']}'";
        }
        //rds实例名搜索条件
        if (isset($filter['dbname']) && $filter['dbname'] != '') {
            $sql_main .= " AND r.rds_dbname LIKE '%" . $filter['dbname'] . "%'";
        }
        //到期时间
        /* if (isset($filter['rds_endtime']) && $filter['rds_endtime'] != '') {
          $sql_main .= " AND r.rds_endtime <='" . $filter['rds_endtime']."'";
          } */
        //到期时间
        if (!empty($filter['rds_endtime_start'])) {
            $sql_main .= " AND r.rds_endtime >= '" . $filter['rds_endtime_start'] . " 00:00:00'";
        }
        if (!empty($filter['rds_endtime_end'])) {
            $sql_main .= " AND r.rds_endtime <= '" . $filter['rds_endtime_end'] . " 23:59:59'";
        }
        //部署条件
        if (isset($filter['rds_deployment']) && $filter['rds_deployment'] != '') {
            $sql_main .= " AND r.rds_deployment='{$filter['rds_deployment']}'";
        }
        //状态
        if (isset($filter['rds_state']) && $filter['rds_state'] != '') {
            $sql_main .= " AND r.rds_state='{$filter['rds_state']}'";
        }

        //rds连接地址
        if (isset($filter['rds_link']) && $filter['rds_link'] != '') {
            $sql_main .= " AND rds_link LIKE '%" . $filter['rds_link'] . "%'";
        }

        //排序条件
        $sql_main .= " AND r.kh_id !=0 order by rds_id desc";

        $select = 'r.*,kh.kh_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('rds_dbtype|osp_cloud_server'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('rds_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('kh_id|osp_kh', 'rds_dbtype|osp_cloud_server', 'rds_createuser|osp_user_id', 'rds_updateuser|osp_user_id'));
        return $this->format_ret($ret_status, $data);
//        return $this->get_row(array('rds_id' => $id));
    }

    /*
     * 添加主机
     */

    function insert($rds) {
        if (isset($rds)) {
            $this->format_ret($rds);
        }
//        $ret = $this->is_exists($rds['rds_dbname']);
//        if ($ret['status'] > 0 && !empty($ret['data']))
//            return $this->format_ret(USER_ERROR_UNIQUE_CODE);
        $ret = $this->is_exists($rds['rds_dbname'], 'rds_dbname');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(name_is_exist);

        return parent::insert($rds);
    }

    /*
     * 修改客户信息。
     */

    function update($rds, $id) {
        if (isset($rds)) {
            $ret = parent::update($rds, array('rds_id' => $id));
            return $ret;
        }
    }

    /*
     * 部署操作
     */

    function update_deployment($id) {
        $ret = parent::update(array('rds_deployment' => '1'), array('rds_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'rds_dbname') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //获取rds用户密码
    function get_rds_pwd($rdsid) {
        $ret = $this->get_row(array('rds_id' => $rdsid));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }

    //更新rds密码
    function update_rds_pass($pwd, $rdsid) {
        $data = array('rds_pass' => $pwd);
        $result = parent::update($data, array('rds_id' => $rdsid));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    //加密密码
    function encrypt($data) {
        if (isset($data)) {
            $ret = parent::encrypt($data);
            return $ret;
        }
    }

    //解密密码
    function decrypt($data) {
        if (isset($data)) {
            $ret = parent::decrypt($data);
            return $ret;
        }
    }

    //更新数据库状态（启用/停用）
    function update_rds_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }

        $ret = parent::update(array('rds_state' => $active), array('rds_id' => $id));
        return $ret;
    }

    function get_select() {
        $sql = " select rds_id,rds_dbname,rds_notes from osp_aliyun_rds where rds_state=1  AND  rds_dbtype=1 ";
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach ($data as $val) {
            $val['rds_dbname'] .= empty($val['rds_notes']) ? '' : "({$val['rds_notes']})";
            $arr[] = array(
                $val['rds_id'],
                $val['rds_dbname']
            );
        }
        return $arr;
    }

}
