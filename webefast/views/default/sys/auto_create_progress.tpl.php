<style>
	.panel-body {
		padding: 0;
	}

	.panel-body table {
		margin: 0;
	}
</style>

<div style="height: 440px;overflow:auto;">
	<p id="category">请稍候，正在生成大类：</p>
	<p id="category_content_<?php echo $response['_rand_code']?>" style="display: none;"></p>
	<p id="category_count_<?php echo $response['_rand_code']?>" style="display: none;">大类全部生成成功，共生成大类数量:<span id="s_category_count_<?php echo $response['_rand_code']?>" style="font-weight: bold;"></span></p>

	<p id="spec_<?php echo $response['_rand_code']?>" style="display: none;">请稍候，正在生成规格档案：</p>
	<p id="spec_content_<?php echo $response['_rand_code']?>" style="display: none;"></p>
	<p id="spec_count_<?php echo $response['_rand_code']?>" style="display: none;">规格档案全部生成成功，共生成规格1数量：<span id="s_spec1_count_<?php echo $response['_rand_code']?>" style="font-weight: bold;"></span>，规格2数量：<span id="s_spec2_count_<?php echo $response['_rand_code']?>" style="font-weight: bold;"></span></p>

	<p id='brand_<?php echo $response['_rand_code']?>' style="display: none;">请稍候，正在生成品牌档案：</p>
	<p id='brand_content_<?php echo $response['_rand_code']?>' style="display: none;"></p>
	<p id="brand_count_<?php echo $response['_rand_code']?>" style="display: none;">品牌档案全部生成成功，共生成品牌档案数量:<span id="s_brand_count_<?php echo $response['_rand_code']?>" style="font-weight: bold;"></span></p>

	<p id="item_<?php echo $response['_rand_code']?>" style="display: none;">请稍候，正在生成商品：</p>
	<p id="item_content_<?php echo $response['_rand_code']?>" style="display: none;"></p>
	<p id="item_count_<?php echo $response['_rand_code']?>" style="display: none;">商品全部生成成功，共生成商品数量:<span id="s_item_count_<?php echo $response['_rand_code']?>" style="font-weight: bold;"></span></p>

	<p id="error_info_<?php echo $response['_rand_code']?>" style="color: red;"></p>

</div>

<span id="_s_shop_<?php echo $response['_rand_code']?>" style="display: none;"><?php echo $response['shop_code']?></span>
<span id="_s_tb_cats_<?php echo $response['_rand_code']?>" style="display: none;"><?php echo $response['tb_cats']?></span>

<script type="text/javascript">

