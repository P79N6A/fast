<?php
render_control('PageHead', 'head1', array('title' => '客户云主机列表',
    'links' => array(
        array('url' => 'clients/aliinfo/detail&app_scene=add', 'title' => '新建客户云主机信息', 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '客户名称',
            'type' => 'input',
            'title' => '客户',
            'id' => 'client_name'
        ),
        array(
            'label' => 'IP地址',
            'title' => '外网IP地址',
            'type' => 'input',
            'id' => 'ipaddr'
        ),
        /*array(
            'label' => '到期时间',
            'title' => '服务器到期时间',
            'type' => 'date',
            'id' => 'ali_endtime'
        ),*/
        array(
            'label' => '到期时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'ali_endtime_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'ali_endtime_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '云服务商',
            'type' => 'select',
            'id' => 'ali_type',
            'data' => ds_get_select('host_cloud',2)
        ),
        array(
            'label' => '部署状态',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'ali_deployment',
            'data' => ds_get_select_by_field('boolstatus')
        ),
        array (
            'label' => '操作系统',
            'type' => 'select',
            'id' => 'system_type',
             'data'=>ds_get_select_by_field('system_type')
        ),
        array (
            'label' => '状态',
            'type' => 'select',
            'id' => 'ali_state',
             'data'=>ds_get_select_by_field('boolstate')
        ),
    )
));
?>
<ul class="toolbar" id="btn_toolbar" style="margin-top: 10px;">
    <li><button class="button button-primary btn_reset_pwd">批量重置密码</button></li>
    <li><button class="button button-primary btn_reset_all_pwd">重置所有密码</button></li>
    <!-- <li><button class="button button-primary btn_run_all_command">批量执行命令</button></li>
    <li><button class="button button-primary btn_mv_all_command">批量移动文件</button></li> -->
</ul>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
//                'field' => 'kh_id_name',
                'field' => 'kh_name',
                'width' => '150',
                'align' => ''
            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '所属机构',
//                'field' => 'xx',
//                'width' => '100',
//                'align' => '' 
//        ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '外网IP',
                'field' => 'ali_outip',
                'width' => '150',
                'align' => ''
            ),
            /*array(
                'type' => 'text',
                'show' => 1,
                'title' => '内网IP',
                'field' => 'ali_inip',
                'width' => '150',
                'align' => ''
            ),*/
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '云服务商',
                'field' => 'ali_type_name',
                'width' => '100',
                'align' => '',
