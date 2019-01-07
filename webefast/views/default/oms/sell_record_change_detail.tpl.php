<style>
.sear_ico{
        right: 325px;
        top: 38px;
    }
#new_barcode_select_img {
    cursor: pointer;
    height: 18px;
    left: 375px;
    top: 72px;
    position: absolute;
    width: 18px
}
</style>

<table cellspacing="0"  style="width:600px;margin-left:15px"  class="table table-bordered">
    <tr>
        <td  colspan="2"  style="color:red;">所需要换商品的订单数: &nbsp;&nbsp;&nbsp;&nbsp; <?php echo count(explode(',', $request['sell_record_code_list']));?></td>
    </tr>
        <tr>
            <td width="30%" align="right">输入更换商品：</td>
            <td width="70%">
                <input type="text"  name="old_barcode" id="old_barcode" value=""  placeholder="请输入商品条形码"/>
                <img id="old_barcode_select_img" class="sear_ico" src="assets/img/search.png">
            </td>
        </tr>
        <tr>
            <td width="30%" align="right">选择更换后商品：</td>
            <td width="70%">
                <input type="text" name="new_barcode" id='new_barcode'   value="" placeholder="请输入商品条形码"/>
                <img id="new_barcode_select_img" class="sear_ico_a" src="assets/img/search.png">
            </td>
        </tr>
    </table>
<br/>
<div style="text-align: center">
    更换规则：
    <input type="radio" class="common_barcode" name="common_barcode"  value="1"/>同步修改商品套餐子商品(即套餐中包含此商品也会被修改)
</div>
<!--<div  id="result_grid"  class="panel-body">-->

</div>
<div class="clearfix" id="save_change" style="text-align: center">
    <button class="button button-primary" id="btn_pay_continue">保存继续</button>
    <button class="button button-primary" id="btn_pay_exit">保存退出</button>
