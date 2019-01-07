<?php
class PluginUtil {
	
	static function handle_plugins() {
		$plugins = self::get_active_plugins();
		
		foreach ($plugins as $plugin) {
			$class = $plugin['class'];
			$obj = new $class();
			$ret = call_user_func(array($obj, 'init'));
			if (!$ret) {
				CTX()->log_debug('plugin ['.$plugin['name'].'] return false');
			}
		}
	}
	
	static function get_active_plugins() {
		$plugins = self::get_plugins();
		// TODO 增加插件激活功能
		
		if (!is_array($plugins)) {
		    $plugins = array();
		}
		
		return $plugins;
	}
	
	static function get_plugins() {
		$plugins =  array();
		$path = ROOT_PATH . CTX()->app_name . DS . 'plugins' . DS;
		
		$handle = @opendir($path);
		if (!$handle) {
			return ;
		}
		
		while ( false !== ($file = readdir($handle)) ) {
			if (is_dir($path.$file) && $file != '.' && $file != '..') {
				$_plugin_file = $path.$file.DS.$file.'Plugin.php';
				
				if (file_exists($_plugin_file)) {
					include_once $_plugin_file;
					$class = $file.'Plugin';
					$obj = new $class();
					$plugins[$file] = $obj->info();
					$plugins[$file]['class'] = $class;
					$plugins[$file]['file'] = $_plugin_file;
					unset($obj);
				}
			}
		}
		
		return $plugins;
	}
}