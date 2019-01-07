<?php

/**
 * 表格样式的form表单
 *
 * @author jia.ceng
 */
require_once ROOT_PATH . 'lib/ctl/Control.class.php';
require_lib('util/web_util', true);
class FormTable extends Control {
    private $data = array();
    private $scene = 'view';
    function render($clazz, $id, array $options) {
        $fields = $options['conf']['fields'];
        $hidden_fields = isset($options['conf']['hidden_fields'])?$options['conf']['hidden_fields']:array();
        $col = isset($options['col'])?$options['col']:'1';
        $per = isset($options['per'])?$options['per']:'';//标题与input的比例
        $this->data = isset($options['data'])?$options['data']:array();
        $this->scene = isset($options['scene'])?$options['scene']:(isset(CTX()->app['scene'])?CTX()->app['scene']:'view');
        include get_tpl_path($this->get_tpl_path());
    }
    
    function get_tpl_path() {
    	return 'ctl/FormTable';
    }
    
    function change_type(&$value,&$val){
        $val = (string)$val;
        
        switch ($value['type']){
            case 'textarea':
            case 'input':
                $value['type'] = 'label';
                break;
            case 'radio_group':
                $value['type'] = 'label';
                $_val = $val;
                $val = '';
                foreach($value['data'] as $k=>$v){
                    if($_val ==  current($v)){
                        $val = next($v);
                    }
                }                
                break;                
            case 'select':
                $value['type'] = 'label';
                $_val = $val;
                if($val==''){
                    break;
                }
                $val = '';
                foreach($value['data'] as $k=>$v){
                    $_v = current($v);
                    if($_val ===(string) $_v){
                        $val = next($v);
                    }
                }
                break;
        }
    }
    
    function format_input($value){
 
        $html = '';
        $disabled = '';
        if (isset($value['field'])){
	        $val = isset($this->data[$value['field']]) ?$this->data[$value['field']] : '';
                if(isset($value['format_js'])){
                    switch ($value['format_js']['type']){
                        case 'map':$val = $value['format_js']['value'][$val];
                    }
                }
        }
             
        $s = isset($value['class'])?" class='{$value['class']}'":'';
        $edit_scene = isset($value['edit_scene'])?$value['edit_scene']:'';
        if($this->scene=='view'||$edit_scene=='view') {
            $this->change_type($value,$val);
            $disabled = "disabled";
        }
        if($this->scene=='add') {
            $val = '';
        }
        $val = (string)$val;
        switch ($value['type']) {
            case 'label':
                $html = $val;
                break;
            case 'input':
                $html = "<input type='text' value='{$val}'  name='{$value['field']}' id='{$value['field']}' $s>";
                break;
            case 'select':
                $html = "<select id='{$value['field']}'  name='{$value['field']}'>";
                foreach($value['data'] as $k=>$v){
                    if($val === (string)current($v)||(isset($value['active'])&&$value['active']==current($v))){
                        $html .= "<option value='".current($v)."' selected>".next($v)."</option>";
                    }else{
                        $html .= "<option value='".current($v)."'>".next($v)."</option>";
                    }
                }
                $html .= "</select>";
                break;
            case 'radio_group':
                foreach($value['data'] as $k=>$v){
                    if(current($v)==$val||(isset($value['active'])&&current($v)==@$value['active'])){
                        $html .="<input type='radio' name='{$value['field']}' value='".current($v)."' checked {$disabled}>".next($v);
                    }else{
                        $html .="<input type='radio' name='{$value['field']}' value='".current($v)."' {$disabled}>".next($v);
                    }
                }
                break;
            case 'html':
                $html = $value['html'];
                break;
            case 'textarea':
                $html = "<textarea id='{$value['field']}'  name='{$value['field']}'>{$val}</textarea>";
                break;
        }
        return $html;
    }
}
