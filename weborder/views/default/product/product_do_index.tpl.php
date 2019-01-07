<?php echo load_js('jquery.touchSlider.js',true);?>
<style>
.order_wrap .roll{ position:relative; overflow:hidden;}
.order_wrap .roll .main_visual{height:560px;overflow:hidden;position:relative;}
.order_wrap .roll .main_image{height:560px;overflow:hidden;position:relative;}
.order_wrap .roll .main_image ul{width:9999px;height:560px;overflow:hidden;position:absolute;top:0;left:0}
.order_wrap .roll .main_image li{float:left;width:100%;height:560px;}
.order_wrap .roll .main_image li span{display:block;width:100%;height:560px;}
.order_wrap .roll .main_image li a{display:block;width:100%;height:560px;}
.order_wrap .roll .flicking_con{ position:absolute; left:50%; bottom:3%; width:500px; margin-left:-250px; text-align:center;}
.order_wrap .roll .flicking_con a{ display:inline-block; width:66px; height:12px; margin:0 8px; background:url(assets/img/roll_num_bg.png) no-repeat; overflow:hidden; text-indent:-100px;}
.order_wrap .roll .flicking_con a.on{ background-position:right top;}
</style>
<div class="order_wrap">
    <?php include get_tpl_path('top')?>
    <div class="roll">
    	<div class="main_visual">
            <div class="main_image">
            	<ul>
                  <li><a href="javascript:void(0)"><span class="img_11"></span></a></li>
<!--                  <li><a href="javascript:void(0)"><span class="img_12"></span></a></li>
                  <li><a href="javascript:void(0)"><span class="img_13"></span></a></li>-->
                </ul>
                <a href="javascript:void(0);" id="btn_prev"></a> <a href="javascript:void(0);" id="btn_next"></a> 
             </div> 
            <!--<p class="flicking_con"><a class="on" href="javascript:void(0)">1</a><a href="javascript:void(0)">2</a><a href="javascript:void(0)">3</a></p>-->
        </div>
    </div>
    <div class="products_intro">
    	<div class="efast_erp">
            <h1>eFAST 365</h1>
            <p>做最高效的电商ERP</p>
            <button class="ljdg" type="button" onclick="order_loginstate();">立即订购</button>
        </div>
        <div class="hxgn">
          <div class="hxgn_cont">
            <h3>eFAST365五大核心功能</h3>
            <div class="gnjs"> <a href="javascript:void(0)" class="gnjs_on">采购管理</a> <a href="javascript:void(0)">订单管理</a> <a href="javascript:void(0)">仓储管理</a> <a href="javascript:void(0)">报表体系</a> <a href="javascript:void(0)">策略管理</a> </div>
            <div class="gnjs_box" style="display:block;">
              <p><span>供应商管理：</span><br>进行供应商分级管理，针对供应商的供货及时率、退换率等，通过多维度进行供应商的评级，优选供应商，保证供货的及时性</p>
              <p><span>采购管理：</span><br>采购订单可直接生成入库单，一张采购订单可根据到货时间不同分批入库，对采购过程中的采购数与到货数进行实时跟踪，缺货订单可根据供应商直接生成相应采购订单</p>
              <p><span>智能采购：</span><br>根据商品销售畅滞销情况并结合商品补货周期，进行智能补货，保证销售的连续性和库存的最小化</p>
            </div>
            <div class="gnjs_box">
              <b>订单处理遵循原则：自动化、智能化、可追朔、可配置，精细化。</b>
              <p><span>订单分类处理：</span>对于不同来源渠道的订单，进行智能分类，并根据系统设定的筛选参数，进行订单自动处理。正常订单无需人工干预，直接进入仓库发货；金额异常、黑名单客户订单进行提醒标识；留言、加单、延迟发货订单进行挂起操作</p>
              <p><span>订单合并拆分：</span>支持现货与预售的销售模式，可对同一收货人和收货地址订单进行合并；对于缺货、现货与预售混合的订单可进行拆分发货</p>
              <p><span>实时跟踪订单处理状态：</span>可以实时查看订单的处理状况，方便客服人员回答客户的查件需求，同时记录操作人员的实时操作情况，方便订单出现问题时进行追溯</p>
            </div>
            <div class="gnjs_box">
            <p><span>管理功能：</span>入库操作、订单打印、拣货、复核打包、出库、盘点、信息跟踪、绩效考核</p>