</div>
</br>
</br>
<div style="text-align: center;color: red;font-size: 13px;" >温馨提示：改款仅针对未确认的订单，且不涉及金额变化！</div>
<?php echo load_js('comm_util.js') ?>
<script>
    $(document).ready(function(){
        $("#btn_pay_continue").click(function(){
            td_save_goods();
        })
        $("#btn_pay_exit").click(function(){
            td_save_goods_exit();
        })

    })
    var selectPopWindowp_code = {
        dialog: null,
        callback: function(value) {
            var goods_code = value[0]['goods_code'];
            var barcode = value[0]['barcode'];
            $('#goods_code').val(goods_code);
            $('#new_barcode').val(barcode);
            if (selectPopWindowp_code.dialog != null) {
                selectPopWindowp_code.dialog.close();
            }
        }
    };
    var selectPopWindowshelf_code = {
        dialog: null,
        callback: function(value) {
            var goods_code = value[0]['goods_code'];
            var barcode = value[0]['barcode'];
            $('#goods_code').val(goods_code);
            $('#old_barcode').val(barcode);
            if (selectPopWindowshelf_code.dialog != null) {
                selectPopWindowshelf_code.dialog.close();
            }
        }
    };

    $('#new_barcode_select_img').click(function() {
        selectPopWindowp_code.dialog = new ESUI.PopSelectWindow('?app_act=prm/goods_barcode/serach', 'selectPopWindowp_code.callback', {title: '选择商品条码', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
    });
    $('#old_barcode_select_img').click(function() {
        selectPopWindowshelf_code.dialog = new ESUI.PopSelectWindow('?app_act=prm/goods_barcode/serach', 'selectPopWindowshelf_code.callback', {title: '选择商品条码', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
    });
    //保存商品
    function td_save_goods() {
        if ($("#new_barcode").val() == '' || $("#old_barcode").val()=='') {
            BUI.Message.Alert('请检查,商品条形码不能为空', 'error');
            return false;
        }
        var params = {
            "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
            "old_barcode": $("#old_barcode").val(),
            "new_barcode": $("#new_barcode").val()
        };

        BUI.Message.Show({
            title: '更换确认',
            msg: "由条码："+params['old_barcode']+"更换成条码："+params['new_barcode']+"，请确认操作！",
            icon: 'question',
            buttons: [
                {
                    text: '确认',
                    elCls: 'button button-primary',
                    handler: function () {
                        var update_status;
                        if ($('input:radio[name="common_barcode"]:checked').val()) {
                            update_status = 1;
                        } else {
                            update_status = 0;
                        }
                        var params_new = {
                            "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                            "old_barcode": $("#old_barcode").val(),
                            "new_barcode": $("#new_barcode").val(),
                            "update_status": update_status
                        };
                        $.post('<?php echo get_app_url('oms/sell_record/sure_change_detail'); ?>', params_new, function (data) {
                            var type = data.status == 1 ? 'success' : 'error';
                            if (data.status == 1) {
                                BUI.Message.Alert(data.message, function () {
                                    document.getElementById("old_barcode").value = '';
                                    document.getElementById("new_barcode").value = '';
                                }, type);
                            } else if (data.status == -1) {
                                BUI.Message.Alert(data.message, function () {
                                    document.getElementById("old_barcode").value = '';
                                }, type);
                            } else if (data.status == -2) {
                                BUI.Message.Alert(data.message, function () {
                                    document.getElementById("new_barcode").value = '';
                                }, type);
                            } else if (data.status == -3) {
                                BUI.Message.Alert(data.message, function () {
                                    document.getElementById("old_barcode").value = '';
                                    document.getElementById("new_barcode").value = '';
                                }, type);
                            }
                        }, "json");
                        this.close();
                    }
                },
                {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }
    function td_save_goods_exit() {
        if ($("#new_barcode").val() == '' || $("#old_barcode").val()=='') {
            BUI.Message.Alert('请检查,商品条形码不能为空', 'error');
            return false;
        }
        var params = {
            "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
            "old_barcode": $("#old_barcode").val(),
            "new_barcode": $("#new_barcode").val()
        };
        //console.log($request);
        BUI.Message.Show({
            title: '更换确认',
            msg: "由条码："+params['old_barcode']+"更换成条码："+params['new_barcode']+"，请确认操作！",
            icon: 'question',
            buttons: [
                {
                    text: '确认',
                    elCls: 'button button-primary',
                    handler: function () {
                        var update_status;
                        if($('input:radio[name="common_barcode"]:checked').val()){
                            update_status =1;
                        }else{
                            update_status =0;
                        }
                        var params_new = {
                            "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                            "old_barcode": $("#old_barcode").val(),
                            "new_barcode": $("#new_barcode").val(),
                            "update_status": update_status
                        };
                        $.post('<?php echo get_app_url('oms/sell_record/sure_change_detail'); ?>', params_new, function (data) {
                            var type = data.status == 1 ? 'success' : 'error';
                            if (data.status == 1) {
                                BUI.Message.Alert(data.message, function () {ui_closePopWindow(<?php echo $request['ES_frmId']?>);},type);
                                //window.location.reload();
                            } else {
                                BUI.Message.Alert(data.message, function () {ui_closePopWindow(<?php echo $request['ES_frmId']?>); }, type);
                            }
                        }, "json");
                        this.close();
                    }
                },
                {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }
    function addgoods_barcode(obj, type,barcode_type) {
        var data = top.skuSelectorStore.getResult();
        var old_barcode ;
        BUI.each(data, function (value, key) {
            if (top.$("input:radio:checked[name='change_sku']").val() != '' && top.$("input:radio:checked[name='change_sku']").val() != undefined ) {
                old_barcode = top.$("input:radio:checked[name='change_sku']").val();
            }
        });
        var _thisDialog = obj;
        if(type == 0){
            _thisDialog.close();
            if(barcode_type==0){
                document.getElementById("old_barcode").value=old_barcode;
            }else{
                document.getElementById("new_barcode").value=old_barcode;
            }

        }
    }

</script>