<?php
$register = load_model('sys/PrivilegeModel')->check_priv('base/custom/register');
$add = load_model('sys/PrivilegeModel')->check_priv('base/custom/detail&app_scene=add');
$links = array();
/*if($register == TRUE){
    $links[] = array('type' => 'js', 'js' => 'custom_register()', 'title' => '分销商注册', 'is_pop' => false, 'pop_size' => '500,400');
}
if($add == TRUE) {
    $links[] = array('url' => 'base/custom/detail&app_scene=add', 'title' => '添加分销商', 'is_pop' => true, 'pop_size' => '600,500');
}

render_control('PageHead', 'head1', array('title' => '分销商',
    'links' => $links,
    'ref_table' => 'table'
));*/
if($add == TRUE) {
?>
    <div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
        <span class="page-title"><h2>分销商列表</h2></span>
        <span class="page-link">
            <?php if($response['service_custom'] == TRUE) { ?> 
                <span class="action-link">
                    <a href="javascript:custom_register()" class="button button-primary">
                        分销商注册
                    </a>
                </span>
            <?php } ?>
                <span class="action-link">
                    <a class="button button-primary" href="javascript:openPage('P2FwcF9hY3Q9cHJtL2dvb2RzL2RldGFpbCZhY3Rpb249ZG9fYWRk','?app_act=base/custom/detail&app_scene=add','添加分销商')">
                        添加分销商
                    </a>
                </span>
                <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
            </span>
    </div>
    <div class="clear" style="margin-top: 40px; "></div>
<?php } ?>
<?php
$keyword_type = array();
$keyword_type['custom_code'] = '分销商编码';
$keyword_type['custom_name'] = '分销商名称';
$keyword_type['contact_person'] = '联系人';
$keyword_type['mobile'] = '手机号';
$keyword_type = array_from_dict($keyword_type);
$fields = array(
    array(
        'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
        'type' => 'input',
        'title' => '',
        'data' => $keyword_type,
        'id' => 'keyword',
    ),
    array(
        'label' => '分销商类型',
        'type' => 'select',
        'id' => 'custom_type',
        'data' => array(
            array('all', '全部'), array('pt_fx', '普通分销'), array('tb_fx', '淘宝分销'),
        ),
    ),
    array(
        'label' => '是否启用',
        'type' => 'select',
        'id' => 'is_effective',
        'data' => array(
            array('all', '全部'), array('0', '未启用'), array('1', '已启用'),
        ),
    ),
);
if($response['service_custom'] == TRUE) {
    $fields[] = 
    array(
        'label' => '分销商分类',
        'title' => '',
        'type' => 'select_multi',
        'id' => 'custom_grade',
        'data' => load_model('base/CustomGradesModel')->get_all_grades(2),
    );
    $fields[] =
    array(
        'label' => '店铺',
        'type' => 'select_multi',
        'id' => 'shop_code',
        'data' => load_model('base/ShopModel')->get_purview_ptfx_shop(),
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => $fields,
));
?>

