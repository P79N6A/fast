
<style type="text/css">
.table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
.table_pane2{width: 100%;border: solid 1px #ded6d9;margin-bottom: 5px;margin-top: 5px;}
 .table_panel td {
    border-top: 0px solid #dddddd;
    line-height: 15px;
	padding:5px 10px;
    text-align: left;
}
.table_panel1 td {
    border:1px solid #dddddd;
    line-height: 15px;
    padding: 5px;
    text-align: left;
}
.table_pane2 td {
    border:1px solid #dddddd;
    line-height: 15px;
    padding: 5px;
    text-align: left;
    width:20%;
}
.table_panel_tt td{ padding:10px 5px;}
.table_panel_tt2 td{ padding:10px 5px;}
.btns{ text-align:right;}
.nav-tabs{ padding-top:10px; margin-bottom:10px;}
.panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
.panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
.panel > .panel-header h3{ font-size:14px;}
input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}
.panel_div { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;margin-bottom: 5px;}
</style>

<?php echo load_js("baison.js,record_table.js",true);?>
<ul class="nav-tabs oms_tabs">
    <li onClick="do_page('base');" ><a href="#"  >规则设置</a></li>
    <li onClick="do_page('gift');"><a href="#" >赠品商品</a></li>
    <li class="active"><a href="#" >活动商品</a></li>
    <?php if($response['data']['type'] != 2){?>
    <li onClick="do_page('customer');"><a href="#" >定向会员</a></li>
   <?php }?>
</ul>
<?php if($response['data']['type'] != 2){?>
<table class='table_panel table_panel_tt' >
<tr>
  <td>策略名称：<?php echo $response['strategy']['strategy_name']; ?></td>
  <td >活动店铺：<?php echo $response['strategy']['shop_code_name']; ?></td>
</tr>
<tr>
  <td >活动开始时间：<?php echo date('Y-m-d H:i',$response['strategy']['start_time']); ?></td>
  <td >活动结束时间：<?php echo date('Y-m-d H:i',$response['strategy']['end_time']); ?></td>
</tr>
</table>
<?php }?>

<div>
	<div class="btns">
        <button type="button"  class="button button-primary btns"  onclick="export_rule_goods();" >导出</button>
	<button type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary btns"  onclick="import_rule_goods();" >导入</button>
	<button type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary btns"  onclick="show_select_goods(1,0);" >添加</button>
	<button  type="button" <?php if($response['strategy']['is_check']==1){?>disabled <?php }?> class="button button-primary btns"  onclick="delGoodsBatch();" >一键清空</button>
	</div>

	<div>
		<?php
		$list =  array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '300',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品规格',
                'field' => 'spec',
                'width' => '250',
                'align' => ''
            ),

            array (
	            'type' => 'text',
	            'show' => 1,
	            'title' => '商品条形码',
	            'field' => 'barcode',
	            'width' => '200',
	            'align' => ''
            ),

          );
		if ($response['data']['goods_condition'] == 0){
			$list[] = array (
						'type' => 'text',
						'show' => 1,
						'title' => '数量',
						'field' => 'num',
						'width' => '80',
						'align' => ''
					);
		}
                    $list[] = array(
                       'type' => 'text',
                       'show' => 1,
                       'title' => '组合商品',
                       'field' => 'diy_html',
                       'width' => '100',
                       'align' => ''
                   );
                   $list[] = array(
                       'type' => 'text',
                       'show' => 1,
                       'title' => '套餐商品',
                       'field' => 'is_combo_html',
                       'width' => '100',
                       'align' => ''
                   );
                   if ($response["strategy"]['is_check']==0) {
                        $list[] = array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '80',
                            'buttons' => array(
                                array(
                                    'id' => 'delete',
                                    'title' => '删除',
                                    'callback' => 'delete_gift_goods',
                                ),
                            ),
                        );
                   }                   
