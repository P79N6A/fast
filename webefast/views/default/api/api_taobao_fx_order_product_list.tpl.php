<?php
render_control('PageHead', 'head1', array('title' => '淘宝分销商品列表',
    'links' => array(
        array('url' => 'api/api_taobao_fx_order/down_product', 'title' => '商品下载', 'is_pop' => true, 'pop_size' => '800,500'),
      //  array('url' => 'api/sys/goods/upload&app_scene=add', 'title' => '一键库存同步', 'is_pop' => true, 'pop_size' => '800,600'),
    ),
    'ref_table' => 'table'
));
?>
<div class="clear" style="margin-top: 40px; "></div>
<style>
    #num_start,#num_end{
        width:60px;
    }
    .detail_main td{
        width:200px;
        text-align: right;
        padding:10px;
    }
	#sort{ margin-top:8px; font-size:12px; border-collapse:inherit; color:#666;}
    #sort td.sort_btn{
		border:1px solid #d5d5d5;
		padding:0 15px;
    	text-align:center;
    	cursor:pointer;
		height:24px;
		border-radius:3px;
		position:relative;
		
    }
    td#header{
    	padding:0 15px;
		text-align:center;
		height:24px;
		color:#ef8742;
		font-size:14px;
    }
</style>

<?php

//库存同步
$is_synckc = array(
                '' => '全选',
		'0' => '否',
		'1' => '是',
);
$is_synckc = array_from_dict($is_synckc);
//商品状态
$status = array(
                '' => '全选',
		'down' => '在库',
		'up' => '在售',
);
$status = array_from_dict($status);
$keyword_type = array();
$keyword_type['goods_code'] = '产品商品编码';
$keyword_type['goods_barcode'] = '平台规格编码';
$keyword_type['pid'] = '产品ID';
$keyword_type['sku_id'] = '平台SKUID';
$keyword_type = array_from_dict($keyword_type);

render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(

   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'
    ),
    array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),
         ) ,

    'fields' => array(
        
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',															  
            'title'=>'支持模糊查询',														
            'data'=>$keyword_type, 															
            'id' => 'keyword',															
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
			'data' => load_model('base/ShopModel')->get_purview_tbfx_shop(),
        ),
        
        array(
            'label' => '产品名称',
            'type' => 'input',
            'id' => 'goods_name'
        ),
        
        array(
            'label' => '商品状态',
            'type' => 'select',
            'id' => 'status',
            'data'=>$status,
        ),
        
        /*array(
            'label' => '产品商品编码',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array(
            'label' => '平台规格编码',
            'type' => 'input',
            'id' => 'goods_barcode'
        ),*/
        
        
        array(
            'label' => '是否同步库存',
            'type' => 'select',
            'id' => 'is_snyc',
           'data'=>$is_synckc,
        ),
        
    )
));
?>

<ul class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active($(this),'enable')">批量允许库存同步</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="active($(this),'disable')">批量禁止库存同步</button></li>
    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="multi_sync($(this))">批量库存同步</button></li>
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
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'send_again', 'priv' => 'api/sys/goods/fenxiao_sync_goods_inv', 'title' => '库存同步', 'callback' => 'sync'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此分销商品吗？','priv'=>'api/api_taobao_fx_order/do_delete'),
                ),
            ),
        /*
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步',
                'field' => 'is_allow_sync_inv',
                'width' => '60',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),*/
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上架',
                'field' => 'status',
                'width' => '60',
               'format_js' => array(
            				'type' => 'map',
            				'value' => array(
            						'up'=>'<img src="'.get_theme_url('images/ok.png',true).'" />',
            						'down'=>'<img src="'.get_theme_url('images/no.gif',true).'" />',
            				),
            		),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品ID',
                'field' => 'pid',
                'width' => '120',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品商品编码',
                'field' => 'outer_id',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'name',
                'width' => '420',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '采购价(元)',
                'field' => 'cost_price',
                'width' => '80',
                'align' => '',
                //'editor'=>"{xtype : 'select', items: ".json_encode($expressList)."}"
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存',
                'field' => 'quantity',
                'width' => '80',
                'align' => ''
            ),
  
          
        )
    ),
    'dataset' => 'api/FxTaoBaoProductModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pid',
	'CascadeTable'=>array(
		'list'=>array(
            array('title'=>'平台SKUID','width' => '200', 'field'=>'id'),
			array('title'=>'平台规格编码', 'width' => '200','field'=>'outer_id'),
			array('title'=>'经销采购价(元)', 'field'=>'dealer_cost_price'),
            array('title' => '平台库存', 'field' => 'quantity'),
            array('title' => '最后同步库存数量', 'field' => 'inv_num'),
            array('title' => '最后同步库存时间', 'field' => 'inv_update_time'),
            array('title' => '允许同步库存', 'field' => 'is_allow_sync_inv', 'format_js' => array('type' => 'function', 'value' => 'get_is_allow_sync_inv')),
            array('title' => '平台已删除', 'field' => 'status', 'width' => '80', 'format_js' => array('type' => 'map_checked', 'value' => 'sku_status'), 'align' => 'center'),
            array('title' => '同步日志', 'field' => '', 'width' => '80', 'format_js' => array('type' => 'function', 'value' => 'show_api_log'), 'align' => 'center'),         
                    
        ),
		'page_size'=>50,
		'url'=>get_app_url('api/api_taobao_fx_order/get_product_sku_list_by_pid&app_fmt=json'),
		'params'=>'pid',
	),
    'CheckSelection' => true,
    'export' => array('id' => 'exprot_list', 'conf' => 'api_fenxiao_goods_list', 'name' => '淘宝分销商品', 'export_type' => 'file'),

    //'CellEditing' => true,
));
?>
<?php echo load_js("baison.js",true);?>
<script>
function get_is_allow_sync_inv(value, row, index){
    if (value == 1) {
		return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,'+row.id+','+value+')"><img  src="'+ES.Util.getThemeUrl('images/ok.png')+'" /></a>';
    } else {
      return '<a href="javascript:void(0)" onclick="sku_is_sync_inv(this,'+row.id+','+value+')"><img  src="'+ES.Util.getThemeUrl('images/no.gif')+'" /></a>';
    }
}
function sku_is_sync_inv(_this,id,value){
     value = (value==0)?1:0;
	 $.ajax({ type: 'POST', dataType: 'json',
	    url: '<?php echo get_app_url('api/api_taobao_fx_order/update_active_sku&app_fmt=json');?>',
	    data: {id: id,type: value},
	    success: function(ret) {
	    	var type = ret.status == 1 ? 'success' : 'error';
	    	if (type == 'success') {
	    		var row = {id:id};
                var html = get_is_allow_sync_inv(value, row, 1);
                $(_this).parent().html(html);                
	    	} else {
            	BUI.Message.Alert(ret.message, type);
	    	}
	    }
	});



}