<?php
$lists = array(
    array(
        'type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '200',
        'align' => '',
        'buttons' => array(
            array(
                'id' => 'edit', 'title' => '编辑', 'priv' => 'base/custom/detail&app_scene=edit',
                'callback' => 'do_edit',
                'show_name' => '编辑',
                'show_cond' => 'obj.is_buildin != 1'
            ),
            array(
                'id' => 'delete',
                'title' => '删除',
                'callback' => 'do_delete',
                'confirm' => '确认要删除此信息吗？',
                'priv' => 'base/custom/do_delete',
                'show_cond' => 'obj.is_effective != 1',
            ),
            array(
                'id' => 'is_effective_ok',
                'title' => '启用',
                'callback' => 'is_effective_ok',
                'show_cond' => 'obj.is_effective != 1',
            ),
            array(
                'id' => 'is_effective_no',
                'title' => '停用',
                'callback' => 'is_effective_no',
                'show_cond' => 'obj.is_effective == 1',
            ),
            array(
                'id' => 'address_do_list',
                'title' => '地址维护',
                'callback' => 'address_do_list',
            ),
//                    array('id' => 'add_user_code', 'title' => '登录设置','priv' => 'base/custom/add_user&app_scene=add',
//                        'act' => 'pop:base/custom/add_user&app_scene=add&custom_code={custom_code}',
//                        'pop_size' => '600,500',
//                        'show_name' => '编辑',
//                        'show_cond' => 'obj.user_code == null ||obj.user_code == "" '
//                    ),
            array('id' => 'reset_password', 'title' => '重设密码', 'callback' => 'do_reset_pwd', 'priv' => 'base/custom/reset_pwd',
                'show_cond' => 'obj.user_code != null && obj.user_code != "" ', 'confirm' => '确认要重置<b>[{custom_name}]</b>的登录密码吗？'),
            array(
                'id' => 'express_money_detail',
                'title' => '运费按快递区分',
                'callback' => 'express_money_detail',
                'show_cond' => 'obj.settlement_method == 0',
            ),
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '启用状态',
        'field' => 'is_effective',
        'width' => '80',
        'format_js' => array('type' => 'map_checked')
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分销商编号',
        'field' => 'custom_code',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分销商名称',
        'field' => 'custom_name',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '分销商类型',
        'field' => 'custom_type_name',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '联系人',
        'field' => 'contact_person',
        'width' => '80',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '联系人手机',
        'field' => 'mobile',
        'width' => '110',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '联系人地址',
        'field' => 'address_str',
        'width' => '250',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '注册时间',
        'field' => 'create_date',
        'width' => '100',
        'align' => ''
    ),
        /* array(
          'type' => 'text',
          'show' => 1,
          'title' => '账户余额',
          'field' => 'yck_account_capital',
          'width' => '100',
          'align' => ''
          ), */
);
if($response['service_custom'] == TRUE) {
    $lists[] = array(
        'type' => 'text',
        'show' => 1,
        'title' => '关联店铺',
        'field' => 'shop_name_str',
        'width' => '120',
        'align' => ''
    );
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $lists,
    ),
    'dataset' => 'base/CustomModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'custom_id',
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
    'params' => array(array('custom_code' => $response['custom_code'],'list_type' => 'custom_do_list')),
    'export' => array('id' => 'exprot_list', 'conf' => 'custom_do_list', 'name' => '分销商列表', 'export_type'=>'file'),
));
?>
<script type="text/javascript">
    function do_edit(_index, row) {
        openPage('<?php echo base64_encode('?app_act=base/custom/detail&app_scene=edit&_id=') ?>' + row.custom_id, '?app_act=base/custom/detail&app_scene=edit&_id=' + row.custom_id, '编辑分销商');
        return;
    }
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/custom/do_delete'); ?>', data: {custom_id: row.custom_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
    function do_reset_pwd (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json', 
            url: '<?php echo get_app_url('base/custom/reset_pwd'); ?>', data: {user_code: row.user_code}, 
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                BUI.Message.Alert('新密码：'+ret.data, type);
                } else {
                    if(ret.status == -10){
                        BUI.Message.Alert(ret.message,function(){
                        window.open(ret.data);
                        }, type);
                    }else{
                        BUI.Message.Alert(ret.message, type);
                    }

                }
            }
        });
    }
    
    function custom_register(){
        var kh_id = "<?php echo $response['kh_id'];?>";
        var url = "?app_act=base/custom/register&kh_id="+kh_id;
        window.open(url);
    }
    parent._action = function() {
        tableStore.load();
    };
    //启用
    function is_effective_ok(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/custom/is_effective'); ?>', 
            data: {custom_id: row.custom_id,is_effective:1},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('启用成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //停用
    function is_effective_no(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('base/custom/is_effective'); ?>', 
            data: {custom_id: row.custom_id,is_effective:0},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('停用成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    //地址维护
    function address_do_list(_index, row) {
        openPage('<?php echo base64_encode('?app_act=base/custom/address_do_list&app_scene=edit&_id=') ?>' + row.custom_id, '?app_act=base/custom/address_do_list&app_scene=edit&_id=' + row.custom_id, '地址维护');
        return;
    }
    //固定运费按快递区分
    function express_money_detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=base/custom/express_money_detail&_id=') ?>' + row.custom_id, '?app_act=base/custom/express_money_detail&_id=' + row.custom_id, '运费按快递区分');
        return;
    }
</script>




