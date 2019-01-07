<style>
    #shelf_name{
        width: 151px;
    }
    #clear_shelf{
        position: absolute;
        right: 31px;
        top: 1px;
        border:none;
        border-left:1px solid rgba(128, 128, 128, 0.64);
        height: 24px;
    }
    .icon-remove{
        position: absolute;
        right: 4px;
        top: 4px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品库位管理',
    'links' => array(
        array('url' => 'prm/goods_shelf/scanning_unbind', 'title' => '扫描解除绑定', 'is_pop' => true, 'pop_size' => '600,300'),
        array('url' => 'prm/goods_shelf/a_key_unbind', 'title' => '一键解除绑定', 'is_pop' => true, 'pop_size' => '400,300'),
        array('url' => 'prm/goods_shelf/import', 'title' => '库位商品导入(按条码)', 'is_pop' => true, 'pop_size' => '500,400'),
        array('url' => 'prm/goods_shelf/import_bygoods_code', 'title' => '库位商品导入(按商品编码)', 'is_pop' => true, 'pop_size' => '500,400')
    ),
    'ref_table' => 'table'
));
?>

<?php
// $lof_status = $response['lof_status'];
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
            'value' => $response['category_code_val'],
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            'data' => ds_get_select('brand_code'),
            'value' => $response['brand_code_val'],
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(),
        ),
        array(
            'label' => '库位',
            'type' => 'group',
            'field' => 'shelf',
            'child' => array(
                array('type' => 'input','field'=>'shelf_name','readonly'=>1,'remark' => "<span class='x-icon x-icon-normal' id = 'clear_shelf' title='清除选中库位' ><i class='icon-remove'></i></span><a href='#' id = 'base_shelf'><img src='assets/img/search.png'></a><input type='hidden' id='shelf_code'>"),
            ),
        ),
        array(
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'goods_code',
            'value' => $response['goods_code_val'],
        ),
        array(
            'label' => '商品名称',
            'type' => 'input',
            'id' => 'goods_name',
            'value' => $response['goods_name_val'],
        ),
        array(
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'barcode',
            'value' => $response['barcode_val'],
        ),
    )
));
?>

<ul class="nav-tabs oms_tabs" style="margin-bottom:6px;">
    <li class="active"><a href="#" onClick="do_page('do_list');">已绑定</a></li>
    <li><a href="#" onClick="do_page('ex_list');" >未绑定</a></li>
</ul>
<?php if (load_model('sys/PrivilegeModel')->check_priv('base/goods_shelf/opt_nubind')) { ?>          
    <ul class="toolbar frontool" id="ToolBar2">
         <li class="li_btns"><button class="button button-primary" id="opt_unbind">批量解除绑定</button></li>
         <li class="front_close">&lt;</li>
     </ul>                  
<?php } ?>
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
    });
