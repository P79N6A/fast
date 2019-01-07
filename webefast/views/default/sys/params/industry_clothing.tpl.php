<style>
    .panel-header{border-bottom: 1px solid;margin-top: 5px;height: 30px;}
    .panel-header .pull-left{float: left; margin-top: 4px; cursor: pointer;}
    .panel-header .pull-center{float: left; margin-left: 20px;margin-top: 2px; }
    .panel-header .pull-right{margin-top: 1px;}
    .panel-body {padding:20px 0 10px 20px; border:1px solid #ddd; border-top:none;}
    .panel-body a{text-decoration: none;cursor: pointer;}
</style>

<?php echo load_css('checkbox/cbclass.css', true) ?>

<?php foreach ($response['data'] as $key => $value) : ?>
    <div class="row" id="<?php echo $key; ?>" <?php if($key === 'character_print' && $response['data']['size_layer']['value'] == 0){?>style="display: none" <?php }?>>
        <div class="panel">
            <div class="panel-header clearfix">
                <div><h4 class="pull-left toggle"><?php echo $value['param_name']; ?></h4></div>
                <div class="pull-center">
                    <input class='tgl tgl-light' id="<?php echo $value['param_code'] . '_cbx'; ?>" type='checkbox' onchange="changeState(this);" <?php echo $value['value'] == 1 ? 'checked="checked"' : '' ?>>
                    <label class='tgl-btn' for="<?php echo $value['param_code'] . '_cbx'; ?>"></label>
                </div>
                <div class="pull-right">
                </div>
            </div>
            <div class="panel-body">
                <div class="memo">
                    <p><?php echo $value['memo']; ?></p>
                </div>
                <div id="size_layer_set">
                    <?php
                    if ($value['param_code'] === 'size_layer') {
                        echo $value['issizelayer'] == 0 ? '<p>当前未设置尺码层 <a onclick="sizeLayerSet()"><i class="icon-cog"></i>设置</a></p>' : '<p>当前已设置尺码层 <a onclick="sizeLayerSet()"><i class="icon-cog"></i>修改</a></p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script type="text/javascript">
    var issuccess = 1;

    $(function () {
        //加载参数特有元素
        var type_arr = ['size_layer'];
        $.each(type_arr, function (index, value) {
            showElement(value);
        });
    });

    /*
     * 更新参数状态
     */
    function changeState(_this) {
        var _state = $(_this).is(':checked') ? 1 : 0;
        var _type = $(_this).attr('id');
        _type = _type.replace('_cbx', '');

        updateParamValue(_type, _state,1);
    }

    /*
     * 提交更新参数值
     */
    function updateParamValue(_type, _value,action_type) {
        var param = {param_code: _type, value: _value};
        $.post("?app_act=sys/params/update_param_value", param, function (ret) {
            if (ret.status == 1) {
                if(_type === 'size_layer'){
                    if(_value == 0){
                        $('#character_print').hide();
                        $("#character_print_cbx").prop('checked', !$("#character_print_cbx").prop('checked'));
                    }else{
                        $('#character_print').show();
                        $("#character_print_cbx").prop('checked', !$("#character_print_cbx").prop('checked'));
                    }
                    updateParamValue('character_print',_value,2);
                }
                if(action_type != 2){
                    BUI.Message.Tip('参数更新成功', 'success');
                    issuccess = 1;
                }
            } else {
                if(action_type != 2){
                    BUI.Message.Tip('参数更新出错,请重试', 'error');
                    issuccess = 2;
                    //失败要还原元素状态
                    $("#" + _type + "_cbx").prop('checked', !$("#" + _type + "_cbx").prop('checked'));
                }else if(action_type == 2 && _type === 'character_print'){
                    BUI.Message.Tip('单据颜色、尺码层商品打印关闭失败,请手动关闭', 'error');
                    $('#character_print').show();
                }
            }
            //加载参数特有元素
            showElement(_type);
        }, "json");
    }

    /*
     * 元素显示,指定参数特有元素
     */
    function showElement(_type) {
        var _state = $("#" + _type + "_cbx").is(':checked') ? 1 : 0;
        if (_state === 1) {
            switch (_type) {
                case 'size_layer':
                    $("#size_layer_set").show();
                    break;
                default :
                    break;
            }
        } else {
            switch (_type) {
                case 'size_layer':
                    $("#size_layer_set").hide();
                    break;
                default :
                    break;
            }
        }
    }

    /*
     * 尺码层设置
     */
    function sizeLayerSet() {
        if (!$("#size_layer_cbx").is(":checked")) {
            return false;
        }
        openPage(window.btoa('?app_act=prm/size_layer/set'), '?app_act=prm/size_layer/set', '尺码层设置');
    }

    /*
     * 面板展开和隐藏
     */
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

</script>