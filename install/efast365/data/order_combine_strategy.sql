INSERT  INTO `order_combine_strategy`(rule_code,rule_status_value,rule_desc,rule_scene_value,remark) VALUES ( 'order_outo_combine', '1', '已打印的订单不参与合并（快递单打印或发货单打印，系统均认为为已打印），默认启用', '0', 'rule_status_value:1-开启 0-关闭;rule_scene_value:0-仅自动合并 1-手工合并自动合并');
INSERT  INTO `order_combine_strategy`(rule_code,rule_status_value,rule_desc,rule_scene_value,remark) VALUES ( 'order_combine_is_change', '1', '换货单参与合并', '0', '');
INSERT  INTO `order_combine_strategy`(rule_code,rule_status_value,rule_desc,rule_scene_value,remark) VALUES ( 'order_combine_is_split', '1', '拆分单参与合并', '0', '');