<style>
    .bui-grid{
        border-bottom: 1px solid #dddddd;
    }
    .bui-grid-table .bui-grid-hd-inner{
        height: 20px;
        text-align: center;
    }
    .bui-grid-table .bui-grid-db-hd .bui-grid-hd-inner {
        padding:0;
    }
    .result_grid{
        margin-top: 10px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '宝贝上新列表',
    'links' => array(
        array('type' => 'js', 'js' => 'select_goods()', 'title' => '选择上新宝贝'),
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
<div class="row" style="margin-left: 10px;">
    <div class="span6">
        <div id="issue_status">

        </div>
    </div>
    <div class="span6">
        <div id="full_status">

        </div>
    </div>
</div>
<div class="result_grid">
    <div id="grid" style="margin-bottom: 5px;"></div>
    <div id="grid_pager"></div>
</div>
<div>
    <ul class="toolbar frontool" id="tool">
        <li class="li_btns"><button class="button button-primary " onclick="batck_issue()">批量上新</button></li>
    </ul>
</div>
<script type="text/javascript">
    var form;
    var tableStore, tableGrid;
    var formListeners = {'beforesubmit': []};
    var init_data;
    var page_size = 20;
    var shop_code = $('#searchForm #shop_code').val();
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }
        tools();

        //获取当前选择店铺
        $('#searchForm #shop_code').on('change', function () {
            shop_code = $(this).val();
            init_data();
        });

        init_full_status(); //加载宝贝信息完整度工具条

        /*------Begin---查询-----*/
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
        /*------End---查询-----*/

        /*------Begin---数据加载-----*/
        var pagingBar = BUI.Toolbar.PagingBar,
                Grid = BUI.Grid,
                Store = BUI.Data.Store;
        var columns = [
            {title: '操作', dataIndex: '_operate', width: '10%', elCls: 'center', sortable: false, renderer: function (value, obj) {
                    var str = '<ul class="bui-bar button-group" role="toolbar" aria-disabled="false" aria-pressed="false">';
                    if (obj.full_state == '完整' && obj.issue_status != 1) {
                        str += '<li class="bui-bar-item button button-small bui-inline-block issue" title="上传" aria-disabled="false"  aria-pressed="false" style="width:30px;"><i class="icon icon-arrow-up issue"></i></li>';
                    }
                    if (obj.issue_status != 1) {
                        str += '<li class="bui-bar-item button button-small bui-inline-block edit" title="编辑" aria-disabled="false" aria-pressed="false" style="width:30px;"><i class="icon icon-edit edit"></i></li>';
                    }
                    if (obj.issue_status != 1) {
                        str += '<li class="bui-bar-item button button-small bui-inline-block delete" title="删除" aria-disabled="false" aria-pressed="false" style="width:30px;"><i class="icon icon-remove delete"></i></li>';
                    }
                    str += '</ul>';
                    return str;
                }},
            {title: '宝贝标题', dataIndex: 'title', width: '17%'},
            {title: '商品编码', dataIndex: 'goods_code', width: '15%'},
            {title: '商品售价', dataIndex: 'price', width: '10%'},
            {title: '信息是否完整', dataIndex: 'full_state', width: '10%', sortable: false, elCls: 'center'},
            {title: '基本信息', dataIndex: 'is_base_full', width: '10%', sortable: false, elCls: 'center'},
            {title: '类目信息', dataIndex: 'is_item_full', width: '10%', sortable: false, elCls: 'center'},
            {title: '规格(SKU)信息', dataIndex: 'is_spec_full', width: '9%', sortable: false, elCls: 'center'}
        ];
        //数据加载
        tableStore = new Store({
            url: '?app_act=api/tb_issue/get_issue_goods',
            autoLoad: false,
            proxy: {
                method: 'post',
                //dataType : 'jsonp', //返回数据的类型
                limitParam: 'page_size', //一页多少条记录
                pageIndexParam: 'page', //页码
                pageStart: 1
            },
            pageSize: page_size
        });
        //列分组
        var colGroup = new Grid.Plugins.ColumnGroup({
            groups: [{
                    title: '上传宝贝信息完整度',
                    from: 6,
                    to: 9
                }]
        });
        //数据窗口
        tableGrid = new Grid.Grid({
            render: '#grid',
            width: document.documentElement.clientWidth - 40,
            columns: columns,
            idField: 'issue_id',
            store: tableStore,
            itemStatusFields: {//设置数据跟状态的对应关系
                selected: 'selected',
                disabled: 'disabled'
            },
            emptyDataTpl: '<div class="centered">查询的数据不存在</div>',
            plugins: [Grid.Plugins.CheckSelection, Grid.Plugins.AutoFit, colGroup, Grid.Plugins.RowNumber]
        });
        tableGrid.render();

        function clear_nodata() {
            if ($('.nodata').length > 0) {
                $('.nodata').remove();
            }
        }
        //分页
        var tablePage = new pagingBar({
            render: '#grid_pager',
            elCls: 'image-pbar pull-right',
            store: tableStore,
            totalCountTpl: ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_table" style="width:50px;height:20px;"><option value="5" >5</option><option  value="10" >10</option><option selected="selected" value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 ',
        });
        tablePage.render();

        //页容量变更重新加载数据
        $('.bui_page_table').live('change', function () {
            var num = parseInt($(this).val());
            var obj = form.serializeToObject();
            obj.limit = num;
            obj.page_size = num;
            obj.pageSize = num;
            obj.start = 1;
            obj.issue_status = $("#issue_status").find(".active").attr("id");
            if (obj.issue_status == 'tabs_stay') {
                obj.full_status = $("#full_status").find(".active").attr("id");
            } else {
                obj.full_status = '';
            }
            tablePage.set('pageSize', num);
            tableStore.load(obj);
        });

        //查询
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
            obj.issue_status = $("#issue_status").find(".active").attr("id");
            if (obj.issue_status == 'tabs_stay') {
                obj.full_status = $("#full_status").find(".active").attr("id");
            } else {
                obj.full_status = '';
            }
            tableStore.load(obj, function (data, params) {
                $('.bui_page_table').val(_pageSize);
            });
        });

        //数据加载
        init_data = function () {
            var obj = form.serializeToObject();
            obj.start = 1; //返回第一页
            obj.page_size = $('#grid_pager .bui_page_select').val();
            obj.page = $('#result_grid .bui-pb-page').val();
            obj.pageIndex = obj.page - 1;
            obj.issue_status = $("#issue_status").find(".active").attr("id");
            if (obj.issue_status == 'tabs_stay') {
                obj.full_status = $("#full_status").find(".active").attr("id");
            } else {
                obj.full_status = '';
            }
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
            if (target.hasClass('edit')) {
                showDetail(_data);
                return false;
            }
            if (target.hasClass('issue')) {
                _data.category_id = record.category_id;
                issue_goods(_data);
                return false;
            }
            if (target.hasClass('delete')) {
                BUI.Message.Confirm('确认要删除该宝贝吗？', function () {
                    delete_goods(_data);
                }, 'question');
                return false;
            }
        });

        init_data();
        /*------End---数据加载-----*/

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
    });

    //宝贝上新状态工具条
    BUI.use('bui/toolbar', function (Toolbar) {
        var bar_issue = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '待上新', id: 'tabs_stay', selected: true},
                {content: '上传失败', id: 'tabs_fail'},
                {content: '历史上新', id: 'tabs_history'}
            ],
            render: '#issue_status'
        });

        bar_issue.render();
        bar_issue.on('itemclick', function (ev) {
            var issue_status = ev.item.get('id');
            if (issue_status == 'tabs_stay' && $('#full_status').text() == '') {
                init_full_status();
                init_data();
                $('#tool').css('display', 'block');
            } else if (issue_status == 'tabs_fail') {
                $('#full_status').html('');
                init_data();
                $('#tool').css('display', 'block');
            } else if (issue_status == 'tabs_history') {
                $('#full_status').html('');
                init_data();
                $('#tool').css('display', 'none');
            }
        });
    });
    //宝贝信息完整度工具条
    function init_full_status() {
        var Bar = BUI.Toolbar.Bar;
        var bar_full = new Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: [
                {content: '全部', id: 'full_all', selected: true},
                {content: '完整', id: 'full_ok'},
                {content: '不完整', id: 'full_not'}
            ],
            render: '#full_status'
        });

        bar_full.render();
        bar_full.on('itemclick', function (ev) {
//            if (ev.item.get('id') == 'full_ok') {
//                $('#tool').css('display', 'block');
//            } else {
//                $('#tool').css('display', 'none');
//            }
            init_data();
        });
    }

    var ids = new Array();
    //批量发布宝贝
    function batck_issue() {
        get_checked(true, '上新', function () {
            var params = {data: ids};
            $.post("?app_act=api/tb_issue/batch_issue_goods", params, function (ret) {
                alert_msg(ret.status, ret.message);
                init_data();
            }, "json");
        });
    }

    //读取已选中项
    function get_checked(isConfirm, opt_name, func) {
        var selecteds = tableGrid.getSelection();
        if (selecteds.length == 0) {
            BUI.Message.Alert("请选择宝贝", 'warning');
            return;
        }

        for (var i in selecteds) {
            var _id = {};
            _id.shop_code = selecteds[i].shop_code;
            _id.goods_code = selecteds[i].goods_code;
            _id.category_id = selecteds[i].category_id;
            ids.push(_id);
        }

        if (isConfirm) {
            BUI.Message.Show({
                title: '批量' + opt_name,
                msg: '是否执行批量' + opt_name + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null);
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        } else {
            func.apply(null);
        }
    }

    //选择上新宝贝
    function select_goods() {
        shop_code = $('#searchForm #shop_code').val();
        var shop_name = $('#searchForm #shop_code option:selected').text();
        selectPopWindow.dialog = new ESUI.PopSelectWindow('?app_act=common/select/issue_goods&shop_code=' + shop_code, 'selectPopWindow.callback', {title: '选择系统商品，&nbsp;&nbsp;&nbsp;当前店铺：<b>' + shop_name + '</b>', width: 700, height: 500, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();
    }

    //选择系统商品
    var selectPopWindow = {
        dialog: null,
        callback: function (value) {
            if (value.length > 0) {
                add_goods(value);
            }
            if (selectPopWindow.dialog != null) {
                selectPopWindow.dialog.close();
            }
        }
    };

    //新增宝贝
    function add_goods(_data) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/add_goods'); ?>', data: {goods: _data, shop_code: shop_code},
            success: function (ret) {
                if (ret.status == 1) {
                    alert_msg(1, '添加成功');
                    init_data();
                } else {
                    alert_msg('-1', ret.message);
                    return false;
                }
            }
        });
    }

    //打开宝贝编辑页
    function showDetail(_data) {
        var url = '?app_act=api/tb_issue/detail&app_scene=edit&shop_code=' + _data.shop_code + '&goods_code=' + _data.goods_code;
        openPage(window.btoa(url), url, '编辑宝贝');
    }

    //获取类目
    function get_items(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/get_items'); ?>', data: {},
            success: function (ret) {
                if (ret.status == 1) {
                    alert_msg(1, '下载类目成功');
                } else {
                    alert_msg('-1', ret.message);
                }
            }
        });
    }

    //发布
    function issue_goods(_data) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/issue_goods'); ?>', data: {_param: _data},
            success: function (ret) {
                if (ret.status == 1) {
                    alert_msg(1, '发布成功');
                    init_data();
                } else {
                    alert_msg('-1', ret.message);
                }
            }
        });
    }

    //删除宝贝
    function delete_goods(_data) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('api/tb_issue/delete_goods'); ?>', data: {_param: _data},
            success: function (ret) {
                if (ret.status == 1) {
                    alert_msg(1, '删除成功');
                    init_data();
                } else {
                    alert_msg('-1', ret.message);
                }
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


