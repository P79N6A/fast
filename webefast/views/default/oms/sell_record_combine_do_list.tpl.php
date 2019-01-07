<?php echo load_js('comm_util.js') ?>
<?php render_control('PageHead', 'head1',
    array('title'=>'订单合并',
        'links'=>array(

        ),
        'ref_table'=>'table'
));?>


<?php
render_control('SearchForm', 'searchForm', array (
  'buttons' => 
  array (
    0 => 
    array (
      'label' => '查询',
      'id' => 'btn-search',
      'type'=>'submit'
    ),
  ),
  'show_row' => 3,
  'fields' => 
  array (
    1 => 
    array (
      'label' => '订单号',
      'type' => 'text',
      'id' => 'sell_record_code',
    ),
    2 => 
    array (
      'label' => '交易号',
      'type' => 'text',
      'id' => 'deal_code_list',
    ),
    3 => 
    array (
      'label' => '销售平台',
      'type' => 'select_multi',
      'id' => 'sale_channel_code',
      //'data' => load_model('base/SaleChannelModel')->get_select()
        'data' => load_model('base/SaleChannelModel')->get_my_select(),
    ),
    4 => 
    array (
      'label' => '店铺',
      'type' => 'select_multi',
      'id' => 'shop_code',
      'data' => load_model('util/FormSelectSourceModel')->get_shop(),
    ),
    5 => 
    array (
      'label' => '商品编码',
      'type' => 'text',
      'id' => 'goods_code',
      'title' => '支持模糊查询',
    ),
    6 => 
    array (
      'label' => '商品条形码',
      'type' => 'text',
      'id' => 'goods_barcode',
      'title' => '支持模糊查询',
    ),
    7 => 
    array (
      'label' => '买家留言',
      'type' => 'text',
      'id' => 'buyer_remark',
      'title' => '支持模糊查询',
    ),
    8 => 
    array (
      'label' => '商家留言',
      'type' => 'text',
      'id' => 'seller_remark',
      'title' => '支持模糊查询',
    ),
    9 => 
    array (
      'label' => '买家昵称',
      'type' => 'text',
      'id' => 'buyer_name',
      'title' => '支持模糊查询',
    ),
    10 => 
    array (
      'label' => '收货人',
      'type' => 'text',
      'id' => 'receiver_name',
      'title' => '支持模糊查询',
    ),
    11 => 
    array (
      'label' => '手机号码',
      'type' => 'text',
      'id' => 'receiver_mobile',
      'title' => '支持模糊查询',
    ),
    12 => 
    array (
      'label' => '配送方式',
      'type' => 'select_multi',
      'id' => 'express_code',
      'data' => load_model('util/FormSelectSourceModel')->get_comm_express(),
    ),
    13 => 
    array (
      'label' => '仓库',
      'type' => 'select_multi',
      'id' => 'store_code',
      'data' => load_model('util/FormSelectSourceModel')->get_store(),
    ),
    14 => 
    array (
      'label' => '国家',
      'type' => 'select',
      'id' => 'country',
//      'data' => load_model('util/FormSelectSourceModel')->get_country(),
        'data' => ds_get_select('country', 2)
    ),
    15 => 
    array (
      'label' => '省份',
      'type' => 'select',
      'id' => 'province',
      'data' => array(),
    ),
    16 => 
    array (
      'label' => '城市',
      'type' => 'select',
      'id' => 'city',
      'data' => array(),
    ),
    17 => 
    array (
      'label' => '地区',
      'type' => 'select',
      'id' => 'district',
      'data' => array(),
    ),
    18 => 
    array (
      'label' => '详细地址',
      'type' => 'text',
      'id' => 'receiver_address',
      'title' => '支持模糊查询',
    ),
    19 => 
    array (
      'label' => '换货单',
      'type' => 'select_multi',
      'id' => 'is_change_record',
      'data' => load_model('util/FormSelectSourceModel')->get_sell_change_record(),
    ),
    20 => 
    array (
      'label' => '发票',
      'type' => 'select_multi',
      'id' => 'invoice_status',
      'data' => load_model('util/FormSelectSourceModel')->get_sell_invoice_status(),
        //'data' => ds_get_select_by_field('havestatus'),
    ),
    21 => 
    array (
      'label' => '下单时间',
      'type' => 'group',
      'field' => 'daterange1',
      'child' => 
      array (
        0 => 
        array (
          'title' => 'start',
          'type' => 'date',
          'field' => 'record_time_start',
        ),
        1 => 
        array (
          'pre_title' => '~',
          'type' => 'date',
          'field' => 'record_time_end',
          'remark' => '',
        ),
      ),
    ),
    22 => 
    array (
      'label' => '支付时间',
      'type' => 'group',
      'field' => 'daterange1',
      'child' => 
      array (
        0 => 
        array (
          'title' => 'start',
          'type' => 'date',
          'field' => 'pay_time_start',
        ),
        1 => 
        array (
          'pre_title' => '~',
          'type' => 'date',
          'field' => 'pay_time_end',
          'remark' => '',
        ),
      ),
    ),
    23 =>
    array(
       'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => ds_get_select('order_label',4),
        ), 
  ),
));
?>

