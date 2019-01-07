<?php echo get_js("member.js,goods.js,tab.js") ?>
<?php echo get_js('ui-1.8.18.min.js, tab.js,common.js,baison.js'); ?>
<?php echo get_webpub('style/css/print.css');?>
<?php echo get_webpub('style/css/blitzer/ui-1.8.18.css');?>
<div id="list">
	<?php render_control('title',"",array("title_button"=>array()));?>

	<?php render_control('table', "shop_print_data_type", array(
	"sql" => $response['sql'],
	"template" => "common/danju_print/conf/shop_by_print_data_type",
	"first_click" => array(
		"class" => "first_click",
		"url" => "?app_act=common/danju_print/get_list_by_type_and_shop_code"
	)
));?>
</div>

<div id="load_dialog"></div>

<script type="text/javascript">

	var danju_template = {
		shop_setDefaultPaper:function(shop_print_id){

			$('#load_dialog').dialog({
				modal:true,
				resizable:false,width:350,height:210,autoOpen:false,
				title:"设置纸张",buttons:{
					"取消":function(){
						$("#load_dialog").dialog("close");
					},
					"保存":function(){
						var url = '?app_act=common/danju_print/do_set_shop_page_style&app_page=null&app_fmt=json&'+$('#updatePaper').serialize();
						$.post(url,function(data){
							var ret = $.parseJSON(data);
							if(1 != ret.status) {
								alert(ret.message);
								return false;
							}
							alert("设置成功!");
							refresh();
//	                        $("#load_dialog").dialog("close");
						});
					}
				}
			});
			var url = "?app_act=common/danju_print/set_shop_page_style&app_page=null&shop_print_id=" + shop_print_id;
			$('#load_dialog').load(url).dialog("open");
		},
		set_shop_default_print_data_type:function(print_data_type, shop_print_id, shop_code) {

			var params = {};
			params.print_data_type = print_data_type;
			params.shop_print_id = shop_print_id;
			params.shop_code = shop_code;
			var url = '?app_act=common/danju_print/set_shop_default&app_page=null&app_fmt=json';
			$.post(url, params, function(data){
				var ret = $.parseJSON(data);
				alert(ret.message);
				refresh();
			})
		},

		set_shop_enable:function(shop_print_id) {
			var params = {};
			params.shop_print_id = shop_print_id;
			var url = '?app_act=common/danju_print/set_shop_enable&app_page=null&app_fmt=json';
			$.post(url, params, function(data){
				var ret = $.parseJSON(data);
				alert(ret.message);
				refresh();
			})
		},

		set_shop_disable:function(shop_print_id) {
			var params = {};
			params.shop_print_id = shop_print_id;
			var url = '?app_act=common/danju_print/set_shop_disable&app_page=null&app_fmt=json';
			$.post(url, params, function(data){
				var ret = $.parseJSON(data);
				alert(ret.message);
				refresh();
			})
		},

		//同步主模板
		sync_main:function(shop_print_id) {
			var params = {};
			params.shop_print_id = shop_print_id;
			var url = '?app_act=common/danju_print/sync_main&app_page=null&app_fmt=json';
			$.post(url, params, function(data){
				var ret = $.parseJSON(data);
				alert(ret.message);
				refresh();
			})
		}
	}

	var t_dlg_copy_shop_print;
	function dlg_copy_shop_print(shop_print_id) {
		t_dlg_copy_shop_print = new opendiv();

		t_dlg_copy_shop_print.init({
			"id":"copy_shop_print",
			"action":"url",
			"html":cutoverUrl('return_html_dlg_copy_shop_print')+'&shop_print_id='+shop_print_id,
			"width":520,
			"height":200,
			"async":false,
			"callback":function(){

			}
		})
	}

	/**
	 * 执行复制
	 * @param goods_id
	 */
	function do_copy_shop_print(shop_print_id) {

		var params = {};
		params.shop_print_id = shop_print_id;

		var t_remark = $('#t_remark').val();
		params.remark = t_remark;

		var url = cutoverUrl('do_copy_shop_print');

		$.post(url, params, function(data){

			var ret = $.parseJSON(data);
			alert(ret.message);

			if (1 == ret.status) {
				t_dlg_copy_shop_print.close();
			}
		});
	}


	var _shop_print_id = null;
    /**
     * 粘贴
     * @param shop_print_id
     */
	function select_paste_shop_print(shop_print_id,danju_print_code) {
	    _shop_print_id = shop_print_id;
	    var url = 'shop_print_copy_record';
	    url += '&danju_print_code='+danju_print_code;

	    var bind_id = 'select_shop_print_copy_record';
	  //  bind_select(id,url,shop_print_paste_callback);
	    var callback = 'do_shop_print_paste';

	    if(typeof callback != "function" || callback == null)
		    callback = null;

	    var parameter = new Object();
	   // parameter['url_parameter'] = get_url_parameter();

	    var listurl = "?app_act=common/select/"+url+"&id="+bind_id;
	    var open_select = new opendiv();
	    open_select.init({
		    "id":"open_select"+bind_id,
		    "action":"url",
		    "html":listurl,
		    "width":800,
		    "height":500,
		    "parameter":parameter,
		    "async":false,
		    "callback":function(){
			    select.prototype = new page();//继承page
			    select.prototype.mode = "ajax";

			    var open_select_list = new select();

			    open_select_list.init("select_page_table"+bind_id,listurl,open_select_list.select_bind);
			    open_select_list.select_init("open_select"+bind_id,do_shop_print_paste);
			    if(typeof parameter['tpl'] != "undefined")
				    open_select_list.parameter['tpl'] = parameter['tpl'];

			    var obj = new Object();
			    obj['open_select'] = open_select;
			    obj['open_select_list'] = open_select_list;
			    if(typeof selectHashtable == "undefined")
				    selectHashtable = new Hashtable();
			    selectHashtable.add(bind_id,obj);

			    //判断是否是树形结构
			    jQuery("#"+"open_select"+bind_id).find("#tree_search").click(function(){
				    open_select_list.parameter['tree'] = get_all_tree_value();
				    open_select_list.search();
			    })

			    jQuery("#"+"open_select"+bind_id).find("#tree_search_clear").click(function(){
				    jQuery("#"+"open_select"+bind_id+" .sq_tree").find("input[type='checkbox']").attr("checked",false);
				    delete open_select_list.parameter['tree'];
				    open_select_list.search();
			    })
		    }
	    })
	}

    /**
     * 执行粘贴
     * @param value
     * @returns {boolean}
     */
	function do_shop_print_paste(value) {

		if (!confirm('确定操作')){
			return false;
		}

		var copy_id = value[0].find(".copy_id").text();

	    var shop_print_id = _shop_print_id;

	    var params = {};
	    params.copy_id = copy_id;
	    params.shop_print_id = shop_print_id;

	    var url = '?app_act=common/danju_print/do_shop_print_paste';

	    $.post(url, params, function(data) {
		    var ret = $.parseJSON(data);
		    if (1 == ret.status) {
			    alert(ret.message);
		    }
	    });
	}
