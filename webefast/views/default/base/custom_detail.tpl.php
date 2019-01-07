 <style type="text/css">
    .form-horizontal .control-label {
    display: inline-block;
    float: left;
    line-height: 30px;
    text-align: left;
    width: 100px;
}
.span11 {width: 550px;}
</style>

<?php
if ($response['app_scene'] == 'add') {
    $remark = "一旦保存不能修改";
    $title = '添加分销商';
} else {
    $remark = "";
    $title = '编辑分销商';
}
render_control('PageHead', 'head1', array('title' => $title,
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$button = array();
    $button = array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    );
?>
<div id="TabPage1Contents">
    <div>
        <?php
        if($response['service_custom'] == TRUE) {
            $fields = array(
                array('title' => '分销商编号', 'type' => 'input', 'field' => 'custom_code', 'remark' => $remark, 'edit_scene' => 'add'),
                array('title' => '分销商名称', 'type' => 'input', 'field' => 'custom_name'),
                array('title' => '分销商类型', 'type' => 'select', 'field' => 'custom_type', 'data' => $response['custom_type'], 'remark' => "<span id = 'shop_name'></span>"),
                array('title' => '分销商分类', 'type' => 'select', 'field' => 'custom_grade', 'data' => $response['area']['grades']),
                array('title' => '分销商联系人', 'type' => 'input', 'field' => 'contact_person',),
                array('title' => '手机号', 'type' => 'input', 'field' => 'mobile',),
                array('title' => '联系电话', 'type' => 'input', 'field' => 'tel',),
                array('title' => '联系人地址(国)', 'type' => 'select', 'field' => 'country', 'data' => $response['area']['country']),
                array('title' => '联系人地址(省)', 'type' => 'select', 'field' => 'province', 'data' => $response['area']['province']),
                array('title' => '联系人地址(市)', 'type' => 'select', 'field' => 'city', 'data' => $response['area']['city']),
                array('title' => '联系人地址(区)', 'type' => 'select', 'field' => 'district', 'data' => $response['area']['district']),
                array('title' => '详细地址', 'type' => 'input', 'field' => 'address',),
                array('title' => '结算价格', 'type' => 'select', 'field' => 'custom_price_type', 'data' => array(array('0', '吊牌价')/* ,array('1','分销价') */, array('2', '批发价'))),
                array('title' => '结算折扣', 'type' => 'input', 'field' => 'custom_rebate', 'remark' => "<span style='color: #F00'>输入值需小于等于1</span>", 'value' => '1.0'),
                array('title' => '运费结算方式', 'type' => 'select', 'field' => 'settlement_method', 'data' => $response['settlement_method'], 'remark' => "<span style='color: #000000' id = 'fx_express_money'></span>"),
                array('title' => '', 'type' => 'checkbox', 'field' => 'ckeck_user', 'remark' => "开通分销商账号"),
                array('title' => '账号', 'type' => 'input', 'field' => 'user_code'),
                array('title' => '密码', 'type' => 'password', 'field' => 'password'),
                array('title' => '是否启用', 'type' => 'radio_group', 'field' => 'is_effective', 'data' => array(array('0', '未启用'), array('1', '启用'))),
            );
        } else {
            $fields = array(
                array('title' => '分销商编号', 'type' => 'input', 'field' => 'custom_code', 'remark' => $remark, 'edit_scene' => 'add'),
                array('title' => '分销商名称', 'type' => 'input', 'field' => 'custom_name'),
                array('title' => '分销商类型', 'type' => 'select', 'field' => 'custom_type', 'data' => $response['custom_type'], 'remark' => "<span id = 'shop_name'></span>"),
                array('title' => '分销商联系人', 'type' => 'input', 'field' => 'contact_person',),
                array('title' => '手机号', 'type' => 'input', 'field' => 'mobile',),
                array('title' => '联系电话', 'type' => 'input', 'field' => 'tel',),
                array('title' => '联系人地址(国)', 'type' => 'select', 'field' => 'country', 'data' => $response['area']['country']),
                array('title' => '联系人地址(省)', 'type' => 'select', 'field' => 'province', 'data' => $response['area']['province']),
                array('title' => '联系人地址(市)', 'type' => 'select', 'field' => 'city', 'data' => $response['area']['city']),
                array('title' => '联系人地址(区)', 'type' => 'select', 'field' => 'district', 'data' => $response['area']['district']),
                array('title' => '详细地址', 'type' => 'input', 'field' => 'address',),
                array('title' => '结算折扣', 'type' => 'input', 'field' => 'custom_rebate', 'remark' => "<span style='color: #F00'>输入值需小于等于1</span>", 'value' => '1.0'),
                array('title' => '是否启用', 'type' => 'radio_group', 'field' => 'is_effective', 'data' => array(array('0', '未启用'), array('1', '启用'))),
            );
        }
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
                'hidden_fields' => array(array('field' => 'custom_id')),
            ),
            'buttons' => $button,
            'act_edit' => 'base/custom/do_edit', //edit,add,view
            'act_add' => 'base/custom/do_add',
            'data' => $response['data'],
            'callback' => 'goto_edit',
            'rules' => array(
                array('custom_code', 'require'),
                array('custom_name', 'require'),
                array('contact_person', 'require'),
                array('mobile', 'require'),
                array('province', 'require'),
                array('city', 'require'),
                array('custom_rebate', 'require'),
            ),
        ));
        ?>
    </div>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var fixed_money = "<?php echo $response['data']['fixed_money'];?>";
    var type = "<?php echo $response['app_scene'];?>";
    var fx_type = '<?php echo $response['data']['custom_type'];?>';
    var shop_name = '<?php echo $response['shop_name'];?>';
    var settlement_method = '<?php echo $response['data']['settlement_method'];?>';
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    var user_code = '<?php echo $response['data']['user_code'];?>';
    
    if(type == 'add'){
        $("#rd_is_effective_1").attr("checked",true);
    }
    if(fx_type == 'tb_fx' && shop_name != ''){
        $('#shop_name').html('关联店铺：' + shop_name);
    }
    if(type == 'edit') {
        if(settlement_method == 1) {
            $('#settlement_method').val('1');
        }
        if(user_code != '') {
            $('#ckeck_user').hide();
            $('#ckeck_user').next().hide();
            $("#ckeck_user").parent().prev().hide();
            $('#password').attr('disabled',true);
            $('#user_code').attr('disabled',true);
            $('#password').attr('value','************');
        }
    }
    $('#user_code').attr('maxLength','16');
    $('#password').attr('maxLength','16');
    form.on('beforesubmit', function () {
        if (($("#user_code").val() == '' || $('#user_code').val() == undefined) && $('#ckeck_user').is(':checked')) {
            BUI.Message.Alert('请填写账号');
            return false;
        }
        if (($("#password").val() == '' || $('#password').val() == undefined) && $('#ckeck_user').is(':checked')) {
            BUI.Message.Alert('请填写密码');
            return false;
        }
    });
    
    function goto_edit(data, ES_frmId) {
        if (data.status == 1) {
            BUI.Message.Alert(data.message);
            parent._action();
            ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');	
        } else {
            BUI.Message.Alert(data.message);
        }
    }

    $(document).ready(function () {
        var user_code = '<?php echo $response['data']['user_code']; ?>';
        if(user_code != '') {
            $('#ckeck_user').attr('checked',true);
        } else {            
            $('#user_code').attr('disabled',true);
            $('#password').attr('disabled',true);
        }
        $('#fixed_money').val(fixed_money);
        $("#account_money,#frozen_money,#settlement_amount").attr("disabled", "disabled");
        $("#settlement_method").change();
        
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
        if($('#country').val() == 1 && type == 'add') {
            var parent_id = $('#country').val();
            areaChange(parent_id,0,url);
        }
    });
    
    $('#settlement_method').change(function(){
        if($('#settlement_method :selected').val() == 0) {//固定运费
            $('#fx_express_money').html("固定费用（运费）<input type = 'text' id = 'fixed_money' value = '"+fixed_money+"' name = 'fixed_money' style='width:50px'/>");
        } else {
            $('#fx_express_money').html('');
        }
    });
    $('#ckeck_user').change(function() {
        if($('#ckeck_user').is(':checked')) {
            if(type == 'edit' &&　user_code != '') {
                $('#password').attr('disabled',true);
                $('#user_code').attr('disabled',true);
            } else {
                $('#password').attr('disabled',false);
                $('#user_code').attr('disabled',false);
            }
        } else {
            $('#user_code').attr('disabled',true);
            $('#password').attr('disabled',true);
        }
    });

</script>

