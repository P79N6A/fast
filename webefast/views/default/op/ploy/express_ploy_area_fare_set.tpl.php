<style>
    #set_list{margin-bottom: 50px;}
    #set_detail{display:none;}
    #set_detail .panel{float: left;}
    #set_detail,#area,#freight{height: 450px;}

    #set_detail .panel-header{border-radius: initial}
    #set_detail .panel-body{border:1px solid #dddddd}

    #area{width: 30%;}
    #freight{width: 70%;}
    #freight .panel-header, #freight .panel-body{border-left: none;}
    #freight .panel-body{height: 100%;overflow-y: scroll}
    #freight .bui-grid-body, .bui-grid-height{border-bottom: 1px solid #dddddd;}
</style>

<?php
$check = $response['ploy_status'] != 1;
$title = "<span>策略：{$response['ploy_name']}[{$response['ploy_code']}]</span><span style='margin-left:20px;'>快递：{$request['express_name']}[{$request['express_code']}]</span>";

$links = array();
if ($check) {
    $links[] = array('type' => 'js', 'js' => 'add_detail()', 'title' => '新增配置');
}
render_control('PageHead', 'head1', array('title' => $title,
    'links' => $links,
    'ref_table' => 'table'
));
?>
<div id="set_list">
    <?php
    $buttons = array();
    if ($check) {
        $buttons[] = array('id' => 'edit', 'title' => '编辑', 'callback' => 'edit_detail', 'show_name' => '编辑', 'show_cond' => '');
        $buttons[] = array('id' => 'delete', 'title' => '删除', 'callback' => 'delete_detail', 'show_cond' => '');
    } else {
        $buttons[] = array('id' => 'view', 'title' => '查看', 'callback' => 'show_detail', 'show_name' => '查看', 'show_cond' => '');
    }

    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '120',
                    'align' => '',
                    'buttons' => $buttons
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配置名称',
                    'field' => 'express_set_name',
                    'width' => '200',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '目的地<img height="23" width="23" src="assets/images/tip.png" class="tip" data-align="bottom-left" title="点击查看或编辑，列表下方展示详细信息" />',
                    'field' => 'area',
                    'width' => '500',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '是否设置区域运费',
                    'field' => 'is_set',
                    'width' => '150',
                    'align' => 'center',
                    'format_js' => array('type' => 'map_checked')
                ),
            )
        ),
        'dataset' => 'op/ploy/ExpressPloyExpSetModel::get_by_page',
        'params' => array('filter' => array('ploy_express_id' => $request['ploy_express_id'])),
        'idField' => 'express_set_id',
    ));
    ?>
</div>

<div id="set_detail">
    <div style="margin:10px 20px 10px 20px;">
        <label>配置名称：</label>
        <?php if ($response['ploy_status'] != 1): ?>
            <input type="text" id="express_set_name" placeholder="配置名称">
            <button type="submit" class="button button-primary" id="btn_save"  style="margin-left: 20px;">保存配置</button>
        <?php else: ?>
            <b id="express_set_name"></b>
        <?php endif; ?>
        <button type="submit" class="button button-primary" id="btn_return" style="margin-left: 20px;float: right">返回列表</button>
    </div>
    <div class="panel" id="area">
        <input type="hidden" id="express_set_id" value="0">
        <div class="panel-header">
            <h3>选择目的区域</h3>
        </div>
        <div class="panel-body">
            <input type="hidden" id="selected_ids" name="selected_ids" value=""/>
            <div id="sortTree">
            </div>
        </div>
    </div>
    <div class="panel"  id="freight">
        <div class="panel-header">
            <h3>设置区域运费</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="span16">
                    <div id="grid">
                    </div>
                    <span class="auxiliary-text" style="color: red;"><i>请注意：保存配置时只会保存不存在空项的记录</i></span>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="gridValue">
</div>


