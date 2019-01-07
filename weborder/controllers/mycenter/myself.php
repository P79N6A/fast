<?php

require_lib ( 'util/web_util', true );
require_lib("keylock_util");

class Myself {
    
    function self_info(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        //获取客户详细信息
        $kh_id=CTX()->get_session("kh_id");
        $ret = load_model('mycenter/MyselfModel')->getkhinfo($kh_id);
        $len = strlen($ret['data']['kh_licence_num']);
        $ret['data']['kh_licences_num'] = substr_replace($ret['data']['kh_licence_num'], '********', 3, $len-7); 
        $response['data'] = $ret['data'];
    }
    
    
    function order_info(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        //获取客户产品的订购信息
        $kh_id=CTX()->get_session("kh_id");
        //$ret = load_model('mycenter/MyselfModel')->getproduct_orderauth($kh_id);
        $ret = load_model('mycenter/MyselfModel')->getclientorder($kh_id);
        $response['data'] =$ret;
    }
    
    function orderdetail(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        $djbh=$request["djbh"]; 
        $ret = load_model('mycenter/MyselfModel')->getorderdetail($djbh);
        $response['data'] =$ret;
    }
    
    function orderauth_info(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        //获取客户产品的授权信息
        $kh_id=CTX()->get_session("kh_id");
        //$ret = load_model('mycenter/MyselfModel')->getproduct_orderauth($kh_id);
        $ret = load_model('mycenter/MyselfModel')->getclientorder_auth($kh_id);
        $data = load_model('mycenter/MyselfModel')->get_path_by_khid($kh_id);
        $response['pra_serverpath'] = $data['pra_serverpath'];
        $response['pra_kh_status'] = $data['pra_kh_status'];
        $response['is_notice'] = $data['is_notice'];
        $response['data'] =$ret;
    }
    
    function orderauthdetail(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        $id=$request["id"]; 
        $ret = load_model('mycenter/MyselfModel')->getorderauthdetail($id);
        $response['data'] =$ret;
    }
        
    
    function pay_desc(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
    }
    
    //客户编辑个人信息
    function edit_client(array & $request, array & $response, array & $app) {
            if(CTX()->get_session("LoginState")!=true){
                CTX()->redirect('index/do_index');
            }
            $client = get_array_vars($request, 
                    array( 
                            'kh_address',
                            'kh_tel',
                            'kh_email',
                            'kh_itphone',
                            'kh_itname',
                            'kh_licence_num',
                    ));
            $client['kh_updateuser'] = "2";
            $client['kh_updatedate'] = date('Y-m-d H:i:s');
            $ret = load_model('mycenter/MyselfModel')->update_client($client,$request['kh_id']);
            exit_json_response($ret);
    }
    
    //个人中心修改密码
    function do_chgpasswd(array & $request, array & $response, array & $app){
            if(CTX()->get_session("LoginState")!=true){
                CTX()->redirect('index/do_index');
            }
            $oldpwd=$request["old_user_pwd"]; //获取原密码
            $ret = load_model('mycenter/MyselfModel')->getkhinfo($request["kh_id"]);
            $keylock=get_keylock_string($ret['data']['kh_createdate']);
            $oldpwd_d = create_aes_decrypt($ret['data']['kh_login_pwd'],$keylock);   //解密原始密码
            if($ret!=""){
                if($oldpwd!=$oldpwd_d){//原密码验证不通过
                    exit_json_response(load_model('mycenter/MyselfModel')->format_ret("-1", '', '原密码错误'));
                }
                else{
                    //更新密码
                    $newpwd=$request["new_user_pwd"];  //获取新密码
                    $newpwd=  create_aes_encrypt($newpwd,$keylock); //加密新密码
                    $result=load_model('mycenter/MyselfModel')->updatepwd($request["kh_id"],$newpwd);
                    exit_json_response($result);
                }
            }else{
                //原密码验证不通过
                exit_json_response(load_model('mycenter/MyselfModel')->format_ret("-1", '', '原密码错误'));
            }
    }
    
    //发票信息
    function receipt_info(array & $request, array & $response, array & $app) {
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        $kh_id=CTX()->get_session("kh_id");
        $response = load_model('mycenter/OspReceiptModel')->get_by_kh_id($kh_id);
    }
    //申请发票    
    function apply_receipt(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        //获取客户id
        $kh_id = CTX()->get_session("kh_id");
        
        if($request['scene']=='view' || $request['scene']=='edit'){
            $response = load_model('mycenter/OspReceiptApplyModel')->get_info_by_receipt_id($request['_id']);
            $response['app_scene'] = $request['scene'];
            $response['receipt_id'] = $request['_id'];
        }else{
            $response = load_model('mycenter/MyselfModel')->getkhinfo($kh_id);
        }
    }

