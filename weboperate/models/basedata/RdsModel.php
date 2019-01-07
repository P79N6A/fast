<?php

/**
 * 基础数据-云数据库相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
class RdsModel extends TbModel {
    public $type_name = array(
        '1' => '独享',
        '2' => '共享',
    );
    function get_table() {
        return 'osp_aliyun_rds';
    }

    /*
     * 获取rds信息方法
     */
    function get_rds_info($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_value=array();
//        $sql_join = "left join osp_kehu kh on r.kh_id=kh.kh_id  ";
        $sql_join = "left join osp_kehu kh on r.kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} r $sql_join  WHERE 1";
        
        //客户名称搜索条件
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['client_name'] . "%'";
        }
        //服务商搜索条件dbname
        if (isset($filter['dbtype']) && $filter['dbtype'] != '') {
            $sql_main .= " AND r.rds_dbtype ='{$filter['dbtype']}'";
        }
        //rds实例名搜索条件
        if (isset($filter['dbname']) && $filter['dbname'] != '') {
            $sql_main .= " AND r.rds_dbname LIKE '%" . $filter['dbname'] . "%'";
        }
       //到期时间
        /*if (isset($filter['rds_endtime']) && $filter['rds_endtime'] != '') {
            $sql_main .= " AND rds_endtime <='" . $filter['rds_endtime']."'";
        }*/
        //到期时间
        if (!empty($filter['rds_endtime_start'])) {
            $sql_main .= " AND r.rds_endtime >= '".$filter['rds_endtime_start'] . " 00:00:00'";
        }
        if (!empty($filter['rds_endtime_end'])) {
            $sql_main .= " AND r.rds_endtime <= '".$filter['rds_endtime_end'] . " 23:59:59'";
        }
        //服务器用途
        if (isset($filter['server_use']) && $filter['server_use'] != '') {
            $sql_main .= " AND r.rds_server_use ='{$filter['server_use']}'";
        } 
        //rds连接地址
        if (isset($filter['rds_link']) && $filter['rds_link'] != '') {
            $sql_main .= " AND r.rds_link LIKE '%" . $filter['rds_link'] . "%'";
        }
        //状态
        if (isset($filter['rds_state']) && $filter['rds_state'] != '') {
            $sql_main .= " AND r.rds_state ='{$filter['rds_state']}'";
        }
        //模式
        if (isset($filter['ali_share_type']) && $filter['ali_share_type'] != '') {
            $sql_main .= " AND r.ali_share_type=:ali_share_type";
            $sql_value[':ali_share_type']=$filter['ali_share_type'];
        }
        //别名
        if (isset($filter['ali_another_name']) && $filter['ali_another_name'] != '') {
            $sql_main .= " AND r.ali_another_name=:ali_another_name ";
            $sql_value[':ali_another_name']=$filter['ali_another_name'];

        }
        //型号
        if (isset($filter['rds_server_model']) && $filter['rds_server_model'] != '') {
            $sql_main .= " AND r.rds_server_model=:rds_server_model ";
            $sql_value[':rds_server_model']=$filter['rds_server_model'];
        }
        //未绑定的客户
//        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
//            $sql_main .= " AND r.kh_id=:kh_id ";
//            $sql_value[':kh_id']=$filter['kh_id'];
//        }
        //排序条件
        $sql_main .= " order by r.rds_id desc";

        $select = 'r.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value,$select);
        $ret_status = OP_SUCCESS;
        
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('rds_dbtype|osp_cloud_server','rds_server_model|osp_cloud_db'));
        foreach($ret_data['data'] as &$value){
            $value['ali_share_type_name']=$this->type_name[$value['ali_share_type']];
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('rds_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('rds_createuser|osp_user_id','rds_updateuser|osp_user_id','kh_id|osp_kh'));
        return $this->format_ret($ret_status, $data);
//        return $this->get_row(array('rds_id' => $id));
    }

    /*
     * 添加新rds
     */
    function insert($rds) {
        if (isset($rds)){
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
     * 修改rds
     */
    function update($rds, $id) {
        if (isset($rds)){
           $ret = parent::update($rds, array('rds_id' => $id));
           return $ret;
        }
    }

    /*
     * 部署操作
     */
    function update_deployment($id){
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
    function update_rds_pass($pwd,$rdsid ) {
        $data = array('rds_pass' => $pwd);
        $result = parent::update($data, array('rds_id' => $rdsid));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    //加密密码
    function encrypt($data){
        if(isset($data)){
            $ret = parent::encrypt($data);
            return $ret;
        }
    }
    //解密密码
    function decrypt($data){
        if(isset($data)){
            $ret = parent::decrypt($data);
            return $ret;
        }
    }
    
    //rds服务器连接测试
    function rds_net_test($ip, $user, $pwd) {
        if (isset($ip) && isset($ip) && isset($pwd)) {
//            print_r($ip);print_r($user);print_r($pwd);die;
            $connection = mysql_connect("$ip","$user","$pwd");
            if (!$connection) {
                return $this->format_ret("-1", '', '连接失败,可能密码不对');
            } else {
                mysql_close($connection);
                return $this->format_ret("1", "", '连接成功');
            }
        } else {
            return $this->format_ret("-1", '', '账号密码不能为空.');
        }
    }
    
    

}
