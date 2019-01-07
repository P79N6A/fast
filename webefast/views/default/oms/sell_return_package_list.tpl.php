<style type="text/css">
   .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '退货包裹单',
    'links' => array(
        array('url'=>'oms/sell_return/package_add', 'title'=>'新增退货包裹单'),
       // array('url'=>'oms/sell_return/package_scan', 'title'=>'退货包裹单扫描', 'is_pop'=>true, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['return_express_no'] = '快递单号';
$keyword_type['sell_record_code'] = '关联订单号';
$keyword_type['deal_code'] = '关联交易号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['sell_return_code'] = '关联退单号';
$keyword_type['return_package_code'] = '系统包裹单号';
$keyword_type['return_mobile'] = '手机号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['return_name'] = '退货人';
$keyword_type['remark'] = '备注';
$keyword_type = array_from_dict($keyword_type);

$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
) ;
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_return/export_package_list')) {
    $buttons[] =  array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
render_control('SearchForm', 'searchForm', array(

    'buttons' =>$buttons,
    'show_row'=>3,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '退货仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'return_express_code',
            'data' => ds_get_select('express'),
        ),

        array(
            'label' => '单据标识',
            'type' => 'select',
            'id' => 'tag',
            'data' => ds_get_select_by_field('tag'),
        ),
        array(
            'label' => '绑定退单',
            'type' => 'select',
            'id' => 'relation_return',
            'data' => ds_get_select_by_field('relation_return'),
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'stock_date_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'stock_date_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '是否换货',
            'type' => 'select',
            'id' => 'is_exchange_goods',
            'data' => ds_get_select_by_field('relation_return'),
        ),
        array(
            'label' => '验收入库人',
            'type' => 'input',
            'id' => 'receive_person',
            'data' => '',
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待收货', 'active' => true, 'id' => 'wait_receive'),
        array('title' => '已收货', 'active' => false, 'id' => 'having_receive'), // 默认选中active=true的页签
        array('title' => '已作废', 'active' => false, 'id' => 'canceled'),
    ),
    //'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<!--<ul class="toolbar" id="tool" style="z-index: 100;text-align: center;position: fixed;bottom: 0px;left:-100%;width:100%;background-color:#fff">
    <li><button class="button button-primary btn-opt-store_in">批量入库</button></li>
</ul>-->
<script>
    $("#tool").animate({left:'-500px'},1000);

</script>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据标识',
                'field' => 'tag_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (
                    array('id'=>'view', 'title' => '查看', 'callback' => 'view'),
//                    array('id'=>'store_in', 'title' => '入库', 'callback' => 'store_in'),
//                    array('id'=>'cancel', 'title' => '作废', 'callback' => 'cancel'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'return_order_status_txt',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '包裹单号',
                'field' => 'return_package_code_href',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'stock_date',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联退单号',
                'field' => 'sell_return_code_href',
                'width' => '140',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '170',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_code_name',
                'width' => '130',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'return_express_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'return_express_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号',
                'field' => 'return_mobile',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_tel',
		),
                
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_name',
		),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货人',
                'field' => 'return_name',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_return_name',
		),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家退单说明',
                'field' => 'return_buyer_memo',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '卖家退单备注',
                'field' => 'return_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '标签',
                'field' => 'tag_desc',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收入库时间',
                'field' => 'receive_time',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收入库人',
                'field' => 'receive_person',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否换货',
                'field' => 'is_exchange_goods_str',
                'width' => '80',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/ReturnPackageModel::get_package_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'return_package_id',

 'export'=> array('id'=>'exprot_list','conf'=>'return_package_list','name'=>'退货包裹单','export_type' => 'file'),
    'CascadeTable' => array(
		'list' => array(
			array('title' => '商品图片','type' => 'text','width' => '120','field' => 'goods_thumb_img_src'),
                        array('type' => 'text','title' => '商品名称','field' => 'goods_name','width' => '120',),
                        array('type' => 'text','title' => '商品编码','field' => 'goods_code','width' => '120',),
                        array('type' => 'text','title' => '规格1','field' => 'spec1_name','width' => '80',),
                        array('type' => 'text','title' => '规格2','field' => 'spec2_name','width' => '80',),
                        array('type' => 'text','title' => '商品条形码','field' => 'barcode','width' => '130','id' => 'barcode'),
                        array('type' => 'text','title' => '买家申请退货数量','field' => 'apply_num', 'width' => '120',),
                        array('type' => 'text','title' => '实际退货数(入库数)','field' => 'num','width' => '130',),
		),
		'page_size' => 10,
		'url' => get_app_url('oms/sell_return/get_detail_list_by_sell_record_code&app_fmt=json'),
		'params' => 'return_package_code',
	),
    'customFieldTable'=>'oms/sell_return_package_list',
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>

<script type="text/javascript">
    $(document).ready(function() {
        //TAB选项卡
        $("#TabPage1 a").click(function() {
            tableStore.load();
        });
        tableStore.on('beforeload', function(e) {
            e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            tableStore.set("params", e.params);
        });
//        tableStore.load();
    });


    function view(_index, row) {
        var url = '?app_act=oms/sell_return/package_detail&return_package_code=' +row.return_package_code;
        openPage(window.btoa(url),url,'退货包裹单详情');
    }

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_return/package_detail&return_package_code=') ?>'+row.return_package_code,'?app_act=oms/sell_return/package_detail&ref=do&return_package_code='+row.return_package_code,'退货包裹单详情');
    }
    
    //手机号
    function check_tel(value, row, index){
        if(value.indexOf('***')>-1){
                return set_show_text(row,value,'return_mobile');
        }else{
            return value;
        }
    }
    
    //买家昵称
     function check_name(value, row, index){
        if(value.indexOf('***')>-1){
                return set_show_text(row,value,'buyer_name');
        }else{
            return value;
        }
    }
    //退货人
    function check_return_name(value, row, index){
        if(value.indexOf('***')>-1){
                return set_show_text(row,value,'return_name');
        }else{
            return value;
        }
    }
    
    function set_show_text(row,value,type){
        return '<span class="like_link" onclick =\"show_info(this,\''+row.return_package_code+'\',\''+type+'\');\">'+value+'</span>';
    }
    
    function show_info(obj,return_package_code,key){
        var url = "?app_act=oms/sell_return/get_package_key_data&app_fmt=json";
         $.post(url,{'return_package_code':return_package_code,key:key},function(ret){
             if(ret[key]==null){
                  BUI.Message.Tip('解密出现异常！', 'error');
                 return ;
             }
             $(obj).html(ret[key]);
             $(obj).attr('onclick','');

             $(obj).removeClass('like_link');
        },'json');
    }

</script>