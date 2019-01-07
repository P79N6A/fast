<?php render_control('PageHead', 'head1',
    array('title' => '库存对照',

    'ref_table' => 'table'
    ));

?>

<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
			array(
                  'id' => 'download',
                  'title' => '下载',
                  'callback' => 'do_download',
                  ),        	
        )
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '编号',
        'field' => 'id',
        'width' => '60',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '对照时间',
        'field' => 'compare_time',
        'width' => '100',
        'align' => '',
    ),
        array('type' => 'text',
        'show' => 1,
        'title' => 'wms仓储名称',
        'field' => 'wms_name',
        'width' => '100',
        'align' => '',
    ),
            array('type' => 'text',
        'show' => 1,
        'title' => '系统仓库',
        'field' => 'store_name',
        'width' => '100',
        'align' => '',
    ),
            array('type' => 'text',
        'show' => 1,
        'title' => 'wms仓库',
        'field' => 'wms_name',
        'width' => '80',
        'align' => '',
    ),
                array('type' => 'text',
        'show' => 1,
        'title' => '差异条码数',
        'field' => 'compare_sku_num',
        'width' => '100',
        'align' => '',
    ),
                array('type' => 'text',
        'show' => 1,
        'title' => '差异库存数',
        'field' => 'compare_num',
        'width' => '100',
        'align' => '',
    ),


    )
),
    'dataset' => 'wms/WmsInvModel::get_compare_list',
  //  'queryBy' => 'searchForm',
    'idField' => 'compare_code',
));

?>

<script type="text/javascript">

function do_download(_index, row) {
    
    var url = "?app_act=wms/wms_trade/down_compare_data&compare_code="+row.compare_code+"&store_code="+row.store_code;
    window.open(url);
}

</script>