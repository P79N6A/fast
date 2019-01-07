<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT仓库管理',
    'links' => array(),
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
            'label' => '仓库名称',
            'title' => '仓库名称',
            'type' => 'input',
            'id' => 'warehouse_name',
        ),
    )
));
?>
<?php
$customlist = load_model('base/CustomModel')->get_purview_custom_select('pt_fx', 5);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '110',
                'align' => '',
                'buttons' => array(
                    array('id' => 'enable', 'title' => '启用',
                        'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
                    array('id' => 'disable', 'title' => '停用',
                        'callback' => 'do_disable', 'show_cond' => 'obj.is_active != 0', 'confirm' => '确定要停用仓库吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用状态',
                'field' => 'status',
                'width' => '70',
                'align' => '',
                'format' => array('type' => 'map_checked'),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '序号',
                'field' => 'warehouse_no',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库编码',
                'field' => 'warehouse_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'warehouse_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'desc',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '绑定分销商',
                'field' => 'custom_code',
                'format_js'=> array('type'=>'map', 'value'=>$customlist),
                'width' => '120',
                'align' => '',
                'editor'=>"{xtype : 'select', items: ".json_encode($customlist)."}"
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitWarehouseModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'warehouse_id',
    'CellEditing' => true,
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
            url: '<?php echo get_app_url('api/api_weipinhuijit_warehouse/update_active'); ?>',
            data: {id: row.warehouse_id, active: active},
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
    $(function() {
        tableCellEditing.on('accept', function(record) {
            var params = {
                "warehouse_id": record.record.warehouse_id,
                "custom_code": record.record.custom_code,
            }
            $.post("?app_act=api/api_weipinhuijit_warehouse/edit_custom", params, function(data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                } else if(data.status == 1){
                    BUI.Message.Tip(data.message, 'success');
                    tableStore.load();
                }
            }, "json")
        });
    })

</script>
