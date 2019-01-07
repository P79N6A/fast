<style type="text/css">
    .well {
        min-height: 100px;
    }
    /*#searchForm .control-label{width:6em;}*/
</style>

<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title">
        <h2>组装商品列表</h2>
    </span>
        <span class="page-link">
            <a class="button button-primary" href="javascript:openPage('<?php echo base64_encode('?app_act=prm/goods/diy_goods_detail') ?>','?app_act=prm/goods/diy_goods_detail&ES_frmId=prm/goods/diy_goods_detail&action=do_diy_add','添加组装商品')" >添加组装商品</a>
            <!--<a class="button button-primary" href="javascript:openPage('P2FwcF9hY3Q9cHJtL2dvb2RzL2RldGFpbCZhY3Rpb249ZG9fYWRk','?app_act=prm/goods/diy_goods_detail&ES_frmId=prm/goods/diy_goods_detail&action=do_add','添加组装商品')" >添加组装商品</a>-->
            <button  class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
        </span>
        <span class="page-link" style="margin-right: 5px;">
            
            <a class="button button-primary" href="<?php echo $response['goods_issue'] == true ? '?app_act=increment' : 'http://www.baotayun.com' ?>" target="_blank" rel="noopener noreferrer" >商品上新</a>

        </span>

</div>
<div class="clear" style="margin-top: 40px; "></div>
<hr>
<div>
        <ul id="ToolBar1" class="toolbar frontool">
            <li class="li_btns"><button class="button button-primary btn_opt_pending" id="opt_enable" >批量启用</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_pending" id="opt_disable" >批量停用</button></li>
            <div class="front_close">&lt;</div>
        </ul>
    <script>
        $(function(){
            function tools(){
                $(".frontool").css({left:'0px'});
                $(".front_close").click(function(){
                    if($(this).html()=="&lt;"){
                        $(".frontool").animate({left:'-100%'},1000);
                        $(this).html(">");
                        $(this).addClass("close_02").animate({right:'-10px'},1000);
                    }else{
                        $(".frontool").animate({left:'0px'},1);
                        $(this).html("<");
                        $(this).removeClass("close_02").animate({right:'0'},1000);
                    }
                });
            }
            tools();
        });
    </script>
</div>
<script type="text/javascript">

    var ES_PAGE_ID = 'prm/goods/do_list';

    function PageHead_show_dialog(_url, _title, _opts) {

        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                 tableStore.load();                 if (typeof _opts.callback == 'function') _opts.callback();
            }
        }).show();
    }
</script>
<?php
$diy = array(
    '0' => '否',
    '1' => '是',
);
$keyword_type = array();
$keyword_type['goods_code'] = '组装商品编码';
$keyword_type['goods_name'] = '组装商品名称';
$keyword_type['goods_short_name'] = '组装商品简称';
$keyword_type['goods_produce_name'] = '出厂名称';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
    $buttons[] = array(
        'label' => '导出明细',
        'id' => 'exprot_detail',
    );

$diy = array_from_dict($diy);
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,

    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            'help'=>'商品编码支持多个查询，用逗号分隔'
        ),
        array(
            'label' => '分类',
            'type' => 'select_multi',
            'id' => 'category_code',
            'data' => $response['category'],
        ),
        array(
            'label' => '品牌',
            'type' => 'select_multi',
            'id' => 'brand_code',
            //'data'=>ds_get_select('brand_code'),
            'data' => $response['brand'],
        ),
        array(
            'label' => '年份',
            'type' => 'select_multi',
            'id' => 'year_code',
            'data' => ds_get_select('year'),
        ),
        array(
            'label' => '季节',
            'type' => 'select_multi',
            'id' => 'season_code',
            'data' => ds_get_select('season_code'),
        ),
        array(
            'label' => '商品属性',
            'type' => 'select_multi',
            'id' => 'goods_prop',
            'data' => $response['prop'],
        ),
        array(
            'label' => '启用状态',
            'type' => 'select',
            'id' => 'status',
            'data' => $response['status']
        ),
        array(
            'label' => '商品状态',
            'type' => 'select_multi',
            'id' => 'state',
            'data' => $response['state'],
        ),
        /*array(
            'label' => '组装商品',
            'type' => 'select_multi',
            'id' => 'diy',
            'data' => $diy,
        ),*/
        array(
            'label' => '更新时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'lastchanged_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'lastchanged_end',),
            )
        ),
    )
));
?>

