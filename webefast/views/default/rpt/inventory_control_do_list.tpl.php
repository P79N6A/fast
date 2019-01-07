<?php
render_control('PageHead', 'head1', array('title' => '库存差异对比报表',
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
//            array('type' => 'text',
//                'show' => 1,
//                'title' => '编号',
//                'field' => 'id',
//                'width' => '60',
//                'align' => '',
//            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '对照时间',
                'field' => 'compare_time',
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
                'title' => '可用唯一码总数',
                'field' => 'unique_num',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '在库库存总数',
                'field' => 'inventory_sku_num',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '差异库存总数',
                'field' => 'compare_num',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'rpt/SellGoodsReportModel::get_compare_list',
    'idField' => 'compare_code',
));
?>

<script type="text/javascript">

    function do_download(_index, row) {
        var url = "?app_act=rpt/inventory_control/down_compare_data&compare_code=" + row.compare_code + "&store_code=" + row.store_code;
        window.open(url);
    }

</script>
