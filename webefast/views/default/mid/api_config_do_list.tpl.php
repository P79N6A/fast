<?php
$link_arr = array();
$butten = array();

 if(!empty($response['service_data'])){
    $link_arr[] = array('url' => 'mid/mid/config_detail&app_scene=add', 'title' => '新增配置', 'is_pop' => false, 'pop_size' => '900,600');
 }

$butten = array('id' => 'edit', 'title' => '编辑', 'act' => 'mid/mid/config_detail&app_scene=edit', 'show_name' => '编辑');

render_control('PageHead', 'head1', array('title' => '第三方接口配置列表',
    'links' => $link_arr,
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => '配置名称',
            'title' => '',
            'type' => 'input',
            'id' => 'api_name',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '200',
                'align' => '',
                'buttons' => array(
                    $butten,
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
                ),
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '配置名称',
                'field' => 'api_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '接口代码',
                'field' => 'api_product',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '对接方式',
                'field' => 'erp_type_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'mid/MidApiConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));
?>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('mid/api_config/del');
?>', data: {id: row.id,mid_code: row.mid_code},
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





    $(function () {
        $(".control-label").css("width", "110px");
    });
    
     parent.reload_parent_page = function(){
        tableStore.load();
    }
</script>

<?php  if(empty($response['service_data'])):?>
<div class="row" style="color: red;padding-top: 100px; font-size: 24px;">
    请购买相关增值服务才可以使用！
</div>

<?php endif; ?>

