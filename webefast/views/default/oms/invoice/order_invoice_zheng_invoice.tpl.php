<style>
        .bui-tab-item{
        position: relative;
    }
    .bui-tab-item .bui-tab-item-text{
        padding-right: 25px;
    }
    #cel{
        margin-top: 200px;
        margin-left: 40%;
    }
</style>
<div id ="m">
<div id="invoice_kai" class="zheng_invoice">

    <table class="table">
        <tr>
            <td  >订单编号</td>
            <td  >待开票金额</td>
             <td  >开票性质</td>
             <td >开票备注</td>
              <td id="t4">冲红原因</td>
        </tr>
        <?php 
        
        foreach($response['data'] as $key =>$val): ?>
        <tr>
            <td ><?php echo $val['sell_record_code']; ?></td>
            <td ><?php echo $val['invoice_amount']; ?></td>
            <td><span id="invoice_type"><?php if($val['is_invoice']=='0' || $val['is_red'] == 2){ echo '<span style=color:blue>正票</span>';} if($val['is_invoice']==2 && $val['is_red']=='0'){ echo '<span style=color:red>红票</span>';}?></span></td>
            <?php if($val['invoice_remark']==''): ?>
            <td class="t1"><span  class="zheng_<?php echo $key;?>">店铺名称：<?php echo $val['shop_name']; ?>,&nbsp;&nbsp;&nbsp;交易号：<?php echo $val['deal_code_list']; ?></span></td>
            <?php else : ?>
            <td  class="t1"><span  class="zheng_<?php echo $key;?>"><?php echo $val['invoice_remark']; ?></span></td>
           <?php endif;?>
            <td class="t2"><span  class="hong_<?php echo $key;?>">正票的发票代码：<?php echo $val['fp_dm']; ?>,&nbsp;&nbsp;&nbsp;正票的发票号：<?php echo $val['invoice_no']; ?></span></td>
            <td class="t3"><input type="text" class="chyy_<?php echo $key;?>" value="退单"/></td>
        </tr>
        <input type="hidden" class="sell_record_<?php echo $key;?>" value="<?php echo $val['sell_record_code']?>">
        <?php endforeach;?>
    </table>
</div>
         <div class='' id="cel">
                <div class="span13 offset3">
                    <button type="confirm" class="button button-primary" id="confirm">确认</button>
                    <button type="cancle" class="button" id="cancle" >取消</button>
                </div>
            </div>
</div>
<script>
    
    var invoice_type = $("#invoice_type").text();
    
    var is_red;
    if(invoice_type=='正票'){
        is_red='0';
    }else{
        is_red=1;
    }
   // var sell_record_code = "<?php echo $response['data']['sell_record_code']; ?>";
    var num = <?php echo count($response['data']);?>;
    
    $(function () {
        if(is_red=='0'){
            //开正票
            $("#invoice_kai .t1").show();
            $("#invoice_kai .t2").hide();
            $("#invoice_kai .t3").hide();
            $("#t4").hide();
        }else{
           //开红票
            $("#invoice_kai .t1").hide();
            $("#invoice_kai .t2").show();  
            $("#invoice_kai .t3").show();
            $("#t4").show();
        }
        
       //获取class为caname的元素 点击修改开票备注
       for(var i=0;i<num;i++){
            $("#invoice_kai .zheng_"+i).on("click",function(){
            var span = $(this); 
            var txt = span.text();
            var input = $("<input type='text'value='" + txt + "'/>");
            span.html(input);
            input.on("click",function(){
                return false;
            });
             //获取焦点 
            input.trigger("focus"); 
            //文本框失去焦点后提交内容，重新变为文本 
            input.on("blur",function(){
                var newtxt = $(this).val();
                if(newtxt==''){
                    newtxt="&nbsp;";
                }
                if (newtxt != txt) {
                    span.html(newtxt); 
                }else{
                    span.html(txt); 
                }
            });
        });
       }
       
       var param_arr = new Array();
        $('#confirm').click(function () {
                BUI.use(['bui/mask'],function(Mask){
                  loadMask = new Mask.LoadMask({
                      el : 'body',
                      msg : '正在请求开票中...'
                  });
                   loadMask.show();
                 });
        param_arr = [];
        //var param;
            for(var i=0;i<num;i++){
                sell_record_code = $("#invoice_kai .sell_record_"+i).val();
                 if(is_red =='0'){
                    data_info = $("#invoice_kai .zheng_"+i).text().replace(/\s/g, "");
                    param = {sell_record_code: sell_record_code,data_info:data_info};
                 }else{
                     chyy = $('#invoice_kai .chyy_'+i).val();
                     data_info = $("#invoice_kai .hong_"+i).text();
                     param= {sell_record_code: sell_record_code,data_info:data_info,chyy:chyy};
                 }
                 
                    param.type=is_red;
                    param_arr.push(param);
            }
                 
                 var url = "?app_act=oms/invoice/order_invoice/confirm&app_fmt=json";
                    $.post(url, {'list':param_arr}, function (ret) {
                       loadMask.hide();//隐藏进度条
                       var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert('提交开票请求成功',type);
                        } else {
                            BUI.Message.Alert(ret.message, type);
                        }
                   }, 'json');
            

        });
        //取消关闭弹窗
       $('#cancle').click(function () {
            ui_closeTopPopWindow();
        });
    });
    


</script>