<style type="text/css">
.form-horizontal .control-label {
    display: inline-block;
    float: left;
    line-height: 30px;
    text-align: left;
    width: 97px;
}
</style>

<?php
if(load_model('sys/PrivilegeModel')->check_priv('base/custom_grades/detail&app_scene=add')) {
    $links = array(
        array('url' => 'base/custom_grades/detail&app_scene=add', 'title' => '添加分销商分类', 'is_pop' => false, 'pop_size' => '600,500'),
    );
}
render_control('PageHead', 'head1', array('title' => '分销商分类',
    'links' => $links,
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
            'label' => '分销商分类名称',
            'type' => 'input',
            'id' => 'code_name'
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
                        'id'=>'show_custom',
                        'title' => '查看分销商', 
                        'callback'=>'show_custom',
                        'priv' => 'base/custom_grades/detail&app_scene=show_custom',
                    ),
                    array(
                        'id'=>'edit',
                        'title' => '编辑', 
                        'callback'=>'do_edit',
                        'priv'=>'base/custom_grades/detail&app_scene=edit',
                    ),
                    
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？','priv'=>'base/custom_grades/do_delete'),
                    
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'grade_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'grade_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商数量',
                'field' => 'custom_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '250',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'base/CustomGradesModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'custom_id',
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/custom_grades/do_delete'); ?>', data: {grade_code: row.grade_code},
            success: function (ret) {
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
    
    function do_reset_pwd (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json', 
            url: '<?php echo get_app_url('base/custom/reset_pwd'); ?>', data: {user_code: row.user_code}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                BUI.Message.Alert('新密码：'+ret.data, type);
                } else {
                    if(ret.status == -10){
                        BUI.Message.Alert(ret.message,function(){
                        window.open(ret.data);
                        }, type);
                    }else{
                        BUI.Message.Alert(ret.message, type);
                    }

                }
            }
        });
    }
     
    function do_edit(_index, row) {
        var url = '?app_act=base/custom_grades/detail&app_scene=edit&_id=' + row.grade_id;
        openPage(window.btoa(url), url, '编辑分销商分类');
    }
    
    function show_custom(_index, row){
        var url = '?app_act=base/custom_grades/detail&app_scene=show_custom&_id=' + row.grade_id;
        openPage(window.btoa(url), url, '编辑分销商分类');
    }
</script>




