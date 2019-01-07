<style>
    .add-goods{margin-top:50px;}
</style>

<?php echo load_js("baison.js,record_table.js", true); ?>

<div class="add-goods">
    <div class="row">
        <div style="float: left;margin-right: 50px;">	
            <label>指定快递：</label>
            <select id="select_appoint_express">
                <option value="" >请选择</option>
                <?php foreach ($response['express_data'] as $val): ?>
                    <option value="<?php echo $val['express_code']; ?>" <?php if ($response['appoint_express'] == $val['express_code']) echo 'selected'; ?> ><?php echo $val['express_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
<!--        <div style="float: left;">	
            <label>切换快递：</label>
            <select id="appoint_express" name="appoint_express">
                <option value="" >请选择</option>
                <?php foreach ($response['express_data'] as $val): ?>
                    <option value="<?php echo $val['express_code']; ?>"><?php echo $val['express_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>-->
        <div style ="float:right;">
            <button type="button" class="button button-primary is_view " onclick="import_customer()">
                <i class="icon-plus-sign icon-white"></i>
                会员导入
            </button>
            <button type="button" class="button button-success" value="一键清空" id="btnDeleteAll" >
                <i class="icon-plus-sign icon-white"></i>
                一键清空
            </button>
        </div>
    </div>
</div>

<?php
render_control('PageHead', 'head1', array(
    'title' => '会员指定快递',
    'links' => array(
        array('url' => 'crm/express_strategy/do_list', 'title' => '订单快递适配策略'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array (
            array (
                'type'  => 'text',
                'show'  => 1,
                'title' => '会员昵称',
                'field' => 'customer_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type'  => 'text',
                'show'  => 1,
                'title' => '手机号',
                'field' => 'mobile',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type'  => 'text',
                'show'  => 1,
                'title' => '导入时间',
                'field' => 'lastchanged',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'crm/OpExpressByUserModel::get_by_page',
    'idField' => 'op_express_id',
    'params' => array('filter' => array('express_code' => $response['appoint_express'])),
));
?>
<br/>
<span style="color:red;">提示：列表中的会员将会匹配指定快递。</span>
<script type="text/javascript">
    var express_code = document.getElementById('select_appoint_express').value;
    /**
     * 会员导入
     */
    function import_customer(){
        if (express_code !== '') {
            var url= '?app_act=crm/express_strategy/customer_import&express_code=' + express_code;
            new ESUI.PopWindow(url, {
                title: '导入会员',
                width:500,
                height:380,
                onBeforeClosed: function() {  tableStore.load(); 
                }
            }).show();
        } else {
            BUI.Message.Alert('请先指定要导入的快递');
        }
    }
    
    /**
     * 查询快递对应的会员
     */
    $('#select_appoint_express').change(function () {
        var url = '<?php echo get_app_url('crm/express_strategy/get_op_express_by_user'); ?>';
        var new_code = $(this).val();
        if (0 !== new_code.length) {
            url += '&express_code=' + new_code;
        }
        window.location.href = url;
    });
    
    /**
     * 指定快递给所有会员
     */
    $('#appoint_express').change(function () {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('crm/express_strategy/do_update_express_user'); ?>',
            data: {new_express_code: $('#appoint_express').val(), express_code: express_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('更新成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    });

    /**
     * 一键清空
     */
    $("#btnDeleteAll").click(function () {
        BUI.Message.Confirm('确认要删除全部会员么？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('crm/express_strategy/do_delete_all_users'); ?>',
                data: {express_code: express_code},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, 'question');
        return;
    });
</script>
