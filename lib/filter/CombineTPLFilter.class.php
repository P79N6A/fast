<?php
require_once ROOT_PATH.'boot/req_inc.php';

class CombineTPLFilter implements IReponseFilter{
	public  $force=false;
	private $page_file;
	private $tpl_list=array();
	private $tpl_include_file;
	private $recount=5;	
	private $need_page=false;
	function handle_after(array & $request,array & $response,array & $app){
		if($app ['fmt'] !== 'html' && $app['err_no']!==0) return; 
		if(isset($app['page']) && strcasecmp($app['page'],'NULL')!==0) return; //fix web page;
		require_once ROOT_PATH.'lib/filter/HtmlRenderer.class.php';
		
		//init variable
		$ctx = $GLOBALS ['context'];
		$cache_dir = ROOT_PATH . $ctx->app_name . DIRECTORY_SEPARATOR .'cache'. DIRECTORY_SEPARATOR . 'views';
		$tpl_dir= ROOT_PATH . $ctx->app_name . DIRECTORY_SEPARATOR . 'views';
		if ($ctx->theme){
			$cache_dir .= DIRECTORY_SEPARATOR . $ctx->theme;
			$tpl_dir .= DIRECTORY_SEPARATOR . $ctx->theme;
		} 
		if( defined('RUN_MLANG_VIEW') && RUN_MLANG_VIEW){
			$cache_dir .= DIRECTORY_SEPARATOR . $ctx->app_lang;
			$tpl_dir .= DIRECTORY_SEPARATOR . $ctx->app_lang;			
		}		
		$this->need_page=! isset($app['page']) || strcasecmp($app['page'],'NULL')!==0;
		
		if(isset($app['tpl']) && $app['tpl']){
			$tpl_name=$app['tpl'];
			if($tpl_name[1]!==':' && $tpl_name[0]!=='/') //not absolute page path
				$tpl_file=$tpl_dir .DIRECTORY_SEPARATOR. "{$tpl_name}.tpl.php";
		} 	
		else{
			$tpl_name= "{$ctx->app_path}{$app['grp']}_{$app['act']}";
			$tpl_file=$tpl_dir .DIRECTORY_SEPARATOR. "{$tpl_name}.tpl.php";
		}
		
		
		
		$cache_file=$cache_dir. DIRECTORY_SEPARATOR .str_replace(array('/','\\'),'_',$tpl_name);
		if($this->need_page) $cache_file .='_page';
		$cache_list=$cache_file.'.dep';
		$cache_file .= '.php';
		
		if(! $this->force && $this->check_cache_valid($tpl_file,$cache_file,$cache_list,$this->need_page)){
			$app['page']='NULL';
			$app['tpl']=$cache_file;
			return ;
		}	
		
		if($this->need_page) {
			if(! $this->page_file){
				if (! isset ( $app ['page'] ))	$this->page_file=get_web_page($tpl_file);
				else{
					$this->page_file =$app ['page'];
					if($this->page_file[1]!==':' && $this->page_file[0]!=='/') //not absolute page path
						$this->page_file = $tpl_dir . DIRECTORY_SEPARATOR . $this->page_file . '.tpl.php';
				}  				
			} 
			$this->tpl_list[]=$this->page_file;
		}
		if(! file_exists($tpl_file)) {
			$ctx->log_error("CombineTPLFilter : not found tpl file {$tpl_file}");
			return;
		}
		$template = file_get_contents($tpl_file);
		if($this->need_page){
			if(file_exists($this->page_file)){
				$this->tpl_include_file=$this->page_file;
				$web_content=file_get_contents($this->page_file);
				$recount=$this->recount;
				$this->parse_tpl($web_content,$recount);
				$template=preg_replace($this->preg_web_page_pattern,$template,$web_content); 
				unset($web_content);
			}else{
				$web_content="<html><head>\n<meta http-equiv='content-Type' content='text/html; charset=UTF-8'/>";	
				$web_content .= '<?php if(isset($app["title"])) echo "<title>{$app[\'title\']}</title>" ?></head><body>';
 				$template = $web_content. $template . '</body></html>';
 				unset($web_content);					
			}
		}
		$this->tpl_include_file=$tpl_file;
		$recount=$this->recount;
		$this->parse_tpl($template,$recount);
	
		if(! file_exists($cache_dir)) mkdir($cache_dir,0777,true);
		file_put_contents($cache_file,$template);
	
		$this->tpl_list=array_unique($this->tpl_list);
		file_put_contents($cache_list,filemtime($cache_file).';'.implode(';',$this->tpl_list));
		$app['page']='NULL';
		$app['tpl']=$cache_file;
	}

