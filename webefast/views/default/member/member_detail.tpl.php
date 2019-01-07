<?php
/* 
render_control('PageHead', 'head1',
array('title'=>isset($app['title']) ? $app['title'] : '添加仓库模板',
	'links'=>array(
		'cangku/cangku/do_list'=>'仓库列表'
	)
));
*/
?>
<?php
/* 
render_control('Form', 'member', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'会员名称', 'type'=>'input', 'field'=>'user_name', ),
			array('title'=>'昵称', 'type'=>'input', 'field'=>'user_nick', ),
			
		), 
		'hidden_fields'=>array(array('field'=>'store_id')), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'member/member/do_edit', //edit,add,view
	'act_add'=>'member/member/do_add',
	'data'=>$response['member'],
	'rules'=>array(
		array('user_name', 'require'),
	)
)); 

if(($response['consignee'])){
	$options = array();
	$k = 0;
	
	foreach ($response['consignee'] as $key=>$value){
		$options['conf']['fields'][$k] = array('title'=>'街道地址', 'type'=>'input', 'field'=>'address_'.$value['consignee_id'],);
		$k++;
		$options['conf']['fields'][$k] = array('title'=>'收货人', 'type'=>'input', 'field'=>'consignee_'.$value['consignee_id'],);
		//$options['hidden_fields'] = array(array('field'=>'consignee_id_'.$value['consignee_id']));
		$k++;
		$options['conf']['fields'][$k] = array('title'=>'id', 'type'=>'input', 'field'=>'consignee_id_'.$value['consignee_id'],);
		$k++;
		foreach ($value as $k=>$v){
			
			$options['data'][$k.'_'.$value['consignee_id']] = $v;
		}		
		//$options['data'] = $value;
	}
	
	$options['act_edit'] = 'member/consignee/do_edit';
	$options['act_add'] = 'member/consignee/do_edit';
	$options['buttons'] = array(
				array('label'=>'提交', 'type'=>'submit'),
				array('label'=>'重置', 'type'=>'reset'),
		);
	render_control('Form', 'consignee',$options);
}
*/
?>

<?php echo load_js('jquery.min.js');?>
<style>
.switch_root_open {
    background: url(<?php echo get_theme_url('css/zTreeStyle/img/minus_root.gif')?>) repeat scroll 0 0 transparent;
}
.switch_root_close {
    background: url(<?php echo get_theme_url('css/zTreeStyle/img/plus_root.gif')?>) repeat scroll 0 0 transparent;
}
.first-cell button {
	background-color: transparent;background-position: 0 0;background-repeat: no-repeat;border: 0 none;cursor: pointer;
    height: 18px;margin: 0;padding: 0;vertical-align: middle;width: 18px;
}
label{
　　white-space:nowrap;
}
.checkbox1{
	vertical-align:text-bottom;margin-bottom:2px;*margin-bottom:5px;
}
/*acl_action中type=act的权限设置样式 区分url类型*/
.action {
	color: blue; font-style: italic
}
table {
    display: table;
    border-collapse: separate;
    border-spacing: 2px;
    border-color: gray;
}
</style>
<?php render_control('PageHead', 'head1',
array('title'=>'分配权限['.$response['role']['role_code'].'-'.$response['role']['role_name'].']',
	'links'=>array(
		get_app_url('sys/role/do_list')=>'角色列表'
	)
));?>
<?php  
 $aa[0] = array('title'=>'订单管理1') ;
 $aa[1] = array('title'=>'订单管理2') ;
 $aa[2] = array('title'=>'订单管理3') ;
 
