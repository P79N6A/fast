<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $GLOBALS['context']->get_app_conf('charset') ?>" />
<title><?php if(isset($app['title'])) echo $app['title']; else echo "宝塔网络运维平台";?></title>
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
</head>
<body <?php if (CTX()->app['show_mode'] == 'pop'):?>style="overflow-x:hidden;overflow-y:auto"<?php endif;?>>
<?php echo load_js('jquery-1.8.1.min.js,bui/bui.js,util/date.js');?>
<?php echo load_js('common.js');?>
<div id="container" class="page_container">
<?php	include $main_child_tpl; ?>
<?php $GLOBALS['context']->put_wlog();?>
</div>
 <?php if(CTX()->get_app_conf('is_strong_safe')):?>
<script type="text/javascript" src="http://g.tbcdn.cn/sj/securesdk/0.0.3/securesdk_v2.js" id="J_secure_sdk_v2" data-appkey="23272446"></script>
<?php endif;?>
</body>
</html>
