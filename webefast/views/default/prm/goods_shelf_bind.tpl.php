<style>
    .bui-pagingbar{float:left;}
    .div_title{padding:6px;font-weight:bold;}
</style>
<input type="hidden" id="goods_inv_id" value="<?php echo $request['_id']; ?>" />
<?php echo load_js('comm_util.js') ?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'type' => 'select',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_select(1),
        ),
        array(
            'label' => '库位编码/名称',
            'title' => '库位编码/名称',
            'type' => 'input',
            'id' => 'shelf_code'
        ),
    )
));
?>

<div class="row"  >

    绑定商品条码：<span id="barcode"><?php echo $response["barcode"]; ?></span>
    <?php if ($response["lof_status"] == 1): ?>
        批次号：<span id="lof_no"><?php echo $response["lof_no"]; ?></span>
    <?php endif; ?>
</div>





<div class="row-fluid">
    <div class="span12">
        <div class="div_title">可选库位列表</div>
        <?php
        render_control('DataTable', 'DataTable2', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '库位代码',
                        'field' => 'shelf_code_txt',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '库位名称',
                        'field' => 'shelf_name',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '所属仓库',
                        'field' => 'store_name',
                        'width' => '100',
                        'align' => ''
                    ),
                )
            ),
            'dataset' => array('prm/GoodsShelfModel::get_shelf_by_page', array($request['_id'])),
            'queryBy' => 'searchForm',
            'idField' => 'shelf_code',
            'CheckSelection' => true,
            'params' => array('filter' => array('sku' => $response['sku'])),
        ));
        echo "<div id='div_pgbar3'></div>";
        ?>
    </div>

    <div class="span1">
        <a href="javascript:_add();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0114;</a>
        <a href="javascript:_remove();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0112;</a>
    </div>

    <div class="span11">
        <div class="div_title">已选库位列表</div>
        <?php
        render_control('DataTable', 'DataTable3', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '库位代码',
                        'field' => 'shelf_code_txt',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '库位名称',
                        'field' => 'shelf_name',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '所属仓库',
                        'field' => 'store_name',
                        'width' => '100',
                        'align' => ''
                    ),
                )
            ),
            'dataset' => array('prm/GoodsShelfModel::get_shelf_by_page2', array($request['_id'])),
            'idField' => 'shelf_code',
            'CheckSelection' => true,
            'params' => array('filter' => array('sku' => $response['sku'])),
        ));
        echo "<div id='div_pgbar6'></div>";
        ?>
    </div>
</div>

<!--<div class="clearfix" style="text-align: center; padding: 5px;">-->
<!--    <button class="button button-primary" id="btn_ok">确定</button>-->
<!--</div>-->


<script type="text/javascript">
    var selectWindow = null;
    var codes = {};

    function set_pagebar() {

        $("#bar3").wrap("<div id='t_bar3'></div>");
        $("#bar6").wrap("<div id='t_bar6'></div>");

        $('#div_pgbar3').html($('#t_bar3').html());
        $('#div_pgbar6').html($('#t_bar6').html());

        $('#t_bar3').empty();
        $('#t_bar6').empty();
    }
    $(document).ready(function () {
        //    setTimeout("set_pagebar()",1000);

        DataTable2Store.on('beforeload', function (e) {
            e.params.ex_list = codes;
        })

        DataTable3Store.on('beforeload', function (e) {
            e.params.in_list = codes;
        })

//        $("#btn_ok").click(function(){
//            if(codes.length == 0) {
//                BUI.Message.Alert('请至少选择一条记录', 'info')
//                return ;
//            }
//            var params = {
//                "goods_inv_id": <?php echo $request['_id']; ?>,
//                "shelf_code_list": codes
//            };
//
//            $.post("?app_act=prm/goods_shelf/bind_action", params, function(data){
//                BUI.Message.Alert(data.message, 'info')
////                if(data.status == 1) ui_closePopWindow("<?php echo $request['ES_frmId'] ?>")
//            }, "json")
//        })
    });


    function  _bind() {
        if (codes.length == 0) {
            BUI.Message.Alert('请至少选择一条记录', 'info')
            return;
        }
        var rows = DataTable2Grid.getSelection();
        var params = {
            "goods_inv_id": <?php echo $request['_id']; ?>,
            "skuCode": '<?php echo $response["sku"]; ?>',
            "batch_number": '<?php echo $response["lof_no"]; ?>',
            "shelf_code_list": codes,
            "shelf_info":rows,
        };
        $.post("?app_act=prm/goods_shelf/bind_action", params, function (data) {
            BUI.Message.Alert(data.message, 'info');
            DataTable2Store.load();
            DataTable3Store.load();

        }, "json")

    }


    function _remove() {

        var sel_role_id_arr = new Array();
        $("#DataTable3 .bui-grid-row-selected input").each(function () {
            sel_role_id_arr.push($(this).val());

            if (typeof codes[$(this).val()] != "undefined") {
                delete codes[$(this).val()]
            }
        });

        if (sel_role_id_arr.length == 0) {
            BUI.Message.Alert('请至少选择一条记录', 'info')
            return;
        }

        console.log(codes)

        _unbind(sel_role_id_arr);
        var obj = {'start': 1};
        DataTable2Store.load(obj);
        DataTable3Store.load(obj);
    }

    function _add() {

        var sel_role_id_arr = new Array();
        $("#DataTable2 .bui-grid-row-selected input").each(function () {
            sel_role_id_arr.push($(this).val());

            if (typeof codes[$(this).val()] == "undefined") {
                codes[$(this).val()] = $(this).val();
            }
        });
        if (sel_role_id_arr.length == 0) {
            BUI.Message.Alert('请至少选择一条记录', 'info')
            return;
        }

        _bind();
        var obj = {'start': 1};
        DataTable2Store.load(obj);
        DataTable3Store.load(obj);
    }

    function _unbind(sel_role_id_arr) {

        var sku = '<?php echo $response['sku']; ?>';

        var rows = DataTable3Grid.getSelection();

        for (var i in rows) {
            var row = rows[i];
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods_shelf/new_unbind'); ?>', data: {sku: sku, store_code: row.store_code, shelf_code: row.shelf_code},
                success: function (ret) {
//                    var type = ret.status == 1 ? 'success' : 'error';
                    DataTable2Store.load();
                    DataTable3Store.load();
                }
            });
        }

//        if (type == 'success') {
//            BUI.Message.Alert('解除成功：', type);
//            tableStore.load();
//        } else {
//            BUI.Message.Alert(ret.message, type);
//        }

    }

    $(function () {
        $(".control-label").css("width", "100px");
        $(".control-group").css("width", "42%");

    })
</script>