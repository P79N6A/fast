<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '',
    'links' => array(
        array('url' => 'market/planprice/do_list', 'title' => '报价模板列表'),
    )
));
?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">报价模板信息</h3>
<?php if ($app['scene'] == "add" || $app['scene'] == "edit") { ?>
            <div class="pull-right">
                <button type="submit" class="button button-primary" id="submit">提交</button>
                <button type="reset" class="button " id="reset">重置</button>
            </div>
<?php } ?>
    </div>
    <div class="panel-body">
        <?php
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => array(
                                array('title'=>'模板名称', 'type'=>'input', 'field'=>'price_name'),
                                array('title'=>'产品', 'type'=>'select', 'field'=>'price_cpid','data'=>ds_get_select('chanpin',2)),
                                array('title'=>'产品版本', 'type'=>'select', 'field'=>'price_pversion','data' => ds_get_select_by_field('product_version', 2)),
                                array('title'=>'基础报价', 'type'=>'input', 'field'=>'price_base'),
                                array('title'=>'默认点数', 'type'=>'input', 'field'=>'price_dot'),
                                array('title'=>'营销类型', 'type'=>'select', 'field'=>'price_stid','data'=>ds_get_select('market',2),'value'=>'2'),
                                /*array('title'=>'满', 'type'=>'input', 'field'=>'price_fulldate','remark'=>'月'),
                                array('title'=>'优惠', 'type'=>'input', 'field'=>'price_disdate','remark'=>'月'),*/
                                array('title'=>'默认期限', 'type'=>'input', 'field'=>'price_default_limit','remark'=>'月'),
                                array('title'=>'描述','type'=>'input', 'field'=>'price_note'),
                                array('title'=>'启用状态','type'=>'checkbox', 'field'=>'price_status'),
                ),
                'hidden_fields' => array(array('field' => 'price_id')),
            ),
            'col' => 2,
            'act_edit' => 'market/planprice/do_edit', //edit,add,view
            'act_add' => 'market/planprice/do_add',
            'data' => $response['data'],
           'rules'=>'market/planprice_add', //有效性验证
        ));
        ?>
    </div>
</div>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
       array('title' => '平台店铺', 'active' => true), // 默认选中active=true的页签
    ),
    'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '平台名称',
                            'field' => 'pd_pt_id_name',
                            'width' => '300',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '默认店铺数',
                            'field' => 'pd_shop_amount',
                            'width' => '300',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺单价',
                            'field' => 'pd_shop_price',
                            'width' => '300',
                            'align' => ''
                        ),                        
                    )
                ),
                'dataset' => 'market/PlatformshopModel::get_by_page_shop',
                'params' => array('filter' => array('price_id' => $request['_id'])),
                'idField' => 'pd_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
</div>

