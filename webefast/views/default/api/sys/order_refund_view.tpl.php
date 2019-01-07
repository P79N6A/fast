<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
</style>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">退单信息</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <td style="display: none"><input type="hidden" name="refund_id" value="<?php echo $response['refund']['refund_id']; ?>"></td>
                <td width="10%" align="right">退单编号：</td>
                <td width="20%" id="tid"><?php echo $response['refund']['refund_id']; ?></td>
                <td width="10%" align="right">申请时间：</td>
                <td width="15%"><?php echo $response['refund']['order_first_insert_time'] ?></td>
                <td width="10%" align="right">交易号：</td>
                <td width="20%"><?php echo $response['refund']['tid']; ?></td>
            </tr>
            <tr>
                <td align="right">退单类型：</td>
                <td><?php echo $response['refund']['refund_type']; ?></td>
                <td align="right">退款金额：</td>
                <td><?php echo round($response['refund']['refund_fee'], 2); ?></td>
                <td align="right">买家昵称：</td>
                <td><?php echo $response['refund']['buyer_nick']; ?></td>
            </tr>
            <tr>
                <td align="right">退货原因：</td>
                <td><?php echo $response['refund']['refund_reason']; ?></td>
                <td align="right">物流公司：</td>
                <td><?php echo $response['refund']['refund_express_name']; ?></td>
                <td align="right">退货运单号：</td>
                <td><?php echo $response['refund']['refund_express_no']; ?></td>
            </tr>
            <tr>
                <td align="right">退款说明：</td>
                <td colspan="5"><?php echo $response['refund']['refund_desc']; ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">商品明细</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th width="15%">商品编码</th>
                <th width="30%">商品名称</th>
                <th width="15%">商品属性</th>
                <th width="20%">商品条形码</th>
                <th width="5%">数量</th>
                <th width="10%">金额</th>
            </tr>
            <?php foreach ($response['refund']['detail'] as $detail) { ?>
                <tr>
                    <td><?php echo $detail['goods_code']; ?></td>
                    <td><?php echo $detail['title']; ?></td>
                    <td><?php echo isset($detail['sku_properties']) ? $detail['sku_properties'] : ''; ?></td>
                    <td class="barcode" id="<?php echo $detail['detail_id']; ?>"><?php echo $detail['goods_barcode']; ?></td>
                    <td><?php echo $detail['num']; ?></td>
                    <td><?php echo $detail['refund_price']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
<div class="clearfix" style="padding: 0 0 15px 0; text-align: center;">
    <?php if ($response['refund']['status'] == 1 && $response['refund']['is_change'] != 1 && !empty($response['refund']['detail'])): ?>
        <input type="button" class="button button-primary" id="btn_edit" value="修改商品">
        <input type="button" class="button button-primary" id="btn_save" style="display:none;" value = "保存">
        <input type="button" class="button button-primary" id="btn_cancel" style="display:none;" value = "取消">
    <?php endif; ?>
    <button class="button button-primary" id="btn_close">关闭</button>
</div>
<?php echo load_js('comm_util.js') ?>
<script>
    var refund_id = "<?php echo $response['refund']['refund_id']; ?>";
    var from = "<?php echo $response['from']; ?>";
    //编辑
    $("#btn_edit").on('click', function () {
        $("#panel_baseinfo .barcode").each(function (index) {
            var value = $(this).text();
            var name = $(this).attr("id");
            $(this).html("<input type='text' name='" + name + "' style='width:95%' value_old='" + value + "' value='" + value + "'>");

        });
        $("#btn_edit").hide();
        $("#btn_save").show();
        $("#btn_cancel").show();
    });

    //保存
    $("#btn_save").on('click', function () {
        var params = [];
        $("#panel_baseinfo .barcode").each(function (index) {
            var detail_id = $(this).attr("id");
            var barcode_new = $(this).find('input').val();
            var barcode_old = $(this).find('input').attr('value_old');
            if (barcode_new == barcode_old) {
                return true;
            }
            var param = {};
            param.refund_id = refund_id;
            param.detail_id = detail_id;
            param.goods_barcode = barcode_new;
            params.push(param);
        });

        if (params.length < 1) {
            BUI.Message.Alert('未更改商品信息', 'warning');
            return;
        }

        $.post('<?php echo get_app_url('api/sys/order_refund/edit'); ?>', {data: params}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Alert(ret.message, 'success');
                cancel_edit();
                $("#btn_edit").show();
                $("#btn_save").hide();
                $("#btn_cancel").hide();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, "json");
    });

    //取消
    $("#btn_cancel").on('click', function () {
        cancel_edit();
        $("#btn_edit").show();
        $("#btn_save").hide();
        $("#btn_cancel").hide();
    });

    function cancel_edit() {
        $("#panel_baseinfo .barcode").each(function (index) {
            var value = $(this).find('input').val();
            $(this).text(value);
        });
    }

    //关闭
    $("#btn_close").click(function () {
        if (from == 'pt_return') {
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        } else {
            ui_closeTabPage();
        }
    });
</script>