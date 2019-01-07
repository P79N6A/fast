<?php
render_control('PageHead', 'head1', array('title' => "{$response['record_type_property_name']}",
    'links' => array(
        array('url' => "base/record_type/detail&app_scene=add", 'title' => '新增业务类型', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_type_name'] = '名称';
$keyword_type['record_type_code'] = '代码';
$keyword_type['remark'] = '备注';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type','type' => 'select','data' => $keyword_type),
            'type' => 'input',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '单据类型',
            'type' => 'select',
            'id' => 'record_type_property',
            'data' => array(array('','请选择'),array('0', '采购进货'),array('1','采购退货'),array('2','批发发货'),array('3','批发退货'),array('8','库存调整')),
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
			array(
					'type' => 'button',
					'show' => 1,
					'title' => '操作',
					'field' => '_operate',
					'width' => '150',
					'align' => '',
					'buttons' => array(
							array(
									'id' => 'edit',
									'title' => '编辑',
									'act' => 'pop:base/record_type/detail&app_scene=edit',
									'show_name' => '编辑',
									'show_cond' => 'obj.sys == 0',
							),
							array(
									'id' => 'delete',
									'title' => '删除',
									'callback' => 'do_delete',
									'confirm' => '确认要删除类型吗？',
									'show_cond' => 'obj.sys == 0',
							),
					)
			),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型代码',
                'field' => 'record_type_code',
                'width' => '100',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型名称',
                'field' => 'record_type_name',
                'width' => '100',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'record_type_property_name',
                'width' => '100',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'remark',
                'width' => '300',
                'align' => '',
                'format' => array('type' => 'truncate',
                    'value' => 20,
                )
            ),
           
        )
    ),
    'dataset' => 'base/RecordTypeModel::get_by_page',
    'queryBy' => 'searchForm',
    'params' => "",
    'idField' => 'record_type_id',
));
?>

<script type="text/javascript">
<!--
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/record_type/do_delete');?>', 
            data: {record_type_id: row.record_type_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
//-->
</script>
