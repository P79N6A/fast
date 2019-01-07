<style>
    .span11 {
        width: 730px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '版本切换',
    'links' => array(//   array('url' => 'basedata/hostinfo/do_list', title => '云主机(VM)列表')
    )
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array(
                'title' => '客户名称',
                'type' => 'input',
                'field' => 'kh_name',
                'remark' => '',
                //'show_scene' => 'edit'
                'edit_scene' => 'add'
            ),
            array(
                'title' => '数据库名称',
                'type' => 'input',
                'field' => 'rem_db_name',
                'remark' => '',
                //'show_scene' => 'edit'
                'edit_scene' => 'add'
            ),
            array(
                'title' => '选择RDS',
                'type' => 'select_pop',
                'field' => 'rds_id',
                'select' => 'products/rdsinfo_2',
                'remark' => '(切换前) ' . $response['data']['rds_link'],
                'show_scene' => 'add,edit'
            ),
            array(
                'title' => '选择定时器ip',
                'type' => 'select_pop',
                'field' => 'time_ip',
                'select' => 'products/vminfo_2',
                'remark' => '(切换前) ' . $response['data']['rem_db_version_ip'],
                'show_scene' => 'add,edit'
            ),
            array(
                'title' => '选择接口ip',
                'type' => 'select_pop',
                'field' => 'api_ip',
                'select' => 'products/vminfo_2',
                'remark' => '(切换前) ' . $response['data']['rem_db_api_ip'],
                'show_scene' => 'add,edit'
            ),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 1, //列数
    'hidden_fields' => array(array('field' => 'rem_db_khid'), array('field' => 'rem_db_pid')),
    //'act_edit' => 'basedata/hostinfo/ali_edit', //edit,add,view
    'act_edit' => 'market/productorder/version_update_action',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => 'basedata/share_update', //对应方法在conf/validator/basedata_conf.php
    'event' => array('beforesubmit' => 'formBeforesubmit'),
));
?>
<script type="text/javascript">
    /**
     * 表单验证
     */
    function formBeforesubmit() {

    }

    /**
     * 页面加载
     */
    $(document).ready(function () {

    })


    /**
     * 回调函数
     * @param result
     * @param ES_frmId
     */
    function after_submit(result, ES_frmId) {
        if (result.status == 1) {
            BUI.Message.Alert(result.message, function () {
                location.reload();
            }, 'success');
        } else {
            BUI.Message.Alert(result.message, 'error');
        }
    }
</script>