render_control ( 'DataTable', 'table', array (
		    'conf' => array (
		        'list' =>$list,

		    ),
		    'dataset' => 'op/GiftStrategy2GoodsModel::get_by_page',
		    'idField' => 'op_gift_strategy_customer_id',
			   'params' => array(
		        'filter' => array('op_gift_strategy_detail_id'=>$response['data']['op_gift_strategy_detail_id'],'is_gift'=>0),
		    ),

		) );
		?>
	</div>
	<br />    <br />    <br />
	<?php if ($response['data']['type'] == 0){ ?>
		<div id='msg' style='color:red'>说明：若此满赠规则需要针对商品，请设置商品信息。设置后仅购买活动商品中任意一款且满足金额才会送赠品，若无此需求请不要设置活动商品。</div>
	<?php }else if($response['data']['type'] == 1){ ?>
	<div id='msg' style='color:red'>说明：若此买赠规则需要针对商品，请设置商品信息。设置后仅购买活动商品中任意一款且满足数量才会送赠品，若无此需求请不要设置活动商品。</div>
	<?php }else if($response['data']['type'] == 2){ ?>
	<div id='msg' style='color:red'>说明：若此排名规则需要针对商品，请设置商品信息。设置后仅购买活动商品中任意一款且满足金额才会送赠品，若无此需求请不要设置活动商品。</div>
	<?php } ?>
	</div>

<script type="text/javascript">
var id = "<?php echo $request['_id']; ?>";
var strategy_code = "<?php echo $response['strategy']['strategy_code'];?>";
var detail_id = "<?php echo $response['data']['op_gift_strategy_detail_id'];?>";
var rank_type = "<?php echo $response['data']['type']?>";
function do_page(type) {
    if(rank_type == 2){
        if (type == 'base') {
            location.href = "?app_act=op/gift_strategy/ranking_rule_view&app_scene=edit&_id=" + id;
        } else if (type == 'gift') {
            location.href = "?app_act=op/gift_strategy/ranking_gift_goods&app_scene=edit&_id=" + id;
        } else if (type == 'goods') {
            location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id=" + id;
        }
    } else {
        if (type == 'base'){
		location.href = "?app_act=op/gift_strategy/rule_view&app_scene=edit&_id="+id;
	} else if (type == 'gift'){
		location.href = "?app_act=op/gift_strategy/gift_goods&app_scene=edit&_id="+id;
	} else if (type == 'goods'){
		location.href = "?app_act=op/gift_strategy/rule_goods&app_scene=edit&_id="+id;
	} else if (type == 'customer'){
		location.href = "?app_act=op/gift_strategy/rule_customer&app_scene=edit&_id="+id;
	}
    }
}

function delete_gift_goods (index,row) {
    BUI.Message.Confirm('确认要删除吗？',function(){       
        var url = '?app_act=op/gift_strategy/delete_gift_goods';          
        $.post(url, {gift_goods_id: row.op_gift_strategy_goods_id,gift_detail_id:row.op_gift_strategy_detail_id,strategy_code:row.strategy_code,barcode:row.barcode}, function (result) {
	    if (result.status!=1) {
	        BUI.Message.Alert(result.message,function(){
                    location.reload();
                },'error');
	    } else {
	        BUI.Message.Alert(result.message,'success');
                tableStore.load();
	    }
	}, 'json');
    })    
}
var select_is_gift = 0;
var select_is_select = 0;
var select_url = '';
//var is_select = <?php //if ($response['data']['type'] == 0) { echo 1;} else {echo 0;}?>;
var is_select = <?php if ( $response['data']['goods_condition']==1 ) { echo 1;} else {echo 0;}?>;

