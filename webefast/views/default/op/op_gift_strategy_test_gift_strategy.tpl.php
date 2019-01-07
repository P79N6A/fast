<style type="text/css">
    .frontool1 {
        position: fixed;
        bottom: 0px;
        width: 99%;
        padding-left: 1%;
        background-color: #FFF;
        border-top: 2px solid #1695ca;
        z-index: 1000;
        height: 37px;
    }
    #btn-test{
        color: #FFF;
        background-color: #29baf7;
        border: none;
        width: 80px;
        height: 28px;
        font-size: 18px;
    }
    #search_tid img{
        position: absolute;
        right: 12px;
        top: 3px;
    }
    #tid{
        width: 200px;
    }

</style>
<?php
render_control('PageHead', 'head1', array('title' => $response['title'],
    'ref_table' => 'table'
));
?>

<?php
//交易类型
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '测试',
            'id' => 'btn-test',
            'type' => 'button'
        ),
    ),
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => '交易号',
            'type' => 'group',
            'field' => 'tid',
            'child' => array(
                array('type' => 'input','field'=>'tid','remark' => "<a href='#' id = 'search_tid'><img src='assets/img/search.png' ></a><input type='hidden' id='select_tid'>"),
            ),
        ),
    )

));
?>
<input type="hidden" name="search_id" value="">
<?php include 'op_test_gift_com.tpl.php';?>
<div class="demo-content ret-content" style="margin-top: 50px;display: none;">
    <div class="doc-content">
        <div class="panel">
            <div class="panel-header ret-hearder">
            </div>
            <div class="panel-body ret-body">
            </div>
        </div>
    </div>
</div>
<div class="demo-content rule-content" style="margin-top: 50px;display: none;">
    <div class="doc-content">
        <div class="panel">
            <div class="panel-header rule-header">
                <h3>应用赠品规则如下：</h3>
            </div>
            <div class="panel-body rule-body">
            </div>
        </div>
    </div>
</div>
<span style="color:red">* 测试赠品策略不包含以下测试：<br/>
1、赠品策略限定的活动时间；2、一个会员仅送一次的校验项；3、合并订单赠品升档</span>
<script type="text/javascript">
    var nodata = $(".nodata").text() === '' ?  0 : 1;
    var id = "<?php echo $request['_id'];?>";
    $("#tid").css('border', '1px solid red');
    $('#btn_more').hide();
    $(function () {
        $('#tid').css('border','1px solid rgba(128, 128, 128, 0.64)')

        $(".order_change_fail").click(function () {
            $("#is_change").val(-1);
            $("#record_time_start").val('');
            $("#record_time_end").val('');
            $("#btn-search").click();
        });
    });
    $('#search_tid').click(function(){
        var tid = '';
        new ESUI.PopWindow('?app_act=op/test_strategy/test_tid&type=test_gift&id='+id, {
            title: '选择用于测试的交易号',
            width: 1200,
            height: 500,
            onBeforeClosed: function () {
                tid = top.window.tid;
                if(tid == undefined || tid === '') return false;
                $('#tid').val(tid);
                $('#searchForm').submit();
                $('input[name="search_id"]').val(tid);
                $('.demo-content').hide();
            }
        }).show();
    })
    $('#btn-search').click(function(){
        var tid = $('#tid').val();
        if(tid == ''){
            BUI.Message.Alert('请输入交易号','error');
            return false;
        }else{
            $('input[name="search_id"]').val(tid);
            $('.demo-content').hide();
            return true;
        }
    })
    $('#btn-test').click(function(){
        var tid = $('input[name="search_id"]').val();;
        if(tid == ''){
            BUI.Message.Alert('请输入交易号','error');
            return false;
        }else{
            //策略测试
            $.post('?app_act=op/test_strategy/execute',{tid:tid,id:id,type:'test_gift'},function(data){
                if(data.data.is_error == 1 ){
                    $('.ret-hearder').html('<h3 style="color: red">添加赠品失败!</h3>');
                    $('.ret-body').html('<p>'+data.data.data+'</p>');
                    $('.ret-content').show();
                    $('.rule-content').hide();
                }else if(data.data.is_error == 0){
                    $('.ret-hearder').html('<h3 style="color: red">添加赠品成功</h3>');
                    var str = '<table class="gift_ret"><tr><td>赠品名称</td><td>赠品规格</td><td>赠品条形码</td><td>赠品件数</td></tr>';
                    for(i in data.data.data.data){
                        str += '<tr><td>'+data.data.data.data[i].goods_name+'</td><td>'+data.data.data.data[i].spec_name+'</td><td>'+data.data.data.data[i].barcode+'</td><td>'+data.data.data.data[i].num+'</td></tr>';
                    }
                    str +='</table>';
                    $('.ret-body').html('<p>'+str+'</p>');
                    $('.ret-content').show();
                    var rule_str = '<table class="gift_ret"><tr><td>分组</td><td>规则名称</td><td>规则类型</td><td>优先级</td><td>互溶/互斥</td></tr>';
                    for(i in data.data.rule){
                        var type_name = '';
                        if(data.data.rule[i].type == 1){
                            type_name = '买送';
                        }else{
                            type_name = '满送';
                        }
                        var is_mutex_name = '';
                        if(data.data.rule[i].is_mutex == 1){
                            is_mutex_name = '互溶';
                        }else{
                            is_mutex_name = '互斥';
                        }
                        rule_str += '<tr><td>'+data.data.rule[i].sort+'</td><td>'+data.data.rule[i].name+'</td><td>'+type_name+'</td><td>'+data.data.rule[i].level+'</td><td>'+is_mutex_name+'</td></tr>';
                    }
                    rule_str +='</table>';
                    $('.rule-body').html(rule_str)
                    $('.rule-content').show();
                    $('.gift_ret tr td').css('border','1px solid').css('text-align','center').css('width','150px');

                }
            },'json')
        }
    })
    $('#table_pager').hide();
</script>