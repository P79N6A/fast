<?php

$api_conf = array('api'=>array(), 'alias'=>array());
$conf_list = array('market','clients','products');
foreach ($conf_list as $name) {
	$ret = require_conf('serverapi/'.$name);
	if (empty($ret)) {
		continue;
	}
	$api_conf['api'] = array_merge($api_conf['api'], $ret['api']);
	$api_conf['alias'] = array_merge($api_conf['alias'], $ret['alias']);
}

return $api_conf;