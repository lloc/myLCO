<?php

class MyLCOmessage extends MyLCOtemplate {

	protected $_file = '<div id="message" class="{css}"><p>{text}</p></div>';

	public function __construct() {}

	public function get() {
		$text = $this->__get( 'text' );
		return( is_null( $text ) ? '' : parent::get() );
	}

}
