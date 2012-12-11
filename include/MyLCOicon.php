<?php

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
