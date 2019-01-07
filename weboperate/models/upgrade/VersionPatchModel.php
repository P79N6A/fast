<?php

/**
 * 版本sql代码
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");
class VersionPatchModel extends TbModel {

    var $rds_db = '';

    function get_table() {
        return 'osp_version_patch';
    }

    function get_upgrade_patch($kh_id) {

        $sql = "select * from osp_version_patch where version_patch not in(
                select version_patch from  osp_version_upgrade_record
                where kh_id='{$kh_id}')";
        $data = $this->db->get_all($sql);

        return $this->format_ret(1, $data);
    }
    
    function check_sql_file($version_patch){
        $path = ROOT_PATH.CTX()->app_name."/data/efast5/".$version_patch.".php";
        if(file_exists($path)){
            return $path;
        }
        return FALSE;
    }
    
    
    function set_upgrade_ids($ids, $vesion_no, $version_patch) {
        $kh_arr = array();
        $fail_error = array();
        if ($this->check_sql_file($version_patch) === false) {
            return $this->format_ret(-1, '', '没找到补丁对应的数据库脚本文件');
        }
        $sql_up_kh = "select kh_id from osp_version_upgrade_kh where is_upgrade=0 ";
        $no_up_kh_data = $this->db->get_all($sql_up_kh);
        $no_up_kh_arr = array_column($no_up_kh_data, 'kh_id');
        foreach ($ids as $kh_id) {
            if (in_array($kh_id, $no_up_kh_arr)) {
                continue;
            }
            $ret = $this->upgrade_patch($kh_id, $vesion_no, $version_patch);

            if ($ret['status'] < 0) {
                $fail_error[$kh_id] = $ret['message'];
            }
        }
        if (!empty($fail_error)) {
            return $this->format_ret(2, $fail_error);
        }
        return $this->format_ret(1, count($ids));
    }

    function upgrade_patch($kh_id, $vesion_no, $version_patch) {

        $sql = "select * from osp_version_upgrade_record where kh_id='{$kh_id}'
        and version_no='{$vesion_no}' and version_patch='{$version_patch}'";
        $version_data = $this->db->get_row($sql);
        $start_time = time();
        $task_sn = '';
        if (!empty($version_data)) {
            //0未升级，1开始升级，2升级成功，3失败
            $err_msg = array('1' => '升级正在进行，不能重复升级', '2' => '已经升级成，不能重复升级');
            if ($version_data['status'] != 0 && $version_data['status'] != 3) {
                return $this->format_ret(-1, '', $err_msg[$version_data['status']]);
            }
            $record_id = $version_data['id'];
            $task_sn = $this->set_sn($version_data);
            $up_data = array(
                'start_time' => 0,
                'status' => 0,
                'task_sn' => $task_sn,
            );
            $this->update_exp('osp_version_upgrade_record', $up_data, array('id' => $record_id));
        } else {
            $record_data = array();
            $record_data['kh_id'] = $kh_id;
            $record_data['version_no'] = $vesion_no;
            $record_data['version_patch'] = $version_patch;
            $record_data['start_time'] = 0;
            $record_data['status'] = 0;
            $task_sn = $this->set_sn($record_data);
            $record_data['task_sn'] = $task_sn;

            $ret_data = $this->insert_exp('osp_version_upgrade_record', $record_data);
            $record_id = $ret_data['data'];
        }
        $exec_data= array('app_act'=>'upgrade/upgrade/exec_upgrade_patch','app_fmt'=>'json');
    
        $exec_data['kh_id'] = $kh_id;
        $exec_data['record_id'] = $record_id;
        $exec_data['version_patch'] = $version_patch;
        $exec_data['vesion_no'] = $vesion_no;
        $exec_data['t_sn'] = $task_sn;
        $this->run_command($this->set_cmd($exec_data));
        return $this->format_ret(1, $ret_data);
    }
    function set_cmd($data){
        $path = "weboperate/web/index.php ";
        foreach ($data as $key => $val) {
            $path .=" '" . $key . "=" . $val . "'";
        }
        return $path;
        
    }
    function exec_upgrade_patch($request) {
        $kh_id = $request['kh_id'];
        $version_patch = $request['version_patch'];
        $vesion_no = $request['vesion_no'];
        $record_id = $request['record_id'];

         $up_data = array(
                'start_time' => time(),
                'status' => 1,
            );
            $this->update_exp('osp_version_upgrade_record', $data, array('id' => $record_id));
           
        $fail_arr = array();

        $fail_i = 0;
        $success = 0;
      //  $data = $this->db->get_all("select * from osp_version_patch_sql where version_no='{$vesion_no}'  and version_patch='{$version_patch}' ");
        $sql_file = $this->check_sql_file($version_patch);
         if($sql_file===false){
             return $this->format_ret(-1,'','没找到补丁对应的数据库脚本文件');
        }
        require_once $sql_file;
        
        $exec_data = array();
        $rds_db = $this->get_kh_db($kh_id);
        
        if(!empty($u)){
            foreach ($u as $task_sn=>$task_sql) {      
                $err_data_all = array();
                foreach($task_sql as $sql){
                $code = md5($sql.$task_sn.$version_patch);
                if( $this->check_sql($kh_id,$code,$version_patch)===true){
                    continue;
                }
                $status = $this->exec_sql($rds_db,$sql);
                $new_val = array();
                $err_data = array();
                $err_data['version_patch'] = $version_patch;
                $err_data['content'] = $sql;
                $err_data['task_sn'] = $task_sn;
                $err_data['kh_id'] = $kh_id;
                $err_data['code'] = md5($sql.$task_sn);
                if ($status === true) {
                    $success++;
                     $err_data['msg'] = '';
                     $err_data['status'] = 1;
               } else {
                    $fail_i++;
                     $err_data['status'] = 0;
                     $err_data['msg'] = $status;
                }
                    $new_val['msg'] = "任务：" . $task_sn . "<br />" . "执行sql：<br />" . $sql . "<br />" . "执行结果：" . $status_str . "<br />";
                    $exec_data[] = $new_val;  
                    $err_data_all[] = $err_data;
                }   
                  $update_str = "status = VALUES(status),msg = VALUES(msg)";
                 //status
                 $this->insert_multi_duplicate('osp_version_upgrade_log', $err_data_all,$update_str) ;
            }
        }else{
            $status=2;
        }
        $status = ($fail_i > 0) ? 3 : 2;
        $up_data = array(
            'end_time' => time(),
            'status' => $status
        );
   
        if($status==2){
            $this->set_upgrade_success($kh_id,$version_patch);
        }
        $this->update_exp('osp_version_upgrade_record', $up_data, array('id' => $record_id));
        $ret_data = array('fail' => $fail_i, 'success' => $success, 'data' => $exec_data);
        return $this->format_ret(1, $ret_data);
    }
    function exprot_upgrade_fail_log($filter){
        
        
        
        $sql = "select * from osp_version_upgrade_log where status=0";
        if(isset($filter['kh_id'])){
            $sql.=" AND kh_id='{$filter['kh_id']}' ";
        }
         if(isset($filter['version_patch'])){
            $sql.=" AND version_patch='{$filter['version_patch']}' ";
        }    
        $sql .=" order by kh_id ";
        $data = $this->db->get_all($sql);
        $head_arr[] = array('客户id','执行任务编号','执行SQL','错误日志');
        $content[] = implode(',', $head_arr);
        $kh_id = 0;
        foreach($data as $val){
            if($kh_id!=$val['kh_id']){
                $content[] = $this->get_kh_info_txt($val);
                $kh_id=$val['kh_id'];
            }
             $content[] =$this->get_kh_sql_txt($info);
        }
        
        
        
        
        $file_name = "更新失败的数据脚本";
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . iconv('utf-8','gbk',$file_name) . '.sql"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");   
        echo join("
            ",$content);
        die;
    }
    private function get_kh_info_txt($info){
        $str = "
            /*=====================客户ID:{$info['kh_id']}=============================*/
            ";
    }
    private function get_kh_sql_txt($info){
         $str = "/*执行错误SQL(任务号{$info['task_sn']})*/
             ";   
         $str .=  $info['content']."
             ";
        $str .= "/*执行错误结果*/";
        $str .= "/*
            ";
           $str .= $info['msg'];
        $str .= "
            */";
         
         
    }
    
    private function check_sql($kh_id,$code,$version_patch){
        $sql = " select status from  osp_version_upgrade_log where version_patch = '{$version_patch}'  AND  code='{$code}' AND  kh_id = '{$kh_id}' ";
        $status = $this->db->getOne($sql);
        if(!empty($data)&&$status==1){
            return true;
        }
        return false;
    }


    function get_exec_status() {
        $data = $this->db->get_all("select id,task_sn from osp_version_upgrade_record where status=1");
        $is_run = count($data);
        $end_time = time();
        foreach ($data as $val) {
            $status = $this->check_cmd($val['task_sn']);
            if ($status === FALSE) {
                $up_data = array(
                    'end_time' => time(),
                    'status' => 3
                );
                $this->update_exp('osp_version_upgrade_record', $data, array('id' => $val['id'], 'statsu' => 1));
                $is_run--;
            }
        }
        return $this->format_ret(1, $is_run);
    }

    function get_kh_db($kh_id) {
        if ($this->rds_db == '') {
            $rds_data = $this->db->get_row(" select * from osp_aliyun_rds where rds_id in(select rem_db_pid from osp_rdsextmanage_db where rem_db_khid='{$kh_id}')");
            $keylock = get_keylock_string($rds_data['rds_createdate']);
        
            $rds_data['rds_pass'] = create_aes_decrypt($rds_data['rds_pass'], $keylock);
            $db_name = $this->get_kh_db_name($kh_id);
            $config = array(
                'name' => $db_name,
                'user' => $rds_data['rds_user'],
                'pwd' => $rds_data['rds_pass'],
                'host' => $rds_data['rds_link'],
                'type' => 'mysql',
            );
          
            $this->rds_db = create_db($config);

        }
        return $this->rds_db;
    }

    function set_upgrade_success($kh_id,$version_patch) {
        //rem_db_version_patch
        $this->db->update('osp_rdsextmanage_db', array('rem_db_version_patch'=>$version_patch),array('rem_db_khid'=>$kh_id));//,'rem_db_version'=>$db_version
        $db = $this->get_kh_db($kh_id);
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
        return $this->format_ret(1);
        
    }

    function exec_sql($db, $sql) {
        $status = false;
        try {
            $status = $db->query($sql);
        } catch (Exception $e) {
            $status = $e->getMessage();
        }
        return $status;
    }

    function get_upgrade_list($filter) {


        $sql_main = "FROM osp_rdsextmanage_db d 
            LEFT JOIN osp_kehu  kh  ON kh.kh_id=d.rem_db_khid  
            INNER JOIN osp_chanpin_version cp ON cp.pv_id=d.rem_db_version  
             INNER JOIN osp_version_patch p ON p.cp_id=cp.pv_cp_id  
            LEFT JOIN  osp_version_upgrade_record r ON
            r.kh_id = kh.kh_id and r.version_no = p.version_no and  r.version_patch = p.version_patch
            WHERE 1 ";


        //版本
        if (isset($filter['version_no']) && $filter['version_no'] != '') {
            $sql_main .= " AND p.version_no = '" . $filter['version_no'] . "'";
        }
        //补丁
        if (isset($filter['version_patch']) && $filter['version_patch'] != '') {
            $sql_main .= " AND p.version_patch = '" . $filter['version_patch'] . "'";
        }
            //补丁
        if (isset($filter['is_bindkh']) && $filter['is_bindkh'] != '') {
            if($filter['is_bindkh']==1){
                $sql_main .= " AND d.rem_db_khid >0";
            }  else {
                $sql_main .= " AND d.rem_db_khid=0";
            }
        }
        
        
        

        if (isset($filter['kehu']) && $filter['kehu'] != '') {
            $sql_main .= " AND ( kh.kh_code like '%" . $filter['kehu'] . "%'";
            $sql_main .= " OR kh.kh_name like '%" . $filter['kehu'] . "%')";
        
        }

        if (isset($filter['is_upgrade']) && $filter['is_upgrade'] != '') {
            if($filter['is_upgrade']==0){
                  $sql_main .= " AND r.status is null or r.status=0 ";
            }else{
                  $sql_main .= " AND r.status = '{$filter['is_upgrade']}' ";
            }
        }
        $select = " d.rem_db_name,p.id,r.id as r_id,kh.kh_name,kh.kh_id,p.version_no,d.rem_db_version_patch,p.version_patch,r.start_time,r.end_time,r.status ";

        $sql_up_kh = "select kh_id from osp_version_upgrade_kh where is_upgrade=0 ";
        $no_up_kh_data = $this->db->get_all($sql_up_kh);
        if(!empty($no_up_kh_data)){
             $kh_data = array_column($no_up_kh_data, 'kh_id');
             $kh_data_str = implode(",", $kh_data);
             $sql_main.=" AND d.rem_db_khid not in ({$kh_data_str}) ";
        }
       
        
        $data = $this->get_page_from_sql($filter, $sql_main,array(), $select);
        $status = array('0'=>'未升级','1'=>'正在升级','2'=>'升级完成','3'=>'升级失败');
        foreach($data['data'] as &$val){
            $val['start_time'] = ($val['start_time']>0)? date('Y-m-d H:i:s',$val['start_time']):'';
            $val['end_time'] = ($val['end_time']>0)?date('Y-m-d H:i:s',$val['end_time']):'';
            if(empty($val['status'])){
                $val['status_name'] =$status[0];
                $val['status'] = 0;
            }else{
                $val['status_name'] =$status[$val['status']];
            }
            if(empty($val['r_id'])){
                $val['r_id'] = $val['id']."_".$val['kh_id'];
            }
            
        }
      
        
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_upgrade_patch_list($version_no) {
        $sql = "select *  from osp_version_patch where version_no='{$version_no}' and is_exec<2 ";
        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
    }
    





    function get_kh_db_name($kh_id) {
        $sql = "select rem_db_name from osp_rdsextmanage_db WHERE rem_db_khid='{$kh_id}'  ";
        $data = $this->db->get_row($sql);
        return $data['rem_db_name'];
    }

    function get_version(){
        $sql = "select DISTINCT version_no from osp_version_patch ";

        $data = $this->db->get_all($sql);
 
        return $this->format_ret(1, $data);
    }
      function get_version_patch($version_no){
        $sql = "select version_patch from osp_version_patch where version_no='{$version_no}' and is_exec<2 ";
        $data = $this->db->get_all($sql);
        return $this->format_ret(1, $data);
    }  
    
    function set_sn($data) {
        return md5(json_encode(sort($data)) . time());
    }

    function check_cmd($task_sn) {
        exec("ps aux | grep {$task_sn} | grep -v \"grep\" | awk '{print $2}'", $pids);
        if (!empty($pids)) {
            return $pids;
        }
        return false;
    }
    
    var $php_path = '';
    function real_path() {
        if ($this->php_path != '') {
            return $this->php_path;
        }
        if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
            $ini = ini_get_all();
            //print_r($ini);die();
            $path = $ini['extension_dir']['local_value'];
            $php_path = str_replace('\\', '/', $path);
            $php_path = str_replace(array('/ext/', '/ext'), array('/', '/'), $php_path);
            $real_path = $php_path . 'php.exe';
        } else {
            $real_path = PHP_BINDIR . '/php';
        }

        if (strpos($real_path, 'ephp.exe') !== FALSE) {
            $real_path = str_replace('ephp.exe', 'php.exe', $real_path);
        }
        $this->php_path = $real_path;
        return $this->php_path;
    }
    
    function run_command($path) {

        $real_path = $this->real_path();
        $path = ROOT_PATH . $path;
        $exec_command = $real_path . ' -f ' . $path;
        $file = popen($exec_command.'  1>/dev/null 2>/dev/null &','r');
        $log_path = ROOT_PATH.'logs/up'.date('Ymd').".log";
        file_put_contents($exec_command, $log_path, FILE_APPEND);
        
        pclose($file);
        return TRUE;
    }   
}