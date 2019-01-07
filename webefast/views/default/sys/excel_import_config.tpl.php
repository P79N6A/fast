<form name="mForm" id="mForm" method="post" action="?app_act=sys/excel_import/save_config&_id=<?php echo $request['_id']; ?>">
    <div class="row">
        <div class="span12 offset3 doc-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>代码</th>
                        <th>位置</th>
                        <th>类型</th>
                    </tr>
                </thead>
                <?php foreach ($response['data'] as $area_name => $area_value): ?>
                    <tbody>
                        <tr>
                            <td colspan="4"><b><?php echo $area_value['name']; ?></b></td>
                        </tr>
                        <?php foreach ($area_value['data'] as $data): ?>
                            <tr>
                                <td><?php echo $data['name'] ?></td>
                                <td>{<?php echo $data['code'] ?>}</td>
                                <td><div class="controls  docs-input-sizes"><input name="<?php echo $data['code']; ?>" class="span1 span-width control-text" type="text" value="<?php echo $data['position'] ?>"></div></td>
                                <td><div class="controls  docs-input-sizes"><select class="input-small">
                                            <option>文本</option>
                                            <option>数字</option>
                                        </select></div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endforeach; ?>
            </table>
            <input type="submit" value="保存" class="button button-primary" />
        </div>
    </div>
</form>

<script type="text/javascript">
    BUI.use('bui/form', function (Form) {
        new Form.Form({
            srcNode: '#mForm',
            submitType: 'ajax',
            callback: function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                BUI.Message.Alert(data.message, function(){
                    if (data.status == 1) {
                        ui_closePopWindow('<?php echo $request['ES_frmId'];?>');
                    }
                },type);
            }
        }).render();
    });
</script>