function logout(){
    window.location.href = "?app_act=app/index/logout";
}
function building(){
    alert('正在建造中...');
}
function back(){
    var page = {};
    page[0] = '?app_act=app/index/do_index';
    page[1] = '?app_act=app/operate/do_index';
    page[2] =  '?app_act=app/monitor/do_index';
   window.location.href = page[app_main_index];
}
    $(function(){
        $('.bottom_nav a').eq(app_main_index).addClass('curr');
    });