function show_select_goods(is_gift){
	 select_is_gift = is_gift;
	 select_is_select = is_select;
	var param = {store_code:'',is_diy:0,select_combo:1};
	var url = '?app_act=prm/goods/goods_select_tpl&is_select='+is_select;

	if(typeof(top.dialog)!='undefined'){
	    if(url!=select_url){
	top.dialog.remove(true);
	    }else{
	        top.dialog.show();
	        return ;
	    }
	}
 	var buttons = [
               {
                text:'保存继续',
                elCls : 'button button-primary',
                handler : function(){
                    addgoods(this,1);
                }
              },
              {
                text:'保存退出',
                elCls : 'button button-primary',
                handler : function(){
                    addgoods(this,0);
                }
              },{
                text:'取消',
                elCls : 'button',
                handler : function(){
                 this.close();
                }
              }
            ];

	top.BUI.use('bui/overlay',function(Overlay){
	 top.dialog = new Overlay.Dialog({
	    title: '选择商品',
	    width: '80%',
	    height: 450,
	    loader: {
	        url:url,
	        autoLoad: true, //不自动加载
	        params: param, //附加的参数
	        lazyLoad: false, //不延迟加载
	        dataType: 'text'   //加载的数据类型
	    },
	     			align: {
	      //node : '#t1',//对齐的节点
	      points: ['tc','tc'], //对齐参考：http://dxq613.github.io/#positon
	      offset: [0,20] //偏移
	    },
	    mask: true,
	            buttons:buttons
	});
	 top.dialog.on('closed',function(){
          location.reload();
    });

	top.dialog.show();

    });


}
function addgoods(obj,type){
     var select_data= {};
     if(select_is_select==1){
            select_data = top.SelectoGrid.getSelection();
     }else{
     	var data =top.skuSelectorStore.getResult();
        var di=0;
	    BUI.each(data,function(value,key){
	        var num_name = 'num_'+value.sku;
	        if(top.$("input[name='"+num_name+"']").val()!=''&&top.$("input[name='"+num_name+"']").val()!=undefined){
	            value.num = top.$("input[name='"+num_name+"']").val();
	            select_data[di] = value;
	            di++;
	            }
	    });
    }
    var _thisDialog = obj;
    var arr = Object.keys(select_data);
    if(arr.length == 0){
          _thisDialog.close();
          return ;
    }

  	var url ='?app_act=op/gift_strategy/do_add_goods&app_fmt=json&range_id=0&strategy_code='+strategy_code+'&detail_id=' +detail_id+'&is_gift=0';

	$.post(url, {data: select_data}, function (result) {
	    if (result.status!=1) {
	        //添加失败
	        top.BUI.Message.Alert(result.message, function () {
	       //       _thisDialog.close();
	        }, 'error');
	    } else {
	        if(type==1){
	            top.skuSelectorStore.load();
	        }else{
	             _thisDialog.close();
	        }
	    }

	}, 'json');

}
//删除赠品
function delGoods(op_gift_strategy_goods_id){

	$.post('<?php echo get_app_url('op/gift_strategy/del_goods');?>', {'op_gift_strategy_goods_id':op_gift_strategy_goods_id}, function(data){
        var type = data.status == 1 ? 'success' : 'error';
		if (data.status == 1) {
			location.reload();
		} else {
			BUI.Message.Alert(data.message, function() { }, type);
		}
    }, "json");
}
//删除赠品
function delGoodsBatch(op_gift_strategy_goods_id){

	$.post('<?php echo get_app_url('op/gift_strategy/del_goods_batch');?>', {'op_gift_strategy_detail_id':detail_id,'is_gift':0,strategy_code:strategy_code}, function(data){
        var type = data.status == 1 ? 'success' : 'error';
		if (data.status == 1) {
			location.reload();
		} else {
			BUI.Message.Alert(data.message, function() { location.reload(); }, type);
		}
    }, "json");
}
//导出
function export_rule_goods(){
        var params = '';
//        var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
        var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');
        params.ctl_type = 'export';
        params.ctl_export_conf = 'gift_strategy_rule_goods';
        params.ctl_export_name =  '活动商品';
        <?php echo   create_export_token_js('op/GiftStrategy2GoodsModel::get_by_page');?>        
        for(var key in params){
            url +="&"+key+"="+params[key];
        }
        params.ctl_type = 'view';
        window.open(url);  
}
//导入
function import_rule_goods(){
	var param = {};
	var type = 2;
	var url= '?app_act=op/gift_strategy/rule_goods_import&strategy_code='+strategy_code+"&op_gift_strategy_detail_id="+id;
	    new ESUI.PopWindow(url, {
	            title: '导入活动商品',
	            width:500,
	            height:380,
	            onBeforeClosed: function() {  location.reload();
	            }
	        }).show();

}
</script>
