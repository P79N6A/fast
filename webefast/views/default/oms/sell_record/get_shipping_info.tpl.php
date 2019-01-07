




<div id="result_datatable" class="row" style="" >

    <div id="shping_grid" style="position:relative">
    </div>
    <div id="result_grid_pager"></div>
</div>

<script type="text/javascript">
    var order_status = '<?php echo $response['record']['order_status']; ?>';
    var record_code = "<?php echo $response['record']['sell_record_code'] ?>";
    var country_list = <?php
$country_list = oms_tb_all('base_area', array('type' => '1'));
echo json_encode($country_list, true)
?>;
    var select_city = "<?php echo $response['record']['receiver_city']; ?>";
    var select_district = "<?php echo $response['record']['receiver_district']; ?>";
    var select_street = "<?php echo $response['record']['receiver_street']; ?>";
    var select_name = "<?php echo $response['record']['receiver_name']; ?>";
    var select_mobile = "<?php echo $response['record']['receiver_mobile']; ?>";
    var select_phone = "<?php echo $response['record']['receiver_phone']; ?>";
    var receiver_addr = "<?php echo $response['record']['receiver_addr']; ?>";
    var customer_code = "<?php echo $response['record']['customer_code']; ?>";
    var customer_address_id = "<?php echo isset($response['record']['customer_address_id'])?$response['record']['customer_address_id']:0; ?>";
    var sort = {address: receiver_addr, city: select_city, district: select_district, street: select_street, name: select_name, tel: select_mobile, home_tel: select_phone};
    BUI.use(['bui/grid', 'bui/data'], function (Grid, Data) {
        var Grid = Grid,
                columns = [
                    {
                        title: '收货人',
                        dataIndex: 'name',
                        width: 100,
                        sortable: false,
                        renderer: function (value, obj) {
                            var select = "";
                            if(customer_address_id>0){
                                if( obj.customer_address_id==customer_address_id){
                                    select = "radio_select";
                                }
                            }else{
                                if (obj.city == select_city && obj.district == select_district && (obj.street == select_street || select_street==0) && value == select_name && obj.tel == select_mobile && select_phone == obj.home_tel && receiver_addr == obj.address) {
                                    select = "radio_select";
                                }
                            }
                            return '<input class="receiver_name '+ select + '"  disabled="disabled"     name="receiver_name" style="width:80px;" c_id="' + obj.customer_address_id + '"  type="text" value="' + value + '" />';
                        }
                    },
                    {
                        title: '国家',
                        dataIndex: 'country',
                        width: 110,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<select name="country"  disabled="disabled"  title="' + value + '"    style="width:100px;display:none;" class="country"> <option value ="">国家</option> </select>' + '<span class="country_name">' + obj.country_name + '</span>';
                        }
                    },
                    {
                        title: '省份',
                        dataIndex: 'province',
                        width: 110,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<select name="province"  disabled="disabled"  title="' + value + '"    style="width:100px;display:none;" class="province"> <option value ="">省</option> </select>' + '<span class="province_name">' + obj.province_name + '</span>';
                        }

                    },
                    {
                        title: '城市',
                        dataIndex: 'city',
                        width: 110,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<select name="city" disabled="disabled"   title="' + value + '"    style="width:100px;display:none;" class="city"> <option value ="">城市</option> </select>' + '<span class="city_name">' + obj.city_name + '</span>';
                        }
                    },
                    {
                        title: '区/县',
                        dataIndex: 'district',
                        width: 110,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<select name="district" disabled="disabled"   title="' + value + '"    style="width:100px;display:none;" class="district"> <option value ="">区/县</option> </select>' + '<span class="district_name">' + obj.district_name + '</span>';
                        }
                    },
                    {
                        title: '街道',
                        dataIndex: 'street',
                        width: 110,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<select disabled="disabled"  name="street"  title="' + value + '"    style="width:100px;display:none;" class="street"> <option value ="">区/县</option> </select>' + '<span class="street_name">' + obj.street_name + '</span>';
                        }
                    },
                    {
                        title: '详细地址',
                        dataIndex: 'address',
                        width: 150,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<input  class="receiver_addr"   name="receiver_addr"  style="width:130px;"  type="text" value="' + value + '" disabled="disabled"  />';
                        }
                    },
                    {
                        title: '手机',
                        dataIndex: 'tel',
                        width: 120,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<input    class="receiver_mobile"   name="receiver_mobile" style="width:100px" type="text" value="' + value + '" disabled="disabled"  />';
                        }
                    },
                    {
                        title: '固定电话',
                        dataIndex: 'home_tel',
                        width: 100,
                        sortable: false,
                        renderer: function (value, obj) {
                            return '<input   class="receiver_phone"   name="receiver_phone" style="width:100px" type="text" value="' + value + '" disabled="disabled" />';
                        }
                    },
                        {
                        title: '操作',
                        dataIndex: 'customer_address_id',
                        width: 78,
                        sortable: false,
                        renderer: function (value, obj) {
                            <?php if ($response['record']['order_status'] == 0): ?>
                            return '<a href="javascript:void(0);" onclick="edit_address(this,'+value+');">编辑</a><input type="hidden" class="customer_address_id" value="' + value + '">';
                            <?php else:?>
                                return '';
                            <?php endif;?>
                        }
                    }
                ],
                shippingStore = new Data.Store({
                    url: '?app_act=crm/customer/get_by_page_address&customer_code=<?php echo $response['record']['customer_code'] ?>',
                    autoLoad: true, //自动加载数据
                    autoSync: true,
                    params: {sort: sort},
                    pageSize: 10	// 配置分页数目
                });

        grid = new Grid.Grid({
            render: '#shping_grid',
            columns: columns,
            store: shippingStore,
            plugins: [Grid.Plugins.RadioSelection]	// 插件形式引入单选表格
        });
        var pagingBar = BUI.Toolbar.PagingBar;
        var gridPage = new pagingBar({
            render: '#result_grid_pager',
            elCls: 'image-pbar pull-right',
            store: shippingStore,
            totalCountTpl: ' 共{totalCount}条记录 每页<select name="bui_page_size" class="bui-pb-page bui_page_select" style="width:50px;height:20px;"><option  value="5" >5</option><option selected="selected" value="10" >10</option><option  value="20" >20</option><option  value="50" >50</option><option  value="100" >100</option><option  value="200" >200</option><option  value="500" >500</option><option  value="1000" >1000</option></select>条 ',
        });
        gridPage.render();

        $('.bui_page_select').live('change', function () {
            var num = parseInt($(this).val());
            var obj = {
                limit: num,
                page_size: num,
                pageSize: num,
                start: 1
            };
            gridPage.set('pageSize', num);
            shippingStore.load(obj);
        });

        shippingStore.on('load', function (ev) {


            setTimeout(function () {
                if ($(".radio_select").length > 0) {
                    $(".radio_select").parent().parent().parent().parent().find("input[type='radio']").click();

                }
                //init_shipping();
            }, 10);



        });

