<?php
require_lib ( 'util/web_util', true );
require_lib("keylock_util");
class Product {
    
    function do_index(array & $request, array & $response, array & $app) {
        $response['menutype']="2";
    }
}