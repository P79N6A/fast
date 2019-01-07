
<style type="text/css">
    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_panel1{
        width:100%;
        margin-bottom:5px;
    }
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 18px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 5px;
        text-align: left;
    }
    .table_panel_tt td{ padding:10px 25px;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    .btns{ text-align:right; margin-bottom:5px;}
    .panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
    .panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
    .panel > .panel-header h3{ font-size:14px;}
    input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}

    .bui-dialog .bui-stdmod-body {padding: 40px;}
    .show_scan_mode{ text-align:center;}
    .button-rule{ width:81px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
    .button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
    .button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
    .button-rule:active .icon{ display:block;}
    .button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
    .button-manz{ background-position:27px 26px;}
    .button-maiz{background-position:-208px 25px;}
    .button-manz:hover{background-position:41px -214px;}
    .button-maiz:hover{background-position:-208px -215px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '赠送规则', 'ref_table' => 'table'));
?>
<ul class="nav-tabs oms_tabs">
    <li ><a href="#"  onClick="do_page();">基本信息</a></li>
    <li class="active"  ><a href="#" >赠送规则</a></li>

</ul>
<table class='table_panel table_panel_tt' >
    <tr>
        <td>策略名称：<?php echo $response['strategy']['strategy_name']; ?></td>
        <td >活动店铺：<?php echo $response['strategy']['shop_code_name']; ?></td>
    </tr>
    <tr>
        <td >活动开始时间：<?php echo date('Y-m-d H:i:s', $response['strategy']['start_time']); ?></td>
        <td >活动结束时间：<?php echo date('Y-m-d H:i:s', $response['strategy']['end_time']); ?></td>
    </tr>
</table>

<div class="btns">
    <div id="rule1_btns">
        <button type="button" class="button button-primary is_view" value="新增规则" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> id="btnSelectRule"  onClick="show_ovay_mode();"><i class="icon-plus-sign icon-white"></i> 新增规则</button>
    </div>
</div>
<div id='rule'>
    <?php
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '200',
                    'align' => '',
                    'buttons' => array(
                        array(
                            'id' => 'remove',
                            'title' => '移除规则',
                            'callback' => 'rule_del',
                            'show_cond' => $response['strategy']['is_check'] == 1 ? '0' : '1'
                        ),
                        array('id' => 'edit',
                            'title' => '详情',
                            'act' => 'op/gift_strategy/rule_view',
                            'show_name' => '规则详情',
                            'show_cond' => 'obj.type == 1 || obj.type == 0',
                        ),
                        array('id' => 'ranking_edit',
                            'title' => '详情',
                            'act' => 'op/gift_strategy/ranking_rule_view',
                            'show_name' => '规则详情',
                            'show_cond' => 'obj.type == 2',
                        ),
                        array(
                            'id' => 'do_enable',
                            'title' => '启用',
                            'callback' => 'do_enable',
                            'show_cond' => 'obj.status != 1'
                        ),
                        array(
                            'id' => 'do_disable',
                            'title' => '停用',
                            'callback' => 'do_disable',
                            'show_cond' => 'obj.status == 1'
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '分组',
                    'field' => 'sort',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规则名称',
                    'field' => 'name',
                    'width' => '250',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规则类型',
                    'field' => 'type_txt',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '优先级',
                    'field' => 'level',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '互溶/互斥',
                    'field' => 'is_mutex_txt',
                    'width' => '100',
                    'align' => ''
                ),
                
            )
        ),
        'dataset' => 'op/GiftStrategy2DetailModel::get_by_page',
        'idField' => 'op_gift_strategy_detail_id',
        'params' => array(
            'filter' => array('strategy_code' => $response['strategy']['strategy_code']),
        ),
    ));
    ?>