function active(obj,type){
    get_checked(obj, function(ids){
	    var d = {"pid": ids, type: type,'app_fmt': 'json'};
	    $.post('<?php echo get_app_url('api/api_taobao_fx_order/p_update_active');?>', d, function(data){
	
	        var type = data.status == 1 ? 'success' : 'error';
	        BUI.Message.Alert(data.message, type);
	        tableStore.load();
	    }, "json");
    })

}

//读取已选中项
function get_checked(obj, func) {
    var ids = new Array();
    var rows = tableGrid.getSelection();
    if (rows.length == 0) {
        BUI.Message.Alert("请选择商品", 'error');
        return;
    }
    for (var i in rows) {
        var row = rows[i];
        ids.push(row.pid);
    }
    ids.join(',');
    BUI.Message.Show({
        title: '提示框',
        msg: '是否执行' + obj.text() + '?',
        icon: 'question',
        buttons: [
            {
                text: '是',
                elCls: 'button button-primary',
                handler: function() {
                    func.apply(null, [ids]);
                    this.close();
                }
            },
            {
                text: '否',
                elCls: 'button',
                handler: function() {
                    this.close();
                }
            }
        ]
    });
}
function sync(index, row){
    if(typeof row == "undefined"){
        var id = get_checkbox_id();
    }else{
        var id = row.pid;
    }
    var url = "?app_act=api/sys/goods/fenxiao_sync_goods_inv&id="+id;
    ajax_post({
        url: url,
        async:false,
        alert:false,
        callback:function(data){
            var type = data.status == "1" ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
        }
    })
}
function get_checkbox_id(){
    var str = "";
    var check_id_arr = tableGrid.getSelection();
    for(var i=0;i < check_id_arr.length;i++){
        str += check_id_arr[i].pid+",";
    }
    str=str.substring(0,str.length-1);
    return str;
}

//删除
function do_delete(_index, row) {
    $.ajax({type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('api/api_taobao_fx_order/do_delete'); ?>', data: {pid: row.pid},
        success: function (ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert('删除成功!', type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}
    function show_api_log(value, row, index) {

               return '<a href="javascript:void(0)" onclick="show_log('+row.api_taobao_fx_product_sku_id+')">查看日志</a>';

    }
       function show_log(id) {
            PageHead_show_log('?app_act=api/sys/goods/show_sku_quantity_update&id='+id+'&type=1&app_show_mode=pop', '库存同步日志', {w:800,h:500});
        
       }
    function PageHead_show_log(_url, _title, _opts) {

        new ESUI.PopWindow(_url, {
                title: _title,
                width:_opts.w,
                height:_opts.h,
                onBeforeClosed: function() {   
                }
            }).show();
    }


    //批量库存同步
function multi_sync(obj) {
    get_checked(obj, function(ids){
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                width: 450,
                height: 120,
                elCls: 'custom-dialog',
                bodyContent: '<p style="font-size:15px">正在批量库存同步，请稍后...</p>',
                buttons: []
            });
            dialog.show();
        });
        var params = {"pid": ids,'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/sys/goods/multi_fenxiao_sync_goods_inv');?>', params, function(data){
            var type = data.status == 1 ? 'success' : 'error';
            $(".bui-ext-close .bui-ext-close-x").click();
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    })
}
</script>



