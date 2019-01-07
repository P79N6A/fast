<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
<title>  </title>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link href="assets/css/dpl.css" rel="stylesheet" type="text/css" />
<link href="assets/css/bui.css" rel="stylesheet" type="text/css" />
<link href="assets/css/main-min.css" rel="stylesheet" type="text/css" />
<link href="assets/css/common.css" rel="stylesheet" type="text/css" />
</head>
<style>
table td{padding:12px;border-bottom: 1px #ccc solid;}
</style>
<body style="overflow-x:hidden;">
<?php include get_tpl_path('web_page_top'); ?>
<style>
.bui-tab-item{
position: relative;
}
.bui-tab-item .bui-tab-item-text{
padding-right: 25px;
}
 
.bui-tab-item .icon-remove{
position: absolute;
right: 2px;
top:2px;
z-index: 20;
cursor: pointer;
}

#panel div{padding:6px;}
</style>
<div id="tab">
<ul>
<li class="bui-tab-panel-item active"><a href="#">版本</a></li>
<li class="bui-tab-panel-item"><a href="#" onclick="load_patch_list()">补丁</a></li>
</ul>
</div>
<div id="panel" class="">

	<div id="p1">	
		<table>
		<tr><th>主版本号</th><th>版本更新日期</th><th>版本特性</th></tr>
		<?php 
		 foreach($response['version'] as $row){
		 	echo "<tr><td>{$row['version_num']}</td><td>{$row['update_time']}</td><td><a href='{$row['about_url']}' target='_blank'>查看</a></td></tr>";
		 }
		?>
		</table>
	</div>

	<div id="p2">
		<select id="parent_version_num" onchange="load_patch_list()">
		<?php 
		 foreach($response['version'] as $row){
		 	echo "<option value='{$row['version_num']}'>{$row['version_num']}</option>";
		 }
		?>		
		</select>
		<div id="patch_list_div">

		</div>
	</div>

</div>

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

	function load_patch_list(){
		var parent_version_num = $("#parent_version_num").val();
		$.get("?app_act=sys/sys_version/show_patch&parent_version_num="+parent_version_num, function(data){
		  //alert(data);
		  $("#patch_list_div").html(data);
		});
	}
</script>
</body>
</html>