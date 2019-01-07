<style>
.sqxx .top{ position:relative;}
.sqxx .top .ljdg{ position:absolute; top:10px; right:12px; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.sqxx .top .ljdg1{position:absolute; top:10px; right:5%; display:inline-block;padding:5px 10px; background:#f25216; color:#FFF;}
.sqxx .top .ljdg:hover{ background:#f86a37;}
.show_mask{
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: #000;
    opacity:0.4;
    filter:alpha(opacity=40);
    z-index: 99999;
}

.show_auth{
    width: 470px;
    height: 220px;
}

.show_renew, .show_result{
    width: 470px;
    height: 160px;
}

.show_auth, .show_renew, .show_result{
    position: fixed;
    top:34%;
    left: 35%;
    background-color: white;
    z-index: 999999;
    border:1px solid #f25216;
    border-radius: 10px;
    padding-top:10px; 
}
.show_auth_d lable{
    margin: 10px 0 0 10px;
}
.show_renew_d .msg{
    text-align: center;
    margin: 30px 0 30px 0;
}
.show_auth_d #auth_key{
    margin: 10% 0 10% 0;
    text-align: center;
}
.show_auth_d,.show_renew_d .show_btn{
    text-align: center;
}
button{
        width: 79px;
        height: 33px;
        border: 3px solid #e95513;
        text-align: center;
        font-size: 15px;
        margin-right: 2px;
        cursor: pointer;
        background: #e95513;
        color: #FFF;
    }
button:hover{
        background:#f25c1e;
        border-color:#ee571b;
        color:#eee;
    }
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
                    <li><a href='?app_act=mycenter/myself/order_info'/>订购记录</a><i class="tabs_arrow"></i></li>
                    <li class="curr">授权信息<i class="tabs_arrow"></i></li>
                    <li><a href='?app_act=mycenter/myself/pay_desc'>支付说明</a><i class="tabs_arrow"></i></li>
                </ul>
                <div class="tabs_cont sqxx" style="display:block;">
                    <ul class="top">
                        <?php if(!empty($response['data']['authinfo'])){?><a class="ljdg1" href="<?php if($response['pra_kh_status'] == 1) { echo $response['pra_serverpath'];}else{ echo '?app_act=mycenter/myself/init_login';}; ?>" target="_blank">登录系统</a><?php }?>
                    </ul>
                    <div class="details">
                    	<h3>产品授权记录</h3>
                        <table class="cpsq">
                            <tr>
                                <th>序号</th>
                                <th>产品</th>
                                <th>产品版本</th>
                                <th>授权点数</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>授权状态</th>
                                <th>操作</th>
                            </tr>
                            <?php if(!empty($response['data']['authinfo'])) {?>
                                <?php foreach ($response['data']['authinfo'] as $i=>$authinfo) { ?>
                                <tr>
                                    <td><?php echo $i+1;?></td>
                                    <td><?php echo $authinfo['cp_name']; ?></td>
                                    <td><?php echo $authinfo['pra_product_version_name']; ?></td>
                                    <td><?php echo $authinfo['pra_authnum']?></td>
                                    <td id="pra_startdate"><?php echo $authinfo['pra_startdate']; ?></td>
                                    <td id="pra_enddate"><?php echo $authinfo['pra_enddate']; ?></td>
                                    <?php if($authinfo['pra_state']=='1') {?>
                                        <td class="already">√</td>
                                    <?php } else {?>
                                        <td>×</td>
                                    <?php }?>
                                    <td class="operate_td">
                                        <a onclick="check_auth('<?php echo $authinfo['pra_id']; ?>')">查看授权码</a><br>
                                        <?php if($response['is_notice']=='1') {?>
                                        <a onclick="renew('<?php echo $authinfo['pra_id']; ?>')" id="renew_btn">续费</a><br>
                                        <?php }?>
                                    </td>
                                </tr>
                                <?php }?>
                            <?php } else {?>
                                <tr><td colspan="9">暂无信息</td></tr>
                            <?php }?>
                        </table>
                        
                        <h3>增值授权记录</h3>
                        <table class="zzsq">
                            <tr>
                                <th>序号</th>
                                <th>产品名称</th>
                                <th>产品版本</th>
                                <th>增值服务</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>授权状态</th>
                                <th>操作</th>
                            </tr>
                        </table>
                       </div> 
                </div>
            </div>
        </div>
    </div>
    <div class="show_mask" style="display:none"></div>
    <div class="show_auth" style="display:none">
        <div class="show_auth_d">
            <lable>产品授权码</lable>
            <p id="auth_key"></p>
            <div class="show_btn">
<!--                <button onclick="copyText(document.all.auth_key)">复制</button>-->
                <button class="close_btn">关闭</button>
            </div>
        </div>
    </div>
    <div class="show_renew" style="display:none">
        <div class="show_renew_d">
            <div class="msg">您确定续费了吗？</div>
            <div class="show_btn">
                <button onclick="check_renew_status('<?php echo $authinfo['pra_id']; ?>')">是</button>
                <button class="close_btn">否</button>
            </div>
        </div>
    </div>
    <div class="show_result" style="display:none">
        <div class="show_renew_d">
            <div class="msg">对不起，没有查询到您的续费记录，请核实续费情况</div>
            <div class="show_btn">
                <button class="close_btn">关闭</button>
            </div>
        </div>
    </div>
    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>  

<script>
    function check_auth(pra_id){
        if (pra_id != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('mycenter/myself/get_auth_key'); ?>',
                data: {pra_id: pra_id},
                success: function (ret) {
                    var type = ret.status === 1 ? 'success' : 'error';
                    if (type === 'success') {
                        auth_key = ret.data.pra_authkey;
                        $("#auth_key").html(auth_key);
                        $(".show_mask").show();
                        $(".show_auth").show();
                    }
                }
            });
        }
    }
    
    function renew(pra_id){
        $(".show_mask").show();
        $(".show_renew").show();
         if (pra_id != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('product/soonbuy/renew_product'); ?>',
                data: {pra_id: pra_id},
                success: function (ret) {
                    window.open(ret.data);
                }
            });
        }
    }
    
    function check_renew_status(pra_id){
        if (pra_id != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('product/soonbuy/check_renew'); ?>',
                data: {pra_id: pra_id},
                success: function (ret) {
                    var type = ret.status === 1 ? 'success' : 'error';
                    if (type === 'success') {
                        $("#renew_btn").hide();
                        $("#pra_startdate").html(ret.data.pra_startdate);
                        $("#pra_enddate").html(ret.data.pra_enddate);
                        $(".close_btn").click();
                    }else{
                        $(".show_renew").hide();
                        $(".show_result").show();
                    }
                }
            });
        }
    }
    
    function copyText(obj){
        try{
            var rng = document.body.createTextRange();
            rng.moveToElementText(obj);
            rng.scrollIntoView();
            rng.select();
            rng.execCommand("Copy");
            rng.collapse(false);
            alert("已经复制到粘贴板!你可以使用Ctrl+V 贴到需要的地方去了哦!");
        }catch(e){
            alert("此复制功能仅支持IE浏览器，您的浏览器不支持，请选中相应内容并使用Ctrl+C进行复制!");
        }
    }
    $(".close_btn").click(function(){
       $(".show_mask").hide();
       $(".show_auth, .show_renew, .show_result").hide();
    });
</script>