<style type="text/css">
    .table_panel{width: 100%;border: 1px solid #ded6d9;margin-bottom: 5px;}
    .table_panel1{
        width:100%;
        margin-bottom:5px;
    }
    .table_panel td {
        border-top: 0px solid #dddddd;
        line-height: 18px;
        padding:5px 10px;
        text-align: left;
    }
    .table_panel1 td {
        border:1px solid #dddddd;
        line-height: 20px;
        padding: 5px;
        text-align: left;
    }
    .table_panel_tt td{ padding:10px 25px;}
    .nav-tabs{ padding-top:10px; margin-bottom:10px;}
    .btns{ text-align:right; margin-bottom:5px;}
    .panel-body { padding:5px; border: 1px solid #ded6d9;padding-bottom: 0;}
    .panel > .panel-header{background-color: #ecebeb; border-color:#ded6d9; padding:5px 15px;}
    .panel > .panel-header h3{ font-size:14px;}
    input[type="checkbox"], input[type="radio"] { margin-right:2px; vertical-align: inherit;}

    .bui-dialog .bui-stdmod-body {padding: 40px;}
    .show_scan_mode{ text-align:center;}
    .button-rule{ width:81px; height:108px; line-height: 104px;font-size: 22px;color: #666; background:url(assets/img/ui/add_rules.png) no-repeat; margin:0 8px; background-color:#f5f5f5; border-color:#dddddd; position:relative;}
    .button-rule .icon{ display:block; width:37px; height:25px; background:url(assets/img/ui/add_rules.png) no-repeat center; position:absolute; top:-1px; right:-2px; display:none;}
    .button-rule:active{ background-image:url(assets/img/ui/add_rules.png); box-shadow:none;}
    .button-rule:active .icon{ display:block;}
    .button-rule:hover{ background-color:#fff6f3; border-color:#ec6d3a; color:#ec6d3a;}
    .button-manz{ background-position:27px 26px;}
    .button-maiz{background-position:-208px 25px;}
    .button-manz:hover{background-position:41px -214px;}
    .button-maiz:hover{background-position:-208px -215px;}
    #child_barcode{display: none;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '参与活动商品', 'ref_table' => 'table'));
?>
<ul class="nav-tabs oms_tabs">
    <li ><a href="#"  onClick="do_page();">基本信息</a></li>
    <li ><a href="#"  onClick="do_page_two();">参与活动商品</a></li>
    <li class="active"><a href ="#" >操作日志</a></li>
</ul>
<table class='table_panel table_panel_tt' >
    <tr>
        <td>活动名称：<?php echo $response['activity']['activity_name']; ?></td>
        <td >活动店铺：<?php echo $response['activity']['shop_code_name']; ?></td>
    </tr>
    <tr>
        <td >活动开始时间：<?php echo $response['activity']['start_time']; ?></td>
        <td >活动结束时间：<?php echo $response['activity']['end_time']; ?></td>
    </tr>
</table>

<div class="panel">
    <div class="panel-header">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
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
                            'field' => 'action_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'action_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_desc',
                            'width' => '350',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'crm/ActivityLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'log_id',
                'params' => array('filter' => array('activity_code' => $response['activity']['activity_code'])),
            ));
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    var shop_code = "<?php echo $request['shop_code'] ?>";
    var activity_code = "<?php echo $response['activity']['activity_code']; ?>";
    var id = "<?php echo $response['_id']; ?>";
    function do_page() {
        location.href = "?app_act=crm/activity/view&app_scene=edit&_id=" + id + "&show=1&activity_code=" + activity_code;

    }
    function do_page_two() {
        location.href = "?app_act=crm/activity/goods_stock_do_list&app_scene=edit&_id=" + id + "&show=1&activity_code=" + activity_code;

    }
</script>