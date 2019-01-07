<?php

require_model('tb/TbModel');
require_lib('apiclient/TaobaoClient');

/**
 * Description of TicketModel
 *
 * @author wq
 */
class TicketModel extends TbModel {

    private $app_config;

    function create_ticket($api_data, $ticket_form) {
        $this->save_api_ticket($api_data);
        return $this->create_sys_ticket($api_data, $ticket_form);
    }

    function save_api_ticket($api_data) {
        $data = array();
        $key_arr = array(
            'ticket_id', 'ticket_type', 'enterprise_id', 'ticket_form', 'content'
        );
        foreach ($key_arr as $key) {
            $data[$key] = $api_data[$key];
        }
        $data['create_time'] = time();
        $update_str = " ticket_form=VALUES(ticket_form), create_time = VALUES(create_time) ";
        $this->insert_multi_duplicate('api_qn_tickets', array($data), $update_str);
    }

    function create_sys_ticket($api_data, $ticket_form) {


        $return_data = array(
            'isv_action' => array(
                'name' => '宝塔工作台',
//                'url' => 'http://224343/refund.do', //退的订单地址，获取信息
            ),
        );
        $action_data = array();

        $deal_code = $this->get_tid_by_type($ticket_form, $api_data['ticket_type']);
        $action_data[] = $this->set_ticket($deal_code, $api_data);

        $return_data['isv_tickets'] = $action_data;

        $type_arr = array(
            'official_refund_with_goods_return',
            'official_refund',
            'official_trade_item_exchange',
        );

        //$api_data['ticket_type']
        if(in_array($api_data['ticket_type'], $type_arr)){
            $url = "http://efast.baotayun.com/efast365/webefast/web/open/qn_refund.php";
            $kh_id = CTX()->saas->get_saas_key();
            $url.="?kh_id=" . $kh_id . "&ticket_id=" . $api_data['ticket_id'];
            $return_data['isv_action']['url'] = $url;
        }

        return $return_data;
    }

    function set_ticket($deal_code, &$api_data) {

        $sys_data = array(
            'ticket_id' => $api_data['ticket_id'],
            'deal_code' => $deal_code,
            'record_type' => 'sell_record', //默认订单
            'record_code' => 0, //需要完成查询
            'action' => $api_data['ticket_type'], //根据 ticket_type 转换处理
        );

        $hander_data = array(
            'hander' => '',
            'status' => 1,
            'log_content' => '工单已经接收，尽快安排处理'
        );

        $sys_data = array_merge($sys_data, $hander_data);

        $this->insert_exp('sys_tickets', $sys_data);
        $hander_data['id'] = $this->insert_id();

        //创建生成退单任务
        $this->create_exec_ticket_task($api_data['ticket_id']);


        return $hander_data;
    }

    function create_exec_ticket_task($ticket_id) {

        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();
        $task_data['code'] = "exec_ticket";
        $request['ticket_id'] = $ticket_id;
        $request['app_act'] = 'qianniu/exec_ticket';
        $request['app_fmt'] = 'json';
        $task_data['start_time'] = time();
        $task_data['request'] = $request;
        $ret = $task->save_task($task_data);

        return $ret;
    }

    function get_tid_by_type($ticket_form, $type) {
        $deal_code = '';
        switch ($type) {
            case 'official_refund_with_goods_return':
                $deal_code = isset($ticket_form['refund']['tid']) ? $ticket_form['refund']['tid'] : '';
                break;
            case 'official_refund':
                $deal_code = isset($ticket_form['refund']['tid']) ? $ticket_form['refund']['tid'] : '';
                break;
            case 'official_trade_item_exchange':
                $deal_code = isset($ticket_form['exchange']['tid']) ? $ticket_form['exchange']['tid'] : '';
                break;
            case 'official_trade_modify_address':
                $deal_code = isset($ticket_form['tid']) ? $ticket_form['tid'] : '';
                break;
            default :
                break;
        }
        return $deal_code;
    }

    function cancel_tickets($api_data, $ticket_form) {

        $data['is_stop'] = 1;
        $data['stop_time'] = time();
        $where = " ticket_id='{$api_data['ticket_id']}' ";
        $this->update_exp('api_qn_tickets', $data, $where);
        $ticket_id_arr = explode(",", $api_data['isv_tickets']);
        //一定可以终止 
        $return_data = array();
        $handler_arr = array();
        foreach ($ticket_id_arr as $sys_id) {
            $handler_arr[] = $this->cancel_sys_tickets($sys_id, $api_data['ticket_id']);
        }
        $return_data['isv_tickets'] = $handler_arr;
        return $return_data;
    }

    function cancel_sys_tickets($id, $ticket_id) {
        $data['is_stop'] = 1;
        $where = " id = '{$id}' AND ticket_id = '{$ticket_id}' ";
        $this->update_exp('sys_tickets', $data, $where);
        $hander_data = array(
            'id' => $id,
            'status' => 1,
            'log_content' => '任务关联订单停止处理业务接收，正在处理',
        );
        return $hander_data;
    }

    function set_api_config() {
        if (empty($this->app_config)) {
            $auth_data = load_model('sys/SysAuthModel')->get_auth();
            $qn_app_key = $auth_data['qn_app_key'];
            $api_confg_all = require_conf("sys/app_info");
            $this->app_config = $api_confg_all['taobao'][$qn_app_key];
            //  $this->app_config['session'] = $auth_data['qn_app_session'];
        }
    }

