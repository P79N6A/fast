<?php require_lib('util/oms_util', true); ?>
<style>
    .page_container{
        padding-bottom: 10px;
    }
    .bui-grid-header{ border-bottom:1px solid #dddddd;}    
    .bui-grid-body{ border-bottom:1px solid #dddddd;}
    .bui-grid-table .bui-grid-cell{ border-top:none; border-bottom:1px solid #dddddd;}
</style>

<div style=" color: red;margin-left: 15px;">
    <span style=" margin-right: 20px;">待付金额：<?php echo $response['data']['diff_money']; ?></span>
    <span>当前支付金额：<?php echo $response['current_payment_money']; ?></span>
</div>
<div  id="result_grid" class="panel-body"></div> 
<div style=" margin-left: 590px;">
    <input type="button" class="button button-primary" id = 'money_check'  value="确定">
</div>
<script>
    var current_money = '<?php echo $response['current_payment_money'];?>';
    var record_code_str = '<?php echo $request['record_code_str'];?>';
    var purMoneyStore;
    var page_size = 1000;
    var save_up;
    $(function () {
            BUI.use('bui/form',function (Form) {
                new Form.Form({
                    srcNode : '#JForm'
                }).render();
            });
         //下方结果表格
        BUI.use(['bui/grid', 'bui/data', 'bui/form','bui/tooltip'], function (Grid, Data, Form,Tooltip) {
            
            //数据变量
            var grid = new Grid.Grid();
            purMoneyStore = new Data.Store({
                url: '?app_act=pur/accounts_payable/get_by_page_record&list_type=set_payment_money&record_code_str='+record_code_str+"&current_payment_money="+current_money,
                autoLoad: true, //自动加载数据
                autoSync: true,
                pageSize : page_size,
            });
            var columns = [
                {title: '采购入库单编号', dataIndex: 'purchaser_record_code', width: 150, 'sortable': false},
                {title: '采购订单编号', dataIndex: 'planned_record_code', width: 150, 'sortable': false},
                {title: '待付金额', dataIndex: 'diff_money', width: 150, 'sortable': false},
                {title: '当前付款金额', dataIndex: 'current_payment_money',width: 108,'sortable':false, renderer : function(value,obj){
                    return '<input type="text" class="input-small input_money" name="money_'+obj.purchaser_record_code+'_'+obj.planned_record_code+'"   data-rules="{number:true,min:0}"  value='+obj.current_payment_money+'>';
                }},
            ];
            editing = new Grid.Plugins.CellEditing({
                triggerSelected: false //触发编辑的时候不选中行
            });
				 
               
            grid = new Grid.Grid({
                render: '#result_grid',
                columns: columns,
                idField: 'purchaser_record_code',
                store: purMoneyStore,
            });
            grid.render();
            
            var  errorTpl='<span class="x-icon x-icon-small x-icon-error" data-title="{error}">!</span>'; 
                     var    addPersonGroup = new Form.Group({ //创建表单分组，此分组不在表单form对象中，所以不影响校验
                              srcNode : grid.get('el'),
                              elCls:'',
                              //errorTpl : errorTpl,
                              showError : false,
                              defaultChildCfg : {
                                elCls : ''
                              }
                            });
                     addPersonGroup.render();
                      grid.on('itemrendered',function(ev){
                         itemEl = $(ev.element);
                        var input = itemEl.find('.input_money');
                        addPersonGroup.addChild({
                          xclass : 'form-field',
                         errorTpl : errorTpl,
                          srcNode : input
                        });
              
                    }); 
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
                            //回车切换
                            $('#result_grid input[type="text"]').keydown(function(event){
                             
                                if(event.keyCode == 13){
                                    var inputs = $('#result_grid input[type="text"]')
                                    var idx = inputs.index(this); 
                                    if(idx<inputs.length-1){
                                        inputs[idx+1].focus();
                                    }
                                }
                            });
                        
                         
                      });
                  
                  SelectoGrid = grid;
        })
        $('#money_check').click(function(){
            var record_data = purMoneyStore.getResult();
            var select_data = {};
            var di = 0;
            var sum_money = 0;
            BUI.each(record_data, function (value, key) {
                var money_name = 'money_' + value.purchaser_record_code + '_' + value.planned_record_code;
                if ($("input[name='" + money_name + "']").val() != '' && $("input[name='" + money_name + "']").val() != undefined) {
                    if ($("input[name='" + money_name + "']").val() > 0) {
                        value.current_payment_money = $("input[name='" + money_name + "']").val();
                             //console.log(value.current_payment_money);
                        sum_money = (parseFloat(sum_money) + parseFloat(value.current_payment_money)).toFixed(3);
                        select_data[di] = value;
                        di++;
                    }
                }
            });
            //console.log(sum_money);
            //console.log(current_money);
            if(sum_money != current_money) {
                BUI.Message.Alert('单据汇总付款金额与当前支付金额不一致','error');
                return false;
            }
            parent.save_info(select_data);
            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
        });
    });
//    function current_payment_money(value, row, index) {
//        return "<div id = '" + row.purchaser_record_code + '_' + row.planned_record_code + "'><a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_current_money("+'"'+value+'"'+","+'"'+row.purchaser_record_code+'"'+","+'"'+row.planned_record_code+'"'+")'>" + value + "</a></div>";
//    }
//    function set_current_money(money,purchaser_record_code,planned_record_code) {
//        var id = purchaser_record_code + "_" + planned_record_code;
//        $('#' + id).html("<input type = 'text' value = '" + money + "' style = 'width:60px;' onblur = 'save_current_money(this.value,"+'"'+purchaser_record_code+'"'+","+'"'+planned_record_code+'"'+","+'"'+money+'"'+")' name = 'money'>");
//        $('input[name=money]').focus();
//    }
//    function save_current_money(update_money, purchaser_record_code, planned_record_code, current_money) {
//        var id = purchaser_record_code + "_" + planned_record_code;
//        var a = /^[0-9]*(\.[0-9]{1,3})?$/;
//        if (!a.test(update_money) || update_money == undefined || update_money == '')
//        {
//            BUI.Message.Alert('金额格式不正确', 'error');
//            $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_current_money("+'"'+current_money+'"'+","+'"'+purchaser_record_code+'"'+","+'"'+planned_record_code+'"'+")'>" + current_money + "</a>");
//            return false;
//        }
//        $('#' + id).html("<a href='javascript:void(0)' style = 'text-decoration:underline' onclick='set_current_money("+'"'+update_money+'"'+","+'"'+purchaser_record_code+'"'+","+'"'+planned_record_code+'"'+")'>" + update_money + "</a>");
//    }
    
</script>

