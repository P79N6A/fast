﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=<?php echo $GLOBALS['context']->get_app_conf('charset') ?>" />
<title></title>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
    <link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
<?php echo load_css('animation-css3.css',true);?>
<?php echo load_js('jquery-1.8.1.min.js');?>
    <?php echo load_js('core.min.js');?>
</head>
<body>
<div id="container">
<?php	include $main_child_tpl; ?>
<?php $GLOBALS['context']->put_wlog();?>
</div>
</body>
</html>
