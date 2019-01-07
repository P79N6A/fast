<?php
render_control('PageHead', 'head1', array('title' => '分销商审核',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '手机号',
            'type' => 'input',
            'id' => 'phone'
        ),
    )
));
?>

<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'agree', 'title' => '通过', 'callback' => 'do_agree', 'confirm' => '确认要通过此信息吗？','priv'=>'base/custom/update_user_status'),
                    array('id' => 'deny', 'title' => '拒绝', 'callback' => 'do_deny', 'confirm' => '确认要拒绝此信息吗？','priv'=>'base/custom/update_user_status'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '申请账号',
                'field' => 'user_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商名称',
                'field' => 'custom_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系人姓名',
                'field' => 'user_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '联系人手机号码',
                'field' => 'phone',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '申请时间',
                'field' => 'create_time',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/CustomModel::get_review_list',
    'queryBy' => 'searchForm',
    'idField' => 'user_id',
));
?>
<script type="text/javascript">
    function do_agree(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/custom/update_user_status'); ?>', data: {user_id: row.user_id,type:'enable'},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('通过成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    function do_deny(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/custom/update_user_status'); ?>', data: {user_id: row.user_id,type:'disable'},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('拒绝成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>




