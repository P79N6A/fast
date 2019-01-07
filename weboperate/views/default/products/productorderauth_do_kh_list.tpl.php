<?php
render_control('PageHead', 'head1', array('title' => '客户程序授权',
//    	'links'=>array(
//        array('url'=>'basedata/platform/detail&app_scene=add', 'title'=>'新建平台',  'pop_size'=>'500,400'),
//	),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '客户',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'client'
        ),
        array(
            'label' => '程序版本',
            'title' => '程序版本',
            'type' => 'select',
            'id' => 'pra_program_version',
            'data' => ds_get_select_by_field('pra_program_version', 1)
        ),
        array(
            'label' => '产品',
            'title' => '产品名称',
            'type' => 'select',
            'id' => 'product',
            'data' => ds_get_select('chanpin', 1)
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'databind', 'title' => '切换', 'callback' => 'do_switch',),
//                        array('id'=>'edit', 'title' => '编辑', 
//                		'act'=>'product/productorderauth/detail&app_scene=edit', 'show_name'=>'编辑平台'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'pra_kh_id_name',
                'width' => '250',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pra_cp_id_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'pra_product_version',
                'width' => '120',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => ds_get_field('product_version'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '程序版本',
                'field' => 'pra_program_version',
                'width' => '150',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => ds_get_field('pra_program_version'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'pra_startdate',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'pra_enddate',
                'width' => '150',
                'align' => '',
            ), array(
                'type' => 'text',
                'show' => 1,
                'title' => '淘宝KEY',
                'field' => 'pra_app_key',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'products/ProductorderauthModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pra_id',
    'init' => 'nodata'
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script>
    $(function () {
        $('#product').find("option[value='21']").attr("selected", true);
        $('#btn-search').click();

    });
    function do_switch(_index, row) {
        var pra_id=row.pra_id;
        btn_show_dialog('?app_act=products/productorderauth/switch_kh_program&pra_id='+pra_id,'客户程序版本切换', {w:800,h:400});
           
        
    }
  
        
        function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                 
//                tableStore.load();  
                  location.reload();
                if (typeof _opts.callback == 'function') 
                    _opts.callback();
            }
        }).show();
    }
</script>