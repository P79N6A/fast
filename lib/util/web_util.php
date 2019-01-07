<?php
function load_js($str,$myself=false) {
	$js_path_arr = explode(',', $str);
	$s = '';
	$pre_path = FT_COMMON_DIR;
	$pre_http_path = CTX()->get_app_conf('common_http_url');
	foreach ($js_path_arr as $path) {
	    if($myself){
	        $url = 'assets/js/'.$path;
	    }else{
	        $url = $pre_http_path.'js/'.$path;
	    }
		
		$s .= '<script type="text/javascript" src="'.$url.'"></script>'."\n";
	}
	
	return $s;
}

function load_css($str,$myself=false) {
    $pre_http_path = CTX()->get_app_conf('common_http_url');
	$css_path_arr = explode(',', $str);
	$s = '';
	foreach ($css_path_arr as $path) {
                if($myself){
                    $url = 'assets/css/'.$path;
                }else{
                    $url = $pre_http_path.'css/'.$path;
                }
                $s .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />'."\n";
	}
	
	return $s;
}

function load_resource($str) {
	static $pre_http_path = NULL;
	if ($pre_http_path == NULL) {
		$pre_http_path = CTX()->get_app_conf('common_http_url');
	}
	$s = $pre_http_path.$str;
	
	return $s;
}

function json_response($status, $data='', $message='') {
	if (is_array($status)) {
		echo json_encode();
	} else{
		echo json_encode(array('status'=>$status, 'data'=>$data, 'message'=>$message));
	}
}
/**
 * 以json格式输出并终止当前
 * 结构形如：array('status'=>$status, 'data'=>$data, 'message'=>$message)
 * @param mixed $status 状态码或者消息数组。<b>如果为数组则后面的参数无效</b>
 * @param mixed $data	
 * @param string $message 消息描述
 */
function exit_json_response($status, $data='', $message='') {
	if (is_array($status)) {
		echo json_encode($status);
	} else{
		echo json_encode(array('status'=>$status, 'data'=>$data, 'message'=>$message));
	}
	exit;
}

function ctl_format_field($field, $field_value, $row) {
	$value = $field_value;
	if (!isset($field['format']['type'])) return $value;
	
	$format_type = $field['format']['type'];
	// 格式化数据
    if ($format_type == 'date' || $format_type == 'time') {
        $def = array('date'=>'Y-m-d', 'time'=>'Y-m-d H:i:s');
        $fmt_value = isset($field['format']['value']) ? $field['format']['value'] : $def[$format_type];
        if (!empty($value)) {
            if (is_string($value)) {
            	$field_value = strtotime($field_value);
            }
            $value = date($fmt_value, $field_value);
        }
	
	} else if ($format_type == 'map')		{
		if (!array_key_exists($value, $field['format']['value'])) {
			$value = '';
		} else {
			$value = $field['format']['value'][$value];
		}
		
	} else if ($format_type == 'map_checked')		{
		if ($value == 1) {
			$value = '<img src="'.get_theme_url('images/ok.png').'" />';
		} else {
			$value = '<img src="'.get_theme_url('images/no.gif').'" />';
		}
		
	} else if ($format_type == 'function') {
		$params = array($field_value, $row);
		if (isset($field['format']['params'])) {
			$params = array_merge($params, $field['format']['params']);
		}
		$ret = explode('::', $field['format']['value']);
		if (count($ret) > 1) {
		    $class_file = $ret[0];
		    $method = $ret[1];
		    require_model($class_file);
		    $pos = strrpos($class_file, '/');
		    if ($pos === false) {
		        $class = $class_file;
		    } else {
		        $class = substr($class_file, $pos+1);
		    }
		    
		    $obj = new $class();
			$func = array($obj, $method);
		} else {
		    $func = $ret[0];
		}
		$value = call_user_func_array($func, $params);
	}
	
	return $value;
}

function create_page_select($page, $count) {
	if (empty($page))
	{
		$page = 1;
	}

	if (!empty($count))
	{
		$str = "<option value='1'>1</option>";
		$min = min($count - 1, $page + 3);
		for ($i = $page - 3 ; $i <= $min ; $i++)
		{
			if ($i < 2)
			{
				continue;
			}
			$str .= "<option value='$i'";
			$str .= $page == $i ? " selected='true'" : '';
			$str .= ">$i</option>";
		}
		if ($count > 1)
		{
			$str .= "<option value='$count'";
			$str .= $page == $count ? " selected='true'" : '';
			$str .= ">$count</option>";
		}
	}
	else
	{
		$str = '';
	}

	return $str;
}


