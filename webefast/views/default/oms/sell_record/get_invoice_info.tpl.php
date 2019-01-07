<style type="text/css">
    .well .control-group {
        width: 100%;
    }
    .sort_btn,.invoice_btn {
        border: 1px solid #d5d5d5;
        border-radius: 3px;
        cursor: pointer;
        height: 24px;
        padding: 0 15px;
        position: relative;
        text-align: center;
    }
    .control-group .controls .tip{ display:inline-block; width:20px; height:20px; position:absolute; top:3px; right:3px; background:url(assets/images/tip.png) no-repeat -2px -2px;}
    .form-horizontal .control-label {
        width: 100px;
    }
</style>
<form class="form-horizontal well" style="padding: 5px 0 10px;" id="edit_invoice_info_form">
    <div style="clear:both;"></div>
    <div class="control-group">
        <div><label class="control-label">是否开票：</label></div>
        <div class="controls docs-input-sizes" id="sort">
            <?php if ($response['record']['invoice_status'] == 0) { ?>
                <span  class='sort_btn' onclick = "sort_invoice(this)" id="1">开票</span>
                <span class='sort_btn active' onclick = "sort_invoice(this)" id="0" style="color: rgb(21, 149, 202);">不开票</span>
            <?php } ?>
            <?php if ($response['record']['invoice_status'] != 0) { ?>
                <span  class='sort_btn active' onclick = "sort_invoice(this)" id="1" style="color: rgb(21, 149, 202);">开票</span>
                <span class='sort_btn' onclick = "sort_invoice(this)" id="0">不开票</span>
            <?php } ?>
        </div>
    </div>
    <div class="" id="inv_all_detail"  <?php if ($response['record']['invoice_status'] == 0) { ?> style="display: none;" <?php } ?>>
    <div class="control-group">
        <div><label class="control-label">发票类型：</label></div>
        <div class="controls docs-input-sizes" id="invoice_type">
            <span class='invoice_btn ' onclick = "invoice(this)" id="vat_invoice" >纸质发票</span>
            <span class='invoice_btn  active' onclick = "invoice(this)" id="pt_invoice">电子发票</span>
            <!--<span class='sort_btn' onclick = "invoice(this)" id="0">电子发票</span>-->
        </div>
    </div>
    <div style="clear:both;"></div>
    <div class="control-group">
        <label class="control-label" id="fptt"></label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_invoice_title"  name="invoice_title" value="<?php echo $response['record']['invoice_title']; ?>">
        </div>
    </div>
        <div class="control-group">
        <label class="control-label">发票抬头类型：</label>
        <div class="controls docs-input-sizes" id="title_type">
            <span><input class="" type="radio" onclick = "radio(this)" id="inv_invoice_title_type_qiye" name="invoice_title_type" value="1" <?php if(isset($response['invoice_data']['is_company']) && $response['invoice_data']['is_company']==1) echo 'checked="checked"';?>>企业</span>
            <span><input class="" type="radio" onclick = "radio(this)" id="inv_invoice_title_type_geren" name="invoice_title_type" value="0"<?php if(empty($response['invoice_data']['is_company'])){ ?> checked="checked" <?php }?> >个人</span> 
        </div>
    </div>
    

    <div style="clear:both;"></div>
    <div class="control-group">
        <label class="control-label">发票内容：</label>
        <div class="controls docs-input-sizes">
                        <input class="span10 span-width control-text" type="text" id="inv_invoice_content"  name="invoice_content" value="<?php echo $response['record']['invoice_content']; ?>">
        </div>
    </div>
    <div style="clear:both;"></div>
    <div class="control-group">
        <label class="control-label">发票号：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_invoice_number" name="invoice_number" value="<?php echo $response['record']['invoice_number']; ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">订单开票金额：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="invoice_money" name="invoice_money" value="<?php echo $response['record']['invoice_money']; ?>">
        </div>
    </div>
    <div id="jsdz"  style="display: none">
         <div class="control-group">
            <label class="control-label">寄送地址：</label>
            <div class="controls docs-input-sizes">
                <input class="span10 span-width control-text" type="text" id="inv_receiver_address" name="receiver_address" value="<?php echo empty($response['invoice_data']['receiver_address'])?$response['record']['decrypt_address']:$response['invoice_data']['receiver_address'];?>">
            </div>
        </div>
     </div>
    <div id="yxdz">
        <div class="control-group">
             <label class="control-label">邮箱地址：</label>
             <div class="controls docs-input-sizes">
                 <input class="span10 span-width control-text" type="text" id="inv_receiver_email" name="receiver_email" value="<?php echo empty($response['invoice_data']['receiver_email'])?$response['record']['receiver_email']:$response['invoice_data']['receiver_email'];?>">
             </div>
        </div>
    </div>
    
    <div id="qiyeshuihao" class="control-group shuihao"  <?php if(empty($response['invoice_data']['is_company'])):?> style="display: none"<?php endif;?> >
  <div style="clear:both;" ></div>
        <label class="control-label"><s>*</s>企业税号：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_taxpayers_code" name="taxpayers_code" value="<?php echo empty($response['invoice_data']['taxpayers_code'])?'':$response['invoice_data']['taxpayers_code']; ?>" placeholder="请输入企业纳税人识别号">
     </div>
    </div>
    
    
    <div style="clear:both;"></div>
    <div class="control-group" id="vat_invoice_div" style=" display: none;">
