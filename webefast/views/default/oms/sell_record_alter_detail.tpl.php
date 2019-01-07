<style>
.sear_ico{
        right: 320px;
        top: 38px;
    }
#new_barcode_select_img {
    cursor: pointer;
    height: 18px;
    left: 310px;
    top: 72px;
    position: absolute;
    width: 18px
}
</style>

<table cellspacing="0"  style="width:600px;margin-left:15px"  class="table table-bordered">
    <tr>
        <td  colspan="2"  style="color:red;">所需要改商品的订单数: &nbsp;&nbsp;&nbsp;&nbsp; <?php echo count(explode(',', $request['sell_record_code_list']));?></td>
    </tr>
        <tr>
            <td width="20%" align="right"><input type="radio" checked="true"  clientidmode="Static" onchange="change_barcord_status(0)"  class="add_delete_barcode" name="add_delete_barcode"  value="0"/>批量删除商品：</td>
            <td width="80%">
                <input type="text"  name="old_barcode" id="old_barcode" value=""  placeholder="请输入商品条形码"/>
                <img id="old_barcode_select_img" class="sear_ico" src="assets/img/search.png">
                <span style="color:red;">*</span>
            </td>
        </tr>
        <tr>
            <td width="20%" align="right"><input type="radio" class="add_delete_barcode" onchange="change_barcord_status(1)" name="add_delete_barcode"  value="1"/>批量加商品：</td></td>
            <td width="80%">
                <input type="text" name="new_barcode" id='new_barcode'   value="" placeholder="请输入商品条形码"/>
                <img id="new_barcode_select_img" class="sear_ico_a" src="assets/img/search.png">
                <input type="text"  style="width:80px;" name="avg_money" id="avg_money" value=""  placeholder="商品均摊金额"/>
                <input type="text"  style="width:80px;" name="num" id="num" value=""  placeholder="数量 默认为1"/>
            </td>
        </tr>
    <tr>
        <td width="20%" align="right">
            加商品规则
        </td>
        <td width="80%">
            <input type="radio" class="add_goods" name="add_goods"  value="0"/> 订单已存在不添加
            &nbsp;&nbsp;&nbsp;
            <input type="radio" class="add_goods" name="add_goods"  value="1"/> 订单已存在数量累加,金额累加
        </td>
    </tr>
    </table>
<br/>
<!--<div  id="result_grid"  class="panel-body">-->
</div>
<div class="clearfix" id="save_change" style="text-align: center">
    <button class="button button-primary" id="btn_pay_continue">保存继续</button>
    <button class="button button-primary" id="btn_pay_exit">保存退出</button>
