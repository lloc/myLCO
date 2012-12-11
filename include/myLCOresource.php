<?php

if ( !class_exists( 'simple_html_dom' ) )
	require_once dirname( __FILE__ ) . '/simple_html_dom.php';

class MyLCObookmark {

	public $link_id = 0;
	public $link_name;
	public $link_url;

	const tr = '<tr><td><a href="/wp-admin/admin.php?page=myLCO_edit&amp;cl=%s" title="%s">%s</td><td>%s</td><td>%s</td><td><a href="%s" title="%s" target="_blank">%s</td><td>%s</td></tr>';

	public function __construct( $obj ) {
		if ( is_object( $obj ) ) {
			foreach ( get_object_vars( $obj ) as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	public function get() {
		$arr = get_option( '_myLCO_' . $this->link_id );
		if ( false === $arr ) {
			$arr = array();
			add_option( '_myLCO_' . $this->link_id, $arr, '', 'no' );
		}
		return $arr;
	}

	public function save( $arr ) {
		if ( is_array( $arr ) ) {
			ksort( $arr );
			update_option( '_myLCO_' . $this->link_id, $arr );
			return true;
		}
		return false;
	}

	public function add( $url ) {
		$url = trim( $url );
		if ( !empty( $url ) ) {
			$backlinks = $this->get();
			$res       = new MyLCOresource( $url );
			if ( !isset( $backlinks[$res->get_url()] ) ) {
				$backlinks[$res->get_url()] = $res->check( $this->link_url );
				$this->save( $backlinks );
				return true;
			}
		}
		return false;
	}

	public function modify( $obj ) {
		if ( is_object( $obj ) ) {
			$backlinks = $this->get();
			if ( isset( $backlinks[$obj->get_url()] ) ) {
				$backlinks[$obj->get_url()] = $obj;
				$this->save( $backlinks );
				return true;
			}
		}
		return false;
	}

	public function check( $arr ) {
		if ( !empty($arr) ) {
			$arr       = ( !is_array( $arr ) ? array( $arr ) : $arr );
			$backlinks = $this->get();
			foreach ( $arr as $url ) {
				if ( isset( $backlinks[$url] ) ) {
					$backlinks[$url] = $backlinks[$url]->check( $this->link_url );
				}
			}
			$this->save( $backlinks );
			return true;
		}
		return false;
	}

	public function delete( $arr ) {
		if ( !empty( $arr ) ) {
			$arr       = ( !is_array( $arr ) ? array( $arr ) : $arr );
			$backlinks = $this->get();
			foreach ( $arr as $url ) {
				unset( $backlinks[$url] );
			}
			$this->save( $backlinks );
			return true;
		}
		return false;
	}

	public function option( $current ) {
		$str = '<option value="%s"%s>%s</option>';
		return sprintf(
			$str,
			$this->link_id,
			( $current == $this->link_id ? ' selected="selected"' : '' ),
			$this->link_url
		);
	}

}

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

class MyLCOanchor {

	private $_text;
	private $attr = array();

	public function __construct( $str ) {
		$this->_text = $this->utf8( $str );
	}

	public function __get( $key ) {
		return( isset ($this->attr[$key]) ? $this->attr[$key] : null );
	}

	public function __set( $key, $value ) {
		$this->attr[$key] = $this->utf8( $value );
	}

	public function utf8( $str ) {
		return( seems_utf8( $str ) ? $str : utf8_encode( $str ) );
	}

	public function __toString() {
		$html = str_get_html( $this->_text );
		if ( false != $html ) {
			$ret = $html->find( 'img', 0 );
			if ( !is_null( $ret ) ) {
				return 'image' . ( $ret->alt ? ' alt: ' . $ret->alt : '' );
			}
		}
		return strip_tags( $this->_text );
	}

	public function is_nofollow() {
		$rel = $this->__get( 'rel' );
		if ( !is_null( $rel ) ) {
			$arr = explode( ' ', $rel );
			if ( in_array( 'nofollow', $arr ) )
				return true;
		}
		return false;
	}

	public function check( $url ) {
		if ( substr( $url, -1 ) == '/' ) {
			$url = substr( $url, 0, -1 );
		}
		return preg_match( "|^$url|i", $this->__get( 'href' ) );
	}

}

class MyLCOicon {

	private $path;

	const html = '<img src="%s" alt="%s" title="%s" />';

	public function __construct( $plugindir ) {
		$this->path = '/' . $plugindir . '/icons/';
	}

	public function get( $res ) {
		if ( $res->is_error() ) {
			$response = $res->get_response_code();
			if ( $response ) {
				$text = sprintf( __( 'Could not load the page. Error: %s', 'myLCO' ), $response );
			}
			else {
				$text = __( 'Could not load the page.', 'myLCO' );
			}
			$src = 'stop.png';
		}
		else {
			if ( $res->is_redirect() ) {
				$text = __( 'URL redirection!', 'myLCO' );
				$src  = 'page_go.png';
			}
			else {
				if ( $res->is_nofollow() ) {
					$text = __( 'Page has defined meta robots nofollow!', 'myLCO' );
					$src  = 'page_error.png';
				}
				else {
					if ( is_object( $res->link ) ) {
						if ( $res->link->is_nofollow() ) {
							$text = __( 'Nofollow!', 'myLCO' );
							$src  = 'link_error.png';
						}
						else {
							$text = sprintf( __( 'Link found (%s)!', 'myLCO' ), $res->link->href );
							$src  = 'accept.png';
						}
					}
					else {
						$text = __( 'Link not found!', 'myLCO' );
						$src  = 'error.png';
					}
				}
			}
		}
		return sprintf( self::html, $this->path . $src, $text, $text );
	}

}
