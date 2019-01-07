<style>
    .remark {
        margin-left: 0;
        font-size:10px;
    }
    .span11 {
        width: 630px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '初始化系统用户名和密码',
    'links' => array(
//        array('url'=>'market/productorder/detail&app_scene=add', 'title'=>'新增产品订购', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '登录名', 'type' => 'input', 'field' => 'user_code', 'show_scene' => 'add,edit'),
            array('title' => '初始密码', 'type' => 'password', 'field' => 'password', 'width' => '', 'remark' => '密码长度为8-20位，须为数字、大写字母、小写字母和特殊符号的组合', 'show_scene' => 'add,edit'),
            array('title' => '确认密码', 'type' => 'password', 'field' => 're_password', 'show_scene' => 'add,edit'),
            array('title' => '真实名', 'type' => 'input', 'field' => 'user_name', 'show_scene' => 'add,edit'),
            array('title' => '电话号码', 'type' => 'input', 'field' => 'tel', 'show_scene' => 'add,edit'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 1,//显示列数
    'hidden_fields' => array(array('field' => 'pro_num')),//隐藏字段
    //'act_edit'=>'market/productorder/porders_edit', //edit,add,view
    'act_add' => 'market/productorder/set_login',
    'data' => $response['data'],
    'rules' => 'basedata/pro_init',//设置非空字段
    'callback' => 'after_submit',
));
?>


<script type="text/javascript">
    /**
     * 提交成功，回调
     * @param {type} result
     * @param {type} ES_frmId
     * @returns {undefined}
     */
    function after_submit(result, ES_frmId) {
        if (result.status == 1) {
            BUI.Message.Alert(result.message, function () {
                //关闭页签        
                ui_closeTabPage("<?php echo $request['ES_frmId'] ?>");
            }, 'success');
        } else {
            BUI.Message.Alert(result.message, 'error');
        }
    }

</script>

