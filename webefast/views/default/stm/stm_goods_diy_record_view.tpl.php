
<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:2px 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '商品组装单',
    'links' => array(
        array('url' => 'stm/stm_goods_diy_record/do_list', 'target' => '_self', 'title' => '商品组装单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/stm_goods_diy_record/do_check') ) { ?>
        <?php if ($response['data']['is_check'] == 0 && $response['data']['is_sure'] == 0) { ?>
            <li class="li_btns"> <a class="button button-primary" id="do_check"> 审核</a></li>
        <?php }?>
    <?php }?>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/stm_goods_diy_record/do_sure') ) { ?>
            <?php if (0 == $response['data']['is_sure'] && $response['data']['is_check'] == 1) { ?>
                <a class="button button-primary" id="do_uncheck"> 取消审核</a>
                <?php if (0 == $response['data']['is_wms']) { ?>
                <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['goods_diy_record_id']; ?>')"> 确认调整</a>
               <?php } ?>
               <?php } ?>
        <?php } ?>
    </li>

   <!-- <li class="li_btns"> <button type="button" class="button button-primary" value="返回" onclick="javascript:history.go(-1);"><i class="icon-backward icon-white"></i> 返回</button></li>-->

    <div class="front_close">&lt;</div>
</ul>
<script>
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
    })
</script>
<script>
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['goods_diy_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var lof_status = "<?php echo $response['lof_status']; ?>";
    var type = 1;
    var is_edit = true;
<?php if (1 == $response['data']['is_sure']) { ?>
        is_edit = false;
<?php } ?>
    var data = [
        {
            "name": "record_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input",
        },
        {
            "name": "order_time",
            "title": "下单时间",
            "value": "<?php echo $response['data']['order_time'] ?>",
        },
        {
            "name": "store_code",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>,
        },
        {
            "name": "order_time",
            "title": "单据类型",
            "value": "<?php echo $response['data']['record_type_name'] ?>",
        },
        {
            "name": "relation_code",
            "title": "关联调整单",
            "value": "<?php echo $response['data']['relation_code'] ?>",
        },
        {
            "name": "record_time",
            "title": "业务日期",
            "value": "<?php echo $response['data']['record_time'] ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "num",
            "title": "总数量",
            "value": "<?php echo $response['data']['num'] ?>",
        },
        {
            "name": "money",
            "title": "总金额",
            "value": "<?php echo $response['data']['money'] ?>",
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "title": "确认",
            "value": "<?php echo $response['data']['is_check_src'] ?>",
        },
    ];
    
    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=stm/stm_goods_diy_record/do_edit"
        });

        if (is_edit) {
            get_goods_sku_panel({
                "id": "btnSelectGoods",
                'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'diy': 1},
                "callback": add_detail
            });
            
            get_lof_no_select_inv_panel({
                "id": "btnSelectLofno",
                'param': { 'record_code':'<?php echo $response['data']['record_code'] ?>','store_code': '<?php echo $response['data']['store_code'] ?>'},
                "callback": add_detail_lof
        
            });

            $('#btnSearchGoods').on('click', function () {
                tabs_id = $("#TabPage1 li.active a").attr("id");
                if (lof_status == 1 && tabs_id == 'tabs_batch') {
                    table_lof_listStore.load({'code_name': $('#goods_code').val()});
                } else {
                    table_listStore.load({'code_name': $('#goods_code').val()});
                }
            });
        }
    });
    
    function add_detail(obj){
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                select_data[di] = value;
                di++;
            }
        });
        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        ajax_post({
            url: "?app_act=stm/stm_goods_diy_record/do_add_detail",
            data: {data: select_data, pid:<?php echo $response['data']['goods_diy_record_id']; ?>, store_code: '<?php echo $response['data']['store_code'] ?>'},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                if (data.status == "1") {
                    BUI.Message.Tip(data.message, type);
                } else {
                    BUI.Message.Alert(data.message, type);
                }
                if (typeof _thisDialog.callback == "function") {
                    _thisDialog.callback(this);
                }
            }
        })
        reload_page();
    }
    
    function add_detail_lof(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        var tabs_id = '';
        if($("#TabPage1 li").hasClass("active")){
            tabs_id = $("#TabPage1 li.active a").attr("id");
        }
        BUI.each(data, function (value, key) {
            var sku = top.$("input[name='sku_lof_" + value.sku + "']:checked").val();
            if(sku != '' && typeof(sku) != 'undefined'){
                if (value.lof_no != '' && value.production_date != '') {
                    select_data[di] = value;
                    di++;
                }else{
                    value.lof_no = top.$("#lof_no_"+value.sku).val();
                    value.production_date = top.$("#production_date_"+value.sku).val();
                    select_data[di] = value; 
                    di++;
                } 
            }
            
        });
        var diy_goods_select = top.$("#diy_goods_select input[name='diy_good']:checked").val();
        var diy_goods_lof_no = top.$("#diy_goods_select input[name='diy_good']:checked").data("lof-no");
        if(diy_goods_select.length  <= 0 || diy_goods_lof_no.length  <= 0 ){
            BUI.Message.Tip('选择组装商品为空', 'error');
            return ;
        }

        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        ajax_post({
            url: "?app_act=stm/stm_goods_diy_record/do_add_detail",
            data: {data: select_data, pid:<?php echo $response['data']['goods_diy_record_id']; ?>,diy_sku:diy_goods_select,diy_lof_no:diy_goods_lof_no, store_code: '<?php echo $response['data']['store_code'] ?>','tabs_id':tabs_id},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';

                if (data.status == "1") {
//                    BUI.Message.Tip(data.message, type);
//                    location.reload();
                } else {
                    BUI.Message.Alert(data.message, type);
                }
                if (typeof _thisDialog.callback == "function") {
                    _thisDialog.callback(this);
                }
            }
        });
        
    }

