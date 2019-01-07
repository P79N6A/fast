<?php 
$title = '波次单详情' . $request['record_code'];
render_control('PageHead', 'head1', array('title' => $title,
    'links' => array(
//            array('url'=>'oms/waves_record/two_order_picking ', 'title'=>'二次分拣', 'is_pop'=>false,/* 'pop_size'=>'500,400'*/),
    ),
    'ref_table' => 'table'
));
?>
<?php 
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' =>  '是否有差异',
            'type' => 'select_multi',           
            'data' => array(
                array(0=>'yes',1=>'是'),
                array(0=>'no',1=>'否')
            ),
            'id' => 'keywords',
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '篮位号',
                'field' => 'sort_no',
                'width' => '60',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => '',
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
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统规格',
                'field' => 'spec_data',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位',
                'field' => 'shelf_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分拣数量',
                'field' => 'picking_num',
                'width' => '80',
                'align' => '',
                'editor'=>"{xtype : 'text'}",
            ),
        )
    ),
    'dataset' => 'oms/DeliverRecordModel::get_by_detail_page',
    'queryBy' => 'searchForm',
    'idField' => 'waves_record_id',
    'CellEditing'=>true,
    'export' => array('id' => 'exprot_list', 'conf' => 'waves_record_picking_view', 'name' => '波次单', 'export_type' => 'file'),
    'params' => array('filter' => array('waves_record_id' => $request['waves_record_id'])),
));
?>
<script>
    tableCellEditing.on('accept', function(record) {
            var params = {
                "deliver_record_detail_id": record.record.deliver_record_detail_id,
                "picking_num": record.record.picking_num,
                "sell_record_code": record.record.sell_record_code,
                "sku": record.record.sku,
            };
            $.post("?app_act=oms/deliver_record/edit_picking_num", params, function(data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                } else if(data.status == 1){
//                    BUI.Message.Alert(data.message, 'success');
                    BUI.Message.Show({
                        msg : data.message,
                        icon : 'success',
                        buttons : [],
                        autoHide : true,
                        autoHideDelay : 1000
                    });
                }
                tableStore.load();
            }, "json");
        });
</script>