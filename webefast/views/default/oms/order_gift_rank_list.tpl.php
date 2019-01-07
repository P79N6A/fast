<?php echo load_js('comm_util.js') ?>
<style>
    .table_pane2{width: 100%;border: solid 1px #ded6d9;margin-bottom: 5px;margin-top: 5px;}
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
   .table_pane2 td {
       border:1px solid #dddddd;
       line-height: 15px;
       padding: 5px;
       text-align: left;

   }
    input.calendar {
        width: 150px;
    }
    .custom-dialog{
        border: none;
        -webkit-box-shadow : none;
        box-shadow : none;
      }
 
      .custom-dialog .bui-stdmod-header,.custom-dialog .bui-stdmod-footer{
        display: none;
      }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '排名送赠品',
    'links' => array(),
    'ref_table' => 'table',
));
?>

<div class="row">
    <div class="doc-content" style="width:100%">
        <form method="post" class="form-horizontal well">
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">赠品规则：</label>
                    <div class="controls">
                        <input type="text" id="p_code_select_pop" class="input-normal es-selector" value="" />
                        <img class="sear_ico" id="p_code_select_img" src="assets/img/search.png" /><input type="hidden" id="p_code" value="" name="p_code" />  
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="control-group span8">
                    <label class="control-label">店铺：</label>
                    <div class="controls" id="rank_shop_code">
                       
                    </div>
                    <input type="hidden" name="strategy_code" id="strategy_code" value="" >
                </div>
            </div>
            <div class="control-group span8" style="margin-top:10px;width: 650px;">
                <div class="control-group span8">
                    <div class="controls">
                        <input id="rank_hour" type="text" value="<?php echo date('Y-m-d H:i:s');?>" name="rank_hour" disabled="disabled" data-rules="{required : true}" class="calendar">
                    </div>
                </div>
                <div class="form-actions span offset1" style="width:360px;">
                    <button type="button" class="button button-primary" id="rank_list_reset" disabled="true">排名数据刷新</button>
                    <button type="button" class="button button-primary" id="send_gift" disabled="true">一键送赠品</button>
                    <button type="button" class="button button-primary" id="order_gift_import" disabled="true">导出</button>
                </div>
            </div>
        </form>
    </div>
</div> 


<div class="panel_div_range" style="margin-top:20px;" id="panel_div_range">

</div>
<div  style="color:red;">
    <span>
        说明：数据来源已付款有效的销售订单（包括货到付款订单）,已确认订单不会赠送赠品<br>
        使用排名送赠品工具：1）系统不能开启自动确认服务。<br>
        2）需关闭系统参数 ‘转单自动确认’<br>
    </span>
