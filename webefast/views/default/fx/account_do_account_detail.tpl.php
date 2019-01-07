<style>
    /*    tr{
            height: 35px;
        }*/
    .remainder_top,.title_top{
        font-size: 16px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .bui-grid{
        width: 100%;
    }
    .panel-body {
        padding: 5px;
    }
    .retrieval_item{
        width: 70%;
    }
    #record_time_start,
    #record_time_end {
        width:100px;
    }
    form h2, form h3 {
        margin-bottom: 2px;
    }
    /*    #detail_data{
            width:80%;
        }*/
    .title_td{
        width: 5%;
    }
    .data_td{
        width: 12%;
    }
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    /*.bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}*/
    .ui-icon-sup-offset {
        background-position: -280px -80px;
        display: inline-block;
        color:red;
    }
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("baison.js,record_table.js", true); ?>
<!--<div class="title_top">
    <span>详情——<?php echo $response['data']['custom_name']; ?></span>
</div>
<hr>-->
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title"><h2>详情——<?php echo $response['data']['custom_name']; ?></h2></span>
    <span class="page-link">
        <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<div class="remainder_top">
    <span style="margin-left:5px;">账户：预存款账户</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="yck_account_capital"></span>
</div>
<hr>

<div class="panel">
    <form>
        <div class="panel-body" id="panel_order">
            <table cellspacing="0" class="retrieval_item" id="table1">
                <tbody>
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label">
                                    <select id="keyword_type" name="keyword_type" class="field" style="width :100px">
                                        <option value="serial_number">支付流水号</option>
                                        <option value="record_code">单据编号</option>
                                    </select>
                                </label>
                                <input type="text" id="keyword" name="keyword" class="control-text" value="" />
                                <div class="controls"></div>
                            </div>
                        </td>
                        <td>
                            <div class="control-group">
                                <label class="control-label">日期</label>
                                <input type="text" name="record_time_start" id="record_time_start" class="input-normal calendar calendar-time" value=""  />~
                                <input type="text" name="record_time_end" id="record_time_end" class="input-normal calendar calendar-time" value=""  />
                            </div>
                        </td>
                        <td>
                            <button type="button" class="button " id="btn-search" onclick="select_change_goods()">查询</button>
                            <button type="button" class="button "   id="exprot_list">导出</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="recharge_gentle_deduct">
            <span style="margin-left:5px; margin-right: 5px;">充值：<span id='recharge_money'><?php // echo $response['recharge_money'];  ?></span></span>|
            <span style="margin-left:5px;">扣款：<span id="deduct_money"><?php // echo $response['deduct_money'];  ?></span></span>
        </div>
        <!--<div id="data_count">
        
        </div>-->
        <div  id="result_grid" class="panel-body"></div>
        <div id="result_grid_pager"></div>
    </form>
