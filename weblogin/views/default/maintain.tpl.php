<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>系统维护升级中</title>
<style>
/*reset*/
body,div,p,h1,h2,h3,h4,h5,h6,a,ul,li,ol,span,img,input,marquee{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
li{ list-style:none;}
a,a:hover,a:focus,a:active{ text-decoration:none;}
input,img{ border:none;}

.upgrade{ width:100%; height:100%; position:absolute; left:0; top:0; background:url(assets/img/upgrade.jpg) no-repeat; background-size:100% 100%; text-align:center; min-height:620px;}
.upgrade .rocket{ position:relative; top:4%; height:27%;}
.upgrade .rocket img{ height:100%;}
.upgrade .notice{ position:relative; top:8%; width:835px; margin:0 auto; text-align:center;}
.upgrade .notice h1{ color:#FFF;}
.upgrade .notice .p_01{ color:#FFF; text-align:left; padding:4% 0 3px; font-size:18px;}
.upgrade .notice .p_02{ color:#FFF; font-size:18px; text-align:left; padding:3% 20px 5%; border:2px solid #ffd18f; background:url(assets/img/clarity.png);}
.upgrade .notice .p_02 span{ color:#FFF;}
.upgrade .notice .p_03{ color:#FFF; text-align:left; font-size:16px; padding-top:3px; position:relative; z-index:10;}
.upgrade .notice .p_03 a{ color:#ab0c0c;text-decoration:underline;}
.upgrade .notice .p_04{ color:#FFF; text-align:right; font-size:16px; position:relative; top:-21px; z-index:9;}
.upgrade .bottom{ position:absolute; width:100%; bottom:5%; color:#666;}
</style>
</head>

<body>
<div class="upgrade">
<div class="rocket">
<img src="assets/img/rocket.png"></div>
<div class="notice">
	<h1><?php echo $response['data']['not_title'];?></h1>
    <p class="p_01">亲爱的商家：</p>
    <div class="p_02"><?php echo $response['data']['not_detail'];?></div>
    <p class="p_03">本次维护的功能项目<a href="<?php echo $response['data']['not_detail_url'];?>">请点击这里</a></p>
    <p class="p_04">宝塔信息科技<br><?php echo date('Y年m月d日');?></p>
</div>    
<div class="bottom">感谢您对eFAST365一如既往的支持，祝您工作愉快！</div>
</div>
</body>
</html>
