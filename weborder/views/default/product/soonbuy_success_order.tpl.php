<div class="order_wrap">
    <?php include get_tpl_path('top')?>

      <div class="payment_wrap">
  	<div class="payment">
            <?php if($response['data']['from'] !='detail'){?>
    	<h3><img src="assets/img/registered.png" width="30" height="30">
            <?php if($response["data"]["pro_product_version"] == 1 && $response["data"]["pro_price_id"] == 0){ 
                echo "订单已经提交成功，请您尽快付款。";
            }else{  
                echo "订单已经提交成功，请等待管理员改价。";
            } ?>
        </h3>
            <?php }else{?>
            <div style="margin-bottom: 3%"></div>
            <?php }?>
        <p class="ddh_01">订单号：<span class="span_01"><?php echo $response['data']['pro_num'] ?></span><span class="span_02">￥<?php echo $response['data']['pro_real_price'] ?></span></p>
        <!--p class="ddxq_02"><a class="drop" href="javascript:void(0)">订单详情</a></p-->
        <table class="ddxq">
            	<caption>订单详情</caption>
                <tr>
                    <th>用户名</th>
                    <th>公司名称</th>
                    <th>产品名称</th>
                    <th>产品版本</th>
                    <th>购买类型</th>
                    <th>租用期限</th>
                    <th>点数</th>
                    <th>应付款</th>
                </tr>
                <tr>
                    <td><?php echo $response['data']['kh_code'] ?></td>
                    <td><?php echo $response['data']['kh_name'] ?></td>
                    <td><?php echo $response['data']['cp_name'] ?></td>
                    <td class="cpbb"><?php echo $response['data']['pro_product_version_name'] ?></td>
                    <td><?php echo $response['data']['st_name'] ?></td>
                    <td class="zyqx"><?php echo $response['data']['pro_hire_limit'] ?>个月</td>
                    <td class="zyqx"><?php echo $response['data']['pro_dot_num'] ?></td>
                    <td class="yfk">￥<?php echo $response['data']['pro_real_price'] ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        <p class="dividing">分割线</p>
        <p class="btns">
            <button class="btn_01" type="button" onclick="location.href ='?app_act=mycenter/myself/self_info';">查看个人中心</button>
            <?php if($response['data']['pro_real_price'] != '面议'){ echo '<button class="btn_02" type="button" onclick="payclick();">线下支付</button>';} ?>
        </p>
        <table class="details" style="margin-top: 3%;margin-left: 38%;">
            <tr>
                <td colspan="2" style="padding-left: 30%">线下支付方式</td>
            </tr>
            <tr><td style="padding-top: 3%"></td></tr>
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
    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>


<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('login_reg.js',true);?> 

<script>
	$(".ddxq_02 .drop").click(function(){
            $(".payment .ddxq").toggle();
	});
         
        var djbh="<?php echo $response['data']['pro_num'];?>";
        //确认付款执行js
        function payclick(){
            alert("您确定已经完成支付了吗？")
            $.ajax({type: "POST",dataType: 'json',   
                    url: "?app_act=product/soonbuy/pay_order",   
                    data: {order_num: djbh},
                    success: function(ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success'){
                            alert('付款成功');
                            location.href ='?app_act=mycenter/myself/orderdetail&djbh='+djbh;
                        }else{
                            alert('订购失败');
                        }
                    }
            });
        }
</script>

