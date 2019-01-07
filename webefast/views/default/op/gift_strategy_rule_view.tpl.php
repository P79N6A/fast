
<style type="text/css">
    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_pane2{width: 100%;margin-bottom: 5px;}
    .table_pane3{width: 30%;border: solid 1px #ded6d9;}
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 15px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 15px;
        padding: 5px;
        text-align: left;
    }
    .table_pane3 td {
        border:1px solid #dddddd;
        line-height: 15px;
        padding: 5px;
        text-align: left;

    }
    .table_panel_tt td{ padding:10px 5px;}
    .table_panel_tt2 td{ padding:10px 5px;}

    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    .btns{ text-align:right; margin-bottom:5px;}
    .panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
    .panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
    .panel > .panel-header h3{ font-size:14px;}
    input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}
    .add_range input {width:105px;}
    .bui-dialog .bui-stdmod-body {padding: 40px;}
    .show_scan_mode{ text-align:center;}
    .button-rule{ width:108px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
    .button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
    .button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
    .button-rule:active .icon{ display:block;}
    .button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
    .button-manz{ background-position:41px 26px;}
    .button-maiz{background-position:-208px 25px;}
    .button-manz:hover{background-position:41px -214px;}
    .button-maiz:hover{background-position:-208px -215px;}
    .symbol{
        font-weight:bold;
        font-size:large;
    }
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<ul class="nav-tabs oms_tabs">
    <li class="active" ><a href="#"  >规则设置</a></li>
    <li onClick="do_page('gift');"><a href="#" >赠品商品</a></li>
    <li onClick="do_page('goods');"><a href="#" >活动商品</a></li>
    <li onClick="do_page('customer');"><a href="#" >定向会员</a></li>

</ul>

<table class='table_panel table_panel_tt' >
    <tr>
        <td>策略名称：<?php echo $response['strategy']['strategy_name']; ?></td>
        <td >活动店铺：<?php echo $response['strategy']['shop_code_name']; ?></td>
    </tr>
    <tr>
        <td >活动开始时间：<?php echo date('Y-m-d H:i:s', $response['strategy']['start_time']); ?></td>
        <td >活动结束时间：<?php echo date('Y-m-d H:i:s', $response['strategy']['end_time']); ?></td>
    </tr>
</table>

<form id="form1" action="?app_act=op/gift_strategy/do_edit_detail&app_fmt=json" method="post">

    <table class='table_pane2 table_panel_tt2' >
        <tr>
            <td >分组：<input type="text" name="sort" value="<?php echo $response['data']['sort']; ?>"/></td>
            <td >规则名称：<input type="text" name="name" value="<?php echo $response['data']['name']; ?>"/></td>
            <td >优先级：<input type="text" name="level" placeholder="请输入大于0整数" value="<?php echo $response['data']['level']; ?>"/>
                <img width="25" height="25" src="assets/images/tip.png" alt="" title="数字越大优先级越高">
            </td>
            <td>互溶/互斥：<input type="radio" name="is_mutex" value="1" <?php if ($response['data']['is_mutex'] == 1) { ?> checked <?php } ?> />互溶 <input type="radio" name="is_mutex" value="0" <?php if ($response['data']['is_mutex'] == 0) { ?> checked <?php } ?>/>互斥</td>
            <!-- <td>时间维度：<input type="radio" name="time_type" value="1" <?php if ($response['data']['time_type'] == 1) { ?> checked <?php } ?> />下单时间<input type="radio" name="time_type" value="0" <?php if ($response['data']['time_type'] == 0) { ?> checked <?php } ?> />付款时间</td> -->
        </tr>
    </table> 
    <!-- 满送 -->
    <?php if ($response['data']['type'] == 0) { ?>
        <div>
            金额范围设置：<input type="radio" <?php if ($response['data']['range_type'] == 0) { ?> checked <?php } ?> name="range_type" value="0"/>手工<input type="radio" name="range_type" value="1" <?php if ($response['data']['range_type'] == 1) { ?> checked <?php } ?> />倍增
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox"  <?php if ($response['data']['is_contain_delivery_money'] == 1) { ?> checked <?php } ?> name="is_contain_delivery_money" value="1"/>包含运费
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox"  <?php if ($response['data']['is_goods_money'] == 1) { ?> checked <?php } ?> name="is_goods_money" value="1"/>仅限活动商品满额
        </div>
        <!-- 手工 -->
        <div class="custom_set">
            <table class='table_pane3'>

                <?php foreach ($response['range'] as $key => $range_row) { ?>
                    <tr>
                        <?php if ($response['strategy']['is_check'] == 0) { ?>
                            <td  width="10%"><span class="x-icon  x-icon-mini x-icon-error" onclick="delete_range(<?php echo $range_row['id']; ?>)">×</span></td>
                        <?php } ?>
                        <td width="27.5%" ><?php echo $range_row['range_start']; ?></td>
                        <td width="35%" style="color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;"><span class="symbol"><?php if($response['strategy']['strategy_new_type']==1){echo '≤';}else{echo '<';}?></span> 金额范围 <span class="symbol">≤</span></td>
                        <td width="27.5%" ><?php echo $range_row['range_end']; ?></td>
                    </tr>
                <?php } ?> 
            </table>
            <div class="add_range" style="margin-top:20px;display:none;"> 
                <input type="text" name="range_start" id="range_start" /><span style="color:red;"><span class="symbol"><?php if($response['strategy']['strategy_new_type']==1){echo '≤';}else{echo '<';}?></span> 金额范围 <span class="symbol">≤</span></span><input type="text" name="range_end" id="range_end" />
                <button type="button" class="button"  onclick="save_range();">保存</button>
                <button type="button" class="button" onclick="cancel_range();">取消</button> 
            </div>
            <div style="margin-top:20px;">
                <button type="button" class="button" name="add_je" onclick="add_je_range()">添加</button>
            </div>
            <div style="color:red;">
                说明：可通过‘添加’实现按照金额范围设置送不同的赠品。
            </div>
        </div>
        <!-- 倍增 -->
        <div class="doubled_set">
            <input type="text" name="doubled" id="doubled" value="<?php echo $response['data']['doubled']; ?>"/>
            <div style="color:red;">
                说明：订单金额满足设置金额范围倍数，赠品翻倍赠送。假如设置100.00送商品A1个，买200即送商品A2个，以此类推。
            </div>
        </div>
    <?php } else { ?>
        <!-- 买送 -->
        <div>
        </div>
        买赠类型：<input type="radio" <?php if ($response['data']['goods_condition'] == 2) { ?> checked <?php } ?> name="goods_condition" value="2"/>全场买送<input type="radio" name="goods_condition" value="0" <?php if ($response['data']['goods_condition'] == 0) { ?> checked <?php } ?> />指定商品与数量<input type="radio" name="goods_condition" value="1" <?php if ($response['data']['goods_condition'] == 1) { ?> checked <?php } ?> />随机商品与数量
        <input <?php if ($response['data']['goods_condition'] != 1) { ?> style="display:none;" <?php } ?> type="text" class="buy_random" name="buy_num" placeholder="输入随机购买商品数量" value="<?php echo $response['data']['buy_num']; ?>"/>
        <div style="color:red;<?php if ($response['data']['goods_condition'] != 2) { ?> display:none; <?php } ?>" class="buy_all"  >
            说明：全场买送：购买活动店铺商品即送赠品。无需设置活动商品。
        </div>

        <div class="buy_set">
            <div class="range_set">
                数量范围设置：<input type="radio" <?php if ($response['data']['range_type'] == 0) { ?> checked <?php } ?> name="range_type" id ="handwork" value="0"/>手工<input type="radio" name="range_type" value="1" <?php if ($response['data']['range_type'] == 1) { ?> checked <?php } ?> id="redouble" />倍增
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox"  <?php if ($response['data']['is_contain_delivery_money'] == 1) { ?> checked <?php } ?> name="is_contain_delivery_money" value="1"/>包含运费
            </div>
            <!-- 手工 -->
            <div class="custom_set">
                <table class='table_pane3'>

                    <?php foreach ($response['range'] as $range_row) { ?>
                        <tr>
                            <td  width="10%"><span class="x-icon  x-icon-mini x-icon-error" onclick="delete_range(<?php echo $range_row['id']; ?>)">×</span></td>
                            <td width="27.5%"><?php echo $range_row['range_start']; ?></td>
                            <td width="35%" style="color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;"><span class="symbol"><?php if($response['strategy']['strategy_new_type']==1){echo '≤';}else{echo '<';}?></span> 数量范围 <span class="symbol">≤</span></td>
                            <td width="27.5%"><?php echo $range_row['range_end']; ?></td>
                        </tr>
                    <?php } ?> 
                </table>
                <div class="add_range" style="margin-top:20px;display:none;"> 
                    <input type="text" name="range_start" id="range_start" /><span style="color:red;"><span class="symbol"><?php if($response['strategy']['strategy_new_type']==1){echo '≤';}else{echo '<';}?></span> 数量范围 <span class="symbol">≤</span></span><input type="text" name="range_end" id="range_end" />
                    <button type="button" class="button"  onclick="save_range();">保存</button>
                    <button type="button" class="button" onclick="cancel_range();">取消</button> 
                </div>
                <div style="margin-top:20px;">
                    <button type="button" class="button" name="add_je" onclick="add_je_range()">添加</button>
                </div>
                <!-- 指定商品与数量的手工设置说明 -->
                <div style="color:red;<?php if ($response['data']['goods_condition'] == 1) { ?> display:none; <?php } ?>" class="custom_note_1" >
                    说明：指定商品与数量：购买活动店铺指定商品以及数量才送赠品。可通过‘添加’实现按照数量范围设置送不同的赠品。<br/>
                </div>
                <!-- 随机商品与数量的手工设置说明 -->
                <div style="color:red;<?php if ($response['data']['goods_condition'] == 0) { ?> display:none; <?php } ?>" class="custom_note_2" >
                    说明：随机商品与数量：购买活动店铺指定商品中随机款且满足随机数量即送赠品。可通过‘添加’实现按照数量范围设置送不同的赠品。<br/>
                </div>
            </div>
            <!-- 倍增 -->
            <div class="doubled_set" style="<?php if ($response['data']['range_type'] == 0) { ?>display:none;<?php } ?>">
                <input type="text" name="doubled" id="doubled" value="<?php echo $response['data']['doubled']; ?>"/>
                <div style="color:red;" class="doubled_note_1"  >
                    说明：订单数量满足设置数量范围倍数，赠品翻倍赠送。假如设置买1个送商品A1个，买2个即送商品A2个，以此类推
                </div>
            </div>
        </div>
    <?php } ?>


    <div class="clearfix" style="text-align: center;margin-top:50px;">
        <input type="hidden" name="op_gift_strategy_detail_id" value="<?php echo $response['data']['op_gift_strategy_detail_id']; ?>" />
        <button type="submit" class="button button-primary" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> id="submit">保存</button>
    </div>
</form>
<script type="text/javascript">
    var id = "<?php echo $request['_id']; ?>";
    var type = <?php echo $response['data']['type']; ?>//满赠、买赠
    var range_type = "<?php echo $response['data']['range_type']; ?>"; //固定 倍增
    var goods_condition = "<?php echo $response['data']['goods_condition']; ?>"; //买赠类型 全场、 固定 、 随机
    var strategy_new_type="<?php echo $response['strategy']['strategy_new_type'];?>"//策略类型 0旧策略，1新策略
    $(function () {
        //非全场买赠
        if (type == 0 || (type == 1 && 　goods_condition != 2)) {
            if (range_type == 1) {
                $(".doubled_set").css('display', '');
                $(".custom_set").css('display', 'none');
            } else {
                $(".custom_set").css('display', '');
                $(".doubled_set").css('display', 'none');
            }
        }
        //买赠
        if (type == 1) {
            if (goods_condition == 2) {
                $(".buy_set").css('display', 'none');
            } else {
                $(".buy_set").css('display', '');
            }
        }

        $("input:radio[name='range_type']").change(function () {
            range_type = $("input:radio:checked[name='range_type']").val();
            $.post('<?php echo get_app_url('op/gift_strategy/check_gift'); ?>', {'op_gift_strategy_detail_id': id}, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == -1) {
                    $('input:radio[name="range_type"][value="<?php echo $response['data']['range_type'] ?>"]').prop('checked', true);
                    BUI.Message.Alert('请先删除已设置的赠品商品再修改数量范围设置', function () { }, type);
                } else {
                    if (range_type == 1) {
                        $(".doubled_set").css('display', '');
                        $(".custom_set").css('display', 'none');

                    } else {
                        $(".custom_set").css('display', '');
                        $(".doubled_set").css('display', 'none');
                    }
                    change_display_note();
                }
            },'json')

        });
        $("input:radio[name='goods_condition']").change(function () {
            //是否已经设置了赠品 否则不能修改
            $.post('<?php echo get_app_url('op/gift_strategy/check_gift'); ?>', {'op_gift_strategy_detail_id': id}, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == -1) {
                    $('input:radio[name="goods_condition"][value="<?php echo $response['data']['goods_condition'] ?>"]').prop('checked', true);
                    BUI.Message.Alert('请先删除已设置的赠品商品再修改买赠类型', function () { }, type);
                } else {
                    goods_condition = $("input:radio:checked[name='goods_condition']").val();
                    if (goods_condition == 2) {
                        $(".buy_set").css('display', 'none');
                        $(".buy_all").css('display', '');
                        $(".buy_random").css('display', 'none');
                    } else {
                        $(".buy_set").css('display', '');
                        $(".buy_all").css('display', 'none');
                        if (goods_condition == 1) {
                            $(".buy_random").css('display', '');
                        } else {
                            $(".buy_random").css('display', 'none');
                        }
                    }
                    change_display_note();
                }
            }, "json");

        });
    });

    function change_display_note() {
        if (type == 1) {
            //买赠 数量范围设置不同 说明内容不同
            var goods_condition = $("input:radio:checked[name='goods_condition']").val();
            var range_type = $("input:radio:checked[name='range_type']").val();
            if (goods_condition == 0) {
                //指定商品与数量
                if (range_type == 0) {
                    //手工
                    $(".custom_note_1").css('display', '');
                    $(".custom_note_2").css('display', 'none');
                }

            } else if (goods_condition == 1) {
                //随机商品与数量
                if (range_type == 0) {
                    //手工
                    $(".custom_note_2").css('display', '');
                    $(".custom_note_1").css('display', 'none');
                }
            }
        }
    }

    function do_page(type) {
        if (type == 'base') {
            location.href = "?app_act=op/gift_strategy/rule_view&app_scene=edit&_id=" + id;
        } else if (type == 'gift') {
            location.href = "?app_act=op/gift_strategy/gift_goods&app_scene=edit&_id=" + id;
        } else if (type == 'goods') {
            location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id=" + id;
        } else if (type == 'customer') {
            location.href = "?app_act=op/gift_strategy/rule_customer&app_scene=edit&_id=" + id;
        }


    }
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }
    BUI.use('bui/form', function (Form) {
        var form = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, function () {
                    if (data.status == 1) {
                        ui_closePopWindow(getQueryString('ES_frmId'));

                    }
                }, type);
            }
        }).render();
        form.on('beforesubmit', function () {
            if ($("input:radio[name = 'range_type']:checked").val() == 0) {
                if ($(':radio[name="goods_condition"]:checked').val() != 2) {
                    if ($(".table_pane3").find('td:gt(0)').text() == '') {
                        if ($("#range_start").val() == '') {
                            BUI.Message.Alert("请填写范围", 'error');
                            return false;
                        }
                        if ($("#range_end").val() == '') {
                            BUI.Message.Alert("请填写范围", 'error');
                            return false;
                        }
                    }
                }
            }
            if ($("input:radio[name = 'range_type']:checked").val() == 1) {
                if ($("#doubled").val() == '') {
                    BUI.Message.Alert("请填写倍增", 'error');
                    return false;
                }
            }
        });
    })
