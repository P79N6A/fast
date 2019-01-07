<?php
/**
* **模块注册、重要常量配置，请谨慎更改**
*/

/**
* **设置运行模式和重要常量**
*/
define('DEBUG', true); //是否调试态，调试态可产生调试日志，关闭权限处理等

define('RUN_SAFE', false); //是否设置安全运行模式，如必须使用cookie输入app_efid等.
define('RUN_FAST', false); //是否设置快速运行模式，如忽略检查timezone等，相信系统已经配置好参数.
define('RUN_SESSPUB', true); //是否设置session是否全局，各webapp共享session，其cookie的path为/
define('RUN_ACC_CTL', false); //是否激活用户访问控制模块
define('RUN_USER_DEBUG', false); //是否激活按用户设置调试态模块
define('RUN_CONTROL', true); //是否激活控件支持
define('RUN_WIDGET', false); //是否激活配件支持
define('RUN_COMBILE_TPL', false); //是否激活tpl文件组合装配缓存支持
define('RUN_WEB_LOG', true); //是否激活网页显示log记录

define('APP_SALT', 'eSDM'); //数据加密或签名用的附加文字

/**
* **end 设置运行模式和重要常量**
*/

/**
* *系统设置:注册tools，请小心更改*
*/

$context -> register_tool('log', 'lib/tool/Log.class.php');

$context -> register_tool('db', 'lib/db/PDODB.class.php');

$context -> register_tool('conf', 'lib/tool/Config.class.php', 'FileConfig', 'IConfig', 'set_conf');
// $context->registerTool('conf','lib/tool/Config.class.php','ApcConfig','IConfig','set_conf');
// $context->registerTool('conf','lib/tool/Config.class.php','MemCacheConfig','IConfig','set_conf');
// $context->register_tool('conf','lib/tool/Config.class.php','EmptyCacheConfig','IConfig','set_conf');

$context -> register_tool('cache', 'lib/tool/Cache.class.php', 'FileCache', 'ICache', 'set_cache');
//$context -> register_tool('cache', 'lib/tool/Cache.class.php', 'SeaCache', 'ICache', 'set_cache');
// $context->registerTool('cache','lib/tool/Cache.class.php','ApcCache','ICache','set_cache');
// $context->registerTool('cache','lib/tool/Cache.class.php','MemCacheCache','ICache','set_cache');
// $context->register_tool('cache','lib/tool/Cache.class.php','EmptyCache','ICache','set_cache');
/**
* *end 系统设置*
*/

/**
* *系统设置: 注册filters和renders，请小心更改*
*/
// request filter
// if(defined('RUN_ACC_CTL') && RUN_ACC_CTL) //$context->register_request_filter('UserAccessFilter','lib/filter/UserAccessFilter.class.php');
$context -> register_request_filter('InputFilter', 'lib/filter/InputFilter.class.php');
 //$context->register_request_filter('UserAclFilter', 'lib/filter/UserAclFilter.class.php');

if (defined('RUN_USER_DEBUG') && RUN_USER_DEBUG) $context -> register_request_filter('UserDebugFilter', 'lib/filter/UserDebugFilter.class.php',
    create_function('$app', 'return !( defined("DEBUG") && DEBUG) && isset($app["user_debug"]);')); //function($app){return !( defined("DEBUG") && DEBUG) && isset($app["user_debug"]);});

if (defined('RUN_CONTROL') && RUN_CONTROL) $context -> register_request_filter('ControlFilter', 'lib/ctl/Control.class.php',
    create_function('$app', 'return $app["fmt"]=="html" && $app["err_no"]==0;'));

if (defined('RUN_WIDGET') && RUN_WIDGET) $context -> register_request_filter('DBWidgetAddFilter', 'lib/filter/DBWidgetFilter.class.php',
    create_function('$app', 'return $app["fmt"]=="html" && $app["err_no"]==0;')); //从widget_opt数据库配置表添加配件参数

// reponse filter
if (defined('RUN_WIDGET') && RUN_WIDGET) $context -> register_response_filter('CacheWidgetCallFilter', 'lib/filter/DBWidgetFilter.class.php',
    create_function('$app', 'return $app["fmt"]=="html" && $app["err_no"]==0;')); //调用配件app函数 ，并缓存组合 Widget action	文件
// if(defined('RUN_WIDGET') && RUN_WIDGET) $context->register_response_filter('WidgetCallFilter','lib/filter/DBWidgetFilter.class.php',
// create_function('$app','return $app["fmt"]=="html" && $app["err_no"]==0;'));//直接调用配件Widget action函数,不缓存组合
if (defined('RUN_COMBILE_TPL') && RUN_COMBILE_TPL) $context -> register_response_filter('CombineTPLFilter', 'lib/filter/CombineTPLFilter.class.php',
    create_function('$app', 'return $app["fmt"]=="html" && $app["err_no"]==0;')); //装配并缓存模板文件

// renderers
$context -> register_renderer('JsonRenderer', 'lib/filter/JsonRenderer.class.php',
  create_function('$app', 'return $app["mode"]=="func" || $app["fmt"]=="json";')); //function($app){return $app["mode"]=="func" || $app["fmt"]=="json";});

$context -> register_renderer('CsvRenderer', 'lib/filter/CsvRenderer.class.php',
  create_function('$app', 'return $app["fmt"]=="csv";')); //function($app){return $app["fmt"]=="csv";});

$context -> register_renderer('HtmlRenderer', 'lib/filter/HtmlRenderer.class.php',
  create_function('$app', 'return $app["fmt"]=="html";'));

/**
* *end 系统设置*
*/
