<?php render_control('PageHead', 'head1',
array('title'=>'产品订购列表',
	'links'=>array(
        array('url'=>'market/productorder/detail&app_scene=add', 'title'=>'新增产品订购', 'is_pop'=>false, 'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    //'cmd' => array (
    //    'label' => '查询',
    //    'title' => '查询',
    //    'id' => 'btn-search'
    //),
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
    'fields' => array (
        array (
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'cp_id',
             'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '营销类型',
            'type' => 'select',
            'id' => 'pro_type',
            'data'=>ds_get_select('market',1)
        ),
        array (
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'customer',
            'data' => array()
        ),
//        array (
//            'label' => '订单过期预警',
//            'type' => 'select',
//            'id' => 'orderend',
//            'data'=>ds_get_select_by_field('orderover')
//        ),
    ) 
) );
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '订购编号',
                'field' => 'pro_num',
                'width' => '90',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'pro_channel_id_name',
                'width' => '90',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'pro_kh_id_name',
                'width' => '180',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pro_cp_id_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'pro_product_version',
                'width' => '80',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '营销类型',
                'field' => 'pro_st_id_name',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '报价方案',
                'field' => 'pro_price_id_name',
                'width' => '100',
                'align' => '' 
            ),

            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'pro_pay_status',
                'width' => '70',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核状态',
                'field' => 'pro_check_status',
                'width' => '70',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '部署状态',
                'field' => 'pro_is_arrange',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '初始化状态',
                'field' => 'pro_is_init',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订购类型',
                'field' => 'pro_add_type_name',
                'width' => '70',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑', 'show_cond' => 'obj.pro_check_status != 1 && obj.pro_pay_status != 1',
                        'act' => 'market/productorder/detail&app_scene=edit', 'show_name' => '编辑产品订购',),
                    array('id' => 'view', 'title' => '详细',
                        'act' => 'market/productorder/detail&app_scene=view', 'show_name' => '查看产品订购',),
                    array('id' => 'pay', 'title' => '付款', 'show_cond' => 'obj.pro_check_status != 1 && obj.pro_pay_status != 1',
                        'show_name' => '付款', 'callback' => 'pay_orders', 'confirm' => '确认付款'),
                    array('id' => 'check', 'title' => '审核',
                        'show_cond' => 'obj.pro_check_status != 1 && obj.pro_pay_status == 1', 'show_name' => '订购审核', 'callback' => 'check_orders', 'confirm' => '确认审核'),
                   array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？', 'show_cond' => 'obj.pro_check_status != 1 && obj.pro_pay_status != 1'),
                    ),
               )  
        ) 
    ),
    'dataset' => 'market/ProductorderModel::get_porder_info',
    'queryBy' => 'searchForm',
    'idField' => 'pro_num',
    'export' => array('id' => 'exprot_list', 'conf' => 'market_productorder_do_list', 'name' => '产品订购列表',),//'export_type'=>'file'
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script>
    $('#cp_id').val('21');
    function check_orders(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('market/productorder/do_check_orders');?>',
        data: {_id: row.pro_num}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}    


    function pay_orders(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('market/productorder/do_pay_orders');?>',
        data: {_id: row.pro_num}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

    //删除
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('market/productorder/do_delete'); ?>', data: {pro_num: row.pro_num},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功!', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>
