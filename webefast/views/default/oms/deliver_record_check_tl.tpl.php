<?php echo load_js('jquery.cookie.js') ?>

<style>
    .offset3 .span6{ font-size:16px; font-weight:bold;}
    .offset3 .span6 span{ color:#DC0404;}
    .green{color: #090; font-weight: bold; }
</style>
<style>
    .panel{height: 40px; line-height: 40px;}
    #deliver_detail {padding: 0}
    .panel-body{padding-top: 1px;}
    .panel-body table {margin: 0; }
    form.form-horizontal {
        position: relative;
        padding: 5px 0px 18px;
        overflow: hidden;
    }
    .form-horizontal .control-label {width: auto;}
    .span8 { width: auto; }
    .button.active{color:#FFF;background-color:#ec6d3a;border-color:#ec6d3a;}
    #express_no{width: 320px;height: 30px;}
    #barcode{width:320px;height: 30px; }
    .control-text{font-size: 25px;}
    .spant{margin-right: 9px;}
    #scan_num,#diff_num,#count_num{font-size: 20px;}
    .result{height: 48px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '扫描验货',
    'links' => array(
        array('url' => 'sys/params/do_list', 'title' => '扫描声音设置', 'is_pop' => false, 'pop_size' => '500,400',),
    ),
    'ref_table' => 'table'
));
?>

<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>

<form class="form-horizontal">
    <div class="panel">
        <div class="panel-body">
            <div id="searchAdv">
                <div class="row">
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:1px">扫描物流单后直接发货（受系统参数控制）</label>
                        <div class="controls">
                            <div class="button-group" id="b1" style="margin: 1px 0;">
                            </div>
                        </div>
                    </div>
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:1px">校验物流单是否重复</label>
                        <div class="controls">
                            <div class="button-group" id="b2" style="margin: 1px 0;">
                            </div>
                        </div>
                    </div>
<?php if ($response['lof_status'] == 1): ?>
                        <div class="control-group span8">
                            <label class="control-label" style="padding-top:1px">批次选择</label>
                            <div class="controls">
                                <div class="button-group" id="b3" style="margin: 1px 0;">
                                </div>
                            </div>
                        </div>
<?php endif; ?>
                  <div class="control-group span8">
                        <label class="control-label" style="padding-top:1px">自动打印质保书</label>
                        <div class="controls">
                            <div class="button-group" id="b4" style="margin: 1px 0;">
                            </div>
                        </div>
                    </div>   
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">物流单号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" style="height:40px;font-weight:bold;font-size:30px;" id="express_no">
                    </div>
                </div>
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">商品唯一码:</label>
                    <div class="controls">
                        <input type="text" class="control-text" style="height:40px;font-weight:bold;font-size:30px;" id="barcode">
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls result" style=" height: 50px">
                        <?php //if (load_model('sys/PrivilegeModel')->check_priv('oms/deliver_record/check#check_button')): // 检查权限配置 ?>
<!--                        <button type="button" class="button" id="btn-submit" style="VISIBILITY: hidden">直接发货</button>-->
<?php //endif; ?>
                        <button type="button" class="button" id="btn-clear">清除扫描记录</button>
                        <button type="button" class="button button-primary" id="btn-warranty" >打印质保书</button>
                        <div id="msg" style="color: #ff0000; font-weight: bold;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
<input id="sell_record_code_input" name="sell_record_code" type="hidden" />

<div class="panel">
    <div class="panel-body" id="deliver_detail">
        <br>
    </div>
</div>

<script type="text/javascript">
    var noScanSku = 0;
    var noIsDuplicate = 0;
    var lof_no_select = 0;
    var noIsWarranty = 0;
    var dialog;
    var barcode_scan_ok = '';
    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    //var unique_barcode = new Array();
    var unique_status = "<?php echo $response['unique_status']; ?>";
    var selectable = "<?php echo $response['selectable']; ?>";//控制扫描后直接发货
    var deliver_record_direct_ship = "<?php echo $response['deliver_record_direct_ship']; ?>";//控制扫描后直接发货
    var unique_flag = 0
    //播放提示音
    function play_sound(typ) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[typ] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }
    var g1, g2,g4;
    $(document).ready(function () {
        $("#barcode").attr("disabled", true)
        //$("#btn-submit").attr("disabled", true)
        $("#btn-clear").attr("disabled", true)
        $("#btn-warranty").attr("disabled", true)
        $("#express_no").focus()

        BUI.use('bui/toolbar', function (Toolbar) {
            //不扫条码直接发货
            g1 = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: selectable //允许选中
                },
                children: [
                    {content: '是', id: '1',},
                    {content: '否', id: '0', selected: true,}
                ],

                render: '#b1'
            })



            g1.render();
            g1.on('itemclick', function (ev) {
                noScanSku = ev.item.get('id')
            })




            //校验物流单是否重复
            g2 = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: true //允许选中
                },
                children: [
                    {content: '校验', id: '1', selected: true},
                    {content: '不校验', id: '0'}
                ],
                render: '#b2'
            })

            g2.render();

            g2.on('itemclick', function (ev) {
                noIsDuplicate = ev.item.get('id')
            })

                //自动打印质保单
                g4 = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: true //允许选中
                },
                children: [
                    {content: '是', id: '1',},
                    {content: '否', id: '0', selected: true,}
                ],

                render: '#b4'
            })
            g4.render();
            g4.on('itemclick', function (ev) {
                noIsWarranty = ev.item.get('id')
//                if(noIsWarranty==1){
//                    $('#btn-warranty').removeAttr("disabled");
//                }else{
//                    $("#btn-warranty").attr("disabled", true);
//                }
            })
        })
       


        
        // 自动扫描发货
        $("#express_no").keyup(function (event) {
            if (event.keyCode == 13) {
                unique_flag = 0;
                set_bar_status(false);
                //unique_barcode.splice(0,unique_barcode.length);
                var p = {express_no: $(this).val(), no_is_duplicate: noIsDuplicate,tl:1}
                $.post("?app_act=oms/deliver_record/check_detail_tl", p, function (data) {
                    if (data.status == 1) {

                        document.getElementById('sell_record_code_input').value = $(data.data).find('#sell_record_code').html();
                        $("#deliver_detail").html(data.data)
                        $("#express_no").attr("disabled", true)
                        //$("#btn-submit").removeAttr("disabled")
                        $("#btn-clear").removeAttr("disabled")
                        play_sound("success")
                        //$("#msg").html("")
                        if (noScanSku == 1) {
                            var check = false;
                            submit_it(check);
                        } else {
                            $("#barcode").removeAttr("disabled")
                            $("#barcode").focus()
                        }

                        //返回订单明细

                    } else {
                        messageBox(data.message)
                        $("#express_no").val("")
                    }
                }, "json")
            }
        })



        //get_sku_by_sub_barcode
        $("#barcode").keyup(function (event) {
            if (event.keyCode == 13) {
                var b = $(this).val().trim();

                    // try to find sub barcode.
                    $.post("?app_act=oms/deliver_record/get_sku_by_barcode", {deliver_record_id: $("#deliver_record_id").val(), sub_barcode: b,tl:1}, function (data) {
                        if (data.status == 1) {
                           // $('#table1').show();
                           
                                 addd_unique_row(data.data,data.type);
                                 
                                barcode_check(data.data.sku);
                      
                                _submit_it(data.data.deliver_record_detail_id, b);
                       
                        } else {
                            messageBox(data.message, 'error');
                        }
                    }, "json");
              
            }
        })

        //
        $("#btn-clear").click({is_record: 1}, scan_clear_func);

        function scan_clear_func(event) {
            var params = {is_record: event.data.is_record, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
            $.post("?app_act=oms/deliver_record/scan_clear", params, function (data) {
                if (data.status == -1) {
                    BUI.Message.Alert('清除失败！','error');
                }
            }, "json");
            set_bar_status(true);
            $("#deliver_detail").html("")
            $("#barcode").val("")
            $("#express_no").val("")
            $("#express_no").removeAttr("disabled")

            $("#barcode").attr("disabled", true)
           // $("#btn-submit").attr("disabled", true)
            $("#btn-clear").attr("disabled", true)
            $("#express_no").focus();
        }

        //
//        $("#btn-submit").click(function () {
//            var check = true;
//            submit_it(1);
//        })


        function _submit_it(deliverRecordDetailID, b) {
            var params = {deliver_record_id: $("#deliver_record_id").val(), deliver_record_detail_id: deliverRecordDetailID, barcode: b,is_unique:1};
            $.post("?app_act=oms/deliver_record/scan_detail", params, function (data) {
                if (data.status != 1) {
                    messageBox(data.message);
                } else {
                     $("#scan_num").html(data.data.scan_num);
                     $("#diff_num").html(data.data.all_num - data.data.scan_num);  
                    if( data.data.all_num==data.data.scan_num){
                          submit_it();
                    }

                }
            }, "json");
        }

        function insert_unique_barcode(unique_code) {
//            var sell_record_code = $('#sell_record_code').text();
//            var url = "?app_act=oms/deliver_record/insert_unique_barcode";
//            var param = {app_fmt: 'json', record_code: sell_record_code, unique_code: unique_code};
//            $.ajax({
//                type: "GET",
//                url: url,
//                async: false,
//                data: param,
//                success: function (json_data) {
//                }
//            });
        }

        function barcode_check(iSku){
  
              $("#barcode").val("");
              $("#scan_num").html(parseInt($("#scan_num").html()) + 1);
              $("#diff_num").html(parseInt($("#diff_num").html()) - 1);
              $("#msg").html("扫描成功");
              $("#barcode").focus();
                play_sound("success");

        }
        function submit_it() {
         
            var isOK =   $("#diff_num").text()==0 ?true:false;

            if (isOK) {
               
                var params = {is_record: 0,is_tl: 1,  deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
                if(noIsWarranty!=1){//自动打印
  
                    $('#btn-warranty').removeAttr("disabled");
                    $('#btn-warranty').click(function(){
                        do_print(sell_record_code);
                        //console.log(555);
                        clear_info();
                    });
                }
                $.post("?app_act=oms/deliver_record/check_action", params, function (data) {
                    if (data.status != 1) {
                        messageBox(data.message)
                    } else {
                        if(noIsWarranty==1){//自动打印
                            do_print(sell_record_code);
                            clear_info();
                         }
                        params = {is_record: 0,deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
                        $.post("?app_act=oms/deliver_record/scan_clear", params, function (data) {
                        }, "json");
                     

                        $("#msg").html("发货成功, 请继续");;
                        play_sound("success");
                    }
                }, "json");
            }

            //if(noScanSku == 0 && !isOK){
            //    messageBox("未完成商品扫描")
            //}
        }
        function clear_info(){
               set_bar_status(true);
                        $("#deliver_detail").html("");
                        $("#barcode").val("");
                        $("#express_no").val("");
                        $("#express_no").removeAttr("disabled");

                        $("#barcode").attr("disabled", true);
                        $("#btn-submit").attr("disabled", true);
                        $("#btn-clear").attr("disabled", true);
                        $("#express_no").focus();
        }
        
          //打印

    function  do_print(sell_record_code) {
        var u = '?app_act=sys/flash_print/do_print'
        u += '&template_id=211&model=oms/DeliverRecordWtyModel&typ=default&record_ids='+sell_record_code;
        window.open(u);
        }
    

        function messageErr() {
            var msgUrl = "?app_act=base/error_confirm_code/do_list";
            openPage(window.btoa(msgUrl), msgUrl, "错误确认码")
        }

        function messageBox(m) {
            BUI.use('bui/overlay', function (Overlay) {
                var msg = '<div style="text-align: center"><h2>' + m + '</h2><p class="auxiliary-text" style="padding-top:10px;"><input type="text" class="msg_code" value="" style="width:240px;" placeholder="请扫描错误确认码，如CONFIRM，以确认此错误"></p><p style="padding-top:10px;">提示：如没有错误确认码，请到<a href="javascript:openPage(window.btoa('+"'?app_act=base/error_confirm_code/do_list'"+'),'+"'?app_act=base/error_confirm_code/do_list'"+','+"'错误确认码'"+')">错误确认码</a>中打印以供扫描</p></div>';

                var dialog = new Overlay.Dialog({
                    title: '扫描错误',
                    width: 500,
                    height: 210,
                    bodyContent: msg, //配置DOM容器的编号
                    buttons: [{
                            text: '确定',
                            elCls: 'button button-primary',
                            handler: function () {
                                //do some thing
                                this.close();
                            }
                        }
                    ]
                });

                dialog.show();

                play_sound("error")

                dialog.on("closed", function (event) {
                    if ($("#barcode").attr("disabled") == 'disabled') {
                        $("#express_no").val("");
                        $("#express_no").focus();
                    } else {
                        $("#barcode").val("");
                        $("#barcode").focus();
                    }
                    dialog.close();
                })

                $(".msg_code").val("");
                $(".msg_code").focus();
                $(".msg_code").keyup(function (event) {
                    if (event.keyCode == 13) {
                        var len = $(this).val().length;
                        if ($(this).val() == 'CONFIRM' || len == 0) {
                            if ($("#barcode").attr("disabled") == 'disabled') {
                                $("#express_no").val("");
                                $("#express_no").focus();
                            } else {
                                $("#barcode").val("");
                                $("#barcode").focus();
                            }
                            dialog.close();
                        }
                    }
                });
            });
        }

        function check_lof_data(barcode) {
            var sell_record_code = $('#sell_record_code').text();
            var url = '?app_act=oms/deliver_record/get_oms_selll_recode_lof_info&app_fmt=json';
            var param = {};
            param.record_code = $('#sell_record_code').text();
            param.barcode = barcode;
            $.post(url, param, function (ret) {
                if (ret.data.results > 0) {
                    lof_select_box(ret.data.record, barcode);
                } else if (ret.data.results == 0) {
                    var check = false;
                    submit_it(check);
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, 'json');
        }



    });

    function set_bar_status(status) {
        if (deliver_record_direct_ship == 1) {
            BUI.each(g1.get('children'), function (elememt, index) {
                elememt.set('selectable', status);
            });
        }
        BUI.each(g2.get('children'), function (elememt, index) {
            elememt.set('selectable', status);
        });
    }



    function addd_unique_row(data,type){
        var sa_num = data.scan_num;
        sa_num = ++sa_num;
         if(type == 1){
            if(data.deliver_record_detail_id == $('.id_'+data.deliver_record_detail_id).val()){ 
              $(".sca_"+data.deliver_record_detail_id).html(sa_num);
            }else{
                var html = "    <tr>";
                html+=" <td>"+data.goods_code_name+"</td>";
                 html+=" <td>"+data.goods_code+"</td>";
                 html+=" <td>"+data.barcode+"</td>"; 
                 html+=" <td class=\"unique_code\"></td>";
                 html+=" <td></td>";
                 html+=" <td></td>";
                 html+=" <td></td>";
                   html+=" <td></td>";
                  html+=" <td  class=\"sku\" style=\"display:none\">"+data.sku+"</td>";
                  html+=" <td>"+data.num+"</td>";
                        html+=" <td ><span class=\"sca_"+data.deliver_record_detail_id+"\" >"+sa_num+"</span></td>";
                        html+=" <td><input type=\"hidden\" class=\"id_"+data.deliver_record_detail_id+"\" value = \""+data.deliver_record_detail_id+"\" /></td>";
                         html+=" </tr>";
                         $('#record_detail').prepend(html);
                } 
                
         }
         if(type == 2){
              var html = "    <tr>";
            html+=" <td>"+data.goods_code_name+"</td>";
             html+=" <td>"+data.goods_code+"</td>";
             html+=" <td>"+data.barcode+"</td>"; 
             html+=" <td class=\"unique_code\">"+data.unique_code+"</td>";
              html+=" <td>"+data.check_station_num+"</td>";
              html+=" <td>"+data.pri_diamond_weight+"</td>";
               html+=" <td>"+data.ass_diamond_weight+"</td>";
               html+=" <td>"+data.credential_weight+"</td>";
                html+=" <td  class=\"sku\" style=\"display:none\">"+data.sku+"</td>";
                 html+=" <td>1</td>";
                    html+=" <td>1</td>";
                     html+=" </tr>";
                     $('#record_detail').prepend(html);
         } 
    }
    
     
</script>

<div id="lof_select_box"   class="hide">

</div>