function echo_hint_when_close($msg='你确认要关闭此页面吗？') {
	echo 
'function onBeforeTabClose() {	// return false; 阻止关闭tab页
	if (typeof cb_isModified == "function") {
		if (!cb_isModified()) {
			window._u_hint = false;
			return true;
		}
	}
	if (confirm("'.$msg.'")) {
		window._u_hint = false;
		return true;
	}
	
	return false;
}
window.onbeforeunload = function() {
	if (typeof cb_isModified == "function") {
		if (!cb_isModified()) {
			return ;
		}
	}
	if (window._u_hint !== false)
		return "'.$msg.'";
}';	
}

function echo_table_fields_js($table_id, $conf, $set_name='', $win_title='字段选择') {
	$divid = $table_id.'_show_table_fields';
	if (empty($set_name)) {
		$set_name = substr($conf, strrpos($conf, '/')+1).'_field_set';
	}
	echo 
'
function '.$table_id.'_showFields() {
	openDiv.init("url", 
			"'.get_app_url("common/datatable/show_fields", array("table"=>$conf, "set_name"=>$set_name)).'", 
			"'.$divid.'", 
			250, 400, 
			"'.$win_title.'", 
			{onSubmit: "'.$table_id.'_showFields_onSubmit"});
}
function '.$table_id.'_showFields_onSubmit(data) {
	openDiv.close("'.$divid.'");
	'.$table_id.'.loadList();
}
';
}

function create_datatable2($conf, $dataset, $opts) {
	//$query_url, $delete_url, $ajaxlist_id, $params

	$config = include ROOT_PATH.'webwms/conf/table/'.$conf.'.conf.php';

	$rs = $dataset['data'];
	
	$filter = $dataset['filter'];

	$ctl_resultset = $rs;
	$ctl_list_fields = $config['list'];
	$ctl_list_key = $config['opts']['key'];
	$ctl_filter = $filter;
	$ctl_query_url = $opts['query_url'];
	$ctl_delete_url = $opts['delete_url'];
	$ctl_ajaxlist = empty($opts['ajaxlist']) ? 'ajaxlist' : $opts['ajaxlist'];
	$ctl_pager = $opts['pager'];
	$ctl_script = $opts['script'];
	
	ob_start();
	include get_tpl_path('common/table');
	
	$s = ob_get_contents();
	ob_end_clean();
	
	return array('html'=>$s);
}

/**
 * 根据二位数组结构的数据源生成一个下拉列表
 * @param unknown $data
 * @param unknown $id
 * @param string $default
 * @param string $pre 前置数据
 * @param unknown $opts
 * @return string
 */
function html_select($data, $id, $default=NULL, $pre=FALSE, $opts=array()) {
	if (!isset($opts['attr'])) {
		$opts['attr'] = '';
	}
	$s = '<select id="'.$id.'" class="field" '.$opts['attr'].'>';
	if ($pre == 1) {
	    $s .= '<option value="" >请选择</option>';
	}
	foreach ($data as $opt) {
		$row = array_values($opt);
		if ($default == $row[0]) {
			$s .= '<option value="'.$row[0].'" selected="selected">'.$row[1].'</option>';
		} else {
			$s .= '<option value="'.$row[0].'" >'.$row[1].'</option>';
		}
	}
	$s .= '</select>';
	return $s;
}

/**
 * 根据二位数组结构的数据源生成一系列checkbox
 * $data形如：
 * <pre>
 * array(
 * 		array('1', '上海'),
 * 		array('2', '北京'),
 *  	...
 * )
 * </pre>
 * @param unknown_type $data 
 * @param unknown_type $id
 * @param unknown_type $default (string or array)默认值，可以多个。如果"3,4,5"或array(3,4,5)
 */
function html_checkbox_list($data, $id, $default=NULL) {
	$s = '';
	$i = 0;
	if (is_array($default)) {
		$default_arr = $default;
	} else if (is_string($default)) {
		$arr = explode(",", $default);
		$default_arr = array();
		foreach ($arr as $v) {
			$default_arr[] = trim($v);
		}
	} else {
		$default_arr = array($default);
	}
	foreach ($data as $opt) {
		$row = array_values($opt);
		$cid = 'cb_'.$id.'_'.$i++;
		if (in_array($row[0], $default_arr)) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
		$s .= '<span class="checkbox-line"><input type="checkbox" name="'.$id.'" id="'.$cid.'" class="field checkbox" value="'.$row[0].'" '.$checked.'/><label for="'.$cid.'">'.$row[1]."</label></span>\r\n";
	}
	return $s;
}

