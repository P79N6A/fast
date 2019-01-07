<?php require_lib('util/oms_util', true); ?>
<?php echo load_js("baison.js,record_table.js", true); ?>

<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 5px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '零售日报详情','ref_table' => 'table'));
?>

<div class="panel record_table" id="panel_html">
</div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b></b>
            <input type="text" placeholder="商品名称/编码/条形码" class="input" value="" id="goods_search"/>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
        </div>
    </div>
    <?php
    render_control('DataTable', 'table_list', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '序号',
                    'field' => 'detail_no',
                    'width' => '80',
                    'align' => 'center'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_name',
                    'width' => '250',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '200',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统规格',
                    'field' => 'spec',
                    'width' => '230',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '数量',
                    'field' => 'num',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '金额',
                    'field' => 'money',
                    'width' => '100',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'erp/BsapiDetailModel::get_daily_report_detail',
        'params' => array('filter' => array('record_code' => $response['data']['record_code'], 'record_type' => $response['data']['record_type'])),
        'idField' => 'detail_id',
    ));
    ?>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var dataRecord = [
        {'title': '单据编号', 'type': 'input', 'name': 'record_code', 'value': '<?php echo $response['data']['record_code'] ?>'},
        {'title': '业务日期', 'type': 'input', 'name': 'record_date', 'value': '<?php echo $response['data']['record_date'] ?>'},
        {'title': '生成时间', 'type': 'input', 'name': 'create_time', 'value': '<?php echo $response['data']['create_time'] ?>'},
        {'title': '单据类型', 'type': 'input', 'name': 'record_type_name', 'value': "<?php echo $response['data']['record_type_name'] ?>"},
        {'title': '店铺', 'type': 'input', 'name': 'shop_code_name', 'value': '<?php echo $response['data']['shop_code_name'] ?>'},
        {'title': '仓库', 'type': 'input', 'name': 'store_code_name', 'value': '<?php echo $response['data']['store_code_name'] ?>'},
        {'title': '总数量', 'type': 'input', 'name': 'quantity', 'value': '<?php echo $response['data']['quantity'] ?>'},
        {'title': '总金额', 'type': 'input', 'name': 'amount', 'value': '<?php echo $response['data']['amount'] ?>'},
        {'title': '总运费<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="销售退单的总运费包含赔付金额以及手工调整金额" />', 'type': 'input', 'name': 'express_amount', 'value': '<?php echo $response['data']['express_amount'] ?>'}
    ];
    $(document).ready(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "edit_url": ""
        });
        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'goods_search': $('#goods_search').val()});
        });
    });

</script>


