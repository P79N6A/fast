<div class="order_top_wrap">
    <div class="order_top">
        <a class="logo" href="?app_act=index/do_index"><img src="assets/img/order_logo.png" width="209" height="51"></a>
        <p class="nav" id="topmenu">
            <a id="menu1" class="curr" href="?app_act=index/do_index">首页</a><a id="menu2" href="?app_act=product/product/do_index">产品</a><a id="menu3" href="?app_act=about/do_about">关于宝塔</a>
        </p>
        <?php if(CTX()->get_session("LoginState")!=true){ ?>   
            <p class="btns" id="usercenter">
                <a class="login" id="login" href="javascript:void(0)">登陆</a><a class="register" id="register" href="javascript:void(0)">注册</a>
            </p>
        <?php } else{ ?> 
            <div class="person_icon" id="usercenter">
                <a href="?app_act=mycenter/myself/self_info&kh_id=<?php echo CTX()->get_session("kh_id") ?>"><img src="assets/img/person_icon.png"></a>
                <ul class="relate">
                    <li class="acc_set" onclick="location.href ='?app_act=mycenter/myself/self_info';">个人中心</li>
                    <!--li class="message">消息</li>
                    <li class="my_order">我的订单</li-->
                    <li class="exit" onclick="location.href ='?app_act=index/do_logout';">退出</li>
                    <i><img src="assets/img/relate_icon.png" width="10" height="6"></i>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
<script>
 var topmenu="<?php echo $response['menutype'] ?>";
 var menuid="menu"+topmenu;
 $("#topmenu a").removeClass("curr");
 $("#"+menuid).addClass("curr");
</script>
