<div class="page-header1" style="width: 98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc;">
	<span class="page-title"><h2>淘宝分销商品列表</h2></span>
	<span class="page-link">
	<span class="action-link">
    <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/down&app_show_mode=pop', '一键下载', {w:800,h:600})" class="button button-primary">

            一键下载</a>
        </span>
       <span class="action-link">

       <a href="javascript:PageHead_show_dialog('?app_act=oms/api_order/change&app_show_mode=pop', '一键转单', {w:800,h:600})" class="button button-primary">

            一键库存同步</a>
        </span>

        </span>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<style>
    #num_start,#num_end{
        width:60px;
    }
    .detail_main td{
        width:200px;
        text-align: right;
        padding:10px;
    }
	#sort{ margin-top:8px; font-size:12px; border-collapse:inherit; color:#666;}
    #sort td.sort_btn{
		border:1px solid #d5d5d5;
		padding:0 15px;
    	text-align:center;
    	cursor:pointer;
		height:24px;
		border-radius:3px;
		position:relative;
		
    }
    td#header{
    	padding:0 15px;
		text-align:center;
		height:24px;
		color:#ef8742;
		font-size:14px;
    }
</style>

<?php
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
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
//          'data' => ds_get_select('shop'),
			'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        
        array(
            'label' => '产品名称',
            'type' => 'input',
            'id' => ''
        ),
        
        array(
            'label' => '商品状态',
            'type' => 'select_multi',
            'id' => 'status'
        ),
        
        array(
            'label' => '产品商品编码',
            'type' => 'input',
            'id' => 'goods_code'
        ),
        array(
            'label' => '平台规格编码',
            'type' => 'input',
            'id' => 'barcode'
        ),
        
        
        array(
            'label' => '是否同步库存',
            'type' => 'select_multi',
            'id' => 'is_snyc'
        ),
        
    )
));
?>

<table id="sort" style="margin-bottom: 10px;">
    <tr>
        <td onclick = "sort(this)" id = "record_time" class="sort_btn">批量库存同步</td>
        <td onclick = "sort(this)" id = "pay_time" class="sort_btn">批量上架</td>
        <td onclick = "sort(this)" id = "plan_send_time" class="sort_btn">批量允许库存同步</td>
        <td onclick = "sort(this)" id = "is_notice_time" class="sort_btn">批量禁止库存同步</td>
    </tr>
</table>


<?php
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '同步',
                'field' => 'sell_record_code',
                'width' => '200',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value'=>"<a class=\"sell_record_view\" href=\"javascript:void(0)\">{sell_record_code}</a>",
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上架',
                'field' => 'deal_code_list',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'is_notice_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品ID',
                'field' => 'plan_send_time',
                'width' => '150',
                'align' => ''
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品商品编码',
                'field' => 'store_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '采购价(元)',
                'field' => 'express_code',
                'format_js'=> array('type'=>'map', 'value'=>$expressList),
                'width' => '80',
                'align' => '',
                //'editor'=>"{xtype : 'select', items: ".json_encode($expressList)."}"
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '是否有SKU',
                'field' => '',
                'width' => '100',
                'align' => '',
                //'editor' => "{xtype : 'text'}",
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存扣减模式',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台锁库存',
                'field' => '',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品状态',
                'field' => '',
                'width' => '100',
                'align' => ''
            ),
          
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_deliver_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
     'export'=> array('id'=>'exprot_list','conf'=>'deliver_record_list','name'=>'发货订单'),
    'CheckSelection' => true,
    'CascadeDetail' => 'show_detail',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    //'CellEditing' => true,
));
?>


<div id="searchAdv" style="display: none">
    <div class="row">
        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">生成波次</label>
            <div class="controls">
                <div class="button-group" id="b1" style="margin: 9px 0;">
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">扫描验货</label>
            <div class="controls">
                <div class="button-group" id="b2" style="margin: 9px 0;">
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">打印快递单</label>
            <div class="controls">
                <div class="button-group" id="b3" style="margin: 9px 0;">
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">打印发货单</label>
            <div class="controls">
                <div class="button-group" id="b4" style="margin: 9px 0;">
                </div>
            </div>
        </div>
        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">订单称重</label>
            <div class="controls">
                <div class="button-group" id="b5" style="margin: 9px 0;">
                </div>
            </div>
        </div>
<!--        <div class="control-group span8">
            <label class="control-label" style="padding-top:7px">临近发货</label>
            <div class="controls">
                <div class="button-group" id="b5" style="margin: 9px 0;">
                </div>
            </div>
        </div>-->
    </div>
</div>

<script type="text/javascript">
    function toolbarmaker(Toolbar, children, id) {
        var g = new Toolbar.Bar({
            elCls: 'button-group',
            itemStatusCls: {
                selected: 'active' //选中时应用的样式
            },
            defaultChildCfg: {
                elCls: 'button button-small',
                selectable: true //允许选中
            },
            children: children,
            render: '#' + id
        });
        g.render();
        g.on('itemclick', function(ev) {
            //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
        });
    }

    function show_detail(row) {
        var ret;
        var data = {
            'sell_record_code':row.sell_record_code,
            'app_tpl':'oms/deliver_record_detail',
            'app_page':'NULL'
        };
        $.ajax({
            type : "post",
            url : "?app_act=oms/sell_record/get_detail_by_sell_record_code",
            data : data,
            async : false,
            success : function(data){
                ret = data;
            }
        });
        return ret;
    }
</script>




