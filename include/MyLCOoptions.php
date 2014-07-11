<?php

class MyLCOoptions {

	protected $option_name = '_myLCO';
	protected $params = array(
		'category_name' => 'myLCO',
		'hide_invisible' => 0,
		'orderby' => 'url',
		'api_key' => '',
	);

	public function __construct() {
		$arr = get_option( $this->option_name );
		if ( is_array( $arr ) ) {
			foreach ( $arr as $key => $value ) {
				$this->__set( $key, $value );
			}
		}
	}

	public function __get( $key ) {
		return(
			isset( $this->params[$key] ) ?
			$this->params[$key] :
			null
		);
	}

	public function __set( $key, $value ) {
		$this->params[$key] = $value;
	}

	public function get() {
		return array(
			'category_name' => $this->__get( 'category_name' ),
			'hide_invisible' => $this->__get( 'hide_invisible' ),
			'api_key' => $this->__get( 'api_key' ),
		);
	}

	public function update() {
		update_option( $this->option_name, $this->params );
	}

}
