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
                        <li>产品：<strong><?php echo $response['data']['orderdetail']['cp_name'] ?></strong></li>
                        <li>版本：<strong><?php echo $response['data']['orderdetail']['pro_product_version_name'] ?></strong></li>
                        <?php if($response['data']['orderdetail']['pro_pay_status']!='1' && $response['data']['orderdetail']['pro_real_price'] != '面议') {?>
                        <a class="ljdg" href="?app_act=product/soonbuy/success_order&djbh=<?php echo $response['data']['orderdetail']['pro_num'] ?>&from=detail">立即支付</a>
                        <?php }?>
                    </ul>
                    <div class="details">
                        <h1></h1>
                        <table class="cpdg">
                            <tr>
                                <td width='15%'>订购编号</td>
                                <td width='35%'><?php echo $response['data']['orderdetail']['pro_num'] ?></td>
                                <td width='15%'>订购日期</td>
                                <td width='35%'><?php echo $response['data']['orderdetail']['pro_orderdate'] ?></td>
                            </tr>
                            <tr>
                                <td>订购类型</td>
                                <td><?php echo $response['data']['orderdetail']['st_name'] ?></td>
                                <td>报价类型</td>
                                <td><?php echo $response['data']['orderdetail']['price_name'] ?></td>
                            </tr>
                            <tr>
                                <td>标准售价</td>
                                <td>￥<?php echo $response['data']['orderdetail']['pro_sell_price'] ?></td>
                                <td>优惠</td>
                                <td>￥<?php echo $response['data']['orderdetail']['pro_rebate_price'] ?></td>
                            </tr>
                            <tr>
                                <td>实际价格</td>
                                <td>￥<?php echo $response['data']['orderdetail']['pro_real_price'] ?></td>
                                <td>租用期限(月)</td>
                                <td><?php echo $response['data']['orderdetail']['pro_hire_limit'] ?></td>
                            </tr>
                            <tr>
                                <td>付款状态</td>
                                <?php if($response['data']['orderdetail']['pro_pay_status']=='1') {?>
                                    <td class="already">已付款</td>
                                <?php } else {?>
                                    <td>未付款</td>
                                <?php }?>
                                <td>付款日期</td>
                                <td><?php echo $response['data']['orderdetail']['pro_paydate'] ?></td>
                            </tr>
                            <tr>
                                <td>审核状态</td>
                                <?php if($response['data']['orderdetail']['pro_check_status']=='1') {?>
                                    <td class="already">已审核</td>
                                <?php } else {?>
                                    <td>未审核</td>
                                <?php }?>
                                <td>审核日期</td>
                                <td><?php echo $response['data']['orderdetail']['pro_checkdate'] ?></td>
                            </tr>
                            <tr>
                                <td>用户数</td>
                                <td><?php echo $response['data']['orderdetail']['pro_dot_num'] ?></td>
                                <td>备注</td>
                                <td><?php echo $response['data']['orderdetail']['pro_desc'] ?></td>
                            </tr>
                        </table>
                         <h1></h1>
                        <ul class="top">
                            <a class="ljdg" href="?app_act=mycenter/myself/order_info">返回</a>
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

