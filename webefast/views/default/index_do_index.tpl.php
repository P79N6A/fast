  <div class="header">
    
      <div class="dl-title">
          <span class="lp-title-port"><img src="assets/img/efastlogo.jpg" /></span><span class="dl-title-text"></span>

      </div>

    <div class="dl-log">欢迎您，<span class="dl-log-user"><?php echo $response['user_name'];?></span><a href="<?php echo get_app_url('index/logout');?>" id="logout" title="退出系统" class="dl-log-quit">[退出]</a>
	<!--<a href="http://http://www.builive.com/" title="文档库" class="dl-log-quit">文档库</a>-->
    </div>
  </div>
   <div class="content">
    <div class="dl-main-nav">
      <div class="dl-inform"><div class="dl-inform-title">贴心小秘书<s class="dl-inform-icon dl-up"></s></div></div>
      <ul id="J_Nav"  class="nav-list ks-clear">
      	<li class="nav-item"><div class="nav-item-inner nav-order">主页</div></li>
        <?php foreach ($response['top_menu'] as $item): ?>
        <li class="nav-item"><div class="nav-item-inner nav-order"><?php echo $item['action_name']?></div></li>
        <?php endforeach;?>
      </ul>
    </div>
    <ul id="J_NavContent" class="dl-tab-conten">

    </ul>
   </div>
  <?php echo load_js('config.js');?>

  <script>
    BUI.use('common/main',function(){
      var config = [{
          id:'index', 
          homePage : 'index/welcome',
          menu:[{
              text:'首页',
              items:[                
                     {id:'index/welcome',text:'欢迎',href:'?app_act=index/welcome',closeable : false},
                    
             ]
            }]
      },<?php 
       $count_i = count($response['menu_tree']);
       foreach ($response['menu_tree'] as $cote): ?>{
          id:'<?php $count_i--; echo $cote['action_code']?>', 
          //homePage : 'kehu/kpkh/do_list',
          menu:[<?php  $count_j = count($cote['_child']);
                foreach ($cote['_child'] as $group): ?>{
              text:'<?php  $count_j--; echo $group['action_name']?>',
              items:[<?php  $count_k = count($group['_child']);
                    foreach ($group['_child'] as $url): ?>
                {id:'<?php $count_k--;  echo $url['action_code']?>',text:'<?php echo $url['action_name']?>',href:'<?php echo get_app_url($url['action_code'])?>&ES_frmId=<?php echo $url['action_code']?>'}<?php if($count_k!=0):?>,<?php endif;?>
                <?php endforeach;?>
              ]
            }<?php if($count_j!=0):?>,<?php endif;?><?php endforeach;?>]
            }<?php if($count_i!=0):?>,<?php endif;?>
	  <?php endforeach;?>
      ];
      new PageUtil.MainPage({
        modulesConfig : config
      });
    });
  </script>
