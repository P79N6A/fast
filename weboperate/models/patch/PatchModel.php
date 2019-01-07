<?php

/**
 * 产品补丁升级Model
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class PatchModel extends TbModel {

    function get_table() {
        return 'osp_rdsextmanage_db';
    }

    /*
     * 获取数据库列表
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE (rem_db_sys='0' or rem_db_sys='' or isnull(rem_db_sys))";
        if (isset($filter['kehu']) && $filter['kehu'] != '') {
            $sql_main .= " AND rem_db_khid in (SELECT kh_id from osp_kehu WHERE kh_name like '%".$filter['kehu']."%')";
        }

        if (isset($filter['is_bindkh']) && $filter['is_bindkh'] != '') {
            $sql_main .= " AND rem_db_is_bindkh = " . $filter['is_bindkh'];
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('rem_db_khid|osp_kh', 'rem_db_version|osp_pdt_id'));
        return $this->format_ret($ret_status, $ret_data);
    }
    
    //获取产品版本
    function get_product_version($cpid){
        $sql_main = "select * from osp_chanpin_version where pv_cp_id=:pv_cp_id";
        $sql_values[':pv_cp_id'] = $cpid;
        
        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    //获取产品版本补丁
    function get_version_patch($cpid,$ver_no){
        $sql_main = "select * from osp_version_patch where cp_id=:cp_id and version_no=:version_no";
        $sql_values[':cp_id'] = $cpid;
        $sql_values[':version_no'] = $ver_no;
        
        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    function check_sql_file($version_patch){
        //获取补丁名称
        $patch_name=$this->db->get_value("select version_file_path from osp_version_patch where id=:id", array(":id" => $version_patch));
        $path = ROOT_PATH.CTX()->app_name."/web/".$patch_name;
        if(file_exists($path)){
            return $path;
        }
        return false;
    }
    
    //批量升级数据库补丁
    function set_upgrade_dbs($dbs,$version_no,$version_patch){
        $ret_path=$this->check_sql_file($version_patch);
        if($ret_path==false){
            return $this->format_ret(-1,'','没找到补丁对应的数据库脚本文件');
        }
        foreach($dbs as $db_id){
            //获取数据库信息
            $dbinfo=$this->db->get_row("select * from osp_rdsextmanage_db where rem_db_id=:rem_db_id", array(":rem_db_id" => $db_id));
            //获取RDS连接信息
            $rdsinfo=$this->db->get_row("select * from osp_aliyun_rds where rds_id=:rds_id", array(":rds_id" => $dbinfo['rem_db_pid']));
            $keylock = get_keylock_string($rdsinfo['rds_createdate']);
            $rdsinfo['rds_pass'] = create_aes_decrypt($rdsinfo['rds_pass'], $keylock);
            $params = array(
                'db_name' => $dbinfo['rem_db_name'],
                'rds_account' => $rdsinfo['rds_user'],
                'rds_password' => $rdsinfo['rds_pass'],
                'rds_host' => $rdsinfo['rds_link'],
            );
            //获取产品补丁编号
            $patch_bh=$this->db->get_value("select version_patch from osp_version_patch where id=:id", array(":id" => $version_patch));
            //执行补丁
            $init_state=$this->init_sql($params,$ret_path,$patch_bh);
            //记录日志
            if($init_state===true){
                //执行成功,更新运营平台数据库版本信息,记录日志
                parent::update(array('rem_db_version'=>$version_no,'rem_db_version_patch'=>$patch_bh), array('rem_db_id' => $db_id));
                //记录日志
                $loginfo=array('dbid'=>$db_id,'patchid'=>$version_patch,'content'=>'执行成功','state'=>'1','create_time'=>date('Y-m-d H:i:s'));
                $this->db->insert('osp_version_patch_log',$loginfo);
            }else{
                //执行失败,记录日志
                $loginfo=array('dbid'=>$db_id,'patchid'=>$version_patch,'content'=>'执行失败'.$init_state,'state'=>'0','create_time'=>date('Y-m-d H:i:s'));
                $this->db->insert('osp_version_patch_log',$loginfo);
            }
        }
        return $this->format_ret(1,'','补丁执行完成');
    }
    
    
    /**
     * 执行数据库脚本
     * @param type $params array('db_name', 'rds_host', 'rds_account','rds_password')
     * @param type $sql_path
     */
    function init_sql ($params, $sql_path,$version_patch) {
        
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
            CTX()->log_error('install_db:没找到补丁对应的数据库脚本文件！' );
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
            //更新一下业务库sys_auth版本信息
            $row = $db->get_row("select * from sys_auth where code='version_patch'");
            if(empty($row)){
                $data = array(
                    'code'=>'version_patch',
                    'name'=>'补丁',
                    'value'=>$version_patch,
                );
                $db->insert('sys_auth',$data);
            }else{
                $db->update('sys_auth',array('value'=>$version_patch),array('code'=>'version_patch'));
            }
        } catch (Exception $exc) {
            CTX()->log_error('执行数据库脚本失败!');
            return $ext->getMessage();
        }
        
        return true;
    }
}
