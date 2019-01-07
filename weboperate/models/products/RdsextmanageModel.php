<?php

/**
 * 产品RDS扩展管理相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class RdsextmanageModel extends TbModel {
    
    function get_table() {
        return 'osp_rdsextmanage';
    }
    
    /*
     * 获取产品RDS扩展列表
     */
    function get_by_page($filter) {
            $sql_join = "INNER JOIN osp_aliyun_rds r ON rl.rem_rds_id=r.rds_id ";     //关联表
            $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";

            //产品
            if (isset($filter['rem_cp_id']) && $filter['rem_cp_id']!='' ) {
                    $sql_main .= " AND (rl.rem_cp_id =". $filter['rem_cp_id'].")";
            }

            if (isset($filter['rds_info']) && $filter['rds_info']!='' ) {
                    $sql_main .= " AND r.rds_link like '%". $filter['rds_info']."%' "; 
            }
    
            //排序条件（框架暂时不支持）
            if(isset($filter['__sort']) && $filter['__sort'] != '' ){
                    $filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
                    $sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
            }
            //构造排序条件
            $sql_main .= " ";

            $select = 'rl.*';

            $data =  $this->get_page_from_sql($filter, $sql_main, "",$select);

            $ret_status = OP_SUCCESS;
            $ret_data = $data;

            //处理关联代码表
            filter_fk_name($ret_data['data'], array('rem_rds_id|osp_rdsinfo','rem_cp_id|osp_chanpin','rem_bindcpkey|osp_rdskey'));
            return $this->format_ret($ret_status, $ret_data);
    }
    
    //导入RDS列表展示
    function  get_by_page_rds($filter) {
            $sql_join = " ";     //关联表d
            $sql_main = "FROM osp_aliyun_rds r $sql_join where (r.rds_id not in (select rem_rds_id from osp_rdsextmanage)) ";

            //平台类型搜索条件dbname
            if (isset($filter['dbtype']) && $filter['dbtype'] != '') {
                $sql_main .= " AND r.rds_dbtype ='{$filter['dbtype']}'";
            }
            //rds实例名搜索条件
            if (isset($filter['server_use']) && $filter['server_use'] != '') {
                $sql_main .= " AND r.rds_server_use = '{$filter['server_use']}'";
            }
           //到期时间
            if (isset($filter['rds_endtime']) && $filter['rds_endtime'] != '') {
                $sql_main .= " AND r.rds_endtime <='" . $filter['rds_endtime']."'";
            } 
        
            //构造排序条件
            $sql_main .= " ";

            $select = 'r.*';

            $data =  $this->get_page_from_sql($filter, $sql_main, "",$select);

            $ret_status = OP_SUCCESS;
            $ret_data = $data;

            //处理关联代码表
            filter_fk_name($ret_data['data'], array('rds_dbtype|osp_cloud_server')); 

            return $this->format_ret($ret_status, $ret_data);
    }
    
    //获取数据库列表明细
    function get_rdsdb_info($filter){
        $sql_main = "FROM osp_rdsextmanage_db  WHERE 1";
        
        if (isset($filter['rem_db_pid']) && $filter['rem_db_pid'] != '') {
            $sql_main .= " AND rem_db_pid='{$filter['rem_db_pid']}'";
        }

        //构造排序条件
        $sql_main .= " order by rem_db_createdate desc";
        $select = '*';
        
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联店铺平台类型
        filter_fk_name($ret_data['data'], array('rem_db_khid|osp_kh'));   
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /**
     * 根据ID删除数据库行数据
     * @param $id
     * @return array|void
     */
    function delete_db($id,$is_bindkh,$pid){
        $data = $this -> db -> create_mapper('osp_rdsextmanage_db') -> delete(array('rem_db_id'=>$id));
        if ($data) {
            //更新数量
//            if($is_bindkh=='1'){
//                $sql_main="update {$this->table} set rem_dbnum=rem_dbnum-1 WHERE rem_rds_id=:rem_id "; 
//                $sql_values[':rem_id'] = $pid;
//                $this->db->query($sql_main, $sql_values);
//            }else{
//                $sql_main="update {$this->table} set rem_dbnum=rem_dbnum-1,rem_dbunnum=rem_dbunnum-1 WHERE rem_rds_id=:rem_id "; 
//                $sql_values[':rem_id'] = $pid;
//                $this->db->query($sql_main, $sql_values);
//            }
            $this->updatedbnum($pid);
            return $this -> format_ret("1", $data, 'delete_success');
        } else {
            return $this -> format_ret("-1", '', 'delete_error');
        }
    }
    
    
    function get_chanpin() {
        $sql_main = "select cp_id,cp_name from osp_chanpin ";
        $data = $this->db->get_all($sql_main);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    function get_dbtype() {
        $sql_main = "select cd_id,cd_name from osp_cloud ";
        $data = $this->db->get_all($sql_main);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    //批量导入RDS
    function import_rdslist($request){
        $request['rdsdata'] = json_decode($request['rdsdata'], true);
        //获取产品应用KEY
        if(!isset($request['pt_key_id'])||empty($request['pt_key_id'])){
            $sql_main="SELECT * FROM osp_rds WHERE relation_product=:relation_product and relation_platform=:relation_platform"; 
            $sql_values[':relation_product'] = $request['cpid'];
            $sql_values[':relation_platform'] = $request['platformid'];
            $retkey=$this->db->get_row($sql_main, $sql_values);
            $cpptkeyid=$retkey['rds_id'];
        }else{
            $cpptkeyid = $request['pt_key_id'];
        }
        $iscpptkey='0';
        if(!empty($cpptkeyid)){
            $iscpptkey='1';
        }
        //
        
        $rdsextmanage=array();
        foreach ($request['rdsdata'] as $val) {
            $rdsextmanage[]=array(
                'rem_rds_id'=>$val['rds_id'],
                'rem_rds_endtime'=>$val['rds_endtime'],
                'rem_cp_id'=>$request['cpid'],
                'rem_is_bindcpkey'=>$iscpptkey,
                'rem_bindcpkey'=>$cpptkeyid,
                'rem_is_bindkh'=>0,
                'rem_dbnum'=>0,
                'rem_dbunnum'=>0,
                'rem_rdstype'=>$request['platformid'],
                'rem_updatedate'=>date('Y-m-d H:i:s'),
                'rem_bz'=>'首次导入生成',
            );
        }
        //$data = $this -> db -> create_mapper('osp_rdsextmanage') -> insert($rdsextmanage);
        //return $ret;
        return parent::insert($rdsextmanage);
    }
    
    //获取RDS相关信息
    function getrdsinfo($rdsid){
        $sql_main="SELECT * from osp_aliyun_rds where rds_id=:rds_id"; 
        $sql_values[':rds_id'] = $rdsid; 
        $ret=$this->db->get_row($sql_main, $sql_values);
        return $ret;
    }
    //获取产品应用参数
    function getrdskeyinfo($yid){
        $sql_main="select app_key,app_secret,access_token from osp_rds where rds_id=:rds_id"; 
        $sql_values[':rds_id'] = $yid; 
        $ret=$this->db->get_row($sql_main, $sql_values);
        return $ret;
    }
    
    //插入数据库明细
    function insert_db($rdsdb){
        $data = $this -> db -> create_mapper('osp_rdsextmanage_db') -> insert($rdsdb);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }
    
    //更新合计数据库数量
    function updatedbnum($rem_rdsid){
        $sql_main="update osp_rdsextmanage set osp_rdsextmanage.rem_dbnum=(select count(1) from osp_rdsextmanage_db where rem_db_pid=:rem_rds_id) where rem_rds_id=:rem_rds_id "; 
        $sql_values[':rem_rds_id'] = $rem_rdsid;
        $this->db->query($sql_main, $sql_values);
        
        $sql_main="update osp_rdsextmanage set osp_rdsextmanage.rem_dbunnum=(select count(1) from osp_rdsextmanage_db where rem_db_pid=:rem_rds_id and rem_db_is_bindkh='0') where rem_rds_id=:rem_rds_id "; 
        $sql_values[':rem_rds_id'] = $rem_rdsid;
        $this->db->query($sql_main, $sql_values);
    }
    
    
    //rds服务器连接测试，判断数据库是否存在
    function rds_db_exists($rdsconinfo) {
        if (!empty($rdsconinfo)) {
            $connection = mysql_connect($rdsconinfo['link'],$rdsconinfo['user'],$rdsconinfo['pwd']);
            if (!$connection) {
                return false;
            } else {
                //判断数据库sysdb是否存在  SELECT 1 as type FROM SCHEMATA where SCHEMA_NAME='sysdb'
                $state = mysql_select_db("sysdb", $connection);
                mysql_close($connection);
                return $state;
            }
        }else {
            return false;
        }
    }
    
    /**
     * 
     * @param type $params array('app_key','app_secret','instance_name','session','db_name') //前三个是固定的，session是对应key企业session
     * @param type $sql_path //要执行的sql脚本，比如建表
     */
    function install_rds($params) {
        
        require_lib('util/taobao_util', true);

        $taobao = new taobao_util($params['app_key'],$params['app_secret'], $params['session']);

        $taobao->topUrl = 'http://gw.api.taobao.com/router/rest?';
        $_params = array();
        $_params['db_name'] = $params['db_name'];
        $_params['instance_name'] = $params['instance_name'];
        $rds_create_status = $taobao->post('taobao.rds.db.create', $_params);

        if (1 != $rds_create_status['status']) {
            //创库失败
            //todo 失败逻辑
            $rds_create_status['dbname']=$params['db_name'];
            CTX()->log_error('创建数据库失败!'.print_r($rds_create_status,true));
            return false;
        }else{
            return true;
        }        
    }
    
    /**
     * taobao.rds.db.get查询RDS的数据库
     * @param type $params array('app_key','app_secret','instance_name','session','instance_name') //前三个是固定的，session是对应key企业session,instance_name  RDS实例
     */
    function get_rds_db($params) {
        require_lib('util/taobao_util', true);
        
        $taobao = new taobao_util($params['app_key'],$params['app_secret'], $params['session']);

        $taobao->topUrl = 'http://gw.api.taobao.com/router/rest?';
        $_params = array();
        $_params['instance_name'] = $params['instance_name'];
        $rds_get_status = $taobao->post('taobao.rds.db.get', $_params);
        
        if (1 != $rds_get_status['status']) {
            //获取失败
            CTX()->log_error('查询失败!'.print_r($rds_create_status,true));
            return false;
        }else{
            return true;
        }     
    }
    
    /**
     * 删除数据库
     * @param type $params array('app_key','app_secret','instance_name','session','db_name') //前三个是固定的，session是对应key企业session
     */
    function delete_rdsdb ($params) {
        require_lib('util/taobao_util', true);

        $taobao = new taobao_util($params['app_key'],$params['app_secret'], $params['session']);
    }
    
    /**
     * 初始化数据
     * @param type $params array('db_name', 'rds_host', 'rds_account','rds_password')
     * @param type $sql_path
     */
    function init_sql ($params, $sql_path) {
        
        CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');
        
        CTX()->db_kh->set_conf(array(
            'name' => $params['db_name'],
            'host' => $params['rds_host'],
            'user' => $params['rds_account'],
            'pwd' => $params['rds_password'],
            'type' => 'mysql',
        ));
        
        $db = CTX()->db_kh;
        
        if (!file_exists($sql_path)) {
            CTX()->log_error('install_db:创建数据库失败!数据库文件不存在！' );
            return false;
        }
        
        $sql_str = file_get_contents($sql_path);

        $db->query('set names utf8;');
        $db->query('DROP PROCEDURE IF EXISTS `install_func`;');
        $proc = 'CREATE DEFINER = CURRENT_USER PROCEDURE `install_func`()
            BEGIN ' . $sql_str . '
            END;';
        try {
            $db->query($proc);
            $db->query('call install_func();');
            $db->query('DROP PROCEDURE IF EXISTS `install_func`;');
        } catch (Exception $exc) {
            CTX()->log_error('执行数据库脚本失败!');
            return false;
            //清除掉废数据库
            //$db->query('DROP DATABASE IF EXISTS `' . $renter['renter_code'] . "`");
        }
        
        return true;
    }
    function get_cp_key($cp_id,$pt_id){
        $sql = "SELECT rds_id,memo from osp_rds  where relation_product=:relation_product AND  relation_platform=:relation_platform order by rds_id desc";
        $sql_value = array(':relation_product'=>$cp_id,':relation_platform'=>$pt_id);
        $data = $this->db->get_all($sql,$sql_value);
        return $data;
    }
}
