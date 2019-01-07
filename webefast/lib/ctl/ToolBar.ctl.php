<?php

/**
 * 工具条
 *
 * @author jia.ceng
 */
require_once ROOT_PATH . 'lib/ctl/Control.class.php';
require_lib('util/web_util', true);
class ToolBar extends Control {
    protected $btn_custom_js = array();
    protected $btn_default_js = array();
    function render($clazz, $id, array $options) {
        $button = $options['button'];
        $check_box = isset($options['check_box'])?$options['check_box']:'';
        $custom_js = isset($options['custom_js'])?$options['custom_js']:false;
        if(isset($options['default_url'])){
            $default_url = $options['default_url'];
        }
        include get_tpl_path($this->get_tpl_path());
    }
    
    function get_tpl_path() {
    	return 'ctl/ToolBar';
    }
}
