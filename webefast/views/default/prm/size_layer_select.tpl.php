<style>
    .content{padding: 10px;}
    .content a{text-decoration: none;cursor: pointer;}
    .layer-top td{width: 100px;}
    .layer-main{margin-top: 10px;}
    #layer_table{background-color: #FAFAFA;}
    #layer_table,#layer_table tr,#layer_table td,#layer_table th{border: 1px solid #DFDFDF;}
    #layer_table td,#layer_table .head-th{width: 60px;height: 30px;text-align: center;}
    #layer_table input{width: 40px;height: 25px;text-align: center;}
    #layer_table .x-icon-normal{cursor: pointer;}
    .layer-floor{text-align: center;padding-top: 50px;padding-right: 50px;}
    .tip{cursor: pointer;}
    .sku-num,.total-num{width:85% !important;height:90% !important;}
</style>
<div class="content">
    <div class="layer-top">
        <table>
            <tr>
                <th>商品名称：</th><td colspan="5"><?php echo $request['goods_name'] ?></td>
            </tr>
            <tr>
                <th>商品编码：</th><td><?php echo $request['goods_code']; ?></td>
                <th>吊牌价：</th><td><?php echo $request['sell_price']; ?></td>
                <th>仓库：</th><td><?php echo "{$response['store']['store_name']} [{$request['store_code']}]"; ?></td>
            </tr>
        </table>
    </div>
    <div class="layer-main">
        <table id="layer_table">
            <tr id="tb_head">
                <th rowspan="2" class="head-th">颜色</th>
                <th class="head-th">尺码</th>
            </tr>
        </table>
    </div>
    <div class="layer-floor">
<!--        <button id="submit" class="button button-primary" type="submit" onclick="saveLayer(1)">保存继续</button>-->
        <button id="submit" class="button button-primary" type="submit" onclick="saveLayer(2)">保存</button>
        <button id="submit" class="button" type="submit" onclick="close_window()">关闭</button>
    </div>
</div>

<script type="text/javascript">
    var record_type = "<?php echo $request['model'] ?>";
    var negative_inv = "<?php echo $response['negative_inv'] ?>"; //是否允许负库存
    //出库类单据
    var inv_out = ['pur_return_notice', 'pur_return', 'wbm_notice', 'wbm_store_out', 'stm_stock_lock', 'stm_store_shift'];
    //入库类单据
    var inv_in = ['pur_planned', 'pur_order', 'pur_purchase', 'wbm_return_notice', 'wbm_return', 'stm_stock_adjust', 'stm_take_stock'];
    var column = 0;
    $(function () {
        //初始化尺码层
        initLayer();
    });

    function initLayer() {
        var layer = '<?php echo $response['layer'] ?>';
        var spec1 = '<?php echo $response['spec1'] ?>';
        var goods_inv = '<?php echo $response['goods_inv'] ?>';
        
        var trhtml = '';

        layer = eval(layer);
        trhtml += '<tr>';
        $.each(layer, function (i, size) {
            if (size != '') {
                trhtml += '<td><label>' + size + '</label></td>';
                column++;
            }
        });
        trhtml += '<td><label>小计</label></td>';
        column++;
        trhtml += '</tr>';
        $("#layer_table").append(trhtml);

        spec1 = eval(spec1);
        goods_inv = eval(goods_inv);
        trhtml = '';
        $.each(goods_inv, function (i, row) {
            trhtml += '<tr>';
            trhtml += '<td><label>' + spec1[i] + '</label>';
            $.each(row, function (y, val) {
                trhtml += '<td><input type="text" class="sku-num tip" id="' + val.k + '" sku="' + val.sku + '" title="' + val.tip + '" placeholder="' + val.stock_num + '"';
                if ($.inArray(record_type, inv_out) !== -1) {
                    if (val.inv_type < 0 || (negative_inv == 0 && val.stock_num < 1)) {
                        trhtml += ' disabled="disabled" ';
                    }
                } else {
                    if (val.inv_type == -2 || val.inv_type == -3) {
                        trhtml += ' disabled="disabled" ';
                    }
                }

                trhtml += '/>';
            });
            trhtml += '<td><input type="text" class="total-num" readonly="true" value="0" /></td>';
            trhtml += '</tr>';
        });
        $("#layer_table").append(trhtml);

        $(".sku-num").on("change", function () {
            checkNum(this);
        });

        setCombine();
    }

    /*
     * 设置应该合并的行和列
     */
    function setCombine() {
        $("#tb_head").find("th:eq(1)").attr('colspan', column);

        //设置表格的最小宽度
        $("#layer_table").css("min-width", "");
        $('#layer_table').css("min-width", $('#layer_table').width());
    }

    /*
     * 检查数值有效性
     */
    function checkNum(_this) {
        var num = $(_this).val();
        if (num != '') {
            if (record_type === 'stm_stock_adjust') {
                if (!/^-?\d+$/.test(num) || num == 0) {
                    $(_this).val('');
                    BUI.Message.Alert('数量必须为不等于0的整数', 'warning');
                    return false;
                }
            } else if (record_type === 'stm_take_stock') {
                if (!/^\d+$/.test(num)) {
                    $(_this).val('');
                    BUI.Message.Alert('数量必须为正整数', 'warning');
                    return false;
                }
            } else if (record_type !== 'stm_stock_adjust' && !isPositiveNum(num)) {
                $(_this).val('');
                BUI.Message.Alert('数量必须为大于0的整数', 'warning');
                return false;
            }
        }
        if (negative_inv == 0 && $.inArray(record_type, inv_out) !== -1) {
            if (num > parseInt($(_this).attr('placeholder'))) {
                $(_this).val('');
                BUI.Message.Alert('填写数量超过现有库存，请核实', 'warning');
                return false;
            }
        }

        var total_num = 0;
        $(_this).parent().parent().find("input.sku-num").each(function (i, obj) {
            if ($(obj).val() != '') {
                total_num += parseInt($(obj).val());
            }
        });
        $(_this).parent().parent().find("input.total-num").val(total_num);
        return true;
    }

    /*
     * 判断是否为正整数
     */
    function isPositiveNum(s) {
        var re = /^[0-9]*[1-9][0-9]*$/;
        return re.test(s);
    }

    /*
     * 保存数据
     */
    function saveLayer(_type) {
        var data = getTbData();
        if (!data) {
            return false;
        } else {
            var param = {data: data, model: "<?php echo $request['model']; ?>", record_id: "<?php echo $request['record_id'] ?>", store_code: "<?php echo $request['store_code'] ?>"};
            $.post('?app_act=prm/size_layer/add_detail', param, function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip('新增成功', 'success');
                    if (_type === 1) {
                        window.location.reload();
                    } else {
                        close_window();
                    }
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, "json");
        }
    }

    /*
     * 获取尺码层表格设置数据
     */
    function getTbData() {
        var sub_layer = {};//存储每一行数据
        var total = 0; //检查是否整行为空
        $("#layer_table tr:eq(1)").siblings().each(function (trindex, tritem) {
            $(tritem).find("td:first").siblings().find('input.sku-num').each(function (tdindex, tditem) {
                var num = $(tditem).val();
                if (num != '') {
                    sub_layer[$(tditem).attr('id')] = {sku: $(tditem).attr('sku'), num: num};//遍历每一个数据，并存入
                    total++;
                }
            });
        });

        if (total === 0) {
            BUI.Message.Alert("请填写至少一个商品数量", 'info');
            sub_layer = false;
            return false;
        }
        return sub_layer;
    }

    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'bottom-left', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px;">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });

    function close_window() {
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
    }
</script>
