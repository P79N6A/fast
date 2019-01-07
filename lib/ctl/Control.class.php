<?php
require_once ROOT_PATH.'boot/req_inc.php';

function render_control($clazz,$id,array $options=array()){
	$control=ControlFilter::new_control($clazz);
	try {
		if($control) $control->render($clazz,$id,$options);
	} catch ( Exception $e ) {
		$GLOBALS['context']->log_error ( "call control render [{$clazz}] fail," . $e->getMessage () );
	}
}
abstract class Control implements IControl{
	 static $ctl_class='ctl_class_';

	function render($clazz,$id,array $options){
		if(! $clazz) $clazz=get_class($this);
		echo self::encode_ctl_clazz($clazz,$id,$options);
	}
	function handle($clazz,$id,$options,array & $request,array & $app){}

	static function encode_ctl_clazz($clazz,$id,array $options){
		$opt=base64_encode(serialize(array('clz'=>$clazz,'opt'=>$options)));
		return "<input type='hidden' name='". self::$ctl_class ."{$id}' value='{$opt}'>";
	}
	static function decode_ctl_clazz($name,$value){
		$cnt=strlen(self::$ctl_class);
		if(strncasecmp($name,self::$ctl_class,$cnt)!==0) return false;
		$id=substr($name,$cnt,strlen($name)-$cnt);
		$a=unserialize(base64_decode($value));
		if(! isset($a['clz']) || !  isset($a['opt'])) return false;
		return array('id'=>$id,'clazz'=>$a['clz'],'options'=>$a['opt']);
	}
	static function pump($name,array & $request){
		if( isset($request[$name])){
			$result=$request[$name];
			unset($request[$name]);
		}else $result=NULL;
		return $result;
	}
}

class ControlFilter implements IRequestFilter{
	static function get_class_file($clazz){
		$clazz_file=ROOT_PATH."common/lib/ctl/{$clazz}.ctl.php";
		if(! file_exists($clazz_file)) $clazz_file=ROOT_PATH. $GLOBALS['context']->app_name ."/lib/ctl/{$clazz}.ctl.php";
		if(! file_exists($clazz_file)) $clazz_file=ROOT_PATH. "lib/ctl/{$clazz}.ctl.php";
		if(! file_exists($clazz_file)) $clazz_file=NULL;
		return $clazz_file;
	}
	static function new_control($clazz){
		$clazz_file=ControlFilter::get_class_file($clazz);
		if(! $clazz_file){
			$GLOBALS['context']->log_error ( "control render [{$clazz}] class not found ");
			return;
		}
		try {
			include_once $clazz_file;
			$class_name=basename($clazz);
			$obj=new $class_name();
			return $obj;
		} catch ( Exception $e ) {
			$GLOBALS['context']->log_error ( "call control render [{$clazz_file}] fail," . $e->getMessage () );
		}
	}
	function handle_before(array & $request,array & $response,array & $app){
		$cnt=strlen(Control::$ctl_class);
		foreach($request as $name=>$val){
			if(strncasecmp($name,Control::$ctl_class,$cnt)===0){
				$id=substr($name,$cnt,strlen($name)-$cnt);
				$a=unserialize(base64_decode($val));
				if(isset($a['clz']) && isset($a['opt'])){
					$control=ControlFilter::new_control($a['clz']);
					try {
						if($control)  $control->handle($a['clz'],$id,$a['opt'],$request,$app);
					} catch ( Exception $e ) {
						$GLOBALS['context']->log_error ( "call control filter [{$a['clz']}] fail," . $e->getMessage () );
					}
				}
			}
		}//end foreach
	}
}

