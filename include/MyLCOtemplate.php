<?php

class MyLCOtemplate {

	protected $_file = '';
	private $params  = array();

	public function __construct( $file ) {
		$file = sprintf(
			'%s/templates/%s',
			dirname( __FILE__ ),
			$file
		);
		if ( is_readable( $file ) ) {
			ob_start();
			include( $file );
			$this->_file = ob_get_contents();
			ob_end_clean();
		}
	}

	public function __get( $key ) {
		return(
			isset ($this->params[$key]) ?
			$this->params[$key] :
			null
		);
	}

	public function __set( $key, $value ) {
		$this->params[$key] = $value;
	}

	public function reset() {
		$this->params = array();
	}

	public function get() {
		$content = $this->_file;
		foreach ( $this->params as $key => $value ) {
			$content = str_replace( '{' . $key . '}', $value, $content );
		}
		return $content;
	}

}
