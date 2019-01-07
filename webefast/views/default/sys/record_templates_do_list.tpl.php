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
                 'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({url},{template_name},{print_templates_id},{print_templates_code})">编辑</a>',
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
        )
    ),
    'dataset' => 'sys/RecordTemplatesModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'print_templates_id',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
  
) );
?>

<script type="text/javascript">
    var new_clodop = <?php echo $response['new_clodop_print']; ?>;
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


    function view(url, name, print_templates_id,code) {
        if(code == 'barcode_lodop') {
            if(new_clodop == 1) {
                url = '?app_act=sys/record_templates/do_edit_barcode_clodop&_id=' + print_templates_id;
                openPage(window.btoa(url), url, '编辑模板');
            } else {
                url = '?app_act=sys/record_templates/do_edit_barcode&_id=' + print_templates_id;
                openPage(window.btoa(url), url, '编辑模板');
            }
        } else {
            var template_url = '?app_act=' +url;
            openPage(window.btoa(template_url),template_url,name);
        }
    }


</script>