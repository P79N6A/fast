
<script src="assets/js/jquery.formautofill2.min.js"></script>
<?php render_control('PageHead', 'head1',
    array('title' => '奇门ERP配置',
        'links' => array(
          //  array('url' => 'sys/qm_erp_config/detail&app_scene=add', 'title' => '新增奇门ERP配置'),
        ),
        'ref_table' => 'table'
    ));
?>
<style>
    #form_kis{margin-top: 5px;}
    #form_kis input[type=text]{width: 200px;}
    #form_kis table{width: 100%}
    #form_kis table tr td,#form_kis table tr th {
        border: 1px solid #dddddd;
        line-height: 20px;
        padding: 4px 10px;
        text-align: left;
        vertical-align: middle;
    }
    #form_kis table tr:first-child{background-color:#f5f5f5;}
    #form_kis table tr th:first-child{width:30%;}
    #form_kis table tr th:last-child{width:70%;}
    #form_kis table tr td.td_label{text-align:right;}
    #form_kis .msg,.require_flag{color: #ff0000;margin-left: 10px;vertical-align: middle;}

    .add_btn,.minus_btn{float:right; cursor:pointer; margin:0;}
    .add_btn img,.minus_btn img{ margin-right:5px;}
</style>
<form id="form_kis" action="?app_act=sys/qm_erp_config/do_<?php echo $response['app_scene'] ?>" method="post">
    <!-- 基本信息 -->
    <table id="base_set">
        <tr>
            <th>基本信息</th>
            <th></th>
        </tr>
        <tr>
            <td class="td_label">ERP配置名称</td>
            <td>
                <input type="hidden" id="qm_erp_config_id" name="qm_erp_config_id" value=""/>
                <input type="text" value="" class="input-normal bui-form-field" placeholder="配置名称" id="qm_erp_config_name" name="qm_erp_config_name" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td class="td_label">ERP应用上线日期</td>
            <td>
                <input id="online_time"  type="text" value="<?php echo $response['app_scene'] == 'add' ? date('Y-m-d') : ''; ?>" name="online_time" data-rules="{required : true}" class="calendar">
                <b class="require_flag">*</b>
                <span class="msg">在上线日期之后发货或收货的单据，才会上传到ERP</span>
            </td>
        </tr>
    </table>
    <br/>
    <!-- 参数配置 -->
    <table id="param_set">
        <tr>
            <th>参数配置</th>
            <th></th>
        </tr>
        <tr>
            <td class="td_label">对接ERP系统</td>
            <td>
                <select name="qm_erp_system" >
                    <option value="0">奇门</option>
                </select>
<!--                <input type="radio" disabled="disabled" class="bui-form-field"  checked="checked" />金蝶KIS（版本最低要求：产品版本V14.0）-->
            </td>
        </tr>
<!--        <tr>-->
<!--            <td class="td_label">ERP地址</td>-->
<!--            <td>-->
<!--                <input type="text" value=""  class="input-normal bui-form-field" placeholder="ERP地址" id="qm_erp_address" name="qm_erp_address" data-rules="{required: true}"/>-->
<!--                <b class="require_flag">*</b>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td class="td_label">ERP密钥</td>-->
<!--            <td>-->
<!--                <input type="text" value="" class="input-normal bui-form-field" placeholder="ERP密钥" id="qm_erp_key" name="qm_erp_key" data-rules="{required: true}"/>-->
<!--                <b class="require_flag">*</b>-->
<!--            </td>-->
<!--        </tr>-->
        <tr>
            <td class="td_label">目标AppKey</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" placeholder="目标AppKey" id="target_key" name="target_key" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td class="td_label">Customer ID</td>
            <td>
                <input type="text" value=""  class="input-normal bui-form-field" placeholder="Customer ID" id="customer_id" name="customer_id" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
