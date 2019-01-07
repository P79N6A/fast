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
<div id="container">
<div class="header">
      <div class="dl-title">
          <span class="lp-title-port"><img src="assets/img/logo.png" /></span><span class="dl-title-text"></span>
      </div>
    <div class="dl-log">欢迎您，<span class="dl-log-user"><b style="color: red"><?php echo CTX()->get_session("user_name") ?></b></span>
        <a href="?app_act=login/do_logout" title="退出系统" class="dl-log-quit">[退出]</a>
    </div>
   </div>
   <div class="content">
    <div class="dl-main-nav">
      <ul id="J_Nav"  class="nav-list ks-clear">
        <!--li class="nav-item dl-selected"><div class="nav-item-inner nav-storage">管理中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">个人中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">产品中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">客户中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">提单中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">授权中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">销售管理</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">知识中心</div></li>
        <li class="nav-item"><div class="nav-item-inner nav-inventory">统计中心</div></li-->
        <li class="nav-item dl-selected"><div class="nav-item-inner nav-storage">主页</div></li>
        <?php foreach ($response['top_menu'] as $item): ?>
            <li class="nav-item"><div class="nav-item-inner nav-order"><?php echo $item['action_name']?></div></li>
        <?php endforeach;?>
      </ul>
    </div>
    <ul id="J_NavContent" class="dl-tab-conten">
 
    </ul>
   </div>
    <script type="text/javascript" src="assets/js/config.js"></script>
  <script>
    
    BUI.use('common/main',function(){
      var config = [{
          id:'index', 
          homePage : 'index/do_welcome',
          menu:[{
              text:'首页',
              items:[                
                        {id:'index/do_welcome',text:'欢迎',href:'?app_act=index/do_welcome',closeable : false}                    
                    ]
               }]
      },<?php foreach ($response['menu_tree'] as $cote): ?>{
          id:'<?php echo $cote['action_code']?>', 
          menu:[<?php foreach ($cote['_child'] as $group): ?>{
              text:'<?php echo $group['action_name']?>',
              items:[<?php foreach ($group['_child'] as $url): ?>
                {id:'<?php echo base64_encode($url['action_code'])?>',text:'<?php echo $url['action_name']?>',href:'<?php echo get_app_url($url['action_code'])?>&ES_frmId=<?php echo base64_encode($url['action_code'])?>'},
                <?php endforeach;?>
              ]
            },<?php endforeach;?>]
      },
	  <?php endforeach;?>
      ];
      new PageUtil.MainPage({
        modulesConfig : config
      });
    });
  </script>
</div>
</body>
</html>
