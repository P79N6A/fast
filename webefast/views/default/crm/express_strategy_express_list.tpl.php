<?php echo load_js('comm_util.js') ?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (

            array (
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式代码',
                'field' => 'express_code',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式名称',
                'field' => 'express_name',
                'width' => '100',
                'align' => ''
            ),       
        )
    ),
    'dataset' => 'crm/ExpressStrategyModel::get_express_by_page',
//    'queryBy' => 'searchForm',
    'idField' => 'express_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>

        <div class="row form-actions actions-bar">
          <div class="span13 offset3 ">
           <input type="hidden" id="policy_express_id" name="policy_express_id" value="<?php echo $response['id']?>"/>
            <button type="submit" class="button button-primary" id="submit2">提交</button>
            <button type="reset" class="button " id="reset">重置</button>
          </div>
        </div>

        
<script type="text/javascript">        
    $('#submit2').click(function(){
    	get_checked();
    });   


    //读取已选中项
    function get_checked() {
        var ids = new Array();
        var codes = new Array();
        var names = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择配送方式", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            codes.push(row.express_code+','+row.express_name);
        }

        var  policy_express_id = $("#policy_express_id").val();

      var url = "?app_act=crm/express_strategy/do_add_express";
     	$.ajax({ type: 'POST', dataType: 'json',
		url: url, data: {express_code: codes,policy_express_id: policy_express_id},
		success: function(data) {
            if(data.status == 1){
               top.n_tableStore.load();
               ui_closePopWindow(<?php echo $response['ES_frmId'];?>);
             }
        }});
    }  

</script>
       