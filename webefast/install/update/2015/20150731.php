<?php
$u = array();
$u['FSF-1431'] = array(
"INSERT IGNORE INTO `sys_schedule` VALUES ('26', 'cli_sync_quehuo', '获取wms缺货订单', 'cli_sync_quehuo', '', '0', '2', '', '{\"app_act\":\"wms\\/wms_mgr\\/cli_sync_quehuo\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'sys', '', '0', '0');",
"ALTER TABLE `oms_sell_record_lof`
MODIFY COLUMN `sku`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'sku' AFTER `spec2_code`;
",
);
/**
 * oms_sell_record增加分销商信息
 * 转分销订单定时器
 * by zdd 2015.07.23
 */
$u['FSF-1520'] = array(
		"alter table oms_sell_record add column `fenxiao_id` INT(11)  COMMENT '分销商id'",
		"alter table oms_sell_record add column `fenxiao_name` VARCHAR(200) DEFAULT '' COMMENT '分销商名称'",
		"INSERT INTO `sys_schedule` VALUES ('27', 'auto_trans_api_fenxiao_order', '淘宝分销订单转单', 'auto_trans_api_fenxiao_order', '', '0', '0', '启用后，自动将平台分销订单转成系统订单', '{\"app_act\":\"cli\\/auto_trans_api_fenxiao_order\"}', 'webefast/web/index.php', '0', '0', '0', '900', '0', 'sys', '', '0', '0')",
		);

$u['FSF-1513'] = array(
		"DELETE FROM base_question_label WHERE question_label_code = 'CHANGE_GOODS_MAKEUP' ",
		"INSERT INTO `base_question_label`
		(`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `remark`, `lastchanged`)
		 VALUES('CHANGE_GOODS_MAKEUP','换货产生补差',1,1,'生成换货单、或已付款订单编辑商品后，导致订单已付款 小于 应付款，需再付款。系统将自动设问订单',now());"
);

/**
 * 售后服务单自动确认退款
 * updated time: 2015.07.24
 */
$u['FSF-1517'] = array(
    "
    INSERT INTO `sys_schedule` 
		(`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `update_time`) 
		VALUES 
		( 
			'auto_confirm_return_money', '售后服务单自动确认退款', 'auto_confirm_return_money', '', 
			'1', '0', '开启后，每隔4小时运行一次；\r\n已\'确认收货\'的退款退货单，3天后，系统将自动确认退款；', 
			'{\"app_act\":\"oms\\/sell_return\\/auto_confirm_return_money\",\"app_fmt\":\"json\"}', 
			'webefast/web/index.php', '0', '0', '1437127514', '14400', '0', 'sys', '', '1437127603', '0'
		);
    "
);
/**
 * 售后单里添加一个“确认收货人”
 * updated time: 2015.07.24
 */
$u['FSF-1519'] = array(
    "
    ALTER TABLE `oms_sell_return`   
	ADD COLUMN `receive_person` VARCHAR(20) NOT NULL   COMMENT '确认收货人' AFTER `agreed_refund_time`;
    "
);


/**
 *  菜单名称由“平台订单列表”修改为“平台交易列表”
 * by hhl 2015.07.28
 */

$u['FSF-1511'] = array(
		"update sys_action set action_name = '平台交易列表' where action_id=4010100",
);

$u['FSF-1541'] = array(
	"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
values('','wave_match_express','waves_property','S002_005    快递单打印完成后，弹出自动匹配物流单号页面','radio','[\"关闭\",\"开启\"]','0','0','1-开启 0-关闭','2015-07-28 16:08:17','');",
);

/**
 * 波次单打印模板
 * updated time: 2015.07.28
 */
$u['FSF-1544'] = array(
    "
    INSERT INTO `sys_print_templates` 
    (`print_templates_id`, `print_templates_name`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) 
    VALUES 
    ('27', '波次单', '8', '1', '0', '0', '0', '0', '', '{}', '<?xml version=\"1.0\" encoding=\"utf-8\"?><ReportSettings version=\"1.2\"><LeftMargin>1</LeftMargin><RightMargin>1</RightMargin><TopMargin>1.5</TopMargin><BottomMargin>1.5</BottomMargin><PageHeaderRepeat>false</PageHeaderRepeat><PageFooterRepeat>false</PageFooterRepeat><TableHeaderRepeat>true</TableHeaderRepeat><ShowPageNumber>true</ShowPageNumber><PageNumberFormat>【第{0}页 共{1}页】</PageNumberFormat><PageFooterShowAtEnd>false</PageFooterShowAtEnd><PaperHeight>10</PaperHeight><PageHeaderSettings><ItemSetting type=\"CaptionRowSetting\"><Height>1.3</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>  波 次 单</Value><Style><TextAlign>center</TextAlign><FontSize>18</FontSize><FontBold>true</FontBold></Style><Width>14</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@波次号</Value><Control type=\"BarCode\"><Set name=\"eBarCodeType\">CODE128</Set><Set name=\"nBarCodeScaleX\">1</Set><Set name=\"nBarCodeScaleY\">1</Set></Control><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><Height>0.8</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>波次号：</Value><Width>2.1</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@波次号</Value><PrintOutput>true</PrintOutput><Width>3.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>仓库：</Value><Width>1.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@仓库</Value><Width>3.8</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>商品总数量：</Value><Width>2.6</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@商品总数量</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageHeaderSettings><PageFooterSettings><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>打印人</Value><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@打印人</Value><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>打印时间</Value><Style><TextAlign>right</TextAlign></Style><Width>7.6</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@打印时间</Value><Style><TextAlign>right</TextAlign></Style><Width>4.9</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageFooterSettings><TableColumnSettings><ItemSetting type=\"TableColumnSetting\"><Width>1</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>4.1</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.4</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.2</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.6</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.6</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.6</Width></ItemSetting></TableColumnSettings><TableHeaderSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>序号</Value><Style><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>条形码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>库位</Value><Style><LeftBorder>false</LeftBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableHeaderSettings><TableDetailSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>=#序号</Value><Style><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品名称</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#商品编码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格1</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#规格2</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#条形码</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#库位</Value><Style><LeftBorder>false</LeftBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableDetailSettings><TableFooterSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Style><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>合计</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=@商品总数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableFooterSettings><TableGroupSettings><ItemSetting type=\"TableGroupSetting\"></ItemSetting></TableGroupSettings></ReportSettings>', '', '');
    ",
    "
    INSERT INTO `sys_action` 
    (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) 
    VALUES 
    ('1040700', '1040000', 'url', '波次单模板', 'sys/flash_templates/edit&template_id=27&model=oms/WavesRecordModel&typ=default', '0', '1', '0', '1', '0');
    "
    
);


