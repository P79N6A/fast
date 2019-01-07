<?php render_control('PageHead', 'head1',
    array('title'=>'打印模板',
        'links'=>array(
            array('url'=>'sys/print_templates/add', 'title'=>'新增模板', 'is_pop'=>false, 'pop_size'=>'800,600'),
        ),
        'ref_table'=>'table'
    ));?>


<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array (
        array (
            'label' => '模板名称',
            'type' => 'input',
            'id' => 'print_templates_name'
        ),
    )
) );
?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array (
                    /*
                    array('id'=>'edit', 'title' => '编辑',
                        'act'=>'pop:prm/brand/detail&app_scene=edit', 'show_name'=>'编辑',
                        'show_cond'=>'obj.is_buildin != 1'),

                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
                    */
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '模版名称',
                'field' => 'print_templates_name',
                'width' => '200',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '向下偏',
                'field' => 'offset_top',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '向右偏',
                'field' => 'offset_left',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '纸张宽',
                'field' => 'paper_width',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '纸张高',
                'field' => 'paper_height',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '打印机',
                'field' => 'printer',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'sys/PrintTemplatesModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'print_templates_id',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
) );
?>

<script type="text/javascript">
    function do_delete (_index, row) {
        $.ajax({ type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/brand/do_delete');?>', data: {brand_id: row.brand_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    $(document).ready(function(){

    });

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_id=') ?>'+row.sell_record_id,'?app_act=oms/sell_record/view&ref=do&sell_record_id='+row.sell_record_id,'订单详情');
    }
</script>