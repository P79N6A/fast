<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="roll stage">
    	<img src="assets/img/roll_pic01.jpg" width="100%" height="100%">        
        <div class="animate"><img class="bounceInLeft img_01" src="assets/img/roll_pic01_icon01.png" width="467" height="144"><img class="bounceInUp img_06" src="assets/img/roll_pic01_icon06.png" width="852" height="90"><a href="javascript:order_loginstate();"><img class="img_07" src="assets/img/roll_pic01_btn.png"  width="303" height="93"></a></div>
        <a class="index_link btn_down" index="1">
            <div class='sign signImg1' id='sign1_1'></div>
            <div class='sign signImg2' id='sign2_1'></div>
            <div class='sign signImg3' id='sign3_1'></div>
	</a>
    </div>
    <div class="order_proc stage">
    	<img src="assets/img/order_proc.jpg" width="100%" height="100%">
    	<div class="proc">
            <h3><img src="assets/img/proc_icon.png" width="26" height="40">在线订购</h3>
            <p class="proc_cont"><img src="assets/img/proc.png" width="925" height="472"><a class="a_01" href="javascript:void(0)">1</a><a class="a_02" href="javascript:void(0)">2</a><a class="a_03" href="javascript:void(0)">3</a><a class="a_04" href="javascript:void(0)">4</a><a class="a_05" href="javascript:void(0)">5</a></p>
        </div>
        <a class="index_link btn_down" index="2">
            <div class='sign signImg1' id='sign1_2'></div>
            <div class='sign signImg2' id='sign2_2'></div>
            <div class='sign signImg3' id='sign3_2'></div>
	</a>
    </div>
    <div class="product_prof stage">
    	<img src="assets/img/product_prof.jpg" width="100%" height="100%">
        <div class="profile">
            <h3><img src="assets/img/proc_icon.png" width="26" height="40">产品简介</h3>
            <div class="left">
            	<i class="icon"><img src="assets/img/profile_left_icon.png"></i>
                <span class="icon01"><img src="assets/img/profile_left_icon01.png"></span>
                <span class="icon02"><img src="assets/img/profile_left_icon02.png"></span>
                <span class="icon03"><img src="assets/img/profile_left_icon03.png"></span>
                <span class="icon04"><img src="assets/img/profile_left_icon04.png"></span>
                <span class="icon05"><img src="assets/img/profile_left_icon05.png"></span>
                <span class="icon06"><img src="assets/img/profile_left_icon06.png"></span>
                <span class="icon07"><img src="assets/img/profile_left_icon07.png"></span>
            </div>
            <div class="right">
            	<h2>eFAST 365</h2>
                <p class="text">eFAST365，专业的电商ERP，基于Saas化模式部署的互联网软件，应对跨行业，电商业务多变的特性，订购软件，在线付款，及时使用。有免安装、 免维护、 免运维、 云端更新、 随时随地、按需增减账号等优势。最大程度提升系统服务品质，降低信息化投入成本，让客户专注核心业务。</p>
                <p class="detail"><a href="?app_act=product/product/do_index">详情>></a></p>
                <p class="imme"><a href="javascript:order_loginstate();">立即订购</a></p>
            </div>
        </div>
        <div class="bottm">百胜官网：www.baison.com.cn&nbsp; &nbsp; &nbsp; &nbsp; 400-680-9510&nbsp; &nbsp; &nbsp; &nbsp; 地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</div>
    </div>
    <div id="guider">
		<a class="guider_link" id="guider_link1" href="javascript:void(0)">0</a>
		<a class="guider_link select" id="guider_link2" href="javascript:void(0)">1</a>
		<a class="guider_link" id="guider_link3" href="javascript:void(0)">2</a>
		<a class="guider_link" id="guider_link4" href="javascript:void(0)">3</a>				
	</div>    
</div>
<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('jQueryRotateCompressed.2.2.js',true);?>
<?php echo load_js('sucaijiayuan.js',true);?>
<?php echo load_js('login_reg.js',true);?>
<script>
    $('body').css("overflow","hidden");
</script> 

