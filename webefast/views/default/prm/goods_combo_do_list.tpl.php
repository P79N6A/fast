<?php
/*
render_control('PageHead', 'head1', array('title' => '商品套餐列表',
    'links' => array(
        array('url' => 'prm/goods_combo/detail&app_scene=do_add', 'title' => '添加商品套餐', 'is_pop' => false, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
*/
?>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
<span class="page-title">
<h2>商品套餐列表</h2>
</span>
<span class="page-link">
<?php if (load_model('sys/PrivilegeModel')->check_priv('prm/goods_combo/detail&action=do_add')) { ?>
<span class="action-link">
<a class="button button-primary" href="javascript:openPage('cHJtL2dvb2RzX2NvbWJvL2RldGFpbCZhcHBfc2NlbmU9ZG9fYWRk', '?app_act=prm/goods_combo/detail&app_scene=do_add', '添加商品套餐')"> 添加商品套餐</a>
</span>
<?php } ?>
<button class="button button-primary" onclick="javascript:location.reload();">
<i class="icon-refresh icon-white"></i>
刷新
</button>
</span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<script type="text/javascript">

var ES_PAGE_ID = 'prm/goods_combo/do_list';

function PageHead_show_dialog(_url, _title, _opts) {

    new ESUI.PopWindow(_url, {
            title: _title,
            width:_opts.w,
            height:_opts.h,
            onBeforeClosed: function() {                 tableStore.load();                 if (typeof _opts.callback == 'function') _opts.callback();
            }
        }).show();
}
</script>
<?php
 $fenxiao = ds_get_select('custom',2);
 unset($fenxiao[0]);
$keyword_type = array();
$keyword_type['goods_code'] = '套餐编码';
$keyword_type['goods_name'] = '套餐名称';
$keyword_type['barcode'] = '套餐条形码';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(

   array(
        'label' => '查询',
        'id' => 'btn-search',
        'type'=>'submit'
    ),
   array(
        'label' => '导出',
        'id' => 'exprot_list',
    ),
         ) ,
    'fields' => array(
        array(
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'支持模糊查询',
            'data'=>$keyword_type,
            'id' => 'keyword',
            ),
        array(
            'label' => '启用',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'status',
            'data'=>array(
	    array('0','停用'),array('1','启用')
	    )),

        array(
        		'label' => '创建时间',
        		'type' => 'group',
        		'field' => 'create_time',
        		'child' => array(
        				array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
        				array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
        		)
        ),

    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(

            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '120',
                'align' => '',
                'buttons' => array(
                                array('id'=>'edit', 'title' => '编辑',
                                      'priv'=>'prm/goods_combo/detail&app_scene=do_edit',
                                      'act'=>'prm/goods_combo/detail&app_scene=do_edit', 'show_name'=>'套餐商品编辑',
                                     ),

                    array('id' => 'enable', 'title' => '启用',
                          'priv'=>'prm/goods_combo/update_active',
                    	  'callback' => 'do_enable', 'show_cond' => 'obj.status != 1'),
                    array('id' => 'disable', 'title' => '停用',
                          'priv'=>'prm/goods_combo/update_active',
                          'callback' => 'do_disable', 'show_cond' => 'obj.status == 1'
                    	 ),
                    array(
                        'id' => 'delete', 'title' => '删除',
                        'priv'=>'prm/goods_combo/do_delete',
                        'callback' => 'check_status',
                        'show_cond' => 'obj.status != 1'
                    ),
                    array('id'=>'view', 'title' => '库存查看',
                          'pop_size' => '800,550',
                          'priv'=>'prm/goods_combo/goods_inv',
                    	  'act'=>'prm/goods_combo/goods_inv&app_scene=do_edit', 'show_name'=>'套餐商品库存',
                         ),

                ),
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '启用',
            		'field' => 'status',
            		'width' => '50',
            		'align' => '',
            		'format_js' => array('type' => 'map_checked')
            ),

            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '套餐编码',
            		'field' => 'goods_code',
            		'width' => '160',
            		'align' => '',

            ),

            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '套餐名称',
            		'field' => 'goods_name',
            		'width' => '160',
            		'align' => '',
            ),

            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '套餐描述',
            		'field' => 'goods_desc',
            		'width' => '100',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '套餐条形码',
            		'field' => 'barcode',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => $response['spec1_rename'].'名称',
            		'field' => 'combo_spec1_name',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => $response['spec1_rename'].'编码',
            		'field' => 'combo_spec1_code',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => $response['spec2_rename'].'名称',
            		'field' => 'combo_spec2_name',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => $response['spec2_rename'].'编码',
            		'field' => 'combo_spec2_code',
            		'width' => '160',
            		'align' => ''
            ),
            array(
            		'type' => 'text',
            		'show' => 1,
            		'title' => '创建时间',
            		'field' => 'create_time',
            		'width' => '150',
            		'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/GoodsComboModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'goods_combo_id',
    'CheckSelection'=>true,
    'export'=> array('id'=>'exprot_list','conf'=>'goods_combo_list','name'=>'套餐列表','export_type' => 'file'),
));
?>
<ul class="toolbar frontool" id="tool">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/goods_combo/update_active')) { ?>
        <li class="li_btns"><button class="button button-primary " onclick="multi_enable(1)">批量启用</button></li>
        <li class="li_btns"><button class="button button-primary " onclick="multi_enable(0)">批量停用</button></li>
    <?php } ?>
    <div class="front_close">&lt;</div>
