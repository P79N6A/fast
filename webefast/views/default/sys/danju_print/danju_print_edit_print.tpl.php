<?php //echo get_js('common.js') ?>
<?php echo load_js('print/colResizable-1.3.min.js') ?>

<?php echo load_js('print/jquery.draggable/jquery.ui.position.js') ?>
<?php //echo get_js('print/jquery.draggable/jquery.ui.core.js') ?>
<?php echo load_js('print/jquery.draggable/jquery.ui.mouse.js') ?>
<?php echo load_js('print/jquery.draggable/jquery.ui.widget.js') ?>
<?php echo load_js('print/jquery.draggable/jquery.ui.draggable.js') ?>
<?php echo load_js('print/jquery.draggable/jquery.ui.droppable.js') ?>
<?php echo load_js('jquery-ui.min.js'); ?>
<!-- 2014-03-26 -->
<?php echo load_css('reset.css');?>
<?php echo load_css('print.css');?>
<?php echo load_css('blitzer/jquery-ui.min.css');?>

<?php

$extend_attr = array();

$page_zoom = 100;

$page_top_offset = $page_left_offset = 0;

if (not_null($response['danju_print_data']['extend_attr'])) {
	$extend_attr = object_to_array(json_decode($response['danju_print_data']['extend_attr']));
}

if(not_null($extend_attr['page_zoom'])) {
	$page_zoom = $extend_attr['page_zoom'];
}

if(not_null($extend_attr['page_top_offset'])) {
	$page_top_offset = $extend_attr['page_top_offset'];
}

if(not_null($extend_attr['page_left_offset'])) {
	$page_left_offset = $extend_attr['page_left_offset'];
}

