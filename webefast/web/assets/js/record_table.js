var obj_data = {};
function record_table(arr) {
    _this = this;
    _this.data = new Array();
    _this.id;
    //控制表格列数
    _this.td_num = 3;
    _this.edit_url;
    _this.load_url = '';
    _this.load_callback = '';
    _this.check_url = '';
    _this.is_edit = true;
    this.init = function (obj) {
        obj_data[obj.id] = obj;
        _this.data = obj.data;
        _this.id = obj.id;
        if (obj.title) {
            _this.title = obj.title;
        } else {
            _this.title = '基本信息';
        }
        _this.edit_url = obj.edit_url;
        if (typeof obj.is_edit != "undefined") {
            _this.is_edit = obj.is_edit;
        }
        if (typeof obj.load_url != "undefined") {
            _this.load_url = obj.load_url;
        }
        if (typeof obj.load_callback != "undefined") {
            _this.load_callback = obj.load_callback;
        }
        if (typeof obj.td_num != "undefined") {
            _this.td_num = obj.td_num;
        }
        if (typeof obj.check_url != "undefined") {
            _this.check_url = obj.check_url;
        }
        if (typeof obj.check_params != "undefined") {
            _this.check_params = obj.check_params;
        }
        _this.create_html();
        _this.bind();
    };
    this.init_obj = function (obj) {
        _this.data = obj.data;
        _this.id = obj.id;
        _this.edit_url = obj.edit_url;
        if (typeof obj.is_edit != "undefined") {
            _this.is_edit = obj.is_edit;
        }
        if (typeof obj.load_url != "undefined") {
            _this.load_url = obj.load_url;
        }
        if (typeof obj.load_callback != "undefined") {
            _this.load_callback = obj.load_callback;
        }
        if (typeof obj.check_url != "undefined") {
            _this.check_url = obj.check_url;
        }
        if (typeof obj.check_params != "undefined") {
            _this.check_params = obj.check_params;
        }
    };
    this.bind = function () {
        jQuery("#" + _this.id).find(".btnFormEdit").bind("click", _this.edit);
        jQuery("#" + _this.id).find(".btnFormCancel").bind("click", _this.cancel);
        jQuery("#" + _this.id).find(".btnFormSave").bind("click", _this.check_or_save);
    };

    this.edit = function () {
        var id = $(this).attr('name');
        _this.init_obj(obj_data[id]);

        for (var i = 0; i < _this.data.length; i++) {
            if (typeof _this.data[i].edit != "undefined" && _this.data[i].edit) {
                var name = _this.data[i].name;
                var value = _this.data[i].value;
                var type = _this.data[i].type;
                switch (type) {
                    case "input":
                        var value = jQuery("#" + name).text();
                        var html = "<input type='text' name='" + name + "' value='" + value + "'>";
                        jQuery("#" + name).html(html);
                        break;
                    case "select":
                        jQuery("#" + name).find("p").html("");

                        BUI.use('bui/select', function (Select) {
                            var select = new Select.Select({
                                render: '#' + name,
                                valueField: '#' + name + "_hide",
                                items: _this.data[i].data
                            });
                            select.render();
                        });
                        break;
                    case "time":
                        var value = jQuery("#" + name).text();
                        var html = "<input type='text' id='" + name + "_time' name='" + name + "' value='" + value + "' class='calendar'>";
                        jQuery("#" + name).html(html);
                        BUI.use('bui/calendar', function (Calendar) {
                            var datepicker = new Calendar.DatePicker({
                                trigger: '#' + name + "_time",
                                autoRender: true
                            });
                        });
                        break;
                    case "datetime":
                        var value = jQuery("#" + name).text();
                        var html = "<input type='text' id='" + name + "_time' name='" + name + "' value='" + value + "' class='calendar calendar-time'>";
                        jQuery("#" + name).html(html);
                        BUI.use('bui/calendar', function (Calendar) {
                            var datepicker = new Calendar.DatePicker({
                                trigger: '#' + name + "_time",
                                showTime: true,
                                autoRender: true
                            });
                        });
                        break;
                }
            }
        }
        jQuery("#" + _this.id).find(".btnFormEdit").hide();
        jQuery("#" + _this.id).find(".btnFormSave").show();
        jQuery("#" + _this.id).find(".btnFormCancel").show();
    }
    this.check_or_save = function () {
        if (_this.check_url != '') {
            var id = $(this).attr('name');
            _this.init_obj(obj_data[id]);
            var parameter = getParameter("record_table");
            //alert(_this.check_params+parameter['parameter'].return_express_no);
            if (_this.check_params !== parameter['parameter'].return_express_no) {
                var check_data = parameter['parameter'].return_express_no;
            } else {
                var check_data = '';
            }
            ajax_post({
                url: _this.check_url,
                data: {"return_express_no": check_data},
                async: false,
                alert: false,
                callback: function (ret) {
                    if (ret.status == 'exist') {
                        BUI.Message.Show({
                            msg: '快递单号' + ret.data + '已经存在，确认继续吗！',
                            icon: 'question',
                            buttons: [
                                {
                                    text: '确认',
                                    elCls: 'button button-primary',
                                    handler: function () {
                                        ajax_post({
                                            url: _this.edit_url,
                                            data: parameter,
                                            async: false,
                                            alert: false,
                                            callback: function (data) {
                                                var type = data.status == "1" ? 'success' : 'error';
                                                BUI.Message.Tip(data.message, type);
                                                if (data.status == "1") {
                                                    _this.load_data();
                                                }
                                            }
                                        });
                                        this.close();
                                    }
                                },
                                {
                                    text: '取消',
                                    elCls: 'button',
                                    handler: function () {
                                        this.close();
                                    }
                                }

                            ]
                        });
                    } else {
                        ajax_post({
                            url: _this.edit_url,
                            data: parameter,
                            async: false,
                            alert: false,
                            callback: function (data) {
                                var type = data.status == "1" ? 'success' : 'error';
                                BUI.Message.Tip(data.message, type);
                                if (data.status == "1") {
                                    _this.load_data();
                                }
                            }
                        });
                    }
                }
            });



        } else {
            var id = $(this).attr('name');
            _this.init_obj(obj_data[id]);
            var parameter = getParameter("record_table");
            ajax_post({
                url: _this.edit_url,
                data: parameter,
                async: false,
                alert: false,
                callback: function (data) {
                    var type = data.status == "1" ? 'success' : 'error';
                    BUI.Message.Tip(data.message, type);
                    if (data.status == "1") {
                        _this.load_data();
                    }
                }
            });
        }

    }

    this.cancel = function () {
        var id = $(this).attr('name');
        _this.init_obj(obj_data[id]);

        for (var i = 0; i < _this.data.length; i++) {
            if (typeof _this.data[i].edit != "undefined" && _this.data[i].edit) {
                var name = _this.data[i].name;
                var value = _this.data[i].value;
                var type = _this.data[i].type;
                switch (type) {
                    case "input":
                        var value = jQuery("input[name='" + name + "']").val();
                        var ret = check_value(value);
                        jQuery("#" + name).html(ret);
                    case "time":
                        var value = jQuery("input[name='" + name + "']").val();
                        jQuery("#" + name).html(value);
                        break;
                    case "datetime":
//                    	var value = jQuery("input[name='"+name+"']").val();
                        jQuery("#" + name).html(value);
                        break;
                    case "select":
                        var text = "";
                        var value = jQuery("input[name='" + name + "']").val();
                        var data = _this.data[i].data;
                        for (var j = 0; j < data.length; j++) {
                            if (data[j].value == value) {
                                text = data[j].text;
                                break;
                            }
                        }
                        jQuery("#" + name).find("p").html(text);
                        break;
                }
            }
        }
        jQuery("#" + _this.id).find(".btnFormEdit").show();
        jQuery("#" + _this.id).find(".btnFormSave").hide();
        jQuery("#" + _this.id).find(".btnFormCancel").hide();
        jQuery("#" + _this.id).find(".bui-select").remove();
    }

    function check_value(val) {
        var dd = val.replace(/<\/?.+?>/g, "");
        var dds = dd.replace(/ /g, "");
        return dds;

    }
    this.save = function () {
        var id = $(this).attr('name');
        _this.init_obj(obj_data[id]);
        var parameter = getParameter("record_table");
        ajax_post({
            url: _this.edit_url,
            data: parameter,
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Tip(data.message, type);
                if (data.status == "1") {
                    _this.load_data();
                }
            }
        });
    }
    this.load_data = function (param) {
        if (typeof (param) == 'undefined') {
            param = {};
        }
        if (_this.load_url == '') {
            location.reload();
            return;
        }
        $.post(_this.load_url, param, function (result) {
            for (var i = 0; i < _this.data.length; i++) {
                var key = _this.data[i].name;
                if (typeof (result.data[key]) != 'undefined') {
                    _this.data[i].value = result.data[key];
                }
            }
            _this.create_html();
            _this.bind();
            if (_this.load_callback != '') {
                _this.load_callback();
            }

        }, 'json');

    }
    this.create_html = function () {
        var html = "";
        html += '<div class="panel-header clearfix">';
        html += '<h3 class="pull-left">' + _this.title + '<i class="icon-folder-open toggle"></i></h3>';
        html += '<div class="pull-right">';
        if (_this.is_edit) {
            html += '<button type="button" class="button button-small btnFormEdit" name="' + _this.id + '" ><i class="icon-edit"></i> 编辑</button>';
        }
        html += '<button type="button" class="button button-small btnFormSave"  style="display: none;" name="' + _this.id + '"><i class="icon-ok"></i> 保存</button>';
        html += '<button type="button" class="button button-small btnFormCancel" style="display: none;" name="' + _this.id + '"><i class="icon-remove"></i> 取消</button>';
        // html += '<button type="button" class="button button-small" onclick="javascript:location.reload();"><i class="icon-refresh"></i> 刷新</button>';
        html += '</div>';
        html += '</div>';

        html += '<div class="panel-body">';
        html += '<table id="base_table" class="table panel-head-borded">';
        html += '<tr>';
        var k = 0;
        for (var i = 0; i < _this.data.length; i++) {
            var value = _this.data[i].value;
            var name = _this.data[i].name;

            if (i > 1) {
                var old_name = _this.data[i - 1].name;
            }

            //if(old_name == "remark"){
            //html += '</tr>';
            //html += '<tr>';
            //k = k+2;
            //}else{
            if (k != 0 && k % _this.td_num == 0) {
                html += '</tr>';
                html += '<tr>';
            }
            k = k + 1;
            //}
            html += '<th>' + _this.data[i].title + '</th>';
            if (_this.data[i].type == "select") {
                var data = _this.data[i].data;
                var text = "";
                for (var j = 0; j < data.length; j++) {
                    if (data[j].value == value) {
                        text = data[j].text;
                        break;
                    }
                }


                html += '<td id="' + name + '"><p>' + text + '</p>';

                html += '<input type="hidden" id="' + name + '_hide" name="' + name + '" value="' + value + '">';
                html += '</td>';
            } else {
                // value = $('<div>').text(value).html();
                html += '<td id=' + name + '>' + value;
                if (typeof _this.data[i].act_html != "undefined") {
                    html += "&nbsp;&nbsp;" + _this.data[i].act_html;
                }
                html += '</td>';
            }

        }
        html += '</tr>';
        html += '</table>';
        html += '</div>';
        jQuery("#" + _this.id).html(html);
    }
}
function set_obj_arr() {
    if (typeof (top.goods_dialog_index) == 'undefined') {
        top.goods_dialog_index = 0;
        top.goods_dialog = {};
    } else {
        top.goods_dialog_index++;
    }
}
var goods_goods_panel = {};
function get_goods_panel(obj) {
    var param = new Object();


    if (typeof obj.param != "undefined") {
        param = obj.param;
    }
    if (typeof (top.dialog) != 'undefined') {
        top.dialog.remove(true);
    }
    var url = '?app_act=prm/goods/goods_select_tpl';
    var buttons = [
        {
            text: '保存继续',
            elCls: 'button button-primary',
            handler: function () {
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                // top.form.fire('beforesubmit');
                top.save_up();
            }
        },
        {
            text: '保存退出',
            elCls: 'button button-primary',
            handler: function () {
                this.callback = function () {
                    location.reload();
                }
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
            }
        }, {
            text: '取消',
            elCls: 'button',
            handler: function () {
                location.reload();
            }
        }
    ];
    top.BUI.use('bui/overlay', function (Overlay) {
        top.dialog = new Overlay.Dialog({
            title: '选择商品',
            width: '80%',
            //height: 400,
            loader: {
                url: url,
                autoLoad: true, //不自动加载
                params: param, //附加的参数
                lazyLoad: false, //不延迟加载
                dataType: 'text'   //加载的数据类型
            },
            align: {
                //node : '#t1',//对齐的节点
                points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                offset: [0, 20] //偏移
            },

            mask: true,
            buttons: buttons

        });

        top.dialog.on('closed', function (ev) {
            location.reload();
        });
        var d_id = top.dialog.get('id');
        $("#" + obj.id).click(function (event) {
            if (top.dialog.get('id') != d_id) {
                top.dialog.set('id', d_id);
                top.dialog.get('loader').set('url', url);
                top.dialog.get('loader').set('params', param);
                top.dialog.get('loader').load(param);
                top.dialog.set('buttons', buttons);
            }
            top.dialog.show();
        });
    });
}
function get_goods_inv_panel(obj) {
    var param = new Object();

    if (typeof obj.param != "undefined") {
        param = obj.param;
    }
    if (typeof (top.dialog) != 'undefined') {
        top.dialog.remove(true);
    }
    var url = '?app_act=prm/goods/goods_select_tpl_inv';
    var buttons = [
        {
            text: '保存继续',
            elCls: 'button button-primary',
            handler: function () {
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                //  top.form.fire('beforesubmit');
                top.save_up();
            }
        },
        {
            text: '保存退出',
            elCls: 'button button-primary',
            handler: function () {
                this.callback = function () {
                    location.reload();
                }
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
            }
        }, {
            text: '取消',
            elCls: 'button',
            handler: function () {
                location.reload();
            }
        }
    ];
    top.BUI.use('bui/overlay', function (Overlay) {
        top.dialog = new Overlay.Dialog({
            title: '选择商品',
            width: '80%',
            //height: 400,
            loader: {
                url: url,
                autoLoad: true, //不自动加载
                params: param, //附加的参数
                lazyLoad: false, //不延迟加载
                dataType: 'text'   //加载的数据类型
            },
            align: {
                //node : '#t1',//对齐的节点
                points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                offset: [0, 20] //偏移
            },
            mask: true,
            buttons: buttons
        });

        top.dialog.on('closed', function (ev) {
            location.reload();
        });

        var d_id = top.dialog.get('id');
        $("#" + obj.id).click(function (event) {
            if (top.dialog.get('id') != d_id) {
                top.dialog.set('id', d_id);
                top.dialog.get('loader').set('url', url);
                top.dialog.get('loader').set('params', param);
                top.dialog.get('loader').load(param);
                top.dialog.set('buttons', buttons);
            }
            top.dialog.show();
        });
    });
}
function get_goods_sku_panel(obj) {
    var param = new Object();

    if (typeof obj.param != "undefined") {
        param = obj.param;
    }
    if (typeof (top.dialog) != 'undefined') {
        top.dialog.remove(true);
    }
    var url = '?app_act=prm/goods/goods_select_tpl_sku';
    var buttons = [
        {
            text: '保存继续',
            elCls: 'button button-primary',
            handler: function () {
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                // top.form.fire('beforesubmit');

                top.save_up();
            }
        },
        {
            text: '保存退出',
            elCls: 'button button-primary',
            handler: function () {
//                            this.callback = function(){
//                               location.reload();
//                            }
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                top.form.fire('beforesubmit');
                this.close();
            }
        }, {
            text: '取消',
            elCls: 'button',
            handler: function () {
                // location.reload();
                this.close();
            }
        }
    ];

    top.BUI.use('bui/overlay', function (Overlay) {
        top.dialog = new Overlay.Dialog({
            title: '选择商品',
            width: '80%',
            //height: 400,
            loader: {
                url: url,
                autoLoad: true, //不自动加载
                params: param, //附加的参数
                lazyLoad: false, //不延迟加载
                dataType: 'text'   //加载的数据类型
            },
            align: {
                //node : '#t1',//对齐的节点
                points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                offset: [0, 20] //偏移
            },
            mask: true,
            buttons: buttons
        });
//                      top.dialog.on('closed',function(ev){
//                      location.reload();
//                  });
        var d_id = top.dialog.get('id');
        $("#" + obj.id).click(function (event) {
            if (top.dialog.get('id') != d_id) {
                top.dialog.set('id', d_id);
                top.dialog.get('loader').set('url', url);
                top.dialog.get('loader').set('params', param);
                top.dialog.get('loader').load(param);
                top.dialog.set('buttons', buttons);
            }
            top.dialog.show();
        });
    });
}

