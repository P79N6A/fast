<?php 
//售后只赔付的标识
if (empty($response['mx']) && $response['is_compensate']>0){
	$sell_after_is_compensate = 1;
}else{
	$sell_after_is_compensate = 0;
}
$class1=$class2=$class3=$class4=$class5=$class6=$class7='';
if ($response['return_type']==1){
    if($response['is_package_out_stock']==1){
        $class2=$class4=$class5=$class6='hide';
    }else{
        $class2=$class5=$class6='hide';
    }
}
?>
<?php echo load_js('comm_util.js')?>
<?php echo load_js("pur.js",true);?>
<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    a{
        cursor:pointer;
    }
    .form-horizontal .control-label{
        width:100%;
    }
    .form-horizontal .control-label.span3{
        width:40%;
    }
    .form-horizontal .bui-grid-table input{
        width:90%;
    }
    .span8{
        width:50%
    }
    .span11{
        width:40%;
    }

	.panel-header h3.pull-left {
	    color: #ec6d3a;
	}

/*	.panel-body .form-horizontal{padding-bottom:10px;}
	.panel-body table{border:1px #ccc solid;}
	.panel-body td,.panel-body th{border-right:1px #ccc solid;padding:2px;}*/
        #panel_baseinfo textarea{width:70%;}
        #tools .li_btns{float:left;margin-left:2px;} 
        .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
</style>
<?php echo load_js("jquery.form.js",true); ?>
<?php echo load_js("record_table.js",true); ?>
<?php echo load_js("tan.js",true);?>
<?php render_control('PageHead', 'head1',
 array('title' => "分销退单详细-<font color='red'>退单号：" . $request['sell_return_code'] . "</font>",
//    'links' => array(
//        array('url' => 'fx/sell_return/after_service_list', 'title' => '分销退单列表', 'target'=>'_self', 'is_pop' => false, 'pop_size' => '500,400'),
//    ),
    'ref_table' => 'table'
));
?>
<div id="tag_name_type" style="display: none"></div>
    <div  id="panel_status_info">

    </div>
<div class="panel <?php echo $class1; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/ddxq_icon.png"/>&nbsp;基本信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_baseinfo"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_baseinfo"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_baseinfo"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">

    </div>
</div>


<div class="panel <?php echo $class1; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/fhxx_icon.png"/>&nbsp;退货人信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_return_person"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_return_person"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_return_person"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_return_person">

    </div>
</div>


<!--
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/fhxx_icon.png"/>&nbsp;退单信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_return_order"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_return_order"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_return_order"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_return_order">

    </div>
</div>
-->

<div class="panel <?php echo $class3; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/ddje_icon.png"/>&nbsp;退款信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_return_money"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_return_money"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_return_money"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_return_money">

    </div>
</div>

<?php  if ($sell_after_is_compensate == 0) {?>
<div class="panel <?php echo $class4; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/spxx_icon.png"/>&nbsp;退单商品信息</h3>
        <div class="pull-right">
	        交易号：
            <select id="return_goods_deal_code">
                <?php 
                $deal_code_list = explode(",",$response['deal_code']);
                foreach($deal_code_list as $list): ?>
                <option value="<?php echo $list; ?>"><?php echo $list; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small" id="btn_add_return_goods"><i class="icon-plus"></i>新增商品</button>
            <button class="button button-small hide" id="btn_edit_return_goods"><i class="icon-plus"></i>编辑商品</button>
        </div>
    </div>
    <div class="panel-body" id="panel_return_goods">

    </div>
</div>
<?php }?>

