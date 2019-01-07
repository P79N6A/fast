<?php
return array (
		/**
		 * 不要进行clean_xss处理的请求
		 * 当global_clean_xss=true时生效
		 */
		'clean_xss_opossite' => array (
				/**
				 * 按控制器方法配置
				 * 形如：sys/user/add
				 */
				'methods' => array (
                                    'ctl/ctlext/save_custom_table_field',
                                    'sys/flash_templates/save',
                                    'wms/wmsapi/ydwms_api',
                                    'sys/print_templates/edit_express_action',
                                    'sys/weipinhuijit_box_print/edit_express_action',
                                    'openapi/router',
									'openapi/qimenapi',
                                    'erp/erpapi/qimen_api',
                                    ),

		) 
)
;


