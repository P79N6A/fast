<style type="text/css">
    .well {
        background-color: #fff;
    }
    .well .control-group {
        width: 600px;
        padding-left: 10px;
        margin-top: 10px;
    }
    .well .control-label {
        width: 100px;
    }
    .table_pane3{width: 50%;border: solid 1px #ded6d9;}
    .table_pane3 td {
        border:1px solid #dddddd;
        line-height: 15px;
        padding: 5px;
        text-align: center;
    }
    .span8 {
        width: 600px;
    }
    #range_start{
        width: 100px;
    }
    #range_end{
        width: 100px;
    }
    
input.calendar {
    width: 150px;
}
.symbol{
        font-weight:bold;
        font-size:large;
    }

    #info {
        position:absolute;
        top:90px;
        margin-left:500px;
    }
</style>


<?php echo load_js("baison.js,record_table.js", true); ?>
<ul class="nav-tabs oms_tabs">
    <li class="active" onClick="do_page('base');"><a href="#"  >规则设置</a></li>
    <li onClick="do_page('gift');"><a href="#" >赠品商品</a></li>
    <li onClick="do_page('goods');"><a href="#" >活动商品</a></li>
</ul>
<div class="demo-content">
    <div class="row">
        <div class="span8 doc-content">
            <form id="form1" action="?app_act=op/gift_strategy/do_edit_detail&app_fmt=json" method="post" class="form-horizontal well">
                <div class="control-group">
                    <label class="control-label">规则名称：</label>
                    <div class="controls">
                        <input type="text" class="input-normal control-text"  name="name" value="<?php echo $response['data']['name']; ?>"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">规则状态：</label>
                    <div class="controls">
                        <label class="control-label checkbox">
                            <input type="radio" <?php if($response['data']['status'] == 1){ ?> checked = 'checked' <?php }?> name="status" value="1"/>启用
                        </label>
                        <label class="control-label checkbox">
                            <input type="radio" <?php if($response['data']['status'] == 0){ ?> checked = 'checked' <?php }?> name="status" value="0" style="margin-left: 20px;"/>停用
                        </label>
                    </div>
                </div>
                <div class="control-group" id="ranking_time_type">
                    <label class="control-label">&nbsp;</label>
                    <div class="controls">
                        <label class="control-label checkbox">
                            <input type="radio" <?php // if($response['data']['ranking_time_type'] == 1){ ?> checked = 'checked' <?php // }?> name="ranking_time_type" value="1" />指定时间点
                        </label>
                    </div>
                    <div class="controls" id="ranking_hour">
                        <input id="ranking_hour" type="text" value="<?php echo $response['data']['ranking_hour'];?>" name="ranking_hour" data-rules="{required : true}" class="calendar">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">排名范围设置</label>
                </div>
                <div class="control-group">
                    <div class="custom_set">
                        <table class='table_pane3'>
                            <?php foreach ($response['range'] as $key => $range_row) { ?>
                                <tr>
                                    <?php if ($response['strategy']['is_check'] == 0) { ?>
                                    <td width="10%"><span class="x-icon  x-icon-mini x-icon-error" onclick="delete_range(<?php echo $range_row['id']; ?>)">×</span></td>
                                    <?php } ?>
                                    <td width="30%"><?php echo $range_row['range_start']; ?></td>
                                    <td width="30%" style="color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;"><span class="symbol">≤</span> 排名 <span class="symbol">≤</span></td>
                                    <td width="30%"><?php echo $range_row['range_end']; ?></td>
                                </tr>
                            <?php } ?> 
                        </table>
                        <div class="add_range" style="margin-top:20px;display:none;"> 
                            <input type="text" name="range_start" id="range_start"/><span style="color:red;"><span class="symbol">≤</span> 排名 <span class="symbol">≤</span></span><input type="text" name="range_end" id="range_end"/>
                            <button type="button" class="button"  onclick="save_range();">保存</button>
                            <button type="button" class="button" onclick="cancel_range();">取消</button> 
                        </div>
                        <div style="margin-top:20px;">
                            <button type="button" class="button" name="add_je" onclick="add_je_range()">添加</button>
                        </div>
                        
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label checkbox">
                        <input type="checkbox" name="is_goods_money" value="1" <?php if($response['data']['is_goods_money'] == 1){ ?> checked <?php }?> />启用最低金额
                     </label>
                    <input type="text" name="money_min" value="<?php echo $response['data']['money_min']?>" style="width:100px;"/><span style="color:red;">提示：只有满足此金额的订单，才会参与排名送赠品</span>
                   
                </div>
               <div class="clearfix" style="text-align: center;margin-top:50px;">
                    <input type="hidden" name="op_gift_strategy_detail_id" value="<?php echo $response['data']['op_gift_strategy_detail_id'];  ?>" />
                    <input type="hidden" name="type" value="2" />
                    <button type="submit" class="button button-primary" oncilck ="proving()" <?php if ($response['strategy']['is_check'] == 1) {   ?>disabled <?php }  ?> id="submit">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="info" id="info">温馨提示：排名送规则建好后需要前往<a href="javascript:open_web()">网络订单—按排名送赠品</a>页面手工添加赠品</div>
