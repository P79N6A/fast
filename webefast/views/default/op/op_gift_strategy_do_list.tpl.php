<style type="text/css">
    .well {
        min-height: 100px;
    }
</style>

<?php
render_control('PageHead', 'head1', array('title' => '赠品策略列表',
    'links' => array(
        array('url' => 'op/op_gift_strategy/detail&app_scene=add', 'title' => '添加赠品策略', 'is_pop' => false, 'pop_size' => '500,550'),
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
    /* array(
      'label' => '导出',
      'id' => 'exprot_list',
      ), */
    ),
    'fields' => array(
        array(
            'label' => '策略名称',
            'title' => '',
            'type' => 'input',
            'id' => 'strategy_name'
        ),
        array(
            'label' => '活动时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'start_time', 'value' => $response['start_time'],),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'end_time', 'remark' => ''),
            )
        ),
        /*
          array(
          'label' => '活动时间',
          'type' => 'group',
          'field' => 'active_time',
          'child' => array(
          array('title' => 'start', 'type' => 'time', 'field' => 'start_time',),
          array('pre_title' => '~', 'type' => 'time', 'field' => 'end_time', 'remark' => ''),
          )
          ), */
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
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
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'showDetail'
                    ),
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'op/op_gift_strategy/detail&app_scene=edit', 'show_name' => '编辑',
                        'show_cond' => 'obj.is_check == 0 && obj.status == 0'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '审核',
                        'callback' => 'do_check',
                        'show_cond' => 'obj.is_check == 0 && obj.status == 0'
                    ),
                    array('id' => 'enable', 'title' => '启用',
                        'callback' => 'do_enable', 'show_cond' => 'obj.status != 1 && obj.is_check == 1'),
                    array('id' => 'disable', 'title' => '停用',
                        'callback' => 'do_disable', 'show_cond' => 'obj.status == 1 && obj.is_check == 1'
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
                'width' => '160',
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
                'width' => '100',
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
    'dataset' => 'op/GiftStrategyModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'op_gift_strategy_id',
    'init' => 'nodata',
        // 'export'=> array('id'=>'exprot_list','conf'=>'return_record_list','name'=>'批发销货'),
        /*
          'events' => array(
          'rowdblclick' => 'showDetail',
          ),
         */
));
?>
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
            data: {id: row.op_gift_strategy_id},
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


    /**
     * 查看调整单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        location.href = "?app_act=stm/stm_goods_diy_record/view&goods_diy_record_id=" + row.goods_diy_record_id;
    }

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=op/op_gift_strategy/detail&app_scene=edit&show=1&_id=') ?>' + row.op_gift_strategy_id, '?app_act=op/op_gift_strategy/detail&app_scene=edit&show=1&_id=' + row.op_gift_strategy_id, '赠品策略');
    }
    $(function(){
       $('#btn-search').click();
    });
</script>




