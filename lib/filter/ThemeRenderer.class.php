<?php
require_once ROOT_PATH.'inc/app.inc.php';

//web页面模板处理
define('WEB_PAGE_TPL','web_page.tpl.php'); //web页面模板
define('WEB_PAGE_ERR_TPL','web_page_error.tpl.php'); //错误页面模板

$GLOBALS['_webpage_prefix_stack']=array('');
$GLOBALS['_webpage_postfix_stack']=array();
function push_web_page_prefix($html_item){
	array_push($GLOBALS['_webpage_prefix_stack'],$html_item);		
}
function echo_web_page_prefix(){
	if(count($GLOBALS['_webpage_prefix_stack'])>0)
		echo array_shift($GLOBALS['_webpage_prefix_stack']);		
}

function push_web_page_postfix($html_item){
	array_push($GLOBALS['_webpage_postfix_stack'],$html_item);		
}
function echo_web_page_postfix(){
	if(count($GLOBALS['_webpage_postfix_stack'])>0)
		echo array_pop($GLOBALS['_webpage_postfix_stack']);		
}
function get_parent_web_page($dirpath) {
	$views_path=ROOT_PATH . $GLOBALS['context']->app_name . DIRECTORY_SEPARATOR."views/{$GLOBALS['context']->scheme}" ;
	while ( $dirpath && $dirpath != DIRECTORY_SEPARATOR && $dirpath!=$views_path){
		$pos=strrpos($dirpath,DIRECTORY_SEPARATOR);
		$dirpath=substr($dirpath,0,$pos);
		if (file_exists ( $dirpath .DIRECTORY_SEPARATOR. WEB_PAGE_TPL ))
			 return $dirpath .DIRECTORY_SEPARATOR. WEB_PAGE_TPL;
	}
}
function get_tpl_path($tplname) {
	$views_path=ROOT_PATH . $GLOBALS['context']->app_name . DIRECTORY_SEPARATOR."views/{$GLOBALS['context']->scheme}". DIRECTORY_SEPARATOR ;
	return $views_path.$tplname.'.tpl.php';
}

function get_scheme_url($url_part) {
	$ctx=$GLOBALS['context'];
	return $ctx->get_app_conf('base_url')."/theme/{$ctx->scheme}/{$url_part}";
}
//end web页面模板处理

class ThemeRenderer implements IReponseRenderer {
	
	function render(array & $request,array & $response,  array & $app) {
		if ($app ['fmt'] !== 'theme') return; 
		
		$webpage_charset=$GLOBALS['context']->get_app_conf('charset');
		header('Content-Type: text/html;charset='.$webpage_charset);
		
		if(isset($app ['ttl']) || $app ['ttl']>0)
			header("Cache-Control:max-age={$app ['ttl']}");

		$_views_path=ROOT_PATH.$app ['name'].DIRECTORY_SEPARATOR."views/{$GLOBALS['context']->scheme}".DIRECTORY_SEPARATOR;
		
		if($app['err_no']!==0){
			include $_views_path. WEB_PAGE_ERR_TPL;
			return true;
		}
		$_app_path=$app['path'];
		
		if(isset($app['tpl']) && $app['tpl'])	$main_child_tpl= "{$app['tpl']}.tpl.php";
		else $main_child_tpl= "{$_app_path}{$app['grp']}_{$app['act']}.tpl.php";
		
		if(! file_exists($_views_path.$main_child_tpl)){
			$app['err_no']=20001;
			$app['err_msg']='找不到主模板文件['. $main_child_tpl.']';
			include $_views_path. WEB_PAGE_ERR_TPL;
			return true;
		}
		
		$main_child_tpl=$_views_path.$main_child_tpl;
		$webpage_prefix='';
		
		$web_page_path=NULL;
		
		
		
		//find $main_child_tpl 's web page template in app_path dir,found break
		if(! file_exists($_views_path.$_app_path. WEB_PAGE_TPL)){
			$_app_path=substr($_app_path,0,strlen($_app_path)-1);
			while ( $_app_path && $_app_path != DIRECTORY_SEPARATOR){
				$pos=strrpos($_app_path,DIRECTORY_SEPARATOR);
				if($pos===false) $_app_path='';
				else  $_app_path=substr($_app_path,0,$pos);
				if (file_exists ($_views_path.$_app_path . WEB_PAGE_TPL )){
					$web_page_path=$_views_path.$_app_path;
					break;					
				}
			}		
		}
		else $web_page_path=$_views_path.$_app_path ;
		
		$_web_page_file=$web_page_path. WEB_PAGE_TPL;
		if($web_page_path && file_exists($_web_page_file)){
			include $_web_page_file;
		}else{
			echo '<html><head><meta http-equiv="content-Type" content="text/html; charset=UTF-8">';	
			if(isset($app['title'])) 	echo '<title>'.$app['title']."</title>\n";
	    	echo "\n</head><body>\n";
 			include $main_child_tpl;
			echo "\n</body></html>";
		}
		return true;
	}
}

