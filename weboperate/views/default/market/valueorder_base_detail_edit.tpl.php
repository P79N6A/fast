<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    p {
        margin: 0;
    }
    
    .panel-body{
        padding: 0;
    }
    
    .table{
        margin-bottom: 0;
    }
    
    .bui-grid, .bui-grid-header, .bui-grid-body, .bui-grid-table, .bui-grid-row {
        width: 100% !important;
    }
    
    .table tr{
        padding:5px 0;
    }
    
    .table th, .table td{
        border:1px solid #dddddd;
        padding:3px 0;
        vertical-align:middle;
    }
    
    .table th{
        width:11.3%;
        text-align:center;
    }
    
    .table td{
        width:23%;
        padding:0 1%;
    }
    
    .row{
        margin-left: 0;
        padding: 2px 8px;
        border: 1px solid #ddd;
    }
</style>
<?php
render_control(
    'PageHead',
    'head1',
    array(
        'title' => '编辑增值服务订购',
        'links' => array(
            array('url'=>'market/valueorder_base/do_list','title'=>'增值服务订购')
        )
    )
);
?>
<!--+--------------------+
    | 基本信息面板 start |
    +--------------------+-->
<div class="panel record_table" id="panel_html">
</div>
<div class="record_table" style="display:none;">
    <input id="val_channel_id" type="hidden" name="val_channel_id" value="<?php echo $response['order']['val_channel_id']; ?>">
    <input id="val_kh_id" type="hidden" name="val_kh_id" value="<?php echo $response['order']['val_kh_id']; ?>">
    <input id="val_seller" type="hidden" name="val_seller" value="<?php echo $response['order']['val_seller']; ?>">
