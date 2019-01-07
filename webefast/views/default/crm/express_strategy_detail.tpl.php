
<style>
    .bui-tab-item{
        position: relative;
    }
    .bui-tab-item .bui-tab-item-text{
        padding-right: 25px;
    }
    .introduce {
        color: red;
    }
    .addr_tbl{border-collapse:collapse;border:1px #ccc solid;}
    .addr_tbl th,.addr_tbl td{padding:6px;border-collapse:collapse;border:1px #ccc solid;}
    .addr_tbl th{background: #eee;}
    tr{height:35px; }
    #p3{ padding-top:5px;}
</style>

<script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
<script type="text/javascript" src="../../webpub/js/bui/bui.js"></script>
<script type="text/javascript" src="../../webpub/js/util/date.js"></script>
<script type="text/javascript" src="../../webpub/js/common.js"></script>
<script type="text/javascript" src="?app_act=common/js/index"></script>
<script src="assets/js/jquery.formautofill2.min.js"></script>
<div id="container">
    <?php
    render_control('PageHead', 'head1', array('title' => '订单快递适配策略',
        'links' => array(
            array('url' => 'crm/express_strategy/do_list', 'title' => '订单快递适配策略列表'),
        ),
            //'ref_table'=>'table'
    ));
    ?>

    <div id="tab">
        <ul>
            <li class="bui-tab-panel-item active"><a href="#">基本信息</a></li>
            <li class="bui-tab-panel-item" id="area_select"><a href="#">区域范围</a></li>
            <li class="bui-tab-panel-item"><a href="#">配送方式及运费</a></li>
        </ul>
    </div>

    <div id="form1_data_source" style="display:none;"><?php
        if (isset($response['form1_data_source'])) {
            echo $response['form1_data_source'];
        }
        ?></div>
    <div id="panel" class="">
        <div id="p1">
            <form  class="form-horizontal" id="form1" action="?app_act=crm/express_strategy/do_<?php echo $response['app_scene'] ?>&app_fmt=json" method="post" >
                <input type="hidden" id="app_scene" name="app_scene" value="" />
                <input type="hidden" id="policy_express_id" name="policy_express_id" value="<?php echo $request['_id'] ?>"/>
                <div class="row">
                    <div class="control-group span15">
                        <label class="control-label span3">策略代码：</label>
                        <div class="span10 controls" >
                            <input type="text" name="policy_express_code" id="policy_express_code" class="input-normal" value=""  data-rules="{required: true}"/>
                            <b style="color:red"> *</b>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="control-group span15">
                        <label class="control-label span3">策略名称：</label>
                        <div class="span10 controls" >
                            <input type="text" name="policy_express_name" id="policy_express_name" class="input-normal" value=""  />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="control-group span15">
                        <label class="control-label span2">适用仓库：</label>
                        <div class="span12 controls" >
                            <?php foreach ($response['store_code'] as $store) { ?>
                                <?php if (!empty($response['check_store'])) { ?>
                                    <div class="span4 controls" style="margin-left: 0px;">
                                        <input type="checkbox" name="store_code[]" id="<?php echo $store['store_code'] ?>" class="" value="<?php echo $store['store_code'] ?>" <?php
                                        if (in_array($store['store_code'], $response['check_store'], true)) {
                                            echo 'checked="checked"';
                                        }
                                        ?> />
                                               <?php echo $store['store_name']; ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="span4 controls" style="margin-left: 0px;">
                                        <input type="checkbox" name="store_code[]" id="<?php echo $store['store_code'] ?>" class="" value="<?php echo $store['store_code'] ?>" checked="checked" />
                                        <?php echo $store['store_name']; ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="control-group span15 introduce">默认适用全部仓库，如需区分仓库请重新设置</div>
                </div>
                <div class="row">
                    <div class="control-group span15">
                        <label class="control-label span6" style="width: 100px;">最低运费判断：</label>
                        <div class="span6 controls" >
                            <input type="radio" name="is_fee_first" id="is_fee_first" class="" value="1" <?php
                            if ($response['data']['is_fee_first'] == 1) {
                                echo 'checked="checked"';
                            }
                            ?>/>启用
                            <input type="radio" name="is_fee_first" id="is_fee_first" class="" value="0" <?php
                            if ($response['data']['is_fee_first'] == 0) {
                                echo 'checked="checked"';
                            }
                            ?> />停用
                        </div>
                    </div>
                    <div class="control-group span15 introduce">启用后 ,'最低运费判断'优先级  > 配送方式优先级</div>
                </div>

                <div class="row">
                    <div class="control-group span20 introduce">
                        说明：若针对店铺需要设置不同的配送方式，需在店铺档案中设置配送方式。设置后仅取店铺中配送方式参与判断。
                    </div>
                </div>
                <div class="row form-actions actions-bar">
                    <div class="span13 offset3 ">
                        <button type="submit" class="button button-primary" id="submit">提交</button>
                        <button type="reset" class="button " id="reset">重置</button>
                    </div>
                </div>
            </form>
            <div class="panel">
                <div class="panel-header">
                    <h3 class="">操作日志 <i class="icon-folder-open toggle"></i></h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        render_control('DataTable', 'log', array(
                            'conf' => array(
                                'list' => array(
                                    array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '操作者',
                                        'field' => 'user_code',
                                        'width' => '120',
                                        'align' => ''
                                    ),
                                    array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '操作名称',
                                        'field' => 'action_name',
                                        'width' => '120',
                                        'align' => ''
                                    ),
                                    array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '备注',
                                        'field' => 'desc',
                                        'width' => '200',
                                        'align' => ''
                                    ),
                                    array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '操作时间',
                                        'field' => 'add_time',
                                        'width' => '150',
                                        'align' => ''
                                    ),
                                )
                            ),
                            'dataset' => 'crm/ExpressStrategyLogModel::get_by_page',
                            //'queryBy' => 'searchForm',
                            'idField' => 'log_id',
                            'params' => array('filter' => array('pid' => $response['id'])),
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>


        <div id="p2">
            <div id="p2_form" >
                <div style="color:#f00">  说明：区域地址勾选即自动保存，启用策略状态不能保存！</div>
                <form  id="form2" name="p2_form" action="?app_act=crm/express_strategy/do_save_area" method="post" onsubmit="check_all_info(1);">
                    <input type="hidden" id="policy_express_id" name="policy_express_id" value="<?php echo $request['_id'] ?>"/>
                    <input type="hidden" id="selected_ids" name="selected_ids" value=""/>
                    <div id="sortTree">
                    </div>
                    <div class="row form-actions actions-bar">
                        <div class="span13 offset3 ">
                            <button type="submit" class="button button-primary" id="submit2">提交</button>
                            <button type="reset" class="button " id="reset">重置</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>


        <div id="p3">
            <div id="p3_form" >

                <?php echo load_js('comm_util.js') ?>
                <?php
                $added_weight_type = array("g0" => '实重', "g1" => '半重', "g2" => '过重');
                render_control('DataTable', 'table', array(
                    'conf' => array(
                        'list' => array(
                            array(
                                'type' => 'button',
                                'show' => ($response['data']['status'] == 1) ? 0 : 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '80',
                                'align' => '',
                                'buttons' => array(
                                    array(
                                        'id' => 'delete',
                                        'title' => '删除',
                                        'callback' => 'do_delete',
                                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？',
                                    ),
                                ),
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '配送方式代码',
                                'field' => 'express_code',
                                'width' => '100',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '配送方式名称',
                                'field' => 'express_name',
                                'width' => '150',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '优先级',
                                'field' => 'priority',
                                'width' => '60',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '首重（千克）',
                                'field' => 'first_weight',
                                'width' => '80',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '首重单价（元）',
                                'field' => 'first_weight_price',
                                'width' => '100',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '续重（千克）',
                                'field' => 'added_weight',
                                'width' => '100',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '续重单价（元）',
                                'field' => 'added_weight_price',
                                'width' => '100',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '续重规则',
                                'field' => 'added_weight_type',
                                'format_js' => array('type' => 'map', 'value' => $added_weight_type),
                                'width' => '80',
                                'align' => '',
                                'editor' => "{xtype : 'select', items: " . json_encode($added_weight_type) . "}"
                            ),
                        )
                    ),
                    'dataset' => 'crm/PolicyExpressRuleModel::get_by_page',
//    'queryBy' => 'searchForm',
                    'idField' => 'policy_express_rule_id',
                    'CellEditing' => ($response['data']['status'] == 1) ? false : true,
                    'params' => array('filter' => array('pid' => $response['id'])),
                ));
                ?>
                <br/>
                <button class="button button-primary" onclick="PageHead_show_dialog('?app_act=crm/express_strategy/express_list&app_scene=add&app_show_mode=pop&policy_express_id=<?php echo $response['id'] ?>', '添加配送方式', {w: 560, h: 400})">添加配送方式</button>
                <br/>
                <br/>
                <span style="color:red;">
                    注释：优先级数字越大，优先级越高；点击可修改;<br>
                    续重规则:<br>
                    实重【超出首重的重量 * 续重单价】<br>
                    半重【超出首重的重量不足0.5Kg时讲按照0.5Kg进行收费,超过则按照1Kg的进行收费】<br>
                    过重【无论超出首重多少都按照1Kg进行收费】<br>
                    (某配送方式首重1KG，首重单价10元，续重单价10元/1KG，包裹实际重量为2.2KG； 若为实重，则运费为10+1.2*10=22元；<br>
                    若为半重，则运费为10+1.5*10=25元； 若为过重，则运费为10+2*10=30元。)<br>
                </span>
            </div>
        </div>
    </div>
    <div id="pps">
    </div>
</div>
<script type="text/javascript">
    function PageHead_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function () {
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }

    var form1_data_source_v = $("#form1_data_source").html();
    var express_startegy = {};
    if (form1_data_source_v != '') {
        express_startegy = eval("(" + form1_data_source_v + ")");
        $('form#form1').autofill(express_startegy);
        is_edit();

    }
    var form2_data_source_v = $("#form1_data_source").html();
    function is_edit() {
<?php if ($response['data']['status'] == 1): ?>
            $('#panel button').attr('disabled', true);
            $('#panel input').attr('disabled', true);
<?php endif; ?>
    }

    $(function () {
        top.n_tableStore = tableStore;
        $(".radio").css("width", "130px");
        $(".input-normal").css("width", "160px");
        $(".checkbox").css("width", "35px");
        $('#sale_channel').change(function () {
            var sale_channel_code = $("#sale_channel").val();
            if (sale_channel_code == '') {
                return false;
            }
            var url = "?app_act=base/shop/get_shop_list";
            $.ajax({type: 'POST', dataType: 'json',
                url: url, data: {sale_channel_code: sale_channel_code},
                success: function (data) {
                    if (data.status == 1) {
                        $("#shop_code option").remove();
                        $("<option value=''>请选择</option>").appendTo("#shop_code");
                        for (var i = 0; i < data.data.data.length; i++) {
                            $("<option value='" + data.data.data[i].shop_code + "'>" + data.data.data[i].shop_name + "</option>").appendTo("#shop_code");
                        }
                        var app_scene = '<?php echo $response['app_scene']; ?>';
                        if (app_scene == 'edit') {
                            var pre_shop_code = $("#pre_shop_code").val();
                            $("#shop_code").val(pre_shop_code);
                        }
                    }
                }});
        });

        if ($("#type").val() == 2) {
            $("#status_type").attr("checked", true);
        }


        $('#country').change();
        var app_scene = '<?php echo $response['app_scene']; ?>';
        if (app_scene == 'edit') {
            var sale_channel_code = '<?php
if (isset($response['sale_channel_code'])) {
    echo $response['sale_channel_code'];
}
?>';
            $("#sale_channel").val(sale_channel_code);
            $('#sale_channel').change();
        }
    });

    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('crm/express_strategy/express_do_delete'); ?>', data: {policy_express_rule_id: row.policy_express_rule_id, express_name: row.express_name, pid: row.pid},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function get_express_list() {
        var policy_express_id = $("#policy_express_id").val();
        if (policy_express_id != '') {
            $.get("?app_act=crm/express_strategy/get_express_list&policy_express_id=" + policy_express_id, function (data) {
                $("#express").html(data);
            });
        }
    }
    get_express_list();

    BUI.use('bui/form', function (Form) {
        var form1 = new Form.HForm({
            srcNode: '#form2',
            submitType: 'ajax',
            callback: function (data) {

            }
        }).render();

    });

    BUI.use(['bui/tab', 'bui/mask'], function (Tab) {
        var tab = new Tab.TabPanel({
            srcNode: '#tab',
            elCls: 'nav-tabs',
            itemStatusCls: {
                'selected': 'active'
            },
            panelContainer: '#panel'//如果不指定容器的父元素，会自动生成
                    //selectedEvent : 'mouseenter',//默认为click,可以更改事件

        });
        tab.render();
        tab.on('itemselected', function (e) {
            if ($(e.domTarget).attr('id') == 'area_select') {
                $('#p2 .form-actions').hide();
            } else {
                $('#p2 .form-actions').show();
            }
        });
    });

    //p2_form
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null)
            return unescape(r[2]);
        return null;
    }

    var form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'html',
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';

            BUI.Message.Alert(data.message, function () {
                if (data.status == 1) {
                    ui_closePopWindow(getQueryString('ES_frmId'));
                }
            }, type);
        }
    }).render();
