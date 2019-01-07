<?php
/**
 * Created by PhpStorm.
 * User: BS
 * Date: 2018/1/26
 * Time: 14:26
 */
class test_strategy{
    //模拟测试
    public function execute(array &$request,array &$response,array &$app){
        $type = isset($request['type']) ? $request['type'] : '';
        switch ($type){
            case 'test_gift':
                $func = 'test_gift_strategy';
                break;
            default:
                $func = $type;
        }
        $ret = load_model('op/OpTestStrategyModel')->$func($request);
        exit_json_response(1,$ret);
    }
    //测试交易号选择
    public function test_tid(array &$request,array &$response,array &$app){

    }
}