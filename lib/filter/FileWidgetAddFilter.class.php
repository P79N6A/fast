<?php
/*
 * widget filter：从opt配置文件添加配件参数 ，不建议使用
 *将下面代码插入到req_reg.php文件对应位置，可激活此filter	
 *$context->register_request_filter('FileWidgetAddFilter','lib/filter/FileWidgetAddFilter.class.php',
 *	create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//从opt配置文件添加配件参数 ，不建议使用	  
 */
class FileWidgetAddFilter implements IRequestFilter{
	function handle_before(array & $request,array & $response,array & $app){
		$context=$GLOBALS['context'];
		$path=$grp=$act=NULL;
		if(isset($context->app['path'])) $path=$context->app['path'];
		if(isset($context->app['grp'])) $grp=$context->app['grp'];
		if(isset($context->app['act'])) $act=$context->app['act'];
		
		$opt_file=ROOT_PATH . $context->app_name .DIRECTORY_SEPARATOR .' views' . DIRECTORY_SEPARATOR . $context->theme .DIRECTORY_SEPARATOR ;
		if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW)  $opt_file.=$context->app_lang .DIRECTORY_SEPARATOR ;
		$opt_file .= "{$path}{$grp}_{$act}.opt.php";
		
		if(! file_exists($opt_file)) return;
		Widget::$opt_mdtime=filemtime($opt_file);
		include $opt_file;
		foreach ($options as $widget_id=>$opt)
			Widget::add($widget_id,$opt['action'],$opt['border'],$opt['title'],$opt['request']);		
	}
}