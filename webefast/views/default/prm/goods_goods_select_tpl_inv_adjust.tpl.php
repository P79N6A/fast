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
<div class="row">
    <div class="pull-left" style="width: 15%;">
        <h3>商品分类</h3>
        <div id="cateTree" style="overflow-y: auto;"></div>
    </div>
    <div class="pull-left" style="width: 85%;">
        <div class="well" style="margin-left: 1em;">
            <form id="skuSearchForm" name="skuSearchForm">
                <table class="table table-condensed" style="width: 100%;">
                    <tr>
                        <th>仓库</th>
                        <td>
                            <?php if (!empty($request['store_code'])): ?>
                                <input name="store_name" type="text" value="<?php echo $response['store_name']; ?>"
                                       disabled ="disabled"/>
                                <input name="store_code" type="hidden"
                                       value="<?php echo $request['store_code']; ?>"/>

                            <?php else: ?>
                                <div id="store_code_select" class="controls bui-form-field-select"
                                     data-items='<?php echo $response['selection']['store']; ?>'>
                                    <input name="store_code" type="hidden"
                                           value="<?php echo $request['store_code']; ?>"/>
                                </div>

                            <?php endif; ?>
                        </td>

                        <th>品牌</th>
                        <td colspan="1">
                            <div class="controls bui-form-field-select"
                                 data-items='<?php echo $response['selection']['brand']; ?>'
                                 data-select="{multipleSelect:true}">
                                <input name="brand_code" type="hidden" value="">
                            </div>
                        </td>
                        <th>年份</th>
                        <td>
                            <div class="controls bui-form-field-select"
                                 data-items='<?php echo $response['selection']['year']; ?>'
                                 data-select="{multipleSelect:true}">
                                <input name="year_code" type="hidden" value="">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>季节</th>
                        <td>
                            <div class="controls bui-form-field-select"
                                 data-items='<?php echo $response['selection']['season']; ?>'
                                 data-select="{multipleSelect:true}">
                                <input name="season_code" type="hidden" value="">
                            </div>
                        </td>
                        <th>商品编码/名称</th>
                        <td colspan="1"><input type="text" class="input" placeholder="编码/名称/简称" name="goods_code" id="goods_code" value="" />
                            <input type="hidden" name="type" value="<?php
                            if (!empty($response['type'])) {
                                echo $response['type'];
                            } else {
                                echo 'normal';
                            }
                            ?>"/></td>
                        <th>商品条形码</th>
                        <td colspan="3"><input type="text" class="input" placeholder="商品条码" name="barcode"/></td>
                       <!--   <td colspan="1">
                            <input type="hidden" value="" name="category_code" id="category_code"/>
                            <input type="submit" value="查询" class="button button-primary" id="skuSearchFormSubmit"/>
                            <input type="reset" value="重置" class="button"/>
                        </td>-->
                    </tr>

                    <tr>
                        <th>组装商品</th>
                        <td>
                            <select  class="bui-form-field-select bui-form-field" aria-disabled="false" aria-pressed="false"
                                     <?php if ($request['diy'] != ''): ?>  id="diy_show"  name="diy_show"   disabled="disabled"
                                     <?php else: ?> id="diy"  name="diy"    <?php endif; ?>  >
                                <option  value="">请选择</option>
                                <option  value="0" <?php if ($request['diy'] == '0'): ?> selected="selected" <?php endif; ?>>否</option>
                                <option value="1" <?php if ($request['diy'] == '1'): ?> selected="selected" <?php endif; ?>>是</option>
                            </select>
                            <?php if ($request['diy'] != ''): ?>
                                <input name="diy" id="diy" type="hidden" value="<?php echo $request['diy']; ?>">
                            <?php endif; ?>
                            </select>
                        </td>
                        <?php if ($response['lof'] == 1 && $request['model'] == 'notice_record'): ?>
                            <th>批次号</th>
                            <td><input type="text" class="input" placeholder="批次号" name="lof_no"/></td>
                            <th>失效日期</th>
                            <td><input type="text" class="input" placeholder="失效日期" value="" name="invalid_time" class="calendar"/></td>
                        <?php endif; ?>
                    </tr>
                    <?php if ($response['lof'] == 1 && $request['model'] == 'notice_record'): ?>
                        <th>剩余可卖月</th>
                        <td><input type="text" class="input" placeholder="剩余可卖月数" name="available_month"/></td>
                        <th>有效期</th>
                        <td><input type="text" class="input" placeholder="有效期/月数" name="period_validity"/></td>
                    <?php endif; ?>
                    <tr>
                        <td colspan="8" style='text-align:center;'>

                            <input type="hidden" value="" name="category_code" id="category_code"/>

                            <input type="hidden" value="<?php echo isset($request['is_combo']) ? $request['is_combo'] : ''; ?>" name="is_combo" id="is_combo"/>

                            <input type="hidden" value="<?php echo isset($request['lof_status']) ? $request['lof_status'] : ''; ?>" name="lof_status" id="lof_status"/>
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
    var skuSelectorStore;
    var formListeners = {'beforesubmit': []};
    var save_up;
    var page_size = 10;
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: true,
            autoRender: true
        });
    });
    $(function () {
        //左侧目录树+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        BUI.use(['bui/tree', 'bui/data'], function (Tree, Data) {
            var store = new Data.TreeStore({
                root: {
                    id: '0',
                    text: '全部'
                },
                url: '<?php echo get_app_url('prm/category/get_nodes&app_fmt=json'); ?>'
            }),
                    tree = new Tree.TreeList({
                        render: '#cateTree',
                        checkType: 'all', //checkType:勾选模式，提供了4中，all,onlyLeaf,none,custom
                        store: store
                                //  showRoot : true //可以不配置，则不显示根节点
                    });
            tree.render();
            store.load({id: '0'});//加载根节点，也可以让用户点击加载
            tree.on('checkedchange', function (ev) {
                var checkedNodes = tree.getCheckedNodes();
                var str = '';
                BUI.each(checkedNodes, function (node) {
                    str += node.id + ',';
                });
                $('#category_code').val(str);
            });
        });
        BUI.use('bui/form', function (Form) {
            form = new BUI.Form.HForm({
                srcNode: '#skuSearchForm'
            }).render();
            form.on('beforesubmit', function (ev) {
                for (var i = 0; i < formListeners['beforesubmit'].length; i++) {
                    formListeners['beforesubmit'][i](ev);
                }
                return false;
            });
        });

        //右下方结果表格++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        BUI.use(['bui/grid', 'bui/data', 'bui/form', 'bui/tooltip'], function (Grid, Data, Form, Tooltip) {
            //数据变量---------------------------------------------------------------
            var grid = new Grid.Grid();
            skuSelectorStore = new Data.Store({
                url: '?app_act=prm/goods/goods_select_action_inv_adjust&is_entity=<?php echo $request['is_entity'] ?>' + "&record_code=<?php echo $response['record_code']; ?>" + "&type=<?php echo $response['type']; ?>&custom_code=<?php echo urlencode($response['custom_code']); ?>",
                autoLoad: false, //自动加载数据
                autoSync: true,
                pageSize: page_size	// 配置分页数目
            });

            formListeners['beforesubmit'].push(function (ev) {
                var obj = form.serializeToObject();
                obj.start = 1; //返回第一页
                obj.page = 1;
                obj.pageIndex = 0;
                $('table_datatable .bui-pb-page').val(1);
                var _pageSize = $('.bui_page_select').val();
                obj.limit = _pageSize;
                obj.page_size = _pageSize;
                obj.pageSize = _pageSize;
                skuSelectorStore.load(obj, function (data, params) {
                    $('.bui_page_select').val(_pageSize);
                });
            });
            save_up = function () {
                var obj = form.serializeToObject();
                obj.page = $('#result_grid .bui-pb-page').val();
                obj.pageIndex = obj.page - 1;
                skuSelectorStore.load(obj);
            };
            var columns = [];
