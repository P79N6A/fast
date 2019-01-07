<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    .panel-body { padding: 0; }
    .table { margin-bottom: 0; }
    .table tr { padding: 5px 0; }
    .table th, .table td { border: 1px solid #dddddd; padding: 3px 0; vertical-align: middle; }
    .table th { width: 8.3%; text-align: center; }
    .table td { width: 23%; padding: 0 1%; }
    .row { margin-left: 0; padding: 2px 8px; border: 1px solid #ddd; }
    .bui-grid-header { border-top: none; }
    p { margin: 0; }
    b { vertical-align: middle; }
</style>
<?php
$links = array();
render_control('PageHead', 'head1', array('title' => '有货出库单详情',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<div class="panel record_table" id="panel_html">
</div>
<form class="form-panel" action="post" style="margin-top: 10px;">
    <div class="panel-title">
        <span>商品信息</span>
    </div>
    <ul class="panel-content">
        <li style="overflow: visible; height: auto;">
            <div style="clear: both;"></div>
            <div id="panel_shipping">
                <?php
                render_control('DataTable', 'table', array(
                    'conf' => array(
                        'list' => array(
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '批发销货单',
                                'field' => 'store_out_record_code',
                                'width' => '180',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品条码',
                                'field' => 'factory_code',
                                'width' => '180',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '发货数量',
                                'field' => 'numbers',
                                'width' => '150',
                                'align' => ''
                            ),
                        )
                    ),
                    'dataset' => 'api/YohoDeliveryModel::get_goods_by_page',
                    'params' => array('filter' => array('delivery_no' => $response['data']['delivery_no'])),
                    'idField' => 'id',
                    'CellEditing' => true,
                    'events' => array(),
                ));
                ?>
            </div>
        </li>
    </ul>
</form>
<script type="text/javascript">
    var dataRecord = [
        {'title': '店铺', 'type': 'input', 'name': 'shop_name', 'value': '<?php echo $response['data']['shop_name']; ?>'},
        {'title': '采购单号', 'type': 'input', 'name': 'purchase_no', 'value': '<?php echo $response['data']['purchase_no']; ?>'},
        {'title': '出库单号', 'type': 'input', 'name': 'delivery_no', 'value': '<?php echo $response['data']['delivery_no']; ?>'},
        {'title': '配送方式', 'type': 'input', 'name': 'express_name', 'value': '<?php echo $response['data']['express_name']; ?>'},
        {'title': '快递单号', 'type': 'input', 'name': 'express_no', 'value': '<?php echo $response['data']['express_no']; ?>'},
        {'title': '总数量', 'type': 'input', 'name': 'numbers', 'value': '<?php echo $response['data']['numbers']; ?>'},
        {'title': '回写状态', 'type': 'input', 'name': 'delivery_status', 'value': '<?php echo $response['data']['delivery_status']; ?>'},
        {'title': '回写时间', 'type': 'input', 'name': 'delivery_time', 'value': '<?php echo $response['data']['delivery_time']; ?>'},
        {'title': '回写日志', 'type': 'input', 'name': 'delivery_log', 'value': '<?php echo $response['data']['delivery_log']; ?>'},
    ]
    $(document).ready(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            //"edit_url": "?app_act=api/api_weipinhuijit_pick/do_edit"
        });
    })
</script>


