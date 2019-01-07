<style>
#skuSearchForm{ padding:0;}
#skuSearchForm .table th,
#skuSearchForm .table td{ border:none;}
#skuSearchForm .controls{ margin-left:0;}
.bui-grid-header{ border-bottom:1px solid #dddddd;}
.bui-grid-body{ border-bottom:1px solid #dddddd;}
.bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
.bui-grid-bbar{ border:none;}

.table{ margin-bottom:0;}
.bui-dialog a.bui-ext-close{ top:10px;}
.bui-dialog .bui-stdmod-header {padding:10px 15px;}
.bui-dialog .bui-stdmod-body {padding: 5px 15px;}
.bui-dialog .bui-stdmod-footer {padding:10px 15px;}
.form-horizontal .controls{ margin-top:0;}
.bui-grid-table .bui-grid-cell-inner{ padding:1px 0;}


</style>
<?php $result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2)); ?>
<div class="row">
    <div class="pull-left">
        <div class="well" style="margin-left: 1em;width:800px;">
            <form id="skuSearchForm" name="skuSearchForm">
                <div class="bui-simple-list bui-select-list" aria-disabled="false" aria-pressed="false" id="diy_goods_select">
                    <input type="hidden" name="store_code" value="<?php echo $request['store_code']?>">
                    <input type="hidden" name="record_code" value="<?php echo $request['record_code']?>">
                    <ul>
                        <?php if(!empty($response['diy_goods'])){?>
                            <?php foreach($response['diy_goods'] as $diy_goods){?>
                                <li class="bui-list-item">
                                    <span><input type="radio" class="diy-goods-select" name="diy_good" value="<?php echo $diy_goods['sku']?>" data-lof-no="<?php echo $diy_goods['lof_no'];?>">&nbsp; 商品名称：<?php echo $diy_goods['goods_name'];?>&nbsp; 条码：<?php echo $diy_goods['barcode'];?>&nbsp; 批次号：<?php echo $diy_goods['lof_no'];?></span>
                                </li>
                            <?php }?>
                        <?php }?>
                    </ul>
                 </div>
            </form>
        </div>
        <input type="hidden" id="djfield" name="djfield" >


        <div id="result_datatable" class="row" style="margin-left: 1em;" >

            <div id="result_grid" style="position:relative">
            </div>
            <div id="result_grid_pager"></div>
        </div>

    </div>

</div>

