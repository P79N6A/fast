<?php
$u = array();
$u['FSF-1946'] = array(
"INSERT INTO `base_question_label`
		(`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `remark`, `lastchanged`)
		 VALUES('EXPRESS_CHECK','快递地址不可达到',0,1,'通过快递公司接口匹配发货地址是否可达，需要配置快递公司参数，目前支持韵达',now());",
        "ALTER TABLE `base_express_company`
ADD COLUMN `api_content`  text NULL AFTER `remark`;
",
    );