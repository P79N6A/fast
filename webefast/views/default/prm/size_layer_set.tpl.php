<style>
    body{overflow-x: scroll !important;}
    .content{padding: 30px;}
    .content a{text-decoration: none;cursor: pointer;}
    .layer-top .layer-line,.layer-top .layer-column{display: inline;float: left;}
    .layer-top .layer-column{margin-left: 50px;}
    #layer_line,#layer_column{width: 50px;text-align: center;}
    .layer-main{margin-top: 50px;}
    #layer_table{background-color: #FAFAFA;}
    #layer_table,#layer_table tr,#layer_table td,#layer_table th{border: 1px solid #DFDFDF;}
    #layer_table td{width: 60px;height: 40px;text-align: center;}
    #layer_table input{width: 40px;height: 25px;text-align: center;}
    #layer_table .x-icon-normal{cursor: pointer;}
    #tb_head .head-th{width: 65px;height: 40px;}
    .layer-floor{padding-left: 300px;padding-top: 50px;}
    .layer-tip{margin-top: 50px;color: red;}
</style>

<?php
render_control('PageHead', 'head1', ['title' => '尺码层设置']);
?>

<div class="content">
    <div class="layer-top">
        <div class="layer-line">
            <label>尺码层行数：</label>
            <input type="text" id="layer_line" step="1" min="1" max="5" value="3" onchange="changeLine(this)">
        </div>
        <div class="layer-column">
            <label>尺码层列数：</label>
            <input type="text" id="layer_column" step="1" min="1" max="20" value="5" onchange="changeColumn(this)">
        </div>
    </div>
    <div class="layer-main">
        <table id="layer_table">
            <tr id="tb_head">
                <th rowspan="4" class="head-th">商品编码</th>
                <th rowspan="4" class="head-th">颜色</th>
                <th class="head-th">序号</th>
                <th class="head-th">操作</th>
                <th colspan="6" class="head-th">尺码</th>
            </tr>
        </table>
    </div>
    <div class="layer-floor">
        <button id="submit" class="button button-primary" type="submit" onclick="saveLayer()">保存</button>
    </div>
    <div class="layer-tip">
        <p>*填表说明:</p>
        <p>1、表头尺码框内必须填写尺码名称(填写的尺码名称须预先在尺码列表中设置, <a onclick="jumpSizeSet()">前往设置</a>)</p>
        <p>2、尺码行数最大支持5行,尺码列最大支持20列</p>
    </div>
</div>

