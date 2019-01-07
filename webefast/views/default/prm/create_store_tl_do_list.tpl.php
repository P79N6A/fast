<style type="text/css">
</style>
<?php render_control('PageHead', 'head1',
		array('title'=>'商品唯一码更改仓库',

				'ref_table'=>'table'
));?>

<?php
$keyword_type = array();
$keyword_type['unique_code'] = '唯一码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '饰品名称';
$keyword_type = array_from_dict($keyword_type);
render_control ( 'SearchForm', 'searchForm', array (
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
    ),
    'fields' => array (
		     array(
	            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
	            'type' => 'input',
	            'title'=>'',
	            'data'=>$keyword_type,
	            'id' => 'keyword',
                     'help' => '唯一码支持多个查询，用逗号分隔',
	        ),
                array(
                 'label' => '仓库',
                 'type' => 'select_multi',
                 'id' => 'store_code',
                 'data' => load_model('base/StoreModel')->get_purview_store(),
              ),
    )
    
) );
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'),
        array('title' => '可用', 'active' => false, 'id' => 'tabs_allow'),
        array('title' => '不可用', 'active' => false, 'id' => 'tabs_not_allow'), 
    ),
     'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>



    <?php
    render_control ( 'DataTable', 'table', array (
        'conf' => array (
            'list' => array (
                
             	array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (

                	//array('id' => 'edit_store', 'title' => '更改仓库(生成移仓单)', 'priv' => 'prm/create_store/edit_warehouse', 'callback' => 'edit_warehouse','show_cond' => 'obj.status != 1'),
                   // array('id'=>'unedit_store', 'title' => '更改仓库(不影响库存)','priv' => 'prm/create_store/do_edit',  'callback'=>'edit_warehouse','show_cond' => 'obj.status != 1'),
                    array('id' => 'edit_warehouse', 'title' => '更改仓库(生成移仓单)',
                        'act' => 'pop:prm/create_store_tl/edit_warehouse&id={jewelry_id}&type=edit_warehouse', 'show_name' => '更改仓库(生成移仓单)', 'show_cond' => 'obj.status==0',
                        'priv' => 'prm/create_store/edit_warehouse', 'pop_size' => '500,450','is_pop' => true),
                    array('id' => 'unedit_store', 'title' => '更改仓库(不影响库存)',
                        'act' => 'pop:prm/create_store_tl/edit_warehouse&id={jewelry_id}&type=unedit_store', 'show_name' => '更改仓库(不影响库存)', 'show_cond' => 'obj.status==0',
                        'priv' => 'prm/create_store/do_edit_one', 'pop_size' => '400,300'),
                ),
            ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品唯一码',
                    'field' => 'unique_code',
                    'width' => '200',
                    'align' => ''
                ),
                   array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '仓库',
                	'field' => 'store_name',
                	'width' => '150',
                	'align' => ''
                ),
                    array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '状态',
                    'field' => 'is_allow_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品条形码',
                	'field' => 'barcode',
                	'width' => '150',
                	'align' => ''
                ),
              
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品税收分类编码',
                	'field' => 'good_revenue_code',
                	'width' => '150',
                	'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '厂家款号',
                    'field' => 'factory_code',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '通灵款',
                    'field' => 'tongling_code',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '饰品名称',
                    'field' => 'goods_name',
                    'width' => '150',
                    'align' => ''
                ),

                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '成色',
                    'field' => 'relative_purity',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '金成色',
                    'field' => 'relative_purity_of_gold',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '国际证书号',
                    'field' => 'international_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '检测站证书号',
                    'field' => 'check_station_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '身份证',
                    'field' => 'identity_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '品牌',
                    'field' => 'jewelry_brand',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '子品牌',
                    'field' => 'jewelry_brand_child',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '金属颜色',
                    'field' => 'metal_color',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '颜色',
                    'field' => 'jewelry_color',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '净度',
                    'field' => 'jewelry_clarity',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '切工',
                    'field' => 'jewelry_cut',
                    'width' => '100',
                    'align' => ''
                ),
                               
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '主石重量',
                    'field' => 'pri_diamond_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '主石数量',
                    'field' => 'pri_diamond_count',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '辅石重量',
                    'field' => 'ass_diamond_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '辅石数量',
                    'field' => 'ass_diamond_count',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '珠宝总重量',
                    'field' => 'total_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '类别',
                    'field' => 'jewelry_type',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '手寸长度',
                    'field' => 'ring_size',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '销售含税价',
                    'field' => 'total_price',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '证书类型',
                    'field' => 'credential_type',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '证书总重',
                    'field' => 'credential_weight',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '货单号',
                    'field' => 'record_num',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '饰品简称',
                    'field' => 'short_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性1',
                    'field' => 'user_defined_property_1',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性2',
                    'field' => 'user_defined_property_2',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性3',
                    'field' => 'user_defined_property_3',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性4',
                    'field' => 'user_defined_property_4',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性5',
                    'field' => 'user_defined_property_5',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性6',
                    'field' => 'user_defined_property_6',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性7',
                    'field' => 'user_defined_property_7',
                    'width' => '100',
                    'align' => ''
                ),
                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '自定义属性8',
                    'field' => 'user_defined_property_8',
                    'width' => '100',
                    'align' => ''
                ),

            )
        ),
        'dataset' => 'prm/GoodsUniqueCodeTLModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'unique_id',
        'export'=> array('id'=>'exprot_list','conf'=>'unique_code_list_tl','name'=>'珠宝唯一码','export_type' => 'file'),
        'CheckSelection' => true,
    ) );
    ?>



