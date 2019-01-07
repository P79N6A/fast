<?php
/***模块注册、重要常量配置，请谨慎更改***/

/***设置运行模式和重要常量***/
	define('DEBUG',true); 				//是否调试态，调试态可产生调试日志，关闭权限处理等

	define('RUN_SAFE',false);			//是否设置安全运行模式，如必须使用cookie输入app_efid等.
	define('RUN_CONTROL',true); 		//是否激活控件支持
	define('RUN_WIDGET',false); 			//是否激活配件支持
	define('RUN_COMBILE_TPL',true); 	//是否激活tpl文件组合装配缓存支持
	define('RUN_MLANG_VIEW',true); 		//是否支持多语言view
	define('RUN_TPL_DIR',true); 		//是否支持action对应tpl文件在action文件同名目录下
	define('RUN_TPL_ACT_FILE',true); 	//仅在RUN_TPL_DIR=true下，有效是否支持action对应tpl文件名仅为方法名，如果为false，tpl文件名为类名_方法名
	
	
	define('APP_SALT','EfasT'); 		//数据加密或签名用的附加文字
	
/***end 设置运行模式和重要常量***/	
	
	
/**系统设置:注册tools，请小心更改**/

	$context->register_tool('log','lib/tool/Log.class.php');
  
	//$context->register_tool('db','lib/db/Mysql.class.php');
  	$context->register_tool('db','lib/db/PDODB.class.php');
  	
	$context->register_tool('conf','lib/tool/Config.class.php','FileConfig');
	//$context->register_tool('conf','lib/tool/Config.class.php','ApcConfig');
	//$context->register_tool('conf','lib/tool/Config.class.php','MemCacheConfig');
	//$context->register_tool('conf','lib/tool/Config.class.php','EmptyCacheConfig');
  
	$context->register_tool('cache','lib/tool/Cache.class.php','FileCache');
	//$context->register_tool('cache','lib/tool/Cache.class.php','ApcCache');
	//$context->register_tool('cache','lib/tool/Cache.class.php','MemCacheCache');
	//$context->register_tool('cache','lib/tool/Cache.class.php','EmptyCache');
  	
/**end 系统设置**/
  
/**系统设置: 注册filters和renders，请小心更改**/
	//request filter
	if(defined('RUN_ACC_CTL') && RUN_ACC_CTL) $context->register_request_filter('UserAccessFilter','lib/filter/UserAccessFilter.class.php',
		create_function('$app','return isset($app["mode"]) && $app["mode"]=="func";'));		//添加访问是否非法检查，增加create_function('$app','return $app["mode"]=="func";'仅对func有效。
  		
	if(defined('RUN_CONTROL') && RUN_CONTROL)  $context->register_request_filter('ControlFilter','lib/ctl/Control.class.php',
		create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));

	if(defined('RUN_WIDGET') && RUN_WIDGET) $context->register_request_filter('DBWidgetAddFilter','lib/filter/DBWidgetFilter.class.php',
		create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//从widget_opt数据库配置表添加配件参数
    
	//reponse filter
	if(defined('RUN_WIDGET') && RUN_WIDGET) $context->register_response_filter('CacheWidgetCallFilter','lib/filter/DBWidgetFilter.class.php',
		create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//调用配件app函数 ，并缓存组合 Widget action	文件
	//if(defined('RUN_WIDGET') && RUN_WIDGET) $context->register_response_filter('WidgetCallFilter','lib/filter/DBWidgetFilter.class.php',
	//	create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//直接调用配件Widget action函数,不缓存组合
    
	if(defined('RUN_COMBILE_TPL') && RUN_COMBILE_TPL) $context->register_response_filter('CombineTPLFilter','lib/filter/CombineTPLFilter.class.php',
		create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//装配并缓存模板文件
              
	//renderers
	$context->register_renderer('JsonRenderer','lib/filter/JsonRenderer.class.php',
		create_function('$app','return isset($app["mode"]) && ($app["mode"]=="func" || $app["fmt"]=="json");'));//function($app){return $app["mode"]=="func" || $app["fmt"]=="json";});
  		
	$context->register_renderer('CsvRenderer','lib/filter/CsvRenderer.class.php',
		create_function('$app','return $app["fmt"]=="csv";'));//function($app){return $app["fmt"]=="csv";});
  		
	$context->register_renderer('HtmlRenderer','lib/filter/HtmlRenderer.class.php',
		create_function('$app','return $app["fmt"]=="html";'));
  
/**end 系统设置**/
    