</div>
<script type="text/javascript">
    var skuSelectorStore;
    var page_size = 10;
    var custom_code = '<?php echo $response['data']['custom_code']; ?>';
    $(function () {
        load_count();
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量
            var grid = new Grid.Grid();
//            var Grid = Grid;
            skuSelectorStore = new Data.Store({
                url: '?app_act=fx/account/get_by_account&custom_code=' + custom_code,
                autoLoad: true, //自动加载数据
                autoSync: true,
                pageSize: page_size,
                params : {
                },
            });
//                    Store = Data.Store,
            columns = [{
                    title: '操作',
                    dataIndex: '_operate',
                    visible: 1,
                    width: 50,
                    sortable: false,
                    renderer: function (value, obj) {
                        if (obj.state == 1 && obj.abstract != 'tag_red' && obj.detail_type != 2) {
<?php if (load_model('sys/PrivilegeModel')->check_priv('fx/pending_payment/cancellation')) { ?>
                                return '<span class="grid-command btn-edit" onclick = "rush_red(' + obj.id + ')">红冲</span>';
<?php } ?>
                        }
                    }
                }, {
                    title: '支付流水号',
                    dataIndex: 'serial_number', 'sortable': false,
                    width: 200,
                    renderer: function (value, obj) {
                        if (obj.abstract == 'tag_red') {
                            return '<span>'+obj.relevance_serial_number+'</span><sup class="ui-icon-sup-offset">红冲</sup>';
                        } else {
                            return '<span>'+obj.serial_number+'</span>';
                        }
                    }
                }, {
                    title: '日期',
                    dataIndex: 'record_time', 'sortable': false,
                    width: 200
                }, {
                    title: '资金账户',
                    dataIndex: 'capital_account', 'sortable': false,
                    width: 150
                }, {
                    title: '摘要',
                    dataIndex: 'abstract_name', 'sortable': false,
                    width: 150
                }, {
                    title: '充值',
                    dataIndex: 'recharge_money', 'sortable': false,
                    width: 100
                }, {
                    title: '扣款',
                    dataIndex: 'deduct_money', 'sortable': false,
                    width: 100
                }, {
                    title: '账户余额',
                    dataIndex: 'balance_money', 'sortable': false,
                    width: 100
                }],
                    // 实例化 Grid.Plugins.Cascade 插件
                    cascade = new Grid.Plugins.Cascade({
                        renderer: function (record) {
                            var number;
                            if(record.abstract == 'tag_red') {
                                number = record.relevance_serial_number;
                            } else { 
                                number = record.serial_number;
                            }
                            return '<div style="padding: 5px 10px;"><h3>' + record.abstract_name + '</h3> <table id="detail_data"><tr><td class = "title_td">账户：</td><td class = "data_td">' + record.capital_account + '</td><td class = "title_td">日期：</td><td class = "data_td">' + record.record_time + '</td><td class = "title_td">金额：</td><td class = "data_td">' + record.money + '</td><td class = "title_td">支付方式：</td><td class = "data_td">' + record.pay_type + '</td></tr><tr><td class = "title_td">流水号：</td><td class = "data_td">' +  number  + '</td><td class = "title_td">单据编号：</td><td class = "data_td">' + record.record_code + '</td><td class = "title_td">备注：</td><td class = "data_td">' + record.remark + '</td><td class = "title_td">操作人：</td><td class = "data_td">' + record.operator + '</td></tr></table></div>';
                        }
                    });
            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                emptyDataTpl : '<div class="centered">查询的数据不存在</div>',
                idField: 'account_code',
                store: skuSelectorStore,
                plugins: [cascade]	// Grid.Plugins.Cascade 插件
            });
            var pagingBar = BUI.Toolbar.PagingBar;
            var gridPage = new pagingBar({
                render: '#result_grid_pager',
                elCls: 'image-pbar pull-right',
                store: skuSelectorStore,
                totalCountTpl: ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_select" style="width:50px;height:20px;"><option  value="5" >5</option><option selected="selected" value="10" >10</option><option  value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 '
            });
            gridPage.render();
            $('.bui_page_select').live('change', function () {
                var num = parseInt($(this).val());
                var obj = {
                    limit: num,
                    page_size: num,
                    pageSize: num,
                    start: 1
                };
                page_size = num;
                gridPage.set('pageSize', num);
                skuSelectorStore.load(obj);
            });
            grid.render();
        });
    })
    function select_change_goods() {
        var keyword_type = $("#keyword_type").val();
        var record_time_start = $("#record_time_start").val();
        var record_time_end = $("#record_time_end").val();
        var keyword = $("#keyword").val();

        var obj = {keyword_type: keyword_type, record_time_start: record_time_start, record_time_end: record_time_end, keyword: keyword};
        skuSelectorStore.load(obj);
        load_count(obj)
    }
    function load_count(obj) {
        $.post("?app_act=fx/account/count_money&custom_code=" + custom_code, obj, function (data) {
            $('#recharge_money').html(data.recharge_money);
            $('#deduct_money').html(data.deduct_money);
            $('#yck_account_capital').html('余额：' + data.yck_account_capital);
        }, 'json');
    }

    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
    //红冲
    function rush_red(_id) {
        BUI.Message.Confirm('确认要红冲吗？', function () {
            setTimeout(function () {
                $.ajax({type: 'POST', dataType: 'json',
                    url: '<?php echo get_app_url('fx/account/rush_red'); ?>', data: {id: _id},
                    success: function (ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type == 'success') {
                            BUI.Message.Alert(ret.message, type);
                            skuSelectorStore.load();
                        } else {
                            BUI.Message.Alert(ret.message, type);
                        }
                    }
                });
            });
        }, 'question');
    }
    
    
    //导出
    $('#exprot_list').click(function(){

        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
        params = skuSelectorStore.get('params');
        params.ctl_dataset = "fx/BalanceOfPaymentsModel::get_by_account";
        params.ctl_type = 'export';
        params.ctl_export_conf = 'fx_account_detail';
        params.ctl_export_name =  '资金账户明细';
        params.custom_code =  custom_code;
        <?php echo   create_export_token_js('fx/BalanceOfPaymentsModel::get_by_account');?>
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          window.open(url); 
    });
</script>