/**
 * 调整单新增商品
 *
 */
function get_adjust_goods_sku_panel(obj) {
    var param = new Object();

    if (typeof obj.param != "undefined") {
        param = obj.param;
    }
    if (typeof (top.dialog) != 'undefined') {
        top.dialog.remove(true);
    }
    var url = '?app_act=prm/goods/goods_select_tpl_inv_adjust';
    var buttons = [
        {
            text: '保存继续',
            elCls: 'button button-primary',
            handler: function () {
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                // top.form.fire('beforesubmit');

                top.save_up();
            }
        },
        {
            text: '保存退出',
            elCls: 'button button-primary',
            handler: function () {
//                            this.callback = function(){
//                               location.reload();
//                            }
                if (typeof obj.callback == "function") {
                    obj.callback(this);
                }
                top.form.fire('beforesubmit');
                this.close();
            }
        }, {
            text: '取消',
            elCls: 'button',
            handler: function () {
                // location.reload();
                this.close();
            }
        }
    ];

    top.BUI.use('bui/overlay', function (Overlay) {
        top.dialog = new Overlay.Dialog({
            title: '选择商品',
            width: '80%',
            //height: 400,
            loader: {
                url: url,
                autoLoad: true, //不自动加载
                params: param, //附加的参数
                lazyLoad: false, //不延迟加载
                dataType: 'text'   //加载的数据类型
            },
            align: {
                //node : '#t1',//对齐的节点
                points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                offset: [0, 20] //偏移
            },
            mask: true,
            buttons: buttons
        });
        var d_id = top.dialog.get('id');
        $("#" + obj.id).click(function (event) {
            if (top.dialog.get('id') != d_id) {
                top.dialog.set('id', d_id);
                top.dialog.get('loader').set('url', url);
                top.dialog.get('loader').set('params', param);
                top.dialog.get('loader').load(param);
                top.dialog.set('buttons', buttons);
            }
            top.dialog.show();
        });
    });
}

