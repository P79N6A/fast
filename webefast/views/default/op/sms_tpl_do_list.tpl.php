<style>
    .bui-dialog .bui-stdmod-footer{text-align: center;}
</style>

<?php
$head_links = array();
if ($response['priv']['op/sms_tpl/detail#scene=add']){
    $head_links[] = array('url' => 'op/sms_tpl/detail&app_scene=add', 'title' => '新增短信模板', 'is_pop' => 0, 'pop_size' => '600,450');
}
render_control('PageHead', 'head1',
    array('title' => '短信模板列表',
        'links' => $head_links,
        'ref_table' => 'table'
    ));

?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
    'id' => 'btn-search',
),
    'fields' => array(
        array('label' => '短信模版类型',
            'title' => '',
            'type' => 'select',
            'id' => 'tpl_type',
        	'data'=> $response['select']['sms_tpl_type'],
            ),
       array('label' => '短信模版名称',
            'title' => '',
            'type' => 'input',
            'id' => 'tpl_name',
        ),
       array('label' => '备注',
            'title' => '',
            'type' => 'input',
            'id' => 'remark',
        ),
    )
));

?>
<?php
$operate_buttons = array();
if ($response['priv']['op/sms_tpl/do_preview']){
    $operate_buttons[] = array('id' => 'preview', 'title' => '预览',  'callback' => 'do_preview');
}
if ($response['priv']['op/sms_tpl/detail#scene=edit']){
    $operate_buttons[] = array('id' => 'edit', 'title' => '编辑', 'act' => 'op/sms_tpl/detail&app_scene=edit', 'show_name' => '编辑短信模板');
}
if ($response['priv']['op/sms_tpl/do_delete']){
    $operate_buttons[] = array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？');
}
render_control('DataTable', 'table', array('conf' => array('list' => array(
	array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '150',
        'align' => '',
        'buttons' => $operate_buttons,
    ),
	array(
        'type' => 'text',
        'show' => 1,
        'title' => '短信模版类型',
        'field' => 'tpl_type_name',
        'width' => '150',
        'align' => '',
    ),
	array(
        'type' => 'text',
        'show' => 1,
        'title' => '短信模版名称',
        'field' => 'tpl_name',
        'width' => '200',
        'align' => '',
    ),
	array(
        'type' => 'text',
        'show' => 1,
        'title' => '短信签名',
        'field' => 'sms_sign',
        'width' => '120',
        'align' => '',
    ),
    array('type' => 'text',
        'show' => 1,
        'title' => '模版内容',
        'field' => 'sms_info_sub',
        'width' => '400',
        'align' => '',
    ),
	array(
        'type' => 'text',
        'show' => 1,
        'title' => '备注',
        'field' => 'remark',
        'width' => '100',
        'align' => '',
    ),
)
),
    'dataset' => 'op/SmsTplModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
));

?>

<script type="text/javascript">
function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('op/sms_tpl/do_delete');?>',
        data: {id: row.id},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
            BUI.Message.Alert('删除成功!', type);
            tableStore.load();
            } else {
            BUI.Message.Alert(ret.message, type);
            }
        }
	});
}

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('op/sms_tpl/update_active');?>',
        data: {id: row.id, type: active},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
            BUI.Message.Alert(ret.message, type);
            tableStore.load();
            } else {
            BUI.Message.Alert(ret.message, type);
            }
        }
	});
}
function do_preview(_index, row){
    var html = '<div style="margin:10px;height:178px;overflow-y:scroll;">';
    if ('' != row.sms_sign){
        html += '【' + row.sms_sign + '】';
    }
    html += row.sms_info+'</div>';
    BUI.use('bui/overlay',function(Overlay){
        var dialog = new Overlay.Dialog({
            title:'预览',
            width:500,
            height:300,
            mask: true,
            buttons:[
              {
                text:'确认',
                elCls : 'button button-primary',
                handler : function(){
                  //do some thing
                  this.close();
                }
              }
            ],
            bodyContent:html
          });
        dialog.show();
    });
}

$(function(){
	$(".control-label").css("width","110px");
})

</script>