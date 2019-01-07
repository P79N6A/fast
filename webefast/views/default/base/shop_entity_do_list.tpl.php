<?php
$links = array();
if ($response['login_type'] != 1) {
    $links[] = array('url' => 'base/shop_entity/detail&app_scene=add', 'title' => '添加店铺', 'is_pop' => true, 'pop_size' => '600,570');
}
render_control('PageHead', 'head1', array('title' => '实体店铺',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
if ($response['login_type'] != 1) {
    render_control('SearchForm', 'searchForm', array(
        'cmd' => array(
            'label' => '查询',
            'label' => '查询',
            'id' => 'btn-search',
        ),
        'fields' => array(
            array(
                'label' => '店铺',
                'title' => '店铺名称/代码/助记符',
                'type' => 'input',
                'id' => 'shop_key',
            ),
            array(
                'label' => '营业状态',
                'title' => '是否营业',
                'type' => 'select',
                'id' => 'is_active',
                'data' => ds_get_select_by_field('openstatus', 1)
            )
        )
    ));
}
?>
<?php
$list = array(
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '110',
        'align' => '',
        'buttons' => array(
            array('id' => 'edit', 'title' => '编辑',
                'act' => 'pop:base/shop_entity/detail&app_scene=edit', 'show_name' => '编辑',
                'show_cond' => 'obj.is_buildin != 1',
                'pop_size' => '600,570'),
            array('id' => 'enable', 'title' => '开始营业',
                'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
            array('id' => 'disable', 'title' => '暂停营业',
                'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1',
                'confirm' => '确认要暂停营业吗？'),
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '营业状态',
        'field' => 'is_active_text',
        'width' => '70',
        'align' => '',
        'format' => array('type' => 'map_checked'),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺名称',
        'field' => 'shop_name',
        'width' => '100',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺代码',
        'field' => 'shop_code',
        'width' => '100',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺助记符',
        'field' => 'shop_user_nick',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '联系电话',
        'field' => 'tel',
        'width' => '110',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '地址',
        'field' => 'total_address',
        'width' => '350',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '营业时间',
        'field' => 'open_time',
        'width' => '90',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '创建时间',
        'field' => 'create_time',
        'width' => '150',
        'align' => '',
    ),
);

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'base/ShopModel::get_entity_by_page',
    'queryBy' => $response['login_type'] != 1 ? 'searchForm' : '',
    'idField' => 'shop_id',
));
?>
<input type="hidden" id="sel_shop_code"/>
<script type="text/javascript">
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/shop_entity/update_active'); ?>',
            data: {id: row.shop_id, type: active},
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

</script>