<!--        <label class="control-label"><s>*</s>单位名称：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_company_name"  name="company_name" value="">
        </div>-->

        <div style="clear:both;"></div>
        <label class="control-label">注册地址：</label>
        <div class="controls docs-input-sizes">

            <input class="span10 span-width control-text" type="text" name="registered_addr" id="inv_registered_addr" placeholder="详细地址" value="<?php echo empty($response['invoice_data']['registered_addr'])?'':$response['invoice_data']['registered_addr'];?>" >
        </div>
        <div style="clear:both;"></div>
        <label class="control-label">注册电话：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_phone"  name="phone" value="<?php echo empty($response['invoice_data']['phone'])?'':$response['invoice_data']['phone']; ?>">
        </div>
        <div style="clear:both;"></div>
        <label class="control-label">开户银行：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_bank" name="bank"  value="<?php echo empty($response['invoice_data']['bank'])?'':$response['invoice_data']['bank']; ?>">
        </div>
        <div style="clear:both;"></div>
        <label class="control-label">银行账号：</label>
        <div class="controls docs-input-sizes">
            <input class="span10 span-width control-text" type="text" id="inv_bank_account"  name="bank_account" value="<?php echo empty($response['invoice_data']['bank_account'])?'':$response['invoice_data']['bank_account']; ?>">
        </div>
        <div style="clear:both;"></div>

    </div>
   </div>
    <div style="display: none">
        <input type="hidden" name="invoice_title_old" id="invoice_title_old" value="<?php echo $response['record']['invoice_title']; ?>">
        <input type="hidden" name="invoice_content_old" id="invoice_content_old" value="<?php echo $response['record']['invoice_content']; ?>">
        <input type="hidden" name="invoice_number_old" id="invoice_number_old" value="<?php echo $response['record']['invoice_number']; ?>">
        <input type="hidden" name="invoice_type_old" id="invoice_type_old" value="<?php echo $response['record']['invoice_type']; ?>">
        <input type="hidden" name="sell_record_code" id="sell_record_code" value="<?php echo $response['record']['sell_record_code']; ?>">
        <input type="hidden" name="taxpayers_code_old" id="taxpayers_code_old" value="<?php echo empty($response['invoice_data']['taxpayers_code'])?'':$response['invoice_data']['taxpayers_code']; ?>">
        
        <input type="hidden" name="registered_addr_old" id="registered_addr_old" value="<?php echo empty($response['invoice_data']['registered_addr'])?'':$response['invoice_data']['registered_addr'];?>">
        <input type="hidden" name="phone_old" id="phone_old" value="<?php echo empty($response['invoice_data']['phone'])?'':$response['invoice_data']['phone']; ?>">
        <input type="hidden" name="bank_old" id="bank_old" value="<?php echo empty($response['invoice_data']['bank'])?'':$response['invoice_data']['bank']; ?>">
        <input type="hidden" name="bank_account_old" id="bank_account_old" value="<?php echo empty($response['invoice_data']['bank_account'])?'':$response['invoice_data']['bank_account']; ?>">
        <input type="hidden" name="invoice_title_type_old" id="invoice_title_type_old" value="<?php echo isset($response['invoice_data']['is_company'])?$response['invoice_data']['is_company']:''; ?>">
        <input type="hidden" name="receiver_address_old" id="receiver_address_old" value="<?php echo empty($response['invoice_data']['receiver_address'])?$response['record']['decrypt_address']:$response['invoice_data']['receiver_address'];?>">
        <input type="hidden" name="receiver_email_old" id="receiver_email_old" value="<?php echo empty($response['invoice_data']['receiver_email'])?$response['record']['receiver_email']:$response['invoice_data']['receiver_email'];?>">
        <input type="hidden" name="invoice_money_old" id="invoice_money_old" value="<?php echo $response['record']['invoice_money']; ?>">
    </div>
    <div style="clear:both;"></div>
