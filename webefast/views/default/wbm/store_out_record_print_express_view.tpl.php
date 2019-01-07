
<?php echo_print_plugin()?>
<?php if($request['new_clodop_print'] == 1){echo "<script src='http://127.0.0.1:8000/CLodopfuncs.js?proper=1'></script>";}?>
<script type="text/javascript">
    var wave_print = "<?php echo $response['wave_print']; ?>";
    var new_clodop_print = '<?php echo $request['new_clodop_print'];?>';
    function over_print(){
        parent.$('#<?php echo  $request['iframe_id'] ;?>').remove();
    }

    <?php if(isset($response['message'])):?>
    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
    parent.parent.BUI.Message.Alert('<?php echo $response['message']?>','error');
    over_print();
    <?php else:?>
    var LODOP;
    var p_express = new print_express();
    var istatus = p_express.init(<?php echo json_encode($request);?>);
    if(istatus===true){
        p_express.get_data();
    }
    function print_express(){
        _this = this;
        _this.param = {};
 
        _this.page_size = 5;
        _this.page_max=0;
        _this.num=0;
        _this.print_num = 0;
       if(new_clodop_print == 1){
            LODOP.SET_LICENSES("上海百胜软件","452547275711905623562384719084","","");
            _this.printer = '<?php echo empty($request['clodop_printer']) ? '' : $request['clodop_printer']; ?>';
        }else{
            _this.printer = '<?php echo empty($request['printer'])?'':$request['printer'];?>';
        }
        _this.printer_id = '';
        _this.printer_data = '' ;
    this.init = function(param){
           _this.param = param;
           _this.param.page_size=_this.page_size;
            if(new_clodop_print == 1){
                return true;
            } else {
                return this.set_lodop();
            }
        },
        this.set_page = function(){
            _this.param.page++;
        },
        this.get_data = function(){
            $.post('?app_act=wbm/store_out_record/get_print_express_data&app_fmt=json' ,_this.param , function (result) {
                if (result.status!=1) {//打印结束 或异常
                   // parent.parent.BUI.Message.Alert('获取打印数据异常','error');
                	parent.parent.BUI.Message.Alert(result.message,'error');
                    over_print();
                } else {
                    _this.print_data(result.data);
        
                    
                }
            }, 'json');
        },
//        this.new_page = function(){
//            var url = '?app_act=wbm/store_out_record/print_express_view&';
//            _this.param.printer =  _this.printer;
//            for(var key in  _this.param){
//                    url+="&"+key+"="+_this.param[key];
//            }
//            window.location.href = url;
//        };              
                
        this.print_data = function(data){
      
              var page_max = parseInt(data.filter.page_count);
              var now_page  = parseInt(data.filter.page);


            //模版初始化
            var timestamp=new Date().getTime();
            LODOP.PRINT_INIT('打印快递单'+timestamp);
            <?php  if(isset($response['tpl']['pt']))
                        echo $response['tpl']['pt']['top'];
                ?>
             var now_print_num = 0;     
            var print_status = false;
            for(var k in data.data){
                LODOP.NEWPAGEA();
                print_status = this.set_print_data(data.data[k]);
                if(print_status!=true){
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('打印终止，打印模版异常','error');
                    over_print();
                    return false;
                }
            <?php if(  $response['print_one']==1):?>
                     LODOP.SET_PRINTER_INDEX(_this.printer);
                     LODOP.PRINT();
			<?php endif; ?>
                _this.print_num++;
                now_print_num++;
            }
            if(print_status===false){
                  parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                  parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                  parent.parent.BUI.Message.Alert('打印处理出现异常,可能单据被取消','error');
                  over_print();
                  return false;
            }
            
          	<?php if($response['print_one']==0):?>
                LODOP.SET_PRINTER_INDEX(_this.printer);
                LODOP.PRINT();
			<?php endif; ?> 
       
            
            if(now_page<page_max){
                this.set_page();
                
                //20页刷新1次 防止内存泄漏
                if(_this.print_num==100){
                //    setTimeout(function (){_this.new_page();},3000);//延迟1秒
                    return ;
                }    
                
                
                setTimeout(function (){_this.get_data();},3000);//延迟1秒
            }else{
                    parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                    parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                    parent.parent.BUI.Message.Alert('打印完成',function(){
                    parent.parent.$(".bui-ext-mask").next('div').remove();                        
                    if(wave_print == '1'){
                            parent.location.reload();
                        }   
                    },'info');

             
            }
        },
        this.set_print_data = function(c){
            var print_status = false;
            try {
                    <?php echo $response['tpl']['pt']['body'];?>
                    print_status =  true;
            } catch (e) {
                print_status = false;
                //alert(e.name+": "+ e.lineNumber +" "+ e.message);
            }
            return print_status;
        },
        this.set_lodop = function(){
            LODOP = getLodop();
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
                        check_printer = 1;
                        break;
                    }
                }
            }
            if(_this.printer==''||check_printer==0){
                 var select_i = LODOP.SELECT_PRINTER();
                 if(select_i===-1){
                     return false;
                 }
                _this.printer =  LODOP.GET_VALUE('PRINTSETUP_PRINTER_NAME',1);//当前选择的打印机名称
            }
            return true;

        };
        this.is_print_ok = function(){
            if(_this.printer_id==''){
                return true;
            }else{
                return LODOP.GET_VALUE('PRINT_STATUS_OK',_this.printer_id);
            }
        };
        this.is_print_now = function(){
              var  status = _this.is_print_ok();
              if(status){
                  //打印当前内容
                    _this.print_data(_this.printer_data);
              }else{
                   // _this.print_time = _this.print_time+1000;
		
                    var is_pr = LODOP.GET_VALUE('PRINT_STATUS_EXIST',_this.printer_id);
							   alert('tt:'+is_pr);
                    if(is_pr){
                        setTimeout(function (){ _this.is_print_now(); },1000);
                    }else{
                          parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
                          parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
                          parent.parent.BUI.Message.Alert('打印异常终止','error');
                    }
  
              }
            
        };
        this.wait_next_print = function(){
          var  status = _this.is_printing();
          if(status==1){
                _this.get_data();
          }else if(status == 0){
             setTimeout(function (){ _this.wait_next_print(); },1000);
          }else{
              parent.parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
              parent.parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
              parent.parent.BUI.Message.Alert('打印异常终止','error');
              return false;
          }
        };
    }
    <?php endif;?>
</script>