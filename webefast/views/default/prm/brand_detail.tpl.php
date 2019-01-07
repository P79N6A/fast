<?php

render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑品牌',
    'links' => array(
        'prm/brand/do_list' => '品牌列表'
    )
));
?>
 <?php 
  if ($response['app_scene'] == 'add')
  $remark = "一旦保存不能修改";
  else 
  $remark = "";
 ?>
<?php

$data = array();
if (isset($response['data']) && $response['data'] != '') {
    $data = $response['data'];
}
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '代码', 'type' => 'input', 'field' => 'brand_code', 'remark' => $remark, 'edit_scene' => 'add'),
            array('title' => '名称', 'type' => 'input', 'field' => 'brand_name'),
//            array('title' => 'logo', 'type' => 'file', 'field' => 'brand_logo',
//                 'rules' => array('ext' => '.jpg,.png,.gif', 'max' => 2)),
           
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'brand_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'prm/brand/do_edit', //edit,add,view
    'act_add' => 'prm/brand/do_add',
    'data' => $data,
    'rules' => array(
        array('brand_code', 'require'),
        array('brand_name', 'require'),
    ),
));
?>


