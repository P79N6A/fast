<?php render_control('PageHead', 'head1',
array('title'=>'补丁升级列表',
	'links'=>array(
            //array('url'=>'sys/user/detail&app_scene=add', 'title'=>'新增用户', 'is_pop'=>true, 'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'  //ref_table 表示是否刷新父页面
));?>
<?php

render_control ( 'SearchForm', 'searchForm', array (
   'buttons' =>array( 
   array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
           array(
        'label' => '导出失败SQL',
        'id' => 'exprot_list',
    ),
         ) ,
    'fields' => array (
            array (
            'label' => '绑定客户',
            'title' => '是否绑定客户',
            'type' => 'select',
            'id' => 'is_bindkh', 
            'data'=>ds_get_select_by_field('boolstatus'),
        ),
        array (
            'label' => '客户',
            'title' => '客户名/客户代码',
            'type' => 'input',
            'id' => 'kehu' 
        ),
        array (
            'label' => '版本编号',
            'title' => '版本编号',
            'type' => 'select',
            'id' => 'version_no',
            'data'=>$response['version_no']
        ),
        array (
            'label' => '补丁编号',
            'title' => '补丁编号',
            'type' => 'select',
            'id' => 'version_patch',
            'data'=>array(array('0'=>'','请选择'))
        ),
        array (
            'label' => '升级状态',
            'title' => '升级状态',
            'type' => 'select',
            'id' => 'is_upgrade',
            'data'=>array(array('','全部'),array('0','未升级'),array('1','正在升级'),array('2','升级成功'),array('3','升级失败'),)
        )
    ) 
) );
?>
<ul class="toolbar">
	<li><button class="button button-success" id="upgrade_but">批量升级</button></li><span style="color:red;">（注：一次只能升级1个补丁，升级客户未升级过的补丁）</span>
</ul>
<?php //        $select = " kh.kh_name,kh.kh_id,d.rem_db_version,d.rem_db_version_patch,r.start_time,r.end_time,r.status ";
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
                     array (
                'type' => 'text',
                'show' => 1,
                'title' => '数据库名',
                'field' => 'rem_db_name',
                'width' => '100',
                'align' => '' 
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名',
                'field' => 'kh_name',
                'width' => '100',
                'align' => '' 
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '版本',
                'field' => 'version_no',
                'width' => '100',
                'align' => '' 
            ),
               array (
                'type' => 'text',
                'show' => 1,
                'title' => '当前补丁',
                'field' => 'rem_db_version_patch',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '升级补丁',
                'field' => 'version_patch',
                'width' => '160',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'start_time',
                'width' => '160',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'end_time',
                'width' => '160',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '升级状态',
                'field' => 'status_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (
                	//array('id'=>'view', 'title' => '查看升级日志', 
                		//'act'=>'sys/user/detail&app_scene=view', 'show_name'=>'查看用户'),
                	//array('id'=>'edit', 'title' => '手动升级', 
                		//'act'=>'sys/user/detail&app_scene=edit', 'show_name'=>'编辑用户', 
                		//'show_cond'=>'obj.is_buildin != 1'),

                ),
            )
        ) 
    ),
    'dataset' => 'upgrade/VersionPatchModel::get_upgrade_list',
    'queryBy' => 'searchForm',
    'idField' => 'pid',
    'init' => 'nodata',
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">
$(function(){
        $('#version_no').change(function(){ 
            var version_no = $(this).val();
            if(version_no!=''){
                var params = {};
                var url = '?app_act=upgrade/upgrade/get_version_patch&app_fmt=json&version_no='+version_no;
                 $("#version_patch option").remove(); 
                $('#version_patch').append("<option value=''>请选择</option>");   
                params.version_no = version_no;
                $.post(url, params, function(result){ 
                   for(var i in result.data){
                       $('#version_patch').append("<option value='"+result.data[i].version_patch+"'>"+result.data[i].version_patch+"</option>");
                   }
                },'json');       
            }
        });
 
        $('#exprot_list').click(function(){
             var url = '?app_act=upgrade/upgrade/exprot_upgrade_fail_log&app_fmt=json';
              var version_no = $('#version_no').val();
              if(version_no!=''){
                 url +="&version_no="+version_no;
              }
             window.location.href=url;
        });
      
    
    //批量升级
    var  up_num = 0;
    $('#upgrade_but').on('click',function(){
            //$request['ids'],$request['version_no'],$request['version_patch']

	    var selections = tableGrid.getSelection();
	    var ids = [];
            var version_no = '';
            var version_patch='';
            var check_fail = 0;
	    BUI.each(selections,function(item){
                    if(version_no==''){
                        version_no =item.version_no;
                    }
                    if(version_patch==''){
                        version_patch =item.version_patch;
                    }
                    if(version_no!=item.version_no){
                       BUI.Message.Alert(item.kh_name+'：升级版本必须统一','error');
                       check_fail ++;
                       return ;       
                    }
                   if(version_patch!=item.version_patch){
                       BUI.Message.Alert(item.kh_name+'：升级补丁补统一','error');
                       check_fail ++;
                       return ;       
                    }
                    if(item.status!=0){
                       BUI.Message.Alert(item.kh_name+'：只能升级未升级过补丁','error');
                       check_fail ++;
                       return ;       
                    }
		    ids.push(item.kh_id);
                    up_num++;
	    });
            
            if(version_no==''){
                     BUI.Message.Alert('升级版本不能为空','error');
                       return ;    
            }
            if(version_patch==''){
                        BUI.Message.Alert('升级补丁不能为空','error');
                       return ; 
            }
	    var url = '?app_act=upgrade/upgrade/do_upgrade_all&app_fmt=json';
	    var params = {};
	    params.ids = ids;
            params.version_no =  version_no;
            params.version_patch = version_patch;
            $('#upgrade_but').attr('disabled', true);
	    $.post(url, params, function(result){
                if(result.status==2){
                     BUI.Message.Alert('存在部分不能升级的客户','error');
                }
                set_update();   
	    },'json');
    });
    
    function set_update(){
            var url = '?app_act=upgrade/upgrade/get_upgrade_info&app_fmt=json';
	    var params = {};
	    $.post(url, params, function(result){
                if(result.data<up_num){
                    //刷新列表
                    tableStore.load();
                    up_num = result.data;
                }
                if(result.data>0){
                    setTimeout(function(){set_update(),3000});   
                }else{
                          $('#upgrade_but').attr('disabled', false);
                }
	    },'json');
    }
    
});

</script>