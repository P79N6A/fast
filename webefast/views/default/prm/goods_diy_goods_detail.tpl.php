<style type="text/css">
    .table_panel{
        width:800px;
    }
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }
    .scroll {
        height: 50px;                                  /*高度*/
        padding-left: 10px;                             /*层内左边距*/
        padding-right: 10px;                            /*层内右边距*/
        padding-top: 10px;                              /*层内上边距*/
        padding-bottom: 10px;                           /*层内下边距*/
        overflow-y: scroll;                             /*竖向滚动条*/

        scrollbar-face-color: #D4D4D4;                  /*滚动条滑块颜色*/
        scrollbar-hightlight-color: #ffffff;                /*滚动条3D界面的亮边颜色*/
        scrollbar-shadow-color: #919192;                    /*滚动条3D界面的暗边颜色*/
        scrollbar-3dlight-color: #ffffff;               /*滚动条亮边框颜色*/
        scrollbar-arrow-color: #919192;                 /*箭头颜色*/
        scrollbar-track-color: #ffffff;                 /*滚动条底色*/
        scrollbar-darkshadow-color: #ffffff;                /*滚动条暗边框颜色*/
    }

    #form4 li{padding:4px;}
    #form4 li label{ display:inline-block; min-width:90px;}
    #spec1_html{height:80px; overflow:auto;}
    #spec2_html{height:80px; overflow:auto;}
    .spec1-code-name {display:inline-block; padding-bottom:5px;}
    .spec1-name {display:inline-block; width:108px; height:20px; overflow:hidden; vertical-align:text-bottom; font-size:12px;}
</style>

<?php
$title = $response['action'] == 'do_diy_add' ? '添加组装商品' : '组装商品编辑';
render_control('PageHead', 'head1', array('title' => $title));
//$spec1_realname = load_model('prm/GoodsSpec1Model')->get_spec1_realname();
//$spec2_realname = load_model('prm/GoodsSpec2Model')->get_spec2_realname();
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
?>

<div id="tab">
    <ul>
        <li <?php if ((isset($request['type']) && $request['type'] == '1') || !isset($request['type'])) { ?> class='bui-tab-panel-item11 active' <?php } ?>><a href='#'>通用信息</a></li>
        <li  <?php if (isset($request['type']) && $request['type'] == '2') { ?> class='bui-tab-panel-item11 active' <?php } else { ?> class='bui-tab-panel-item11 '  <?php } ?>><a href='#'>规格信息</a></li>
        <?php if (isset($response['data']['diy']) && $response['data']['diy'] === '1') { ?>
            <li class='bui-tab-panel-item11 '><a href='?app_act=prm/goods_diy/detail&goods_code=<?php echo $response['data']['goods_code']; ?>&goods_id=<?php echo $response['data']['goods_id']; ?>'>组装明细</a></li>
        <?php } ?>
        <li <?php if (isset($request['type']) && $request['type'] == '3') { ?> class='bui-tab-panel-item11 active' <?php } else { ?> class='bui-tab-panel-item11 '  <?php } ?>><a href='#' id="goods_prop" goods_code = '<?php echo $response['data']['goods_code']; ?>'>扩展属性</a></li>
        <li <?php if ((isset($request['type']) && $request['type'] == '4')) { ?> class='bui-tab-panel-item11 active' <?php } else { ?> class='bui-tab-panel-item11 '  <?php } ?>><a href='#'>日志</a></li>
    </ul>
</div>

