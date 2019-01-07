<?php
render_control('PageHead', 'head1', array('title' => '产品订购列表',
    'links' => array(
    //array('url'=>'market/productorder/detail&app_scene=add', 'title'=>'新增产品订购', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'cp_id',
            'data' => ds_get_select('chanpin', 1)
        ),
        array(
            'label' => '营销类型',
            'type' => 'select',
            'id' => 'pro_type',
            'data' => ds_get_select('market', 1)
        ),
        array(
            'label' => '客户名称',
            'type' => 'input',
            'id' => 'customer',
            'data' => array()
        ),
//        array (
//            'label' => '订单过期预警',
//            'type' => 'select',
//            'id' => 'orderend',
//            'data'=>ds_get_select_by_field('orderover')
//        ),
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
                'title' => '订购编号',
                'field' => 'pro_num',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'pro_channel_id_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'pro_kh_id_name',
                'width' => '180',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pro_cp_id_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'pro_product_version',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => ds_get_field('product_version'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '营销类型',
                'field' => 'pro_st_id_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '报价方案',
                'field' => 'pro_price_id_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'pro_pay_status',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核状态',
                'field' => 'pro_check_status',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '部署状态',
                'field' => 'pro_is_arrange',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '初始化状态',
                'field' => 'pro_is_init',
                'width' => '80',
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
                    array('id' => 'arrange',
                        'title' => '系统部署',
                        'show_cond' => 'obj.pro_is_arrange!=1&&obj.pro_is_init!=1',
                        'show_name' => '',
                        'callback' => 'sys_arrange',
                    ),
                    array('id' => 'Initialization',
                        'title' => '初始化账号',
                        'show_cond' => 'obj.pro_is_arrange==1&&obj.pro_is_init!=1',
                        'show_name' => '',
                        'callback' => 'Initialization_account',
                    ),
                    array('id' => 'ver_update',
                        'title' => '版本切换',
                        'show_cond' => 'obj.pro_is_arrange==1&&obj.pro_is_init==1',
                        'show_name' => '',
                        'callback' => 'version_update',
                    ),
                ),
            )
        )
    ),
    'dataset' => 'market/ProductorderModel::get_arrange_porder_info',
    'queryBy' => 'searchForm',
    'idField' => 'pro_num',
    //'RowNumber'=>true,
   // 'CheckSelection' => true,
));
?>
<script>
    /**
     *系统部署
     * @param index
     * @param row
     */
    function sys_arrange(index, row) {
        if (row.pro_st_id == '1') {
            var url = '?app_act=market/productorder/exclusive_view&app_scene=add&pro_num=' + row.pro_num;
            openPage(window.btoa(url), url, '独享模式');
        } else {
            var url = '?app_act=market/productorder/share_view&app_scene=add&pro_num=' + row.pro_num;
            openPage(window.btoa(url), url, '共享模式');
        }
    }

    /**
     *初始化账号
     * @param index
     * @param row
     * @constructor
     */
    function Initialization_account(index, row) {
        var url = '?app_act=market/productorder/initialization_account&app_scene=add&pro_num=' + row.pro_num + '&kh_id=' + row.pro_kh_id;
        openPage(window.btoa(url), url, '初始化账号');
        //  window.open(url);
    }

    /**
     * 版本升级
     * @param index
     * @param row
     */
    function version_update(index, row) {
        if (row.pro_st_id == '1') {
            var url = '?app_act=market/productorder/exclusive_ver_update&app_scene=edit&pro_num=' + row.pro_num + '&kh_id=' + row.pro_kh_id;
            openPage(window.btoa(url), url, '版本切换');
        } else {
            var url = '?app_act=market/productorder/version_update&app_scene=edit&pro_num=' + row.pro_num + '&kh_id=' + row.pro_kh_id;
            openPage(window.btoa(url), url, '版本切换');
        }
        //  window.open(url);
    }
</script>
