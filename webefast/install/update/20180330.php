<?php

$u['2194']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5020605', '5020600', 'act', '删除', 'prm/goods_combo/do_delete', '4', '1', '0', '1', '0');",
);
$u['2184']=array(
    "alter table `oms_sell_record` add column `cancel_time`  datetime not null default '0000-00-00 00:00:00' comment '作废时间';",
);
$u['2195']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060105', '9060100', 'act', '支付宝收款参数设置', 'fx/account/install_alipay_key', '1', '1', '0', '1', '0');",
);
//唯品会档期po添加brand_name
$u['2188']=array(
    "alter table `api_weipinhuijit_po` add column `brand_name` varchar(100) NULL DEFAULT '' COMMENT '品牌名称';",
);
$u['bug_2339'] = array(
    "update sys_print_templates set template_body = '&lt;div id=&quot;report&quot;&gt;&lt;div title=&quot;报表头&quot; class=&quot;group&quot; id=&quot;report_top&quot; style=&quot;float: left; margin: 0 35px; &quot;&gt;&lt;div class=&quot;row border&quot; id=&quot;row_0&quot; style=&quot;height: 60px;&quot; nodel=&quot;1&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_0&quot; style=&quot;width: 245px; height: 40px; text-align: center; line-height: 40px; font-size: 18px;&quot;&gt;采购入库单&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_9&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_117&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;单据编号：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_118&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@单据编号}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_10&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_119&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;原单号：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_123&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@原单号}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_11&quot; style=&quot;float:left;width:243px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_120&quot; style=&quot;height: 30px; line-height: 30px; width: 65px; text-align: right;&quot;&gt;下单时间：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_121&quot; style=&quot;width: 145px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@下单时间}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_12&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_122&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;业务日期：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_124&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@业务日期}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_13&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_125&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;供应商：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_126&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@供应商}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_14&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_127&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;仓库：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_128&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@仓库}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_15&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_129&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;总数量： &lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_130&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@总数量} &lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_16&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_131&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;总金额：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_132&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@总金额}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_17&quot; style=&quot;float:left;width:220px;&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_133&quot; style=&quot;height: 30px; line-height: 30px; width: 70px; text-align: right;&quot;&gt;备注：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_134&quot; style=&quot;width: 100px; text-align: left; height: 30px; line-height: 30px;&quot;&gt;{@备注}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div title=&quot;表格&quot; class=&quot;group&quot; id=&quot;report_table_body&quot; type=&quot;table&quot; nodel=&quot;1&quot; style=&quot;float: left; margin: 0 35px;&quot;&gt;&lt;table class=&quot;table&quot; id=&quot;table_1&quot; border=&quot;0&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;&lt;tr&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 100px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_69&quot; style=&quot;width: 100px; height: 30px; line-height: 30px;&quot;&gt;商品名称&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 80px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_70&quot; style=&quot;width: 50px;&quot;&gt;商品编码&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_71&quot; style=&quot;width: 40px;&quot;&gt;规格1&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_72&quot; style=&quot;width: 40px; height: 20px; line-height: 20px;&quot;&gt;颜色 &lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_73&quot;&gt;商品条形码 &lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_74&quot;&gt;库位&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_75&quot;&gt;单价&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_76&quot;&gt;数量 &lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_77&quot;&gt;金额&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 60px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_78&quot;&gt;通知数&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_160&quot;&gt;吊牌价&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;&lt;!--detail_list--&gt;&lt;/table&gt;&lt;/div&gt;&lt;div title=&quot;表格尾&quot; class=&quot;group&quot; id=&quot;report_table_bottom&quot; style=&quot;float:left; margin: 0 35px;&quot;&gt;&lt;div class=&quot;row border&quot; id=&quot;row_2&quot; nodel=&quot;1&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_6&quot; style=&quot;width: 60px; text-align: left;&quot;&gt;合计：     &lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_22&quot; style=&quot;width: 120px; text-align: right;&quot;&gt;{@总金额}        &lt;/div&gt;&lt;/div&gt;&lt;div id=&quot;row_19&quot; class=&quot;row border&quot;&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 80px;&quot; id=&quot;column_172&quot; class=&quot;column&quot;&gt;总通知数        &lt;/div&gt;&lt;div style=&quot;height: 22px; line-height: 22px; width: 100px;&quot; id=&quot;column_173&quot; class=&quot;column&quot;&gt;{@总通知数}        &lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;' where print_templates_code = 'pur_purchaser_new' ;"
);
//创建erp_商品档案表
$u['2178'] = array(
    "CREATE TABLE `api_erp_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(50) NOT NULL COMMENT '供应商编号',
  `supplier_name` varchar(100) NOT NULL COMMENT '供应商名称',
  `item_code` varchar(50) NOT NULL COMMENT '商品编码',
  `item_id` varchar(50) DEFAULT '' COMMENT '商品id',
  `item_name` varchar(100) DEFAULT '' COMMENT '商品名',
  `short_name` varchar(50) DEFAULT '' COMMENT '商品简称',
  `english_name` varchar(100) DEFAULT '' COMMENT '英文名',
  `bar_code` varchar(255) DEFAULT '' COMMENT '条形码可多个用分号（;）隔开',
  `sku_property` varchar(100) DEFAULT '' COMMENT '商品属性 (如红色XXL)',
  `stock_unit` varchar(20) DEFAULT '' COMMENT '商品计量单位',
  `length` varchar(60) DEFAULT '' COMMENT '长 (厘米)',
  `width` varchar(60) DEFAULT '' COMMENT '宽 (厘米)',
  `height` varchar(60) DEFAULT '' COMMENT '高 (厘米)',
  `volume` varchar(60) DEFAULT '' COMMENT '体积 (升)',
  `gross_weight` varchar(60) DEFAULT '' COMMENT '毛重 (千克)',
  `net_weight` varchar(60) DEFAULT '' COMMENT '净重 (千克)',
  `color` varchar(60) DEFAULT '' COMMENT '颜色',
  `size` varchar(60) DEFAULT '' COMMENT '尺寸',
  `title` varchar(255) DEFAULT '' COMMENT '渠道中的商品标题',
  `category_id` varchar(60) DEFAULT '' COMMENT '商品类别ID',
  `category_name` varchar(60) DEFAULT '' COMMENT '商品类别名称',
  `pricing_category` varchar(60) DEFAULT '' COMMENT '计价货类',
  `safety_stock` int(11) DEFAULT '0' COMMENT '安全库存',
  `item_type` varchar(40) DEFAULT '' COMMENT '商品类型 (ZC 正常商品)',
  `tag_price` decimal(20,3) DEFAULT '0.000' COMMENT '吊牌价',
  `retail_price` decimal(20,3) DEFAULT '0.000' COMMENT '零售价',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本价',
  `purchase_price` decimal(20,3) DEFAULT '0.000' COMMENT '采购价',
  `season_code` varchar(100) DEFAULT '' COMMENT '季节编码',
  `season_name` varchar(60) DEFAULT '' COMMENT '季节名称',
  `brand_code` varchar(100) DEFAULT '' COMMENT '品牌代码',
  `brand_name` varchar(100) DEFAULT '' COMMENT '品牌名称',
  `is_snmgmt` tinyint(3) DEFAULT '0' COMMENT '是否需要串号管理 0否',
  `product_date` datetime NOT NULL COMMENT '生产日期 ',
  `expire_date` datetime NOT NULL COMMENT '过期日期 ',
  `is_shelf_life_mgmt` tinyint(3) DEFAULT '0' COMMENT '是否需要保质期管理 0否',
  `shelf_life` int(11) DEFAULT '0' COMMENT '保质期 (小时)',
  `reject_lifecycle` int(11) DEFAULT '0' COMMENT '保质期禁收天数',
  `lockup_lifecycle` int(11) DEFAULT '0' COMMENT '保质期禁售天数',
  `advent_lifecycle` int(11) DEFAULT '0' COMMENT '保质期临期预警天数',
  `batch_code` varchar(100) DEFAULT '' COMMENT '批次代码',
  `batch_remark` varchar(255) DEFAULT '' COMMENT '批次备注',
  `pack_code` varchar(255) DEFAULT '' COMMENT '包装代码',
  `pcs` int(11) DEFAULT '0' COMMENT '箱规',
  `origin_address` varchar(255) DEFAULT '' COMMENT '商品的原产地',
  `approval_number` varchar(255) DEFAULT '' COMMENT '批准文号',
  `is_fragile` tinyint(3) DEFAULT '0' COMMENT '是否易碎品  0否',
  `is_hazardous` tinyint(3) DEFAULT '0' COMMENT '是否危险品  0否',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间 ',
  `update_time` datetime NOT NULL COMMENT '更新时间 ',
  `is_valid` tinyint(3) DEFAULT '1' COMMENT '是否有效  0否',
  `is_sku` tinyint(3) DEFAULT '1' COMMENT '是否sku bool  0否',
  `package_material` varchar(255) DEFAULT '' COMMENT '商品包装材料类型',
  `extend_props` varchar(255) DEFAULT '' COMMENT '扩展属性',
  `down_time` datetime DEFAULT NULL COMMENT '下载时间',
  `sys_updated_time` datetime  NULL COMMENT '系统更新时间',
  `is_updated` tinyint(3) DEFAULT '0' COMMENT '是否更新  0否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='erp商品档案表';

",
);