<?php 
render_control('DataTable', 'table', array (
  'conf' => 
  array (
    'list' => 
    array (
      0 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '订单编号',
        'field' => 'sell_record_code',
        'width' => '120',
        'align' => 'center',
        'format_js' => array(
            'type' => 'html',
      'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
        ),        
      ),
      1 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '平台',
        'field' => 'sale_channel_name',
        'width' => '65',
        'align' => 'center',
      ),
      2 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '120',
        'align' => 'center',
      ),
      3 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'deal_code_list',
        'width' => '130',
        'align' => 'center',
      ),
      4 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '收货人',
        'field' => 'receiver_name',
        'width' => '80',
        'align' => 'center',
      ),
      5 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '手机号',
        'field' => 'receiver_mobile',
        'width' => '120',
        'align' => 'center',
      ),
      6 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '收货地址',
        'field' => 'receiver_address',
        'width' => '220',
        'align' => '',
      ),
      7 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '仓库',
        'field' => 'store_name',
        'width' => '80',
        'align' => 'center',
      ),
      8 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '已付金额',
        'field' => 'paid_money',
        'width' => '70',
        'align' => 'center',
      ),
      9 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '商家留言',
        'field' => 'seller_remark',
        'width' => '140',
        'align' => 'center',
      ),
      10 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '买家留言',
        'field' => 'buyer_remark',
        'width' => '140',
        'align' => 'center',
      ),
      11 => 
      array (
        'type' => 'text',
        'show' => 1,
        'title' => '订单图标',
        'field' => 'status_text',
        'width' => '140',
        'align' => 'center',
      ),
      12 => 
        $fields[] = array (
        'type' => 'text',
        'show' => 1,
        'title' => '订单标签',
        'field' => 'tag_desc',
        'width' => '140',
        'align' => 'center',
      ),
    ),
  ),
  'dataset' => 'oms/OrderCombineViewModel::get_list_by_page',
  'queryBy' => 'searchForm',
  'idField' => 'sell_record_id',
  'customFieldTable'=>'oms/sell_record_combine_do_list',
  'CheckSelection' => true,

    'export'=> array('id'=>'exprot_list','conf'=>'search_record_list','name'=>'合并订单','export_type'=>'file'),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)',)),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode','format_js' => array('type' => 'map','value'=>array('stock'=>'否','presale'=>'是'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map','value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code'
    ),
    'CheckSelection'=>true,
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),  
));
?>

<ul id="ToolBar1" class="toolbar frontool">
   <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record_combine/do_list')) { ?>
  <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record_combine/opt_batch_combine',obj_name:'订单',ids_params_name:'sell_record_code','submit_all_ids_flag':1}">批量合并</button></li>
   <?php } ?>
     <div class="front_close">&lt;</div>
</ul>
<script>
$(function(){
  function tools(){
        $(".frontool").animate({left:'0px'},1000);
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
        $(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1000);
                $(this).html("<");
        $(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }
  
  tools();
})
</script>

<script type="text/javascript">
$("#sell_record_code").add("#deal_code_list").css("border","1px red solid");
  
function view(sell_record_code) {
    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
    openPage(window.btoa(url),url,'订单详情');
}

var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(document).ready(function() {
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
//        $('#country').val('1');
        $('#country').change();
    })


function showDetail(index, row) {
    openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>'+row.sell_record_code,'?app_act=oms/sell_record/view&ref=do&sell_record_code='+row.sell_record_code,'订单详情');
}

</script>

<?php include_once (get_tpl_path('process_batch_task'));?>