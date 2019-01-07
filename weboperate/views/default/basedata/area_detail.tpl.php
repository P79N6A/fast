
<table cellspacing="0" class="table table-bordered" style="margin-top: 10px">
    <tr>
        <td style="width:12%">行政区域代码：</td><td style="width:15%"><b><?php echo $response['data']['id']; ?></b></td>
        <td style="width:12%">行政区域类别：</td><td style="width:15%"><b><?php echo $response['data']['type_name']; ?></b></td>
        <td style="width:12%">行政区域名称：</td><td style="width:33%"><b><?php echo $response['data']['name']; ?></b></td>
    </tr>
</table>
<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">平台对照列表</h3>
        <div class="pull-right">
            <button class="button button-small" onclick="javascript:location.reload();"><i class="icon-refresh"></i>刷新</button>
        </div>
    </div>
    <div class="panel-body" id="panel_detail">
        <table cellspacing="0" class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:20%">平台代码</th>
                    <th style="width:20%">平台名称</th>
                    <th>地址区域映射名称</th>
                    <th style="width:15%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($response['data']['pt_arealist'] as $value) { ?>
                <tr class="detail_<?php echo $value['pt_code'];?>">
                    <td><?php echo $value['pt_code']; ?></td>
                    <td><?php echo $value['pt_name']; ?></td>
                    <td name="pac_pt_area_name">
                        <div><?php echo $value['pac_pt_area_name'];?></div>
                        <input class="pac_pt_area_name_value" style="display: none;width:90%;" type="text" value="<?php echo $value['pac_pt_area_name'];?>"/>
                    </td>
                    <td>
                        <button class="button button-small edit" title="编辑" onclick="area_edit('<?php echo $value['pt_code'];?>')"><i class="icon-edit"></i></button>
                        <button class="button button-small save hide" title="保存" onclick="area_save('<?php echo $value['pt_code'];?>')"><i class="icon-ok"></i></button>
                        <button class="button button-small cancel hide" title="取消" onclick="area_cancel('<?php echo $value['pt_code'];?>')"><i class="icon-ban-circle"></i></button>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    //平台对照名称编辑
    function area_edit(code){
        var item = $("#panel_detail table tbody").find(".detail_"+code);
        item.find(".edit").hide();
        item.find(".save").show();
        item.find(".cancel").show();
        item.find("td[name=pac_pt_area_name]").find("input").show();
        var name=item.find("td[name=pac_pt_area_name]").find("div").text();
        item.find("td[name=pac_pt_area_name]").find("input").focus();
        item.find("td[name=pac_pt_area_name]").find("input").val("");
        item.find("td[name=pac_pt_area_name]").find("input").val(name);
        item.find("td[name=pac_pt_area_name]").find("div").hide();
    }
    //平台对照名称取消保存
    function area_cancel(code){
        var item = $("#panel_detail table tbody").find(".detail_"+code);
        item.find(".edit").show();
        item.find(".save").hide();
        item.find(".cancel").hide();
        item.find("td[name=pac_pt_area_name]").find("input").hide();
        item.find("td[name=pac_pt_area_name]").find("div").show();
    }
    //平台对照名称编辑保存
    function area_save(code){
        var item = $("#panel_detail table tbody").find(".detail_"+code)
        var params = {
            'pac_pt_area_name': item.find("td[name=pac_pt_area_name]").find("input").val(),
            'pac_base_area_id': '<?php echo $response['data']['id']; ?>',
            'pac_pt_code': code,
            'pac_type': '<?php echo $response['data']['type']; ?>',
        }
        $.post("?app_act=basedata/area/area_save", params, function(data){
            if(data.status == 1){
                //保存成功
                item.find(".edit").show();
                item.find(".save").hide();
                item.find(".cancel").hide();
                item.find("td[name=pac_pt_area_name]").find("input").hide();
                item.find("td[name=pac_pt_area_name]").find("div").text(item.find("td[name=pac_pt_area_name]").find("input").val());
                item.find("td[name=pac_pt_area_name]").find("div").show();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }
</script>