<?php if($sell_after_is_compensate == 0) {?>
<div class="panel <?php echo $class5; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/fhxx_icon.png"/>&nbsp;换货单基本信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_change_baseinfo"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_change_baseinfo"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_change_baseinfo"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_change_baseinfo">

    </div>
</div>

<div class="panel <?php echo $class6; ?>">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/spxx_icon.png"/>&nbsp;换货单商品信息</h3>
        <div class="pull-right">
	        交易号：
            <select id="change_goods_deal_code">
                <?php 
                $deal_code_list = explode(",",$response['deal_code']);
                foreach($deal_code_list as $list): ?>
                <option value="<?php echo $list; ?>"><?php echo $list; ?></option>
                <?php endforeach; ?>
            </select>	
            <button class="button button-small" id="btn_add_change_goods" onclick="change_goods_add();"><i class="icon-plus"></i>新增商品</button>
           <!-- <button class="button button-small" id="btn_add_change_goods" ><i class="icon-plus"></i>新增商品</button>  
            
                  
            <button class="button button-small" id="btn_add_change_goods"><i class="icon-plus"></i>新增商品</button> --> 
            <!--
            <button class="button button-small" id="btn_add_change_goods_by_return_goods"><i class="icon-plus-sign"></i>从退单商品中追加商品</button>
            <button class="button button-small" id="btn_free_change_goods_lock_inv"><i class="icon-share"></i>释放换货单商品库存</button>
            -->
            <button class="button button-small hide" id="btn_change_goods_del"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_change_goods_change"><i class="icon-ok"></i>保存</button>
            <!--<button class="button button-small hide" id="btn_cancel_change_goods"><i class="icon-ban-circle"></i>取消</button>-->
        </div>
    </div>
    <div class="panel-body" id="panel_change_goods">

    </div>
</div>
<?php }?>

   <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/czrz_icon.png"/>操作日志</h3>
            <div class="pull-right">
            </div>
        </div>
        <div class="panel-body" id="panel_action">

        </div>
    </div>

<ul class="clearfix frontool" id="tools">
<!--
    <li class="li_btns"><button class="button button-primary" id="btn_opt_lock">锁定</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_unlock">解锁</button></li>
-->
    <li class="li_btns"><button class="button button-primary" id="btn_opt_confirm">确认</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_unconfirm">取消确认</button></li>
    <!--<li class="li_btns"><button class="button button-primary" id="btn_opt_notice_finance">通知财务退款</button></li>-->
<!--    <li class="li_btns"><button class="button button-primary" id="btn_opt_unnotice_finance">取消通知财务</button></li>-->
    
    <!--<li class="li_btns"><button class="button button-primary" id="btn_opt_finance_reject">财务退回</button></li>-->
    <?php
    if ($response['return_type'] != 1 && $response['is_wms'] != 1){
    ?>

<!--    <li class="li_btns"><button class="button button-primary" id="btn_opt_notice_store">通知仓库收货</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_unnotice_store">取消通知仓库</button></li>-->
    <li class="li_btns"><button class="button button-primary" id="btn_opt_return_shipping">确认收货</button></li>
    
    <?php
	}
    ?>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_finance_confirm">财务确认退款</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_finish">完成</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_create_change_order">生成换货单</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_cancel">作废</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_print_return">打印退单</button></li>
    <li class="li_btns"><button class="button button-primary" id="btn_opt_communicate_log">沟通日志</button></li>
    
    <div class="front_close">&lt;</div>
</ul>
<input id="change_record" type="hidden"  value="<?php echo $response['change_record'];?>" />
<?php echo load_js('comm_util.js')?>
<script>
    var ES_frmId  = '<?php echo $request['ES_frmId'];?>';
    var sell_return_code = <?php echo $request['sell_return_code']?>;
    var sell_return_id = <?php echo $response['sell_return_id']?>;
    var forjs_data_json = <?php echo json_encode($response['forjs_data']);?>;
	var sell_return_scanning = "<?php echo $response['sell_return_scanning'];?>";
	var sell_return_type = "<?php echo $response['return_type'];?>";
    var custom_code = '<?php echo $response['fenxiao_code']?>';
    var components = ['baseinfo','status_info', 'return_person', 'return_order', 'return_money', 'return_goods', 'change_baseinfo', 'change_goods','action'];
    var componentBtns = ['baseinfo','return_person', 'return_order', 'return_money', 'change_baseinfo', 'change_goods'];
