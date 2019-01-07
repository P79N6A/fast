<div class="page-header1" style="margin-top: 4px;">
    <span class="page-title">
        <h2>预采购商品列表</h2>
    </span>
    <span class="page-link">
        <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear"></div>
<hr>
<div>
    <span>
        <span class="action-link">
            <span class="button button-primary" onclick='opt_submit_purchase()'>生成经销采购单</span>
            <span class="button button-primary" onclick='opt_click_clear()'>清空</span>
            <span class="button button-primary" onclick='opt_go_purchase()'>继续选货</span>
        </span>
    </span>
</div>
<div id="data_count">
    <table style="width: 40%;">
        <tr>
            <td style="text-align: right; width: 100px;">已选商品数：</td>
            <td><?php echo $response['sum_num'] ?></td>
            <td style="text-align: right;">已选商品结算金额：</td>
            <td><?php echo $response['sum_price'] ?>元</td>
        </tr>
    </table>
</div>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//            array(
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '80',
//                'align' => '',
//                'buttons' => array(
//                    array('id' => 'delete', 'title' => '删除','callback' => 'do_delete_shopping', 'confirm' => '确认要删除吗？'),
//                ),
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预采购商品数量',
                'field' => 'purchase_num',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype : 'text'}",
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<span style="color:green;font-weight:bold;">{purchase_num}</span>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '图片',
                'field' => 'img_html',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品规格',
                'field' => 'spec_info',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批次',
                'field' => 'lof_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '价格',
                'field' => 'lof_price',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存',
                'field' => 'effec_num',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/GoodsModel::get_by_shopping_page',
//    'queryBy' => 'searchForm',
    'idField' => 'shopping_id',
    'CheckSelection' => true,
//    'customFieldTable' => 'fx_goods_do_list/table',
//    'export' => array('id' => 'exprot_list', 'conf' => 'fx_goods_record_list', 'name' => '商品列表'),
    'CellEditing' => true,
));
?>




<!--<ul class="toolbar frontool" id="tool">
    <li class="li_btns"><button class="button button-primary" onclick="opt_submit_purchase()">提交采购</button></li>
    <li class="li_btns"><button class="button button-primary" onclick="opt_click_clear()">一键清空</button></li>
    <div class="front_close">&lt;</div>
</ul>-->
<script>
    $(function () {
//        function tools() {
//            $(".frontool").animate({left: '0px'}, 1000);
//            $(".front_close").click(function () {
//                if ($(this).html() == "&lt;") {
//                    $(".frontool").animate({left: '-100%'}, 1000);
//                    $(this).html(">");
//                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
//                } else {
//                    $(".frontool").animate({left: '0px'}, 1000);
//                    $(this).html("<");
//                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
//                }
//            });
//        }
//        tools();
        tableCellEditing.on('accept', function (record) {
            var params = {
                "shopping_id": record.record.shopping_id,
                "purchase_num": record.record.purchase_num,
                "is_force": 1
            }
            $.post("?app_act=fx/goods/edit_purchase_num", params, function (data) {
                if (data.status != 1) {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json")
        });

    })
</script>
<script type="text/javascript">

    /*
     * 一键清空
     */
    function opt_click_clear() {
        if (confirm('确定清空吗？')) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('fx/goods/click_clear'); ?>',
                success: function (ret) {
                    if (ret.status == 1) {
                        BUI.Message.Alert('清空成功');
                        tableStore.load();
                    } else {
                        BUI.Message.Alert('清空失败');
                    }
                }
            });
        }
    }
    //提交采购
    function opt_submit_purchase() {
        get_checked(function (ids) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('fx/goods/submit_purchase'); ?>',
                data: {shopping_ids: ids},
                success: function (ret) {
                    if (ret.status == 1) {
                        var url = '?app_act=fx/purchase_record/view&purchaser_record_id=' + ret.data;
                        openPage(window.btoa(url), url, '经销采购详情');
                        location.reload();
                    } else {
                        BUI.Message.Alert(ret.message);
                    }
                }
            });
        })
    }
    //读取已选中项
    function get_checked(func) {
        var ids = [];
        var selecteds = tableGrid.getSelection();
        for (var i in selecteds) {
            ids.push(selecteds[i].shopping_id);
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择商品", 'error');
            return
        }
        func.apply(null, [ids])
    }

    function opt_go_purchase() {
        var url = '?app_act=fx/goods/do_list';
        openPage(window.btoa(url), url, '待采购商品列表');
    }
</script>
