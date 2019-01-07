<?php

render_control('PageHead', 'head1', array('title' => '快递分布',
    'links' => array(),
    'ref_table' => 'table'
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
            'label' => '付款日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_first_time'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_last_time', 'remark' => ''),
            )
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
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
                'title' => '快递代码',
                'field' => 'express_code',
                'width' => '250',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递名称',
                'field' => 'express_name',
                'width' => '250',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单数',
                'field' => 'order_num',
                'width' => '250',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所占比例',
                'field' => 'proportion',
                'width' => '250',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'op/ploy/ExpressPloyCensusModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'init' => 'nodata',
));
?>


