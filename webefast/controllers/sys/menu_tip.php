<?php
require_lib ( 'util/web_util', true );
class menu_tip {
 
    function get_tips(array &$request, array &$response, array &$app) {
        
        $response = load_model('oms/OrderMenuTipModel')->get_tip_all($request['type']);
    }
 
}
