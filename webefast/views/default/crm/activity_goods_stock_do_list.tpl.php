
<style type="text/css">
    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_panel1{
        width:100%;
        margin-bottom:5px;
    }
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 18px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 5px;
        text-align: left;
    }
    .table_panel_tt td{ padding:10px 25px;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    .btns{ text-align:right; margin-bottom:5px;}
    .panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
    .panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
    .panel > .panel-header h3{ font-size:14px;}
    input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}

    .bui-dialog .bui-stdmod-body {padding: 40px;}
    .show_scan_mode{ text-align:center;}
    .button-rule{ width:81px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
    .button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
    .button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
    .button-rule:active .icon{ display:block;}
    .button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
    .button-manz{ background-position:27px 26px;}
    .button-maiz{background-position:-208px 25px;}
    .button-manz:hover{background-position:41px -214px;}
    .button-maiz:hover{background-position:-208px -215px;}
    #child_barcode{display: none;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '参与活动商品', 'ref_table' => 'table'));
?>
<ul class="nav-tabs oms_tabs">
    <li ><a href="#"  onClick="do_page();">基本信息</a></li>
    <li class="active"  ><a href="#" >参与活动商品</a></li>
    <li><a href ="#" onClick="do_page_log();">操作日志</a></li>
</ul>
<table class='table_panel table_panel_tt' >
    <tr>
        <td>活动名称：<?php echo $response['activity']['activity_name']; ?></td>
        <td >活动店铺：<?php echo $response['activity']['shop_code_name']; ?></td>
    </tr>
    <tr>
        <td >活动开始时间：<?php echo $response['activity']['start_time']; ?></td>
        <td >活动结束时间：<?php echo $response['activity']['end_time']; ?></td>
    </tr>
</table>

<div class="btns">
    <div style ="float:left;">
        <b>请输入商品信息</b>
        <input type="text" placeholder="编码/条形码" class="input" value="" id="goods_code"/>
        <button type="button" class="button button-primary menu" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
    </div>
    <div id="rule1_btns">
        <button type="button" class="button button-primary menu" value="刷新库存" id="btnrefresh" >刷新库存</button>
        <button type="button" class="button button-primary menu" value="更新销售数量" id="btnrefreshsell" >更新销售数量</button>

        <button type="button" class="button button-primary menu" value="商品导入" <?php if ($response['activity']['status'] == 1) { ?>disabled <?php } ?> id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
        <button type="button" class="button button-primary is_view menu" value="添加" <?php if ($response['activity']['status'] == 1) { ?>disabled <?php } ?> id="btnSelectRule"  onClick="btn_add_goods();"><i class="icon-plus-sign icon-white"></i> 添加</button>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('crm/activity/do_delete')) { ?>
            <button class="button button-primary menu" id="btn_del_goods" <?php if ($response['activity']['status'] == 1) { ?>disabled <?php } ?> onclick="btn_del_goods();">一键清空</button>        
        <?php } ?>
    </div>
</div>
<ul class="nav-tabs oms_tabs secord">
    <li><a href="#" id="0" >普通商品</a></li>
    <li><a href="#" id="1" >套餐商品</a></li>
    <li><a href="#" id="child_barcode" onClick="do_page_one();">套装子商品</a></li>