</div>
<script>
    window.to_edit = <?php echo $response['order']['val_status'] == '已下单' ? 'true' : 'false'; ?>;
    var data = [
        {
            name: "val_num",
            title: "增值订购编号",
            value: '<?php echo $response['order']['val_num']; ?>',
            type: "input"
        },
        {
            name: "val_kh_name",
            title: "客户名称",
            value: '<?php echo "[{$response['order']['val_kh_id']}]{$response['order']['val_kh_id_name']}"; ?>',
            type: "input",
            edit: to_edit
        },
        {
            name: "val_status",
            title: "状态",
            value: '<?php echo $response['order']['val_status']; ?>',
            type: "input"
        },
        {
            name: "val_orderdate",
            title: "下单时间",
            value: '<?php echo $response['order']['val_orderdate']; ?>',
            type: "input"
        },
        {
            name: "val_cp_id",
            title: "产品名称",
            value: '<?php echo $response['order']['val_cp_id']; ?>',
            type: "select",
            data: <?php echo $response['pro']; ?>,
            edit: to_edit
        },
        {
            name: "val_pt_version",
            title: "产品版本",
            value: '<?php echo $response['order']['val_pt_version']; ?>',
            type: "select",
            data: <?php echo $response['ver']; ?>,
            edit: to_edit
        },
        {
            name: "val_channel_name",
            title: "销售渠道",
            value: '<?php echo "[{$response['order']['val_channel_id']}]{$response['order']['val_channel_id_name']}"; ?>',
            type: "input",
            edit: to_edit
        },
        {
            name: "val_total_price",
            title: "订购总金额（元）",
            value: '<?php echo $response['order']['val_standard_price']; ?>'
        },
        {
            name: "val_cheap_price",
            title: "整单优惠（元）",
            value: '<?php echo $response['order']['val_cheap_price']; ?>'
        },
        {
            name: "val_actual_price",
            title: "订购成交总金额（元）",
            value: '<?php echo $response['order']['val_actual_price']; ?>'
        },
        {
            name: "val_hire_limit",
            title: "使用周期（月）",
            value: '<?php echo $response['order']['val_hire_limit']; ?>',
            type: "input",
            edit: to_edit
        },
        {
            name: "val_seller_name",
            title: "销售经理",
            value: '<?php echo $response['order']['val_seller']; ?>',
            type: "input",
            edit: to_edit
        },
        {
            name: "val_paydate",
            title: "付款时间",
            value: '<?php echo $response['order']['val_paydate']; ?>'
        },
        {
            name: "val_checkdate",
            title: "审核时间",
            value: '<?php echo $response['order']['val_checkdate']; ?>'
        },
        {
            name: "val_desc",
            title: "描述",
            value: '<?php echo $response['order']['val_desc']; ?>',
            type: "input",
            edit: to_edit
        }
    ];
    <?php if (isset($response['order']['api'])): ?>
    data.push({
        name: "api",
        title: "密钥",
        value: '<?php echo empty($response['order']['api']) ? '未生成' : $response['order']['api']; ?>',
        type: "text"
    });
    <?php endif; ?>
    $(function () {
        var r = new record_table();
        r.init({
            id: "panel_html",
            data: data,
            is_edit: to_edit,
            edit_url: "?app_act=market/valueorder_base/valueorder_edit"
        });
        $("#panel_html").find(".btnFormEdit").bind("click", function() {
            // 销售渠道弹窗 start
            window.channel_pop = {
                dialog: null,
                callback: function (value, id, code, name) {
                    var nameArr = [], valueArr = [];
                    for (var i = 0; i < value.length; i++) {
                        nameArr.push('['+value[i][code]+']'+value[i][name]);
                        valueArr.push(value[i][id]);
                    }
                    $('#val_channel_name input').val(nameArr.join(','));
                    $('#val_channel_id').val(valueArr.join(','));
                    if (channel_pop.dialog !== null) {
                        channel_pop.dialog.close();
                    }
                }
            };
            $('input[name=val_channel_name]').click(function() {
                channel_pop.dialog = new ESUI.PopSelectWindow(
                    '?app_act=common/select/sellchannel',
                    'channel_pop.callback',
                    {
                        title: '销售渠道',
                        width: 900,
                        height: 500 ,
                        ES_pFrmId: '<?php echo $request['ES_frmId']; ?>' ,
                        selecttype: "tree"
                    }
                ).show();
            });
            // 销售渠道弹窗 end
            // 客户名称弹窗 start
            window.kh_pop = {
                dialog: null,
                callback: function (value, id, code, name) {
                    var nameArr = [], valueArr = [];
                    for (var i = 0; i < value.length; i++) {
                        nameArr.push('['+value[i][code]+']'+value[i][name]);
                        valueArr.push(value[i][id]);
                    }
                    $('#val_kh_name input').val(nameArr.join(','));
                    $('#val_kh_id').val(valueArr.join(','));
                    if (kh_pop.dialog !== null) {
                        kh_pop.dialog.close();
                    }
                }
            };
            $('input[name=val_kh_name]').click(function() {
                kh_pop.dialog = new ESUI.PopSelectWindow(
                    '?app_act=common/select/clientinfo',
                    'kh_pop.callback',
                    {
                        title: '客户名称',
                        width: 900,
                        height:500 ,
                        ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'
                    }
                ).show();
            });
            // 客户名称弹窗 end
            // 销售经理弹窗 start
            window.seller_pop = {
                dialog: null,
                callback: function (value, id, code, name) {
                    var nameArr = [], valueArr = [];
                    for (var i = 0; i < value.length; i++) {
                        nameArr.push('['+value[i][code]+']'+value[i][name]);
                        valueArr.push(value[i][id]);
                    }
                    $('#val_seller_name input').val(nameArr.join(','));
                    $('#val_seller').val(valueArr.join(','));
                    if (seller_pop.dialog != null) {
                        seller_pop.dialog.close();
                    }
                }
            };
            $('input[name=val_seller_name]').click(function() {
                seller_pop.dialog = new ESUI.PopSelectWindow(
                    '?app_act=common/select/orguser',
                    'seller_pop.callback',
                    {
                        title: '销售经理',
                        width: 900,
                        height: 500 ,
                        ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'
                    }
                ).show();
            });
            // 销售经理弹窗 end
        });
    });
