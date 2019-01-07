<?php echo load_js('comm_util.js')?>
<style>
#process_batch_task_tips div{height:300px;overflow-y:scroll;}
#record_time_start,#record_time_end,#pay_time_start,#pay_time_end,#plan_send_time_start,#plan_send_time_end{
    width:100px;
}
</style>
<?php
/*
render_control('PageHead', 'head1', array('title' => '缺货订单列表',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
        array('type'=>'js','js'=>'group_remove()','title'=>'一键解除缺货', 'is_pop'=>false,),
//        array('type'=>'js','js'=>'group_split()','title'=>'一键拆分订单', 'is_pop'=>false,),
    ),
    'ref_table' => 'table'
));
*/
?>
<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>缺货订单列表</h2></span>
	<span class="page-link">
                <span class="action-link">
                 <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/remove_short')) { ?>
              <!--
              <a href="javascript:group_remove()" class="button button-primary">一键解除缺货</a>
              -->
              <?php } ?>
        </span>
                <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
  if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/exprot_short_list')) {
	$buttons = array(

		   array(
		        'label' => '查询',
		        'id' => 'btn-search',
		           'type'=>'submit'
		    ),
		        array(
		        'label' => '导出',
		        'id' => 'exprot_list',
		    ),
                        array(
                        'label' => '导出商品',
                        'id' => 'exprot_detail',
                        ),
			array(
                        'label' => '汇总导出',
                        'id' => 'exprot_count',
                        ),
	         );
  }else{
  	$buttons = array(
  	
  			array(
  					'label' => '查询',
  					'id' => 'btn-search',
  					'type'=>'submit'
  			),
  			
  	);
  }
  $keyword_type = array();
  $keyword_type['deal_code_list'] = '交易号';
  $keyword_type['sell_record_code'] = '订单号';
  $keyword_type['buyer_name'] = '买家昵称';
  $keyword_type['receiver_mobile'] = '手机号码';
  $keyword_type['receiver_name'] = '收货人';
  $keyword_type['goods_code'] = '商品编码';
  $keyword_type['goods_name'] = '商品名称';
  $keyword_type['barcode'] = '商品条形码';
  $keyword_type['is_lock_person'] = '锁定人';
  $keyword_type = array_from_dict($keyword_type);
  
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,

    'show_row'=>1,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
            'help'=>'支持多交易号，多订单号查询，用逗号隔开；以下字段支持模糊查询：商品条形码、商品编码',
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
         'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => load_model('base/OrderLabelModel')->get_select(),
        ),                 
        array(                  
            'label' => '买家留言',
            'type' => 'input',
            'id' => 'buyer_remark',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '商家留言',
            'type' => 'input',
            'id' => 'seller_remark',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '国家',
            'type' => 'select',
            'id' => 'country',
            'data' => ds_get_select('country',2),
        ),
        array(
            'label' => '省份',
            'type' => 'select',
            'id' => 'province',
            'data' => array(),
        ),
        array(
            'label' => '城市',
            'type' => 'select',
            'id' => 'city',
            'data' => array(),
        ),
        array(
            'label' => '地区',
            'type' => 'select',
            'id' => 'district',
            'data' => array(),
        ),
        array(
            'label' => '详细地址',
            'type' => 'input',
            'id' => 'receiver_addr',
            'title'=>'支持模糊查询'
        ),
        array(
            'label' => '换货单',
            'type' => 'select',
            'id' => 'is_change_record',
            'data' => ds_get_select_by_field('boolstatus',2),
        ),
        array(
            'label' => '发票',
            'type' => 'select',
            'id' => 'is_invoice',
            'data' => ds_get_select_by_field('havestatus',2),
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '支付时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '计划发货时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'plan_send_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'plan_send_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'class' => 'input-small', 'remark' => ''),
            ),
        ),
    )
));
?>
<ul class="toolbar frontool" id="tools">
   <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/remove_short')) { ?>
    <li class="li_btns"><button class="button button-primary btn-opt-batch-remove-short" title="用于缺货商品库存补足后解除缺货状态使用，建议开启自动服务“缺货订单自动解除缺货”">批量解除缺货</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/force_remove_short')) { ?>
    <li class="li_btns"><button class="button button-primary btn-opt-batch-force-remove-short" title="用于系统无库存也需要继续操作发货业务，必须订单仓库开启“缺货商品允许发货”参数才可使用">批量强制解除缺货</button></li>
    <?php } ?>
    <!--<li><button class="button button-primary btn-group-remove">一键解除缺货</button></li>-->
     <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/split_short')) { ?>
    <li class="li_btns"><button class="button button-primary btn-opt-batch-short-split">批量拆分订单</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_express_code')) { ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_express_code">批量修改配送方式</button></li>
   <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_edit_store_code')) { ?>
    <li class="li_btns"><button class="button button-primary btn-opt-batch-edit-store-code">批量修改发货仓库</button></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/cancel_all_one')) { ?>
                <li class="li_btns"><button class="button button-primary btn_cancel">批量作废</button></li>
    <?php }?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/add_plan_record_check')) { ?>
        <li class="li_btns"><button class="button button-primary add_plan_record">生成采购订单</button></li>
    <?php }?>
    <!--<li><button class="button button-primary btn-group-split">一键拆分订单</button></li>-->
    <!--<li><button class="button button-primary">生成采购计划单</button></li>-->
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
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单状态',
                'field' => 'status_text',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'short_split',
                        'title' => '缺货拆分',
                        'callback' => 'short_split','priv'=>'sys/user/enable',
                        'confirm' => '确定要拆分订单？', 'show_cond'=>'obj.lock_inv_status == 2'),
                    array('id' => 'remove_short',
                        'title' => '解除缺货','priv'=>'sys/user/enable',
                        'callback' => 'remove_short',
                        'confirm' => '确定要解除缺货？'),
                    array('id' => 'force_remove_short',
                        'title' => '强制解除缺货','priv'=>'sys/user/enable',
                        'callback' => 'force_remove_short',
                        'confirm' => '确定要强制解除缺货？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
//                    'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}">{sell_record_code}</a>',
					'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_code',
                'width' => '75',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '135',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '90',
                'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '手机号码',
            		'field' => 'receiver_mobile',
            		'width' => '90',
            		'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付金额',
                'field' => 'paid_money',
                'width' => '75',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'goods_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '下单时间',
	            'field' => 'record_time',
	            'width' => '150',
	            'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '付款时间',
	            'field' => 'pay_time',
	            'width' => '150',
	            'align' => ''
            ),
            array(
	            'type' => 'text',
	            'show' => 1,
	            'title' => '计划发货时间',
	            'field' => 'plan_send_time',
	            'width' => '150',
	            'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
     'customFieldTable'=>'oms/sell_record_short_list',
     'export'=> array('id'=>'exprot_list','conf'=>'sell_record_quehuo','name'=>'缺货订单','export_type'=>'file'),
    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '120', 'field' => 'goods_name', 'format_js' => array('type' => 'function','value' => 'change_red',)),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '180', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '150', 'field' => 'num', 'format_js' => array('type' => 'function','value' => 'get_num',)),
            array('title' => '平台规格', 'type' => 'text', 'width' => '100', 'field' => 'platform_spec'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'),//查询展开详情的方法
            'detail_param' => 'sell_record_code',//查询展开详情的使用的参数
        ),
        'params' => 'sell_record_code',
    ),
    'params' => array(
        'filter' => array("stock_out_status"=>'0','left_join'=>'1','detail_list'=>'1'),
    ),
     'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<div style="height:100px;width:100%"></div>
