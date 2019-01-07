<?php
/**
 * 常用下拉列表列表所需的数据源配置
 * 如果指定了table，则使用改值作为table，否者使用键名作为表名
 */

//'gonghuoshang'=>array('from'=>'model', 'fields'=>'Id,ghsdm')
$_select_datasource_cfg = array(
			'post' => array('table'=>'osp_post','fields'=>'post_id,post_name',), //岗位
                        'chanpin' => array('table'=>'osp_chanpin','fields'=>'cp_id,cp_name',), //产品
                        'kehu' => array('table'=>'osp_kehu','fields'=>'kh_id,kh_name',), //客户
                        'shangdian'=>array('table'=>'osp_shangdian','fields'=>'sd_id,sd_name',), //店铺
                        'valser' => array('table'=>'osp_valueserver','fields'=>'value_id,value_name',), //增值服务
                        'shop_platform' => array('table'=>'osp_platform','fields'=>'pt_id,pt_name',), //店铺平台类型
                       'users' => array('table'=>'osp_user','fields'=>'user_id,user_name',), //页面详情里面的用户id关联出来的用户名称
                       'issue_chanpin_version'=> array('table'=>'osp_chanpin_version','fields'=>'pv_id,pv_name'), //产品问题提单关联的产品版本。 
                       'host_cloud'=> array('table'=>'osp_cloud','fields'=>'cd_id,cd_name'), //基础数据，云主机关联的云服务商
                       'host_model'=> array('table'=>'osp_cloud_module','fields'=>'cm_id,cm_host_type'), //基础数据，云主机关联的云服务配置
                       'db_model'=> array('table'=>'osp_cloud_module','fields'=>'cm_id,cm_db_type'), //基础数据，云数据库关联的云服务配置
                       'market' => array('table'=>'osp_market_strategytype','fields'=>'st_id,st_name',), //营销类型
                       'platformshop_type' => array('table'=>'osp_platform_detail','fields'=>'pd_id,pd_shop_type',), //平台店铺类型
                       'valueserver_cat' => array('table'=>'osp_valueserver_category','fields'=>'vc_id,vc_name',), //增值服务类别


    
    
    
    );               
return $_select_datasource_cfg;