    function do_apply_receipt(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        if($app['scene'] == 'edit'){
            $res = load_model('mycenter/OspReceiptApplyModel')->update($request);
            $ret = ($res) ? array('status'=>1, 'data'=>'', 'message'=>'修改成功') : array('status'=>-1, 'data'=>'', 'message'=>'修改失败');
        }else{
            $ret = load_model('mycenter/OspReceiptApplyModel')->add($request);
            $ret['message'] = '申请成功';
        }
        $response = $ret;
    }

    //获取地址
    function get_area(array &$request, array &$response, array &$app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        $parent_id = isset($request['parent_id']) ? $request['parent_id'] : 1;
        $ret = load_model('mycenter/BaseAreaModel')->get_area($parent_id);
        exit_json_response($ret);
    }

    //获取订单价格
    function get_order_info(array &$request, array &$response, array &$app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        $ret = load_model('mycenter/MyselfModel')->get_order_info_by_pro_num($request);
        if (!empty($ret)) {
            exit_json_response(array('status' => 1, 'data' => $ret, 'message' => 'success'));
        }
    }
    /**
     * @todo 上传图片
     */
    function upload_images(array & $request, array & $response, array & $app) {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        $ret = $this->check_ext_image();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        $ret = load_model('mycenter/OspReceiptApplyModel')->upload_images($request, $_FILES);
        $response = $ret;
    }
    
    /**
     * @todo 检测上传图片的后缀是否符合要求
     */
    function check_ext_image() {
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        $ext_arr = array('jpg', 'png', 'gif');
        if (!isset($_FILES['fileData']['name']) || empty($_FILES)) {
            return array('status' => 1);
        }

        $file_name = $_FILES['fileData']['name'];

        $arr = explode('.', $file_name);
        $count = count($arr);
        $is_check = true;
        if ($count < 2) {
            $is_check = false;
        } else {
            $ext = $arr[$count - 1];

            if (!in_array($ext, $ext_arr)) {
                $is_check = false;
            }
        }

        if ($is_check === false) {
            $ret = array('status' => -1, 'data' => '', 'message' => '文件类型只能为.jpg,.jpeg,.png,.gif');
            return $ret;
        }
        $ret = array('status' => 1);
        return $ret;
    }
    /**
     * @todo 删除发票数据
     */
    function delete_receipt(array & $request, array & $response, array & $app){
        if (CTX()->get_session("LoginState") != true) {
            CTX()->redirect('index/do_index');
        }
        $ret = load_model('mycenter/OspReceiptModel')->delete_info_by_id($request['receipt_id']);
        exit_json_response($ret);
    }
    
    function init_login(array & $request, array & $response, array & $app){
        //获取客户id
        $kh_id = CTX()->get_session("kh_id");
        $data = load_model('mycenter/MyselfModel')->get_path_by_khid($kh_id);
        if(CTX()->get_session("LoginState")!=true || empty($data) || $data['pra_kh_status'] != 0 ){
            CTX()->redirect('index/do_index');
        }
        $response = load_model('mycenter/MyselfModel')->get_path_by_khid($kh_id);
  
    }
    
    function check_user_info(array & $request, array & $response, array & $app){
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        //获取客户id
        $kh_id = CTX()->get_session("kh_id");
        $obj = load_model('mycenter/InitLoginModel')->create_rds_db($kh_id);
        $res = load_model('mycenter/InitLoginModel')->get_user_info($obj,$request['user_code']);
        if($res){
           exit_json_response(array('status' => -1, 'data' => '', 'message' => 'error'));
        }else{
           exit_json_response(array('status' => 1, 'data' => '', 'message' => 'success'));
        }
    }
    
    function set_login(array & $request, array & $response, array & $app){
        if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        //获取客户id
        $kh_id = CTX()->get_session("kh_id");
        $obj = load_model('mycenter/InitLoginModel')->create_rds_db($kh_id);
        $res = load_model('mycenter/InitLoginModel')->add_user($obj,$request,$kh_id);
        if($res){
            exit_json_response(array('status' => 1, 'data' => '', 'message' => '初始化账户和密码成功'));
        }else{
            exit_json_response(array('status' => -1, 'data' => '', 'message' => '用户名已存在'));
        }
    }
    function get_auth_key(array & $request, array & $response, array & $app){
         if(CTX()->get_session("LoginState")!=true){
            CTX()->redirect('index/do_index');
        }
        $data = load_model('mycenter/MyselfModel')->get_auth_key($request['pra_id']);
        if($data){
            exit_json_response(array('status'=>1,'data'=>$data,'message'=>''));
        }else{
            exit_json_response(array('status'=>-1,'data'=>'','message'=>''));
        }
    }

}