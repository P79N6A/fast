<style>
<?php if ($app['scene'] != 'view'): ?>
        [data-column-field = shop_sync_ratio], [data-column-field = store_sync_ratio] span.bui-grid-cell-text {
            cursor: pointer;
            text-decoration: underline;
        }
<?php endif; ?>
    .shop_ratio select{width: 150px;}
    .shop_ratio .row{ margin-bottom:10px;}
<?php if (isset($request['set_type'])) { ?>
        #table_shop_pager{display: none;}
        .shop_ratio{margin-left:20px;width: 90%;height: 100px;}
<?php } else { ?>
        .shop_ratio{margin:20px;width: 65%;}
<?php } ?>
</style>

<div class="shop_ratio">
    <?php 
        $request['set_type'] = isset($request['set_type']) ? $request['set_type'] : '';
        if ($request['set_type'] == 'set') { ?>
        <div class="row">
            <span>商品：<?php echo $response['goods_info']['goods_name'] . "[" . $response['goods_info']['goods_code'] . "]"; ?></span>&nbsp;&nbsp;
            <span>规格：<?php echo $response['goods_info']['spec1_name'] . "[" . $response['goods_info']['spec1_code'] . "]" . " , " . $response['goods_info']['spec2_name'] . "[" . $response['goods_info']['spec2_code'] . "]" ?></span>
        </div>
    <?php } ?>
    <?php if ($response['shop_ratio']['sync_mode'] == 2): ?>
        <div class="row">
            <label class="control-label">店铺选择：</label>
            <select class="input-small" id="shop_selector" name="shop_selector">
                <?php
                foreach ($response['shop'] as $shop_code => $shop_name) {
                    echo "<option value='{$shop_code}'>{$shop_name}</option>";
                }
                ?>
            </select>
        </div>
    <?php endif; ?>
    <?php
    $list = array();
    if ($response['shop_ratio']['sync_mode'] == '1') {
        $list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '20%',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺代码',
                'field' => 'shop_code',
                'width' => '20%',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_name',
                'width' => '40%',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步比例（%）',
                'field' => 'shop_sync_ratio',
                'width' => '20%',
                'align' => 'center',
                'editor' => $app['scene'] != 'view' ? "{xtype:'number'}" : ''
            ),
        );
    } else {
        $list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库代码',
                'field' => 'store_code',
                'width' => '20%',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'store_name',
                'width' => '40%',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库性质',
                'field' => 'store_property',
                'width' => '20%',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步比例（%）',
                'field' => 'store_sync_ratio',
                'width' => '20%',
                'align' => 'center',
                'editor' => $app['scene'] != 'view' ? "{xtype:'number'}" : ''
            ),
        );
    }
    $dataset = ($response['shop_ratio']['sync_mode'] == '1') ? 'op/InvSyncRatioModel::get_by_global_page' : 'op/InvSyncRatioModel::get_by_store_page';
    $shop_code = array_keys($response['shop']);
    $params = ($request['set_type'] == 'set') ? array('filter' => array('sync_code' => $request['sync_code'], 'shop_code' => $shop_code[0], 'type' => 'goods_ratio', 'sku' => $response['goods_info']['sku'])) : array('filter' => array('sync_code' => $request['sync_code'], 'shop_code' => $shop_code[0]));
    render_control('DataTable', 'table_shop', array(
        'conf' => array(
            'list' => $list
        ),
        'dataset' => $dataset,
        'idField' => 'ratio_id',
        'CellEditing' => true,
        'params' => $params,
        'HeaderFix' => FALSE
    ));
    ?>
    <div style="margin-top: 40px;color: red;">
        <p>说明：</p>
        <?php if(!empty($request['set_type'])) {?>
            <p>&nbsp;&nbsp;1、商品比例优先级高于店铺比例</p>
            <p>&nbsp;&nbsp;2、同步比例填写大于0的任一整数，如填写0，则同步为0</p>
            <p>&nbsp;&nbsp;3、若该页面比例不修改，不会配置商品比例（库存计算取店铺比例），<br><span  style="margin-left: 27px;">点击后则会以店铺比例更新商品比例</span></p>
        <?php } else {?>
            <p>&nbsp;&nbsp;1、店铺比例优先级低于商品比例</p>
            <p>&nbsp;&nbsp;2、同步比例必须填写大于0的整数</p>
        <?php }?>
    </div>
    <script>
        var sync_mode = '<?php echo $response['shop_ratio']['sync_mode']; ?>';
        var set_type = '<?php echo isset($request['set_type']) ? $request['set_type'] : ''; ?>';
        var sku = "<?php echo isset($request['sku']) ? $request['sku'] : '' ?>";
        var select_wh = <?php echo isset($response['select_wh']) ? $response['select_wh'] : '{}'; ?>;
        var scene = "<?php echo $app['scene'] ?>";
        $(function () {
            $("#shop_selector").change(function () {
                table_shopStore.load({'shop_code': $("#shop_selector").val()});
            });
        });

        if (typeof table_shopCellEditing != "undefined" && scene != 'view') {
            table_shopCellEditing.on('accept', function (record, editor) {
                var sync_ratio_old = record.editor.__attrVals.editValue;
                var sync_ratio = (sync_mode == '1') ? record.record.shop_sync_ratio : record.record.store_sync_ratio;
//                var sync_ratio_tmp = (sync_mode == '1') ? record.record.shop_sync_ratio_tmp : record.record.store_sync_ratio_tmp;
                if (sync_ratio == sync_ratio_old && set_type =='') {
                    BUI.Message.Tip('同步比例未修改，无需更新！', 'error');
                    return;
                }
                var pattern = '';
                var message = '';
                if(set_type == '') {
                    pattern = /^\+?[1-9]\d*$/;
                    message = '同步比例必须为大于0的整数！';
                } else {
                    pattern = /^\+?[0-9]\d*$/;
                    message = '同步比例必须为整数！';
                }
                if (!pattern.test(sync_ratio)) {
                    BUI.Message.Tip(message, 'error');
                    return;
                }
                var params = new Array();
                var url = '?app_act=op/inv_sync/';
                if (set_type == '') {
                    params = {sync_mode: sync_mode, ratio_id: record.record.ratio_id, sync_code: record.record.sync_code, sync_ratio: sync_ratio};
                    url += 'do_edit_ratio';
                } else {
                    params = {sync_code: record.record.sync_code, shop_code: record.record.shop_code, store_code: record.record.store_code, sync_ratio: sync_ratio, select_wh: select_wh, sku: sku, set_type: set_type};
                    url += 'set_goods_ratio';
                }

                $.post(url, params, function (result) {
                    if (result.status == 1) {
                        BUI.Message.Tip(result.message, 'success');
                    } else {
                        BUI.Message.Tip(result.message, 'error');
                    }
                }, 'json');
            });
        }
    </script>
</div>