//        grid.on('selectedchange', function (ev) {
//            //change_address();
//         //   init_shipping();
//        });
        grid.render();
    });
    var country_str = '';
    function init_shipping() {

        if (order_status != 0) {
            $('#shping_grid').find('input').attr('disabled', true);
            $('#shping_grid').find('select').attr('disabled', true);
        }
        //      alert( $('#shping_grid .bui-grid-body .bui-grid-row').eq(0).html());

        $.each($('#shping_grid .bui-grid-body').find('.bui-grid-row'), function (i, line) {

            init_address(line);
        });

    }
    function edit_address(obj,address_id){
          init_shipping();
         var td_obj =  $(obj).parent().parent().parent().parent();
        if(customer_address_id>0){
            var url = "?app_act=oms/sell_record/get_edit_address&app_fmt=json";
            var param = {customer_address_id:address_id,customer_code:customer_code};
            param.record_code=record_code;
            $.post(url,param,function(ret){
                td_obj.find('input.receiver_name').val(ret.data.name);
                td_obj.find('input.receiver_phone').val(ret.data.home_tel);
                td_obj.find('input.receiver_mobile').val(ret.data.tel);
                td_obj.find('input.receiver_addr').val(ret.data.address);
            },'json');
        }     
         td_obj.find("input").attr('disabled',false);     
         td_obj.find("select").attr('disabled',false);   
         td_obj.find('input.customer_address_id').val(0);
     

    }


    function init_address(line) {
        var checked = $(line).find("input[type='radio']:checked").val();
        if (!checked) {
            return;
        }

        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        if (country_str == '') {
            $.each(country_list, function (k, val) {
                country_str += '<option value="' + val['id'] + '"  >' + val['name'] + '</option>';
            });
        }

        $(line).find('.country').html(country_str);
        $(line).find('.country').css('display', '');
        $(line).find('.country_name').css('display', 'none');
        var country_v = $(line).find('.country').attr('title');
        $(line).find('.country option[value="' + country_v + '"]').attr('selected', true);

        $(line).find('.country').change(function () {
            var parent_id = $(this).val();
            var line = $(this).parent().parent().parent().parent();
            multiAreaChange(parent_id, 0, url, line);
        });
        $(line).find('.country').change();


        $(line).find('.province').change(function () {
            var parent_id = $(this).val();
            var line = $(this).parent().parent().parent().parent();
            multiAreaChange(parent_id, 1, url, line);
        });

        //setTimeout(function(){  $(line).find('.province').change();},800);
        $(line).find('.province').change();

        $(line).find('.city').change(function () {
            var parent_id = $(this).val();
            var line = $(this).parent().parent().parent().parent();
            multiAreaChange(parent_id, 2, url, line);
        });
        //setTimeout(function(){ $(line).find('.city').change();},1600);
        $(line).find('.city').change();

        $(line).find('.district').change(function () {
            var parent_id = $(this).val();
            var line = $(this).parent().parent().parent().parent();
            multiAreaChange(parent_id, 3, url, line);
        });

        //setTimeout(function(){ $(line).find('.district').change(); },2400);
        $(line).find('.district').change();


    }