//                'format' => array('type' => 'map', 'value' => ds_get_field('servertype'))
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '部署',
                'field' => 'ali_deployment',
                'width' => '50',
                'align' => '',
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '状态',
                'field' => 'ali_state',
                'width' => '50',
                'align' => '',
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '密码修改日期',
                'field' => 'ali_pass_updatedate',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'ali_endtime',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '详细',
                        'act' => 'clients/aliinfo/detail&app_scene=view', 'show_name' => '查看客户云主机信息'),
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'clients/aliinfo/detail&app_scene=edit', 'show_name' => '编辑客户云主机信息'),
                    array('id' => 'viewpass', 'title' => '查看密码',
                        'act' => 'pop:clients/aliinfo/viewpass', 'show_name' => '查看密码'),
                    array('id' => 'change_pass', 'title' => '修改密码',
                        'act' => 'pop:clients/aliinfo/change_pass&app_scene=edit', 'show_name' => '修改密码',
                        'show_cond' => 'obj.is_buildin != 1'),
                    array('id' => 'forreset_pass', 'title' => '强制重置密码',
                        'act'=>'pop:clients/aliinfo/forreset_pass&app_scene=edit', 'show_name' => '强制重置密码',
                        'show_cond'=>'obj.is_buildin != 1'),
                    array('id' => 'reset_pass', 'title' => '自动重置密码',
                        'show_name' => '自动重置密码','callback' => 'do_reset_pass'),
                    array('id'=>'net_test', 'title' => '连接测试', 
                		 'show_name'=>'连接测试','callback'=>'do_net_test'),
                    array('id' => 'do_deployment', 'title' => '部署',
                        'callback' => 'do_deployment', 'show_cond' => 'obj.ali_deployment != 1',),
                    array('id'=>'enable', 'title' => '启用', 
                		'callback'=>'do_enable', 'show_cond'=>'obj.ali_state != 1'),
                    array('id'=>'disable', 'title' => '停用', 
                            'callback'=>'do_disable', 'show_cond'=>'obj.ali_state == 1', 
                            'confirm'=>'确认要停用吗？'),
                ),
            )
        )
    ),
    'dataset' => 'clients/AliModel::get_aliserver_info',
    'queryBy' => 'searchForm',
    'idField' => 'host_id',
    //'RowNumber'=>true,
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => array('ref_button' => 'view')),
));
?>
<script type="text/javascript">
    function do_deployment(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('clients/aliinfo/do_deployment'); ?>',
            data: {host_id: row.host_id},
            success: function(ret) {
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

    $(".btn_reset_pwd").click(function(){
        var itemlist=tableGrid.getSelection();
        if(itemlist.length!=0){
//            JSON.stringify(itemlist)
            BUI.Message.Confirm("确认重置密码", function(){
                $.ajax({type: 'POST', dataType: 'json',
                     url: '<?php echo get_app_url('clients/aliinfo/do_change_passwd'); ?>',
                     data: {hostdata: JSON.stringify(itemlist)},
                     success: function(ret) {
                         var type = ret.status == 1 ? 'success' : 'error';
                         if (type == 'success') {
                             var strmsg="修改服务器总数:"+ret.data.alllen+",密码成功数:"+ret.data.success_num+",密码失败数:"+ret.data.faild_num;
                             BUI.Message.Alert(strmsg, type);
                             tableStore.load();
                         } else {
                             BUI.Message.Alert(ret.message, type);
                         }
                     }
                 });  
            },'question');
        }else{
            BUI.Message.Alert('请选择服务器','warning');
        }
    });
    
    $(".btn_reset_all_pwd").click(function(){
        BUI.Message.Alert("功能暂时停用", "info");
        /*BUI.Message.Confirm("确认重置所有客户密码", function(){
            $.ajax({type: 'POST', dataType: 'json',
                 url: '<?php echo get_app_url('clients/aliinfo/do_change_allpwd'); ?>',
                 success: function(ret) {
                     var type = ret.status == 1 ? 'success' : 'error';
                     if (type == 'success') {
                         var strmsg="修改服务器总数:"+ret.data.alllen+",密码成功数:"+ret.data.success_num+",密码失败数:"+ret.data.faild_num;
                         BUI.Message.Alert(strmsg, type);
                     } else {
                         BUI.Message.Alert(ret.message, type);
                     }
                 }
             });  
        },'question');*/
}); 


    //单条记录密码重置
    function do_reset_pass(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('clients/aliinfo/do_reset_pass'); ?>',
            data: {host_id: row.host_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var strmsg="操作完成,ROOT密码重置"+ret.data.rootstate+",WEB密码重置:"+ret.data.webstate;
                    BUI.Message.Alert(strmsg, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    
    function do_net_test(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('clients/aliinfo/client_net_test');?>',
        data: {_id: row.host_id}, 
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

    //批量执行所有服务器命令
    $(".btn_run_all_command").click(function(){
        BUI.Message.Confirm("确认重置所有客户密码", function(){
            $.ajax({type: 'POST', dataType: 'json',
                 url: '<?php echo get_app_url('clients/aliinfo/do_run_command'); ?>',
                 success: function(ret) {
                     var type = ret.status == 1 ? 'success' : 'error';
                     if (type == 'success') {
                         var strmsg="修改服务器总数:"+ret.data.alllen+",执行成功数:"+ret.data.success_num+",执行失败数:"+ret.data.faild_num;
                         BUI.Message.Alert(strmsg, type);
                     } else {
                         BUI.Message.Alert(ret.message, type);
                     }
                 }
             });  
        },'question');
}); 



    //批量移动所有服务器目录
    $(".btn_mv_all_command").click(function(){
        BUI.Message.Confirm("确认移动所有服务器文件吗", function(){
            $.ajax({type: 'POST', dataType: 'json',
                 url: '<?php echo get_app_url('clients/aliinfo/do_mv_command'); ?>',
                 success: function(ret) {
                     var type = ret.status == 1 ? 'success' : 'error';
                     if (type == 'success') {
                         var strmsg="移动服务器总数:"+ret.data.alllen+",执行成功数:"+ret.data.success_num+",执行失败数:"+ret.data.faild_num;
                         BUI.Message.Alert(strmsg, type);
                     } else {
                         BUI.Message.Alert(ret.message, type);
                     }
                 }
             });  
        },'question');
}); 


function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
        var url='<?php echo get_app_url('clients/aliinfo/set_active');?>';
	$.ajax({ type: 'POST', dataType: 'json',  
            url:url+"_"+active,
            data: {host_id: row.host_id, type: active}, 
            success: function(ret) {
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


</script>
