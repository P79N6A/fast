<style>
.status_btn{ border:1px solid #efefef; background:#FFF; color:#666; margin-right:2px; border-radius:3px;}
#table_list{ margin-top:8px;}
#service_intro a:hover{TEXT-DECORATION:underline}
#service_intro a{color:red}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '自动服务设置列表',
    'links' => array(
       // array('url' => 'sys/schedule/open_service&app_fmt=json', 'title' => '一键开启服务'),
        //array('url' => 'sys/schedule/close_service&app_fmt=json', 'title' => '一键关闭服务'),
        array('title' => '一键开启服务', 'type' => 'js','js'=>"open_close_service('open_service');"),
        array('title' => '一键关闭服务', 'type' => 'js','js'=>"open_close_service('close_service');"),
    ),
    'ref_table' => 'table'
));
?>


<ul class="nav-tabs oms_tabs">

	<li><a href="#" id="0" >系统自动服务</a></li>
	<li><a href="#" id="1">平台自动服务</a></li>
	<li><a href="#" id="2" >WMS自动服务</a></li>
	<li><a href="#" id="3">ERP系统自动服务</a></li>
	<li><a href="#" id="4">高级应用服务</a></li>
        <?php if(isset($response['service_sap']) && $response['service_sap'] == true) { ?>
            <li><a href="#" id="5">SAP系统自动服务</a></li>
        <?php } ?>
        <li><a href="#" id="6">系统集成自动服务</a></li>
</ul>
<div id="service_intro" style="text-align:right;margin-top:-29px;"><a target='_blank' href="http://operate.baotayun.com:8080/efast365-help/?p=714#11.1.4自动服务设置">一键开启、关闭服务说明</a></div>
<div id="table_list">
</div>
<input type="hidden" name="execute_tast_code" id="execute_tast_code" value="">
<script type="text/javascript">
$(function(){
	_do_set_tab("1");
	$(".oms_tabs").find('li').eq(1).addClass("active");
});

function changeType(id,type) {

	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/schedule/update_active');

?>',
    data: {id: id, type: type},
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

//立即执行
function execute_right_off(code,id) {
	$.ajax({ type: 'POST', dataType: 'json',
	    url: '<?php echo get_app_url('sys/schedule/execute_right_off');?>',
	    data: {code: code},
	    success: function(ret) {
	    	var type = (ret.status == 1 || ret.status == -5) ? 'success' : 'error';
	    	if (type == 'success') {
	    		$("#"+id).html('正在执行');
	    		$("#"+id).attr('disabled',true);
	    		var tast_code = $("#execute_tast_code").val();
	    		if (tast_code) {
	    			var new_tast_code = tast_code+','+code;
		    	} else {
		    		var new_tast_code = code;
		    	}
	    		
	    		$("#execute_tast_code").val(new_tast_code);
	        	setTimeout('check_execute_status()',5000);
	    	} else {
	        	BUI.Message.Alert(ret.message, type);
	    	}
	    }
	});
}
function execute_right_off_api(code,id) {
$("#"+id).html('正在执行');
$("#"+id).attr('disabled',true);
$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('sys/schedule/execute_right_off_api');?>',
    data: {code: code},
    success: function(ret) {
        $("#"+id).html('立即执行');
        $("#"+id).attr('disabled',false);
//        if (ret.status != 1) {
//            BUI.Message.Alert(ret.message, 'error');
//        }
        var tast_code = $("#execute_tast_code").val();
        if (tast_code) {
                var new_tast_code = tast_code+','+code;
        } else {
                var new_tast_code = code;
        }

        $("#execute_tast_code").val(new_tast_code);
       
    }
});
}
//查看任务执行状态
function check_execute_status(){
	var tast_code = $("#execute_tast_code").val();
	if (tast_code){
		$.ajax({ type: 'POST', dataType: 'json',
		    url: '<?php echo get_app_url('sys/schedule/check_execute_status');?>',
		    data: {code: tast_code},
		    success: function(ret) {
		    	var type = ret.status == 1 ? 'success' : 'error';
		    	if (type == 'success') {
			    	jQuery.each(ret.data, function(i,val) {
				    	jQuery.each(val, function(ii,vv) {
					    	if(vv == 2) {
					    		$("#"+ii+'_id').html('立即执行');
					    		$("#"+ii+'_id').attr('disabled',false);
					    		var tast_code_arr = tast_code.split(",");
					    		jQuery.each(tast_code_arr, function(k,v){
									if(ii == v){
										tast_code_arr.splice(k,1);
										return false;
									}
							    });
							    var new_tast_code = tast_code_arr.join(',');
							    $("#execute_tast_code").val(new_tast_code);
					    	} else if(vv == 3) {
                                                    $("#"+ii+'_id').html('立即执行');
                                                    $("#"+ii+'_id').attr('disabled',false);
                                                    BUI.Message.Alert('执行进程暂停！', 'info');
                                                } else if(vv == 4) {
                                                    $("#"+ii+'_id').html('立即执行');
                                                    $("#"+ii+'_id').attr('disabled',false);
                                                    BUI.Message.Alert('执行进程异常！', 'error');
                                                }
				    	});
			    	});
			    	setTimeout('check_execute_status()',5000);
		    	} else {
		        	BUI.Message.Alert(ret.message, type);
		    	}
			}
		});
	}
}

	$(document).ready(function(){
		//TAB选项卡
		$(".oms_tabs a").click(function(){
			$(".oms_tabs").find(".active").removeClass("active");
			$(this).parent("li").addClass("active");
			var tab = $(".oms_tabs").find(".active").find("a").attr("id");
			_do_set_tab(tab);			
		})

	})
	
	function _do_set_tab(tab) {
	    var url= '<?php echo get_app_url('sys/schedule/table');?>'+'&app_page=NULL&type='+tab;
	
		$.get(url,function(ret){
			$("#table_list").html(ret);
			$("#btn-search").click();
		});
		
	}

	function open_close_service(type){
		$.ajax({ 
			type: 'POST', 
			dataType: 'json',
			url: '<?php echo get_app_url('sys/schedule/open_close_service&app_fmt=json');?>',
		    data: {'type':type},
		    success: function(ret) {
		    	var type = ret.status == 1 ? 'success' : 'error';
		    	if (type == 'success') {
			        BUI.Message.Alert(ret.message, type);
			        tableStore.load();
		    	} else {
		        	BUI.Message.Alert(ret.message, type);
		    	}
	   	 	}
		})
	}
</script>