<script type="text/javascript">
    var opts = [
        'opt_waves',
        'edit_express_code', 'edit_express_no',
        'opt_print_goods', 'opt_print_express', 'opt_print_sellrecord'
    ];

    $(document).ready(function(){
    	$("#kw_end").css("width","85px");
    	$("#kw_start").css("width","85px");
    	$("#num_start").css("width","85px");
    	$("#num_end").css("width","85px");
        tableStore.on('beforeload', function(e) {
            //e.params.ex_list_tab = $(".oms_tabs").find(".active").find("a").attr("id");
        })


        //全选
        $('.checkall:checkbox').click(function(){
            var c = $('[name=ckb_record_id]')
            c.prop("checked", !c.prop("checked"))
        })

        //初始化按钮
        btn_init();
        set_sell_record_view();
    })
    function set_sell_record_view(){
       tableGrid.on("aftershow",function(e){
           $('.sell_record_view').on('click',function(){
                    var url = '?app_act=oms/sell_record/view&sell_record_code=' +$(this).text()
                    openPage(window.btoa(url),url,'订单详情');
           });
       });
    }

    //初始化批量操作按钮
    function btn_init(){
        for(var i in opts){
            var f = opts[i]
            switch(f){
                case "opt_waves": btn_init_opt_waves(); break
                case "edit_express_code": btn_init_edit_express_code(); break
                case "edit_express_no": btn_init_edit_express_no(); break
                case "opt_print_goods": btn_init_opt_print_goods(); break
                case "opt_print_express": btn_init_opt_print_express(); break
                case "opt_print_sellrecord": btn_init_opt_print_sellrecord(); break
            }
        }
    }

    //读取已选中项
    function get_checked(isConfirm, obj, func){
        /*var ids = $("[name=ckb_record_id]:checkbox:checked").map(function(){
            return $(this).val()
        }).get()*/

        var ids = []
        var selecteds = tableGrid.getSelection();
        for(var i in selecteds){
            ids.push(selecteds[i].sell_record_code)
        }

        if(ids.length == 0){
            BUI.Message.Alert("请选择订单", 'error');
            return
        }

        if(isConfirm) {
            BUI.Message.Show({
                title : '自定义提示框',
                msg : '是否执行订单'+obj.text()+'?',
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

    //初始化生成波次
    function btn_init_opt_waves(){
        $(".btn_opt_waves").click(function(){
            //get_checked(true, $(this), function(ids){
            get_checked(false, $(this), function(ids){
                var params = {"sell_record_id_list": ids, is_check: 1};
                $.post("?app_act=oms/waves_record/create_waves", params, function(data){
                    if(data.status == 1){
                      //  BUI.Message.Alert(data.message, 'info')
                      waves_record_view(data.message,data.data);
                        //刷新
                        tableStore.load()
                    } else if(data.status == -2) {
                        BUI.Message.Show({
                            title : '自定义提示框',
                            msg : data.message,
                            icon : 'question',
                            buttons : [
                                {
                                    text:'是',
                                    elCls : 'button button-primary',
                                    handler : function(){
                                        var params = {"sell_record_id_list": ids, is_check: 0};
                                        var _self = this;
                                        $.post("?app_act=oms/waves_record/create_waves", params, function(data){
                                            if(data.status == 1){
                                               //BUI.Message.Alert(data.message, 'info');
   ;                                            waves_record_view(data.message,data.data);
                                                //刷新
                                                tableStore.load();
                                                _self.close();
                                            } else {
                                                _self.close();
                                                BUI.Message.Alert(data.message, 'error')
                                            }
                                        }, "json");
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
                        BUI.Message.Alert(data.message, 'error')
                    }
                }, "json");
            })
        });
    }

    function waves_record_view(message,id){
                  // BUI.Message.Confirm(message,function(){
                            var url = '?app_act=oms/waves_record/view&waves_record_id=' +id;
                            openPage(window.btoa(url),url,'波次拣货单');
                       // });
    }

    //批量修改配送方式
    function btn_init_edit_express_code(){
        $(".btn_edit_express_code").click(function(){
            get_checked(false, $(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list="+ids.toString(), {
                    title: "批量修改配送方式",
                    width:500,
                    height:250,
                    onBeforeClosed: function() {
                    },
                    onClosed: function(){
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
        })
    }

    //自动匹配物流单号
    function btn_init_edit_express_no(){
        $(".btn_edit_express_no").click(function(){
            get_checked(false, $(this), function(ids){
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_no&sell_record_id_list="+ids.toString(), {
                    title: "自动匹配物流单号",
                    width:800,
                    height:600,
                    onBeforeClosed: function() {
                    },
                    onClosed: function(){
                        //刷新数据
                        tableStore.load()
                    }
                }).show()
            })
        })
    }

    //打印商品明细
    function btn_init_opt_print_goods(){
        $(".btn_opt_print_goods").click(function(){
            get_checked(false, $(this), function(ids){
                //TODO:打印
                ids = ids.toString();

                var url = '?app_act=oms/sell_record/mark_sell_record_print';
                var params = {};
                params.record_ids = ids;
                $.post(url,params, function(data) {

                });


                var window_is_block = window.open('?app_act=sys/danju_print/do_print_record&app_page=null&print_data_type=order_sell_record&record_ids='+ids);
                if (null == window_is_block) {
                    alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                }
            })
        })
    }

    //打印快递单
    function btn_init_opt_print_express(){
        $(".btn_opt_print_express").click(function(){
            get_checked(false, $(this), function(ids){
                //TODO:打印
                ids = ids.toString();
                print_express.print_express(ids);
            })
        })
    }

    //打印发货单
    function btn_init_opt_print_sellrecord() {

    }

    function sort(_this) {
        $("#sort .sort_btn").css({"color":"#666"});
        $("#sort .sort_btn").removeClass("active");
    	$(_this).css({"color":"#ef8742"});
    	$(_this).addClass("active");
    	tableStore.load();
    }

</script>

<!-- 打印快递单公共文件 -->
<?php //include_once (get_tpl_path('oms/print_express'));?>