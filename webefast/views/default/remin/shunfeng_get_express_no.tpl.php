<?php echo load_js('jquery.cookie.js'); ?>
<table cellspacing="0" class="table table-bordered">
	 <tr>
        <td class="tdlabel">月结账号：</td>
        <td>
            <select name="j_custid" id="j_custid">
            <?php foreach($response['j_custid'] as $k=>$v){ ?>
                    <option value="<?php echo $v['j_custid'];?>"><?php echo $v['j_custid']?></option>
            <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="tdlabel">服务类别</td>
        <td>
            <select name="express_type" id="express_type">
            <?php $express_type = $_COOKIE['sf_express_type_before'];?>
            <?php foreach($response['express_type'] as $k=>$v){ ?>              
                    <option value="<?php echo $k;?>"  <?php if ($express_type == $k ) { echo "selected=selected"; }   ?>  ><?php echo $v?></option>                                  
            <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="tdlabel">是否保价</td>
        <td>
            <input type="checkbox" id="bj" name="bj"  value='1'  onchange="bj_select();"/>
        </td>
    </tr>
    <tr class="bj_tr" style="display:none">
        <td class="tdlabel">保价金额</td>
        <td>
            <input type="text" id="bj_je" name="bj_je"  />
        </td>
    </tr>
    <tr>
        <td>保鲜服务</td>
        <td>
            <input type="checkbox" id='bx' name="bx" value="0"/>
        </td>
    </tr>
</table>

<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>
<hr>
<div class="clearfix" id="msg" style="color:red;">
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var bj = 0;
            var bx = 0;
            var bj_je = 0;
            if($("#bj").is(":checked")){
                bj = 1;
                bj_je = $("#bj_je").val();
            }
            if($("#bx").is(":checked")){
                bx = 1;
            }
            $.cookie('sf_express_type_now',$("#express_type").val());
            var express_type = '';
            var express_type_now = $.cookie('sf_express_type_now');
            var express_type_before = $.cookie('sf_express_type_before');
            if (express_type_now == express_type_before ) {
                express_type = express_type_before;
            }else{
                express_type = express_type_now;
            }
            var params = {
                "waves_record_id": '<?php echo $request['waves_record_id'];?>',
                "record_ids": '<?php echo $request['record_ids'];?>',
                "j_custid": $("#j_custid").val(),
                "express_type": express_type,
                "app_fmt": "json",
                "bj":bj,
                "bj_je":bj_je,
                "bx":bx,
            };
            
            $.post("?app_act=remin/shunfeng/upload_oms_sell", params, function(data){
            	var type = data.status == 1 ? 'success' : 'error';
                if(type == 'error'){
                	$("#msg").html(data.message);
                	
               }else {
                    $.cookie('sf_express_type_before',express_type,{expires: 30});
                    BUI.Message.Alert('获取成功', function(){
                    ui_closePopWindow("<?php echo $request['ES_frmId']?>")
                   },type)
                   
                   
               }
            }, "json")
        })
    });
    function bj_select(){
        if ($("#bj").is(":checked")){
            $(".bj_tr").css('display','');
        } else {
            $(".bj_tr").css('display','none');
        }
    }
</script>
