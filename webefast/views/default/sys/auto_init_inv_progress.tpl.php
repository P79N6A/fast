<style>
	.panel-body {
		padding: 0;
	}

	.panel-body table {
		margin: 0;
	}
</style>

<div style="height: 200px;overflow:auto;">
	<!--进度条-->
	请稍候，正在进行库存初始化
	<div class="progress progress-striped" id="dl_progress_<?php echo $response['_rand_code']?>" style="display: none;"></div>
	<span style="display: none;color: orangered" id="init_success_<?php echo $response['_rand_code']?>">初始化成功!</span>
	<span style="display: none;color: orangered" id="un_need_init_<?php echo $response['_rand_code']?>">没有要初始化库存的商品!</span>
</div>

<span id="_s_shop_<?php echo $response['_rand_code']?>" style="display: none;"><?php echo $response['shop_code']?></span>
<span id="_s_store_<?php echo $response['_rand_code']?>" style="display: none;"><?php echo $response['store_code']?></span>

<script type="text/javascript">

//	var fullMask = null;//提示层
//	$(function () {
//
//		BUI.use(['bui/mask'],function(Mask){
//
//			fullMask = new Mask.LoadMask({
//				el : 'body',
//				msg : '初始化库存。。。'
//			});
//		});
//
//		fullMask.show();
//		auto_create(1);
//	});

	$(function () {
		var progressbar = null;
		pre_do_init_inv();
	});

	function pre_do_init_inv() {

		var url = '?app_act=sys/auto_create/pre_do_init_inv';
		var shop_code = $('#_s_shop_<?php echo $response['_rand_code']?>').html();
		var store_code = $('#_s_store_<?php echo $response['_rand_code']?>').html();
		$.ajax({
			type: "POST",
			url: url,
			data: {'shop_code': shop_code, 'store_code':store_code},
			dataType: "json",
			async: false,
			success: function (data) {

				if (data.status == 1) {
					var stock_adjust_record_id = data.data.stock_adjust_record_id;
					var	num_per_time = data.data.num_per_time;
					var	item_count = data.data.item_count;
					var times = data.data.times;

					$('#dl_progress_<?php echo $response['_rand_code']?>').empty();//重置
					$('#dl_progress_<?php echo $response['_rand_code']?>').show();
					//监测进度
					BUI.use('bui/progressbar',function(ProgressBar){

						var Progressbar = ProgressBar.Base;
						progressbar = new Progressbar({
							elCls : 'progress progress-striped active',
							render : '#dl_progress_<?php echo $response['_rand_code']?>',
							tpl : '<div class="bar"></div>'
							//	percent:10
						});
						progressbar.render();
						progressbar.set('percent',1/times * 100);
					});

					do_init_inv(shop_code,store_code,stock_adjust_record_id, num_per_time, item_count, times, 0);
				} else {
					$('#un_need_init_<?php echo $response['_rand_code']?>').show();
				}
			}
		});
	}

	/**
	 *
	 * @param shop_code
	 * @param store_code
	 * @param stock_adjust_record_id
	 * @param num_per_time
	 * @param item_count
	 * @param times
	 * @param index 第几次
	 */
	function do_init_inv(shop_code,store_code,stock_adjust_record_id,num_per_time,item_count,times,index) {

		var url = '?app_act=sys/auto_create/do_init_inv';
		$.ajax({
			type: "POST",
			url: url,
			data: {'shop_code': shop_code, 'store_code':store_code,'stock_adjust_record_id':stock_adjust_record_id,'num_per_time':num_per_time,'item_count':item_count,'times':times,'index':index},
			dataType: "json",
			async: false,
			success: function (data) {

				if (data.status == 1) {
					progressbar.set('percent',(index+2)/times * 100);
					if (index+1<times) {
						do_init_inv(shop_code,store_code,stock_adjust_record_id, num_per_time, item_count, times, index+1);
					}
				} else if(data.status == 2) {
					$('#init_success_<?php echo $response['_rand_code']?>').show();
				}
			}
		});
	}
</script>