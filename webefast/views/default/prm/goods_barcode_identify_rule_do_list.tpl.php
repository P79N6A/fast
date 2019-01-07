<?php
render_control('PageHead', 'head1', array('title' => '条码识别方案',
    'links' => array(
        array('url' => 'prm/goods_barcode_identify_rule/detail&app_scene=add', 'title' => '添加条码识别方案', 'is_pop' => true, 'pop_size' => '550,550'),
    ),
    'ref_table' => 'table'
));
?>




<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
	        array(
	        		'type' => 'button',
	        		'show' => 1,
	        		'title' => '操作',
	        		'field' => '_operate',
	        		'width' => '150',
	        		'align' => '',
	        		'buttons' => array(
	        				array('id' => 'edit', 'title' => '编辑',
	        						'act' => 'pop:prm/goods_barcode_identify_rule/detail&app_scene=edit', 'show_name' => '编辑',
	        						'show_cond' => 'obj.is_buildin != 1'),
	        				array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？'),
	        		),
	        ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '方案名称',
                'field' => 'rule_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '优先级',
                'field' => 'priority',
                'width' => '60',
                'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '方案1',
            		'field' => 'rule_content1',
            		'width' => '150',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '方案2',
            		'field' => 'rule_content2',
            		'width' => '150',
            		'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '150',
                'align' => '',
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '创建人',
            		'field' => 'is_add_person',
            		'width' => '100',
            		'align' => '',
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '创建时间',
            		'field' => 'is_add_time',
            		'width' => '150',
            		'align' => '',
            ),
        )
    ),
    'dataset' => 'prm/GoodsBarcodeIdentifyRuleModel::get_by_page',
    'idField' => 'rule_id',
   
));
?>
<div class="panel">
     <div class="panel-header clearfix">
	     <h3 class="pull-left"> 测试：</h3>
        <div class="pull-right">
           
                                         输入您要测试的条码：<input type="text" id = "barcode" name= "barcode" value="a113dfg1231149903vd">
            <button type="button" class="button button-small" id="btnFormSave" style="display:;">确定</button><br>
                                           测试结果:<span class='msg'></span>
        </div>
      </div> 
</div>

<script type="text/javascript">
    //验证条码
	$("#btnFormSave").click(function(){
		var barcode = $("#barcode").val();
		 $.ajax({
		        type: 'POST', dataType: 'json',
		        url: '<?php echo get_app_url('prm/goods_barcode_identify_rule/yanzheng'); ?>',
		        data: {barcode: barcode},
		        success: function (ret) {
		            var type = ret.status == 1 ? 'success' : 'error';
		            if (type == 'success') {
		               // BUI.Message.Alert(ret.message, type);
		               $(".msg").html(ret.message);
		            } else {
		                //BUI.Message.Alert(ret.message, type);
		            	$(".msg").html('识别失败，没有商品或商品没有生成条形码');
		            }
		        }
		    });
	});
	
	//删除
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_barcode_identify_rule/do_delete'); ?>', data: {rule_id: row.rule_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>



