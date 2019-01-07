<?php render_control('PageHead', 'head1',
array('title'=>'产品主机管理',
	'links'=>array(
            array('url'=>'products/vmanage/do_importhost_list', 'title'=>'导入主机', 'is_pop'=>true, 'pop_size'=>'850,600'),
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
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'rem_cp_id',
            'data'=>ds_get_select('chanpin',1)
        ),
    ) 
) );
?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '主机IP',
                'field' => 'vm_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '主机到期时间',
                'field' => 'vm_endtime',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '绑定产品',
                'field' => 'vm_cp_id_name',
                'width' => '200',
                'align' => ''
            ),
//            array (
//                'type' => 'button',
//                'show' => 1,
//                'title' => '主机管理',
//                'field' => '_operate',
//                'width' => '100',
//                'align' => '',
//                'buttons' => array (
//                	array('id'=>'manage', 'title' => '管理', 
//                		'act'=>'pop:products/rdsextmanage/do_rdsdb_list','pop_size'=>'700,500','show_name'=>'当前主机信息'),
//                ),
//            )
        ) 
    ),
    'dataset' => 'products/VmanageModel::get_vm_main',
    'queryBy' => 'searchForm',
    'idField' => 'vm_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
    //批量生成数据库
    $(".btn_createdb").click(function(){
        var itemlist=tableGrid.getSelection();
        if(itemlist.length!=0){
            //1.首先检测选择的RDS是否为同一个产品使用
            var cpstate=repeatArray(itemlist,'rem_cp_id');
            if(cpstate == false){
                BUI.Message.Alert('请选择相同产品的RDS','error');
            }else{
                BUI.Message.Confirm("确认生成数据库", function(){
                    //2.选择生成的数据库版本
                    PageHead_show_dialog(encodeURI('?app_act=products/rdsextmanage/do_batch_createdb&app_scene=add&app_show_mode=pop&ctype=1&data='+JSON.stringify(itemlist)), '批量生成数据库', {w:500,h:400});
                },'question'); 
                
            }
        }else{
            BUI.Message.Alert('请选择RDS','warning');
        }
    });
    
    //一键生成数据库操作
    function onecreatedb(){
        BUI.Message.Confirm("确认生成数据库", function(){
            PageHead_show_dialog(encodeURI('?app_act=products/rdsextmanage/do_batch_createdb&app_scene=edit&app_show_mode=pop&ctype=0'), '一键生成数据库', {w:500,h:400});
        },'question'); 
    }
    
    //result是需要过滤重复元素的数组  
    //filterResult是过滤后的数组  
    //lookupName是元素对象的某个field  
    function repeatArray(result,lookupName){
        var index=0;
        for (var i = 0; i < result.length; i++) {
            for (var j = result.length-1; j >=0; j--) {
                if (result[j][lookupName] == result[i][lookupName]) {
                    index++;
                    if(index>1){
                        return false;
                    }
                }
            }
        }
        return true;
    }
</script>
