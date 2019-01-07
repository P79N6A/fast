<?php
require_lib ( 'util/web_util', true );
class order{
    
    function check_deal_code(array &$request, array &$response, array &$app){
        $response = load_model("api/OrderModel")->get_row(array("tid"=>$request['deal_code']));
    }
}