</script>



<form class="form-horizontal well" id="shipping_type" style="padding: 5px 0;">
    <div class="control-group">
        <label class="control-label">支付方式：</label>
        <div class="controls"><?php echo $response['record']['pay_name']; ?></div>

        <label class="control-label">仓库：</label>
        <div class="controls"><select style="width:160px;" name="store_code_shipping" id="store_code_shipping" <?php if ($response['record']['order_status'] != 0) { ?>
                                      disabled="disabled"
                                          <?php } ?>>
                                          <?php
                                          //$list = oms_tb_all('base_store', array());
                                          $list = load_model('base/StoreModel')->get_purview_store();
                                          foreach ($list as $k => $v) {
                                              ?>
                    <option value="<?php echo $v['store_code'] ?>" <?php if ($response['record']['store_code'] === $v['store_code']) echo "selected='selected'" ?>><?php echo $v['store_name'] ?></option>
<?php } ?>
            </select>
        </div>

        <label class="control-label">配送方式：</label>
        <div class="controls"><select style="width:160px;" name="express_shipping" id="express_shipping" <?php if ($response['record']['order_status'] != 0) { ?>
                                          disabled="disabled"
                                          <?php } ?>>
                                          <?php
                                          $list = oms_tb_all('base_express', array('status' => 1));
                                          foreach ($list as $k => $v) {
                                              ?>
                    <option value="<?php echo $v['express_code'] ?>" <?php if ($response['record']['express_code'] == $v['express_code']) echo "selected='selected'"; ?>><?php echo $v['express_name'] ?></option>
<?php } ?>
            </select>
        </div>

        <label class="control-label">运费：</label>
        <div class="controls">
            <input id="shipping_fee" <?php if ($response['record']['order_status'] != 0) { ?>
                       disabled="disabled"
<?php } ?> class="input-normal control-text" type="text" value="<?php echo sprintf("%.2f", $response['record']['express_money']); ?>">
        </div>
    </div>
    <!--<div style="clear:both;"></div>
    <div class="control-group">

    </div>
    <div style="clear:both;"></div>
    <div class="control-group">

    </div>
    <div style="clear:both;"></div>
    <div class="control-group">

    </div>
    <div style="clear:both;"></div>-->
</form>
<div class="control-group">
    <button class="button button-small" <?php if ($response['record']['order_status'] != 0) { ?>
                disabled="disabled"
            <?php } ?> id="btn_save_shipping_info"><i class="icon-ok"></i>保存</button>
    <button class="button button-small" <?php if ($response['record']['order_status'] != 0) { ?>
                disabled="disabled"
<?php } ?> id="btn_cancel_shipping_info"><i class="icon-ban-circle"></i>重置</button>
</div>

