<style>
    body{overflow-x:hidden;}
</style>
<link rel="icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" mce_href="favicon.ico" type="image/x-icon">

<?php
render_control('PageHead', 'head1', array('title' => '系统日志',));
?>
<div id="tab">
    <ul>
        <li class="bui-tab-panel-item active"><a href="#">登录日志</a></li>
        <li class="bui-tab-panel-item"><a href="#">操作日志</a></li>
        <li class="bui-tab-panel-item"><a href="#">接口日志</a></li>
    </ul>
</div>
<div id="panel" class="" style="padding-top:2px">
    <div id="p1">
        <div id="p1_form" >
            <?php
            render_control('SearchForm', 'searchForm1', array(
                'cmd' => array(
                    'label' => '查询',
                    'id' => 'btn-search1'
                ),
                'fields' => array(
                    array(
                        'label' => '登录名',
                        'type' => 'input',
                        'id' => 'user_code'
                    ),
                    array(
                        'label' => '真实姓名',
                        'type' => 'input',
                        'id' => 'user_name'
                    ),
                    array(
                        'label' => 'IP地址',
                        'type' => 'input',
                        'id' => 'ip'
                    ),
                    array(
                        'label' => '操作类型',
                        'type' => 'select',
                        'id' => 'type',
                        'data' => $response['type'],
                    ),
                    array(
                        'label' => '操作时间',
                        'type' => 'group',
                        'field' => 'daterange1',
                        'child' => array(
                            array('title' => 'start', 'type' => 'date', 'field' => 'add_time_start', 'value' => '2015-05-19'),
                            array('pre_title' => '~', 'type' => 'date', 'field' => 'add_time_end'),
                        )
                    ),
                )
            ));
            ?>
            <?php
            render_control('DataTable', 'table1', array('conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '登录名',
                            'field' => 'user_code',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '真实姓名',
                            'field' => 'user_name',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '类型',
                            'field' => 'type',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '时间',
                            'field' => 'add_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'IP地址',
                            'field' => 'ip',
                            'width' => '300',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'sys/LoginLogModel::get_by_page',
                'queryBy' => 'searchForm1',
                'init' => 'nodata',
                'idField' => 'login_log_id',
            ));
            ?>
        </div>
    </div>
    <div id="p2">
        <div id="p2_form" >
            <?php
            render_control('SearchForm', 'searchForm2', array(
                'cmd' => array(
                    'label' => '查询',
                    'id' => 'btn-search2'
                ),
                'fields' => array(
                    array(
                        'label' => '登录名',
                        'type' => 'input',
                        'id' => 'user_code'
                    ),
                    array(
                        'label' => '真实姓名',
                        'type' => 'input',
                        'id' => 'user_name'
                    ),
                    array(
                        'label' => 'IP地址',
                        'type' => 'input',
                        'id' => 'ip'
                    ),
                    array(
                        'label' => '业务模块35453',
                        'type' => 'select',
                        'id' => 'module',
                        'data' => $response['module'],
                    ),
                    array(
                        'label' => '操作类型',
                        'type' => 'select',
                        'id' => 'operate_type',
                        'data' => $response['operate_type'],
                    ),
                    array(
                        'label' => '商品/单据编码',
                        'type' => 'input',
                        'id' => 'yw_code'
                    ),
                    array(
                        'label' => '操作详情',
                        'type' => 'input',
                        'id' => 'operate_xq',
                        'title' => '支持模糊搜索'
                    ),
                    array(
                        'label' => '操作时间',
                        'type' => 'group',
                        'field' => 'daterange1',
                        'child' => array(
                            array('title' => 'start', 'type' => 'date', 'field' => 'add_time_start'),
                            array('pre_title' => '~', 'type' => 'date', 'field' => 'add_time_end'),
                        )
                    ),
                )
            ));
            ?>

            <?php
            render_control('DataTable', 'table2', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '登录名',
                            'field' => 'user_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '真实姓名',
                            'field' => 'user_name',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '业务模块',
                            'field' => 'module',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作类型',
                            'field' => 'operate_type',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品/单据编码',
                            'field' => 'yw_code',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'add_time',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '登录IP',
                            'field' => 'ip',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作详情',
                            'field' => 'operate_xq',
                            'width' => '500',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'sys/OperateLogModel::get_by_page',
                'queryBy' => 'searchForm2',
                'init' => 'nodata',
                'idField' => 'operate_log_id',
            ));
            ?>
        </div>
    </div>
    <div id="p3">
        <div id="p3_form" >
            <?php
            $time = date('Y-m-d H:i:s');
            $keyword_type = array();
            $keyword_type['front_hour'] = '往前推小时数';
            $keyword_type['behind_hour'] = '往后推小时数';
            $keyword_type = array_from_dict($keyword_type);
            render_control('SearchForm', 'searchForm4', array(
                'cmd' => array(
                    'label' => '查询',
                    'id' => 'btn-search4'
                ),
                'fields' => array(
                    array(
                        'label' => '数据表选择',
                        'type' => 'select',
                        'id' => 'log_table',
                        'data' => array(array(0, 'api_logs'), array(1, 'api_open_logs'))
                    ),
                    array(
                        'label' => '日志记录时间',
                        'type' => 'group',
                        'field' => 'log_add_time',
                        'child' => array(
                            array('title' => 'start', 'type' => 'time', 'field' => 'log_add_time_start'),
                        )
                    ),
                    array(
                        'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
                        'type' => 'input',
                        'title' => '',
                        'data' => $keyword_type,
                        'id' => 'keyword',
                        'title' => '最大不超过72小时'
                    // 'help' => '最大不超过72小时',
                    ),
                    array(
                        'label' => '关键字',
                        'type' => 'input',
                        'id' => 'keywords_sle',
                        'title' => '请求或被请求参数'
                    ),
                )
            ));
            ?>

            <?php
            render_control('DataTable', 'table4', array(
                'conf' => array(
                    'list' => array(array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '请求/返回参数',
                            'field' => '_operate',
                            'width' => '120',
                            'buttons' => array(
                                array(
                                    'id' => 'view',
                                    'title' => '查看',
                                    'act' => 'pop:sys/sys_log/do_view_log&logs_id={logs_id}&table_type={table_type}',
                                    'pop_size' => '900,600',
                                ),
                            ),
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'ID',
                            'field' => 'logs_id',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '类型',
                            'field' => 'type',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '方法',
                            'field' => 'method',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => 'URL',
                            'field' => 'url',
                            'width' => '300',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '日志时间',
                            'field' => 'add_time',
                            'width' => '200',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'sys/OperateLogModel::get_by_log_page',
                'queryBy' => 'searchForm4',
                'init' => 'nodata',
                'idField' => 'logs_id',
            ));
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    BUI.use(['bui/tab', 'bui/mask'], function (Tab) {
        var tab = new Tab.TabPanel({
            srcNode: '#tab',
            elCls: 'nav-tabs',
            itemStatusCls: {
                'selected': 'active'
            },
            panelContainer: '#panel'//如果不指定容器的父元素，会自动生成
                    //selectedEvent : 'mouseenter',//默认为click,可以更改事件
        });
        tab.render();
    });

    $(function () {
        $(".control-label").css("width", "100px");
        var time = (new Date).getTime() - 24 * 60 * 60 * 1000;
        var date = new Date(time);
        var month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1;
        var currentDate = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
        var start_time = date.getFullYear() + "-" + month + "-" + currentDate;
        $("#searchForm1 #add_time_start").val(start_time);
        $("#searchForm2 #add_time_start").val(start_time);
        $("#searchForm3 #add_time_start").val(start_time);
        $("#searchForm4 #log_add_time_start").val('<?php echo $time; ?>');
    });
</script>