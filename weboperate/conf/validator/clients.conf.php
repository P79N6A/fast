<?php
return array(
        //客户中心-云主机档案。修改密码
        'change_pass'=>array(
                        array('oldpass','require'),
                        array('newpass','require'),
                        array('newpass2', 'require'),
                        array('newpass2','equalTo','value'=>'newpass'),
                        
                ),
        //客户中心-云RDS档案。修改密码
        'change_rds_pass'=>array(
                        array('rds_oldpass','require'),
                        array('rds_newpass','require'),
                        array('rds_newpass2','require'),
                        array('rds_newpass2','equalTo','value'=>'rds_newpass'),
                ),
        //新建客户字段验证。
        'add_clients'=>array(
                        array('kh_name','require'),
                        array('kh_code','require'),
        ),
        //新建店铺字段验证 add_hosts
        'add_shops'=>array(
                        array('sd_name','require'),
                        array('sd_code','require'),
                        array('sd_kh_id','require'),
        ),
    //新建主机字段验证 
        'add_hosts'=>array(
                        array('kh_id','require'),
                        array('ali_outip','require'),
                        array('ali_starttime','require'),
                        array('ali_endtime','require'),
                        array('ali_root','require'),
                        array('ali_type','require'),
                        array('ali_server_model','require'),
        ),
        //新建云数据库字段验证 
        'add_rds'=>array(
                        array('kh_id','require'),
                        array('rds_user','require'),
                        array('rds_pass','require'),
                        array('rds_link','require'),
                        array('rds_dbname','require'),
                        array('rds_starttime','require'),
                        array('rds_endtime','require'),
                        array('rds_dbtype','require'),
                        array('rds_server_model','require'),
        ),
    
    
    
);