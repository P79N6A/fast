<?php
render_control('PageHead', 'head1', array('title' => '库存流水账查询', 'links' => array(), 'ref_table' => 'table'));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '单据编号',
            'type' => 'text',
            'id' => 'relation_code',
        ),
        array(
            'label' => '单据类型',
            'type' => 'select_multi',
            'id' => 'type',
            'data' => $response['lof_order_type'],
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => $response['store'],
            'value' => $response['store_code']
        ),
        array(
            'label' => '变动日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '商品编号',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array(
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'barcode',
            'title' => '系统SKU码/条形码/子条形码',
        ),
        array(
            'label' => '库存类型',
            'type' => 'select',
            'id' => 'inv_type',
            'data' => array(array('', '请选择'), array('锁定增加', '锁定增加'), array('锁定取消', '锁定取消'), array('实物增加', '实物增加'), array('实物扣减', '实物扣减')),
        ),
    )
));
?>
<?php if (empty($response['lof'])): ?>
    <?php
    render_control('DataTable', 'table1', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据类型',
                    'field' => 'relation_type',
                    'width' => '100',
                    'align' => '',
                //'format' => array('type'=>'map','value'=>  $response['lof_order_type']),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据编号',
                    'field' => 'relation_code',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库名称',
                    'field' => 'store_code_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec1_rename'] . '名称',
                    'field' => 'spec1_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec1_rename'] . '编码',
                    'field' => 'spec1_code',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'] . '名称',
                    'field' => 'spec2_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'] . '编码',
                    'field' => 'spec2_code',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统SKU码',
                    'field' => 'sku',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定期初',
                    'field' => 'lock_num_before_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定变动',
                    'field' => 'lock_change_num',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定期末',
                    'field' => 'lock_num_after_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库期初',
                    'field' => 'stock_num_before_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库变动',
                    'field' => 'stock_change_num',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库期末',
                    'field' => 'stock_num_after_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '变动时间',
                    'field' => 'record_time',
                    'width' => '150',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '操作说明',
                    'field' => 'remark',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/InvRecordModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'inv_record_id',
        'params' => array('filter' => array('is_entity' => $response['is_entity'])),
        'customFieldTable' => 'inv_record_do_list/table',
        'init' => 'nodata',
    ));
    ?>


<?php else: ?>

    <?php
    render_control('DataTable', 'table1', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据类型',
                    'field' => 'relation_type',
                    'width' => '100',
                    'align' => '',
                //'format' => array('type'=>'map','value'=>  $response['lof_order_type']),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据编号',
                    'field' => 'relation_code',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库名称',
                    'field' => 'store_code_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec1_rename'] . '名称',
                    'field' => 'spec1_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec1_rename'] . '编码',
                    'field' => 'spec1_code',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'] . '名称',
                    'field' => 'spec2_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'] . '编码',
                    'field' => 'spec2_code',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统SKU码',
                    'field' => 'sku',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定期初',
                    'field' => 'lock_lof_num_before_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定变动',
                    'field' => 'lock_change_num',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '锁定期末',
                    'field' => 'lock_lof_num_after_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库期初',
                    'field' => 'stock_lof_num_before_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库变动',
                    'field' => 'stock_change_num',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '在库期末',
                    'field' => 'stock_lof_num_after_change',
                    'width' => '70',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '变动时间',
                    'field' => 'record_time',
                    'width' => '150',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '批次号',
                    'field' => 'lof_no',
                    'width' => '100',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '批次日期',
                    'field' => 'production_date',
                    'width' => '100',
                    'align' => '',
                    'format' => array('type' => 'date')
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '操作说明',
                    'field' => 'remark',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/InvRecordModel::get_lof_by_page',
        'queryBy' => 'searchForm',
        'params' => array('filter' => array('is_entity' => $response['is_entity'])),
        'idField' => 'inv_record_id',
        'init' => 'nodata',
    ));
    ?>

<?php endif; ?>


<script type="text/javascript">


    $(function () {
//	 $('#showbatch').addClass("curr");
//          $('#shownobatch').addClass("curr");
    });

    $('#exprot_list').click(function () {
        var params;
        //var url ;
        var url = '?app_act=sys/export_csv/export_show';
        params = table1Store.get('params');
        if ($("#table_datatable").css('display') != 'none') {
            params.ctl_export_conf = 'inv_record_list';
        } else {
            params.ctl_export_conf = 'inv_lof_record_list';
        }
       <?php echo   create_export_token_js('prm/InvRecordModel::get_lof_by_page');?>
        params.ctl_type = 'export';
        params.ctl_export_name = '库存流水账';
        var obj = searchFormForm.serializeToObject();
        for (var key in obj) {
            params[key] = obj[key];
        }
        for (var key in params) {
            url += "&" + key + "=" + params[key];
        }
        params.ctl_type = 'view';
        window.open(url);
        //window.location.href = url;
    });



</script>