<script type="text/javascript">

    $("#btn_save_shipping_info").on("click", function () {
        var params = {"data": {}, "app_fmt": 'json'};
        params.data['radio_checked'] = "";
        if ($("#shping_grid input[type='radio']:checked").length > 0) {
            var select_line = $("#shping_grid input[type='radio']:checked").parent().parent().parent().parent();


            params.data['radio_checked'] = "checked";
            params.data["receiver_name"] = $(select_line).find(".receiver_name").val();
            params.data["receiver_country"] = $(select_line).find(".country").val();
            params.data["receiver_province"] = $(select_line).find(".province").val();
            params.data["receiver_city"] = $(select_line).find(".city").val();
            params.data["receiver_district"] = $(select_line).find(".district").val();
            params.data["receiver_street"] = $(select_line).find(".street").val();
            params.data["receiver_addr"] = $(select_line).find(".receiver_addr").val();
            params.data["receiver_mobile"] = $(select_line).find(".receiver_mobile").val();
            params.data["receiver_phone"] = $(select_line).find(".receiver_phone").val();
            params.data["customer_address_id"] = $(select_line).find(".customer_address_id").val();
        }


        params.data["store_code"] = $("#store_code_shipping").val();
        params.data["express_code"] = $("#express_shipping").val();
        params.data["express_money"] = $("#shipping_fee").val();
        //params.data["sell_record_code"] = $("#goods_info_detail .good_oms_sell_code").val();
        params.data["sell_record_code"] = "<?php echo $response['record']['sell_record_code'] ?>";


        var ajax_url = '?app_act=oms/sell_record/update_shipping_info';
        $.post(ajax_url, params, function (data) {
            if (data.status == 1) {
                BUI.Message.Show({msg: '修改成功',icon: 'success',buttons: [],autoHide: true});
                /*
                 var p = {};
                 p.store_code = params.data["store_code"];
                 update_panel_params(p);*/
                component("money,detail,action,shipping,base_order_info,inv_info,goods_detail,shipping_info", "view");
                /*
                 component("money", "view");
                 component("detail", "view");
                 component("action", "view");
                 component("shipping", "view");
                 component("base_order_info", "view");
                 component("inv_info", "view");
                 component("goods_detail", "view");
                 component("shipping_info", "view");*/


            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, 'json');
    })
    $("#btn_cancel_shipping_info").on('click', function () {
        component("money,detail,action,base_order_info,inv_info,goods_detail,shipping_info", "view");
        /*
         component("money", "view");
         component("detail", "view");
         component("action", "view");
         component("base_order_info", "view");
         component("inv_info", "view");
         component("goods_detail", "view");
         component("shipping_info", "view");*/
    })


    //区域联动
    function  multiAreaChange(parent_id, level, url, line, callback) {
        $.ajax({type: 'POST', dataType: 'json', async: false,
            url: url, data: {parent_id: parent_id}, cache: true,
            success: function (data) {
                var len = data.length;
                var html = '';

                switch (level) {
                    case 0:
                        html = "<option value=''>请选择省</option>";
                        for (var i = 0; i < len; i++) {
                            html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                        }
                        $(line).find(".province").html(html);
                        $(line).find('.province').css('display', '');
                        $(line).find('.province_name').css('display', 'none');
                        var v = $(line).find(".province").attr('title');
                        $(line).find(".province option[value='" + v + "']").attr('selected', true);

                        $(line).find(".city").html("<option value=''>请选择市</option>");
                        $(line).find(".district").html("<option value=''>请选择区/县</option>");
                        $(line).find(".street").html("<option value=''>请选择街道</option>");
                        break;
                    case 1:
                        html = "<option value=''>请选择市</option>";
                        for (var i = 0; i < len; i++) {
                            html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                        }
                        $(line).find(".city").html(html);
                        $(line).find('.city').css('display', '');
                        $(line).find('.city_name').css('display', 'none');
                        var v = $(line).find(".city").attr('title');
                        $(line).find(".city option[value='" + v + "']").attr('selected', true);
                        $(line).find(".district").html("<option value=''>请选择区/县</option>");
                        $(line).find(".street").html("<option value=''>请选择街道</option>");
                        break;
                    case 2:
                        html = "<option value=''>请选择区/县</option>";
                        for (var i = 0; i < len; i++) {
                            html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                        }
                        $(line).find(".district").html(html);
                        $(line).find('.district').css('display', '');
                        $(line).find('.district_name').css('display', 'none');
                        var v = $(line).find(".district").attr('title');
                        $(line).find(".district option[value='" + v + "']").attr('selected', true);
                        $(line).find(".street").html("<option value=''>请选择街道</option>");
                        break;
                    case 3:
                        html = "<option value=''>请选择街道</option>";
                        for (var i = 0; i < len; i++) {
                            html += "<option value='" + data[i].id + "'  >" + data[i].name + "</option>";
                        }
                        $(line).find(".street").html(html);
                        $(line).find('.street').css('display', '');
                        $(line).find('.street_name').css('display', 'none');
                        var v = $(line).find(".street").attr('title');
                        $(line).find(".street option[value='" + v + "']").attr('selected', true);
                        break;
                }

                if (typeof callback == "function") {
                    callback();
                }
            }
        });
    }

</script>