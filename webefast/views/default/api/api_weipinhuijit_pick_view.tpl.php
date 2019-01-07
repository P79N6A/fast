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
$links = array();
render_control('PageHead', 'head1', array('title' => '唯品会JIT拣货单详情',
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
                'title' => '档期号',
                'field' => 'po_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '货号',
                'field' => 'art_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'product_name',
                'width' => '200',
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
                'title' => $response['goods_spec1_rename'] ,
                'field' => 'spec1_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '尺码',
                'field' => 'size',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供货价（不含税）',
                'field' => 'actual_unit_price',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供货价（含税）',
                'field' => 'actual_market_price',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待发货数量',
                'field' => 'stock',
                'width' => '85',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已通知数量',
                'field' => 'notice_stock',
                'width' => '85',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已发货数量',
                'field' => 'delivery_stock',
                'width' => '85',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitPickModel::get_goods_by_page',
    'params' => array('filter' => array('pick_no' => $request['pick_no'])),
    'idField' => 'pick_no',
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

<script type="text/javascript">

    var dataRecord = [
        {'title': '店铺', 'type': 'input', 'name': 'shop_name', 'value': '<?php echo $response['data']['shop_name'] ?>'},
        {'title': '拣货单号', 'type': 'input', 'name': 'pick_no', 'value': '<?php echo $response['data']['pick_no'] ?>'},
        {'title': '档期号', 'type': 'input', 'name': 'po_no', 'value': '<?php echo $response['data']['po_no'] ?>'},
        // {'title':'批发通知单号', 'type':'input', 'name':'pftzd_djbh', 'value':'<?php echo $response['data']['pftzd_djbh'] ?>'},
        //  {'title':'批发销货单号', 'type':'input', 'name':'pfxhd_djbh', 'value':'<?php echo $response['data']['pfxhd_djbh'] ?>'},
        {'title': '送货仓库', 'type': 'input', 'name': 'warehouse_name', 'value': '<?php echo $response['data']['warehouse_name'] ?>'},
        {'title': '出库单号', 'type': 'input', 'name': 'delivery_no', 'value': '<?php echo $response['data']['delivery_no'] ?>'},
    ]
    $(document).ready(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "edit_url": "?app_act=api/api_weipinhuijit_pick/do_edit"
        });
    })



</script>