	private function check_cache_valid($tpl_file,$cache_file,$cache_list,$need_page){
		if(! file_exists($cache_file) || ! file_exists($cache_list)) return false;
		$list=explode(';',file_get_contents($cache_list));
		$cache_time=array_shift($list);
		if(defined('RUN_WIDGET') && RUN_WIDGET)
			if(Widget::$opt_mdtime>=0 && Widget::$opt_mdtime>$cache_time) return false; //exist Widget and options modify
		
		$tpl_time =filemtime($tpl_file);
		if($tpl_time>$cache_time) return false;
				
		if($need_page){
			$this->page_file=get_web_page($tpl_file);
			$org_page_file =array_shift($list);
			if($this->page_file != $org_page_file) return false;
			$page_time=filemtime($this->page_file);
			if($page_time>$cache_time) return false;
		}
		
		foreach ($list as $item){
			$im=filemtime($item);
			if($im && $im >=$cache_time) return false;
		}
		return true;
	}	
	private $replace=false;
	private function parse_tpl(&$template,&$recount){
		if($recount<0) return;
		--$recount;
		$this->replace=false;
		$template=preg_replace_callback($this->preg_get_tpl_path_pattern, array ($this,'preg_get_tpl_path'),$template);
		$template=preg_replace_callback($this->preg_include_path_pattern,array ($this,'preg_include_path'),$template);
		if(defined('RUN_WIDGET') && RUN_WIDGET)
			$template=preg_replace_callback($this->preg_render_widget_pattern,array ($this,'preg_render_widget'),$template);
		if($this->replace) $this->parse_tpl($template,$recount);
	}	
	private $preg_get_tpl_path_pattern='/<\?php\s+include\s+get_tpl_path\s*\(\s*[\'|\"](.+)[\'|\"]\s*\)\s*;*\s*\?>/';
	private function preg_get_tpl_path($matches){
		$child=get_tpl_path($matches[1]);
		if(! file_exists($child)) return $matches[0];
		$this->tpl_list[]=$child;
		$this->replace=true;
		return file_get_contents($child);
		
	}	
	private $preg_include_path_pattern='/<\?php\s+include\s+[\'|\"](.+)[\'|\"]\s*;*\s*\?>/';
	private function preg_include_path($matches){
		$child=$matches[1];
		if(! file_exists($child)){
			$child=dirname($this->tpl_include_file).DIRECTORY_SEPARATOR.$child;
			if(! file_exists($child)) return $matches[0];
		} 
		$this->tpl_list[]=$child;
		$this->replace=true;
		return file_get_contents($child);
	}	
	private $preg_render_widget_pattern='/<\?php\s+render_widget\s*\(\s*\'(.+)\'\s*\)\s*;*\s*\?>/';
	private function preg_render_widget($matches){ 
		$widget_id=$matches[1];
		if(! isset(Widget::$wgt_list[$widget_id])) return '';
		
		$wgt_id="wgt_{$widget_id}";
		$wgt=Widget::$wgt_list[$widget_id];
		$tpl_file=Widget::get_tpl_file($wgt['action']);
		if(isset($wgt['border']) && $wgt['border'])	$border_file=Widget::get_border_file($wgt['border']);
		else $border_file=NULL;

		$template=NULL;
		if($border_file && file_exists($border_file) ){
			$this->tpl_list[]=$tpl_file;
			$this->tpl_list[]=$border_file;
			$template=file_get_contents($border_file);
			$template=preg_replace($this->preg_web_page_pattern,file_get_contents($tpl_file),$template);
		} 
		else if(file_exists($tpl_file)){
			$this->tpl_list[]=$tpl_file;
			$template=file_get_contents($tpl_file);		
		}else  return $matches[0];
		$this->replace=true;
		$pre='<?php $widget_id="'.$widget_id.'";$_FASTAPP_REQUEST_1J4KxQpT=$request;$_FASTAPP_RESPONSE_1J4KxQpT=$response;
$_FASTAPP_TITLE_1J4KxQpT=$GLOBALS["context"]->app["title"];unset($request);unset($response);$request=$response=array();$title="";
Widget::get_request($widget_id,$request);Widget::get_response($widget_id,$response);Widget::get_title($widget_id,$title);$GLOBALS["context"]->app["title"]=$title;unset($title);?>
';		
		$post='<?php $request=$_FASTAPP_REQUEST_1J4KxQpT;$response=$_FASTAPP_RESPONSE_1J4KxQpT;$GLOBALS["context"]->app["title"]=$_FASTAPP_TITLE_1J4KxQpT;?>';
		return 	$pre.$template.$post;	
	}		
	
	private $preg_web_page_pattern='/<\?php\s+include\s+\$main_child_tpl\s*;*\s*\?>/';
		
}