</script>
<!--+------------------+
    | 基本信息面板 end |
    +------------------+-->
<!--+--------------------+
    | 订购明细面板 start |
    +--------------------+-->
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">
            订购明细
            <i class="icon-folder-open toggle"></i>
        </h3>
        <div class="pull-right">
            <?php if ($response['order']['val_status'] == '已下单'): ?>
            <button class="button button-small" onclick="popServer();">
                <i class="icon-plus"></i>
                新增服务
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="panel-body">
        <div id="grid" class="row" style="padding: 0; margin: 0; border: none;">
        </div>
    </div>
</div>
<script>
    BUI.use(['bui/grid','bui/data'], function(Grid,Data){
        var Grid = Grid,
            Store = Data.Store,
            columns = [
                {
                    dataIndex : 'vs_value_id',
                    width: 0,
                    visible: false
                },{
                    title : '增值服务类型',
                    dataIndex : 'value_name',
                    visibleMode: 'visibility',
                    width:100
                },{
                    title : '增值服务描述',
                    editor : {
                        xtype : 'text'
                    },
                    dataIndex : 'value_desc', 
                    width: 100
                },{
                    title : '金额',
                    dataIndex : 'value_price',
                    width:100
                },{
                    title : '明细优惠',
                    editor : {
                        xtype : 'number',
                        rules : {
                            min : 0,
                            required : true
                        }
                    },
                    dataIndex : 'vs_cheap_price',
                    width:100
                },{
                    title : '实际成交金额',
                    dataIndex : 'vs_actual_price',
                    width:100
                },{
                    title : '操作',
                    width: 100,
                    dataIndex : 'nouse',
                    renderer : function () {
                        if (window.to_edit) {
                            return '<button class="button button-small grid-command btn-edit" type="button" title="编辑"><i class="icon-pencil"></i></button>' 
                                 + '<button class="button button-small btn-del" type="button" title="删除"><i class="icon-trash"></i></button>';
                        } else {
                            return '<i style="margin-top:3px;" class="icon-ban-circle"></i>';
                        }
                    }
                }
            ],
            data = <?php echo json_encode($response['valueorder']) ?>;
        
        var editing = new Grid.Plugins.RowEditing({
                triggerCls : 'btn-edit',
                triggerSelected : false
            }),
            store = new Store({
                data : data,
                autoLoad : true
            }),
            grid = new Grid.Grid({
                render:'#grid',
                forceFit : true,
                columns : columns,
                plugins : [editing],
                store : store
            });
            
        grid.render();
        
        editing.on('editorshow',function(ev){
            var editor = editing.get('curEditor');
            editor.set('errorAlign',{
                points :['br','tr'] ,
                offset: [0, 10]
            });
        });
        
        editing.on('accept',function(ev){
            ev.record.value_price = parseFloat(ev.record.value_price);
            ev.record.vs_cheap_price = parseFloat(ev.record.vs_cheap_price);
            if (ev.record.value_price < ev.record.vs_cheap_price) {
                BUI.Message.Alert('明细优惠不能大于金额', 'error');
                location.reload();
                return ;
            }
            ev.record.vs_val_num = '<?php echo $request['_id'];?>';
            delete ev.record.nouse;
            $.post(
                '?app_act=market/valueorder_base/detail_valueorder_edit',
                ev.record,
                function (res) {
                    BUI.Message.Alert(res.message, 'success');
                    location.reload();
                },
                'json'
            );
        });
 
        grid.on('cellclick',function  (ev) {
            var record = ev.record, //点击行的记录
            target = $(ev.domTarget); //点击的元素
            if(target.hasClass('btn-del')){
                BUI.Message.Confirm('确认要删除吗？',function(){
                    $.post(
                        '?app_act=market/valueorder_base/detail_valueorder_delete',
                        {
                            vs_val_num: '<?php echo $request['_id']; ?>',
                            vs_value_id: record.vs_value_id
                        },
                        function (res) {
                            BUI.Message.Alert(res.message, 'success');
                            location.reload();
                        },
                        'json'
                    );
                },'question');
            }
        });
      });
    window.service_pop = {
        dialog: null,
        callback: function (value, id, code, name) {
            if (service_pop.dialog != null) {
                service_pop.dialog.close();
            }
            $.post(
                '?app_act=market/valueorder_base/detail_valueorder_add',
                {
                    vs_val_num : '<?php echo $request['_id'] ?>',
                    vs_value_id : value[0].value_id,
                    vs_cheap_price : 0,
                    vs_actual_price : value[0].value_price
                },
                function(res){
                    BUI.Message.Alert(res.message, 'success');
                    location.reload();
                },
                'json'
            );
        }
    };

    function popServer() {
        var cpid = '<?php echo $response['order']['val_cp_id']; ?>';
        var cpversion = '<?php echo $response['order']['val_pt_version']; ?>';
        service_pop.dialog = new ESUI.PopSelectWindow(
            '?app_act=common/select/valueserver&cpid='+cpid+'&cpversion='+cpversion+'&enable=1',
            'service_pop.callback', {
                title: '增值服务',
                width: 900,
                height:500 ,
                ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'
            }
        ).show();
    }
