
<script src="assets/js/CaiNiaoPrintFuncs.js"></script>

<object id="CaiNiaoPrint_OB" classid="clsid:09896DB8-1189-44B5-BADC-D6DB5286AC57" width=0 height=0> 
    <embed id="CaiNiaoPrint_EM" TYPE="application/x-cainiaoprint" width=0 height=0  ></embed>
</object> 
          <object id="CaiNiaoPrint_OB_2" classid="clsid:09896DB8-1189-44B5-BADC-D6DB5286AC57" width="100%" height="800px">
  <param name="Caption" value="内嵌显示区域">
  <param name="Border" value="1">
  <param name="Color" value="#C0C0C0">
  <embed id="CaiNiaoPrint_EM_2" TYPE="application/x-cainiaoprint" width="100%" height="800px">
</object>   

<input type="hidden" id="CP_CODE" value="<?php echo $response['tpl']['rm']['template_body']['cp_code'];?>"/>
<input type="hidden" id="express_type" value="<?php echo $response['tpl']['rm']['template_body']['express_type'];?>"/>

<input type="hidden" id="AppKey" value="<?php echo $response['tpl']['shop_info']['app_key'];?>" />
<input type="hidden" id="Seller_ID" value="<?php echo $response['tpl']['shop_info']['user_id'];?>"  />
<input type="hidden" id="self_body" value="<?php echo $response['tpl']['rm']['template_body']['self_body'];?>"  />


