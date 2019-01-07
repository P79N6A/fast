<style type="text/css">
    .form-horizontal .control-label {
        display: inline-block;
        float: left;
        line-height: 30px;
        text-align: left;
        width: 100px;
    }
    .span11 {width: 550px;}

    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_pane2{width: 100%;border: solid 1px #ded6d9;margin-bottom: 5px;margin-top: 5px;}
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 15px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 15px;
        padding: 5px;
        text-align: left;
    }
    .table_pane2 td {
        border:1px solid #dddddd;
        line-height: 15px;
        padding: 5px;
        text-align: left;
        width:20%;
    }
    .table_panel_tt td{ padding:10px 5px;}
    .table_panel_tt2 td{ padding:10px 5px;}
    .btns{ text-align:right;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    .panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
    .panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
    .panel > .panel-header h3{ font-size:14px;}
    input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}
    .panel_div { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;margin-bottom: 5px;}

</style>


<?php
if ($response['app_scene'] == 'add')
    $remark = "一旦保存不能修改";
else
    $remark = "";
?>
<?php
$tabs = array(
    array('title' => '基本信息', 'active' => true, 'id' => 'tabs_base'),
);
$button = array();
if ($app['scene'] != 'add') {
    $tabs[] = array('title' => '商品列表', 'active' => false, 'id' => 'tabs_goods_list');
    $tabs[] = array('title' => '商品定价(分销商分类)', 'active' => false, 'id' => 'tabs_goods_price_custom_grade');
    $tabs[] = array('title' => '商品定价(指定分销商)', 'active' => false, 'id' => 'tabs_goods_price_custom');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));