</script>

<div class="panel record_table" id="panel_html"></div>

<?php
$tabs = array(
    array('title' => '组装商品', 'active' => true, 'id' => 'tabs_nobatch'),
);
if ($response['lof_status'] == 1) {
    $tabs[] = array('title' => '指定批次子商品', 'id' => 'tabs_batch');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));
?>
<div class="panel">
    <div class="panel-s">
        <div class="row">
            <b>请输入</b>
            <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            
                <div style ="float:right;">
                    <?php if ($response['data']['is_check'] == 0){?>
                    <button type="button" class="button button-success" value="商品导入" id="btnimport"><i class="icon-plus-sign icon-white"></i> 导入商品</button>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                    <button type="button" class="button button-success" value="选择批次" id="btnSelectLofno" ><i class="icon-plus-sign icon-white"></i> 选择批次</button>
                    <?php } ?>
                </div>
            
        </div>
    </div>
</div>
<div id="TabPage1Contents">
    <div>
        <?php
        $list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec1_rename'],
                'field' => 'spec1_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => $response['goods_spec2_rename'],
                'field' => 'spec2_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '120',
                'align' => '',
                'id' => 'barcode'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单价',
                'field' => 'price',
                'width' => '120',
                'align' => ''
            ),
        );
        
        if($response['lof_status'] == 1){
            $list[] = array(
                'type' => 'text',
                'show' => 1,
                'title' => '批次号',
                'field' => 'lof_no',
                'width' => '120',
                'align' => ''
            );
            $list[] = array(
                'type' => 'text',
                'show' => 1,
                'title' => '生产日期',
                'field' => 'production_date',
                'width' => '120',
                'align' => ''
            ); 
        }
        $list[] = array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '120',
                'align' => '',
            ); 
        $list[] = array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'money',
                'width' => '80',
                'align' => '',
            ); 
        $list[] = array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'del',
                        'title' => '删除',
                        'callback' => 'do_delete_detail',
                        'show_cond' => 'obj.is_sure == 0 && obj.is_check == 0'
                    ),
                ),
            ); 
        
        render_control('DataTable', 'table_list', array(
            'conf' => array(
                'list' => $list
            ),
            'dataset' => 'stm/StmGoodsDiyRecordDetailModel::get_by_page',
            'idField' => 'goods_diy_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'],'type' => 'diy')),
          //  'CellEditing' => (1 == $response['data']['is_sure']) ? false : true,
        ));
        ?>
    </div>
    <?php if ($response['lof_status'] == 1): ?>
        <div>
            <?php
            render_control('DataTable', 'table_lof_list', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '组装商品名称',
                            'field' => 'diy_goods_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '组装条形码',
                            'field' => 'diy_barcode',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品名称',
                            'field' => 'goods_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品编码',
                            'field' => 'goods_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec1_rename'],
                            'field' => 'spec1_name',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec2_rename'],
                            'field' => 'spec2_name',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品条形码',
                            'field' => 'barcode',
                            'width' => '120',
                            'align' => '',
                            'id' => 'barcode'
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单价',
                            'field' => 'price1',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '批次号',
                            'field' => 'lof_no',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '生产日期',
                            'field' => 'production_date',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数量',
                            'field' => 'num',
                            'width' => '120',
                            'align' => '',
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '金额',
                            'field' => 'money',
                            'width' => '80',
                            'align' => '',
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '80',
                            'align' => '',
                            'buttons' => array(
                                array(
                                    'id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'do_delete_detail_lof',
                                    'show_cond' => 'obj.is_sure == 0 && obj.is_check == 0'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'stm/StmGoodsDiyRecordDetailModel::get_by_page_lof',
                'idField' => 'goods_diy_record_detail_id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'],'type' => 'lof')),
            ));
            ?>
        </div>
    <?php endif; ?>
