<?php
/**
 * 搜索选择按钮
 *
 * @author jia.ceng
 */
require_once ROOT_PATH . 'lib/ctl/Control.class.php';
require_lib('util/web_util', true);
class SearchSelectButton  extends Control {
    function render($clazz, $id, array $options) {
        
        include get_tpl_path($this->get_tpl_path());
    }
    
    function get_tpl_path() {
    	return 'ctl/SearchSelectButton';
    }
}
