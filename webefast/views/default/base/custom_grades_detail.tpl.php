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
    $tabs[] = array('title' => '分销商设置', 'active' => false, 'id' => 'tabs_send');
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
                    array('title' => '分类代码', 'type' => 'input', 'field' => 'grade_code', 'remark' => $remark, 'edit_scene' => 'add'),
                    array('title' => '分类名称', 'type' => 'input', 'field' => 'grade_name'),
                    array('title' => '备注', 'type' => 'input', 'field' => 'remark'),
                ),
                'hidden_fields' => array(array('field' => 'grade_id')),
            ),
            'buttons' => array(
                array('label' => '提交', 'type' => 'submit'),
                array('label' => '重置', 'type' => 'reset'),
            ),
            'act_edit' => 'base/custom_grades/do_edit', //edit,add,view
            'act_add' => 'base/custom_grades/do_add',
            'data' => $response['data'],
            'callback' => 'goto_edit',
            'rules' => array(
                array('grade_code', 'require'),
                array('grade_name', 'require'),
            ),
        ));
        ?>
    </div>
    <?php if ($app['scene'] != 'add'): ?>
        <div>
            <div>
                <div class="btns">
                     <?php if ($app['scene'] != 'show_custom'): ?>
                    <button type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="show_select_goods(1, 0);" >添加</button>
                    <button  type="button" <?php if ($response['strategy']['is_check'] == 1) { ?>disabled <?php } ?> class="button button-primary btns"  onclick="delCustom();" >一键清空</button>
                    <?php endif; ?>
                </div>

                <div>
                    <?php
                    if($app['scene'] != 'show_custom'){
                        $list = array(
                            array(
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '100',
                                'align' => '',
                                'buttons' => array(
                                    array('id' => 'delete', 'title' => '移除', 'callback' => 'do_delete_grade', 'confirm' => '确认要删除此信息吗？'),
                                ),
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '分销商类型',
                                'field' => 'custom_type_name',
                                'width' => '200',
                                'align' => ''
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
                                'field' => 'custom_name',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                    } else {
                        $list = array(
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '分销商类型',
                                'field' => 'custom_type_name',
                                'width' => '200',
                                'align' => ''
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
                                'field' => 'custom_name',
                                'width' => '200',
                                'align' => ''
                            ),
                        );
                    }
                    

                    render_control('DataTable', 'table', array(
                        'conf' => array(
                            'list' => $list,
                        ),
                        'dataset' => 'base/CustomGradesModel::get_detail_by_page',
                        'idField' => 'grade_code',
                        'params' => array(
                            'filter' => array('grade_code' => $response['data']['grade_code']),
                        ),
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    var grade_code = "<?php echo $response['data']['grade_code'] ?>";
    function goto_edit(data, ES_frmId) {
        if (data.status == 1 && '<?php echo $app['scene']; ?>' == 'add') {
            window.location.href = "?app_act=base/custom_grades/detail&app_scene=edit&_id=" + data.data + "&ES_frmId=" + ES_frmId;
        } else {
            BUI.Message.Alert(data.message);
        }
    }
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $("#grade_code").attr("disabled", "disabled");

    form.on('beforesubmit', function() {
        $("#grade_code").attr("disabled", false);
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
//        var param = {store_code: '', is_diy: 0, select_combo: 1};
//        var url = '?app_act=base/custom/select&is_select=' + is_select;

        var url = '?app_act=wbm/notice_record/select_custom';
        var param = {};
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
                title: '选择分销商',
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
                //location.reload();
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

        var url = '?app_act=base/custom_grades/do_add_custom&app_fmt=json&grade_code=' + grade_code;

        $.post(url, {data: select_data}, function(result) {
            if (result.status != 1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function() {
                    //       _thisDialog.close();
                }, 'error');
            } else {
                if (type == 1) {
                    //top.skuSelectorStore.load();
                    tableStore.load();
                } else {
                    _thisDialog.close();
                }
            }

        }, 'json');

    }

//删除赠品
    function delCustom() {
        $.post('<?php echo get_app_url('base/custom_grades/delete_custom'); ?>', {'grade_code': grade_code}, function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {
                tableStore.load();
            } else {
                BUI.Message.Alert(data.message, function() {
                }, type);
            }
        }, "json");
    }
    
    function do_delete_grade(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/custom_grades/delete_grade_detail'); ?>', data: {id: row.id},
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
</script>