<!--                <input type="hidden" id="kis_server_url" name="kis_server_url" value="" />-->
<!--                <input type="hidden" id="kis_netid" name="kis_netid" value="" />-->
                <a class="button" onclick="api_test();">
                    <i class="icon icon-random" style="margin:3px 5px 0 -5px;"></i>连通测试
                </a>
                <span class="msg" id="test_result"></span>
            </td>
        </tr>
    </table>
    <br/>
    <!-- 店铺对应关系 -->
    <table id="shop_set">
        <tr>
            <th>系统店铺列表</th>
            <th>外部ERP的店铺代码
                <p class="add_btn" onclick="add_shop_tr()"><img src="assets/images/plus.png" />添&nbsp;加</p>
            </th>
        </tr>
        <?php if ($response['app_scene'] == 'add'): ?>
            <tr>
                <td>
                    <select name="shop[0][shop_store_code]" data-rules="{required: true}">
                        <option value="">请选择</option>
                        <?php foreach ($response['sys_shop'] as $v) : ?>
                            <option value="<?php echo $v['shop_code'] ?>"><?php echo $v['shop_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="text" value="" class="input-normal bui-form-field" placeholder="外部ERP店铺代码" name="shop[0][outside_code]" data-rules="{required: true}"/>
                </td>
            </tr>
        <?php endif; ?>
        <?php if (in_array($response['app_scene'], array('edit', 'view'))): ?>
            <?php foreach ($response['qm_shop'] as $key => $val): ?>
                <tr>
                    <td>
                        <select name="shop[<?php echo $key; ?>][shop_store_code]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['sys_shop'] as $v) : ?>
                                <option value="<?php echo $v['shop_code'] ?>" <?php echo $v['shop_code'] === $val['shop_store_code'] ? 'selected' : ''; ?>><?php echo $v['shop_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" value="<?php echo $val['outside_code']; ?>" class="input-normal bui-form-field" placeholder="外部ERP店铺代码" name="shop[<?php echo $key; ?>][outside_code]" data-rules="{required: true}"/>
                        <?php if ($key != 0): ?>
                            <p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p>
                        <?php endif; ?>
                    </td>
                    <!--                    <td>
                        <input type="text" value="<?php //echo $val['outside_code']    ?>" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[<?php //echo $key;    ?>][outside_code]" data-rules="{required: true}"/>
                    </td>-->
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <br/>

    <!-- 仓库对应关系 -->
    <table id="store_set">
        <tr>
            <th>系统仓库列表
            </th>
            <th>外部ERP仓库&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!--                <div class="add_btn">-->
<!--                    <button class="button button-primary " onclick="get_outside_store_api();">重新获取外部仓库</button>-->
                <a class="button button-primary" onclick="get_outside_store_api();">重新获取外部仓库</a>
                    <p class="add_btn" onclick="add_store_tr()"><img src="assets/images/plus.png" />添&nbsp;加</p>
<!--                </div>-->
            </th>
        </tr>
        <?php if ($response['app_scene'] == 'add'): ?>
            <tr>
                <td>
                    <select name="store[0][shop_store_code]" data-rules="{required: true}">
                        <option value="">请选择</option>
                        <?php foreach ($response['sys_store'] as $v) : ?>
                            <option value="<?php echo $v['store_code'] ?>"><?php echo $v['store_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="store[0][outside_store]" data-rules="{required: true}">
                        <option value="">请选择</option>
                        <?php foreach ($response['outside_store'] as $v) : ?>
                            <option value="<?php echo $v['bill_number'] ?>"><?php echo $v['store_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
<!--                    <input type="text" value="" class="input-normal bui-form-field" placeholder="仓库代码" name="store[0][outside_code]" data-rules="{required: true}"/>-->
                </td>
            </tr>
        <?php endif; ?>
        <?php if (in_array($response['app_scene'], array('edit', 'view'))): ?>
            <?php foreach ($response['qm_store'] as $key => $val): ?>
                <tr>
                    <td>
                        <select name="store[<?php echo $key; ?>][shop_store_code]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['sys_store'] as $v) : ?>
                                <option value="<?php echo $v['store_code'] ?>" <?php echo $v['store_code'] === $val['shop_store_code'] ? 'selected' : ''; ?>><?php echo $v['store_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="store[<?php echo $key; ?>][outside_store]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['outside_store'] as $v) : ?>
                                <option value="<?php echo $v['bill_number'] ?>" <?php echo $v['bill_number'] === $val['outside_code'] ? 'selected' : ''; ?>><?php echo $v['store_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($key != 0): ?>
                            <p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p>
                        <?php endif; ?>
                    </td>
        <!--                    <td>
                        <input type="text" value="<?php //echo $val['outside_code']    ?>" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[<?php //echo $key;    ?>][outside_code]" data-rules="{required: true}"/>
                    </td>-->
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <br/>

    <!-- 分销商对应关系 -->
    <table id="fx_set">
        <tr>
            <th>系统分销商列表
            </th>
            <th>外部ERP客户&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!--                <button class="button button-primary " onclick="get_outside_customer_api();">重新获取外部客户</button>-->
                <a class="button button-primary" onclick="get_outside_customer_api();">重新获取外部客户</a>
                <p class="add_btn" onclick="add_fx_tr()"><img src="assets/images/plus.png" />添&nbsp;加</p>
            </th>
        </tr>
        <?php if ($response['app_scene'] == 'add'): ?>
            <tr>
                <td>
                    <select name="fx[0][sys_fx]" data-rules="{required: true}">
                        <option value="">请选择</option>
                        <?php foreach ($response['sys_fx'] as $v) : ?>
                            <option value="<?php echo $v['custom_code'] ?>"><?php echo $v['custom_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="fx[0][outside_fx]" data-rules="{required: true}">
                        <option value="">请选择</option>
                        <?php foreach ($response['outside_fx'] as $v) : ?>
                            <option value="<?php echo $v['customer_code'] ?>"><?php echo $v['customer_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
<!--                    <input type="text" value="" class="input-normal bui-form-field" placeholder="仓库代码" name="fx[0][outside_code]" data-rules="{required: true}"/>-->
                </td>
            </tr>
        <?php endif; ?>
        <?php if (in_array($response['app_scene'], array('edit', 'view'))): ?>
            <?php foreach ($response['qm_fx'] as $key => $val): ?>
                <tr>
                    <td>
                        <select name="fx[<?php echo $key; ?>][sys_fx]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['sys_fx'] as $v) : ?>
                                <option value="<?php echo $v['custom_code'] ?>" <?php echo $v['custom_code'] === $val['custom_code'] ? 'selected' : ''; ?>><?php echo $v['custom_name'] ?></option>
                            <?php endforeach; ?>
                        </select>

                    </td>
                    <td>
                        <select name="fx[<?php echo $key; ?>][outside_fx]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['outside_fx'] as $v) : ?>
                                <option value="<?php echo $v['customer_code'] ?>" <?php echo $v['customer_code'] === $val['outside_code'] ? 'selected' : ''; ?>><?php echo $v['customer_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($key != 0): ?>
                            <p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p>
                        <?php endif; ?>
                    </td>
                    <!--                    <td>
                        <input type="text" value="<?php //echo $val['outside_code']    ?>" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[<?php //echo $key;    ?>][outside_code]" data-rules="{required: true}"/>
                    </td>-->
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <br/>

    <!-- 系统支持业务 -->
    <table>
        <tr>
            <th>系统支持业务</th>
            <th></th>
        </tr>
        <tr>
             <td style="text-align:right;">档案获取</td>
             <td>
                 <input type="checkbox" class="bui-form-field" value="1" id="item_infos_download" name="item_infos_download" <?php echo ($response['app_scene']=='add' || ($response['app_scene'] != 'add' && $response['config_data']['item_infos_download'] == 1)) ? 'checked' : ''; ?>/>（支持商品基本信息、商品颜色、商品尺码、大类等下载，不支持条码自动生成）
             </td>
        </tr>
        <tr >
            <td style="text-align:right;">库存拉取并覆盖系统库存</td>
            <td>
                <input type="checkbox" value="1" class="bui-form-field" id="manage_stock" name="manage_stock" <?php echo ($response['app_scene']=='add' || ($response['app_scene'] != 'add' && $response['config_data']['manage_stock'] == 1)) ? 'checked' : ''; ?>/>（ 支持商品库存获取，并覆盖系统库存，每次为全量商品库存获取）
            </td>
        </tr>
        <tr>
            <td style="text-align:right;">单据同步</td>
            <td>
                <input type="checkbox" value="1" class="bui-form-field" id="trade_sync" name="trade_sync" <?php echo ($response['app_scene']=='add' || ($response['app_scene'] != 'add' && $response['config_data']['trade_sync'] == 1)) ? 'checked' : ''; ?>/>（ 支持网络订单和售后服务单同步到ERP中）
            </td>
        </tr>
    </table>
    <div style="text-align: center; padding-top:20px" id="opt">
        <button class="button button-primary" type="submit">保存</button>
        <button class="button button-primary" type="reset">重置</button>
    </div>
</form>
<script type="text/javascript">
    var scene = '<?php echo $response['app_scene'] ?>';
    var store_select = <?php echo $response['store_select'] ?>;
    var shop_select = <?php echo $response['shop_select'] ?>;
    var outside_store_select = (scene == 'add') ? '' :<?php echo $response['outside_store_select'] ?>;
    var fx_select = <?php echo $response['fx_select'] ?>;
    var outside_fx_select = (scene == 'add') ? '' :<?php echo $response['outside_fx_select'] ?>;
    /*--- 表单提交 ---*/
    var form_kis = new BUI.Form.HForm({
        srcNode: '#form_kis',
        submitType: 'ajax',
        callback: function (ret) {
            var _type = ret.status == 1 ? 'success' : 'error';
            BUI.Message.Show({
                msg: ret.message,
                icon: _type,
                buttons: [],
                autoHide: true
            });
            if (ret.status == 1) {
                location.href = '?app_act=sys/qm_erp_config/detail&app_scene=edit&_id=' + ret.data;
            }
        }
    }).render();

    /*--- 日期选择 ---*/
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: false,
            autoRender: true
        });
    });
    if (scene == 'edit' || scene == 'view') {
        var config_data = <?php echo $response['form_data_source']; ?>;
        var key_val = ['item_infos_download', 'manage_stock', 'trade_sync'];
        $.each(config_data, function (key, val) {
            if ($.inArray(key, key_val) != '-1') {
                return true;
            }
            var obj = $("#form_kis #" + key);
            if (obj != 'undefined') {
                obj.val(val);
            }
        });
    }
//    if (scene == 'view') {
//        $('#form_kis').find('input').attr('disabled', 'disabled');
//        $('#form_kis').find('select').attr('disabled', 'disabled');
//        $('#form_kis .add_btn').remove();
//        $('#form_kis .minus_btn').remove();
//        $('#form_kis #opt').remove();
//    }
//
    /*--- 增加仓库对应关系行 ---*/
    function add_store_tr() {
        var used_store = [];
        var outside_used_store = [];

        $("#store_set tr").each(function (index, element) {
            if (index > 0) {
                var key = index - 1;
                used_store.push($(element).find("[name='store[" + key + "][shop_store_code]']").val());
                outside_used_store.push($(element).find("[name='store[" + key + "][outside_store]']").val());
            }
        });

        var str = '<option value="">请选择</option>';
        $.each(store_select, function (i, v) {
            if ($.inArray(v.store_code, used_store) != '-1') {
                return true;
            }
            str += '<option value="' + v.store_code + '">' + v.store_name + '</option>';
        });

        var outside_str = '<option value="">请选择</option>';
        $.each(outside_store_select, function (i, v) {
//            if ($.inArray(v.store_code, used_store) != '-1') {
//                return true;
//            }
            outside_str += '<option value="' + v.bill_number + '">' + v.store_name + '</option>';
        });

        var i = $("#store_set").find("tr").length - 1;
        var html = '<tr><td><select data-rules="{required: true}" name="store[' + i + '][shop_store_code]">' + str + '</select></td><td><select data-rules="{required: true}" name="store['+ i + '][outside_store]">'+outside_str+'</select><p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p></td></tr>';
        $("#store_set").append(html);
//        <input type="text" value="" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[' + i + '][outside_code]" data-rules="{required: true}"/>
    }
//
//
//    //分销商对应关系
    function add_fx_tr() {
        var used_fx = [];
        var outside_used_fx = [];

        $("#fx_set tr").each(function (index, element) {
            if (index > 0) {
                var key = index - 1;
                used_fx.push($(element).find("[name='fx[" + key + "][sys_fx]']").val());
                outside_used_fx.push($(element).find("[name='fx[" + key + "][outside_fx]']").val());
            }
        });

        var str = '<option value="">请选择</option>';
        $.each(fx_select, function (i, v) {
            if ($.inArray(v.custom_code, used_fx) != '-1') {
                return true;
            }
            str += '<option value="' + v.custom_code + '">' + v.custom_name + '</option>';
        });

        var outside_str = '<option value="">请选择</option>';
        $.each(outside_fx_select, function (i, v) {
//            if ($.inArray(v.store_code, used_store) != '-1') {
//                return true;
//            }
            outside_str += '<option value="' + v.customer_code + '">' + v.customer_name + '</option>';
        });

        var i = $("#store_set").find("tr").length - 1;
        var html = '<tr><td><select data-rules="{required: true}" name="fx[' + i + '][sys_fx]">' + str + '</select></td><td><select data-rules="{required: true}" name="store['+ i + '][outside_fx]">'+outside_str+'</select><p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p></td></tr>';
        $("#fx_set").append(html);
//        <input type="text" value="" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[' + i + '][outside_code]" data-rules="{required: true}"/>
    }
//
//    /*--- 删除仓库对应关系行 ---*/
    function del_tr(item) {
        $(item).parent("td").parent("tr").remove();
    }
//
//    /*--- 增加店铺对应关系行 ---*/
    function add_shop_tr() {
        var used_shop = [];
        $('#shop_set').find('option:selected').each(function (i, v) {
            used_shop.push($(this).val());
        });

        var str = '<option value="">请选择</option>';
        $.each(shop_select, function (i, v) {
            if ($.inArray(v.shop_code, used_shop) != '-1') {
                return true;
            }
            str += '<option value="' + v.shop_code + '">' + v.shop_name + '</option>';
        });

        var i = $("#shop_set").find("tr").length - 1;
        var html = '<tr><td><select data-rules="{required: true}" name="shop[' + i + '][shop_store_code]">' + str + '</select></td><td><input type="text" value="" class="input-normal bui-form-field" placeholder="外部ERP店铺代码" name="shop['+ i + '][outside_code]" data-rules="{required: true}"/><p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p></td></tr>';
        $("#shop_set").append(html);
//        <input type="text" value="" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[' + i + '][outside_code]" data-rules="{required: true}"/>
    }
//
//
//    /*--- API连通测试 ---*/
    function api_test() {
        var params = {};
        $('#param_set').find('input[type="text"]').each(function () {
            var params_name = $(this).attr('name');
            var params_val = $(this).val();
//            if (params_val == '') {
//                $('#test_result').text(params_name + ' 不能为空');
//                return false;
//            }
            var _json = eval("(" + "{" + params_name + ":'" + params_val + "'}" + ")");
            params = $.extend(params, params, _json);
        });

        if (Object.keys(params).length < 2) {
            return;
        }
        $('#test_result').text('');

        $.ajax({
            url: '<?php echo get_app_url('sys/qm_erp_config/api_test'); ?>',
            type: 'POST',
            dataType: 'json',
            data: params,
            success: function (ret) {
                if (ret.status != 1) {
                    $('#test_result').text(ret.message);
                } else {
                    $('#test_result').text('接口测试成功');
                }
            }
        });
    }

    //获取外部仓库
    function get_outside_store_api() {
        var params = {};
        $('#param_set').find('input[type="text"]').each(function () {
            var params_name = $(this).attr('name');
            var params_val = $(this).val();
            var _json = eval("(" + "{" + params_name + ":'" + params_val + "'}" + ")");
            params = $.extend(params, params, _json);
        });
        $.post('?app_act=sys/qm_erp_config/get_outside_store_api', params, function (result) {
            if (result.status == 1) {
                BUI.Message.Tip('获取成功！', 'info');
                outside_store_select = result.data;
                if (scene == 'add') {
                    var html = '';
                    html = "<option value=''>请选择</option>";
                    $.each(outside_store_select, function (i, v) {
                        html += '<option value="' + v.bill_number + '">' + v.store_name + '</option>';
                    });
                    $("#store_set tr").each(function (index, element) {
                        if (index > 0) {
                            var key = index - 1;
                            $(element).find("[name='store[" + key + "][outside_store]']").html(html);
                        }
                    });
                }
            } else {
                BUI.Message.Alert(result.message, 'error');
            }
        }, 'json');
    }

    //获取外部分销商
    function get_outside_customer_api() {
        var params = {};
        $('#param_set').find('input[type="text"]').each(function () {
            var params_name = $(this).attr('name');
            var params_val = $(this).val();
            var _json = eval("(" + "{" + params_name + ":'" + params_val + "'}" + ")");
            params = $.extend(params, params, _json);
        });
        $.post('?app_act=sys/qm_erp_config/get_outside_costomer_api', params, function (result) {
            if (result.status == 1) {
                //添加失败
               // BUI.Message.Alert('获取成功！', 'success');
                BUI.Message.Tip('获取成功！', 'info');
                outside_fx_select = result.data;
                if (scene == 'add') {
                    var html = '';
                    html = "<option value=''>请选择</option>";
                    $.each(outside_fx_select, function (i, v) {
                        html += '<option value="' + v.customer_code + '">' + v.customer_name + '</option>';
                    });
                    $("#fx_set tr").each(function (index, element) {
                        if (index > 0) {
                            var key = index - 1;
                            $(element).find("[name='fx[" + key + "][outside_fx]']").html(html);
                        }
                    });
                }
            } else {
                BUI.Message.Alert(result.message, 'error');
            }
        }, 'json');
    }

</script>