/**
 * 
 * 根据二位数组结构的数据源生成一系列radio
 * @param unknown_type $data
 * @param unknown_type $id
 * @param unknown_type $default 默认值
 * @see html_checkbox_list
 */
function html_radio_list($data, $id, $default=NULL) {
	$s = '';
	$i = 0;
	foreach ($data as $opt) {
		$row = array_values($opt);
		$cid = 'rd_'.$id.'_'.$i++;
		if ($default == $row[0]) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}
		$s .= '<span class="radio-line"><input type="radio" name="'.$id.'" id="'.$cid.'" class="field radio" value="'.$row[0].'" '.$checked.'/><label for="'.$cid.'">'.$row[1]."</label></span>\r\n";
	}
	return $s;
}


function str_truncate($value, $len) {
	$strlen = mb_strlen($value);
	$value = substr_utf8($value, 0, $len);
	if ($len < $strlen/2) $value .= '...';
	
	return $value;
}


/**
 * 输出使用打印控件所需的html和js
 * @param int $need_object 比如面单设计器，不需要这里的obj，自己页面单独配置，否则有莫名的问题
 * @param int $installStyle 1: 页面提示安装 2: 弹出BUI提示框
 */
function echo_print_plugin($need_object = 1, $installStyle = 1) {

	if ($need_object) {//clsid:2105C259-1E0C-4534-8141-A753534CB4CA
		echo'
		<OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width=0  height=0>
		  <param  name="Caption"  value="">
		  <param  name="Border"   value="1">
		  <param name="CompanyName" value="上海百胜软件">
		  <param name="License" value="452547275711905623562384719084">
		  <embed id="LODOP_EM" TYPE="application/x-print-lodop" width="0" height="0" PLUGINSPAGE="">
		</OBJECT>';
	}

	echo '<script type="text/javascript">
		var http_host = "'.$_SERVER['HTTP_HOST'].'";
		var lodop32_url = "'.load_resource('js/print/lodop/install_lodop32.exe').'";
		var lodop64_url = "'.load_resource('js/print/lodop/install_lodop64.exe').'";
	</script>';

	echo '<script type="text/javascript">
	function getLodop() {
		var _LODOP = document.getElementById("LODOP");//这行语句是为了符合DTD规范
		var _oEMBED = document.getElementById("LODOP_EM");//这行语句是为了符合DTD规范
		var isIE	 = (navigator.userAgent.indexOf("MSIE")>=0) || (navigator.userAgent.indexOf("Trident")>=0);
	    var is64IE  = isIE && (navigator.userAgent.indexOf("x64")>=0);
	    var isChrome = navigator.userAgent.indexOf("Chrome");
	    var isFirefox = navigator.userAgent.indexOf("Firefox");
        //var LODOP=_oEMBED;
		var LODOP = null;

		if (isIE)
            LODOP=_LODOP;
        else
            LODOP=_oEMBED;

		if ((LODOP==null)||(typeof(LODOP.VERSION)=="undefined")) {
             if (is64IE)
                 lodop_download(lodop64_url);
             else
                 lodop_download(lodop32_url);

             return null;
        } else if (LODOP.VERSION < "6.1.9.6") {
             if (is64IE)
                 lodop_update(lodop64_url);
             else
                 lodop_update(lodop32_url);

            return null;
        }

		LODOP.SET_LICENSES("上海百胜软件","452547275711905623562384719084","","");

	    return LODOP;
	}
    ';

    if($installStyle == 1) {
        echo '
        function lodop_download(p) {
            alert("错误: 打印控件未安装. 点击确定开始安装? (安装完成后, 请重新启动浏览器.)")
            window.location = p;
        }
        function lodop_update(p) {
            alert("错误: 打印控件需要升级. 点击确定开始升级? (升级完成后, 请重新启动浏览器.)")
            window.location = p;
        }
        ';
    }

    if($installStyle == 2) {
        echo '
        function lodop_download(p) {
            BUI.use("bui/overlay",function(Overlay){
                var dialog = new Overlay.Dialog({
                    title:"安装打印控件",
                    width:500,
                    height:120,
                    bodyContent:"<font color=\"#FF00FF\">打印控件未安装!点击这里<a href=\""+p+"\" target=\"_self\">执行安装</a>,安装后请刷新页面或重新进入。</font>",
                    success:function () {
                        this.close();
                    }
                });
                dialog.show();
            });
        }

        function lodop_update(p) {
            BUI.use("bui/overlay",function(Overlay){
                var dialog = new Overlay.Dialog({
                    title:"升级打印控件",
                    width:500,
                    height:120,
                    bodyContent:"<font color=\"#FF00FF\">打印控件需要升级!点击这里<a href=\""+p+"\" target=\"_self\">执行升级</a>,升级后请刷新页面或重新进入。</font>",
                    success:function () {
                        this.close();
                    }
                });
                dialog.show();
            });
        }
        ';
    }

    echo '</script>';
}

