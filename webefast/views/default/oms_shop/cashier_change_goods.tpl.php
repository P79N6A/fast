<style>
    .panel-body{padding:0px;}
    /*.panel{width:680px;}*/
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
                        <td colspan="2">当前需修改的商品：<b><?php echo $request['goods_name'] ?></b>【<?php echo $request['goods_code'] ?>】 &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  当前规格【<?php echo $response['spec']['goods_spec1'] . '：' . $request['spec1_name']; ?>&nbsp;&nbsp;<?php echo $response['spec']['goods_spec2'] . '：' . $request['spec2_name'] ?>】</td>
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
        <div style="text-align:right;margin-top:30px;" id="save_change">
            <button type="button" id='enter' class="button button-primary" >确定</button>
            <button type="button" id='cancel' class="button">取消</button>
        </div>
    </form>
</div>

<script>
    var skuSelectorStore;
    var grid;
    var goods_code = "<?php echo $request['goods_code'] ?>";
    $(function () {
        $('#cancel').click(function () {
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
        });
        $("#enter").click(function () {
            var boolCheck = $('input:radio[name="change_sku"]').is(":checked");
            if (!boolCheck) {
                BUI.Message.Alert("请勾选一个换货商品", 'error');
                return false;
            }
            var change_sku = $("input:radio:checked[name='change_sku']").val();
            var goods_info = grid.getSelection();
            var num = $(document.getElementById(goods_info[0].sku)).val();
            goods_info[0].num = num;
            parent.goods_info = goods_info;
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
        });

        //右下方结果表格
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量
            grid = new Grid.Grid();
            skuSelectorStore = new Data.Store({
                url: '?app_act=oms_shop/cashier/get_change_goods',
                autoLoad: false, //自动加载数据
                autoSync: true,
                pageSize: 10	// 配置分页数目
            });
            var columns = [{title: '', dataIndex: '', width: 60, 'sortable': false, renderer: function (value, obj) {
                        return '<input type="radio" class="input-small" name="change_sku" value="' + obj.sku + '"/>';
                    }},
                {title: '数量', dataIndex: 'num', width: 100, 'sortable': false, renderer: function (value, obj) {
                        return '<input type="text" class="input-small" id="' + obj.sku + '" data-rules="{number:true,min:1}"  value=""/>';
                    }},
                {title: '商品名称', dataIndex: 'goods_name', width: 150, 'sortable': false},
                {title: '商品编码', dataIndex: 'goods_code', width: 100, 'sortable': false},
                {title: '规格1', dataIndex: 'spec1_name', width: 80, 'sortable': false},
                {title: '规格2', dataIndex: 'spec2_name', width: 80, 'sortable': false},
                {title: '商品条形码', dataIndex: 'barcode', width: 130, 'sortable': false},
                {title: '可用库存', dataIndex: 'available_num', width: 60, 'sortable': false}];
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
            select_goods = goods_code;
        } else {
            select_goods = $("#select_goods").val();
        }
        if (select_goods == "") {
            BUI.Message.Alert("请输入换货商品名称、编码、条码后点击查询", 'error');
            return false;
        }
        var obj = {goods_multi: select_goods};
        $("#save_change").css('display', '');
        skuSelectorStore.load(obj);
    }
</script>
