<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title>  </title>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
</head>
<style>
.sys_auth{ width:550px; position:absolute; left:50%; margin-left:-275px; top:50%; margin-top:-210px; background:#FFF; box-shadow:0 0 10px #ccc; border-radius:3px;}
.sys_auth h1{ width:200px; height:75px; background:#1695ca; position:absolute; left:50%; margin-left:-100px; top:-38px; text-align:center; box-shadow:2px 3px 5px #ccc;}
.sys_auth h1 img{ padding-top:20px;}
.sys_auth table{ width:90%; margin:80px 0 0 5%; color:#666; font-size:14px;}
.sys_auth table tr:first-child{ border-bottom:2px solid #1695ca;}
.sys_auth table tr:last-child{ border-bottom:2px solid #1695ca;}
.sys_auth table tr td{ padding:15px 0 15px 20px;}
.sys_auth table tr td.rowname{ color:#999;}
.sys_auth .copyright{ padding:15px 5%; background:url(assets/img/sys/copyright.jpg) no-repeat 10% center; background-size:250px auto; font-size:12px; overflow:hidden;}
.sys_auth .copyright span{float:right;}
.sys_auth .copyright a{ float:left;margin-left:57%}
.sys_auth .copyright span.telephone{ font-size:20px; color:#FF0000; position:relative; right:102px;}
.sys_auth .copyright span.note{ color:#999;}
</style>
<body>
    <?php include get_tpl_path('web_page_top'); ?>
<div class="sys_auth">
        
	<h1>
            <?php 
                $response['cp_code'] = strtolower($response['cp_code']);
            if($response['cp_code']=='efast5_standard'): ?>
            <img src="assets/img/ui/efast365_standard.png" />
            <?php elseif($response['cp_code']=='efast5_enterprise'): ?>
             <img src="assets/img/ui/efast365_enterprise.png" />
            <?php else: ?>
               <img src="assets/img/ui/efast365_ultimate.png" />
        <?php endif ;?>
        </h1>
        
        
        
	<table>
	<?php 
	 foreach($response['auth'] as $row){
	 	echo "<tr><td class='rowname'>{$row['name']}</td><td>{$row['value']}</td></tr>";
	 }
	?>
	</table>
    <p class="copyright">
        <span class="telephone">400-600-9585</span>
    <br /><a href="http://www.baison.com.cn/" target="_blank">baison.com.cn</a><br /><span class="note">Copyright©2017,All Rights Reserved</span>
    </p>
</div>
</body>
</html>