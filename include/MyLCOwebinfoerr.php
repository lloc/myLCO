<?php

class MyLCOwebinfoerr {

	protected $transient = '_myLCO_webinfoerr';
	protected $error     = true;

	public function __construct() {
		if ( false === ( $value = get_transient( $this->transient ) ) ) {
			$this->error = false;
		}
	}

	public function is_error() {
		return $this->error;
	}

	public function verify( $str ) {
		switch ( $str ) {
			case 'E:Too Busy. Try again in a few minutes.':
				$time = 3600;
				break;
			case 'E:Limit Exceeded':
				$time = 86400;
				break;
			default:
				$time = 0;
		}
		if ( $time > 0 ) {
			set_transient( $this->transient, 1, $time );
			return false;
		}
		return true;
	}

}
