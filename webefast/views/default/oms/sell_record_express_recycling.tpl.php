<?php echo load_js('jquery.cookie.js') ?>

<?php
render_control('PageHead', 'head1', array('title' => '发货快递单回收',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<style>
    .span6{ float: left;
    margin-top: 20px;
    width: 510px;}
    .offset3 .span6 span{ color:#DC0404;}
    .panel{height: 40px; line-height: 40px;}
    #deliver_detail {padding: 0}
    .panel-body{padding-top: 1px;}
    .panel-body table {margin: 0; }
    .form-horizontal {
        position: relative;
        padding: 5px 0px 18px;
        overflow: hidden;
        clear:both;
    }
    .form-horizontal .control-label {width: auto;}
    .span8 { width: auto; }
    .button.active{color:#FFF;background-color:#ec6d3a;border-color:#ec6d3a;}
    #express_no{width: 320px;height: 30px;}
    .control-text{font-size: 25px;}
    .spant{margin-right: 9px;}
    #scan_num,#diff_num,#count_num{font-size: 20px;}
    .result{height: 48px;}
    table{font-size: 16px;width:100%;margin:0 auto;}
    table td{font-weight: normal;text-align:left;width:250px;border:1px solid #dddddd;font-size:15px;}
    .table_top{text-align:right;padding-right:5px;width:100px;}
    .table_bottom{text-align:left;padding-left:5px;}
    td{height:30px;line-height:20px;}
</style>
<div class="form-horizontal" >

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">物流单号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" style="height:40px;font-weight:bold;font-size:30px;" id="express_no" value="">
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls result" style=" height: 50px">
                            <button type="button" class="button" id="btn-submit" onclick="express_back()">确认回收</button>
                            <button type="button" class="button" id="btn-clear" onclick="clear()">清除扫描记录</button>
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls result" style=" height: 50px;margin-left:20px;">
                            <div id="msg" style="color: #ff0000; font-weight: bold;font-size:17px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <div id="order_list">
    
    </div>
</div>
    <div class="span6">
        <input type="hidden" value="" name="company_code" id="company_code" />
        <input type="hidden" value="" name="shop_code" id="shop_code" />
        <input type="hidden" value="" name="express" id="express" />
        <div style="color:red;">温馨提醒：此页面仅针对已发货快递单号回收。且快递单号必须是通过菜鸟云打印获取。</div>   
    </div>
<!--    <iframe id="id_iframe" name="nm_iframe" style="display:none;"></iframe>    -->
<script type="text/javascript">
    $("#express_no").click(function(){
        $("#express_no").attr("value","");
        $("#msg").html("");
    });
    $("#express_no").keyup(function (event) {
        var html_express = '';
        var p = {express_no: $(this).val()};
        if (event.keyCode == 13) {
            if($("#express_no").html() == null){
                $("#msg").html("快递单不能为空");
                return;
            }
            $.post("?app_act=oms/sell_record/show_express", p, function (data) {
                if(data.status == '1'){
                    $("#msg").html("");
                    var row = data.data;
                    html_express += '<table><tr><td class="table_top">订单号</td><td class="table_bottom">'+ row.sell_record_code +'</td><td class="table_top">订单状态</td><td class="table_bottom">'+ row.record_status +'</td><tr><td class="table_top">配送方式</td><td class="table_bottom">'+ row.express_name +'</td><td class="table_top">快递单号</td><td class="table_bottom">'+ row.express_no +'</td></tr>';
//                    html_express += '<tr class="table_bottom"><td>'+ row.sell_record_code +'</td><td>'+ row.record_status +'</td><td>'+ row.express_name +'</td><td>'+ row.express_no +'</td></tr></table>';
                    html_express += '<br/>';
                    $('#company_code').val(row.express_code);
                    $('#shop_code').val(row.shop_code);
                    $('#express').val(row.express_no);
                    $('#order_list').html(html_express);
                    
                }else{
                    $("#msg").html(data.message);
                    $('#order_list').html("");
                }
            },'json');
        }
    });
    function express_back(){
    $.ajax({type: 'POST', dataType: 'json',
                    url: '<?php echo get_app_url('oms/sell_record/express_back'); ?>', data: {id: $('#express').val(),code:$('#company_code').val(),shop:$('#shop_code').val()},
                    success: function (ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            $("#msg").html(ret.message);
                            $('#express_no').val("");
                            $('#company_code').val("");
                            $('#shop_code').val("");
                            $('#order_list').html("");
                            $('#express').val("");
                        } else {
                            $("#msg").html(ret.message);
                        }
                    }
                });            
    }
    $("#btn-clear").click({is_record: 1}, scan_clear_func);
    function scan_clear_func(event) {
            $('#express_no').val("");
            $('#company_code').val("");
            $('#shop_code').val("");
            $('#order_list').html("");
            $('#express').val("");
            $("#msg").html("");
        }
</script>