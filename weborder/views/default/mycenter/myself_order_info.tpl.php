<style>
.dgjl .top{ position:relative;}
.dgjl .top .ljdg{ position:absolute; top:10px; right:12px; display:inline-block; padding:5px 10px; background:#f25216; color:#FFF;}
.dgjl .top .ljdg:hover{ background:#f86a37;}
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
                    <li class="curr">订购记录<i class="tabs_arrow"></i></li>
                    <li><a href='?app_act=mycenter/myself/orderauth_info'>授权信息</a><i class="tabs_arrow"></i></li>
                    <li><a href='?app_act=mycenter/myself/pay_desc'>支付说明</a><i class="tabs_arrow"></i></li>
                </ul>
                <div class="tabs_cont dgjl" style="display:block;">
                    <ul class="top">
                        <a class="ljdg" href="?app_act=product/soonorder/show_order">立即订购</a>
                    </ul>
                    <div class="details">
                    	<h3>产品订购记录</h3>
                        <table class="cpdg">
                            <tr>
                                <th>序号</th>
                                <th>订单号</th>
                                <th>产品</th>
                                <th>产品版本</th>
                                <th>用户数</th>
                                <th>租期（月）</th>
                                <th>付款状态</th>
                                <th>订购时间</th>
                                <th>审核状态</th>
                                <th>操作</th>
                            </tr>
                            <?php if(!empty($response['data']['orderinfo'])) {?>
                                <?php foreach ($response['data']['orderinfo'] as $i=>$orderinfo) { ?>
                                <tr>
                                    <td><?php echo $i+1;?></td>
                                    <td><?php echo $orderinfo['pro_num']; ?></td>
                                    <td><?php echo $orderinfo['cp_name']; ?></td>
                                    <td><?php echo $orderinfo['pro_product_version_name']; ?></td>
                                    <td><?php echo $orderinfo['pro_dot_num']; ?></td>
                                    <td><?php echo $orderinfo['pro_hire_limit']; ?>月</td>
                                    <?php if($orderinfo['pro_pay_status']=='1') {?>
                                        <td class="already">已付款</td>
                                    <?php } else {?>
                                        <td>未付款</td>
                                    <?php }?>
                                    <td><?php echo $orderinfo['pro_orderdate']; ?></td>
                                    <?php if($orderinfo['pro_check_status']=='1') {?>
                                    <td class="already">已审核</td>
                                    <?php }else if($orderinfo['pro_real_price'] == '面议') {?>
                                        <td>未审核(待改价)</td>
                                    <?php }else{?>
                                        <td>未审核</td>
                                    <?php } ?>
                                    <td class="operate_td">
                                        <a href="?app_act=mycenter/myself/orderdetail&djbh=<?php echo $orderinfo['pro_num']; ?>">详情</a><br>
                                        <?php if($orderinfo['pro_pay_status']!='1' && $orderinfo['pro_real_price'] != '面议'){?>
                                            <a href="javascript:payclick('<?php echo $orderinfo['pro_num']; ?>')">付款</a><br>
                                        <?php }?>
                                        <?php if($orderinfo['pra_state'] !='1') {?>
                                            <a href="?app_act=product/soonorder/show_order">续费</a><br>
                                        <?php }?>    
                                    </td>
                                </tr>
                                <?php }?>
                            <?php } else {?>
                                <tr><td colspan="10">暂无信息</td></tr>
                            <?php }?>
                        </table>
                        
                        <h3>增值订购记录</h3>
                        <table class="zzdg">
                            <tr>
                                <th>序号</th>
                                <th>订单号</th>
                                <th>租期（月）</th>
                                <th>增值项目</th>
                                <th>应付金额</th>
                                <th>付款状态</th>
                                <th>订购时间</th>
                                <th>审核状态</th>
                                <th>操作</th>
                            </tr>
                        </table>
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
/*$(function(){
    $(".tabs li").each(function(i) {
        $(this).click(function(){
            $(this).addClass("curr").siblings().removeClass("curr");
            $(".tabs_cont").hide().eq(i).show();
	});
    });
});*/
//确认付款执行js
function payclick(num){
    $.ajax({type: "POST",dataType: 'json',   
            url: "?app_act=product/soonbuy/pay_order",   
            data: {order_num: num},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success'){
                    alert('付款成功');
                    location.href ='?app_act=mycenter/myself/order_info';
                }else{
                    alert('订购失败');
                }
            }
    });
}
</script>    

