<?php
require_lib ( 'util/web_util', true );
require_model("sys/SysTaskModel");
class express{
    function index(array &$request, array &$response, array &$app){
        $app['tpl'] = "api/taobao/express/index";
    }
}