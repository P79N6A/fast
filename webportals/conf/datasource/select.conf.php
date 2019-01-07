<?php

/**
 * 常用下拉列表列表所需的数据源配置
 * 如果指定了table，则使用改值作为table，否者使用键名作为表名
 */
//'gonghuoshang'=>array('from'=>'model', 'fields'=>'Id,ghsdm')
$_select_datasource_cfg = array(
    'post' => array('table' => 'osp_post', 'fields' => 'post_id,post_name',), //岗位
    'users' => array('table' => 'osp_user', 'fields' => 'user_id,user_name',), //页面详情里面的用户id关联出来的用户名称
);
return $_select_datasource_cfg;
