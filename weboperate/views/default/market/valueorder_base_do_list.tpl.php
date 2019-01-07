<?php 
render_control(
    'PageHead',
    'head1',
    array(
        'title' => '增值订购列表',
	'links' => array(
            array(
                'url'      => 'market/valueorder_base/detail_add&app_scene=add',
                'title'    => '新增增值服务订购',
                'is_pop'   => false,
                'pop_size' => '500,400'
            ),
	),
	'ref_table' => 'table'
    )
);
render_control (
    'SearchForm',
    'searchForm',
    array (
        'cmd' => array (
            'label' => '查询',
            'title' => '查询',
            'id'    => 'btn-search' 
        ),
        'fields' => array (
            array (
                'label' => '产品',
                'title' => '产品',
                'type'  => 'select',
                'id'    => 'val_cp_id',
                'data'  => ds_get_select('chanpin', 1)
            ),
            array (
                'label' => '客户名称',
                'type'  => 'input',
                'id'    => 'customer',
                'data'  => array()
            ),
            array(
                'label' => '备注',
                'type'  => 'input',
                'id'    => 'remark',
                'data'  => array()
            )
        ) 
    ) 
);
render_control ( 
    'DataTable',
    'table',
    array (
        'conf' => array (
            'list' => array (
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '订购编号',
                    'field' => 'val_num',
                    'width' => '100',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '销售渠道',
                    'field' => 'val_channel_id_name',
                    'width' => '90',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '客户名称',
                    'field' => 'val_kh_id_name',
                    'width' => '110',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '产品名称',
                    'field' => 'val_cp_id_name',
                    'width' => '110',
                    'align' => '' 
                ),
                array (
                    'type'      => 'text',
                    'show'      => 1,
                    'title'     => '订购总金额',
                    'field'     => 'val_standard_price',
                    'width'     => '80',
                    'align'     => '',
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '状态',
                    'field' => 'val_status',
                    'width' => '70',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '下单时间',
                    'field' => 'val_orderdate',
                    'width' => '90',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '付款时间',
                    'field' => 'val_paydate',
                    'width' => '90',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '审核时间',
                    'field' => 'val_checkdate',
                    'width' => '90',
                    'align' => '' 
                ),
                array (
                    'type'  => 'text',
                    'show'  => 1,
                    'title' => '备注',
                    'field' => 'val_desc',
                    'width' => '110',
                    'align' => '' 
                ),
                array (
                    'type'    => 'button',
                    'show'    => 1,
                    'title'   => '操作',
                    'field'   => '_operate',
                    'width'   => '80',
                    'align'   => '',
                    'buttons' => array (
                        array(
                            'id'        => 'view',
                            'title'     => '详细', 
                            'act'       => 'market/valueorder_base/detail_edit&app_scene=edit',
                            'show_name' => '查看增值订购'
                        ), 
                        array(
                            'id'        => 'pay',
                            'title'     => '付款', 
                            'show_cond' => 'obj.val_status == "已下单"',
                            'show_name' => '订购付款',
                            'callback'  => 'doPay',
                            'confirm'   => '确认付款'
                        ),
                        array(
                            'id'        => 'check',
                            'title'     => '审核', 
                            'show_cond' => 'obj.val_status == "已付款"',
                            'show_name' => '订购审核',
                            'callback'  => 'doCheck',
                            'confirm'   => '确认审核'
                        ),
                    ),
                ),
            ) 
        ),
        'dataset'        => 'market/ValueorderBaseModel::valueorderList',
        'queryBy'        => 'searchForm',
        'idField'        => 'val_num',
//        'CheckSelection' => true,
    ) 
);
?>
<script>
    function doPay(_index, row){
        $.post(
            '?app_act=market/valueorder_base/doPay',
            {val_num: row.val_num},
            function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    refreshTab(row.val_num);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            },
            'json'
        );
    }
    
    function doCheck(_index, row) {
        $.post(
            '?app_act=market/valueorder_base/doCheck',
            {val_num: row.val_num},
            function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    refreshTab(row.val_num);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            },
            'json'
        );
    }    
    
    function refreshTab(val_num) {
        for (var i = 0; top[i] !== undefined; i++) {
            if (top[i].val_num === val_num) {
                top[i].location.reload();
            }
        }
    }
</script>
