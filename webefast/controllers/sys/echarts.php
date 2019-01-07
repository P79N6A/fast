<?php
require_lib('util/oms_util', true);
class echarts {

    /**
     * 首页登录
     */
    public function view_index(array &$request, array &$response, array &$app){
        $app['page'] = 'NULL';
        $user_code = CTX()->get_session('user_code');
        CTX()->db->update("sys_user",array("echarts_index"=>0),array('user_code'=>$user_code));

    }
    public function view_index_data(array &$request, array &$response, array &$app) {
        //$app['page'] = NULL;
        //判断登录账户类型
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
        //分销商登录显示分销商信息
        if($login_type == 2) {
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_data($user_code);
            
            $response['data'] = $custom;
            $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area(1);
            $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($custom['province']);
            $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($custom['city']);
        } else{
            $response['shop'] = load_model('base/ShopModel')->get_bui_select_shop('0');
        }
        $user_code = CTX()->get_session('user_code');
        CTX()->db->update("sys_user",array("echarts_index"=>1),array('user_code'=>$user_code));

    }

    /**
     * 双十一看板页面
     */
    public function view(array &$request, array &$response, array &$app) {
        //判断登录账户类型
        $login_type = CTX()->get_session('login_type');
        $response['login_type'] = $login_type;
        //分销商登录显示分销商信息
        if($login_type == 2) {
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_data($user_code);

            $response['data'] = $custom;
            $response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area(1);
            $response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($custom['province']);
            $response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($custom['city']);
        } else{
            $response['shop'] = load_model('base/ShopModel')->get_bui_select_shop('0');
        }
    }

    /**
     * 获取双十一的数据
     */
    public function getChartsData(array &$request, array &$response, array &$app) {
        $model = load_model('oms/OmsEchartDataModel');
        /** 对数据进行格式化 */
        $up = $model->getData($request['shop_code']);
        /** 判断数据是否为空 */
        if (empty($up)) {
            $up = $down = array(0, 0, 0, 0, 0, 0);
            $right = array(0, 0, 0, 0, 0, 0, 0);
        } else {
            $down = array_splice($up, 6);
            $right = array_splice($down, 6);
        }
        /** 1：生成完成，0：任务进行中 */
        exit_json_response($response['data'], compact('up', 'down', 'right'));
    }

    /**
     * 存储该店铺的数据
     */
    public function saveChartsDataByTask(array &$request, array &$response, array &$app) {
        $shop = load_model('base/ShopModel')->get_purview_shop();
        $shop_code_arr = array_column($shop, 'shop_code');
        array_unshift($shop_code_arr, '0');
        foreach ($shop_code_arr as $shop_code) {
            /** 对请求数据处理 */
            $start = date('Y-m-d H:i:s', strtotime('today'));
            $end = date('Y-m-d H:i:s');
            $where_time = "BETWEEN '{$start}' AND '{$end}'";
//            $where_time = 'BETWEEN "2016-11-11" AND "2016-11-12"';
            $api_model = load_model('oms/ApiOrderModel');
            $sell_model = load_model('oms/SellRecordModel');
            // $back_model = load_model('oms/ApiOrderSendModel');
            $save_model = load_model('oms/OmsEchartDataModel');
            /** 获取交易总笔数['total_order' => '', 'total_money' => ''] */
            extract($api_model->getTotalOrderNum($where_time, $shop_code));
            /** 获取转入的交易笔数['change_done' => '', 'change_todo' => ''] */
            extract($api_model->getChangeOrderNum($where_time, $shop_code));
            /** 获取确认的订单笔数['confirm_done' => '', 'confirm_todo' => ''] */
            extract($sell_model->getConfirmOrderNum($shop_code));
            /** 获取拣货订单笔数['pick_done' => '', 'pick_todo' => ''] */
            extract($sell_model->getPickOrderNum($shop_code));
            /** 获取发货订单笔数['delivery_done' => '', 'delivery_todo' => ''] */
            extract($sell_model->getDeliveryOrderNum($where_time, $shop_code));
            /** 获取回写订单笔数['back_done' => '', 'back_todo' => ''] */
            extract($sell_model->getBackOrderNum($where_time, $shop_code));
            //获取右边表格信息
            $rigth_data = $sell_model->get_is_change_fail_num($shop_code);
            /** 数据存入数据库 */
            $data = array(
                'up' => array($total_order, $change_done, $confirm_done, $pick_done, $delivery_done, $back_done),
                'down' => array($total_money, $change_todo, $confirm_todo, $pick_todo, $delivery_todo, $back_todo),
                'right' => array($rigth_data['fail_num'], $rigth_data['chec_timeout'], $rigth_data['overtime'], $rigth_data['write_fail'], $rigth_data['problem'], $rigth_data['out_store'], $rigth_data['pending']),
            );
            $save_model->saveData($shop_code, $data);
        }
        $response['status'] = 1;
    }

}
