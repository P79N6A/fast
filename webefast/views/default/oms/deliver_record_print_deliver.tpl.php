
<?php echo_print_plugin()?>

<script type="text/javascript">
    function over_print(){
        parent.$('#<?php echo  $request['iframe_id'] ;?>').remove();
    }
    <?php if(isset($response['message'])):?>
    parent.parent.BUI.Message.Alert('<?php echo $response['message']?>','error');
    over_print();
    <?php else:?>
    var LODOP;
    var p_express = new print_express();
    p_express.init(<?php echo json_encode($request);?>);
    p_express.get_data();
    function print_express(){
        _this = this;
        _this.param = {};
        _this.page = 1;
        _this.page_size = 10;
        _this.page_max=0;
        _this.num=0;
        _this.print_num = 0;
        _this.printer = '<?php echo empty($response['tpl']['printer'])?'':$response['tpl']['printer'];?>';
        this.init = function(param){
            _this.param = param;
            _this.param.page_size=_this.page_size;
            _this.param.page=_this.page;
            this.set_lodop();
        }
        this.set_page = function(){
            _this.page++;
            _this.param.page=_this.page;
        }
        this.get_data = function(){
            $.post('?app_act=oms/deliver_record/get_print_deliver_record_data&app_fmt=json' ,_this.param , function (result) {
                if (result.status!=1) {//打印结束 或异常
                    parent.parent.BUI.Message.Alert('获取打印数据异常','error');
                    over_print();
                } else {
                    _this.print_data(result.data);
                }
            }, 'json');
        }
        this.print_data = function(data){
            if(_this.page==1){
                _this.page_max = data.filter.page_count;
                _this.num = data.filter.record_count;
            }
            //模版初始化
            LODOP.PRINT_INIT('打印发货单');
            <?php  if(isset($response['tpl']['top']))
                         echo $response['tpl']['top'];
             ?>
            for(var k in data.data){
                LODOP.NEWPAGEA();
                print_status = this.set_print_data(data.data[k]);
                if(print_status!=true){
                    parent.parent.BUI.Message.Alert('打印终止，打印模版异常','error');
                    over_print();
                    return false;
                }
                _this.print_num++;
            }
            LODOP.SET_PRINTER_INDEX(_this.printer);
            LODOP.PRINT();
            if(data.filter.page!=this.page){
                this.set_page();
                // setTimeout(function (){_this.get_data();},1000);//延迟1秒
            }else{
                if(_this.print_num!=_this.num){
                    parent.parent.BUI.Message.Alert('打印结束计划打印：'+_this.num+',实际打印'+_this.print_num,
                        function(){
                            parent.location.reload();
                        },
                        'error');
                    parent.location.reload();
                }else{
                    parent.parent.BUI.Message.Alert('打印完成',function(){
                        parent.location.reload();
                    },'info');

                }
            }
        }
        this.set_print_data = function(c){
            //console.log(c)
            var print_status = false;
            //try {
                switch(parseInt(c['print_templates_id'])){
                    <?php foreach($response['tpl']['data'] as $val):?>
                    case <?php echo $val['id']?>:
                    <?php echo $val['body'];?>
                    <?php endforeach;?>
                        print_status = true; break;
                    default:
                        print_status = false;
                }
                if(print_status){
                    if(typeof c['detail_tpl'] != 'undefined'){
                        LODOP.ADD_PRINT_HTML(c['detail_tpl']['top'],c['detail_tpl']['left'],c['detail_tpl']['width'],c['detail_tpl']['height'],c['detail_tpl']['html']);
                    }
                }
            //}catch (e) {
                //print_status = false;
            //}
            return print_status;
        }
        this.set_lodop = function(){
            LODOP = getLodop();
            var printer_count = LODOP.GET_PRINTER_COUNT();
            if (printer_count < 1) {
                parent.parent.BUI.Message.Alert('该系统未安装打印设备,请添加相应的打印设备','error');
                return;
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
                LODOP.SELECT_PRINTER();
                _this.printer =  LODOP.GET_VALUE('PRINTSETUP_PRINTER_NAME',1);//当前选择的打印机名称
            }

        }
    }
    <?php endif;?>
</script>