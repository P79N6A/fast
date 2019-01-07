<?php

render_control('PageHead', 'head1', 
        array('title' => '支付方式',
                'ref_table' => 'table',
                    'links' => array(
                        array('url' => 'base/payment/add&app_scene=add&action=do_add', 'title' => '新增支付方式', 'is_pop' => true, 'pop_size' => '450,450'),
                    ),
        ));
?>
<?php

render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array('label' => '代码',
            'title' => '支付方式代码',
            'type' => 'input',
            'id' => 'pay_type_code'
        ),
        array('label' => '名称',
            'title' => '支付方式名称',
            'type' => 'input',
            'id' => 'pay_type_name'
        ),
    )
));
?>
<?php

render_control('DataTable', 'table', array('conf' => array('list' => array(
             array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'pop:base/payment/detail&app_scene=edit', 'show_name' => '编辑'),
                )),
			array('type' => 'text',
                'show' => 1,
                'title' => '支付方式代码',
                'field' => 'pay_type_code',
                'width' => '100',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '支付方式名称',
                'field' => 'pay_type_name',
                'width' => '100',
                'align' => ''
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '支付类型',
                'field' => 'pay_type',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'remark',
                'width' => '200',
                'align' => '',
                'format' => array('type' => 'truncate',
                    'value' => 20
                )
            ),
           
        )
    ),
    'dataset' => 'base/PaymentModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pay_type_id',
));
?>