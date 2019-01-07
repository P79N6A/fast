<?php

require_lib('util/web_util', true);

class question_label {

    function do_list(array &$request, array &$response, array &$app) {
    }

    function detail(array &$request, array &$response, array &$app) {
        $title_arr = array('edit' => '编辑设问标签', 'add' => '添加设问标签');
        $app['title'] = $title_arr[$app['scene']];
        if (isset($request['_id'])) {
            $ret = load_model('base/QuestionLabelModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
        } else {
            $response['data'] = array();
        }
    }

    function do_edit(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('content'));
        $ret = load_model('base/QuestionLabelModel')->update($data, $request['question_label_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $data = get_array_vars($request, array('question_label_code', 'question_label_name', 'remark'));
        $ret = load_model('base/QuestionLabelModel')->insert($data);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/QuestionLabelModel')->delete($request['question_label_id']);
        exit_json_response($ret);
    }

    function update_active(array &$request, array &$response, array &$app) {
        $ret = load_model('base/QuestionLabelModel')->update_active($request['type'], $request['id']);
        exit_json_response($ret);
    }


    /**
     * 编辑订单超重
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function over_weight(array &$request, array &$response, array &$app) {
        $ret = load_model('base/QuestionLabelModel')->get_row(array('question_label_code' => 'SELL_RECORD_OVERWEIGHT'));
        $response = $ret['data'];
    }

    /**
     * 保存设置的重量
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function do_edit_weight(array &$request, array &$response, array &$app) {
        $params = get_array_vars($request, array('content'));
        $ret = load_model('base/QuestionLabelModel')->update_over_weight($params);
        exit_json_response($ret);
    }


}