</script>

<OBJECT ID="LODOP" CLASSID="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" WIDTH=0  HEIGHT=0>
	<param name="Caption"  value="">
	<param name="Border"   value="1">
	<param name="CompanyName" value="上海百胜软件">
	<param name="License" value="452547275711905623562384719084">
	<?php $project = $GLOBALS['context']->config['project'];?>
	<embed id="LODOP_EM" TYPE="application/x-print-lodop" width="0" height="0" PLUGINSPAGE="/<?php echo $project?>webpub/js/print/lodop/install_lodop.exe">
</OBJECT>

<?php echo get_js('print/lodop/LodopFuncs.js'); ?>
<script type="text/javascript">
	var LODOP;
	var modSettingPrinter = {
		shop_selectPrinter:function(shop_print_id) {
			LODOP = getLodop(document.getElementById('LODOP'),document.getElementById('LODOP_EM'));

			var printer_count = LODOP.GET_PRINTER_COUNT();
			if (printer_count<1) {
				alert('该系统未安装打印设备,请添加相应的打印设备');
				return;
			}
			//选择打印机
			var p = LODOP.SELECT_PRINTER();
			if (p<0) {
				return;
			}
			//获取打印机名称
			var printer_name = LODOP.GET_PRINTER_NAME(p);
			var params = {shop_print_id:shop_print_id,printer_name:printer_name};
			//  loadingDialog('提示', '提交中...');
			$.post('?app_act=common/danju_print/shop_select_printer&app_fmt=json', params, function(data) {
				//     hideLoadingdialog();
				var ret=$.parseJSON(data);
				if (ret.status == 1) {
					$("#td_printer_name_"+shop_print_id).html(printer_name);
				} else {
					alert(ret.message);
				}
				refresh();
			});
		}
	}
</script>