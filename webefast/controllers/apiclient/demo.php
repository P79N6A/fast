<?php
require_lib('util/web_util', true);
require_lib('apiclient/IwmsClient');
class demo
{
    function request1(array & $request, array & $response, array & $app) {
        $m = new IwmsClient(array('app_key'=>'something')); // 手工初始化全局参数, 实际业务中通常通过商店直接构造

        // 多线程请求示例:
        $h = array();
        $h[] = $m->newHandle('api1_name', array('a1'=>'a1', 'b1'=>'b1'));
        $h[] = $m->newHandle('api2_name', array('a2'=>'a2', 'b2'=>'b2'));

        $result = $m->multiExec($h);
        var_dump($result);
    }

    function request2(array & $request, array & $response, array & $app) {
        $m = new IwmsClient();
        $m->initByShopCode('c0002');  // 构造时根据商店代码初始化全局参数

        // 单个请求示例:
        $result = $m->getStock('p1', 'p2');
        var_dump($result);
    }
}