<div id="TabPage1Contents">
     <div>
          <ul class="toolbar frontool" id="ToolBar1">
            <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/create_store/opt_do_edit')) { ?>
                 <li class="li_btns"><button class="button button-primary btn_opt_unedit_store hide" >批量更改仓库(不影响库存)</button></li>
            <?php } ?>
            <div class="front_close">&lt;</div>
           </ul>

                <script>
			$(function(){
				var custom_opts = $.parseJSON('[{"id":"opt_unedit_store","custom":"opt_unedit_store"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar1 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
    </div>
    <div>
          <ul class="toolbar frontool" id="ToolBar2">
            <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/create_store/opt_do_edit')) { ?>
                 <li class="li_btns"><button class="button button-primary btn_opt_unedit_store" >批量更改仓库(不影响库存)</button></li>
            <?php } ?>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('prm/create_store/opt_edit_warehouse')) { ?>
                 <li class="li_btns"><button class="button button-primary btn_opt_edit_warehouse" >批量更改仓库(生成移仓单)</button></li>
            <?php } ?>
            <div class="front_close">&lt;</div>
           </ul>

                <script>
			$(function(){
				var custom_opts = $.parseJSON('[{"id":"opt_unedit_store","custom":"opt_unedit_store"},{"id":"opt_edit_warehouse","custom":"opt_edit_warehouse"}]');
				for(var j in custom_opts){
				    var g = custom_opts[j];
				    $("#ToolBar2 .btn_"+g['id']).click(eval(g['custom']));
				}
			});
		</script>
    </div>
    	
	
		
	
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
</script>
</div>
<script type="text/javascript">

$(document).ready(function() {
	$("#TabPage1 a").click(function() {
        tableStore.load();
    });
    
    tableStore.on('beforeload', function(e) {
    	e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
    	tableStore.set("params", e.params);
    });

})
    //读取已选中项
    function get_checked(isConfirm, obj, func){
        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].jewelry_id)
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择列表", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '批量操作',
                msg : '是否执行列表'+obj.text()+'?',
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
    
//批量更改仓库不影响库存
   function opt_unedit_store(){
        get_checked(false, $(this), function(ids){
            //console.log(ids);
            var params = {jewelry_id: ids}
            //console.log(jewelry_id);
            $.post("?app_act=prm/create_store_tl/judge_store",params, function(data) {
                if(data.status == 1){
                    unedit_store(data.data.toString())
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
    function unedit_store(jewelry_id){
        new ESUI.PopWindow("?app_act=prm/create_store_tl/edit_warehouse&id=" + jewelry_id + "&type=unedit_store", {
                      title: "更改仓库(不影响库存)",
                      width: 400,
                      height: 380,
                      onBeforeClosed: function () {
                      },
                      onClosed: function () {
                          //刷新数据
                            tableStore.load()
                      }
                  }).show()
    }

//批量更改仓库,生成移仓单
       function opt_edit_warehouse(){
        get_checked(false, $(this), function(ids){
            var params = {jewelry_id: ids}
            $.post("?app_act=prm/create_store_tl/judge_store",params, function(data) {
                if(data.status == 1){
                    transfer_warehouse(data.data.toString());
                }else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        })
    }
  function transfer_warehouse(jewelry_id){
        new ESUI.PopWindow("?app_act=prm/create_store_tl/edit_warehouse&id=" + jewelry_id + "&type=edit_warehouse", {
                      title: "更改仓库(生成移仓单)",
                      width: 500,
                      height: 450,
                      onBeforeClosed: function () {
                      },
                      onClosed: function () {
                          //刷新数据
                            tableStore.load()
                      }
                  }).show()
    }
</script>
<script type="text/javascript">
    //唯一码添加逗号分隔
    $('#keyword').attr('maxlength','10000');//输入框限制10000个字符长度
    $('#keyword').blur(function(){
        var str = $.trim($(this).val().replace((/[\r\n\"]/g),''));//去除换行双引号和前后空格
        new_str = str.replace((/\s+/g),',');
        //console.log(new_str);
        $(this).val(new_str);
    });  
</script>