?>
<div id="TabPage1Contents">
    <div>
        <?php
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => array(
                    array('title' => '产品线代码', 'type' => 'input', 'field' => 'goods_line_code', 'remark' => $remark, 'edit_scene' => 'add'),
                    array('title' => '产品线名称', 'type' => 'input', 'field' => 'goods_line_name'),
                ),
                'hidden_fields' => array(array('field' => 'id')),
            ),
            'buttons' => array(
                array('label' => '提交', 'type' => 'submit'),
                array('label' => '重置', 'type' => 'reset'),
            ),
            'act_edit' => 'fx/goods_manage/do_edit', //edit,add,view
            'act_add' => 'fx/goods_manage/do_add',
            'data' => $response['data'],
            'callback' => 'goto_edit',
            'rules' => array(
                array('goods_line_code', 'require'),
                array('goods_line_name', 'require'),
            ),
        ));
        ?>
    </div>
    <?php if ($app['scene'] != 'add'): ?>
        <div>
            <div>
                <div class="btns">
                    <?php if($app['scene'] != 'show_view'){?>
                    <button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="import_goods();" >导入</button>
                    <button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="show_select_goods(1, 0);" >添加</button>
                    <button  type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="delete_all_goods();" >一键清空</button>
                    <?php }?>
                </div>
                <div>
                    <?php
                     if($app['scene'] != 'show_view'){
                         $list = array(
                            array(
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '100',
                                'align' => '',
                                'buttons' => array(
                                    array('id' => 'delete', 'title' => '移除', 'callback' => 'do_delete_goods', 'confirm' => '确认要删除此信息吗？'),
                                ),
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品名称',
                                'field' => 'goods_name',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品规格',
                                'field' => 'spec_name',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品条形码',
                                'field' => 'goods_barcode',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                     } else {
                         $list = array(
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品名称',
                                'field' => 'goods_name',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品规格',
                                'field' => 'spec_name',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品条形码',
                                'field' => 'goods_barcode',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                         }


                        render_control('DataTable', 'table', array(
                            'conf' => array(
                                'list' => $list,
                            ),
                            'dataset' => 'fx/GoodsManageModel::get_goods_list',
                            'idField' => 'grade_code',
                            'params' => array(
                                'filter' => array('goods_line_code' => $response['data']['goods_line_code']),
                            ),
                        ));
                    ?>
                </div>
                <div style=" margin-top: 4%">
                    <span style="color: red;">说明：启用产品线设置商品后即代销订单中包含任意一款商品按照对应折扣计算订单结算金额若未设置商品即针对全部商品有效,若未设置产品线，订单结算金额按照分销商设置价格计算。</span>
                </div>
            </div>
        </div>
        <div>
            <div>
                <div class="btns">
                    <?php if($app['scene'] != 'show_view'){?>
                    <button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="add_custom_grade();" >添加</button>
                    <button  type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="del_all_custom_grade();" >一键清空</button>
                    <?php }?>
                </div>
                <div>
                    <?php
                    if($app['scene'] != 'show_view'){
                        $list = array(
                            array(
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '100',
                                'align' => '',
                                'buttons' => array(
                                    array('id' => 'delete', 'title' => '移除', 'callback' => 'delete_custom_grade', 'confirm' => '确认要删除此信息吗？'),
                                ),
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '分销商分类',
                                'field' => 'grade_name_new',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '折扣（基于吊牌价）',
                                'field' => 'rebates',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                    } else {
                        $list = array(
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '分销商分类',
                                'field' => 'grade_name_new',
                                'width' => '200',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '折扣（基于吊牌价）',
                                'field' => 'rebates',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                    }
                    
                    render_control('DataTable', 'table3', array(
                        'conf' => array(
                            'list' => $list,
                        ),
                        'dataset' => 'fx/GoodsManageModel::get_custom_grade_by_page',
                        'idField' => 'goods_line_code',
                        'params' => array(
                            'filter' => array('goods_line_code' => $response['data']['goods_line_code']),
                        ),
                    ));
                    ?>
                </div>
            </div>
            <div><span style="color: red;">注：指定分销商定价优先级高于分销商分类定价。</span></div>
        </div>
        <div>
            <div>
                <div class="btns">
                    <?php if($app['scene'] != 'show_view'){?>
                    <button  type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="del_all_custom();" >一键清空</button>
                    <!--<button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="import_custom();" >导入</button>-->
                    <button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="add_custom();" >添加</button>
                    <?php }?>
                </div>
                <div>
                    <?php
                        if($app['scene'] != 'show_view'){
                            $list = array(
                                array(
                                    'type' => 'button',
                                    'show' => 1,
                                    'title' => '操作',
                                    'field' => '_operate',
                                    'width' => '100',
                                    'align' => '',
                                    'buttons' => array(
                                        array('id' => 'delete', 'title' => '移除', 'callback' => 'delete_custom', 'confirm' => '确认要删除此信息吗？'),
                                    ),
                                ),
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '分销商代码',
                                    'field' => 'custom_code',
                                    'width' => '200',
                                    'align' => ''
                                ),
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '分销商名称',
                                    'field' => 'custom_name_new',
                                    'width' => '200',
                                    'align' => ''
                                ),
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '折扣（基于吊牌价）',
                                    'field' => 'rebates',
                                    'width' => '200',
                                    'align' => ''
                                ),
                            );
                        } else {
                            $list = array(
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '分销商代码',
                                    'field' => 'custom_code',
                                    'width' => '200',
                                    'align' => ''
                                ),
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '分销商名称',
                                    'field' => 'custom_name_new',
                                    'width' => '200',
                                    'align' => ''
                                ),
                                array(
                                    'type' => 'text',
                                    'show' => 1,
                                    'title' => '折扣（基于吊牌价）',
                                    'field' => 'rebates',
                                    'width' => '200',
                                    'align' => ''
                                ),
                            );
                        }
                    

                    render_control('DataTable', 'table4', array(
                        'conf' => array(
                            'list' => $list,
                        ),
                        'dataset' => 'fx/GoodsManageModel::get_custom_by_page',
                        'idField' => 'grade_code',
                        'params' => array(
                            'filter' => array('goods_line_code' => $response['data']['goods_line_code'], 'is_gift' => 0),
                        ),
                    ));
                    ?>
                </div>
            </div>
            <div><span style="color: red;">注：指定分销商定价优先级高于分销商分类定价。</span></div>
        </div>
    <?php endif; ?>
</div>


<script type="text/javascript">
    var goods_line_code = "<?php echo $response['data']['goods_line_code'] ?>";
    function goto_edit(data, ES_frmId) {
        if (data.status == 1 && '<?php echo $app['scene']; ?>' == 'add') {
            window.location.href = "?app_act=fx/goods_manage/detail&app_scene=edit&_id=" + data.data + "&ES_frmId=" + ES_frmId;
        } else {
            BUI.Message.Alert(data.message);
        }
    }
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $("#goods_line_code").attr("disabled", "disabled");

    form.on('beforesubmit', function() {
        $("#goods_line_code").attr("disabled", false);
    });

    $(document).ready(function() {
        $("#TabPage1Submit").find("#submit").click(function() {
            var data = new Object();
            $("#form1").find("input").each(function() {
                data[$(this).attr("id")] = $(this).val();
            });
            $("#form1").find("select").each(function() {
                data[$(this).attr("id")] = $(this).val();
            });
            data['is_effective'] = $('#form1 input[name="is_effective"]:checked').val();
            $("#form2").find("input").each(function() {
                data[$(this).attr("id")] = $(this).val();
            });
            $("#form2").find("select").each(function() {
                data[$(this).attr("id")] = $(this).val();
            });
            $.post("?app_act=base/custom/do_edit", data, function(ret) {
                BUI.Message.Alert(ret.message);
            }, 'json');
        });

    });
    var select_is_gift = 0;
    var select_is_select = 0;
    var select_url = '';
    var is_select = <?php if ($response['data']['type'] == 0) {
        echo 1;
    } else {
        echo 0;
    } ?>;
    function show_select_goods(is_gift) {
        select_is_gift = is_gift;
        select_is_select = is_select;
        var param = {store_code: '', is_diy: 0, select_combo: 1};
        var url = '?app_act=prm/goods/goods_select_tpl&is_select=' + is_select;

        if (typeof (top.dialog) != 'undefined') {
            if (url != select_url) {
                top.dialog.remove(true);
            } else {
                top.dialog.show();
                return;
            }
        }
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function() {
                    addgoods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function() {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function() {
                    this.close();
                }
            }
        ];

        top.BUI.use('bui/overlay', function(Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择商品',
                width: '80%',
                height: 450,
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function() {
                tableStore.load();
            });

            top.dialog.show();

        });
    }
    function addgoods(obj, type) {
        var select_data = {};
        if (select_is_select == 1) {
            select_data = top.SelectoGrid.getSelection();
        } else {
            var data = top.skuSelectorStore.getResult();
            var di = 0;
            BUI.each(data, function(value, key) {
                var num_name = 'num_' + value.sku;
                if (top.$("input[name='" + num_name + "']").val() != '' && top.$("input[name='" + num_name + "']").val() != undefined) {
                    value.num = top.$("input[name='" + num_name + "']").val();
                    select_data[di] = value;
                    di++;
                }
            });
        }
        var _thisDialog = obj;
        if (di == 0) {
            thisDialog.close();
            return;
        }

            var url = '?app_act=fx/goods_manage/do_add_goods&app_fmt=json' + '&goods_line_code=' + goods_line_code;

        $.post(url, {data: select_data}, function(result) {
            if (result.status != 1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function() {
                    //       _thisDialog.close();
                }, 'error');
            } else {
                if (type == 1) {
                    tableStore.load();
                } else {
                    _thisDialog.close();
                }
            }

        }, 'json');
    }
    
    function do_delete_goods(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_manage/do_delete_goods'); ?>', data: {goods_id: row.goods_id,'goods_line_code': goods_line_code},
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

//清空所有商品
    function delete_all_goods() {
        $.post('<?php echo get_app_url('fx/goods_manage/delete_all_goods'); ?>', {'goods_line_code': goods_line_code}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                tableStore.load();
            } else {
                BUI.Message.Alert(data.message, function() {
                }, type);
            }
        }, "json");
    }

    function import_goods() {
        var param = {};
        var url = '?app_act=fx/goods_manage/import_goods&goods_line_code=' + goods_line_code;
        new ESUI.PopWindow(url, {
            title: '导入商品',
            width: 500,
            height: 380,
            onBeforeClosed: function() {
                tableStore.load();
            }
        }).show();
    }
    
    function add_custom_grade(){
        var param = {};
        var url = '?app_act=fx/goods_manage/add_custom_grade&app_scene=add&goods_line_code=' + goods_line_code;
        new ESUI.PopWindow(url, {
            title: '添加分销商分类',
            width: 400,
            height: 400,
            onBeforeClosed: function() {
                table3Store.load();
            }
        }).show();
    }

    function delete_custom_grade(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_manage/delete_custom_grade'); ?>', data: {price_custom_grade_id: row.price_custom_grade_id,goods_line_code:row.goods_line_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    table3Store.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //清空分销商等级
    function del_all_custom_grade() {
        $.post('<?php echo get_app_url('fx/goods_manage/delete_all_custom_grade'); ?>', {'goods_line_code': goods_line_code}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                table3Store.load();
            } else {
                BUI.Message.Alert(data.message, function() {
                }, type);
            }
        }, "json");
    }
    
    function add_custom(){
        var param = {};
        var url = '?app_act=fx/goods_manage/add_custom&app_scene=add&goods_line_code=' + goods_line_code;
        new ESUI.PopWindow(url, {
            title: '添加分销商',
            width: 400,
            height: 400,
            onBeforeClosed: function() {
                table4Store.load();
            }
        }).show();
    }
    
    function delete_custom(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_manage/delete_custom'); ?>', data: {price_custom_id: row.price_custom_id,goods_line_code:row.goods_line_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    table4Store.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    //清空分销商
    function del_all_custom() {
        $.post('<?php echo get_app_url('fx/goods_manage/delete_all_custom'); ?>', {'goods_line_code': goods_line_code}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                table4Store.load();
            } else {
                BUI.Message.Alert(data.message, function() {
                }, type);
            }
        }, "json");
    }
    
    function import_custom() {
        var param = {};
        var url = '?app_act=fx/goods_manage/import_custom&goods_line_code=' + goods_line_code;
        new ESUI.PopWindow(url, {
            title: '导入分销商',
            width: 500,
            height: 380,
            onBeforeClosed: function() {
                table4Store.load();
            }
        }).show();
    }
</script>