</ul>
<div id='goods_common'>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var shop_code = "<?php echo $request['shop_code'] ?>";
    var activity_code = "<?php echo $response['activity']['activity_code']; ?>";
    var id = "<?php echo $response['_id']; ?>";
    var status = "<?php echo $response['activity']['status']?>";
    $(function () {
        _do_set_tab("0");
        $(".secord").find('li').eq(0).addClass("active");
    });
    $(document).ready(function () {
        //TAB选项卡

        $(".secord a:not(#child_barcode)").click(function () {
            $(".secord").find(".active").removeClass("active");
            $(this).parent("li").addClass("active");
            var tab = $(".secord").find(".active").find("a").attr("id");

            _do_set_tab(tab);
        })

    })
    $("#1").click(function () {
        $("#child_barcode").css("display", 'list-item');
    });
    $("#0").click(function () {
        $("#child_barcode").css("display", 'none');
    });
    function _do_set_tab(tab) {
        var code = "<?php echo $response['activity']['activity_code']; ?>";
        var shop_code = "<?php echo $response['activity']['shop_code'] ?>";
        var url = '<?php echo get_app_url('crm/activity/view_table'); ?>' + '&app_page=NULL&type=' + tab + '&code=' + code + '&shop=' + shop_code + '&status=' +<?php echo $response['activity']['status'] ?> + '&start_time=' + '<?php echo $response['activity']['start_time'] ?>' + '&end_time=' + '<?php echo $response['activity']['end_time']; ?>' + '&is_first=' + '<?php echo $response['activity']['is_first'] ?>';
        $.get(url, function (ret) {
            $("#goods_common").html(ret);
            $("#btn-search").click();
        if(status != 1){
            $('.menu').removeAttr("disabled");
            $('#goods_code').removeAttr("disabled");
        }
        });

    }
    function do_page() {
        location.href = "?app_act=crm/activity/view&app_scene=edit&_id=" + id + "&show=1&activity_code=" + activity_code;
    }
    function do_page_one() {
        $(".secord").find(".active").removeClass("active");
        $("#child_barcode").parent("li").addClass("active");
        var shop_code = "<?php echo $response['activity']['shop_code'] ?>";
        var url = "?app_act=crm/activity/goods_child_barcode&app_page=NULL&show=1&activity_code=" + activity_code + "&shop=" + shop_code;
        $.get(url, function (ret) {
            $("#goods_common").html(ret);
            $("#btn-search").click();
            $(".menu").attr("disabled", "disabled");                
            $("#goods_code").attr('disabled', true);
        });
    }
    function do_page_log() {
        location.href = "?app_act=crm/activity/goods_log&app_scene=edit&_id=" + id + "&show=1&activity_code=" + activity_code;
    }
    var select_url = '';
    //新增明细按钮
    function btn_add_goods() {
        if ($(".secord").find(".active").find("a").attr("id") == 1) {
            var param = {store_code: '', diy: 0, select_combo: 1, combo: 1};
        } else if ($(".secord").find(".active").find("a").attr("id") == 0) {
            var param = {store_code: '', diy: 0, select_combo: 1, combo: 0};
        }

        var url = '?app_act=prm/goods/goods_select_tpl&is_select=1';
        if (typeof (top.dialog) != 'undefined') {
            if (url != select_url) {
                top.dialog.remove(true);
            } else {
                top.dialog.show();
                return;
            }
        }
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];

        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择商品',
                width: '80%',
                height: 450,
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'],
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function () {
                tableStore.load();
            });

            top.dialog.show();

        });
    }


    function addgoods(obj, type) {
        select_data = top.SelectoGrid.getSelection();
        //console.log(select_data);  
        var _thisDialog = obj;
        var arr = Object.keys(select_data);
        if (arr.length == 0) {
            _thisDialog.close();
            return;
        }
        var start_time = "<?php echo $response['activity']['start_time']; ?>";
        var end_time = "<?php echo $response['activity']['end_time']; ?>";
        var shop_code = "<?php echo $response['activity']['shop_code'] ?>";
        $.post('?app_act=crm/activity/add_activity_goods&activity_code=' + activity_code + "&shop_code=" + shop_code + '&start_time=' + start_time + '&end_time=' + end_time, {data: select_data, shop_code: shop_code}, function (result) {
            if (result.status != 1) {
                    _thisDialog.close();
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //       _thisDialog.close();
                }, 'error');
            } else {
                if (type == 1) {
                    top.skuSelectorStore.load();
                } else {
                    _thisDialog.close();
                }
            }

        }, 'json');

    }
    function btn_del_goods() {
        var tab = $(".secord").find(".active").find("a").attr("id");
        if (confirm('确定清空商品信息吗？')) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('crm/activity/do_delete'); ?>',
                data: {activity_code: activity_code, tab: tab},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功：', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }
    }
    $(function () {
        $('#btnimport').click(function () {
            if ($(".secord").find(".active").find("a").attr("id") == 1) {
                var type = "combo";
            } else if ($(".secord").find(".active").find("a").attr("id") == 0) {
                var type = "common";
            }
            url = "?app_act=crm/activity/importGoods&activity_code=" + activity_code + "&type=" + type;
            new ESUI.PopWindow(url, {
                title: "导入商品",
                width: 500,
                height: 350,
                onBeforeClosed: function () {
                    location.reload();
                },
                onClosed: function () {
                }
            }).show();
        });
    });
    function delete_goods(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('crm/activity/delete'); ?>',
            data: {barcode: row.sku, activity_code: activity_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', 'success');
                    tableStore.load();
                } else {
                    BUI.Message.Alert('删除失败', 'error');
                }
            }
        });
    }
    $("#btnrefresh").click(function () {
        var activity_code = "<?php echo $response['activity']['activity_code']; ?>";
        var shop_code = "<?php echo $response['activity']['shop_code'] ?>";
        var start_time = "<?php echo $response['activity']['start_time']; ?>";
        var end_time = "<?php echo $response['activity']['end_time']; ?>";
        $.post('?app_act=crm/activity/inv_fresh&activity_code=' + activity_code + '&shop_code=' + shop_code + '&start_time=' + start_time + '&end_time=' + end_time, function (result) {
            BUI.Message.Alert('刷新成功');
            tableStore.load();
        }, 'json');
    });
    $("#btnrefreshsell").click(function () {
        $.post('?app_act=crm/activity/update_sell_data&app_fmt=json&activity_code=' + activity_code, function (result) {
            BUI.Message.Alert('更新成功');
            tableStore.load();
        }, 'json');
    });

    $("#btnSearchGoods").click(function () {
        tableStore.load({'code_name': $('#goods_code').val()});
    });
</script>
