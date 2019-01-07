<?php render_control('PageHead', 'head1',
array('title'=>'导入主机',
	'links'=>array(
            
	),
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '云服务商',
            'type' => 'select',
            'id' => 'cloudtype',
            'data'=>ds_get_select('host_cloud',1)
        ),
        array (
            'label' => '到期时间',
            'type' => 'date',
            'id' => 'host_endtime' 
        ),
        array (
            'label' => '用途',
            'type' => 'select',
            'id' => 'server_use',
             'data'=>ds_get_select_by_field('serveruse')
        ),
    ) 
) );
?>
<div class="row">
    <div class="span18">
        <b>绑定产品</b>
        <select name="cpid" id="cpid" class="input-normal">
            <option value=''>请选择</option>
            <?php 
                $retchanpin=ds_get_select('chanpin');
                foreach ($retchanpin as $chanpin){
                    echo '<option value='.$chanpin['cp_id'].'>'.$chanpin['cp_name'].'</option>';
                }
            ?>
        </select>
        <b style="margin-left: 10px">平台</b>
        <select name="shop_platformid" id="shop_platformid" class="input-normal">
            <option value=''>请选择</option>
            <?php 
                $retplatform=ds_get_select('shop_platform');
                foreach ($retplatform as $platform){
                    echo '<option value='.$platform['pt_id'].'>'.$platform['pt_name'].'</option>';
                }
            ?>
        </select>
        <button style="margin-left: 10px" type="button" class="button button-info" value="导入" id="btnimport"><i class="icon-upload icon-white"></i> 导入</button>
    </div>
</div>
<hr/>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '主机IP',
                'field' => 'ali_outip',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '云服务商',
                'field' => 'ali_type_name',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '操作系统',
                'field' => 'ali_operate_system',
                'width' => '80',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('system_type'))
            ),
            
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'ali_starttime',
                'width' => '90',
                'align' => '' 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'ali_endtime',
                'width' => '90',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'products/VmanageModel::get_import_hostinfo',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5)),
    'idField' => 'host_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
    //初始化
    importinit();
    
    function importinit(){
        $("#btnimport").click(function() {
            importclick();
        });
    }
    
    //绑定导入事件
    function importclick(){
        if ($("#cpid").val() == "") {
            BUI.Message.Alert("先选择产品信息", "error");
            return;
        } 
        if ($("#shop_platformid").val() == "") {
            BUI.Message.Alert("先选择平台", "error");
            return;
        } 
        //导入操作
        var itemlist=tableGrid.getSelection();
        if(itemlist.length!=0){
            BUI.Message.Confirm("确认导入", function(){
                $.ajax({type: 'POST', dataType: 'json',
                     url: '<?php echo get_app_url('products/vmanage/do_importrds'); ?>',
                     data: {hostdata: JSON.stringify(itemlist),cpid:$("#cpid").val(),platformid:$("#shop_platformid").val()},
                     success: function(ret) {
                         var type = ret.status == 1 ? 'success' : 'error';
                         if (type == 'success') {
                             BUI.Message.Alert("导入成功", type);
                             tableStore.load();
                         } else {
                             BUI.Message.Alert(ret.message, type);
                         }
                     }
                 });  
            },'question');
        }else{
            BUI.Message.Alert('请选择导入的VM主机','warning');
        }
    }
</script>