function update_panel_params(params) {
    //if(params.store_code!=top.store_code){
    top.dialog.get('loader').set('params', params);
    top.dialog.get('loader').load(params);
    //} 
}
function import_goods_recode(btid, id, type) {
    var param = {};
    var url = '?app_act=prm/goods/record_import&id=' + id + "&type=" + type;
    $('#' + btid).click(function () {
        new ESUI.PopWindow(url, {
            title: '商品导入',
            width: 500,
            height: 380,
            onBeforeClosed: function () {
                location.reload();
            }
        }).show();
    });
}

function select_goods_panel(obj) {
    var param = new Object();

    if (typeof obj.param != "undefined") {
        param = obj.param;
    }
    if (typeof (top.dialog) != 'undefined') {
        top.dialog.remove(true);
    }
    var buttons = [
        {
            text: '关闭',
            elCls: 'button button-primary',
            handler: function () {
                // location.reload();
                this.close();
            }
        }
    ];

    var url = '?app_act=common/select_comm/goods_layer';

    top.BUI.use('bui/overlay', function (Overlay) {
        top.dialog = new Overlay.Dialog({
            title: '商品选择',
            width: '750',
            height: '560',
            loader: {
                url: url,
                autoLoad: true, //不自动加载
                params: param, //附加的参数
                lazyLoad: false, //不延迟加载
                dataType: 'text'   //加载的数据类型
            },
            align: {
                //node : '#t1',//对齐的节点
                points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                offset: [0, 20] //偏移
            },
            mask: true,
            buttons: buttons,
            elCls: 'custom-dialog'
        });

        top.dialog.on('closed', function (ev) {
            location.reload();
        });

        var d_id = top.dialog.get('id');
        $("#" + obj.id).click(function (event) {
            if (top.dialog.get('id') != d_id) {
                top.dialog.set('id', d_id);
                top.dialog.get('loader').set('url', url);
                top.dialog.get('loader').set('params', param);
                top.dialog.get('loader').load(param);
                top.dialog.set('buttons', buttons);
            }
            top.dialog.show();
        });
    });
}