</script>
<!--+------------------+
    | 订购明细面板 end |
    +------------------+-->
<!--+--------------------+
    | 日志显示面板 start |
    +--------------------+-->
<div class="panel">
    <div class="panel-header">
        <h3>日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row" style="width: 100%; padding: 0; margin: 0; border: none; padding-bottom: 5px;">
            <?php
            render_control('DataTable', 'log', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type'  => 'text',
                            'show'  => 1,
                            'title' => '操作者',
                            'field' => 'user_code',
                            "width" => '120',
                            'align' => ''
                        ),
                        array(
                            'type'  => 'text',
                            'show'  => 1,
                            'title' => '操作名称',
                            'field' => 'val_action',
                            "width" => '200',
                            'align' => ''
                        ),
                        array(
                            'type'  => 'text',
                            'show'  => 1,
                            'title' => '操作时间',
                            'field' => 'val_time',
                            "width" => '200',
                            'align' => ''
                        ),
                        array(
                            'type'  => 'text',
                            'show'  => 1,
                            'title' => '完成状态',
                            'field' => 'val_status',
                            "width" => '120',
                            'align' => ''
                        ),
                        array(
                            'type'  => 'text',
                            'show'  => 1,
                            'title' => '备注',
                            'field' => 'val_remark',
                            "width" => '200',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'market/ValueorderLogModel::getLogByPage',
                'params' => array('filter' => array('val_num' => $request['_id'], 'page_size' =>  '10')),
            ));
            ?>
        </div>
    </div>
</div>
<!--+------------------+
    | 日志显示面板 end |
    +------------------+-->
<!--+--------------------+
    | 控制按钮面板 start |
    +--------------------+-->
<?php if($response['order']['val_status'] != '已作废'): ?>
<form class="form-horizontal">
    <div class="row form-actions actions-bar">
        <div class="span13 offset3" style="margin: 10px 0;">
            <?php if(isset($response['order']['api']) && empty($response['order']['api']) && $response['order']['val_status'] != '已下单'): ?>
            <button onclick="btnApi();" type="button" class="button button-primary">生成密钥</button>
            <?php endif;?>
            <?php if($response['order']['val_status'] == '已下单'): ?>
            <button onclick="btnPay();" type="button" class="button button-primary">付款</button>
            <?php elseif($response['order']['val_status'] == '已付款'): ?>
            <button onclick="btnCheck();" type="button" class="button button-info">审核</button>
            <?php endif; ?>
            <button onclick="btnAbate();" type="button" class="button button-danger">作废</button>
            <button id="btnLog" type="button" class="button">填写日志</button>
        </div>
    </div>
