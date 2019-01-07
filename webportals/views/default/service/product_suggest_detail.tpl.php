<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看产品建议提单',
    'links' => array(
    )
));
?>
<?php echo load_js('ueditor1_4_3/ueditor.config.js,ueditor1_4_3/ueditor.all.js') ?>
<div class="panel">
    <div class="panel-header">
        <h3>基本信息</h3>
    </div>
    <div class="panel-body">
        <?php
        render_control('Form', 'form1', array(
            'noform' => true,
            'conf' => array(
                'fields' => array(
                    array('title' => '提单编号', 'type' => 'input', 'field' => 'xqsue_number', 'show_scene' => 'view'),
                    array('title' => '客户名称', 'type' => 'input', 'field' => 'xqsue_kh_id_name', 'show_scene' => 'view', 'edit_scene' => ''),
                    array('title' => '客户联系人', 'type' => 'input', 'field' => 'xqsue_kh_contact'),
                    array('title' => '客户联系方式', 'type' => 'input', 'field' => 'xqsue_kh_phone'),
                    array('title' => '提单人员', 'type' => 'input', 'field' => 'xqsue_user_name', 'show_scene' => 'view',),
                    array('title' => '提单邮箱', 'type' => 'input', 'field' => 'xqsue_email', 'show_scene' => 'view'),
                    array('title' => '提单创建时间', 'type' => 'input', 'field' => 'xqsue_submit_time', 'show_scene' => 'view'),
                    array('title' => '产品模块', 'type' => 'input', 'field' => 'xqsue_product_fun_name', 'show_scene' => 'view'),
                ),
                'hidden_fields' => array(array('field' => 'xqsue_number')),
            ),
            'col' => 3,
            'data' => $response['data'],
            'rules' => 'service/xqissue_add'
        ));
        ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3>产品建议详情</h3>
    </div> 
    <div class="panel-body">
        <?php
        render_control('Form', 'form2', array(
            'noform' => true,
            'conf' => array(
                'fields' => array(
                    array('title' => '需求标题', 'type' => 'input', 'field' => 'xqsue_title'),
                    array('title' => '业务背景', 'type' => 'textarea', 'field' => 'xqsue_background',),
                    array('title' => '需求详情', 'type' => 'richinput', 'field' => 'xqsue_detail', 'span' => 20,),
                ),
            ),
            'col' => 1,
            'data' => $response['data'],
            'rules' => 'service/xqissue_add'
        ));
        ?>
    </div>
    <div class="row">
        <div class="control-group span11">
            <label class="control-label span3">需求附件：</label>
            <div class="span8 controls">
                <?php foreach ($response['data']['fjmx'] as $fjmx) { ?>
                    <a style="cursor:pointer" onclick="downFile('<?php echo $fjmx['xqnex_path'] ?>', '<?php echo $fjmx['xqnex_name'] ?>')">
                        <?php echo $fjmx['xqnex_name'] ?>
                    </a>
                    <br>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php if ($app['scene'] == "view") : ?>
    <div class="panel">
        <div class="panel-header">
            <h3>需求处理结果</h3>
        </div>
        <div class="panel-body">
            <?php
            render_control('Form', 'form1', array(
                'noform' => true,
                'conf' => array(
                    'fields' => array(
                        array('title' => '提单状态', 'type' => 'select', 'field' => 'xqsue_status', 'edit_scene' => 'add', 'data' => ds_get_select_by_field('xqissue_type', 2)),
                        array('title' => '受理人', 'type' => 'input', 'field' => 'xqsue_accept_user_name', 'show_scene' => 'view'),
                        array('title' => '受理时间', 'type' => 'date', 'field' => 'xqsue_accept_time', 'edit_scene' => 'edit'),
                        array('title' => '需求类型', 'type' => 'select', 'field' => 'xqsue_xqtype', 'data' => ds_get_select_by_field('xqsuetype', 3)),
                        array('title' => '处理方式', 'type' => 'select', 'field' => 'xqsue_processtype', 'data' => ds_get_select_by_field('xqsue_processtype', 3)),
                        array('title' => '预返时间', 'type' => 'date', 'field' => 'xqsue_return_time', 'edit_scene' => 'edit'),
                        array('title' => '审批人', 'type' => 'input', 'field' => 'xqsue_idea_user_name', 'show_scene' => 'view'),
                        array('title' => '审批时间', 'type' => 'date', 'field' => 'xqsue_idea_time', 'edit_scene' => 'edit'),
                        array('title' => '审批意见', 'type' => 'textarea', 'field' => 'xqsue_idea', 'edit_scene' => 'edit'),
                    ),
                ),
                'col' => 3,
                'data' => $response['data'],
            ));
            ?>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    //附件下载js
    function downFile(filepath, downname) {
        window.location = "?app_act=common/file/download_upload_file&path=" + filepath + "&name=" + downname;
    }
</script>



