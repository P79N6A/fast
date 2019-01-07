<?php
//transfer control request to control method
function do_index(array & $request,array & $response,array & $app){	
		$action  = $app ['ctl'];
		if(! $action ){
			$this->log_error ( "transfer control handle func fail " );
			$GLOBALS['context']->put_error(501,lang('req_err_501'));			
			return;
		} 
		$path=$grp=$method_name=NULL;
		$GLOBALS['context']->get_path_grp_act($action,$path,$grp,$method_name);
		$class_name= "{$path}{$grp}";
		$control=ControlFilter::new_control($class_name);
		if(! $control || ! $method_name || ! method_exists ( $control, $method_name )){
			$GLOBALS['context']->log_error ( "transfer control handle {$class_name}[{$method_name}] fail " );
			$GLOBALS['context']->put_error(501,lang('req_err_501'));			
			return;			
		}
		return $control->$method_name( $request,  $response,  $app );
}