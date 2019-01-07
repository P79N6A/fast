<style>
    .print_type_btn{ border:1px solid #1695ca; background:#FFF; color:#1695ca; margin-right:2px; border-radius:3px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '订单设问策略',
    'links' => array(
    ),
    'ref_table' => 'table',
));
?>

<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '配置', 'act' => 'base/question_label/detail&app_scene=edit', 'show_name' => '配置', 'show_cond' => 'obj.question_label_code =="EXCEPTION_ADDRESS"',),
                    array('id' => 'overweight_edit', 'title' => '配置','callback' => 'edit_overweight', 'show_name' => '配置', 'show_cond' => 'obj.question_label_code =="SELL_RECORD_OVERWEIGHT"',),
                )
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'is_active',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '设问代码',
                'field' => 'question_label_code',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '设问原因',
                'field' => 'question_label_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '设问描述',
                'field' => 'remark',
                'width' => '350',
                'align' => '',
                'format' => array('type' => 'truncate',
                    'value' => 20,
                )
            ),
        )
    ),
    'dataset' => 'base/QuestionLabelModel::get_by_page',
    //'queryBy' => '',
    'idField' => 'question_label_id',
));
?>

<script type="text/javascript">
    function change_status(_this, status, id) {
        _do_set_active(_this, status, id);
    }

    function _do_set_active(_this, status, id) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/question_label/update_active'); ?>',
            data: {id: id, type: status},
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

//订单超重
    function edit_overweight(_index, row) {
        var url = '<?php echo get_app_url('base/question_label/over_weight'); ?>';
        new ESUI.PopWindow(url, {
            title: "设问配置-订单超重",
            width: 500,
            height: 300,
            onBeforeClosed: function () {
                tableStore.load();
                //location.reload();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }
</script>