<script type="text/javascript">
    var line, column, newline, newcolumn;
    var data = '<?php echo $response['data'] ?>';
    $(function () {
        //初始化尺码层
        initLayer();
    });

    function initLayer() {
        var trhtml = '';
        if (data.length < 1) {
            //加载默认尺码层表
            getlayerNum(1);

            for (var l = 1; l <= newline; l++) {
                trhtml += '<tr id="tr_' + l + '">';
                trhtml += '<td>' + l + '</td>';
                trhtml += '<td><span class="x-icon x-icon-normal" onclick="deleteLine(this)">×</span></td>';
                for (var c = 0; c < newcolumn; c++) {
                    trhtml += '<td><input type="text" ></td>';
                }
            }
        } else {
            data = eval(data);
            $.each(data, function (line, row) {
                var linenumber = line + 1;
                trhtml += '<tr id="tr_' + linenumber + '">';
                trhtml += '<td>' + linenumber + '</td>';
                trhtml += '<td><span class="x-icon x-icon-normal" onclick="deleteLine(this)">×</span></td>';
                $.each(row, function (column, value) {
                    trhtml += '<td><input type="text" value="' + value + '"></td>';
                });
            });
        }

        $("#layer_table").append(trhtml);

        getlayerNum(2);
        setCombine();
    }

    //增减行数
    function changeLine(_this) {
        getlayerNum();
        if (!checkNum(_this, 'line')) {
            BUI.Message.Tip('请输入1-5的整数', 'warning');
            return false;
        }
        var addline = newline - line;

        if (addline > 0) {
            //增加行
            var trhtml = '';
            for (var l = 1; l <= addline; l++) {
                var linenumber = line + l;
                trhtml += '<tr id="tr_' + linenumber + '">';
                trhtml += '<td>' + linenumber + '</td>';
                trhtml += '<td><span class="x-icon x-icon-normal" onclick="deleteLine(this)">×</span></td>';
                for (var c = 0; c < column; c++) {
                    trhtml += '<td><input type="text" ></td>';
                }
            }
            $("#layer_table").append(trhtml);
        } else {
            //减少行
            var delline = Math.abs(addline);
            deleteLine(null, delline);
        }
        setCombine();
    }

    //增减列数
    function changeColumn(_this) {
        getlayerNum();
        if (!checkNum(_this, 'column')) {
            BUI.Message.Tip('请输入1-20的整数', 'warning');
            return false;
        }
        var addcol = newcolumn - column;

        if (addcol > 0) {
            //增加列
            for (var c = 0; c < addcol; c++) {
                for (var i = 1; i <= line; i++) {
                    $("#tr_" + i).append('<td><input type="text" ></td>');
                }
            }
        } else {
            //减少列
            var delline = Math.abs(addcol);
            deleteColumn(null, delline);
        }

        setCombine();
    }

    /*
     * 设置行、列数
     */
    function getlayerNum(_type) {
        //设置最新的行、列数
        newline = parseInt($("#layer_line").val());
        newcolumn = parseInt($("#layer_column").val());
        //$("table#layer_table").find('tr').siblings().length;

        if (_type === 1) {
            line = newline;
            column = newcolumn;
        } else {
            //设置当前的行、列数
            line = $("table#layer_table").find("tr").length - 1;
            column = $("table#layer_table").find("tr:eq(1) td").length - 2;
        }
        if (_type === 2) {
            $("#layer_line").val(line);
            $("#layer_column").val(column);
            newline = line;
            newcolumn = column;
        }
    }

    /*
     * 设置应该合并的行和列
     */
    function setCombine() {
        $("#tb_head").find("th:eq(0),th:eq(1)").attr('rowspan', newline + 1);
        $("#tb_head").find("th:eq(4)").attr('colspan', newcolumn);

        //设置表格的最小宽度
        $("#layer_table").css("min-width", "");
        $('#layer_table').css("min-width", $('#layer_table').width());
    }

    /*
     * 移除行
     */
    function deleteLine(_this, _num) {
        getlayerNum();
        if (_num > 0) {
            for (var i = 0; i < _num; i++) {
                $("#tr_" + (line - i)).remove();
            }
        } else {
            if (line < 2) {
                BUI.Message.Tip('尺码层不能少于一行', 'warning');
                return false;
            }
            //移除当前行
            $(_this).parents("tr").remove();
            _num = 1;
        }

        //重新生成序号
        var linenum = 1;
        $("#layer_table tr:first").siblings().each(function (trindex, tritem) {
            $(tritem).find("td:first").text(linenum);
            $(tritem).attr('id', 'tr_' + linenum);
            linenum++;
        });

        $("#layer_line").val(line - _num);
        //更新行、列数值
        getlayerNum();
    }

    /*
     * 移除列
     */
    function deleteColumn(_this, _num) {
        if (column < 2) {
            BUI.Message.Tip('尺码层不能少于一列', 'warning');
            return false;
        }
        if (_num > 0) {
            for (var i = 0; i < _num; i++) {
                for (var l = 1; l <= line; l++) {
                    $("#tr_" + l).find("td:last").remove();
                }
            }
        }
    }

    /*
     * 检查行、列数值有效性
     */
    function checkNum(_this, _type) {
        var num = $(_this).val();
        if (_type === 'line') {
            if (num < 1 || num > 5 || !isPositiveNum(num)) {
                $(_this).val(line);
                return false;
            }
        } else if (_type === 'column') {
            if (num < 1 || num > 20 || !isPositiveNum(num)) {
                $(_this).val(column);
                return false;
            }
        }

        return true;
    }

    /*
     * 判断是否为正整数
     */
    function isPositiveNum(s) {
        var re = /^[0-9]*[1-9][0-9]*$/;
        return re.test(s);
    }

    /*
     * 保存尺码层
     */
    function saveLayer() {
        var data = getTbData();
        if (!data) {
            return false;
        } else {
            var param = {data: data, line: newline, column: newcolumn};
            $.post('?app_act=prm/size_layer/update_layer', param, function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip('尺码层设置更新成功', 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, "json");
        }
    }

    /*
     * 获取尺码层表格设置数据
     */
    function getTbData() {
        var sub_layer = {};//存储每一行数据
        var nullnum = 0; //检查是否整行为空
        $("#layer_table tr:first").siblings().each(function (trindex, tritem) {
            var arr = new Array();
            $(tritem).find("td:first").siblings().find('input').each(function (tdindex, tditem) {
                var value = $(tditem).val();
                if (value == '') {
                    nullnum++;
                }
                arr[tdindex] = value;//遍历每一个数据，并存入
            });
            if (nullnum == newcolumn) {
                BUI.Message.Tip("不能整行为空", 'warning');
                sub_layer = false;
                return false;
            }
            sub_layer[trindex] = arr;//将每一行的数据存入

            nullnum = 0;
        });

        return sub_layer;
    }

    function jumpSizeSet() {
        openPage(window.btoa('?app_act=prm/spec2/do_list'), '?app_act=prm/spec2/do_list', '尺码设置');
    }
</script>
