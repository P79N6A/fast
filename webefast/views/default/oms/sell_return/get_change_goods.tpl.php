<?php if (isset($response['data']['change_detail_list']) && !empty($response['data']['change_detail_list'])): ?>
    <table cellspacing="0" class="table table-bordered">
        <thead>
            <tr>
                <th>交易号</th>
                <th>商品名称</th>
                <th>商品编码</th>
                <th><?php echo $response['data']['goods_spec1_rename']; ?></th>
                <th><?php echo $response['data']['goods_spec2_rename']; ?></th>
                <th>商品条形码</th>
                <th>数量</th>            
                <th>吊牌价</th>        
                <th>实际应收款</th>
                <?php if (in_array($response['data']['is_fenxiao'], array(1, 2))) : ?>
                    <th>分销商实际应收款</th>
                <?php endif; ?>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($response['data']['change_detail_list'] as $key => $detail) : ?>
                <tr class="detail_<?php echo $detail['sell_change_detail_id']; ?>">
                    <td name="deal_code">
                        <span><?php echo $detail['deal_code']; ?></span>
                        <input class="deal_code" style="width: 120px;display: none" type="text" value="<?php echo $detail['deal_code']; ?>">
                    </td>
                    <td><?php echo $detail['goods_name']; ?></td>
                    <td><?php echo $detail['goods_code']; ?></td>
                    <td><?php echo $detail['spec1_name'] ?></td>
                    <td><?php echo $detail['spec2_name'] ?></td>
                    <td><?php echo $detail['barcode']; ?></td>
                    <td name="num">
                        <div><?php echo $detail['num']; ?></div>
                        <input class="new_num" onblur="change_avg_money('<?php echo $detail['num']; ?>', '<?php echo sprintf("%.2f", $detail['avg_money']); ?>', this)" style="width:30px; text-align:center; display:none;" type="text" value="<?php echo $detail['num']; ?>">
                    </td>        
                    <td><?php echo sprintf("%.2f", $detail['goods_price']); ?></td>
                    <td name="avg_money">
                        <span><?php echo sprintf("%.2f", $detail['avg_money']); ?></span>
                        <input class="avg_money" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.2f", $detail['avg_money']); ?>">
                    </td>    
                    <?php if (in_array($response['data']['is_fenxiao'], array(1, 2))) : ?>
                        <td name="fx_amount">
                            <span><?php echo sprintf("%.3f", $detail['fx_amount']); ?></span>
                            <input class="fx_amount" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.3f", $detail['fx_amount']); ?>">
                        </td> 
                    <?php endif; ?>         
                    <td style="width: 13%">
                        <button class="button button-small change" title="改款" onclick="goods_change_detail('<?php echo $detail['goods_code']; ?>', '<?php echo $detail['sell_change_detail_id']; ?>', '<?php echo $detail['sku']; ?>', '<?php echo $detail['deal_code']; ?>', '<?php echo $detail['avg_money']; ?>', '<?php echo (isset($detail['is_gift']) ? $detail['is_gift'] : ''); ?>', '<?php echo $detail['num']; ?>')"><i class="icon-pencil"></i></button>
                        <button class="button button-small edit" title="编辑" onclick="chg_detail_edit(<?php echo $detail['sell_change_detail_id']; ?>)"><i class="icon-edit"></i></button>
                        <button class="button button-small delete" title="删除" onclick="chg_detail_delete(<?php echo $detail['sell_change_detail_id']; ?>)"><i class="icon-trash"></i></button>
                        <button class="button button-small save hide" title="保存" onclick="chg_detail_save(<?php echo $detail['sell_change_detail_id']; ?>)"><i class="icon-ok"></i></button>
                        <button class="button button-small cancel hide" title="取消" onclick="chg_detail_cancel(<?php echo $detail['sell_change_detail_id']; ?>)"><i class="icon-ban-circle"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<script type="text/javascript">
    //换货明细编辑
    function chg_detail_edit(id) {
        var item = $("#panel_change_goods table tbody").find(".detail_" + id);
        item.find(".edit").hide();
        item.find(".delete").hide();
        item.find(".save").show();
        item.find(".cancel").show();
        item.find("td[name=num]").find("input").show();
        item.find("td[name=num]").find("div").hide();
        item.find("td[name=avg_money]").find("input").show();
        item.find("td[name=avg_money]").find("span").hide();
        item.find("td[name=fx_amount]").find("input").show();
        item.find("td[name=fx_amount]").find("span").hide();
    }

    //换货明细保存
    function chg_detail_save(id) {
        var item = $("#panel_change_goods table tbody").find(".detail_" + id);
        var params = {};
        params[id] = {
            "sell_return_code": sell_return_code,
            "sell_change_detail_id": id,
            "num": item.find("td[name=num]").find("input").val(),
            "deal_code": item.find("td[name=deal_code]").find("input").val(),
            "avg_money": item.find("td[name=avg_money]").find("input").val(),
            "fx_amount": item.find("td[name=fx_amount]").find("input").val()
        };
        $.post("?app_fmt=json&app_act=oms/sell_return/save_component&type=change_goods&sell_return_code=" + sell_return_code, params, function (data) {
            if (data.status != '1') {
                BUI.Message.Alert(data.message, 'error');
            } else {
                //刷新数据
                component('change_goods', "view");
                component('return_money', "view");
            }
        }, "json")
    }

    //换货明细删除
    function chg_detail_delete(id) {
        var msg = "<?php echo lang('op_delete_confirm'); ?>";
        BUI.Message.Confirm(msg, function () {
            var params = {"sell_return_code": sell_return_code, "sell_change_detail_id": id}
            $.post(
                    "?app_act=oms/sell_return/delete_change_detail_by_id&app_fmt=json",
                    params,
                    function (data) {
                        if (data.status == 1) {
                            component('change_goods', "view");
                            component('return_money', "view");
                            //刷新按钮权限
                            //btn_check();
                        } else {
                            BUI.Message.Alert(data.message, 'error');
                        }
                    },
                    "json"
                    );
        });
    }

    //换货明细取消保存
    function chg_detail_cancel(id) {
        var item = $("#panel_change_goods table tbody").find(".detail_" + id);
        item.find(".edit").show();
        item.find(".delete").show();
        item.find(".save").hide();
        item.find(".cancel").hide();
        item.find("td[name=num]").find("input").hide();
        item.find("td[name=num]").find("div").show();
        item.find("td[name=avg_money]").find("input").hide();
        item.find("td[name=avg_money]").find("span").show();
        item.find("td[name=deal_code]").find("input").hide();
        item.find("td[name=deal_code]").find("span").show();
        item.find("td").eq(8).find("input").attr("disabled", true);
    }
    var selectPopWindowshelf_code = {
        callback: function (value, id, code, name) {
            if (typeof value.sku == "undefined") {
                alert("请选择商品");
                return;
            }
            var num = value.num;
            var type = "^[0-9]*[1-9][0-9]*$";
            var re = new RegExp(type);
            if (num.match(re) == null)
            {
                alert("请输入大于零的整数!");
                return;
            }
            $.ajax({
                type: "GET",
                url: "?app_act=oms/sell_return/opt_change_detail",
                data: {sku: value.sku,
                    num: value.num,
                    sell_change_detail_id: value.sell_change_detail_id,
                    sell_return_code: sell_return_code,
                    deal_code: value.deal_code,
                    avg_money: value.avg_money,
                    is_gift: value.is_gift,
                    app_fmt: 'json'},
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        save_component('change_goods');
                        change_btn_status('change_goods');
                    }
                }
            });

            if (selectPopWindowshelf_code.dialog != null) {
                selectPopWindowshelf_code.dialog.close();
            }
        }
    };

    function goods_change_detail(goods_code, sell_change_detail_id, sku, deal_code, avg_money, is_gift, num) {
        selectPopWindowshelf_code.dialog = new ESUI1.PopSelectWindow('?app_act=common/select1/order_goods&goods_code=' + goods_code + '&deal_code=' + deal_code + '&sell_change_detail_id=' + sell_change_detail_id + '&sku=' + sku + '&avg_money=' + avg_money + '&num=' + num, 'selectPopWindowshelf_code.callback', {title: '修改商品规格', width: 900, height: 500, ES_pFrmId: ES_frmId}).show();
    }
</script>
