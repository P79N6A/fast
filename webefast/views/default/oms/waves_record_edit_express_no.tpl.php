<form id="form1" method="post" action="?app_act=oms/waves_record/edit_express_no_action" tabindex="0" style="outline: none;">
    <input type="hidden" id="waves_record_id" name="waves_record_id" value="">
    <table cellspacing="0" class="table">
        <tbody>
        <tr>
            <td width="12%" align="right">配送方式：</td>
            <td width="28%">
                <select name="express_code" id="express_code">
                    <?php $list = oms_tb_all('base_express', array()); foreach($list as $k=>$v){ if(isset($response['express_arr'][$v['express_code']])){ ?>
                        <option value="<?php echo $v['express_code']?>"><?php echo $v['express_name']?></option>
                    <?php }} ?>
                </select>
            </td>
            <!--<td width="17%">
                <div class="control-group">
                    <label class="control-label checkbox">
                        <input type="checkbox" name="consecutive_match" id="consecutive_match" value="1" checked> 自动连续匹配
                    </label>
                </div>
            </td>-->
            <td width="25%">
                <div class="control-group">
                    <label class="control-label checkbox" style="width:120px;">
                        <input type="checkbox" name="check_the_no" id="check_the_no" value="1" checked> 校验物流单号
                    </label>
                </div>
            </td>
            <td style="color: red;"><!--(请输入第一条物流单号后回车)--></td>
        </tr>
        </tbody>
    </table>
    <table cellspacing="0" class="table table-bordered" id="record_list">
        <thead>
        <th width="50%">订单号</th>
        <th width="50%">物流单号</th>
        </thead>
        <tbody>
        <?php foreach($response['deliver_record_list'] as $k=>$v){ ?>
            <tr id="row_<?php echo $k;?>" class="<?php echo $v['express_code']?>">
                <td><?php echo $v['sell_record_code']?></td>
                <td>
                    <input type="text" name="express_no[<?php echo $v['deliver_record_id']?>]" id="express_no_<?php echo $v['deliver_record_id']?>" value="<?php echo $v['express_no']?>">
                    <button value="<?php echo $k;?>" type="button" class="button button-small" title="向下连续匹配"><i class="icon-arrow-down"></i></button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="clearfix" style="text-align: center;">
        <button type="submit" class="button button-primary" >确定</button>
    </div>
</form>

<script>
    var rows = <?php echo count($response['deliver_record_list']);?>;
    BUI.use('bui/form',function (Form) {
        var form1 = new BUI.Form.HForm({
            srcNode : '#form1',
            submitType : 'ajax',
            callback : function(data){
                if(data.status != '1'){
                    BUI.Message.Alert(data.message, 'error')
                } else {
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                }
            }
        }).render();
    });

    $(document).ready(function(){
        $("#record_list tbody").find("button").click(function(){
            var i = parseInt($(this).val());

            var params = {
                "express_code": $("#express_code").val(),
                "express_no": $(this).parent("td").find("input").val(),
                "check_the_no": $("input[name=check_the_no]:checked").val(),
                "rows": rows
            };
            $.post("?app_act=oms/sell_record/next_express_no", params, function(data){
                if(data.status != 1){
                    BUI.Message.Alert(data.message, 'error')
                    return
                }
                for(var j = i + 1; j < rows; j++){
                    var item = $("#row_"+j);
                    if(typeof item.css("display") == "undefined" || item.css("display") == "none") continue;
                    var no = data.data.shift();
                    item.find("input").val(no)
                }
            }, "json")
        })
    })

    $("#express_code").click(function(){
        var defaultExpress = $("#express_code").val()
        $("#record_list tbody").find("tr").each(function(){
            if($(this).attr("class") != defaultExpress){
                $(this).hide();
            } else {
                $(this).show();
            }
        })
    })

    $("#express_code").click()
</script>