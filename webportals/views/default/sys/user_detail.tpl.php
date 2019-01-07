<?php

//用户详细
$links = array(array('url' => 'sys/user/do_list', 'title' => '用户列表'));
//个人信息
if (!isset($request['_id'])) {
    //存在id，表示用户信息链接详细
    $links[] = array('url' => 'sys/user/do_chgpasswd&app_scene=edit', 'title' => '修改密码', 'is_pop' => true, 'pop_size' => '500,400');
    if ($app["scene"] != 'edit' && $app["scene"] != 'add') {
        $links[] = array('url' => 'sys/user/detail_info&app_scene=edit', 'title' => '编辑个人信息', 'is_pop' => false, 'pop_size' => '500,400');
    }
}
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看用户',
    'links' => $links,
        )
);
?>
<?php

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '登录名', 'type' => 'input', 'field' => 'user_code', 'edit_scene' => 'add'),
            array('title' => '真实名', 'type' => 'input', 'field' => 'user_name', 'edit_scene' => 'add'),
            array('title' => '有效用户', 'type' => 'checkbox', 'field' => 'user_active', 'edit_scene' => ''),
            array('title' => '所属机构', 'type' => 'input', 'field' => 'user_org_code_name', 'edit_scene' => ''),
            array('title' => '直属上级', 'type' => 'input', 'field' => 'user_highedrup_name', 'edit_scene' => ''),
            array('title' => '邮箱地址', 'type' => 'input', 'field' => 'user_email', 'edit_scene' => ''),
            array('title' => '性别', 'type' => 'select', 'field' => 'user_sex', 'data' => ds_get_select_by_field('sex', 2), 'edit_scene' => 'add,edit'),
            array('title' => '联系电话', 'type' => 'input', 'field' => 'user_phone', 'edit_scene' => 'add,edit'),
            array('title' => '手机', 'type' => 'input', 'field' => 'user_mobile', 'edit_scene' => 'add,edit'),
            array('title' => '工号', 'type' => 'input', 'field' => 'user_work_no', 'edit_scene' => 'add,edit'),
            array('title' => '生日', 'type' => 'date', 'field' => 'user_birthday', 'edit_scene' => 'add,edit'),
            array('title' => '曾从事岗位', 'type' => 'input', 'field' => 'user_worked'),
            array('title' => '学历', 'type' => 'select', 'field' => 'user_education', 'data' => ds_get_select_by_field('education', 2)),
            //ds_get_select(city)
            array('title' => '职位', 'type' => 'input', 'field' => 'user_title'),
            //array('title'=>'岗位', 'type'=>'select', 'field'=>'user_post','show_scene'=>'edit','data'=>load_model('sys/UserModel_ex')->get_codelist("osp_post",array("post_id","post_name"),"post_state='1'")),
            //array('title'=>'岗位', 'type'=>'input', 'field'=>'user_post_name','show_scene'=>'view'),
            array('title' => '岗位', 'type' => 'select', 'field' => 'user_post', 'data' => ds_get_select('post', 2, array('post_state' => '1'))),
            array('title' => '工作年限', 'type' => 'input', 'field' => 'user_work_limit'),
            array('title' => '身份证号', 'type' => 'input', 'field' => 'user_ident_no'),
            array('title' => '入职时间', 'type' => 'date', 'field' => 'user_in_date'),
            array('title' => '地址', 'type' => 'input', 'field' => 'user_address'),
            array('title' => '邮编', 'type' => 'input', 'field' => 'user_postal'),
            array('title' => '系统管理员', 'type' => 'checkbox', 'field' => 'user_admin', 'edit_scene' => 'edit'),
            array('title' => '已离职', 'type' => 'checkbox', 'field' => 'user_out', 'edit_scene' => ''),
            array('title' => '离职日期', 'type' => 'date', 'field' => 'user_out_date', 'edit_scene' => ''),
            array('title' => '创建人', 'type' => 'input', 'field' => 'user_create_code_name', 'edit_scene' => '', 'show_scene' => 'view,edit'),
            array('title' => '创建日期', 'type' => 'date', 'field' => 'user_create_date', 'edit_scene' => '', 'show_scene' => 'view,edit'),
            array('title' => '修改人', 'type' => 'input', 'field' => 'user_update_code_name', 'edit_scene' => '', 'show_scene' => 'view'),
            array('title' => '修改日期', 'type' => 'date', 'field' => 'user_update_date', 'edit_scene' => '', 'show_scene' => 'view'),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'user_remark'),
        ),
        'hidden_fields' => array(array('field' => 'user_id'), array('field' => 'user_code'), array('field' => 'user_name')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 3,
    'act_edit' => 'sys/user/do_edit', //edit,add,view
    'act_add' => 'sys/user/do_add',
    'data' => $response['data'],
    'rules' => 'sys/user_edit'        //有效性验证
));
?>