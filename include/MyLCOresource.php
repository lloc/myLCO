<?php
/**
 * MyLCOresource
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.8.1
 */

/**
 * Check if simple_html_dom is already known
 */
if ( ! class_exists( 'simple_html_dom' ) ) {
	require_once MYLCO_PLUGIN_PATH . '/lib/simple_html_dom.php';
}

/**
 * Basic resource
 *
 * @package MyLCO
 */
class MyLCOresource {

	private $url;
	private $ip;
	private $checkdate;
	private $response;

	private $error    = false;
	private $redirect = false;
	private $nofollow = false;

	private $contact = array();

	public $link;

	public function __construct( $str ) {
		$result = parse_url( $str );
		if ( !$result ) {
			$this->error = true;
		} else {
			$this->url = ( !isset( $result['scheme'] ) ? 'http://' : '' ) . $str;
		}
	}

	public function __get( $key ) {
		return( isset( $this->contact[$key] ) ? $this->contact[$key] : null );
	}

	public function __set( $key, $value ) {
		$this->contact[$key] = $value;
	}

	public function is_error() {
		return $this->error;
	}

	public function is_nofollow() {
		return $this->nofollow;
	}

	public function is_redirect() {
		return $this->redirect;
	}

	public function is_details() {
		return( !empty( $this->contact ) ? true : false );
	}

	public function check( $url ) {
		$this->checkdate = time();
		$host            = parse_url( $this->url, PHP_URL_HOST );
		if ( !empty( $host ) ) {
			$ip = gethostbyname( $host );
			if ( $host != $ip ) {
				$this->ip    = $ip;
				$this->link  = null;
				$this->error = $this->redirect = $this->nofollow = false;
				$result      = wp_remote_get(
					$this->url, 
					array( 'redirection' => 0 )
				);
				if ( is_wp_error( $result ) ) {
					$result = wp_remote_get( $this->url );
					if ( !is_wp_error( $result ) ) {
						$this->redirect = true;
					}
					else {
						$this->error = true;
					}
				}
				if ( !is_wp_error( $result ) ) {
					$this->response = $result['response']['code'];
					if ( '200' != $this->response ) {
						$this->error = true;
					}
					$html = str_get_html( $result['body'] );
					if ( false != $html ) {
						foreach ( $html->find( 'meta' ) as $reg ) {
							if ( isset ( $reg->name ) && 'robots' == strtolower( $reg->name ) && isset( $reg->content ) ) {
								$values = explode( ',', $reg->content );
								foreach ( $values as $value ) {
									if ( 'nofollow' == trim( strtolower( $value ) ) ) {
										$this->nofollow = true;
									}
								}
							}
						}
						if ( true != $this->nofollow ) {
							foreach ( $html->find( 'a' ) as $reg ) {
								if ( isset( $reg->href ) ) {
									$a       = new MyLCOanchor( $reg->innertext );
									$a->href = $reg->href;
									if ( isset( $reg->rel ) ) {
										$a->rel = $reg->rel;
									}
									if ( $a->check( $url ) ) {
										$this->link = $a;
										break;
									}
								}
							}
						}
					}
				}
			}
		}
		return $this;
	}

	public function get_response_code() {
		return $this->response;
	}

	public function get_url() {
		return $this->url;    
	}

	public function get_ip() {
		return $this->ip;
	}

	public function get_checkdate() {
		date_default_timezone_set( get_option( 'timezone_string' ) );
		$timezone_format = _x( 'Y-m-d G:i:s', 'timezone date format' );
		return date( $timezone_format, $this->checkdate );
	}

}