//添加金额范围
    function add_je_range() {
        $(".add_range").css('display', '');

    }
    function cancel_range() {
        $(".add_range").css('display', 'none');

    }
    function save_range() {
        var range_start = $("#range_start").val();
        var range_end = $("#range_end").val();
        if (parseInt(range_end) < parseInt(range_start)) {
            BUI.Message.Alert("数据范围设置值不能相等！", 'error');
            return;
        }
        $.post('<?php echo get_app_url('op/gift_strategy/add_range'); ?>', {'op_gift_strategy_detail_id': id, 'range_start': range_start, 'range_end': range_end,'strategy_new_type':strategy_new_type}, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                $(".add_range").css('display', 'none');
                reload_range();
            } else {
                BUI.Message.Alert(data.message, function () { }, type);
            }
        }, "json");

    }
    function reload_range() {
        var range_div = "";
        $.post('<?php echo get_app_url('op/gift_strategy/rule_range'); ?>', {'op_gift_strategy_detail_id': id}, function (data) {
            $.each(data.data, function (i, row) {
                var type_show=(strategy_new_type==1)?'≤':'<';
                if(data.type == 0){
                    range_div += "<tr><td  width='10%'><span class='x-icon  x-icon-mini x-icon-error' onclick='delete_range(" + row.id + ")'>×</span></td><td>" + row.range_start + "</td><td width='35%' style='color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;'><span class='symbol'>"+type_show+"</span> 金额范围 <span class='symbol'>≤</span></td><td>" + row.range_end + "</td></tr>";
                }else if(data.type == 1){
                    range_div += "<tr><td  width='10%'><span class='x-icon  x-icon-mini x-icon-error' onclick='delete_range(" + row.id + ")'>×</span></td><td>" + row.range_start + "</td><td width='35%' style='color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;'><span class='symbol'>"+type_show+"</span> 数量范围 <span class='symbol'>≤</span></td><td>" + row.range_end + "</td></tr>";
                }
            });
            $(".table_pane3").html(range_div);
        }, "json");
    }
    function delete_range(range_id) {
        BUI.Message.Confirm('是否确定要删除，执行此操作后，也将清空此范围下设置的赠品商品', function () {
            $.post('<?php echo get_app_url('op/gift_strategy/delete_range'); ?>', {'range_id': range_id}, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    $(".add_range").css('display', 'none');
                    reload_range();
                } else {
                    BUI.Message.Alert(data.message, function () { }, type);
                }
            }, "json");
        }, 'warning');


    }

</script>
