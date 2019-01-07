<?php
require_lib ( 'util/web_util', true );
     $data = array('deal_code'=>'2341243','sale_channel_code'=>'taobao');
            $d = array($data);
              load_model('common/TBlLogModel')->set_log_multi($d ,'search');
              echo 11;die;
class Tabtest {

	function do_index(array & $request, array & $response, array & $app) {
       echo 11;die;
         
            
            
            
		$user_model = load_model('common/UserModel');
		$response['top_menu'] = $user_model->get_top_menu();
		$response['menu_tree'] = $user_model->get_menu_tree();
	
	}
	
	function tabtest() {
		//if($_COOKIE['aa']!="hjk"){
		//	$_COOKIE['aa']="hjk";
    render_control('TabPage', 'tabl1', array(
            'list' => array (
						array('name'=>'sina', 	'show'=>1, 'type'=>'1',    	'contain'=>'开始进入时不能异步调用文件', ),
						array('name'=>'baidu', 	'show'=>2, 'type'=>'1',  'contain'=>'这是同步调用文件', 	),
						array('name'=>'sohu', 	'show'=>3, 'type'=>'2', 	 'contain'=>'../../test.html', ),
						array('name'=>'中华网', 	'show'=>4, 'type'=>'2', 'contain'=>'../../text.php', 	),
	           ),	
						'dataset' => 'sys/RoleModel::get_by_page',
						'queryBy' => 'searchForm'
			
			));

		//}
 
      exit;
	}
}