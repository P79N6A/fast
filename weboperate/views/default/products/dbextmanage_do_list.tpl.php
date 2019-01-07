<?php
render_control('PageHead', 'head1', array('title' => '产品数据库扩展管理',
    'links' => array(
        array('js'=>'addrem_db();','title'=>'新增数据库', 'type'=>'js'),
    ),
    'ref_table' => 'table'  //ref_table 表示是否刷新父页面
));
?>
<div id="Form1">
    <?php
render_control ( 'SearchForm', 'searchForm1', array (
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
            'id' => 'product',
            'data'=>ds_get_select('chanpin',1)
        ),
    ) 
) );
?>
</div>


<div id="Form2" class="hide">
    <?php
render_control ( 'SearchForm', 'searchForm2', array (
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
            'id' => 'product',
            'data'=>ds_get_select('chanpin',1)
        ),
        array(
            'label' => '客户名称',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'kh_name'
        ),
        array (
            'label' => '是否启用',
            'title' => '是否启用',
            'type' => 'select',
            'id' => 'rem_db_is_bindkh',
            'data'=>ds_get_select_by_field('boolstate')
        ),
    )
    
) );
?>
</div>



<ul class="nav-tabs oms_tabs">
	<li class="active"><a href="#" id="table1">未绑定客户数据库</a></li>
	<li ><a href="#" id="table2">已绑定客户数据库</a></li>

</ul>


<div id="TabPageContents">
    <div id="panel1" class="panel" >
        <div class="panel-body">
            <?php
            render_control('DataTable', 'tb_table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'RDS信息',
                            'field' => 'rem_db_pid_name',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数据库名称',
                            'field' => 'rem_db_name',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '版本号',
                            'field' => 'rem_db_version_name',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '创建时间',
                            'field' => 'rem_db_createdate',
                            'width' => '200',
                            'align' => ''
                        ),
                        array (
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '200',
                            'align' => '',
                            'buttons' => array (
                                    array('id' => 'del1','title' => '删除', 'show_name'=>'删除','callback'=>'del_no_bind','confirm' => '确认要删除吗？'),
                                    array('id'=>'databind', 'title' => '绑定', 'callback'=>'do_databind', 'show_cond'=>'obj.rem_db_is_bindkh != 1'),
                            ),
                        )
                    )
                ),
                'dataset' => 'products/dbextmanageModel::get_by_page',
                'queryBy' => 'searchForm1',
                'params' => array('filter' => array('bind_kh' => '0')),
                'idField' => 'rem_db_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
    <div id="panel2" class="panel hide">
        <div class="panel-body">
            <?php
            render_control('DataTable', 'tb_table2', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'RDS信息',
                            'field' => 'rem_db_pid_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数据库名称',
                            'field' => 'rem_db_name',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'checkbox',
                            'show' => 1,
                            'title' => '绑定客户',
                            'field' => 'rem_db_is_bindkh',
                            'width' => '100',
                            'align' => '',
//                            'format'=>array('type'=>'map', 'value'=>ds_get_field('is_bind_kh'))
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '客户名称',
                            'field' => 'rem_db_khid_name',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'checkbox',
                            'show' => 1,
                            'title' => '试用客户',
                            'field' => 'rem_try_kh',
                            'width' => '100',
                            'align' => '',
                            //'format'=>array('type'=>'map', 'value'=>ds_get_field('is_try_kh'))
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '版本号',
                            'field' => 'rem_db_version_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '自动服务IP',
                            'field' => 'rem_db_version_ip',
                            'width' => '120',
                            'align' => ''
                        ),
                       array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '接口服务IP',
                            'field' => 'rem_db_api_ip',
                            'width' => '120',
                            'align' => ''
                        ),
                        array (
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '120',
                            'align' => '',
                            'buttons' => array(
                                array('id' => 'del2', 'title' => '删除', 'show_cond' => "obj.rem_try_kh == '1'", 'callback' => 'delete_try_client', 'confirm' => '确认要删除吗？',),
                                array('id' => 'viewpass', 'title' => '查看密码', 'act' => 'pop:basedata/rdsinfo/viewpass&type=1', 'show_name' => '查看密码'),
                                array('id' => 'enable', 'title' => '启用', 'show_cond' => "obj.rem_db_is_bindkh == 0", 'callback' => 'update_enable', 'confirm' => '确认要启用吗？',),
                                array('id' => 'disable', 'title' => '停用', 'show_cond' => "obj.rem_db_is_bindkh == 1", 'callback' => 'update_disable', 'confirm' => '确认要停用吗？',),
                            ),
                        )
                    )
                ),
                'dataset' => 'products/dbextmanageModel::get_by_page',
                'queryBy' => 'searchForm2',
              //  'params' => array('filter' => array('bind_kh' => '1')),
                'idField' => 'rem_db_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
</div>
<script>
	$(document).ready(function(){
		//TAB选项卡
		$(".oms_tabs a").click(function(){
                    	$(".oms_tabs").find(".active").removeClass("active");
			$(this).parent("li").addClass("active");
                       
                        var tabName = $(this).attr("id");
                        if(tabName == "table1"){
                    
                            $("#Form1").show();
                            $("#panel1").show();
                            $("#Form2").hide();
                            $("#panel2").hide();
                                    tb_table1Store.load();
                        } else {
                       
                            $("#Form1").hide();
                            $("#panel1").hide();
                            $("#Form2").show();
                            $("#panel2").show();
                                 tb_table2Store.load();
                        }
		});

                $('#Form2').find('#product option[value="21"]').attr('selected',true);
             
            });
            
            function addrem_db(){
                var tabName = $(".oms_tabs").find(".active").children().attr("id");
                if(tabName == "table1"){
//                    alert("现在新增未绑定的");
                      btn_show_dialog('?app_act=products/dbextmanage/show_add_dbextmanage','新增数据库', {w:800,h:400});
                }else{
                    alert("暂时不能添加已绑定客户数据库");
                }
            }


    function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                 
//                tableStore.load();  
                  location.reload();
                if (typeof _opts.callback == 'function') 
                    _opts.callback();
            }
        }).show();
    }
    
    
    function del_no_bind(_index, row) {
    $.ajax({ type: 'POST', dataType: 'json',  
        url:'<?php echo get_app_url('products/dbextmanage/do_del_nobind');?>',
        data: {rem_db_id: row.rem_db_id,rem_db_is_bindkh:row.rem_db_is_bindkh,rem_db_pid: row.rem_db_pid},  
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
//                location.reload();
                tb_table1Store.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

    //删除数据库明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function delete_try_client(_index, row) {
            $.ajax({ type: 'POST', dataType: 'json',  
                url:"<?php echo get_app_url('products/dbextmanage/do_delete_try_client');?>",
                data: {rem_db_id: row.rem_db_id,rem_db_is_bindkh:row.rem_db_is_bindkh,rem_db_pid: row.rem_db_pid}, 
                success: function(ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
//                        location.reload();
                          tb_table2Store.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
    }

    //绑定客户，初始化
    function do_databind(_index, row) {
        var rem_db_id=row.rem_db_id;
        btn_show_dialog('?app_act=products/dbextmanage/bind_dbextmanage&dbid='+rem_db_id,'绑定客户', {w:800,h:400});
    }


    function update_enable(_index, row) {
        update_bind_action(row.rem_db_id, 1);
    }
    function update_disable(_index, row) {
        update_bind_action(row.rem_db_id, 0);
    }

    function update_bind_action(id, is_bind) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('products/dbextmanage/update_bind_action');?>",
            data: {rem_db_id: id, rem_db_is_bindkh: is_bind},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tb_table2Store.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>