<p><span>精确定位：</span>专门针对电子商务业务的小批量、多批次的配发货方式，减少人工干预，支持摘果、播种等拣货方式，并通过货架货位结合先进的电子标签技术精确定位商品的位置，提高拣货效率和减少出错率</p>
<p><span>条码配拣货：</span>系统通过全程条码扫描配发货功能，可以根据订单所选的配送方式、区域等对订单进行分类处理，并对发货商品进行统一配货，统一打印配货单、发货单、物流单。同时对所配货物进行条码验证，降低出错率</p>
<p><span>库存同步：</span>多仓多店库存管理，一键同步店铺库存到前端销售平台</p></div>
            <div class="gnjs_box">
            	<b>可灵活自定义报表展现形式，可保存个性化报表模板，展示内容可拖可拽，按需展示。</b>

<p><span>销售报表：</span>商品销售排行分析、销售周报、销售月报、销售时段分析、分类销售分析等</p>
<p><span>库存报表：</span>商品实时库存分析、品类销存统计、仓库调整分析、商品移仓分析等</p>
<p><span>财务报表：</span>快递费用对账分析、应收应付账分析、销售毛利表等</p>
<p><span>发货报表：</span>零售销货分析、订单发货统计分析、商品发货统计分析、商店退货分析等</p>
            </div>
            <div class="gnjs_box">
            <p><span>单合并策略：</span>店铺ID、购买人ID、收货人姓名、地址相同满足条件订单自动合并，减少快递费用</p>
<p><span>快递策略：</span>优化快递费用，提高客户收货速度</p>
<p><span>仓库策略：</span>多仓发货策略，提高多仓利用，提高发货速度，节省运费以及提高客户收货速度</p>
<p><span>订单免审策略：</span>满足条件订单自动下发仓储进行配发货，提高审单效率。对日常业务以及大促业务有非常明显地效率提升</p> 
<p><span>赠品策略：</span>满足条件的订单自动增加赠品，支持不涉及金额的促销，提高客户体验减少二次邮费</p>
<p><span>库存分配策略：</span>提高库存准确率，减少库存超卖。支持共享、比例库存的同步方式。可全局设定支持单个商品</p>
            </div>
          </div>
        </div>
        <div class="cptd">
        	<img src="assets/img/cptd.png" width="892" height="175">
        </div>
        <div class="khjz">
          <div class="khjz_cont">
            <h3>eFAST365三大客户价值</h3>
            <div class="jzjs">
              <h4>价值1：标准化电商开放平台，轻松应对电商生态圈变化</h4>
              <p>宝塔eFAST365软件内置了十余个市场主流平台接口，特别针对订单业务、货到付款业务、仓储物流进行了特别的优化处理；并且对顺风、EMS等快递公司的电子面单业务也有完善的支持。产品提供标准化的开放接口，供技术人员调用进行快速开发，保障企业快速实现新业务功能，轻松应对电商生态圈的变化。</p>
            </div>
            <div class="jzjs">
              <h4>价值2：支持全网全程业务，紧密整合企业电商内部业务流</h4>
              <p>宝塔eFAST365软件整合了客服、仓储、商品、运营、采购、渠道、营销等各个部门管理要求，同时可与SAP/Oracle/用友/金蝶等常见企业ERP进行对接，消除企业内部“信息孤岛”。不仅紧密整合了电商企业的内部流程，也能与线下业务进行了对接，从而为进一步的线上线下整合打下了牢靠的基础。</p>
            </div>
            <div class="jzjs">
              <h4>价值3：顺应大数据时代，助理电商实现精细化管理</h4>
              <p>宝塔eFAST365软件基于先进的技术架构，可以支持电子商务与日俱增的庞大数据量，在稳定性能的基础上，承载更多的电商业务数据。并且通过采购、库存、订单、分销、财务、会员等各个环节的精细化管控点，实现企业对整个电子商务的精细化管理。</p>
            </div>
          </div>
        </div>
        <p class="ljdg_wrap">
        	<button class="ljdg" type="button" id="right_order" onclick="order_loginstate();">立即订购</button>
        </p>
    </div>
    <div class="order_bottom">
    	<p><span>百胜官网：www.baison.com.cn</span><span>400-680-9510</span><span>地址：上海市浦东新区峨山路91弄100号陆家嘴软件园2号楼5楼（200127）</span></p>
    </div>
</div>
<?php include get_tpl_path('login');?>
<?php include get_tpl_path('register');?>
<?php echo load_js('login_reg.js',true);?> 
<script>
$(function(){
	$(".gnjs a").each(function(i){
		$(this).click(function(){
			$(".gnjs a").removeClass("gnjs_on").eq(i).addClass("gnjs_on");
			$(".hxgn_cont .gnjs_box").hide().eq(i).show();
			})
		})
	})
        
        
        
        
        
</script>
