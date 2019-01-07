<?php

$u['001'] = array(
    "ALTER TABLE oms_sell_record_inspect MODIFY COLUMN `type_val` varchar(50) NOT NULL DEFAULT '' COMMENT '类型值'",
    "ALTER TABLE oms_sell_record_inspect MODIFY COLUMN `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商店代码';"
);

$u['749'] = array(
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('320511000000', '4', '沧浪区', '320500000000', '', '2016-10-09 11:22:06', '', '');",
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('320512000000', '4', '高新区', '320500000000', '', '2016-10-09 11:22:06', '', '');",
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('320119000000', '4', '其他区', '320100000000', '', '2016-11-03 10:19:06', '', '');",
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('320586000000', '4', '其他区', '320500000000', '', '2016-11-03 10:19:06', '', '');",
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('320283000000', '4', '新区', '320200000000', '', '2016-11-03 10:19:06', '', '');"
); 