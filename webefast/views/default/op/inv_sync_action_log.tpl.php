<style>
    .action_log{margin:20px;}
    .action_log select{width: 100px;}
</style>
<?php
$ratio_type = array();
$ratio_type['baseinfo'] = '基本信息';
$ratio_type['shop_ratio'] = '店铺比例配置';
$ratio_type['goods_ratio'] = '商品比例配置';
$ratio_type['anti_oversold'] = '防超卖预警配置';
$ratio_type = array_from_dict($ratio_type);
?>
<div class="action_log">
    <form id="log_form">
        <select class="input-small" id="ratio_type">
            <option value="">标签类型</option>
            <?php
            foreach ($ratio_type as $type) {
                echo "<option value='" . $type[0] . "'>" . $type[1] . "</option>";
            }
            ?>
        </select>
        <input type="text" placeholder="关键字" class="key_code" id="key_code"/>
        <button type="button" class="button button-info" value="搜索" id="btnSearchType"><i class="icon-search icon-white"></i> 搜索</button>
        <button type="button" class="button button-info" id="resetSearch">重置搜索</button>
    </form>
</div>

<?php
    $list = array(
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '标签类型',
            'field' => 'type_name',
            'width' => '12%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '用户名称',
            'field' => 'user_name',
            'width' => '12%',
            'align' => '',
        ),
/*        array(
            'type' => 'text',
            'show' => 1,
            'title' => '用户IP',
            'field' => 'user_ip',
            'width' => '12%',
            'align' => '',
        ),*/
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '日志时间',
            'field' => 'log_time',
            'width' => '25%',
            'align' => '',
        ),
        array(
            'type' => 'text',
            'show' => 1,
            'title' => '日志内容',
            'field' => 'log_info',
            'width' => '100%',
            'align' => '',
        ),
    );
    render_control('DataTable', 'table_log', array(
        'conf' => array(
            'list' => $list
        ),
        'dataset' => 'op/InvSyncLogModel::get_by_page',
        'idField' => 'log_id',
        'CheckSelection' => false,
        'params' => array('filter' => array('sync_code' => $request['sync_code'])),
        //'init' => 'nodata',
    ));
?>

<script>
    var sync_code = '<?php echo $request['sync_code']; ?>';

    $('#btnSearchType').on('click', function () {
        reload_log_info();
    });
    $('#resetSearch').on('click', function () {
        document.getElementById("log_form").reset();
        reload_log_info();
    });

    function reload_log_info() {
        var type_code = $('#ratio_type').val();
        var key_code = $('#key_code').val();
        table_logStore.load({'sync_code': sync_code, 'type_code': type_code, 'key_code': key_code});
    }
</script>