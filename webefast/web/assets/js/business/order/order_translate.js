var change = true;
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

function task_change() {
    $("#change_button").attr("disabled", true);
    order_num = 0;
    jQuery("#loading").html("");
    var param = getParameter("search");
    var shop_code = param['parameter']['shop_code'].split(',');
    for( var i in shop_code){
        change_ajax(shop_code[i],'');
    }
    $("#change_button").attr("disabled", false);
}

function change_ajax(shop_code,pay_time,id){
    ajax_post({
        url: "?app_act=oms/api_order/change_task_action",
        data: {shop_code: shop_code,pay_time:pay_time,id:id},
        async: false,
        alert: false,
        callback: function(data) {
            if (data.status >0 && change) {
                jQuery("#loading").prepend("<b style='color:green'>订单："+data.data.tid+"转单成功</b><br/>");
                window.setTimeout("change_ajax('"+shop_code+"','"+data.data.pay_time+"','"+data.data.id+"')", 100);
            } else if(data.status <= 0 && change) {
                jQuery("#loading").prepend("<b style='color:red'>订单："+data.data.tid+"转单失败,"+data.message+"</b><br/>");
                window.setTimeout("change_ajax('"+shop_code+"','"+data.data.pay_time+"','"+data.data.id+"')", 100);
            }else{
                jQuery("#loading").prepend("<b style='color:red'>商店："+data.data+"转单完成</b><br/>");
            }
        }
    })
}
