<?php
$arry = array('goods_spec1', 'goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arry);
$goods_spec1_rename = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$goods_spec2_rename = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
?>
<table cellspacing="0" class="table table-bordered">
    <thead>
        <tr>
            <th>交易号</th>
            <th>商品名称</th>
            <th>商品编码</th>
            <th><?php echo $response['data']['goods_spec1_rename']; ?></th>
            <th><?php echo $response['data']['goods_spec2_rename']; ?></th>
            <th>商品条形码</th>
            <th>原单商品数量</th>
            <th>申请退货数量</th>
            <th>实际退货数量</th>    
            <th>吊牌价</th>        
            <th>实际应退款</th>
            <?php if (in_array($response['data']['is_fenxiao'], array(1, 2))) { ?>
                <th>结算单价</th>
                <th>结算金额</th>
            <?php } ?>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($response['data']['detail_list'] as $key => $detail) : ?>
            <tr class="detail_<?php echo $detail['sell_return_detail_id']; ?>">
                <td name="deal_code">
                    <span><?php echo $detail['deal_code']; ?></span>
                    <input class="deal_code" style="width: 120px;display: none" type="text" value="<?php echo $detail['deal_code']; ?>">
                </td>
                <td><?php echo $detail['goods_name']; ?></td>
                <td><?php echo $detail['goods_code']; ?></td>
                <td><?php echo $detail['spec1_name'] ?></td>
                <td><?php echo $detail['spec2_name'] ?></td>
                <td name = "barcode"><?php echo $detail['barcode']; ?></td>
                <td name = "record_num"><?php echo $detail['relation_num']; ?></td>
                <td name="num">
                    <div><?php echo $detail['note_num']; ?></div>
                    <input class="new_num" onblur="change_avg_money('<?php echo $detail['note_num']; ?>', '<?php echo sprintf("%.2f", $detail['avg_money']); ?>', this)" style="width:30px; text-align:center; display:none;" type="text" value="<?php echo $detail['note_num']; ?>">
                </td>
                <td name = "recv_num">
                    <span><?php echo $detail['recv_num']; ?></span>
                    <input class="recv_num" style="width:50px; display:none" type="text" value="<?php echo $detail['recv_num']; ?>">
                </td>
                <td><?php echo sprintf("%.2f", $detail['goods_price']); ?></td>
                <td name="avg_money">
                    <span><?php echo sprintf("%.2f", $detail['avg_money']); ?></span>
                    <input class="avg_money" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.2f", $detail['avg_money']); ?>">
                </td>        
                <?php if (in_array($response['data']['is_fenxiao'], array(1, 2))) : ?>
                    <td name="trade_price">
                        <span><?php echo sprintf("%.3f", $detail['trade_price']); ?></span>
                        <input class="trade_price" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.3f", $detail['trade_price']); ?>">
                    </td>
                    <td name="fx_amount">
                        <span><?php echo sprintf("%.3f", $detail['fx_amount']); ?></span>
                        <input class="fx_amount" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.3f", $detail['fx_amount']); ?>">
                    </td> 
                <?php endif; ?>
                <td style="width: 13%">
                    <button class="button button-small change" title="改款" onclick="detail_change('<?php echo $detail['goods_code']; ?>', '<?php echo $detail['sell_return_detail_id']; ?>', '<?php echo $detail['sku']; ?>', '<?php echo $detail['deal_code']; ?>', '<?php echo $detail['avg_money']; ?>', '<?php echo isset($detail['is_gift']) ? $detail['is_gift'] : 0; ?>', '<?php echo $detail['note_num']; ?>')"><i class="icon-pencil"></i></button>
                    <button class="button button-small edit" title="编辑" onclick="detail_edit(<?php echo $detail['sell_return_detail_id']; ?>)"><i class="icon-edit"></i></button>
                    <button class="button button-small delete" title="删除" onclick="detail_delete(<?php echo $detail['sell_return_detail_id']; ?>)"><i class="icon-trash"></i></button>
                    <button class="button button-small save hide" title="保存" onclick="detail_save(<?php echo $detail['sell_return_detail_id']; ?>)"><i class="icon-ok"></i></button>
                    <button class="button button-small cancel hide" title="取消" onclick="detail_cancel(<?php echo $detail['sell_return_detail_id']; ?>)"><i class="icon-ban-circle"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    var sell_return_code = '<?php echo $response['data']['sell_return_code']; ?>';
    var selectPopWindowshelf_return_goods = {
        callback: function (value, id, code, name) {
            if (typeof value.sku == "undefined") {
                alert("请选择商品");
                return;
            }
            var num = value.num;
            var type = "^[0-9]*[1-9][0-9]*$";
            var re = new RegExp(type);
            if (num.match(re) == null) {
                alert("请输入大于零的整数!");
                return;
            }
            $.ajax({
                type: "GET",
                url: "?app_act=oms/sell_return/opt_return_detail",
                data: {
                    sku: value.sku,
                    num: value.num,
                    sell_return_detail_id: value.sell_return_detail_id,
                    sell_return_code: sell_return_code,
                    deal_code: value.deal_code,
                    avg_money: value.avg_money,
                    is_gift: value.is_gift,
                    app_fmt: 'json'
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        component("return_money", "view");
                        component("return_goods", "view");
                        component("action", "view");
                    } else {
                        alert(data.message);
                    }
                }
            });
            if (selectPopWindowshelf_return_goods.dialog != null) {
                selectPopWindowshelf_return_goods.dialog.close();
            }
        }
    };
    function detail_change(goods_code, sell_return_detail_id, sku, deal_code, avg_money, is_gift, num) {
        selectPopWindowshelf_return_goods.dialog = new ESUI1.PopSelectWindow('?app_act=common/select1/return_goods&goods_code=' + goods_code + '&sell_return_detail_id=' + sell_return_detail_id + '&sku=' + sku + '&deal_code=' + deal_code + '&avg_money=' + avg_money + '&is_gift=' + is_gift + '&num=' + num, 'selectPopWindowshelf_return_goods.callback', {title: '修改商品规格', width: 900, height: 500, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();

    }
    function change_avg_money(num, avg_money, _this) {
        var new_num = $(_this).val();
        $(_this).parents("tr").find(".avg_money").val(Number((avg_money / num) * new_num).toFixed(2));
    }
</script>
<?php
$lof_status = load_model("sys/SysParamsModel")->get_val_by_code(array("lof_status"));
if ($lof_status['lof_status']):
    ?>
    <div id="show"></div>
    <style>
        .lock_detail{
            background:#fff;
            position:absolute;
            z-index:100;
            padding:10px;
        }
        .lock_detail td,.lock_detail th{
            text-align: center;
            border:1px solid #000;
            padding:5px;
        }
    </style>
    <script>
        function show_dialog(url, title, opt) {
            new ESUI.PopWindow(url, {
                title: title,
                width: opt.w,
                height: opt.h,
                onBeforeClosed: function () {
                    if (typeof opt.callback == 'function')
                        opt.callback();
                }
            }).show();
        }
    </script>
<?php endif; ?>