<?php render_control('PageHead', 'head1',
array('title'=>'增值订购列表',
	'links'=>array(
        array('url'=>'market/valueorder/detail&app_scene=add', 'title'=>'新增增值服务订购', 'is_pop'=>false, 'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'val_cp_id',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'customer',
            'data'=>array()
        ),
        array (
            'label' => '类别',
            'title' => '类别',
            'type' => 'select',
            'id' => 'value_cat',
            'data'=>ds_get_select('valueserver_cat',1)
        ),
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
                'field' => 'val_num',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'val_channel_id_name',
                'width' => '90',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'val_kh_id_name',
                'width' => '130',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'val_cp_id_name',
                'width' => '110',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'val_pt_version',
                'width' => '70',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '增值服务',
                'field' => 'val_serverid_name',
                'width' => '110',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'val_pay_status',
                'width' => '70',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核状态',
                'field' => 'val_check_status',
                'width' => '70',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'val_desc',
                'width' => '110',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array (
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'market/valueorder/detail&app_scene=edit', 'show_name'=>'编辑增值订购','show_cond'=>'obj.val_pay_status != 1 && obj.val_check_status != 1 '),
                        array('id'=>'view', 'title' => '详细', 
                		'act'=>'market/valueorder/detail&app_scene=view', 'show_name'=>'查看增值订购',), 
                        array('id'=>'pay', 'title' => '付款', 
                		'show_cond'=>'obj.val_check_status != 1 && obj.val_pay_status != 1','show_name'=>'订购付款','callback'=>'pay_value_orders','confirm' => '确认付款'),
                        array('id'=>'check', 'title' => '审核', 
                		'show_cond'=>'obj.val_pay_status == 1 && obj.val_check_status != 1','show_name'=>'订购审核','callback'=>'check_value_orders','confirm' => '确认审核'),
                ),
            )
        ) 
    ),
    'dataset' => 'market/ValueorderModel::get_valorder_info',
    'queryBy' => 'searchForm',
    'idField' => 'val_num',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script>
    
    function pay_value_orders(_index, row){
        $.ajax({ type: 'POST', dataType: 'json',  
            url:'<?php echo get_app_url('market/valueorder/do_pay_value_orders');?>',
            data: {val_num: row.val_num}, 
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
    
    function check_value_orders(_index, row) {
        $.ajax({ type: 'POST', dataType: 'json',  
            url:'<?php echo get_app_url('market/valueorder/do_check_value_orders');?>',
            data: {val_num: row.val_num}, 
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
    $('#val_cp_id').val('21');
</script>