?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top" width="250">

			<div class="toBePrint" id='div_op_area' style="overflow-y:scroll;height: 550px;border-color: transparent;">

				<div id='op_page_top_area'>
					<?php foreach ($response['danju_print_conf']['main_conf'] as $conf_key => $conf_val) { ?>
						<?php if ('page_top_area' == $conf_key) {?>
						<div class='submenu' id="div_<?php echo $conf_key ?>">
							<?php if ('grid' == $conf_val['type']) { ?>
								<div class="subhead" id="<?php echo $conf_key; ?>">
									<span><?php echo $conf_val['title']; ?></span>&nbsp;
									<input type="checkbox" alias_name='op_ck' id="op_ck_<?php echo $conf_key; ?>" onclick="op_grid_show_change('<?php echo $conf_key; ?>', 'page_top')"/>
									<input type="button" value="表格" onclick="create_table('div_table_<?php echo $conf_key ?>','page_top');"/>
									<input type="button" value="配置" onclick="dlg_table_setting('div_table_<?php echo $conf_key ?>')"/>
								</div>
								<div class="submain" id="sm_<?php echo $conf_key; ?>" style="display: none;">
									<ul id="ul_sm_<?php echo $conf_key; ?>">
										<?php foreach ($conf_val['data'] as $data_key => $data_val) { ?>
											<li>
													<span
														id="<?php echo $conf_key . '_' . $data_key; ?>"
														rel_field="<?php echo $data_key ?>" style="cursor: move;"><?php echo $data_val; ?></span>
											</li>
										<?php } ?>
									</ul>
								</div>
								</div>
							<?php }?>
						<?php }?>
					<?php }?>
				</div>

				<?php $can_move_up_down_num = 0;//配置区域能上下排序的数量 ?>
				<?php foreach ($response['danju_print_conf']['main_conf'] as $conf_key => $conf_val) { ?>

					<?php if ('page_top_area' != $conf_key && 'page_bottom_area' != $conf_key ) { ?>
						<?php $can_move_up_down_num++;?>
					<?php } ?>
				<?php }?>

				<div id="op_major_area">
					<?php foreach ($response['danju_print_conf']['main_conf'] as $conf_key => $conf_val) { ?>

						<?php if ('page_top_area' == $conf_key || 'page_bottom_area' == $conf_key ) { ?>
							<?php continue; ?>
						<?php }?>

						<div class='submenu' id="div_<?php echo $conf_key ?>">
							<?php if ('grid' == $conf_val['type']) { ?>
								<div class="subhead" id="<?php echo $conf_key; ?>">
									<span><?php echo $conf_val['title']; ?></span>&nbsp;
									<input type="checkbox" alias_name='op_ck' id="op_ck_<?php echo $conf_key; ?>" onclick="op_grid_show_change('<?php echo $conf_key; ?>', 'main_major')"/>
									<input type="button" value="表格" onclick="create_table('div_table_<?php echo $conf_key ?>', 'main_major');"/>
									<input type="button" value="配置" onclick="dlg_table_setting('div_table_<?php echo $conf_key ?>')"/>
									<a href="javascript:void(0)"
									   onClick="div_move_up(this, 'div_op_move_<?php echo $conf_key ?>')"
									   id="div_op_moveup_<?php echo $conf_key ?>"><?php if ($can_move_up_down_num > 1) {?>上<?php }?></a>
									<a href="javascript:void(0)"
									   onClick="div_move_down(this, 'div_op_move_<?php echo $conf_key ?>')"
									   id="div_op_movedown_<?php echo $conf_key ?>"><?php if ($can_move_up_down_num > 1){?>下<?php }?></a>
								</div>
								<div class="submain" id="sm_<?php echo $conf_key; ?>" style="display: none;">
									<ul id="ul_sm_<?php echo $conf_key; ?>">
										<?php foreach ($conf_val['data'] as $data_key => $data_val) { ?>
											<li>
												<span
													id="<?php echo $conf_key . '_' . $data_key; ?>"
													rel_field="<?php echo $data_key ?>" style="cursor: move;"><?php echo $data_val; ?></span>
											</li>
										<?php } ?>
									</ul>
								</div>

							<?php } ?>

							<?php if ('list' == $conf_val['type']) { ?>
								<div class="subhead" id="<?php echo $conf_key; ?>">
									<span><?php echo $conf_val['title']; ?></span>
									<input type="checkbox" alias_name="op_ck" id="op_ck_<?php echo $conf_key; ?>" onclick="op_list_show_change('<?php echo $conf_key; ?>')"/>
									<input type="button" value="配置" onclick="dlg_list_table_setting('div_table_<?php echo $conf_key ?>')"/>
									<a href="javascript:void(0)" onclick="div_move_up(this, 'div_op_move_<?php echo $conf_key ?>')" id="div_op_moveup_<?php echo $conf_key ?>"><?php if ($can_move_up_down_num > 1) {?>上<?php }?></a>
									<a href="javascript:void(0)" onclick="div_move_down(this, 'div_op_move_<?php echo $conf_key ?>')" id="div_op_movedown_<?php echo $conf_key ?>"><?php if ($can_move_up_down_num > 1) {?>下<?php }?></a>
								</div>
								<div class="submain" id="sm_<?php echo $conf_key; ?>" style="display: none;">
									<ul>
										<li>
											<table width="100%" style="text-align:center">
												<?php foreach ($conf_val['data'] as $data_key => $data_val) { ?>
													<tr id="<?php echo $conf_key . '_tr_' . $data_key ?>">
														<td style="width: 35px;">
															<?php if(isset($conf_val['ext_col'])) {?>
																<?php if ($conf_val['ext_col'] != $data_key) {?>
																	<input id="<?php echo 'conf_'.$conf_key.'_'.$data_key?>"  type="button" value="配置" style="display: none;" onclick="dlg_list_table_td_style('<?php echo 'table_'.$conf_key?>','<?php echo $data_key?>',true, '<?php echo $conf_val['ext_col']?>')"/>
																<?php } else { ?>
																	<input id="<?php echo 'conf_'.$conf_key.'_'.$data_key?>"  type="button" value="配置" style="display: none;" onclick="dlg_list_table_ext_col_td_style('<?php echo 'table_'.$conf_key?>','<?php echo $data_key?>',true, '<?php echo $conf_val['ext_col']?>')"/>

																<?php } ?>

															<?php } else {?>
																<input id="<?php echo 'conf_'.$conf_key.'_'.$data_key?>"  type="button" value="配置" style="display: none;" onclick="dlg_list_table_td_style('<?php echo 'table_'.$conf_key?>','<?php echo $data_key?>',false, '')"/>
															<?php } ?>
														</td>
														<td id="<?php echo $conf_key . '_td_' . $data_key ?>"><?php echo $data_val ?></td>
														<td>
															<input type="checkbox" id="op_ck_data_<?php echo $conf_key ?>_<?php echo $data_key ?>"  alias_name="ck_<?php echo $conf_key ?>" value="<?php echo $data_key; ?>"
																<?php if(isset($conf_val['ext_col'])) {?>
																	onclick="plugin_table_list_area.ext_th_show_change(this, 'table_<?php echo $conf_key; ?>','<?php echo $conf_key ?>','<?php echo $data_key ?>','<?php echo $conf_val['ext_col']; ?>',<?php if (isset($conf_val['ext_col']) && ($data_key == $conf_val['ext_col'])){ ?>true<?php } else { ?>false<?php }?>)"

																<?php } else {?>
																	onclick="plugin_table_list_area.th_show_change(this, 'table_<?php echo $conf_key; ?>','<?php echo $conf_key ?>','<?php echo $data_key ?>')"
																<?php }?>
																/>
														</td>
														<td>
															<a href="javascript:void(0)"
																<?php if(isset($conf_val['ext_col'])) {?>
																	onclick="plugin_table_list_area.ext_moveUp(this, 'ck_<?php echo $conf_key ?>','table_<?php echo $conf_key; ?>','<?php echo $conf_val['ext_col']; ?>')"
																<?php } else {?>
																	onclick="plugin_table_list_area.moveUp(this, 'ck_<?php echo $conf_key ?>','table_<?php echo $conf_key; ?>')"
																<?php }?>
                                                               id="moveup_<?php echo $conf_key ?>_<?php echo $data_key ?>">上</a>
															<a href="javascript:void(0)"
																<?php if(isset($conf_val['ext_col'])) {?>
																	onclick="plugin_table_list_area.ext_moveDown(this, 'ck_<?php echo $conf_key ?>','table_<?php echo $conf_key ?>','<?php echo $conf_val['ext_col']; ?>')"
																<?php } else {?>
																	onclick="plugin_table_list_area.moveDown(this, 'ck_<?php echo $conf_key ?>','table_<?php echo $conf_key ?>')"
																<?php }?>
                                                               id="movedown_<?php echo $conf_key ?>_<?php echo $data_key ?>">下</a>
														</td>
													</tr>
												<?php } ?>
											</table>
										</li>
									</ul>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</div>

				<div id='op_page_bottom_area'>
					<?php foreach ($response['danju_print_conf']['main_conf'] as $conf_key => $conf_val) { ?>
						<?php if ('page_bottom_area' == $conf_key) {?>
						<div class='submenu' id="div_<?php echo $conf_key ?>">
							<?php if ('grid' == $conf_val['type']) { ?>
								<div class="subhead" id="<?php echo $conf_key; ?>">
									<span><?php echo $conf_val['title']; ?></span>&nbsp;
									<input type="checkbox" alias_name='op_ck' id="op_ck_<?php echo $conf_key; ?>" onclick="op_grid_show_change('<?php echo $conf_key; ?>','page_bottom')"/>
									<input type="button" value="表格" onclick="create_table('div_table_<?php echo $conf_key ?>', 'page_bottom');"/>
									<input type="button" value="配置" onclick="dlg_table_setting('div_table_<?php echo $conf_key ?>')"/>
								</div>
								<div class="submain" id="sm_<?php echo $conf_key; ?>" style="display: none;">
									<ul id="ul_sm_<?php echo $conf_key; ?>">
										<?php foreach ($conf_val['data'] as $data_key => $data_val) { ?>
											<li>
													<span
														id="<?php echo $conf_key . '_' . $data_key; ?>"
														rel_field="<?php echo $data_key ?>" style="cursor: move;"><?php echo $data_val; ?></span>
											</li>
										<?php } ?>
									</ul>
								</div>
								</div>
							<?php }?>
						<?php }?>
					<?php }?>
				</div>

				<input type="button" value="总体设置" style="margin-top: 10px;" onclick="dlg_page_setting()"/>
			</div>
		</td>

		<td valign="top" width="100%">
			<div style="width: 100%">
				<div class="main_print_nr" id="danju_print_content"
				     style="width:<?php echo ($response['danju_print_data']['template_page_width']-2)*$page_zoom/100; ?>mm;margin:0 auto;">

					<?php if (not_null($response['danju_print_data']['danju_print_content'])) { ?>
						<?php echo $response['danju_print_data']['danju_print_content'] ?>
					<?php } else { ?>
						<div id="div_print_page_top_area"></div>
						<div id="div_print_main_major_area"></div>
						<div id="div_print_page_bottom_area"></div>
					<?php }?>
				</div>
			</div>

			<input type="button" value="保存" onclick="save_danju_print()" class="button button-primary"/>
			<input type="button" value="清空" onclick="clear_print_content()" class="button button-primary"/>
			<input type="button" value="刷新" onclick="window.location.reload();" class="button button-primary"/>
		</td>
	</tr>
</table>

<div id="dlg_create_table" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="width:30%;text-align: center;">列数:</td>
			<td><input style="margin-left: 10px;" type="text" id="cols_num"/></td>
		</tr>
		<tr>
			<td style="text-align: center;">行数:</td>
			<td><input style="margin-left: 10px;" type="text" id="rows_num"/></td>
		</tr>
	</table>
</div>

<div id="dlg_page_setting" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="width:30%;text-align: center;">整体缩放比例:</td>
			<td>
				<input style="margin-left: 10px;" type="text" id="page_zoom" value="<?php if (not_null($extend_attr['page_zoom'])) echo $extend_attr['page_zoom']; else echo 100; ?>"/>%
			</td>
		</tr>

		<tr>
			<td style="width:30%;text-align: center;">上偏移量:</td>
			<td>
				<input style="margin-left: 10px;" type="text" id="page_top_offset" value="<?php echo $page_top_offset; ?>"/>mm
			</td>
		</tr>

		<tr>
			<td style="width:30%;text-align: center;">左偏移量:</td>
			<td>
				<input style="margin-left: 10px;" type="text" id="page_left_offset" value="<?php  echo $page_left_offset; ?>"/>mm
			</td>
		</tr>

	</table>
</div>

<div id="dlg_table_setting" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="text-align: center;">字体:</td>
			<td><select id="table_font_family" style="margin-left: 10px;">
					<option value="宋体">宋体</option>
					<option value="黑体">黑体</option>
					<option value='华文中宋'>华文中宋</option>
					<option value='隶书'>隶书</option>
					<option value="颜体">颜体</option>
					<option value="幼圆">幼圆</option>
					<option value="雅黑">雅黑</option>
				</select></td>
		</tr>
		<tr>
			<td style="width:30%;text-align: center;">表格线条:</td>
			<td>
				<select id="table_line_style" style="margin-left: 10px;">
					<option value="solid">实线</option>
<!--					<option value='dashed'>虚线</option>-->
<!--					<option value='dotted'>点线</option>-->
					<option value="none">无</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">行高度:</td>
			<td><input style="margin-left: 10px;" type="text" id="table_td_height"/>px</td>
		</tr>
	</table>
</div>

<!--明细区配置-->
<div id="dlg_list_table_setting" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="text-align: center;">字体:</td>
			<td><select id="list_table_font_family" style="margin-left: 10px;">
					<option value="宋体">宋体</option>
					<option value="黑体">黑体</option>
					<option value='华文中宋'>华文中宋</option>
					<option value='隶书'>隶书</option>
					<option value="颜体">颜体</option>
					<option value="幼圆">幼圆</option>
					<option value="雅黑">雅黑</option>
				</select></td>
		</tr>
		<tr>
			<td style="width:30%;text-align: center;">表格线条:</td>
			<td>
				<select style="margin-left: 10px;" id="list_table_line_style">
					<option value="solid">实线</option>
<!--					<option value='dashed'>虚线</option>-->
<!--					<option value='dotted'>点线</option>-->
					<option value="none">无</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">表头高度:</td>
			<td><input style="margin-left: 10px;" type="text" id="list_table_th_height"/>px</td>
		</tr>
		<tr>
			<td style="text-align: center;">行高度:</td>
			<td><input style="margin-left: 10px;" type="text" id="list_table_td_height"/>px</td>
		</tr>
	</table>
</div>

<!-- 网格类型td内容设置 -->
<div id="dlg_grid_table_td_content_setting" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="width:30%;text-align: center;">文字大小:</td>
			<td>
				<select style="margin-left: 10px;" id="font_size">
					<option value="8px">8</option>
					<option value="10px">10</option>
					<option value="12px" selected>12</option>
					<option value='14px'>14</option>
					<option value='16px'>16</option>
					<option value="18px">18</option>
					<option value="20px">20</option>
					<option value="22px">22</option>
					<option value="24px">24</option>
					<option value="26px">26</option>
					<option value="28px">28</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">文字对齐:</td>
			<td>
				<select style="margin-left: 10px;" id="text_align">
					<option value="left">居左</option>
					<option value='center' selected>居中</option>
					<option value='right'>居右</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">文字粗细:</td>
			<td>
				<select style="margin-left: 10px;" id="font_weight">
					<option value="400">正常</option>
					<option value='700'>加粗</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">左边距:</td>
			<td>
				<input style="margin-left: 10px;"  type="text" value="" id="padding_left"/>px
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">右边距:</td>
			<td>
				<input style="margin-left: 10px;"  type="text" value="" id="padding_right"/>px
			</td>
		</tr>
	</table>
</div>


<!-- 列表类型td内容设置 12.23-->
<div id="dlg_list_table_td_content_setting" style="display: none">
	<table width="100%" class="table">
		<tr>
			<td style="width:30%;text-align: center;">文字大小:</td>
			<td>
				<select style="margin-left: 10px;" id="list_font_size">
					<option value="8px">8</option>
					<option value="10px">10</option>
					<option value="12px" selected>12</option>
					<option value='14px'>14</option>
					<option value='16px'>16</option>
					<option value="18px">18</option>
					<option value="20px">20</option>
					<option value="22px">22</option>
					<option value="24px">24</option>
					<option value="26px">26</option>
					<option value="28px">28</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">文字对齐:</td>
			<td>
				<select style="margin-left: 10px;" id="list_text_align">
					<option value="left">居左</option>
					<option value='center' selected>居中</option>
					<option value='right'>居右</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align: center;">文字粗细:</td>
			<td>
				<select style="margin-left: 10px;" id="list_font_weight">
					<option value="400">正常</option>
					<option value='700'>加粗</option>
				</select>
			</td>
		</tr>
<!--		<tr>-->
<!--			<td style="text-align: center;">左边距:</td>-->
<!--			<td>-->
<!--				<input style="margin-left: 10px;"  type="text" value="" id="padding_left"/>px-->
<!--			</td>-->
<!--		</tr>-->
<!--		<tr>-->
<!--			<td style="text-align: center;">右边距:</td>-->
<!--			<td>-->
<!--				<input style="margin-left: 10px;"  type="text" value="" id="padding_right"/>px-->
<!--			</td>-->
<!--		</tr>-->
	</table>
</div>

<!-- js -->

<script type="text/javascript">

//保存用户要打印的内容
var customer_print_conf = new Object();

var shop_code = '<?php echo $response['shop_code'];?>';

//检测能否把保存，当打开合并单元格的情况下不能保存
function check_can_save() {

	var is_can_save = true;

	<?php foreach($response['danju_print_conf']['main_conf'] as $conf_key=>$conf_val) { ?>
	<?php if ('grid' == $conf_val['type']) { ?>

	if (!$("#op_col_row_table_<?php echo $conf_key ?>").is(':hidden')) {
		if ($("#table_<?php echo $conf_key ?>_before_merge").is(':hidden')) {
			is_can_save = false;
		}
	}
	<?php } ?>
	<?php } ?>

	return is_can_save;
}

//清空打印内容
function clear_print_content() {
	$init_print_content = $("<div id='div_print_page_top_area'><\/div><div id='div_print_main_major_area'><\/div><div id='div_print_page_bottom_area'><\/div>");
	$('#danju_print_content').empty().append($init_print_content);

	$("input[type='checkbox']").removeAttr("checked");
}

function save_danju_print() {

	var is_can_save = check_can_save();

	if (!is_can_save) {
		alert('合并单元格动作未完成,请取消合并');
		return;
	}

	var danju_print_content = $('#danju_print_content').html();

	//2014-03-31
	$('#_t_print_content').html(danju_print_content);
	$("#_t_print_content").find('.CRC').remove();
	$("#_t_print_content").find('.CRL').remove();
	$("#_t_print_content").find('.CRG').remove();

	$("#_t_print_content").find('.ui-droppable').removeClass('ui-droppable');
	$("#_t_print_content").find('.ui-draggable').removeClass('ui-draggable');
	$("#_t_print_content").find('.CRZ').removeClass('CRZ');
	danju_print_content = $("#_t_print_content").html();

	var url = "?app_act=sys/danju_print/do_save_print&app_fmt=json&app_page=null";
	var params = {
		'danju_print_code': '<?php echo $response['danju_print_code']?>',
		'customer_print_conf': customer_print_conf,
		'danju_print_content': danju_print_content,
		'shop_code': shop_code
	};

	//table样式结构 2014-03-31
	var obj_print_html_style = prev_save();
	params.print_html = $('#_t_print_content').html();
	params.print_html_style = obj_print_html_style;

	$('#_t_print_content').html('');

	$.post(url, params, function (data) {
		var data = $.parseJSON(data);
		alert(data.message);
	})
}

function init_list_width(table_id) {

	var danju_print_content_width = parseInt($('#danju_print_content').width());
	$("#"+table_id).css('width', danju_print_content_width);

	var old_tr_width = 0;//原始的所有td的宽度
	$('#'+table_id).find("tr").eq(0).find("th").each(function(){

		var _t = parseInt($(this).css('width'));
		old_tr_width += _t;
	});

	//2014-04-01补充
	$_th_count = $("#"+table_id).find('tr').eq(0).children('th').length;

	var _old_th_width_plus = 0;//前面th宽度累加

	$("#"+table_id).find('tr').eq(0).children('th').each(function($k, $v){

		if (($k + 1) == $_th_count) {
		//	$(this).css('width', danju_print_content_width - _old_th_width_plus);
		} else {
			var old_th_width = parseInt($(this).width());
			var _width = parseInt(old_th_width / old_tr_width * danju_print_content_width);
			_old_th_width_plus += _width;
		//	$(this).css('width', _width);
		}
	});

	//2014-04-01注释
//	var tr_count = $("#"+table_id).find("tr").length;
//	for(var i = 0; i < tr_count; ++i) {

//		$("#"+table_id).find("tr").eq(i).find("th").each(function(){
//			var old_th_width = parseInt($(this).css('width'));
//
//			var _width = parseInt(old_th_width / old_tr_width * danju_print_content_width);
//			$(this).css('width', _width);
//		});

//		$("#"+table_id).find("tr").eq(i).find("td").each(function(){
//			var old_td_width = parseInt($(this).css('width'));
//
//			var _width = parseInt(old_td_width / old_tr_width * danju_print_content_width);
//			$(this).css('width', _width);
//		});
//	}
}

function init_grid_width(table_id) {

	var danju_print_content_width = parseInt($('#danju_print_content').width());

	$("#"+table_id).css('width', danju_print_content_width);

	var old_tr_width = 0;//原始的所有td的宽度
	$('#'+table_id).find("tr").eq(0).find("td").each(function(){

		var _t = parseInt($(this).css('width'));

		old_tr_width += _t;
	});

	var tr_count = $("#"+table_id).find("tr").length;

	for(var i = 0; i < tr_count; ++i) {
		$("#"+table_id).find("tr").eq(i).find("td").each(function(){

			var old_td_width = parseInt($(this).width());

			var _width = parseInt(old_td_width / old_tr_width * danju_print_content_width);
			$(this).css('width', _width);
		})
	}
}

$(function () {

	<?php foreach($response['danju_print_conf']['main_conf'] as $conf_key=>$conf_val) { ?>

	//列表类型初始化
	<?php if ('list' == $conf_val['type']) { ?>

	init_list_width('table_<?php echo $conf_key?>');
	plugin_table_list_area.add_dbclick_for_table_detail_area_th('table_<?php echo $conf_key?>');
	plugin_table_list_area.add_resize_for_table_detail_area('table_<?php echo $conf_key ?>');

	plugin_table_list_area.toggle_detail_area_move_action('ck_<?php echo $conf_key?>');

	init_op_list_data_is_checked('table_<?php echo $conf_key?>');

	init_list_customer_obj('table_<?php echo $conf_key ?>', '<?php echo $conf_key?>');

	<?php } ?>

	//格子类型初始化
	<?php if ('grid' == $conf_val['type']) { ?>

	init_grid_width('table_<?php echo $conf_key?>');

	init_bind_drop('<?php echo $conf_key?>');
	add_dbclick_for_td('<?php echo 'div_table_'.$conf_key?>');
	plugin_table_list_area.add_resize_for_table_detail_area('table_<?php echo $conf_key ?>');

	init_grid_customer_obj('table_<?php echo $conf_key ?>', '<?php echo $conf_key?>');

	init_grid_table_td_content_setting('table_<?php echo $conf_key?>');

	<?php } ?>
	<?php } ?>

	init_op_list_is_checked();
});

/**
 * 保存用户要打印的内容
 */
function init_grid_customer_obj(table_id) {

	var key = table_id.replace('table_','');

	if (!object_customer_print_conf.contains(customer_print_conf, key)) {
		customer_print_conf[key] = new Object();

		$('#' + table_id + ' td span').each(function () {

			if ($(this).attr('rel_field')) {
				object_customer_print_conf.add(customer_print_conf[key], $(this).attr('rel_field'), $(this).attr('rel_field'));
			}
		});
	}
}

/**
 * 保存用户要打印的内容
 * @param table_id
 */
function init_list_customer_obj(table_id) {

	var key = table_id.replace('table_','');

	if (!object_customer_print_conf.contains(customer_print_conf, key)) {
		customer_print_conf[key] = new Object();
		$('#' + table_id + ' th').each(function () {

			if ($(this).attr('rel_field')) {
				object_customer_print_conf.add(customer_print_conf[key], $(this).attr('rel_field'), $(this).attr('rel_field'));
			}
		});
	}
}

/**
 * 初始化左边显示是否选中
 */
function init_op_list_is_checked() {

	$div_op_area = $('#div_op_area');

	$("#div_print_page_top_area").children('div').each(function (){

		var div_id_val = $(this).attr('id');
		var op_check_id = div_id_val.replace('div_table', 'op_ck');

		if (!$(this).is(":hidden")) {
			$('#' + op_check_id).attr('checked', 'checked');
		}

	})

	$("#div_print_main_major_area").children('div').each(function () {

		var div_id_val = $(this).attr('id');
		var op_check_id = div_id_val.replace('div_table', 'op_ck');

		if (!$(this).is(":hidden")) {
			$('#' + op_check_id).attr('checked', 'checked');
		}
	});

	$("#div_print_page_bottom_area").children('div').each(function (){

		var div_id_val = $(this).attr('id');
		var op_check_id = div_id_val.replace('div_table', 'op_ck');

		if (!$(this).is(":hidden")) {
			$('#' + op_check_id).attr('checked', 'checked');
		}
	});

	op_list_order();
}

//配置区域排序
function op_list_order() {

	$div_op_area = $('#op_major_area');
	$("#div_print_main_major_area").children('div').each(function () {

		var div_id_val = $(this).attr('id');
		var op_check_id = div_id_val.replace('div_table', 'op_ck');

		//左边操作顺序根据右边列表表头的顺序排序
		var div_table_index = $(this).index();
		var current_op = $('#' + op_check_id).parent().parent();
		var current_op_index = $(current_op).index();

		$target_div = $div_op_area.children('div').eq(div_table_index);
		var target_div_index = $target_div.index();
		if (current_op_index > target_div_index) {
			current_op.insertBefore($target_div);
		}
	});

	toggle_op_area_move_action($div_op_area);
}

/**
 * 切换显示上下移动操作
 * @param obj
 */
function toggle_op_area_move_action(div_op_obj) {

	$(div_op_obj).children('div').each(function () {
		var move_up_id = $(this).attr('id').replace('div_', 'div_op_moveup_');
		var move_down_id = $(this).attr('id').replace('div_', 'div_op_movedown_');
		toggle_move_action($('#' + move_up_id), true);
		toggle_move_action($('#' + move_down_id), true);
	});

	$first_obj = $(div_op_obj).children('div').first();
	var move_up_id = $first_obj.attr('id').replace('div_', 'div_op_moveup_');
	var move_down_id = $first_obj.attr('id').replace('div_', 'div_op_movedown_');
	toggle_move_action($('#' + move_up_id), false);
	toggle_move_action($('#' + move_down_id), true);

	$last_obj = $(div_op_obj).children('div').last();
	var move_up_id = $last_obj.attr('id').replace('div_', 'div_op_moveup_');
	var move_down_id = $last_obj.attr('id').replace('div_', 'div_op_movedown_');
	toggle_move_action($('#' + move_up_id), true);
	toggle_move_action($('#' + move_down_id), false);
}

/**
 * 上移是否显示
 * @param obj
 * @param is_show  true or fasle
 */
function toggle_move_action(obj, is_show) {

	if (is_show)
		$(obj).show();
	else
		$(obj).hide();
}

/**
 * 根据右边明细表头信息，初始化左边明细那些元素是选中
 * @param table_id
 */
function init_op_list_data_is_checked(table_id) {

	$("#" + table_id + " thead tr th").each(function () {

		var ck_id = $(this).attr('id').replace('table_', 'op_ck_data_').replace('th_', '');
		$('#' + ck_id).attr('checked', 'checked');

		//12.23
		var conf_id = ck_id.replace('op_ck_data', 'conf');
		$('#' + conf_id).show();

		//左边操作顺序根据右边列表表头的顺序排序

		var th_index = $(this).index();
		var current_tr = $('#' + ck_id).parent().parent();
		var current_tr_index = $(current_tr).index();
		$table_tbody_obj = $(current_tr).parent();

		$target_tr = $table_tbody_obj.find('tr').eq(th_index);
		var target_tr_index = $target_tr.index();
		if (current_tr_index > target_tr_index) {
			current_tr.insertBefore($target_tr);
		}
	});

	var op_data_ck_alias_name = table_id.replace('table_', 'ck_');
	plugin_table_list_area.toggle_detail_area_move_action(op_data_ck_alias_name);
}

/**
 * 初始化拖动事件
 * @param op_id
 */
function init_bind_drop(op_id) {

	var table_id = 'table_' + op_id;
	var op_drop_id = 'ul_sm_' + op_id;
	bind_drop(op_drop_id, table_id);
}

/**
 * 上移
 * @param obj
 */
function div_move_up(obj, alias_name) {
	var current = $(obj).parent().parent();
	var prev = current.prev();

	if (current.index() > 0) {
		current.insertBefore(prev);
	}

	var div_table_id = current.attr('id').replace('div_', 'div_table_');
	div_table_move_up($('#' + div_table_id));

	toggle_op_area_move_action($('#op_major_area'));
}

/**
 * 内容区域上移
 * @param div_table_id
 */
function div_table_move_up(obj) {

	var current = $(obj);
	var prev = current.prev();

	if (current.index() > 0) {
		current.insertBefore(prev);
	}
}

/**
 * 下移
 * @param obj
 */
function div_move_down(obj, alias_name) {
	var current = $(obj).parent().parent();
	var next = current.next();
	if (next) {
		current.insertAfter(next);
	}

	var div_table_id = current.attr('id').replace('div_', 'div_table_');
	div_table_move_down($('#' + div_table_id));

	toggle_op_area_move_action($('#op_major_area'));
}

/**
 * 内容区域下移
 * @param div_table_id
 */
function div_table_move_down(obj) {
	var current = $(obj);
	var next = current.next();
	if (next) {
		current.insertAfter(next);
	}
}

/**
 * 绑定拖动
 * @param op_id
 * @param table_id
 */
function bind_drop(op_id, table_id) {

	// let the gallery items be draggable
	$($("#" + op_id + " li span")).draggable({
		//cancel: "a.ui-icon", // clicking an icon won't initiate dragging
		revert: "invalid", // when not dropped, the item will revert back to its initial position
		containment: "document",
		helper: "clone",
		cursor: "move"
	});

	$("td", $("#" + table_id)).droppable({
		accept: "#" + op_id + " > li > span",
		drop: function (event, ui) {

			//判断该td能否拖进去，如果已经手工编辑过，不允许拖动
			if ($(this).html()) {
				if ($(this).attr('allow_edit') == 'true') {
					alert('不允许拖动');
					return;
				}
			}

			$(this).html($(this).html().replace("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>",''));
			$(this).html($(this).html().replace("<A onclick=dlg_grid_table_td_style(this)>[编]</A>",''));

			//	$(this).css('text-align', 'center');

			$drag_obj = ui.draggable.clone();
			//$drag_obj.css('margin-right', '10px');

			generate_td_title(this, $drag_obj.html());

			$(this).append($drag_obj.append('<a onclick="clean_drop(this)">[删]</a>'));
			$(this).attr('allow_edit', 'false');

			//9.16
			$(this).append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");

			var table_id = $(this).parent().parent().parent().attr('id');

			object_customer_print_conf.reset_grid(table_id);
		}
	});
}

/**
 * 初始化grid类型的td编辑按钮
 * @param table_id
 */
function init_grid_table_td_content_setting(table_id) {

	$('#'+table_id+' td').each(function(){

		if ($(this).html() != '') {

			var html = $(this).html();
			var Reg = /^((?:&nbsp;)*)([^;].*?)((?:&nbsp;)*)$/i;

			var f = Reg.exec(html);

			var before_nbsp = '';
			var end_nbsp = '';
			var main_content = '';
			if (f) {
				before_nbsp = f[1];
				main_content = f[2];
				end_nbsp = f[3];
			}

			var new_html = before_nbsp+main_content+"<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>"+end_nbsp;
			$(this).html(new_html);
			//console.log(Reg.exec(html));

//			var search_result = html.search(Reg);
//
//			if (search_result == 0 || search_result == -1) {
//				$(this).append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");
//			} else {
//				var replace_html = html.replace(Reg,'$1 <a onclick=\"dlg_grid_table_td_style(this)\">[编]</a> $2');
//				$(this).html(replace_html);
//			}
			//$(this).append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");
		}
	})
}

/**
 * 递归生成由于拖动的上一个文字
 * @param td
 * @param text
 */
function generate_td_title(td, text) {

	var prev_td = $(td).prev();

	if (prev_td.index() >= 0) {
		if  ($(prev_td).is(":hidden")) {
			generate_td_title(prev_td, text);
		} else {
			if ($(prev_td).html() == '') {
				$(prev_td).css('text-align', 'center');
				$(prev_td).html(text+':');
				//9.16
				$(prev_td).append("<a onclick='dlg_grid_table_td_style(this)'>[编]</a>");
			}
		}
	}
}

/**
 * 删除拖动后的对象
 * @param obj
 * @param td_obj
 */
function clean_drop(obj) {

	var td_obj = $(obj).parent().parent();

	$(obj).parent().remove();

	var has_span = $(td_obj).find('span').length;
	if (!has_span) {
		$(td_obj).html($(td_obj).html().replace("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>",''));
		$(td_obj).html($(td_obj).html().replace("<A onclick=dlg_grid_table_td_style(this)>[编]</A>",''));
		$(td_obj).attr('allow_edit', 'true');
	} else {

	}
	var table_id = $(td_obj).parent().parent().parent().attr('id');
	object_customer_print_conf.reset_grid(table_id);
}

/**
 * td样式
 * @param obj
 */
function dlg_grid_table_td_style(obj) {

//	$('#font_size').val(12);
//	$('#text_align').val('center');
//	$('#font_weight').val('normal');
//	$('#padding_left').val("");
//	$('#padding_right').val("");

	//获取每个td之前设置的样式
	var td_obj = $(obj).parent();
	var text = td_obj.html();
	text = text.replace("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>",'');
	text = text.replace("<A onclick=dlg_grid_table_td_style(this)>[编]</A>",'');

	var before_Reg = /^((?:&nbsp;)*)([^;].*?)((?:&nbsp;)*)$/i;
	var f = before_Reg.exec(text);

	var before_nbsp = '';
	var end_nbsp = '';

	if (f) {
		before_nbsp = f[1];
		end_nbsp = f[3];
	}

	var before_nbsp_arr = before_nbsp.split('&nbsp;');
	var before_nbsp_count = before_nbsp_arr.length;
	$('#padding_left').val(before_nbsp_count-1);


	var end_nbsp_arr = end_nbsp.split('&nbsp;');
	var end_nbsp_count = end_nbsp_arr.length;
	$('#padding_right').val(end_nbsp_count-1);


	var font_size = $(td_obj).css('font-size');
	var text_align = $(td_obj).css('text-align');
	var font_weight = $(td_obj).css('font-weight');

	$('#font_size').val(font_size);
	$('#text_align').val(text_align);
	$('#font_weight').val(font_weight);


	jQuery('#dlg_grid_table_td_content_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 300, autoOpen: true,
		title: "文字设置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var font_size = $('#font_size').val();
				var text_align = $('#text_align').val();
				var font_weight = $('#font_weight').val();
				var padding_left = $('#padding_left').val();
				var padding_right = $('#padding_right').val();

				if(padding_left && !$.isNumeric(padding_left)) {
					alert('请输入数字');
					return;
				}

				if(padding_right && !$.isNumeric(padding_right)) {
					alert('请输入数字');
					return;
				}

				save_grid_table_td_style(td_obj,font_size, text_align, font_weight,padding_left, padding_right);
				$(this).dialog('close');
			}
		}
	});
}

