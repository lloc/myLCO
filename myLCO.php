<?php

/*
Plugin Name: myLCO
Plugin URI: http://lloc.de/wp-plugin-mylco
Description: Maintain and verify the backlinks to your sites pointing from the linking pages of your link-exchange-partners
Version: 0.8.1
Author: Dennis Ploetner
Author URI: http://lloc.de/
Text Domain: myLCO
License: GPL2
*/

/*
Copyright 2010  Dennis Ploetner  (email : re@lloc.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists( 'MyLCO' ) ) {
	if ( ! defined( 'MYLCO_PLUGIN_PATH' ) ) {
		define( 'MYLCO_PLUGIN_PATH', plugin_basename( __FILE__ ) );
	}
	add_filter( 'pre_option_link_manager_enabled', '__return_true' );

	/**
	 * The Autoloader does all the magic when it comes to include a file
	 * @package MyLCO
	 */
	class MyLCOautoloader {

		/**
		 * Static loader method
		 * @param string $cls
		 */
		public static function load( $cls ) {
			if ( 'MyLCO' == substr( $cls, 0, 5 ) )
				require_once dirname( __FILE__ ) . '/includes/' . $cls . '.php';
		}

	}

	/**
	 * The autoload-stack could be inactive so the function will return
	 * false
	 */
	if ( in_array( '__autoload', (array) spl_autoload_functions() ) ) {
		spl_autoload_register( '__autoload' );
	}
	spl_autoload_register( array( 'MyLCOautoloader', 'load' ) );

	function mylco_install() {
		$options = new MyLCOoptions();
		$lco     = new MyLCO( $options );
		$lco->options->update();
	}
	if ( function_exists( 'register_activation_hook' ) ) {
		register_activation_hook( __FILE__, 'mylco_install' );
	}

	function mylco_uninstall() {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name like '_myLCO%'"
			)
		);
	}
	if ( function_exists( 'register_uninstall_hook' ) ) {
		register_uninstall_hook( __FILE__, 'mylco_uninstall' );
	}

	function mylco_pagerank() {
		$gpr = new MyLCOpr();
		$url = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
		echo $gpr->set( $url );
		die();
	}
	add_action( 'wp_ajax_mylco_pagerank', 'mylco_pagerank' );

	function mylco_alexa() {
		$alx = new MyLCOalexa();
		$url = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );
		echo $alx->set( $url );
		die();
	}
	add_action( 'wp_ajax_mylco_alexa', 'mylco_alexa' );

	if ( is_admin() ) {
		$options = new MyLCOoptions();
		$lco     = new MyLCO( $options );
	}

}
