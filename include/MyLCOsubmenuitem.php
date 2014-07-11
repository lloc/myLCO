<?php

/**
 * MyLCOsubmenuitem.php
 *
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.8.1
 */

/**
 * Submenuitem
 *
 * @property string $text  
 * @property string $func  
 * @property string $action  
 * @package MyLCO
 */
class MyLCOsubmenuitem {

	private $_attributes = array();

	const HTML = '<li><a href="/wp-admin/admin.php?page=%s"%s>%s</a> | </li>';

	public function __get( $key ) {
		return(
			isset( $this->_attributes[ $key ] ) ?
			$this->_attributes[ $key ] :
			null
		);
	}

	public function __set( $key, $value ) {
		$this->_attributes[ $key ] = $value;
	}

	public function get_page_action() {
		$action = $this->__get( 'action' );
		return(
			is_null( $action ) ?
			__FILE__ :
			$action
		);
	}

	public function get_page_arg() {
		$action = $this->__get( 'action' );
		return(
			is_null( $action ) ?
			plugin_basename( __FILE__ ) :
			$action
		);
	}

}
