<?php echo load_js('comm_util.js') ?>
<style>
   #record_time_start, #record_time_end { width: 100px;}
</style>

<?php render_control('PageHead', 'head1',array('title' => '商品滞销分析','ref_table' => 'table',));?>
<?php
$keyword_type = array();
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';

$record_time_end = date("Y-m-d ").'23:59:59';
$record_time_start = date("Y-m-d ",strtotime("-1 month")).'00:00:00';
$keyword_type = array_from_dict($keyword_type);

$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit',
    ),
    array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),
);

render_control('SearchForm', 'searchForm', array(
	'buttons' => $buttons,
	'fields' =>array(
		array(
                    'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
                    'type' => 'input',
                    'title' => '',
                    'data' => $keyword_type,
                    'id' => 'keyword',
		),
		array(
                    'label' => '时间',
                    'type' => 'group',
                    'field' => 'daterange1',
                    'child' => array(
                        array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
                        array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
                    ),
		),
		
	),
));
?>

<?php
render_control('DataTable', 'table', array(
	'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '图片',
                    'field' => 'pic',
                    'width' => '100',
                    'align' => '',
                ),
                array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '200',
                        'align' => '',
                ),
                array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品编码',
                        'field' => 'goods_code',
                        'width' => '200',
                        'align' => '',
                ),
                array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品颜色',
                        'field' => 'spec1_name',
                        'width' => '100',
                        'align' => '',
                ),
                array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品尺码',
                        'field' => 'spec2_name',
                        'width' => '100',
                        'align' => '',
                ),
                array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条码',
                        'field' => 'barcode',
                        'width' => '200',
                        'align' => '',
                ),
            )
	),
	'dataset' => 'oms/SellRecordModel::get_unsalable_report',
	'queryBy' => 'searchForm',
	'idField' => 'sell_record_code',
	'export' => array('id' => 'exprot_list', 'conf' => 'unsalable_report_do_list', 'name' => '商品滞销分析'),
        'init' => 'nodata'
));
?>
<div style='margin-top:20px;'>
    <span style='color: red;font-size: 15px;font-weight: bold'>温馨提示：仅显示商品在平台在售且检索时间内（默认为一个月）未产生订单的商品数据。</span>
</div>
<script>
    $("#record_time_start").val("<?php echo $record_time_start?>");
    $("#record_time_end").val("<?php echo $record_time_end?>");
</script>

