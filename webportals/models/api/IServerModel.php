<?php

/**
 * 接口服务
 * @author WMH
 */
class IServerModel {

    private $api_server = 'osp';

    public function osp_server($api, $params) {
        return load_model('common/ApiServerModel', FALSE)->exec_api($this->api_server, $api, $params);
    }

    public function osp_get_by_page($api, $params) {
        $server_config = array(
            'server' => $this->api_server,
            'api' => $api
        );
        return load_model('common/ApiServerModel', FALSE)->get_by_page($params, $server_config);
    }

}
