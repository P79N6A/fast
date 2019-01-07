<?php

render_control('PageHead', 'head1', array('title' => '快递公司列表',
    'links' => array(
    //array('url' => 'base/shop/detail&app_scene=add', 'title' => '添加店铺', 'is_pop' => true, 'pop_size' => '500,500'),
    ),
    'ref_table' => 'table'
));
?>

<?php

render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '代码',
            'title' => '快递公司代码',
            'type' => 'input',
            'id' => 'company_code',
        ),
        array(
            'label' => '名称',
            'title' => '快递公司名称',
            'type' => 'input',
            'id' => 'company_name',
        ),
     ),
));
?>

<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
               array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => array(

		array(
                  'id' => 'delete',
                  'title' => '编辑接口参数',
                  'callback' => 'do_edit_param',
                  'show_cond' => "obj.company_code == 'YUNDA'"
                  ),        	

    ),   ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递公司代码',
                'field' => 'company_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递公司名称',
                'field' => 'company_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '匹配规则',
                'field' => 'rule',
                'width' => '300',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'remark',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/ExpressCompanyModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'company_id',
));
?>
<script>

    function do_edit_param(_index, row) {
        var url = '?app_act=base/express_company/edit_param&company_code='+row.company_code;
        var _title = '参数编辑';
        var _opts = {w:380,h:360};
        show_dialog(url, _title, _opts);
    }
function show_dialog(_url, _title, _opts) {
	
    new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {               

                if (typeof _opts.callback == 'function') _opts.callback();
            }
        }).show();
}
</script>