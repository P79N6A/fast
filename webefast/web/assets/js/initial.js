$(function () {
    $("body").css({'overflow':'hidden','width':'100%','height':'100%'});
    change_size();

    $(window).resize(function(){
        change_size();
    });
		if($.cookie('left_sim')==1){
			setTimeout(function(){$(".updown").click();},10);

		}

	$(".server").click(function(){
                $(".widget").find("a").removeAttr("style");
                if($(".contact").css("display")!='none'){
                    $(this).removeAttr("style");
                }else{
                    $(this).css({"background-color":"#1695ca","background-position":"center -53px"});
                }
                $(".pwd_contact").stop().slideUp();
		$(".contact").stop().slideToggle();
		});

        //修改change_pwd
        $(".change_pwd").click(function(){
                $(".widget").find("a").removeAttr("style");
                if($(".pwd_contact").css("display")!='none'){
                    $(this).removeAttr("style");
                }else{
                    $(this).css({"background-color":"#1695ca","background-position":"center -53px"});
                }
                $(".contact").stop().slideUp();
                $(".pwd_contact").stop().slideToggle();
        });
		$("#cancel").click(function(){
			$(".pwd_contact table td input").val("");
			$(".pwd_contact").slideUp();
			})
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
    setTimeout(function () {
        $.get('?app_act=index/message', {page_type: page_type}, function (html) {
            $('#J_NavContent .dl-tab-item .dl-second-tree').eq(0).html(html);
        });
    }, 30);
    function get_pwd(pwd){
                    var password =  new Base64().encode(pwd);
              var  l = password.substr(3,3);

             return new Base64().encode(l+password);
    }

$("#change_pwd").click(function(){

    var data = {
       // current_pwd:$(".pwd_contact input[name=current_pwd]").val(),
        current_pwd:get_pwd($(".pwd_contact input[name=current_pwd]").val()),// 临时实现，后面改成rsa算法
        psw_strong:$(".pwd_contact input[name=psw_strong]").val(),
        //new_pwd:$(".pwd_contact input[name=new_pwd]").val(),
          new_pwd:get_pwd($(".pwd_contact input[name=new_pwd]").val()),// 临时实现，后面改成rsa算法
       // sure_pwd:$(".pwd_contact input[name=sure_pwd]").val()
         sure_pwd:get_pwd($(".pwd_contact input[name=sure_pwd]").val())// 临时实现，后面改成rsa算法

    };
    $.post("?app_act=index/change_pwd",data,function(ret){

        if(ret.status=='-1'){
            alert(ret.message);
        }else if(ret.status=='-10'){
            alert(ret.message);
             window.open(ret.data);
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

$(".updown").click(function(){

	//if($.cookie('left_sim')==1)
	$(".left").toggleClass("left_sim");

	if($(".left").hasClass('left_sim')){
	$.cookie('left_sim', "1",{expires:365});
	}else{
			$.cookie('left_sim',null);

	}
	change_size();
	})


});
function change_size(){
    var w = $(document).width();
    var h = $(window).height();
    w = w;


	if($('.left').hasClass('left_sim')){
		h=h-47;
                check =1;
		}else{
                    h=h-134;
            }
    $(".left .box").css('width',w);
    $(".left .box").css('height',h);

    setTimeout(function(){
       var h2= h-25;
   $("#J_NavContent .dl-second-nav").css('height',h);
	$("#J_NavContent .dl-second-tree>ul").css('height',h);
     $(".tab-content-container").css('height',h2);
    },50);

}
//771

/*头部消息小喇叭 */
  /*function u() {
    return newIndex = Math.floor(Math.random() * tips.length),
    top_new_con = tips[newIndex],
    O.data('id', newIndex),
    top_new_con
  }
  function f(e) {
    return handIndex = O.data('id'),
    '-' == e ? handIndex - 1 < 0 ? handIndex = M - 1 : handIndex-- : '+' == e && (handIndex + 1 >= M ? handIndex = 0 : handIndex++),
    O.data('id', handIndex),
    tips[handIndex]
  }

  tips = new Array(10),
  tips[0] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦111111</a>',
  tips[1] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦222222</a>',
  tips[2] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦333333</a>',
  tips[3] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦444444</a>',
  tips[4] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦555555</a>';
  tips[5] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦666666</a>',
  tips[6] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦777777</a>',
  tips[7] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦888888</a>',
  tips[8] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦999999</a>',
  tips[9] = '<a href="http://www.yishangwangluo.com/" target="_blank">翼商网络官方网站上线啦100000</a>';
  var O = $('.news_info'),
  M = ($('.news_prev'), $('.news_next'), tips.length),
  N = setInterval(function () {
    $('.news_info').html(u())
  }, 4000);
  $('#news').hover(function () {
    clearTimeout(N)
  }, function () {
    N = setInterval(function () {
      $('.news_info').html(u())
    }, 4000)
  })*/

  getnoticeinfo();
  //获取产品公告信息
  function getnoticeinfo(){
        $.ajax({type: 'POST', dataType: 'json',
            url: "?app_act=sys/notice/get_detail",
            data: {},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $("#news").show();
                   $(".news_info a").attr("href",ret.data.not_detail_url);
                   //$(".news_info a").text(ret.data.not_detail);
		   $(".news_info a").text("");
		   $(".news_info a").append(ret.data.not_detail);
                } else {

                }
            }
        });
  }


