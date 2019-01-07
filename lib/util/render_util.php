<?php


/**
 * 得到js列表对应的script声明字符串。
 * @param string $jslist	js列表，可用,分隔来加载多个js。
 * @param bool $mutil_lang	js是否是多语言版本，默认为true，如果$mutil_lang为true，且RUN_MLANG_VIEW为true，激活多语言选项。
 * @return string 返回js列表对应的script声明字符串
 */
function get_theme_js($jslist,$mutil_lang=true){
	if (! $jslist)	return '';
	$s='';
	foreach (explode(',',$jslist) as $js){
		$u=get_theme_url('js/'.trim($js),$mutil_lang);
		$s .= "<script type='text/javascript' src='{$u}'></script>\n";
	}
	return $s;
}
/**
 * 输出js列表对应的script声明字符串。
 * @param string $jslist js列表，可用,分隔来加载多个js。
 * @param bool $mutil_lang	js是否是多语言版本，默认为true，如果$mutil_lang为true，且RUN_MLANG_VIEW为true，激活多语言选项。
 */
function echo_theme_js($jslist,$mutil_lang=true){
	echo get_theme_js($jslist,$mutil_lang);
}

/**
 * 得到css列表对应的link声明字符串。
 * @param string $csslist	css列表，可用,分隔来加载多个css。
 * @param bool $mutil_lang	css是否是多语言版本，默认为true，如果$mutil_lang为true，且RUN_MLANG_VIEW为true，激活多语言选项。
 * @return string 返回css列表对应的script声明字符串
 */
function get_theme_css($csslist,$mutil_lang=true){
	if (! $csslist)	return '';
	$l='';
	foreach (explode(',',$csslist) as $css){
		$u=get_theme_url('css/'.trim($css),$mutil_lang);
		$l .= "<link rel='stylesheet' type='text/css' href='{$u}'></link>\n";
	}
	return $l;
}
/**
 * 输出css列表对应的link声明字符串。
 * @param string $jslist css列表，可用,分隔来加载多个css。
 * @param bool $mutil_lang css是否是多语言版本，默认为true，如果$mutil_lang为true，且RUN_MLANG_VIEW为true，激活多语言选项。
 */
function echo_theme_css($csslist,$mutil_lang=true){
	echo get_theme_css($csslist,$mutil_lang);
}

/**
 * 得到image列表对应的src字符串。
 * @param string $imagelist image列表，可用,分隔来加载多个图片。
 * @return string 返回$imagelist列表对应的src字符串
 */
function get_theme_image($imagelist) {
	if (!$imagelist) return '';
	$l = '';
	foreach (explode(',', $imagelist) as $image) {
		$u = get_theme_url('images/' . trim($image));
		$l .= $u;
	}
	return $l;
}
/**
 * 输出image列表对应的src字符串。
 * @param string $imagelist image列表，可用,分隔来加载多个图片。
 */
function echo_theme_image($imagelist){
	echo get_theme_image($imagelist);
}


/**将数组$data渲染为html的select组件option字符串，其中data的key为option的value，data的value为option的caption。
 * @param array $data			数据数组。
 * @param string $sel_key		select框开始选择的key，如果没有设置或不在 var之中，不选择。
 * @param string $null_caption	设置未选择的caption，如果没有设置，表示不需要未选择项，如果设置其对应的value为null，且为第一项，未选择的caption如：&lt;请选择&gt;。
 */
function get_select_option(array $data, $sel_key = NULL, $null_caption = NULL) {
	if (! $data)	return '';
	$o = '';
	if ($null_caption) {
		$o .= "<option value='null'";
		if ($sel_key && $sel_key == 'null')	$o .= " selected='selected' ";
		$o .= ">{$null_caption}</option>";
	}
	foreach ( $data as $key => $val ) {
		if ( ($key !==0 && empty ( $key )) || ( $val !==0 && empty ( $val )) )	continue;
		$o .= "<option value='{$key}' ";
		if ($sel_key && $sel_key == $key)	$o .= " selected='selected' ";
		$o .= " >{$val}</option> ";
	}
	return $o;
}