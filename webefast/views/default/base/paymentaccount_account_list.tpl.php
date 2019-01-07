<style type="text/css">
    .table_pane2{width: 100%;border: solid 1px #ded6d9;margin-bottom: 5px;margin-top: 5px;}
    .table_pane2 td {
    border:1px solid #dddddd;
    line-height: 15px;
    padding: 5px;
    text-align: left;
    width:14.2%;
</style>
<?php
render_control('PageHead', 'head1', array('title' => '收款账户', 
    'links' => array(
        array('url' => 'base/paymentaccount/add_account&app_scene=add', 'title' => '添加线下账户', 'is_pop' => true, 'pop_size' => '500,550','id' => 'add_account'),
    ),
    'ref_table' => 'table2'));
?>
<div id="tab">
        <ul>
<!--            <li class="bui-tab-panel-item active"><a href="#">在线支付</a></li>-->
            <li class="bui-tab-panel-item"><a href="#">线下支付</a></li>
        </ul>
    </div>

    <div id="form1_data_source" style="display:none;"><?php if(isset($response['form1_data_source'])){ echo $response['form1_data_source']; }?></div>
    <div id="panel" class="" style="padding-top:10px">
		<div id="p1">
            <div id="p1_form" >
                <table class='table_pane2'>
                    <tr>
                    <td width="10%">操作</td>
                    <td width="35%">支付方式</td>
                    <td width="25%">账户</td>
                    </tr>
                    <tr>
                    <td onclick="zhifubao()"><a href="">开通</a></td>
                    <td>支付宝</td>
                    <td><?php ?></td>
                    </tr>
                    <tr>
                    <td onclick="weixin()"><a href="">开通</a></td>
                    <td>微信</td>
                    <td><?php ?></td>
                    </tr>
                    </table>
            </div>

        </div>


        <div id="p2">
            <div id="p2_form" >
				            
                <?php
                render_control('DataTable', 'table2', array(
                                'conf' => array(
                                    'list' => array(
                                        array(
                                        'type' => 'button',
                                        'show' => 1,
                                        'title' => '操作',
                                        'field' => '_operate',
                                        'width' => '200',
                                        'align' => '',
                                        'buttons' => array(
                                            array(
                                                'id' => 'do_change',
                                                'title' => '修改',
                                                'callback' => 'change_account',
                                                //'confirm' => '确认要删除吗？',

                                                ),
                                            array(
                                                'id' => 'do_delete',
                                                'title' => '删除',
                                                'callback' => 'delete_account',
                                                'confirm' => '确认要删除吗？',

                                                ),
                                            ),
                                        ),
                                        array(
                                            'type' => 'text',
                                            'show' => 1,
                                            'title' => '账户名称',
                                            'field' => 'account_name',
                                            'width' => '200',
                                            'align' => ''
                                        ),
                                        array(
                                            'type' => 'text',
                                            'show' => 1,
                                            'title' => '开户银行',
                                            'field' => 'account_bank',
                                            'width' => '200',
                                            'align' => ''
                                        ),
                                        array(
                                            'type' => 'text',
                                            'show' => 1,
                                            'title' => '银行账号',
                                            'field' => 'bank_code',
                                            'width' => '200',
                                            'align' => ''
                                        ),
                                    )
                                ),
                                'dataset' => 'base/PaymentaccountModel::get_account_by_page',
                                'idField' => 'id',
                            ));
                ?>
            </div>

        </div>
        
    
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
BUI.use(['bui/tab','bui/mask'],function(Tab){
            var tab = new Tab.TabPanel({
                srcNode : '#tab',
                elCls : 'nav-tabs',
                itemStatusCls : {
                    'selected' : 'active'
                },
                panelContainer : '#panel'//如果不指定容器的父元素，会自动生成
                //selectedEvent : 'mouseenter',//默认为click,可以更改事件

            });
            tab.render();
        });

function weixin(_index,row){
    new ESUI.PopWindow("?app_act=base/paymentaccount/weixinpay", {
                title: "微信签约信息",
                width: 500,
                height: 570,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
//                    tableStore.load()
                    table2Store.load();
                }
            }).show()
}
function zhifubao(_index,row){
    new ESUI.PopWindow("?app_act=base/paymentaccount/alipay", {
                title: "支付宝签约信息",
                width: 500,
                height: 570,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
//                    tableStore.load()
                    table2Store.load();
                }
            }).show()
}
function delete_account(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/paymentaccount/delete');?>',
            data: {id: row.id},
            success:function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
//                tableStore.load();
                table2Store.load();
                } else {
                BUI.Message.Alert(ret.message, type);
                }
            }
        });
}

function change_account(_index,row){
      new ESUI.PopWindow("?app_act=base/paymentaccount/add_account&app_scene=edit&id="+row.id, {
                title: "修改信息",
                width: 500,
                height: 570,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
//                    tableStore.load()
                    table2Store.load();
                }
            }).show()  
}
</script>