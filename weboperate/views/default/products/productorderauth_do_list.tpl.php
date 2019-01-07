<?php render_control('PageHead', 'head1',array('title'=>'订购授权列表',
//    	'links'=>array(
//        array('url'=>'basedata/platform/detail&app_scene=add', 'title'=>'新建平台',  'pop_size'=>'500,400'),
//	),
	'ref_table'=>'table'    
    ));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '客户',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'client' 
        ),
        array (
            'label' => '产品',
            'title' => '产品名称',
            'type' => 'select',
            'id' => 'product',
             'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '营销类型',
            'title' => '客户名称',
            'type' => 'select',
            'id' => 'market',
             'data'=>ds_get_select('market',1)
        ),
    ) 
) );
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'pra_kh_id_name',
                'width' => '180',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pra_cp_id_name',
                'width' => '120',
                'align' => ''
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'pra_product_version',
                'width' => '70',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '授权KEY',
                'field' => 'pra_authkey',
                'width' => '150',
                'align' => ''    
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '授权点数',
                'field' => 'pra_authnum',
                'width' => '70',
                'align' => ''    
            ),
                        array (
                'type' => 'text',
                'show' => 1,
                'title' => '淘宝应用KEY',
                'field' => 'pra_app_key',
                'width' => '90',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '店铺数量',
                'field' => 'pra_shopnum',
                'width' => '70',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'pra_startdate',
                'width' => '90',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'pra_enddate',
                'width' => '90',
                'align' => '',
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '授权状态',
                'field' => 'pra_state',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'act' => 'products/productorderauth/detail&app_scene=view', 'show_name' => '查看详细'),
                    array('id' => 'renew', 'title' => '续费', 'callback' => 'do_renew', 'show_cond' => ''),
//                        array('id'=>'edit', 'title' => '编辑', 
//                		'act'=>'product/productorderauth/detail&app_scene=edit', 'show_name'=>'编辑平台'),
                ),
            )
        ) 
    ),
    'dataset' => 'products/ProductorderauthModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pra_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script>
    //续费
    function do_renew(_index, row) {
        var pra_id = row.pra_id;
        var url = '?app_act=products/productorderauth/do_renew&app_scene=edit&pra_id=' + pra_id;
        var title = "续费";
        btn_show_dialog(url, title, {w: 600, h: 400});
    }

    function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {
                location.reload();
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }

</script>

