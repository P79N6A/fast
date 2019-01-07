<style>
.bui-pagingbar{float:left;}
.div_title{padding:6px;font-weight:bold;}
</style>
<input type="hidden" id="role_code" value="<?php echo $response['role_code'];?>" />
<input type="hidden" id="role_id" value="<?php echo $request['role_id'];?>" />
<input type="hidden" id="profession_type" value="2" />
<?php render_control('PageHead', 'head1',
    array('title'=>'业务/数据权限['.$response['role']['role_code'].'-'.$response['role']['role_name'].']',
        'links'=>array(
            // array('url'=>'sys/role/do_list', 'title'=>'角色列表'),
        ),
    ));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
		'cmd' => array (
				'label' => '查询',
				'id' => 'btn-search' 
		),
		'fields' => array (
				array (
						'label' => '代码/名称',
						'title' => '代码/名称',
						'type' => 'input',
						'value' => $response['keyword'],
						'id' => 'keyword' 
				),
		) 
) );
?>
<!--
<div id="bar"></div>
-->

<ul class="nav-tabs oms_tabs">
    <li ><a href="#" onClick="do_page('do_list');">店铺</a></li>
    <li class="active"><a href="#" onClick="do_page('store_list');" >仓库</a></li>
    <li><a href="#" onClick="do_page('brand_list');" >品牌</a></li>
    <?php if ($response['version_no'] > 0): ?>
    <li><a href="#" onClick="do_page('supplier_list');" >供应商</a></li>
    <?php endif; ?>
    <li><a href="#" onClick="do_page('sensitive_list');" >敏感数据</a></li>
    <li><a href="#" onClick="do_page('manage_price');" >价格管控</a></li>
    <li><a href="#" onClick="do_page('custom_list');" >分销商</a></li>
</ul>
<div class="row-fluid msg" > <?php if($response['power'] == '1'){ ?> 已启用仓库权限，停用请点击这里<a href="#" onClick="do_set_active_shenhe('store_power','disable');"> <font color="#0000FF ">停用</font></a> <?php }else{ ?>  未启用仓库权限，只有启用后才允许配置，启用请猛击这里<a href="#" onClick="do_set_active_shenhe('store_power','enable');"> <font color="#0000FF ">启用</font></a> <?php } ?></div>
<div class="row-fluid" <?php if($response['power'] == '1'){ ?> style="display:block;" <?php }else{ ?>style="display:none;" <?php } ?>>
  <div class="span12">
  	<div class="div_title">可选仓库列表</div>
	<?php
	render_control ( 'DataTable', 'DataTable2', array (
			'conf' => array (
					'list' => array (
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '仓库代码',
									'field' => 'relate_code',
									'width' => '100',
									'align' => '' 
							),
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '仓库名称',
									'field' => 'store_name',
									'width' => '100',
									'align' => '' 
							),
					) 
			),
			//'dataset' => array('sys/RoleProfessionModel::get_shop_list_noset', array($request['_id'])),
			'dataset' => 'sys/RoleProfessionModel::get_store_list_noset',
			'queryBy' => 'searchForm',
			'idField' => 'store_id',
			'params' => array('filter' => array('role_code' => isset($request['role_code'])?$request['role_code']:'','keyword' => isset($response['keyword'])?$response['keyword']:'')),
			'CheckSelection'=>true,
	) );
	echo "<div id='div_pgbar3'></div>";
	?>
  </div>

  <div class="span1">
  	<a href="javascript:role_add();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0114;</a>
   	<a href="javascript:role_remove();" class="iconfont" style="font-size:30px;margin-top:40px">&#xf0112;</a> 	
  </div>

  <div class="span11">
  	<div class="div_title">已选仓库列表</div>  
	<?php
	render_control ( 'DataTable', 'DataTable3', array (
			'conf' => array (
					'list' => array (
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '仓库代码',
									'field' => 'relate_code',
									'width' => '100',
									'align' => '' 
							),
							array (
									'type' => 'text',
									'show' => 1,
									'title' => '仓库名称',
									'field' => 'store_name',
									'width' => '100',
									'align' => '' 
							),
					) 
			),
			'dataset' => 'sys/RoleProfessionModel::get_store_list',
			'idField' => 'sys_role_profession_id',
			'params' => array('filter' => array('role_code' => isset($request['role_code'])?$request['role_code']:'')),
			'CheckSelection'=>true,			
	) );
	echo "<div id='div_pgbar6'></div>";	
	?>
  </div>
</div>


<?php echo load_js('role_profession.js')?>
