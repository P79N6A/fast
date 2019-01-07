<style type="text/css">
    .form-horizontal .control-label {
        display: inline-block;
        float: left;
        line-height: 30px;
        text-align: left;
        width: 97px;
    }
    
.control-label select {
    margin-top: 2px;
    width: 100px;
}
</style>

<?php
if(load_model('sys/PrivilegeModel')->check_priv('fx/goods_manage/detail&app_scene=add')) {
    $links =array(
        array('url' => 'fx/goods_manage/detail&app_scene=add', 'title' => '新增产品线', 'is_pop' => false),
    );
}
render_control('PageHead', 'head1', array('title' => '分销产品线列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['goods_line_name'] = '产品线名称';
$keyword_type['goods_line_code'] = '产品线代码';
$keyword_type['goods_barcode'] = '商品条形码';
$keyword_type['goods_code'] = '商品编码';
$keyword_type = array_from_dict($keyword_type);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '分销商分类',
            'type' => 'select_multi',
            'id' => 'grade_code',
            'data' => load_model('base/CustomGradesModel')->get_all_grades(2),
        ),
        array(
            'label' => '分销商',
            'type' => 'select_pop',
            'id' => 'custom_code',
            'select' => 'base/custom_multi'
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'show_view',
                        'title' => '查看',
                        'callback' => 'show_view',
                        'priv' => 'fx/goods_manage/detail&app_scene=show_view',
                    ),
                    array(
                        'id' => 'edit',
                        'title' => '编辑',
                        'callback' => 'do_edit',
                        'priv' => 'fx/goods_manage/detail&app_scene=edit',
                    ),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？','priv' => 'fx/goods_manage/do_delete',),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品线代码',
                'field' => 'goods_line_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品线名称',
                'field' => 'goods_line_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品总数',
                'field' => 'goods_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'SKU总数',
                'field' => 'sku_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最后更新时间',
                'field' => 'last_change_time',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/GoodsManageModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'fx_goods_manage', 'name' => '分销产品线列表','export_type'=>'file'),
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function (value) {
            var custom_code = [];
            var custom_name = [];
            $.each(value, function (i, v) {
                custom_code.push(v['custom_code']);
                custom_name.push(v['custom_name']);
            });
            $('#custom_code_select_pop').val(custom_name.join());
            $('#custom_code').val(custom_code.join());
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_manage/do_delete'); ?>', data: {goods_line_code: row.goods_line_code},
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

    function do_edit(_index, row) {
        var url = '?app_act=fx/goods_manage/detail&app_scene=edit&_id=' + row.id;
        openPage(window.btoa(url), url, '编辑');
    }
    
    function show_view(_index, row){
        var url = '?app_act=fx/goods_manage/detail&app_scene=show_view&_id=' + row.id;
        openPage(window.btoa(url), url, '编辑');
    }
</script>




