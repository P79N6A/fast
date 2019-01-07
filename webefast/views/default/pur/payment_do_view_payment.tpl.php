<style type="text/css">
.bui-dialog .bui-stdmod-body {
    padding: 5px;
}
.panel-body {
    padding: 0px;
}
.bui-message {
    padding: 20px 35px;
}
.bui-grid-header{ border-bottom:1px solid #dddddd;}    
.bui-grid-body{ border-bottom:1px solid #dddddd;}
.bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
</style>
<div class="panel">
    <div class="panel-body" id="panel_baseinfo">
        <table  class="table table-bordered">
            <tr>
                <td width="13%" align="right">供应商：</td>

                <td width="12%" id="tid"><?php echo $response['data']['supplier_name']; ?></td>
                <td width="13%" align="right">单据编号：</td>
                <td width="10%"><?php echo $request['record_code']; ?></td>
                <td width="10%" align="right"></td>
                <td width="18%"></td>
            </tr>
            <tr>
                <td  align="right">待付金额：</td>
                <td ><?php echo $response['data']['diff_money']; ?></td>
                <td align="right">已付金额：</td>
                <td ><?php echo $response['data']['payment_money']; ?></td>
                <td  align="right">单据金额：</td>
                <td ><?php echo $response['data']['record_money']; ?></td>
            </tr>
        </table>
    </div>
    <div>
        <?php if($response['data']['diff_money'] > 0) { ?>
            <a href='javascript:void(0)' onclick="add_payment('<?php echo $request['record_code']; ?>')">添加付款记录</a>
        <?php } ?>
    </div>
    <div id="result_grid">
        
    </div>
</div>
<script>
    var record_code = '<?php echo $request['record_code']; ?>';
    var purMoneyStore;
    var page_size = 1000;
    var save_up;
    $(function(){
        BUI.use(['bui/grid', 'bui/data', 'bui/form','bui/tooltip'], function (Grid, Data, Form,Tooltip) {
            //数据变量
            var grid = new Grid.Grid();
            purMoneyStore = new Data.Store({
                url: '?app_act=pur/payment/get_by_page_record&list_type=set_payment_money&record_code='+record_code,
                autoLoad: true, //自动加载数据
                autoSync: true,
                pageSize : page_size,
            });
            var columns = [
                {title: '操作', dataIndex: '_operate',width: 80,'sortable':false, renderer : function(value,obj){
                    if(obj.status == 1) {
                        <?php if(load_model('sys/PrivilegeModel')->check_priv('pur/payment/do_cancellation')) { ?>
                            return "<a href='javascript:void(0)' onclick='do_cancellation("+'"'+obj.serial_number+'"'+")'>作废</a>";
                        <?php } ?>
                    } else if(obj.status == 2){
                        return "<a href='javascript:void(0)' onclick='do_delete("+'"'+obj.serial_number+'"'+")'>删除</a>";
                    }
                }},
                {title: '支付流水号', dataIndex: 'serial_number', width: 200, 'sortable': false},
                {title: '付款金额', dataIndex: 'money', width: 70, 'sortable': false},
                {title: '付款日期', dataIndex: 'payment_time', width: 150, 'sortable': false},
                {title: '状态', dataIndex: 'status_str', width: 80, 'sortable': false},
                {title: '备注', dataIndex: 'remark', width: 150, 'sortable': false},
            ];
				 
               
            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                idField: 'purchaser_record_code',
                store: purMoneyStore,
            });
            grid.render();
        })
    })
    /**
    * 添加付款记录
    */
    function add_payment(record_code) {
        parent.view_add_payment(record_code);
        ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
    }
    /**
    * 作废
    */
    function do_cancellation(serial_number) {
        var url = "?app_act=pur/payment/do_cancellation";
        $.post(url, {serial_number: serial_number}, function (ret) {
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
            } else {
                BUI.Message.Tip(ret.message,'success');
            }
            location.reload();
        }, 'json');
    }
    /**
    * 删除
     * @param {type} serial_number
     * @returns {undefined}     
    */
    function do_delete(serial_number) {
        var url = "?app_act=pur/payment/do_delete";
        $.post(url, {serial_number: serial_number}, function (ret) {
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
            } else {
                BUI.Message.Tip(ret.message,'success');
            }
            location.reload();
        }, 'json');
    }
</script>