?>
<!-- 分派角色权限 -->
<form method="POST" action="<?php echo get_app_url('sys/role/update_allot')?>" name="theFrom">
	<!-- 加载tab -->
	<?php render_control(' ', 'tab1', array('for'=>'tabs', 'tabs'=>$aa));?>
	<div id="tabs">
		<!-- 各个tab页中的权限数据 -->
		<?php foreach ($response['menu_tree'] as $cote):?>
			<div style="width: 100%;" id="tabs-<?php echo $cote['action_id'];?>" class="tab-content">
				<table cellspacing='1' style="width: 100%;"  class="bui-grid-table">
					<?php foreach ($cote['_child'] as $group):$group_id=$group['action_id'];?>
					 <tr>
					  <td width="20%" valign="top" style="text-align: left;" class="first-cell">
					    <button onclick="switchGroup(<?php echo $group['action_id'];?>)" title="" id="btn_<?php echo $group['action_id'];?>" class="switch_root_close" type="button"></button>
					    <input id="<?php echo $group_id;?>" name="action_id[]" type="checkbox" value="<?php echo $group_id?>" onclick="check(this, 'group', '<?php echo $cote['action_id'];?>');" class="checkbox1" <?php if($group['_is_active'] == 1):?> checked="checked" <?php endif;?> />
					    <label for="<?php echo $group_id;?>"><?php echo $group['action_name']?></label>
					  </td>
					  <td style="text-align: left;">
					  	<div id="list_<?php echo $group_id?>" >
						    <?php foreach ($group['_child'] as $url):$url_id=$url['action_id'];?>
							    <div style="float:left;width:100%;">
							        <input id="<?php echo $url_id;?>" name="action_id[]" type="checkbox" value="<?php echo $url_id?>" onclick="check(this,'url', '<?php echo $group_id;?>');" class="checkbox1" <?php if($url['_is_active'] == 1):?> checked="checked" <?php endif;?> />
					        		<label for="<?php echo $url_id;?>"><?php echo $url['action_name']?></label>
							    </div>
						    <?php endforeach;?>
					    </div>
					  </td>
					</tr>
					<?php endforeach;?>
				</table>
			</div>
		<?php endforeach;?>
	</div><!-- tabs div end -->
	<table style="width: 100%;">
		<tr>
		    <td align="center" colspan="2" >
		      <input type="checkbox" name="checkall" value="checkbox" class="checkbox1" id="check-tab"/><label for="check-tab">全选当前选项页</label>
		      &nbsp;&nbsp;&nbsp;&nbsp;
		      <input type="button" id="submit" name="submit"   value=" 保存 " class="button button-primary" />
		      <input type="hidden" id="role_id" name="role_id"    value="<?php echo $response['allot']['role_id']?>" />
		    </td>
	    </tr>
	</table>
</form>

<script type="text/javascript">
g_isModified = false;
/**
 * 标识是否修改了信息(即是否需要离开页面时的提示)
 * @return
 *		true 标识修改了信息
 *		false 表示没有修改信息
 */
function cb_isModified() {
	return g_isModified;
}

function check(obj, type, parent_id) {
	id = $(obj).val();
	if (type == 'group') {
		//$('#list_'+type).toggle();
		if ($(obj).attr('checked') == 'checked') {
			$('#list_'+id+" input[type=checkbox]").attr('checked', 'checked')
		} else {
			$('#list_'+id+" input[type=checkbox]").attr('checked', false)
		}
	} else if (type == 'url') {
		if ($('#list_'+parent_id+" input[type=checkbox]:checked").length > 0) {
			  $('#'+parent_id).attr('checked', 'checked');
		} else {
			 $('#'+parent_id).attr('checked',  false);
		}
	}
}

$(function() {
    $('#check-tab').click(function() {
        var cbs = $('.tab-content:visible input[type=checkbox]');
        if ($(this).attr('checked') == 'checked') {
        	cbs.attr('checked', 'checked');
        } else {
        	cbs.attr('checked', false);
        }
    });
	$('input[type=checkbox]').change(function() {
		g_isModified = true;
	});
	// end load
	$('#submit').click(function() {
		var action_id = "";
		$("[name='action_id[]']:checked").each(function(){
			action_id += $(this).val()+",";
        })
        if(action_id == ''){
            alert('请选择一项权限');
            return false;
        }
		var params = {'do': 1};
			params.role_id = $('#role_id').val();
			params.action_id = action_id;

		$('#submit').attr('disabled', true);
		var url = '<?php echo get_app_url('sys/role/update_allot')?>';
		var listurl = '<?php echo get_app_url('sys/role/do_list')?>';
		$.post(url, params, function(data) {
			try {
				var ret = eval('('+data+')');
				if (ret.status == 1) {
					BUI.Message.Alert(ret.message, 'success');
					g_isModified = false;
				}else{
					BUI.Message.Alert(ret.message, 'error');
					
				}
			} catch (e) {
				BUI.Message.Alert(e, 'error');
			}
			$('#submit').attr('disabled', false);
		});
	});
});

function switchGroup(priv_id){
	if($('#list_'+priv_id).is(":hidden")){
		$('#list_'+priv_id).css("display", "block");
		$('#btn_'+priv_id).attr("class", "switch_root_open");
	}else{
		$('#list_'+priv_id).css("display", "none");
		$('#btn_'+priv_id).attr("class", "switch_root_close");
	}
}

<?php echo_hint_when_close('您正在分派角色权限，确认要放弃吗？');?>
</script>




