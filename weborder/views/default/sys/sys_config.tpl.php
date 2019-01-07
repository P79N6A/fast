<?php render_control('PageHead', 'head1',
array('title'=>'系统参数设置'));?>


<div id="tab">
<ul>
<?php
foreach($response['sys_conf'] as $k=>$row){
	$v = $k == 0 ? ' active' : '';
	echo "<li class='bui-tab-panel-item {$v}'><a href='#'>{$row['title']}</a></li>";
}
?>
</ul>
</div>
<form action="?app_act=sys_config/save" method="post">
<div id="panel" class="">
<?php
//echo '<hr/>$response<xmp>'.var_export($response['sys_conf'],true).'</xmp>';die;

foreach($response['sys_conf'] as $k=>$row){
	$ks = $k+1;
	echo "<table class='table table-striped' id='p".$ks."'>";
	echo "<thead><tr><th>参数编号</th><th>参数名称</th><th>参数值</th><th>说明</th></thead><tbody>";
	foreach($row['child'] as $v){
		if($v['type'] == 'text'){
			$html = "<input type='text' name='{$v['code']}' value='{$v['value']}'/>";
		}
		if($v['type'] == 'checkbox'){
			$chked = $v['value'] == 1 ? 'checked' : '';
			$html = "<input type='checkbox' name='{$v['code']}' value='1' {$chked}/>";
		}
		if($v['type'] == 'select'){
			$html = "<select name='{$v['code']}'>";
			$html .= "</select>";
		}
		echo "<tr><td>{$v['cid']}</td><td>{$v['title']}</td><td>{$html}</td><td>&nbsp;</td></tr>";
	}
	echo "<tbody></table>";
}
?>
</div>
 <div class="row">
		<div class="form-actions offset3">
		<button type="submit" class="button button-primary">保存</button>
		<button type="reset" class="button">重置</button>
		</div>
	</div>
</form>


<script type="text/javascript">
	BUI.use(['bui/tab','bui/mask'],function(Tab){
	var tab = new Tab.TabPanel({
	srcNode : '#tab',
	elCls : 'nav-tabs',
	itemStatusCls : {
	'selected' : 'active'
	},
	panelContainer : '#panel'//如果不指定容器的父元素，会自动生成
	//selectedEvent : 'mouseenter',//默认为click,可以更改事件
	});
	tab.render();
	});
</script>