</div>
    <?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var strategy_code = "<?php echo $response['strategy']['strategy_code']; ?>";
    var id = "<?php echo $response['_id']; ?>";
    function rule_del(index, row) {
        BUI.Message.Confirm('是否确定要移除此规则', function() {
            var data = {'op_gift_strategy_detail_id': row.op_gift_strategy_detail_id};
            $.post('<?php echo get_app_url('op/op_gift_strategy/del_detail'); ?>', data, function(data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (data.status == 1) {

                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();

                } else {
                    BUI.Message.Alert(data.message, function() {
                    }, type);
                }
            }, "json");
        }, 'warning');
    }
    function show_ovay_mode() {
        BUI.use('bui/overlay', function(Overlay) {
            var html_str = '<div class="show_scan_mode"><a class="button button-rule button-manz" href="javascript:fullGift(0)">满赠<i class="icon"></i></a>   ' +
                    '    <a class="button button-rule button-manz" href="javascript:fullGift(1)">买赠<i class="icon"></i></a>   ' +
                    '    <a class="button button-rule button-manz" href="javascript:fullGift(2)">排名送<i class="icon"></i></a>   ' +
                    '   </div><div class="show_scan_mode" style="color:red;">满赠针对订单金额而言；买赠针对订单商品而言</div>';
            var dialog = new Overlay.Dialog({
                title: '新增规则',
                width: 500,
                height: 220,
                mask: false,
                buttons: [],
                bodyContent: html_str
            });
            dialog.show();
        });

        $(".show_scan_mode").click(function() {
            $(".bui-ext-close").click();
        });

    }
    function fullGift(type) {
        var data = {
            'strategy_code': strategy_code,
            'type': type
        };
        $.post('<?php echo get_app_url('op/gift_strategy/do_add_detail'); ?>', data, function(data) {
                tableStore.load();
                if(type == 1 || type == 0){
                    var url = "?app_act=op/gift_strategy/rule_view&app_scene=edit&_id="+data.status;
                    openPage(window.btoa(url),url,'规则详情');
                }else if(type == 2){
                    var url = "?app_act=op/gift_strategy/ranking_rule_view&app_scene=edit&_id="+data.status;
                    openPage(window.btoa(url),url,'规则详情');
                }               
                
        }, "json");
    }
    function do_page() {
        location.href = "?app_act=op/gift_strategy/view&app_scene=edit&_id=" + id + "&show=1&strategy_code=" + strategy_code;

    }

    function do_enable(_index, row) {
        //校验有没有赠品商品
        var url = '<?php echo get_app_url('op/gift_strategy/is_gift_strategy_goods'); ?>';
        var data = {op_gift_strategy_detail_id: row.op_gift_strategy_detail_id,type:row.type, app_fmt: 'json'};
        $.post(url,data,function(ret){
            var type = ret.status == 1 ? 'success' : 'error';
            if(ret.status == -1){
                BUI.Message.Alert(ret.message,type);
                tableStore.load();
            } else {
                var url = '<?php echo get_app_url('op/gift_strategy/is_set_gift_num'); ?>';
                var data = {op_gift_strategy_detail_id: row.op_gift_strategy_detail_id, set_gift: '<?php echo $response['strategy']['set_gifts_num'];?>', app_fmt: 'json'};
                $.post(url,data,function(ret){
                        var type = ret.status == 1 ? 'success' : 'error';
                        if(ret.status == -1){
                        BUI.Message.Alert(ret.message,type);
                        tableStore.load();
                    }else{
                        var url = '<?php echo get_app_url('op/gift_strategy/update_rule_enable'); ?>';
                        var data = {op_gift_strategy_detail_id: row.op_gift_strategy_detail_id, status: 1, app_fmt: 'json'};
                        $.post(url, data, function(ret) {
                        tableStore.load();
                        }, 'json');
                    }
                }, 'json');              
            }
        },'json');
    }
    function do_disable(_index, row) {
        var url = '<?php echo get_app_url('op/gift_strategy/update_rule_enable'); ?>';
        var data = {op_gift_strategy_detail_id: row.op_gift_strategy_detail_id, status: 0, app_fmt: 'json'};
        $.post(url, data, function(ret) {
            tableStore.load();
        }, 'json');
    }


</script>
