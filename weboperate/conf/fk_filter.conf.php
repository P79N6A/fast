<?php
/**
 * 配置格式：
 * -------------------------------------------------------------------------------------
 * 	 外键	=>  ( 表名	 |  主键	   |  名称字段 )
 * -------------------------------------------------------------------------------------
 * ***[名称(字段)]是一般对外显示给人看的，比如颜色表(color_id,color_code,color_name...)，那么名称字段就是color_name
 *
 */
return array(
	'area'			=> array('base_area', 			'id', 			'name'), //大区
      //  'osp_user_id'           => array('vt_user', 			'user_id', 		'user_name'), //用户机构组合显示
        'osp_user_id_p'         => array('osp_user', 			'user_id', 		'user_name','user_code'), //用户机构组合显示
        'osp_org_id'            => array('vt_org', 			'org_id', 		'org_name'), //机构上级机构组合显示
        'osp_post'              => array('osp_post', 			'post_id', 		'post_name'), //岗位显示
        'osp_chanpin'           => array('osp_chanpin', 		'cp_id', 		'cp_name'), //产品显示
        'osp_kh'                => array('osp_kehu',                    'kh_id',                'kh_name','kh_id'), //客户显示
        'osp_val'               => array('osp_valueserver',             'id',                  'value_name'), //增值服务
        'org_id'                => array('osp_organization',             'org_id',              'org_name','org_code'), //机构名称关联
        'org_channel'           => array('osp_sell_channel',             'channel_id',          'channel_name','channel_id'), //销售渠道关联
        'osp_chanpin_version'   => array('osp_chanpin_version',          'pv_id',                'pv_name','pv_code'), //产品版本
        'osp_pt_type'           => array('osp_platform',                 'pt_id',                'pt_name'), //关联的店铺平台类型
        'osp_product_module'    => array('osp_chanpin_module',          'pm_id',                'pm_name','pm_id'), //产品模块
        'osp_cloud_server'      => array('osp_cloud',                   'cd_id',               'cd_name'), //关联的与服务商
        'osp_cloud_type'        => array('osp_cloud_module',              'cm_id',                'cm_host_type'), //关联的云主机配置
        'osp_cloud_db'          => array('osp_cloud_module',              'cm_id',                  'cm_db_type'), //关联的云数据库配置
        'market_plan'           => array('osp_market_strategytype',        'st_id',                  'st_name'), //关联的云数据库配置
        'client_shop'           => array('osp_shangdian',                  'sd_id',                  'sd_name','sd_id'), //关联店铺名称
        'osp_rdsinfo'           => array('osp_aliyun_rds',                 'rds_id',                 'rds_link'),//关联RDS信息
        'osp_rdskey'            => array('osp_rds',                      'rds_id',                 'app_key'),//关联产品应用KEY
        'plan_price'           => array('osp_plan_price',                 'price_id',               'price_name'),//报价方案
        'osp_valueserver_cat'  => array('osp_valueserver_category',       'vc_id',               'vc_name','vc_code'),//增值服务类别
        'osp_valueserver'      => array('osp_valueserver',                'value_id',            'value_name','value_code'),//增值服务
         'osp_hostinfo'        => array('osp_aliyun_host',                 'host_id',                 'ali_outip'),//关联RDS信息
         'osp_pdt_bh'          => array('osp_chanpin_version',              'pv_bh',                'pv_name','pv_bh',), //版本编号
         'osp_pdt_id'          => array('osp_chanpin_version',              'pv_id',                'pv_name','pv_bh',), //版本编号


    
    );