    function download_tickets() {
        //这个流程暂时不用

        $auth_data = load_model('sys/SysAuthModel')->get_auth();
        $enterprise_id = $auth_data['qn_enterprise_id'];
        $code = 'download_tickets_max_id';
        $ret_id = load_model('sys/SysScheduleRecordModel')->get_record($code);
        $api_param = array(
            'enterprise_id' => $enterprise_id,
            'template_name' => 'official_test',
        );
        $last_max_id = 0;
        if (!empty($ret_id['data']['shop_code'])) {
            $api_param['last_max_id'] = $ret_id['shop_code'];
        }


        while (true) {
            $ret = $this->ticketsystem_query($api_param);
            if ($ret['status'] < 0) {
                break;
            }
            $last_max_id = $ret['data']['last_max_id'];
            $this->save_tickets_info($ret['data']['items']['items']);
            //
            if ($ret['data']['has_more'] === false || count($ret['data']['items']['items']) < 100) {
                break;
            }
        }
        $updata_data = array('shop_code' => $last_max_id);
        load_model('sys/SysScheduleRecordModel')->update_code($updata_data, $code);
    }

    function save_tickets_info($data) {
        //ticket_id
        $ticket_data = array();
        foreach ($data as $val) {
            $ticket_data[] = array(
                'ticket_id' => $val['id'],
                'template_name' => $val['template_name'],
                'form_value' => $val['form_value'],
            );
        }
    }

    function hanlder_tickets($id) {

        $sql = "select * from sys_tickets where id=:id ";
        $sql_values = array(':id' => $id);
        $data = $this->db->get_row($sql, $sql_values);
        //todo:实现处理过程

        $hander_data = array();
        //都是必填字段
        $hander_data['hander'] = '操作人员'; //操作人员
        $hander_data['log_content'] = '业务处理测试'; //业务处理内容
        $hander_data['status'] = 2; // 2 正常 3 异常
        $hander_data['ticket_id'] = $data['ticket_id'];
        $hander_data['isv_ticket_id'] = $data['id'];
        //接口提交前可以保存1次防止接口异常，信息丢失


        $ret_api = $this->upload_tickets($hander_data);
        if ($ret_api['status'] > 0) {
            $hander_data['is_upload'] = 1;
        }
        $where = " id = '{$id}' ";
        $this->update_exp('sys_tickets', $hander_data, $where);
        return $this->format_ret(1);
    }

    function upload_tickets($api_param) {



        $TaobaoClient = new TaobaoClient();
        $this->set_api_config();

        $TaobaoClient->set_api_config($this->app_config);

        $api = 'taobao.qianniu.worklink.isvticket.change.upload';
        $data = $TaobaoClient->getTaobaoData($api, $api_param);

        if (isset($data['qianniu_worklink_isvticket_change_upload_response']['result'])) {
            $ret = $this->format_ret(1);
        } else {
            $ret = $this->format_ret(-1, $data, '接口异常');
        }
        return $ret;
    }

    function get_ticket($ticket_id) {
        $sql = "select * from sys_tickets where ticket_id=:ticket_id";
        $data = $this->db->get_row($sql, array(':ticket_id' => $ticket_id));
        return $this->format_ret(1, $data);
    }

    function ticketsystem_query($api_param) {
        $TaobaoClient = new TaobaoClient();
        $this->set_api_config();
        $TaobaoClient->set_api_config($this->app_config);

        $api = 'taobao.qianniu.worklink.ticketsystem.query';
        $data = $TaobaoClient->getTaobaoData($api, $api_param);
        var_dump($this->app_config, $api_param, $data);
        die;
        if (isset($data['qianniu_worklink_ticketsystem_query_response']['gw_result']['result']['items']['items'])) {
            $ret = $this->format_ret(1, $data['qianniu_worklink_ticketsystem_query_response']['gw_result']['result']['items']['items']);
        } else {
            $ret = $this->format_ret(-1, $data, '接口异常');
        }
        return $ret;
    }

    function multi_account($api_param = array()) {
        $TaobaoClient = new TaobaoClient();
        $this->set_api_config();
        $this->app_config['session'] = '50000400939s8is7inuwOrDAYoUjLsZVrhfEjvenWXFkmuGyjZE1bc4ce39xWRIK5UM';
        $TaobaoClient->set_api_config($this->app_config);
        //     $api_param = array('auth_token'=>'500008000412N0awTp9bQQlxlkFuZqpBg4g0cLyE0wCBicphm5s4X1fc2110bWgStus');
        $api_param = array('auth_token' => '50001800241yM0UoacPLEwEooq1tTdYanzdopD0TleBFw5kVvqe5B17fc1c9fZIPo9B');
        $api = 'taobao.qiannniu.yungw.multi.account.auth';
        $data = $TaobaoClient->getTaobaoData($api, $api_param);
        var_dump($this->app_config, $api_param, $data);
        die;
        if (isset($data['qianniu_worklink_ticketsystem_query_response']['gw_result']['result']['items']['items'])) {
            $ret = $this->format_ret(1, $data['qianniu_worklink_ticketsystem_query_response']['gw_result']['result']['items']['items']);
        } else {
            $ret = $this->format_ret(-1, $data, '接口异常');
        }
        return $ret;
    }

}