<?php
$list = array(
    array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(
            /*
              array('id'=>'edit1', 'title' => '编辑1',
              'act'=>'pop:prm/brand/detail&app_scene=edit', 'show_name'=>'编辑',
              'show_cond'=>'obj.is_buildin != 1'), */
            array('id' => 'edit', 'title' => '编辑', 'callback' => 'do_edit', 'show_cond' => 'obj.status != 1'),
            array('id' => 'delete', 'title' => '删除',
                'callback' => 'do_delete', 'show_cond' => 'obj.status == 1', 'confirm' => '确认要删除吗？'),
            array('id' => 'enable', 'title' => '停用',
                'callback' => 'do_enable', 'show_cond' => 'obj.status != 1', 'confirm' => '确认要停用吗？'),
            array('id' => 'disable', 'title' => '启用',
                'callback' => 'do_disable', 'show_cond' => 'obj.status == 1',
                'confirm' => '确认要启用吗？'),
        ),
    ),
    array(
        'title' => '商品图片',
        'show' => 1,
        'type' => 'text',
        'width' => '100',
        'field' => 'goods_thumb_img',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '组装商品编码',
        'field' => 'goods_code',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '组装商品名称',
        'field' => 'goods_name',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '组装商品简称',
        'field' => 'goods_short_name',
        'width' => '200',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分类',
        'field' => 'category_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '品牌',
        'field' => 'brand_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '季节',
        'field' => 'season_name',
        'width' => '50',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '年份',
        'field' => 'year_name',
        'width' => '50',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品状态',
        'field' => 'state',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品属性',
        'field' => 'goods_prop',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '吊牌价格',
        'field' => 'sell_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '成本价',
        'field' => 'cost_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '批发价',
        'field' => 'trade_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '进货价',
        'field' => 'purchase_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '最低售价',
        'field' => 'min_price',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '生产周期',
        'field' => 'goods_days',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品描述',
        'field' => 'goods_desc',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '重量',
        'field' => 'weight',
        'width' => '100',
        'align' => ''
    ),
    array(
         'type' => 'text',
         'show' => 1,
         'title' => '是否组装商品',
         'field' => 'diy',
         'width' => '100',
         'align' => '',
         'format_js' => array(
             'type' => 'map',
             'value' => array(
                 '0' => '否',
                 '1' => '是',
             ),
         ),
     ),
    array(
        'type' => 'text',
        'show' => 0,
        'title' => '出厂名称',
        'field' => 'goods_produce_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 0,
        'title' => '保质期',
        'field' => 'period_validity',
        'width' => '100',
        'align' => ''
    ),

);
if(!empty($response['proprety'])) {
    foreach($response['proprety'] as $val) {
        $list[] = array('title' => $val['property_val_title'],
            'show' => 1,
            'type' => 'text',
            'width' => '80',
            'field' => $val['property_val']);
    }
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'prm/GoodsModel::get_by_page',
    'params' => array('filter' => array('user_id' => $response['user_id'],'diy'=>'1')),
    'queryBy' => 'searchForm',
    'idField' => 'goods_id',
    'customFieldTable' => 'goods_do_list/table',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_record_list_diy', 'name' => '组装商品列表','export_type' => 'file'),
    //'RowNumber'=>true,
    'CheckSelection'=>true,
    'ColumnResize' => true,
));
?>
<script type="text/javascript">
    //读取已选中项
    function get_checked(obj, func, type) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请先选择数据！", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.goods_id);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '批量操作',
            msg: '是否执行批量操作?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        func.apply(null, [ids]);
                        this.close();
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

    $("#opt_enable").click(function(){
        opt_set_active('enable');
    });
    $("#opt_disable").click(function(){
        opt_set_active('disable');
    });
    $('#exprot_detail').click(function(){
        var params = '';
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');
        params.ctl_type = 'export';
        params.ctl_export_conf = 'goods_record_list_detail';
        params.ctl_export_name =  '组装商品明细';
        <?php echo   create_export_token_js('prm/GoodsModel::get_by_page');?>
        var obj = searchFormForm.serializeToObject();
        for(var key in obj){
            params[key] =  obj[key];
        }

        for(var key in params){
            url +="&"+key+"="+params[key];
        }
        params.ctl_type = 'view';
        window.open(url);
    });
    function opt_set_active(active){
        get_checked($(this),function(ids){
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods/opt_update_active_diy'); ?>',
                data: {id: ids, type: active},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        })
    }

    function do_edit(_index, row) {
        openPage('<?php echo base64_encode('?app_act=prm/goods/diy_goods_detail&action=do_edit&goods_id=') ?>' + row.goods_id, '?app_act=prm/goods/diy_goods_detail&action=do_edit&goods_id=' + row.goods_id, '编辑');
        return;
    }
    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/update_active_diy'); ?>',
            data: {id: row.goods_id, type: active},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    /*
    * 商品删除
    * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
    */
    function do_delete(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/do_delete'); ?>',
            data: {goods_code: row.goods_code},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message);
                }
            }
        });
    }
    $(function(){
        $("body").on('mouseover','td>div>span>img',function(e){
            var img_src = $(this).data('goods-img');
            var tooltip = "<div id='tooltipimg' style='position:fixed;top:25%;left:25%;'> <img  width='500px' height='auto' src='"+ img_src +"' alt='原图'/> </div>";
            //创建 div 元素
            $('tbody').parent().parent().parent().parent().append(tooltip);
        }).mouseout(function(){
            $("#tooltipimg").remove(); //移除
        })
    })
</script>
