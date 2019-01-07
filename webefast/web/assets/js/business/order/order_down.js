var down = true;
var down_arr;
var order_num = 0;
jQuery(function(){
    jQuery("#search_shop").find("li").each(function(){
        if(jQuery(this).find("input[type='checkbox']").size() == 0){
            jQuery(this).remove();
            var cls = jQuery(this).attr("class");
            cls = cls.replace("shop_","");
            jQuery(".platform_"+cls).remove();
        }
    })
})

function pause(){
	down = false;
}

function goon(){
	down = true;
	var param = getParameter("search");
	task_down_order(down_arr,param);
	task_down_detail(param);
}

function control_down(){
	control_down();
}

function task_down(){
	$("#down_button").attr("disabled", true);
	order_num = 0;
    jQuery("#down_loading").html("");
    var param = getParameter("search");
    ajax_post({
        url: "?app_act=oms/api_order/down_task_action",
        data:param,
        async:false,
        alert:false,
        callback:function(data){
            jQuery("#down_loading").prepend("正在下载订单");
            down_arr = data;
            task_down_order(down_arr,param);
            
        }
    })
    task_down_detail(param);
    jQuery("#down_loading").prepend("<b style='color:red'>下载完成</b>");
    $("#down_button").attr("disabled", false);
}

function task_down_detail(param){
	if(!down)
		return;
    ajax_post({
        url: "?app_act=sys/task/get_detail",
        data:param,
        async:false,
        alert:false,
        callback:function(data){
        	jQuery("#down_loading").prepend(data.message);
        	if(!data.data.next || data.data.next == "false"){
        		return;
        	}else{
        		task_down_detail();
        	}
        }
    })
}

function task_down_order(data,param){
	for(var i=order_num;i < data.length;i++){
		if(!down){
			break;
		}
        var param = data[i];
        ajax_post({
            url: "?app_act=sys/task/get_order",
            data:{shop_code:param.shop_code,start_time:param.start,end_time:param.end},
            async:false,
            alert:false,
            callback:function(data){
            	jQuery("#down_loading").prepend(data.message);
            }
        })
        order_num = i;
    }
}

function down(){
	jQuery("#down_loading").html("");
	var param = getParameter("search");
	ajax_post({
        url: "?app_act=oms/api_order/down_action",
        data:param,
        async:false,
        callback:function(data){
        	jQuery("#down_loading").prepend("正在下载订单");
        	log(data.data);
        }
    })
}

function log(task_id_str){
	var task_id_arr = task_id_str.split(",");
    for(var i=0;i < task_id_arr.length;i++){
        var num = 0;
        show_log(task_id_arr[i],num);
    }
}

function show_log(task_id,num){
	setTimeout(function(){
		var param = getParameter("search");
	    var status = 0;
		ajax_post({
	        url: "?app_act=oms/api_order/get_down_action_log",
	        data:{"task_id":task_id,"num":num},
	        async:false,
	        callback:function(data){
	            jQuery("#down_loading").append(data.msg);
	        	if(data.code != "1"){
                    if(typeof data.log_file_offset != "undefined"){
                        num = data.log_file_offset;
                    }else{
                    	num = 0;
                    }
	        		 show_log(task_id,num);
                }else{
                    return;
                }
	        }
	    })
    },3000);
}