<div style="color: red; ">未选择仓库默认生成所有仓库拣货单，勾选仓库针对已选仓库生成拣货单！ </div>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'warehouse_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未拣货数',
                'field' => 'warehouse_not_pick',
                'width' => '95',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '补货数',
                'field' => 'supply_num',
                'width' => '95',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitPoModel::get_unpick_by_page',
    'idField' => 'id',
    'params' => array('filter' => array('po_no' => $response['po_no'])),
    //'RowNumber'=>true,
    'CheckSelection' => true,
));
?>
<br/><br/>
<div class="row form-actions actions-bar">
    <div class="span13 offset3 ">
        <button type="button" id="ok" class="button button-primary" onclick="create_pick_action()">生成拣货单</button>
    </div>
</div>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var po_id = '<?php echo $response['data']['id']?>';

    function create_pick_action() {
        $("#ok").html("正在生成，请稍后。。。");
        $("#ok").attr("disabled", "disalbed");
        var warehouse_code_arr = new Array();
        var warehouse_code_str = '';
        var rows = tableGrid.getSelection();
        if (rows.length > 0) {
            for (var i in rows) {
                var row = rows[i];
                warehouse_code_arr.push(row.warehouse_code);
            }
            warehouse_code_str = warehouse_code_arr.join(',').toString();
        }
        var params = {"warehouse_code": warehouse_code_str, 'po_id': po_id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/create_pick'); ?>', params, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert('拣货单生成成功！',function () {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                }, 'success');
            } else {
                $("#ok").html("生成拣货单");
                $("#ok").removeAttr("disabled");
                BUI.Message.Alert(data.message, 'error');
            }
            tableStore.load();
        }, "json");
    }

</script>