/**
 * 辅助输出表单提交JS，形如params.code = $('#code').val();
 * @param unknown_type $fields
 * @param unknown_type $params_name
 * @param unknown_type $include_list
 */
function form_submit_js($fields, $params_name='params', $include_list=array('input', 'select', 'checkbox', 'input_select', 'date', 'year', 'time', 'textarea')) {
	$return = '';
	foreach ($fields as $item) {
		if (!in_array($item['type'], $include_list)) continue;
		if ($item['type'] == 'checkbox') {
			$return .= "{$params_name}.{$item['field']} = $('#{$item['field']}').attr('checked') ? '1' : '0';\r\n";
		} else {
			$return .= "{$params_name}.{$item['field']} = $('#{$item['field']}').val();\r\n";
		}
	}
	
	return $return;
}

/**
 * 获取打开新页面的js
 * @param unknown_type $url
 */
function js_new_window($url) {
	return "javascript:window.open('{$url}', 'newwindow', 'toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no');void(0);";
}

/**
 * 获取打开tab的js
 * @param unknown_type $url
 */
function js_new_tab($url, $title=1) {
	return "javascript:ui_addTab('{$title}', '{$url}');void(0);";
}

function echo_selectwindow_js($request, $table, $opts) {
    extract($opts);
    ?>
	<script type="text/javascript">
            //判断浏览器类型
            var mb=myBrowser();
	<?php echo $table;?>Grid.on('itemdblclick',function(ev){
		var data = [<?php echo $table;?>Grid.getHighlighted()];
		<?php if ($request['callback']):?>
		if(window.frames.length+1 != parent.frames.length){
			<?php echo "getTopFrameWindowByName('{$request['ES_pFrmId']}').{$request['callback']}(data, '{$id}', '{$code}', '{$name}')";?>
        }else{
        	<?php echo  "parent.{$request['callback']}(data, '{$id}', '{$code}', '{$name}')";?>
        }
        <?php endif;?>
	});
	function ES_getSelection() {
		var data = <?php echo $table;?>Grid.getSelection();
        <?php if ($request['callback']):?>
		if(window.frames.length+1 != parent.frames.length){
			<?php echo "getTopFrameWindowByName('{$request['ES_pFrmId']}').{$request['callback']}(data, '{$id}', '{$code}', '{$name}')";?>
        }else{
        	<?php echo  "parent.{$request['callback']}(data, '{$id}', '{$code}', '{$name}')";?>
        }
        <?php endif;?>
	}
	</script><?php
}


/**
 * PHP发送异步请求
 * @author zuo <zuojianghua@guanyisoft.com>
 * @date 2012-12-19
 * @param string $url 请求地址
 * @param array $param 请求参数
 * @param string $httpMethod 请求方法GET或者POST
 * @return boolean
 * @link http://www.thinkphp.cn/code/71.html
 */
function makeRequest($url, $param, $httpMethod = 'GET') {
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    if ($httpMethod == 'GET') {
        curl_setopt($oCurl, CURLOPT_URL, $url . "?" . http_build_query($param));
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    } else {
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($param));
    }
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return FALSE;
    }
}
function set_uplaod( & $request,  & $response,  & $app){
    $app['page'] = 'NULL';
    $app['tpl'] = 'web_page_upload';
    $app['fmt'] = 'html';
    
}
function get_excel_url($execl_name,$type=1,$down_name=''){
    $token = create_user_token($execl_name);
    $url = "?app_act=sys/file/get_file&type={$type}&name=".$execl_name."&token=".$token;
    if(!empty($down_name)){
        $url.="&down_name=".urlencode($down_name);
    }
    
    return $url;
}