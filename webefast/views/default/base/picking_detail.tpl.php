<?php

render_control('PageHead', 'head1', array(
    'title' => isset($app['title']) ? $app['title'] : '编辑模板',
    'links' => array(
        'base/picking/do_list' => '拣货单模板列表'
    )
));
?>
<?php


render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array(
                'title' => '模板名称',
                'type' => 'input',
                'field' => 'picking_name'
            ),
            array(
                'title' => '是否启用',
                'type' => 'checkbox',
                'field' => 'ispublic',
                'desc' => '启用'
              
            ),
            
            array(
                'title' => '模板内容',
                'type' => 'textarea',
                'field' => 'picking_content',
                'width' =>600,
                'height' =>100,
                'desc' =>'（注：模板变量定义如下 {$user_id} - 会员登录名、{$user_name} - 会员昵称、{$order_sn} - 订单号、{$consignee} - 收货人、{$exmc} - 快递公司、{$invoice_no} - 快递单号、{$now_time} - 当前时间、{$goods_sns} - 商品货号串）'
            ),
            
            array(
                'title' => '描述',
                'type' => 'textarea',
                'field' => 'picking_desc',
                'width' =>600,
                'height' =>100
            ),
          
        ),
        'hidden_fields' => array(
            array(
                'field' => 'id'
            )
        )
    ),
    'buttons' => array(
        array(
            'label' => '提交',
            'type' => 'submit'
        ),
        array(
            'label' => '重置',
            'type' => 'reset'
        )
    ),
    'act_edit' => 'base/picking/do_edit', // edit,add,view
    'act_add' => 'base/picking/do_add',
    'data' => $response['data'],
    'rules' => array(
        array(
            'picking_name',
            'require'
        )
        
    )
));
?>