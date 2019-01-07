<?php
require_lib ( 'util/web_util', true );
require_lib ( 'app_util', true );
class Index {
         function __construct() {
            CTX()->response['app_main_index'] = 0;

        }


    function do_index(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
        $response['title'] = '今日罗盘';
//        $pre_date = date('Y-m-d', strtotime('-1 days'));
        $model = load_model('oms/OmsEchartDataModel');
        //获取昨日成交量，发货量
        $pre = $model->getData(0, 1);
        $response['pre_data']['sell_num'] = empty($pre[0]) ? 0 : (int)$pre[0];
        $response['pre_data']['oms_send'] = empty($pre[4]) ? 0 : (int)$pre[4];
    }
 
        function logout(array & $request, array & $response, array & $app) {

		load_model('sys/UserModel')->logout();
                $url = 'http://login.yishangonline.com/weblogin/web/?app_act=index/login_app';
     
		CTX()->redirect($url);

        }
        
  
  
}