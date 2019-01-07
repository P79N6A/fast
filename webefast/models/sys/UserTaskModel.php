<?php

/**
 * 用户执行任务
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class UserTaskModel extends TbModel {

    function __construct() {
        parent::__construct('sys_user_task');
    }

    /*
     * 创建任务
     */

    function create_task($request, $type) {
        $this->filer_request($request);
        $conf = $this->get_conf_type($type);
        if (empty($conf)) {
            $this->format_ret(-1, '', '对应配置不存在');
        }
        $ret_check = $this->check_task_user($type);
        if ($ret_check['status'] < 0) {
            $ret_data['task_id'] = $ret_check['data'];
            $ret_data['title'] = $conf['title'];
            $msg = '您上次执行尚未结束，请等待执行结束后再执行';
            return $this->format_ret(-2, $ret_data, $msg);
        }
        $task_data = array();
        $task_data['code'] = $this->get_task_code($type);
        $request['app_fmt'] = 'json';
        $request['__t_user_code'] = $this->get_user_code();
        $request['__t_user_name'] = $this->get_user_name();
        $request['_user_type'] = $type;
        $request = array_merge($conf['action'], $request);

        $task_data['start_time'] = time();
        $task_data['request'] = $request;

        $ret_task = load_model('common/TaskModel')->save_task($task_data);
        $ret_status = 1;
        $msg = '';
        $user_data = array(
            'task_code' => $type,
            'content' => json_encode($request),
            'msg' => '',
        );
        if ($ret_task['status'] == 1) {
            $ret_data['task_id'] = $ret_task['data'];
            $ret_data['title'] = $conf['title'];
            $user_data['task_id'] = $ret_data['task_id'];
            $this->save_user_task($user_data);
        } else {
            $ret_data['task_id'] = $ret_task['data'];
            $ret_data['title'] = $conf['title'];
            $ret_status = -2;
            $msg = '您上次执行尚未结束，请等待执行结束后再执行';
        }
        return $this->format_ret($ret_status, $ret_data, $msg);
    }

    private function get_task_code($type) {
        return $type . "_" . CTX()->get_session('user_code', TRUE);
    }

    /*
     * 检查任务
     */

    function check_task_user($type) {
        $param = array();
        $param['task_code'] = $type;
        $param['user_code'] = $this->get_user_code();
        $ret = $this->get_row($param);
        if (empty($ret['data'])) {
            return $this->format_ret(1);
        }
        if (empty($ret['data']['task_id'])) {
            return $this->format_ret(1);
        }
        $ret_task = $this->get_sys_task_status($ret['data']['task_id']);
        if ($ret_task['status'] < 1) {
            return $this->format_ret(1);
        }
        if ($ret_task['status']['data'] > 1) {
            $this->update_status($type); //设置状态
            return $this->format_ret(1);
        }
        return $this->format_ret(-1, $ret['data']['task_id']);
    }

    function save_user_task($data) {
        if (!empty($data)) {
            $data['user_code'] = $this->get_user_code();
        }
        return $this->insert_dup($data, 'update', 'task_id,content,msg');
    }

    /*
     * 获取后台运行任务状态
     */

    function get_sys_task_status($task_id) {
        return load_model('common/TaskModel')->get_task_status($task_id);
    }

    /*
     * 更新任务状态
     */

    function update_status($type) {
        $param['task_code'] = $type;
        $param['user_code'] = CTX()->get_session('user_code', TRUE);
        $data = array('task_id' => 0);
        return $this->update($data, $param);
    }

    private function get_conf_type($type) {
        $conf = require_conf('sys/user_task');
        return isset($conf[$type]) ? $conf[$type] : array();
    }

    /*
     * 获取任务状态
     */

    function get_task_status($request) {
        $task_id = $request['task_id'];
        $type = $request['type'];
        $ret_check = $this->get_sys_task_status($task_id);
        if ((int)$ret_check['data'] > 1) {
            $this->update_status($type);
            $param['task_code'] = $type;
            $param['user_code'] = $this->get_user_code();
            $ret = $this->get_row($param);
            if (empty($ret['data']['msg']) && $ret_check['data'] == 3) {
                $ret['data']['msg'] = '执行异常';
            }
            return $this->format_ret(2, $ret['data']['msg']);
        } else {
            return $ret_check;
        }
    }

    /*
     * 保存任务日志
     */

    function save_msg($msg, $type) {
        $param['task_code'] = $type;
        $param['user_code'] = $this->get_user_code();
        $data['msg'] = $msg;
        return $this->update($data, $param);
    }

    function get_user_code() {
        $user_code = '';
        if (CTX()->is_in_cli()) {
            $user_code = isset(CTX()->request['__t_user_code'])?CTX()->request['__t_user_code']:'';
        } else {
            $user_code = CTX()->get_session('user_code', TRUE);
        }
        return $user_code;
    }

    function get_user_name() {
        $user_name = '';
        if (CTX()->is_in_cli()) {
            $user_name =  isset(CTX()->request['__t_user_name'])?CTX()->request['__t_user_name']:'计划任务';
        } else {
            $user_name = CTX()->get_session('user_name', TRUE);
        }
        return $user_name;
    }

    private function filer_request(&$request) {

        $unset_arr = array('PHPSESSID', '_ati', '_umdata',);
        foreach ($request as $key => $val) {
            if (in_array($key, $unset_arr)) {
                unset($request[$key]);
            }
        }
    }

}
