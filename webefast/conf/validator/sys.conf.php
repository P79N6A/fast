<?php
return array(
	'user_edit'=>array(
			array('user_code', 'require'),
			array('user_code', 'minlength', 'value'=>5),
			array('date', 'minDate', 'value'=>date('Y-m-d')),
		),

);