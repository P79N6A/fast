<?php echo load_js('comm_util.js')?>
<?php echo load_js("pur.js",true);?>

<?php

render_control('PageHead', 'head1', array('title' => 'BS3000J商品库存维护日志',
    'links' => array(
         //array('url' => 'erp/bs3000j_inv_sync/get_inv_and_update', 'title' => 'BS3000J库存获取并eFAST库存更新', 'is_pop' => false, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>

<?php
 render_control ( 'SearchForm', 'searchForm', array (
    'buttons' =>array(

   array(
        'label' => '查询',
        'id' => 'btn-search',
           'type'=>'submit'        
    ),
         ) ,

    'fields' => array (
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('sys/ShopStoreModel')->get_erp_store_code()
        ),
        array (
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array (
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'goods_barcode'
        ),
        array(
            'label' => '库存状态',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'update_status',
            'data' => array(
                array('0', '未更新'), array('1', '已更新')
            )
        ),
        array(
            'label' => '库存获取时间',
            'type' => 'group',
            'field' => 'updated',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'updated_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'updated_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '库存更新时间',
            'type' => 'group',
            'field' => 'efast_update',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'efast_update_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'efast_update_end', 'remark' => ''),
            )
        ),
	  
      
    )
) );
?>

<?php render_control ( 'DataTable', 'table', array (
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
                    array('id'=>'get_inv_and_update', 'title' => '获取3000J库存并更新', 'callback'=>'get_inv_and_update'),
                ),
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '本地库存状态',
                'field' => 'update_status_name',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'eFAST仓库',
                'field' => 'efast_store_name',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'BS3000J仓库',
                'field' => 'CKDM',
                'width' => '90',
                'align' => ''
            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品代码',
                'field' => 'SPDM',
                'width' => '100',
                'align' => ''
            ),
            
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '颜色代码',
                'field' => 'GG1DM',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '尺码代码',
                'field' => 'GG2DM',
                'width' => '80',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'ERP在库库存',
                'field' => 'SL',
                'width' => '120',
                'align' => ''
            ),

            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'ERP锁定库存',
                'field' => 'SL1',
                'width' => '120',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'BS3000J库存最后获取时间',
                'field' => 'updated',
                'width' => '180',
                'align' => '',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '本地库存最后更新时间',
                'field' => 'efast_update',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'erp/Bs3000jInvSyncModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
	'CheckSelection'=>true,
) );
?>
<!--
 <ul id="ToolBar1" class="toolbar frontool">
        <li class="li_btns"><button class="button button-primary btn_upload_record">批量上传</button></li>
        <div class="front_close">&lt;</div>
 </ul>   -->
<br />
<span style="color:red">友情提示：以ERP在库库存-ERP锁定库存同步到系统作为实物库存</span>
<script>
$(function(){
	//TAB选项卡
    $("#TabPage1 a").click(function() {
        tableStore.load();
    });
    $("input[name='is_normal']").change(function(){
        tableStore.load();
    });
    tableStore.on('beforeload', function(e) {
        e.params.upload_tab = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });
	
	function tools(){
        $(".frontool").animate({left:'0px'},1000);
        $(".front_close").click(function(){
            if($(this).html()=="&lt;"){
                $(".frontool").animate({left:'-100%'},1000);
                $(this).html(">");
				$(this).addClass("close_02").animate({right:'-10px'},1000);
            }else{
                $(".frontool").animate({left:'0px'},1000);
                $(this).html("<");
				$(this).removeClass("close_02").animate({right:'0'},1000);
            }
        });
    }
	
	tools();
})
</script>
<script type="text/javascript">
	//批量上传
    $(".btn_upload_record").click(function(){
    	
        get_checked($(this), function(ids){
        	//校验是否绑定批发通知单
          	var d = {"record_codes": ids.toString(),'app_fmt': 'json'};
         	 $.post("?app_act=erp/bs3000j/upload_multi", d, function(data){
         		var type = data.status == 1 ? 'success' : 'error';
          		BUI.Message.Alert(data.message, type);
      			tableStore.load();
	         }, "json");
          	
        })
    })
    
     //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        ids.join(',');
        BUI.Message.Show({
            title: '自定义提示框',
            msg: '是否确定要执行' + obj.text() + '?',
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
    
    
    function get_inv_and_update(index, row){
        url = '?app_act=erp/bs3000j_inv_sync/get_inv_and_update';
        data = {id: row.id,erp_config_id:row.erp_config_id};
        _do_operate(url, data, 'table');
    }
</script>



