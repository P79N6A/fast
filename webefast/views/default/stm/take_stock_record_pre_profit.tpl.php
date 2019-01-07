<style>
table{
    border-top: 1px solid #dddddd;
    border-left: 1px solid #dddddd;
}

table td,
table th{
    border-bottom: 1px solid #dddddd;
    border-right: 1px solid #dddddd;
    line-height:30px;
    text-align: center;
}

.bui-grid-table .bui-grid-hd{
    text-align: center;
}
</style>
<div class="panel record_table">
<!-- <div class="panel-header clearfix"><h3 class="pull-left">基本信息 <i class="icon-folder-open toggle"></i></h3></div>
</div> -->
<table class="bui-grid-table" cellspacing="0" cellpadding="0">
<tr>
<th class="bui-grid-hd">商品名称</th>
<th class="bui-grid-hd">商品编码</th>
<th class="bui-grid-hd"><?php echo $response['goods_spec1_rename']?></th>
<th class="bui-grid-hd"><?php echo $response['goods_spec2_rename']?></th>
<th class="bui-grid-hd">商品条形码</th>
<th class="bui-grid-hd">盘点数量</th>
<th class="bui-grid-hd">账面数量</th>
<th class="bui-grid-hd">盈亏数量</th>
</tr>

<?php foreach ($response['detail'] as $detail):?>
<tr>
    <td><?php echo get_goods_name_by_code($detail['goods_code'])?></td>
    <td><?php echo $detail['goods_code']?></td>
    <td><?php echo $detail['spec1_code']?></td>
    <td><?php echo $detail['spec2_code']?></td>
    <td><?php echo $detail['barcode']?></td>
    <td><?php echo $detail['num']?></td>
    <td><?php echo $detail['stock_num']?></td>
    <td><?php echo $detail['num']-$detail['stock_num']?></td>
</tr>
<?php endforeach;?>
</table>