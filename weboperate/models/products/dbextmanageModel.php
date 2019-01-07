<?php

/**
 * 产品中心-产品数据库扩展管理
 *
 * @author zyp
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class dbextmanageModel extends TbModel {

    function get_table() {
        return 'osp_rdsextmanage_db';
    }

    /*
     * 根据条件查询数据,分页列表数据
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        $sql_values=array();
        if (isset($filter['product']) && $filter['product'] != '') {
            $sql_main .= " AND rem_db_pid in (SELECT rem_rds_id from osp_rdsextmanage WHERE rem_cp_id =" . $filter['product'] . ")";
        }

        if (isset($filter['bind_kh']) && $filter['bind_kh'] != '') {
            $sql_main .= " AND rem_db_is_bindkh = " . $filter['bind_kh'];
        }
        //启用，停用
        if (isset($filter['rem_db_is_bindkh']) && $filter['rem_db_is_bindkh'] != '') {
            $sql_main .= " AND rem_db_is_bindkh =:rem_db_is_bindkh ";
            $sql_values[':rem_db_is_bindkh'] = $filter['rem_db_is_bindkh'];
        }
        if (isset($filter['kh_name']) && $filter['kh_name'] != '') {
            $sql_main .= " AND rem_db_khid in (SELECT kh_id from osp_kehu where kh_name like '%{$filter['kh_name']}%') ";
        }
        //数据库名称
        if (isset($filter['rem_db_name']) && $filter['rem_db_name'] != '') {
            $sql_main .= " AND rem_db_name = :rem_db_name ";
            $sql_values[':rem_db_name']=$filter['rem_db_name'];
        }
        //指定rds的数据库
        if (isset($filter['dbext']) && $filter['dbext'] == 1) {
            //数据库名称
            if (isset($filter['rds_id']) && $filter['rds_id'] != '') {
                $sql_main .= " AND rem_db_pid = :rem_db_pid ";
                $sql_values[':rem_db_pid'] = $filter['rds_id'];
            } else {
                $sql_main .= " AND 1 = 2 ";
            }
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main,$sql_values,$select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('rem_db_pid|osp_rdsinfo', 'rem_db_khid|osp_kh', 'rem_db_version|osp_chanpin_version'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function insert($dbinfo,$pid) {
        $ret = $this->is_exists($dbinfo['rem_db_name'], 'rem_db_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        $data = parent::insert($dbinfo);
        if (isset($data['status'])) {
            //更新数量
            $sql_main = "update osp_rdsextmanage set rem_dbnum=rem_dbnum+1,rem_dbunnum=rem_dbunnum+1 WHERE rem_rds_id=:rem_rds_id ";
            $sql_values[':rem_rds_id'] = $pid;
            $data = $this->db->query($sql_main, $sql_values);
            return $this->format_ret("1", $data, '添加成功');
        } else {
            return $this->format_ret("-1", '', '添加失败');
        }
    }

    private function is_exists($value, $field_name = 'rem_db_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    //删除试用客户数据库和未绑定客户数据库
    function delete_db($id, $is_bindkh, $pid) {
        $data = parent::delete(array('rem_db_id' => $id));
        if (isset($data['status'])) {
            //更新数量
            $sql_main = "update osp_rdsextmanage set rem_dbnum=rem_dbnum-1 WHERE rem_rds_id=:rem_rds_id ";
            $sql_values[':rem_rds_id'] = $pid;
            $data = $this->db->query($sql_main, $sql_values);
            return $this->format_ret("1", $data, '删除成功');
        } else {
            return $this->format_ret("-1", '', '删除失败');
        }
    }
    
    
       function get_sysdb($atask, $id) {
            $ret = parent::get_row(array('rem_db_name' => 'sysdb'));
            return $ret;
    }
    
    function get_by_bindinfo($dbid){
        $sql_main="select db.rem_db_id,rds.rem_rds_id,rds.rem_cp_id,db.rem_db_name,db.rem_db_sys_version from osp_rdsextmanage_db db "
                . "left join osp_rdsextmanage rds on db.rem_db_pid=rds.rem_rds_id where rem_db_id=:rem_db_id"; 
        $sql_values[':rem_db_id'] = $dbid;
        $data=$this->db->get_row($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('rem_rds_id|osp_rdsinfo', 'rem_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $data);
    }
    
    //客户绑定操作
    function do_bind_dbextmanage($dbid,$rdsid,$khid){
        //首先验证客户授权
        $pra_product_version_map = array('1'=>'efast5_Standard','2'=>'efast5_Enterprise','3'=>'efast5_Ultimate');
        $sql = "select pra_id,pra_cp_id,pra_kh_id,pra_enddate,pra_state,pra_authkey,pra_authnum,pra_serverpath,pra_product_version from osp_productorder_auth where pra_kh_id = :pra_kh_id";
        $db_auth_row = $this->db->get_row($sql,array(':pra_kh_id'=>$khid));
        if(empty($db_auth_row)){
            return $this->format_ret(-1,'',"授权不存在");
        }
        $pra_enddate = $db_auth_row['pra_enddate'];
        if(strtotime($pra_enddate)<time()){
            return $this->format_ret(-1,'',"授权已超期");
        }
        if((int)$db_auth_row['pra_state'] == 0){
            return $this->format_ret(-1,'',"授权已禁用");
        }
        $user_cp_code = @$pra_product_version_map[$db_auth_row['pra_product_version']];
        if(empty($user_cp_code)){
            return $this->format_ret(-1,'',"授权关联的产品版本标识有错");
        }
        //判断是否有指定的自动服务
        $sql = "select ali_outip from osp_autoservice_acc acc,osp_aliyun_host vm where acc.asa_vm_id = vm.host_id and asa_cp_id =:asa_cp_id  and asa_rds_id =:asa_rds_id ";
        $ali_outip = $this->db->get_value($sql,array(':asa_cp_id'=>$db_auth_row['pra_cp_id'],':asa_rds_id'=>$rdsid));
        if(empty($ali_outip)){
            return $this->format_ret(-1,'','自动服务分配到的VM_IP为空');
        }
        //初始化待绑定数据库 
        //第一步：获取RDS信息，连接数据库
        $sql="SELECT * from osp_aliyun_rds where rds_id=:rds_id"; 
        $rdsinfo=$this->db->get_row($sql, array(':rds_id'=>$rdsid));
        //解密连接信息
        $keylock=get_keylock_string($rdsinfo['rds_createdate']);
        $rdspwd= create_aes_decrypt($rdsinfo['rds_pass'],$keylock);
        //获取数据库名称
        $db_name=$this->db->get_value("select rem_db_name from osp_rdsextmanage_db where rem_db_id=:rem_db_id", array(":rem_db_id" => $dbid));
        //获取客户名称
        $kh_name=$this->db->get_value("select kh_name from osp_kehu where kh_id=:kh_id", array(":kh_id" => $khid));
        //创建数据库连接
        CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');
        CTX()->db_kh->set_conf(array(
            'name' => $db_name,
            'host' => $rdsinfo['rds_link'],
            'user' => $rdsinfo['rds_user'],
            'pwd' => $rdspwd,
            'type' => 'mysql',
        ));
        $db = CTX()->db_kh;
        //初始化业务库授权信息
        $info = array();
        $info['version'] = 'v5.0.1';
        $info['company_name'] = $kh_name;
        $info['auth_key'] = $db_auth_row['pra_authkey'];
        $info['auth_num'] = $db_auth_row['pra_authnum'];
        $info['cp_code'] = $user_cp_code;
        $info['login_server_url'] = "http://login.baotayun.com/efast/weblogin/web/index.php";
        $info['kh_id'] = $khid;
        //先清空授权表信息
        $sql = "truncate table sys_auth";
        $db->query($sql);
        $ins = array();
        $ins[] = array('code'=>'version','name'=>'版本号','value'=>'v5.0.1');
        $ins[] = array('code'=>'company_name','name'=>'授权公司名称','value'=>$kh_name);
        $ins[] = array('code'=>'auth_key','name'=>'授权产品密钥','value'=>$db_auth_row['pra_authkey']);
        $ins[] = array('code'=>'auth_num','name'=>'授权注册用户','value'=>$db_auth_row['pra_authnum']);
        $ins[] = array('code'=>'cp_code','name'=>'产品类型代码','value'=>$user_cp_code);
        $ins[] = array('code'=>'login_server_url','name'=>'登录服务器的URL','value'=>'http://login.baotayun.com/efast/weblogin/web/index.php');
        $ins[] = array('code'=>'kh_id','name'=>'客户ID号','value'=>$khid);
        $ret=$db->insert('sys_auth',$ins);
        /*foreach($ins as $key=>$sub_ins){
            $sub_ins['value'] = $info[$key];
            $ret = $db->insert($sub_ins);
        }*/
        //更新运营平台数据库绑定信息,默认为已使用数据库(rem_db_bindtype = 1)
        $sql = "update osp_rdsextmanage_db set rem_db_khid = :rem_db_khid,rem_db_is_bindkh=1,rem_db_bindtype = 1 where rem_db_id = :rem_db_id";
        $this->db->query($sql,array(':rem_db_khid'=>$khid,':rem_db_id'=>$dbid));
        //更新rds数据库数量
        $sql_main="update osp_rdsextmanage set osp_rdsextmanage.rem_dbnum=(select count(1) from osp_rdsextmanage_db where rem_db_pid=:rem_rds_id) where rem_rds_id=:rem_rds_id "; 
        $sql_values[':rem_rds_id'] = $rdsid;
        $this->db->query($sql_main, $sql_values);
        
        $sql_main="update osp_rdsextmanage set osp_rdsextmanage.rem_dbunnum=(select count(1) from osp_rdsextmanage_db where rem_db_pid=:rem_rds_id and rem_db_is_bindkh='0') where rem_rds_id=:rem_rds_id "; 
        $sql_values[':rem_rds_id'] = $rdsid;
        $this->db->query($sql_main, $sql_values);
        //初始化 rem_db_version_ip
        $sql = "update osp_rdsextmanage_db set rem_db_version_ip =:rem_db_version_ip where rem_db_pid =:rem_db_pid and rem_db_name =:rem_db_name";
        $this->db->query($sql,array(':rem_db_version_ip'=>$ali_outip,':rem_db_pid'=>$rdsid,':rem_db_name'=>$db_name));
        //同步数据到sysdb
        $ret = load_model('basedata/RdsDataModel')->update_kh_data($khid,0,'osp_rdsextmanage_db');
        /*if ($ret['status']<0){
            return $ret;
        }*/
        return $this->format_ret(1,'',"客户数据库绑定成功");
    }
    function create_conf(){
        $sql  = "SELECT DISTINCT rem_db_pid,rem_db_version_ip 
            from osp_rdsextmanage_db where rem_db_bindtype=1  order by rem_db_version_ip";
        
        $data = $this->db->get_all($sql);
        $ip_rds_data = array();
        $rds_arr = array();
        foreach($data as $val){
            $rds_arr[]=$val['rem_db_pid'];
            $ip_rds_data[$val['rem_db_version_ip']][] = $val['rem_db_pid'];
        }
        
        $_task_conf = array(
          'process_max'=>20,
          'process_num'=>5,   
        );
        $conf_data = array();
        
        foreach($ip_rds_data as $ip=>$val){
            $_conf = array('ip'=>$ip,'db_id'=>$val);
            $conf_data[$ip] = array_merge($_conf,$_task_conf);
        }
        $rds_all = $this->get_rds_info($rds_arr);
        
        foreach($conf_data as $key=>$val){
           $path =  ROOT_PATH.CTX()->app_name."/temp/".$key;
            //"/task.conf.php";
            if (!file_exists($path)) {
                        mkdir($path, 0777, true);
           }
           $task_path =$path.'/task.conf.php';
           $db_path =$path.'/db.conf.php';
           $this->create_conf_file($task_path, $val);
           $this->create_conf_file($db_path, $rds_all);
           
        }
        return $this->format_ret(1);
        
    }
    function create_conf_file($file,$data){
    
        
        $lines = "<?php \n";
        $lines = $lines . 'return ' . var_export($data, true) . ";\n";

        file_put_contents($file, $lines, LOCK_EX);
    }

    function get_rds_info($rds_arr){
        
        $rds_str = "'".implode("','", $rds_arr)."'";
        $sql = "select * from osp.osp_aliyun_rds where rds_id in ({$rds_str}) ";
        $data = $this->db->get_all($sql);
        $ret_data = array();
        foreach($data as $val){
            $keylock = get_keylock_string($val['rds_createdate']);
            $rds_pass = create_aes_decrypt($val['rds_pass'], $keylock);
            $ret_data[$val['rds_id']] = array(
                'type' => 'mysql',
                'host' => $val['rds_link'],
                'name' => 'sysdb',
                'user' => $val['rds_user'],
                'pwd' => $rds_pass,
            );
        }
        return $ret_data;
    }

    /**数据库版本升级
     * @param $request
     * @return array
     */
    function version_update($request) {
        $data = array(
            'rem_db_pid' => $request['rds_id'],
            'rem_db_version_ip' => $request['time_ip'],
        );
        if (isset($request['api_ip'])) {
            $data['rem_db_api_ip'] = $request['api_ip'];
        }
        $where = array(
            'rem_db_khid' => $request['rem_db_khid'],
            'rem_db_is_bindkh' => 1,
            'rem_db_pid' => $request['rem_db_pid']
        );
        $this->begin_trans();
        try {
            $ret = $this->update($data, $where);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '更新部署表失败！');
            }
            $ret = load_model('basedata/RdsDataModel')->update_rds($request['rds_id']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '主配置同步RDS失败！');
            }
            $this->commit();
            return $this->format_ret('1', '', '升级成功！');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '升级失败:' . $e->getMessage());
        }

    }


    /**
     * 启用，停用
     * @param $params
     */
    function update_bind_action($params) {
        $ret = $this->update(array('rem_db_is_bindkh' => $params['rem_db_is_bindkh']), array('rem_db_id' => $params['rem_db_id']));
        $mes = ($params['rem_db_is_bindkh'] == 0) ? '停用' : '启用';
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', $mes . '失败！');
        }
        return $this->format_ret('1', '', $mes . '成功！');
    }

}
