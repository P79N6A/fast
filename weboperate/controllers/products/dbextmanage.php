<?php
/**
 * 产品中心-产品数据库扩展管理
 */
//require_lib ( 'util/web_util', true );
class dbextmanage {
//    public function __construct() {
//        //parent::__construct();
//        $this->mdl = load_model('sys/RdsModel_ex');
//    }
    
    /**
     * 系统参数列表
     */
    public function do_list(array & $request, array & $response,  array & $app){
    }
    
    function show_add_dbextmanage(array & $request, array & $response, array & $app){
        $app['scene'] = "add";
        $response['data'] = $ret['data'];
     }
     
     function do_add_dbextmanage(array & $request, array & $response, array & $app) {
        $rem_cp_id=$request['cpid'];  //获取产品ID
        $rem_db_version=$request['rem_db_version'];  //版本ID
        $rem_all=load_model('products/RdsextmanageModel')->get_row(array('rem_rds_id'=>$request['rem_db_pid']));
        //获取RDS和对应产品应用参数
        $rdsinfo= load_model('products/RdsextmanageModel')->getrdsinfo($rem_all['data']['rem_rds_id']);
        $rdskeyinfo=load_model('products/RdsextmanageModel')->getrdskeyinfo($rem_all['data']['rem_bindcpkey']);
        //构造创库参数
        $params=array(
            'app_key'=>$rdskeyinfo['app_key'],
            'app_secret'=>$rdskeyinfo['app_secret'],
            'instance_name'=>$rdsinfo['rds_dbname'],
            'session'=>$rdskeyinfo['access_token'],
        );
        //$dbname = 'efast5_' . uniqid();
        $dbname=$request['rem_db_name'];
        $params['db_name']=$dbname;
        //生成数据库
        $createstate=load_model('products/RdsextmanageModel')->install_rds($params);
        if($createstate){
            $dbinfo = get_array_vars($request, 
                array('rem_db_pid', 'rem_db_name', 'rem_db_version','rem_db_sys','rem_db_sys_version'));
            $dbinfo['rem_db_createdate'] = date('Y-m-d H:i:s');
            $ret = load_model('products/dbextmanageModel')->insert($dbinfo,$request['rem_db_pid']);
            $sysdb = load_model('products/dbextmanageModel')->get_sysdb();
            if($sysdb['data']['rem_db_name'] =='sysdb' ){
                load_model('basedata/RdsDataModel')->update_kh_data(0,$request['rem_db_pid'],'osp_rdsextmanage_db');
            }
        }else{
            $ret=load_model('products/dbextmanageModel')->format_ret("-1", '', '添加失败');
        }
        exit_json_response($ret);
    }
    
    //删除未绑定客户
    function do_del_nobind(array & $request, array & $response, array & $app) {
        $ret = load_model('products/dbextmanageModel')->delete_db($request['rem_db_id'],$request['rem_db_is_bindkh'],$request['rem_db_pid']);
        exit_json_response($ret);
    }
    
   //删除使用客户
    function do_delete_try_client(array & $request, array & $response, array & $app) {
        //删除数据库记录，更新数量
        $ret = load_model('products/dbextmanageModel')->delete_db($request['rem_db_id'],$request['rem_db_is_bindkh'],$request['rem_db_pid']);
        exit_json_response($ret);
    }

    //绑定客户
    function bind_dbextmanage(array & $request, array & $response, array & $app){
        $app['scene'] = "add";
        $rem_db_id=$request['dbid'];
        $ret = load_model('products/dbextmanageModel')->get_by_bindinfo($rem_db_id);
        $response['data'] = $ret['data'];
    }
    
    //绑定客户保存
    function do_bind_dbextmanage(array & $request, array & $response, array & $app){
        $ret = load_model('products/dbextmanageModel')->do_bind_dbextmanage($request['rem_db_id'],$request['rem_rds_id'],$request['rem_db_khid']);
        exit_json_response($ret);
    }

    /**
     * 启用 1，停用0
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function update_bind_action(array & $request, array & $response, array & $app) {
        $params = get_array_vars($request, array('rem_db_id', 'rem_db_is_bindkh'));
        $ret = load_model('products/dbextmanageModel')->update_bind_action($params);
        exit_json_response($ret);
    }


}