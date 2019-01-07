 <style type="text/css">
    #key,#pid {
        width: 230px;
    }
    .form-horizontal .control-label{
        width: 30px;
    }
</style>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => ' PID', 'type' => 'input', 'field' => 'pid'),
            array('title' => ' KEY', 'type' => 'input', 'field' => 'key'),
        )
    ),
    'buttons' => array(
        array('label' => '保存', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'fx/account/alipay_do_edit',
    'data' => $response,
    'callback' => 'dd',
    'rules' => array(
        array('pid', 'require'),
        array('key', 'require'),
    ),
));
?>
<div>
<span style="color: red">获取步骤说明：</span><br>
<span style="color: red">1、在 蚂蚁金服开放平台->开发者平台->网页&移动应用，创建应用“支付接入”；</span><br>
<span style="color: red">2、设定新增应用的“使用场景”和“应用名称”，其中“使用场景”需选择“自用型应用”；</span><br>
<span style="color: red">3、选择并添加新增应用的功能--“电脑网站支付”，并进行开发配置后，提交审核；</span><br>
<span style="color: red">4、新增的应用上线后，在 蚂蚁金服商家中心->我的商家服务->常用功能->查看PID/KEY，可查看到获取的PID及MD5密钥(即KEY)&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="assets/images/alipay_help.png"><b>示意图</b></a></span><br>
</div>
<script>
    var old_pid = '<?php echo $response['pid']; ?>';
    var old_key = '<?php echo $response['key']; ?>';
    form.on('beforesubmit', function () {
        var key = $("#key").val();
        var pid = $("#pid").val();
        if (old_pid == pid.trim() && old_key == key.trim()) {
//            BUI.Message.Alert('数据未做修改！', 'error');
            return false;
        }
    });
    function dd(data, Esfrom_Id) {
        if (data.status < 0) {
            BUI.Message.Alert('操作失败！', function(){ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');}, 'error');
        } else {
            BUI.Message.Alert('操作成功！',function(){ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');}, 'success');
        }
    }
</script>


