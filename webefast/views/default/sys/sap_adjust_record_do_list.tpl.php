<style>
    #handle_date_start,#handle_date_end,#download_date_start,#download_date_end{
        width: 90px;
    }
</style>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
	<span class="page-title"><h2>调整单单据</h2></span>
	<span class="page-link">
            <span class="action-link"><a onclick="download_record()" href="#" class="button button-primary">
             
            下载单据</a>
        </span>
                <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<?php
//render_control('PageHead', 'head1', array('title' => '调整单单据',
//    'links' => array(
//        array('url' => 'sys/sap_adjust_record/download_data', 'title' => '下载单据'),
//    ),
//    'ref_table' => 'table'
//));
?>

<?php
$keyword_type['sap_record'] = 'SAP单号';
$keyword_type['record_code'] = '系统单号';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
//          'data' => ds_get_select('store'),
            'data' => load_model('base/StoreModel')->get_sap_store(),
        ),
        array(
            'label' => '下载时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'download_date_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'download_date_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '处理时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'handle_date_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'handle_date_end', 'remark' => ''),
            )
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '未处理', 'active' => true, 'id' => 'no_handle'),
        array('title' => '已处理', 'active' => false, 'id' => 'ok_handle'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '70',
                'align' => '',
                'buttons' => array(
//                    array('id' => 'detail', 'title' => '详情', 'act' => '', 'show_name' => '详情'),
                    array('id' => 'handle_record', 'title' => '处理', 'callback' => 'handle_record', 'show_cond' => 'obj.status == 0 || obj.status == 2'),
                ),
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => 'SAP单号',
                'field' => 'mblnr',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code',
                'width' => '80',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '物料号',
                'field' => 'matnr',
                'width' => '180',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '80',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '调整类型',
                'field' => 'shkzg',
                'width' => '70',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '处理失败信息',
                'field' => 'handle_info',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '下载时间',
                'field' => 'download_date',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '处理时间',
                'field' => 'handle_date',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '系统单号',
//                'field' => 'stm_record_code', 
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:stock_adjust_record_do_list("{stm_record_code}")>{stm_record_code}</a>',
                ),
            ),
        )
    ),
    'dataset' => 'sys/SapAdjustRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sap_adjust_record_id',
));
?>

<script type="text/javascript">
    var fullMask;
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.ex_list_tab = $("#TabPage1").find(".active").find("a").attr("id")
            tableStore.set("params", e.params);
        });
        
        BUI.use(['bui/mask'],function(Mask){
            fullMask = new Mask.LoadMask({
                el : 'body',
                msg : '请稍后...'
            });
        });
    });
    //处理
    function handle_record(_index,row) {
        $.post('?app_act=sys/sap_adjust_record/handle_data',{sap_adjust_record_id:row.sap_adjust_record_id},function(ret){
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message,'success');
                tableStore.load();
            }
        },'json')
    }
    //下载
    function download_record() {
        fullMask.show();  
        $.post('?app_act=sys/sap_adjust_record/download_data',{},function(ret){
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
                fullMask.hide();  
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message,'success');
                fullMask.hide();  
                tableStore.load();
            }
        },'json')
    }
    /**
     // 计时器
     var t = null; // 每过5分钟刷新数据
     
     //设置定时器，每5分钟刷新一次数据,并且运行一次
     
     function setSapTimer() {
     createChart();
     if (t === null) {
     t = setInterval(createSapRecord, 1000 * 60 * 60 * 24);
     }
     }
     
     //清除5分钟定时器
     
     function removeSapTimer() {
     if (t !== null) {
     clearInterval(t);
     t = null;
     }
     }
     function createSapRecord() {
     $.post('?app_act=sys/sap_adjust_record/download_data',{},function(ret){
     
     },'json') 
     }
     */
    function stock_adjust_record_do_list(stm_record_code) {
        openPage('<?php echo base64_encode('?app_act=stm/stock_adjust_record/viewt&stm_record_code='.stm_record_code) ?>', '?app_act=stm/stock_adjust_record/view&stm_record_code='+stm_record_code,'调整单详情');
    }
</script>