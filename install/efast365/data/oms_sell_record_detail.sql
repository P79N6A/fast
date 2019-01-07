ALTER TABLE `oms_sell_record_detail` DROP KEY `idxu_key`;
ALTER TABLE `oms_sell_record_detail` ADD UNIQUE`idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_delete`,`is_gift`);