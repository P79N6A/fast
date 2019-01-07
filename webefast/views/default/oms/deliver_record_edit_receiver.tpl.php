<?php echo load_js('comm_util.js')?>
<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="10%" align="right">收货人：</td>
        <td width="40%"><input id="receiver_name" name="receiver_name" type="text" value="<?php echo $response['record']['receiver_name'];?>"></td>
        <td width="10%" align="right">手机：</td>
        <td width="40%"><input id="receiver_mobile" name="receiver_mobile" type="text" value="<?php echo $response['record']['receiver_mobile'];?>"></td>
    </tr>
    <tr>
        <td width="10%" align="right">固定电话：</td>
        <td width="40%"><input id="receiver_phone" name="receiver_phone" type="text" value="<?php echo $response['record']['receiver_phone'];?>"></td>
        <td width="10%" align="right">邮编：</td>
        <td width="40%"><input id="receiver_zip_code" name="receiver_zip_code" type="text" value="<?php echo $response['record']['receiver_zip_code'];?>"></td>
    </tr>
    <tr>
        <td width="10%" align="right">详细地址：</td>
        <td width="90%" colspan="3">
            <select name="country" id="country">
                <option value ="">国家</option>
                <?php $list = oms_tb_all('base_area', array('type'=>'1')); foreach($list as $k=>$v){ ?>
                    <option value="<?php echo $v['id']?>"><?php echo $v['name']?></option>
                <?php } ?>
            </select>
            <select name="province" id="province">
                <option>省</option>
            </select>
            <select name="city" id="city">
                <option>市</option>
            </select>
            <select name="district" id="district">
                <option>区</option>
            </select>
            <select name="street" id="street">
                <option>街道</option>
            </select>
            <input id="receiver_addr" name="receiver_addr" type="text" value="<?php echo $response['record']['receiver_addr'];?>">
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="btn_pay_ok">确定</button>
</div>

<script>
    $(document).ready(function(){
        $("#btn_pay_ok").click(function(){
            var params = {
                "deliver_record_id": <?php echo $request['deliver_record_id']?>,
                "receiver_name": $("#receiver_name").val(),
                "receiver_mobile": $("#receiver_mobile").val(),
                "receiver_country": $("#country").val(),
                "receiver_province": $("#province").val(),
                "receiver_city": $("#city").val(),
                "receiver_district": $("#district").val(),
                "receiver_street": $("#street").val(),
                "receiver_addr": $("#receiver_addr").val(),
                "receiver_phone": $("#receiver_phone").val(),
                "receiver_zip_code": $("#receiver_zip_code").val()
            };

            $.post("?app_act=oms/deliver_record/edit_receiver_action", params, function(data){
                var info = 'info';
                if(data.status<0){
                    info = 'error';
                }
                BUI.Message.Alert(data.message,function(){
                        ui_closePopWindow("<?php echo $request['ES_frmId']?>");
                } ,info);
            
            }, "json");
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        var url = '<?php echo get_app_url('base/store/get_area');?>';
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });

        $("#country").val("<?php echo $response['record']['receiver_country'];?>");
        areaChange($("#country").val(),0,url,function(){
            $("#province").val("<?php echo $response['record']['receiver_province'];?>");
            areaChange($("#province").val(),1,url,function(){
                $('#city').val("<?php echo $response['record']['receiver_city'];?>");
                areaChange($("#city").val(),2,url,function(){
                    $('#district').val("<?php echo $response['record']['receiver_district'];?>");
                    areaChange($("#district").val(),3,url,function(){
                        $('#street').val("<?php echo $response['record']['receiver_street'];?>");
                    });
                });
            });
        })

    })
</script>