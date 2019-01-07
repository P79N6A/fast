<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-Type" content="text/html; charset=UTF-8" />
        <title>宝塔eFAST 365</title>
        <script type="text/javascript" src="../../webpub/js/jquery-1.8.1.min.js"></script>
        <style>
            /*reset*/
            body,div,a,p,ul,li,img,h1,h2,h3,h4,h5,h6,ol,table,tr,td,form,input,button{ margin:0; padding:0;font-family: "Microsoft YaHei","微软雅黑","Arial","宋体","Times New Roman",Times,serif;}
            a{ text-decoration:none;}
            li{ list-style:none;}
            img,input{ border:none;}

            .scan_wrap{ padding:15px 1.5%; color:#333;}
            .scan_wrap .dj_info .lab_v{ margin-right:100px;}
            #total_no_scan_sl{color:red;cursor: pointer;}
            .mx_tbl table{ width:100%; border-collapse:collapse; background:#FFF;}
            .mx_tbl th,.mx_tbl td{padding:4px;border:1px #ccc solid;}
            .mx_tbl th{background:#f2f2f2;}
            .mx_tbl td{ text-align:center;}
            .mx_tbl td.smsl{ color:#008000;}
            .scan_div{ padding:20px 0;}
            .scan_div #scan_barcode{font-size:20px;font-weight:bold;padding:10px; width:400px; color:#351A50; border:1px solid #999;}
            #err_tips{color:#e95513; padding:8px; font-size:20px; border:2px solid #fc9580; text-align:center; margin-bottom:20px;}
            #ys_box_record_and_print,#ys_box_task,#clean_scan{width:120px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
            #print_packing,#print_jit_xiangmai{width:110px;height:50px;font-size:20px; cursor:pointer; margin-left:15px; background:#f2f2f2; color:#666; border:1px solid #999; border-radius:3px;}
            #print_packing:hover,#print_jit_xiangmai:hover{ background:#FFF;}

            #ys_box_record_and_print:hover,#ys_box_task:hover{ background:#FFF;}
            #success_tips{ padding:8px 0; color:#3c763d; font-size:20px; background:#fdffe1; border:2px solid #cfdba1; text-align:center; margin-bottom:5px;}
            #ys_tips{ padding:8px 0; color:red;text-indent:24px; font-size:14px; background:#fdffe1; border:2px solid #cfdba1; text-align:left; margin-bottom:5px;}
            .scan_wrap .scan_sl_info{ padding:10px 0 20px; font-size:18px;}
            .scan_wrap .scan_sl_info .lab{ font-weight:bold;}
            .scan_wrap .scan_sl_info .lab_v{ display:inline-block; width:150px; text-indent:50px; color:#e95513;}

            .well {
                min-height: 100px;
            }
        </style>
    </head>
    <body style="overflow-x:hidden; background:#f6f6f6;">
        <?php include get_tpl_path('web_page_top'); ?>
        <div class="scan_wrap">
            <div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
                <span class="page-title">商品差异数<?php echo $request['diff_num'] ?></span>
                <span class="page-link">
                    <span class="action-link"><a id="export_list" class="button button-primary">导出</a>
                    </span>
                    <button class="button button-primary" onclick="javascript:location.reload();"><i class="icon-refresh icon-white"></i> 刷新</button>
                </span>
            </div>
            <div class="clear" style="margin-top: 40px; "></div>
            <div class="dj_info">	
                <span class="lab">批发销货单: </span><span class="lab_v" id="box_code"> <?php echo $request['record_code']; ?></span>
                <span class="lab">仓库: </span><span class="lab_v"><?php echo $request['store_name'] ?></span>
                <span class="lab">供应商/分销商: </span><span class="lab_v"><?php echo $request['custom_name'] ?></span>
            </div>
        </div>
        <?php
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
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
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '颜色',
                        'field' => 'spec1_name',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '尺码',
                        'field' => 'spec2_name',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条形码',
                        'field' => 'barcode',
                        'width' => '150',
                        'align' => '',
                        'id' => 'barcode'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品库位',
                        'field' => 'shelf_name',
                        'width' => '150',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际出库数',
                        'field' => 'num',
                        'width' => '100',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '通知数',
                        'field' => 'enotice_num',
                        'width' => '100',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数',
                        'field' => 'num_differ',
                        'width' => '100',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
                    ),
                )
            ),
            'dataset' => 'wbm/StoreOutRecordDetailModel::get_by_page',
            'idField' => 'store_out_record_detail_id',
            'params' => array('filter' => array('record_code' => $request['record_code'], 'from_type' => 'print_box_diff')),
            'export' => array('id' => 'exprot_list', 'conf' => 'store_out_record_scan_diff', 'name' => '批发销货'),
        ));
        ?>
        <script>
            $(function () {
                $('#export_list').click(function () {
                    var url = tableStore.get('url');
                    params = tableStore.get('params');

                    params.ctl_type = 'export';
                    params.ctl_export_conf = 'store_out_record_scan_diff';
                    params.ctl_export_name = '批发销货单装箱扫描商品差异数';
                    <?php echo   create_export_token_js('wbm/StoreOutRecordDetailModel::get_by_page');?>
                    for (var key in params) {
                        url += "&" + key + "=" + params[key];
                    }
                    params.ctl_type = 'view';
                    window.open(url);
                });
            });
        </script>
    </body>
</html>