<script type="text/javascript">
var wave_print = "<?php echo $response['wave_print']; ?>";

    function over_print(){
        parent.$('#<?php echo  $request['iframe_id'] ;?>').remove();
    }

    <?php if(isset($response['message'])):?>
    parent.parent.BUI.Message.Alert('<?php echo $response['message']?>','error');
    over_print();
    <?php else:?>
    var LODOP; //声明为全局变量 

    var p_express = new print_express();
    var is_print = p_express.init(<?php echo json_encode($request);?>);
    if(is_print){
        p_express.get_data();
    }
    var new_clodop_print = <?php echo isset($request['new_clodop_print']) ? $request['new_clodop_print'] : 0;  ?>;
    function print_express(){
        _this = this;
        _this.param = {};
        _this.page = 1;
        _this.page_size = 5;
        _this.page_max=0;
        _this.num=0;
        _this.print_num = 0;
        _this.printer = '<?php echo empty($response['tpl']['printer'])?'':$response['tpl']['printer'];?>';
        this.init = function(param){
            _this.param = param;
            _this.param.page_size=_this.page_size;
         //   _this.param.page=_this.page;
            return this.set_lodop();
        }
        this.set_page = function(){
            _this.param.page=_this.param.page+1;
        }
        this.get_data = function(){
            $.post('?app_act=oms/deliver_record/get_print_express_data&app_fmt=json' ,_this.param , function (result) {
                if (result.status!=1) {//打印结束 或异常
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert(result.message,'error');
                    over_print();
                } else {
                    _this.print_data(result.data);
                }
            }, 'json');
        }
        this.print_data = function(data){
 
                var page_max = parseInt(data.filter.page_count);
               var now_page  = parseInt(data.filter.page);
                _this.num = data.filter.record_count;

            var print_status = false;
            //模版初始化
            var timestamp=new Date().getTime();
            
            for(var k in data.data){
                LODOP.NewPageA();
                print_status = this.set_print_data(data.data[k]);
                if(print_status!=true){
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('打印终止，打印模版异常','error');
                    over_print();
                    return false;
                }
                _this.print_num++;
            }
            if(print_status===false){
                  parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                  parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                  parent.parent.BUI.Message.Alert('打印处理出现异常,可能单据被取消','error');
                  over_print();
                  return false;
            }
            LODOP.SET_PRINTER_INDEX(_this.printer);
            LODOP.PRINT();
            //LODOP.PRINT_DESIGN();
            if(now_page<page_max){
                this.set_page();
                setTimeout(function (){_this.get_data();},3000);//延迟1秒
            }else{
                if(_this.print_num!=_this.num){
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('打印结束计划打印：'+_this.num+',实际打印'+_this.print_num,
                        function(){
                            parent.location.reload();
                        },
                        'error');
                    parent.location.reload();
                }else{
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('打印完成',function(){
                        if(wave_print == '1'){
                        	parent.location.reload();
                        }
                    },'info');

                }
            }
        }
        this.set_print_data = function(c){
            var print_status = false;
            try {
            var cp_code = $('#CP_CODE').val();
     
            LODOP.SET_PRINT_MODE("CAINIAOPRINT_MODE", "CP_CODE=" + cp_code + "&CONFIG=" + c['print_config']);      
            LODOP.ADD_PRINT_DATA("ProgramData", $('#self_body').val());
            
                //需要加参数
                LODOP.SET_PRINT_STYLEA("ali_waybill_cp_logo_up","PreviewOnly",<?php echo $response['tpl']['rm']['template_body']['ali_waybill_cp_logo_up']; ?>);
		LODOP.SET_PRINT_STYLEA("ali_waybill_cp_logo_down","PreviewOnly",<?php echo $response['tpl']['rm']['template_body']['ali_waybill_cp_logo_down']; ?>);
               //c['package_center_name']= '测试集散地';
              //  c['package_center_code']= '111222';
		LODOP.SET_PRINT_CONTENT("ali_waybill_product_type",$('#express_type').val());//单据类型
		LODOP.SET_PRINT_CONTENT("ali_waybill_short_address",c['receiver_top_address']);
		LODOP.SET_PRINT_CONTENT("ali_waybill_package_center_name",c['package_center_name']);//集散地名称
		LODOP.SET_PRINT_CONTENT("ali_waybill_package_center_code",c['package_center_code']);//集散地条码
		LODOP.SET_PRINT_CONTENT("ali_waybill_waybill_code",c['express_no']);//
		//LODOP.SET_PRINT_CONTENT("ali_waybill_cod_amount","FKFS=到付;PSRQ=2015-07-10");//服务
		LODOP.SET_PRINT_CONTENT("ali_waybill_consignee_name",c['receiver_name']); 
		LODOP.SET_PRINT_CONTENT("ali_waybill_consignee_phone",c['receiver_mobile']);
		LODOP.SET_PRINT_CONTENT("ali_waybill_consignee_address",c['receiver_address']);//收件人地址
		LODOP.SET_PRINT_CONTENT("ali_waybill_send_name",c['sender']); //
		LODOP.SET_PRINT_CONTENT("ali_waybill_send_phone",c['sender_phone']);
		LODOP.SET_PRINT_CONTENT("ali_waybill_shipping_address",c['sender_address']);
                LODOP.SET_PRINT_CONTENT("ali_waybill_shipping_branch_name",c['shipping_branch_name']);
                
                 LODOP.SET_PRINT_CONTENT("ali_waybill_shipping_address_city",c['receiver_city']);//发件城市：ali_waybill_shipping_address_city 中国邮政要求
                LODOP.SET_PRINT_CONTENT("ali_waybill_ext_send_date",c['print_time2']);//发件日期：1/2/2015 ali_waybill_ext_send_date (中通要求)
                if(c['ali_waybill_serv_cod_amount'] != undefined){
                    LODOP.SET_PRINT_CONTENT ("ali_waybill_service","ali_waybill_serv_cod_amount="+c['ali_waybill_serv_cod_amount']+";");//代收金额
                }
                //LODOP.SET_PRINT_CONTENT ("ali_waybill_service","ali_waybill_serv_cod_amount=100;");//代收金额
                <?php 
                
                foreach($response['tpl']['itemkey'] as $key):?>
                       LODOP.SET_PRINT_CONTENT("<?php echo $key;?>",c['<?php echo $key;?>']);
                 <?php endforeach;?>
              
             //$response['tpl']['deteil_key']
             <?php if(!empty($response['tpl']['detail_key'])):?>
                 var detail_tmp = '<?php echo $response['tpl']['detail_val']; ?>'; //str.replace(/\r\n/ig,"<br/>"); 
                 var detail_str ='';
                for(var i in c['detail']){
					var detail_tmp2 = detail_tmp;
                    <?php foreach ($response['tpl']['detail'] as $item):?>
                    detail_tmp2=detail_tmp2.replace('<?php echo $item;?>',c['detail'][i]['<?php echo $item;?>']);         
                    <?php endforeach;?>
                          detail_str+=detail_tmp2;
                    <?php if($response['tpl']['detail_row']==1):?>
                          detail_str+="\n";
                    <?php endif;?>
                }
                
                  LODOP.SET_PRINT_CONTENT("<?php echo $response['tpl']['detail_key'];?>",detail_str);
             <?php endif;?>
                  //     LODOP.SET_PRINT_CONTENT("detail:goods_name|detail:goods_code|detail:barcode",'11111111111111');
                 
                //receiver_city
		//LODOP.SET_PRINT_CONTENT("EWM","123456789012");
                /*
扩展：
服务：ali_waybill_service
到付金额：ali_waybill_serv_dest_amount EMS 邮政小包
返款周期：ali_waybill_serv_cod_arrival_days  德邦
签单返回：ali_waybill_serv_receipt_return_type  德邦

业务类型：ali_waybill_ext_sf_biz_type 顺丰
第三方地区：ali_waybill_ext_sf_third_area  
付款方式：ali_waybill_ext_payment_type 顺丰 寄付月结 转第三方支付
月结账号：ali_waybill_ext_custom_code
            */    
                
                print_status =  true;
             
            } catch (e) {
                print_status = false;
                alert(e.name+": "+ e.lineNumber +" "+ e.message);
            }
            return print_status;
        }
        this.set_lodop = function(){
            parent.parent.BUI.use('bui/overlay', function (Overlay) {
                var dialog = new Overlay.Dialog({
                    title: '快递单打印',
                    width: 300,
                    height: 130,
                    mask: true,
                    buttons: [
                        {
                            text: '',
                            elCls: 'bui-grid-cascade-collapse',
                            handler: function () {
                                  this.close();
                            }
                        }
                   ],
                   bodyContent: '打印进行中，请勿关闭页面！'
                });
                dialog.show();
            });
            var AppKey = $('#AppKey').val();
            var Seller_ID = $('#Seller_ID').val();
            if (AppKey == '' || Seller_ID == '') {
                  parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                  parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                  parent.parent.BUI.Message.Alert('对应快递模版参数异常','error');
                return false;
            }
              try {
                LODOP = getCaiNiaoPrint(document.getElementById('CaiNiaoPrint_OB_2'), document.getElementById('CaiNiaoPrint_EM_2'));
                LODOP.SET_PRINT_IDENTITY("AppKey=" + AppKey + "&Seller_ID=" + Seller_ID);//登陆appkey、seller_id 验证

                LODOP = getCaiNiaoPrint(document.getElementById('CaiNiaoPrint_OB_2'), document.getElementById('CaiNiaoPrint_EM_2'));
                LODOP.PRINT_INITA(0, 0, 400, 800, "云栈电子面单");


                var printer_count = LODOP.GET_PRINTER_COUNT();
                if (printer_count < 1) {
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('该系统未安装打印设备,请添加相应的打印设备','error');
                    return false;
                }
                //选择打印机
                var check_printer = 0;
                if(_this.printer!=''){
                    var c = LODOP.GET_PRINTER_COUNT();
                    for(var i = 0; i < c; i++) {
                        if(LODOP.GET_PRINTER_NAME(i)==_this.printer){
                            check = 1;
                            break;
                        }
                    }
                }
                if(_this.printer==''||check_printer==0){
                  //  LODOP.SELECT_PRINTER();
                    _this.printer =   LODOP.SELECT_PRINTER();//当前选择的打印机名称
                }
                if( _this.printer ==-1){
                      return false;
                }
                return true;
                } catch (e) {
                    no_install();
                return false;
               
            }
        }
    }
    <?php endif;?>

    function no_install(){
        var str_html = "<font color='#FF00FF'>打印组件未安装!<a href='http://www.taobao.com/market/cainiao/eleprint.php' target='_blank'>请点击这里</a>";
        parent.parent.BUI.Message.Alert(str_html,'error');
       
    }


</script>