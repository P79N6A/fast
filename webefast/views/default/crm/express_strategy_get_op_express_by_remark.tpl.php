<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1',
		array('title'=>'买家留言匹配',
				'links'=>array(
						array('url'=>'crm/express_strategy/do_list', 'title'=>'订单快递适配策略'),
				),
				'ref_table'=>'table'
));?>

<?php
render_control ( 'DataTable', 'table', array (
  'conf' => array(
        'list' => array(

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '买家备注关键字',
                'field' => 'key_word',
                'width' => '350',
                'format_js' => array(
                   'type' => 'html',
                   'value' => '<input style="width:280px"  name="key_word" id="{express_code}"  type="text" value="{key_word}"   /> ',
               ),
                'align' => ''
            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '区域范围',
//                'field' => 'area_range',
//                'width' => '350',
//                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<span title="{area_range_all}">{area_range}</span>',
//                ),
//            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式代码',
                'field' => 'express_code',
                'width' => '200',
                'align' => '',
      
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式名称',
                'field' => 'express_name',
                'width' => '200',
                'align' => '',
        )
    ),
 ),
    'dataset' => 'crm/OpExpressByBuyerRemarkModel::get_by_page',
    //'queryBy' => 'searchForm',
    'idField' => 'express_code',
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
) 
        );
?>

<script type="text/javascript">
$(function(){

    tableStore.on('load',function(){ ;
        set_editer();
    });
    function set_editer(){ 
        $.each($('input[name="key_word"]'),function(){ 

    
                $(this).click(function(){
                    if($(this).next('button').length==0){
                    var but =' <button class="button button-small save hide" title="保存" style="display: inline-block;"><i class="icon-ok"></i></button>';
                    var express_code = $(this).attr('id');
                  
                    $(this).after($(but))
                    $(this).next('button').click(function(){
                        key_word_save(this,express_code)
                     });
                    }
                });
          
        });
        

    }
    function key_word_save(but,express_code){
        var data ={};
        data.express_code = express_code;
        data.key_word = $.trim($(but).prev().val());
        if(data.key_word==''){
              BUI.Message.Tip('关键字不能为空!','error');
              return ;
        }
        var url = "?app_act=crm/express_strategy/save_op_express_by_remark&app_fmt=json";
        $.post(url,data,function(ret){
            if(ret.status==1){
                BUI.Message.Tip('保存成功!');
                $(but).remove();
            }else{
                  BUI.Message.Tip(ret.message,'error');
            }
        },'json');
    }
     
});


</script>