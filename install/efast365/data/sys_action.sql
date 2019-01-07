INSERT INTO `sys_action` VALUES ('1000000', '0', 'cote', '系统管理', 'sys', '60', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010000', '1000000', 'group', '系统管理', 'sys-sys', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010100', '1010000', 'url', '系统日志', 'sys/sys_log/do_list', '2', '1', '0', '1','0');
/*
INSERT INTO `sys_action` VALUES ('1010101', '1010100', 'act', '删除日志', 'sys/operate_log/delete&app_scene=add', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010200', '1010000', 'url', '登陆日志', 'sys/login_log/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010201', '1010200', 'act', '删除日志', 'sys/login_log/delete&app_scene=add', '2', '1', '0', '1','0');
*/
/* INSERT INTO `sys_action` VALUES ('1010300', '1010000', 'url', '系统自动建档', 'sys/auto_create/ams_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010301', '1010300', 'act', '立即生成档案', 'sys/auto_create/create_progress_html', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010302', '1010300', 'act', '立即初始化库存', 'sys/auto_create/create_init_inv_html', '3', '1', '0', '1','0');*/
INSERT INTO `sys_action` VALUES ('1010400', '1010000', 'url', '行业特性设置', 'sys/params/detail&sort=hytx', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010500', '1010000', 'url', '系统安全设置', 'sys/params/detail&sort=security_set', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010600', '1010000', 'url', '自动服务设置', 'sys/schedule/do_list', '6', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010700', '1010000', 'url', '平台参数配置', 'sys/platform_params/do_list', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010800', '1010000', 'url', '系统参数设置', 'sys/params/do_list', '8', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1011000', '1010000', 'url', '门店参数设置', 'sys/params/shop_do_list', '9', '1', '0', '1', '2');

INSERT INTO `sys_action` VALUES ('1010801', '1010800', 'act', '转单自动分仓拆单', 'sys_params_tran_order_auto_split', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('1010701', '1010700', 'act', '系统参数设置店铺权限', 'sys_params_shop_power', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010702', '1010700', 'act', '系统参数设置仓库权限', 'sys_params_store_power', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010703', '1010700', 'act', '系统参数设置品牌权限', 'sys_params_brand_power', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010704', '1010700', 'act', '系统参数设置财审金额权限', 'sys_params_fanance_money', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010705', '1010700', 'act', '系统参数设置自动通知截止发货时间权限', 'sys_params_off_deliver_time', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010706', '1010700', 'act', '系统参数设置自动通知配货权限', 'sys_params_oms_notice', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1010900', '1010000', 'url', '订单流程设置', 'sys/sys_oms/do_index', '8', '1', '0', '1','0');
/*
INSERT INTO `sys_action` VALUES ('1020000', '1000000', 'group', '短信管理', 'sys_msg', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1020100', '1020000', 'url', '短信模版', 'sys/sms_tpl/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1020200', '1020000', 'url', '短信发送日志', 'sys/sms_queue/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1020300', '1020000', 'url', '短信通道配置', 'sys/sms_supplier/do_list', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1020400', '1020000', 'url', '批量发送短信', 'sys/sms_queue/batch_send&app_scene=add', '3', '1', '0', '1','0');
*/
INSERT INTO `sys_action` VALUES ('1030000', '1000000', 'group', '用户权限', 'sys-user', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030100', '1030000', 'url', '角色列表', 'sys/role/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030101', '1030100', 'act', '查看', 'sys/role/detail#scene=view', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030102', '1030100', 'act', '新增', 'sys/role/detail#scene=add', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030103', '1030100', 'act', '编辑', 'sys/role/detail#scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030105', '1030100', 'act', '分配权限', 'sys/role/allot&app_scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030106', '1030100', 'act', '业务权限', 'sys/role_profession/do_list&app_scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030107', '1030100', 'act', '删除', 'sys/role/do_delete','0','1','0','1','0');
INSERT INTO `sys_action` VALUES ('1030200', '1030000', 'url', '用户列表', 'sys/user/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030201', '1030200', 'act', '查看', 'sys/user/detail#scene=view', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030202', '1030200', 'act', '新增', 'sys/user/detail#scene=add', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030203', '1030200', 'act', '编辑', 'sys/user/detail#scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030205', '1030200', 'act', '启用/停用', 'sys/user/update_active', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030211', '1030200', 'act', '重设密码', 'sys/user/reset_pwd', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030212', '1030200', 'act', '角色列表', 'sys/user/role_list', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1030213', '1030200', 'act', '删除','sys/user/do_delete','0','1','0','1','0');
INSERT INTO `sys_action` VALUES ('1030214', '1030200', 'act', '导出','sys/user/export_list','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('1040000', '1000000', 'group', '打印模版配置', 'base-print-tpl', '4', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040100', '1040000', 'url', '发货单模板', 'sys/flash_templates/edit&template_id=5&model=oms/DeliverRecordModel&typ=default', '0', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040110', '1040000', 'url', '发货单模板(新)', 'tprint/tprint/do_edit&print_templates_code=deliver_record', '0', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('1040101', '1040100', 'act', '查看', 'sys/danju_print/edit_print', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1040102', '1040100', 'act', '设置纸张', 'sys/danju_print/set_page_style&app_scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1040103', '1040100', 'act', '设置默认', 'sys/danju_print/set_default&app_fmt=json', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1040104', '1040100', 'act', '修改打印机', 'sys/danju_print/modify_printer&app_fmt=json', '0', '1', '0', '1','0');

-- INSERT INTO `sys_action` VALUES ('1040300', '1040000', 'url', '条码模板', 'sys/flash_templates/edit_td&template_id=11&model=prm/GoodsBarcodeModel&typ=default', '0', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040400', '1040000', 'url', '批发销货单模板', 'sys/flash_templates/edit&template_id=15&model=wbm/StoreOutRecordModel&typ=default', '0', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040500', '1040000', 'url', '采购入库单模板', 'sys/flash_templates/edit&template_id=17&model=pur/PurchaseRecordModel&typ=default', '0', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040600', '1040000', 'url', '装箱单模板', 'tprint/tprint/do_edit&print_templates_code=b2b_box', '0', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040700', '1040000', 'url', '波次单模板', 'sys/flash_templates/edit&template_id=27&model=oms/WavesRecordModel&typ=default', '0', '1', '0', '1', '0');
-- INSERT INTO `sys_action` VALUES ('1040800', '1040000', 'url', '发票模板', 'sys/flash_templates/edit_td&template_id=31&model=oms/InvoiceRecordModel&typ=default', '8', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1040900', '1040000', 'url', '箱唛打印模板', 'sys/weipinhuijit_box_print/do_list', '10', '1', '0', '1','0');
-- INSERT INTO `sys_action` VALUES ('1041000', '1040000', 'url', '移仓单模版', 'tprint/tprint/do_edit&print_templates_code=store_shift', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1040200', '1040000', 'url', '快递单模板', 'sys/express_tpl/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1041100', '1040000', 'url', '单据模板', 'sys/record_templates/do_list', '10', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('1090000', '1000000', 'group', '系统版本', 'sys-version-info', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1090100', '1090000', 'url', '版本信息', 'sys/sys_auth/do_list', '0', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('1100000', '1000000', 'group', '实施工具', 'gongju-info', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('1100100', '1100000', 'url', '系统初始化', 'prm/api_goods_export/do_list', '0', '1', '0', '1','0');
/*INSERT INTO `sys_action` VALUES ('1100101', '1100000', 'url', '订单转移', 'prm/tran_order/do_list', '0', '1', '0', '1','0');*/


INSERT INTO `sys_action` VALUES ('2000000', '0', 'cote', '基础数据', 'base', '50', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010000', '2000000', 'group', '订单档案', 'base-prm', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010300', '2010000', 'url', '支付方式', 'base/payment/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010302', '2010300', 'act', '编辑', 'base/payment/detail#scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010400', '2010000', 'url', '销售平台', 'base/sale_channel/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010402', '2010400', 'act', '编辑', 'base/sale_channel/detail#scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010403', '2010400', 'act', '删除', 'base/sale_channel/delete', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010404', '2010400', 'act', '启用/停用', 'base/sale_channel/update_active', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010500', '2010000', 'url', '配送方式', 'base/shipping/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010501', '2010500', 'act', '编辑模版', 'sys/shipping/edit_print', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010502', '2010500', 'act', '修改打印机', 'sys/shipping/modify_printer&app_fmt=json', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010600', '2010000', 'url', '快递公司', 'base/express_company/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010700', '2010000', 'url', '退货原因', 'base/return_reason/do_list', '10', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010701', '2010700', 'act', '新增', 'base/return_reason/detail#scene=add', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010702', '2010700', 'act', '编辑', 'base/return_reason/detail#scene=edit', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010703', '2010700', 'act', '删除', 'base/return_reason/delete', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010704', '2010700', 'act', '启用/停用', 'base/return_reason/active_switch', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010800', '2010000', 'url', '地址区域', 'base/taobao_area/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2010900', '2010000', 'url', '订单标签', 'base/order_label/do_list', '6', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('2011100', '2010000', 'url', '退单标签', 'base/return_label/do_list', '9', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2011200', '2010000', 'url', '订单挂起标签', 'base/suspend_label/do_list', '8', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2011300', '2010000', 'url', '扫描错误确认码', 'base/error_confirm_code/do_list', '11', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020000', '2000000', 'group', '仓储档案', 'base-store', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020100', '2020000', 'url', '仓库列表', 'base/store/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020101', '2020100', 'act', '添加仓库', 'base/store/detail#scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020102', '2020100', 'act', '编辑', 'base/store/detail#scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020103', '2020100', 'act', '删除', 'base/store/delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020200', '2020000', 'url', '库位管理', 'base/shelf/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020300', '2020000', 'url', '商品库位管理', 'prm/goods_shelf/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020400', '2020000', 'url', '仓库类别', 'base/store_type/do_list', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020401', '2020400', 'act', '添加仓库类别', 'base/store_type/detail#scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020402', '2020400', 'act', '编辑', 'base/store_type/detail#scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2020403', '2020400', 'act', '删除', 'base/store_type/delete', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('2060000', '2000000', 'group', '供应商档案', 'base-supplier-type', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2060100', '2060000', 'url', '供应商列表', 'base/supplier/do_list', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('2040000', '2000000', 'group', '业务类型', 'base-business-type', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040100', '2040000', 'url', '库存调整类型', 'base/store_adjust_type/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040101', '2040100', 'act', '新增', 'base/store_adjust_type/detail#scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040102', '2040100', 'act', '编辑', 'base/store_adjust_type/detail#scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040103', '2040100', 'act', '删除', 'base/store_adjust_type/delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040200', '2040000', 'url', '采购进货类型', 'base/record_type/do_list&record_type_property=0', '0', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040201', '2040200', 'act', '新增', 'base/record_type/detail#scene=add&record_type_property=0', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040202', '2040200', 'act', '编辑', 'base/record_type/detail#scene=edit&record_type_property=0', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040203', '2040200', 'act', '删除', 'base/record_type/delete#scene=edit&record_type_property=0', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040300', '2040000', 'url', '采购退货类型', 'base/record_type/do_list&record_type_property=1', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040301', '2040300', 'act', '新增', 'base/record_type/detail#scene=add&record_type_property=1', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040302', '2040300', 'act', '编辑', 'base/record_type/detail#scene=edit&record_type_property=1', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2040303', '2040300', 'act', '删除', 'base/record_type/delete#scene=edit&record_type_property=1', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('2050000', '2000000', 'group', '店铺档案', 'base-shop', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2050100', '2050000', 'url', '网络店铺', 'base/shop/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2050101', '2050100', 'act', '添加店铺', 'base/shop/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2050102', '2050100', 'act', '编辑', 'base/shop/detail&app_scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2050103', '2050100', 'act', '启用/停用', 'base/shop/update_active', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('2050200', '2050000', 'url', '实体店铺','base/shop_entity/do_list','1','1','0','1','2');
INSERT INTO `sys_action` VALUES ('2050300', '2050000', 'url', '店员列表','base/shop_clerk/do_list','1','1','0','1','2');

INSERT INTO `sys_action` VALUES ('3000000', '0', 'cote', '运营', 'operate', '30', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('3010000', '3000000', 'group', '会员管理', 'crm-manage', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3010100', '3010000', 'url', '会员列表', 'crm/customer/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3010101', '3010100', 'act', '添加', 'crm/customer/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3010102', '3010100', 'act', '编辑', 'crm/customer/detail&app_scene=edit', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('3020000', '3000000', 'group', '策略管理', 'pe-manage', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020100', '3020000', 'url', '订单快递适配策略', 'crm/express_strategy/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020101', '3020100', 'act', '添加', 'crm/express_strategy/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020102', '3020100', 'act', '编辑', 'crm/express_strategy/detail&app_scene=edit', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('3020200', '3020000', 'url', '订单设问策略', 'base/question_label/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020700', '3020000', 'url', '订单赠品策略', 'op/gift_strategy/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020703','3020700','act','启用/停用','op/op_gift_strategy/check_repeat','3','1','0','1','0');
INSERT INTO `sys_action` VALUES ('3020704','3020700','url','审核','op/op_gift_strategy/do_check','5','1','0', '1','0');

INSERT INTO `sys_action` VALUES ('3020400', '3020000', 'url', '订单合并规则', 'oms/order_combine_strategy/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020600', '3020000', 'url', '订单审核规则', 'oms/order_check_strategy/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020500', '3020000', 'url', '仓库适配策略', 'op/policy_store/do_list', '6', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3020800', '3020000', 'url', '库存同步策略', 'op/inv_sync/do_list', '7', '1', '0', '0', '0');

INSERT INTO `sys_action` VALUES ('4000000', '0', 'cote', '网络订单', 'order', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010000', '4000000', 'group', '平台交易', 'oms-trade', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4010300', '4010000', 'url', '平台商品列表', 'api/sys/goods/index', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010500', '4010000', 'url', '交易监控', 'api/sell_record_monitor/do_list', '5', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4010100', '4010000', 'url', '平台交易列表', 'oms/sell_record/td_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010101', '4010100', 'act', '下载', 'oms/sell_record/download', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010102', '4010100', 'act', '详情', 'oms/sell_record/td_view', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010103', '4010100', 'act', '转单', 'oms/sell_record/td_tran', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010104', '4010100', 'act', '置为已转单', 'oms/sell_record/td_traned', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010105', '4010100', 'act', ' 一键下载', 'oms/api_order/down', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010106', '4010100', 'act', '一键转单', 'oms/api_order/change', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010107', '4010100', 'act', '批量置为已转单', 'oms/sell_record/pl_td_traned', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010108', '4010100', 'act', '批量转单', 'oms/sell_record/pl_td_tran', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010109', '4010100', 'act', '修改', 'oms/sell_record/td_save', '2', '1', '0', '1', '0');

INSERT INTO `sys_action` VALUES ('4010200', '4010000', 'url', '平台网单回写列表', 'api/sys/order_send/index', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010201', '4010200', 'act', '正常回写', 'api/sys/order_send/callback1', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010202', '4010200', 'act', '再次回写', 'api/sys/order_send/callback2', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4010203', '4010200', 'act', '本地回写', 'api/sys/order_send/callback3', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4010400', '4010000', 'url', '平台退单列表', 'api/sys/order_refund/do_list', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4020000', '4000000', 'group', '销售订单', 'order-order', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020100', '4020000', 'url', '新增订单', 'oms/sell_record/add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020200', '4020000', 'url', '订单查询', 'oms/sell_record/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020201', '4020200', 'url', '批量订单拦截', 'oms/order_opt/opt_batch_intercept', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020202', '4020200', 'act', '导出', 'oms/sell_record/export_list', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4020300', '4020000', 'url', '订单列表', 'oms/sell_record/ex_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020301', '4020300', 'url', '确认', 'oms/order_opt/opt_confirm', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020302', '4020300', 'url', '取消确认', 'oms/order_opt/opt_unconfirm', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020303', '4020300', 'url', '批量修改发货仓库', 'oms/order_opt/opt_edit_store_code', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020304', '4020300', 'url', '批量修改仓库留言', 'oms/order_opt/opt_edit_store_remark', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020305', '4020300', 'url', '批量修改配送方式', 'oms/order_opt/opt_edit_express_code', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020306', '4020300', 'url', '挂起', 'oms/order_opt/opt_pending', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020307', '4020300', 'url', '解挂', 'oms/order_opt/opt_unpending', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020308', '4020300', 'url', '锁定', 'oms/order_opt/opt_lock', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020309', '4020300', 'url', '解锁', 'oms/order_opt/opt_unlock', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020310', '4020300', 'url', '付款', 'oms/order_opt/opt_pay', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020311', '4020300', 'url', '取消付款', 'oms/order_opt/opt_unpay', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020312', '4020300', 'url', '通知配货', 'oms/order_opt/opt_notice_shipping', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020313', '4020300', 'url', '取消通知配货', 'oms/order_opt/opt_unnotice_shipping', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020314', '4020300', 'url', '生成退单', 'oms/order_opt/opt_create_return', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020315', '4020300', 'url', '设为问题单', 'oms/order_opt/opt_problem', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020316', '4020300', 'url', '返回正常单', 'oms/order_opt/opt_unproblem', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020317', '4020300', 'url', '作废', 'oms/order_opt/opt_cancel', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020318', '4020300', 'url', '复制订单', 'oms/order_opt/opt_copy', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020319', '4020300', 'url', '强制解锁', 'oms/order_opt/opt_force_unlock', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020320', '4020300', 'url', '手工发货', 'oms/order_opt/opt_send', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020321', '4020300', 'url', '订单拦截', 'oms/order_opt/opt_intercept', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020322', '4020300', 'url', '急单', 'oms/order_opt/set_rush', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020323', '4020300', 'act', '导出', 'oms/sell_record/export_ext_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020340', '4020300', 'act', '结算', 'oms/order_opt/opt_settlement', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020341', '4020300', 'act', '取消结算', 'oms/order_opt/opt_unsettlement', '5', '1', '0', '1','0');

/*
INSERT INTO `sys_action` VALUES ('4020301', '4020300', 'act', '电商订单流程处理', 'oms/sell_record/opt', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020302', '4020300', 'act', '电商订单流程批量处理', 'oms/sell_record/opt_batch', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020303', '4020300', 'act', '修改配送方式', 'oms/sell_record/edit_express_code', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020304', '4020300', 'act', '自动匹配物流单号', 'oms/sell_record/edit_express_no', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020305', '4020300', 'act', '打印发货单', 'oms/sell_record/mark_sell_record_print', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020306', '4020300', 'act', '打印快递单', 'oms/sell_record/print_express', '2', '1', '0', '1','0');
*/
INSERT INTO `sys_action` VALUES ('4020400', '4020000', 'url', '问题订单列表', 'oms/sell_record/question_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020401', '4020400', 'act', '批量返回正常单', 'oms/sell_record/opt_batch1', '1', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('4020500', '4020000', 'url', '缺货订单列表', 'oms/sell_record/short_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020501', '4020500', 'act', '解除缺货', 'oms/order_opt/remove_short', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020502', '4020500', 'act', '批量拆分订单', 'oms/order_opt/split_short', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020503', '4020500', 'act', '导出', 'oms/sell_record/exprot_short_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020504', '4020500', 'act', '强制解除缺货', 'oms/order_opt/force_remove_short', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4020600', '4020000', 'url', '合并订单列表', 'oms/sell_record_combine/do_list', '6', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020700', '4020000', 'url', '挂起订单列表', 'oms/sell_record/pending_list', '7', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4020800', '4020000', 'url', '已发货订单列表', 'oms/sell_record/shipped_list', '8', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4030000', '4000000', 'group', '销售退单', 'order-return', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030100', '4030000', 'url', '售后服务单', 'oms/sell_return/after_service_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030101', '4030100', 'act', '确认', 'oms/return_opt/opt_confirm', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030102', '4030100', 'act', '取消确认', 'oms/return_opt/opt_unconfirm', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030103', '4030100', 'act', '通知财务退款', 'oms/return_opt/opt_notice_finance', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030104', '4030100', 'act', '取消通知财务', 'oms/return_opt/opt_unnotice_finance', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030105', '4030100', 'act', '财务确认退款', 'oms/return_opt/opt_finance_confirm', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030106', '4030100', 'act', '财务退回', 'oms/return_opt/opt_finance_reject', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030107', '4030100', 'act', '通知仓库收货', 'oms/return_opt/opt_notice_store', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030108', '4030100', 'act', '取消通知仓库', 'oms/return_opt/opt_unnotice_store', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030109', '4030100', 'act', '确认收货', 'oms/return_opt/opt_return_shipping', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030110', '4030100', 'act', '生成换货单', 'oms/return_opt/opt_create_change_order', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030111', '4030100', 'act', '作废', 'oms/return_opt/opt_cancel', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030112', '4030100', 'act', '换货单商品信息-删除', 'oms/return_opt/change_goods_del', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030113', '4030100', 'act', '换货单商品信息-改款', 'oms/return_opt/change_goods_change', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4030114', '4030100', 'act', '完成', 'oms/return_opt/opt_finish', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('4040000', '4000000', 'group', '订单流程', 'order-action-show', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4040100', '4040000', 'url', '订单处理流程', 'index/order', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8000000', '0', 'cote', '网络分销', 'fenxiao', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8010000', '8000000', 'group', '基础数据', 'base-fenxiao', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8010100', '8010000', 'url', '分销商列表', 'base/custom/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8010400', '8010000', 'url', '分销商等级', 'base/custom_grades/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8010600', '8010000', 'url', '分销商审核', 'base/custom/review_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020000', '8000000', 'group', '网络批发管理', 'wbm_manage', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8080000', '8000000', 'group', '分销商品', 'fenxiao_goods', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8080100', '8080000', 'url', '分销商品列表', 'fx/goods/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8080200', '8080000', 'url', '分销产品线管理', 'fx/goods_manage/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8080300', '8080000', 'url', '商品库存查询', 'fx/goods_inv/do_list', '10', '1', '0', '1','0');



INSERT INTO `sys_action` VALUES ('8020100', '8020000','url','批发销货单','wbm/store_out_record/do_list','3','1','0','1','0');
INSERT INTO `sys_action` VALUES ('8020101', '8020100', 'act', '确认/取消确认', 'wbm/store_out_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020102', '8020100', 'act', '删除', 'wbm/store_out_record/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020103', '8020100', 'act', '出库', 'wbm/store_out_record/do_shift_out', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020104', '8020100', 'act', '导出', 'wbm/store_out_record/export_list', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8020200', '8020000','url','批发退货单','wbm/return_record/do_list','4','1','0','1','0');
INSERT INTO `sys_action` VALUES ('8020201', '8020200', 'act', '确认/取消确认', 'wbm/return_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020202', '8020200', 'act', '删除', 'wbm/return_record/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020203', '8020200', 'act', '入库', 'wbm/return_record/do_shift_in', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8020300', '8020000','url','批发销货通知单','wbm/notice_record/do_list','2','1','0','1','0');
INSERT INTO `sys_action` VALUES ('8020301', '8020300', 'act', '确认/取消确认', 'wbm/notice_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020302', '8020300', 'act', '删除', 'wbm/notice_record/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020303', '8020300', 'act', '终止', 'wbm/notice_record/do_stop', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020304', '8020300', 'act', '生成销货单', 'wbm/notice_record/do_execute', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8020400', '8020000','url','批发退货通知单','wbm/return_notice_record/do_list','3','1','0','1','0');
INSERT INTO `sys_action` VALUES ('8020401', '8020400', 'act', '确认/取消确认', 'wbm/return_notice_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020402', '8020400', 'act', '删除', 'wbm/return_notice_record/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020403', '8020400', 'act', '生成退单', 'wbm/return_notice_record/do_return', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8020404', '8020400', 'act', '完成', 'wbm/return_notice_record/do_finish', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8020500', '8020000','url','批发统计分析','wbm/wbm_report/do_list','5','1','0','1','0');


INSERT INTO `sys_action` VALUES ('8030000', '8000000', 'group', '平台分销', 'platform-fenxiao', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8030100', '8030000', 'url', '淘宝分销商品', 'api/api_taobao_fx_order/product_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8030200', '8030000', 'url', '淘宝分销订单', 'api/api_taobao_fx_order/td_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8030300', '8030000', 'url', '淘宝分销退单', 'api/api_taobao_fx_refund/do_list', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('8070000', '8000000', 'group', '网络代销管理', 'net_fenxiao_manager', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8070100', '8070000', 'url', '新增分销订单', 'fx/sell_record/add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8070200', '8070000', 'url', '分销订单查询', 'fx/sell_record/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8070300', '8070000', 'url', '分销订单列表', 'fx/sell_record/ex_list', '10', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8070400', '8070000', 'url', '分销退单列表', 'fx/sell_return/after_service_list', '15', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('5000000', '0', 'cote', '商品', 'goods', '40', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010000', '5000000', 'group', '商品属性', 'goods-property', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010100', '5010000', 'url', '季节', 'base/season/do_list', '6', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010101', '5010100', 'act', '添加季节', 'base/season/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010102', '5010100', 'act', '编辑', 'base/season/detail&app_scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010103', '5010100', 'act', '删除', 'base/season/do_delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010200', '5010000', 'url', '年份', 'base/year/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010201', '5010200', 'act', '添加年份', 'base/year/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010202', '5010200', 'act', '编辑', 'base/year/detail&app_scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010203', '5010200', 'act', '删除', 'base/year/do_delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010300', '5010000', 'url', '品牌', 'prm/brand/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010301', '5010300', 'act', '添加品牌', 'prm/brand/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010302', '5010300', 'act', '编辑', 'prm/brand/detail&app_scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010303', '5010300', 'act', '删除', 'prm/brand/do_delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010400', '5010000', 'url', '分类', 'prm/category/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010401', '5010400', 'act', '添加分类', 'prm/category/detail&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010402', '5010400', 'act', '编辑', 'prm/category/detail&app_scene=edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010403', '5010400', 'act', '删除', 'prm/category/do_delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010404', '5010400', 'act', '新增子分类', 'prm/category/detail&app_scene=add&child=1', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010500', '5010000', 'url', '规格2', 'prm/spec2/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010501', '5010500', 'act', '编辑', 'prm/spec2/detail&app_scene=edit', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010502', '5010600', 'act', '删除', 'prm/spec2/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010600', '5010000', 'url', '规格1', 'prm/spec1/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010601', '5010600', 'act', '设置规格1、2别名', 'sys/goods_rule/detail&app_scene=edit', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010602', '5010600', 'act', '添加规格1', 'prm/spec1/detail&app_scene=add', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010603', '5010600', 'act', '编辑', 'prm/spec1/detail&app_scene=edit', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5010604', '5010600', 'act', '删除', 'prm/spec1/do_delete', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020000', '5000000', 'group', '商品管理', 'goods-goods', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020100', '5020000', 'url', '条码管理', 'prm/goods_barcode/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020103', '5020100', 'act', '条码编辑', 'prm/goods_barcode/edit_barcode', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('5020300', '5020000', 'url', '条码识别方案', 'prm/goods_barcode_identify_rule/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020101', '5020100', 'act', '删除', 'prm/goods_barcode/do_delete', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020200', '5020000', 'url', '商品列表', 'prm/goods/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020201', '5020200', 'act', '添加商品', 'prm/goods/detail&action=do_add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020202', '5020200', 'act', '编辑', 'prm/goods/detail&action=do_edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020203', '5020200', 'act', '启用/停用', 'prm/goods/update_active', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020204', '5020200', 'act', '导出', 'prm/goods/export_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020400', '5020000', 'url', '商品信息导入', 'prm/goods_import/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020500', '5020000', 'url', '商品扩展属性', 'prm/goods_property/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020102', '5020100', 'act', '导出', 'prm/goods_barcode/export_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020600', '5020000', 'url', '商品套餐列表', 'prm/goods_combo/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020601', '5020600', 'act', '添加商品套餐', 'prm/goods_combo/detail&action=do_add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020602', '5020600', 'act', '编辑', 'prm/goods_combo/detail&app_scene=do_edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020603', '5020600', 'act', '启用/停用', 'prm/goods_combo/update_active', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5020604', '5020600', 'act', '库存查看', 'prm/goods_combo/goods_inv', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030000', '5000000', 'group', '商品唯一码', 'goods-unique-code', '3', '1', '0', '0','0');
INSERT INTO `sys_action` VALUES ('5030100', '5030000', 'url', '唯一码档案', 'prm/goods_unique_code/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030200', '5030000', 'url', '唯一码跟踪', 'prm/goods_unique_code/do_log_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5040000', '5000000', 'group', '商品铺货', 'goods-api', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5040100', '5040000', 'url', '淘宝商品铺货', 'api/taobao/goods/ph_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5040101', '5040100', 'act', '导出', 'api/taobao/goods/ph_export_list', '2', '1', '0', '1','0');
/*
INSERT INTO `sys_action` VALUES ('5030000', '5000000', 'group', '商品铺货', 'goods-api', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030100', '5030000', 'url', '淘宝商品管理', 'api/base_item/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030101', '5030100', 'act', '下载商品', 'api/base_item/dl_taobao_items&app_scene=add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030102', '5030100', 'act', '总库存同步', 'api/base_item/batch_store_synchro&app_scene=add', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('5030103', '5030100', 'act', 'SKU级别库存同步', 'api/base_item/store_synchro_by_sku_id&app_fmt=json', '3', '1', '0', '1','0');
*/
INSERT INTO `sys_action` VALUES ('6000000', '0', 'cote', '进销存', 'stm', '20', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010000', '6000000', 'group', '库存管理', 'stm_manage', '2', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('6010100', '6010000', 'url', '调整单', 'stm/stock_adjust_record/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010101', '6010100', 'act', '查看', 'stm/stock_adjust_record/view', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010102', '6010100', 'act', '验收', 'stm/stock_adjust_record/do_checkin', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010103', '6010100', 'act', '删除', 'stm/stock_adjust_record/do_delete_detail', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('6010500', '6010000', 'url', '盘点单', 'stm/take_stock_record/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010600', '6010000', 'url', '移仓单', 'stm/store_shift_record/do_list', '5', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010700', '6010000', 'url', '库存维护', 'prm/inv/maintain_list', '6', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('6010800', '6010000', 'url', '商品组装单', 'stm/stm_goods_diy_record/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010801', '6010800', 'act', '查看', 'stm/stm_goods_diy_record/view', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010802', '6010800', 'act', '确认', 'stm/stm_goods_diy_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010803', '6010800', 'act', '删除', 'stm/stm_goods_diy_record/do_delete', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6010804', '6010800', 'act', '添加', 'stm/stm_goods_diy_record/detail', '3', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('6020000', '6000000', 'group', '库存查询', 'stm_search', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6020100', '6020000', 'url', '商品库存查询', 'prm/inv/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6020400', '6020000', 'url', '商品批次库存查询', 'prm/inv_lof/do_list', '1', '1', '0', '0','0');
INSERT INTO `sys_action` VALUES ('6020200', '6020000', 'url', '库存流水帐', 'prm/inv_record/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6020300', '6020000', 'url', '商品进销存分析', 'rpt/report_jxc/do_list&url_id=inv', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('6030000', '6000000', 'group', '采购管理', 'purchase_manage', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030300','6030000','url','采购订单','pur/planned_record/do_list','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('6030400','6030000','url','采购通知单','pur/order_record/do_list','2','1','0','1','0');
INSERT INTO `sys_action` VALUES ('6030100', '6030000', 'url', '采购入库单', 'pur/purchase_record/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030101', '6030100', 'act', '删除', 'pur/purchase_record/do_delete', '1', '1', '0', '1', '0');
INSERT INTO `sys_action` VALUES ('6030102', '6030100', 'act', '验收', 'pur/purchase_record/do_checkin', '2', '1', '0', '1', '0');
INSERT INTO `sys_action` VALUES ('6030200', '6030000', 'url', '采购退货单', 'pur/return_record/do_list', '5', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('6030500', '6030000', 'url', '采购退货通知单', 'pur/return_notice_record/do_list', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030501', '6030500', 'act', '确认/取消确认', 'pur/return_notice_record/do_sure', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030502', '6030500', 'act', '删除', 'pur/return_notice_record/do_delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030503', '6030500', 'act', '终止', 'pur/return_notice_record/do_stop', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030504', '6030500', 'act', '生成退货单', 'pur/return_notice_record/do_execute', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030505', '6030500', 'act', '添加', 'pur/return_notice_record/detail&app_scene=add', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030506', '6030500', 'act', '编辑', 'pur/return_notice_record/do_edit', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('6030600', '6030000', 'url', '采购统计分析', 'pur/purchase_analyse/do_list', '10', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('7000000','0','cote','配发货','with-delivery','10','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010001','7000000','group','订单发货管理','with-delivery-odm','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010104','7010001','url','波次生成策略','oms/sell_record_notice/do_list','9','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010101','7010001','url','订单波次生成','oms/sell_record/fh_list','10','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010102','7010001','url','订单波次打印','oms/waves_record/do_list','20','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010111','7010001','url','已发货订单列表','oms/sell_record/shipped_list', '32', '1', '0', '1', '0');
INSERT INTO `sys_action` VALUES ('7010108','7010102','act','验收且发货','oms/waves_record/do_accept_and_send','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010109','7010102','act','整单发货/批量发货','oms/waves_record/waves_batch_send','60','1','0','1','0');

INSERT INTO `sys_action` VALUES ('7010103','7010001','url','订单扫描验货','oms/deliver_record/check','30','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010105','7010001','url','订单包裹查询','oms/deliver_record/search_package','50','1','0','0','0');
INSERT INTO `sys_action` VALUES ('7010110','7010001','url','待称重订单列表','oms/sell_record_cz/no_weighing_list','40','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010106','7010001','url','订单称重校验','oms/sell_record_cz/view','41','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7010107','7010001','url','已称重订单列表','oms/sell_record_cz/do_list','42','1','0','1','0');




INSERT INTO `sys_action` VALUES ('7020001','7000000','group','装箱打印','with-xiang-odm','2','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7020101','7020001','url','装箱任务列表','b2b/box_task/do_list','2','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7020102','7020001','url','装箱单列表','b2b/box_record/do_list','3','1','0','1','0');

INSERT INTO `sys_action` VALUES ('7030001','4000000','url','外包仓管理','wms_store','5','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7030101','7030001','url','外包仓零售单','wms/wms_trade/do_list&wmsId=oms','10','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7030102','7030001','url','外包仓进销存单','wms/wms_trade/do_list&wmsId=b2b','20','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7030103','7030001','url','外包仓库存','wms/wms_trade/inv_list','30','1','0','1','0');

INSERT INTO `sys_action` VALUES ('7030110', '7030101', 'act', '强制取消', 'wms/wms_mgr/force_cancel', '10', '1', '0', '1','0');



/*
INSERT INTO `sys_action` VALUES ('7010104','7010001','url','订单称重校验','oms/deliver_record/weigh','40','1','0','1','0');
*/

INSERT INTO `sys_action` VALUES ('7020000','7000000','group','订单退货拆包','with-delivery-orp','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('7020100','7020000','url','退货包裹单','oms/sell_return/package_list','1','1','0','1','0');

/*
--INSERT INTO `sys_action` VALUES ('8000000', '0', 'cote', '网络分销', 'order_ncm', '5', '1', '0', '1','0');
*/
INSERT INTO `sys_action` VALUES ('9000000', '0', 'cote', '账务', 'finance', '25', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('9060000', '9000000', 'group', '分销账务管理', 'fx_manage', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('9060100', '9060000','url','分销商预存款','fx/account/do_list','10','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9060101', '9060100', 'act', '充值确认', 'fx/account/confirm', '10', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('9060300', '9060000','url','分销结算单','fx/account_settlement/do_list','13','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9060200', '9060000','url','分销商往来流水账','fx/running_account/do_list','15','1','0','1','0');

INSERT INTO `sys_action` VALUES ('9010000', '9000000', 'group', '应付帐管理', 'return_manage', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('9010100','9010000','url','待退款订单列表','oms/sell_return_finance/do_list','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9020000', '9000000', 'group', '应收帐管理', 'receivable_manage', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('9020100','9020000','url','零售结算明细单','acc/retail_settlement_detail/do_list','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9020200','9020000','url','零售结算汇总查询','acc/retail_settlement_total/do_list','2','1','0','1','0');

INSERT INTO `sys_action` VALUES ('9030000', '9000000', 'group', '支付宝对账', 'alipay_manage', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('9030100','9030000','url','支付宝收支流水','acc/api_taobao_alipay/do_list','5','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9030200','9030000','url','支付宝流水核销统计','acc/report_alipay/do_list','1','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9030300','9030000','url','支付宝流水核销查询','acc/api_taobao_alipay/search_list','2','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9030400','9030000','url','零售结算核销统计','acc/report_sell_settlement/do_list','3','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9030500','9030000','url','零售结算核销查询','acc/sell_settlement/do_list','4','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9030600','9030000','url','对账科目','acc/alipay_account_item/do_list','6','1','0','1','0');

INSERT INTO `sys_action` VALUES ('9040000','9000000','group','成本管理','cost_manage','5','1','0','1','0');
INSERT INTO `sys_action` VALUES ('9040100','9040000','url','商品成本月结单','acc/cost_month/do_list','1','1','0','1','0');

INSERT INTO `sys_action` VALUES ('21000000', '0', 'cote', '报表', 'reports', '35', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010000', '21000000', 'group', '销售报表', 'sell_report', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010100', '21010000', 'url', '销售数据分析', 'rpt/sell_report/data_analyse', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010200', '21010000', 'url', '商品销售排行分析', 'rpt/goods_report/trends', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010300', '21010000', 'url', '店铺运营数据分析', 'rpt/shop_report/data_analyse', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010400', '21010000', 'url', '销售商品毛利分析', 'rpt/sell_goods_profit_rate/data_analyse', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21010500', '21010000', 'url', '商品滞销分析', 'rpt/unsalable_report/do_list', '10', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('21001000', '21000000', 'group', '发货统计', 'sr-reports', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21001010', '21001000', 'url', '订单发货数据分析', 'rpt/sell_record/shipped', '1', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('21020000', '21000000', 'group', '售后统计', 'cs-reports', '2', '1', '0', '1', '0');
INSERT INTO `sys_action` VALUES ('21020100', '21020000', 'url', '售后退货数据分析', 'rpt/sell_return/after_analysis', '1', '1', '0', '1', '0');

INSERT INTO `sys_action` VALUES ('21002000', '21000000', 'group', '账务报表', 'cw-reports', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('21002010', '21002000', 'url', '商品进销存分析', 'rpt/report_jxc/do_list', '3', '1', '0', '1','0');

INSERT INTO `sys_action` VALUES ('22000000', '0', 'cote', '增值服务', 'value', '75', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('22010000', '22000000', 'group', '增值服务', 'value_service', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('22010100', '22010000', 'url', '增值服务订购', 'sys/service/do_value_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('22010200', '22010000', 'url', '已订购增值服务', 'sys/service/do_order_list', '2', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('80000000', '0', 'cote', '系统集成', 'integrate', '70', '1', '0', '1','0');




/*INSERT INTO `sys_action` VALUES ('10000000','0','cote','平台接口','api-platform-manage','1','1','0','1','1');
INSERT INTO `sys_action` VALUES ('10010000','10000000','group','基础配置','platform-base','10','1','0','1','1');

INSERT INTO `sys_action` VALUES ('10020000','10000000','group','淘宝集成','taobao-manage','20','1','0','1','1');
INSERT INTO `sys_action` VALUES ('10020200', '10020000', 'url', '淘宝商品管理', 'api/taobao/goods/do_list', '1', '1', '0', '1','1');
INSERT INTO `sys_action` VALUES ('10020100', '10020000', 'url', '物流公司', 'api/taobao/express/index', '1', '1', '0', '1','1');
INSERT INTO `sys_action` VALUES ('10020300', '10020000', 'url', '淘宝订单全链路', 'sys/order_link/do_list', '1', '1', '0', '1','1');

INSERT INTO `sys_action` VALUES ('10030000','10000000','group','京东集成','jingdong-manage','30','1','0','1','1');
INSERT INTO `sys_action` VALUES ('10040000','10000000','group','唯品会集成','weipinhui-manage','40','1','0','1','1');
INSERT INTO `sys_action` VALUES ('10050000','10000000','group','其他集成','other-platform-manage','50','1','0','1','1');*/


INSERT INTO `sys_action` VALUES ('11000000','0','cote','WMS集成','api-wms-manage','2','1','0','1','1');
INSERT INTO `sys_action` VALUES ('11010000','11000000','group','基础配置','api-wms-base','60','1','0','1','1');
INSERT INTO `sys_action` VALUES ('11010100','11010000','url','WMS配置','sys/wms_config/do_list','1','1','0','1','1');
INSERT INTO `sys_action` VALUES ('11020000','11000000','group','WMS集成接口','wms-manage','70','1','0','1','1');

INSERT INTO `sys_action` VALUES ('12000000','0','cote','ERP集成','api-erp-manage','3','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12010000','12000000','group','基础配置','api-erp-base','80','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12010100','12010000','url','ERP配置','sys/erp_config/do_list','1','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12020000','12000000','group','百胜BSERP2','bserp2-manage','90','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12020100','12020000','url','BSERP2单据同步','erp/bserp/trade_list','1','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12030000','12000000','group','百胜BS3000J','bs3000j-manage','100','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12030100','12030000','url','BS3000J单据同步','erp/bs3000j/trade_list','1','1','0','1','1');
INSERT INTO `sys_action` VALUES ('12030200','12030000','url','BS3000J商品库存维护','erp/bs3000j_inv_sync/trade_list','1','1','0','1','1');

UPDATE sys_action SET action_id = '4050000',sort_order='5' WHERE action_name = '订单流程';
UPDATE sys_action SET action_id = '4050100',parent_id='4050000' WHERE action_name = '订单处理流程';
INSERT INTO `sys_action` VALUES ('4040000', '4000000', 'group', '工具箱', 'order_gift', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4040100', '4040000', 'url', '订单赠品工具', 'oms/order_gift/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4040101', '4040100', 'url', '添加赠品', 'oms/order_gift/add', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4040102', '4040100', 'url', '删除赠品', 'oms/order_gift/delete', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('4060000', '4040000', 'url', '按排名送赠品', 'oms/order_gift/rank_list', '2', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('3030000', '3000000', 'group', '运营罗盘', 'operate-manage', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3030100', '3030000', 'url', '运营分析', 'crm/operate_fx/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3030500', '3030000', 'url', '月度销售分析', 'crm/monthly_analysis/do_list', '50', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('3030200', '3030000', 'url', '商品补货建议', 'op/pur_advise/do_list', '100', '1', '0', '1','0');


INSERT INTO `sys_action` VALUES ('8040000', '8000000', 'group', '唯品会JIT', 'platform-weipinhuijit', '4', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8040100', '8040000', 'url', '档期管理', 'api/api_weipinhuijit_po/do_list', '1', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8040200', '8040000', 'url', '拣货单管理', 'api/api_weipinhuijit_pick/do_list', '2', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8040300', '8040000', 'url', '出库单管理', 'api/api_weipinhuijit_delivery/do_list', '3', '1', '0', '1','0');
INSERT INTO `sys_action` VALUES ('8040400', '8040000', 'url', '唯品会仓库管理', 'api/api_weipinhuijit_warehouse/do_list', '4', '1', '0', '1', '0');

INSERT INTO `sys_action` VALUES ('30000000','0','cote','门店管理','store-management','15','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30010000','30000000','group','门店库存','store-management-inv','4','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30010104','30010000','url','门店库存调拨单','stm/store_shift_record/entity_shop','5','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30010108','30010000','url','门店库存调整单','stm/stock_adjust_record/entity_shop','10','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30010300', '30010000', 'url', '门店商品库存查询', 'prm/inv/do_list', '11', '1', '0', '1','2');
INSERT INTO `sys_action` VALUES ('30020000','30000000','group','门店会员','client_manage','30','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30020100','30020000','url','门店会员列表','crm/client/do_list','1','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30020101','30020100','act','编辑','crm/client/detail&app_scene=edit','1','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30030000','30000000','group','门店零售','store_sell','3','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30030100','30030000','url','门店销售订单','oms_shop/oms_shop/do_list','1','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30040000','30000000','group','门店收银','store_cashier','2','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30040100','30040000','url','前台收银','oms_shop/cashier/do_list','1','1','0','1','2');
INSERT INTO `sys_action` VALUES ('30050000', '30000000', 'group', '门店档案', 'store_archives', '1', '1', '0', '1', '2');
INSERT INTO `sys_action` VALUES ('30050100', '30050000', 'url', '门店商品', 'prm/shop_goods/do_list', '2', '1', '0', '1', '2');s
