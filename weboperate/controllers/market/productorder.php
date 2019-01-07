<?php

/*
 * 营销中心-产品订购
 */
require_lib('util/oms_util', true);
class productorder {

    //产品订购列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑产品订购显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑产品订购', 'add' => '新建产品订购');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('market/ProductorderModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
        $taobao_app = require_conf('taobao_app');
//        $response['taobao_app'][] = array('','请选择');
        foreach($taobao_app as $key=>$val){
             $response['taobao_app'][] = array($key,$val);
        }
        
    }

    //编辑订购信息数据处理。
    function porders_edit(array & $request, array & $response, array & $app) {
        $porders = get_array_vars($request, array(
                                'pro_channel_id',
                                'pro_kh_id',
                                'pro_cp_id',
                                'pro_price_id',
                                'pro_st_id',
                                'pro_sell_price',
                                'pro_rebate_price',
                                'pro_real_price',
                                'pro_dot_num',
                                'pro_seller',
                                'pro_desc',
                                'pro_hire_limit',
                                'pro_product_version',
                                'pro_product_area',
                                'pro_app_key',
            
        ));
        $ret = load_model('market/ProductorderModel')->update($porders, $request['pro_num']);
        exit_json_response($ret);
    }

    //添加产品订购信息数据处理。    
    function porders_add(array & $request, array & $response, array & $app) {
        $porders = get_array_vars($request, array(
                                'pro_channel_id',
                                'pro_kh_id',
                                'pro_cp_id',
                                'pro_price_id',
                                'pro_st_id',
                                'pro_sell_price',
                                'pro_rebate_price',
                                'pro_real_price',
                                'pro_dot_num',
                                'pro_seller',
                                'pro_desc',
                                'pro_hire_limit',
                                'pro_product_version',
                                 'pro_product_area',
                                'pro_app_key',
        ));
        $porders['pro_orderdate']=date('Y-m-d H:i:s');
        $ret = load_model('market/ProductorderModel')->insert($porders);
        exit_json_response($ret);
    }
    
    //
    function get_plan_price(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ProductorderModel')->get_planprice($request['priceid']);
        exit_json_response($ret);
    }
    
    //营销中心-产品订购审核
    function do_check_orders(array & $request, array & $response, array & $app) {
        if (!empty($request['_id'])) {
            $ret = load_model('market/ProductorderModel')->get_by_id($request['_id']);
            if (($ret['data']['pro_pay_status']) !== '1') {
                exit_json_response(load_model('market/ProductorderModel')->format_ret("-1", '', '请先付款'));
            }
            $check_update['pro_check_status'] = 1;
            $check_update['pro_checkdate'] = date('Y-m-d H:i:s');
            $check_stat = load_model('market/ProductorderModel')->update($check_update,$request['_id']);
            $pdetail = load_model('market/ProductorderModel')->get_plan_detail($ret['data']['pro_price_id']);
            $pshopnum = load_model('market/ProductorderModel')->get_shop_num($ret['data']['pro_price_id']);
            $orderauth = array();
            if (isset($ret['data'])) {
                //判断是否已经存在授权记录
                $authinfo=array('pra_kh_id' => $ret['data']['pro_kh_id'],
                                'pra_cp_id' => $ret['data']['pro_cp_id'],
                                'pra_product_version' => $ret['data']['pro_product_version'],);
                $ret_auth=load_model('products/ProductorderauthModel')->get_by_other($authinfo);
                if(!empty($ret_auth['data'])){
                    if ($ret['data']['pro_st_id']=="2") {  //租用
                        $orderauth['pra_enddate']=date('Y-m-d H:i:s',strtotime('+'.$ret['data']['pro_hire_limit'].' month', strtotime($ret_auth['data']['pra_enddate'])));  //期限
                    }else{
                        $orderauth['pra_enddate'] = date('Y-m-d H:i:s',strtotime("+20 year"));
                    }
                    $orderauth['pra_authnum']=$ret_auth['data']['pra_authnum']+$ret['data']['pro_dot_num'];   //点数
                    $orderauth['plat_shopinfo']=$pdetail['data'];//店铺明细
                    $orderauth['is_notice'] = 0;
                    //更新操作
                    $order_data = load_model('products/ProductorderauthModel')->update_order_auth($orderauth,$ret_auth['data']['pra_id']);
                    exit_json_response($order_data);
                }else{
                    if ($ret['data']['pro_st_id']=="2") {  //租用
                        $orderauth['pra_enddate'] = date('Y-m-d H:i:s',strtotime("+".$ret['data']['pro_hire_limit']. month));
                    }else{  //买断
                        $orderauth['pra_enddate'] = date('Y-m-d H:i:s',strtotime("+20 year"));
                    }
                    $orderauth['pra_pro_num'] = $ret['data']['pro_num'];
                    $orderauth['pra_kh_id'] = $ret['data']['pro_kh_id'];
                    $orderauth['pra_cp_id'] = $ret['data']['pro_cp_id'];
                    $orderauth['pra_product_area'] = $ret['data']['pro_product_area'];
                    $orderauth['pra_product_version'] = $ret['data']['pro_product_version'];
                    $orderauth['pra_authnum'] = $ret['data']['pro_dot_num'];
                    $orderauth['pra_shopnum'] = $pshopnum['data']['shopnum'];
                    $orderauth['pra_startdate'] = date('Y-m-d H:i:s');
                    $orderauth['pra_strategytype'] = $ret['data']['pro_st_id'];
                    $orderauth['pra_authkey'] = md5(uniqid());
                    $orderauth['plat_shopinfo'] = $pdetail['data'];
                    $orderauth['pra_state'] = '1';
                    $orderauth['pra_app_key'] = $ret['data']['pro_app_key'];
                    $orderauth['pra_serverpath'] = CTX()->get_app_conf('efast_redurl');
                    $order_data = load_model('products/ProductorderauthModel')->insert_order_auth($orderauth);
                    exit_json_response($order_data);
                }
            }
        } else {
            exit_json_response(load_model('market/ProductorderModel')->format_ret("-1", '', '订购编号错误'));
        }
    }
    
    //付款更新状态
    function  do_pay_orders (array & $request, array & $response, array & $app) {
        if (isset($request['_id'])) {
            $prorders['pro_pay_status'] = 1;
            $prorders['pro_paydate'] = date('Y-m-d H:i:s');
            $pay_status = load_model('market/ProductorderModel')->update($prorders,$request['_id']);
            exit_json_response($pay_status);
        }
    }


    /**系统部署独享页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function exclusive_view(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ProductorderModel')->get_row(array('pro_num' => $request['pro_num']));
        $response['data'] = $ret['data'];
    }

    /**系统部署共享页面
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function  share_view (array & $request, array & $response, array & $app) {
        $ret = load_model('market/ProductorderModel')->get_row(array('pro_num' => $request['pro_num']));
        $response['data'] = $ret['data'];
    }

    /**独享添加
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function  exclusive_add (array & $request, array & $response, array & $app) {
        $ret = load_model('market/ProductorderModel')->exclusive_add($request);
        exit_json_response($ret);

    }

    /**共享添加
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function  share_add (array & $request, array & $response, array & $app) {
        $ret = load_model('market/ProductorderModel')->share_add($request);
        exit_json_response($ret);
    }

    /**初始化账号
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function initialization_account(array & $request, array & $response, array & $app) {
        $response['data']['pro_num'] = $request['pro_num'];
    }


    /**初始化添加
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function set_login(array & $request, array & $response, array & $app) {
        //密码验证
        $this->check_pwd($request);
        $productorder = load_model('market/ProductorderModel')->get_by_id($request['pro_num']);
        if ($productorder['status'] != 1) {
            exit_json_response(array('status' => -1, 'data' => '', 'message' => '客户不存在！'));
        }
        $kh_id = $productorder['data']['pro_kh_id'];
        $user_data = array(
            'user_code' => $request['user_code'],
            'password' => $request['password'],
            'user_name' => $request['user_name'],
            'phone' => $request['tel'],
            'create_time' => date('Y-m-d H:i:s'),
        );
        $ret = load_model('pubdata/UserPubModel')->add_app_user_info($kh_id, $user_data);
        if ($ret['status']==1) {
            //发送邮件
            load_model('pubdata/UserPubModel')->send_email($productorder['data'], $user_data);
            //更新初始化状态
            $check_update['pro_is_init'] = 1;
            $check_stat = load_model('market/ProductorderModel')->update($check_update,$request['pro_num']);
            exit_json_response(array('status' => 1, 'data' => '', 'message' => '初始化账户和密码成功'));
        } else {
            exit_json_response(array('status' => -1, 'data' => '', 'message' => '用户名已存在'));
        }
    }

    /**密码规则验证
     * @param $request
     */
    function check_pwd($request) {
        $current_pwd = $re_pwd = '';
        if (isset($request['password']) && $request['password'] != '') {
            $current_pwd = $request['password'];
        } else {
            exit_json_response(-1, array(), "初始密码不能为空");
        }
        if (isset($request['re_password']) && $request['re_password'] != '') {
            $re_pwd = $request['re_password'];
        } else {
            exit_json_response(-1, array(), "确认密码不能为空");
        }
        if ($current_pwd !== $re_pwd) {
            exit_json_response(-1, array(), "确认密码与初始密码不一致");
        }
        if (strlen($current_pwd) < 8 || strlen($current_pwd) > 20) {
            exit_json_response(-1, array(), "密码长度必须为8-20位");
        }
        if (preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $current_pwd) == false) {
            exit_json_response(-1, array(), "密码须为数字、大写字母、小写字母和特殊符号的组合");
        }
    }
    
    //系统部署，产品订购列表
    function new_do_list(array & $request, array & $response, array & $app) {
        
    }

    /**版本升级(共享)
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function version_update(array & $request, array & $response, array & $app) {
        $ret = load_model('products/dbextmanageModel')->get_row(array('rem_db_khid'=>$request['kh_id']));
        $ret['data']['kh_name']=  oms_tb_val('osp_kehu', 'kh_name', array('kh_id'=>$request['kh_id']));
        $ret['data']['rds_link']=  oms_tb_val('osp_aliyun_rds', 'rds_link', array('rds_id'=>$ret['data']['rem_db_pid']));
        $response['data']=$ret['data'];
    }

    /**版本升级
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function version_update_action(array & $request, array & $response, array & $app) {
        $ret = load_model('products/dbextmanageModel')->version_update($request);
        exit_json_response($ret);
    }

    /**版本升级（独享）
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function exclusive_ver_update(array & $request, array & $response, array & $app) {
        $ret = load_model('products/dbextmanageModel')->get_row(array('rem_db_khid'=>$request['kh_id']));
        $ret['data']['kh_name']=  oms_tb_val('osp_kehu', 'kh_name', array('kh_id'=>$request['kh_id']));
        $ret['data']['rds_link']=  oms_tb_val('osp_aliyun_rds', 'rds_link', array('rds_id'=>$ret['data']['rem_db_pid']));
        $response['data']=$ret['data'];
    }
    
        /**删除
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('market/ProductorderModel')->delete($request['pro_num']);
        exit_json_response($ret);
    }
    
}
