<style>
	.panel-body {padding: 0;}
	.panel-body table {margin: 0; }
</style>

<?php render_control('PageHead', 'head1',
	array('title' => '系统自动建档',
		'ref_table' => 'table'
	));
?>
<form id="_myform">
	<table cellspacing="0" class="table table-bordered">
		<tr>
			<td width="15%" align="right">请选择档案来源店铺:</td>
			<td>
				<select id="s_shop" onclick="shop_change();">
					<?php foreach($response['shop_list'] as $_shop){ ?>
						<option value="<?php echo $_shop['shop_code']?>"><?php echo $_shop['shop_name']?></option>
					<?php }?>
				</select>
			</td>

			<td width="15%" align="right" id="_td1_tb_cats" style="display:none;">请选择店铺所属大类:</td>
			<td id="_td2_tb_cats" style="display: none;">
				<div class="controls bui-form-field-select"
				     data-items='<?php echo $response['tb_cats']; ?>'
				     data-select="{multipleSelect:false}">
					<input name="tb_cat" id="tb_cat" type="hidden" value="">
				</div>
			</td>
		<tr>
	</table>
</form>
<table cellspacing="0" class="table table-bordered">
	<tr>
		<td width="15%" height="80" align="center" style="text-align: center;vertical-align: middle;font-size: 20px;">品牌</td>
		<td width="15%" height="80" align="center" style="text-align: center;vertical-align: middle;font-size: 20px;">大类</td>
		<td width="15%" height="80" align="center" style="text-align: center;vertical-align: middle;font-size: 20px;">规格</td>
		<td width="15%" height="80" align="center" style="text-align: center;vertical-align: middle;font-size: 20px;">商品</td>
		<td width="15%" height="80" align="center" style="text-align: center;vertical-align: middle;font-size: 20px;">商品明细</td>
	<tr>

	<tr>
		<td colspan="5">
			<input type="button" class="button button-primary" value="立即生成档案" onclick="auto_create()"/>
		</td>
	</tr>
</table>

<table cellspacing="0" class="table table-bordered">
	<tr>
		<td width="15%" align="right">仓库:</td>
		<td>
			<select id="s_store">
				<?php foreach($response['store_list'] as $_store){ ?>
					<option value="<?php echo $_store['store_code']?>"><?php echo $_store['store_name']?></option>
				<?php }?>
			</select>
		</td>
	<tr>
</table>

<table cellspacing="0" class="table table-bordered">
<!--	<tr>-->
<!--		<td width="30%" align="center" style="text-align: center;">商品库存</td>-->
<!--	<tr>-->

	<tr>
		<td colspan="5">
			<input type="button" class="button button-primary" value="立即初始化库存" onclick="auto_init_inv()"/>
		</td>
	</tr>
</table>

<script type="text/javascript">


	$(function () {

		BUI.use(['bui/form'], function (Form) {

			var form = new Form.Form({
				srcNode: '#_myform',
				submitType: 'ajax',
				callback: function (data) {

				}
			}).render();
		});
	});

	/**
	 * 店铺change
	 */
	function shop_change() {

		var url = '?app_act=sys/auto_create/shop_change';
		var shop_code = $('#s_shop').val();

		$.ajax({
			type: "POST",
			url: url,
			data: {'shop_code': shop_code},
			dataType: "json",
			async: false,
			success: function (data) {

				if (data.status == 1) {
					if ('C' == data.data.tb_shop_type) {
						$('#_td1_tb_cats').attr('style','display:table-cell');
						$('#_td2_tb_cats').attr('style','display:table-cell');

//						$('#_td1_tb_cats').show();
//						$('#_td2_tb_cats').show();
					}

					if ('B' == data.data.tb_shop_type) {
						$('#_td1_tb_cats').attr('style','display:none');
						$('#_td2_tb_cats').attr('style','display:none');

//						$('#_td1_tb_cats').hide();
//						$('#_td2_tb_cats').hide();
					}
				}
			}
		});
	}

	function auto_create() {

		var shop_code = $('#s_shop').val();
		var tb_cats = $('#tb_cat').val();

		if (!shop_code) {
			alert('请选择店铺');
			return;
		}

		//弹出页面
		BUI.use(['bui/overlay'], function (Overlay) {
			var dialog = new Overlay.Dialog({
				title: '立即生成档案',
				width: '98%',
				height: 550,
				buttons: [

				],
			//	bodyContent:'请选择授权结果？（注：未订购应用请先完成订购，之后授权）',
				loader: {
					url: '?app_act=sys/auto_create/create_progress_html',
					autoLoad: true, //不自动加载
					params: {'shop_code':shop_code,'tb_cats':tb_cats}, //附加的参数
					lazyLoad: false, //不延迟加载
					dataType: 'text'   //加载的数据类型
				},
				mask: false
			});

			dialog.show();
		});
	}

	//初始化库存
	function auto_init_inv() {

		var shop_code = $('#s_shop').val();
		var store_code = $('#s_store').val();

		if (!shop_code) {
			alert('请选择店铺');
			return;
		}

		if (!store_code) {
			alert('请选择仓库');
			return;
		}

		BUI.use('bui/overlay',function(overlay) {

				BUI.Message.Confirm('初始化库存后将以淘宝库存为准，您确定要继续进行库存初始化吗？', function () {
					do_auto_init_inv(shop_code, store_code);
				}, 'question');
			}
		)
	}

	/**
	 * 初始化库存
	 * @param shop_code
	 * @param store_code
	 */
	function do_auto_init_inv(shop_code, store_code) {
		//弹出页面
		BUI.use(['bui/overlay'], function (Overlay) {
			var dialog = new Overlay.Dialog({
				title: '初始化库存',
				width: '60%',
				height: '300',
				buttons: [

				],
				loader: {
					url: '?app_act=sys/auto_create/create_init_inv_html',
					autoLoad: true, //不自动加载
					params: {'shop_code':shop_code,'store_code':store_code}, //附加的参数
					lazyLoad: false, //不延迟加载
					dataType: 'text'   //加载的数据类型
				},
				mask: true
			});

			dialog.show();
		});
	}

</script>