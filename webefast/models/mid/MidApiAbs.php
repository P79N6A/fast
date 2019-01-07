<?php

require_model('tb/TbModel');

abstract class MidApiAbs extends TbModel {

    abstract function create_api($api_conf);

    abstract function upload($record_code);

    abstract function cancel($record_code);

    function write_log($log_params, $api_names) {
        $filepath = ROOT_PATH.'logs/server_api_'. $api_names . date('Ymd') . '.log';
        file_put_contents($filepath, "time:".date('Y-m-d H:i:s'), FILE_APPEND);
        file_put_contents($filepath, var_export($log_params, TRUE) . "\n", FILE_APPEND);
    }
}
