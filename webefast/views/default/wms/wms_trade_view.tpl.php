<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    #tr_log td{
        padding: 4px;
        line-height: 20px;
        text-align: left;
        vertical-align: top;
        border-top: 1px solid #dddddd;
    }
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:9%; padding:0 1%;}
    .table td input{ width:100px;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<form id="recordForm" name="recordForm"  >
<script>
var is_edit = false;
<?php if ($response['data']['wms_info']['record_type'] == 'sell_record' && $response['data']['wms_info']['process_flag'] != 30) { ?>
        is_edit = true;
<?php } ?>
var data = [
    {
        "title": "单据编号",
        "value": "<?php echo $response['data']['wms_info']['record_code'];?>",
    },
    {
        "title": "单据类型",
        "value": "<?php echo $response['data']['record_order_type'] ?>",
    },
    {
        "title": "WMS单据号",
        "value": "<?php echo $response['data']['wms_record_code'] ?>",
    },
    <?php  if ($response['type'] == 'oms') { ?>
    {
        "title": "WMS收发货时间",
        "value": "<?php echo $response['data']['wms_order_time'] ?>",
    },
    <?php } else { ?>
    {
        "title": "WMS出入库时间",
        "value": "<?php echo $response['data']['wms_order_time'] ?>",
    },
    <?php  } ?>
    {
        "title": "WMS单据状态",
        "value": "<?php echo $response['data']['order_status_txt'] ?>",
    },
    {
        "title": "eFAST总数量",
        "value": "<?php echo $response['data']['total_efast_sl'] ?>",
    },
    <?php  if ($response['type'] == 'oms') { ?>
    {
        "title": "WMS收发总数量",
        "value": "<?php echo $response['data']['total_wms_sl'] ?>",
    },
    <?php } else { ?>
    {
        "title": "WMS出入总数量",
        "value": "<?php echo $response['data']['total_wms_sl'] ?>",
    },
    <?php  } ?>
    <?php  if ($response['data']['wms_info']['record_type'] == 'sell_record') { ?>
        {
            "name": "express_code",
            "title": "物流公司",
            "value": "<?php echo $response['data']['wms_info']['express_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['data']['express_company'] ?>,
        },
        {
            "name": "express_no",
            "title": "物流单号",
            "type": "input",
            "value": "<?php echo $response['data']['wms_info']['express_no'] ?>",
            "edit": true,
        },
    <?php } ?>
    
    ];
    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wms/wms_trade/do_edit"
        });
    });
</script>
<div class="panel record_table" id="panel_html">

</div>

<?php  if ($response['data']['wms_info']['wms_order_flow_end_flag'] == 1) { ?>

  <div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left">WMS收发货信息</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">
        <table cellspacing="0" class="table table-bordered">
            <tr>
                <th width="10%">商品名称</th>
                <th width="10%">商品编码</th>
                <th width="10%">规格1</th>
                <th width="10%">规格2</th>
                <th width="15%">商品条形码</th>
                <th width="7%">eFAST数量</th>
                <th width="10%">WMS收发货数量</th>
            </tr>
            <?php
            //echo '<hr/>$xx<xmp>'.var_export($response['record']['detail_list'],true).'</xmp>';
            foreach($response['data']['goods'] as $key=>$detail){?>
                <tr>
                    <td><?php echo isset($detail['goods_name'])?$detail['goods_name']:'';?></td>
                    <td><?php echo isset($detail['goods_code'])?$detail['goods_code']:'';?></td>
                    <td><?php echo isset($detail['spec1_name'])?$detail['spec1_name']:'';?></td>
                    <td><?php echo isset($detail['spec2_name'])?$detail['spec2_name']:'';?></td>
                    <td><?php echo isset($detail['barcode'])?$detail['barcode']:'';?></td>
                    <td><?php if($detail['efast_sl'] >= 0) echo $detail['efast_sl']; else echo "";?></td>
                    <td><?php if($detail['wms_sl'] >= 0) echo $detail['wms_sl']; else echo "";?></td>
                </tr>
            <?php }?>
        </table>
    </div>
  </div>
<?php  } ?>


<div class="panel">
    <div class="panel-header clearfix">
      <!--   <h3 class="pull-left"><img src="assets/img/sys/czrz_icon.png"/>操作日志</h3> -->
        <h3 class="">操作日志 <i class="icon-folder-open toggle"></i></h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_action">
        <table cellspacing="0" class="table table-bordered">
            <?php
            //echo '<hr/>$xx<xmp>'.var_export($response['record']['detail_list'],true).'</xmp>';
            foreach($response['data']['log_info'] as $key=>$log_info){?>
              <tr>
                <td>
                  <?php echo isset($log_info['action_time'])?($log_info['action_time'].'：'):'';?> &nbsp;
                  <?php echo isset($log_info['action_msg'])?($log_info['action_msg']):'';?>
                </td>
              </tr>
            <?php }?>
        </table>
        <br/><br/>
        <div class="button button-primary1 show-log" style="margin-left:40%;">点我显示接口日志</div>
        <table cellspacing="0" class="table table-bordered api_log">
            <tr id="tr_log" style="display: none">
                <td>日志类型</td><td>时间</td><td>请求报文</td><td>返回报文</td><td>状态</td>
            </tr>
        </table>
    </div>
</div>

</form>
<?php echo load_js('comm_util.js')?>
<script>
    var url = '<?php echo get_app_url('base/store/get_area');?>';
    $(document).ready(function(){
        $('#country').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });

        $("#btn_close").click(function(){
            ui_closePopWindow("<?php echo $request['ES_frmId']?>");
        });

        $(document).keydown(function(event){
            switch(event.keyCode){
                case 13:return false;
            }
        });
        
        
    });
    function show(param, id){
        var params='';
        if(param === 'post'){
            params = {
                'id': id,
                'record_code': '<?php echo $response['data']['wms_info']['record_code'];?>',
                'param': 'post'
            };
        }else if(param === 'return'){
           params = {
                'id': id,
                'record_code': '<?php echo $response['data']['wms_info']['record_code'];?>',
                'param': 'return'
                };
        }
         $.ajax({
                type: "post",
                url: "?app_act=wms/wms_trade/show_detail",
                data: params,
                success: function (data) {
                    show_detail('JSON数据',data);
                }
        });
    }
     function show_detail(title,content){
        BUI.use('bui/overlay',function(Overlay){
            var dialog = new Overlay.Dialog({
                title:title,
                width:500,
                height:400,
                mask:true,
                bodyContent:content
            });
            dialog.show();
        });
    }
    $(".show-log").click(function(){
        $("#tr_log").attr('style','display:line!important');
        $(".show-log").attr('style','display:none');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wms/wms_trade/get_api_log'); ?>',
            data: {record_code: '<?php echo $response['data']['wms_info']['record_code'];?>'},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    var arr = ret.data;
                   for(var i =0;i<arr.length;i++){
                        if(typeof(arr[i].method_name) == 'undefined'){
                            arr[i].method_name = '';
                        }
                        if(typeof(arr[i].result) == 'undefined'){
                            arr[i].result = '';
                        }
                        $('.api_log').append("<tr><td>"+arr[i].method_name+"</td><td>"+arr[i].add_time+"</td><td><a href=\"#\" onclick=\"show('post'," + arr[i].id + ")\">JSON</a></td><td><a href=\"#\" onclick=\"show('return'," + arr[i].id + ")\">JSON</a></td><td>"+arr[i].result+"</td></tr>");
                   }
                } else {
                    $('.api_log').append("<tr><td colspan='5'>暂无接口日志</td></tr>")
                }
            }
        })
    })

</script>