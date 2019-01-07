<?php
/*$my_fields = array(
    array('title' => '交易号', 'type' => 'label', 'field' => 'deal_code'),
    array('title' => '商品名称', 'type' => 'label', 'field' => 'goods_name'),
    array('title' => '商品编码', 'type' => 'label', 'field' => 'goods_code'),
    array('title' => $response['data']['goods_spec1_rename'], 'type' => 'label', 'field' => 'spec1_name'),
    array('title' => $response['data']['goods_spec2_rename'], 'type' => 'label', 'field' => 'spec2_name'),
    array('title' => '商品条形码', 'type' => 'label', 'field' => 'barcode'),
    array('title' => '原单商品数量', 'type' => 'label', 'field' => 'relation_num'),
    array('title' => '申请退货数量', 'type' => 'input', 'field' => 'note_num'),
    array('title' => '实际退货数量', 'type' => 'label', 'field' => 'recv_num'),
    array('title' => '吊牌价', 'type' => 'label', 'field' => 'goods_price'),
    array('title' => '实际应退款', 'type' => 'input', 'field' => 'avg_money'),
//    array('title' => '已退货数量', 'type' => 'label', 'field' => 'num'),
);
if($app['scene']=='edit'){
    $my_fields[] = array('title' => '操作', 'type' => 'label', 'field'=>'operate', 'value' => '<a onclick=javascript:if(confirm("确定要删除吗？")){delete_detail("return_goods",this);}>删除</a>');
}
render_control('TableForm', 'table1', array(
    'conf' => array(
        'fields' => $my_fields,
    ),
    'act_edit'=>'oms/sell_return/do_edit&app_fmt=json',
    'buttons' => array(),
    'data' => $response['data']['detail_list'],
));*/
?>
<?php
$arry = array('goods_spec1','goods_spec2');
$arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arry);
$goods_spec1_rename = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
$goods_spec2_rename = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
?>
<table cellspacing="0" class="table table-bordered">
    <thead>
    <tr>
        <th>交易号</th>
        <th>商品名称</th>
        <th>商品编码</th>
        <th><?php echo $response['data']['goods_spec1_rename'];?></th>
        <th><?php echo $response['data']['goods_spec2_rename'];?></th>
        <th>商品条形码</th>
        <th>原单商品数量</th>
        <th>申请退货数量</th>
        <th>实际退货数量</th>    
        <th>吊牌价</th>        
        <th>实际应退款</th>
        <th>结算单价</th>
        <th>结算金额</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php 

    foreach($response['data']['detail_list'] as $key=>$detail){?>
        <tr class="detail_<?php echo $detail['sell_return_detail_id'];?>">
            <td name="deal_code">
                <span><?php echo $detail['deal_code'];?></span>
                <input class="deal_code" style="width: 120px;display: none" type="text" value="<?php echo $detail['deal_code'];?>">
            </td>
            <td><?php echo $detail['goods_name'];?></td>
            <td><?php echo $detail['goods_code'];?></td>
            <td><?php echo $detail['spec1_name']?></td>
            <td><?php echo $detail['spec2_name']?></td>
            <td><?php echo $detail['barcode'];?></td>
            <td><?php echo $detail['relation_num'];?></td>
            <td name="num">
                <div><?php echo $detail['note_num'];?></div>
                <input class="new_num" onblur="change_avg_money('<?php echo $detail['note_num']; ?>','<?php echo sprintf("%.2f", $detail['avg_money']); ?>',this)" style="width:30px; text-align:center; display:none;" type="text" value="<?php echo $detail['note_num'];?>">
            </td>
            <td name = "recv_num">
                <span><?php echo $detail['recv_num'];?></span>
                <input class="recv_num" style="width:50px; display:none" type="text" value="<?php echo $detail['recv_num'];?>">
            </td>
            <td><?php echo sprintf("%.2f", $detail['goods_price']);?></td>
            <td name="avg_money">
                <span><?php echo sprintf("%.2f", $detail['avg_money']);?></span>
                <input class="avg_money" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.2f", $detail['avg_money']);?>">
            </td>
            <td name="trade_price">
                <span><?php echo sprintf("%.3f", $detail['trade_price']);?></span>
                <input class="trade_price" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.3f", $detail['trade_price']);?>">
            </td>
            <td name="fx_amount">
                <span><?php echo sprintf("%.3f", $detail['fx_amount']);?></span>
                <input class="fx_amount" style="width:50px; display:none" type="text" value="<?php echo sprintf("%.3f", $detail['fx_amount']);?>">
            </td>
            <td style="width: 13%">
                <button class="button button-small change" title="改款" onclick="detail_change('<?php echo $detail['goods_code'];?>','<?php echo $detail['sell_return_detail_id'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['avg_money'];?>','<?php echo isset($detail['is_gift'])?$detail['is_gift']:0;?>','<?php echo $detail['note_num'];?>')"><i class="icon-pencil"></i></button>
                <button class="button button-small edit" title="编辑" onclick="detail_edit(<?php echo $detail['sell_return_detail_id'];?>)"><i class="icon-edit"></i></button>
                <button class="button button-small delete" title="删除" onclick="detail_delete(<?php echo $detail['sell_return_detail_id'];?>)"><i class="icon-trash"></i></button>
                <button class="button button-small save hide" title="保存" onclick="detail_save(<?php echo $detail['sell_return_detail_id'];?>)"><i class="icon-ok"></i></button>
                <button class="button button-small cancel hide" title="取消" onclick="detail_cancel(<?php echo $detail['sell_return_detail_id'];?>)"><i class="icon-ban-circle"></i></button>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script>
var selectPopWindowshelf_return_goods = {
    callback: function (value, id, code, name) {
        //console.info(value);
        if(typeof value.sku == "undefined"){
            alert("请选择商品");
            return;
        }
        var num = value.num;
        var   type="^[0-9]*[1-9][0-9]*$";
        var   re   =   new   RegExp(type);
        if(num.match(re)==null) {
            alert( "请输入大于零的整数!");
            return;
        }
        $.ajax({
            type: "GET",
            url: "?app_act=fx/sell_return/opt_return_detail",
            //async: false,
            data: {
                sku:value.sku,
                num:value.num,
                sell_return_detail_id:value.sell_return_detail_id,
                sell_return_code:sell_return_code,
                deal_code:value.deal_code,
                avg_money:value.avg_money,
                is_gift:value.is_gift,
                app_fmt:'json'
            },
            dataType: "json",
            success: function(data){
                if(data.status == 1){
                    //console.info(data);
                    component("return_money", "view");
                    component("return_goods", "view");
                    component("action", "view");
                } else {
                    alert(data.message);
                }
            }
        });
        //console.info(selectPopWindowshelf_return_goods.dialog);
        if (selectPopWindowshelf_return_goods.dialog != null) {
            selectPopWindowshelf_return_goods.dialog.close();
        }
    }
};
	function detail_change(goods_code,sell_return_detail_id,sku,deal_code,avg_money,is_gift,num){
		selectPopWindowshelf_return_goods.dialog = new ESUI1.PopSelectWindow('?app_act=common/select1/return_goods&goods_code='+goods_code+'&sell_return_detail_id='+sell_return_detail_id+'&sku='+sku+'&deal_code='+deal_code+'&avg_money='+avg_money+'&is_gift='+is_gift+'&num='+num, 'selectPopWindowshelf_return_goods.callback', {title: '修改商品规格', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>'}).show();

	}        
    function change_avg_money(num,avg_money,_this){
        var new_num = $(_this).val();
        $(_this).parents("tr").find(".avg_money").val(Number((avg_money/num)*new_num).toFixed(2));
    }
</script>
<?php $lof_status = load_model("sys/SysParamsModel")->get_val_by_code(array("lof_status"));if($lof_status['lof_status']):?>
<div id="show"></div>
<style>
    .lock_detail{
        background:#fff;
        position:absolute;
        z-index:100;
        padding:10px;
    }
    .lock_detail td,.lock_detail th{
        text-align: center;
        border:1px solid #000;
        padding:5px;
    }
</style>
<script>
	
	
    function show_dialog(url,title,opt){
        new ESUI.PopWindow(url, {
            title: title,
            width:opt.w,
            height:opt.h,
            onBeforeClosed: function() {
                 if (typeof opt.callback == 'function') opt.callback();
            }
        }).show();
    }
   /* $(function(){
        $(".num").click(function(){
            var num_v = $(this).html();
            if(num_v == 0){
                return;
            }
            var id = $(this).attr('param1');
            var sku = $(this).attr('param2');
            var title = '';
            var url = "?app_act=oms/sell_record/lock_detail&sell_record_detail_id="+id+"&sku="+sku;
            show_dialog(url,title,{w:900,h:400});
        });
    });
    */
</script>
<?php endif;?>