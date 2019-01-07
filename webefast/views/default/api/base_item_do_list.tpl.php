<style type="text/css">
	.well {
		min-height: 100px;
	}
</style>

<?php
render_control('PageHead', 'head1', array(
	'title' => '淘宝商品管理',
	'links' => array(
		array('url' => 'api/base_item/dl_taobao_items&app_scene=add', 'title' => '下载商品', 'is_pop' => true, 'pop_size' => '500,500'),
		array('url' => 'api/base_item/batch_store_synchro&app_scene=add', 'title' => '库存同步', 'is_pop' => true, 'pop_size' => '500,500'),
//		array('url' => 'api/base_item/one_click_relation_goods&app_scene=add', 'title' => '一键关联商品', 'is_pop' => true, 'pop_size' => '500,200'),
	//	array('url' => 'api/base_item/one_click_create_goods&app_scene=add', 'title' => '一键建档', 'is_pop' => true, 'pop_size' => '500,200'),
	),
	'ref_table' => 'table'
));
?>

<?php
//状态
$approve_status = array('' => '全部',
		'0' => '在售',
		'1' => '在库',
);
$approve_status = array_from_dict($approve_status);
//库存同步
$is_synckc = array('' => '全部',
		'0' => '否',
		'1' => '是',
);
$is_synckc = array_from_dict($is_synckc);
//物流宝
$is_wlb = array('' => '全部',
		'0' => '否',
		'1' => '是',
         );
$is_wlb = array_from_dict($is_wlb);
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
		    array (
		    		'label' => '商品标题',
		    		'type' => 'input',
		    		'id' => 'title',
		    ),
		    array (
		    		'label' => '商品外部ID',
		    		'type' => 'input',
		    		'id' => 'outer_id',
		    ),
		    array ('label' => '状态',
		    		'type' => 'select',
		    		'id' => 'approve_status',
		    		'data' => $approve_status,
		    ),
		    array (
			    'label' => '店铺',
			    'type' => 'select_multi',
			    'id' => 'shop_code',
			    'data' => ds_get_select('shop'),
		    ),
//		    array (
//		    		'label' => 'SKU ID',
//		    		'type' => 'input',
//		    		'id' => 'sku_id',
//		    ),
//		    array (
//		    		'label' => '库存同步',
//		    		'type' => 'select',
//		    		'id' => 'is_synckc',
//		    		'data'=>$is_synckc,
//		    ),
//		    array (
//		    		'label' => 'sku关联',
//		    		'type' => 'select',
//		    		'id' => 'aa',
//		    		'data'=>$response['prop'],
//		    ),
//		    array (
//		    		'label' => '移除',
//		    		'type' => 'select',
//		    		'id' => 'aa',
//		    		'data'=>$response['prop'],
//		    ),
//    		array (
//    				'label' => '物流宝',
//    				'type' => 'select',
//    				'id' => 'is_wlb',
//    				'data'=>$is_wlb,
//    		),
    ) 
) );
?>

<ul class="toolbar">
	<li><button class="button button-success" id="batch_listing">批量上架</button></li>
	<li><button class="button button-success" id="batch_delisting">批量下架</button></li>
</ul>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品主图',
                'field' => 'pic_url',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商店',
                'field' => 'shop_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '商品标题',
            		'field' => 'title',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '商品编码',
            		'field' => 'base_item_outer_id',
            		'width' => '100',
            		'align' => ''
            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '商品数字ID',
//            		'field' => '',
//            		'width' => '100',
//            		'align' => ''
//            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '数量',
            		'field' => 'base_item_quantit',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '库存计数',
            		'field' => 'with_hold_quantity',
            		'width' => '100',
            		'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '状态',
            		'field' => 'approve_status',
            		'width' => '100',
            		'align' => '',
	                'format'=>array('map', ds_get_field('approve_status'))
            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => 'SKU数字ID',