/**
 * td样式 12.23
 * @param obj
 */
function dlg_list_table_td_style(table_id, rel_field, is_ext, ext_col) {

//	$('#list_font_size').val(12);
//	$('#list_text_align').val('center');
//	$('#list_font_weight').val('normal');
//	$('#padding_left').val("");
//	$('#padding_right').val("");

	//获取每个td之前设置的样式
//	var td_obj = $(obj).parent();
//	var text = td_obj.html();
	var th_obj = $('#'+table_id).find("th[rel_field='"+rel_field+"']");

	var text = $(th_obj).html();

//	var before_Reg = /^((?:&nbsp;)*)([^;].*?)((?:&nbsp;)*)$/i;
//	var f = before_Reg.exec(text);
//
//	var before_nbsp = '';
//	var end_nbsp = '';
//
//	if (f) {
//		before_nbsp = f[1];
//		end_nbsp = f[3];
//	}

//	var before_nbsp_arr = before_nbsp.split('&nbsp;');
//	var before_nbsp_count = before_nbsp_arr.length;
//	$('#padding_left').val(before_nbsp_count-1);
//
//
//	var end_nbsp_arr = end_nbsp.split('&nbsp;');
//	var end_nbsp_count = end_nbsp_arr.length;
//	$('#padding_right').val(end_nbsp_count-1);


	var font_size = $(th_obj).css('font-size');
	var text_align = $(th_obj).css('text-align');
	var font_weight = $(th_obj).css('font-weight');

	$('#list_font_size').val(font_size);
	$('#list_text_align').val(text_align);
	$('#list_font_weight').val(font_weight);


	jQuery('#dlg_list_table_td_content_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 250, autoOpen: true,
		title: "文字设置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var font_size = $('#list_font_size').val();
				var text_align = $('#list_text_align').val();
				var font_weight = $('#list_font_weight').val();
//				var padding_left = $('#padding_left').val();
//				var padding_right = $('#padding_right').val();

//				if(padding_left && !$.isNumeric(padding_left)) {
//					alert('请输入数字');
//					return;
//				}
//
//				if(padding_right && !$.isNumeric(padding_right)) {
//					alert('请输入数字');
//					return;
//				}

				save_list_table_td_style(table_id,th_obj,is_ext, ext_col,font_size, text_align, font_weight);
				$(this).dialog('close');
			}
		}
	});
}

