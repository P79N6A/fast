<style>
    .add-goods{margin-top:50px;}
    #goods_priority{
        width: 60px;
    }
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>

<div class="add-goods">
    <div class="row">
        <div style="float: left;">	
            <div id="select_express" style="float: left;">
                <label>指定快递：</label>
                <select id="appoint_express" name="appoint_express">
                    <?php // foreach ($response['express_data'] as $val): ?>
                        <!--<option value="<?php // echo $val['express_code']; ?>"><?php // echo $val['express_name']; ?></option>-->
                    <?php // endforeach; ?>
                </select>
            </div>
            <div style="float: right;">
                <lable>优先级：</lable>
                <input type="text" name="goods_priority" id="goods_priority">
                <input type="hidden" name="goods_priority_hidden" id="goods_priority_hidden">
                <img height="25" width="25" title="用于订单包含多个商品且多个商品指定的快递不一致，取优先级高的快递适配！优先级数字越大优先级越高。" alt="" src="assets/images/tip.png">
            </div>
        </div>
        <div style ="float:right;">
            <button type="button" class="button button-success" value="商品导入" id="btnimport" onclick="express_goods_import()"  ><i class="icon-plus-sign icon-white"></i>商品导入</button>
            <button type="button" class="button button-success" value="添加商品" id="btnSelectGoods" onclick="show_select_goods()"  ><i class="icon-plus-sign icon-white"></i>新增商品</button>&nbsp;
            <button type="button" class="button button-success" value="一键清空" id="btnDeleteAll" ><i class="icon-plus-sign icon-white"></i>一键清空</button>
        </div>
    </div>
</div>

<?php
render_control('PageHead', 'head1', array('title' => '商品指定快递',
    'links' => array(
        array('url' => 'crm/express_strategy/do_list', 'title' => '订单快递适配策略'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete_goods',
                        'confirm' => '确定要删除吗？',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格1',
                'field' => 'spec1_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格2',
                'field' => 'spec2_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '组装商品',
                'field' => 'is_diy_html',
                'width' => '80',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'crm/OpExpressByGoodsModel::get_by_page',
    'idField' => 'op_express_id',
    'params' => array('filter' => array('pid' => $response['id'])),
));
?>
<br/>
<span style="color:red;">提示：只要订单中包含任意一款指定商品即匹配指定快递。</span>
<script type="text/javascript">
    var priority_arr = '';
    $(function () {
        do_all_express();
        $('#goods_priority').change(function () {
            if($('#appoint_express').val() == '') {
                BUI.Message.Alert('请先选择快递','error');
                $('#goods_priority').val(1);
                return false;
            }
            var goods_priority = $('#goods_priority').val();
            if (goods_priority == '' || goods_priority == undefined) {
                $('#goods_priority').val($('#goods_priority_hidden').val());
                return false;
            } else if (isNaN(goods_priority) || !(/^(\+|-)?\d+$/.test(goods_priority)) || goods_priority <= 0) {
                BUI.Message.Alert('请输入正整数', 'error');
                $('#goods_priority').val($('#goods_priority_hidden').val());
                return false;
            }
            var express_code = $('#appoint_express').val();

            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('crm/express_strategy/do_update_priority'); ?>',
                data: {goods_priority: goods_priority, express_code: express_code},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        do_all_express();
                        BUI.Message.Alert('更新成功', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        })
    })

    tableStore.on('beforeload', function (e) {
        e.params.express_code = $('#appoint_express').val();
        tableStore.set("params", e.params);
    });

    $('#appoint_express').change(function () {
        tableStore.load();
        var express_code = $('#appoint_express').val();
        $('#goods_priority').val(priority_arr[express_code]);
        $('#goods_priority_hidden').val(priority_arr[express_code]);
//        $.ajax({type: 'POST', dataType: 'json',
//            url: '<?php // echo get_app_url('crm/express_strategy/do_goods_priority'); ?>',
//            data: {express_code: express_code},
//            success: function (ret) {
//                $('#goods_priority').val(ret.data);
//                $('#goods_priority_hidden').val(ret.data);
//            }
//        });
    });

    var is_create = 0;
    function show_select_goods() {
        if($('#appoint_express').val() == '') {
            BUI.Message.Alert('请先选择快递','error');
            return false;
        }
        if (is_create == 0) {
            create_select_goods();
        }
        top.dialog.show();
    }

    function create_select_goods() {
        var is_select = 1;
        var param = {store_code: '', is_diy: 0};
        var url = '?app_act=prm/goods/goods_select_tpl&is_select=' + is_select+'&list_type=goods_matching_express';
        if (typeof top.dialog !== 'undefined') {
            top.dialog.remove(true);

        }

        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 1);
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];

        top.BUI.use('bui/overlay', function (Overlay) {
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
                buttons: buttons,
            });
            top.dialog.on('closed',function(){
                top.skuSelectorStore.load();
            });
           
        });
        is_create = 1;

    }
    var select_is_select = 1;
    function addgoods(obj, type) {
        var appoint_express = $('#appoint_express').val()
        var select_data = {};
        var _thisDialog = obj;
        select_data = top.SelectoGrid.getSelection();

        if (select_data.length < 1) {
            _thisDialog.close();
            return;
        }

        var url = '?app_act=crm/express_strategy/do_add_goods&app_fmt=json&appoint_express=' + appoint_express;
        $.post(url, {data: select_data}, function (result) {
            if (result.status != 1) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                }, 'error');
            } else {
                tableStore.load();
                if (type == 1) {
                    top.skuSelectorStore.load();
                } else {
                    _thisDialog.close();
                }
            }
        }, 'json');
    }

    function do_delete_goods(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('crm/express_strategy/do_delete_goods'); ?>', data: {id: row.op_express_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    $("#btnDeleteAll").click(function () {
        if($('#appoint_express').val() == '') {
            BUI.Message.Alert('请先选择快递','error');
            return false;
        }
        var express_code = $('#appoint_express').val();
        BUI.Message.Confirm('确认要删除该配送方式下全部商品么？', function () {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('crm/express_strategy/do_delete_all_goods'); ?>', 
                data: {express_code:express_code},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, 'question');
        return;
    })
    
    function do_all_express() {
        var express_code = $('#appoint_express').val();
        $.ajax({type :'POST',dataType: 'json',
            url: '<?php echo get_app_url('crm/express_strategy/do_all_express'); ?>', 
            data: {},
            success: function (ret) {
                var express = ret.express;
                priority_arr = ret.priority;
                var html = '<option value="" >请选择</option>';
                for(var i = 0; i < express.length; i++) {
                    if(express[i]['express_code'] == express_code) {
                        html += "<option value = '"+express[i]['express_code']+"' selected = 'selected'>"+express[i]['express_name']+"</option>";
                    } else {
                        html += "<option value = '"+express[i]['express_code']+"'>"+express[i]['express_name']+"</option>";
                    }
                }
                $('#appoint_express').html(html);
            }
        });
    }

    function express_goods_import(){
        if($('#appoint_express').val() == '') {
            BUI.Message.Alert('请先选择快递','error');
            return false;
        }
        var id = $('#appoint_express').val();
        var type=3;
        var excel_tpl='express_goods_import';
        var url = '?app_act=prm/goods/record_import&id=' + id + "&type=" + type+"&excel_tpl="+excel_tpl;
            new ESUI.PopWindow(url, {
                title: '商品导入',
                width: 500,
                height: 380,
                onBeforeClosed: function () {
                    tableStore.load();
                }
            }).show();

    }
</script>