</div>
<br/>
<br/>
<div style="text-align: center;color: red;font-size: 13px;" >温馨提示：仅针对未确认的订单，添加/删除商品会影响订单应付款变化！</div>
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
    function change_barcord_status(status){
        if(status == 0){
            $("input[type=radio][name=add_goods][value=0]").attr("checked",false);
        }else{
            $("input[type=radio][name=add_goods][value=0]").attr("checked",true);
        }
    }

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
        if($('input:radio[name="add_delete_barcode"]:checked').val()==1) {
            if ($("#new_barcode").val() == '' || $("#avg_money").val()=='') {
                BUI.Message.Alert('请检查,商品条形码,商品均摊金额不能为空', 'error');
                return false;
            }
            var num;
            if($("#num").val() == ''){
                 num = 1;
            }else{
                 num = $("#num").val();
            }
            var params = {
                "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                "new_barcode": $("#new_barcode").val(),
                "add_status": $('input:radio[name="add_goods"]:checked').val(),
                "avg_money": $("#avg_money").val(),
                "num": num
            };
            BUI.Message.Show({
                title: '修改确认',
                msg: "添加条码："+params['new_barcode'] + ",金额:" + params['avg_money'] + ",数量:" + params['num'],
                icon: 'question',
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.post('<?php echo get_app_url('oms/sell_record/alter_add_detail'); ?>', params, function (data) {
                                var type = data.status == 1 ? 'success' : 'error';
                                if (data.status == 1) {
                                    BUI.Message.Alert(data.message, function () {
                                        document.getElementById('new_barcode').value='';
                                        document.getElementById('avg_money').value='';
                                        document.getElementById('num').value='';
                                    },type)
                                } else {
                                    BUI.Message.Alert(data.message, function () {
                                        document.getElementById('new_barcode').value='';
                                        document.getElementById('avg_money').value='';
                                        document.getElementById('num').value='';
                                    }, type);
                                }
                            }, "json");
                            this.close();
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }else{
            if ($("#old_barcode").val() == '') {
                BUI.Message.Alert('请检查,商品条形码不能为空', 'error');
                return false;
            }
            var params = {
                "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                "old_barcode": $("#old_barcode").val()
            };
            BUI.Message.Show({
                title: '修改确认',
                msg: "删除条码："+params['old_barcode'],
                icon: 'question',
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.post('<?php echo get_app_url('oms/sell_record/alter_detete_detail'); ?>', params, function (data) {
                                var type = data.status == 1 ? 'success' : 'error';
                                if (data.status == 1) {
                                    BUI.Message.Alert(data.message, function () {
                                        document.getElementById("old_barcode").value='';
                                    },type)
                                } else {
                                    BUI.Message.Alert(data.message, function () {
                                        document.getElementById("old_barcode").value='';
                                    }, type);
                                }
                            }, "json");
                            this.close();
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }

    }
    function td_save_goods_exit() {
        if($('input:radio[name="add_delete_barcode"]:checked').val()==1){
            if ($("#new_barcode").val() == '' || $("#avg_money").val()=='') {
                BUI.Message.Alert('请检查,商品条形码,商品均摊金额不能为空', 'error');
                return false;
            }
            var num;
            if($("#num").val() == ''){
                num = 1;
            }else{
                num = $("#num").val();
            }
            var params = {
                "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                "new_barcode": $("#new_barcode").val(),
                "add_status": $('input:radio[name="add_goods"]:checked').val(),
                "avg_money": $("#avg_money").val(),
                "num": num
            };
            BUI.Message.Show({
                title: '修改确认',
                msg: "添加条码："+params['new_barcode'] + ",金额:" + params['avg_money'] + ",数量:" + params['num'],
                icon: 'question',
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.post('<?php echo get_app_url('oms/sell_record/alter_add_detail'); ?>', params, function (data) {
                                var type = data.status == 1 ? 'success' : 'error';
                                if (data.status == 1) {
                                    BUI.Message.Alert(data.message, function () {ui_closePopWindow(<?php echo $request['ES_frmId']?>);},type)
                                } else {
                                    BUI.Message.Alert(data.message, function () {}, type);
                                }
                            }, "json");
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }
                ]

            });
        }else{
            if ($("#old_barcode").val() == '') {
                BUI.Message.Alert('请检查,商品条形码不能为空', 'error');
                return false;
            }
            var params = {
                "sell_record_code_list": <?php echo json_encode(explode(',', $request['sell_record_code_list']))?>,
                "old_barcode": $("#old_barcode").val()
            };
            BUI.Message.Show({
                title: '修改确认',
                msg: "删除条码："+params['old_barcode'],
                icon: 'question',
                buttons: [
                    {
                        text: '确认',
                        elCls: 'button button-primary',
                        handler: function () {
                            $.post('<?php echo get_app_url('oms/sell_record/alter_detete_detail'); ?>', params, function (data){
                                var type = data.status == 1 ? 'success' : 'error';
                                if (data.status == 1) {
                                    BUI.Message.Alert(data.message, function () {ui_closePopWindow(<?php echo $request['ES_frmId']?>);},type)
                                } else {
                                    BUI.Message.Alert(data.message, function () {}, type);
                                }
                            }, "json");
                        }
                    },
                    {
                        text: '取消',
                        elCls: 'button button-primary',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }

    }

</script>