</form>
<div class="control-group">
    <button class="button button-small" id="btn_save_invoice_info" <?php if($response['edit_type'] == 'not_modify') {?> disabled="disabled" <?php }?>><i class="icon-ok"></i>保存</button>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    $(document).ready(function () {

        var invoice_title = $("#invoice_title_old").val();
        var invoice_content = $("#invoice_content_old").val();
        var invoice_number = $("#invoice_number_old").val();
        var taxpayers_code_old = $("#taxpayers_code_old").val();
        var invoice_status = $("#sort").find(".active").attr("id"); 
        var invoice_type_old = $("#invoice_type_old").val();
        var registered_addr_old = $("#registered_addr_old").val();
        var phone_old = $("#phone_old").val();
        var bank_old = $("#bank_old").val();
        var bank_account_old = $("#bank_account_old").val();
        var receiver_address_old = $("#receiver_address_old").val();
        var receiver_email_old = $("#receiver_email_old").val();
        var invoice_title_type_old = $("#invoice_title_type_old").val();
        var invoice_money_old = $("#invoice_money_old").val();//开票金额
       // var label = 1;//省市区联动id标记
        var fptt = $('#title_type input[name="invoice_title_type"]:checked ').val();
        if(fptt == 1){
            $("#fptt").html("<s>*</s>发票抬头：");
        }else{
            $("#fptt").html("发票抬头：");
        }
        $("#btn_save_invoice_info").on("click", function () {
            var is_kp = $("#sort").find(".active").attr("id"); 
            var params = {"data": {}, "app_fmt": 'json'};
            params.data["sell_record_code"] = $("#sell_record_code").val();//单据编号
            //if(is_kp == 1){//开票
            var invoice_type = $("#invoice_type").find('.active').attr("id");
            //获取单选框的值
            var title_type = $('#title_type input[name="invoice_title_type"]:checked ').val();
            params.data["invoice_title"] = $("#edit_invoice_info_form #inv_invoice_title").val();
               if(title_type==1 && is_kp == 1){
                     params.data["taxpayers_code"] = $("#edit_invoice_info_form #inv_taxpayers_code").val();
                                   
                    if(params.data['taxpayers_code'] == '') {
                         BUI.Message.Alert('企业税号不能为空', 'error');
                        return;
                     }
                      //发票企业税号
                    if(taxpayers_code_old != params.data["taxpayers_code"]) {
                             params.data["invoice_taxpayers_diff"] = 1;
                         }
                         if(params.data["invoice_title"] == ''){
                             BUI.Message.Alert('企业发票的发票抬头不能为空', 'error');
                            return;
                         }
                }
                if(is_kp == 1){//开票
                    params.data["invoice_money"] = $("#edit_invoice_info_form #invoice_money").val();//开票金额
                    if(params.data["invoice_money"] == ''){
                        BUI.Message.Alert('开票金额不能为空', 'error');
                        return;
                    } 
                    if(params.data["invoice_money"] <= 0){
                         BUI.Message.Alert('开票金额必须大于0', 'error');
                         return;
                    }
                    var reg = /^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/;
                    if(!reg.test(params.data["invoice_money"])){
                        BUI.Message.Alert('开票金额必须为数字', 'error');
                        return;
                    }
                    var preg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/  //验证小数点两位的数字
                    if(!preg.test(params.data["invoice_money"])){
                        BUI.Message.Alert('开票金额不能以0开头,并且小数点后不能超过两位', 'error');
                        return;
                    }
                     //开票金额
                    if(invoice_money_old != params.data["invoice_money"]) {
                        params.data["invoice_money_diff"] = 1;
                    }
                }
               
                    //寄送地址
                   params.data["receiver_address"] = $("#edit_invoice_info_form #inv_receiver_address").val();
                   if(receiver_address_old != params.data["receiver_address"]) {
                         params.data["receiver_address_diff"] = 1;
                   }
                  
                          params.data["registered_addr"] = $("#edit_invoice_info_form #inv_registered_addr").val();
                        
                         params.data["phone"] = $("#edit_invoice_info_form #inv_phone").val();
                         
                         params.data["bank"] = $("#edit_invoice_info_form #inv_bank").val();
                         
                         params.data["bank_account"] = $("#edit_invoice_info_form #inv_bank_account").val();
                         

                         //注册地址
                         if(registered_addr_old != params.data["registered_addr"]) {
                             params.data["registered_addr_diff"] = 1;
                         }
                         //注册电话
                         if(phone_old != params.data["phone"]) {
                             params.data["phone_diff"] = 1;
                         }
                         //开户银行
                         if(bank_old != params.data["bank"]) {
                             params.data["bank_diff"] = 1;
                         }
                         //银行账号
                          if(bank_account_old != params.data["bank_account"]) {
                             params.data["bank_account_diff"] = 1;
                         }
                
                   
           
                //电子
                //邮箱地址
                params.data["receiver_email"] = $("#edit_invoice_info_form #inv_receiver_email").val();
                    if(receiver_email_old != params.data["receiver_email"]) {
                    params.data["receiver_email_diff"] = 1;
                 }
            
           
              //  invoice_title_type
            params.data['is_company'] = title_type;
            params.data['invoice_type'] = invoice_type;

            params.data["invoice_content"] = $("#edit_invoice_info_form #inv_invoice_content").val();
            params.data["invoice_number"] = $("#edit_invoice_info_form #inv_invoice_number").val();
            params.data["invoice_status"] = $("#sort").find(".active").attr("id");
            params.data["title_type"] = title_type;
           // params.data["invoice_money"] = $("#edit_invoice_info_form #invoice_money").val();//开票金额
            //企业个人
             if (invoice_title_type_old != params.data["is_company"]) {
                params.data["invoice_title_type_diff"] = 1;
            }
            
            //发票抬头
            if (invoice_title != params.data["invoice_title"]) {
                params.data["invoice_title_diff"] = 1;
            }
            //发票内容
            if (invoice_content != params.data["invoice_content"]) {
                params.data["invoice_content_diff"] = 1;
            }
            //发票号
            if (invoice_number != params.data["invoice_number"]) {
                params.data["invoice_number_diff"] = 1;
            }
            //是否开票
            if (invoice_status != params.data["invoice_status"]) {
                params.data["invoice_status_diff"] = 1;
            }
            //纸质电子
            if(invoice_type_old != params.data["invoice_type"]) {
                params.data["invoice_type_diff"] = 1;
            }
           
            params.data["invoice_money_old"] = invoice_money_old;//开票金额
            params.data["invoice_title_old"] = invoice_title;
            params.data["invoice_content_old"] = invoice_content;
            params.data["invoice_number_old"] = invoice_number;
            params.data["taxpayers_code_old"] = taxpayers_code_old;
            params.data["invoice_type_old"] = invoice_type_old;
            params.data["registered_addr_old"] = registered_addr_old;
            params.data["phone_old"] = phone_old;
            params.data["bank_old"] = bank_old;
            params.data["bank_account_old"] = bank_account_old;
            params.data["receiver_address_old"] = receiver_address_old;
             params.data["receiver_email_old"] = receiver_email_old;
             //params.data["invoice_title_type_old"] = invoice_title_type_old;
//        }else{
//            params.data["invoice_status"] = $("#sort").find(".active").attr("id"); 
//            //是否开票
//            if (invoice_status != params.data["invoice_status"]) {
//                params.data["invoice_status_diff"] = 1;
//            }
//        }  
             
            var ajax_url = '?app_act=oms/sell_record/update_invoice_info';
            $.post(ajax_url, params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Show({msg: '修改成功', icon: 'success', buttons: [], autoHide: true});
                    component("money,detail,action,base_order_info,goods_detail,shipping_info,invoice_info", "view");
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, 'json');
        })


        if('<?php echo $response['record']['invoice_type']; ?>' == 'vat_invoice') {
            $('#vat_invoice').css('color', 'rgb(21, 149, 202)');
            $('#vat_invoice').trigger("click");
        } else {
            $('#pt_invoice').css('color', 'rgb(21, 149, 202)');
        }
    });
    function sort_invoice(_this) {
        $(".sort_btn").css({"color": "#666"});
        $(".sort_btn").removeClass("active");
        $(_this).css({"color": "#1695ca"});
        $(_this).addClass("active");
          var inv_all = $(_this).attr("id");
        if(inv_all==1){
            $('#inv_all_detail').show();
        }else{
           $('#inv_all_detail').hide();
        }
        //tableStore.load();
    }

    function invoice(_this) {
        $(".invoice_btn").css({"color": "#666"});
        $(".invoice_btn").removeClass("active");
        $(_this).css({"color": "#1695ca"});
        $(_this).addClass("active");
        radio(_this);
        var inv_ty = $("#invoice_type").find('.active').attr("id");
        if(inv_ty=='vat_invoice'){
            $('#jsdz').show();
            $('#yxdz').hide();
        }else{
            $('#jsdz').hide();
            $('#yxdz').show();
        }
        //tableStore.load();
    }
    //企业和个人
    function radio(_this){
        var inv_ty = $("#invoice_type").find('.active').attr("id");
        var check_ty = $('#title_type input[name="invoice_title_type"]:checked ').val();
        if(inv_ty=='vat_invoice'){
            if(check_ty==1){
                $('#qiyeshuihao').show();
                $('#vat_invoice_div').show()
            }else{
                $('#qiyeshuihao').hide();
                $('#vat_invoice_div').hide();
            }
        }else{
            if(check_ty==1){
                $('#qiyeshuihao').show();
                $('#vat_invoice_div').hide();
            }else{
                $('#qiyeshuihao').hide();
                $('#vat_invoice_div').hide();
            }
        }
        if(check_ty == 1){
            $("#fptt").html("<s>*</s>发票抬头：");
        }else{
            $("#fptt").html("发票抬头：");
        }
        //tableStore.load();
    }
</script>
