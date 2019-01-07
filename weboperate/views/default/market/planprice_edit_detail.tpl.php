<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看报价模板',
    'links' => array(
        array('url' => 'market/planprice/do_list', 'title' => '报价模板列表'),
    )
));
?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=market/planprice/do_' . $app["scene"] ?>" method="post">
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
                'noform' => true,
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
                                array('title'=>'描述','type'=>'textarea', 'field'=>'price_note'),
                        
                    ),
                    'hidden_fields' => array(array('field' => 'price_id')),
                ),
                'col' => 2,
                'act_edit' => 'market/planprice/do_edit', //edit,add,view
                'act_add' => 'market/planprice/do_add',
                'data' => $response['data'],
                'rules'=>'market/planprice_add', //有效性验证
                'event'=>array('beforesubmit'=>'formBeforesubmit'),
            ));
            ?>
        </div>
    </div>
</form>
<script type="text/javascript">
    form =  new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
        }
    }).render();
    
    form.on('beforesubmit', function() { return formBeforesubmit(); });
    
    function formBeforesubmit() {
        if($("#price_stid").val()=='2'){  //表示租用型，必须设置默认期限
            if($("#price_default_limit").val()==''){
                BUI.Message.Alert("租用型，默认期限不能为空","error");
                return false;
            }
        }
	return true; // 如果不想让表单继续提交，则return false
    }
</script>
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
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=market/planprice/platform_shop&app_scene=add&app_show_mode=pop&priceid=<?php echo $request['_id'] ?>', '平台店铺', {w: 500, h: 400}, table1Store)"><i class="icon-plus"></i>添加平台店铺</button>
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '平台名称',
                            'field' => 'pd_pt_id_name',
                            'width' => '260',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '默认店铺数',
                            'field' => 'pd_shop_amount',
                            'width' => '260',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺单价',
                            'field' => 'pd_shop_price',
                            'width' => '260',
                            'align' => ''
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '260',
                            'align' => '',
                            'buttons' => array(
                                array('id' => 'edit', 'title' => '编辑',
                                    'act' => 'pop:market/planprice/platform_shop&app_scene=edit', 'show_name' => '编辑平台店铺',
                                ),
                                array('id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'delete_platshop',
                                    'confirm' => '确认要删除吗？'
                                ),
                            ),
                        )
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
<script type="text/javascript">
    function PageHead_show_dialog_ref(_url, _title, _opts, refgrid) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function() {
                if (refgrid) {
                    refgrid.load();
                }
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
    //删除模块明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function delete_platshop(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('market/planprice/do_delete_platshop'); ?>",
            data: {pd_id: row.pd_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    table1Store.load();
//                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

</script>

