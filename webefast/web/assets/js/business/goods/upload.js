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

function upload_goods_inv(){
	jQuery("#down_button").attr("disabled", true);
	order_num = 0;
    jQuery("#loading").html("");
    var param = getParameter("search");
    ajax_post({
        url: "?app_act=api/sys/goods/get_goods_page",
        data:param,
        async:false,
        alert:false,
        callback:function(data){
            jQuery("#loading").prepend("正在更新库存<br />");
            for(var i=1;i <= data.page;i++){
            	update_by_page(i,param);
            }
        }
    })
    
    jQuery("#loading").prepend("<b style='color:red'>同步完成</b>");
    jQuery("#down_button").attr("disabled", false);
}

function update_by_page(page,search_param){
	ajax_post({
        url: "?app_act=api/sys/goods/get_goods_page&type=2&page="+page,
        data:search_param,
        async:false,
        alert:false,
        callback:function(data){
        	jQuery("#loading").prepend(data.message);
        }
    })
}