</div>
<br><br>
<div class="panel">
    <div class="panel-header">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
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
                            'title' => '操作时间',
                            'field' => 'add_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '确认状态',
                            'field' => 'sure_status',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '120',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['goods_diy_record_id'], 'module' => 'stm_goods_diy_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    //确认
    function  do_sure(_index, goods_diy_record_id) {
        params = {id: goods_diy_record_id, type: 'enable'};
        $.post("?app_act=stm/stm_goods_diy_record/do_sure", params, function (data) {
            var msg = data.msg;
            var stock_adjust_record_id = data.stock_adjust_record_id;
            var status = data.status;
            if (status == '-1') {
                BUI.Message.Alert(data.message, 'error');
                return;
            }
            BUI.Message.Show({
                title: '自定义提示框',
                msg: msg,
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            window.location.reload();
//                            reload_page();
                            var url = '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=' + stock_adjust_record_id;
                            openPage(window.btoa(url), url, '调整单');
                            this.close();
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            //window.location.reload();
                            this.close();
                            reload_page();
                        }
                    }
                ]
            });
        }, "json");
    }
    
    $("#do_check").click(function(){
        var goods_diy_record_id = '<?php echo $response['data']['goods_diy_record_id']; ?>';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stm_goods_diy_record/do_check'); ?>',
            data: {id: goods_diy_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('审核成功！', type);
                   location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    })
    
    $("#do_uncheck").click(function(){
        var goods_diy_record_id = '<?php echo $response['data']['goods_diy_record_id']; ?>';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('stm/stm_goods_diy_record/do_uncheck'); ?>',
            data: {id: goods_diy_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('取消审核成功！', type);
                   location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    })
</script>

<script type="text/javascript">
    $("#btnSelectLofno").hide();
    $(function(){
        TabPage1Tab.on('click',function(ev){
            tabs_id = $("#TabPage1 li.active a").attr("id");
            if(tabs_id == 'tabs_nobatch'){
                $("#btnSelectGoods").show();
                $("#btnSelectLofno").hide();
            }else {
                $("#btnSelectLofno").show();
                $("#btnSelectGoods").hide();
            }
        });

        $('#btnimport').on('click', function () {
        url = "?app_act=stm/stm_goods_diy_record/import_goods&id="+id+"&lof_status="+lof_status;
        new ESUI.PopWindow(url, {
            title: "导入商品",
            width:880,
            height:400,
            onBeforeClosed: function() {
                location.reload();
                  //table_listStore.load();
                  //table_lof_listStore.load();
            },
            onClosed: function(){
                //刷新数据

            }
        }).show();
        });
    });




    function reload_page() {
        if (typeof (r) != 'undefined') {
            r.load_data();
        }
        if(lof_status == 1){
            table_lof_listStore.load();  
        }else{
            table_listStore.load();
        }
        logStore.load();
    }
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });
    
    

    //删除单据明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=stm/stm_goods_diy_record/do_delete_detail',
            data: {id: row.goods_diy_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                //batchStore.load({'code_name': ''});
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                    table_lof_listStore.load();
                } else {
                    location.reload();
                    //reload_page();
                }
            }
        });
    }

    function do_delete_detail_lof(_index, row) {
        ajax_post({
            url: "?app_act=stm/stm_goods_diy_record/do_delete_detail_lof",
            data: {id: row.goods_diy_record_detail_id, pid: row.pid},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Tip(data.message, type);
                if (data.status == "1") {
                    table_lof_listStore.load();
                }
            }
        })
    }
    if (typeof table_listCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_listCellEditing.on('accept', function (record, editor) {
            $.post('?app_act=stm/stm_goods_diy_record/do_edit_detail',
                    {pid: record.record.pid, sku: record.record.sku, num: record.record.num, price: record.record.price},
                    function (result) {
                        //window.location.reload();
                        reload_page();
                    }, 'json');
        });
    }
