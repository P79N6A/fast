<?php
render_control('PageHead', 'head1', array('title' => '店铺授权列表',
    'links' => array(
        array('url'=>'clients/shopwarrant/detail&app_scene=add', 'title'=>'新增店铺SESSION', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'  //ref_table 表示是否刷新父页面
));
?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '客户名称',
            'title' => '客户模糊匹配',
            'type' => 'input',
            'id' => 'client_name',
        ),
        array (
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'product',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '平台',
            'title' => '平台',
            'type' => 'select',
            'id' => 'platform',
            'data'=>ds_get_select('shop_platform',1)
        ),
        array (
            'label' => '店铺名称',
            'title' => '店铺模糊匹配',
            'type' => 'input',
            'id' => 'shop_name',
        )
    ) 
) );
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户',
                'field' => 'sw_kh_id_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'sw_cp_id_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'sw_sd_id_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sw_pt_id_name',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺SESSION',
                'field' => 'sw_shop_session',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '更新时间',
                'field' => 'sw_update_date',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'sw_valid_date',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '查看', 
                		'act'=>'clients/shopwarrant/detail&app_scene=view', 'show_name'=>'查看店铺授权'),
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'clients/shopwarrant/detail&app_scene=edit', 'show_name'=>'编辑店铺授权', 
                		'show_cond'=>'obj.is_buildin != 1'),
                        array('id'=>'push', 'title' => '推送', 'show_name'=>'推送',
                		'show_cond'=>'obj.is_buildin != 1','callback'=>'do_push_click'),
                ),
            )
        )
    ),
    'dataset' => 'clients/ShopwarrantModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sw_id',
    'CheckSelection' => true,
    'events'=>array(
            'rowdblclick'=>array('ref_button'=>'view')),
));
?>
<script type="text/javascript">
    
    function setpushtitle(){
        $('#table').find('.push').attr('title','推送授权到业务库');
    }
    
    setTimeout("setpushtitle()",500);
    
    function do_push_click(_index, row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('clients/shopwarrant/do_push'); ?>',
            data: {sw_sd_id: row.sw_sd_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    //tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
</script>
