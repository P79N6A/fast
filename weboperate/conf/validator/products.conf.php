<?php
return array(
        //产品新建编辑
	'products_edit'=>array(
                        array('cp_code', 'require'), 
                        array('cp_name', 'require'),  
		),
        'pversion_edit'=>array(
                        array('pv_code', 'require'), 
                        array('pv_name', 'require'), 
                        array('pv_bh', 'require'), 
                        array('pv_rq', 'require'),  
                        array('pv_path','require'),
		),
        'add_vhost'=>array(
                        array('vem_vm_id', 'require'), 
                        array('vem_product_version', 'require'), 
                        array('vem_cp_version_ip', 'require'), 
                        array('vem_cp_id', 'require'),  
		),
        'add_ptpatch'=>array(
                        array('cp_id', 'require'), 
                        array('version_no', 'require'), 
                        array('version_patch', 'require'), 
                        array('version_file_path', 'require'),  
                        array('upgrade_patch','require'),
		),
);