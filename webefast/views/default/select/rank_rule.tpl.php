<style type="text/css">
    .well .control-group {
    padding-left: 1%;
    width: 50%;
}
</style>
<?php
render_control ('SearchForm', 'searchForm', array ('cmd' => array ('label' => '查询',
            'id' => 'btn-search'
            ),
          'fields' => array (
		        array (
		        		'label' => '名称',
		        		'type' => 'input',
		        		'id' => 'code_name'
		        ),
            
            )
        ));

?>
<?php

render_control ('DataTable', 'table', array ('conf' => array ('list' => array (

                        array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '活动名称',
                                        'field' => 'strategy_name',
                                        'width' => '130',
                                        'align' => '',
                        ),
                        array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '规则名称',
                                        'field' => 'name',
                                        'width' => '150',
                                        'align' => '',
                        ),
                        array(
                                        'type' => 'text',
                                        'show' => 1,
                                        'title' => '活动时间',
                                        'field' => 'active_time',
                                        'width' => '300',
                                        'align' => '',
                        ),
                )
            ),
        'dataset' => 'op/GiftStrategy2DetailModel::get_by_page_list',
        'queryBy' => 'searchForm',
        'idField' => 'op_gift_strategy_detail_id',
//        'init' => 'nodata',
        
        ));

?>

<?php echo_selectwindow_js($request, 'table', array('id'=>'customer_code', 'code'=>'customer_code', 'name'=>'customer_name')) ?>

<script type="text/javascript">


</script>
