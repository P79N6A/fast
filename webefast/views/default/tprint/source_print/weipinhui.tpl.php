<?php include get_tpl_path('web_page_top'); ?>
<span id="percent" style="display:none;">96%</span>
<span id="tpl_height" style="display:none;">297mm</span>
		<div style = "margin-top:30px;" >
		    <div class="abox">
				<div class="logo"><img width="800px" src="assets/images/vip/hd.png"></div>
				<div style = "">
				<div class="bd">
					<div class="tit">
						<div class="right">
							<p class="code">
							<img  class="barcode" width="189" height="67" src="assets/tprint/picon/barcode.png" title="<?php echo $response['body']['record']['deal_code_list'];?>"/>
							<!--<img src="http://vis.vip.com/barCode.php?text=16011380755129" alt="http://vis.vip.com/barCode.php?text=16011380755129">-->
							</p>
						</div>
						<div class="receiver">
							<p>收货人：<?php echo $response['body']['record']['receiver_name'];?>&nbsp;&nbsp;&nbsp;&nbsp;联系电话：<i class = "num"><?php echo $response['body']['record']['receiver_mobile'];?></i></p>
							<p>收货地址：<?php echo $response['body']['record']['receiver_address'];?></p>
						</div>
					</div>
					<div class="list">
						<table>
							<thead>
								<tr>
									<th>商品名称</th>
									<th>商品编号</th>
									<th>规格</th>
									<th>数量</th>
									<th>单价</th>
									<th>总价</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($response['body']['detail'] as $goods) {?>
								<tr>
									<td><?php echo $goods['goods_name'];?></td>
									<td><?php echo $goods['goods_code'];?></td>
									<td><?php echo $goods['spec1_name'].' '.$goods['spec2_name'];?></td>
									<td><?php echo $goods['num'];?></td>
									<td><?php echo $goods['goods_price'];?></td>
									<td><?php echo $goods['avg_money'];?></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
					</div>
					<div class="add">
						<!--                            <p class="time">打印时间：</p>-->
						<div class="all" >
							<p>商品总数量：<?php echo $response['body']['record']['goods_num'];?></p>
							<p>商品总金额：<i class = "num"><?php echo $response['body']['record']['goods_money'];?></i></p>
						</div>
							<div style="width:80%">
						    <p>寄回地址：<?php echo $response['body']['record']['sender_address'];?></p>
                                                    <p>邮编：<i class = "num"><?php echo $response['body']['record']['sender_zip'];?></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="name">收件人：</span><i class = "num"><?php echo $response['body']['record']['shop_contact_person'];?></i>&nbsp;&nbsp;&nbsp;&nbsp;电话/手机：<i class = "num"><?php if(empty($response['body']['record']['sender_mobile'])) {echo $response['body']['record']['sender_phone'];} else {echo $response['body']['record']['sender_mobile'];}?></i></p>
						</div>
					</div> 
						
				</div>
				</div>
				<div style = "">
				<div class="ft" style = "">
					<p class="red">*返回商品时，请务必把此送货单置入包裹中一并寄回，否则将无法为您办理退货，敬请谅解。</p>
					<p>温馨提示：唯品会官方主办的一切赠品、奖品活动，以唯品会网站上公布为准，如会员得奖，所有赠品、奖品都是免费派送的，如遇可疑来电请及时致电唯品会客服热线<i class = "num">400-6789-888</i>咨询。</p>
					<p class="pic"><img width="800px" src="assets/images/vip/ft.png"></p>
				</div>
				</div>
			</div>
			<div class="cbox">
				<div class="hd"><img width="800px" src="assets/images/vip/hd2.png"></div>
				<div style = "">
				<div class="bd">
					<div class="section">
						<h3 class="m0">亲爱的会员</h3>
						<p>您好！精挑细选的商品终于到货啦，它们如您所愿吗？如果此时您脸上充满了笑容，迫不及待地分享战果，便是对我们的最大肯定。但如果由于种种原因，您最终决定要放弃它们，我们将为您提供完善的售后服务。</p>
					</div>
					<div class="section">
						<h3>7天无理由退货说明</h3>
						<div class="wrap">
							<div class="right"><img src="assets/images/vip/code.png"><br><p>手机扫描二维码查看退货说明</p></div>
							<div style="width:82%">
								<p><img class="bg" src="assets/images/vip/1.png">先在线申请退货，待审核通过后寄回；<img class="bg" src="assets/images/vip/2.png">签收后7天内寄出；<img class="bg" src="assets/images/vip/3.png">选择普通快递，请勿使用到付、平邮；<img class="bg" src="assets/images/vip/4.png">预付快递费用，回寄运费以10元礼品卡形式在退款时统一补贴；<img class="bg" src="assets/images/vip/5.png">退货商品不得影响二次销售，单据、配件、商品条码、吊牌、包装等必须完好、齐全，否则不予办理退款；<img class="bg" src="assets/images/vip/6.png">在退货流程完成后，根据您退换的具体商品的积分原则，在您的账户积分将会相应变动。<span class="more">更多无条件退货说明，请参考网站帮助中心。</span></p>
							</div>
						</div>
						<div style="width:70%;padding-bottom:3px;margin-top:10px">
							<h3>自助退货流程</h3> 
						</div>
						<p class="auto">
							<span class="text">进入“我的账户”点击“自助<br>退货”申请<i class="red">或</i>通过手机客户端<br>进入“订单管理”申请</span>
							<span class="icon"><img src="assets/images/vip/icon.png"></span>
							<span class="text h60">退货审核通过</span>
							<span class="icon"><img src="assets/images/vip/icon.png"></span>
							<span class="text">寄回商品(包括所有<br>单据、赠品、包装)</span>
							<span class="icon"><img src="assets/images/vip/icon.png"></span>
							<span class="text">商品经供应商验收通过</span>
							<span class="icon"><img src="assets/images/vip/icon.png"></span>
							<span class="text">唯品会办理退款和<br>运费补贴（礼品卡）</span>
						</p>
					</div>
					<ul class="card">
						<li class="com">
							<p class="insurance"><img src="assets/images/vip/com.png"></p>
							<p>保险公司名称：中国太平洋财产保险股份有限公司</p>
							<p>网址：www.cpic.com.cn</p>
							<p>保单号码：AGUZA0039112B000003Q</p>
							<p>被保险人：广州唯品会信息科技有限公司</p>
							<p>受 益 人：商品收货人</p>
							<p>全国统一理赔热线：<i class = "num">020-95500</i></p>
						</li>
						<li>
							<h4><img src="assets/images/vip/tt1.png"></h4>
							<p>1.理赔时效：收到货物的90天内</p>
							<p>2.理赔时须提供网上所购货物及送货单</p>
							<p>3.理赔时须提供工商局或有资质的机构出具的鉴定报告</p>
							<p>4.收货人的身份证明文件</p>
							<p><b>产品编号</b></p>
							<p>请参考送货单上的商品编号，并请妥善保管送货单</p>
							<p class="more">本卡只作保险项目说明，具体事宜以保险合同为准，详情请登录<br>www.vip.com查询</p>
						</li>
						<li>
							<h4><img src="assets/images/vip/tt2.png"></h4>
							<p class="words">在唯品会www.vip.com上售卖的品牌均为名牌正品，如经工商局或有资质的机构进行产品质量鉴定，鉴定结果为非品牌正品的，可在理赔时效内，凭鉴定报告和保险卡号依法向太平洋保险索取该商品售价的全额赔偿。另请及时通知唯品会，我们将协助您进行全面的查证调研。</p>						   
							<p class="more">希望在给您带来愉悦购物体验的同时，也能保障您的权益，所以请您妥善保管您的保险卡，以备索赔。</p>
						</li>    
					</ul>
				</div>
				</div>
			</div>
		</div>
	<style type="text/css">
            body,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,form,fieldset,legend,input,button,textarea,p,th,td{margin:0 auto; padding:0;background-color:#fff;text-align:left;}
            body{font-size:12px; font-family:'Times New Roman'; background:#fff; color:#000;}
            .abox {width:800px;margin:0 auto;overflow:hidden;}
            .abox .logo {height:84px;}
            .bd {border:solid #d50080;border-width:0 31px;padding:5px 19px 3px;margin-top:-4px;position:relative;z-index:10;}
            .tit {height:65px;text-align:left;}
            .tit .right {float:right;width:189px;text-align:center;margin:-3px -10px 0 0;}
            .tit .right .code {height:65px;line-height:65px;}
            .tit .right .num {font-family:Arial; font-size:14px;}
            .tit .receiver {width:500px;margin-top:-8px;}
            .tit .receiver p {padding-top:5px;}//line-height:18px;
            .tit .receiver i {font-style:normal;display:inline-block;text-align:right;margin-right:5px;}//width:60px;
            .tit .receiver .name {margin-right:20px;min-width:120px;display:inline-block;}
            .list {width:700px;margin:4px 0 10px;}
            .list table {border-collapse:collapse;border-spacing:0;width:100%;font-family:simsun;}
            .list table th, .list table td {border:1px solid #6d6d6d;text-align:center;padding:0 8px;line-height:16px;}
            .add .all {float:right;line-height:20px;font-weight:bold;margin:-4px 0 0 20px;}
            .add .time {padding-top:5px;}
            .ft p {border:solid #d50080;border-width:0 31px;padding:0 20px;}
            .ft .pic {padding:0;border:none;}
            .ft .red {font-size:11px;color:#d70280;padding:2px 20px 1px;}
            .ft .address {font-size:12px;padding:0px 20px;text-align:left!important;margin-top:-6px;line-height:15px; }
            .cbox {width:800px;margin:5px auto 0;overflow:hidden;}
            .cbox .hd {height:55px;}
            .cbox .bd {border:solid #d50080;border-width:0 31px 20px;margin-top: -0.1px;}
            .cbox .section {padding-bottom:7px;border-bottom:1px dashed #d70f87;line-height:18px;}
            .cbox .section h3 {color:#d50080;font-size:16px;padding:7px 0 5px;}
            .cbox .section .m0 {padding-top:0;margin-top:-2px;}
            .cbox .section .bg {margin:0 2px;vertical-align:top;}
            .cbox .section i {font-style:normal;color:#d70280;}
            .cbox .section .auto {height:54px;overflow:hidden;margin-top:2px;}
            .cbox .section .auto span {display:inline-block;vertical-align:middle;}
            .cbox .section .text {line-height:18px;}
            .cbox .section .h60 {line-height:54px;}
            .cbox .section .icon {display:inline-block;height:20px;width:20px;margin:0 2px;}
            .cbox .section .right {float:right;width:156px;text-align:center;margin:-30px -12px;}
            .cbox .card {margin:10px 0;border:1px dashed #d70f87;padding:5px 0 0 15px;height:228px; }
            .cbox .card li {width:210px;float:left;list-style:none;margin-right:15px;}
            .cbox .card h4 {margin:12px 0 6px;}
            .cbox .card .com {width:216px;}
            .cbox .card .com p {padding:1px 0 5px;line-height:18px;}
            .cbox .com .insurance {height:32px;margin:3px 0 4px;}
            .cbox .card p {padding:1px 0 0;line-height:16px;}
            .cbox .card .words {line-height:17px;}
            .cbox .card .more {font-size:10px;color:#666;line-height:14px;}
            .cbox .card b {font-size:14px;}
			.num {font-style:normal;font-family:Tahoma}//,Georgia,Serif;
		</style>
