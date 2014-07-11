<?php
/**
 * MyLCOanchor.php
 *
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.8.1
 */

/**
 * Anchor
 *
 * @package MyLCO

 * @property string $rel
 * @property string $href
 */
class MyLCOanchor {

	private $_text;
	private $attr = array();

	public function __construct( $str ) {
		$this->_text = $this->utf8( $str );
	}

	public function __get( $key ) {
		return( isset( $this->attr[ $key ] ) ? $this->attr[ $key ] : null );
	}

	public function __set( $key, $value ) {
		$this->attr[ $key ] = $this->utf8( $value );
	}

	public function utf8( $str ) {
		return( seems_utf8( $str ) ? $str : utf8_encode( $str ) );
	}

	public function __toString() {
		$html = str_get_html( $this->_text );
		if ( false != $html ) {
			$ret = $html->find( 'img', 0 );
			if ( ! is_null( $ret ) ) {
				return 'image' . ( $ret->alt ? ' alt: ' . $ret->alt : '' );
			}
		}
		return strip_tags( $this->_text );
	}

	public function is_nofollow() {
		$rel = $this->__get( 'rel' );
		if ( ! is_null( $rel ) ) {
			$arr = explode( ' ', $rel );
			if ( in_array( 'nofollow', $arr ) ) {
				return true;
			}
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