<div id="panel" class="">
    <form action="?app_act=prm/goods/<?php echo $response['action']; ?>" id="form1" method="post">
        <table class='table_panel'   id='p1'>
            <input type="hidden" id="goods_id" name="goods_id" value="<?php echo $response['data']['goods_id']; ?>">

            <tr><td style="width:108px;">组装商品编码：<b style="color:red"> *</b></td><td><input id="goods_code" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_code']; ?>" name="goods_code" data-rules="{required : true}" aria-disabled="false" aria-pressed="false" <?php if ($response['action'] == 'do_edit') { ?> disabled="disabled" <?php } ?>></td></tr>
            <tr><td style="width:108px;">组装商品名称：<b style="color:red"> *</b></td><td><input id="goods_name" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_name']; ?>" name="goods_name" data-rules="{required : true}"  aria-disabled="false" aria-pressed="false"></td></tr>
            <tr><td style="width:108px;">组装商品：</td><td>
                        <select disabled="disabled" >
                            <option  value ="1">是</option>
                        </select>
                        <input type="hidden" name="diy" id="diy" value="1">
                </td></tr>
            <tr>
                <td style="width:108px;">商品主图：</td><td>
                    <div id="img_show">
                        <?php if (!empty($response['data']['goods_thumb_img'])) { ?>
                            <img src="<?php echo $response['data']['goods_thumb_img']; ?>">
                        <?php } ?>
                    </div>
                    <div class="upload1">
                        <div class="row form-actions actions-bar">
                            <div style="float: left;"><span id="J_Uploader" style="display: inline-block;"></span></div>
                            <input type="hidden" name="goods_img" id="goods_img" value="<?php echo $response['data']['goods_img']; ?>" >
                            <input type="hidden" name="goods_thumb_img" id="goods_thumb_img" value="<?php echo $response['data']['goods_thumb_img']; ?>" >
                        </div>
                    </div>
                    <div class="tips tips-small tips-info">
                        <span class="x-icon x-icon-small x-icon-info"><i class="icon icon-white icon-info"></i></span>
                        <div class="tips-content">附件支持jpg\png\gif格式，大小不超过2M</div>
                    </div>
                    <!--
                        <img src="<?php //echo $response['data']['goods_img_zhu']; ?>" width=50 height=50>

                    <span id="goods_imgUploader">
                        <input type="hidden" id="goods_img" name="goods_img" value='<?php //echo $response['data']['goods_img']; ?>'/>
                    </span>-->
                </td>
            </tr>
            <tr><td style="width:108px;">商品简称：</td><td><input id="goods_short_name" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_short_name']; ?>" name="goods_short_name" aria-disabled="false" aria-pressed="false"></td></tr>
            <tr><td style="width:108px;">出厂名称：</td><td><input id="goods_produce_name" class="bui-form-field" type="text"  value="<?php echo $response['data']['goods_produce_name']; ?>" name="goods_produce_name" aria-disabled="false" aria-pressed="false"></td></tr>
            <tr><td style="width:108px;">商品分类：<b style="color:red"> *</b></td><td>
                    <select name = "category_code" id = "category_code" data-rules="{required : true}">
                        <option value ="">请选择分类</option>
                        <?php foreach ($response['category'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['category_code'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                    <a href='#' id = 'category__code'><img id="category_code_select_img" src="assets/img/search.png"></a>
                </td>
                <td>
                    如果没有分类信息，请<a href="javascript:PageHead_show_dialog_type('?app_act=prm/category/detail&app_scene=add&app_show_mode=pop', '添加分类', {w:500,h:400},'get_category')" onclick="">新增</a>

                </td>
            </tr>
            <tr><td style="width:108px;">商品品牌：<b style="color:red"> *</b></td><td>
                    <select name="brand_code" id="brand_code" data-rules="{required : true}">
                        <option value ="">请选择品牌</option>
                        <?php foreach ($response['brand'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['brand_code'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                    <a href='#' id = 'brand__code'><img id="brand_code_select_img" src="assets/img/search.png"></a>
                </td><td>
                    如果没有品牌信息，请<a href="javascript:PageHead_show_dialog_type('?app_act=prm/brand/detail&app_scene=add&app_show_mode=pop', '添加品牌', {w:500,h:400},'get_brand')" onclick="">新增</a>

                </td></tr>
            <tr><td style="width:108px;">商品季节：</td><td>
                    <select name="season_code" id="season_code">
                        <option value ="">请选择季节</option>
                        <?php foreach ($response['season'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['season_code'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                </td><td>
                    如果没有季节信息，请<a href="javascript:PageHead_show_dialog_type('?app_act=base/season/detail&app_scene=add&app_show_mode=pop', '添加季节', {w:500,h:400},'get_season')" onclick="">新增</a>

                </td>
            </tr>
            <tr><td style="width:108px;">商品年份：</td><td>
                    <select name="year_code" id="year_code">
                        <option value ="">请选择年份</option>
                        <?php foreach ($response['year'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['year_code'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                </td><td>
                    如果没有年份信息，请<a href="javascript:PageHead_show_dialog_type('?app_act=base/year/detail&app_scene=add&app_show_mode=pop', '添加季节', {w:500,h:400},'get_year')" onclick="">新增</a>

                </td>
            </tr>
            <tr><td style="width:108px;">商品属性：</td><td>
                    <select name="goods_prop" id="goods_prop">
                        <option value ="">请选择属性</option>
                        <?php foreach ($response['prop'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['goods_prop'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                </td></tr>
            <tr><td style="width:108px;">商品状态：</td><td>
                    <select name="state" id="state">
                        <option value ="">请选择状态</option>
                        <?php foreach ($response['state'] as $k => $v) { ?>
                            <option  value ="<?php echo $v[0]; ?>" <?php if ($response['data']['state'] === $v[0]) { ?> selected <?php } ?> ><?php echo $v[1]; ?></option>
                        <?php } ?>
                    </select>
                </td></tr>

            <tr><td style="width:108px;">商品重量（克）：</td><td><input id="weight" value="<?php echo $response['data']['weight']; ?>" name="weight" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.584</td></tr>
            <tr><td style="width:108px;">吊牌价：</td><td><input id="sell_price" value="<?php
                    if (isset($response['data']['sell_price'])) {
                        echo $response['data']['sell_price'];
                    }
                    ?>" name="sell_price" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.584</td></tr>
            <tr><td style="width:108px;">成本价：</td><td><input id="cost_price" value="<?php
                    if (isset($response['data']['cost_price'])) {
                        echo $response['data']['cost_price'];
                    }
                    ?>" name="cost_price" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.583</td></tr>
            <tr><td style="width:108px;">批发价：</td><td><input id="trade_price" value="<?php
                    if (isset($response['data']['trade_price'])) {
                        echo $response['data']['trade_price'];
                    }
                    ?>" name="trade_price" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.582</td></tr>
            <tr><td style="width:108px;">进货价：</td><td><input id="purchase_price" value="<?php
                    if (isset($response['data']['purchase_price'])) {
                        echo $response['data']['purchase_price'];
                    }
                    ?>" name="purchase_price" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.582</td></tr>

            <tr><td style="width:108px;">最低售价：</td><td><input id="min_price" value="<?php
                    if (isset($response['data']['min_price'])) {
                        echo $response['data']['min_price'];
                    }
                    ?>" name="min_price" type="text"></td><td>只允许输入数字，支持三位小数，示例值23.582</td></tr>

            <tr><td style="width:108px;">生产周期（天）：</td><td><input id="goods_days" value="<?php echo $response['data']['goods_days']; ?>" name="goods_days" type="text"></td><td>用于采购，只允许输入数字，示例值3</td></tr>
            <?php if ($response['lof_status'] == 1) { ?>
                <tr><td style="width:108px;">保质期（月）：</td><td><input id="period_validity" value="<?php echo $response['data']['period_validity']; ?>" name="period_validity" type="text"></td><td>用于计算商品失效时间，只允许输入数字，示例值3</td></tr>
                <tr><td style="width:108px;">使用周期（月）：</td><td><input id="operating_cycles" value="<?php echo $response['data']['operating_cycles']; ?>" name="operating_cycles" type="text"></td><td>按照商品的使用周期维度分析，只允许输入数字，示例值3</td></tr>
            <?php } ?>
            <tr><td style="width:108px;">商品描述：</td><td><textarea id="goods_desc" class="bui-form-field" style="width:184px; height: 80px;" cols="40" rows="10" name="goods_desc" aria-disabled="false" aria-pressed="false"> <?php echo $response['data']['goods_desc']; ?></textarea>

                </td></tr>
            <tr><td style="width:108px;">

                </td><td><button type="submit" class="button button-primary">保存</button>&nbsp;&nbsp;&nbsp;<button type="reset" class="button button-primary">重置</button> &nbsp;&nbsp;&nbsp;<input type="button" class="button button-primary"  value="返回" onclick="javascript:window.location = '?app_act=prm/goods/do_list_diy';">

                </td></tr>
        </table>
    </form>
    <form action="?app_act=prm/goods/<?php echo $response['action_spec']; ?>" id="form2" method="post">
        <div id='p2'>
            <table class='table_panel1' style='width:100%'>
                <tr><td style="width:80px;" >
                        <?php echo $response['goods_spec1_rename']; ?><input type="hidden" id="goods_code" name="goods_code" value="<?php echo $response['data']['goods_code']; ?>"><br>  <?php if($response['spec_power']['spec_power'] == 1){?><a href="#" id = 'goods_spec1'><img src='assets/img/search.png'>点我新增</a><?php }?>
                        <input type="hidden"   value="<?php echo $response['data']['diy']; ?>" name="diy" />
                        <input type="hidden"   value="<?php echo $response['data']['goods_id']; ?>" name="goods_id" />
                        <input type="hidden" id="spec1_code"  value="<?php echo $response['data']['goods_spec1_str_code']; ?>" name="spec1_code" />
                        <input type="hidden" id="spec1_name"  value="<?php echo $response['data']['goods_spec1_str_name']; ?>" name="spec1_name" />
                    </td><td style="width:1000px;" >
                        <div align="left">
                            <div class="scroll" id="spec1_html">
                                <?php foreach ($response['data']['goods_spec1_code'] as $k => $v) {
                                    if($v != '' && isset($v)){ ?>
                                        <?php if ($k % 12 == 0 && $k > 0) { ?>
                                        <?php } ?>
                                        <div class="spec1-code-name">
                                            <span><input name="spec1[]" type="checkbox" checked="checked" <?php if ((isset($response['spec1_limit'][$v]) && $response['spec1_limit'][$v] == '1')) { ?>   disabled="disabled" <?php } ?> value="<?php echo $v; ?>" /></span>
                                            <span class="spec1-name"  title="<?php echo $response['data']['goods_spec1_name'][$k]; ?>（<?php echo $v; ?>）"><?php echo $response['data']['goods_spec1_name'][$k]; ?>（<?php echo $v; ?>）</span>
                                        </div>

                                    <?php }} ?>
                            </div>
                        </div>  </td><!--<td style="width:300px;">  <span class="spec1_html" ></span></td>-->
                    <!--  <td> 没找到规格1信息，需要<a href="javascript:PageHead_show_dialog_type('?app_act=prm/spec1/detail&app_scene=add&app_show_mode=pop', '添加规格1', {w:500,h:400},'get_spec1')" onclick="">添加规格1</a>   找到规格1，
                   </td>	-->
                </tr>
                <tr><td><?php echo $response['goods_spec2_rename']; ?>  <input type="hidden" id="spec2_code"  value="<?php echo $response['data']['goods_spec2_str_code']; ?>" name="spec2_code" /><br>  <?php if($response['spec_power']['spec_power'] == 1){?><a href="#" id = 'goods_spec2'><img src='assets/img/search.png'>点我新增</a><?php }?>
                        <input type="hidden" id="spec2_name"  value="<?php echo $response['data']['goods_spec2_str_name']; ?>" name="spec2_name" />
                    </td><td>
                        <div align="left">
                            <div class="scroll" id="spec2_html" >
                                <?php         foreach ($response['data']['goods_spec2_code'] as $k => $v) {   if($v != '' && isset($v)){ ?>
                                    <?php if ($k % 12 == 0 && $k > 0) { ?>
                                    <?php } ?>
                                    <div class="spec1-code-name">
                                        <span><input name="spec2[]" type="checkbox" <?php if ((isset($response['spec2_limit'][$v]) && $response['spec2_limit'][$v] == '1')) { ?> disabled="disabled"   <?php } ?> checked="checked" value="<?php echo $v; ?>" /></span>
                                        <span class="spec1-name" title="<?php echo $response['data']['goods_spec2_name'][$k]; ?>（<?php echo $v; ?>）"><?php echo $response['data']['goods_spec2_name'][$k]; ?>（<?php echo $v; ?>）</span>
                                    </div>

                                <?php }} ?>
                            </div>
                        </div>
                    </td>

                    <!--  <td> 没找到规格2信息，需要<a href="javascript:PageHead_show_dialog_type('?app_act=prm/spec2/detail&app_scene=add&app_show_mode=pop', '添加规格2', {w:500,h:400},'get_spec2')" onclick="">添加规格2</a>   找到规格2，
                   </td>-->
                </tr>
                <tr><td>商品条码</td><td>
                        <table class='table_panel1' style='width:100%'>
                            <tr><td style="width:8%;"><?php echo $response['goods_spec1_rename']; ?></td><td style="width:8%;"><?php echo $response['goods_spec2_rename']; ?></td><td style="width:10%;">系统SKU码</td><td style="width:10%;">商品条形码</td><td style="width:10%;">国标码</td><td style="width:5%;">吊牌价(元)</td><td style="width:5%">成本价（元）</td><td style="width:5%;">重量(克)</td><td style="width:14%;">商品条码备注</td></tr>
                            <tbody id="tiaoma">
                            <?php foreach ($response['data']['barcode'] as $k => $v) { ?>
                                <tr><td><?php echo $v['spec1_name'] ?>（<?php echo $v['spec1_code']; ?>）</td><td ><?php echo $v['spec2_name'] ?>（<?php echo $v['spec2_code']; ?>）</td><td ><?php echo $v['sku'] ?><input  id= "<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_sku'; ?>"  name= "<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_sku'; ?>" value="<?php echo $v['sku'] ?>"  type='hidden' /></td>

                                    <td>
                                            <span class="shuru" >
                                                <input id= "<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_barcode'; ?>" name="<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_barcode'; ?>" type="text" onblur="inputbarcord(this);" style="width:98%;" value="<?php echo $v['barcode'] ?>"/>
                                            </span>
                                    </td>

                                    <td>
                                            <span class="shuru" >
                                                <input id= "<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_gb_code'; ?>" name="<?php echo $v['spec1_code'] . '_' . $v['spec2_code'] . '_gb_code'; ?>" type="text" onblur="inputgbcode(this);" style="width:98%;" value="<?php echo $v['gb_code'] ?>"/>
                                            </span>
                                    </td>

                                    <td >
                                        <?php
                                        $spec12_code = "{$v['spec1_code']}_{$v['spec2_code']}";
                                        $sell_price = isset($v['price']) ? $v['price'] : '';

                                        echo "<input type='text' id='{$spec12_code}_sell_price' name = '{$spec12_code}_sell_price' style='width:98%' onblur='inputprice(this);' value='{$sell_price}'/>";
                                        //echo $sell_price;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cost_price = isset($v['cost_price']) ? $v['cost_price'] : '';
                                        echo "<input type='text' id='{$spec12_code}_cost_price' name = '{$spec12_code}_cost_price' onblur='inputcostprice(this);' style='width:98%' value='{$cost_price}'/>";
                                        ?>
                                    </td>
                                    <td >
                                        <?php
                                        //$weight = isset($response['data']['weight'])?$response['data']['weight']:'';
                                        $weight = isset($v['weight']) ? $v['weight'] : '';
                                        echo "<input type='text' id='{$spec12_code}_weight' name = '{$spec12_code}_weight' onblur='inputweight(this);' style='width:98%' value='{$weight}'/>";
                                        //echo $weight;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $sku_remark = isset($v['remark']) ? $v['remark'] : '';
                                        echo "<input type='text' id='{$spec12_code}_sku_remark' name='{$spec12_code}_sku_remark' style='width:98%' value='{$sku_remark}'/>";
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody >
                        </table>
                    </td></tr>
                <tr><td style="width:80px;"></td><td><button type="submit" class="button button-primary">保存</button>&nbsp;&nbsp;&nbsp;<button type="reset" class="button button-primary">重置</button>&nbsp;&nbsp;&nbsp;<input type="button" class="button button-primary"  value="返回" onclick="javascript:window.location = '?app_act=prm/goods/do_list_diy';">&nbsp;&nbsp;&nbsp;<input type="hidden" name="msg" id="msg"></td></tr>
            </table>
            <div><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php if (isset($response['data']['diy']) && $response['data']['diy'] === '1') { ?>
                    <font color="red">提示：只能对已保存的商品规格设置组装明细，所以请设置商品规格先保存，然后在设置组装明细</font>
                <?php } ?>
            </div>
        </div>
    </form>
    <?php if (isset($response['data']['diy']) && $response['data']['diy'] === '1') { ?>
        <div id="form3_div" style="">

        </div>
    <?php } ?>
    <div id="form4_div" style="">

    </div>
    <div id="form5_div" style="">
        <?php
        render_control('DataTable', 'log', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作者',
                        'field' => 'user_code',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作名称',
                        'field' => 'operation_name',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作时间',
                        'field' => 'add_time',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '备注',
                        'field' => 'operation_note',
                        'width' => '400',
                        'align' => ''
                    ),
                )
            ),
            'dataset' => 'prm/GoodsModel::get_by_page_log',
            //'queryBy' => 'searchForm',
            'idField' => 'goods_id',
            'params' => array('filter' => array('goods_id' => $response['data']['goods_id'])),
        ));
        ?>
    </div>

</div>
<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var barcode = '<?php echo empty($response['data']['barcode'])?0:1;?>';
    var spec1 = '<?php echo $response['data']['goods_spec1_str_code']; ?>';
    var spec2 = '<?php echo $response['data']['goods_spec2_str_code']; ?>';
    var spec1_name = '<?php echo $response['data']['goods_spec1_str_name']; ?>';
    var spec2_name = '<?php echo $response['data']['goods_spec2_str_name']; ?>';
    var cost_price_status = '<?php echo $response['cost_price_status']; ?>';//成本价权限
    var purchase_price_status = '<?php echo $response['purchase_price_status']; ?>';//进货价权限
    var action = '<?php echo $response['action']; ?>';
    $(function(){
        //价格权限控制
        //if (action == 'do_edit') {//新增，编辑都控制价格权限
        if (cost_price_status != 1) {
            $("#cost_price").attr('disabled', 'disabled');
            sku_cost_price_auth();
        }
        if (purchase_price_status != 1) {
            $("#purchase_price").attr('disabled', 'disabled');
        }
        //}

        //选择规格1
        $("#goods_spec1").click(function(){
            var spec1_code_list = $("#spec1_code").val();
            new ESUI.PopWindow("?app_act=prm/goods/select_spec1&spec1_code_list="+spec1_code_list, {
                title: "选择规格1（<?php echo $response['goods_spec1_rename']; ?>）",
                width: 700,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        })
        //选择规格2
        $("#goods_spec2").click(function(){
            var spec2_code_list = $("#spec2_code").val();
            new ESUI.PopWindow("?app_act=prm/goods/select_spec2&spec2_code_list="+spec2_code_list, {
                title: "选择规格2（<?php echo $response['goods_spec2_rename']; ?>）",
                width: 700,
                height: 610,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        })
        //点击规格信息，并且参数关闭，且之前未有数据，就执行一次生成sku
        $('li').eq(1).one('click',function () {
            if(spec_power == '0' && barcode=='0'){
                var html = create_barcode_html(spec1,spec2,spec1_name,spec2_name);
                $("#tiaoma").append(html);
            }
        });
    })

    //sku级成本价权限
    function sku_cost_price_auth() {
        if (cost_price_status != 1) {
            $("#tiaoma tr").each(function (index, element) {
                var id = $(element).children("td").eq(6).find("input").attr('id');
                $("#" + id).val('*****');
                $("#" + id).attr('disabled', 'disabled');
                //var val=$(element).children("td").eq(7).find("input").val();
            });
        }
    }

    //给规格1赋值
    parent.add_spec1 = function(ids){
        $.each(ids, function(i,val){
            $("#spec1_html").append("<div class='spec1-code-name'><span> <input class='bui-form-field-checkbox bui-form-check-field bui-form-field' id='spec1_"+val[0]+"'  name='spec1[]' checked aria-disabled='false' type='checkbox' value='"+val[0]+"'/> </span><span class='spec1-name' title = '"+val[1]+"("+val[0]+"）'>"+val[1]+"("+val[0]+"）</span></div>");
            $("#spec1_"+val[0]).click(function () {
                spec_checked('spec1',this);
            })
            $("#spec1_"+val[0]).trigger("click");
            $("#spec1_"+val[0]).attr('checked',true);

        });
        spec1Check();
        sku_cost_price_auth();
    }
    //给规格2赋值
    parent.add_spec2 = function(ids){
        $.each(ids, function(i,val){
            $("#spec2_html").append("<div class='spec1-code-name'><span> <input class='bui-form-field-checkbox bui-form-check-field bui-form-field' name='spec2[]' checked type='checkbox' id='spec2_"+val[0]+"' value='"+val[0]+"'/> </span><span class='spec1-name' title = '"+val[1]+"("+val[0]+"）'>"+val[1]+"("+val[0]+"）</span></div>");
            $("#spec2_"+val[0]).click(function () {
                spec_checked('spec2',this);
            })
            $("#spec2_"+val[0]).trigger("click");
            $("#spec2_"+val[0]).attr('checked',true);
        });
        spec2Check();
        sku_cost_price_auth();
    }

    var action = '<?php echo $response['action']; ?>';
    var next = '<?php echo $response['next']; ?>';

    var type = '<?php echo $request['type']; ?>';
    var spec_power = '<?php echo $response['spec_power']['spec_power']?>';
    if (action == 'do_add') {
        $("#tab").find('li').eq(1).hide();
        $("#tab").find('li').eq(2).hide();
    }
    if (next == '1') {

    }
    var ES_frmId  = '<?php echo $request['ES_frmId'];?>';
    //form1
    form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
        validators: {
            '#goods_days': function (value) { //读取input的表单字段 name
                if (value) {
                    var patrn = /^[1-9]\d*$/;
                    if (!patrn.exec(value)) {
                        return '生产周期必须为数字';
                    }
                }
            },
            '#weight': function (value) { //读取input的表单字段 name
                if (value) {
                    var flag = valiFloat(value);
                    if (!flag) {
                        return '重量必须为数字或1-3位小数';
                    }

                }
            },
            '#sell_price': function (value) { //读取input的表单字段 name
                if (value) {
                    var flag = valiFloat(value);
                    if (!flag) {
                        return '吊牌价必须为数字或1-2位小数';
                    }
                }
            },
            '#cost_price': function (value) { //读取input的表单字段 name
                if (value) {
                    if (cost_price_status == 1) {
                        var flag = valiFloat(value);
                        if (!flag) {
                            return '成本价必须为数字或1-2位小数';
                        }
                    }
                }
            },
            '#trade_price': function (value) { //读取input的表单字段 name
                if (value) {
                    var flag = valiFloat(value);
                    if (!flag) {
                        return '批发价必须为数字或1-2位小数';
                    }
                }

            },
            '#purchase_price': function (value) { //读取input的表单字段 name
                if (value) {
                    if (purchase_price_status == 1) {
                        var flag = valiFloat(value);
                        if (!flag) {
                            return '进货价必须为数字或1-2位小数';
                        }
                    }
                }
            },
            '#min_price': function (value) { //读取input的表单字段 name
                if (value) {
                    var flag = valiFloat(value);
                    if (!flag) {
                        return '最低售价必须为数字或1-2位小数';
                    }
                }

            },
            '#goods_desc': function (value) { //读取input的表单字段 name
                if (value) {
                    var len = GetLength(value);
                    if (len > 255) {
                        return '商品描述最多允许输入255个字符';
                    }
                }
                $("#goods_code").attr("disabled", false);
            },
        },
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {

                BUI.Message.Alert('保存成功', type);
                window.location.href = "?app_act=prm/goods/diy_goods_detail&action=do_edit&goods_id=" + data.data+"&ES_frmId="+ES_frmId;

            } else {
                BUI.Message.Alert(data.message, 'error');
            }


        }
    }).render();

    form2 = new BUI.Form.HForm({
        srcNode: '#form2',
        submitType: 'ajax',
        validators: {
            '#goods_code': function (value) { //读取input的表单字段 name

                if (!value) {
                    return '请先添写商品基本信息后再选规格';

                }
            },
            '#msg': function (value) { //输入条码是否重复
                var spec1_code = $("#spec1_code").val();
                var spec2_code = $("#spec2_code").val();
                arr_spec1_code = spec1_code.split(",");
                arr_spec2_code = spec2_code.split(",");
                var arr_barcode = new Array();
                var arr_gb_code = new Array();

                k = 0;
                if (spec1_code != '' && spec2_code != '') {
                    for (var i = 0; i < arr_spec1_code.length; i++) {
                        var jiage = '';
                        var zhong = '';
                        for (var j = 0; j < arr_spec2_code.length; j++) {
                            arr_barcode[k] = $("#" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_barcode").val();
                            arr_gb_code[k] = $("#" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_gb_code").val();
                            jiage = $("#" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sell_price").val();
                            zhong = $("#" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_weight").val();

                            if (jiage !== null && jiage !== undefined && jiage !== '') {
                                var flag = valiFloat(jiage);
                                if (!flag) {
                                    return '价格只允许输入数字，支持三位小数';
                                }
                            }
                            if (zhong !== null && zhong !== undefined && zhong !== '') {
                                var flag = valiFloat(zhong);

                                if (!flag) {

                                    return '重量只允许输入数字，支持三位小数';
                                }
                            }
                            k++;
                        }
                    }
                }
                len = arr_barcode.length;
                for (i = 0; i < len; i++) {
                    for (j = i + 1; j < len; j++) {
                        if (arr_barcode[i] != '' && arr_barcode[i] != undefined && arr_barcode[j] != '' && arr_barcode[j] != undefined) {
                            if (arr_barcode[i] == arr_barcode[j]) {

                                return '输入商品条码有重复';
                                break;
                            }
                        }
                    }
                }

                gb_len = arr_gb_code.length;
                for (i = 0; i < gb_len; i++) {
                    for (j = i + 1; j < gb_len; j++) {
                        if (arr_gb_code[i] != '' && arr_gb_code[i] != undefined && arr_gb_code[j] != '' && arr_gb_code[j] != undefined) {
                            if (arr_gb_code[i] == arr_gb_code[j]) {
                                return '输入国标码有重复';
                                break;
                            }
                        }
                    }
                }

            },
        },
        callback: function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {

                BUI.Message.Alert(data.message, type);
                logStore.load();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }


        }
    }).render();
    //form2

    //get规格1复选框值
    function spec1Check() {
        //spec1_code
        var str1 = "";
        var str2 = "";
        $('input[name="spec1[]"]:checked').each(function () {
            str1 += $(this).val() + ",";
            str2 += $(this).parent().next().html() + ",";
        });
        str1 = str1.substring(0, str1.length - 1);
        str2 = str2.substring(0, str2.length - 1);
        $("#spec1_code").val(str1);
        $("#spec1_name").val(str2);
    }
    //get规格2复选框值
    function spec2Check() {
        //spec2_code
        var str2 = "";
        var str3 = "";
        $('input[name="spec2[]"]:checked').each(function () {
            str2 += $(this).val() + ",";
            str3 += $(this).parent().next().html() + ",";
        });
        str2 = str2.substring(0, str2.length - 1);
        str3 = str3.substring(0, str3.length - 1);
        $("#spec2_code").val(str2);
        $("#spec2_name").val(str3);
    }
    //只允许输入数字，支持两位小数
    function valiFloat(value) {
        var patrn = /^[1-9]\d*\.\d{1,2}|0\.\d{1,2}|^[1-9]\d*$/;
        var patrn1 = /^[1-9]\d*\.\d{1,3}$/;
        var patrn2 = /^[1-9]\d*$/;
        var patrn3 = /0\.\d{1,3}$/;
        var flag = false;
        if (patrn1.exec(value)) {
            flag = true;
        }
        if (patrn2.exec(value)) {
            flag = true;
        }
        if (patrn3.exec(value)) {
            flag = true;
        }
        return flag;
    }


</script>

<script type="text/javascript">
    BUI.use(['bui/tab', 'bui/mask'], function (Tab) {
        var tab = new Tab.TabPanel({
            srcNode: '#tab',
            elCls: 'nav-tabs',
            itemStatusCls: {
                'selected': 'active'
            },
            panelContainer: '#panel'//如果不指定容器的父元素，会自动生成
            //selectedEvent : 'mouseenter',//默认为click,可以更改事件
        });
        tab.render();
    });
    if (type == '3') {
        get_prop_data();
    }
    function PageHead_show_dialog_type(_url, _title, _opts, calljs) {

        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function () {
                eval(calljs + "()");   // get_brand();
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }

    //品牌
    function get_brand() {
        $.ajax({type: 'GET', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/get_brand_js'); ?>',
            success: function (data) {
                var len = data.length;
                var html = '';
                html = "<option value=''>请选择品牌</option>";
                for (var i = 0; i < len; i++) {
                    html += "<option value='" + data[i].brand_code + "'  >" + data[i].brand_name + "</option>";
                }
                $("#brand_code").html(html);
            }
        });
    }

    function get_season() {
        $.ajax({type: 'GET', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/get_season_js'); ?>',
            success: function (data) {
                var len = data.length;
                var html = '';
                html = "<option value=''>请选择季节</option>";
                for (var i = 0; i < len; i++) {
                    html += "<option value='" + data[i].season_code + "'  >" + data[i].season_name + "</option>";
                }
                $("#season_code").html(html);
            }
        });
    }

    function get_year() {
        $.ajax({type: 'GET', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/get_year_js'); ?>',
            success: function (data) {
                var len = data.length;
                var html = '';
                html = "<option value=''>请选择年份</option>";
                for (var i = 0; i < len; i++) {
                    html += "<option value='" + data[i].year_code + "'  >" + data[i].year_name + "</option>";
                }
                $("#year_code").html(html);
            }
        });
    }

    function get_category() {
        $.ajax({type: 'GET', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods/get_category_js'); ?>',
            success: function (data) {
                var len = data.length;
                var html = '';
                html = "<option value=''>请选择类别</option>";
                for (var i = 0; i < len; i++) {
                    html += "<option value='" + data[i].category_code + "'  >" + data[i].category_name + "</option>";
                }
                $("#category_code").html(html);
            }
        });
    }
    function spec_checked(type,_this){
        if(type === 'spec1'){
            if ($(_this).attr('checked') === 'checked') {
                add_barcode('spec1', _this);
            } else {
                remove_barcode('spec1', _this);
                if(confirm("没被选择的规格会被移除，确定移除吗？")){
                    $(_this).parent().parent('.spec1-code-name').remove();
                }else{
                    $(_this).attr('checked',true);
                    spec_checked('spec1',_this);
                }
            }
        }else{
            if ($(_this).attr('checked') === 'checked') {
                add_barcode('spec2', _this);
            } else {
                remove_barcode('spec2', _this);
                if(confirm("没被选择的规格会被移除，确定移除吗？")){
                    $(_this).parent().parent('.spec1-code-name').remove();
                }else{
                    $(_this).attr('checked',true);
                    spec_checked('spec2',_this);
                }
            }
        }
    }
    //条码联动
    $("#spec1_html :checkbox").click(function () {
        spec_checked('spec1',this);
    });
    $("#spec2_html :checkbox").click(function () {
        spec_checked('spec2',this);
    });

    function add_barcode(type, select) {
        //spec1
        spec1Check();
        spec2Check();

        if (type == 'spec1') {
            var spec1_name = $(select).parent().next().html();
            var spec1_code = $(select).val();
            var spec2_name = $("#spec2_name").val();
            var spec2_code = $("#spec2_code").val();
        } else {
            var spec1_name = $("#spec1_name").val();
            var spec1_code = $("#spec1_code").val();
            var spec2_name = $(select).parent().next().html();
            var spec2_code = $(select).val();
        }
        var html = create_barcode_html(spec1_code, spec2_code, spec1_name, spec2_name);
        $("#tiaoma").append(html);
    }
    function remove_barcode(type, select) {
        //spec1
        var i = 0;
        var spec_name = $(select).parent().next().html();
        if (type == 'spec2') {
            i = 1;
        }
        $('#tiaoma tr').each(function () {
            if ($(this).find('td').eq(i).html() == spec_name) {
                $(this).remove();
            }
        });
        spec1Check();
        spec2Check();


    }
    function create_barcode_html(spec1_code, spec2_code, spec1_name, spec2_name) {
        arr_spec1_code = spec1_code.split(",");
        arr_spec2_code = spec2_code.split(",");
        arr_spec1_name = spec1_name.split(",");
        arr_spec2_name = spec2_name.split(",");
        var html = '';
        var sell_price = $("#sell_price").val() == '' ? '0.000' : $("#sell_price").val();
        var weight = $("#weight").val() == '' ? '0.000' : $("#weight").val();
        var cost_price = cost_price_status == 1 ? '0.000' : '*****';
        for (var i = 0; i < arr_spec1_code.length; i++) {
            for (var j = 0; j < arr_spec2_code.length; j++) {
                if (arr_spec1_code[i] != '' && arr_spec2_code[j] != '') {
                    barcord1 = '';
                    gb_code = '';
                    sku_remark = '';
                    price = sell_price;
                    //  weight = weight;
                    var sku = $("#goods_code").val() + arr_spec1_code[i] + arr_spec2_code[j];
                    if(barcode == '0' && spec_power== '0' ){
                        barcord1 = $("#goods_code").val();
                    }
                    <?php foreach ($response['data']['barcode'] as $k => $v) { ?>
                    sku1 = '<?php echo $v['sku']; ?>';
                    if (sku == sku1) {
                        barcord1 = '<?php echo $v['barcode']; ?>';
                        gb_code = '<?php echo $v['gb_code']; ?>';
                        sku_remark = '<?php echo @$v['remark']; ?>';
                        price = '<?php echo @$v['price']; ?>';
                        weight = '<?php echo @$v['weight']; ?>';
                        cost_price = '0.00';
                    }
                    <?php } ?>

                    html += "<tr><td >" + arr_spec1_name[i] + "</td><td >" + arr_spec2_name[j] + "</td><td >" + sku + "<input  id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sku' name= '" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sku' value='" + sku + "'  type='hidden' /></td><td ><span class='shuru' style='display:;'><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_barcode' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_barcode' style='width:98%;' value='" + barcord1 + "' onBlur= 'inputbarcord(this);' type='text' /></span></td><td ><span class='shuru' style='display:;'><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_gb_code' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_gb_code' style='width:98%;' value='" + gb_code + "' onBlur= 'inputgbcode(this);' type='text' /></span></td><td ><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sell_price' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sell_price' style='width:98%;' value='" + price + "' onblur='inputprice(this);' type='text' /></td><td><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_cost_price' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_cost_price' style='width:98%;' onblur='inputcostprice(this);' value='"+cost_price+"' type='text' /></td> <td ><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_weight' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_weight' style='width:98%;' onblur='inputweight(this);' value='" + weight + "' type='text' /></td><td><input name='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sku_remark' id='" + arr_spec1_code[i] + "_" + arr_spec2_code[j] + "_sku_remark' style='width:98%;' value='" + sku_remark + "' type='text' /></td></tr>";
                    //html += "<tr><td >"+arr_spec1_name[i]+"</td><td >"+arr_spec2_name[j]+"</td><td >"+sku+"<input  id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' name= '"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku' value='"+sku+"'  type='hidden' /></td><td ><span class='shuru' style='display:;'><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_barcode' style='width:98%;' value='"+barcord1+"' onBlur= 'inputbarcord(this);' type='text' /></span></td><td >"+sell_price+"</td><td >"+weight+"</td><td><input name='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' id='"+arr_spec1_code[i]+"_"+arr_spec2_code[j]+"_sku_remark' style='width:98%;' value='"+sku_remark+"' type='text' /></td></tr>";
                }
            }
        }
        return html;
    }




    function tiaolist() {
        //alert($(this).val());
        //alert($(this).parent().text());
        $("#tiaoma").html("");
        $("#spec1_code").val("");
        $("#spec2_code").val("");
        $("#spec1_name").val("");
        $("#spec2_name").val("");
        spec1Check();
        spec2Check();
        var spec1_code = $("#spec1_code").val();
        var spec2_code = $("#spec2_code").val();
        var spec1_name = $("#spec1_name").val();
        var spec2_name = $("#spec2_name").val();

        arr_spec1_code = spec1_code.split(",");
        arr_spec2_code = spec2_code.split(",");
        arr_spec1_name = spec1_name.split(",");
        arr_spec2_name = spec2_name.split(",");

        var html = create_barcode_html(spec1_code, spec2_code, spec1_name, spec2_name);

        $("#tiaoma").html(html);
    }
    function  inputprice(obj) {
        var value = $(obj).val();
        if (value !== null && value !== undefined && value !== '') {
            var flag = valiFloat(value);
            if (!flag) {
                alert('价格只允许输入数字，支持三位小数');
                // $(obj).val('');
            }
        }
    }
    function inputcostprice(obj) {
        var value = $(obj).val();
        if (value !== null && value !== undefined && value !== '') {
            var flag = valiFloat(value);
            if (!flag) {
                alert('成本价只允许输入数字，支持三位小数');
                // $(obj).val('');
            }
        }
    }
    function inputweight(obj) {
        var value = $(obj).val();

        if (value !== null && value !== undefined && value !== '') {

            var flag = valiFloat(value);
            if (!flag) {
                alert('重量只允许输入数字，支持三位小数');
                //$(obj).val('');
            }
        }
    }
    function inputbarcord(obj) {
        // $(obj).parent().hide();
        var value = $(obj).val();
        var spec = $(obj).attr('name');
        if (value != '') {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods/barcode_exist'); ?>',
                data: {barcode: value, goods_code: "<?php echo $response['data']['goods_code']; ?>", spec: spec},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
                        //$(obj).val('');
                        //value = '填写条码';
                        // $(obj).parent().parent().find(".biao").text('填写条码');
                        //$(obj).parent().parent().find(".biao").show();
                    } else {

                        //BUI.Message.Alert(ret.message, type);
                        //$(obj).parent().parent().find(".biao").text(value);
                        //$(obj).parent().parent().find(".biao").show();
                    }
                }
            });
        } else {

            //value = '填写条码';
            //$(obj).parent().parent().find(".biao").text(value);
            //$(obj).parent().parent().find(".biao").show();
        }

    }
    function inputgbcode(obj) {
        var value = $(obj).val();
        var spec = $(obj).attr('name');
        if (value != '') {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods/gb_code_exist'); ?>',
                data: {gb_code: value, goods_code: "<?php echo $response['data']['goods_code']; ?>", spec: spec},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
                    } else {
                    }
                }
            });
        } else {
        }

    }
    function labelbarcord(obj) {

        $(obj).hide();
        $(obj).parent().find(".shuru").show();
        return;
    }
    $(".stage").click(function () {
        var barcord = $(this).text();
        // alert(barcord);
    });
    parent.add_c = function(category_code){
        $('#category_code').find('option[value="'+category_code+'"]').attr('selected',true);
        $('#category_code').parent().find('.valid-text').html('');
    }
    function get_prop_data() {
        var goods_code = $("#goods_code").val();
        var url = "?app_act=prm/goods_property/goods_prop";
        $.getJSON(url,{goods_code:goods_code,type:'diy_goods'}, function (result) {
            $("#form4_div").html(result.data);
        });
    }

    $("#goods_prop").click(function () {
        get_prop_data();
    });

    $("#form4_submit").live('click', function () {
        var goods_code = $("#goods_code").val();
        var prop_data = $("#form4").serialize();
        var url = '?app_act=prm/goods_property/save_goods_prop&'+prop_data;
        var post_data = {'goods_code': goods_code};
        $.post(url, post_data, function (result_json) {
            var result = eval('(' + result_json + ')');
            if (result.status < 0) {
                alert(result.message);
            } else {
                top.BUI.Message.Tip('更新成功', 'info');
                get_prop_data();
            }
        });
    });

    var selectPopWindowp_code = {
        dialog: null,
        callback: function(value) {
            var category_name = value[0]['category_name'];
            var category_code = value[0]['category_code'];
            $('#category_code').val(category_code);
            $('#category_name').val(category_name);
            if (selectPopWindowp_code.dialog != null) {
                selectPopWindowp_code.dialog.close();
            }
        }
    };

    var selectPopWindowp_code_brand = {
        dialog: null,
        callback: function(value) {
            var brand_name = value[0]['brand_name'];
            var brand_code = value[0]['brand_code'];
            $('#brand_code').val(brand_code);
            $('#brand_name').val(brand_name);
            if (selectPopWindowp_code_brand.dialog != null) {
                selectPopWindowp_code_brand.dialog.close();
            }
        }
    };


    $('#category_code_select_img').click(function() {
        selectPopWindowp_code.dialog = new ESUI.PopSelectWindow('?app_act=prm/goods/category', 'selectPopWindowp_code.callback', {title: '选择商品分类', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
    });

    $('#brand_code_select_img').click(function() {
        selectPopWindowp_code_brand.dialog = new ESUI.PopSelectWindow('?app_act=prm/goods/brand', 'selectPopWindowp_code_brand.callback', {title: '选择商品品牌', width: 900, height:500 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
    });

    BUI.use('bui/uploader', function (Uploader) {
        var url = "<?php echo $response['upload_path']; ?>";
        var filetype = {
            ext: ['.jpg,.png,.gif', '文件类型只能为{0}'],
            maxSize: [2048, '文件大小不能大于2M'],
            //minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!'],
        };

        var uploader = new Uploader.Uploader({
            type: 'iframe',
            render: '#J_Uploader',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#goods_img").val(result.url);
                $("#goods_thumb_img").val(result.thumb_url);
                BUI.Message.Alert("图片上传成功", "success");
                $('#img_show').html('<img src="'+result.thumb_url+'"  />');
            },
            //失败的回调
            error: function (result) {
                console.log("error" + result);
                BUI.Message.Alert("上传失败", "error");
            }
        }).render();
    });
    $(function(){
        $("#img_show").on('mouseover','img',function(e){
            var img_src = $('#goods_img').val();
            console.log(img_src);
            var tooltip = "<div id='tooltipimg' style='position:fixed;top:25%;left:25%;'> <img  width='500px' height='auto' src='"+ img_src +"' alt='原图'/> </div>";
            //创建 div 元素
            $('#panel').append(tooltip);
        }).mouseout(function(){
            $("#tooltipimg").remove(); //移除
        })
    })
</script>