</ul>
<?php echo load_js("pur.js",true);?>
<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() == "&lt;") {
                    $(".frontool").animate({left: '-100%'}, 1000);
                    $(this).html(">");
                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                } else {
                    $(".frontool").animate({left: '0px'}, 1000);
                    $(this).html("<");
                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                }
            });
        }
        tools();
    })

function do_enable(_index, row) {
	_do_set_active(_index, row, 'enable');
}
function do_disable(_index, row) {
	_do_set_active(_index, row, 'disable');
}
function _do_set_active(_index, row, active) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('prm/goods_combo/update_active');?>',
    data: {id: row.goods_combo_id, type: active},
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

    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        location.href = "?app_act=wbm/notice_record/view&notice_record_id=" + row.notice_record_id;
    }

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=wbm/notice_record/view&notice_record_id') ?>'+row.notice_record_id,'?app_act=wbm/notice_record/view&notice_record_id='+row.notice_record_id,'通知单');
    }

    //读取已选中项
    function get_checked(isConfirm, func,operate){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].goods_combo_id)
        }
        if(ids.length == 0){
            BUI.Message.Alert("请选择套餐", 'error');
            return
        }
        if(isConfirm) {
            BUI.Message.Show({
                title : '提示',
                msg : '是否执行批量'+operate,
                icon : 'question',
                buttons : [
                    {
                        text:'是',
                        elCls : 'button button-primary',
                        handler : function(){
                            func.apply(null, [ids])
                        }
                    },
                    {
                        text:'否',
                        elCls : 'button',
                        handler : function(){
                            this.close();
                        }
                    }
                ]
            });
        } else {
            func.apply(null, [ids])
        }
    }

    //批量启用/停用
    function multi_enable(active) {
        var operate = (active == 1) ? "启用" : "停用";
        get_checked(true, function (ids) {
            $.ajax({
                type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods_combo/multi_update_active');?>',
                data: {ids: ids.toString(), active: active},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, operate)
    }
    
//删除
function check_status(_index, row) {
    $.ajax({
        type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('prm/goods_combo/check_status');?>',
        data: {barcode: row.barcode, goods_combo_id:row.goods_combo_id},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Confirm('请确认是否要删除该商品套餐？', function(){
                    do_delete(row.barcode, row.goods_code);
                }, 'question');
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
    });
}

function do_delete(barcode,goods_code) {
    $.ajax({
        type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('prm/goods_combo/do_delete');?>',
        data: {barcode: barcode, goods_code:goods_code},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert('删除成功！', type);
                tableStore.load();
            } else {
                BUI.Message.Alert('删除失败！', type);
            }
        }
    });
}
</script>


