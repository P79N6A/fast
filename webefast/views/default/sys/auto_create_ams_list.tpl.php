<?php echo load_js('comm_util.js') ?>

<div><button class="button">数据初始化</button></div>


<div class="demo-content">
    <div class="row" style="height: 40px;" >
        <div class="span22">
            <div  style="float: right" >

                <select id="shop_code" <?php if ($response['init'] == 1)
    echo 'disabled="disabled"'; ?> >
                    <option value="">请选择</option>
                    <?php foreach ($response['shop_list'] as $val): ?>
                        <option value="<?php echo $val['shop_code'] ?>"  <?php if ($response['init'] == 1)
    echo 'selected="selected"'; ?> ><?php echo $val['shop_name'] ?></option>
<?php endforeach; ?>
                </select>

                <button class="button button-primary" id="init_all">一键初始化</button>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="span22">
            <table cellspacing="0" class="table table-bordered">
                <thead>
                    <tr>
                        <th width="15%" style="text-align: center">初始化项</th>
                        <th width="35%" style="text-align: center">说明</th>
                        <th width="25%" style="text-align: center">设置</th>
                        <th width="10%" style="text-align: center">操作</th>
                        <th width="20%" style="text-align: center">进度</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td  style="text-align: center;line-height: 75px;">初始化商品
                        </td>
                        <td  style="text-align:left">将淘宝的品牌、大类、规格、宝贝 全部下载到系统中，并初始化为系统的商品档案

                            注意：只初始化已设置商家编码的宝贝到商品资料中，请慎重，具体规则对应关系及规则，<a  href="#dd">请查看详细说明</a>
                        </td>

                        <td  style="text-align: center;line-height: 30px;">

                            <div id="tb_cats_list" style="display:none">
                                请选择店铺所属大类：


                                <div id="tb_cats_select">
                                    <input type="hidden" id="tb_cats" value="" name="tb_cats">
                                </div>

                            </div>
                        </td>

                        <td  style="text-align: center;line-height: 75px;"><button class="button button-primary" id="init_goods"  disabled="disabled">初始化</button></td>
                        <td  style="text-align: center;line-height: 80px;" id="goods">未开始</td>
                    </tr>
                    <tr>
                        <td  style="text-align: center;line-height: 75px;">初始化订单

                        </td>
                        <td  style="text-align:left">上线日期已设置为<span style="color:#0000FF"><?php echo $response['time']; ?></span> ，所以会将下单日期大于<?php echo $response['time']; ?>凌晨的所有订单初始化到系统中，便于确认及配货发货操作
                        </td>
                        <td></td>
                        <td  style="text-align: center"><button class="button button-primary"  id="init_order" disabled="disabled">初始化</button></td>
                        <td  style="text-align: center;line-height: 75px;" id="order">未开始</td>
                    </tr>
                    <tr>
                        <td  style="text-align: center;line-height: 75px;">初始化期初库存

                        </td>
                        <td style="text-align: center;line-height: 30px;">将淘宝的商品库存初始化到系统中的期初库存
                        </td>
                        <td  style="text-align:left;line-height: 35px;"> 请选择初始化库存的仓库：
                            <select id="store_code" class="input-small">
                                <?php foreach ($response['store_list'] as $key => $val): ?>
                                    <?php
                                    if ($key == 0) {
                                        $val['store_code'] = $val[0];
                                        $val['store_name'] = $val[1];
                                    }
                                    ?>
                                    <option value="<?php echo $val['store_code']; ?>"><?php echo $val['store_name']; ?></option>
<?php endforeach; ?>
                            </select>

                        </td>
                        <td  style="text-align: center"><button class="button button-primary" id="init_inv" disabled="disabled">初始化</button></td>
                        <td  style="text-align: center;line-height: 75px;" id="inv">未开始</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row" >
            <div class="span22">
                <div  style="float: right" >
