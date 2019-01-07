<?php echo_print_plugin() ?>
<?php render_control('PageHead', 'head1',
	array('title' => '打印设置',
		'ref_table' => 'table'
	));
?>

<?php
render_control('DataTable', 'table', array(
	'conf' => array(
		'list' => array(
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '打印模版类型',
				'field' => 'print_data_type_name',
				'width' => '100%',
				'align' => 'center'
			),
		)
	),
	'dataset' => 'sys/DanjuPrintModel::get_print_data_type_by_page',
//	'queryBy' => 'searchForm',
	'idField' => 'print_id',

	'CascadeTable'=>array(
		'list'=>array(
			array('title'=>'模板名称', 'field'=>'danju_print_name'),
			array('title'=>'纸张类型', 'field'=>'template_page_style'),
			array('title'=>'纸张宽度', 'field'=>'template_page_width'),
			array('title'=>'纸张高度', 'field'=>'template_page_height'),
			array('title'=>'打印机', 'field'=>'printer_name'),
			array('title'=>'默认', 'field'=>'is_default','format'=>array('map', ds_get_field('is_default'))),
			array (
				'type' => 'button',
				'show' => 1,
				'title' => '操作',
				'field' => '_operate',
				'width' => '300',
				'align' => '',
				'buttons' => array (
					array('id'=>'view', 'title' => '查看',),
					array('id'=>'set_pager', 'title' => '设置纸张',),
					array('id'=>'set_default', 'title' => '设置默认',),
					array('id'=>'modify_printer', 'title' => '修改打印机',),
				)
			)
		),
		'page_size'=>10,
		'url'=>get_app_url('sys/danju_print/get_list_by_type'),
		'params'=>'print_data_type'
	),
));
?>

<script type="text/javascript">

	/**
	 * 展开列表回调动作
	 */
	function tableCascadeTableCallback(_index, _row, _this, _grid, _store) {

		switch($(_this).attr('es_btn_id')) {
			case "view":
				openPage('',"<?php echo get_app_url("sys/danju_print/edit_print&danju_print_code=")?>"+_row.danju_print_code,_row.danju_print_name);
				break;
			case "set_pager":
				set_pager(_row.print_id);
				break;
			case "set_default":
				set_default(_row.print_id, _row.print_data_type);
				break;
			case "modify_printer":
				modify_printer(_row.print_id);
				break;
		}
	}

	/**
	 * 设置默认
	 */
	function set_default(print_id,print_data_type) {

		var params = {};
		params.print_id = print_id;
		params.print_data_type = print_data_type;

		$.post('?app_act=sys/danju_print/set_default&app_fmt=json', params, function(data) {
			var ret=$.parseJSON(data);
			alert(ret.message);
			window.location.reload();
		});
	}

	/**
	 *  设置纸张
	 */
	function set_pager(print_id) {

		var _url = "?app_act=sys/danju_print/set_page_style&app_scene=edit&print_id=" + print_id;

		var _title = '设置纸张';
		var _opts = new Array();
		_opts.w = 550;
		_opts.h = 450;

		new ESUI.PopWindow(_url, {
			title: _title,
			width:_opts.w,
			height:_opts.h
		}).show();
	}

	/**
	 * 修改打印机
	 * @param print_id
	 */
	function modify_printer(print_id) {

		var LODOP = getLodop();
		var printer_count = LODOP.GET_PRINTER_COUNT();
		if (printer_count < 1) {
			alert('该系统未安装打印设备,请添加相应的打印设备');
			return;
		}
		//选择打印机
		var p = LODOP.SELECT_PRINTER();
		if (p < 0) {
			return;
		}
		//获取打印机名称
		var printer_name = LODOP.GET_PRINTER_NAME(p);
		var params = {print_id:print_id,printer_name:printer_name};

		$.post('?app_act=sys/danju_print/modify_printer&app_fmt=json', params, function(data) {
			var ret=$.parseJSON(data);
			alert(ret.message);
			window.location.reload();
		});
	}

</script>