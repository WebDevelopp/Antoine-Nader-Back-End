<?php
final class Session {
	public $data = array();
			
  	public function __construct() {		
		@ini_set('session.use_cookies', '1');
		@ini_set('session.use_trans_sid', 'false');
	
		@session_set_cookie_params(0, '/');
		@session_start();
		
		$this->data = &$_SESSION;
	}
}
?>