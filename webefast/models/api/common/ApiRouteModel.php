<?php

/**
 * 公共接口分发
 * @author WMH
 */
class ApiRouteModel {

    /**
     * 取消接口
     * @param array $param
     * @return array
     */
    public function api_cancel($param) {
        $ret = load_model('sys/ApiServiceConfigModel')->get_service_by_archives($param);
        if (empty($ret['data'])) {
            return $ret;
        }
        $api_config = $ret['data'];

        $model = ucfirst($api_config['service_code']) . 'ClientModel';
        require_model('api/common/' . $model);
        $obj = new $model($api_config);
        $ret = $obj->cancel($param);
        if ($ret['status'] < 1) {
            return $ret;
        }
    }

}
