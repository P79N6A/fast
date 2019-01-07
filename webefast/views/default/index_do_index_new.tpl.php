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
<div id="container">
<?php echo load_js('jquery-1.8.1.min.js');?>
<?php echo load_js('core.min.js');?>
<?php echo load_js('base64.js',true);?>
<script type="text/javascript" src="<?php echo get_app_url('common/js/index')?>"></script>

<link href="assets/css/initial.css" rel="stylesheet" type="text/css" />
<script src="assets/js/initial.js"></script>
<style type="text/css" >

#toplogo{
    background: url("assets/img/ui/<?php echo $response['cp_area'];?>.png") no-repeat scroll 1% center rgba(0, 0, 0, 0);
    height: 44px;
    overflow: hidden;
    width: 100%;}
</style>
<div class="top" id="toplogo">
	<div class="search_wrap" style="display: none">
    	<select>
        	<option>订单</option>
            <option>商品</option>
            <option>订单</option>
        </select>
        <input type="text" />
        <span class="searchbtn">搜索</span>
    </div>

    <div id="news" class="news_inner">
        <marquee width=400 scrollamount=2>
			<p class="news_info" data-id=""><a href="" target="_blank"></a></p>
		</marquee>
    </div>
    <p class="widget">
         <a class="operate" href="?app_act=server_value#server/value/value_add/server_list"  target="_blank">服务市场
        </a>
        <?php if ($response['pra_strategytype'] == 2) { ?>
            <a class="server" href="javascript:void(0)">在线客服
            </a>
        <?php } ?>
        <a class="help" href="http://operate.baotayun.com:8080/efast365-help/"  target="_blank">在线帮助</a>
        <a class="tickling" href="<?php echo $response['operateurl'].'&khid='.$response['md5kh_id'].'&user_code='.$response['user_code'].'&user_name='.$response['user_name'] ?>"  target="_blank">产品吐槽</a>
            <a class="change_pwd" href="javascript:void(0)">修改密码</a>

        <span class="welcome">欢迎您！<br />
        <strong><?php echo $response['user_name']; ?></strong></span>
        <a class="exit" href="<?php if(defined('CLOUD') && CLOUD){ echo $GLOBALS['context']->get_app_conf('cloud_url');} else {echo get_app_url('index/logout');} ?>" title="退出系统">退出系统</a>
    </p>
