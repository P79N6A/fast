<?php render_control('PageHead', 'head1',
    array('title' => '淘宝地址区域列表',
		    'links' => array(
		    		//array('url' => 'base/taobao_area/download_taobao&app_scene=add', 'title' => '淘宝地理信息下载', 'is_pop' => false, 'pop_size' => '500,300'),
		    		//array('url' => 'base/taobao_area/update_code&app_scene=add', 'title' => '省代码修改', 'is_pop' => false, 'pop_size' => '500,300'),
			    //array('url' => 'base/taobao_area/dl_area_type_5&app_scene=add', 'title' => '下载街道区域', 'is_pop' => true, 'pop_size' => '500,300'),

		    ),
        'ref_table' => 'table'
        ));

?>
<?php
render_control ('SearchForm', 'searchForm', array ('cmd' => array ('label' => '查询',
            'id' => 'btn-search'
            ),
        'fields' => array (
            array ('label' => '区域类型',
                'title' => '区域类型',
                'type' => 'select',
                'id' => 'type',
                'data' => array_from_dict(array('' => '请选择','1' => '国家' ,'2' => '省/自治区/直辖市', '3' => '地级市', '4' => '县/市(县级市)/区','5' => '街道'))
                ),
            array ('label' => '地理名称',
                'type' => 'input',
                'id' => 'name'
                ),
            array ('label' => '邮政编码',
                  'type' => 'input',
                  'id' => 'zip'
              ),
            ),
            'hidden_fields' =>array(array('field'=>'area_type','value'=>'')),
        ));

?>
<?php
render_control ('DataTable', 'table', array ('conf' => array ('list' => array (
                array ('type' => 'button',
                    'show' => 1,
                    'title' => '查看',
                    'field' => '_operate',
                    'width' => '150',
                    'align' => '',
                    'buttons' => array (
                        array('id' => 'child', 'title' => '查看下级',
                            'callback' => 'do_list_child','show_cond'=>'obj.has_next == 1'),
                        array('id' => 'parent', 'title' => '查看上级',
                            'callback' => 'do_list_parent','show_cond'=>'obj.has_parent == 1'),
                        ),
                    ),
				array ('type' => 'text',
                    'show' => 1,
                    'title' => '地理名称',
                    'field' => 'name',
                    'width' => '150',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '类型',
                    'field' => 'type_txt',
                    'width' => '150',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '行政区域编码',
                    'field' => 'id',
                    'width' => '150',
                    'align' => ''
                    ),
                array ('type' => 'text',
                    'show' => 1,
                    'title' => '邮政编码',
                    'field' => 'zip',
                    'width' => '100',
                    'align' => ''
                    ),
                
                )
            ),
        'dataset' => 'base/TaobaoAreaModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'id',
        ));

?>

<script type="text/javascript">
//tableStore.set('pageSize', 2);
//tableStore.load(); 
function do_delete(_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('base/TaobaoArea/do_delete');
?>', data: {id: row.id},
    success: function(ret) {
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

function do_list_child(_index, row){
	var obj = {"area_type":"child","area_id":row.id,"page":"1","type":"","name":""};
	obj.start = 1;
	tableStore.load(obj);
	page.jumpToPage(1);
}

function do_list_parent(_index, row){
	var obj = {"area_type":"parent","area_id":row.parent_id,"page":"1","type":"","name":""};
	obj.start = 1;
	tableStore.load(obj);
	page.jumpToPage(1);
}

//-->
</script>