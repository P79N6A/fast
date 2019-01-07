<style>
    .button-common {
        padding: 0 4px;
        font-size: 12px;
        line-height: 17px;
    }
    #exprot_detail{
        width:80px;
    }
    #year_money{
        data-rules:'{}';
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => $response['title'],
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_detail',
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        )
    ),
    'fields' => array(
        array(
            'label' => '月份',
            'type' => 'input',
            'id'=>'year_month',
            'value'=>$request['year_month']
        ),
        array(
            'label' => '店铺',
            'type' => 'select',
            'id' => 'shop_code',
            'value'=>$request['shop_code'],
            'data' => array_merge(array(array('shop_code'=>'','shop_name'=>'请选择店铺')),load_model('base/ShopModel')->get_purview_shop())
        ),
        array(
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'goods_code',
        )
    )
));
?>
<div class="panel record_table" id="panel_html">
</div>
<div class="panel">
    <?php
    render_control('TabPage', 'TabPage1', array(
        'tabs' => array(
            array('title' => '商品条形码维度', 'active' => true, 'id' => 'goods_barcode'),
            array('title' => '商品编码维度', 'active' => false, 'id' => 'goods_code_list'),
        ),
        'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
    ));
    ?>
    <div id="TabPage1Contents">
        <div>
            <?php
            render_control('DataTable', 'table_list', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '图片',
                            'field' => 'goods_thumb_img',
                            'width' => '70',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品条形码',
                            'field' => 'barcode',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品名称',
                            'field' => 'goods_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品编码',
                            'field' => 'goods_code',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec']['goods_spec1'],
                            'field' => 'spec1_code_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec']['goods_spec2'],
                            'field' => 'spec2_code_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '金额',
                            'field' => 'money',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数量',
                            'field' => 'num',
                            'width' => '150',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'oms/SellRecordModel::barcode_well_by_page',
                'export' => array('id' => 'exprot_detail', 'conf' => 'well_info_barcode', 'name' => '月度畅销商品（条码）','export_type'=>'file'),
                'queryBy' => 'searchForm',
                'params'=>array('filter'=>array('order_by'=>$request['order_by'],'year_month'=>$request['year_month'],'shop_code'=>$request['shop_code']))
            ));
            ?>
        </div>
        <div>
            <?php
            render_control('DataTable', 'table_goods', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '图片',
                            'field' => 'goods_thumb_img',
                            'width' => '70',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品名称',
                            'field' => 'goods_name',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品编码',
                            'field' => 'goods_code',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '金额',
                            'field' => 'money',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数量',
                            'field' => 'num',
                            'width' => '150',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'oms/SellRecordModel::goods_well_by_page',
                'export' => array('id' => 'exprot_list', 'conf' => 'well_info_good', 'name' => '月度畅销商品（商品）','export_type' => 'file'),
                'queryBy' => 'searchForm',
                'params'=>array('filter'=>array('order_by'=>$request['order_by'],'year_month'=>$request['year_month'],'shop_code'=>$request['shop_code']))
            ));
            ?>
        </div>
    </div>

</div>
<script type="text/javascript">
    BUI.use('bui/calendar',function(Calendar){
        var inputEl = $('#year_month');
        var monthpicker = new BUI.Calendar.MonthPicker({
            trigger : inputEl,
            // month:1, //月份从0开始，11结束
            autoHide : true,
            align : {
                points:['bl','tl']
            },
            //year:2000,
            success:function(){
                var month = this.get('month');
                var year = this.get('year');
                inputEl.val(year + '-' + (month + 1));//月份从0开始，11结束
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show',function(ev){
            var val = inputEl.val(),
                arr,month,year;
            if(val){
                arr = val.split('-'); //分割年月
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year',year);
                monthpicker.set('month',month - 1);
            }
        });
    });
    $(function(){
        $("body").on('mouseover','td>div>span>img',function(e){
            var img_src = $(this).data('goods-img');
            var tooltip = "<div id='tooltipimg' style='position:fixed;top:25%;left:25%;'> <img  width='500px' height='auto' src='"+ img_src +"' alt='原图'/> </div>";
            //创建 div 元素
            $('tbody').parent().parent().parent().parent().append(tooltip);
        }).mouseout(function(){
            $("#tooltipimg").remove(); //移除
        })
        $("#goods_barcode").click(function () {
            table_listStore.load();
            $('#exprot_detail').show();
            $('#exprot_list').hide();
        });
        $('#goods_code_list').click(function(){
            table_goodsStore.load();
            $('#exprot_detail').hide();
            $('#exprot_list').show();
        });
        $('#exprot_list').hide();
    })
    var form = new BUI.Form.HForm({
        srcNode : '#searchForm'
    }).render();
    form.on('beforesubmit',function(ev) {
        //序列化成对象
        var obj = form.serializeToObject();
        obj.start = 0; //返回第一页
        if(obj.year_month == ''){
            BUI.Message.Alert('月份不能为空','error');
            return false;
        }
        if(obj.shop_code == ''){
            BUI.Message.Alert('店铺不能为空','error');
            return false;
        }
        return true;
    });
</script>
