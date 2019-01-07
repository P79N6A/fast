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
                    <li><a href='?app_act=mycenter/myself/order_info'>订购记录</a><i class="tabs_arrow"></i></li>
                    <li><a href='?app_act=mycenter/myself/orderauth_info'>授权信息</a><i class="tabs_arrow"></i></li>
                    <li class="curr">支付说明<i class="tabs_arrow"></i></li>
                </ul>
                <div class="tabs_cont zfsm" style="display: block;">
                    <p class="title">eFAST365现在仅支持线下支付。当提交完订单之后，您可以通过<a href="tencent://message/?uin=4006809510&Site=&menu=yes">在线客服</a>、400-680-9510电话、直接打款至下面银行账户或者来我们公司与我们取得联系，并且与公司直接签署订购合同，我们就会后台给您完成付款流程，并发送授权码给您。
                            </p>
                    <table class="details" style="margin-top: 3%;">
                        <tr>
                            <td width="35%">开户名称：</td>
                            <td>上海宝塔信息科技有限公司</td>
                        </tr>
                        <tr>
                            <td>开户行：</td>
                            <td>工行陆家嘴软件园支行</td>
                        </tr>
                        <tr>
                            <td>银行账号：</td>
                            <td>1001189709006869383</td>
                        </tr>
                        <tr>
                            <td>归属地：</td>
                            <td>上海</td>
                        </tr>
                    </table>
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