</div>
<script type="text/javascript">
    $("#order_gift_import").click(function (){
        var op_gift_strategy_detail_id = $("#p_code").val();
        var rank_hour = $("#rank_hour").val();
        var shop_code = $("#shop_code_select").val();
        var param="";
    	param += "&op_gift_strategy_detail_id="+op_gift_strategy_detail_id+"&rank_hour="+rank_hour+"&shop_code="+shop_code;
    	url="?app_act=oms/order_gift/export_csv_list"+param;
    	
    	window.location.href=url;
    });
    
    $('#send_gift').on('click',function () {
        $('#send_gift').attr("disabled","disabled");
        var op_gift_strategy_detail_id = $("#p_code").val();
        var rank_hour = $("#rank_hour").val();
        var shop_code = $("#shop_code_select").val();
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/order_gift/send_gift'); ?>', 
            data: {op_gift_strategy_detail_id: op_gift_strategy_detail_id,rank_hour:rank_hour,shop_code:shop_code},
            success: function(ret) {
                $('#send_gift').removeAttr("disabled");
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message);
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    });

    $("#rank_list_reset").click(function() {
        var op_gift_strategy_detail_id = $("#p_code").val();
        var rank_hour = $("#rank_hour").val();
        var shop_code = $("#shop_code_select").val();
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/order_gift/get_rank_list'); ?>', data: {op_gift_strategy_detail_id: op_gift_strategy_detail_id,rank_hour:rank_hour,shop_code:shop_code},
            success: function(ret) {
                if (ret.status == 1) {
                    $('#send_gift').attr("disabled",false);
                    $('#order_gift_import').attr("disabled",false);
                    var data = ret.data;
                    var html = '';
                    var i = 1;
                    $.each(data, function(k1, v1){
                        var j = 1;
                        html += '<div>';
                        html += '<span class="range">排名范围'+v1[0]['rank_start']+'~'+v1[0]['rank_end']+'</span>';
                        html += '<table class="table_pane2">';
                        html += '<tr><td width="50">序号</td>';
                        html += '<td width="100">订单号</td>';
                        html += '<td width="100">交易号</td>';
                        html += '<td width="50">订单状态</td>';
                        html += '<td width="100">是否已参与排名送</td>';
                        html += '<td width="150">下单时间</td>';
                        html += '<td width="150">付款时间</td>';
                        html += '<td width="100">买家昵称</td>';
                        html += '<td width="100">订单应收金额</td></tr>';
                        $.each(v1, function(k2, v2){
                            if(j >= 6){
                                html += '<tr><td width="50">...</td>';
                                html += '<td width="150"></td>';
                                html += '<td width="150"></td>';
                                html += '<td width="150"></td>';
                                html += '<td width="150"></td>';
                                html += '<td width="100"></td>';
                                html += '<td width="100"></td></tr>';
                                return false;
                            } else {
                                html += '<tr><td width="50">'+i+'</td>';
                                html += '<td width="100">'+v2['sell_record_code']+'</td>';
                                html += '<td width="100">'+v2['deal_code_list']+'</td>';
                                html += '<td width="50">'+(v2['order_status'] == 1 ? '<font color=\"red\">已确认</font>' : '未确认')+'</td>';
                                html += '<td width="50">'+(v2['is_has_given'] == 1 ? '已赠送':'未赠送')+'</td>';
                                html += '<td width="150">'+v2['record_time']+'</td>';
                                html += '<td width="150">'+v2['pay_time']+'</td>';
                                html += '<td width="100">'+v2['buyer_name']+'</td>';
                                html += '<td width="100">'+v2['order_money']+'</td></tr>';
                                i++;
                                j++;
                            }
                           
                        });
                        html += '</table>';
                        html += '</div>';
                    });
                    $("#panel_div_range").html(html);
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    });

    var selectPopWindowp_code = {
        dialog: null,
        callback: function(value) {
            var op_gift_strategy_detail_id = value[0]['op_gift_strategy_detail_id'];
            var ranking_hour = value[0]['ranking_hour'];
            var name = value[0]['name'];
            var shop_code = value[0]['shop_code'];
            $('#p_code_select_pop').val(name);
            $('#p_code').val(op_gift_strategy_detail_id);
            $("#rank_hour").val(ranking_hour);
            $('#rank_list_reset').attr("disabled",false);
            var html = '<select name="shop_code" id="shop_code_select">';
            $.each(shop_code, function(k, v){
                html += ' <option value="' + v['shop_code']+'">'+v['shop_name']+'</option>';
            });
            html += ' </select>';
            $("#rank_shop_code").html(html);
            if (selectPopWindowp_code.dialog != null) {
                selectPopWindowp_code.dialog.close();
            }
        }
    };

    $('#p_code_select_pop,#p_code_select_img').click(function() {
        selectPopWindowp_code.dialog = new ESUI.PopSelectWindow('?app_act=common/select/rank_rule', 'selectPopWindowp_code.callback', {title: '选择规则', width: 900, height: 500, ES_pFrmId: "<?php echo $request['ES_frmId']; ?>"}).show();
    });

    BUI.use('bui/calendar',function(Calendar){
        var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
            showTime:true,
            autoRender : true
        });
    });

</script>

<?php include_once (get_tpl_path('process_batch_task')); ?>