function dlg_list_table_ext_col_td_style (table_id, rel_field, is_ext, ext_col) {

	var th_obj = $('#'+table_id).find("th[rel_field='"+rel_field+"']");

	var text = $(th_obj).html();

	var font_size = $(th_obj).css('font-size');
	var text_align = $(th_obj).css('text-align');
	var font_weight = $(th_obj).css('font-weight');

	$('#list_font_size').val(font_size);
	$('#list_text_align').val(text_align);
	$('#list_font_weight').val(font_weight);

	jQuery('#dlg_list_table_td_content_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 250, autoOpen: true,
		title: "文字设置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var font_size = $('#list_font_size').val();
				var text_align = $('#list_text_align').val();
				var font_weight = $('#list_font_weight').val();

				save_list_table_ext_col_td_style(table_id,th_obj,is_ext, ext_col,font_size, text_align, font_weight);
				$(this).dialog('close');
			}
		}
	});
}

/**
 * 保存设置
 * @param td_obj
 */
function save_grid_table_td_style(td_obj, font_size, text_align, font_weight, padding_left, padding_right) {

	$(td_obj).css('font-size', font_size);
	$(td_obj).css('text-align', text_align);
	$(td_obj).css('font-weight', font_weight);
//	$(td_obj).css('padding-left', padding_left);
//	$(td_obj).css('padding-right', padding_right);

	$(td_obj).html($(td_obj).html().replace(/&nbsp;/gi,''));

	for(var i=0; i < padding_left; ++i) {
		$(td_obj).prepend("&nbsp;")
	}

	for(var j=0; j < padding_right; ++j) {
		$(td_obj).append("&nbsp;")
	}
}

/**
 * 保存设置 12.23
 * @param td_obj
 */
function save_list_table_td_style(table_id, th_obj, is_ext, ext_col, font_size, text_align, font_weight) {

	$(th_obj).css('font-size', font_size);
	$(th_obj).css('text-align', text_align);
	$(th_obj).css('font-weight', font_weight);

	var th_index = $(th_obj).index();

	var is_ext_col_before = false; //扩展字段是否在配置项之前

	//扩展模版
	if (is_ext) {
		for(var i = 0; i < th_index; ++i) {
			var _t = $("#"+table_id).find('thead').find('tr').find('th').eq(i);
			if ($(_t).attr('rel_field') == ext_col) {
				is_ext_col_before = true;
			}
		}
	}

	$("#"+table_id).children('tbody').find('tr').find('td').each(function() {

		if (is_ext_col_before) {
			if ($(this).index() == th_index + 2) {
				$(this).css('font-size', font_size);
				$(this).css('text-align', text_align);
				$(this).css('font-weight', font_weight);
			}
		} else {
			if ($(this).index() == th_index) {
				$(this).css('font-size', font_size);
				$(this).css('text-align', text_align);
				$(this).css('font-weight', font_weight);
			}
		}
	});
}

/**
 * 保存设置 2014-06-24
 * @param td_obj
 */
function save_list_table_ext_col_td_style(table_id, th_obj, is_ext, ext_col, font_size, text_align, font_weight) {

	$(th_obj).css('font-size', font_size);
	$(th_obj).css('text-align', text_align);
	$(th_obj).css('font-weight', font_weight);

	var th_index = $(th_obj).index();

	$(th_obj).find('table').find('td').css('font-size', font_size).css('text-align', text_align).css('font-weight', font_weight);

	$('#'+table_id).children('tbody').find('tr').find('td').each(function(){
		if ($(this).index() >= th_index && $(this).index() <= th_index + 2) {
			$(this).css('font-size', font_size);
			$(this).css('text-align', text_align);
			$(this).css('font-weight', font_weight);
		}
	})
}

/**
 * td添加双击事件
 */
function add_dbclick_for_td(table_id) {
	$("#" + table_id + " tr td").dblclick(function (e) {

		if ('false' == $(this).attr('allow_edit')) {
			return;
		}

		var inputobj = $("<input type='text'>");
		//获取当前点击的单元格对象
		var thobj = $(this);
		//获取单元格中的文本
		thobj.html(thobj.html().replace("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>",''));
		thobj.html(thobj.html().replace("<A onclick=dlg_grid_table_td_style(this)>[编]</A>",''));

		var text = thobj.html();

		var before_Reg = /^((?:&nbsp;)*)([^;].*?)((?:&nbsp;)*)$/i;
		var f = before_Reg.exec(text);

		var before_nbsp = '';
		var end_nbsp = '';

		if (f) {
			before_nbsp = f[1];
			end_nbsp = f[3];
		}

		thobj.html(thobj.html().replace(/&nbsp;/gi,''));
		var text = thobj.html();

		//如果当前单元格中有文本框，就直接跳出方法
		//注意：一定要在插入文本框前进行判断
		if (thobj.children("input").length > 0) {
			return false;
		}
		//清空单元格的文本
		thobj.html("");

		inputobj.css("border", "0")
			.css("font-size", thobj.css("font-size"))
			.css("font-family", thobj.css("font-family"))
			.css("background-color", thobj.css("background-color"))
			.css("color", "#C75F3E")
			//.width(thobj.width())
			.val(text)
			.appendTo(thobj);

		inputobj.trigger("focus").trigger("select");

		//阻止文本框的点击事件
		inputobj.click(function () {
			return false;
		});

		inputobj.blur(function () {
			//恢复td的文本
			thobj.html(text);

			var inputtext = $.trim($(this).val());

			//将td的内容修改成文本框中的内容
			if (inputtext) {
				thobj.html(inputtext);
				thobj.prepend(before_nbsp);
				thobj.append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");
				thobj.append(end_nbsp);
			} else {
				thobj.html('');
			}

			//删除input
			$(this).remove();
		});

		//处理文本框上回车和esc按键的操作
		//jQuery中某个事件方法的function可以定义一个event参数，jQuery会屏蔽浏览器的差异，传递给我们一个可用的event对象
		inputobj.keyup(function (event) {
			//获取当前按键的键值
			//jQuery的event对象上有一个which的属性可以获得键盘按键的键值
			var keycode = event.which;
			//处理回车的情况
			if (keycode == 13) {
				//获取当前文本框的内容
				var inputtext = $.trim($(this).val());

				//将td的内容修改成文本框中的内容
				if (inputtext) {
					thobj.html(inputtext);
					thobj.prepend(before_nbsp);
					thobj.append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");
					thobj.append(end_nbsp);
				} else {
					thobj.html('');
				}
			}
			//处理esc的情况
			if (keycode == 27) {
				//将td中的内容还原成text
				thobj.html(text);
				thobj.append("<a onclick=\"dlg_grid_table_td_style(this)\">[编]</a>");
			}
		});
	})
}

