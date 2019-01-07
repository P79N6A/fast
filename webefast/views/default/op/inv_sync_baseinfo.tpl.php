<style>
    .baseinfo input[type="checkbox"]{margin-top: 7px;}
    .baseinfo .control-label { width:120px;}
    .baseinfo .controls input[type="text"]{width: 200px;}
    .baseinfo .row{ margin-bottom:10px;}
    .baseinfo{ padding:20px;}
    #shop_name,#store_name{width: 350px;}
    .span11{width: 600px;}
</style>
<div class="baseinfo">
    <form class="form-horizontal" id="form1" action="?app_act=op/inv_sync/do_<?php echo $app['scene'] ?>&app_fmt=json" method="post">
        <div class="row">
            <div class="control-group span9">
                <label class="control-label">策略名称：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" name="sync_code" value="">
                    <input name="sync_name" type="text" data-rules="{required:true}" data-messages="{required:'策略名称不能为空'}" class="input-normal control-text" data-tip="{text:'请输入策略名称'}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span11">
                <label class="control-label">店铺名称：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" value="" id="shop_code" name="shop_code"/>
                    <input name="shop_name" type="text" data-rules="{required:true}" data-messages="{required:'店铺不能为空'}" readonly="true" id="shop_name" class="input-normal control-text" >
                    <a href="#" id = 'select_shop'><img src='assets/img/search.png'>选择</a>
                    <a id="clean_shop" class="button button-primary">清除</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span11">
                <label class="control-label">供货仓库：<b style="color:red"> *</b></label>
                <div class="controls">
                    <input type="hidden" value="" id="store_code" name="store_code"/>
                    <input name="store_name" type="text" data-rules="{required:true}" data-messages="{required:'仓库不能为空'}" readonly="true" id="store_name" class="input-normal control-text">
                    <a href="#" id = 'select_store'><img src='assets/img/search.png'>选择</a>
                    <a id="clean_store" class="button button-primary">清除</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group span19">
                <label class="control-label">策略模式：<b style="color:red"> *</b></label>
                <div class="controls">
                    <label class="radio"><input type="radio" name="sync_mode" value="1" checked="checked">全局模式（适用不同店铺不同同步比例设置，如淘宝店80%，京东20%）</label><br>
                    <label class="radio" style="line-height: 35px;"><input type="radio" name="sync_mode" value="2">仓库模式（适用不同店铺不同仓库同步比例设置，如淘宝店 仓库1同步100%，仓库2同步80%）</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">启用在途数：</label>
                <div class="controls">
                    <label class="checkbox"><input type="checkbox" name="is_road"><i class=""></i></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <label class="control-label">启用安全库存：</label>
                <div class="controls">
                    <label class="checkbox"><input type="checkbox" name="is_safe"><i class=""></i></label>
                </div>
            </div>
        </div>
        <div class="row form-actions actions-bar">
            <div class="span13 offset3 ">
                <button type="submit" class="button button-primary">保存</button>
                <button type="reset" class="button" onclick='clean()'>重置</button>
            </div>
        </div>
    </form>
    <p style="color:red">
        注意：若在途数和安全库存均启用，则同步库存数 =（实物库存 - 实物锁定 - 缺货数 + 在途数 - 安全库存）* 同步比例 - 平台未转单数 
        <br>
        <span style="margin-left: 38px;">若店铺和仓库设置了库存锁定单，<a href="#" onclick="lock_sync_mode_help();return false;">点击查看详细描述</a></span>
    </p>
    <script>
        $(function () {
            if (scene == 'edit' || scene == 'view') {
                var baseinfo = <?php echo $response['baseinfo']; ?>;
                $.each(baseinfo, function (key, val) {
                    var obj = $("#form1 input[name='" + key + "']");
                    if (key == 'sync_mode') {
                        obj.eq(val - 1).attr('checked', 'true');
                        return true;
                    }
                    if ((key == 'is_road' || key == 'is_safe') && val == '1') {
                        obj.attr('checked', 'true');
                        return true;
                    }
                    if (obj != 'undefined') {
                        obj.val(val);
                    }
                });
            }
            if (scene == 'add') {
                var obj = $("#form1 input[name='is_safe']");
                obj.attr('checked', 'true');
            }

            if (scene == 'view') {
                $('.baseinfo input').attr('disabled', 'disabled');
                $('#select_store').remove();
                $('#select_shop').remove();
                $('.baseinfo .actions-bar').remove();
                $('#clean_store').remove();
                $('#clean_shop').remove();
            }
        });

        $("#select_shop").click(function () {
            show_select('shop');
        });
        $("#select_store").click(function () {
            show_select('store');
        });
        $("#clean_shop").click(function () {
            $('#shop_code').attr("value", "");
            $('#shop_name').attr("value", "");
        });
        $("#clean_store").click(function () {
            $('#store_code').attr("value", "");
            $('#store_name').attr("value", "");
        });
        function show_select(_type) {
            var param = {};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=op/inv_sync/select_' + _type;
            var buttons = [
                {
                    text: '保存继续',
                    elCls: 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                            var _id = _type == 'shop' ? 'shop_name' : 'store_name';
                        }
                        auto_enter('#' + _id);
                    }
                },
                {
                    text: '保存退出',
                    elCls: 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                            var _id = _type == 'shop' ? 'shop_name' : 'store_name';
                        }
                        auto_enter('#' + _id);
                        this.close();
                    }
                },
