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
<?php echo load_js("baison.js,record_table.js",true);?>
<?php

render_control('PageHead', 'head1', array('title' => '库存调剂详细',
//    'links' => array(
//    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
//        array('type'=>'js','js'=>'group_remove()','title'=>'一键解除缺货', 'is_pop'=>false,),
////        array('type'=>'js','js'=>'group_split()','title'=>'一键拆分订单', 'is_pop'=>false,),
//    ),
    'ref_table' => 'table'
));

?>

<script>

var record_code = "<?php echo $response['data']['record_code']; ?>";


var data = [
			
            {  
                "name":"sell_record_code",
                "title":"订单号",
                "value":"<?php echo $response['data']['sell_record_code']?>",
                "type":"input",
                
            },
            {
                "name":"deal_code_list",
                "title":"交易号",
                "value":"<?php echo $response['data']['deal_code_list']?>",
                "type":"input"
            },

            {
           	    "name":"record_time",
                "title":"下单时间",
                "value":"<?php echo $response['data']['record_time']?>",
                "type":"input"

            },
            {  
                "name":"pay_time",
                "title":"付款时间",
                "value":"<?php echo $response['data']['pay_time']?>",
                "type":"input"
            },
            {  
                "name":"plan_send_time",
                "title":"计划发货时间",
                "value":"<?php echo $response['data']['plan_send_time'];?>",
                "type":"time"
            }
        ];
        
jQuery(function(){
	var r = new record_table();
	r.init({
        "id":"panel_html",
        "data":data,    
        "title":"单据信息",
        "is_edit":false
    });   
   
	

});

</script>

<div class="panel record_table" id="panel_html">

</div>

<div class="panel">

    <div class="panel-body">
        <div class="row" style=" height: 32px;padding-top: 8px;">
            <div class="span18">
                <span style="font-weight: bold;">缺货商品列表</span>
                <select name="short_sku" id="short_sku" style="width: 500px;">
                <?php foreach( $response['short_list']  as $k=> $val):?>
                        <option value="<?php echo $k ?>" <?php if($val['sku']==$request['sku']):?> selected="selected" <?php endif;?>><?php echo $val['name'] ?></option>
                 <?php endforeach;?>   
                </select> 
     
            </div>
       
          
        </div>
        <?php
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
                      array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '120',
                        'align' => '',
                        'buttons' => array(
                            array(
                                'id' => 'adjust',
                                'title' => '确认调剂',
                                'callback' => 'do_adjust',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '订单号',
                        'field' => 'sell_record_code',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '交易号',
                        'field' => 'deal_code_list',
                        'width' => '180',
                        'align' => ''
                    ),
                  
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '付款时间',
                        'field' => 'pay_time',
                        'width' => '150',
                        'align' => ''
                    ),
                   array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '计划发货时间',
                        'field' => 'plan_send_time',
                        'width' => '150',
                        'align' => ''
                    ),
                    
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '买家昵称',
                        'field' => 'buyer_name',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '数量',
                        'field' => 'num',
                        'width' => '120',
                        'align' => '',
                    )
                )
            ),
            'dataset' => 'oms/SellRecordInvAdjustModel::get_adjust_record_list',
            'idField' => 'sell_record_code',
            'init'=>'nodata',
            'init_note_nodata'=>'',
        ));
        ?>
          
    </div>
   
</div>
<script>
$(function(){
    $('#short_sku').change(function(){
        get_list();
        
        
    });
    function get_list(){
        var short_sku = $('#short_sku').val();
        var sku_data =  short_sku.split(',');
     var num = $('.bui_page_table').eq(0).val();
     var obj = {
        	    limit: num, 
        	    page_size: num, 
        	    pageSize: num, 
        	    start: 1,
                    sku:sku_data[0],
                    short_num:sku_data[1],
                    sell_record_code:$('#sell_record_code').text()
        };
        tableStore.load(obj);    
    }
 
  
    get_list();
});
      function do_adjust(_index, row){
        var short_sku = $('#short_sku').val();
        var sku_data =  short_sku.split(',');
        var sku = sku_data[0];
        var param = {};
        param.record_code = $('#sell_record_code').text();
        param.sku = sku_data[0];
        param.short_num = sku_data[1];
        param.by_record_code =  row.sell_record_code;
        
        var url = '?app_act=oms/sell_record_ajust/inv_adjust&app_fmt=json';
        $.post(url,param, function(ret) {
            if(ret.status == '1'){
                BUI.Message.Alert('调剂成功','info');
                window.location.href = '?app_act=oms/sell_record/view&ref=do&sell_record_code='+ param.record_code ;
            }else{
                  BUI.Message.Alert(ret.message,'error');
            }
        },'json');
    }
</script>