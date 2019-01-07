<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '查看产品',
    'links' => array(
        array('url' => 'basedata/cloud/do_list', 'title' => '服务商列表'),
    )
));
?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
<form  class="form-horizontal" id="form1" action="<?php echo '?app_act=basedata/cloud/do_' . $app["scene"] ?>" method="post">
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">服务商信息</h3>
            <?php if ($app['scene'] == "add" || $app['scene'] == "edit") { ?>
                <div class="pull-right">
                    <button type="submit" class="button button-primary" id="submit">提交</button>
                    <button type="reset" class="button " id="reset">重置</button>
                </div>
            <?php } ?>
        </div>
        <div class="panel-body">
            <?php
            render_control('Form', 'form1', array(
                'noform' => true,
                'conf' => array(
                    'fields' => array(
                        array('title' => '服务商名称', 'type' => 'input', 'field' => 'cd_name',),
                        array('title' => '服务商官网', 'type' => 'input', 'field' => 'cd_official',),
                        array('title' => '服务商描述', 'type' => 'input', 'field' => 'cd_note',),
                    ),
                    'hidden_fields' => array(array('field' => 'cd_id')),
                ),
                'col' => 3,
                'act_edit' => 'basedata/cloud/do_edit', //edit,add,view
                'act_add' => 'basedata/cloud/do_add',
                'data' => $response['data'],
                'rules' => 'basedata/add_cloud', //有效性验证
            ));
            ?>
        </div>
    </div>
</form>
<script type="text/javascript">
    new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        callback: function(data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
        }
    }).render();
</script>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '云主机', 'active' => true), // 默认选中active=true的页签
        array('title' => '云RDS'), // 默认选中active=true的页签
    ),
    'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPageContents">
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=basedata/cloudserver/hostdetail&app_scene=add&app_show_mode=pop&cdid=<?php echo $request['_id'] ?>', '添加云主机', {w: 500, h: 400}, table1Store)"><i class="icon-plus"></i>添加型号</button>
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '型号名称',
                            'field' => 'cm_host_type',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'CPU',
                            'field' => 'cm_host_cpu',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '内存',
                            'field' => 'cm_host_mem',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '硬盘',
                            'field' => 'cm_host_disk',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '带宽',
                            'field' => 'cm_host_net',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '120',
                            'align' => '',
                            'buttons' => array(
                                array('id' => 'edit', 'title' => '编辑',
                                    'act' => 'pop:basedata/cloudserver/hostdetail&app_scene=edit', 'show_name' => '编辑型号配置',
                                ),
                                array('id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'do_delete_host',
                                    'confirm' => '确认要删除吗？'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'basedata/CloudserverModel::get_by_page_host',
                'params' => array('filter' => array('cd_id' => $request['_id'])),
                'idField' => 'cm_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <button class="button button-small" onclick="PageHead_show_dialog_ref('?app_act=basedata/cloudserver/dbdetail&app_scene=add&app_show_mode=pop&cdid=<?php echo $request['_id'] ?>', '添加云数据库型号', {w: 500, h: 400}, table2Store)"><i class="icon-plus"></i>添加型号</button>
            <?php
            render_control('DataTable', 'table2', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '型号名称',
                            'field' => 'cm_db_type',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '内存',
                            'field' => 'cm_db_mem',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '容量',
                            'field' => 'cm_db_disk',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '最大连接数',
                            'field' => 'cm_max_con',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'QPS最大执行次数',
                            'field' => 'cm_max_qps',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'IOPS每秒最大读写次数',
                            'field' => 'cm_max_iops',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '120',
                            'align' => '',
                            'buttons' => array(
                                array('id' => 'edit', 'title' => '编辑',
                                    'act' => 'pop:basedata/cloudserver/dbdetail&app_scene=edit', 'show_name' => '编辑型号配置',
                                ),
                                array('id' => 'del',
                                    'title' => '删除',
                                    'callback' => 'do_delete_db',
                                    'confirm' => '确认要删除吗？'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'basedata/CloudserverModel::get_by_page_db',
                'params' => array('filter' => array('cd_id' => $request['_id'])),
                'idField' => 'cm_id',
                'CheckSelection' => false,
            ));
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    function PageHead_show_dialog_ref(_url, _title, _opts, refgrid) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function() {
                if (refgrid) {
                    refgrid.load();
                }
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
    //删除模块明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_host(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloudserver/do_delete'); ?>",
            data: {cm_id: row.cm_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    table1Store.load();
//                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_delete_db(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: "<?php echo get_app_url('basedata/cloudserver/do_delete'); ?>",
            data: {cm_id: row.cm_id},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    table2Store.load();
//                    tablemdStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>

