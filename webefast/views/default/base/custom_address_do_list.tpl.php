<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>

<?php
render_control('PageHead', 'head1', array('title' => '分销商地址维护',
    'links' => array(
        // array('type' => 'js', 'js' => 'report_excel()', 'title' => '导出'), 
        array('url' => 'base/custom/do_list', 'target' => '_self', 'title' => '返回分销商列表'),
    ),
    'ref_table' => 'table'
));
?>
<script>
    var data = [
        {
            "name": "custom_name",
            "title": "名称",
            "value": "<?php echo $response['data']['custom_name'] ?>",
            "type": "input",
        },
        {
            "name": "custom_type",
            "title": "类型",
            "value": "<?php echo $response['data']['custom_type'] == 'pt_fx' ? '普通分销' : '淘宝分销'; ?>",
            "type": "input",
        },
        {
            "name": "custom_grade",
            "title": "分类",
            "value": "<?php echo $response['data']['custom_grade_name'] ?>",
            "type": "input",
        },
        {
            "name": "contact_person",
            "title": "联系人",
            "value": "<?php echo $response['data']['contact_person']; ?>",
            "type": "input",
        },
        {
            "name": "mobile",
            "title": "手机号",
            "value": "<?php echo $response['data']['mobile']; ?>",
            "type": "input",
        },
        {
            "name": "tel",
            "title": "联系电话",
            "value": "<?php echo $response['data']['tel']; ?>",
            "type": "input",
        },
        {
            "name": "price_and_rebate",
            "title": "结算价格与折扣",
            "value": "<?php echo $response['data']['price_and_rebate']; ?>",
            "type": "input",
        },
        {
            "name": "settlement_method_name",
            "title": "运费结算方式",
            "value": "<?php echo $response['data']['settlement_method_name']; ?>",
            "type": "input",
        },
        {
            "name": "fixed_money",
            "title": "结算运费",
            "value": "<?php echo $response['data']['fixed_money']; ?>",
            "type": "input",
        },
        {
            "name": "radio_group",
            "title": "是否启用",
            "value": "<?php echo $response['data']['is_effective_str']; ?>",
        },
    ];

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
        });
        $('[name = panel_html]').hide();
        $('#btn_address').click(function () {
            new ESUI.PopWindow("?app_act=base/custom/address_detail&app_scene=add&custom_id=" +<?php echo $response['data']['custom_id'] ?>, {
                title: "新增地址",
                width: 700,
                height: 550,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        });
    })

    parent._action = function () {
        tableStore.load();
        logStore.load();
    };
</script>       

<div class="panel record_table" id="panel_html">

</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">收货地址 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div style='float:right'>
                <button type="button" class="button button-success" value="新增地址" id="btn_address"><i class="icon-plus-sign icon-white"></i> 新增地址</button>
            </div>
        </div>
        <?php
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '150',
                        'align' => '',
                        'buttons' => array(
                            array(
                                'id' => 'default',
                                'title' => '设为默认',
                                'callback' => 'do_set_default',
                                'show_cond' => 'obj.is_default == 0'
                            ),
                            array(
                                'id' => 'edit_address',
                                'title' => '修改',
                                'callback' => 'do_edit_address',
                            ),
                            array(
                                'id' => 'del',
                                'title' => '删除',
                                'confirm' => '确认要删除此地址吗？',
                                'callback' => 'do_delete_address',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '收货人',
                        'field' => 'name',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '手机号',
                        'field' => 'tel',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '地址',
                        'field' => 'address_str',
                        'width' => '400',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '邮编',
                        'field' => 'zipcode',
                        'width' => '100',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '联系电话',
                        'field' => 'home_tel',
                        'width' => '120',
                        'align' => '',
                    ),
                )
            ),
            'dataset' => 'base/CustomAddressModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'order_record_detail_id',
            'params' => array('filter' => array('custom_code' => $response['data']['custom_code'])),
                //'RowNumber'=>true,
                //'CheckSelection'=>true,
        ));
        ?>

    </div>

</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">

            <?php
            render_control('DataTable', 'log', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作者',
                            'field' => 'user_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作名称',
                            'field' => 'action_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'lastchanged',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '600',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'base/CustomAddressModel::get_by_log',
                //'queryBy' => 'searchForm',
                'idField' => 'log_id',
                'params' => array('filter' => array('custom_code' => $response['data']['custom_code'])),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script>
    //修改地址
    function do_edit_address(_index, row) {
        new ESUI.PopWindow("?app_act=base/custom/address_detail&app_scene=edit&custom_id=" +<?php echo $response['data']['custom_id'] ?> + '&addr_id=' + row.custom_address_id, {
            title: "修改地址",
            width: 700,
            height: 550,
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    }
    //删除地址
    function do_delete_address(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/custom/do_delete_address'); ?>', 
            data: {addr_id: row.custom_address_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                    logStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //设为默认
    function do_set_default(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/custom/do_set_default'); ?>', 
            data: {addr_id: row.custom_address_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    
    }
</script>