<?php if (isset($response['lof']) && $response['lof'] == '1'): ?>
                columns = [
                    {title: '商品名称', dataIndex: 'goods_name', width: 100, 'sortable': false, renderer: function (value, obj) {
                            if (value.length < 7) {
                                return value;
                            } else {
                                var newobj = {'name': value};
                                var objStr = BUI.JSON.stringify(newobj).replace(/\"/g, "'");
                                return '<span class="grid-goods_name" data-title="' + objStr + '">' + value.substr(0, 7) + '</span>';
                            }
                        }},
                    {title: '商品编码', dataIndex: 'goods_code', width: 100, 'sortable': false},
                    {title: 'spec1_code', dataIndex: 'spec1_code', visible: false, width: 50, 'sortable': false},
                    {title: '<?php echo $response['goods_spec1_rename']; ?>', dataIndex: 'spec1_name', width: 65, 'sortable': false},
                    {title: 'spec2_code', dataIndex: 'spec2_code', visible: false, width: 50},
                    {title: '<?php echo $response['goods_spec2_rename']; ?>', dataIndex: 'spec2_name', width: 65, 'sortable': false},
                    {title: '批次号', dataIndex: 'lof_no', width: 82, 'sortable': false},
                    {title: '生产日期', dataIndex: 'production_date', width: 95, 'sortable': false},
                    {title: '数量', dataIndex: 'num', width: 108, 'sortable': false, renderer: function (value, obj) {
                            return '<input type="text" class="input-small" name="num_' + obj.goods_inv_id + '"   data-rules="{number:true}"  value=""/>';
                        }},
                    {title: '可用库存', dataIndex: 'available_mum', width: 65, 'sortable': false},
                    {title: '实物库存', dataIndex: 'stock_num', width: 65, 'sortable': false},
                    {title: '商品条形码', dataIndex: 'barcode', width: 80, 'sortable': false},
                ];
<?php else: ?>
                columns = [
                    {title: '商品名称', dataIndex: 'goods_name', width: 120, 'sortable': false, renderer: function (value, obj) {
                            if (value.length < 10) {
                                return value;
                            } else {
                                var newobj = {'name': value};
                                var objStr = BUI.JSON.stringify(newobj).replace(/\"/g, "'");
                                return '<span class="grid-goods_name" data-title="' + objStr + '">' + value.substr(0, 10) + '</span>';
                            }
                        }},
                    {title: '商品编码', dataIndex: 'goods_code', width: 120, 'sortable': false},
                    {title: 'spec1_code', dataIndex: 'spec1_code', visible: false, width: 50, 'sortable': false},
                    {title: '<?php echo $response['goods_spec1_rename']; ?>', dataIndex: 'spec1_name', width: 80, 'sortable': false},
                    {title: 'spec2_code', dataIndex: 'spec2_code', visible: false, width: 50, 'sortable': false},
                    {title: '<?php echo $response['goods_spec2_rename']; ?>', dataIndex: 'spec2_name', width: 80, 'sortable': false},
                    {title: '数量', dataIndex: 'num', width: 95, 'sortable': false, renderer: function (value, obj) {
                            return '<input type="text" class="input-small" name="num_' + obj.goods_inv_id + '"   data-rules="{number:true}"  value=""/>';

                        }},
                    {title: '可用库存', dataIndex: 'available_mum', width: 80, 'sortable': false},
                    {title: '实物库存', dataIndex: 'stock_num', width: 80, 'sortable': false},
                    {title: '商品条形码', dataIndex: 'barcode', width: 120, 'sortable': false}
                ];
<?php endif; ?>
            editing = new Grid.Plugins.CellEditing({
                triggerSelected: false //触发编辑的时候不选中行
            });
            // 单元格编辑输入回车或者选择通过验证之后会触发此事件。
            editing.on('accept', function (record, editor) {
                var value = record.record.lof_no;
                var production_date = record.record.production_date;
                var field = $("#djfield").val();
                if (field == 'lof_no') {
                    if (value != '') {
                        $.ajax({type: 'GET', dataType: 'json',
                            url: '<?php echo get_app_url('prm/goods/lof_exist'); ?>',
                            data: {production_date: production_date, lof_no: value},
                            success: function (ret) {
                                if (ret.status == 1) {
                                    record.record.production_date = ret.data;
                                }
                                if (ret.status == 3) {
                                    BUI.Message.Alert(ret.message, 'error');
                                }
                            }
                        });
                    }
                }
            });
            grid = new Grid.Grid({
                render: '#result_grid',
                width: '100%', //如果表格使用百分比，这个属性一定要设置
                height: 352,

                columns: columns,
                idField: 'goods_code',
                store: skuSelectorStore,
                // bbar: {pagingBar: true},
                plugins: [editing, Grid.Plugins.ColumnResize]
            });
            grid.on('cellclick', function (record, field) {
                $("#djfield").val(record.field);
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
            var errorTpl = '<span class="x-icon x-icon-small x-icon-error" data-title="{error}">!</span>';
            var addPersonGroup = new Form.Group({//创建表单分组，此分组不在表单form对象中，所以不影响校验
                srcNode: grid.get('el'),
                elCls: '',
                //  errorTpl : errorTpl,
                showError: false,
                defaultChildCfg: {
                    elCls: ''
                }
            });
            addPersonGroup.render();
            grid.on('itemrendered', function (ev) {
                itemEl = $(ev.element);
                var input = itemEl.find('input');
                addPersonGroup.addChild({
                    xclass: 'form-field',
                    errorTpl: errorTpl,
                    srcNode: input
                });
            });
            grid.render();
            grid.on('aftershow', function (ev) {
                var tips = new Tooltip.Tips({
                    tip: {
                        trigger: '.grid-goods_name', //出现此样式的元素显示tip
                        alignType: 'top', //默认方向
                        elCls: 'panel',
                        width: 200,
                        zIndex: '1000000',
                        titleTpl: ' <div class="panel-body">{name}</div>',
                        offset: 10
                    }
                });
                tips.render();
            });
        });
    });
</script>