</script>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var action = '<?php echo $response['app_scene']; ?>';
    if (action == 'add') {
        $("#tab").find('li').eq(1).hide();
        $("#tab").find('li').eq(2).hide();
    }

    tableCellEditing.on('accept', function (record, editor) {
        $.post('?app_act=crm/express_strategy/do_edit_rule',
                {policy_express_rule_id: record.record.policy_express_rule_id, express_name: record.record.express_name, pid: record.record.pid, priority: record.record.priority, first_weight: record.record.first_weight, first_weight_price: record.record.first_weight_price, added_weight: record.record.added_weight, added_weight_price: record.record.added_weight_price, added_weight_type: record.record.added_weight_type},
                function (result) {
                    top.n_tableStore.load();
                }, 'json');
    });
</script>
<script type="text/javascript">
    BUI.use(['bui/tree', 'bui/data'], function (Tree, Data) {
        //数据缓冲类
        var store = new Data.TreeStore({
            root: {
                id: '1',
                text: '中国',
                checked: false
            },
            url: '<?php echo get_app_url('crm/express_strategy/get_nodes&app_fmt=json&policy_express_id=' . $request['_id']); ?>',
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
<?php if ($response['data']['status'] == 1): ?>
                alert('启用策略状态不能保存选择状态！');
                return;
<?php endif; ?>
            if (e.node.text === selecttext) {
                var area_data = {};
                area_data.type = find_node_type(e.node, 0);
                area_data.checked = e.checked ? 1 : 0;
                area_data.id = e.node.id;
                area_data.policy_express_id = $('#policy_express_id').val();
                save_area(area_data);
            }

        });

        function save_area(area_data) {
            var url = "?app_act=crm/express_strategy/do_save_area";
            $.post(url, area_data, function (ret) {
                if (ret.status < 1) {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, 'json');

        }

        function find_node_type(node, type) {
            if (node != null) {
                type++;
                return   find_node_type(node.parent, type);
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
</script>
