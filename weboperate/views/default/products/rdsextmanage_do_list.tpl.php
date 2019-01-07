<?php
render_control('PageHead', 'head1', array('title' => '产品RDS扩展管理',
    'links' => array(
        array('js' => "onecreatedb()", 'title' => '一键生成数据库', 'type' => 'js'),
        array('url' => 'products/rdsextmanage/do_importrds_list', 'title' => '导入RDS', 'is_pop' => true, 'pop_size' => '850,600'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'rem_cp_id',
            'data' => ds_get_select('chanpin', 1)
        ),
                array(
            'label' => 'RDS信息',
            'title' => 'RDS信息',
            'type' => 'input',
            'id' => 'rds_info',
        ),
    )
));
?>
<ul class="toolbar" id="btn_toolbar" style="margin-top: 10px;">
    <li><button class="button button-primary btn_createdb">批量生成数据库</button></li>
    <li><button class="button button-primary btn_updatesource">数据更新</button></li>
</ul>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS信息',
                'field' => 'rem_rds_id_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'rds到期时间',
                'field' => 'rem_rds_endtime',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '绑定产品',
                'field' => 'rem_cp_id_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '绑定产品应用KEY',
                'field' => 'rem_bindcpkey_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '绑定客户',
                'field' => 'rem_is_bindkh',
                'width' => '80',
                'align' => '',
                ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数据库数量',
                'field' => 'rem_dbnum',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '空闲数据库数量',
                'field' => 'rem_dbunnum',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '数据库管理',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'manage', 'title' => '管理',
                        'act' => 'pop:products/rdsextmanage/do_rdsdb_list', 'pop_size' => '700,500', 'show_name' => '当前RDS信息'),
                    array('id' => 'add_rds', 'title' => '添加数据库',
                        'act' => 'pop:products/rdsextmanage/do_add_rds_db', 'pop_size' => '700,500', 'show_name' => '添加数据库'),
                    array('id' => 'sync', 'title' => '主配置同步RDS', 'callback' => 'sync_data', 'confirm' => '确认同步数据吗？'),
                ),
            )
        )
    ),
    'dataset' => 'products/RdsextmanageModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'rem_rds_id',
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>
<script type="text/javascript">
    $(function(){
        $('#rem_cp_id option[value="21"]').attr('selected',true);
        
    });
    
    
    //批量生成数据库
    $(".btn_createdb").click(function() {
        var itemlist = tableGrid.getSelection();
        if (itemlist.length != 0) {
            //1.首先检测选择的RDS是否为同一个产品使用
            var cpstate = repeatArray(itemlist, 'rem_cp_id');
            if (cpstate == false) {
                BUI.Message.Alert('请选择相同产品的RDS', 'error');
            } else {
                BUI.Message.Confirm("确认生成数据库", function() {
                    //2.选择生成的数据库版本
                    PageHead_show_dialog(encodeURI('?app_act=products/rdsextmanage/do_batch_createdb&app_scene=add&app_show_mode=pop&ctype=1&data=' + JSON.stringify(itemlist)), '批量生成数据库', {w: 500, h: 400});
                }, 'question');

            }
        } else {
            BUI.Message.Alert('请选择RDS', 'warning');
        }
    });



    //一键生成数据库操作
    function onecreatedb() {
        BUI.Message.Confirm("确认生成数据库", function() {
            PageHead_show_dialog(encodeURI('?app_act=products/rdsextmanage/do_batch_createdb&app_scene=edit&app_show_mode=pop&ctype=0'), '一键生成数据库', {w: 500, h: 400});
        }, 'question');
    }

    //result是需要过滤重复元素的数组  
    //filterResult是过滤后的数组  
    //lookupName是元素对象的某个field  
    function repeatArray(result, lookupName) {
        var index = 0;
        for (var i = 0; i < result.length; i++) {
            for (var j = result.length - 1; j >= 0; j--) {
                if (result[j][lookupName] == result[i][lookupName]) {
                    index++;
                    if (index > 1) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function sync_data(index, row) {
        var d = {"rds_id": row.rem_rds_id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('products/rdsextmanage/sys_rds_data'); ?>', d, function(ret) {
            if (ret.status > 0) {
                BUI.Message.Alert('同步成功', 'info');
            } else {
                BUI.Message.Alert('同步失败', 'error');
            }

        }, "json");
    }


</script>
