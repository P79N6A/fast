<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '服务商信息',
    'links' => array(
        array('url' => 'basedata/cloud/do_list', 'title' => '服务商列表'),
    )
));
?>
<style>
    .panel-body {padding: 2px;}
    .panel-body table {margin: 0; }
</style>
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

