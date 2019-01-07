<style type="text/css">
    /*.form-horizontal .control-label {*/
        /*display: inline-block;*/
        /*float: left;*/
        /*line-height: 30px;*/
        /*text-align: left;*/
        /*width: 100px;*/
    /*}*/
    /*.span11 {width: 550px;}*/
</style>
<?php
$button = array(
    array('label' => '提交', 'type' => 'submit'),
    array('label' => '重置', 'type' => 'reset'),
);
?>
<div id="TabPage1Contents">
    <div>
        <?php
            $fields = array(
                array('title' => '国家', 'type' => 'select', 'field' => 'country', 'data' => $response['area']['country']),
                array('title' => '省份', 'type' => 'select', 'field' => 'province', 'data' => $response['area']['province']),
                array('title' => '城市', 'type' => 'select', 'field' => 'city', 'data' => $response['area']['city']),
                array('title' => '区/县(ID)', 'type' => 'input', 'field' => 'district_id', ),
                array('title' => '区/县', 'type' => 'input', 'field' => 'district', ),
                array('title' => '街道(ID)', 'type' => 'input', 'field' => 'address_id',),
                array('title' => '街道', 'type' => 'input', 'field' => 'address',),
            );
        render_control('Form', 'form1', array(
            'conf' => array(
                'fields' => $fields,
               // 'hidden_fields' => array(array('field' => 'custom_id')),
            ),
            'buttons' => $button,
            //'act_edit' => 'base/custom/do_edit', //edit,add,view
            'act_add' => 'servicenter/base_area/do_add',
            'data' => $response['data'],
            'callback' => 'after_submit',
            'rules' => array(
                array('country', 'require'),
                array('province', 'require'),
                array('city', 'require'),
                array('district_id', 'require'),
                array('district', 'require'),
            ),
        ));
        ?>
    </div>
</div>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    $("#country").attr('disabled', true);
    form.on('beforesubmit', function () {
        $("#country").attr("disabled", false);
    });
    var url = '<?php echo get_app_url('servicenter/base_area/get_area'); ?>';
    function after_submit(data, ES_frmId) {
        if (data.status == 1) {
            //ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');//页面
            ui_closePopWindow(ES_frmId);//弹窗
        } else {
            BUI.Message.Alert(data.message,'error');
        }
    }

    $(document).ready(function () {
        //区域联动
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
    });


</script>

