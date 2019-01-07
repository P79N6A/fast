$(function () {
    $("body").css({'overflow':'hidden','width':'100%','height':'100%'});
    change_size();
    
    $(window).resize(function(){
        change_size();
    });
    
	$(".server").click(function(){
                $(".widget").find("a").removeAttr("style");
                if($(".contact").css("display")!='none'){
                    $(this).removeAttr("style");
                }else{
                    $(this).css("background-color","#ec6d3a");
                }
                $(".pwd_contact").stop().slideUp();
		$(".contact").stop().slideToggle();
		});

        //修改密码
        $(".change_pwd").click(function(){
                $(".widget").find("a").removeAttr("style");
                if($(".pwd_contact").css("display")!='none'){
                    $(this).removeAttr("style");
                }else{
                    $(this).css("background-color","#ec6d3a");
                }
                $(".contact").stop().slideUp();
                $(".pwd_contact").stop().slideToggle();
        });
    $(".list li a").each(function (i) {
        $(this).click(function () {
            change_size();
            $(".list li a").removeClass("ac").eq(i).addClass("ac");
            $("#J_NavContent li").removeClass('dl-collapse');
            //$("#J_NavContent .dl-tab-item").eq(i).find('.dl-second-tree').stop().animate({width: '190px'}, 700);
            //$(".left .box").css({"display":"block","width":"800px"});
            //$(".left .box").css({"display":"block","width":"0"}).stop().animate({width: '180px'}, 700);
            //$("#J_NavContent .dl-second-tree").stop().animate({width: '0'}).css("display", "none").eq(i).css("display", "block").stop().animate({width: '190px'}, 700);
        });
    });
    
    /**
    $(".back").click(function () {
        //$(".left .box").animate({width: '0'}, 700);
        //setTimeout("$('.left .box').css('display','none')", 600);
        //$(".list li a").removeClass("ac");
    });
    **/
   setTimeout(function(){
       $.get('?app_act=index/message',function(html){
           $('#J_NavContent .dl-tab-item .dl-second-tree').eq(0).html(html);
       });
   },30);

$("#change_pwd").click(function(){
    var data = {
        current_pwd:$(".pwd_contact input[name=current_pwd]").val(),
        new_pwd:$(".pwd_contact input[name=new_pwd]").val(),
        sure_pwd:$(".pwd_contact input[name=sure_pwd]").val()
    };
    
    $.post("?app_act=index/change_pwd",data,function(ret){
        if(ret.status=='-1'){
            alert(ret.message);
        }else{
            alert(ret.message);
            window.location.reload();
        }
    },'json');
});

$(".pwd_contact").keydown(function(){
        var event = arguments.callee.caller.arguments[0] || window.event;//消除浏览器差异     
        if (event.keyCode == 13) {
            $("#change_pwd").click();
        }
});
});

function change_size(){
    var w = $(document).width();
    var h = $(window).height();
    w = w - 114;
    h = h - 60;
    $(".left .box").css('width',w);
    $(".left #left_menu").css('height',h);
}