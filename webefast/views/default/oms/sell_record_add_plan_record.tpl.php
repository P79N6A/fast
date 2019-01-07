<div>
    <div style="height:300px;overflow:auto;">
        <table cellspacing="0" class="table table-bordered" id="goods_info_detail" >
            <thead>
            <tr>
                <th>商品名称</th>
                <th>商品编码</th>
                <th style="">颜色</th>
                <th style="">尺码</th>
                <th>商品条形码</th>
                <th>数量</th>
                <th>仓库</th>
                <th>供应商</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach($response['sell_record_detail'] as $key=>$detail){?>
                <tr class="">
                    <td><?php echo $detail['goods_name'];?></td>
                    <td class='goods_code' name="goods_code"><?php echo $detail['goods_code'];?></td>
                    <td class='spec1_name' name="spec1_name"><?php echo $detail['spec1_name'];?></td>
                    <td class='spec2_name' name="spec2_name"><?php echo $detail['spec2_name'];?></td>
                    <td class='barcode' name="barcode"><?php echo $detail['barcode'];?>
                        <input type="hidden" class="sku" name="sku" value="<?php echo $detail['sku'];?>">
                    </td>
                    <td class='' name="">
                        <input class="short_num" name="short_num" style="width: 50px;" type="text" value="<?php echo $detail['short_num'];?>" disabled="disabled">
                    </td>
                    <td>
                        <select class="store_code" name='store_code' style="width:90px;">
                            <?php foreach($response['store_code'] as $key=>$store){?>
                                <option value="<?php echo $store['store_code'];?>" <?php if ($detail['store_code'] === $store['store_code']){ echo "selected='selected'";}?>>
                                    <?php echo $store['store_name'];?>
                                </option>
                            <?php }?>
                        </select>
                    </td>
                    <td style="width: 100px;">
                        <select class="supplier_code" name='supplier_code' style="width:90px;">
                            <?php foreach($response['supplier_code'] as $key=>$supplier){?>
                                <option value="<?php echo $supplier['supplier_code'];?>" >
                                    <?php echo $supplier['supplier_name'];?>
                                </option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
    <br/><br/>
    <div style="text-align: center" >
        <button class="button button-small" id="btn_save_goods_info"><i class="icon-ok"></i>生成采购订单</button>
    </div>

</div>

<script>
    //保存
    $("#btn_save_goods_info").on("click", function () {
        $("#btn_save_goods_info").attr("disabled", "disalbed");
        var params = {"data": {}, "app_fmt": 'json'};
        $("#goods_info_detail tr").each(function (index, element) {
            if (index > 0) {
                var record_params = {};
                var sku = $(element).find("[name='sku']").val();
                record_params['sku'] = sku;
                var short_num = $(element).find("[name='short_num']").val();
                record_params['short_num'] = short_num;
                var store_code = $(element).find("[name='store_code']").val();
                record_params['store_code'] = store_code;
                var supplier_code = $(element).find("[name='supplier_code']").val();
                record_params['supplier_code'] = supplier_code;
                params.data[index] = record_params;
            }
        });
        var ajax_url = '?app_act=oms/sell_record/add_plan_record_action';
        $.post(ajax_url, params, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert('采购订单生成成功！', function () {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                }, 'success');
            } else {
                $("#btn_save_goods_info").removeAttr("disabled");
                BUI.Message.Alert(data.message, 'error');
            }
        }, 'json');
    });

</script>