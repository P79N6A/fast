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
render_control('PageHead', 'head1', array('title' => '有货采购单详情',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<div class="panel record_table" id="panel_html">
</div>
<form class="form-panel" action="post" style="margin-top: 10px;">
    <div class="panel-title">
        <span>
            商品信息
        </span>
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
                                'title' => '商品条码',
                                'field' => 'factory_code',
                                'width' => '180',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '采购数量',
                                'field' => 'numbers',
                                'width' => '150',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '待发货数量',
                                'field' => 'no_deliver_num',
                                'width' => '150',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '已发货数量',
                                'field' => 'deliver_num',
                                'width' => '150',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '价格',
                                'field' => 'purchase_price',
                                'width' => '150',
                                'align' => ''
                            ),
                        )
                    ),
                    'dataset' => 'api/YohoPurchaseModel::get_goods_by_page',
                    'params' => array('filter' => array('purchase_no' => $request['purchase_no'])),
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
        {'title': '收件人', 'type': 'input', 'name': 'recipients', 'value': '<?php echo $response['data']['recipients']; ?>'},
        {'title': '手机号', 'type': 'input', 'name': 'phone', 'value': '<?php echo $response['data']['phone']; ?>'},
        {'title': '地址', 'type': 'input', 'name': 'address', 'value': '<?php echo $response['data']['address']; ?>'},
        {'title': '总金额', 'type': 'input', 'name': 'amount', 'value': '<?php echo $response['data']['amount']; ?>'},
        {'title': '总商品数', 'type': 'input', 'name': 'numbers', 'value': '<?php echo $response['data']['numbers']; ?>'},
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


