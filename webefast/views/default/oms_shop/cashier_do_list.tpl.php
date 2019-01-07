<style>
    .panel-body{padding:0px;}
    .panel{width:680px;}
    #panel_order .span11{
        width:400px;
        margin:0px;
    }
    #result_grid{
        overflow: auto;
        overflow-x: auto;
        max-height: 200px;
        _height : 300px;
    }
    .modify{
        cursor: pointer;
    }
</style>
<?php echo load_js('comm_util.js') ?>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title"><h2>前台收银</h2></span>
    <span class="page-link">
        <button class="button button-primary" onclick="javascript:location.reload(true);"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php echo load_css('cashier.css', true) ?>
<div id="content">
    <div id="pos">
        <div id="shop_info">
            <ul>
                <li class="shop_name"><span class="shop_name_icon">&nbsp;</span><?php echo $response['shop_name'] ?></li>
                <li class="cur_info">当前时间<b></b>收银员<b><?php echo $response['user_name'] ?></b>
                    <div id="pending">
                    </div>
                </li>
                <li class="up_box">
                    <a class="button button-primary" id="full_screen">全屏</a>
                    <!--<a class="button button-primary display" id="exit_full_screen">退出全屏</a>-->
                </li>
                <li></li>
            </ul>
        </div>

        <div class="clear"></div>
        <div id="main">
            <div id="goods" >
                <div id="goods_table"  >
                    <table id="table_goods">
                        <tr>
                            <th class="w1">NO.</th>
                            <th class="w2">名称</th>
                            <th class="w3">编码</th>
                            <th class="w4">颜色</th>
                            <th class="w4">尺码</th>
                            <th class="w4">数量</th>
                            <th class="w4">吊牌价</th>
                            <th class="w4">折扣</th>
                            <th class="w4">单价</th>
                            <th class="w4">金额</th>
                            <th class="display"></th>
                            <th class="display"></th>
                            <th class="display"></th>
                        </tr>
                    </table>
                </div>
                <div class="state_guides">
                    <span class="remarks">备注：<b id="remark"></b></span>
                </div>
                <!--                <div class="shortcut_keys clearfix" >
                <label>快捷键</label>
                <dl class=" clearfix">
                <dd>商品输入<span>[半角“,” 或 ENTER]</span></dd><dd>模糊输入<span>[,?]</span></dd><dd>改颜色<span>[C/]</span></dd><dd>改尺码<span>[S/]</span></dd><dd>改单价<span>[//]</span></dd><dd>改数量<span>[/]</span></dd><dd>改折扣<span>[./]</span></dd><dd>整单折扣<span>[Z/]</span></dd><dd>定位行号<span>[:/]</span></dd><dd>删行<span>[-]</span></dd><dd>现金收银<span>[F8]</span></dd><dd>信用卡收银<span>[F9]</span></dd><dd>锁定输入框<span>[F2]</span></dd><dd>取消交易<span>[ESC]</span></dd><dd>VIP输入<span>[V/]</span></dd>                </dl>
                </div>-->
            </div>
            <div id="record_info">
                <input type="text" id="barcode">
                <!--<p class="open_on_btn"><input type="button" id="open_quick"></p>-->
                <dl>
                    <dd><label>数量&nbsp;:&nbsp;</label><p id="num">0</p></dd>
                    <dd><label>金额&nbsp;:&nbsp;</label><p id="amount">0</p></dd>
                    <dd><label>优惠&nbsp;:&nbsp;</label><p id="discount">0</p></dd>
                    <dd class="display"><label>实收&nbsp;:&nbsp;</label><p id="receive_amount">0</p></dd>
                    <dd class="total"><label>小票&nbsp;:&nbsp;</label><p>0&nbsp;<span>张</span></p></dd>
                </dl>
            </div>
        </div>
        <div class="clear"></div>
        <div id="footer_info">
            <p class="display" id="tel"></p>
            <!--<div class="vip_pro">
            <table>
            <tr><td>卡号：</td><td>姓名：</td></tr>
            <tr><td>手机：</td><td>性别：</td></tr>
            <tr><td>卡种：</td><td>折扣：</td></tr>
            </table>
            </div>-->
            <div id="controls">
                <div class="detail-row">
                    <div class="detail-actions">
                        <a class="button button-primary" href="javascript:void(0)" id="cash">现金</a>
                        <a class="button button-primary" href="javascript:void(0)" id="alipay" disabled>支付宝</a>
                        <a class="button button-primary" href="javascript:void(0)" id="wechat" disabled>微信</a>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-actions">
                        <a class="button button-primary" href="javascript:void(0)" id="alter_amount">整单改价</a>
                        <a class="button button-primary" href="javascript:void(0)" id="alter_goods">修改商品</a>
                        <a class="button button-primary" href="javascript:void(0)" id="delete_goods">删除商品</a>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-actions">
                        <a class="button button-primary" href="javascript:void(0)" id="member">会员</a>
                        <a class="button button-primary" href="javascript:void(0)" id="cancel">作废订单</a>
                        <a class="button button-primary" href="javascript:void(0)" id="all_remark">整单备注</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="change_cash" class="display">
    <label class="control-label">应收：</label>
    <b class="payable"></b><br><br>
    <label class="control-label">实收：</label>
    <input type="text" class="input-normal control-text receive" value=""><br><br>
    <label class="control-label">找零：</label><b class="zero"></b>
</div>
<div id="change_price" class="display">
    <label class="control-label">修改价格：</label>
    <input type="text" class="input-normal control-text pic" value="">
</div>
<div id="change_remark" class="display">
    <label class="control-label">备注信息：</label>
    <textarea class="control-row4 input-large rmk" ></textarea>
</div>
<div id="relate_member" class="display">
    <label class="control-label">手机号：</label>
    <input type="text" class="input-normal control-text mem" value="">
</div>
<div id="change_goods" class="panel display">
    <form>
        <div class="panel-body" id="panel_order">
            <table cellspacing="0" class="table table-bordered" id="table1">
                <tbody>
                    <tr>
                        <td colspan="2">当前需修改的商品:<b><?php echo $response['cur_goods']['goods_name'] ?></b>[<?php echo $response['cur_goods']['goods_code'] ?>] &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  系统规格:规格1:<?php echo $response['cur_goods']['spec1_name']; ?>;规格2:<?php echo $response['cur_goods']['spec2_name'] ?>;</td>
                    </tr>
                    <tr>
                        <td>选择换货商品</td>
                        <td>
                            <input type='text' value=''  class="span11" name='select_goods' id='select_goods' placeholder="支持商品名称，商品编码，商品条形码查询">
                            <input type="button" class="button" id="btn-search" onclick="select_change_goods()" value="查询" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div  id="result_grid" class="panel-body">
        </div>
        <div class="clearfix" style="text-align:right;margin-top:50px;display:none;" id="save_change">
            <input class="button button-primary" onclick="save_change_goods()" value="保存并退出" />
        </div>
    </form>
</div>
<?php echo load_js('jquery.md5.js', TRUE); ?>
<script>
    var shop_code = "<?php echo $response['shop_code'] ?>";
    var ticket_print_power = <?php echo $response['ticket_print_power'] ?>;
    //是否开启clodop打印: 1开启
    var new_clodop_print = '<?php echo $response['new_clodop_print'];?>';
    //获取服务器时间
    $(function () {
        $.ajax({
            url: "?app_act=oms_shop/cashier/get_time",
            error: function () {
                alert('网络异常,请刷新重试！');
                return;
            },
            success: function (response) {
                response = $.parseJSON(response);
                var clock = response[0] + ' ' + response[1] + ' 星期' + '日一二三四五六'.charAt(response[2]);
                $(".cur_info b:first-child").html(clock);
            }
        });
        setInterval(function () {
            $.ajax({
                url: "?app_act=oms_shop/cashier/get_time",
                error: function () {
                    alert('网络异常,请刷新重试！');
                    return;
                },
                success: function (response) {
                    response = $.parseJSON(response);
                    var clock = response[0] + ' ' + response[1] + ' 星期' + '日一二三四五六'.charAt(response[2]);
                    $(".cur_info b:first-child").html(clock);
                }
            });

        }, 60000);

        $("#barcode").focus();
        //扫描条码
        $("#barcode").keyup(function (event) {
            if (event.keyCode == 13) {
                var params = {barcode: $(this).val().trim(), shop_code: shop_code};
                $.post("?app_act=oms_shop/cashier/scan_barcode", params, function (data) {
                    if (data.status == 1) {
                        insert_goods(data.data);
                        total();
                        tr_click();
                        $("#barcode").val('');
                        $("#barcode").focus();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            }
        });

        //整单改价
        var dialog_price;
        var price = 0;
        BUI.use('bui/overlay', function (Overlay) {
            dialog_price = new Overlay.Dialog({
                title: '整单改价',
                width: 300,
                height: 150,
                contentId: 'change_price',
                success: function () {
                    price = $('.pic').val();
                    var re = /^[0-9]+(.[0-9]{0,3})?$/;
                    if (!re.test(price)) {
                        BUI.Message.Alert('价格不合法', 'error');
                        return false;
                    } else {
                        var original_price = parseFloat($('#amount').text());
                        $('#amount').text(price);
                        $('#discount').text(original_price - parseFloat(price));
                        this.close();
                    }
                }
            });
        });
        $('#alter_amount').on('click', function () {
            if (exists_goods() == false) {
                return false;
            }
            dialog_price.show();
            $('.pic').focus();
            $('.pic').val('');
        });

        //关联会员
        var dialog_member;
        var member;
        BUI.use('bui/overlay', function (Overlay) {
            dialog_member = new Overlay.Dialog({
                title: '关联会员',
                width: 250,
                height: 150,
                contentId: 'relate_member',
                success: function () {
                    member = $('.mem').val();
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: '<?php echo get_app_url('oms_shop/cashier/check_member'); ?>',
                        data: {tel: member},
                        success: function (ret) {
                            if (ret.status != 1) {
                                create_member(member);
                            }
                            $('#tel').text(member);
                        }
                    });
                    this.close();
                }
            });
        });
        $('#member').on('click', function () {
            if (exists_goods() == false) {
                return false;
            }
            dialog_member.show();
            $('.mem').focus();
            $('.mem').val('');
        });

        //整单备注
        var dialog_remark;
        var remark = '';
        BUI.use('bui/overlay', function (Overlay) {
            dialog_remark = new Overlay.Dialog({
                title: '整单备注',
                width: 350,
                height: 200,
                contentId: 'change_remark',
                success: function () {
                    remark = $('.rmk').val();
                    $('#remark').text(remark);
                    this.close();
                }
            });
        });
        $('#all_remark').on('click', function () {
            if (exists_goods() == false) {
                return false;
            }
            dialog_remark.show();
            $('.rmk').focus();
            $('.rmk').val('');
        });

        //修改商品
        $('#alter_goods').on('click', function () {
            if (exists_select_goods() == false) {
                return false;
            }
            var tr = $('#goods_table tr.tr_click');
            var goods_code = tr.find('td:eq(2)').text();
            var goods_name = tr.find('td:eq(1)').text();
            var spec1_name = tr.find('td:eq(3)').text();
            var spec2_name = tr.find('td:eq(4)').text();
            new ESUI.PopWindow('?app_act=oms_shop/cashier/change_goods&goods_code=' + goods_code + '&goods_name=' + goods_name + '&spec1_name=' + spec1_name + '&spec2_name=' + spec2_name, {
                title: "修改商品",
                width: 820,
                height: 500,
                buttons: [],
                onBeforeClosed: function () {
                    if (parent.goods_info) {
                        tr.remove();
                        insert_goods(parent.goods_info[0]);
                        tr_click();
                        sort();
                        total();
                    }
                },
                onClosed: function () {
                }
            }).show();
        });

        //删除商品
        $('#delete_goods').on('click', function () {
            if (exists_select_goods() == false) {
                return false;
            }
            var tr = $('#goods_table tr.tr_click');
            var goods_code = tr.find('td:eq(2)').text();
            BUI.Message.Confirm('确认删除 ' + goods_code + ' 商品吗？', function () {
                setTimeout(function () {
                    tr.remove();
                    sort();
                    total();
                    this.close();
                });

            }, 'question');
        });

        //作废订单
        $('#cancel').on('click', function () {
            BUI.Message.Confirm('确认要作废该订单吗', function () {
                setTimeout(function () {
                    $('#goods_table tr:first').siblings().remove();
                    total();
                    this.close();
                });

            }, 'question');
        });

        //现金收银
        var dialog_cash;
        BUI.use('bui/overlay', function (Overlay) {
            dialog_cash = new Overlay.Dialog({
                title: '现金收银',
                width: 300,
                height: 200,
                contentId: 'change_cash',
                success: function () {
                    cash('cash');
                    this.close();
                }
            });
        });

        $('#cash').on('click', function () {
            if (exists_goods() == false) {
                return false;
            }
            $('.payable').text($("#amount").text());
            dialog_cash.show();
            $('.receive').focus();
            $('.receive').val('');
            $('.zero').val('');
        });

        $(".receive").change(function () {
            $(".zero").text($(this).val() - $("#amount").text());
        });

    });

    //打印小票
    function opt_print_ticket(record_code) {
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=cashier_ticket&record_ids="+record_code, {
                title: "小票打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        }else{
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=cashier_ticket&record_ids=' + record_code;
            $("#print_iframe").attr('src', u);
        }
    }

    //现金收银
    function cash(pay_code) {
        var cashier_code = '<?php echo $response['user_code'] ?>';
        var shop_code = '<?php echo $response['shop_code'] ?>';
        var goods = get_table_goods();
        var remark = $('#remark').text();
        var tel = $('#tel').text();
        var amount = $('#amount').text();
        var num = $('#num').text();
        var receive_amount = $('.receive').val();
        var discount = $("#discount").text();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('oms_shop/cashier/cash'); ?>',
            data: {cashier_code: cashier_code, shop_code: shop_code, remark: remark, tel: tel, amount: amount, num: num, real_amount: receive_amount, discount: discount, goods: goods, pay_code: pay_code},
            success: function (data) {
                if (data.status != 1) {
                    BUI.Message.Alert(data.message, 'error');
                    return false;
                } else {
                    if (ticket_print_power == 1) {
                        opt_print_ticket(data.data);
                    }
                    $('#goods_table tr:first').siblings().remove();
                    total();
                }
            }
        });
    }

    function get_table_goods() {
        var goods_arr = {};//新建对象，用来存储所有数据
        var sub_goods = {};//存储每一行数据
        var tableData = {};
        $("#table_goods tr:first").siblings().each(function (trindex, tritem) {
            tableData[trindex] = new Array();
            $(tritem).find("td:first").siblings().each(function (tdindex, tditem) {
                tableData[trindex][tdindex] = $(tditem).text();//遍历每一个数据，并存入
                sub_goods[trindex] = tableData[trindex];//将每一行的数据存入
            });
        });
        for (var key in sub_goods) {
            goods_arr[key] = sub_goods[key];//将每一行存入对象
        }
        return goods_arr;
    }

    function create_member(member) {
        new ESUI.PopWindow('?app_act=crm/client/detail&app_scene=add&tel=' + member, {
            title: "新增会员",
            width: '40%',
            height: 590,
            buttons: [],
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();

    }

    //检查列表中是否存在商品
    function exists_goods() {
        var tr = $('#goods_table tr:first').siblings();
        if (tr.length < 1) {
            BUI.Message.Alert('未扫描商品', 'error');
            return false;
        }
    }
    //检查是否选中商品
    function exists_select_goods() {
        var tr = $('#goods_table tr.tr_click');
        if (tr.length < 1) {
            BUI.Message.Alert('未选择商品', 'error');
            return false;
        }
    }

    //行选中效果
    function tr_click() {
        $('#goods_table tr').on('click', function () {
            $('#goods_table tr').removeClass();
            $(this).addClass('tr_click');
        });
    }

    //向表格中插入新商品
    function insert_goods(data) {
        //表格序号
        var no = $('#goods_table tr:last').find('td:eq(0)').text();
        no = no == '' ? 1 : no * 1 + 1;
        //商品数量
        var str_sku = $.md5(data.sku);
        var tr_key = $('#goods_table #' + str_sku);
        if (tr_key.length > 0) {
            var goods_num = parseInt(tr_key.find('td:eq(5)').text()) + 1;
            tr_key.find('td:eq(5)').text(goods_num);
            modify_row_money(tr_key);
        } else {
            var num = data.num != null ? data.num : 1;
            var tr_goods = '<tr id=' + str_sku + '>';
            tr_goods += '<td class = "td_goods">' + no + '</td>';
            tr_goods += '<td class = "td_goods">' + data.goods_name + '</td>';
            tr_goods += '<td class = "td_goods">' + data.goods_code + '</td>';
            tr_goods += '<td class = "td_goods">' + data.spec1_name + '</td>';
            tr_goods += '<td class = "td_goods">' + data.spec2_name + '</td>';
            tr_goods += '<td class = "td_goods">' + num + '</td>';
            tr_goods += '<td class = "td_goods">' + data.sell_price + '</td>';
            tr_goods += '<td class = "td_goods">' + data.rebate + '</td>';
            tr_goods += '<td class = "td_goods modify" onclick="modify_row_value(this)">' + data.price + '</td>';
            tr_goods += '<td class = "td_goods">' + data.price * num + '</td>';
            tr_goods += '<td class = "display">' + data.sku + '</td>';
            tr_goods += '<td class = "display">' + data.spec1_code + '</td>';
            tr_goods += '<td class = "display">' + data.spec2_code + '</td>';
            tr_goods += '</tr>';
            $('#goods_table table').append(tr_goods);
        }
    }

    //修改商品属性值
    function modify_row_value(_this) {
        var _value = $(_this).text();
        var _html = '<input type="text" style="width:90%;" id="modify_value" />';
        $(_this).html(_html);
        var obj = $(_this).find('#modify_value');
        obj.focus();
        obj.val(_value);

        $(_this).find('#modify_value').blur(function () {
            reload_row_value(_this);
        });

        $(_this).keydown(function (e) {
            if (e.keyCode == 13) {
                reload_row_value(_this);
            }
        });
    }

    function reload_row_value(_this) {
        $(_this).text($(_this).find('#modify_value').val());
        modify_row_money($(_this).parent('tr'));
        total();
    }

    //计算商品总价
    function modify_row_money(_this) {
        var num = _this.find('td:eq(5)').text();
        var price = _this.find('td:eq(8)').text();
        var money = price * num;
        _this.find('td:eq(9)').text(money.toFixed(2));
    }

    //统计
    function total() {
        var num = 0;
        var amount = 0;
        $('#goods_table tr').each(function (i, e) {
            if (i != 0) {
                num += parseInt($(this).children('td:eq(5)').text());
                amount += parseFloat($(this).children('td.td_goods:last').text());
            }
        });
        $('#num').text(num);
        $('#amount').text(amount);
    }

    //列表商品序号重新生成
    function sort() {
        $('#goods_table tr').each(function (i, e) {
            if (i != 0) {
                $(this).children("td:eq(0)").text(i);
            }
        });
    }

    //全屏
    var fullscreen = function () {
        elem = document.body;
        if (elem.webkitRequestFullScreen) {
            elem.webkitRequestFullScreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.requestFullScreen) {
            elem.requestFullscreen();
        } else {
            BUI.Message.Alert('浏览器不支持全屏或已被禁用', 'error');
        }

    };
    //退出全屏
    var exitFullscreen = function () {
        var elem = document;
        if (elem.webkitCancelFullScreen) {
            elem.webkitCancelFullScreen();
        } else if (elem.mozCancelFullScreen) {
            elem.mozCancelFullScreen();
        } else if (elem.cancelFullScreen) {
            elem.cancelFullScreen();
        } else if (elem.exitFullscreen) {
            elem.exitFullscreen();
        } else {
            BUI.Message.Alert('浏览器不支持全屏或已被禁用', 'error');
        }
    };
    $('#full_screen').click(function (e) {
        window.open('?app_act=oms_shop/cashier/do_list');
    });
    //获取本地时间并拼接时间串
    function showLocale(objD) {
        var yy = objD.getYear();
        if (yy < 1900)
            yy = yy + 1900;
        var MM = objD.getMonth() + 1;
        if (MM < 10)
            MM = '0' + MM;
        var dd = objD.getDate();
        if (dd < 10)
            dd = '0' + dd;
        var hh = objD.getHours();
        if (hh < 10)
            hh = '0' + hh;
        var mm = objD.getMinutes();
        if (mm < 10)
            mm = '0' + mm;
        var ss = objD.getSeconds();
        if (ss < 10)
            ss = '0' + ss;
        var ww = objD.getDay();
        if (ww == 0)
            ww = "星期日";
        if (ww == 1)
            ww = "星期一";
        if (ww == 2)
            ww = "星期二";
        if (ww == 3)
            ww = "星期三";
        if (ww == 4)
            ww = "星期四";
        if (ww == 5)
            ww = "星期五";
        if (ww == 6)
            ww = "星期六";
        str = yy + "-" + MM + "-" + dd + " " + hh + ":" + mm + ":" + ss + "  " + ww;
        return(str);
    }

    //定时器
    function tick() {
        var today;
        today = new Date();
        $('#current_time').text(showLocale(today));
        window.setTimeout("tick()", 1000);
    }

    $('#full_screen').click(function (e) {
        $("#shop_info").fullScreen();
        e.preventDefault();
    });
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px; visibility: hidden;" ></iframe>