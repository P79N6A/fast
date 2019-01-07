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
    'osp_user_id_p' => array('osp_user', 'user_id', 'user_name', 'user_code'), //用户机构组合显示
    'osp_org_id' => array('osp_organization', 'org_id', 'org_name'), //机构上级机构组合显示
    'osp_post' => array('osp_post', 'post_id', 'post_name'), //岗位显示
    'org_id' => array('osp_organization', 'org_id', 'org_name', 'org_code'), //机构名称关联
);
