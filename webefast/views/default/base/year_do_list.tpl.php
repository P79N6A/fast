<?php 
$is_power = load_model('sys/PrivilegeModel')->check_priv('base/year/detail&app_scene=add');
$links = '';
if($is_power == true){
    $links = array(array('url'=>'base/year/detail&app_scene=add', 'title'=>'添加年份', 'is_pop'=>true, 'pop_size'=>'500,400'));
}
    render_control('PageHead', 'head1',
        array(
            'title'=>'年份',
            'links'=>$links,
            'ref_table'=>'table'
        )
    );
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
    				'label' => '名称/代码',
    				'type' => 'input',
    				'id' => 'code_name'
    		),	
    ) 
) );
?>

<div>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('base/year/opt_delete')) {?>
        <ul id="ToolBar1" class="toolbar frontool">      
            <li class="li_btns"><button class="button button-primary btn_opt_pending" id="opt_delete">批量删除</button></li>   
        <div class="front_close">&lt;</div>
        </ul>
    <?php }?>
    <script>    
    $(function(){
        function tools(){
            $(".frontool").css({left:'0px'});
            $(".front_close").click(function(){
                if($(this).html()=="&lt;"){
                    $(".frontool").animate({left:'-100%'},1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right:'-10px'},1000);
                }else{
                    $(".frontool").animate({left:'0px'},1);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right:'0'},1000);
                }
            });
        }        
        tools();
    });
    </script>
</div>
<?php

render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
		
			array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'pop:base/year/detail&app_scene=edit', 'show_name'=>'编辑','priv' => 'base/year/detail&app_scene=edit', 
                		'show_cond'=>'obj.is_buildin != 1'),
                	//array('id'=>'reset_password', 'title' => '重设密码', 'callback'=>'do_reset_pwd'),
                	
                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','priv' => 'base/year/do_delete','confirm'=>'确认要删除此信息吗？'),
                	
                ),
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'year_code',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'year_name',
                'width' => '100',
                'align' => '' 
            ),
            
        ) 
    ),
    'dataset' => 'base/YearModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'year_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
    //读取已选中项
    function get_checked(obj, func, type) {
        var ids = new Array();      
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择数据！", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.year_id);
        }
        ids.join(',');             
        BUI.Message.Show({
            title: '批量操作',
            msg: '是否执行年份的批量删除?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });      
    }
    
    $("#opt_delete").click(function(){
        get_checked($(this), function (ids) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('base/year/opt_delete'); ?>', data: {year_id: ids},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功!', type);                  
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                    tableStore.load();
                }
            });
        });     
    });

function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',  
    url: '<?php echo get_app_url('base/year/do_delete');?>', data: {year_id: row.year_id}, 
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


</script>
