<?php echo_print_plugin(1, 2)?>

<script type="text/javascript">
    var LODOP;
    var print_express = {

        print_express_start:function(deliver_record_ids) {
            var params = {deliver_record_ids:deliver_record_ids};

	        var url = "?app_act=oms/deliver_record/print_express&app_page=null&app_fmt=json";
            $.post(url,params,function(data){
                if(data.status < 0) {
                    alert(data.message);
                    return;
                }

	            if (0 == data.data.length){
		            alert('无订单信息!');
		            return;
	            }

                for(var i = 0; i < data.data.length; i++) {
	                print_express.do_print_express(data.data[i]);
                }
            }, "json");
        },

        print_express:function(sell_record_ids) {
            LODOP = getLodop();
            var printer_count = LODOP.GET_PRINTER_COUNT();
            if (printer_count < 1) {
                alert('该系统未安装打印设备,请添加相应的打印设备');
                return;
            }

	        print_express.print_express_start(sell_record_ids);
        },

        do_print_express:function( data ){

            if (data['shipping_code'] == '') {
                data['shipping_code'] = 'ems';
            }
            print_express.set_print_page(data, data['express_code']);
            LODOP.PRINT();
        },

        set_print_page:function(c, express) {
        <?php require_model("base/ShippingModel");;?>
        <?php
            //$mdl_express = new ShippingModel();
            //$express_list = $mdl_express->get_by_page(array());
            $express_list = oms_tb_all('base_express', array('status'=>'1'));
        ?>
            switch(express) {
            <?php foreach($express_list as $key=>$value):?>
                case '<?php echo $value['express_code']; ?>':
                    <?php
                    $value['print']=str_replace('"c[','c[',$value['print']);
                    $value['print']=str_replace(']"',']',$value['print']);
                    echo $value['print'];
                    ?>
                    <?php if( $value['printer_name'] ) {?>
                    LODOP.SET_PRINTER_INDEX("<?php echo $value['printer_name'];?>");
                    <?php }?>
                    break;
                <?php endforeach;?>
            }
        }
    }
</script>