</form>
<?php endif; ?>
<div id="log_pop" class="hide">
    <form id="log_form" class="form-horizontal">
        <div style="border: none;" class="row">
            <div class="control-group span12">
                <label class="control-label"><s>*</s>操作名称：</label>
                <div class="controls">
                    <input name="val_action" type="text" data-tip="{text : '提示信息'}" class="input-normal control-text" data-rules="{required : true}">
                </div>
            </div>
        </div>
        <div style="border: none;" class="row">
            <div class="control-group span12">
                <label class="control-label">备注：</label>
                <div class="controls">
                    <input name="val_remark" type="text" class="input-normal control-text">
                </div>
            </div>
            <input type="hidden" name="val_num" value="<?php echo $request['_id'] ?>">
        </div>
    </form>
</div>
<script>
    this.val_num = '<?php echo $request['_id'] ?>';
    
    function btnApi() {
        $.get(
            '?app_act=market/valueorder/openapi',
            {
                val_kh_id: '<?php echo $response['order']['val_kh_id']; ?>'
            },
            function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type === 'success') {
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            },
            'json'
        );
    }
    
    function btnPay() {
        BUI.Message.Confirm('确认要付款吗？', function(){
            $.post(
                '?app_act=market/valueorder_base/doPay',
                {val_num: '<?php echo $request['_id'];?>'},
                function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type === 'success') {
                        BUI.Message.Alert(ret.message, type);
                        refreshTab();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                },
                'json'
            );
        }, 'question');
    }
    function btnCheck() {
        BUI.Message.Confirm('确认要审核吗？', function(){
            $.post(
                '?app_act=market/valueorder_base/doCheck',
                {val_num: '<?php echo $request['_id'];?>'},
                function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type === 'success') {
                        BUI.Message.Alert(ret.message, type);
                        refreshTab();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                },
                'json'
            );
        }, 'question');
    }
    function btnAbate() {
        BUI.Message.Confirm('确认要作废吗？', function(){
            $.post(
                '?app_act=market/valueorder_base/doAbate',
                {val_num: '<?php echo $request['_id'];?>'},
                function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type === 'success') {
                        BUI.Message.Alert(ret.message, type);
                        refreshTab();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                },
                'json'
            );
        }, 'question');
        
    }
    
    BUI.use('bui/overlay',function(Overlay){
        var dialog = new Overlay.Dialog({
            title: '填写日志',
            width: 500,
            height: 170,
            contentId: 'log_pop',
            success: function () {
                if (document.getElementById('log_form').val_action.value.length < 1) {
                    BUI.Message.Alert('操作名称不能为空', 'warning');
                }
                $.post(
                    '?app_act=market/valueorder_base/addLog',
                    {
                        val_num: '<?php echo $request['_id'] ?>',
                        val_action: document.getElementById('log_form').val_action.value,
                        val_remark: document.getElementById('log_form').val_remark.value
                    },
                    function (ret) {
                        var type = ret.status == 1 ? 'success' : 'error';
                        if (type === 'success') {
                            BUI.Message.Alert(ret.message, type);
                        } else {
                            BUI.Message.Alert(ret.message, type);
                        }
                        location.reload();
                    },
                    'json'
                );
            }
        });
        $('#btnLog').on('click', function () {
            dialog.show();
        });
    });
    
    function refreshTab() {
        for (var i = 0; top[i] !== undefined; i++) {
            if (top[i].ES_PAGE_ID === 'market/valueorder_base/do_list') {
                top[i].location.reload();
            }
        }
        window.location.reload();
    }
</script>
<!--+------------------+
    | 控制按钮面板 end |
    +------------------+-->
