<style type="text/css">
    body{
        color: #333333;
        font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman";
    }
    table {
    border-collapse: collapse;
    border-spacing: 0;
    margin-top:50px;
    }
    td, th {
    padding: 0;
    }
    td{
        width:180px;
        border:1px solid #ded6d9;
        height:30px;
        padding-left:20px;
    }
    td span{
       margin-right:70px
    }
    #second_row{
        font-weight:bold;
        font-size: 12px;
    }
</style>

<?php render_control('PageHead', 'head1',
    array('title' => '效验结果查询',

    'ref_table' => 'table'
    ));

?>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<div id="check_task">
    
</div>
<script type="text/javascript">
    $("#check_task").ready(function(){
        $.post("?app_act=op/op_api_activity_check/check_task_list","",function(result){
            content = '';
            var str = '';
            for(var key in result){
                //console.log(result[key]);
                if(result[key].status == 0){
                    str = '未开始';
                }
                if(result[key].status == 1){
                    str = '执行中';
                }
                if(result[key].status == 2){
                    str = '已完成';
                }
                content += '<table>';
                content += '<tr><td colspan="5" id="first_row"><span>任务编号：'+result[key].check_sn+'</span><span>任务生成时间：'+result[key].start_time+'</span><span>任务结束时间：'+result[key].end_time+'</span><span>任务状态：'+str+'</span></td></tr>';
                content += '<tr id="second_row"><td>操作</td><td>店铺</td><td>商家编码异常SKU数</td><td>商品库存不一致SKU数</td><td>商品价格不一致SKU数</td></tr>';
                for(var a in result[key].data){
                    content += '<tr><td><a href="">下载</a></td><td>'+result[key].data[a].shop_code+'</td><td>'+result[key].data[a].barcode_num+'</td><td>'+result[key].data[a].inv_num+'</td><td>'+result[key].data[a].sale_price_num+'</td></tr>';
                
                }
//                   content += '<span style="margin-left:30px">';
//        	   content += '  <input class="check_shop" type="checkbox" checked="checked" value="'+result.code[key]+'">';
//        	   content += result.name[key];
//                   content += '</span>';
                    
            }
            $("#check_task").append(content);
        },"json");
        
    });
    
</script>