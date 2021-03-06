<style type="text/css">
    .well {
        min-height: 100px;
    }
    .x-icon{
        display: none;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '赠品策略列表',
    'links' => array(
        array('url' => 'op/gift_strategy/get_strategy_log', 'title' => '赠品策略匹配结果日志查询', 'is_pop' => false),
        array('url' => 'op/gift_strategy/view&app_scene=add', 'title' => '添加赠品策略', 'is_pop' => false, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$fenxiao = ds_get_select('supplier', 2);
unset($fenxiao[0]);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => '策略名称',
            'title' => '',
            'type' => 'input',
            'id' => 'strategy_name'
        ),
        array(
            'label' => '策略代码',
            'title' => '',
            'type' => 'input',
            'id' => 'strategy_code'
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '活动开始时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'activity_start_first_time'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'activity_start_last_time', 'remark' => ''),
            )
        ),
        array(
            'label' => '活动结束时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'activity_end_first_time', 'value' => $response['start_time'],),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'activity_end_last_time', 'remark' => ''),
            )
        ),
        array(
            'label' => '启用',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'status',
            'data' => array(
                array('0', '停用'), array('1', '启用')
            )),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '180',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '查看',
                          'act' => 'op/gift_strategy/view&app_scene=edit', 
                          'show_name' => '查看',
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '审核',
                        'callback' => 'do_check',
                        'show_cond' => 'obj.is_check == 0 && obj.status == 0 && obj.check_auth == 1'
                    ),
                    array('id' => 'enable', 
                          'title' => '启用',
                          'callback' => 'do_enable', 
                          'show_cond' => 'obj.status != 1 && obj.is_check == 1 && obj.able_auth == 1'
                    ),
                    array('id' => 'disable', 
                          'title' => '停用',
                          'callback' => 'do_disable', 
                          'show_cond' => 'obj.status == 1 && obj.is_check == 1 && obj.able_auth == 1'
                    ),
                    array(
                        'id' => 'copy',
                        'title' => '复制',
                        'callback' => 'opt_copy',
                    ),
                    array(
                        'id' => 'extend_date',
                        'title' => '延长效期',
                        'callback' => 'update_end_time',
                        'show_cond' => 'obj.date_auth == 1',
                    ),
                    array(
                        'id' => 'do_delete',
                        'title' => '删除',
                        'callback' => 'delete_op',
                        'show_cond' => 'obj.is_check == 0 && obj.del_auth == 1',
                        'confirm' => '确认要删除吗？',
                        
                    ),
                    array(
                        'id' => 'test_gift',
                        'title' => '测试',
                        'show_name'=>'测试赠品策略',
                        'act' => 'op/op_gift_strategy/test_gift_strategy',
                        'show_cond' => 'obj.test_status == 1',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用',
                'field' => 'status',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略代码',
                'field' => 'strategy_code',
                'width' => '140',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '策略名称',
                'field' => 'strategy_name',
                'width' => '160',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '280',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动开始时间',
                'field' => 'start_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动结束时间',
                'field' => 'end_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'op/GiftStrategy2Model::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'op_gift_strategy_id',
    'init' => 'nodata',
));
?>
<br /><br /><br />
<span style="color:red">温馨提示：“延长效期”仅限策略已启用但未到活动结束时间的赠品策略。可以修改活动结束时间来延续赠品策略。</span>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    function do_enable(_index, row) {
        var url = '<?php echo get_app_url('op/op_gift_strategy/check_repeat'); ?>';
        var data = {strategy_code: row.strategy_code, app_fmt: 'json'};
        $.post(url, data, function(ret) {
            if (ret.status > 0) {
                var message = '系统检测到，同一时间段该店铺存在赠送策略' + ret.data + '，请确认！（若全部启用，单据满足规则会多次赠送）';
                BUI.Message.Confirm(message, function() {
                    _do_set_active(_index, row, 'enable');
                }, 'question');
            } else {
                _do_set_active(_index, row, 'enable');
            }
        }, 'json');


    }

    function do_disable(_index, row) {
        BUI.Message.Confirm('确认停用策略吗？', function() {
            _do_set_active(_index, row, 'disable');
        }, 'question');
    }
    function update_end_time(_index, row) {
        console.log(row);
        BUI.Message.Confirm('修改活动结束时间<br/><br/>现有活动结束时间:'+ row.end_time + '<br/>延长活动结束时间:<input type="text" name="start_time" id="update_end_time" \n\
                              class="input-normal calendar calendar-time"  \n\
                              value="" \n\
                              data-rules="{required: true}" />', function() {
            _update_active_time(_index, row); 
            });
        
          BUI.use('bui/calendar',function(Calendar){
          var datepicker = new Calendar.DatePicker({
            trigger:'.calendar-time',
              showTime:true,
              autoRender : true
          });
      });
       
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('op/op_gift_strategy/update_active'); ?>',
            data: {id: row.op_gift_strategy_id, type: active},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    //BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function do_check(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('op/op_gift_strategy/do_check'); ?>',
            data: {id: row.op_gift_strategy_id,strategy_code:row.strategy_code},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    //BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function _update_active_time(_index, row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('op/op_gift_strategy/update_end_time'); ?>',
            data: {id: row.op_gift_strategy_id,new_endtime:$("#update_end_time").val(),end_time:row.end_time},
            success: function(ret) {
                var type = ret.status == true ? 'success' : 'error';
                if (type == 'success') {
                    ret.message = "修改成功";  
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    $(function(){
       $('#btn-search').click();
    });
    
    //复制订单赠品策略
    function opt_copy(_index,row) {
        var params = {"strategy_code": row.strategy_code};
        $.post("?app_act=op/op_gift_strategy/opt_copy", params, function (data) {
            if (data.status == 1) {
                var url = "?app_act=op/gift_strategy/view&app_scene=edit&_id=" + data.data+ "&ref=do" + "&ES_frmId=" + '<?php echo $request['ES_frmId']; ?>';
                openPage(window.btoa(url),url,'赠品策略详情');
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }
    
    function delete_op(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('op/op_gift_strategy/do_delete'); ?>',
            data: {id: row.op_gift_strategy_id,strategy_code:row.strategy_code},
            success: function(ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    
 //添加图标 
  $(function(){
    $(".page-header1").append("<span class='page-link'><img src='assets/images/gift.png' height='20' id='guide' width='29'></span>");
});
</script>




