<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $GLOBALS['context']->get_app_conf('charset') ?>" />
<title> <?php  echo (isset($app['title'])&&!empty($app['title']))?$app['title']:'宝塔eFAST 365'?> </title>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
</head>
<body style="overflow-x:hidden;">
    <?php include get_tpl_path('web_page_top'); ?>
<style type="text/css">
#__sys_loading{position:fixed;_position:absolute;top:50%;left:50%;width:120px;height:120px;overflow:hidden;background:url(<?php echo get_theme_url('images/loading.gif');?>) no-repeat;z-index:10;padding-left: 40px; paddding-top:20px}
</style>
<div id="__sys_loading"><b>数据加载中</b></div>
<div id="container" class="page_container <?php if (in_array(CTX()->app['show_mode'], array('select', 'pop'))) echo 'pop_container';?>">
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('core.min.js');?>
<script type="text/javascript" src="<?php echo get_app_url('common/js/index')?>"></script>
<?php include $main_child_tpl; ?>
<?php $GLOBALS['context']->put_wlog();?>
</div>

<script type="text/javascript">
$(function() {
	jQuery("#__sys_loading").fadeOut()  
});
</script>

<?php include get_tpl_path('j_secure_sdk'); ?>
</body>
</html>
