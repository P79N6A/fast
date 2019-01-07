<?php

/**
 * 产品RDS扩展管理相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class VmanageModel extends TbModel {
    
    function get_table() {
        return 'osp_vmextmanage';
    }
    
    /*
     * 获取产品RDS扩展列表
     */
    function get_vm_main($filter) {
            $sql_join = "";     //关联表
            $sql_main = "FROM {$this->table} vm $sql_join WHERE 1";

            //产品
            if (isset($filter['rem_cp_id']) && $filter['rem_cp_id']!='' ) {
                    $sql_main .= " AND (rl.rem_cp_id =". $filter['rem_cp_id'].")";
            }
            //排序条件（框架暂时不支持）
            if(isset($filter['__sort']) && $filter['__sort'] != '' ){
                    $filter['__sort_order'] = $filter['__sort_order'] =='' ? 'asc':$filter['__sort_order'];
                    $sql_main .= ' order by '.trim($filter['__sort']).' '.$filter['__sort_order'];
            }
            //构造排序条件
            $sql_main .= " ";

            $select = 'vm.*';
            $data =  $this->get_page_from_sql($filter, $sql_main, "",$select);
            $ret_status = OP_SUCCESS;
            $ret_data = $data;
            //处理关联代码表
            filter_fk_name($ret_data['data'], array('vm_id|osp_hostinfo','vm_cp_id|osp_chanpin'));
            return $this->format_ret($ret_status, $ret_data);
    }
    
    //导入RDS列表展示
    function  get_import_hostinfo($filter) {
            $sql_join = " ";     //关联表d
            $sql_main = "FROM osp_aliyun_host r $sql_join where (r.host_id not in (select vm_id from osp_vmextmanage) and r.kh_id =0) ";

            //平台类型搜索条件
            if (isset($filter['cloudtype']) && $filter['cloudtype'] != '') {
                $sql_main .= " AND r.ali_type ='{$filter['cloudtype']}'";
            }
            //服务器用途
            if (isset($filter['server_use']) && $filter['server_use'] != '') {
                $sql_main .= " AND r.ali_server_use = '{$filter['server_use']}'";
            }
           //到期时间
            if (isset($filter['host_endtime']) && $filter['host_endtime'] != '') {
                $sql_main .= " AND r.ali_endtime <='" . $filter['host_endtime']."'";
            } 
        
            //构造排序条件
            $sql_main .= " ";
            $select = 'r.*';
            $data =  $this->get_page_from_sql($filter, $sql_main, "",$select);
            $ret_status = OP_SUCCESS;
            $ret_data = $data;

            //处理关联代码表
            filter_fk_name($ret_data['data'], array('ali_type|osp_cloud_server')); 

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
            if($is_bindkh=='1'){
                $sql_main="update {$this->table} set rem_dbnum=rem_dbnum-1 WHERE rem_rds_id=:rem_id "; 
                $sql_values[':rem_id'] = $pid;
                $this->db->query($sql_main, $sql_values);
            }else{
                $sql_main="update {$this->table} set rem_dbnum=rem_dbnum-1,rem_dbunnum=rem_dbunnum-1 WHERE rem_rds_id=:rem_id "; 
                $sql_values[':rem_id'] = $pid;
                $this->db->query($sql_main, $sql_values);
            }
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
    
    //批量导入VM主机
    function import_vmhost($request){
        $request['hostdata'] = json_decode($request['hostdata'], true);
        $vmmanage=array();
        foreach ($request['hostdata'] as $val) {
            $vmmanage[]=array(
                'vm_id'=>$val['host_id'],
                'vm_endtime'=>$val['ali_endtime'],
                'vm_cp_id'=>$request['cpid'],
                'vm_is_bindkh'=>0,
                'vm_rdstype'=>$request['platformid'],
                'vm_updatedate'=>date('Y-m-d H:i:s'),
                'vm_note'=>'首次导入生成',
            );
        }
        return parent::insert($vmmanage);
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
    
}
