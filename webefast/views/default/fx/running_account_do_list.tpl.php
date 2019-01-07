<?php
render_control('PageHead', 'head1', array('title' => '分销商往来流水账',
//    'links' => array(
//        array('url' => 'fx/running_account/detail&app_scene=add', 'title' => '新增', 'is_pop' => true, 'pop_size' => '400,500'),
//    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' =>  array(
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
        array('label'=>'分销商', 'type'=>'select_pop', 'id'=>'p_code', 'select'=>'base/custom' ),
        array(
            'label' => '业务类型',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'record_type',
            'data' => load_model('fx/RunningAccountModel')->get_record_type()
        ),
        array(
            'label' => '备注',
            'title' => '',
            'type' => 'input',
            'id' => 'remark'
        ),
        array(
            'label' => '变更时间',
            'type' => 'group',
            'field' => 'change_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'change_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'change_time_end', 'remark' => ''),
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
                'type' => 'text',
                'title' => '流水号',
                'field' => 'record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '关联单据编号',
                'field' => 'relation_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '分销商名称',
                'field' => 'custom_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '业务类型',
                'field' => 'record_type_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '变更前余额',
                'field' => 'account_money_start',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '金额',
                'field' => 'account_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '变更后余额',
                'field' => 'account_money_end',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '变更时间',
                'field' => 'change_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'title' => '备注',
                'field' => 'remark',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'fx/RunningAccountModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'running_account_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'running_account_do_list', 'name' => '分销商往来流水账','export_type' => 'file'),
));
?>
<script type="text/javascript">
    var login_type = "<?php echo $response['login_type']?>";
    $(document).ready(function(){
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#p_code_select_pop").parent().append(html);
        if(login_type == 2){
            $("#searchForm #p_code_select_pop").attr("disabled","true");
            $("#searchForm #p_code_select_img").unbind();
        }
    });
     var selectPopWindowp_code = {
        dialog: null,
        callback: function(value) {
            var custom_code = value[0]['custom_code'];
            var custom_name = value[0]['custom_name'];
            $('#p_code_select_pop').val(custom_name);
            $('#custom_code').val(custom_code);
            if (selectPopWindowp_code.dialog != null) {
                selectPopWindowp_code.dialog.close();
            }
        }
    };
    
    
    
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/do_delete'); ?>', data: {account_id: row.account_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    function do_confirm(_index, row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/do_confirm'); ?>', data: {account_id: row.account_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('确认成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>




