
DROP TABLE IF EXISTS `api_bs3000j_barcode`;
CREATE TABLE `api_bs3000j_barcode` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
        `SPTM` varchar(100) DEFAULT NULL COMMENT '商品条码',
        `SPDM` varchar(100) DEFAULT NULL COMMENT '商品代码',
        `BYZD1` varchar(255) DEFAULT NULL COMMENT 'SPGG1表Byzd1',
        `GG1DM` varchar(50) DEFAULT NULL COMMENT '颜色代码',
        `GG2DM` varchar(50) DEFAULT NULL COMMENT '尺码代码',
        `update_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未更新，1已更新，2异常',
        `uptime` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `SPTM` (`SPTM`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='bs3000+条形码更新';
