<style>
#table{ float:right;}
#table_datatable{ overflow:hidden;}
</style>

<?php 
$is_power = load_model('sys/PrivilegeModel')->check_priv('prm/category/detail&app_scene=add');
$links = '';
if($is_power == true){
    $links = array(array('url'=>'prm/category/detail&app_scene=add', 'title'=>'添加分类', 'is_pop'=>true, 'pop_size'=>'500,400'));
}
render_control('PageHead', 'head1',
        array('title' => '分类',
            'links'=>$links,
            'ref_table' => 'table'
        )
    );
?>
<?php
render_control ('SearchForm', 'searchForm', array ('cmd' => array ('label' => '查询',
            'id' => 'btn-search'
            ),
        'fields' => array (
		        array (
		        		'label' => '名称/代码',
		        		'type' => 'input',
		        		'id' => 'code_name'
		        ),
        		array('label'=>'上级分类', 'type'=>'select_pop', 'id'=>'p_code', 'select'=>'prm/category' ),
            )
        ));

?>
<div class="row">
    <div class="span8 offset3" style="margin-right:10px;">
        <div id="sortTree">
        </div>
    </div>
    <?php

render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
				
				array ('type' => 'button',
                    'show' => 1,
                    'title' => '查看',
                    'field' => '_operate',
                    'width' => '150',
                    'align' => '',
                    'buttons' => array (
                    	array('id'=>'edit', 'title' => '编辑','priv' => 'prm/category/detail&app_scene=edit', 
                    		'act'=>'pop:prm/category/detail&app_scene=edit&p_code={p_code}', 'show_name'=>'编辑'),	 
                    	array('id'=>'delete', 'title' => '删除','priv' => 'prm/category/do_delete', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
                    	array('id'=>'add', 'title' => '新增子分类','priv'=>'prm/category/detail&app_scene=add&child=1',
                    			'act'=>'pop:prm/category/detail&app_scene=add&child=1', 'show_name'=>'新增子分类'),
                      /*  array('id' => 'child', 'title' => '查看下级',
                            'callback' => 'do_list_child','show_cond'=>'obj.has_next == 1'),
                           
                        array('id' => 'parent', 'title' => '查看上级',
                            'callback' => 'do_list_parent','show_cond'=>'obj.has_parent == 1'),*/
                        ),
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '代码',
                    'field' => 'category_code',
                    'width' => '100',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '名称',
                    'field' => 'category_name',
                    'width' => '180',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '描述',
                    'field' => 'remark',
                    'width' => '200',
                    'align' => ''
                    ),
                
                )
            ),
        'dataset' => 'prm/CategoryModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'category_id',
        ));

?>
</div>


    <script type="text/javascript">
        BUI.use(['bui/tree','bui/data'],function (Tree,Data) {
        
      //数据缓冲类
      var store = new Data.TreeStore({
          root : {
            id : '0',
            text : '分类',
            checked : false
          },
          url : '<?php echo get_app_url('prm/category/get_nodes&app_fmt=json');?>',
          autoLoad : true
        });
        
      var tree = new Tree.TreeList({
        render : '#sortTree',
        showLine : true,
        height:'auto',
        store : store,
        checkType : 'all',
        showRoot : true
      });
      tree.render();
      
 
      tree.on('itemclick',function(ev){
        var item = ev.item;
        list_child(item.id);
      });
      
    });
    </script>
<script type="text/javascript">
<!--
function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('prm/category/do_delete');
?>', data: {category_id: row.category_id},
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

function do_list_child(_index, row){
	list_child(row.category_id);
}
function list_child(p_code){
	var obj = {"category_type":"child","p_code":p_code,"type":"","name":""};
	obj.start = 1;
	tableStore.load(obj);
}
function do_list_parent(_index, row){
	var obj = {"category_type":"parent","category_id":row.p_id,"type":"","name":""};
	obj.start = 1;
	//for(var s in obj){
	//	alert(obj[s]);
	//}
	tableStore.load(obj);
}

//-->
</script>
