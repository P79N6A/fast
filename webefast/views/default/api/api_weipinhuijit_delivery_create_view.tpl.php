<div>已验收未生成出库单的批发销货单</div>
<?php
render_control('DataTable', 'table', array(
		'conf' => array(
				'list' => array(
						array(
								'type' => 'text',
								'show' => 1,
								'title' => '批发销货单号',
								'field' => 'record_code',
								'width' => '150',
								'align' => '',
						),
						array(
								'type' => 'text',
								'show' => 1,
								'title' => '送货仓库',
								'field' => 'warehouse_name',
								'width' => '100',
								'align' => '',
						),
						array(
								'type' => 'text',
								'show' => 1,
								'title' => '创建时间',
								'field' => 'order_time',
								'width' => '150',
								'align' => '',
						),
						
							
						
				)
		),
		'dataset' => 'api/WeipinhuijitStoreOutRecordModel::get_by_page',
		'idField' => 'record_code',
		'params' => array('filter' => array('is_store_out' => 1,'have_delivery' => 0)),
		'CheckSelection'=>true,
));
?>
<div class="clearfix" style="text-align: center; padding: 5px;">
    <button class="button button-primary" id="create_delivery">生成出库单</button>
</div>
<?php echo load_js('comm_util.js')?>
<script type="text/javascript">
$("#create_delivery").click(function(){
	get_checked($(this), function(ids) {
		var d = {"out_ids": ids.toString(),'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_delivery/do_create');?>', d, function(data){

       	 var type = data.status == 1 ? 'success' : 'error';
         BUI.Message.Alert(data.message,function(){
        	ui_closePopWindow(<?php echo $response['data']['ES_frmId'];?>);
            }, type);
        }, "json");
    })
	
});
$(document).ready(function(){
	$("#bar3").hide();
});
//读取已选中项
function get_checked(obj, func) {
    var ids = new Array();
    var rows = tableGrid.getSelection();
    if (rows.length == 0) {
        BUI.Message.Alert("请选择销货订单", 'error');
        return;
    }
    for (var i in rows) {
        var row = rows[i];
        ids.push(row.store_out_record_id);
    }
    ids.join(',');
    BUI.Message.Show({
        title: '自定义提示框',
        msg: '是否确定' + obj.text() + '?',
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

