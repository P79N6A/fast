<?php

require_lib('util/oms_util', true);

class policy_store{

    function do_list(array & $request, array & $response, array & $app) {
        
    }
   function clear_area(array & $request, array & $response, array & $app){
       $response = load_model("op/PolicyStoreAreaModel")->clear_area($request['store_code']);
   }
      function set_sort(array & $request, array & $response, array & $app){
       $response = load_model("op/PolicyStoreModel")->set_sort($request['store_code'],$request['sort']);
       
   }
   
   function set_area(array & $request, array & $response, array & $app){
   }
   
   function get_nodes(array & $request, array & $response, array & $app){
         $response = load_model("op/PolicyStoreAreaModel")->get_child($request['id'], $request['store_code']) ;
   }
   function do_save_area(array & $request, array & $response, array & $app){
             $app['fmt'] = 'json';
            $response = load_model('op/PolicyStoreAreaModel')->save_area($request);
   }
}