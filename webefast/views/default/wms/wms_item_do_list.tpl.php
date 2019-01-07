<?php
render_control('PageHead', 'head1', array('title' => '外包仓商品档案列表',
    'links' => array(
        array('url' => 'wms/wms_item/get_item', 'title' => '获取外包仓商品', 'is_pop' => true, 'pop_size' => '650,500'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword = array();
$keyword['barcode'] = '商品条形码';
$keyword['goods_code'] = '商品编码';
$keyword['goods_name'] = '商品名称';
$keyword['api_code'] = 'WMS商品ID';
$keyword = array_from_dict($keyword);
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
        )
    ),
    'fields' => array(
        array(
            'label' => 'WMS配置',
            'type' => 'select',
            'id' => 'wms_config_id',
            'data' => $response['wms_config'],
        ),
        array(
            'label' => '上传状态',
            'type' => 'select_multi',
            'id' => 'status',
            'data' => ds_get_select_by_field('wms_upload_status', 0),
        ),
        array(
            'label' => array('id' => 'keyword', 'type' => 'select', 'data' => $keyword),
            'type' => 'input',
            'title' => '',
            'data' => $keyword,
            'id' => 'keyword_value',
        ),
        array(
            'label' => '系统更新时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'upload_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'upload_time_end', 'remark' => ''),
            )
        ),
    )
));
?>

<div id="fail_div">
    
</div>

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
                'align' => 'center',
                'buttons' => array(
                    array(
                        'id' => 'upload',
                        'title' => '上传',
                        'callback' => 'upload_to_wms',
                        'confirm' => '确认要上传吗',
                    //'priv' => 'wms/wms_item/upload_item_to_wms',
                    //'show_cond' => "obj.wms_system_code==='qimen'"
                    ),
                    array(
                        'id' => 'update',
                        'title' => '更新',
                        'callback' => 'update_to_wms',
                        'confirm' => '确认要更新吗',
                    //'priv' => 'wms/wms_item/update_item_to_wms',
                    //'show_cond' => "obj.wms_system_code==='qimen'"
                    )
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配置名称',
                'field' => 'wms_config_name',
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
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '160',
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
                'title' => 'WMS商品ID',
                'field' => 'api_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上传状态',
                'field' => 'upload_status_txt',
                'width' => '80',
                'align' => 'center',
            //'format_js' => array('type' => 'map_checked')
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '最后上传的档案更新时间',
//                'field' => 'last_upload_time',
//                'width' => '150',
//                'align' => 'center'
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统更新时间',
                'field' => 'sys_lastchanged',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '错误信息',
                'field' => 'msg',
                'width' => '200',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'wms/WmsItemModel::get_by_page',
    'export' => array('id' => 'exprot_list', 'conf' => 'wms_item_list', 'name' => '外包仓商品档案',),
    'idField' => 'id',
    'queryBy' => 'searchForm',
    'CheckSelection' => true,
    'init' => 'nodata',
));
?>

<div>
    <ul class="toolbar frontool" id="ToolBar1">
        <li class="li_btns"><button class="button button-primary" id="batch_upload">批量上传</button></li>
        <li class="li_btns"><button class="button button-primary" id="batch_update">批量更新</button></li>
        <li class="front_close">&lt;</li>
    </ul>
</div>
<script type="text/javascript">
    $(function () {
        tools();
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            get_summary();
        });
        get_summary();
        function get_summary() {
            var obj = searchFormForm.serializeToObject();
            var url = "?app_act=wms/wms_item/get_count_by_status&app_fmt=json";
            $.post(url, obj, function (result) {
                if(result.data.data>0){
                   var str = "<a style='color:red;cursor:pointer;text-decoration:red underline;'>上传失败商品(<span id='goods_upload_fail_num'>"+result.data.data+"</span>)</a>";
                    $('#fail_div').html(str); 
                }else{
                    $('#fail_div').html(''); 
                }
            }, 'json');

        }
    });
    $(function(){
        $("#fail_div").click(function () {
            $("#status").val(4);
            $("#status_select_multi").find(".bui-select-input").click();
            status_select.get('picker').hide();
            $("#btn-search").click();
        });
    })
    function upload_to_wms(state, res) {
        upload(res, 'add');
    }
    function update_to_wms(state, res) {
        upload(res, 'update');
    }

    function upload(res, _type) {
        var url = '?app_act=wms/wms_item/';
        url += _type === 'add' ? 'upload_item_to_wms' : 'update_item_to_wms';

        var params = {wms_config_id: res.wms_config_id, skus: res.sku};
        $.post(url, {params: params}, function (ret) {
             var type = ret.status == "1" ? 'success' : 'error';
             BUI.Message.Alert(ret.message, type);
             if(ret.status >0){//成功
                 if(ret.data > 0){//失败数量大于0
                     $("#fail_div").html("<a style='color:red;cursor:pointer;text-decoration:red underline;'>上传失败商品(<span id='goods_upload_fail_num'>"+ret.data+"</span>)</a>");
                 }else{
                     $("#fail_div").html('');
                 }
             }
                    
            tableStore.load();
        }, "json");

    }

    $("#batch_upload,#batch_update").on('click', function () {
        var id = $(this).attr('id');
        get_checked($(this), function (params) {
            var act = 'wms/wms_item/upload_item_to_wms';
            var txt = '批量上传';
            if (id === 'batch_update') {
                act = 'wms/wms_item/update_item_to_wms';
                txt = '批量更新';
            }

            process_batch_task(act, txt, params, 'skus', 0, id);
        });
    });

    //读取已选中项
    function get_checked(obj, func) {
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择要上传的商品", "warning");
            return false;
        }

        var params = [];
        for (var i in rows) {
            var row = rows[i];
            var arr = {};
            arr.skus = row.sku;
            arr.wms_config_id = row.wms_config_id;
            params.push(arr);
        }

        func.apply(null, [params]);
    }

    $("#wms_config_id").change(function () {
        tableStore.load();
        $(".nodata").text('');//清除加载提示
    });

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
</script>
<?php include_once (get_tpl_path('common/process_batch_task')); ?>