//     var opts = [
//         'opt_lock','opt_unlock','opt_confirm','opt_unconfirm','opt_notice_finance','opt_unnotice_finance',
//         'opt_notice_store','opt_unnotice_store','opt_return_shipping','opt_cancel','opt_finance_confirm','opt_finance_reject','opt_create_change_order'
//     ];
	var opts = [
        'opt_lock','opt_unlock','opt_confirm','opt_unconfirm','opt_notice_finance','opt_unnotice_finance',
        'opt_notice_store','opt_unnotice_store','opt_cancel','opt_finance_confirm','opt_finance_reject','opt_create_change_order','opt_finish'
    ];
    var btns = {
        'edit_baseinfo':0,'edit_return_person':0, 'edit_return_order':0, 'edit_return_money':0, 'edit_return_goods':0, 'add_return_goods':0,
        'edit_change_baseinfo':0, 'edit_change_goods':0,'add_change_goods':0,'change_goods_del':0,'change_goods_change':0,
        'opt_lock':0, 'opt_unlock':0, 'opt_confirm':0, 'opt_unconfirm':0, 'opt_notice_finance':0, 'opt_unnotice_finance':0,'opt_finish':0,
        'opt_notice_store':0, 'opt_unnotice_store':0, 'opt_return_shipping':0, 'opt_cancel':0,'opt_finance_confirm':0,'opt_finance_reject':0,'opt_create_change_order':0
    };

    $(document).ready(function(){
        //初始化按钮
        btn_init();

        //初始化数据
        component("all", "view");

        //检查按钮权限
        btn_check();


    });

    $(function(){
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
    //初始化按钮
    function btn_init(){
        //编辑按钮
        for(var i in componentBtns){
            btn_init_component(componentBtns[i]);
        }

        //操作按钮
        for(var i in opts){
            var f = opts[i]
            btn_init_opt(f);
        }
    }

    

    //初始化操作按钮
    function btn_init_opt(id){
        $("#btn_"+id).click(function(){
   

            if(id=='opt_finance_confirm'){
               opt_finance_confirm(id);
            }else  if(id=='opt_create_change_order'){
                       BUI.Message.Confirm('此单为退货单，确认生成换货单吗？',function(){
                           action_opt(id);
                  },'question'); 
            }else if(id=='opt_confirm'){  
            	opt_confirm(id);
            }else{
                   action_opt(id);
            }
            
        });
    }
    function opt_finance_confirm(id){
       var change_record = $('#change_record').val();
       if(change_record!=''){
                       BUI.Message.Confirm('此退单为换货单，换货单号：'+change_record+'，请确认退款金额，避免多退',function(){
                           action_opt(id);
                  },'question');      
              }else{
                    action_opt(id);
              }
       
    }
    //退单确认
    function opt_confirm(id){
    	$.post('?app_act=fx/sell_return/check_return_goods',{"sell_return_code": sell_return_code},function(data){
            if(data.status == -1){
            	BUI.Message.Confirm('退货单中'+data.message+'商品在销售单中不存在，请确认是否退货！',function(){
            		action_opt(id);
            	},'warning');
            } else if (data.status == -2) {
                BUI.Message.Confirm('退单应退款与退还给买家的金额不一致，是否继续？', function () {
                    action_opt(id);
                }, 'warning');
            } else {
            	action_opt(id);
            }
        },'json');
        
     }
    
    
    function action_opt(id){
                 var params = {"sell_return_code": sell_return_code, "type": id};
            $.post("?app_act=fx/sell_return/opt", params, function(data){
                if(data.status == 1){
                    //刷新按钮权限
                    btn_check();
                    component("baseinfo", "view");
                    component("action", "view");
                    component("status_info", "view");
                    location.reload();
                    if ('opt_create_change_order' == id)
                    {
                        var url = '?app_act=oms/sell_record/view&sell_record_code=' + data.data;
                        openPage(window.btoa(url),url,'订单详情');
                    }
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");  
    }


    $("#btn_opt_return_shipping").click(function(){
        if (sell_return_scanning == 1 && sell_return_type != 1) {
        	var url = '?app_act=fx/sell_return/sell_return_scanning_view&sell_return_code=' + sell_return_code;
            openPage(window.btoa(url),url,'收货服务单收货扫描');

        } else {
        	var params = {"sell_return_code": sell_return_code, };
            $.post("?app_act=fx/sell_return/opt_return_shipping", params, function(data){
                if(data.status == 1){
                    //刷新按钮权限
                    btn_check();
                    component("baseinfo", "view");
                    component("action", "view");
                    component("status_info", "view");
                    location.reload();
                    if ('opt_create_change_order' == id)
                    {
                        var url = '?app_act=oms/sell_record/view&sell_record_code=' + data.data;
                        openPage(window.btoa(url),url,'订单详情');
                    }
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        }
    });
    
    //部件操作按钮
    function btn_init_component(id){
        btn_init_component_edit(id);
        btn_init_component_cancel(id);
        btn_init_component_save(id);
    }

    //初始化编辑按钮
    function btn_init_component_edit(id){
        $("#btn_edit_"+id).click(function(){
            $("#btn_edit_"+id).hide();
            $("#btn_cancel_"+id).show();
            $("#btn_save_"+id).show();
            if(id=='change_goods'||id=='return_goods'){
                $("#btn_add_"+id).show();
            }
            component(id, "edit");
        });
    }

    //初始化保存按钮
    function btn_init_component_save(id){
        $("#btn_save_"+id).click(function(){
            //更新按钮状态
            $("#btn_edit_"+id).show();
            $("#btn_cancel_"+id).hide();
            $("#btn_save_"+id).hide();
            if(id=='change_goods'||id=='return_goods'){
                $("#btn_add_"+id).hide();
            }

            //保存数据
            save_component(id);
        });
    }

    //初始化取消按钮
    function btn_init_component_cancel(id){
        $("#btn_cancel_"+id).click(function(){
            $("#btn_edit_"+id).show();
            $("#btn_cancel_"+id).hide();
            $("#btn_save_"+id).hide();
            if(id=='change_goods'||id=='return_goods'){
                $("#btn_add_"+id).hide();
            }

            //刷新数据
            component(id, "view");
        });
    }
    
    //挂起

    $("#btn_opt_communicate_log").click(function () {
        new ESUI.PopWindow("?app_act=fx/sell_return/communicate_log&sell_return_code=" + sell_return_code, {
            title: "沟通日志",
            width: 450,
            height: 350,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                component("all", "view");
                //刷新按钮权限
                //                    btn_check()
            }
        }).show()
    })




    //检查所有按钮权限
    function btn_check(){
        var params = {"sell_return_code": sell_return_code, "fields": btns};

        $.post("?app_act=fx/sell_return/btn_check&app_fmt=json", params, function(data){
            var k;
            for(k in data){
                btn_check_item(k, data[k])
            }
        }, "json");
    }

    //检查按钮权限
    function btn_check_item(id, s){
        var b = $("#btn_"+id);
        
        if(id == 'edit_return_goods'){
            b = $("#panel_return_goods table tbody button");
        }
        if (id == 'change_goods_del') {
            b = $("#panel_change_goods table tbody button.delete");
        }
        if (id == 'change_goods_change') {
            b = $("#panel_change_goods table tbody button.change");
        }
        if (id == 'edit_change_goods') {
            b = $("#panel_change_goods table tbody button.edit");
        }
        if (b.length <= 0) {
            return false;
        }
        
        if(s['status'] == 1){
            b.removeAttr("disabled");
            b.removeAttr("message");
        } else {
            b.attr("disabled", true);
            b.attr("message",s['message']);
        }
    }


    parent._action = component;
    //读取各部分详情
    function component(id, opt){
        var params = {"sell_return_code": sell_return_code, "type": id, "opt": 'get', "components": components, ES_frmId: '<?php echo $request['ES_frmId'];?>'};

        $.post("?app_act=fx/sell_return/component&app_fmt=json&app_scene="+opt, params, function(data){
            if(id != "all"){
                components = [id];
            }
            for(var i in components){
                $("#panel_"+components[i]).html(data[components[i]]);
            }
            if (id == 'return_goods' && opt == 'edit'){
	            init_return_goods_data();
            }
            if (id == 'change_goods' && opt == 'edit'){
	            init_change_goods_data();
            }
            btn_check();
//            console.log(g_mx_json);                        
        }, "json");
    }

	var g_mx_json = {};
        g_mx_json['return_goods'] = {};
        g_mx_json['change_goods'] = {};
    function init_return_goods_data(){
	    $("#panel_return_goods input[name$='[note_num]']").each(function(){
			var mx_id = $(this).attr('name').replace("[note_num]","");
			var money_el_name = mx_id+"[avg_money]";
			var money = $("#panel_return_goods input[name='"+money_el_name+"']").val();
			
			g_mx_json['return_goods'][mx_id] = {};
			g_mx_json['return_goods'][mx_id]['num'] = $(this).val();
			g_mx_json['return_goods'][mx_id]['money'] = money;
	    });
	    $("#panel_return_goods input[name$='[note_num]']").change(function(){
		    var mx_id = $(this).attr('name').replace("[note_num]","");
		    var money_el_name = mx_id+"[avg_money]";
		    var new_money = g_mx_json['return_goods'][mx_id]['money'] / g_mx_json['return_goods'][mx_id]['num'] * $(this).val();
		    new_money = Math.round(new_money*100)/100;
		    $("#panel_return_goods input[name='"+money_el_name+"']").val(new_money);
	    });	    
    }
    function init_change_goods_data(){
	    $("#panel_change_goods input[name$='[num]']").each(function(){
			var mx_id = $(this).attr('name').replace("[num]","");
			var money_el_name = mx_id+"[avg_money]";
			var money = $("#panel_change_goods input[name='"+money_el_name+"']").val();
			
			g_mx_json['change_goods'][mx_id] = {};
			g_mx_json['change_goods'][mx_id]['num'] = $(this).val();
			g_mx_json['change_goods'][mx_id]['money'] = money;			
	    });
	    $("#panel_change_goods input[name$='[num]']").change(function(){
		    var mx_id = $(this).attr('name').replace("[num]","");
		    var money_el_name = mx_id+"[avg_money]";
		    var new_money = g_mx_json['change_goods'][mx_id]['money'] / g_mx_json['change_goods'][mx_id]['num'] * $(this).val();
		    new_money = Math.round(new_money*100)/100;
		    $("#panel_change_goods input[name='"+money_el_name+"']").val(new_money);
	    });		    
    }
    
    //保存各部分详情
    function save_component(id){
	    var params = $("#panel_"+id+" form").serializeArray();
	    //console.log("panel_"+id);
	    //console.log(params);
        $.ajax({
             type: "post",
             url: "?app_fmt=json&app_act=fx/sell_return/save_component&type="+id+"&sell_return_code="+sell_return_code,
             data: params,
             success: function(ret){
	            ret = $.parseJSON(ret);
	            if(ret.status!='1'){
	                BUI.Message.Alert(ret.message,'error');
	            }else{
	                //刷新数据
	                component(id, "view");
	                if (id == 'return_goods' || id == 'change_goods'){
		                component('return_money', "view");
                        btn_check();
	                }
	            }
              }
         });
    }

    //删除退单商品
    function delete_detail(_id,_this){
        var id = $(_this).parent().attr("id");
        id = id.split('[');
        $.post("?app_act=fx/sell_return/delete_detail_by_id&app_fmt=json",{sell_return_detail_id:id[0]},function(ret){
            if(ret.status!='1'){
                BUI.Message.Alert(ret.message,'error');
            }else{
                component(_id, "edit");
            }
        },'json');

    }

    //删除换货单商品
    function delete_change_detail(_id,_this){
        var id = $(_this).parent().attr("id");
        id = id.split('[');
        $.post("?app_act=fx/sell_return/delete_change_detail_by_id&app_fmt=json",{sell_change_detail_id:id[0]},function(ret){
            if(ret.status!='1'){
                BUI.Message.Alert(ret.message,'error');
            }else{
                component(_id, "edit");
            }
        },'json');

    }
    
    //新增退单明细按钮
    get_goods_inv_panel({
        "id":"btn_add_return_goods",
        "param":{'store_code':forjs_data_json['return_store_code'], 'custom_code' : custom_code, 'sell_return_code' : sell_return_code},
        "callback":add_return_goods
    });

    function add_return_goods(obj){
         var data =top.skuSelectorStore.getResult();
   var select_data={};
   var di=0;
    BUI.each(data,function(value,key){
          if(top.$("input[name='num_"+value.goods_inv_id+"']").val()!=''&&top.$("input[name='num_"+value.goods_inv_id+"']").val()!=undefined){
            value.num = top.$("input[name='num_"+value.goods_inv_id+"']").val();
            if(value.num>0){
                    if(parseInt(value.num) > parseInt(value.available_mum)){
                        value.num = value.available_mum;
                    }
                    select_data[di] = value;
                     di++;
                }
            }
    });
        var _thisDialog = obj;
      if(di==0){
          _thisDialog.close();
          return ;
      }
        $.post('?app_fmt=json&app_act=fx/sell_return/add_return_goods&sell_return_code=' + sell_return_code + '&store_code=' + forjs_data_json['return_store_code'], {data: select_data,deal_code:$("#return_goods_deal_code").val()}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                  //  _thisDialog.remove(true);
                }, 'error');
            } else {
                //_thisDialog.close();
               // _thisDialog.remove(true);
                //tableStore.load();
                //form.submit();
            }
        if(typeof _thisDialog.callback == "function"){
                    _thisDialog.callback(this);
          }
        }, 'json');

    }
    //新增 换货商品
    function change_goods_add(){
    	//var url = "?app_act=fx/sell_return/add_change_goods_view&sell_return_code="+sell_return_code+ '&store_code=<?php echo $response['forjs_data']['sell_store_code'];?>'+'&deal_code='+$("#change_goods_deal_code").val();
    	var url = "?app_act=fx/sell_return/add_change_goods_view&sell_return_code="+sell_return_code+ '&store_code=<?php echo $response['forjs_data']['sell_store_code'];?>'+'&deal_code='+$("#change_goods_deal_code").val();
      // _do_execute(url, '','添加换货商品',1000,550);
        new ESUI.PopWindow(url, {
            title: '添加换货商品',
            width:750,
            height:500,
            onBeforeClosed: function() {
            	
            },
            onClosed: function(){
            	save_component('change_goods');
                
            }
        }).show()
    }
    /* 
    get_change_goods_inv_panel_t1({
        "id":""btn_add_change_goods"",
        "param":{store_code:forjs_data_json['sell_store_code'],sell_return_code:sell_return_code,deal_code:$("#change_goods_deal_code").val()},
        "callback":add_change_goods_call
    });
    function get_change_goods_inv_panel_t1(obj){
		var param = new Object();

		if(typeof obj.param != "undefined"){
			param = obj.param;
		}
		if(typeof(top.dialog_t1)!='undefined'){
			top.dialog_t1.remove(true);
		}
		top.BUI.use('bui/overlay',function(Overlay){
			 top.dialog_t1 = new Overlay.Dialog({
			    title: '添加换货商品',
			    width: '80%',
			    height: 550,
			    loader: {
			        url: '?app_act=fx/sell_return/add_change_goods_view',
			        autoLoad: true, //不自动加载
			        params: param, //附加的参数
			        lazyLoad: false, //不延迟加载
			        dataType: 'text'   //加载的数据类型
			    },
			    //mask: true,
			    buttons : [],
			    success: function () {
			    	if(typeof obj.callback == "function"){
			    		obj.callback(this);
			    	}
			    }
			});
			$("#"+obj.id).click(function(event) {
				top.dialog_t1.show();
			});
	    });
	}
	function add_change_goods_call(){
		alert(123);
	}
    //新增换货明细按钮
	
    get_goods_inv_panel_t1({
        "id":"btn_add_change_goods",
        "param":{'store_code':forjs_data_json['sell_store_code']},
        "callback":add_change_goods
    });

	function get_goods_inv_panel_t1(obj){
		var param = new Object();

		if(typeof obj.param != "undefined"){
			param = obj.param;
		}
		if(typeof(top.dialog_t1)!='undefined'){
			top.dialog_t1.remove(true);
		}
		top.BUI.use('bui/overlay',function(Overlay){
			 top.dialog_t1 = new Overlay.Dialog({
			    title: '选择商品',
			    width: '80%',
			    height: 400,
			    loader: {
			        url: '?app_act=prm/goods/goods_select_tpl_inv',
			        autoLoad: true, //不自动加载
			        params: param, //附加的参数
			        lazyLoad: false, //不延迟加载
			        dataType: 'text'   //加载的数据类型
			    },
			    mask: true,
			    success: function () {
			    	if(typeof obj.callback == "function"){
			    		obj.callback(this);
			    	}
			    }
			});
			$("#"+obj.id).click(function(event) {
				top.dialog_t1.show();
			});
	    });
	}
	
    function add_change_goods(obj){
       var data =top.skuSelectorStore.getResult();
     var select_data={};
     var di=0;
    BUI.each(data,function(value,key){
          if(top.$("input[name='num_"+value.goods_inv_id+"']").val()!=''&&top.$("input[name='num_"+value.goods_inv_id+"']").val()!=undefined){
            value.num = top.$("input[name='num_"+value.goods_inv_id+"']").val();
            select_data[di] = value;
            di++;
            }
    });
   if(di==0){
          _thisDialog.close();
          return ;
      }
        var _thisDialog = obj;
        $.post('?app_fmt=json&app_act=fx/sell_return/add_change_goods&sell_return_code=' + sell_return_code + '&store_code=' + forjs_data_json['sell_store_code'], {data: select_data,deal_code:$("#change_goods_deal_code").val()}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                   // _thisDialog.remove(true);

                    
                }, 'error');
            } else {
                //_thisDialog.close();
               // _thisDialog.remove(true);
                
                //tableStore.load();
                //form.submit();
            	save_component('change_goods');
            	//change_btn_status('change_goods');
            }
            _thisDialog.close();
           // location.reload();
        }, 'json');

    }*/

    $("#btn_add_change_goods_by_return_goods").click(function(){
        var ajax_url = '?app_fmt=json&app_act=fx/sell_return/add_change_goods_by_return_goods&sell_return_code='+sell_return_code;
        $.get(ajax_url, function(return_str){
            try{
                var return_json = $.parseJSON(return_str);
            }catch(e){
                alert('JSON数据解析出错：'+return_str);
                return;
            }
            if (return_json.status!=1) {
                alert(return_json.data);
            }else{
				component("change_goods", "view");
            }
        });
    });

function my_areaChange(el_name_append,parent_id,level,url, callback){
	$.ajax({ type: 'POST', dataType: 'json',
		url: url, data: {parent_id: parent_id},
		success: function(data) {
			var len = data.length;
			var html = '';

			switch(level){
				case 0:
					html = "<option value=''>请选择省</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#"+el_name_append+"_province").html(html);
					$("#"+el_name_append+"_city").html("<option value=''>请选择市</option>");
					$("#"+el_name_append+"_district").html("<option value=''>请选择区/县</option>");
					$("#"+el_name_append+"_street").html("<option value=''>请选择街道</option>");
					break;
				case 1:
					html = "<option value=''>请选择市</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#"+el_name_append+"_city").html(html);
					$("#"+el_name_append+"_district").html("<option value=''>请选择区/县</option>");
					$("#"+el_name_append+"_street").html("<option value=''>请选择街道</option>");
					break;
				case 2:
					html = "<option value=''>请选择区/县</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#"+el_name_append+"_district").html(html);
					$("#"+el_name_append+"_street").html("<option value=''>请选择街道</option>");
					break;
				case 3:
					html = "<option value=''>请选择街道</option>";
					for (var i = 0; i < len; i++) {
						html += "<option value='"+data[i].id+"'  >"+data[i].name+"</option>";
					}
					$("#"+el_name_append+"_street").html(html);
					break;
			}

			if(typeof callback == "function"){
				callback();
			}
		}
	});
}
//更新按钮状态
 function change_btn_status(id){
     $("#btn_edit_"+id).show();
     $("#btn_cancel_"+id).hide();
     $("#btn_save_"+id).hide();
     $("#btn_add_"+id).hide();
}
//退货明细编辑
function detail_edit(id){
    var item = $("#panel_return_goods table tbody").find(".detail_"+id);
    item.find(".edit").hide();
    item.find(".delete").hide();
    item.find(".save").show();
    item.find(".cancel").show();
    item.find("td[name=num]").find("input").show();
    item.find("td[name=num]").find("div").hide();
    item.find("td[name=avg_money]").find("input").show();
    item.find("td[name=avg_money]").find("span").hide();
    item.find("td[name=fx_amount]").find("input").show();
    item.find("td[name=fx_amount]").find("span").hide();
    //item.find("td").eq(8).find("input").removeAttr("disabled")
}
//退货明细保存
function detail_save(id){
    var item = $("#panel_return_goods table tbody").find(".detail_"+id);
    var params = {};
    params[id] = {
        "sell_return_code": sell_return_code,
        "sell_return_detail_id": id,
        "note_num": item.find("td[name=num]").find("input").val(),
        "deal_code": item.find("td[name=deal_code]").find("input").val(),
        "avg_money": item.find("td[name=avg_money]").find("input").val(),
        "fx_amount": item.find("td[name=fx_amount]").find("input").val()
    };
    //console.log(params);
    $.post("?app_fmt=json&app_act=fx/sell_return/save_component&type=return_goods&sell_return_code="+sell_return_code, params, function(data){
       //var data = $.parseJSON(data);
       if(data.status!='1'){
           BUI.Message.Alert(data.message,'error');
        }else{
            //console.log(data.data);
            //刷新数据
            component('return_goods', "view");
            component('return_money', "view");
    }
    }, "json")
}
//退货明细删除
function detail_delete(id){
    var msg = "<?php echo lang('op_delete_confirm');?>";
    BUI.Message.Confirm(msg,function(){
        var params = {"sell_return_code": sell_return_code, "sell_return_detail_id": id};
        $.post(
            "?app_act=fx/sell_return/delete_detail_by_id&app_fmt=json", 
            params,
            function(data){
                if(data.status == 1){
                    component('return_goods', "view");
                    component('return_money', "view");
                    //刷新按钮权限
                    //btn_check();
                } else {
                    BUI.Message.Alert(data.message,'error');
                }
            }, 
            "json"
        );
    });
}
//退货明细取消保存
function detail_cancel(id){
    var item = $("#panel_return_goods table tbody").find(".detail_"+id)
    item.find(".edit").show()
    item.find(".delete").show()
    item.find(".save").hide()
    item.find(".cancel").hide()
    item.find("td[name=num]").find("input").hide()
    item.find("td[name=num]").find("div").show()
    item.find("td[name=avg_money]").find("input").hide()
    item.find("td[name=avg_money]").find("span").show()
    item.find("td[name=deal_code]").find("input").hide()
    item.find("td[name=deal_code]").find("span").show()
    item.find("td[name=fx_amount]").find("span").show();
    item.find("td[name=fx_amount]").find("input").hide();
    item.find("td").eq(8).find("input").attr("disabled", true)
}

$("#btn_opt_print_return").click(function (){
    var u = '?app_act=tprint/tprint/do_print&print_templates_code=sell_return&record_ids='+sell_return_code;
    $("#print_iframe").attr('src',u);
});


/*$("#btn_opt_finish").click(function (){
    url = '?app_act=fx/sell_return/opt_finish';
    data = {sell_return_code: sell_return_code};
//    _do_operate(url, data, 'table');
//    
    $.post(url,data,function(ret){
        if(ret.status != '1'){
            BUI.Message.Alert(ret.message,'error');
        }else{
            location.reload();
        }
    },'json');
});*/
    //解密
    function show_safe_info(obj,sell_return_code,key){
        var url = "?app_act=oms/sell_return/get_record_key_data&app_fmt=json";
         $.post(url,{'sell_return_code':sell_return_code,key:key},function(ret){
             if(ret[key]==null){
                  BUI.Message.Tip('解密出现异常！', 'error');
                 return ;
             }
             $(obj).html(ret[key]);
             $(obj).attr('onclick','');
             $(obj).removeClass('like_link');
        },'json');
    }
</script>

<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>