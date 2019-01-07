<?php
render_control('PageHead', 'head1', array('title' => '企业SESSION列表',
    'links' => array(
        //array('url'=>'sys/session/get_sessionkey', 'title'=>'刷新SESSION'),
        array('js'=>'refresh_session()', 'title'=>'刷新SESSION', 'type'=>'js'),
        array('url'=>'sys/session/detail&app_scene=add', 'title'=>'新增产品KEY', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'relation_product',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '平台',
            'title' => '平台',
            'type' => 'select',
            'id' => 'relation_platform',
            'data'=>ds_get_select('shop_platform',1)
        ),
        array (
            'label' => 'KEY',
            'title' => 'KEY',
            'type' => 'text',
            'id' => 'rdsapp_key',
        )
    ) 
) );
?>
<!--ul class="toolbar" id="btn_toolbar" style="margin-top: 10px;">
        <li><button class="button button-primary btn_reset_pwd">批量重置密码</button></li>
</ul-->
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'relation_product_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'relation_platform_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '企业KEY',
                'field' => 'app_key',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '企业密钥',
                'field' => 'app_secret',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '企业SESSION',
                'field' => 'access_token',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '更新时间',
                'field' => 'refresh_time',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'memo',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '查看', 
                		'act'=>'sys/session/detail&app_scene=view', 'show_name'=>'查看产品平台KEY'),
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'sys/session/detail&app_scene=edit', 'show_name'=>'编辑产品平台KEY', 
                		'show_cond'=>'obj.is_buildin != 1'),
                        //array('id'=>'refresh_session', 'title' => '刷新SESSION', 'callback'=>'do_refresh',),
                ),
            )
        )
    ),
    'dataset' => 'sys/RdsModel_ex::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'rds_id',
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>
<script type="text/javascript">
    
    /*$(".btn_reset_pwd").click(function(){
        var itemlist=tableGrid.getSelection();
        if(itemlist.length!=0){
            alert(JSON.stringify(itemlist));
        }
    }); */
    
    function refresh_session(){
         var url='<?php echo get_app_url('sys/session/get_sessionkey'); ?>';
        $.ajax({ type: 'POST', dataType: 'json',  
            url:url,
            data: {sue_number: '<?php echo $request['_id'] ?>',}, 
            success: function(ret) {
                /*var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }*/
                //error
                //error_description
            }
        });
    }  
</script>