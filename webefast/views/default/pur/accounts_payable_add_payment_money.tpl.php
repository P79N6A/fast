<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>

<?php echo load_js('comm_util.js') ?>
<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}

    .panel .payment_info{border:1px solid #dddddd; padding:3px;}
    .panel .icon-plus{margin-right: 5px;}

    .prettyprint {
        padding: 15px 50px;
        background-color: #f7f7f9;
        border: 1px solid #e1e1e8;
        margin-top: 10px;
    }
    .txt{margin-left: 10px;}
    .tag{cursor: pointer;}
    .panel .row{margin-bottom: 5px;}
    .panel .input-normal{width: 145px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '添加付款记录',
    'links' => array(),
    'ref_table' => 'table'
));
?>

<div class="panel record_table" id="panel_html">
    <div class="panel record_table" id="panel_html">
        <div class="panel-header clearfix">
            <h3 class="pull-left">单据信息<i class="icon-folder-open toggle"></i></h3>
        </div>
        <div class="panel-body">
            <table id="record_table" class="table panel-head-borded">
                <tbody>
                    <tr>
                        <th>供应商</th>
                        <td id="supplier_name"><?php echo $response['data']['supplier_name']; ?></td>
                        <th>单据编号</th>
                        <td id="record_code_str" colspan="3"><div style="width : 400px;word-wrap:break-word;"><?php echo $request['params']; ?></div></td>
                    </tr>
                    <tr>
                        <th>待付金额</th>
                        <td id="diff_money"><span style="color:red;"><?php echo $response['data']['diff_money']; ?></span></td>
                        <th>已付金额</th>
                        <td id="payment_money"><?php echo $response['data']['payment_money']; ?></td>
                        <th>单据金额</th><td id="record_money"><?php echo $response['data']['record_money']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-header" >
        <h3 class="">收款信息<i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body payment_info">
        <!--action="?app_act=pur/accounts_payable/do_add_payment"-->
        <form id="J_Form" class="form-horizontal"   method="post">
            <input type="hidden" name="custom_code" id="custom_code" value="<?php echo $response['data']['custom_code'] ?>">
            <input type="hidden" name="record_code" id="record_code" value="<?php echo $response['data']['record_code'] ?>">
            <div>
                <div class="panel-header">
                    <span id="offline" class="label label-info toggle tag" style="font-size: 1.05em"><i class="icon-plus"></i>线下支付</span>
                </div>
                <div class="panel-body prettyprint">
                    <div style="width:50%;float: left">
                        <div class="row">
                            <div class="control-group span8">
                                <label class="control-label">付款日期：</label>
                                <div class="controls">
                                    <input type="text" name="pay_time" id="pay_time" class="calendar calendar-time" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="control-group span8">
                                <label class="control-label">付款金额：</label>
                                <div class="controls">
                                    <input name="current_payment_money" id="current_payment_money" type="text" class="input-normal control-text" data-rules="{required:true,regexp:/^([1-9]\d*|0)(\.\d*[1-9])?$/}" data-messages="{regexp:'请输入大于0的数字'}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="control-group span11">
                                <label class="control-label">备注：</label>
                                <div class="controls">
                                    <textarea name="remark" id="remark" data-tip='{"text":"请填写备注"}' class="input-large" type="text"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row actions-bar">
                <div class="form-actions span13 offset3">
                    <button type="button" class="button button-primary" onclick="set_payment_money()">确定</button>
                    <button type="reset" class="button">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var diff_money = <?php echo $response['data']['diff_money']; ?>;
    var supplier_code = '<?php echo $response['data']['supplier_code']; ?>';
    $(function () {
        //面板展开和隐藏
        $('.toggle').click(function () {
            $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
            return false;
        });
        $('.account').hide();

        BUI.use('bui/form', function (Form) {
            var form = new Form.HForm({
                srcNode: '#J_Form',
                submitType: 'ajax',
                defaultChildCfg: {
                    validEvent: 'blur' //移除时进行验证
                },
//                callback: function (ret) {
//                    var _type = ret.status == 1 ? 'success' : 'error';
//                    BUI.Message.Show({
//                        msg: ret.message,
//                        icon: _type,
//                        buttons: [],
//                        autoHide: true
//                    });
//                    if (_type == 'success') {
//                        ui_closeTabPage('<?php // echo $request['ES_frmId'] ?>');
//                    }
//                }
            }).render();
//            form.on('beforesubmit', function () {
//
//            });
        });


        $("#record_code_str").attr('colspan', '3');
    });
    function set_payment_money() {
        var current_payment_money = $('#current_payment_money').val();
        if(diff_money < current_payment_money) {
            BUI.Message.Alert('付款金额超过待付金额','error');
            return false;
        }
        var record_code_str = $("#record_code_str").text();
        new ESUI.PopWindow("?app_act=pur/accounts_payable/set_payment_money&current_payment_money=" + current_payment_money + "&record_code_str=" + record_code_str, {
            title: "设置付款金额",
            width: 700,
            height: 550,
//            buttons:[
//              {
//                text:'确认',
//                id:'money_check',
//                elCls : 'button button-primary',
//                handler : function(){
//        console.log(parent.dd);
////                  this.close();
//                }
//              }
//            ],
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    }
    parent.save_info = function(record_data) {
        var pay_time = $('#pay_time').val();
        var remark = $('#remark').val();
        var url = "?app_act=pur/accounts_payable/save_info";
        $.post(url,{record_data:record_data,pay_time:pay_time,supplier_code:supplier_code,remark:remark},function(ret) {
            console.log(ret.status);
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
            } else {
                BUI.Message.Tip(ret.message,'success');
                parent.load_table();
                ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
            }
        },'json');
    }
    
</script>


