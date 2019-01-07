<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>

<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>

<?php
$links = array(
    array('url' => 'api/api_weipinhuijit_delivery/do_list', 'title' => '出库单管理')
);
render_control('PageHead', 'head1', array('title' => '唯品会JIT出库单详情',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
<?php if ($response['data']['is_delivery'] != 1) { ?><button id="confirm_delivery" class="button button-primary"  onclick="confirm_delivery('<?php echo $response['data']['delivery_id']; ?>')">确认出库</button><?php } ?>
    </li>

    <div class="front_close">&lt;</div>
</ul>
<div class="panel record_table" id="panel_html">
</div>

<form class="form-panel" action="post" style="margin-top: 10px;">
    <div class="panel-body">
        <div class="row">
            <b>唯品会拣货单</b>
            <input type="text" class="input" value="" id="pick_no"/>
            <b>系统批发销货单号</b>
            <input type="text" class="input" value="" id="record_code"/>
            <b>商品编码/商品条形码</b>
            <input type="text" class="input" value="" id="goods_code"/>
            <button type="button" class="button button-info" value="查询" id="btnSearchGoods"><i class="icon-search icon-white"></i>查询</button>
        </div>
    </div>
    <ul class="panel-content">

        <li style="overflow: visible; height: auto;">
            <div style="clear: both;"></div>
            <div id="panel_shipping">

<?php
render_control('DataTable', 'table_list', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '拣货单号',
                'field' => 'pick_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '批发销货单号',
                'field' => 'record_code',
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
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格1',
                'field' => 'spec1_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格2',
                'field' => 'spec2_code',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条码',
                'field' => 'barcode',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'amount',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitDeliveryModel::get_goods_by_page',
    'params' => array('filter' => array('delivery_id' => $response['data']['delivery_id'])),
    'idField' => 'id',
    'CellEditing' => true,
    'events' => array(
    //'rowdblclick' => 'showDetail',
    ),
));
?>
            </div>
        </li>
    </ul>
</form>
<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }

        tools();
    })
</script>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">

    var dataRecord = [
        //  {'title':'店铺', 'type':'input', 'name':'shop_name', 'value':'<?php echo $response['data']['shop_name'] ?>'},
        {'title': '出库单号', 'type': 'input', 'name': 'delivery_id', 'value': '<?php echo $response['data']['delivery_id'] ?>'},
        {'title': '生成时间', 'type': 'input', 'name': 'insert_time', 'value': '<?php echo $response['data']['insert_time'] ?>'},
        {'title': '商品总数', 'type': 'input', 'name': 'amount', 'value': '<?php echo $response['data']['amount'] ?>'},
        {'title': '出库状态', 'type': 'input', 'name': 'is_delivery', 'value': "<?php echo $response['data']['is_delivery_src'] ?>"},
        {'title': '品牌', 'type': 'input', 'name': 'brand_name', 'value': '<?php echo $response['data']['brand_name'] ?>'},
        //  {'title':'送货仓库', 'type':'input', 'name':'po_no', 'value':'<?php echo $response['data']['warehouse_name'] ?>'},
        //  {'title':'预计到货时间', 'type':'input', 'name':'arrival_time', 'value':'<?php echo $response['data']['arrival_time'] ?>'},
        {'title': '配送方式', 'type': 'input', 'name': 'carrier_name', 'value': '<?php echo $response['data']['express_name'] ?>'},
        {'title': '快递单号', 'type': 'input', 'name': 'delivery_no', 'value': '<?php echo $response['data']['express'] ?>'},
        {'title': '出库时间', 'type': 'input', 'name': 'delivery_time', 'value': '<?php echo $response['data']['delivery_time'] ?>'},
    ]
    $(document).ready(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "edit_url": "?app_act=api/api_weipinhuijit_delivery/do_edit"
        });
        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'pick_no': $('#pick_no').val(), 'record_code': $('#record_code').val(), 'goods_code': $('#goods_code').val()});

        });
    })

    function confirm_delivery(delivery_id) {
        $("#confirm_delivery").attr('disabled', true);
        var data = {delivery_id: delivery_id, type: 'enable', 'app_fmt': 'json'};
        var url = '?app_act=api/api_weipinhuijit_delivery/confirm_delivery';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    $("#confirm_delivery").attr('disabled', false);
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

</script>


