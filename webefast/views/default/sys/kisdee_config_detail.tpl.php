<script src="assets/js/jquery.formautofill2.min.js"></script>
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
<form id="form_kis" action="?app_act=sys/kisdee_config/do_<?php echo $response['app_scene'] ?>" method="post">
    <!-- 基本信息 -->
    <table id="base_set">
        <tr>
            <th>基本信息</th>
            <th></th>
        </tr>
        <tr>
            <td class="td_label">配置名称</td>
            <td>
                <input type="hidden" id="config_id" name="config_id" value=""/>
                <input type="text" value="" class="input-normal bui-form-field" placeholder="配置名称" id="config_name" name="config_name" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td class="td_label">应用上线日期</td>
            <td>
                <input id="online_time"  type="text" value="<?php echo $response['app_scene'] == 'add' ? date('Y-m-d') : ''; ?>" name="online_time" data-rules="{required : true}" class="calendar">
                <b class="require_flag">*</b>
                <span class="msg">在上线日期之后发货或收货的单据，才会上传到金蝶</span>
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
            <td class="td_label">对接系统</td>
            <td>
                <input type="radio" disabled="disabled" class="bui-form-field"  checked="checked" />金蝶KIS（版本最低要求：产品版本V14.0）
            </td>
        </tr>
        <tr>
            <td class="td_label">企业号</td>
            <td>
                <input type="text" value=""  class="input-normal bui-form-field" placeholder="eid" id="kis_eid" name="kis_eid" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td class="td_label">访问口令</td>
            <td>
                <input type="text" value="" class="input-normal bui-form-field" placeholder="auth_token" id="kis_auth_token" name="kis_auth_token" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td class="td_label">账套号</td>
            <td>
                <input type="text" value=""  class="input-normal bui-form-field" placeholder="AccountDB" id="AccountDB" name="AccountDB" data-rules="{required: true}"/>
                <b class="require_flag">*</b>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="hidden" id="kis_server_url" name="kis_server_url" value="" />
                <input type="hidden" id="kis_netid" name="kis_netid" value="" />
                <a class="button" onclick="api_test();">
                    <i class="icon icon-random" style="margin:3px 5px 0 -5px;"></i>连通测试
                </a>
                <span class="msg" id="test_result"></span>
            </td>
        </tr>
    </table>
    <br/>
    <!-- 仓库对应关系 -->
    <table id="store_set">
        <tr>
            <th>系统仓库列表
                <p class="add_btn" onclick="add_store_tr()"><img src="assets/images/plus.png" />添&nbsp;加</p>
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
    <!--                <td>
                    <input type="text" value="" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[0][outside_code]" data-rules="{required: true}"/>
                </td>-->
            </tr>
        <?php endif; ?>
        <?php if (in_array($response['app_scene'], array('edit', 'view'))): ?>
            <?php foreach ($response['kis_store'] as $key => $val): ?>
                <tr>
                    <td>
                        <select name="store[<?php echo $key; ?>][shop_store_code]" data-rules="{required: true}">
                            <option value="">请选择</option>
                            <?php foreach ($response['sys_store'] as $v) : ?>
                                <option value="<?php echo $v['store_code'] ?>" <?php echo $v['store_code'] === $val['shop_store_code'] ? 'selected' : ''; ?>><?php echo $v['store_name'] ?></option>
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
        </tr>
        <tr>
            <td>零售发货日报上传</td>
        </tr>
        <tr>
            <td>零售退货日报上传</td>
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
                location.href = '?app_act=sys/kisdee_config/detail&app_scene=edit&_id=' + ret.data;
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
        var config_data = <?php echo $response['form_kis_data_source']; ?>;
        $.each(config_data, function (key, val) {
            var obj = $("#form_kis #" + key);
            if (obj != 'undefined') {
                obj.val(val);
            }
        });
    }
    if (scene == 'view') {
        $('#form_kis').find('input').attr('disabled', 'disabled');
        $('#form_kis').find('select').attr('disabled', 'disabled');
        $('#form_kis .add_btn').remove();
        $('#form_kis .minus_btn').remove();
        $('#form_kis #opt').remove();
    }

    /*--- 增加仓库对应关系行 ---*/
    function add_store_tr() {
        var used_store = [];
        $('#store_set').find('option:selected').each(function (i, v) {
            used_store.push($(this).val());
        });

        var str = '<option value="">请选择</option>';
        $.each(store_select, function (i, v) {
            if ($.inArray(v.store_code, used_store) != '-1') {
                return true;
            }
            str += '<option value="' + v.store_code + '">' + v.store_name + '</option>';
        });

        var i = $("#store_set").find("tr").length - 1;
        $("#store_set").append('<tr><td><select data-rules="{required: true}" name="store[' + i + '][shop_store_code]">' + str + '</select><p class="minus_btn" onclick="del_tr(this);" ><img src="assets/images/minus.png" />删&nbsp;除</p></td></tr>');
//        <input type="text" value="" class="input-normal bui-form-field" placeholder="KIS仓库代码" name="store[' + i + '][outside_code]" data-rules="{required: true}"/>
    }

    /*--- 删除仓库对应关系行 ---*/
    function del_tr(item) {
        $(item).parent("td").parent("tr").remove();
    }

    /*--- API连通测试 ---*/
    function api_test() {
        var params = {};
        $('#param_set').find('input[type="text"]').each(function () {
            var params_name = $(this).attr('name');
            var params_val = $(this).val();
            if (params_val == '') {
                $('#test_result').text(params_name + ' 不能为空');
                return false;
            }
            var _json = eval("(" + "{" + params_name + ":'" + params_val + "'}" + ")");
            params = $.extend(params, params, _json);
        });

        if (Object.keys(params).length < 3) {
            return;
        }
        $('#test_result').text('');

        $.ajax({
            url: '<?php echo get_app_url('sys/kisdee_config/api_test'); ?>',
            type: 'POST',
            dataType: 'json',
            data: params,
            success: function (ret) {
                if (ret.status != 1) {
                    $('#test_result').text(ret.message);
                } else {
                    $('#test_result').text('接口测试成功');
                    $('#kis_server_url').val(ret.data.server_url);
                    $('#kis_netid').val(ret.data.netid);
                }
            }
        });
    }
</script>