function get_lof_no_select_inv_panel(obj){
	var param = new Object();

	if(typeof obj.param != "undefined"){
		param = obj.param;
	}
	if(typeof(top.dialog)!='undefined'){
		top.dialog.remove(true);
	}
        var url = '?app_act=stm/stm_goods_diy_record/get_lof_no_select_inv_panel';
        var buttons = [
                      {
                        text:'保存退出',
                        elCls : 'button button-primary',
                        handler : function(){
                            this.callback = function(){
                                location.reload();
                            }
                            if(typeof obj.callback == "function"){
                                    obj.callback(this);
                            }
                        }
                      },{
                        text:'取消',
                        elCls : 'button',
                        handler : function(){
                           location.reload();
                        }
                      }
                    ];
	top.BUI.use('bui/overlay',function(Overlay){
		 top.dialog = new Overlay.Dialog({
		    title: '选择商品',
		    width: '80%',
		    //height: 400,
		    loader: {
		        url: url,
		        autoLoad: true, //不自动加载
		        params: param, //附加的参数
		        lazyLoad: false, //不延迟加载
		        dataType: 'text'   //加载的数据类型
		    },
			align: {
              //node : '#t1',//对齐的节点
              points: ['tc','tc'], //对齐参考：http://dxq613.github.io/#positon
              offset: [0,20] //偏移
            },
                    mask: true,
                    buttons:buttons
		});
     
                       top.dialog.on('closed',function(ev){
                      location.reload();
                  });
  
           var d_id = top.dialog.get('id');
		$("#"+obj.id).click(function(event) {
                        if(top.dialog.get('id')!=d_id){
                            top.dialog.set('id',d_id);
                            top.dialog.get('loader').set('url',url);
                            top.dialog.get('loader').set('params',param);
                            top.dialog.get('loader').load(param);
                            top.dialog.set('buttons',buttons);
                        }
                         top.dialog.show();
		});
    });
}
</script>