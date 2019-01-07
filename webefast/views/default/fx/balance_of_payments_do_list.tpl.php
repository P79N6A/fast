<style>
    #record_time_start,#record_time_end{
        width:100px;
    }
    form h2, form h3 {
        margin-bottom: 2px;
    }
    .title_td{
        width: 5%;
    }
    .data_td{
        width: 10%;
    }
    #result_grid{
        padding:1px;
        padding-bottom: 1px;
    }
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    .ui-icon-sup-offset {
        background-position: -280px -80px;
        display: inline-block;
        color:red;
    }
</style>
<?php
$is_power = load_model('sys/PrivilegeModel')->check_priv('fx/account/add');
$links = '';
if ($is_power == true) {
//    $links = array(array('url' => 'fx/account/detail&app_scene=add', 'title' => '新增', 'is_pop' => true, 'pop_size' => '400,500'));
}
render_control('PageHead', 'head1', array('title' => '收支明细  ',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['serial_number'] = '流水号';
$keyword_type['record_code'] = '单据编号';

$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array('label' => '分销商', 'type' => 'select_pop', 'id' => 'custom_code', 'select' => 'base/custom_multi'),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '摘要',
            'title' => '',
            'type' => 'select',
            'id' => 'abstract',
            'data' => ds_get_select_by_field('abstract'),
        ),
        array(
            'label' => '收款账户',
            'title' => '',
            'type' => 'select',
            'id' => 'income_account',
            'data' => load_model('base/PaymentaccountModel')->get_by_account(),
        ),
        array(
            'label' => '时间',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
    )
));
?>
<div id="recharge_gentle_deduct"></div>
<div  id="result_grid" class="panel-body"></div>
<div id="result_grid_pager"></div>
<script type="text/javascript">
    var skuSelectorStore;
    var page_size = 50;
    var login_type = "<?php echo $response['login_type'] ?>";
    $(function () {
        if (login_type == 2) {
            $("#searchForm #custom_code_select_pop").attr("disabled", "true");
            $("#searchForm #custom_code_select_img").unbind();
        }
    });
    $(function () {
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量
            var grid = new Grid.Grid();
//            var Grid = Grid;
            skuSelectorStore = new Data.Store({
                url: '?app_act=fx/balance_of_payments/get_by_page&list_type=balance_of_payments',
                autoLoad: true, //自动加载数据
                autoSync: true,
                pageSize: page_size,
                params : {
                },
            });
//                    Store = Data.Store,
            columns = [
                {
                    title: '操作',
                    dataIndex: '_operate',
                    visible: 1,
                    width: 80,
                    sortable: false,
                    renderer: function (value, obj) {
                        if(obj.state == 1 && obj.detail_type != 2) {
                            <?php if (load_model('sys/PrivilegeModel')->check_priv('fx/pending_payment/cancellation')) { ?>
                                return '<span class="grid-command btn-edit" onclick = "cancellation('+obj.id+')">作废</span>';
                            <?php } ?>
                        }
                        if(obj.detail_type ==1 && obj.income_type == 1) {
                            <?php if (load_model('sys/PrivilegeModel')->check_priv('fx/pending_payment/do_delete')) { ?>
                                return '<span class="grid-command btn-edit" onclick = "do_delete('+obj.id+')">删除</span>';
                            <?php } ?>
                        }
                    }
                }, {
                    title: '状态',
                    dataIndex: 'status', 'sortable': false,
                    width: 70,
                }, {
                    title: '支付流水号',
                    dataIndex: 'serial_number', 'sortable': false,
                    width: 200,
                    renderer: function (value, obj) {
                        if(obj.relevance_red_serial_number != '') {
                            return '<span>'+obj.serial_number+'</span><sup class="ui-icon-sup-offset">红冲</sup>';
                        } else {
                            return '<span>'+obj.serial_number+'</span>';
                        }
                    }
                }, {
                    title: '时间',
                    dataIndex: 'record_time', 'sortable': false,
                    width: 150
                }, {
                    title: '分销商名称',
                    dataIndex: 'custom_name', 'sortable': false,
                    width: 100
                },{
                    title: '摘要',
                    dataIndex: 'abstract_name', 'sortable': false,
                    width: 100
                }, {
                    title: '金额',
                    dataIndex: 'money', 'sortable': false,
                    width: 100
                }, {
                    title: '支付方式',
                    dataIndex: 'pay_type', 'sortable': false,
                    width: 100
                }, {
                    title: '收款账户',
                    dataIndex: 'account_name', 'sortable': false,
                    width: 150
                }, {
                    title: '备注',
                    dataIndex: 'remark', 'sortable': false,
                    width: 200
                }, {
                    title: '操作人',
                    dataIndex: 'operator', 'sortable': false,
                    width: 150
                }],
                    // 实例化 Grid.Plugins.Cascade 插件
                    cascade = new Grid.Plugins.Cascade({
                        renderer: function (record) {
                            return '<div style="padding: 5px 10px;"><h2>' + record.abstract_name + '</h2> <table><tr><td class = "title_td">扣减账户：</td><td class = "data_td">' + record.capital_account + '</td><td class = "title_td">日期：</td><td class = "data_td">' + record.record_time + '</td><td class = "title_td">金额：</td><td class = "data_td">' + record.money + '</td><td class = "title_td">支付方式：</td><td class = "data_td">' + record.pay_type + '</td></tr><tr><td class = "title_td">流水号：</td><td class = "data_td">' + record.serial_number + '</td><td class = "title_td">单据编号：</td><td class = "data_td">' + record.record_code + '</td><td class = "title_td">备注：</td><td class = "data_td">' + record.remark + '</td><td class = "title_td">操作人：</td><td class = "data_td">' + record.operator + '</td></tr></table></div>';
                        }
                    });

            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                emptyDataTpl : '<div class="centered">查询的数据不存在</div>',
                idField: 'account_code',
                store: skuSelectorStore,
                plugins: [cascade],	// Grid.Plugins.Cascade 插件
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
           $('.bui-grid-table:first tr:first th:first .bui-grid-hd-title').html('<i class="bui-grid-cascade-icon" id="expand_list"></i>');
            $('.bui-grid-table:first tr:first th:first .bui-grid-hd-title').click(function() {
                var tableStore_data = skuSelectorStore.getResult();
                if(tableStore_data.length == 0){
                    BUI.Message.Alert("未查询或查询数据不存在，无法展开！","error");
                        return;
                    }
                    if($(this).hasClass('bui-grid-cascade-expand')){
                        $(this).removeClass('bui-grid-cascade-expand');
                        cascade.collapseAll();
                    }else  {
                        cascade.expandAll();
                        $(this).addClass('bui-grid-cascade-expand');
                    }
            });
            //绑定加载事件
            skuSelectorStore.on('load',function(){
                if($('.bui-grid-table:first tr:first th:first .bui-grid-hd-title').hasClass('bui-grid-cascade-expand')){
                    $('.bui-grid-table:first tr:first th:first .bui-grid-hd-title').removeClass('bui-grid-cascade-expand');
                    cascade.collapseAll();
                }
                
            });
        });
        $('#btn-search').click(function () {
            select_change_goods();
        })
    })
    function select_change_goods() {
        var keyword_type = $("#keyword_type").val();
        var p_code = $("#custom_code").val();
        var abstract = $("#abstract").val();
        var keyword = $("#keyword").val();
        var income_account = $("#income_account").val();
        var record_time_start = $("#record_time_start").val();
        var record_time_end = $("#record_time_end").val();

        var obj = {keyword_type: keyword_type, record_time_start: record_time_start, record_time_end: record_time_end, keyword: keyword, custom_code: p_code, abstract: abstract, income_account: income_account};
        skuSelectorStore.load(obj);
    }
    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function (value) {
            var custom_code = [];
            var custom_name = [];
            $.each(value, function (i, v) {
                custom_code.push(v['custom_code']);
                custom_name.push(v['custom_name']);
            });
            $('#custom_code_select_pop').val(custom_name.join());
            $('#custom_code').val(custom_code.join());
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };
    function cancellation(id) {
        BUI.Message.Confirm('确认要作废吗？',function(){
            setTimeout(function(){
                 $.ajax({type: 'POST', dataType: 'json',
                    url: '<?php echo get_app_url('fx/balance_of_payments/cancellation'); ?>', data: {id: id},
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
        },'question');
    }
    function do_delete(_id) {
        BUI.Message.Confirm('确认要删除吗？',function(){
            setTimeout(function(){
                 $.ajax({type: 'POST', dataType: 'json',
                    url: '<?php echo get_app_url('fx/balance_of_payments/do_delete'); ?>', data: {id: _id},
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
        },'question');
    }
    
    $('#exprot_list').click(function(){
//        var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
        params = skuSelectorStore.get('params');
        params.ctl_dataset = "fx/BalanceOfPaymentsModel::get_by_account";
        params.ctl_type = 'export';
        params.ctl_export_conf = 'fx_balance_payment';
        params.ctl_export_name =  '收支明细';
        <?php echo   create_export_token_js('fx/BalanceOfPaymentsModel::get_by_account');?>
//        params.ctl_export_token =  'c9453c5e7e15b9d966e5b8febb858faf';
//        params.ctl_export_time =  '1495417944';
        
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  } 
          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          //params.ctl_type = 'view';
          window.open(url); 
       // window.location.href = url;
    });

</script>




