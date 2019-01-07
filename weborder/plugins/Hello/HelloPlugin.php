<?php
class HelloPlugin implements IPlugin {
	function info() {
		return array(
			'name'          => 'hello',
			'author'        => array( 'wzd' ),
			'url'           => 'http://192.168.158.165/wiki',
			'desc' 			=> 'poem-desc',
		);
	}
	function init() {
		Event::listen('beforeCallAction', function($request, $response, $app) {
			if (get_app_act() == 'sys/role/do_list') {
				echo 'hello world!';
				return true;
			}
		});
	}	
}