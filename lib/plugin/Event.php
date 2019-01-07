<?php
class Event {
	static $_events = array();
	
	public static function fire($event, $args=array()) {
		if (!isset(self::$_events[$event])) {
			return ;
		}
		foreach (self::$_events[$event] as $callbacks) {
			foreach ($callbacks as $callback) {
				self::_call_event_handler($callback, $args);
			}
		}
	}
	
	public static function listen($event, $callback, $priority=10) {
		self::$_events[$event][$priority][] = $callback;
	}
	
	private static function _call_event_handler($callback, $args) {
		CTX()->log_debug('call event:'.var_export($callback, true));
		call_user_func_array($callback, $args);
	}
}