<style type="text/css">

    #price_start,#price_end{
        width: 50px;
    }
    #num_start,#num_end{
        width: 84px;
    }
    .control-group{
        width:350px!important;
    }
    .grid_header_fix{
        top:0px!important;
    }
</style>
<?php
//交易类型
$keyword_type = array(
    'goods_code'=>'商品编码',
    'title'=>'商品名称',
    'goods_barcode'=>'商品条形码',
);
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        )
    ),
    'show_row' => 2,
    'hidden_fields'=>array(
         array(
             'field'=>'include_express',
             'value'=>0
         )
    ),
    'fields' => array(
        array(
            'label' => '订单价格',
            'type' => 'group',
            'field' => 'order_price',
            'child' => array(
                array('title' => 'start', 'type' => 'text', 'field' => 'price_start'),
                array('pre_title' => '~', 'type' => 'text', 'field' => 'price_end', 'remark' => ''),
                array('pre_title' => '&nbsp;&nbsp;', 'type' => 'checkbox', 'field' => 'include_express_c'),
            ),
        ),
        array(
            'label' => '买家昵称',
            'type' => 'text',
            'field' => 'buyer_nick',
        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'goods_num',
            'child' => array(
                array('title' => 'start', 'type' => 'text', 'field' => 'num_start'),
                array('pre_title' => '~', 'type' => 'text', 'field' => 'num_end', 'remark' => ''),
            ),
        ),
    ),
    'col'=>1,

));
?>
<input type="hidden" name="search_id" value="">
<?php include 'op_test_gift_com.tpl.php';?>
<script>
    $(function(){
        var nodata = $(".nodata").text() === '' ?  0 : 1;
        $('#btn_more').hide();
        $('#include_express_c').after('&nbsp;&nbsp含运费')
    })
    $('#include_express_c').change(function(){
        var include_type = $('#include_express').val();
        if(include_type == 1){
            $('#include_express').val(0);
        }else{
            $('#include_express').val(1);
        }

    })
    function close_window(index, row){
        top.window.tid = row.tid;
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
    }
</script>