//左边操作点击展开
<?php foreach($response['danju_print_conf']['main_conf'] as $conf_key=>$conf_val) { ?>
$("#<?php echo $conf_key ?> span").click(function () {
	$("#sm_<?php echo $conf_key ?>").toggle();
})
<?php } ?>

/**
 * 左边控制右边是否显示(grid类型)
 * @param op_id
 */
function op_grid_show_change(op_id, is_top_or_main_or_bottom) {

	var div_print_area = null;
	if ('page_top' == is_top_or_main_or_bottom) {
		div_print_area = 'div_print_page_top_area';
	}
	if ('main_major' == is_top_or_main_or_bottom) {
		div_print_area = 'div_print_main_major_area';
	}
	if ('page_bottom' == is_top_or_main_or_bottom) {
		div_print_area = 'div_print_page_bottom_area';
	}

	if ($("#div_table_" + op_id).length > 0) {
		if ($('#op_ck_' + op_id).attr('checked') == 'checked') {
			$('#div_table_' + op_id).show();
		} else {
			$('#div_table_' + op_id).hide();
		}
	} else {
		$div = $("<div class='' id='div_table_" + op_id + "'></div>");

		$('#'+div_print_area).append($div);
	}

	init_op_list_is_checked();
}

/**
 * 左边控制右边是否显示(list类型)
 * @param op_id
 */
function op_list_show_change(op_id) {

	if ($("#div_table_" + op_id).length > 0) {

		if ($('#op_ck_' + op_id).attr('checked') == 'checked') {
			$('#div_table_' + op_id).show();
		} else {
			$('#div_table_' + op_id).hide();
		}

	} else {
		$div = $("<div  id='div_table_" + op_id + "' class=''><table id='table_" + op_id + "' width='100%' cellspacing='0' cellpadding='0' border='1'><thead><tr></tr></thead><tbody></tbody></table></div>");
		$('#div_print_main_major_area').append($div);
	}

	init_op_list_is_checked();//8.29
}


/**
 * 获取最后一个checkbox
 * @param alias_name
 * @returns {string}
 */
function getLastCheckedByAliasName(alias_name) {

	var obj = '';
	$("input[alias_name=" + alias_name + "][type='checkbox']").each(function () {
		if ($(this).attr('checked') == 'checked') {
			obj = $(this)
		}
	})
	return obj;
}

/**
 * 获取checkbox选中数量
 * @param alias_name
 * @returns {string}
 */
function getCheckedNumByAliasName(alias_name) {

	var num = 0;
	$("input[alias_name=" + alias_name + "][type='checkbox']").each(function () {
		if ($(this).attr('checked') == 'checked') {
			num++;
		}
	})
	return num;
}

/**
 * 获取第一个checkbox
 * @param alias_name
 */
function getFirstCheckboxByAliasName(alias_name) {
	var obj = $("input[alias_name=" + alias_name + "][type='checkbox']").first();
	return obj;
}

/**
 * 平均分配表头th宽度
 * @param table_id
 * @param th_or_td
 */
function avg_alloc_th_width(table_id, cols_num, th_or_td) {

	//默认表格平均宽度
	var _table_width = $('#'+table_id).width();

	var td_width = parseInt(_table_width / cols_num);

	var _old_th_width_plus = 0;//前面th宽度累加
	$("#" + table_id + " thead tr " + th_or_td).each(function ($k, $v) {

		if (($k + 1) == cols_num) {
			$(this).css('width', _table_width - _old_th_width_plus);
		} else {
			_old_th_width_plus += td_width;
			$(this).css('width', td_width);
		}
	})
}

//------------------------------------------------

/**
 * 列表区操作类
 */
