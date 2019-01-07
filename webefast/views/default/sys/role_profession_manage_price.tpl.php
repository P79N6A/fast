<style>
    .bui-pagingbar{float:left;}
    .div_title{padding:6px;font-weight:bold;}
    .table_panel1{
        width:90%;
        margin-bottom:5px;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 5px;
        text-align: left;
    }
    .status_btn{ border:1px solid #efefef; background:#FFF; color:#666; margin-right:2px; border-radius:3px;}
</style>
<input type="hidden" id="role_code" value="<?php echo $response['role_code']; ?>" />
<input type="hidden" id="role_id" value="<?php echo $request['role_id'];?>" />
<?php render_control('PageHead', 'head1',
    array('title'=>'业务/数据权限['.$response['role']['role_code'].'-'.$response['role']['role_name'].']',
        'links'=>array(
            // array('url'=>'sys/role/do_list', 'title'=>'角色列表'),
        ),
    ));?>
<ul class="nav-tabs oms_tabs">
    <li ><a href="#" onClick="do_page('do_list');">店铺</a></li>
    <li><a href="#" onClick="do_page('store_list');" >仓库</a></li>
    <li><a href="#" onClick="do_page('brand_list');" >品牌</a></li>
    <?php if ($response['version_no'] > 0): ?>
        <li><a href="#" onClick="do_page('supplier_list');" >供应商</a></li>
    <?php endif; ?>
    <li><a href="#" onClick="do_page('sensitive_list');" >敏感数据</a></li>
    <li class="active"><a href="#" onClick="do_page('manage_price');" >价格管控</a></li>
    <li><a href="#" onClick="do_page('custom_list');" >分销商</a></li>
</ul>

<div class="row-fluid msg" > 
    <?php if ($response['power'] == '1') { ?> 已启用价格管控权限，停用请点击这里<a href="#" onClick="do_set_active_shenhe('manage_price', 'disable');"> <font color="#0000FF ">停用</font></a> <?php } else { ?>  未启用 价格管控 权限，只有启用后才允许配置，启用请猛击这里<a href="#" onClick="do_set_active_shenhe('manage_price', 'enable');"> <font color="#0000FF ">启用</font></a>
    <?php } ?>
</div>
<div class="row-fluid" <?php if ($response['power'] == '1') { ?> style="display:block;" <?php } else { ?>style="display:none;" <?php } ?>>
    <div class="panel">
        <div class="panel-body">
            <div class="row" >
                <div class= 'detail'>
                    <table class='table_panel1' >
                        <tr>
                            <td> 价格种类</td> <td> 操作</td> <td> 说明</td>
                        </tr>

                        <?php foreach ($response['role_list'] as $k2 => $v2) { ?>
                            <tr>
                                <td>
                                    <?php echo $v2['manage_price_name'] ?>
                                </td>
                                <td>
                                    <?php echo $v2['status_html']; ?>
                                </td>
                                <td> <?php echo $v2['desc'] ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div> 
    </div>
</div>
<script type="text/javascript">
    var role_code = "<?php echo $response['role_code']; ?>";
    function changeType(manage_code, type) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/role_profession/manage_update_status'); ?>',
            data: {manage_code: manage_code, type: type, role_code: role_code},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    //tableStore.load();
                    window.location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_set_active_shenhe(param_code, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '?app_act=sys/role_profession/update_active',
            data: {param_code: param_code, type: active},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    window.location.reload();
                    //tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_page(param) {
        location.href = "?app_act=sys/role_profession/" + param + "&role_code=" + $("#role_code").val()+ "&role_id="+$("#role_id").val()+ "&keyword=";
    }
</script>


