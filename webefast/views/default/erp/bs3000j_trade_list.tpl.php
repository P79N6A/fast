<?php echo load_js('comm_util.js')?>
<?php echo load_js("pur.js",true);?>
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>BS3000J单据同步</h2></span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
 render_control ( 'SearchForm', 'searchForm', array (
    'buttons' =>array(

   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'        
    ),
         ) ,

    'fields' => array (
	    array (
	    		'label' => '订/退单编号',
	    		'type' => 'input',
	    		'id' => 'sell_record_code'
	    ),
	    array (
	    		'label' => '店铺',
	    		'type' => 'select_multi',
	    		'id' => 'shop_code',
	    		'data' => load_model('erp/BserpModel')->get_erp_shop(),
	    ),
	  
      
    )
) );
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
		array('title' => '未上传', 'active' => true, 'id' => 'no_upload'),
        array('title' => '已上传', 'active' => false, 'id' => 'upload'),
       
        
       
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
   
</div>

<?php render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	//array('id'=>'create_out_record', 'title' => '生成销货单', 'callback'=>'create_wbm_store_out_record'),
                  // array('id'=>'view', 'title' => '查看', 'callback'=>'showDetail'),
                ),
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '订/退单编号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array (
            		'type' => 'text',
            		'show' => 1,
            		'title' => '单据类型',
            		'field' => 'order_type',
            		'width' => '80',
            		'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '70',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '单据金额',
                'field' => 'payable_money',
                'width' => '100',
                'align' => ''
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '发货/收货时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '上传失败消息',
                'field' => 'upload_msg',
                'width' => '150',
                'align' => ''
            ),

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '上传时间',
                'field' => 'upload_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'erp/Bs3000jModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
	'CheckSelection'=>true,
) );
?>

 <ul id="ToolBar1" class="toolbar frontool">
        <li class="li_btns"><button class="button button-primary btn_upload_record">批量上传</button></li>
        <div class="front_close">&lt;</div>
 </ul>   
<script>
$(function(){
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        tableStore.load();
    });
    $("input[name='is_normal']").change(function(){
        tableStore.load();
    });
    tableStore.on('beforeload', function(e) {
        e.params.upload_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
	
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
<script type="text/javascript">
	//批量上传
    $(".btn_upload_record").click(function(){
    	
        get_checked($(this), function(ids){
        	//校验是否绑定批发通知单
          	var d = {"record_codes": ids.toString(),'app_fmt': 'json'};
         	 $.post("?app_act=erp/bs3000j/upload_multi", d, function(data){
         		var type = data.status == 1 ? 'success' : 'error';
          		BUI.Message.Alert(data.message, type);
      			tableStore.load();
	         }, "json");
          	
        })
    })
    
     //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否确定要执行' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function() {
                        this.close();
                    }
                }
            ]
        });
    }
</script>



