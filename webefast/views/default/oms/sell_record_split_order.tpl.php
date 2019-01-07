<style>
    .lock_num {color:red;}
</style>
<?php
$arry = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arry);
$goods_spec1_rename = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$goods_spec2_rename = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
?>
<?php
render_control('PageHead', 'head1', array('title' => '拆分订单',
));
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '拆分', 'active' => true, 'id' => 'tabs_split'),
        array('title' => '批量拆分', 'active' => false, 'id' => 'tabs_split_all'),
    ),
    'for' => 'TabPage1Contents'
));
?>
<table cellspacing="0" class="table table-bordered" id="goods_info_detail">
    <thead>
        <tr>
            <th>商品名称</th>
            <th>商品编码</th>
            <th style="width:195px;">系统规格</th>
            <th>商品条形码</th>
            <th>数量(实物锁定)</th>
            <th><font color="red" id="split_num">拆分数量</font></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($response['detail_list'] as $key => $detail) { ?>
            <tr class="detail_content">
                <td>
                    <?php echo $detail['goods_name']; ?>
                    <input type="hidden" class="good_oms_sell_code" id="" value="<?php echo $response['record']['sell_record_code']; ?>">
                    <input type="hidden" class="sell_record_detail_id" value="<?php echo $detail['sell_record_detail_id']; ?>">
                </td>
                <td class='goods_code'><?php echo $detail['goods_code']; ?></td>
                <td>
                    <?php echo $goods_spec1_rename . ':'; ?>
                    <?php echo $detail['spec1_name']; ?>
                    <?php echo $goods_spec2_rename . ':' ?>
                    <?php echo $detail['spec2_name']; ?>
                </td>			
                <td><?php echo $detail['barcode']; ?></td>
                <td name="num" class="num">
                    <div><span class="detail_nums"><?php echo $detail['num']; ?></span>(<span <?php if ($detail['num'] > $detail['lock_num']) {
                    echo "class='lock_num'";
                } ?> param1="<?php echo $detail['sell_record_detail_id']; ?>" param2="<?php echo $detail['sku']; ?>" style="text-decoration:underline"><?php echo $detail['lock_num']; ?></span>)</div>
                </td>
                <td name="split_order_num">
                    <input class="split_order_num" data-detail-num="<?php echo $detail['num']; ?>" id="split_order_num_<?php echo $detail['sell_record_detail_id']; ?>" name="split_order_num" style="width: 50px;" type="text" value="0" <?php if ($response['record']['order_status'] >= 3 || ($detail['num'] == 1 && $response['count'] == 1)) { ?>
                               disabled="disabled" 
    <?php } ?>>
                </td>
            </tr>
<?php } ?>
    </tbody>
</table>
<button class="button button-small" id="btn_sure_split_order"><i class="icon-ok"></i>确认拆单</button>
<button class="button button-small" id="btn_cancel_split_order"><i class="icon-ban-circle"></i>取消</button>
<div style="color:red;margin-top: 20px;font-size: 20px;" id="message">说明：此拆分适用于1单多商品，输入商品拆分数量，系统按照输入的数量拆为1单，剩余数量拆为1单。</div>

<script>
    $(document).ready(function () {
        $("#btn_sure_split_order").click(function () {
            $("#btn_sure_split_order").attr('disabled', true);
            var opt = $(".active a:first-child").attr("id");
            var is_num = 0;
            var is_int = 1;
            var params = {sell_record_code: $(".good_oms_sell_code").val(), "app_fmt": 'json', "opt": opt, "data": {}};
            var split_num_count = 0;
            var detail_num_count = 0;
            var r = /^[0-9]*$/;
            $(".sell_record_detail_id").each(function (index, element) {

                var sell_record_detail_id = $(this).val();
                var split_num = $("#split_order_num_" + sell_record_detail_id).val();
                var detail_num = $("#split_order_num_" + sell_record_detail_id).attr("data-detail-num");
                //正整数  
                if (parseInt(split_num) > parseInt(detail_num)) {
                    is_num = 1;
                }
                if (!r.test(split_num)) {
                    is_int = 0;
                }
                split_num_count += parseInt(split_num);
                detail_num_count += parseInt(detail_num);
                params.data[index] = {
                    "sell_record_detail_id": sell_record_detail_id,
                    "split_num": split_num,
                    "detail_num": detail_num
                };
            });

            if (is_int === 0) {
                BUI.Message.Alert('拆分数量必须为整数', 'error');
                return;
            }
            if (is_num === 1) {
                BUI.Message.Alert('拆分数量应该小于或等于商品数量', 'error');
                return;
            }

            if (0 >= split_num_count) {
                BUI.Message.Alert('拆分总数应该为大于0的整数', 'error');
                return;
            }

            $.post("?app_act=oms/sell_record/opt_split_order&app_fmt=json", params, function (data) {
                if (data.status === 1) {
                    $(".split_order_num").attr('disabled', true);
                    $("#btn_sure_split_order").attr('disabled', true);
                    BUI.Message.Alert(data.message, 'info');
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        });
        $('#btn_cancel_split_order').click(function () {
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
        });
        var count = <?php echo $response['count']; ?>;
        $('#tabs_split').click(function () {
            $('#split_num').html('拆分数量');
            $('#message').html('说明：此拆分适用于1单多商品，输入商品拆分数量，系统按照输入的数量拆为1单，剩余数量拆为1单。');
            if (count > 1) {
                $('.split_order_num').attr('disabled', false);
            }
        });
        $('#tabs_split_all').click(function () {
            $('#split_num').html('拆分每单数量');
            $('#message').html('说明：此拆分适用于商品多数量，输入拆分每单数量，系统按照输入的数量计算拆分订单，会产生N个订单。');
            $('#goods_info_detail .detail_content').each(function (index, ele) {
                var new_index = 'a' + index;
                $(this).attr('id', new_index);
                var nums = $('#' + new_index + " .detail_nums").text();
                if (nums == 1) {
                    $('#' + new_index + " .split_order_num").attr('disabled', true);
                }

            });
        });
    });

    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });

</script>