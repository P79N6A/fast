<style>
    .panel-body{padding:0px;}
    .panel{width:680px;}
    #panel_order .span11{
        width:400px;
        margin:0px;
    }
    .bui-grid-header{ border-bottom:1px solid #dddddd;}    
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    .bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
    .bui-grid-bbar{ border:none;}
    .bui-select-list{
        overflow: auto;
        overflow-x: auto;
        max-height: 150px;
        _height : 300px;
    }
</style>


<div class="panel">
    <form>
        <div class="panel-body" id="panel_order">
            <table cellspacing="0" class="table table-bordered" id="table1">
                <tbody>
                    <tr>
                        <td colspan="2">当前需修改的商品:<b><?php echo $response['cur_goods']['goods_name'] ?></b>[<?php echo $response['cur_goods']['goods_code'] ?>] &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  系统规格:规格1:<?php echo $response['cur_goods']['spec1_name']; ?>;规格2:<?php echo $response['cur_goods']['spec2_name'] ?>;</td>
                    </tr>
                    <tr>
                        <td>选择换货商品</td>
                        <td>
                            <input type='text' value=''  class="span11" name='select_goods' id='select_goods' placeholder="支持商品名称，商品编码，商品条形码查询">
                            <input type="button" class="button" id="btn-search" onclick="search_change_goods()" value="查询" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div  id="result_grid" class="panel-body">

        </div>
        <div class="clearfix" style="text-align:right;margin-top:50px;display:none;" id="save_change">
            <input class="button button-primary" onclick="save_change_goods()" value="保存并退出" />
        </div>
    </form>
</div>

<script>
    var skuSelectorStore;
    $(function () {
        //右下方结果表格
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量
            var grid = new Grid.Grid();
            var select_goods = $("#select_goods").val();
            skuSelectorStore = new Data.Store({
                url: '?app_act=oms_shop/oms_shop/search_change_goods',
                autoLoad: false, //自动加载数据
                autoSync: true,
                pageSize: 10	// 配置分页数目
            });
            var columns = [{title: '', dataIndex: '', width: 60, 'sortable': false, renderer: function (value, obj) {
                        return '<input type="radio" class="input-small" name="change_sku"  value="' + obj.sku + '"/>';
                    }},
                {title: '数量', dataIndex: 'num', width: 100, 'sortable': false, renderer: function (value, obj) {
                        return '<input type="text" class="input-small" id="' + obj.sku + '"   data-rules="{number:true,min:1}"  value="<?php echo $response['cur_goods']['num'] ?>"/>';
                    }},
                {title: '商品编码', dataIndex: 'goods_code', width: 100, 'sortable': false},
                {title: '规格1', dataIndex: 'spec1_name', width: 100, 'sortable': false},
                {title: '规格2', dataIndex: 'spec2_name', width: 100, 'sortable': false},
                {title: '商品条形码', dataIndex: 'barcode', width: 120, 'sortable': false},
                {title: '可用库存', dataIndex: 'available_num', width: 80, 'sortable': false}];
            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                idField: 'goods_code',
                store: skuSelectorStore
            });
            grid.render();
        });
        search_change_goods(1);
    });

    function search_change_goods(val) {
        var select_goods;
        if (val == 1) {
            select_goods = '<?php echo $response['cur_goods']['goods_code'] ?>';
        } else {
            select_goods = $("#select_goods").val();
        }
        var store_code = '<?php echo $request['store_code']; ?>';
        if (select_goods == "") {
            BUI.Message.Alert("请输入换货商品名称、编码、条码后点击查询", 'error');
            return false;
        }
        var obj = {goods_multi: select_goods, store_code: store_code};
        $("#save_change").css('display', '');
        skuSelectorStore.load(obj);

    }
//保存换货商品
    function save_change_goods() {
        var boolCheck = $('input:radio[name="change_sku"]').is(":checked");
        if (!boolCheck) {
            BUI.Message.Alert("请勾选一个换货商品", 'error');
            return false;
        }
        var change_sku = $("input:radio:checked[name='change_sku']").val();
        var num = $(document.getElementById(change_sku)).val();
        var record_code = '<?php echo $response['cur_goods']['record_code']; ?>';
        if ($("#select_goods").val()) {
            goods_code = $("#select_goods").val();
        } else {
            goods_code = '<?php echo $response['cur_goods']['goods_code']; ?>';
        }
        var sell_goods_id = '<?php echo $response['cur_goods']['sell_goods_id']; ?>';
        var avg_money = '<?php echo $response['cur_goods']['avg_money']; ?>';
        var barcode = '<?php echo $response['cur_goods']['barcode']; ?>';
        var url = "?app_fmt=json&app_act=oms_shop/oms_shop/opt_change_detail";
        var data = {sku: change_sku, record_code: record_code, num: num, goods_code: goods_code, sell_goods_id: sell_goods_id, avg_money: avg_money, barcode: barcode}
        $.post(url, data, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (type == 'error') {
                BUI.Message.Alert(data.message, 'error');
            } else {
                BUI.Message.Alert(data.message, function () {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
                }, type)
            }
        }, 'json');
    }

</script>
