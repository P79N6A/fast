<?php
$u['640'] = array(
    "CREATE TABLE IF NOT EXISTS `sys_log_clean_up_log` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
        `type` varchar(20) NOT NULL COMMENT '日志类型',
        `status` tinyint(1) NOT NULL COMMENT '操作是否成功，1：成功，0：失败',
        `lastchanged` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '操作时间',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='清除日志记录表';",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('log_clean_up', '日志自动清除', '', '', '1', '10', '开启后，日志清除每隔半天执行一次', '{\"app_act\":\"sys/log_clean_up/doCleanUp\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '43200', '0', 'sys', '', '0', '', '0');
"
);

$u['bug_493']=array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('wbm_notice_store_out', '批发销货通知单模版', NULL, '12', '1', '0', '0', '0', '0', '', '{}', '<?xml version=\"1.0\" encoding=\"utf-8\"?><ReportSettings version=\"1.2\"><LeftMargin>1</LeftMargin><RightMargin>1</RightMargin><TopMargin>1.5</TopMargin><BottomMargin>1.5</BottomMargin><PageHeaderRepeat>false</PageHeaderRepeat><PageFooterRepeat>false</PageFooterRepeat><TableHeaderRepeat>true</TableHeaderRepeat><ShowPageNumber>true</ShowPageNumber><PageNumberFormat>【第{0}页 共{1}页】</PageNumberFormat><PageHeaderSettings><ItemSetting type=\"CaptionRowSetting\"><Height>1.3</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>批发销货通知单</Value><Style><TextAlign>center</TextAlign><FontSize>18</FontSize><FontBold>true</FontBold></Style><Width>19</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><Height>0.8</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>单据编号：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@单据编号</Value><Width>4</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>下单时间：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@下单时间</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>仓库：</Value><Style><TextAlign>right</TextAlign></Style><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@仓库</Value><Width>4</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>分销商：</Value><Style><TextAlign>right</TextAlign></Style><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@分销商</Value><Width>4</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>总数：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@总数</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageHeaderSettings><PageFooterSettings><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>打印人</Value></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@打印人</Value><Width>7</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>打印时间</Value></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@打印时间</Value><Width>7</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageFooterSettings><TableColumnSettings><ItemSetting type=\"TableColumnSetting\"><Width>1</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.4</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.2</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.6</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.3</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>3.6</Width></ItemSetting></TableColumnSettings><TableHeaderSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>序号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>单价</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>金额</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>库位</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableHeaderSettings><TableDetailSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>=#序号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#单价</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#单价*#数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#库位</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableDetailSettings><TableFooterSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>合计</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=@总数</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableFooterSettings><TableGroupSettings><ItemSetting type=\"TableGroupSetting\"></ItemSetting></TableGroupSettings></ReportSettings>', '', '');"
);

$u['668'] = array(
	"DELETE FROM sys_user_pref WHERE iid = 'oms/sell_record_short_list';"
);

$u['670'] = array(
    "INSERT INTO `sys_action` VALUES('4010305','4010300','act','允许上架(单个/批量)','oms/api_goods/p_update_onsale','1','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES('4010306','4010300','act','禁止上架(单个/批量)','oms/api_goods/p_update_onsale/ban','1','1','0','1','0');"
    );
    
$u['671'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('xiaohongshu','xiaohongshu','小红书','1','1');");