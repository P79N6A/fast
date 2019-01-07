DROP TABLE IF EXISTS `api_taobao_itemcats`;
CREATE TABLE `api_taobao_itemcats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` varchar(50) NOT NULL COMMENT '��Ʒ������ĿID',
  `parent_cid` varchar(50) NOT NULL DEFAULT '0' COMMENT '����ĿID=0ʱ���������һ������Ŀ',
  `name` varchar(255) NOT NULL COMMENT '��Ŀ����',
  `is_parent` tinyint(1) NOT NULL COMMENT '����Ŀ�Ƿ�Ϊ����Ŀ(��������Ŀ�Ƿ�������Ŀ)',
  `status` varchar(20) DEFAULT NULL COMMENT '״̬����ѡֵ:normal(����),deleted(ɾ��)',
  `sort_order` tinyint(11) DEFAULT NULL COMMENT '������ţ���ʾͬ����Ŀ��չ�ִ�������ֵ��������ƴ������С�ȡֵ��Χ:�����������',
  `taosir_cat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '�Ƿ��������Ŀ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cid` (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
