

<style type="text/css">
    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_panel1{
        width:100%;
        margin-bottom:5px;
    }
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 18px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 5px;
        text-align: left;
    }
    .table_panel_tt td{ padding:10px 25px;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
</style>

<table class='table_panel table_panel_tt' >
    <?php if(!isset( $request['type'])):?>
    <tr>
        <td style="width: 180px">平台SKUID：<?php echo    $response['sku_info']['sku_id'];?></td>
        <td style="width:220px">平台规格编码：<?php echo    $response['sku_info']['goods_barcode'];?></td>
            </tr>
                <tr>
        <td colspan="2" >平台商品属性：<?php echo $response['sku_info']['sku_properties_name'];?></td>
    </tr>
<?php else :?>
    <tr>
        <td style="width: 180px">平台SKUID：<?php echo    $response['sku_info']['id'];?></td>
        <td style="width:220px">平台规格编码：<?php echo    $response['sku_info']['outer_id'];?></td>
            </tr>
                <tr>
        <td colspan="2" >平台商品属性：<?php echo $response['sku_info']['name'];?></td>
    </tr> 
  <?php endif;?>  
    
</table>
<?php

$params = array('filter'=>array('shop_code' => $response['sku_info']['shop_code'], 'sku_id' => $response['sku_info']['sku_id'],));
if(isset( $request['type'])){//分销用like
    $params['filter']['post_data_key_like'] = $response['sku_info']['sku_id'];
}

if($response['sku_info']['source']=='weipinhui'){
    $params['filter']['sku_id'] = $response['sku_info']['goods_barcode'];
}

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存同步数',
                'field' => 'num',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步时间',
                'field' => 'add_time',
                'width' => '150',
                     'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否成功',
                'field' => 'is_err',
                'width' => '150',
                   'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '失败原因',
                'field' => 'error_msg',
                'width' => '300',
                'align' => 'center'
            ),
        ),
        'page_size'=>50,
    ),
    'dataset' => 'apilog/LogsShowModel::get_sku_quantity_update',
    'idField' => 'id',
    'HeaderFix'=>false,
    'params' =>$params,
));

?>