<?php if ($response['init'] == 1): ?>
                        <button class="button button-primary" id="pre">上一部</button> <button class="button button-primary" id="ok">完成</button>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <input id="shop_type" type="hidden" value="" />
    <input id="goods_status" type="hidden" value="0" />
    <input id="order_status" type="hidden" value="0" />
    <input id="inv_status" type="hidden" value="0" />

    <input id="all_load" type="hidden" value="0" />
    <script>
      
        BUI.use('bui/select',function(Select){
  
            var items = <?php echo json_encode($response['tb_cat_list']) ?>,
            select = new Select.Select({
                render:'#tb_cats_select',
                valueField:'#tb_cats',
                multipleSelect:true,
                items:items
            });
            select.render();
        });
      
      
        $(function(){
            $('#pre').click( function(){
                if($('#goods_status').val()=='1'||$('#order_status').val()=='1'||$('#inv_status').val()=='1'){
                    BUI.Message.Alert('正在初始化，不能完成','error');
                }else{
                    window.location.href="?app_act=sys/auto_create/param_list";
                }
            }
            );
            $('#ok').click( function(){
                if($('#goods_status').val()=='1'||$('#order_status').val()=='1'||$('#inv_status').val()=='1'){
                    BUI.Message.Confirm('初始化尚未完成，确认进入系统',function(){
                        window.location.href="?app_act=index/do_index";
                    },'question');                    
                }else{
                    window.location.href="?app_act=index/do_index";
                }
            }
            ); 

            $('#shop_code').change(function(){
                get_init_info();
            });
        
            $('#init_all').click(function(){
            
                if($('#goods_status').val()=='0'||$('#order_status').val()=='0'||$('#inv_status').val()=='0'){
                    $('#all_load').val(1);
                    init_task(1);
                    init_task(2);
                    $('#init_all').attr('disabled',true); 
                }else{
                    BUI.Message.Alert('已经全部初始化','error');
                }   
            
            });
            $('#init_goods').click(function(){
                init_task(1);
            });
            $('#init_order').click(function(){
                init_task(2);
            });
            $('#init_inv').click(function(){
                init_task(3);
            });
            var load_status = 0;
            function init_task(type){
                var url = "?app_act=sys/auto_create/init_task&app_fmt=json";
                var data = {};
                data.type = type;
                data.shop_code = $('#shop_code').val();
                if(type==1&&$('#shop_type').val()=='C'){
                    if($('#tb_cats').val()==""){
                        BUI.Message.Alert('请选择初始化分类','error');
                        return  false;
                    }
                    data.tb_cats  = $('#tb_cats').val();
                }
                if(type==3){

                    if($('#store_code').val()==""){
                        BUI.Message.Alert('请设置初始化仓库','error');
                        return  false;
                    }
                    if($('#store_code').val()!=''){
                        data.store_code  = $('#store_code').val();
                    }
            
                }
            
                $.post(url,data,function(result){
                    if(result.status>0){
                        get_load_status();
                        if(type==1){
                            $('#init_goods').attr('disabled',true);
                            $('#init_inv').attr('disabled',true); 
                            $('#goods_status').val(1);  
                            set_load('goods');
                        }else if(type==2){
                            $('#init_order').attr('disabled',true); 
                            $('#order_status').val(1); 
                            set_load('order');
                        }else if(type==3){
                            $('#init_inv').attr('disabled',true);
                            $('#inv_status').val(1);  
                            set_load('inv');
                        }
                    
                    }else{
                        BUI.Message.Alert(result.message,'error');
                    }
                },'json');
            }
        
            function get_init_info(){
                var url = "?app_act=sys/auto_create/get_init_info&app_fmt=json";
                var data = {};
                var  shop_code = $('#shop_code').val();
                if(shop_code==''){
                    return ;
                }
          
                data.shop_code = shop_code;
                $.post(url,data,function(result){
                    $('#shop_type').val(result.data.tb_shop_type);
                    if(result.data.tb_shop_type=='C'){
                        $('#tb_cats_list').show();
                    }else{
                        $('#tb_cats_list').hide();
                    }
                    $('#init_goods').attr('disabled',true);
                    $('#init_order').attr('disabled',true);
                    $('#init_inv').attr('disabled',true);
                    var status = 0;
                    if(result.data.goods_status>0&&result.data.goods_status<3){
                        set_load('goods');
                        get_load_status();
                        status = 1;
                    }else if(result.data.goods_status==9){
                        BUI.Message.Alert(result.data.goods_message,'error');
                    }else if(result.data.goods_status==0){
                        $('#init_goods').attr('disabled',false);
                        $('#init_inv').attr('disabled',false);
                    }else if(result.data.goods_status==3){
                        $('#goods_status').val(2);
                        $('#goods').html('已经初始化');
                    }
               
                    if(result.data.order_status==1){
                        set_load('order');
                        get_load_status();
                        status = 1;
                    } if(result.data.order_status==9){
                        BUI.Message.Alert(result.data.order_message,'error');
                    }else if(result.data.order_status==0){
                        $('#init_order').attr('disabled',false);
                    }else if(result.data.goods_status==1){
                        $('#order_status').val(2);
                        $('#order').html('已经初始化');
                    }
                    if(result.data.inv_status>0&&result.data.goods_status<3){
                        set_load('inv');
                        get_load_status();
                        status = 1;
                    }else if(result.data.inv_status==9){
                        BUI.Message.Alert(result.data.inv_message,'error');
                    }else if(result.data.inv_status==0){
                        $('#init_inv').attr('disabled',false);
                    }else if(result.data.inv_status==3){
                        $('#inv_status').val(2);
                        $('#inv').html('已经初始化');
                    }
                    if(status==1){
                        $('#shop_code').attr('disabled',true);
                    }
               
                },'json');   
            
            }
            function get_load_status(){
                if(load_status==0){
                    load_status = 1;
                }else{
                    return ;
                }
                if($('#goods_status').val()=='2'&&$('#order_status').val()=='2'&&$('#inv_status').val()=='2'){
                    return ;
                }
  
                if(load_status>0){
                    var url = "?app_act=sys/auto_create/get_init_status&app_fmt=json";
                    var data = {};
                    data.shop_code = $('#shop_code').val();

                    if($('#goods_status').val()==1||$('#goods_inv').val()==1){
                        data.goods_load = $('#goods_load').html();
                    }
                    if($('#order_status').val()==1){
                        data.order_load = $('#order_load').html();
                    } 
                    data.goods_load = $('#goods_load').html();
                    
                    $.post(url,data,function(result){
                        if(result.status>0){
                            var goods_load = parseInt(result.data.goods_load);
                            var goods_status = parseInt(result.data.goods_status);
                            if($('#goods_status').val()<2){
                                $('#goods_load').html(goods_load);
                                if(goods_load==100){
                                    $('#goods_status').val(2);
                                    $('#init_inv').attr('disabled',false);
                                    if($('#all_load').val()==1){
                                        init_task(3);
                                    }
                            
                            
                                }
                            }else if($('#inv_status').val()<2){
                                $('#inv_load').html(goods_load);
                                if(goods_load==100){
                                    $('#goods_status').val(2);
                                }
                            }
                    
                            if($('#order_status').val()==1){
                                $('#goods_load').html(result.data.order_load);
                                if(result.data.order_status==2){
                                    $('#goods_order').val(2);
                                }
                            }     
                            if($('#goods_status').val()=='1'||$('#order_status').val()=='1'||$('#inv_status').val()=='1'){
                                load_status=0;
                                setTimeout(function(){ get_load_status();},2000);
                            }  
                      
                       
                        }else{
                            load_status = 0;
                            BUI.Message.Alert(result.message,'error');
                        }
                    },'json');
                }
            }
            function set_load(id){
                $('#'+id).html(' 已初始化 <span id="'+id+'_load">0</span>%');
            }
            get_init_info();
        

        });  
      
      
      
    </script>