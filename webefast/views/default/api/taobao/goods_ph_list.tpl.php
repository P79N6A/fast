<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<style>
    #money_start{width:50px;}
    #money_end{width:50px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '淘宝商品铺货列表',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchSelectButton', 'select_button', array(
    'fields' => array(
        array('id' => 'relation_status', 'title' => '商品绑定', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '未绑定', 'id' => '0',),
                array('content' => '已绑定', 'id' => '1',),
            )),
        array('id' => 'goods_code_status', 'title' => '商家编码', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '未填写', 'id' => '0',),
                array('content' => '已填写', 'id' => '1',),
            )),
        array('id' => 'approve_status', 'title' => '商品状态', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '在库', 'id' => '0',),
                array('content' => '在售', 'id' => '1',),
            )),
        array('id' => 'sku_relation_status', 'title' => 'SKU绑定', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true,),
                array('content' => '未绑定', 'id' => '0',),
                array('content' => '已绑定', 'id' => '1',),
            )),
        array('id' => 'sku_status', 'title' => 'SKU商家编码', 'children' => array(
                array('content' => '全部', 'id' => 'all', 'selected' => true),
                array('content' => '未填写', 'id' => '0',),
                array('content' => '已填写', 'id' => '1',),
            )),
    ),
    'for' => 'searchForm',
    'style' => 'width:192px;'
));
?>
<?php
if (load_model('sys/PrivilegeModel')->check_priv('api/taobao/goods/ph_export_list')) {
    $buttons = array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    );
} else {
    $buttons = array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'fields' => array(
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao'),
        ),
        array(
            'label' => '商家编码',
            'type' => 'input',
            'id' => 'outer_id'
        ),
        array(
            'label' => 'SKU商家编码',
            'type' => 'input',
            'id' => 'sku_outer_id'
        ),
    )
));
?>

<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary outer_id_relation ">商家编码匹配</button></li>
    <li class="li_btns"><button class="button button-primary sku_gg_relation ">商品规格匹配</button></li>

    <!-- <li class="li_btns"><button class="button button-primary create_delivery ">店铺手工库存导入</button></li>
   <li class="li_btns"><button class="button button-primary create_delivery ">生成商品系统档案</button></li>
       <div class="front_close">&lt;</div> -->
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

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '',
                'field' => 'is_relation',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => array('0' => '<img src="assets/images/un_bind.png" height="20" width="20">', '1' => '<img src="assets/images/bind.png" height="20" width="20">'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '标识符numiid',
                'field' => 'num_iid',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品标题',
                'field' => 'title',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家编码',
                'field' => 'outer_id',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:edit_goods_code({num_iid})">{outer_id}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台售价',
                'field' => 'price',
                'width' => '80',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'approve_status_txt',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存',
                'field' => 'num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分类',
                'field' => 'cat',
                'width' => '155',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否有SKU',
                'field' => 'has_sku',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品更新时间',
                'field' => 'modified',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/taobao/GoodsModel::get_ph_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'num_iid',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_ph_list', 'name' => '淘宝商品铺货'),
//    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array(
                'type' => 'text', 'title' => '', 'width' => '50', 'field' => 'is_relation',
                'format_js' => array('type' => 'map', 'value' => array('0' => '<img src="assets/images/un_bind.png" height="20" width="20">', '1' => '<img src="assets/images/bind.png" height="20" width="20">', '2' => '<img src="assets/images/bind.png" height="20" width="20">'))
            ),
            array('title' => '淘宝标识符skuid', 'width' => '120', 'field' => 'sku_id'),
            array('title' => 'SKU商家编码', 'width' => '120', 'field' => 'outer_id',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:edit_goods_barcode({sku_id})">{outer_id}</a>',
                ),),
            array('title' => 'SKU平台规格', 'width' => '400', 'field' => 'properties_name'),
            array('title' => 'SKU平台售价', 'width' => '100', 'field' => 'price'),
            array('title' => 'SKU库存', 'width' => '100', 'field' => 'quantity'),
            array('title' => '平台SKU更新时间', 'width' => '150', 'field' => 'last_update_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('api/taobao/goods/get_sku_list_by_num_iid&app_fmt=json'),
        'params' => 'num_iid'
    ),
));
?>
<div style="clear:both"></div>
<div style="color: #F00">
    说明：<br>
    商家编码匹配：将平台上商家编码与系统中商品条形码/子条码进行匹配，通过此操作可检查平台商家编码是否填错；<br>
    商品规格匹配：在商家编码匹配基础上，将平台规格与系统中商品规格进行匹配，通过此操作精确检查商家编码是否串码；
</div>

<script type="text/javascript">
    function edit_goods_code(num_iid) {
        url = "?app_act=api/taobao/goods/update_code&num_iid=" + num_iid;
        _do_execute(url, 'table', '商家编码修改', 550, 350);
    }
    function edit_goods_barcode(sku_id) {
        url = "?app_act=api/taobao/goods/update_code&sku_id=" + sku_id;
        _do_execute(url, 'table', '商家编码修改', 550, 350);
    }

    $(".outer_id_relation").click(function () {
        $(".outer_id_relation").attr('disabled', 'disabled');
        $.post('<?php echo get_app_url('api/taobao/goods/do_relation'); ?>', {}, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $(".outer_id_relation").removeAttr('disabled');
            tableStore.load();
        }, "json");
    });

    $(".sku_gg_relation").click(function () {
        $(".sku_gg_relation").attr('disabled', 'disabled');
        $.post('<?php echo get_app_url('api/taobao/goods/do_relation_gg'); ?>', {}, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            $(".sku_gg_relation").removeAttr('disabled');
            tableStore.load();
        }, "json");
    });
</script>

<?php include_once (get_tpl_path('process_batch_task')); ?>