</div>
<div class="left left_sim">
    <?php if($response['list_type'] == 'server_value') { ?>
        <div class="fixed_item"><i class="item_icon"></i><span class="cont">服务市场</span></div>
    <?php } else { ?>
        <div class="fixed_item"><i class="item_icon"></i><span class="cont">主页</span></div>
    <?php } ?>
    <div id="left_menu" style="overflow:auto;">
    <ul class="list" id="J_Nav">
        <?php if($response['list_type'] != 'server_value') { ?>
            <li class="nav-item"><a class="a01 ac" href="javascript:void(0)"><span class="nav-item-inner nav-order">主页</span></a></li>
        <?php } ?>
        <!--li class="nav-item"><a class="a02" href="javascript:void(0)"><span class="nav-item-inner nav-order">网络订单</span></a></li>
        <li class="nav-item"><a class="a02" href="javascript:void(0)"><span class="nav-item-inner nav-order">配发货</span></a></li>
        <li class="nav-item" style="width:130px;"><a class="a03" href="javascript:void(0)"><span class="nav-item-inner nav-order">进销存管理</span></a></li>
        <li class="nav-item"><a class="a04" href="javascript:void(0)"><span class="nav-item-inner nav-order">会员管理</span></a></li>
        <li class="nav-item"><a class="a05" href="javascript:void(0)"><span class="nav-item-inner nav-order">商品管理</span></a></li>
        <li class="nav-item"><a class="a06" href="javascript:void(0)"><span class="nav-item-inner nav-order">基础数据</span></a></li>
        <li class="nav-item"><a class="a07" href="javascript:void(0)"><span class="nav-item-inner nav-order">系统管理</span></a></li-->
                <?php
                $nav_arr = array('4000000'=>'a02','8000000'=>'a03','7000000'=>'a04','30000000'=>'a05','6000000'=>'a06','9000000'=>'a07','3000000'=>'a08','21000000'=>'a09','5000000'=>'a10','2000000'=>'a11','1000000'=>'a12','22000000'=>'a13');
                foreach ($response['top_menu'] as $item): ?>
        <?php

        if($item['action_id']=='80000000'):?>
          <li class="nav-item"><a class="a15" name="<?php echo $item['action_code'];?>"  href="?app_act=api" target="_blank"><span class="nav-item-inner nav-order"><?php echo $item['action_name']?></span></a></li>
        <?php else:?>

        <li class="nav-item"><a class="<?php echo $nav_arr[$item['action_id']];?>"  name="<?php echo $item['action_code'];?>"   href="javascript:void(0)"><span  class="nav-item-inner nav-order"><?php echo $item['action_name']?></span></a></li>

 <?php endif;?>
     <?php endforeach;?>
    </ul>
    </div>
    <div class="box">
    	<div class="contact">
        	<i class="sharp"></i>
            <h3>在线客服</h3>
            <p class="qq_wrap">
            <a class="qq" href="http://crm2.qq.com/page/portalpage/wpa.php?uin=4006809510&f=1&ty=1&aty=0&a=&from=5" target="_blank"><img src="assets/img/ui/zx.jpg" /> </a></p>
                <p class="p_01"><span>服务时间：
                9:00-12:00 13:30-17:30（工作日）<br/>
                温馨提示：<br/>
                客服咨询，如果进入排队状态，请耐心等待，一般情况30分钟内会被接入并受理<br/>
                非工作时段咨询请留言，我们会在看到留言时第一时间给您回复</span></p>
                <p class="p_02"></p>
            </div>
            <div class="pwd_contact">
            		<i class="sharp"></i>
                    <h3>修改密码</h3>
                    <table>
                            <tr>
                                    <td class="note">用户<i>*</i></td>
                                    <td style="text-indent:5px;"><?php echo $response['user_name']; ?></td>
                            </tr>
                            <tr>
                                    <td class="note">当前密码<i>*</i></td>
                                    <td><input type="password" name="current_pwd"/> <input type="hidden" name="psw_strong" value ="<?php echo $response['psw_strong']; ?>"/></td>
                            </tr>
                            <tr>
                                    <td class="note">新密码<i>*</i></td>
                                    <td><input type="password" name="new_pwd"/></td>
                            </tr>
                            <tr>
                                    <td class="note">确认密码<i>*</i></td>
                                    <td><input type="password" name="sure_pwd"/></td>
                            </tr>
                    </table>
                    <p class="notice">
							 <?php if($response['psw_strong']==1){?>
                                    <span>注：密码长度为8-20位，须为数字、大写字母、小写字母和特殊符号的组合</span>
                             <?php }else{ ?>
                             		<span>注：密码长度为8-20位，须为数字和字母的组合</span>
                             <?php }?></p>
                    <div class="sure"><input type="button" id="cancel" value="取&nbsp;消" /><input type="submit" id="change_pwd" value="确&nbsp; 认" /></div>
            </div>
        <ul id="J_NavContent" class="dl-tab-content"></ul>
    </div>
</div>
<?php echo load_js('config.js'); ?>
<?php echo load_js('jquery.mousewheel.min.js'); ?>
<?php echo load_js('jquery.cookie.js'); ?>
<script>
    var page_type = '<?php echo $app['act'] ?>';
    var indexM,updatemenu;
    BUI.use('common/main', function(){
        var home_page = new Array;
    <?php if($response['list_type'] != 'server_value') { ?>
         home_page = {
                    id:'index',
                    homePage : page_type=='increment'?'':'<?php echo $response['echarts_index'] ?>',
                    menu:[page_type=='increment'?{}:{
                        text:'首页',
                        items:[{id:'<?php echo $response['echarts_index'] ?>', text:'欢迎使用 ^_^', href:'?app_act=<?php echo $response['echarts_index'] ?>', closeable : false}]
                    }]
                };
    <?php } ?>
            var config = [
                home_page,

<?php
$count_i = count($response['menu_tree']);
foreach ($response['menu_tree'] as $cote):
    ?>{
            id:'<?php $count_i--; echo $cote['action_code']?>',
            <?php if(isset($response['home_page'][$cote['action_code']])): ?>
            homePage : '<?php echo $response['home_page'][$cote['action_code']]['id']; ?>',
                <?php endif;?>
            menu:[<?php
    $count_j = count($cote['_child']);
    foreach ($cote['_child'] as $group):
        ?>{
                            text:'<?php $count_j--;
        echo $group['action_name']
        ?>',
                                    items:[<?php
        $count_k = count($group['_child']);
        foreach ($group['_child'] as $url):
            ?>
                                            {id:'<?php $count_k--;
            echo $url['action_code']
            ?>', text:'<?php echo $url['action_name'] ?>', href:'<?php echo get_app_url($url['action_code']) ?>&ES_frmId=<?php echo $url['action_code'] ?>'
                     <?php if(isset($response['home_page'][$cote['action_code']])&&$response['home_page'][$cote['action_code']]['id']===$url['action_code']):?>, closeable : false<?php endif;?>
                            }<?php if ($count_k != 0): ?>,<?php endif; ?>
        <?php endforeach; ?>
                                                        ]
                                                }<?php if ($count_j != 0): ?>,<?php endif; ?><?php endforeach; ?>]
                                }<?php if ($count_i != 0): ?>,<?php endif; ?>
<?php endforeach; ?>


                        ];
                        indexM= new PageUtil.MainPage({
                        width_item:{value:80},
                        pageConfig:{subHeight:120},
                        modulesConfig : config
                        });
                            updatemenu =function(code){
                                var url = '?app_act=index/get_menu&app_fmt=json';
                                var data = {};
                                data.code= code;
                                $.post(url, data, function(result){
                                    indexM._updateContents(result.data);
                             }, 'json');
                        }

                        });





