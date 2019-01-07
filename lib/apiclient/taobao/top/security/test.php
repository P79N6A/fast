<?php

include './MagicCrypt.php';
$input = '13482155297';
$date = date('Y-m-d');
$key = md5($date);
$sStr = Security::encrypt($input, $key);
echo "$sStr";
echo "<br/><br/>";
echo Security::decrypt($sStr, $key);

