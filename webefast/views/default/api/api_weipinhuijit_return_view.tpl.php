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
render_control('PageHead', 'head1', array('title' => '唯品会退供单详情',
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
                'title' => '商品名称',
                'field' => 'product_name',
                'width' => '250',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'qty',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '档期号',
                'field' => 'po_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '箱号',
                'field' => 'box_no',
                'width' => '250',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitReturnModel::get_goods_by_page',
    'params' => array('filter' => array('return_sn' => $request['return_sn'])),
    'idField' => 'return_sn',
    'CellEditing' => true,
));
?>
            </div>
        </li>
    </ul>
</form>

<script type="text/javascript">

    var dataRecord = [
        {'title': '店铺', 'type': 'input', 'name': 'shop_name', 'value': '<?php echo $response['data']['shop_name'] ?>'},
        {'title': '退供单号', 'type': 'input', 'name': 'return_sn', 'value': '<?php echo $response['data']['return_sn'] ?>'},
        {'title': '关联批发退货单', 'type': 'input', 'name': 'record_code', 'value': '<?php echo $response['data']['record_code'] ?>'},
        {'title': '唯品会仓库', 'type': 'input', 'name': 'warehouse_name', 'value': '<?php echo $response['data']['warehouse_name'] ?>'},
        {'title': '总商品数', 'type': 'input', 'name': 'num', 'value': '<?php echo $response['data']['num'] ?>'},
        {'title': '总箱数', 'type': 'input', 'name': 'box_num', 'value': '<?php echo $response['data']['box_num'] ?>'},
    ]
    $(document).ready(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "edit_url": "?app_act=api/api_weipinhuijit_return/do_edit"
        });
    })



</script>


