
<table cellspacing="0" class="table table-bordered">
    <thead>
    <tr>
        <th>商品图片</th>
        <th>交易号</th>
        <th>商品名称</th>
        <th class="platform_name">平台商品名称</th>
        <th>商品编码</th>
        <th>系统规格</th>
        <th>平台规格</th>
        <th>商品条形码</th>
        <?php if ($response['lof_status']['lof_status'] == 1) { ?>
			<th style="width:90px;">批次号</th>
		<?php } ?>
        <th>数量(实物锁定)</th>
        <th>吊牌价</th>
        <th>均摊金额</th>
        <?php if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2){?>
            <th>结算单价</th>
            <th>结算金额</th>
        <?php }?>
        <th>赠品</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php 
//    echo '<pre>';var_dump($response['detail_list']);die;
    $record_type = empty($response['record_type'])? 'oms' : 'fx';
    foreach($response['detail_list'] as $key=>$detail){?>
        <tr class="detail_<?php
        echo $detail['sell_record_detail_id'];
        if($response['record']['order_status']!=3 && $response['record']['must_occupy_inv'] == 1 && $detail['num'] > $detail['lock_num']){
            echo " is_stock_out_row";
        }     
           if ($detail['api_refund_num']>0){
			echo " api_refund_desc";
		}
        ?>" 
        <?php
        if ($detail['api_refund_num']>0){
			echo " style='background-color:#666666'   title='{$detail['api_refund_desc']}'";
		}
        ?>
            param1="<?php echo $detail['sell_record_detail_id']; ?>" param2="<?php echo $detail['sku']; ?>"
        >
            <td>
                <?php if ($detail['deal_code']) { ?>
                    <?php echo $detail['pic_path']; ?>
                <?php } ?>
            </td>
            <td name="deal_code">
                <span><?php echo $detail['deal_code'];?></span>
                <input class="deal_code" style="width: 120px;display: none" type="text" value="<?php echo $detail['deal_code'];?>">
            </td>
            <td><?php echo $detail['goods_name'];?></td>
            <td class="platform_name"><?php echo $detail['platform_name'];?></td>
            <td><?php echo $detail['goods_code'];?></td>
            <td><?php echo $response['spec_name']['spec1_rename'].':'.$detail['spec1_name'].';'.$response['spec_name']['spec2_rename'].':'.$detail['spec2_name'];?></td>
            <td><?php echo $detail['platform_spec']?></td>
            <td><?php echo $detail['barcode'];?>
            <?php  if(!empty($detail['combo_barcode'])){  echo '('.$detail['combo_barcode'].')';}?>
            
            </td>
            <?php if ($response['lof_status']['lof_status'] == 1) { ?>
				<td style="width:90px;"><?php echo $detail['lof_no']?></td>
			<?php } ?>
            <td name="num">
                <div><?php echo $detail['num'];?>(<span class="num" onclick="show_batch_detail(this)"><?php echo $detail['lock_num'];?></span>)
                    <?php 
                        if(($response['record']['lock_inv_status'] ==2 || $response['record']['lock_inv_status'] ==3) && $response['record']['must_occupy_inv'] == 1 && ($detail['lock_num'] < $detail['num'])){
                             echo '<a href="javascript:void(0)" onclick="inv_adjust(\''.$detail['sell_record_code'].'\',\''.$detail['sku'].'\')">库存调剂</a>';
        }
                    ?>
                </div>
                <input class="new_num" onblur="change_avg_money('<?php echo $detail['num']; ?>','<?php echo sprintf("%.2f", $detail['avg_money']); ?>',this)" style="width: 30px; text-align: center; display: none;" type="text" value="<?php echo $detail['num'];?>">
            </td>
            <td><?php echo sprintf("%.2f", $detail['goods_price']);?></td>
            <td  name="avg_money">
                <span><?php echo sprintf("%.2f", $detail['avg_money']);?></span>
                <input class="avg_money" style="width: 50px;display: none" type="text" value="<?php echo sprintf("%.2f", $detail['avg_money']);?>">
            </td>
            <?php if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2){?>
            <td>
                <?php echo sprintf("%.2f", $detail['trade_price']);?>
            </td>
            <td name='fx_amount'>
                <span><?php echo sprintf("%.2f", $detail['fx_amount']);?></span>
                <input class="fx_amount" style="width: 50px;display: none" type="text" value="<?php echo sprintf("%.2f", $detail['fx_amount']);?>">
            </td>
            <?php }?>
            <td><?php
            echo $detail['is_gift'] == 1 ? '是' : '否';
            ?></td>
            <td style="width: 12%">
                <?php if($response['login_type'] == 2) { ?>                   
                    <?php if(load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_change_goods') ){  ?>
                    <button class="button button-small change" title="等价换货" onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>','<?php echo $detail['trade_price'];?>','<?php echo $detail['fx_amount'];?>')"><i class="icon-pencil"></i></button>
                    <?php } ?>
                <?php }else{ ?>                    
                    <?php if($record_type == 'oms' && load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/oms_change_goods') ){  ?>
                        <button class="button button-small change" title="等价换货" onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>','<?php echo $detail['trade_price'];?>','<?php echo $detail['fx_amount'];?>')"><i class="icon-pencil"></i></button>
                    <?php }elseif ($record_type == 'fx' && load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/fx_change_goods')) {?>                                         
                        <button class="button button-small change" title="等价换货" onclick="change_goods_add('<?php echo $detail['sell_record_code']; ?>','<?php echo $detail['deal_code'];?>','<?php echo $detail['goods_name'];?>','<?php echo $detail['goods_code'];?>','<?php echo $detail['sku'];?>','<?php echo $detail['barcode'];?>','<?php echo $response['record']['store_code'];?>','<?php echo $detail['spec1_name'];?>','<?php echo $detail['spec2_name'];?>','<?php echo $detail['avg_money'];?>','<?php echo $detail['sell_record_detail_id']?>','<?php echo $detail['num'];?>','<?php echo $detail['trade_price'];?>','<?php echo $detail['fx_amount'];?>')"><i class="icon-pencil"></i></button>
                    <?php } ?>                   
                <?php } ?>  
                        
                <?php if($response['login_type'] != 2) { ?>
                    <button class="button button-small edit" title="编辑" onclick="detail_edit(<?php echo $detail['sell_record_detail_id'];?>)"><i class="icon-edit"></i></button>
                <?php } ?>
                <button class="button button-small delete" title="删除" onclick="detail_delete(<?php echo $detail['sell_record_detail_id'];?>)"><i class="icon-trash"></i></button>
                <button class="button button-small save hide" title="保存" onclick="detail_save(<?php echo $detail['sell_record_detail_id'];?>)"><i class="icon-ok"></i></button>
                <button class="button button-small cancel hide" title="取消" onclick="detail_cancel(<?php echo $detail['sell_record_detail_id'];?>)"><i class="icon-ban-circle"></i></button>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo load_js('common.js') ?>
<script>
function change_goods_add(sell_record_code,deal_code,goods_name,goods_code,sku,barcode,store_code,spec1_name,spec2_name,avg_money,sell_record_detail_id,num,trade_price,fx_amount){
    var url = "?app_act=oms/sell_record/add_change_goods_view&sell_record_code="+sell_record_code+'&deal_code='+deal_code+'&goods_name='+goods_name+'&goods_code='+goods_code+'&store_code='+store_code+'&spec1_name='+spec1_name+'&sku='+sku+'&barcode'+barcode+'&spec2_name='+spec2_name+'&sell_record_detail_id='+sell_record_detail_id+'&num='+num+'&avg_money='+avg_money+'&trade_price='+trade_price+'&fx_amount='+fx_amount;
    new ESUI.PopWindow(url, {
        title: '等价换货',
        width:750,
        height:500,
        onBeforeClosed: function() {
        },
        onClosed: function(){
            window.location.reload();
        }
    }).show();
}
function inv_adjust(record_code,sku){
        var url = '?app_act=oms/sell_record_ajust/detail&record_code=' +record_code+"&sku="+sku;
        openPage(window.btoa(url),url,'库存调剂');
    }
</script>