<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="aboutus">
    	<div class="segment section"><img src="assets/img/segment_01.jpg" width="100%" height="100%">
        	<div class="baison_intro">
            	<span class="animate bounceIn">animate</span>
                <p class="p_01 bounceInDown"><strong>上海百胜集团</strong><br>成就<span>智慧</span>品牌</p>
                <p class="p_02 bounceInUp">上海百胜集团拥有百胜软件、宝塔信息科技、星联科技、胜镜网络科技四大品牌公司。百胜软件定位为品牌企业全渠道零售解决方案服务商；<span>宝塔信息科技定位为互联网电商技术服务商；</span>星联科技定位为时尚行业提供销售终端解决方案服务商；胜镜网络科技定位为仓储管理提供解决方案服务商；四大品牌企业互相协作，成就百胜集团，创造非凡价值。</p>
            </div>
        </div>
        <div class="segment section" id="segment02"><img class="bg" src="assets/img/segment_02.jpg" width="100%" height="100%">
        	<div class="mask">遮罩层</div>
        	<div class="yishang_intro">
            	<h4><img src="assets/img/yishang.png" width="266" height="55"></h4>
                <p class="p_title">助力企业<span>快速</span>成长</p>
                <p class="p_text">上海宝塔信息科技有限公司属于百胜集团旗下子公司，2010年成立至今一直专注于互联网技术，以专业、稳定的电子商务管理系统为核心，拥有自主的研发团队；以创新、便捷的服务平台为纽带，构建了独具创新意识的在线服务体系。依托SaaS化的运营平台，以操作便捷、客户体验为宗旨，面向企业提供最高效的IT系统服务。</p>
                <p class="p_text">产品包括电商ERP、打单工具、CRM客户关系管理，同时也提供包括专业培训、聚石塔云服务、第三方平台对接、电商专项服务在内的增值服务和包括产品平台化运营、全面移动应用等多种创新服务。特别是公司自主研发的eFAST365产品，已成功为上千家企业提供专业的电子商务解决方案，特别是淘宝双十一各类目排名靠前客户，大多使用我们的产品，该产品稳定、高效、便捷广受各大企业的好评。</p>
            </div>
            <ul class="intro_bottom">
            	<li class="li_01"><i class="left">left</i><i class="right">Cooperation</i><p class="keyword"><strong>1</strong>合作</p></li>
                <li class="li_02"><i class="left">left</i><i class="right">Focus</i><p class="keyword"><strong>2</strong>专注</p></li>
                <li class="li_03"><i class="left">left</i><i class="right">Honesty</i><p class="keyword"><strong>3</strong>诚信</p></li>
                <li class="li_04"><i class="left">left</i><i class="right">Innovation</i><p class="keyword"><strong>4</strong>创新</p></li>
                <li class="li_05"><i class="left">left</i><i class="right">Passion</i><p class="keyword"><strong>5</strong>激情</p></li>
            </ul>
        </div>
        <div class="segment section" id="segment03"><img class="bg" src="assets/img/segment_03.jpg" width="100%" height="100%">
        	<div class="mask">遮罩层</div>
        	<div class="contactus_wrap">
            	<div class="contactus">
                	<h3>上海宝塔信息科技有限公司</h3>
                    <ul>
                    	<li class="li_01">地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼</li>
                        <li class="li_02">电话：400-680-9510</li>
                        <li class="li_03">QQ：400-680-9510</li>
                        <li class="li_04">邮件：bt@baisonmail.com</li>
                        <li class="li_05">官网：www.baison.com.cn</li>
                    </ul>
                </div>
            </div>
            <div class="footer">
            	<span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span>
            </div>
        </div>
    </div>   
</div>
<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('jQueryRotateCompressed.2.2.js',true);?>
<?php echo load_js('jquery.fullPage.js',true);?>
<?php echo load_js('login_reg.js',true);?>
<?php echo load_css('jquery.fullPage.css',true);?>

<script src="js/jquery.fullPage.js"></script>
<script>
$(function(){
    $('.aboutus').fullpage({
		afterLoad: function(anchorLink, index){
			if(index == 1){
				$('.baison_intro').find('.animate').addClass('bounceIn')
				$('.baison_intro').find('.p_01').addClass('bounceInDown')
				$('.baison_intro').find('.p_02').addClass('bounceInUp')
			}
			if(index == 2){
				$('#segment02').find('.mask').fadeIn(1000)
				$('#segment02').find('.yishang_intro').addClass('bounceInDown')
				$('#segment02').find('.intro_bottom').addClass('bounceInUp')
				
			}
			if(index == 3){
				$('#segment03').find('.mask').fadeIn(1000)
				$('#segment03').find('.contactus_wrap').addClass('bounceInLeft')
				$('#segment03').find('.footer').addClass('bounceInRight')
			}
		},
		onLeave: function(index, direction){
			if(index == '1'){
				$('.baison_intro').find('.animate').removeClass('bounceIn')
				$('.baison_intro').find('.p_01').removeClass('bounceInDown')
				$('.baison_intro').find('.p_02').removeClass('bounceInUp')
			}
			if(index == '2'){
				$('#segment02').find('.mask').fadeOut(1500)
				$('#segment02').find('.yishang_intro').removeClass('bounceInDown')
				$('#segment02').find('.intro_bottom').removeClass('bounceInUp')
			}
			if(index == '3'){
				$('#segment03').find('.mask').fadeOut(1500)
				$('#segment03').find('.contactus_wrap').removeClass('bounceInLeft')
				$('#segment03').find('.footer').removeClass('bounceInRight')
			}
		}	
	});
	
});
</script>

