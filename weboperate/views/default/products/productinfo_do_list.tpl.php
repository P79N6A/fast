<?php
render_control('PageHead', 'head1', array('title' => '产品信息',
    'links' => array(
        array('url' => 'products/productinfo/detail&app_scene=add', 'title' => '新建产品', 'is_pop' => false, 'pop_size' => '500,400'),
    ),
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
            'label' => '关键字',
            'title' => '产品代码/产品名称',
            'type' => 'input',
            'id' => 'keyword'
        )
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品代码',
                'field' => 'cp_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'cp_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '英文名称',
                'field' => 'cp_en_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品简称',
                'field' => 'cp_jc',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统维护',
                'field' => 'cp_maintain',
                'width' => '100',
                'format_js' => array('type' => 'function', 'value' => 'get_cp_maintain')
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '详细',
                        'act' => 'products/productinfo/detail&app_scene=view', 'show_name' => '产品详情'),
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'products/productinfo/detail&app_scene=edit', 'show_name' => '编辑产品',
                        'show_cond' => 'obj.is_buildin != 1'),
                ),
            )
        )
    ),
    'dataset' => 'products/ProductModel::get_products_info',
    'queryBy' => 'searchForm',
    'idField' => 'cp_id',
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>
<script>

    function get_cp_maintain(value, row, index) {
        if (value == 1) {

            return '<a href="javascript:void(0)" onclick="set_cp_maintain(this,' + row.cp_id + ',' + value + ')"><img  src="../../webpub/theme/default/images/ok.png" /></a>';

        }  
        else{
                  // return '111';   
             return '<a href="javascript:void(0)" onclick="set_cp_maintain(this,' + row.cp_id + ',' + value + ')"><img  src="../../webpub/theme/default/images/no.gif" /></a>';

            }

    }
    function set_cp_maintain(obj, cp_id,status) {
        var url = "?app_act=products/productinfo/set_cp_maintain&app_fmt=json";
        var param = {};
        param.cp_id=cp_id;
          param.status=status;
        
        $.post(url, param, function(ret) {
            if(ret.status>0){
                $('#btn-search').click();
            }else{
                BUI.Message.Alert(ret.message);
            }
        }, 'json');
    }

</script>