<?php
render_control('PageHead', 'head1', array('title' => '设置结转客户库',
//	'links'=>array(
//		array('url'=>'market/valueservice/do_list','title'=>'增值服务列表')
//	)
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '客户', 'type' => 'select', 'field' => 'kh_id', 'data' => $response['select_kh']),
            array('title' => '结转库', 'type' => 'select', 'field' => 'carry_db_id', 'data' => $response['select_carry_db']),
        ),
        'hidden_fields' => array(array('field' => 'kh_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    //'act_edit'=>'market/valueservice_cat/do_edit', //edit,add,view
    'act_add' => 'sys/carry/do_add_db_kh&app_fmt=json',
    'callback'=>'add_ok',
    'data' => $response['data'],
    'rules' => array(
        array('kh_id', 'require'),
        array('rds_id', 'require'),
    ),
));
?>

<script type="text/javascript">
    function add_ok(data, id) {
        if (data.status > 1) {
            alert('添加成功');
            window.location.reload();
        } else {
            alert(data.message);

        }
    }
</script>