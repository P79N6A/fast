<style>
    #container{
        padding: 0 1% 20px;
    }
</style>
<!--<div class="panel">-->
    <form>
            <div class="panel-body" id="panel_order">
                <table cellspacing="0" class="table table-bordered" id="table1">
                    <tbody>
                        <tr style="height: 40px;">
                            <td align="center">
                                <span>是否保价</span>
                            </td>
                            <td>
                                <input type="checkbox" name="is_guarantee" id="is_guarantee">      
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <span>保价金额</span>
                            </td>
                            <td>
                                <input type="text" name="guarantee_money" id="guarantee_money" disabled></br>
                                <span style="color: red">只保留小数点后两位</span>
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align:right;margin-top:10px;" id="save_change">
                <button type="button" id='enter' class="button button-primary" >确定</button>
                <button type="button" id='cancel' class="button">取消</button>
            </div>
    </form>
<!--</div>-->
<script type="text/javascript">    
    $(function () {
       $('#is_guarantee').change(function() {
            if($('#is_guarantee').is(':checked')) {
               $("#guarantee_money").attr('disabled',false);
            } else {
               $("#guarantee_money").attr('disabled',true);
               $("#guarantee_money").val('');
            }
        });
        $('#cancel').click(function(){
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        })
        $("#enter").click(function () {
            var guarantee_money = $("#guarantee_money").val() ;
            if($('#is_guarantee').is(':checked')) {
                if(guarantee_money == '' || guarantee_money == undefined) {
                    BUI.Message.Alert('请填写保价金额','error');
                    return false;
                }
                if (isNaN(guarantee_money) || guarantee_money <= 0) {
                    BUI.Message.Alert('保价金额必须是大于零的数字', 'error');
                    return false;
                }
            }
            parent.get_jd_express_code(guarantee_money);
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        })
    })
</script>