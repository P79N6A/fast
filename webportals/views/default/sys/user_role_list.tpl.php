<style>
.bui-pagingbar{float:left;}
.div_title{padding:6px;font-weight:bold;}
</style>
<input type="hidden" id="user_id" value="<?php echo $request['user_id'];?>" />
<?php
render_control ( 'SearchForm', 'searchForm', array (
		'cmd' => array (
				'label' => '查询',
				'id' => 'btn-search' 
		),
		'fields' => array (
				array (
						'label' => '代码/名称',
						'title' => '代码/名称',
						'type' => 'input',
						'id' => 'keyword' 
				),
		) 
) );
?>
<!--
<div id="bar"></div>
-->


<div class="row-fluid">
  <div class="span12">
  	<div class="div_title">可选角色列表</div>
	<?php
	render_control ( 'DataTable', 'DataTable2', array (
			'conf' => array (
					'list' => array (
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '角色代码',
									'field' => 'role_code_txt',
									'width' => '100',
									'align' => '' 
							),
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '角色名称',
									'field' => 'role_name',
									'width' => '100',
									'align' => '' 
							),
					) 
			),
			'dataset' => array('sys/UserModel::get_role_list_noset', array($request['_id'])),
			'queryBy' => 'searchForm',
			'idField' => 'role_code',
			'CheckSelection'=>true,
	) );
	echo "<div id='div_pgbar3'></div>";
	?>
  </div>

  <div class="span1">
  	<a href="javascript:user_add_role();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0114;</a>
   	<a href="javascript:user_remove_role();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0112;</a> 	
  </div>

  <div class="span11">
  	<div class="div_title">已选角色列表</div>  
	<?php
	render_control ( 'DataTable', 'DataTable3', array (
			'conf' => array (
					'list' => array (
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '角色代码',
									'field' => 'role_code_txt',
									'width' => '100',
									'align' => '' 
							),
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '角色名称',
									'field' => 'role_name',
									'width' => '100',
									'align' => '' 
							),
					) 
			),
			'dataset' => array('sys/UserModel::get_role_list', array($request['_id'])),
			'idField' => 'role_id_code',
			'CheckSelection'=>true,			
	) );
	echo "<div id='div_pgbar6'></div>";	
	?>
  </div>
</div>



<script type="text/javascript">
var selectWindow = null;
function test2(objs) {
	selectWindow.close();
	if (objs.length < 1) {
	    return ;
	}
	var ids = '';
	for(var i = 0; i < objs.length; i++) {
		ids += ','+objs[i].role_id;
	}
	ids = ids.substr(1);
	var params = {user_id: <?php echo $request['_id']?>, role_ids: ids };
	$.post('<?php echo get_app_url('sys/user/role_add');?>', params, function(data) {
		var ret = eval('('+data+')');
		var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
    	    BUI.Message.Alert(ret.message, type);
    	    DataTable2Store.load();
    	} else {
    	    BUI.Message.Alert(ret.message, type);
    	}
	});
}
BUI.use('bui/toolbar',function(Toolbar){
	var bar = new Toolbar.Bar({
		render : '#bar',
		elCls: 'toolbar',
		children : [
        {
            xtype:'spacer',
            width : 10
        },
		{
			xtype:'button',
			btnCls : 'button button-success',
			text:'新增',

			handler:function(event){
				selectWindow = new ESUI.PopSelectWindow('<?php echo get_app_url('common/select/role');?>', 'test2', {title: '角色列表',
		            width: 900,
		            height:600,
		            ES_pFrmId: '<?php echo $request['ES_frmId']?>'
		            });
				selectWindow.show();
			}

		},
		{
			xtype:'separator'
		},
		{
			xtype:'button',
			btnCls : 'button button-danger',
			text:'<i class="icon-white icon-trash"></i>删除',
			listeners : {
			'click':function(event){
				var objs = DataTable2Grid.getSelection();
				if (objs.length < 1) {
					BUI.Message.Alert('请至少选择一条记录','error');
					return ;
				}
				var ids = '';
				for(var i = 0; i < objs.length; i++) {
					ids += ','+objs[i].role_id;
				}
				ids = ids.substr(1);
				BUI.Message.Confirm('确认要删除么？',function(){
					var params = {user_id: <?php echo $request['_id']?>, role_ids: ids };
					$.post('<?php echo get_app_url('sys/user/role_delete');?>', params, function(data) {
						var ret = eval('('+data+')');
						var type = ret.status == 1 ? 'success' : 'error';
				    	if (type == 'success') {
				    	    BUI.Message.Alert(ret.message, type);
				    	    DataTable2Store.load();
				    	} else {
				    	    BUI.Message.Alert(ret.message, type);
				    	}
					});
    	          
    	        },'question');
			}
			}
		}
		]
	});
	bar.render();
});

function set_pagebar(){
  $("#bar3").wrap("<div id='t_bar3'></div>");
  $("#bar6").wrap("<div id='t_bar6'></div>");

  $('#div_pgbar3').html($('#t_bar3').html());
  $('#div_pgbar6').html($('#t_bar6').html());

  $('#t_bar3').empty();
  $('#t_bar6').empty();  
}
$(document).ready(function(){
	setTimeout("set_pagebar()",1000);
});

function user_remove_role(){
	var sel_role_id_arr = new Array();
	$("#DataTable3 .bui-grid-row-selected input").each(function(){
	 	sel_role_id_arr.push($(this).val()); 
	});
	var sel_role_id = sel_role_id_arr.join(',');
	if (sel_role_id == ''){
		alert('请至少选择一条记录');
		return;
	}
    var params = {user_id: <?php echo $request['user_id']?>, sel_role_id: sel_role_id };	
	$.get("?app_act=sys/user/user_remove_role",params,function(data){
	  var obj = {'start':1};
	  DataTable2Store.load(obj);
	  DataTable3Store.load(obj);	  
	});
}

function user_add_role(){
	var sel_role_id_arr = new Array();
	$("#DataTable2 .bui-grid-row-selected input").each(function(){
	 	sel_role_id_arr.push($(this).val()); 
	});
	var sel_role_id = sel_role_id_arr.join(',');
	if (sel_role_id == ''){
		alert('请至少选择一条记录');
		return;
	}
    var params = {user_id: <?php echo $request['user_id']?>, sel_role_id: sel_role_id };
	$.get("?app_act=sys/user/user_add_role", params, function(data){
	  var obj = {'start':1};
	  DataTable2Store.load(obj);
	  DataTable3Store.load(obj);
	});	
}
</script>