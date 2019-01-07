<style>
    #birthday{width:140px;}
    #type,#sex,#relation_shop{width: 145px;}
</style>
<?php
$clerk_sex = array(array('0', '保密'), array('1', '男'), array('2', '女'));
$type = 0;
$remark = '';
if ($app['scene'] == 'add') {
    $type = 2;
    array_unshift($clerk_sex, array('', '请选择'));
    $remark = '保存后不可修改';
}
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '工号(账号)', 'type' => 'input', 'field' => 'user_code', 'edit_scene' => 'add', 'remark' => $remark),
            array('title' => '姓名', 'type' => 'input', 'field' => 'user_name'),
            array('title' => '性别', 'type' => 'select', 'field' => 'sex', 'data' => $clerk_sex),
            array('title' => '手机号码', 'type' => 'input', 'field' => 'phone'),
            array('title' => '生日', 'type' => 'date', 'field' => 'birthday'),
            array('title' => '所属门店', 'type' => 'select', 'field' => 'relation_shop', 'data' => load_model('base/ShopModel')->get_select_entity($type)),
            array('title' => '角色', 'type' => 'select', 'field' => 'type', 'data' => ds_get_select_by_field('parttype', $type)),
            array('title' => '初始密码', 'type' => 'html', 'html' => 'baota8888'),
        ),
        'hidden_fields' => array(array('field' => 'user_id'), array('field' => 'user_code'),),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/shop_clerk/do_edit',
    'act_add' => 'base/shop_clerk/do_add',
    'data' => isset($response['data']) ? $response['data'] : '',
    'rules' => array(
        array('user_code', 'require'),
        array('type', 'require'),
        array('relation_shop', 'require'),
//        array('user_code', 'minlength', 'value' => 5),
    )
));
?>
