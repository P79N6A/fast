<?php
render_control('PageHead', 'head1', array('title' => '增值订购列表(新)',
    'links' => array(
        array('url' => 'market/valueorder/detail_new&app_scene=add', 'title' => '新增增值服务订购', 'is_pop' => true, 'pop_size' => '500,400'),
        array('url' => 'market/valueorder/import', 'title' => '导入增值服务订购', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'customer',
            'data' => array()
        ),
        array(
            'label' => '订购编号',
            'type' => 'input',
            'id' => 'order_code',
            'data' => array()
        ),
         array (
            'label' => '类别',
            'title' => '类别',
            'type' => 'select',
            'id' => 'value_cat',
            'data'=>ds_get_select('valueserver_cat',1)
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订购编号',
                'field' => 'order_code',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'kh_id_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'val_channel_id_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'val_orderdate',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订购总数量',
                'field' => 'server_num',
                'width' => '110',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额',
                'field' => 'server_money',
                'width' => '110',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'val_desc',
                'width' => '110',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单状态',
                'field' => 'status',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
//                    array('id' => 'edit', 'title' => '编辑',
//                        'act' => 'market/valueorder/detail&app_scene=edit', 'show_name' => '编辑增值订购', 'show_cond' => 'obj.val_pay_status != 1 && obj.val_check_status != 1 '),
                    array('id' => 'view', 'title' => '详细',
                         'callback' => 'do_view', 'show_name' => '查看增值订购',),
                    array('id' => 'pay', 'title' => '付款',
                        'show_cond' => 'obj.pay_status != 1', 'show_name' => '订购付款', 'callback' => 'pay_value_orders', 'confirm' => '确认付款'),
//                    array('id' => 'check', 'title' => '审核',
//                        'show_cond' => 'obj.val_pay_status == 1 && obj.val_check_status != 1', 'show_name' => '订购审核', 'callback' => 'check_value_orders', 'confirm' => '确认审核'),
                    array('id' => 'delete', 'title' => '删除',
                        'show_cond' => 'obj.pay_status != 1', 'callback' => 'do_delete', 'confirm' => '确认删除该订单？'),
                ),
            )
        )
    ),
    'dataset' => 'market/ValueorderModel::get_valorder_main_info',
    'queryBy' => 'searchForm',
    'idField' => 'order_code',
        //'RowNumber'=>true,
        //  'CheckSelection'=>true,
));
?>
<script>

    function pay_value_orders(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('market/valueorder/do_pay_orders_main'); ?>',
            data: {id: row.id},
            success: function (ret) {
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
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('market/valueorder/do_check_value_orders'); ?>',
            data: {val_num: row.val_num},
            success: function (ret) {
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



    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('market/valueorder/do_delete_order'); ?>',
            data: {id: row.id},
            success: function (ret) {
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

//详情页
  function  do_view(_index, row) {
        var url = '?app_act=market/valueorder/view_new&id=' + row.id
        openPage(window.btoa(url), url, '订购详情');
    }

</script>