<div id="searchAdv" style="display: none">
    <div class="row">
        <div class="control-group span8">
            <label class="control-label">缺货状态</label>
            <div class="controls">
                <div class="button-group" id="b1" >
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" >是否预售单</label>
            <div class="controls">
                <div class="button-group" id="b2" >
                </div>
            </div>
        </div>
       <!-- <div class="control-group span8">
            <label class="control-label" >采购计划单</label>
            <div class="controls">
                <div class="button-group" id="b3" >
                </div>
            </div>
        </div>-->
        <div class="control-group span8">
            <label class="control-label" >是否分销单</label>
            <div class="controls">
                <div class="button-group" id="b4" >
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" >是否已拆单</label>
            <div class="controls">
                <div class="button-group" id="b5" >
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" >买家留言</label>
            <div class="controls">
                <div class="button-group" id="b6" >
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" >商家留言</label>
            <div class="controls">
                <div class="button-group" id="b7" >
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="tbl_muil_check_ids" value=""/>

<script type="text/javascript">
    
$('#exprot_count').click(function () {
        var url =  '?app_act=sys/export_csv/export_show';   
        params = tableStore.get('params');
       
        params.ctl_type = 'export';
        params.ctl_export_conf = 'sell_record_short_goods_count';
        params.ctl_export_name =  '缺货商品汇总数据';
        <?php echo   create_export_token_js('oms/SellRecordModel::get_list_by_page');?>
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

    
    $('#exprot_detail').click(function () {
        var url =  '?app_act=sys/export_csv/export_show';   
        params = tableStore.get('params');
       
        params.ctl_type = 'export';
        params.ctl_export_conf = 'sell_record_short_goods_list';
        params.ctl_export_name =  '缺货商品导出';
        <?php echo   create_export_token_js('oms/SellRecordModel::get_list_by_page');?>
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
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>'+row.sell_record_code,'?app_act=oms/sell_record/view&ref=do&sell_record_code='+row.sell_record_code,'缺货订单详情');
    }
    function change_red(value, row, index){

        if( parseInt(row.lock_num)<parseInt(row.num)){
            for(var f in row){
                row[f] = "<span style='color:red'>"+row[f]+"</span>";
            }
            return "<span style='color:red'>"+value+"</span>";
        }
        return value;
//        return row.num+"("+row.lock_num+")";
    }
    function get_num(value, row, index){
        var lock_num =  parseInt($(row.lock_num).text());
        var  num = parseInt($(row.num).text());
        if(lock_num<num){
           var  str = "";
         var sell_record_code = $(row.sell_record_code).text();
          var  sku = $(row.sku).text();
            str ="<span style='color:red'>"+row.num+"("+row.lock_num+")"+"</span>"+'<a href="javascript:void(0)" onclick="inv_adjust(\''+sell_record_code+'\',\''+sku+'\')">库存调剂</a>';
            return str;
        }
        return row.num+"("+row.lock_num+")";
    }
    function inv_adjust(record_code,sku){
        var url = '?app_act=oms/sell_record_ajust/detail&record_code=' +record_code+"&sku="+sku;
        openPage(window.btoa(url),url,'库存调剂');
    }
    
    
    function toolbarmaker(Toolbar, children, id) {
        var g = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: children,
            render: '#' + id
        });
        g.render();
        g.on('itemclick', function(ev) {
            //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
        });
    }

    $(document).ready(function() {
        $("#sell_record_code").css("border","red 1px solid");
        $("#deal_code_list").css("border","red 1px solid");
        $("#searchForm").find(".row").eq(0).before($("#searchAdv").html())
        $("#searchAdv").remove()

        BUI.use('bui/toolbar', function(Toolbar) {
            //可勾选
            var b1 = [
                {content: '全部', id: 'all', selected: true},
                {content: '部分缺货', id: '2'},
                {content: '全部缺货', id: '0'},
            ];
            var b2 = [
                {content: '全部', id: 'all', selected: true},
                {content: '是', id: 'presale'},
                {content: '否', id: 'stock'}
            ];
            var b3 = [
                {content: '全部', id: 'all'},
                {content: '未生成', id: '0', selected: true},
                {content: '已生成', id: '1'}
            ];
            var b4 = [
                {content: '全部', id: 'all', selected: true},
                {content: '是', id: '1'},
                {content: '否', id: '0'}
            ];
            var b5 = [
                {content: '全部', id: 'all', selected: true},
                {content: '已拆单', id: '1'},
                {content: '未拆单', id: '0'}
            ];
            var b6 = [
                {content: '全部', id: 'all', selected: true},
                {content: '有留言', id: '1'},
                {content: '无留言', id: '0'}
            ];
            var b7 = [
                {content: '全部', id: 'all', selected: true},
                {content: '有留言', id: '1'},
                {content: '无留言', id: '0'}
            ];
            toolbarmaker(Toolbar, b1, 'b1');
            toolbarmaker(Toolbar, b2, 'b2');
            toolbarmaker(Toolbar, b3, 'b3');
            toolbarmaker(Toolbar, b4, 'b4');
            toolbarmaker(Toolbar, b5, 'b5');
            toolbarmaker(Toolbar, b6, 'b6');
            toolbarmaker(Toolbar, b7, 'b7');
        });

        tableStore.on('beforeload', function(e) {
            e.params.is_stock_out = $("#b1").find(".active").attr("id");
            e.params.is_persale = $("#b2").find(".active").attr("id");
            e.params.is_purchase = $("#b3").find(".active").attr("id");
            e.params.is_fenxiao = $("#b4").find(".active").attr("id");
            e.params.is_split_new = $("#b5").find(".active").attr("id");
            e.params.is_buyer_remark = $("#b6").find(".active").attr("id");
            e.params.is_seller_remark = $("#b7").find(".active").attr("id");
            tableStore.set("params",e.params);
        });

    });

    function show_detail(url,_this) {
        var param1 = $(_this).attr('param1');
        url += "&app_tpl=oms/sell_record_short_detail&app_page=NULL";
        if($("#tr"+param1).length == 0){
            $.get(url,function(ret){
                $(_this).find("span").attr('class','bar-btn-close');
                $(_this).parents("tr").after(ret);
            });
        }else{
            $(_this).find("span").attr('class','bar-btn-add');
            $("#tr"+param1).remove();
        }
    }

    function remove_action(sell_record_code){
        $.post('<?php echo $response['operate']['remove_short']; ?>',{sell_record_code:sell_record_code},function(ret){
            if(ret.status == '1'){
                alert("操作成功");
                window.location.reload();
            }else{
                alert("操作失败");
            }
        },'json');
    }


    function do_split(sell_record_code,mode){
        $.post('<?php echo $response['operate']['splite']; ?>',{mode:mode,sell_record_code:sell_record_code},function(ret){
            if(ret.status == '1'){
                alert("操作成功");
                window.location.reload();
            }else{
                alert(ret.message);
            }
        },'json');
    }

function short_split(_index, row){
    do_split(row.sell_record_code,2);
}

    function pre_split(_index, row){
        do_split(row.sell_record_code,3);
    }

    function remove_short(_index, row){
        var sell_record_code = row.sell_record_code;
        $.get('?app_act=oms/sell_record/remove_short&app_fmt=json&sell_record_code='+sell_record_code, function(ret) {
            if(ret.status == '1'){
                alert("解除缺货操作成功");
                window.location.reload();
            }else{
                alert(ret.message);
            }
        },'json');
    }

    function force_remove_short(_index, row){
        var sell_record_code = row.sell_record_code;
        $.get('?app_act=oms/sell_record/remove_short&app_fmt=json&force=1&sell_record_code='+sell_record_code, function(ret) {
            if(ret.status == '1'){
                alert("强制解除缺货操作成功");
                window.location.reload();
            }else{
                alert(ret.message);
            }
        },'json');
    }

    function group_action(){
        var sell_record_code = new Array();
        $("#table input[name='ckb_record_id']").each(function(){
            if($(this).attr("checked")){
                sell_record_code.push($(this).val());
            }
        });
        return sell_record_code;
    }

    function group_split(){
        do_split('',0);
    }
    function group_remove(){
        remove_action("REMOVE_A_KEY");
    }

	//读取已选中项
function get_checked(obj, act) {
    var ids = new Array();
    var idss = new Array();
    var idsss = new Array()
    var rows = tableGrid.getSelection();
    if (rows.length == 0) {
        BUI.Message.Alert("请选择订单", 'error');
        return;
    }
    for (var i in rows) {
        var row = rows[i];
        ids.push(row.sell_record_code);
    }
    $("#tbl_muil_check_ids").val(ids.join(','));
    if (obj.text() == '批量修改发货仓库') {
        for (var j in rows) {
            var row = rows[j];
            idss.push(row.sell_record_id);
        }
        act.apply(null, [idss]);
    } else if (obj.text() == '批量修改配送方式') {
        for (var j in rows) {
            var row = rows[j];
            idsss.push(row.sell_record_code);
        }
        act.apply(null, [idsss]);
    }else if(obj.text() == '生成采购订单'){
        for (var j in rows) {
            var row = rows[j];
            idsss.push(row.sell_record_code);
        }
        act.apply(null, [idsss]);
    }else {
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行订单' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        show_process_batch_task_plan(obj.text(), '<div id="process_batch_task_tips"><div>处理中，请稍等......');
                        process_batch_task(act);
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
}
    //批量修改配送方式
    function btn_init_edit_express_code() {
        var obj = $(".btn_opt_edit_express_code");
        get_checked(obj, function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                title: "批量修改配送方式",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    tableStore.load()
                }
            }).show()
        })
    }
    //批量修改发货仓库
    function btn_opt_edit_store_code() {
        var obj = $(".btn-opt-batch-edit-store-code");
        get_checked(obj, function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_code&sell_record_id_list=" + ids.toString(), {
                title: "批量修改发货仓库",
                width: 500,
                height: 250,
                onBeforeClosed: function () {},
                onClosed: function () {
                    tableStore.load()
                }
            }).show()
        })
    }


    function process_batch_task(act){
	    var ids = $("#tbl_muil_check_ids").val();
	    if (ids == ''){
		    $("#process_batch_task_tips div").append("<br/><span style='color:red'>批量任务执行完成。</span>");
                    tableStore.load()
		    return;
	    }
	    var ids_arr = ids.split(',');
	    var cur_id = ids_arr.pop();
	    $("#tbl_muil_check_ids").val();
	    $("#tbl_muil_check_ids").val(ids_arr.join(','));
	    if (act == 'remove_short'){
		     var ajax_url = "?app_fmt=json&app_act=oms/sell_record/"+act+"&sell_record_code="+cur_id;
	    }
	    if (act == 'short_split'){
		    var ajax_url = "?app_fmt=json&app_act=oms/sell_record/split&sell_record_code="+cur_id+"&mode=2";
	    }
	    if (act == 'force_remove_short'){
			var ajax_url = "?app_fmt=json&app_act=oms/sell_record/remove_short&sell_record_code="+cur_id+"&force=1";
		}
	    if (act == 'cancel'){
                var ajax_url = "?app_act=oms/sell_record/cancel_all_one&sell_record_code="+cur_id;
            }
	    $.get(ajax_url,function(result){
		    var result_obj = eval('('+result+')');
		    $("#process_batch_task_tips div").append("<br/>"+cur_id+' '+result_obj.message);
		    process_batch_task(act);
	    });
    }

	function show_process_batch_task_plan(title,content){
		BUI.use('bui/overlay',function(Overlay){
			var dialog = new Overlay.Dialog({
				title:title,
				width:500,
				height:400,
				mask:true,
				buttons:[],
				bodyContent:content
			});
			dialog.show();
		});
	}

    var url = '<?php echo get_app_url('base/store/get_area');?>';
    $(function(){
        $(".toolbar .btn-opt-batch-remove-short").click(function(){
			get_checked($(this),'remove_short');
        });
        $(".toolbar .btn-opt-batch-force-remove-short").click(function(){
			get_checked($(this),'force_remove_short');
        });
        $(".toolbar .btn-opt-batch-short-split").click(function(){
			get_checked($(this),'short_split');
        });
        $(".toolbar .btn_cancel").click(function(){
			get_checked($(this),'cancel');
        });
        $(".toolbar .btn-opt-batch-edit-store-code").click(function(){
            btn_opt_edit_store_code();
        });
        $(".toolbar .btn_opt_edit_express_code").click(function(){
            btn_init_edit_express_code();
        });
        //生成采购订单
        $(".toolbar .add_plan_record").click(function(){
            btn_add_plan_record();
        });
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
        tableStore.load();
    });

    function view(sell_record_code) {
	    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code;
	    openPage(window.btoa(url),url,'缺货订单详情');
    }


function btn_add_plan_record() {
    var obj = $(".add_plan_record");
    get_checked(obj, function (ids) {
        new ESUI.PopWindow("?app_act=oms/sell_record/add_plan_record&sell_record_code_list=" + ids.toString(), {
            title: "生成采购订单",
            width: 900,
            height: 600,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
}



</script>


