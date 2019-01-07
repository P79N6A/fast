<?php
render_control('PageHead', 'head1', array('title' => '快递公司列表',
    'links' => array(
        array('type' => 'js', 'title' => '下载快递公司', 'js' => 'download_express()'),
        array('type' => 'js', 'title' => '同步到客户环境', 'js' => 'sync_express()'),
     //   array('type' => 'js', 'title' => '', 'js' => 'sync_express()'),
        array('url' => 'pubdata/pubdata/express_detail&app_scene=add', 'title' => '新增快递', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'  //ref_table 表示是否刷新父页面
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => '代码',
            'title' => '代码',
            'type' => 'input',
            'id' => 'company_code',
        ),
        array(
            'label' => '名称',
            'title' => '代码',
            'type' => 'input',
            'id' => 'company_name',
        ),
    )
));
?>

<hr/>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '代码',
                'field' => 'company_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                   'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'company_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流单号匹配规则',
                'field' => 'rule',
                'width' => '500',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'pubdata/BaseExpressCompanyModel::get_by_page',
    'queryBy' => 'searchForm',
    'params' => array('filter' => array()),
    'idField' => 'company_id',
));
?>
<script type="text/javascript">
    function download_express() {
        var url = "?app_act=pubdata/pubdata_sync/download_express&app_fmt=json";
        $.post(url, {}, function (ret) {
            if (ret.status < 1) {
                BUI.Message.Alert(ret.message, 'error');
            } else {
                BUI.Message.Alert(ret.message, 'info');
            }

        }, 'json');
    }

    function sync_express() {
        var url = "?app_act=pubdata/pubdata_sync/sync_express&app_fmt=json";
        $.post(url, {}, function (ret) {
            if (ret.status < 1) {
                BUI.Message.Alert(ret.message, 'error');
            } else {
                BUI.Message.Alert(ret.message, 'info');
            }

        }, 'json');
    }
</script>