//            		'field' => 'sku_id',
//            		'width' => '100',
//            		'align' => ''
//            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '商家编码',
//            		'field' => 'base_sku_outer_id',
//            		'width' => '100',
//            		'align' => ''
//            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '数量',
//            		'field' => 'base_sku_quantit',
//            		'width' => '100',
//            		'align' => ''
//            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '价格',
//            		'field' => 'price',
//            		'width' => '100',
//            		'align' => ''
//            ),
//            array (
//            		'type' => 'text',
//            		'show' => 1,
//            		'title' => '状态',
//            		'field' => 'status',
//            		'width' => '100',
//            		'align' => ''
//            ),
//            array (
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '300',
//                'align' => '',
//                'buttons' => array (
//
//                   array('id'=>'synchro', 'title' => '库存同步', 'callback'=>'do_synchro'),
//
//                ),
//            )
        ) 
    ),
    'dataset' => 'api/BaseItemModel::get_main_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,

	'CascadeTable'=>array(
		'list'=>array(
			array('title'=>'商家编码', 'field'=>'outer_id'),
			array('title'=>'数量', 'field'=>'quantit'),
			array('title'=>'价格', 'field'=>'price'),
			array('title'=>'状态', 'field'=>'item_sku_status', 'format'=>array('map', ds_get_field('item_sku_status'))),
			array (
				'type' => 'button',
				'show' => 1,
				'title' => '操作',
				'field' => '_operate',
				'width' => '300',
				'align' => '',
				'buttons' => array (
					array('id'=>'sku_synchro', 'title' => '库存同步',),
				)
			)
		),
		'page_size'=>10,
		'url'=>get_app_url('api/base_item/get_sku_list_by_item_id'),
		'params'=>'base_item_id'
	),
) );
?>

<script type="text/javascript">

    /**
     * 库存同步
     * @param _index
     * @param row
     */
	function do_synchro(_index, row) {

		var url = '?app_act=api/base_item/store_synchro_by_sku_id&app_fmt=json';
		var params = {};
	    params.sku_id = row.sku_id;

	    $.post(url, params, function(data){
		    var ret=$.parseJSON(data);
		    alert(ret.message);
	    });
	}


    /**
     * 展开列表回调动作
     */
    function tableCascadeTableCallback(_index, _row, _this, _grid, _store) {

	    switch($(_this).attr('es_btn_id')) {
		    case "sku_synchro":
			    do_synchro(_index, _row);
				break;
	    }
    }


    var fullMask = null;//提示层
    BUI.use(['bui/mask'],function(Mask){

	    fullMask = new Mask.LoadMask({
		    el : 'body',
		    msg : '处理中。。。'
	    });
    });

    //批量上架
    $('#batch_listing').on('click',function(){
	    fullMask.show();
	    var selections = tableGrid.getSelection();
	    var ids = [];
	    BUI.each(selections,function(item){
		    ids.push(item.base_item_id);
	    });

	    var url = '?app_act=api/base_item/do_batch_listing&app_fmt=json';
	    var params = {};
	    params.item_ids = ids;
	    $.post(url, params, function(data){
		    var ret=$.parseJSON(data);
		    alert(ret.message);
		    window.location.reload();
	    });
    });

    //批量下架
    $('#batch_delisting').on('click',function(){
	    fullMask.show();
	    var selections = tableGrid.getSelection();
	    var ids = [];
	    BUI.each(selections,function(item){
		    ids.push(item.base_item_id);
	    });

	    var url = '?app_act=api/base_item/do_batch_delisting&app_fmt=json';
	    var params = {};
	    params.item_ids = ids;
	    $.post(url, params, function(data){
		    var ret=$.parseJSON(data);
		    alert(ret.message);
		    window.location.reload();
	    });
    });


////合并
//$(".bui-grid-body .bui-grid-table tbody").addClass("tbl");
//merge();
//function merge() {     //可实现合并单元格,上下行来比较
//
//	var totalCols = 8;
//	var totalRows = $(".tbl").find("tr").length;
//	for ( var i = totalCols-1; i >=0 ; i--) {
//		for ( var j = totalRows-1; j >= 0; j--) {
//			startCell = $(".tbl").find("tr").eq(j).find("td").eq(i);
//			targetCell = $(".tbl").find("tr").eq(j - 1).find("td").eq(i);
//			startCell2 = $(".tbl").find("tr").eq(j).find("td").eq(0);
//			targetCell2 = $(".tbl").find("tr").eq(j - 1).find("td").eq(0);
//			//alert('i:'+i+":"+'j:'+j);
//			if (startCell2.find('.hetitle').text() == targetCell2.find('.hetitle').text() && targetCell2.find('.hetitle').text() != "") {
//				targetCell.attr("rowSpan", (startCell.attr("rowSpan")==undefined)?2:(eval(startCell.attr("rowSpan"))+1));
//				startCell.remove();
//			}
//
//		}
//	}
//}
//
//tableGrid.on('aftershow', function() {
//	merge();
//}); // end on aftershow
</script>