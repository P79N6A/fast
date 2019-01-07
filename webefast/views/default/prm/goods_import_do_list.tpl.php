<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .table_panel td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 6px;
        text-align: left;
        width:200px;
        vertical-align: top;
    }

    #tab{ margin-bottom:5px;}
    .bui-tab-item{ cursor:pointer;}
    .title{ color:#008000; margin:10px 5px;}
    .table_panel{ width:100%;}
    .table_panel sup{ color:#de1330;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品信息导入',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<table cellspacing="0" class="table table-bordered">
    <!--
      <tr>
       <td> 规格1 <input type="text" id="url1" name="url1"></td>
       <td><span id="J_Uploader1"></span>  </td>
        <td><input  id="submit_1" class="button button-primary" type="button" value="导入"></td>
        <td><a class="button" target="_blank" href="?app_act=base/shelf/tplDownload&code=goods_shelf_record">模版下载</a> </td>
      </tr>
        <tr>
       <td> 规格2 <input type="text" id="url2" name="url2"></td>
       <td><span id="J_Uploader2"></span>  </td>
       <td><input  id="submit_2" class="button button-primary" type="button" value="导入"></td>
        <td><a class="button" target="_blank" href="?app_act=base/shelf/tplDownload&code=goods_shelf_record">模版下载</a> </td>
       </tr>
    -->
<!--    <tr>
        <td> 商品信息 <input type="hidden" id="url7" name="url7"></td>
        <td><div class="upload7"><span id="J_Uploader7"></span> </div> </td>
        <td><input  id="submit_7" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="?app_act=prm/goods_import/tplDownload&code=base_goods_initia">模版下载</a>
        </td>
        <td></td>
    </tr>
    <tr>
        <td> 商品规格导入 <input type="hidden" id="url8" name="url8"></td>
        <td><div class="upload8"><span id="J_Uploader8"></span> </div> </td>
        <td><input  id="submit_8" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="?app_act=prm/goods_import/tplDownload&code=base_barcord_initia">模版下载</a>
        </td>
        <td></td>
    </tr>-->
    <tr>
        <td>   商品扩展属性导入 <input type="hidden" id="url9" name="url9"></td>
        <td><div class="upload9"><span id="J_Uploader9"></span> </div> </td>
        <td><input  id="submit_9" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_property.xls",1) ?>">模版下载</a>
        </td>
        <td>商品扩展属性导入</td>
    </tr>
    <tr>
        <td>   规格1（别名）导入 <input type="hidden" id="url10" name="url10"></td>
        <td><div class="upload10"><span id="J_Uploader10"></span> </div> </td>
        <td><input  id="submit_10" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="<?php echo get_excel_url("spec.xls",1) ?>">模版下载</a>
        </td>
        <td>商品属性，规格1代码、规格1名称导入</td>
    </tr>
    <tr>
        <td>   规格2（别名）导入 <input type="hidden" id="url11" name="url11"></td>
        <td><div class="upload11"><span id="J_Uploader11"></span> </div> </td>
        <td><input  id="submit_11" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="<?php echo get_excel_url("spec.xls",1) ?>">模版下载</a>
        </td>
        <td>商品属性，规格2代码、规格2名称导入
        </td>
    </tr>
    <tr>
        <td> 混合数据导入（按规格名称） <input type="hidden" id="url12" name="url12"></td>
        <td><div class="upload12"><span id="J_Uploader12"></span> </div> </td>
        <td><input  id="submit_12" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="?app_act=prm/goods_import/hunhemoban">模版下载</a>
        </td>
        <td>商品基本信息、规格名称、扩展属性的一次性导入</td>
    </tr>
    <tr <?php if ($response['product_version'] == 0) { ?> style="display:none;" <?php } ?> >
        <td> 混合数据导入（按规格代码） <input type="hidden" id="url13" name="url12"></td>
        <td><div class="upload13"><span id="J_Uploader13"></span> </div> </td>
        <td><input  id="submit_13" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="?app_act=prm/goods_import/hunhemoban&type=code">模版下载</a>
        </td>
        <td>商品基本信息、规格代码、扩展属性的一次性导入</td>
    </tr>
    <tr >
        <td> 商品条形码信息价格导入 <input type="hidden" id="url14" name="url14"></td>
        <td><div class="upload14"><span id="J_Uploader14"></span> </div> </td>
        <td><input  id="submit_14" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_barcode_price.xlsx",1) ?>">模版下载</a>
        </td>
        <td>商品条形码吊牌价、成本价、重量、描述、国标码一次性导入</td>
    </tr>
    <tr>
        <td>套餐商品导入<input type="hidden" id="url15" name="url15"></td>
        <td><div class="upload15"><span id="J_Uploader15"></span> </div> </td>
        <td><input  id="submit_15" class="button button-primary" type="button" value="导入"></td>
        <td>
            <?php if($response['spec_power']['spec_power'] == 0){ ?>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_combo.xlsx",1) ?>">模版下载</a>
            <?php }else{ ?>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_combo_new.xlsx",1) ?>">模版下载</a>
            <?php }?>
        </td>
        <td>提供套餐商品以及套餐子商品批量导入，导入成功后进入商品套餐列表查看</td>
    </tr>
    <tr>
        <td>组装商品导入<input type="hidden" id="url16" name="url16"></td>
        <td><div class="upload16"><span id="J_Uploader16"></span> </div> </td>
        <td><input  id="submit_16" class="button button-primary" type="button" value="导入"></td>
        <td>
            <?php if($response['spec_power']['spec_power'] == 0){ ?>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_diy.xlsx",1) ?>">模版下载</a>
             <?php }else{ ?>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_diy_new.xlsx",1) ?>">模版下载</a>
            <?php }?>
        </td>
        <td>提供组装商品以及组装子商品批量导入，导入成功后进入商品列表查看</td>
    </tr>
    <tr>
        <td>商品价格导入<input type="hidden" id="url17" name="url17"></td>
        <td><div class="upload17"><span id="J_Uploader17"></span> </div> </td>
        <td><input  id="submit_17" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="<?php echo get_excel_url("goods_price.xlsx",1) ?>">模版下载</a>
        </td>
        <td>提供商品编码、吊牌价、成本价、批发价、进货价、最低售价批量导入，导入成功后进入商品列表查看(单次导入仅支持5000条)</td>
    </tr>
    <tr>
        <td>商品信息更新导入<input type="hidden" id="url18" name="url18"></td>
        <td><div class="upload18"><span id="J_Uploader18"></span> </div> </td>
        <td><input  id="submit_18" class="button button-primary" type="button" value="导入"></td>
        <td>
            <a class="button" target="_blank" href="?app_act=prm/goods_import/goods_update_ex">模版下载</a>
        </td>
        <td>根据商品编码唯一适配，商品基本信息、规格代码、扩展属性根据输入内容进行更新（为空不处理）</td>
    </tr>
</table>

<div class="result1" style="display: block">
</div>
<div id="tab">
</div>

<div id='p1'>
    <table class='table_panel'  >
        <tr><td style="width:10%">导入列</td><td style="width:10%">是否必须</td><td style="width:30%">示例值</td><td>详细说明</td></tr>
        <tr><td>规格代码<sup>*</sup></td><td>是</td><td>G001</td><td>
                若在系统中<strong>不存在</strong>，则新增规格数据；<br>
                若在系统中<strong>已存在</strong>，则更新规格数据；
            </td></tr>
        <tr><td>规格名称<sup>*</sup></td><td>是</td><td>2W/黄</td><td>不允许为空。需更新此字段；</td></tr>
        <tr><td>描述</td><td>否</td><td>与619名称重复，停止使用</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
    </table>
</div>

<div id='p2'>
    <table class='table_panel'  >
        <tr><td style="width:10%">导入列</td><td style="width:10%">是否必须</td><td style="width:30%">示例值</td><td>详细说明</td></tr>
        <tr>
            <td>商品编码<sup>*</sup></td><td>是</td><td>101</td><td>
                若在系统中<strong>不存在</strong>，则新增商品规格数据；<br>
                若在系统中<strong>已存在</strong>，则更新商品规格数据；
            </td>
        </tr>
        <tr><td>规格1名称<sup>*</sup></td><td>是</td><td>欢乐兔</td><td>不允许为空。需更新此字段；</td></tr>
        <tr><td>规格2名称<sup>*</sup></td><td>是</td><td>203x229cm</td><td>不允许为空。需更新此字段；</td></tr>
        <tr>
            <td>商品条形码</td><td>否</td><td>116011320300430</td><td>
                商品条形码，<strong>不允许重复</strong>；<br>
                商品条形码，<strong>为空</strong>就<strong>不更新</strong>此条数据的商品条形码字段，其它字段根据填入数据新增/更新。
            </td>
        </tr>
        <tr>
            <td>价格（元）</td><td>否</td><td>398</td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr>
            <td>重量（克）</td><td>否</td><td>2000</td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
    </table>
</div>

<div id='p3'>
    <table class='table_panel'  >
        <tr><td style="width:10%">导入列</td><td style="width:10%">是否必须</td><td style="width:30%">示例值</td><td>详细说明</td></tr>
        <tr>
            <td>商品编码<sup>*</sup></td><td>是</td><td>G001</td><td>
                若在系统中<strong>不存在</strong>，则新增商品数据；<br>
                若在系统中<strong>已存在</strong>，则更新商品数据；
            </td>
        </tr>
        <tr><td>商品名称<sup>*</sup></td><td>是</td><td>男士短袖T恤夏 纯棉白色潮韩版修身半袖衣服字母印花圆领运动体恤</td><td>不允许为空。需更新此字段；</td></tr>
        <tr>
            <td>商品简称</td><td>否</td><td>男士短袖T恤夏 </td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr><td>商品分类<sup>*#</sup></td><td>是</td><td>衣服</td><td>商品分类名称，若全匹配不成功，则创建分类档案</td></tr>
        <tr><td>商品品牌<sup>*#</sup></td><td>是</td><td>探路者</td><td>商品品牌名称，若全匹配不成功，则创建品牌档案</td></tr>
        <tr><td>商品季节<sup>#</sup></td><td>否</td><td>夏季</td><td>商品季节名称，若全匹配不成功，则创建季节档案</td></tr>
        <tr><td>商品年份<sup>#</sup></td><td>否</td><td>2015</td><td>商品年份名称，若全匹配不成功，则创建年份档案</td></tr>
        <tr><td>商品属性</td><td>否</td><td>普通商品</td><td>
                <strong>为空</strong>就不更新； <br><strong>不为</strong>空，则更新；
            </td></tr>
        <tr><td>商品状态</td><td>否</td><td>在售</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr>
            <td>商品重量（克）</td><td>否</td><td>800</td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr><td>吊牌价</td><td>否</td><td>399</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>成本价</td><td>否</td><td>268</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>批发价</td><td>否</td><td>299</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>进货价</td><td>否</td><td>258</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>商品描述</td><td>否</td><td>夏季热卖款</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>规格1名称<sup>*</sup></td><td>是</td><td>白色</td><td>不允许为空。需更新此字段；</td></tr>
        <tr><td>规格2名称<sup>*</sup></td><td>是</td><td>S</td><td>不允许为空。需更新此字段；</td></tr>
        <tr><td>商品条形码<sup>*</sup></td><td>是</td><td>BL1451002A4</td><td>商品条形码，<strong>不允许重复</strong>，<strong>不允许为空</strong>。需更新此字段；</td></tr>
        <tr><td>商品出厂名称</td><td>否</td><td>    </td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>扩展属性</td><td>否</td><td>纯棉 100%</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
    </table>
</div>

<div id='p4'>
    <table class='table_panel'  >
        <tr><td style="width:10%">导入列</td><td style="width:10%">是否必须</td><td style="width:30%">示例值</td><td>详细说明</td></tr>
        <tr>
            <td>套餐条形码<sup>*</sup></td><td>是</td><td>SPTC001001002</td>
            <td>不允许为空。此值需要维护到销售平台<br>若在系统中<strong>不存在</strong>，则新增；<br>若在系统中<strong>已存在</strong>，则更新</td>
        </tr>
        <tr><td>套餐名称<sup>*</sup></td><td>是</td><td>芦荟胶三支装</td><td>不允许为空。需更新此字段</td></tr>
        <?php if($response['spec_power']['spec_power'] == 1){ ?>
        <tr><td>规格1<sup>*</sup></td><td>是</td><td>001</td><td>不允许为空。需更新此字段，为套餐规格1代码</td></tr>
        <tr><td>规格2<sup>*</sup></td><td>是</td><td>001</td><td>不允许为空。需更新此字段，为套餐规格2代码</td></tr>
        <?php } ?>
        <tr><td>子商品条形码<sup>*</sup></td><td>是</td><td>ZRZH0062</td><td>不允许为空。需更新此字段，为套餐明细商品条形码</td></tr>
        <tr><td>子商品数量<sup>*</sup></td><td>是</td><td>3</td><td>不允许为空。需更新此字段，为套餐明细商品数量</td></tr>
        <tr><td>子商品单价</td><td>否</td><td>398.00</td><td>需要更新此字段，为套餐明细商品价格，不填取值商品条形码吊牌价</td></tr>
    </table>
</div>

<div id='p5'>
    <table class='table_panel'  >
        <tr><td style="width:10%">导入列</td><td style="width:10%">是否必须</td><td style="width:30%">示例值</td><td>详细说明</td></tr>
        <tr><td>组装条形码<sup>*</sup></td><td>是</td><td>A001</td><td>不允许为空。不允许重复<br>有值则新增</td></tr>
        <tr><td>组装名称<sup>*</sup></td><td>是</td><td>组装款</td><td>不允许为空。需更新此字段</td></tr>
        <?php if($response['spec_power']['spec_power'] == 1){ ?>
        <tr><td>规格1<sup>*</sup></td><td>是</td><td>001</td><td>不允许为空。需更新此字段，为规格1代码</td></tr>
        <tr><td>规格2<sup>*</sup></td><td>是</td><td>001</td><td>不允许为空。需更新此字段，为规格2代码</td></tr>
        <?php } ?>
        <tr><td>分类<sup>*</sup></td><td>是</td><td>内衣</td><td>不允许为空。需更新此字段</td></tr>
        <tr><td>品牌<sup>*</sup></td><td>是</td><td>B+</td><td>不允许为空。需更新此字段</td></tr>
        <tr><td>子商品条形码<sup>*</sup></td><td>是</td><td>0000001034</td><td>不允许为空。不允许重复，有值则新增</td></tr>
        <tr><td>子商品数量<sup>*</sup></td><td>是</td><td>3</td><td>不允许为空。需更新此字段</td></tr>
        <tr><td>子商品单价</td><td>否</td><td>38</td><td>不允许为空。需更新此字段</td></tr>
    </table>
</div>

<div id='p6'>
    <table class='table_panel'  >
        <tr>
            <td style="width:10%">导入列</td>
            <td style="width:10%">是否必须</td><td style="width:30%">示例值</td>
            <td>详细说明</td>
        </tr>
        <tr>
            <td>商品编码<sup>*</sup></td>
            <td>是</td>
            <td>G001</td>
            <td>
                若在系统中<strong>不存在</strong>，则新增商品数据；<br>
                若在系统中<strong>已存在</strong>，则更新商品数据；
            </td>
        </tr>
        <tr>
            <td>商品名称<sup>*</sup></td>
            <td>是</td>
            <td>男士短袖T恤夏 纯棉白色潮韩版修身半袖衣服字母印花圆领运动体恤</td>
            <td> <strong>为空</strong>就不更新；<br>
                <strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr>
            <td>商品简称</td><td>否</td><td>男士短袖T恤夏 </td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr><td>商品分类</td><td>是</td><td>衣服</td>
            <td>商品分类名称：<br/>
                <strong>为空</strong>就不更新；<br/>
                <strong>不为</strong>空，则更新;<br/>
                若匹配不成功，则创建分类档案
            </td>
        </tr>
        <tr><td>商品品牌</td><td>是</td><td>探路者</td>
            <td>商品品牌名称：<br/>
                <strong>为空</strong>就不更新；<br/>
                <strong>不为</strong>空，则更新;<br/>
                若匹配不成功，则创建品牌档案
            </td></tr>
        <tr><td>商品季节</td><td>否</td><td>夏季</td>
            <td>商品季节名称：<br/>
                <strong>为空</strong>就不更新；<br/>
                <strong>不为</strong>空，则更新；若匹配不成功，则创建季节档案
            </td></tr>
        <tr><td>商品年份</td><td>否</td><td>2015</td>
            <td>
                商品年份名称：<br/>
                <strong>为空</strong>就不更新；<br/>
                <strong>不为</strong>空，则更新；<br/>
                若匹配不成功，则创建年份档案
            </td></tr>
        <tr><td>商品属性</td><td>否</td><td>普通商品</td><td>
                <strong>为空</strong>就不更新； <br><strong>不为</strong>空，则更新；
            </td></tr>
        <tr><td>商品状态</td><td>否</td><td>在售</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr>
            <td>商品重量（克）</td><td>否</td><td>800</td><td>
                <strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；
            </td>
        </tr>
        <tr><td>吊牌价</td><td>否</td><td>399</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>成本价</td><td>否</td><td>268</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>批发价</td><td>否</td><td>299</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>进货价</td><td>否</td><td>258</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>商品描述</td><td>否</td><td>夏季热卖款</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>规格1名称<sup>*</sup></td><td>是</td><td>白色</td><td><strong>不允许为空</strong>。需更新此字段；</td></tr>
        <tr><td>规格2名称<sup>*</sup></td><td>是</td><td>S</td><td><strong>不允许为空</strong>。需更新此字段；</td></tr>
        <tr><td>商品条形码<sup>*</sup></td><td>是</td><td>BL1451002A4</td><td>商品条形码，<strong>不允许为空</strong>。需更新此字段；</td></tr>
        <tr><td>商品出厂名称</td><td>否</td><td>    </td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
        <tr><td>扩展属性</td><td>否</td><td>纯棉 100%</td><td><strong>为空</strong>就不更新；<br><strong>不为</strong>空，则更新；</td></tr>
    </table>
</div>

<script type="text/javascript">
    BUI.use('bui/tab', function (Tab) {
        var tab = new Tab.Tab({
            render: '#tab',
            elCls: 'nav-tabs',
            autoRender: true,
            children: [
                {text: '规格（别名）导入说明', value: '1'},
                {text: '商品规格导入说明', value: '2'},
                {text: '混合数据导入说明', value: '3'},
                {text: '套餐商品导入说明', value: '4'},
                {text: '组装商品导入说明', value: '5'},
                {text: '商品信息更新导入说明', value: '6'}
            ]
        });
        tab.on('selectedchange', function (ev) {
            var item = ev.item;
            id = item.get('value');
            show_hide(id);
            $('#log').text(item.get('text') + ' ' + item.get('value'));
        });
        tab.setSelected(tab.getItemAt(0));
    });

    //显示、隐藏说明
    function show_hide(_num) {
        for (var i = 1; i <= 5; i++) {
            if (i == _num) {
                $("#p" + i).show();
                continue;
            }
            $("#p" + i).hide();
        }
    }

    BUI.use('bui/uploader', function (Uploader) {
        /**
         * 返回数据的格式
         *
         *  默认是 {url : 'url'},否则认为上传失败
         *  可以通过isSuccess 更改判定成功失败的结构
         */
        var url = '?app_act=prm/goods_import/import_upload';
        var filetype = {
            ext: ['.csv,.xlsx,.xls', '文件类型只能为{0}'],
            maxSize: [20480, '文件大小不能大于20M'],
            //  minSize: [1, '文件最小不能小于1k!'],
            max: [5, '文件最多不能超过{0}个！'],
            min: [1, '文件最少不能少于{0}个!']
        };
        var uploader1 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader1',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url1").val(result.url);
                /*
                 $.post("?app_act=prm/goods_shelf/import_action", result, function(data){
                 $(".upload1").hide()
                 $(".result1").show()

                 $(".result1").html("导入成功: "+data.success+"行<br>导入失败列表:<br>"+data.faild)

                 }, "json")
                 */
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader2 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader2',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url2").val(result.url);

            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader7 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader7',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url7").val(result.url);
                //$(".upload7").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
        var uploader8 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader8',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url8").val(result.url);
                //$(".upload8").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
        var uploader9 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader9',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url9").val(result.url);
                //$(".upload9").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader10 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader10',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url10").val(result.url);
                //$(".upload9").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();


        var uploader11 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader11',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url11").val(result.url);
                //$(".upload9").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader12 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader12',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                //console.log(result);
                $("#url12").val(result.url);
                //$(".upload9").hide();
            },
            //isSuccess : function(result){},
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader13 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader13',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url13").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader14 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader14',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url14").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader15 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader15',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url15").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();

        var uploader16 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader16',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url16").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
        //商品价格导入
        var uploader17 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader17',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url17").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
        //商品信息更新导入
        var uploader18 = new Uploader.Uploader({
            'type': 'iframe',
            render: '#J_Uploader18',
            url: url,
            rules: filetype,
            multiple: false,
            //可以直接在这里直接设置成功的回调
            success: function (result) {
                $("#url18").val(result.url);
            },
            //失败的回调
            error: function (result) {
                BUI.Message.Alert("失败", "error");
            }
        }).render();
    });
    $(document).ready(function () {
        var default_opts = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14','15','16','17','18'];
        var default_url = [
            '?app_act=prm/goods_import/base_spec1',
            '?app_act=prm/goods_import/base_spec2',
            '',
            '',
            '',
            '',
            '?app_act=prm/goods_import/base_goods',
            '?app_act=prm/goods_import/base_barcode',
            '?app_act=prm/goods_import/base_goods_property',
            '?app_act=prm/goods_import/base_spec&type=1',
            '?app_act=prm/goods_import/base_spec&type=2',
            '?app_act=prm/goods_import/base_goods_barcode_property',
            '?app_act=prm/goods_import/base_goods_barcode_property&type=code',
            '?app_act=prm/goods_import/base_goods_barcode_price',
            '?app_act=prm/goods_import/goods_combo',
            '?app_act=prm/goods_import/goods_diy',
            '?app_act=prm/goods_import/goods_price',
            '?app_act=prm/goods_import/goods_import_exec&type=update',
        ];
        for (var i in default_opts) {
            var f = default_opts[i];
            var r = default_url[i];
            btn_init_opt(f, r);
        }
        /*
         $("#submit").click(function(){
         var url = $("#url").val();
         if(url == ''){
         BUI.Message.Alert('请上传excel文件', 'error');
         return false;
         }

         var result = {
         "url": url

         };
         $.post("?app_act=prm/goods_shelf/import_action", result, function(data){
         $(".upload1").hide()
         $(".result1").show()

         $(".result1").html("导入成功: "+data.success+"行<br>导入失败列表:<br>"+data.faild)

         }, "json")

         })
         */
    });
    //初始化
    function btn_init_opt(id, r) {
        $("#submit_" + id).click(function () {
            url = $("#url" + id).val();
            if (url == '') {
                BUI.Message.Alert('请先上传文件', 'error');
                return false;
            }
            //r = "?app_act=prm/goods_shelf/import_action1";
            var params = {"url": url};
            $.post(r, params, function (data) {
                //$(".result1").show()
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(data.message, type);
                    location.reload();
                } else {
                    BUI.Message.Alert(data.message, type);
                }
                // BUI.Message.Alert('导入成功：', 'success');
                // $(".result1").html("导入成功: "+data.success+"行<br>导入失败列表:<br>"+data.faild)
            }, "json");
        });
    }
</script>