//                  {
//                    text:'重置',
//                    elCls : 'button',
//                    handler: function () {
//                        this.close();
//                        reset(_type);
//                    }
//                  }
            ];
//            var buttons = [
//                {
//                    text: '确定',
//                    elCls: 'button button-primary',
//                    handler: function () {
//                        var data = top.SelectoGrid.getSelection();
//                        if (data.length > 0) {
//                            deal_data(data, _type);
//                            var _id = _type == 'shop' ? 'shop_name' : 'store_name';
//                        }
//                        auto_enter('#' + _id);
//                        this.close();
//                    }
//                }, {
//                    text: '取消',
//                    elCls: 'button',
//                    handler: function () {
//                        this.close();
//                    }
//                }
//            ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: _type == 'shop' ? '选择店铺' : '选择仓库',
                    width: '700',
                    height: '550',
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

                });
                top.dialog.show();
            });
        }

        function deal_data(obj, _type) {
            var shop_name = new Array();
            var shop_code = new Array();
            $.each(obj, function (i, val) {
                shop_name[i] = val[_type + '_name'];
                shop_code[i] = val[_type + '_code'];
            });
            shop_name = shop_name.join(',');
            shop_code = shop_code.join(',');
            $(".baseinfo input[name='" + _type + "_code']").val(shop_code);
            $(".baseinfo input[name='" + _type + "_name']").val(shop_name);
        }
        function deal_data_1(obj, _type) {
            var shop_name = new Array();
            var shop_code = new Array();
            var string_code = "";
            var string_name = "";
            string_code = $(".baseinfo input[name='" + _type + "_code']").val();
            var data_code = string_code.split(',');
            string_name = $(".baseinfo input[name='" + _type + "_name']").val();
            var data_name = string_name.split(',');
            $.each(obj, function (i, val) {
                shop_name[i] = val[_type + '_name'];
                shop_code[i] = val[_type + '_code'];
            });
            for (var j = 0; j < shop_code.length; j++)
                for (var i = 0; i < data_code.length; i++) {
                    if (data_code[i] == shop_code[j]) {
                        shop_code.splice(j, 1);
                    }
                }
            for (var j = 0; j < shop_name.length; j++)
                for (var i = 0; i < data_name.length; i++) {
                    if (data_name[i] == shop_name[j]) {
                        shop_name.splice(j, 1);
                    }
                }
            if (shop_name.length > 0) {
                shop_name = shop_name.join(',');
                if (string_code == "") {
                    string_code = shop_code;
                    $(".baseinfo input[name='" + _type + "_code']").val(string_code);
                } else {
                    string_code = string_code + ',' + shop_code;
                    $(".baseinfo input[name='" + _type + "_code']").val(string_code);
                }
            }
            if (shop_code.length > 0) {
                shop_code = shop_code.join(',');
                if (string_name == "") {
                    string_name = shop_name;
                    $(".baseinfo input[name='" + _type + "_name']").val(string_name);
                } else {
                    string_name = string_name + ',' + shop_name;
                    $(".baseinfo input[name='" + _type + "_name']").val(string_name);
                }
            }
        }

        BUI.use('bui/form', function (Form) {
            new Form.HForm({
                srcNode: '#form1',
                submitType: 'ajax',
                callback: function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'success');
                        window.location = '?app_act=op/inv_sync/detail&app_scene=edit&sync_code=' + data.data;
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }
            }).render();
        });

        //模拟回车事件，触发文本框规则检查
        function auto_enter(_id) {
            var e = jQuery.Event("keyup");//模拟一个键盘事件
            e.keyCode = 13;//keyCode=13是回车
            $(_id).trigger(e);
        }
        function clean() {
            $('#shop_code').attr("value", "");
            $('#store_code').attr("value", "");
        }

        //锁定同步两种模式区别提示
        function lock_sync_mode_help() {
            openPage('<?php echo base64_encode('?app_act=stm/stock_lock_record/lock_mode_help') ?>', '?app_act=stm/stock_lock_record/lock_mode_help', '锁定模式详解');
        }
    </script>
</div>