<script type="text/javascript">
    var id = "<?php echo $request['_id']; ?>";
    var type = <?php echo $response['data']['type']; ?>//满赠、买赠
    var range_type = "<?php echo $response['data']['range_type']; ?>"; //固定 倍增
    var goods_condition = "<?php echo $response['data']['goods_condition']; ?>"; //买赠类型 全场、 固定 、 随机
    var ranking_time_type = "<?php echo $response['data']['ranking_time_type']?>";
    var rank_end = "<?php echo $response['last_range'];?>";
    $(function() {
        if(ranking_time_type == 1){
            $("#ranking_hour").show();
        } else {
            $("#ranking_hour").hide();
        }
        
        $("#ranking_time_type input[name='ranking_time_type'] ").change(function (){
            var ranking_time_type = $("#ranking_time_type input[name='ranking_time_type']:checked").val();
            if(ranking_time_type == 1){
                $("#ranking_hour").show();
            } else {
                $("#ranking_hour").hide();
            }
        });
        
        
        
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

        $("input:radio[name='range_type']").change(function() {
            range_type = $("input:radio:checked[name='range_type']").val();
            if (range_type == 1) {
                $(".doubled_set").css('display', '');
                $(".custom_set").css('display', 'none');

            } else {
                $(".custom_set").css('display', '');
                $(".doubled_set").css('display', 'none');
            }
            change_display_note();

        });
        $("input:radio[name='goods_condition']").change(function() {
            //是否已经设置了赠品 否则不能修改
            $.post('<?php echo get_app_url('op/gift_strategy/check_gift'); ?>', {'op_gift_strategy_detail_id': id}, function(data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == -1) {
                    $('input:radio[name="goods_condition"][value="<?php echo $response['data']['goods_condition'] ?>"]').prop('checked', true);
                    BUI.Message.Alert('请先删除已设置的赠品商品再修改买赠类型', function() {
                    }, type);
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
            location.href = "?app_act=op/gift_strategy/ranking_rule_view&app_scene=edit&_id=" + id;
        } else if (type == 'gift') {
            location.href = "?app_act=op/gift_strategy/ranking_gift_goods&app_scene=edit&_id=" + id;
        } else if (type == 'goods') {
            location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id=" + id;
        }
    }
    
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }
    BUI.use('bui/form',function (Form) {
        var form = new BUI.Form.HForm({
            srcNode: '#form1',
            submitType: 'ajax',
            callback: function(data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, function() {
                    if (data.status == 1) {
                        ui_closePopWindow(getQueryString('ES_frmId'));

                    }
                }, type);
            }
        }).render();
        form.on('beforesubmit', function() {
//            if($("#range_start").val() == '') {
//                BUI.Message.Alert("请填写排名范围", 'error');
//                return false;
//            }
//            if($("#range_end").val() == '') {
//                BUI.Message.Alert("请填写排名范围", 'error');
//                return false;
//            }
        });
    });
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
            BUI.Message.Alert("请正确填写排名！", 'error');
            return;
        }
//        if(parseInt(range_start) <= parseInt(rank_end)){
//            BUI.Message.Alert("请正确填写排名！", 'error');
//            return;
//        }
        
        $.post('<?php echo get_app_url('op/gift_strategy/add_range'); ?>', {'op_gift_strategy_detail_id': id, 'range_start': range_start, 'range_end': range_end,'strategy_new_type':1}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                $(".add_range").css('display', 'none');
                reload_range();
            } else {
                BUI.Message.Alert(data.message, function() {
                }, type);
            }
        }, "json");

    }
    function reload_range() {
        var range_div = "";
        $.post('<?php echo get_app_url('op/gift_strategy/rule_range'); ?>', {'op_gift_strategy_detail_id': id}, function(data) {
            $.each(data.data, function(i, row) {
                range_div += "<tr><td  width='10%'><span class='x-icon  x-icon-mini x-icon-error' onclick='delete_range(" + row.id + ")'>×</span></td><td>" + row.range_start + "</td><td width='30%' style='color:red;border-left: 1px solid #dddddd;border-right: 1px solid #dddddd;border-top: 1px solid #ffffff;border-bottom: 1px solid #ffffff;'><span class='symbol'>≤</span> 排名 <span class='symbol'>≤</span></td><td>" + row.range_end + "</td></tr>";
            });
            $(".table_pane3").html(range_div);
        }, "json");
    }
    function delete_range(range_id) {
        BUI.Message.Confirm('是否确定要删除，执行此操作后，也将清空此范围下设置的赠品商品', function() {
            $.post('<?php echo get_app_url('op/gift_strategy/delete_range'); ?>', {'range_id': range_id}, function(data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {
                    $(".add_range").css('display', 'none');
                    reload_range();
                } else {
                    BUI.Message.Alert(data.message, function() {
                    }, type);
                }
            }, "json");
        }, 'warning');
    }
    
    BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            showTime:true,
            autoRender : true
        });
    });

    function open_web() {
        var url = "?app_act=/oms/order_gift/rank_list";
        openPage(window.btoa(url), url, '排名送赠品');
    }
</script>
