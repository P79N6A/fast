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

function down_goods(){
	jQuery("#down_button").attr("disabled", true);
	order_num = 0;
    jQuery("#loading").html("");
    var param = getParameter("search");
    ajax_post({
        url: "?app_act=api/sys/goods/down_action",
        data:param,
        async:false,
        alert:false,
        callback:function(data){
            jQuery("#loading").prepend("正在下载商品");
            down_arr = data;
            down_by_time(down_arr,param);
            
        }
    })
    
    jQuery("#loading").prepend("<b style='color:red'>下载完成</b>");
    jQuery("#down_button").attr("disabled", false);
}

function down_by_time(data,search_param){
	for(var i=order_num;i < data.length;i++){
		if(!down){
			break;
		}
        var param = data[i];
        ajax_post({
            url: "?app_act=sys/task/get_goods",
            data:{shop_code:param.shop_code,start_time:param.start,end_time:param.end,status:search_param.parameter.status},
            async:false,
            alert:false,
            callback:function(data){
            	jQuery("#loading").prepend(data.message);
            }
        })
        order_num = i;
    }
}