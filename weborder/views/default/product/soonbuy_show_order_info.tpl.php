<div class="order_wrap">
    <?php include get_tpl_path('top') ?>
    <div class="firmorder_wrap">
        <div class="firmorder">
            <h3>请您确认订单信息</h3>
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
                    <td id="kh_code"><?php echo $response['data']['kh_code'] ?></td>
                    <td><?php echo $response['data']['kh_name']  ?></td>
                    <td><?php echo $response['data']['cpname']  ?></td>
                    <td class="cpbb"><?php echo $response['data']['pro_product_version'] ?></td>
                    <td><?php echo $response['data']['stname'] ?></td>
                    <td class="zyqx"><?php echo $response['data']['pro_hire_limit'] ?></td>
                    <td class="zyqx" id="pro_dot_num" ><?php echo $response['data']['pro_dot_num'] ?></td>
                    <td class="yfk" id="pro_price"><?php echo $response['data']['pro_price'] ?></td>
                    <input  type='hidden' id="p_version" value="<?php echo $response['data']['p_version'] ?>">
                    <input  type='hidden' id="p_limit" value="<?php echo $response['data']['p_limit'] ?>">
                    <input  type='hidden' id="p_st_id" value="<?php echo $response['data']['stid'] ?>">
                    <input  type='hidden' id="p_priceid" value="<?php echo $response['data']['priceid'] ?>">
                    <input  type='hidden' id="cpid" value="<?php echo $response['data']['cpid'] ?>">
                </tr>
            </table>
            <p class="btns">
                <button class="qrtj" id="right_submit_order" type="button">确认提交</button>
                <button class="cxxg" id="back_product" type="button">重新选购</button>
            </p>
        </div>
    </div>
    <div class="registered" id="registered">
    <img src="assets/img/onlysorry.png" width="30" height="30">Sorry，您的账号信息未审核，还不能订购。
    </div>

    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>


<script>
//确认提交执行js
    $("#right_submit_order").click(function() {
        $.ajax({type: "POST", dataType: 'json',
            url: "?app_act=product/soonbuy/submit_order_info",
            data: {pro_hire_limit: $("#p_limit").val(),
                pro_dot_num: $("#pro_dot_num").text(),
                p_version: $("#p_version").val(),
                pro_price: $("#pro_price").text(),
                p_priceid:$("#p_priceid").val(),
                p_st_id:$("#p_st_id").val(),
                cpid:$("#cpid").val(),
            },
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var sDJBH=ret.data;
                    location.href = '?app_act=product/soonbuy/success_order&djbh='+sDJBH;
                } else {
                   $("#registered").show();
                   setTimeout(function(){$("#registered").hide();},3000);
                }
            }
        });
    });

    $("#back_product").click(function() {
        location.href = '?app_act=product/soonorder/show_order';

    });



</script>
