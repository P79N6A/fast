<?php
render_control('PageHead', 'head1', array('title' => '装箱任务列表',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchSelectButton', 'select_button', array(
    'fields' => array(
        array('id' => 'is_change', 'title' => '完成状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '未完成', 'id' => '0',),
                array('content' => '已完成', 'id' => '1',),
            )),
    ),
    'for' => 'searchForm',
    'style' => 'width:192px;'
));
?>

<?php
$keyword_type = array();
$keyword_type['task_code'] = '装箱任务号';
$keyword_type['relation_code'] = '关联单号';
$keyword_type['create_user'] = '创建人';
$keyword_type = array_from_dict($keyword_type);


$fenxiao = load_model('base/CustomModel')->get_purview_custom_select('pt_fx',4);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'value' => $response['task_code']
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '分销商',
            'type' => 'select_multi',
            'id' => 'distributor_code',
            'data' => $fenxiao,
        ),
        array(
            'label' => '创建时间',
            'type' => 'group',
            'field' => 'create_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
            )
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
                'width' => '140',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '装箱扫描',
                        'callback' => 'do_box_scan',
                        'priv' => 'b2b/box_task/do_box_scan',
                        'show_cond' => 'obj.is_change == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '完成',
                        'callback' => 'do_change',
                        'priv' => 'b2b/box_task/do_change',
                        'show_cond' => 'obj.is_change == 0'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成状态',
                'field' => 'is_change',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '装箱任务号',
                'field' => 'task_code',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:view("{task_code}")>{task_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'distributor_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建人',
                'field' => 'create_user',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联单号',
                'field' => 'relation_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总箱数',
                'field' => 'x_count',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'b2b/BoxTaskModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('task_code' => $response['task_code'])),
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    function  do_box_scan(_index, row) {
        url = '?app_act=common/record_scan_box/view_scan&dj_type=' + row.record_type + '&record_code=' + row.relation_code;
        window.open(url);
    }

    function  do_change(_index, row) {
        url = '?app_act=b2b/box_task/ys_box';
        data = {app_fmt: 'json', task_code: row.task_code, dj_type: row.record_type, record_code: row.relation_code};
        _do_operate(url, data, 'table');
    }

    /**
     * 查看装箱单列表
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        detail(_index, row);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        detail(_index, row);
    }
    function detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=b2b/box_record/do_list&task_code=') ?>' + row.task_code, '?app_act=b2b/box_record/do_list&task_code=' + row.task_code, '装箱单');
    }

    function view(task_code) {
        openPage('<?php echo base64_encode('?app_act=b2b/box_record/do_list&task_code=') ?>' + task_code, '?app_act=b2b/box_record/do_list&task_code=' + task_code, '装箱单');
    }
</script>

