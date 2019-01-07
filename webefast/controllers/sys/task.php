<?php

set_time_limit(0);
require_model("sys/SysTaskModel");
require_model("common/TaskModel");
require_model("api/sys/OrderSendModel");
require_lib('business_util', true);
header('Content-Type: text/html;charset=utf-8');

class task {

    function __construct() {
        $this->mdl = new SysTaskModel();
        //$this->mdl_auto = new TaskModel();
    }

    function download_order(array & $request, array & $response, array & $app) {

//        $start_time = date("Y-m-d H:i:s",  strtotime("-6 hours"));
//        $param = array("start_time"=>$start_time,"end_time"=>date("Y-m-d H:i:s"),"shop_code"=>'cartelo优众专卖店');//"shop_code"=>'cartelo优众专卖店'
//	$this->mdl->get_order($param);
//        $this->mdl->get_detail();
//        $response['status'] = 1;
    }

    function order_send(array & $request, array & $response, array & $app) {

        $this->mdl->send_order_all();
        $response['status'] = 1;
    }

    function get_express(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $response = $this->mdl->get_express();
    }

    function send_order(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $type = 0;
        if (isset($request['type']))
            $type = $request['type'];
        if ($request['send_local'] && $request['send_local'] == 'send_local') {
            $response = $this->mdl->send_local($request['id'], $type, $request['action']);
        } else {
            $response = $this->mdl->send_order($request['id'], $type);
        }
        if ($response['status'] == -1) {
            $response['fail_num'] = load_model("sys/OrderSendModel")->get_count_by_status(-1);
        } else {
            $response['fail_num'] = 0;
        }
    }

    function get_goods(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $param = array();
        if (isset($request['start_time'])) {
            $param['start_time'] = $request['start_time'];
        }
        if (isset($request['end_time'])) {
            $param['end_time'] = $request['end_time'];
        }
        if (isset($request['shop_code'])) {
            $param['shop_code'] = $request['shop_code'];
        }
        if (isset($request['status']) && $request['status'] == "onsale") {
            $param['inv_goods'] = false;
        }
        if (isset($request['status']) && $request['status'] == "inv") {
            $param['onsale_goods'] = false;
        }
        if (isset($request['goods_id'])) {
            $param['goods_id'] = $request['goods_id'];
        }

        $response = $this->mdl->get_goods($param);
    }

    function get_order(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $start_time = "";
        $end_time = "";
        $message = "";
        //$task_id = $this->mdl_auto->get_task_id($request);
        if (isset($request['start_time'])) {
            $start_time = $request['start_time'];
        }
        if (isset($request['end_time'])) {
            $end_time = $request['end_time'];
        }

        $message .= "正在拉取" . get_shop_name_by_code($request['shop_code']) . $start_time . "-" . $end_time . "的订单列表";
        $param = array("start_time" => $start_time, "end_time" => $end_time, "shop_code" => $request['shop_code']);

        $response = $this->mdl->get_order($param);

        $message .= $response['message'] . "<br />";
        $response['message'] = $message;
        //$this->mdl_auto->save_log($task_id, $message);
    }

    function get_detail(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";
        $response = $this->mdl->get_detail();
        //$task_id = $this->mdl_auto->get_task_id($request);
        //$this->mdl_auto->save_log($task_id, $response['message']);
    }

    function sync_goods_inv(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";

        $response = $this->mdl->sync_goods_inv($request['id']);
    }

    function get_ncm_order(array & $request, array & $response, array & $app) {
        $app['fmt'] = "json";

        $response = $this->mdl->get_ncm_order($request);
    }

}
