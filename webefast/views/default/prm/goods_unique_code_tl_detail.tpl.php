
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
$title = $response['action'] == 'do_add' ? '添加商品' : '商品编辑';
render_control('PageHead', 'head1', array('title' => $title));
//$spec1_realname = load_model('prm/GoodsSpec1Model')->get_spec1_realname();
//$spec2_realname = load_model('prm/GoodsSpec2Model')->get_spec2_realname();
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
?>

<div id="panel" class="">
    <form action="?app_act=prm/goods_unique_code_tl/<?php echo $response['action']; ?>" id="form1" method="post">
        <table class='table_panel'   id='p1'>
            <input type="hidden" id="unique_code" name="unique_code" value="<?php echo $response['data']['unique_code']; ?>">

            <tr><td style="width:108px;">唯一码：<b style="color:red"> *</b></td><td><input id="unique_code" class="bui-form-field" type="text"  value="<?php echo $response['data']['unique_code']; ?>" name="unique_code" data-rules="{required : true}" aria-disabled="false" aria-pressed="false" <?php if ($response['action'] == 'do_edit') { ?> disabled="disabled" <?php } ?>></td></tr>
            
            <tr><td style="width:108px;">销售含税价：</td><td><input id="total_price" class="bui-form-field" type="text"  value="<?php echo $response['data']['total_price']; ?>" name="total_price" aria-disabled="false" aria-pressed="false"></td></tr>
           
            

                </td></tr>
            <tr><td style="width:108px;">

                </td><td><button type="submit" class="button button-primary">保存</button>&nbsp;&nbsp;&nbsp;<button type="reset" class="button button-primary">重置</button> &nbsp;&nbsp;&nbsp;<input type="button" class="button button-primary"  value="返回" onclick="javascript:window.location = '?app_act=prm/goods_unique_code_tl/do_list';">

                </td></tr>
        </table>
    </form>
    </div>



<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    var barcode = '<?php echo empty($response['data']['barcode'])?0:1;?>';
  


    var action = '<?php echo $response['action']; ?>';
    var next = '<?php echo $response['next']; ?>';

    var type = '<?php echo $request['type']; ?>';

    if (action == 'do_add') {
        $("#tab").find('li').eq(1).hide();
        $("#tab").find('li').eq(2).hide();
    }
 
    var ES_frmId  = '<?php echo $request['ES_frmId'];?>';
    //form1
    form = new BUI.Form.HForm({
        srcNode: '#form1',
        submitType: 'ajax',
     
        callback: function (data) {
            console.log(data);
            var type = data.status == 1 ? 'success' : 'error';
            if (data.status == 1) {

                BUI.Message.Alert('保存成功', type);
                window.location.href = "?app_act=prm/goods_unique_code_tl/detail&action=do_edit&unique_code=" + data.data+"&ES_frmId="+ES_frmId;

            } else {
                BUI.Message.Alert(data.message, 'error');
            }

        }
    }).render();

    

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

  
</script>