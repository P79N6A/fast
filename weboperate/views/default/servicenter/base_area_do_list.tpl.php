<?php render_control('PageHead', 'head1',
    array('title' => '地址库',
        'links' => array(
            array('url' => 'servicenter/base_area/detail&app_scene=add', 'title' => '新增地址', 'is_pop' => true, 'pop_size' => '650,500'),
            array('type' => 'js', 'js' => 'sync_client_environment()', 'title' => '同步客户环境'),
            //array('url' => 'base/taobao_area/dl_area_type_5&app_scene=add', 'title' => '下载街道区域', 'is_pop' => true, 'pop_size' => '500,300'),

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
        array('label' => '区域类型',
            'title' => '区域类型',
            'type' => 'select',
            'id' => 'type',
            'data' => ds_get_select_by_field('area_type', 0),
        ),
        array('label' => '地理名称',
            'type' => 'input',
            'id' => 'name'
        ),
        array('label' => '邮政编码',
            'type' => 'input',
            'id' => 'zip'
        ),
    ),
    'hidden_fields' => array(array('field' => 'area_type', 'value' => '')),
));

?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
    array('type' => 'button',
        'show' => 1,
        'title' => '查看',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
            array('id' => 'child', 'title' => '查看下级',
                'callback' => 'do_list_child', 'show_cond' => 'obj.has_next == 1'),
            array('id' => 'parent', 'title' => '查看上级',
                'callback' => 'do_list_parent', 'show_cond' => 'obj.has_parent == 1'),
        ),
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '地理名称',
        'field' => 'name',
        'width' => '150',
        'align' => ''
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '类型',
        'field' => 'type_txt',
        'width' => '150',
        'align' => ''
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '行政区域编码',
        'field' => 'id',
        'width' => '150',
        'align' => ''
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '邮政编码',
        'field' => 'zip',
        'width' => '100',
        'align' => ''
    ),

)
),
    'dataset' => 'servicenter/BaseAreaModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">

    function do_list_child(_index, row) {
        var obj = {"area_type": "child", "area_id": row.id, "page": "1", "type": "", "name": ""};
        obj.start = 1;
        tableStore.load(obj);
        page.jumpToPage(1);
    }

    function do_list_parent(_index, row) {
        var obj = {"area_type": "parent", "area_id": row.parent_id, "page": "1", "type": "", "name": ""};
        obj.start = 1;
        tableStore.load(obj);
        page.jumpToPage(1);
    }

    //同步客户
    function sync_client_environment() {
        var d = {'app_fmt': 'json'};
        $("#get").attr('disabled', true);
        $.post('<?php echo get_app_url('pubdata/pubdata_sync/sync_base_area'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $("#get").attr('disabled', false);
            tableStore.load();//刷新
        }, "json");
    }



</script>