<script>
    var ploy_express_id = "<?php echo $request['ploy_express_id'] ?>",
            ploy_status = "<?php echo $request['ploy_status'] ?>";
    var area_data = [], freight_store;
    function load_area(express_set_id) {
        area_data = [];
        BUI.use(['bui/tree', 'bui/data'], function (Tree, Data) {
            //数据缓冲类
            var store = new Data.TreeStore({
                root: {
                    id: '1',
                    text: '中国',
                    checked: false
                },
                url: '?app_act=op/ploy/express_ploy/get_nodes&express_set_id=' + express_set_id + '&ploy_express_id=' + ploy_express_id,
                autoLoad: true
            });

            var tree = new Tree.TreeList({
                render: '#sortTree',
                showLine: true,
                height: 450,
                store: store,
                checkType: 'custom',
                showRoot: true
            });
            tree.render();

            var selecttext = '';
            tree.on('checkedchange', function (e) {
                if (ploy_status == 1) {
                    return;
                }
                if (e.node.text === selecttext) {
                    var temp_area = {};
                    temp_area.type = find_node_type(e.node, 0);
                    temp_area.checked = e.checked ? 1 : 0;
                    temp_area.id = e.node.id;
                    area_data.push(temp_area);
                }

            });

            function find_node_type(node, type) {
                if (node != null) {
                    type++;
                    return find_node_type(node.parent, type);
                }
                return type;
            }

            store.on('beforeprocessload', function (ev) {
                setTimeout(function () {
                    nochange = 1;
                    BUI.each(ev.data, function (subNode, index) {
                        var node = tree.findNode(subNode.id);
                        tree.setNodeChecked(node, subNode.checked); //勾选
                    });
                    nochange = 0;
                }, 10);
            });

            store.on('load', function (ev) {
                setTimeout(function () {
                    $('.x-tree-icon-checkbox').off('click');
                    $('.x-tree-icon-checkbox').on('click', function () {
                        selecttext = $(this).parent().parent().text();
                    });
                });
            });
        });
    }

    function load_freight(express_set_id) {
        BUI.use(['bui/grid', 'bui/data'], function (Grid, Data) {
            var enumObj = {"0": "实重", "1": "半重", "2": "过重"},
            columns = [
//            {title: '操作', renderer: function () {
//                    return '<span class="grid-command btn-delete" onclick="Store.remove(grid.getSelection())">删除</span>'
//                }},
                {title: '首重（克）', dataIndex: 'first_weight', editor: {xtype: 'text', validator: validFn}}, //editor中的定义等用于 BUI.Form.Field.Text的定义
                {title: '首重单价（元）', dataIndex: 'first_weight_price', editor: {xtype: 'number', rules: {required: true}, editableFn: function (value, record) {
                            return true;
                        }}},
                {title: '续重（克）', dataIndex: 'added_weight', editor: {xtype: 'number', rules: {required: true}, editableFn: function (value, record) {
                            return true;
                        }}},
                {title: '续重单价（元）', dataIndex: 'added_weight_price', editor: {xtype: 'number', rules: {required: true}, editableFn: function (value, record) {
                            return true;
                        }}},
                {title: '续重规则', dataIndex: 'added_weight_rule', editor: {id: 'mySelect', xtype: 'select', items: enumObj, rules: {required: true}, validator: valid}, renderer: Grid.Format.enumRenderer(enumObj)},
                {title: '免费额度（元）', dataIndex: 'free_quota', editor: {xtype: 'number', rules: {required: true}, editableFn: function (value, record) {
                            return true;
                        }}},
                {title: '折扣', dataIndex: 'rebate', editor: {xtype: 'number', rules: {required: true}, editableFn: function (value, record) {
                            return true;
                        }}}
            ];
            function valid(value) {
                if (value === '1') {
                    return '不能选择1';
                }
            }
            var editing = new Grid.Plugins.CellEditing({
                triggerSelected: false //触发编辑的时候不选中行
            });

            var params;
            if (express_set_id == 0) {
                params = {data: [{}], autoLoad: true};
            } else {
                params = {url: '?app_act=op/ploy/express_ploy/get_freight&express_set_id=' + express_set_id, autoLoad: true};
            }
            freight_store = new Data.Store(params);

            var params_grid = {
                render: '#grid',
                columns: columns,
                width: 700,
                forceFit: true,
                plugins: [editing, Grid.Plugins.CheckSelection],
                store: freight_store
            };
            if (ploy_status != 1) {
                params_grid.tbar = {
                    items: [{
                            btnCls: 'button button-small',
                            text: '<i class="icon-plus"></i>增加',
                            listeners: {
                                'click': addFunction
                            }
                        },
                        {
                            btnCls: 'button button-small',
                            text: '<i class="icon-remove"></i>删除',
                            listeners: {
                                'click': delFunction
                            }
                        }]
                };
            }
            var grid = new Grid.Grid(params_grid);
            grid.render();

            function validFn(value, obj) {
                var records = freight_store.getResult(),
                        rst = '';
                BUI.each(records, function (record) {
                    if (record.a == value && obj != record) {
                        rst = '文本不能重复';
                        return false;
                    }
                });
                return rst;
            }

            //添加记录
            function addFunction() {
                var newData = {};
                freight_store.addAt(newData, 0);
                editing.edit(newData, 'a'); //添加记录后，直接编辑
            }
            //删除选中的记录
            function delFunction() {
                var selections = grid.getSelection();
                freight_store.remove(selections);
            }
        });
    }

    //保存配置
    $("#btn_save").click(function () {
        var express_set_name = $("#express_set_name").val(),
                express_set_id = $("#express_set_id").val();
        if (express_set_name == '') {
            BUI.Message.Tip('请填写配置名称', 'warning');
            return false;
        }
        if (express_set_id == 0 && area_data.length < 1) {
            BUI.Message.Tip('请选择目的区域', 'warning');
            return false;
        }
        freight_temp = freight_store.getResult();
        var freight_data = [];
        $.each(freight_temp, function (key, row) {
            if (row.length < 1) {
                return true;
            }
            var empty_status = 0;
            $.each(row, function (k, v) {
                if (v == '') {
                    empty_status = 1;
                    return false;
                }
            });
            if (empty_status == 1) {
                return true;
            }
            freight_data.push(row);
        });

        var params = {ploy_express_id: ploy_express_id, express_set_id: express_set_id, express_set_name: express_set_name, area_data: area_data, freight_data: freight_data};
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('op/ploy/express_ploy/save_express_set'); ?>',
            data: {params: params},
            success: function (ret) {
                if (ret.status == 1) {
                    tag = 'success';
                    reload_page(ret.data);
                } else if (ret.status == 2) {
                    tag = 'warning';
                } else {
                    tag = 'error';
                }
                BUI.Message.Tip(ret.message, tag);
            }
        });
    });

    //返回列表
    $("#btn_return").click(function () {
        $("#sortTree,#grid").html('');
        $("#set_detail").hide();
        reload_page('');
    });

    //新增配置
    function add_detail() {
        show_set_detail(0, '');
//        location.href = "#set_detail";
    }

    //查看配置
    function show_detail(index, row) {
        $("#express_set_name").text(row.express_set_name);
        show_set_detail(row.express_set_id, '');
    }

    //编辑配置
    function edit_detail(index, row) {
        show_set_detail(row.express_set_id, row.express_set_name);
    }

    function show_set_detail(express_set_id, express_set_name) {
        $("#sortTree,#grid").html('');
        $("#express_set_id").val(express_set_id);
        if (express_set_name != '') {
            $("#express_set_name").val(express_set_name);
        }
        load_area(express_set_id);
        load_freight(express_set_id);
        reload_page(express_set_id);
        $("#set_detail").show();
    }

    //删除配置
    function delete_detail(index, row) {
        BUI.Message.Confirm('确定要删除该配置吗？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('op/ploy/express_ploy/delete_express_set'); ?>',
                data: {express_set_id: row.express_set_id, ploy_express_id: ploy_express_id},
                success: function (ret) {
                    if (ret.status == 1) {
                        tag = 'success';
                        $("#btn_return").click();
                    } else {
                        tag = 'error';
                    }
                    BUI.Message.Tip(ret.message, tag);
                }
            });
        });
    }

    function reload_page(express_set_id) {
        tableStore.on('beforeload', function (e) {
            e.params.express_set_id = express_set_id;
            tableStore.set("params", e.params);
        });
        tableStore.load();
    }


    //页面帮助提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var tips = new Tooltip.Tips({
            tip: {
                trigger: '.tip', //出现此样式的元素显示tip
                alignType: 'right', //默认方向
                elCls: 'tips tips-info',
                titleTpl: '<div class="tips-content" style="margin-left: 0px">{title}</div>',
                offset: 10 //距离左边的距离
            }
        });
        tips.render();
    });

</script>