<?php
return array(
        //基础档案-产品销售渠道-新建渠道字段验证。
        'add_channel'=>array(
                        array('channel_name','require'),
                        array('channel_type','require'),
                        array('channel_mode','require'),
        ),
        //基础数据-云主机
        'add_hostinfo'=>array(
                        array('ali_type','require'),
                        array('ali_server_model','require'),
                        array('ali_outip','require'),
                        array('ali_server_use','require'),
                        array('ali_root','require'),
                        array('ali_starttime','require'),
                        array('ali_endtime','require'),
                        array('ali_cost_price','require'),
                        array('ali_sales_price','require'),
                        array('ali_share_type','require'),
        ),
       //基础数据-云主机
        'add_rdsinfo'=>array(
                        array('rds_dbtype','require'),
                        array('rds_server_model','require'),
                        array('rds_link','require'),
                        array('rds_user','require'),
                        array('rds_pass','require'),
                        array('rds_dbname','require'),
                        array('rds_server_use','require'),
                        array('rds_starttime','require'),
                        array('rds_endtime','require'),
                        array('rds_cost_price','require'),
                        array('rds_sales_price','require'),
                        array('ali_share_type','require'),

        ),
        //基础数据-云供应商
        'add_cloud'=>array(
                        array('cd_name','require'),
                        array('cd_official','require'),
                       

        ),

    //系统部署，独享模式
    'exclusive_add' => array(
        array('ali_outip', 'require'),
        array('rds_id', 'require'),
        array('rem_db_name', 'require'),
    ),

    //系统部署，共享模式
    'share_add' => array(
        array('rds_id', 'require'),
        array('time_ip', 'require'),
        array('api_ip', 'require'),
        array('rem_db_name', 'require'),
    ),
    
      //系统初始化
    'pro_init' => array(
        array('user_code', 'require'),
        array('password', 'require'),
        array('re_password', 'require'),
        array('user_name', 'require'),
    ),

    //独享版本更新
    'exclusive_update' => array(
        array('rds_id', 'require'),
        array('time_ip', 'require'),
    ),
    //共享版本更新
    'share_update' => array(
        array('rds_id', 'require'),
        array('time_ip', 'require'),
        array('api_ip', 'require'),
    ),

);