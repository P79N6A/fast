<?php
$links = array(array('url' => 'base/store_staff/detail&app_scene=add', 'is_pop' => true, 'pop_size' => '700,450', 'title' => '新增仓库员工'),);
render_control('PageHead', 'head1', array('title' => '仓库员工档案',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '员工',
            'title' => '员工名称/代码',
            'type' => 'input',
            'id' => 'staff_name',
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
                'width' => '190',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:base/store_staff/detail&app_scene=edit', 'show_name' => '编辑', 'show_cond' => '', 'priv' => ''),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？', 'show_cond' => 'obj.status != 1', 'priv' => 'base/store_staff/delete',),//
                    array('id' => 'enable', 'title' => '启用', 'callback' => 'do_enable', 'show_cond' => 'obj.status != 1'),
                    array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.status == 1', 'confirm' => '确认要停用吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用',
                'field' => 'status',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked'),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '员工代码',
                'field' => 'staff_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '员工名称',
                'field' => 'staff_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '员工类型',
                'field' => 'staff_type_name',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/StoreStaffModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'staff_id',
    //'RowNumber'=>true,
    // 'CheckSelection'=>true,
));
?>
<br />
<span style="color: red">说明：用于仓库内部拣货员业绩统计，用于波次单中分配拣货员。</span>
<script type="text/javascript">
    //删除
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/store_staff/do_delete'); ?>', data: {staff_id: row.staff_id},
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

    /**
     * 启用
     * @param _index
     * @param row
     */
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    /**
     * 停用
     * @param _index
     * @param row
     */
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }

    //更新启用状态
    function _do_set_active(_index, row, active) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/store_staff/update_active'); ?>',
            data: {id: row.staff_id, type: active},
            success: function (ret) {
                var _type = ret.status == 1 ? 'success' : 'error';
                BUI.Message.Show({
                    msg: ret.message,
                    width: 150,
                    icon: _type,
                    buttons: [],
                    autoHide: true
                });
                if (_type == 'success') {
                    tableStore.load();
                }
            }
        });
    }
</script>
