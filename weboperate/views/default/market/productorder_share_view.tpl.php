<?php
render_control('PageHead', 'head1', array('title' => '系统部署',
    'links' => array(//   array('url' => 'basedata/hostinfo/do_list', title => '云主机(VM)列表')
    )
));
?>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array(
                'title' => '选择RDS',
                'type' => 'select_pop',
                'field' => 'rds_id',
                'select' => 'products/rdsinfo_2',
                'remark' => '<a href="javascript:rds_add()" >新增RDS</a>',
                'params' => array('filter' => array('is_auth' => '88')),
                'show_scene' => 'add,edit'
            ),
            array(
                'title' => '选择定时器ip',
                'type' => 'select_pop',
                'field' => 'time_ip',
                'select' => 'products/vminfo_2',
                'remark' => '',
                'show_scene' => 'add,edit'
            ),
            array(
                'title' => '选择接口ip',
                'type' => 'select_pop',
                'field' => 'api_ip',
                'select' => 'products/vminfo_2',
                'remark' => '',
                'show_scene' => 'add,edit'
            ),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 1,
//    'act_edit' => 'basedata/hostinfo/ali_edit', //edit,add,view
    'act_add' => 'market/productorder/share_add',
    'hidden_fields' => array(array('field' => 'pro_num')),
    'data' => $response['data'],
    'callback' => 'after_submit',
    'rules' => 'basedata/share_add', //对应方法在conf/validator/basedata_conf.php
    'event' => array('beforesubmit' => 'formBeforesubmit'),
));
?>
<div id="rem_db" style="display: none">
    <div class="row">		
        <div class="control-group span11">
            <label class="control-label span3">绑定数据库: </label>
            <div class="controls " >
                <input type="text" id="rem_db_name_select_pop" class="input-normal es-selector" value="" /><img class="sear_ico" id="rem_db_name_select_img" src="assets/img/search.png" /><input type="hidden" id="rem_db_name" value="" name="rem_db_name" data-rules="{required: true}" data-messages="{required:'数据库不能为空'}" readonly="true"/><b style="color:red"> *</b>
                <label class="remark" for="rem_db_name"><a href="javascript:addrem_db()" >新增数据库</a></label>
                <span class="valid-text" id="rem_error">
                </span>
            </div>
        </div>
    </div>		
</div>
<script type="text/javascript">
    function formBeforesubmit() {
        if ($("#rem_db_name").val() == '') {
            var error_img = "<span class='estate error'><span class='x-icon x-icon-mini x-icon-error'>!</span><em>不能为空！</em></span>";
            $("#rem_error").html(error_img);
            return false;
        } else {
            $("#rem_error").html('');
        }
        return true; // 如果不想让表单继续提交，则return false
    }
    function rds_add() {
        var url = '?app_act=basedata/rdsinfo/detail&app_scene=add';
        openPage(window.btoa(url), url, '新增RDS');
    }

    function addrem_db() {
        var url = '?app_act=products/dbextmanage/show_add_dbextmanage';
        openPage(window.btoa(url), url, '新增数据库');
    }

    $(document).ready(function () {
        $("#form1").find(".row").eq(0).after($("#rem_db").html())
        //$("#rem_db").remove();
        $("#rem_db").empty();
        $('#rem_db_name_select_pop,#rem_db_name_select_img').click(function () {
            var rds_id = $("#rds_id").val();
            if (rds_id == '') {
                BUI.Message.Alert('请选择RDS', 'error');
                return;
            }
            var url = '?app_act=common/select/dbextinfo&rds_id=' + rds_id;
            selectPopWindowrem_db_name.dialog = new ESUI.PopSelectWindow(url, 'selectPopWindowrem_db_name.callback', {title: '绑定数据库', width: 900, height: 500, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();
        });
    })

    var selectPopWindowrem_db_name = {
        dialog: null,
        callback: function (value, id, code, name) {
            var nameArr = [], valueArr = [];
            for (var i = 0; i < value.length; i++) {
                nameArr.push('[' + value[i][code] + ']' + value[i][name]);
                valueArr.push(value[i][id]);
            }
            $('#rem_db_name_select_pop').val(nameArr.join(','));
            $('#rem_db_name').val(valueArr.join(','));
            if (selectPopWindowrem_db_name.dialog != null) {
                selectPopWindowrem_db_name.dialog.close();
            }
        }
    };

    /**
     *回调函数
     * @param result
     * @param ES_frmId
     */
    function after_submit(result, ES_frmId) {
        if (result.status == 1) {
            BUI.Message.Alert(result.message, function () {
                ui_closeTabPage("<?php echo $request['ES_frmId'] ?>");
            }, 'success');
        } else {
            BUI.Message.Alert(result.message, 'error');
        }
    }
</script>