//	var fullMask = null;//提示层
	$(function () {

//		BUI.use(['bui/mask'],function(Mask){
//
//			fullMask = new Mask.LoadMask({
//				el : 'body',
//				msg : '建档中。。。'
//			});
//		});
//
//		fullMask.show();
		do_auto_create(1);
	});

	function do_auto_create(step) {
		var url = '?app_act=sys/auto_create/do_auto_create';
		var shop_code = $('#_s_shop_<?php echo $response['_rand_code']?>').html();
		var tb_cats = $('#_s_tb_cats_<?php echo $response['_rand_code']?>').html();

		var total_step = 3;

		$.ajax({
			type: "POST",
			url: url,
			data: {'shop_code': shop_code,'tb_cats':tb_cats,'step':step},
			dataType: "json",
			async: false,
			success: function (data) {

				if (data.status < 1) {
					$('#error_info_<?php echo $response['_rand_code']?>').html(data.message);
				}

				if (data.status == 1) {

					check_create_status();
					if (step < total_step) {
						do_auto_create(step+1);
					}
				}
			}
		});
	}


	//监测下载进度
	function check_create_status() {

		var check_url = '?app_act=sys/auto_create/check_create_progress';
		$.ajax({
			type: "POST",
			url: check_url,
			data: '',
			dataType: "json",
			async: false,
			success: function (data) {
				if (data.status == 1) {
					if (data.data.tb_brand_create_over) {
						//品牌下载完成
						fill_brand_content(data.data.tb_brand, data.data.tb_brand_count);

						$('#brand_content_<?php echo $response['_rand_code']?>').show();
						$('#brand_count_<?php echo $response['_rand_code']?>').show();

						$('#category_<?php echo $response['_rand_code']?>').show();
					}

					if (data.data.tb_category_create_over) {
						//分类下载完成
						fill_category_content(data.data.tb_category, data.data.tb_category_count);
						$('#category_content_<?php echo $response['_rand_code']?>').show();
						$('#category_count_<?php echo $response['_rand_code']?>').show();

						$('#spec_<?php echo $response['_rand_code']?>').show();
					}

					if (data.data.tb_spec_create_over) {
						//规格下载完成
						fill_spec_content(data.data.tb_spec1, data.data.tb_spec2, data.data.tb_spec1_count, data.data.tb_spec2_count)
						$('#spec_content_<?php echo $response['_rand_code']?>').show();
						$('#spec_count_<?php echo $response['_rand_code']?>').show();

						$('#item_<?php echo $response['_rand_code']?>').show();
						$('#brand_<?php echo $response['_rand_code']?>').show();
					}

					if (data.data.tb_item_create_over) {
						//商品下载完成
						fill_item_content(data.data.tb_item, data.data.tb_item_count);
						$('#item_content_<?php echo $response['_rand_code']?>').show();
						$('#item_count_<?php echo $response['_rand_code']?>').show();
					}

				//	setTimeout(check_create_status, 1000);
					if (1 == data.data.create_over) {
						alert('建档成功!');
					}
				}
			}
		});
	}

	/**
	 * 填充品牌内容
	 * @param brand
	 */
	function fill_brand_content(brand, count) {
		$('#brand_content_<?php echo $response['_rand_code']?>').html('');

		var brand_index = 0;

		for(obj in brand) {
			brand_index++;
			if (brand_index < 6) {
				var name = obj;
				$('#brand_content_<?php echo $response['_rand_code']?>').append(name + '<br>');
			}
		}

		$('#s_brand_count_<?php echo $response['_rand_code']?>').html(count);
	}

	/**
	 * 填充分类内容
	 * @param category
	 * @param count
	 */
	function fill_category_content(category, count) {
		$('#category_content_<?php echo $response['_rand_code']?>').html('');

		var category_index = 0;

		for(obj in category) {

			category_index++;
			if (category_index < 6) {
				var name = obj;
				$('#category_content_<?php echo $response['_rand_code']?>').append(name + '<br>');
			}
		}

		$('#s_category_count_<?php echo $response['_rand_code']?>').html(count);
	}

	/**
	 * 填充规格内容
	 */
	function fill_spec_content(spec1, spec2, spec1_count, spec2_count) {
		$('#spec_content_<?php echo $response['_rand_code']?>').html('');

		var spec1_index = spec2_index = 0;

		for (var obj in spec1) {

			spec1_index++;
			if (spec1_index<3) {
				var name = obj;
				$('#spec_content_<?php echo $response['_rand_code']?>').append(name + '<br>');
			}
		}

		for (var obj in spec2) {

			spec2_index++;
			if (spec2_index<3) {
				var name = obj;
				$('#spec_content_<?php echo $response['_rand_code']?>').append(name + '<br>');
			}
		}

		$('#s_spec1_count_<?php echo $response['_rand_code']?>').html(spec1_count);
		$('#s_spec2_count_<?php echo $response['_rand_code']?>').html(spec2_count);
	}

	/**
	 * 填充商品内容
	 * @param category
	 * @param count
	 */
	function fill_item_content(item, count) {
		$('#item_content_<?php echo $response['_rand_code']?>').html('');

		var item_index = 0;
		for(obj in item) {

			item_index++;
			if (item_index < 6) {
				var name = obj;
				$('#item_content_<?php echo $response['_rand_code']?>').append(name + '<br>');
			}
		}

		$('#s_item_count_<?php echo $response['_rand_code']?>').html(count);
	}

</script>