<?php

require_lib('util/web_util', true);

class export_csv {

    function export_show(array &$request, array &$response, array &$app) {
        check_export_token($request);
        $response = load_model("sys/ExportModel")->create_task($request);
    }

    function get_export_status(array &$request, array &$response, array &$app) {
        $response = load_model("sys/ExportModel")->get_status($request['task_id']);
    }

    function download_csv(array &$request, array &$response, array &$app) {
        load_model("sys/ExportModel")->downlaod_csv($request['file_key'], $request['export_name']);
    }

}
