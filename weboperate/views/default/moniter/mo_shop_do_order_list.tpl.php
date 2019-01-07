<style>
    .well .control-group {
        width:100%;
    }
</style>
<div>
    <form id="searchForm" class="form-horizontal well" tabindex="0" style="outline: none;">
        <table width="100%">
            <tr>
                <td width="100%">
                    <div class="row">
                        <div class="control-group">
                            <label class="control-label">平台</label>
                            <div class="controls">
                                <select id="type" name="type">
                                    <option value=" ">全部</option>
                                    <?php foreach ($response['data']['source'] as $value){?>
                                    <option value="<?php echo $value['pt_code']?>"><?php echo $value['pt_name']?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <label class="control-label">活动时间</label>
                            <div class="controls">
                            <input type="text" id="date_start" name="date_start" class="calendar" style="width:95px;height:20px;" value="<?php echo date('Y-m-d');?>"/>
                             ~
                            <input type="text" id="date_end" name="date_end" class="calendar" style="width:95px;height:20px;" value="<?php echo date('Y-m-d');?>"/>
                            </div>
                        </div>
                </td>
            </tr>
            <tr>
                <td class="efast5_btn">
                    <div class="form-actions span2_5">                 	
                        <button type="button" class="button button-primary" id="btn-search">查询</button>
                    </div>	

                    </div>
                </td>	
            </tr>
        </table>
    </form>
</div>
<div class="row">  总单量：<span id="order_num">0</span>  &nbsp; &nbsp; &nbsp;总金额：<span id="order_money">0</span>  </div>
<div class="row">
    <div class="span30">
        <div id="grid">

        </div>
    </div>
</div>

<!-- script start --> 
<script type="text/javascript">
    var data = [];
    var grid, store;
    BUI.use(['bui/grid', 'bui/data'], function (Grid, Data) {
        var Grid = Grid,
                Store = Data.Store,
                columns = [
                    {title: '客户ID', dataIndex: 'kh_id', width: 100},
                    {title: '客户名称', dataIndex: 'kh_name', width: 150},
                    {title: '单据数量', dataIndex: 'num', width: 100},
                    {title: '交易总金额', dataIndex: 'all_money', width: 150},
                    {title: '店铺代码', dataIndex: 'shop_code', width: 150},
                    {title: '店铺昵称', dataIndex: 'seller_nick', width: 150}
                ];


        store = new Store({
            data: data
        }),
                grid = new Grid.Grid({
                    render: '#grid',
                    width: '100%', //如果表格使用百分比，这个属性一定要设置
                    columns: columns,
                    idField: 'seller_nick',
                    store: store
                });

        grid.render();
    });

    function get_data() {

        var param = {};
        param.type = $('#type').val();
      //  param.date = $('#date').val();
        param.date_start = $('#date_start').val();
        param.date_end = $('#date_end').val();
        var url = '?app_act=moniter/mo_shop/get_order_data&&app_fmt=json';
        $.post(url, param, function (ret) {
            data = ret.data.data;
            $('#order_num').text(ret.data.data_all.order_num);
            $('#order_money').text(ret.data.data_all.order_money);
            store.setResult(data); //重设数据

        }, 'json');

    }


    $(function () {
        $('#btn-search').click(function () {
            get_data();
        });
    });
    
    $(function(){
	BUI.use('bui/calendar',function(Calendar){
    	var datepicker = new Calendar.DatePicker({
	    	trigger:'.calendar',
	    	autoRender : true,
	    	showTime:false,//显示时分秒
    	});
   });
})

</script>