<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_model('sys/carry/CarryBaseModel');

class CarryIndexModel extends CarryBaseModel {

    function exec(&$param) {
        $is_init = $this->init_action($param);
        if ($is_init === false) {
            return $this->format_ret(1);
        }
        load_model('sys/CarryDataModel')->set_index($param['task_sn'], $param['task_code']);
        $up_data['status'] = 2;
        $parent_task_code = isset($param['parent_task_code']) ? $param['parent_task_code'] : '';
        load_model('sys/carry/SysCarryTaskModel')->update_task($param['task_type'], $param['task_code'], $parent_task_code, $up_data);
        $this->check_task_is_over();
        return $this->format_ret(1);
    }

}