var plugin_table_list_area = {

	/**
	 * 上移是否显示
	 * @param chk_obj
	 * @param alias_name
	 * @param is_show  true or fasle
	 */
	toggle_detail_area_moveup_action: function (chk_obj, alias_name, is_show) {
		var chk_obj_val = chk_obj.val();

		var moveup_id = alias_name.replace('ck', 'moveup') + '_' + chk_obj_val;

		if (is_show)
			$("#" + moveup_id).show();
		else
			$("#" + moveup_id).hide();
	},

	/**
	 * 扩展打印上移
	 * @param obj
	 */
	ext_moveUp: function (obj, alias_name, table_id, ext_col) {
		var current = $(obj).parent().parent();
		var prev = current.prev();

		if (current.index() > 0) {
			current.insertBefore(prev);
		}

		//上下移动按钮显示隐藏
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);
		plugin_table_list_area.detail_order_change(current, 'left');

		var chk_num = getCheckedNumByAliasName(alias_name);
	//	avg_alloc_th_width(table_id, chk_num, 'th');

		plugin_table_list_area.ext_simulate_td_change(table_id, ext_col);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);
	},

	/**
	 * 上移
	 * @param obj
	 */
	moveUp: function (obj, alias_name, table_id) {
		var current = $(obj).parent().parent();
		var prev = current.prev();

		if (current.index() > 0) {
			current.insertBefore(prev);
		}

		//上下移动按钮显示隐藏
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);
		plugin_table_list_area.detail_order_change(current, 'left');

		var chk_num = getCheckedNumByAliasName(alias_name);
	//	avg_alloc_th_width(table_id, chk_num, 'th');

		plugin_table_list_area.simulate_td_change(table_id);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);
	},

	/**
	 * 扩展打印下移
	 * @param obj
	 */
	ext_moveDown: function (obj, alias_name, table_id, ext_col) {
		var current = $(obj).parent().parent();
		var next = current.next();
		if (next) {
			current.insertAfter(next);
		}

		//上下移动按钮显示隐藏
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);
		plugin_table_list_area.detail_order_change(current, 'right', table_id);

		var chk_num = getCheckedNumByAliasName(alias_name);
	//	avg_alloc_th_width(table_id, chk_num);

		plugin_table_list_area.ext_simulate_td_change(table_id, ext_col);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);
	},

	/**
	 * 下移
	 * @param obj
	 */
	moveDown: function (obj, alias_name, table_id) {
		var current = $(obj).parent().parent();
		var next = current.next();
		if (next) {
			current.insertAfter(next);
		}

		//上下移动按钮显示隐藏
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);
		plugin_table_list_area.detail_order_change(current, 'right', table_id);

		var chk_num = getCheckedNumByAliasName(alias_name);
	//	avg_alloc_th_width(table_id, chk_num);

		plugin_table_list_area.simulate_td_change(table_id);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);
	},

	/**
	 * 表格左移
	 * @param obj
	 */
	moveLeft: function (obj) {
		var current = obj;

		var prev = current.prev();
		if (current.index() > 0) {
			current.insertBefore(prev);
		}
	},

	/**
	 * 表格右移
	 * @param obj
	 */
	moveRight: function (obj) {

		var current = obj;
		var next = current.next();
		if (next) {
			current.insertAfter(next);
		}
	},

	/**
	 * 扩展明细表头
	 * @param obj
	 * @param table_id
	 * @param op_id
	 * @param rel_field
	 * @param ext_col
	 * @param is_ext_col
	 */
	ext_th_show_change:function(obj, table_id, op_id, rel_field, ext_col, is_ext_col){

		//操作区选中
		op_list_show_change(op_id);
		$('#op_ck_' + op_id).attr('checked', 'checked');

		//当有数据的时候，设置div margin-top
		$('#div_'+table_id).attr('class', 'printTable');

		//12.23 list是否显示配置
		$('#conf_'+op_id+'_'+rel_field).hide();

		var is_check = false;
		if ($(obj).attr("checked") == "checked") {

			is_check = true;
			$(obj).attr("checked", false);
			//12.23 list是否显示配置
			$('#conf_'+op_id+'_'+rel_field).show();
		}

		var chk_num = getCheckedNumByAliasName('ck_' + op_id);

		var is_insert = true;
		if (0 == chk_num && false == is_check) {
			is_insert = false;
		}

		var current_tr = $(obj).parent().parent();

		if (is_insert) {

			if (0 == chk_num) {
				//都没有选中，插入到第一个
				var first_chk_obj = getFirstCheckboxByAliasName('ck_' + op_id);
				if (first_chk_obj.val() != $(obj).val()) {
					var insert_tr = $(first_chk_obj).parent().parent();

					current_tr.insertBefore(insert_tr);
				}
			} else {
				//插入到最后一个之后
				var last_chk_obj = getLastCheckedByAliasName('ck_' + op_id);
				if (last_chk_obj.val() != $(obj).val()) {
					var insert_tr = $(last_chk_obj).parent().parent();
					current_tr.insertAfter(insert_tr);
				}
			}

			if (is_check) {
				$(obj).attr("checked", "checked");
			}
		}

		var tr_id = current_tr.attr("id");

		var td_id = tr_id.replace('tr', 'td');

		var table_detail_th_id = 'table_' + tr_id;
		table_detail_th_id = table_detail_th_id.replace('tr', 'th');

		if (is_check) {
			var th_str = $("#" + td_id).html();

			if (is_ext_col) {
				$th = $("<th style='padding:0;heigth:100%' id='" + table_detail_th_id + "' colspan='3' rel_field='" + rel_field + "'></th>");
			} else {
				$th = $("<th id='" + table_detail_th_id + "' rel_field='" + rel_field + "'>" + th_str + "</th>");
			}
			$("#" + table_id + " thead").children().append($th);

			if (is_ext_col) {

				var ext_col_replace_begin = $("<!--<"+table_id+"_col_replace>-->");
				var ext_col_replace_end = $("<!--</"+table_id+"_col_replace>-->");
				$('#'+table_detail_th_id).append(ext_col_replace_begin);

				$ext_table = $("<table style='width:100%;height:100%' class='inner_table_ext_print_col'><tr><td>S</td><td>M</td><td>L</td></tr><tr><td>160</td><td>170</td><td>180</td></tr></table>");
				$('#'+table_detail_th_id).append($ext_table);
				$('#'+table_detail_th_id).append(ext_col_replace_end);
				$('#'+table_detail_th_id).attr('allow_edit','false');
			}
		} else {
			jQuery("#" + table_detail_th_id).remove();
		}

		var alias_name = $(obj).attr('alias_name');
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);

		avg_alloc_th_width(table_id, chk_num + 1, 'th');

		//模拟列表数据
		plugin_table_list_area.ext_simulate_td_change(table_id, ext_col);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);

		do_table_setting(table_id);
	},

	/**
	 * 明细表格列显示变化
	 * @param table_id
	 * @param conf_area_id 配置数组key(eq.main_area)
	 */
	th_show_change: function (obj, table_id, op_id, rel_field) {

		//操作区选中
		op_list_show_change(op_id);
		$('#op_ck_' + op_id).attr('checked', 'checked');

		//12.23 list是否显示配置
		$('#conf_'+op_id+'_'+rel_field).hide();

		//当有数据的时候，设置div margin-top
		$('#div_'+table_id).attr('class', 'printTable');

		var is_check = false;
		if ($(obj).attr("checked") == "checked") {

			is_check = true;
			$(obj).attr("checked", false);

			//12.23 list是否显示配置
			$('#conf_'+op_id+'_'+rel_field).show();
		}

		var chk_num = getCheckedNumByAliasName('ck_' + op_id);

		var is_insert = true;
		if (0 == chk_num && false == is_check) {
			is_insert = false;
		}

		var current_tr = $(obj).parent().parent();

		if (is_insert) {

			if (0 == chk_num) {
				//都没有选中，插入到第一个
				var first_chk_obj = getFirstCheckboxByAliasName('ck_' + op_id);
				if (first_chk_obj.val() != $(obj).val()) {
					var insert_tr = $(first_chk_obj).parent().parent();

					current_tr.insertBefore(insert_tr);
				}
			} else {
				//插入到最后一个之后
				var last_chk_obj = getLastCheckedByAliasName('ck_' + op_id);
				if (last_chk_obj.val() != $(obj).val()) {
					var insert_tr = $(last_chk_obj).parent().parent();
					current_tr.insertAfter(insert_tr);
				}
			}

			if (is_check) {
				$(obj).attr("checked", "checked");
			}
		}

		var tr_id = current_tr.attr("id");

		var td_id = tr_id.replace('tr', 'td');

		var table_detail_th_id = 'table_' + tr_id;
		table_detail_th_id = table_detail_th_id.replace('tr', 'th');

		if (is_check) {
			var th_str = $("#" + td_id).html();

			$th = $("<th id='" + table_detail_th_id + "' rel_field='" + rel_field + "'>" + th_str + "</th>");
			jQuery("#" + table_id + " thead tr").append($th);
		} else {
			jQuery("#" + table_detail_th_id).remove();
		}

		var alias_name = $(obj).attr('alias_name');
		plugin_table_list_area.toggle_detail_area_move_action(alias_name);

		avg_alloc_th_width(table_id, chk_num + 1, 'th');

		//模拟列表数据
		plugin_table_list_area.simulate_td_change(table_id);

		plugin_table_list_area.add_dbclick_for_table_detail_area_th(table_id);
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		object_customer_print_conf.reset_list(table_id);

		do_table_setting(table_id);
	},


	/**
	 * 扩张列表模拟数据添加
	 * @param table_id
	 */
	ext_simulate_td_change: function (table_id, ext_col) {

		//扩展字段th id
		var ext_col_th_id = table_id+'_th_'+ext_col;

		$('#' + table_id).children('tbody').empty();

		var replace_begin = $("<!--<"+table_id+"_replace>-->");
		var replace_end = $("<!--</"+table_id+"_replace>-->");

		$('#' + table_id).children('tbody').append(replace_begin);

		var simulate_td_count = 3; //模拟数据数量

		for (var i = 0; i < simulate_td_count; ++i) {

			$new_tr = $("<tr></tr>");
			$('#' + table_id + ' th').each(function () {

				var th_id = $(this).attr('id');
				if (th_id == ext_col_th_id) {
					//扩展字段模拟数据
					$new_td1 = $("<td>1</td>");
					$new_td2 = $("<td>2</td>");
					$new_td3 = $("<td>3</td>");
					$new_tr.append($new_td1);
					$new_tr.append($new_td2);
					$new_tr.append($new_td3);
				} else {
					var th_td_val = $(this).html();
					$new_td = $("<td>" + th_td_val + "_" + eval(i + 1) + "</td>");

					//2014-04-02 把th的style赋于td
					$new_td.attr('style', $(this).attr('style'));
					$new_tr.append($new_td);
				}
			})

			$('#' + table_id).children('tbody').append($new_tr);
		}

		$('#' + table_id).children('tbody').append(replace_end);
	},

	/**
	 * 列表模拟数据添加
	 * @param table_id
	 */
	simulate_td_change: function (table_id) {

		$('#' + table_id + ' tbody').empty();

		var replace_begin = $("<!--<"+table_id+"_replace>-->");
		var replace_end = $("<!--</"+table_id+"_replace>-->");

		$('#' + table_id + ' tbody').append(replace_begin);

		var simulate_td_count = 3; //模拟数据数量

		for (var i = 0; i < simulate_td_count; ++i) {

			$new_tr = $("<tr></tr>");
			$('#' + table_id + ' th').each(function () {

				var th_td_val = $(this).html();
				$new_td = $("<td>" + th_td_val + "_" + eval(i + 1) + "</td>");
				//2014-04-02 把th的style赋于td
				$new_td.attr('style', $(this).attr('style'));

				$new_tr.append($new_td);
			})

			$('#' + table_id + ' tbody').append($new_tr);
		}

		$('#' + table_id + ' tbody').append(replace_end);
	},

	/**
	 * 切换显示上下移动操作
	 * @param obj
	 */
	toggle_detail_area_move_action: function (alias_name) {

		var first_chk_obj = getFirstCheckboxByAliasName(alias_name);

		var chk_num = getCheckedNumByAliasName(alias_name);

		var last_chk_obj = null;
		$("input[alias_name=" + alias_name + "][type='checkbox']").each(function () {
			if ($(this).attr('checked') == 'checked' && chk_num > 1) {

				last_chk_obj = $(this);
				plugin_table_list_area.toggle_detail_area_moveup_action($(this), alias_name, true);
				plugin_table_list_area.toggle_detail_area_movedown_action($(this), alias_name, true);
			} else {
				plugin_table_list_area.toggle_detail_area_moveup_action($(this), alias_name, false);
				plugin_table_list_area.toggle_detail_area_movedown_action($(this), alias_name, false);
			}
		})

		if (chk_num <= 1)
			return;

		if ($(first_chk_obj).attr("checked") == "checked") {

			plugin_table_list_area.toggle_detail_area_moveup_action($(first_chk_obj), alias_name, false);
			plugin_table_list_area.toggle_detail_area_movedown_action($(first_chk_obj), alias_name, true);
		}

		if (last_chk_obj.attr("checked") == "checked") {

			plugin_table_list_area.toggle_detail_area_moveup_action(last_chk_obj, alias_name, true);
			plugin_table_list_area.toggle_detail_area_movedown_action(last_chk_obj, alias_name, false);
		}
	},

	/**
	 * 下移是否显示
	 * @param chk_obj
	 * @param alias_name
	 * @param is_show  true or fasle
	 */
	toggle_detail_area_movedown_action: function (chk_obj, alias_name, is_show) {

		var chk_obj_val = chk_obj.val();

		var movedown_id = alias_name.replace('ck', 'movedown') + '_' + chk_obj_val;
		if (is_show)
			$("#" + movedown_id).show();
		else
			$("#" + movedown_id).hide();
	},

	/**
	 * 明细顺序变化（左右顺序）
	 * @param obj
	 * @param orientation
	 */
	detail_order_change: function (obj, orientation) {

		var id = obj.attr('id');
		var new_id = "table_" + id;
		new_id = new_id.replace('tr', 'th');

		var new_obj = jQuery("#" + new_id);

		switch (orientation) {
			case 'left':
				plugin_table_list_area.moveLeft(new_obj);
				break;
			case 'right':
				plugin_table_list_area.moveRight(new_obj);
				break;
		}
	},

	/**
	 * 添加明细表头拖动
	 */
	add_resize_for_table_detail_area: function (table_id) {

		//先禁用
		$("#" + table_id).colResizable({
			disable: true
		});

		//再启用
		$("#" + table_id).colResizable({
			disable: false
		});
	},

	/**
	 * 明细区表头添加双击事件
	 */
	add_dbclick_for_table_detail_area_th: function (table_id) {
		$("#" + table_id + " thead tr th").dblclick(function (e) {

			if ('false' == $(this).attr('allow_edit')) {
				return;
			}

			var inputobj = $("<input type='text'>");
			//获取当前点击的单元格对象
			var thobj = $(this);
			//获取单元格中的文本
			var text = thobj.html();

			//如果当前单元格中有文本框，就直接跳出方法
			//注意：一定要在插入文本框前进行判断
			if (thobj.children("input").length > 0) {
				return false;
			}
			//清空单元格的文本
			thobj.html("");

			inputobj.css("border", "0")
				.css("font-size", thobj.css("font-size"))
				.css("font-family", thobj.css("font-family"))
				.css("background-color", thobj.css("background-color"))
				.css("color", "#C75F3E")
				//.width(thobj.width())
				.val(text)
				.appendTo(thobj);

			inputobj.trigger("focus").trigger("select");

			//阻止文本框的点击事件
			inputobj.click(function () {
				return false;
			});

			inputobj.blur(function () {
//				//恢复td的文本
//				thobj.html(text);
//				//删除input
//				$(this).remove();


				//恢复td的文本
				thobj.html(text);
				var inputtext = $.trim($(this).val());

				//将td的内容修改成文本框中的内容
				if (inputtext) {
					thobj.html(inputtext);
				} else {
					thobj.html('');
				}

				//删除input
				$(this).remove();
			});

			//处理文本框上回车和esc按键的操作
			//jQuery中某个事件方法的function可以定义一个event参数，jQuery会屏蔽浏览器的差异，传递给我们一个可用的event对象
			inputobj.keyup(function (event) {
				//获取当前按键的键值
				//jQuery的event对象上有一个which的属性可以获得键盘按键的键值
				var keycode = event.which;
				//处理回车的情况
				if (keycode == 13) {
					//获取当前文本框的内容
					var inputtext = $(this).val();
					//将td的内容修改成文本框中的内容
					thobj.html(inputtext);

					//改变左边选择区的文字hk
//					var thobj_id = thobj.attr('id');
//					detail_area_id = thobj_id.replace('th', 'td').replace('table_', '');
//					$("#" + detail_area_id).html(inputtext);
				}
				//处理esc的情况
				if (keycode == 27) {
					//将td中的内容还原成text
					thobj.html(text);
				}
			});
		})
	}
}

/**
 * 创建表格
 * @param div_id
 * @param is_top_or_main_or_bottom
 */
function create_table(div_id, is_top_or_main_or_bottom) {

	jQuery('#dlg_create_table').dialog({
		modal: true,
		resizable: false, width: 400, height: 300, autoOpen: true,
		title: "新建表格",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var cols_num = $('#cols_num').val();
				var rows_num = $('#rows_num').val();

				if(!$.isNumeric(cols_num)) {
					alert('请输入数字');
					return;
				}

				if(!$.isNumeric(rows_num)) {
					alert('请输入数字');
					return;
				}

				if (cols_num > 15) {
					alert('列不能大于15');
					return;
				}
				if (rows_num > 15) {
					alert('行不能大于15');
					return;
				}

				plugin_table.create_table(cols_num, rows_num, div_id, is_top_or_main_or_bottom);
				$(this).dialog('close');
			}
		}
	});
}

/**
 * 表格配置对话框
 * @param div_id
 */
function dlg_table_setting(div_id) {

	//2014-09-02 显示上次填写的大小
	$('#dlg_table_setting').find('#table_line_style').val('');
	$('#dlg_table_setting').find('#table_td_height').val('');
	//2014-09-23字体
	$('#dlg_table_setting').find('#table_font_family').val('');
	var table_id = div_id.replace('div_', '');

	var table_setting = $('#'+table_id).attr('custom_table_setting');
	if (table_setting) {
		var table_setting_arr = table_setting.split(';');

		var table_line_style = table_setting_arr[0].split(':')[1];
		var table_td_height = table_setting_arr[1].split(':')[1];

		$('#dlg_table_setting').find('#table_line_style').val(table_line_style);
		$('#dlg_table_setting').find('#table_td_height').val(table_td_height);

		//第4个数组是因为，list内有行高度 2014-09-23
		if (table_setting_arr[3]) {
			var table_font_family = table_setting_arr[3].split(':')[1];
			$('#dlg_table_setting').find('#table_font_family').val(table_font_family);
		}
	}

	$('#dlg_table_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 300, autoOpen: true,
		title: "表格配置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var table_line_style = $('#table_line_style').val();
				var table_td_height = $('#table_td_height').val();
				var table_font_family = $('#table_font_family').val();

				if(table_td_height && !$.isNumeric(table_td_height)) {
					alert('请输入数字');
					return;
				}

				save_table_setting(div_id,table_line_style, table_td_height,'none',table_font_family);
				$(this).dialog('close');
			}
		}
	});
}

