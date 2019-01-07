/**
 * 角色业务权限相关
 * */
function set_pagebar() {
    $("#bar3").wrap("<div id='t_bar3'></div>");
    $("#bar6").wrap("<div id='t_bar6'></div>");

    $('#div_pgbar3').html($('#t_bar3').html());
    $('#div_pgbar6').html($('#t_bar6').html());

    $('#t_bar3').empty();
    $('#t_bar6').empty();
}
//$(document).ready(function () {
//    setTimeout("set_pagebar()", 1000);
//});

function do_set_active_shenhe(param_code, active) {
    $.ajax({type: 'POST', dataType: 'json',
        url: '?app_act=sys/params/update_active',
        data: {param_code: param_code, type: active},
        success: function (ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                window.location.reload();
                //tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

function role_remove() {
    var sel_role_code_arr = new Array();

    var selections = DataTable3Grid.getSelection();
    BUI.each(selections, function (item) {
        sel_role_code_arr.push(item.relate_code);
    });
    var sel_role_code = sel_role_code_arr.join(',');
    if (sel_role_code == '') {
        alert('请至少选择一条记录');
        return;
    }
    var params = {role_code: $("#role_code").val(),
        profession_type: $("#profession_type").val(),
        sel_shop_code: sel_role_code};
    $.get("?app_act=sys/role_profession/role_remove", params, function (data) {
        var obj = {'start': 1};
        DataTable2Store.load(obj);
        DataTable3Store.load(obj);
    });
}

function role_add() {
    var sel_shop_code_arr = new Array();
    var selections = DataTable2Grid.getSelection();
    BUI.each(selections, function (item) {
        sel_shop_code_arr.push(item.relate_code);
    });
    var sel_shop_code = sel_shop_code_arr.join(',');

    if (sel_shop_code == '') {
        alert('请至少选择一条记录');
        return;
    }
    var params = {role_code: $("#role_code").val(),
        profession_type: $("#profession_type").val(),
        sel_shop_code: sel_shop_code};
    $.get("?app_act=sys/role_profession/role_add", params, function (data) {
        var obj = {'start': 1};
        DataTable2Store.load(obj);
        DataTable3Store.load(obj);
    });
}

function do_page(param) {
    location.href = "?app_act=sys/role_profession/" + param + "&role_code=" + $("#role_code").val()+ "&role_id=" + $("#role_id").val() + "&keyword=" + $("#keyword").val();
}