$(document).ready(function(){
    //实现左侧一级菜单鼠标滚动事件
    $('#J_Nav').bind('mousewheel', function(event, delta, deltaX, deltaY) {
        var contenter_h = $('#left_menu').height();
        var menu_h = $('#J_Nav').height();
        var m = $('#left_menu').scrollTop();
        if(-1==deltaY){
            //鼠标滚动向下
            m = m+15;
            if(m>menu_h-contenter_h){
                m = menu_h-contenter_h;
            }
            $('#left_menu').scrollTop(m);
        }else{
            //鼠标滚动向上
            m = m-15;
            if(m<0){
                m=0;
            }
            $('#left_menu').scrollTop(m);
        }
    });
    setTimeout(function(){
        var navlist = $('#J_Nav li');
         var itemcount = navlist.length;
      if(navlist.last().css('display')=="none"){
			$('.roll_rigt').addClass('roll_rigt_ac')
        var show_i = 0;
        var show_max = 0;
        for(var i=0;i<itemcount;i++){
            if(navlist.eq(i).css('display')!='none'){
                show_max++;
            }else{
                break;
            }
        }
        show_max = show_max-1;
        $('.roll_left').click(function(){
            if(show_i>0){
                show_i--;
                navlist.eq(show_i).show();
                navlist.eq(show_max).hide();
                show_max--;
            }
        });
         $('.roll_rigt').click(function(){
            show_max++;
            if(show_max<itemcount){
                navlist.eq(show_i).hide();
                 show_i++;
                 navlist.eq(show_max).show();
            }else{
                show_max--;
            }
        });
    }else{$('.roll_rigt').removeClass('roll_rigt_ac')}
    },500);


    $.each($('#J_Nav a'),function(){
       var  menu_name = $(this).attr('name');
       var tip_all = ['order','fenxiao','stm'];
       if($.inArray(menu_name,tip_all)>-1 ){
           $(this).click(function(){
                var id='M_'+menu_name+'_menu';
               var url = '?app_act=sys/menu_tip/get_tips&app_fmt=json&type='+menu_name;
               $.post(url,{},function(ret){
                        for(var key in ret.data){
                            var act = $('#'+id+" li[data-id='"+key+"']");
                            var em = $(act);
                             if(ret.data[key]>0){
                                  if(em.find("span.remindpoint").length==0){
                                      em.find('a').eq(0).after('<span class="remindpoint"></span>');
                                  }
                             }else{
                                  if(em.find("span.remindpoint").length>0){
                                      em.find("span.remindpoint").eq(0).remove();
                                  }
                             }
                        }


               },'json');
           });
       }
    });


});

$(function(){
	var left_height=$(".left").css("height")
	left_menu_height=parseInt(left_height)-43+'px';
	$("#left_menu").css("height",left_menu_height);

	$(".list li").click(function(){
		$("#left_menu").hide();
		var a_bgimg=$(this).children("a").css("background-image")
		var item_inner=$(this).find(".nav-item-inner").html()
		$(".fixed_item").children(".item_icon").css("background-image",a_bgimg)
		$(".fixed_item").children(".cont").html(item_inner);
		})
	$(".fixed_item").hover(function(){
		$("#left_menu").show();
		})
	$("#left_menu").hover(function(){

		},function(){
			$(this).hide();
			})

	$(".dl-second-slib-con").click(function(){
		$(".fixed_item").toggle()
		})
	})
</script>

<?php $GLOBALS['context']->put_wlog();?>
</div>
</body>
</html>