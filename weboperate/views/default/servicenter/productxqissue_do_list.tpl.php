<?php render_control('PageHead', 'head1',array('title'=>'需求提单列表',
    	'links'=>array(
            array('url'=>'servicenter/productxqissue/detail&app_scene=add', 'title'=>'新建需求提单',  'pop_size'=>'500,400'),
	),
	'ref_table'=>'table'    
    ));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    //'cmd' => array (
    //    'label' => '查询',
    //    'title' => '查询',
    //    'id' => 'btn-search'
    //),
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
    'fields' => array (
        array (
            'label' => '提单编号',
            'title' => '编号',
            'type' => 'input',
            'id' => 'xqsue_number' 
        ),
        array (
            'label' => '提单标题',
            'title' => '标题模糊搜索',
            'type' => 'input',
            'id' => 'xqsue_title' 
        ),
        array (
            'label' => '产品',
            'type' => 'select',
            'id' => 'xqsue_cp_id',
            'class'=>'input-large',
            'data'=>ds_get_select('chanpin',2)
        ),
        array (
            'label' => '状态',
            'type' => 'select_multi',
//            'id' => 'issue_status',
            'field'=>'xqsue_status',
            'data'=>ds_get_select_by_field('xqissue_type',0)
        ),
        array(
            'label' => '期返时间',
            'type' => 'group',
            'field' => 'daterange4',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'xqsue_return_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'xqsue_return_time_end',),
            )
        ),
        array (
            'label' => '提单人',
            'title' => '单据提交人',
            'type' => 'input',
            'id' => 'xqsue_user',
        ),
        array (
            'label' => '需求类型',
            'type' => 'select_multi',
            'field'=>'xqsuetype',
            'data'=>ds_get_select_by_field('xqsuetype',0)
        ),
        array (
            'label' => '紧急程度',
            'type' => 'select_multi',
            'field'=>'xqsue_urgency',
            'data'=>ds_get_select_by_field('xqsue_urgency',0)
        ),
        array (
            'label' => '难易度',
            'type' => 'select_multi',
            'field'=>'xqsue_difficulty',
            'data'=>ds_get_select_by_field('xqsue_difficulty',0)
        ),
        array (
            'label' => '是否计划周次',
            'type' => 'select',
            'id' => 'xqsue_plan_week_status',
            'class'=>'input-large',
            'data'=>ds_get_select_by_field('xqsue_plan_week_status',1),
        ),
        array (
            'label' => '计划周次',
            'title' => '计划周次',
            'type' => 'input',
            'id' => 'xqsue_plan_week',
        ),
        array (
            'label' => '业务类型',
            'type' => 'select',
            'id' => 'xqsue_service_type',
            'class'=>'input-large',
            'data'=>ds_get_select_by_field('xqsue_service_type',1),
        ),
        array (
            'label' => '客户名称',
            'title' => '支持模糊查询',
            'type' => 'input',
            'id' => 'kh_name' 
        ),
        array (
            'label' => '关键字',
            'title' => '审批意见模糊查询',
            'type' => 'input',
            'id' => 'xqsue_idea' 
        ),
        array (
            'label' => '提单内容',
            'title' => '提单内容模糊查询',
            'type' => 'input',
            'id' => 'xqsue_detail'
        ),
    ) 
) );
?>

<ul class="toolbar frontool" id="tool">
    <li class="li_btns"><button class="button button-primary btn_pass" >批量已解决</button></li>
    <li class="li_btns"><button class="button button-primary btn_online" >批量上线</button></li>
</ul>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提单编号',
                'field' => 'xqsue_number',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '需求标题',
                'field' => 'xqsue_title',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'xqsue_kh_id_name',
                'width' => '200',
                'align' => '' ,
                ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '业务类型',
                'field' => 'xqsue_service_type_name',
                'width' => '200',
                'align' => '' ,
            ),
            //array (
            //    'type' => 'text',
            //    'show' => 1,
            //    'title' => '需求详情',
            //    'field' => 'xqsue_detail',
            //    'width' => '200',
            //    'align' => '' ,
            //),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '计划周次',
                'field' => 'xqsue_plan_week',
                'width' => '100',
                'align' => '' ,
            ),
            //array (
            //    'type' => 'text',
            //    'show' => 1,
            //    'title' => '产品名称',
            //    'field' => 'xqsue_cp_id_name',
            //    'width' => '120',
            //    'align' => '',
            //),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提单人',
                'field' => 'xqsue_user',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '提交时间',
                'field' => 'xqsue_submit_time',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '紧急程度',
                'field' => 'xqsue_urgency',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '难易度',
                'field' => 'xqsue_difficulty_name',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'xqsue_status',
                'width' => '120',
                'align' => '',
               'format_js'=>array('type'=>'map', 'value'=>ds_get_field('xqissue_type'))
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                        array('id'=>'view', 'title' => '查看', 
                		'act'=>'servicenter/productxqissue/detail&app_scene=view', 'show_name'=>'查看需求提单'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'servicenter/productxqissue/detail&app_scene=edit', 'show_name'=>'编辑需求提单','show_cond'=>"obj.xqsue_status == 1"
                       ),   
                ),
            )
        ) 
    ),
    'dataset' => 'servicenter/ProductxqissueModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'xqsue_number',
    'export' => array('id' => 'exprot_list', 'conf' => 'servicenter_productxqissue_list', 'name' => '需求提单列表',),//'export_type'=>'file'
    'CheckSelection'=>true,
    'events'=>array(
        'rowdblclick'=>array('ref_button'=>'view')),
) );
?>
<script type="text/javascript">
//读取已选中项
function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择需求提单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.xqsue_number);
        }
        ids.join(',');
        
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否执行需求提单' + obj.text() + '?',
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function() {
                        func.apply(null, [ids]);
                        this.close();
                    }
                },
                {
                    text: '否',
                    elCls: 'button',
                    handler: function() {
                        this.close();
                    }
                }
            ]
        });
        
    }

$(".btn_online").click(function(){

    get_checked($(this), function(xqsue_numbers){
        
        var url='?app_act=servicenter/productxqissue/do_show_online&type=2&xqsue_number='+xqsue_numbers;
        var title="上线描述";
        btn_show_dialog(url,title, {w:800,h:450});
        /*
        var params = {"xqsue_numbers": xqsue_numbers};
        $.post("?app_act=servicenter/productxqissue/batch_do_xqissue_online", params, function(data){
            if(data.status == '1'){
                //刷新数据
                BUI.Message.Alert(data.message, 'success');
                tableStore.load();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");*/

    })

})

//批量已解决
$(".btn_pass").click(function () {
    get_checked($(this), function (xqsue_numbers) {
        var url = '?app_act=servicenter/productxqissue/multi_do_show_unable_pass&xqsue_numbers=' + xqsue_numbers;
        var title = "解决描述";
        btn_show_dialog(url, title, {w: 800, h: 450});
    });
});

function btn_show_dialog(_url, _title, _opts) {
        new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                  
                tableStore.load();
                if (typeof _opts.callback == 'function') 
                    _opts.callback();
            }
        }).show();
    }
</script>