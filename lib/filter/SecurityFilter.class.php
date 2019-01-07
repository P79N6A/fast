<?php

require_once ROOT_PATH . 'boot/req_inc.php';

class SecurityFilter implements IRequestFilter {

    function handle_before(array & $request, array & $response, array & $app) {
        $this->_do_xss();
    }
    
    private function _do_xss(){
        $clean_xss = CTX()->get_app_conf('global_clean_xss');
        
        if($clean_xss){
            $security = require_conf('security');
            $list = $security['clean_xss_opossite']['methods'];
            if(!in_array(get_app_act(), $list)){
                CTX()->request = clean_xss(CTX()->request);
            }
        }
 
    }

}

