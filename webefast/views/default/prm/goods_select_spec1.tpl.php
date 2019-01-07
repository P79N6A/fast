<style>
    .panel-body{padding:0px;}
    .panel{width:620px;}
    #panel_order .span11{
        width:300px;
        margin:0px;
    }
    .bui-grid-header{ border-bottom:1px solid #dddddd;}    
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    .bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
</style>

<div class="panel">
    <form>
            <div class="panel-body" id="panel_order">
                <table cellspacing="0" class="table table-bordered" id="table1">
                    <tbody>
                        <tr>
                            <td>
                                <span>名称/代码</span>
                            </td>
                            <td>
                                <input type="text" id="code_name" class="span11" >
                                <button type="button" class="button " id="btn-search" onclick="select_change_goods()">查询</button>            
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div  id="result_grid" class="panel-body"></div> 
            <div id="result_grid_pager"></div>
            <div style="text-align:right;margin-top:30px;" id="save_change">
                <button type="button" id='enter' class="button button-primary" >确定</button>
                <button type="button" id='cancel' class="button">取消</button>
            </div>
    </form>
</div>
<script type="text/javascript">
    var skuSelectorStore;
    var page_size = 10;
    var save_up;
    $(function () {
        $('#cancel').click(function(){
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
        })
        $("#enter").click(function () {
            var rows = $("input[name = 'spec1[]']:checked").map(function (index, elem) {
                return $(elem).val();
            }).get();
            var ids = new Array();
            if (rows.length == 0) {
                BUI.Message.Alert("请选择规格", 'error');
                return;
            }
            for (var i in rows) {
                ids[i] = new Array();
                var row = rows[i].split(",")
                ids[i].push(row[0], row[1]);
            }
            parent.add_spec1(ids);
            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
        })
        //获取以选择的规格
        var code_list = "<?php echo $request['spec1_code_list']?>";
        //下方结果表格
        BUI.use(['bui/grid', 'bui/data', 'bui/form','bui/tooltip'], function (Grid, Data, Form,Tooltip) {
            //数据变量
            var grid = new Grid.Grid();
            skuSelectorStore = new Data.Store({
                url: '?app_act=prm/goods/search_change_spec1&spec1_code_list='+code_list,
                autoLoad: true, //自动加载数据
                autoSync: true,
                pageSize : page_size,
            });
            var columns = [{title: '', dataIndex: '', width: 60, 'sortable': false, renderer: function (value, obj) {
                        return '<input type="checkbox" class="input-small" name="spec1[]"  value="' + obj.spec1_code + ',' + obj.spec1_name + '"/>';
                    }},
                {title: '代码', dataIndex: 'spec1_code', width: 200, 'sortable': false},
                {title: '名称', dataIndex: 'spec1_name', width: 200, 'sortable': false},
                {title: '描述', dataIndex: 'remark', width: 150, 'sortable': false}];
            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                idField: 'spec1_code',
                store: skuSelectorStore,
            });
            var pagingBar = BUI.Toolbar.PagingBar;
            var gridPage = new pagingBar({
                render : '#result_grid_pager',
                elCls : 'image-pbar pull-right',
                store : skuSelectorStore,
                totalCountTpl : ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_select" style="width:50px;height:20px;"><option  value="5" >5</option><option selected="selected" value="10" >10</option><option  value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 '
            });
            gridPage.render();
            
            $('.bui_page_select').live('change',function(){
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
        })
    })
    function select_change_goods() {
        var code_name;
        code_name = $("#code_name").val();
//	if (code_name == ""){
//		BUI.Message.Alert("请输入名称或代码后点击查询", 'error');
//		return false;
//	} 
        var obj = {code_name: code_name};
        skuSelectorStore.load(obj);
    }
</script>