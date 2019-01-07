<style>
    #skuSearchForm{ padding:0;}
    #skuSearchForm .table th,
    #skuSearchForm .table td{ border:none;}
    #skuSearchForm .controls{ margin-left:0;}
    .bui-grid-header{ border-bottom:1px solid #dddddd;}
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    .bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
    .bui-grid-bbar{ border:none;}


    .table{ margin-bottom:0;}
    .bui-dialog a.bui-ext-close{ top:10px;}
    .bui-dialog .bui-stdmod-header {padding:10px 15px;}
    .bui-dialog .bui-stdmod-body {padding: 5px 15px;}
    .bui-dialog .bui-stdmod-footer {padding:10px 15px;}
    .form-horizontal .controls{ margin-top:0;}
    .bui-grid-table .bui-grid-cell-inner{ padding:1px 0;}

</style>
<?php //$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2)); ?>
<div class="row">
    <div class="pull-left" style="width: 98%;">
        <div class="well" style="margin-left: 1em;">
            <form id="skuSearchForm" name="skuSearchForm" >
                <table class="table table-condensed" style="width: 80%;">
                    <tr>
                        <input type="hidden" name="store_code" value=" ">
                        <td style=" width: 20%" >
                            <select  id="keyword_type" name="keyword_type" class="field" style=" width: 100%">
                                <option value="barcode" >商品条形码</option>
                                <option value="goods_code" >商品编码</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" value="" name="keyword" placeholder="支持模糊查询"/>
                        </td>
                        <td style='text-align:center;'>
                            <input type="submit" value="查询" class="button button-primary" id="skuSearchFormSubmit"/>
                            <input type="reset" value="重置" class="button"/>


                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <input type="hidden" id="djfield" name="djfield" >

        <div id="result_datatable" class="row" style="margin-left: 1em;" >

            <div id="result_grid" style="position:relative">
            </div>
            <div id="result_grid_pager"></div>
        </div>

    </div>

</div>

<script type="text/javascript">
    var form;
    var skuSelectorStore, SelectoGrid;
    var formListeners = {'beforesubmit': []};
    var save_up;
    var page_size = 10;
    $(function () {
        BUI.use('bui/form', function (Form) {
            form = new BUI.Form.HForm({
                srcNode : '#skuSearchForm'
            }).render();
            form.on('beforesubmit', function(ev) {
                for (var i = 0; i < formListeners['beforesubmit'].length; i++) {
                    formListeners['beforesubmit'][i](ev);
                }
                return false;
            });
        });
//        $(".bui-grid-table").bind("dblclick", function () {
//            $(this).html($(this).html() +" : "+ $("#selScore").val());
//        });

        //右下方结果表格++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量---------------------------------------------------------------
            var myGrid = new Grid.Grid();
            skuSelectorStore = new Data.Store({
                url : '?app_act=prm/goods/serach_barcode_action',
                autoLoad:false, //自动加载数据
                autoSync: true,
                pageSize:page_size	// 配置分页数目
            });
            formListeners['beforesubmit'].push(function(ev) {
                var obj = form.serializeToObject();
                obj.start = 1; //返回第一页
                obj.page = 1; obj.pageIndex = 0;
                $('table_datatable .bui-pb-page').val(1);
                var _pageSize = $('.bui_page_select').val();
                obj.limit = _pageSize; obj.page_size = _pageSize; obj.pageSize = _pageSize;
                skuSelectorStore.load(obj, function (data, params) {
                    $('.bui_page_select').val(_pageSize);
                });
            });
            save_up = function(){
                var obj = form.serializeToObject();
                obj.page = $('#result_grid .bui-pb-page').val();
                obj.pageIndex = obj.page - 1;
                skuSelectorStore.load(obj);
            }

            //----------------------------------------------------------------------
            //渲染结果列表或刷新结果列表
            //  var reloadGrid = function (grid, data) {
            var columns = [
                {title: '', dataIndex: '',width: 60,'sortable':false, renderer : function(value,obj){
                    return '<input type="radio" class="input-small" name="change_sku"  value="'+obj.barcode+'"/>';

                }},
                {title: '商品编码', dataIndex: 'goods_code', width: 150, 'sortable':false},
                {title: '商品条形码', dataIndex: 'barcode', width: 150, 'sortable':false},
                {title: '国标码', dataIndex: 'gb_code', width: 150, 'sortable':false},
                {title: '商品名称', dataIndex: 'goods_name', width: 120, 'sortable':false},
                {title: '系统规格1', dataIndex: 'spec1_name', width: 100, 'sortable':false},
                {title: '系统规格2', dataIndex: 'spec2_name', width: 100, 'sortable':false},
            ];
            editing = Grid.Plugins.CheckSelection;

            grid = new Grid.Grid({
                render: '#result_grid',
                width: '100%', //如果表格使用百分比，这个属性一定要设置
                height: 352,
                //forceFit : true,
                columns: columns,
                idField: 'goods_code',
                // store: skuSelectorStore,
                store: skuSelectorStore,
                //bbar: {pagingBar: true},
                useEmptyCell:true,
//                plugins: [editing]
            });
            grid.on('cellclick', function(record, field) {
                $("#djfield").val(record.field);
            });
            grid.render();
            var pagingBar = BUI.Toolbar.PagingBar;
            var gridPage = new pagingBar({
                render : '#result_grid_pager',
                elCls : 'image-pbar pull-right',
                store : skuSelectorStore,
                totalCountTpl : ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_select" style="width:50px;height:20px;"><option  value="5" >5</option><option selected="selected" value="10" >10</option><option  value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 ',
            });
            gridPage.render();
            $('.bui_page_select').live('change', function(){
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
            var errorTpl = '<span class="x-icon x-icon-small x-icon-error" data-title="{error}">!</span>';
            var addPersonGroup = new Form.Group({ //创建表单分组，此分组不在表单form对象中，所以不影响校验
                srcNode : grid.get('el'),
                elCls:'',
                //  errorTpl : errorTpl,
                showError : false,
                defaultChildCfg : {
                    elCls : ''
                }
            });
            addPersonGroup.render();
            grid.on('itemrendered', function(ev){
                itemEl = $(ev.element);
                var input = itemEl.find('.input_num');
                addPersonGroup.addChild({
                    xclass : 'form-field',
                    errorTpl : errorTpl,
                    srcNode : input
                });
            });
            grid.on('aftershow', function(ev){
                BUI.use('bui/calendar', function(Calendar){
                    var datepicker = new Calendar.DatePicker({
                        trigger:'.calendar',
                        //delegateTrigger : true, //如果设置此参数，那么新增加的.calendar元素也会支持日历选择
                        autoRender : true
                    });
                });
                var tips = new Tooltip.Tips({
                    tip : {
                        trigger :'.grid-goods_name', //出现此样式的元素显示tip
                        alignType : 'top', //默认方向
                        elCls : 'panel',
                        width: 200,
                        zIndex : '1000000',
                        titleTpl : ' <div class="panel-body">{name}</div>',
                        offset : 10
                    }
                });
                tips.render();
                //回车切换
                $('#result_grid input[type="text"]').keydown(function(event){
                    if (event.keyCode == 13){
                        var inputs = $('#result_grid input[type="text"]')
                        var idx = inputs.index(this);
                        if (idx < inputs.length - 1){
                            inputs[idx + 1].focus();
                        }
                    }
                });
            });
            SelectoGrid = grid;
        });
    });


</script>