/**
 * 表格配置对话框
 * @param div_id
 */
function dlg_page_setting() {

	jQuery('#dlg_page_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 300, autoOpen: true,
		title: "总体设置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var page_zoom = $('#page_zoom').val();

				if(page_zoom && !$.isNumeric(page_zoom)) {
					alert('请输入数字');
					return;
				}

				var page_top_offset = $('#page_top_offset').val();
				if (page_top_offset && !$.isNumeric(page_top_offset)) {
					alert('请输入数字!');
					return;
				}

				var page_left_offset = $('#page_left_offset').val();
				if (page_left_offset && !$.isNumeric(page_left_offset)){
					alert('请输入数字!');
					return;
				}

				var url = "?app_act=sys/danju_print/do_save_page_setting&app_fmt=json&app_page=null";
				var params = {
					'danju_print_code': '<?php echo $response['danju_print_code']?>',
					'page_zoom': page_zoom,
					'page_top_offset' : page_top_offset,
					'page_left_offset' : page_left_offset,
					'shop_code':shop_code
				};

				$.post(url, params, function (data) {
					var ret = $.parseJSON(data);
//					alert(data.message);
					alert('修改整体缩放比例后，请重新保存！');
					window.location.reload();
				});
				$(this).dialog('close');
			}
		}
	});
}

/**
 * 表格配置对话框
 * @param div_id
 */
function dlg_list_table_setting(div_id) {

	//2014-09-02 显示上次填写的大小
	var table_id = div_id.replace('div_', '');

	$('#dlg_list_table_setting').find('#list_table_line_style').val('');
	$('#dlg_list_table_setting').find('#list_table_td_height').val('');
	$('#dlg_list_table_setting').find('#list_table_th_height').val('');
	//2014-09-23字体
	$('#dlg_list_table_setting').find('#list_table_font_family').val('');
	var table_setting = $('#'+table_id).attr('custom_table_setting');
	if (table_setting) {
		var table_setting_arr = table_setting.split(';');

		var table_line_style = table_setting_arr[0].split(':')[1];
		var table_td_height = table_setting_arr[1].split(':')[1];
		var table_th_height = table_setting_arr[2].split(':')[1];

		$('#dlg_list_table_setting').find('#list_table_line_style').val(table_line_style);
		$('#dlg_list_table_setting').find('#list_table_td_height').val(table_td_height);
		$('#dlg_list_table_setting').find('#list_table_th_height').val(table_th_height);
		//第4个数组是因为，list内有行高度 2014-09-23
		if (table_setting_arr[3]) {
			var table_font_family = table_setting_arr[3].split(':')[1];
			$('#dlg_list_table_setting').find('#list_table_font_family').val(table_font_family);
		}
	}


	jQuery('#dlg_list_table_setting').dialog({
		modal: true,
		resizable: false, width: 400, height: 300, autoOpen: true,
		title: "表格配置",
		buttons: {
			"取消": function () {
				$(this).dialog('close');
			},
			"确定": function () {
				var table_line_style = $('#list_table_line_style').val();
				var table_td_height = $('#list_table_td_height').val();
				var table_th_height = $('#list_table_th_height').val();
				var table_font_family = $('#list_table_font_family').val();

				if(table_td_height && !$.isNumeric(table_td_height)) {
					alert('请输入数字');
					return;
				}

				if(table_th_height && !$.isNumeric(table_th_height)) {
					alert('请输入数字');
					return;
				}

				save_table_setting(div_id,table_line_style, table_td_height,table_th_height,table_font_family);
				$(this).dialog('close');
			}
		}
	});
}

function do_table_setting(table_id) {

	var table_setting = $('#'+table_id).attr('custom_table_setting');

	if (!table_setting) {
		return;
	}

	var table_setting_arr = table_setting.split(';');

	var table_line_style = table_setting_arr[0].split(':')[1];
	var table_td_height = table_setting_arr[1].split(':')[1];
	var table_th_height = table_setting_arr[2].split(':')[1];

	//2014-09-23
	var table_font_family = table_setting_arr[3].split(':')[1];


	var border_color = 'black';
	var border_width = '1px';

	switch(table_line_style){
		case 'none':
			border_color = 'transparent';
			border_width = '0px';
			break;
	}

	$("#" + table_id).css('border-color', border_color);
	$("#" + table_id).css('border-width', border_width);
	$("#" + table_id).css('border-style', table_line_style);

	$("#" + table_id).css('font-family', table_font_family);

	$("#" + table_id + " td").each(function () {
		$(this).css('border-color', border_color);
		$(this).css('border-width', border_width);
		$(this).css('border-style', table_line_style);
		$(this).css('height', table_td_height);
	})

	$("#" + table_id + " th").each(function () {
		$(this).css('border-color', border_color);
		$(this).css('border-width', border_width);
		$(this).css('border-style', table_line_style);
		if (table_th_height) {
			$(this).css('height', table_th_height);
		}
	})
}

/**
 * 执行表格配置
 * @param div_id
 * @param table_line_style
 * @param table_line_height
 * @param table_font_family
 */
function save_table_setting(div_id,table_line_style,table_td_height,table_th_height,table_font_family) {

	var table_id = div_id.replace('div_', '');

	$('#'+table_id).attr('custom_table_setting', 'table_line_style:'+table_line_style+';table_td_height:'+table_td_height+';table_th_height:'+table_th_height+';table_font_family:'+table_font_family);
	do_table_setting(table_id);
}

function before_add_col(table_id, op_area_id) {

	add_col(table_id)

	//添加行重新绑定事件
	var col_num = $("#" + table_id + " tr").eq(0).find('td').length;
	avg_alloc_th_width(table_id, col_num, 'td');
	//添加编辑动作
	add_dbclick_for_td(table_id);
	//绑定拖动
	bind_drop(op_area_id, table_id);

	//绑定拖动大小table
	plugin_table_list_area.add_resize_for_table_detail_area(table_id);

	do_table_setting(table_id);
}

/**
 * 增加列
 * @param table_id
 */
function add_col(table_id) {

	$col = $("<td></td>");
	$("#" + table_id + " tr").append($col);
}

/**
 * 删除列(最后一列)
 * @param table_id
 */
function del_col(table_id) {
	//移除最后一列
	$("#" + table_id + " tr > td:last-child").remove();
}

/**
 * 添加row前的准备
 * @param table_id
 * @param op_area_id
 */
function before_add_row(table_id, op_area_id) {

	add_row(table_id);

	//添加行重新绑定事件
	var col_num = $("#" + table_id + " tr").eq(0).find('td').length;
	avg_alloc_th_width(table_id, col_num, 'td');
	//添加编辑动作
	add_dbclick_for_td(table_id);
	//绑定拖动
	bind_drop(op_area_id, table_id);

	//绑定拖动大小table
	plugin_table_list_area.add_resize_for_table_detail_area(table_id);

	do_table_setting(table_id);
}

/**
 * 添加行
 * @param table_id
 */
function add_row(table_id) {
	var col_num = $("#" + table_id + " tr").eq(0).find('td').length;

	$tr = $("<tr></tr>");
	for (var i = 0; i < col_num; ++i) {
		$td = $("<td></td>");
		$tr.append($td);
	}
	$("#" + table_id).append($tr);
}

/**
 * 删除行最后一行
 * @param table_id
 */
function del_row(table_id) {
	//移除最后一行
	$("#" + table_id + " tr:last").remove();
}

/**
 * 合并单元格准备动作
 * @param table_id
 * @param op_area_id
 */
function before_merge(table_id, op_area_id) {

	$td_ck = $("<input type='checkbox' alias_name='ck_" + table_id + "_td'>");
	$("#" + table_id + " tr td").append($td_ck);

	$("#" + table_id + '_before_merge').hide();
	$("#" + table_id + '_exec_merge').show();
	$("#" + table_id + '_cancel_merge').show();
}

/**
 * 执行合并
 * @param table_id
 * @param op_area_id
 */
function exec_merge(table_id, op_area_id) {

	//选中个数
	var ck_num = $("#" + table_id).find(":checkbox:checked").length;

	$("#" + table_id + " tr td").find(":checkbox:checked").each(function () {
		if ($(this).attr('checked') == 'checked') {
			$td = $(this).parent();
			$tr = $td.parent();

			$tr_index = $tr.index();
			$td_index = $td.index();
		}
	})

	merge(table_id);
	merge(table_id);

	//绑定拖动
	bind_drop(op_area_id, table_id);

	//绑定拖动大小table
	plugin_table_list_area.add_resize_for_table_detail_area(table_id);
}

/**
 * 单元格合并
 * @param table_id
 */
function merge(table_id) {

	var totalRows = $("#" + table_id).find("tr").length;

	//行合并
	for (var i = 0; i < totalRows; ++i) {

		var totalCols = $("#" + table_id).find("tr").eq(i).find("td").length;

		for (var j = 0; j < totalCols; ++j) {

			$start_td_cell = $("#" + table_id).find('tr').eq(i).find('td').eq(j);
			$start_td_ck = $start_td_cell.find("input[type=checkbox]");
			if ($start_td_ck.attr('checked') == 'checked') {

				var start_td_col_val = parseInt($start_td_cell.attr('colspan'));

				if (!start_td_col_val) {
					start_td_col_val = 1;
				}

				var start_td_row_val = parseInt($start_td_cell.attr('rowspan'));

				if (!start_td_row_val) {
					start_td_row_val = 1;
				}

				var next_td_cell_index = j + start_td_col_val;
				if (next_td_cell_index >= totalCols)
					continue;

				//相邻的checkbox
				$next_td_cell = $("#" + table_id).find('tr').eq(i).find('td').eq(next_td_cell_index);
				$next_td_ck = $next_td_cell.find("input[type=checkbox]");

				if ($next_td_ck.attr('checked') == 'checked') {

					var next_td_row_val = parseInt($next_td_cell.attr('rowspan'));

					if (!next_td_row_val) {
						next_td_row_val = 1;
					}
					var next_td_col_val = parseInt($next_td_cell.attr('colspan'));
					if (!next_td_col_val) {
						next_td_col_val = 1;
					}

					if (start_td_row_val != next_td_row_val) {
						continue;
					}

					$start_td_cell.width($start_td_cell.width() + $next_td_cell.width());

					$start_td_cell.attr("colspan", eval(start_td_col_val + next_td_col_val));
					$next_td_cell.attr("colspan", 1);
					$next_td_ck.removeAttr('checked');
					$next_td_cell.hide();
					--j;//索引还是从原来的开始
				}
			}
		}
	}


	//合并列
	for (var i = 0; i < totalRows; ++i) {

		var totalCols = $("#" + table_id).find("tr").eq(i).find("td").length;
		for (var j = 0; j < totalCols; ++j) {

			$start_td_cell = $("#" + table_id).find('tr').eq(i).find('td').eq(j);
			$start_td_ck = $start_td_cell.find("input[type=checkbox]");

			if ($start_td_ck.attr('checked') == 'checked') {

				var start_td_row_val = parseInt($start_td_cell.attr('rowspan'));
				if (!start_td_row_val) {
					start_td_row_val = 1;
				}

				var start_td_col_val = parseInt($start_td_cell.attr('colspan'));
				if (!start_td_col_val) {
					start_td_col_val = 1;
				}

				var next_td_cell_index = i + start_td_row_val;
				if (next_td_cell_index >= totalRows)
					continue;

				$next_td_cell = $("#" + table_id).find('tr').eq(next_td_cell_index).find('td').eq(j);

				$next_td_ck = $next_td_cell.find("input[type=checkbox]");

				if ($next_td_ck.attr('checked') == 'checked') {

					var next_td_row_val = parseInt($next_td_cell.attr('rowspan'));
					if (!next_td_row_val) {
						next_td_row_val = 1;
					}

					var next_td_col_val = parseInt($next_td_cell.attr('colspan'));
					if (!next_td_col_val) {
						next_td_col_val = 1;
					}

					if (start_td_col_val != next_td_col_val) {
						continue;
					}

					$start_td_cell.attr("rowspan", eval(start_td_row_val + next_td_row_val));
					$next_td_cell.attr("rowspan", 1);
					$next_td_ck.removeAttr('checked');
					$next_td_cell.hide();
					--i;
				}
			}
		}
	}
}

