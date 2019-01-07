<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js",true);?>
<style>
#money_start{width:50px;}
#money_end{width:50px;}
</style>
<?php render_control('PageHead', 'head1',
		array('title'=>'零售结算交易核销明细查询',
				'links'=>array(
				),
				'ref_table'=>'table'
));?>
<script>
$(function(){
	function tools(){
        $(".frontool").animate({left:'0px'},1000);
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
				$(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1000);
                $(this).html("<");
				$(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }
	
	tools();
})
</script>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '零售结算订单号',
                'field' => 'sell_settlement_code',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订（退）单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '单据性质',
                'field' => 'order_attr_txt',
                'width' => '50',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结算类别',
                'field' => 'settle_type_txt',
                'width' => '50',
                'align' => ''
            ),
            
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'je',
                'width' => '80',
                'align' => ''
            ),

             array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销状态',
	            'field' => 'check_accounts_status_txt',
	            'width' => '80',
	            'align' => '',
            
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销备注',
	            'field' => '',
	            'width' => '120',
	            'align' => '',
            
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销时间',
	            'field' => 'check_accounts_time',
	            'width' => '150',
	            'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '核销操作人',
	            'field' => 'check_accounts_user_code',
	            'width' => '100',
	            'align' => ''
            ),
          

        )
    ),
    'dataset' => 'acc/OmsSellSettlementModel::get_record_by_deal_code',
    'queryBy' => 'searchForm',
    'idField' => 'num_iid',
    'params' => array('filter' => array('deal_code'=>$request['deal_code'])),
    'CheckSelection' => true,
    'CascadeTable' => array(
       'list'=>array(
			array('title'=>'商品编码', 'width' => '150','field'=>'goods_code'),
			array('title'=>'商品名称', 'width' => '150','field'=>'goods_name',
				),
			array('title'=>'系统规格','width' => '180', 'field'=>'spec'),
			array('title'=>'商品条形码','width' => '150', 'field'=>'barcode'),
			array('title'=>'商品数量', 'width' => '80','field'=>'num'),
			array('title'=>'应收金额/应退金额', 'width' => '150','field'=>'avg_money'),
			
		),
        'page_size' => 10,
        'url'=>get_app_url('acc/sell_settlement/get_record_detail&app_fmt=json'),
        'params' => 'sell_record_code,deal_code,order_attr'
    ),
) );
?>

<?php include_once (get_tpl_path('process_batch_task'));?>