<script type="text/javascript">
    var form;
    var skuSelectorStore;
    var formListeners = {'beforesubmit': []};
    var save_up;
    var page_size=10;
    
    
    BUI.use('bui/calendar',function(Calendar){
     var datepicker = new Calendar.DatePicker({
         trigger:'.calendar',
         showTime:true,
         autoRender : true
        });
    });

    $(function (){
        $(".diy-goods-select").change(function (){
            var diy_good = $("input[name='diy_good']:checked").val();
            $("#skuSearchForm").submit();
        });
    })

    $(function () {
        BUI.use('bui/form',function (Form) {
            form = new BUI.Form.HForm({
            srcNode : '#skuSearchForm'
        }).render();
            form.on('beforesubmit',function(ev) {
                for (var i = 0; i < formListeners['beforesubmit'].length; i++) {
                    formListeners['beforesubmit'][i](ev);
                }     
                return false;
            });
        });
 
        //右下方结果表格++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        BUI.use(['bui/grid', 'bui/data', 'bui/form','bui/tooltip'], function (Grid, Data, Form,Tooltip) {
            //数据变量---------------------------------------------------------------
            var grid = new Grid.Grid();
                skuSelectorStore = new Data.Store({
                url : '?app_act=stm/stm_goods_diy_record/goods_diy_select_action_inv',
                autoLoad:false, //自动加载数据
                autoSync: true,
                pageSize:page_size	// 配置分页数目
            });

            formListeners['beforesubmit'].push(function(ev) {
                var obj = form.serializeToObject();
                obj.start = 1; //返回第一页
                obj.page = 1; obj.pageIndex = 0;
                $('table_datatable .bui-pb-page').val(1);
                var _pageSize = $('.bui_page_select').val();
                obj.limit = _pageSize; obj.page_size = _pageSize; obj.pageSize = _pageSize;
                skuSelectorStore.load(obj, function (data,params) {
                    $('.bui_page_select').val(_pageSize);
                });     
            });
            save_up =function(){
                var obj = form.serializeToObject();
                obj.page =$('#result_grid .bui-pb-page').val();
                obj.pageIndex = obj.page-1;
                skuSelectorStore.load(obj);
            } 
            var columns = [
                {title: '', dataIndex: 'goods_name', width: 80,'sortable':false, renderer : function(value,obj){
                    return '<input type="radio" class="input-small" name="sku_lof_'+obj.sku+'"  value="'+obj.sku+'"/>';
                }},
                {title: '商品名称', dataIndex: 'goods_name', width: 100,'sortable':false, renderer : function(value,obj){
                    if(value.length<7){
                         return value;
                    }else{
                        var newobj = {'name':value};
                        var objStr = BUI.JSON.stringify(newobj).replace(/\"/g,"'");
                        return '<span class="grid-goods_name" data-title="'+objStr+'">'+value.substr(0,7)+'</span>';
                    }
                }},
                {title: '商品编码', dataIndex: 'goods_code', width: 100,'sortable':false},
                {title: 'spec1_code', dataIndex: 'spec1_code', visible : false,width: 50,'sortable':false},
                {title: '<?php echo $response['goods_spec1_rename'];?>', dataIndex: 'spec1_name', width: 65,'sortable':false},
                {title: 'spec2_code', dataIndex: 'spec2_code', visible : false, width: 50},
                {title: '<?php echo $response['goods_spec2_rename'];?>', dataIndex: 'spec2_name', width: 65,'sortable':false},
                {title: '批次号', dataIndex: 'lof_no', width: 108,'sortable':false,renderer : function(value,obj){
                      if(value.length != 0){
                         return value;
                      }else{
                         return '<input type="text" class="input-small" name="lof_no" id="lof_no_'+obj.sku+'"/>';
                     } 
                }},
                {title : '生产日期',dataIndex :'production_date', width: 108,'sortable':false,renderer : function(value,obj){
                     if(value.length != 0){
                        return value;
                     }else{
                        return '<input type="text" class="calendar test" name="production_date"  id="production_date_'+obj.sku+'"/>';
                     }
                }},
                {title: '商品条形码', dataIndex: 'barcode', width: 120,'sortable':false}
        
            ];
            editing = new Grid.Plugins.CellEditing({
                triggerSelected: false //触发编辑的时候不选中行
            });			 
            // 单元格编辑输入回车或者选择通过验证之后会触发此事件。
            editing.on('accept',  function(record, editor) {
                // console.log(record);
                // console.log($(this));
                var value = record.record.lof_no;
                var production_date = record.record.production_date;
                var field = $("#djfield").val();
                if(field == 'lof_no'){
                    if(value != '' ){
                        $.ajax({ type: 'GET', dataType: 'json',
                            url: '<?php echo get_app_url('prm/goods/lof_exist');?>',
                            data: {production_date: production_date,lof_no:value},
                            success: function(ret) {
                                if(ret.status == 1){
                                    record.record.production_date = ret.data;
                                }
                                if(ret.status == 3){
                                   BUI.Message.Alert(ret.message, 'error');
                                }	   
                            }
                        });
                    }
                }	
            });
            grid = new Grid.Grid({
                render: '#result_grid',
                width: '100%', //如果表格使用百分比，这个属性一定要设置
                height: 352,

                columns: columns,
                idField: 'goods_code',
                store: skuSelectorStore,
               // bbar: {pagingBar: true},
                plugins: [editing]    
            });
            grid.on('cellclick',  function(record, field) {
                $("#djfield").val(record.field); 
            });
            var pagingBar = BUI.Toolbar.PagingBar;
            var gridPage = new pagingBar({
                render : '#result_grid_pager',
                elCls : 'image-pbar pull-right',
                store : skuSelectorStore,
                totalCountTpl : ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_select" style="width:50px;height:20px;"><option  value="5" >5</option><option selected="selected" value="10" >10</option><option  value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 '
            });
            gridPage.render();
    
            $('.bui_page_select').live('change',function(){
                   var num = parseInt($(this).val());
                   var obj = {
                        limit: num, 
                        page_size: num, 
                        pageSize: num, 
                        start: 1
                   };
                   page_size = num;
                   gridPage.set('pageSize', num);
                   skuSelectorStore.load(obj);
            });        
            var  errorTpl='<span class="x-icon x-icon-small x-icon-error" data-title="{error}">!</span>';      
            var  addPersonGroup = new Form.Group({ //创建表单分组，此分组不在表单form对象中，所以不影响校验
                srcNode : grid.get('el'),
                elCls:'',
                //  errorTpl : errorTpl,
                showError : false,
                defaultChildCfg : {
                  elCls : ''
                }
            });
            addPersonGroup.render();
            grid.render(); 
            grid.on('aftershow',function(ev){
                BUI.use('bui/calendar',function(Calendar){
                     var datepicker = new Calendar.DatePicker({
                       trigger:'.calendar',
                       //delegateTrigger : true, //如果设置此参数，那么新增加的.calendar元素也会支持日历选择
                       autoRender : true
                     });
                });
                var tips = new Tooltip.Tips({
                    tip : {
                      trigger :'.grid-goods_name', //出现此样式的元素显示tip
                      alignType : 'top', //默认方向
                      elCls : 'panel',
                      width: 200,
                      zIndex : '1000000',
                      titleTpl : ' <div class="panel-body">{name}</div>',
                      offset : 10
                    }
                });
                tips.render();
            });
        });
    });
</script>