/**
 * 取消合并
 * @param table_id
 * @param op_arae_id
 */
function cancel_merge(table_id, op_area_id) {

	$("#" + table_id + " tr td").find("input[type=checkbox]").remove();

	$("#" + table_id + '_before_merge').show();
	$("#" + table_id + '_exec_merge').hide();
	$("#" + table_id + '_cancel_merge').hide();

	//绑定拖动
	bind_drop(op_area_id, table_id);

	//绑定拖动大小table
	plugin_table_list_area.add_resize_for_table_detail_area(table_id);
}

/**
 * 恢复合并
 * @param table_id
 * @param op_area_id
 */
function restore_merge(table_id, op_area_id) {

	cancel_merge(table_id, op_area_id);
	$("#" + table_id + " tr td").attr('rowspan', 1).attr('colspan', 1).show();


	var colCount = $("#" + table_id).find("tr").eq(0).find("td").length;
	avg_alloc_th_width(table_id, colCount, 'td');

	//绑定拖动
	bind_drop(op_area_id, table_id);

	//绑定拖动大小table
	plugin_table_list_area.add_resize_for_table_detail_area(table_id);
}

var plugin_table = {

	/**
	 * 创建表格
	 * @param colCount
	 * @param rowCount
	 * @param div_id
	 */
	create_table: function (colCount, rowCount, div_id, is_top_or_main_or_bottom) {

		var div_print_area = null;
		if ('page_top' == is_top_or_main_or_bottom) {
			div_print_area = 'div_print_page_top_area';
		}
		if ('main_major' == is_top_or_main_or_bottom) {
			div_print_area = 'div_print_main_major_area';
		}
		if ('page_bottom' == is_top_or_main_or_bottom) {
			div_print_area = 'div_print_page_bottom_area';
		}

		if ($('#' + div_id).length <= 0) {
			$div = $("<div id='" + div_id + "' class='printTable'></div>");
			$('#'+div_print_area).append($div);
		}

		//当有数据的时候，设置div margin-top
		$('#'+div_id).attr('class', 'printTable');

		//清空原有table
		$("#" + div_id).empty();

		var op_area_ck_id = div_id.replace('div_table_', 'op_ck_');
		$('#' + op_area_ck_id).attr('checked', 'checked');

		var op_area_id = div_id.replace('div_table_', 'ul_sm_');
		var table_id = div_id.replace('div_', '');

		var replace_begin = $("<!--<replace_empty>-->");
		var replace_end = $("<!--</replace_empty>-->");

		$op_table_col_div = $("<div id='op_col_row_" + table_id + "'><input type='button' value='增加列' class='op_table_col' onclick=\"before_add_col('" + table_id + "','" + op_area_id + "')\"><input type='button' value='删除列' class='op_table_col' onclick=\"del_col('" + table_id + "')\"><input type='button' value='增加行' class='op_table_col' onclick=\"before_add_row('" + table_id + "','" + op_area_id + "')\"><input type='button' value='删除行' class='op_table_col' onclick=\"del_row('" + table_id + "')\"></div>");
		$op_merge_div = $("<div id='op_merge_" + table_id + "'><input type='button' id='" + table_id + "_before_merge' value='合并单元格' class='op_table_col' onclick=\"before_merge('" + table_id + "','" + op_area_id + "')\"><input type='button' id='" + table_id + "_exec_merge' style='display: none' value='执行合并' class='op_table_col' onclick=\"exec_merge('" + table_id + "','" + op_area_id + "')\"><input type='button' id='" + table_id + "_cancel_merge' style='display: none' value='取消合并' class='op_table_col' onclick=\"cancel_merge('" + table_id + "','" + op_area_id + "')\"><input type='button' id='" + table_id + "_restore_merge' value='恢复合并' class='op_table_col' onclick=\"restore_merge('" + table_id + "','" + op_area_id + "')\"></div>");

		$("#" + div_id).append(replace_begin).append($op_table_col_div).append($op_merge_div).append(replace_end);

		var table = $("<table id='" + table_id + "' width='100%'></table>");
		//$("#" + div_id).append(table);

		for (var i = 0; i < rowCount; i++) {
			var tr = $("<tr></tr>");
			tr.appendTo(table);
			for (var j = 0; j < colCount; j++) {
				var td = $("<td allow_edit='true' style='padding:0px; margin:0px;'></td>");
				tr.append(td);
			}
			table.append(tr);
		}
		$("#" + div_id).append(table);

		avg_alloc_th_width(table_id, colCount, 'td');

		//添加编辑动作
		add_dbclick_for_td(table_id);
		//绑定拖动
		bind_drop(op_area_id, table_id);

		//绑定拖动大小table
		plugin_table_list_area.add_resize_for_table_detail_area(table_id);

		init_op_list_is_checked();//8.29
	}
};

//用户配置
var object_customer_print_conf = {

	remove: function (obj, key) {
		delete obj[key];
	},

	contains: function (obj, key) {
		return typeof (obj[key]) != "undefined";
	},

	add: function (obj, key, value) {
		obj[key] = typeof (value) == "undefined" ? null : value;
	},

	reset_list: function (table_id) {

		var key = table_id.replace('table_','');

		customer_print_conf[key] = new Object();
		$('#' + table_id + ' th').each(function () {

			if ($(this).attr('rel_field')) {
				object_customer_print_conf.add(customer_print_conf[key], $(this).attr('rel_field'), $(this).attr('rel_field'));
			}
		});
	},

	reset_grid: function (table_id){

		var key = table_id.replace('table_','');
		customer_print_conf[key] = new Object();
		$('#' + table_id + ' td span').each(function () {

			if ($(this).attr('rel_field')) {
				object_customer_print_conf.add(customer_print_conf[key], $(this).attr('rel_field'), $(this).attr('rel_field'));
			}
		});
	}
}

/**
 * 保存前处理
 */
function prev_save() {

	var danju_print_content = $('#danju_print_content').html();

	$('#_t_print_content').html(danju_print_content);

	$("#_t_print_content").find('.CRC').remove();
	$("#_t_print_content").find('.CRL').remove();
	$("#_t_print_content").find('.CRG').remove();

	$("#_t_print_content").find('.ui-droppable').removeClass('ui-droppable');
	$("#_t_print_content").find('.ui-draggable').removeClass('ui-draggable');
	$("#_t_print_content").find('.CRZ').removeClass('CRZ');

	//页面的宽度
	$page_width = $("#danju_print_content").width();

	//设置页眉表格的宽度
	$("#_t_print_content").find('#div_print_page_top_area').find('table').removeAttr('width');//移除width属性，使用style的width
	$("#_t_print_content").find('#div_print_page_top_area').find('table').css('width', $page_width);

	//设置主区域表格宽度
	$("#_t_print_content").find('#div_print_main_major_area').find('table').removeAttr('width');//移除width属性，使用style的width
	$("#_t_print_content").find('#div_print_main_major_area').find('table').css('width', $page_width);

	//设置页尾表格宽度
	$("#_t_print_content").find('#div_print_page_bottom_area').find('table').removeAttr('width');//移除width属性，使用style的width
	$("#_t_print_content").find('#div_print_page_bottom_area').find('table').css('width', $page_width);

	//删除（有"[编]"的元素）
	$("#_t_print_content").find("a[onclick='dlg_grid_table_td_style(this)']").remove();
	$("#_t_print_content").find("a[onclick='clean_drop(this)']").remove();

	//样式保存
	var obj_print_html_style = new Object();

	<?php foreach($response['danju_print_conf']['main_conf'] as $conf_key => $conf_val) { ?>

		<?php if ('list' == $conf_val['type']) { ?>
			//清除列表类型td的宽度，使用th的宽度
		//	$("#_t_print_content").find("#div_print_main_major_area").find("#table_<?php echo $conf_key ?>").find('td').css('width','');

			$('#_t_print_content').find("#table_<?php echo $conf_key?>").find('tbody').empty();
			$('#_t_print_content').find("#table_<?php echo $conf_key?>").find('tbody').html("<!--<table_<?php echo $conf_key?>_replace>--><!--</table_<?php echo $conf_key?>_replace>-->");

			var _obj_style = new Object();
			_obj_style['type'] = 'list';

			//table的
			var __obj_table = new Object();
			__obj_table['style'] = $('#_t_print_content').find("#table_<?php echo $conf_key ?>").attr('style');
			__obj_table['custom_table_setting'] = $('#_t_print_content').find("#table_<?php echo $conf_key ?>").attr('custom_table_setting');
			_obj_style['table'] = __obj_table;

			//th的
			var __obj_th = new Object();
			$('#_t_print_content').find('#table_<?php echo $conf_key?>').find('th').each(function(){
				var rel_field = $(this).attr('rel_field');
				if (rel_field) {
					__obj_th[rel_field] = $(this).attr('style');
				}
			});
			_obj_style['th'] = __obj_th;

			obj_print_html_style['<?php echo $conf_key ?>'] = _obj_style;
		<?php } ?>

		<?php if ('grid' == $conf_val['type']) { ?>

			//去处gird合并表格操作部分
			$('#_t_print_content').find('#op_col_row_table_<?php echo $conf_key?>').remove();
			$('#_t_print_content').find('#op_merge_table_<?php echo $conf_key?>').remove();

			var _obj_style = new Object();
			_obj_style['type'] = 'grid';

			//table的
			var __obj_table = new Object();
			//table的style
			__obj_table['style'] = $('#_t_print_content').find("#table_<?php echo $conf_key ?>").attr('style');
			__obj_table['custom_table_setting'] = $('#_t_print_content').find("#table_<?php echo $conf_key?>").attr('custom_table_setting');
			_obj_style['table'] = __obj_table;

			//td的
			var __obj_td = new Object();
			$('#_t_print_content').find("#table_<?php echo $conf_key ?>").find('td').each(function(){

				$(this).attr('rel_field','');
				var rel_field = $(this).find('span').attr('rel_field');//关联字段保存在span中
				if (rel_field) {
					$(this).attr('rel_field', rel_field);
					__obj_td[rel_field] = $(this).attr('style');
				}
			});
			_obj_style['td'] = __obj_td;

			obj_print_html_style['<?php echo $conf_key?>'] = _obj_style;
		<?php } ?>
	<?php } ?>

	return obj_print_html_style;
}

</script>


<!-- 保存前处理 -->
<div id="_t_print_content" style="display:none">

</div>