</script>
<?php
if (isset($response['lof_status']) && $response['lof_status'] == '1') {
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '150',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'unbind', 'title' => '解除绑定', 'callback' => 'do_unbind', 'confirm' => '确认要解除绑定吗？'),
                        array('id' => 'bind', 'title' => '绑定库位', 'callback' => 'do_bind'),
                    ),
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '100',
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
                    'title' => $response['goods_spec1_rename'],
                    'field' => 'spec1_name',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'],
                    'field' => 'spec2_name',
                    'width' => '50',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '批次',
                    'field' => 'batch_number',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '生产日期',
                    'field' => 'production_date',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库',
                    'field' => 'store_name',
                    'width' => '100',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '库位',
                    'field' => 'shelf_code',
                    'width' => '100',
                    'align' => '',
                ),
            )
        ),
        'dataset' => 'prm/GoodsShelfModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'goods_shelf_id',
        'params' => array('filter' => array('category_code' => $response['category_code_val'], 'brand_code' => $response['brand_code_val'], 'goods_code' => $response['goods_code_val'], 'goods_name' => $response['goods_name_val'], 'barcode' => $response['barcode_val'])),
        'export' => array('id' => 'exprot_list', 'conf' => 'goods_bind_shelf_list', 'name' => '商品已绑定库位列表', 'export_type' => 'file'),
            //'RowNumber'=>true,
        'CheckSelection'=>true,
    ));
} else {
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '100',
                    'align' => ''
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
                    'title' => $response['goods_spec1_rename'],
                    'field' => 'spec1_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'],
                    'field' => 'spec2_name',
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '仓库',
                    'field' => 'store_name',
                    'width' => '100',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '库位',
                    'field' => 'shelf_code',
                    'width' => '100',
                    'align' => '',
                ),
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '200',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'unbind', 'title' => '解除绑定', 'callback' => 'do_unbind', 'confirm' => '确认要解除绑定吗？'),
                        array('id' => 'bind', 'title' => '绑定库位', 'callback' => 'do_bind'),
                    ),
                )
            )
        ),
        'dataset' => 'prm/GoodsShelfModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'goods_shelf_id',
        'params' => array('filter' => array('category_code' => $response['category_code_val'], 'brand_code' => $response['brand_code_val'], 'goods_code' => $response['goods_code_val'], 'goods_name' => $response['goods_name_val'], 'barcode' => $response['barcode_val'])),
        'export' => array('id' => 'exprot_list', 'conf' => 'goods_shelf_list', 'name' => '商品已绑定库位列表', 'export_type' => 'file'),
            //'RowNumber'=>true,
            // 'RowNumber'=>true,
        'CheckSelection'=>true,       
    ));   
}
?>
<script type="text/javascript">
    $(function () {
        $("#shelf_code").attr("value","");
        $("#shelf_name").attr("value","");
    });
    $("#base_shelf").click(function () {
        show_select('shelf');
    });
    $("#clear_shelf").click(function () {
        $("#shelf_code").attr("value","");
        $("#shelf_name").attr("value","");
    });
    function show_select(_type) {
        var store_code = $("#store_code").val();
        var param = {'store_code':store_code};
        var url = '?app_act=oms/sell_record/select_shelf';
        var title='请选择库位';
        if (typeof (top.dialog) !== 'undefined') {
            top.dialog.remove(true);
        }
        var buttons = [
            {
                text:'保存继续',
                elCls : 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#shelf_code');
                    top.tablesStore.load();
                    var string_name = $("#shelf_name").val();
                    var string_code = $("#shelf_code").val();
                    if (string_name !== '') {
                        store_shelf_name(string_name,'name');
                        store_shelf_name(string_code,'code');
                    }
                }
            },
            {
                text:'保存退出',
                elCls : 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#shelf_code');
                    var string_name = $("#shelf_name").val();
                    var string_code = $("#shelf_code").val();
                    if (string_name !== '') {
                        store_shelf_name(string_name,'name');
                        store_shelf_name(string_code,'code');
                    }
                    this.close();
                }
            },
            {
                text:'取消',
                elCls : 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: title,
                width: '800',
                height: '500',
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
            top.dialog.on('closed', function (ev) {

            });
            top.dialog.show();
        });
    }
    //去重
    function store_shelf_name(string_name,type) {
        string = string_name.split(',');
        var hash=[],arr=[];
        for (var i = 0,elem;(elem=string[i])!=null; i++) {
            if(!hash[elem]){
                arr.push(elem);
                hash[elem]=true;
            }
        }
        if (type === 'code') {
            $("#shelf_code").val(arr.join(','));
        }else{
            $("#shelf_name").val(arr.join(','));
        }
    }
    function deal_data_1(obj, _type) {
        var shelf_code = new Array();
        var shelf_name = new Array();
        var string_code = "";
        var string_name = "";
        string_code = $("#shelf_code").val();
        string_name = $("#shelf_name").val();
        $.each(obj, function (i, val) {
            shelf_code[i] = val[_type + '_code'];
            shelf_name[i] = val[_type + '_name']+"["+val[_type + '_code']+"]";
        });
        shelf_code = shelf_code.join(',');
        shelf_name = shelf_name.join(',');
        if (string_code == "") {
            string_code = shelf_code;
            string_name = shelf_name;
            $("#shelf_code").val(string_code);
            $("#shelf_name").val(string_name);
        } else {
            string_code = string_code + ',' + shelf_code;
            string_name = string_name + ',' + shelf_name;
            $("#shelf_code").val(string_code);
            $("#shelf_name").val(string_name);
        }
    }
    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).trigger(e);
    }
    tableStore.on('beforeload', function (e) {
        e.params.shelf_code = $("#shelf_code").val();
        tableStore.set("params", e.params);
    });
    
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择商品", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.goods_shelf_id);
        }      
        ids.join(',');      
        BUI.Message.Show({
            title : '批量操作',
            msg : '是否执行商品'+obj.text()+'?',
            icon : 'question',
            buttons : [
                {
                    text:'是',
                    elCls : 'button button-primary',
                    handler : function(){
                        func.apply(null, [ids])
                    }
                },
                {
                    text:'否',
                    elCls : 'button',
                    handler : function(){
                        this.close();
                    }
                }
            ]
        });       
    }   
    $('#opt_unbind').click(function(){
        get_checked($(this),function(ids){
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods_shelf/opt_unbind'); ?>', data: {goods_shelf_id: ids},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('解除成功：', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        })                 
    });

    function do_unbind(index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_shelf/unbind'); ?>', data: {goods_shelf_id: row.goods_shelf_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('解除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //绑定
    function do_bind(index, row) {
        new ESUI.PopWindow("?app_act=prm/goods_shelf/bind&sku=" + row.sku.toString() + "&_id=" + row.goods_shelf_id.toString(), {
            title: "绑定库位",
            width: 800,
            height: 600,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load()
            }
        }).show()
    }

    function a_key_unbind() {
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '确定需要一键解除实物库存为0的商品库位关联吗?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        $.ajax({type: 'POST', dataType: 'json',
                            url: '<?php echo get_app_url('prm/goods_shelf/a_key_unbind'); ?>', data: {},
                            success: function (ret) {
                                if (ret.status == 1) {
                                    BUI.Message.Alert('解除成功：', 'success');
                                    tableStore.load();
                                } else {
                                    BUI.Message.Alert(ret.message, 'error');
                                }
                            }
                        });
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
    }
    function do_page(param) {
        location.href = "?app_act=prm/goods_shelf/" + param + "&category_code=" + $("#category_code").val() + "&brand_code=" + $("#brand_code").val() + "&goods_code=" + $("#goods_code").val() + "&goods_name=" + $("#goods_name").val() + "&barcode=" + $("#barcode").val()+"&ES_frmId=prm/goods_shelf/do_list";
    }
</script>
