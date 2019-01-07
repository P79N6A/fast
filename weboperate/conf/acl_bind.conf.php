<?php
/**
 * 权限项关联绑定配置
 * 
 * 背景：
 * 由于部分控制器方法不是菜单项或者按钮，但是这个控制器方法的权限应该是和某个操作是一样的。
 * 所以我们将某个控制器方法与操作的权限代码关联起来，这样在做权限检测时，会读取关联的操作权限代码来做控制
 * 
 * 配置数组说明：
 *      key：         操作的权限代码，如"sys/user/detail#scene=edit"
 *      value：     控制器方法标识 ，如"sys/user/do_edit"，如果有多个以逗号分隔（如"sys/user/do_edit,sys/user/do_edit_pwd"）
 * 
 * 为了减少配置，会将一些固定规则做自动关联：
 *      1、“path/class/do_{scene}”会自动和“path/class/detail#scene={scene}”，如“sys/user/do_edit”会自动和“sys/user/detail#scene=edit”关联
 */
$__acl_bind_cfg = array(
    // 操作权限代码                           =>  需要关联的控制器方法标识
        'sys/user/detail#scene=view'=>'sys/user/detail',           //用户列表查看权限,重置密码权限
        'sys/user/detail#scene=edit'=>'sys/user/do_edit',          //用户列表编辑权限
        'sys/user/role_list'=>'sys/user/user_remove_role,sys/user/user_add_role',
    //组织机构权限
        'sys/org/do_list'=>'sys/org/detail',
    //角色权限
        'sys/role/allot#scene=edit'=>'sys/role/update_allot',
    //产品信息操作权限
        'products/productinfo/detail#scene=view'=>'products/productinfo/detail',           //产品列表查看权限
        'products/productinfo/detail#scene=edit'=>'products/productinfo/product_edit#scene=edit',     //产品列表编辑权限
        'products/productinfo/detail#scene=add'=>'products/productinfo/product_add#scene=add',       //产品列表新建权限
    //客户中心-客户档案操作权限
        'clients/clientinfo/detail#scene=view'=>'clients/clientinfo/detail',           //客户列表查看权限
        'clients/clientinfo/detail#scene=edit'=>'clients/clientinfo/client_edit#scene=edit',     //客户档案编辑权限
        'clients/clientinfo/detail#scene=add'=>'clients/clientinfo/client_add#scene=add',       //客户新建权限
       
    //客户中心-店铺档案操作权限
        'clients/shopinfo/detail#scene=view'=>'clients/shopinfo/detail',           //店铺信息查看权限
        'clients/shopinfo/detail#scene=edit'=>'clients/shopinfo/shop_edit#scene=edit',     //店铺档案编辑权限
        'clients/shopinfo/detail#scene=add'=>'clients/shopinfo/shop_add#scene=add',       //店铺新建权限
    
     //客户中心-云主机档案操作权限
        'clients/aliinfo/detail#scene=view'=>'clients/aliinfo/detail',           //云主机信息查看权限
        'clients/aliinfo/detail#scene=edit'=>'clients/aliinfo/ali_edit#scene=edit',     //云主机档案编辑权限
        'clients/aliinfo/detail#scene=add'=>'clients/aliinfo/ali_add#scene=add',      
        //'clients/aliinfo/viewpass'=>'clients/aliinfo/ali_add#viewpass',      
        //'clients/aliinfo/change_pass'=>'clients/aliinfo/change_pass#change_pass',      
    //客户中心-云RDS档案操作权限
        'clients/alirds/detail#scene=edit'=>'clients/alirds/rds_edit#scene=edit',     //云rds档案编辑权限
        'clients/alirds/detail#scene=add'=>'clients/alirds/rds_add#scene=add',      
        //'clients/alirds/viewpass'=>'clients/alirds/ali_add#viewpass',      
        //'clients/alirds/change_pass'=>'clients/alirds/change_pass#change_pass',      
    //营销中心-增值服务列表操作权限
        'market/valueservice/detail#scene=view'=>'market/valueservice/detail',           //增值服务列表查看权限
        'market/valueservice/detail#scene=edit'=>'market/valueservice/valueserver_edit#scene=edit',     //增值服务编辑权限
        'market/valueservice/detail#scene=add'=>'market/valueservice/valueserver_add#scene=add',       //增值服务新建权限
     //营销中心-产品订购列表操作权限
        'market/productorder/detail#scene=view'=>'market/productorder/detail',           //产品订购查看权限
        'market/productorder/detail#scene=edit'=>'market/productorder/porders_edit#scene=edit',     //产品订购编辑权限
        'market/productorder/detail#scene=add'=>'market/productorder/porders_add#scene=add',       //产品订购新建权限
    //营销中心-增值订购列表操作权限
        'market/valueorder/detail#scene=view'=>'market/valueorder/detail',           //增值订购查看权限
        'market/valueorder/detail#scene=edit'=>'market/valueorder/valorders_edit#scene=edit',     //增值订购编辑权限
        'market/valueorder/detail#scene=add'=>'market/valueorder/valorders_add#scene=add',       //增值订购新建权限
    
    );

return $__acl_bind_cfg;