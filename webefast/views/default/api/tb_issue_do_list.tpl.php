<style>
    .bui-grid{
        border-bottom: 1px solid #dddddd;
    }
    .simulate span.bui-grid-cell-text{
        cursor:pointer;
        text-decoration:underline;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '宝贝列表',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'shop_code', 'type' => 'select', 'data' => $response['tb_shop'], 'value' => 'shopping_attb'),
            'type' => 'input',
            'title' => '宝贝标题/编码',
            'id' => 'code_name',
        ),
    )
));
?>
<div class="row" style="margin-top: -10px;">
    <div class="span6" >
        <div id="shelf_status">

        </div>
    </div>
</div>
<div>
    <div id="grid" style="margin-bottom: 5px;"></div>
    <div id="table_pager"></div>
</div>

<script type="text/javascript">
    var form;
    var tableStore, tableGrid;
    var formListeners = {'beforesubmit': []};
    var init_data;
    var page_size = 20;
    var shop_code = $('#searchForm #shop_code').val();
    $(function () {
        //获取当前选择店铺
        $('#searchForm #shop_code').on('change', function () {
            shop_code = $(this).val();
            init_data();
        });
        BUI.use('bui/form', function (Form) {
            form = new BUI.Form.HForm({
                srcNode: '#searchForm'
            }).render();
            form.on('beforesubmit', function (ev) {
                for (var i = 0; i < formListeners['beforesubmit'].length; i++) {
                    formListeners['beforesubmit'][i](ev);
                }
                return false;
            });
        });
        var tableCascadeTable = new BUI.Grid.Plugins.Cascade({
            renderer: function (row) {
                return '<div class="inner-grid"></div>';
            }
        });
        var tableCascadeCellEditing = new BUI.Grid.Plugins.CellEditing({
            triggerSelected: false //触发编辑的时候不选中行
        });
        var tableCascadeTableColumns = [
            {title: '商品编码', dataIndex: 'goods_code', width: '18%'},
            {title: 'SKU条码', dataIndex: 'sku_barcode', width: '18%'},
            {title: '颜色', dataIndex: 'spec1_name', width: '15%'},
            {title: '尺码', dataIndex: 'spec2_name', width: '15%'},
            {title: '一口价', dataIndex: 'sku_price', width: '15%'},
            {title: '数量', dataIndex: 'sku_quantity', width: '15%', editor: {xtype: 'number'}, elCls: 'simulate'}
        ];
        tableCascadeTable.on('expand', function (ev) {
            var data = ev.record,
                    row = ev.row,
                    sgrid = $(row).data('sub-grid');
            if (!sgrid) {
                var store = new Store({
                    url: '?app_act=api/tb_issue/get_goods_sku_list',
                    autoLoad: true, //自动加载数据
                    params: {
                        shop_code: data.shop_code,
                        goods_code: data.goods_code
                    },
                    pageSize: 100	// 配置分页数目
                });
                var sgrid = new Grid.Grid({
                    render: $(row).find('.inner-grid'),
                    columns: BUI.cloneObject(tableCascadeTableColumns),
                    loadMask: true, //加载数据时显示屏蔽层
                    store: store,
                    plugins: [tableCascadeCellEditing]
                });
                sgrid.render();
                $(row).data('sub-grid', sgrid);
            }
            sgrid.on('aftershow', function () {
                $(row).find('.inner-grid').find('').click(function () {
                    var _rowDom = $(this).parents('.bui-grid-row');
                    var _index = $(row).find('.inner-grid .bui-grid-row').index(_rowDom);
                    var _row = sgrid.getItemAt(_index);
                    typeof tableCascadeTableCallback != 'undefined' && tableCascadeTableCallback(_index, _row, this, sgrid, store);
                });
            });
        });
        tableCascadeTable.on('removed', function (ev) {
            var data = ev.record,
                    row = ev.row,
                    sgrid = $(row).data('sub-grid');
            if (sgrid) {
                sgrid.destroy();
            }
        });
        var tableCellEditing = new BUI.Grid.Plugins.CellEditing({
            triggerSelected: false //触发编辑的时候不选中行
        });
        var pagingBar = BUI.Toolbar.PagingBar;
        var Grid = BUI.Grid;
        var Store = BUI.Data.Store;
        var columns = [
            {title: '操作', dataIndex: '_operate', width: '10%', sortable: false, renderer: function (value, obj) {
                    var str = '<ul class="bui-bar button-group" role="toolbar" aria-disabled="false" aria-pressed="false">';
                    str += '<li class="bui-bar-item button button-small bui-inline-block download" title="下载数据" aria-disabled="false"  aria-pressed="false" style="width:30px;" ><i class="icon icon-arrow-down download"></i></li>';
                    str += '<li class="bui-bar-item button button-small bui-inline-block upload" title="上传更新" aria-disabled="false"  aria-pressed="false" style="width:30px;"><i class="icon icon-arrow-up upload"></i></li>';
                    str += '</ul>';
                    return str;
                }},
            {title: '宝贝标题', dataIndex: 'title', width: '19%', editor: {xtype: 'text'}, elCls: 'simulate'},
            {title: '商家编码', dataIndex: 'outer_id', width: '10%'},
            {title: '商品条形码', dataIndex: 'barcode', width: '10%', sortable: false},
            {title: '宝贝类目', dataIndex: 'category_name', width: '10%'},
            {title: '售价', dataIndex: 'price', width: '10%'},
            {title: '数量', dataIndex: 'quantity', width: '10%'},
            {title: '最后修改时间', dataIndex: 'lastchanged', width: '15%'}
        ];
        tableStore = new Store({
            url: '?app_act=api/tb_issue/get_goods_list',
            autoLoad: false,
            proxy: {//设置请求相关的参数
                method: 'post',
                //dataType : 'jsonp', //返回数据的类型
                limitParam: 'page_size', //一页多少条记录
                pageIndexParam: 'page', //页码
                pageStart: 1
            },
            pageSize: page_size
        });
        tableGrid = new Grid.Grid({
            render: '#grid',
            width: '100%',
            columns: columns,
            idField: 'issue_id',
            store: tableStore,
            emptyDataTpl: '<div class="centered">查询的数据不存在</div>',
            plugins: [tableCascadeTable, tableCellEditing, Grid.Plugins.AutoFit, Grid.Plugins.RowNumber] //Grid.Plugins.CheckSelection,
        });
        tableGrid.render();
        function clear_nodata() {
            if ($('.nodata').length > 0) {
                $('.nodata').remove();
            }
        }

        var tablePage = new pagingBar({
            render: '#table_pager',
            elCls: 'image-pbar pull-right',
            store: tableStore,
            totalCountTpl: ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_table" style="width:50px;height:20px;"><option  value="5" >5</option><option  value="10" >10</option><option selected="selected" value="20" >20</option><option value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 '
        });
        tablePage.render();
        $('.bui_page_table').live('change', function () {
            var num = parseInt($(this).val());
            var obj = {
                limit: num,
                page_size: num,
                pageSize: num,
                start: 1
            };
            tablePage.set('pageSize', num);
            tableStore.load(obj);
        });
        formListeners['beforesubmit'].push(function (ev) {
            var obj = form.serializeToObject();
            clear_nodata();
            obj.start = 1; //返回第一页
            obj.page = 1;
            obj.pageIndex = 0;
            $('table_datatable .bui-pb-page').val(1);
            var _pageSize = $('.bui_page_table').val();
            obj.limit = _pageSize;
            obj.page_size = _pageSize;
            obj.pageSize = _pageSize;
            obj.shelf_status = $("#shelf_status").find(".active").attr("id");
            tableStore.load(obj, function (data, params) {
                $('.bui_page_table').val(_pageSize);
            });
        });
        //数据加载
        init_data = function () {
            var obj = form.serializeToObject();
            obj.start = 1; //返回第一页
            obj.page_size = $('#table_pager .bui_page_select').val();
            obj.page = $('#table_pager .bui-pb-page').val();
            obj.pageIndex = obj.page - 1;
            obj.shelf_status = $("#shelf_status").find(".active").attr("id");
            tableStore.load(obj);
        };

        //表格按钮事件
        tableGrid.on('cellclick', function (ev) {
            var record = ev.record; //点击行的记录
            //var field = ev.field; //点击对应列的dataIndex
            var target = $(ev.domTarget); //点击的元素
            var _data = {};
            _data.shop_code = record.shop_code;
            _data.goods_code = record.goods_code;
            _data.category_id = record.category_id;
            _data.item_id = record.num_iid;
            if (target.hasClass('download')) {
                down_update_data(_data);
                init_data();
                return false;
            }
            if (target.hasClass('upload')) {
                upload_update_data(_data);
                return false;
            }
        });

        tableStore.on('load', function () {
            var ctl_id = 'table';
            var srcoll_id = 'table_srcoll';
            var content_width = $('#' + ctl_id + ' .bui-grid-body>table').width();
            var width = $('#' + ctl_id).width();
            var c_width = content_width - width;
            if (c_width > 30) {
                $('#' + srcoll_id).show();
                var scroll_auto_time;
                $('#' + srcoll_id + ' .left_srcoll').mousedown(function () {
                    scroll_auto_time = setInterval(function () {
                        scroll_auto(-1);
                    }, 100);
                });
                $('#' + srcoll_id + ' .right_srcoll').mousedown(function () {
                    scroll_auto_time = setInterval(function () {
                        scroll_auto(1);
                    }, 100);
                });
                $('#' + srcoll_id + ' .right_srcoll').mouseup(function () {
                    clearInterval(scroll_auto_time);
                });
                $('#' + srcoll_id + ' .left_srcoll').mouseup(function () {
                    clearInterval(scroll_auto_time);
                });
            }

            function scroll_auto(i) {
                var ctl_id = 'table';
                var croll_move = i * 30;
                var crollLeft = $('#' + ctl_id + ' .bui-grid-body').scrollLeft();
                var new_crollLeft = crollLeft + croll_move;
                $('#' + ctl_id + ' .bui-grid-body').scrollLeft(new_crollLeft);
                var n_crollLeft = $('#' + ctl_id + ' .bui-grid-body').scrollLeft();
                if (n_crollLeft == crollLeft) {
                    clearInterval(scroll_auto_time);
                }
            }
        });
        //主信息编辑
        if (typeof tableCellEditing != "undefined") {
            tableCellEditing.on('accept', function (record) {
                var params = {};
                params.type = 'goods';
                params.id = record.record.issue_id;
                params.field = record.editor.get('field').get('name');
                params.value = record.editor.getValue();
                $.post('?app_act=api/tb_issue/update_field', params, function (ret) {
                    alert_msg(ret.status, ret.message);
                }, 'json');
            });
        }
        //SKU信息编辑
        if (typeof tableCascadeCellEditing != "undefined") {
            tableCascadeCellEditing.on('accept', function (record) {
                var params = {};
                params.type = 'sku';
                params.id = record.record.sell_prop_id;
                params.field = record.editor.get('field').get('name');
                params.value = record.editor.getValue();
                $.post('?app_act=api/tb_issue/update_field', params, function (ret) {
                    alert_msg(ret.status, ret.message);
                    if (ret.status == 1) {
                        init_data();
//                        var record = tableGrid.getItem('a');
//                        tableCascadeTable.expand(record);
                    }
                }, 'json');
            });
        }

        /*------Begin---宝贝在售状态工具条-----*/
        var Bar = BUI.Toolbar.Bar;
        var bar_shelf = new Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '全部', id: 'tabs_all', selected: true},
                {content: '在售', id: 'tabs_sale'},
                {content: '在库', id: 'tabs_stock'}
            ],
            render: '#shelf_status'
        });
        bar_shelf.render();
        bar_shelf.on('itemclick', function (ev) {
            init_data();
        });
        /*------End---宝贝在售状态工具条-----*/

        init_data();
    });

    //下载更新数据
    function down_update_data(_data) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/down_update_goods'); ?>', data: {_param: _data},
            success: function (ret) {
                alert_msg(ret.status, ret.message);
            }
        });
    }

    //上传更新数据
    function upload_update_data(_data) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/upload_update_goods'); ?>', data: {_param: _data},
            success: function (ret) {
                alert_msg(ret.status, ret.message);
            }
        });
    }

    function alert_msg(_status, _msg) {
        if (_status == 1) {
            BUI.Message.Show({
                msg: _msg,
                icon: 'success',
                buttons: [],
                autoHide: true
            });
        } else {
            BUI.Message.Show({
                msg: _msg,
                icon: 'error',
                buttons: [],
                autoHide: true
            });
        }
    }
</script>


