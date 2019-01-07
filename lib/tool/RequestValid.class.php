<?php

class RequestValid{
	var $rules;
	function parseRule($rule_group,$rule_method){
		$rules=NULL;
		include $rule_group;
		$this->rules=$rules[$rule_method];
	}
	function & toJSON(){
		return json_encode($this->rules); 
	}
	function valid(array & $request){
		$rulfunc=new RuleValidMethod();
	}
}

class RuleValidMethod{
	function not_null($str){
		if ( ! is_array($str))
			return (trim($str) == '') ? false : true;
		else
			return ( ! empty($str));
	}
	function str($str,$minlen,$maxlen){
		$strlen=strlen($str);
		if($minlen!==NULL && $strlen <$minlen ) return false;
		return $maxlen!==NULL && $strlen>$maxlen;
	}
	function int($str,$min,$max){
		$option=array();
		if($min!==NULL) $option['min_range']=$min;
		if($max!==NULL) $option['max_range']=$max;
		return filter_var($str, FILTER_VALIDATE_INT,$option)!==false;
	}	
	function float($str,$min,$max,$decimal){
		$option=array();
		if($decimal!==NULL) $option['decimal']=$decimal;
		$data=filter_var($str, FILTER_VALIDATE_FLOAT,$option);
		if($data==false) return false; 
		if($min!==NULL && $min <$data ) return false;
		return $max!==NULL && $data>$max;
	}		
	function date($str){
		
	}	
	function enum($str){
		
	}	
	function email($str){
		return filter_var($str, FILTER_VALIDATE_EMAIL)!==false;
	}
	function ip($str){
		return filter_var($str, FILTER_VALIDATE_IP)!==false;
	}	
	function url($str){
		return filter_var($str, FILTER_VALIDATE_URL)!==false;
	}
	function base64($str){
		
	}
	function regexp($str,$value){
		return filter_var($str, FILTER_VALIDATE_REGEXP,array('regexp'=>$value))!==false;
	}		
	function eq($str){
		
	}
	function gt($str){
		
	}
	function lt($str){
		
	}			
}


