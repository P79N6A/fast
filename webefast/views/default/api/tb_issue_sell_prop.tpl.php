<style>
    .sell_prop select{ width: 147px;}
    .sell_prop .row{ margin-bottom:5px;}
    .sell_prop{ padding:20px;}

    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }
    .scroll {
        height: 50px;                                  /*高度*/
        padding-left: 10px;                             /*层内左边距*/
        padding-right: 10px;                            /*层内右边距*/
        padding-top: 10px;                              /*层内上边距*/
        padding-bottom: 10px;                           /*层内下边距*/
        overflow-y: scroll;                             /*竖向滚动条*/

        scrollbar-face-color: #D4D4D4;                  /*滚动条滑块颜色*/
        scrollbar-hightlight-color: #ffffff;                /*滚动条3D界面的亮边颜色*/
        scrollbar-shadow-color: #919192;                    /*滚动条3D界面的暗边颜色*/
        scrollbar-3dlight-color: #ffffff;               /*滚动条亮边框颜色*/
        scrollbar-arrow-color: #919192;                 /*箭头颜色*/
        scrollbar-track-color: #ffffff;                 /*滚动条底色*/
        scrollbar-darkshadow-color: #ffffff;                /*滚动条暗边框颜色*/
    }
    #spec1_html{height:80px; overflow:auto;}
    #spec2_html{height:80px; overflow:auto;}
</style>
<div class="sell_prop">
    <div class="row">
        <table class='table_panel1' style='width:100%'>
            <tr>
                <td style="width:80px;" >颜色分类</td>
                <td style="width:1000px;" >
                    <div align="left">
                        <div class="scroll" id="spec1_html">
                            <?php
                            $control = '';
                            foreach ($response['sell_element']['prop_1627207']['list_data'] as $key => $val) {
                                $checked = in_array($val, $response['select_spec1']) ? 'checked="checked"' : '';
                                $control .= '<label class = "checkbox"><input disabled name = "' . $response['sell_element']['prop_1627207']['id'] . '" type = "checkbox" value = "' . $key . '" ' . $checked . ' />' . $val . '</label> &nbsp;&nbsp;';
                            }
                            echo $control;
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>尺码</td>
                <td>
                    <div align="left">
                        <div class="scroll" id="spec2_html" >
                            <?php
                            $control = '';
                            foreach ($response['sell_element']['prop_20509']['list_data'] as $key => $val) {
                                $checked = in_array($val, $response['select_spec2']) ? 'checked="checked"' : '';
                                $control .= '<label class = "checkbox"><input disabled name = "' . $response['sell_element']['prop_20509']['id'] . '" type = "checkbox" value = "' . $key . '" ' . $checked . ' />' . $val . '</label> &nbsp;&nbsp;';
                            }
                            echo $control;
                            ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>商品条码</td>
                <td>
                    <div class="row">
                        <input type="text" id="one_key_price" value="" placeholder="填充价格">&nbsp;&nbsp;
                        <input type="text" id="one_key_num" value="" placeholder="填充数量">&nbsp;&nbsp;
                        <button type="button" id="one_key" class="button">确定</button>
                    </div>
                    <table class='table_panel1' id="_sell_prop" style='width:100%'>
                        <tr>
                            <td style="width:10%;">颜色分类</td>
                            <td style="width:10%;">尺码</td>
                            <td style="width:10%;">一口价</td>
                            <td style="width:10%;">数量</td>
                            <td style="width:10%;">SKU编码</td>
                        </tr>
                        <?php foreach ($response['sku_data'] as $k => $v) { ?>
                            <tr>
                                <td><?php echo $v['spec1_name'] ?></td>
                                <td><?php echo $v['spec2_name'] ?></td>
                                <td>
                                    <input class="input-normal" type="text" name= "price" value="<?php echo $v['sku_price'] ?>">
                                </td>
                                <td>
                                    <input class="input-normal" type="text"  name= "sku_quantity" value="<?php echo $v['sku_quantity'] ?>">
                                </td>
                                <td ><?php echo $v['sku_barcode'] ?></td>
                                <td style="display: none;"><?php echo $v['spec1_code'] ?></td>
                                <td style="display: none;"><?php echo $v['spec2_code'] ?></td>
                                <td style="display: none;"><?php echo $v['sku'] ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="row form-actions actions-bar">
        <div class="span13 offset3 ">
            <button type="submit" class="button button-primary" id="save_sell_prop">提交</button>
        </div>
    </div>
    <script type="text/javascript">
        $('#save_sell_prop').on('click', function () {
            var goods = get_table_goods();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('api/tb_issue/save_sell_prop'); ?>',
                data: {shop_code: shop_code, goods_code: goods_code, goods: goods},
                success: function (data) {
                    if (data.status != 1) {
                        BUI.Message.Alert(data.message, 'error');
                    } else {
                        BUI.Message.Alert(data.message, 'success');
                    }
                }
            });
        });

        $('#one_key').on('click', function () {
            var price = $('#one_key_price').val();
            var num = $('#one_key_num').val();
            $("#_sell_prop tr:first").siblings().each(function (trindex, tritem) {
                if (price != '') {
                    $(tritem).find('input[name="price"]').val(price);
                }
                if (num != '') {
                    $(tritem).find('input[name="sku_quantity"]').val(num);
                }
            });
        });

        function get_table_goods() {
            var sub_goods = {};//存储每一行数据
            var tableData = {};
            $("#_sell_prop tr:first").siblings().each(function (trindex, tritem) {
                tableData[trindex] = new Array();
                $(tritem).find("td").each(function (tdindex, tditem) {
                    if (tdindex == 2) {
                        tableData[trindex][tdindex] = $(tditem).find('input[name="price"]').val();
                    } else if (tdindex == 3) {
                        tableData[trindex][tdindex] = $(tditem).find('input[name="sku_quantity"]').val();
                    } else {
                        tableData[trindex][tdindex] = $(tditem).text();//遍历每一个数据，并存入
                    }
                });
                sub_goods[trindex] = tableData[trindex]; //将每一行的数据存入
            });

            return sub_goods;
        }
    </script>
</div>