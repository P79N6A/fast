<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sysServerModel
 *
 * @author wq
 */
class sysServerModel {

    /**
     * 
     * @param type $api
     * @param type $params
     * @return type
     */
    function osp_server($api, $params) {
        return load_model('common/ApiServerModel')->exec_api('osp', $api, $params);
    }
}
