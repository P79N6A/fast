<style>
.sqxx .top{ position:relative;}
.sqxx .top .ljdg{ position:absolute; top:10px; right:12px; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.sqxx .top .ljdg1{position:absolute; top:10px; right:9%; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.sqxx .top .ljdg:hover{ background:#f86a37;}
</style>
<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="person_wrap">
    	<div class="person">
            <div class="sidebar">
            	<p class="person_pic"><img src="assets/img/person_pic.png"></p>
                <p class="person_name"><?php echo CTX()->get_session("kh_name") ?></p>
                <ul class="person_options" id="person_options">
                    <li class="li_01 "><a href='?app_act=mycenter/myself/self_info'>账号信息</a></li>
                    <li class="li_02 curr">我的订单</li>
                    <li class="li_03"><a href="?app_act=mycenter/myself/receipt_info"/>发票信息</li>
                </ul>
            </div>
            <div class="content" style="display: block;">
                <ul class="tabs">
                    <li><a href='?app_act=mycenter/myself/order_info'>订购记录</a><i class="tabs_arrow"></i></li>
                    <li class="curr">授权信息<i class="tabs_arrow"></i></li>
                    <li><a href='?app_act=mycenter/myself/pay_desc'>支付说明</a><i class="tabs_arrow"></i></li>
                </ul>
                <div class="tabs_cont sqxx" style="display:block;">
                    <ul class="top">
                    	<li>产品：<strong><?php echo $response['data']['orderauthdetail']['cp_name'] ?></strong></li>
                        <li>版本：<strong><?php echo $response['data']['orderauthdetail']['pra_product_version_name'] ?></strong></li>
                    </ul>
                    <div class="details">
                    	<h1></h1>
                        <table class="cpsq">
                            <tr>
                                <td width='15%'>开始时间</td>
                                <td width='35%'><?php echo $response['data']['orderauthdetail']['pra_startdate'] ?></td>
                                <td width='15%'>结束时间</td>
                                <td width='35%'><?php echo $response['data']['orderauthdetail']['pra_enddate'] ?></td>
                            </tr>
                            <tr>
                                <td width='15%'>用户数</td>
                                <td><?php echo $response['data']['orderauthdetail']['pra_authnum'] ?></td>
                                <td width='15%'>店铺数</td>
                                <td><?php echo $response['data']['orderauthdetail']['pra_shopnum'] ?></td>
                            </tr>
                            <tr>
                                <td width='15%'>授权KEY</td>
                                <td><?php echo $response['data']['orderauthdetail']['pra_authkey'] ?></td>
                                <td width='15%'>备注</td>
                                <td><?php echo $response['data']['orderauthdetail']['pra_bz'] ?></td>
                            </tr>
                        </table>
                        <h1></h1>
                        <ul class="top">
                            <a class="ljdg" href="?app_act=mycenter/myself/orderauth_info">返回</a>
                        </ul>
                       </div> 
                </div>
            </div>
        </div>
    </div>
    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>
<script>

</script>    

