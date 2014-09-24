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

	public function get( $url ) {
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

class MyLCOpr extends MyLCOoptions {

	protected $option_name = '_myLCO_pr';
	protected $params = array();

	public function __construct() {
		$arr = get_option( $this->option_name );
		if ( is_array( $arr ) ) {
			foreach ( $arr as $key => $value ) {
				$this->__set( $key, $value );
			}
		}
		else {
			add_option( $this->option_name, $this->params, '', 'no' );
		}
	}

	public function clean() {
		$yesterday = time() - 86400;
		foreach ( $this->params as $key => $value ) {
			if ( $yesterday > $value['time'] ) {
				unset( $this->params[$key] );
			}
		}
	}

	public function set( $url ) {
		$options = new MyLCOoptions();
		$arr     = array(
			'pr' => 'N/A',
			'time' => time(),
		);
		if ( '' != $options->api_key ) {
			$err = new MyLCOwebinfoerr;
			if ( !$err->is_error() ) {
				$result = wp_remote_get( 
					sprintf(
						'http://pr.webinfodb.net/pr.php?key=%s&url=%s',
						$options->api_key,
						urlencode( $url )
					)
				);
				if ( !is_wp_error( $result ) && '200' == $result['response']['code'] ) {
					if ( $err->verify( $result['body'] ) ) {
						$arr['pr'] = (int) $result['body'];
						$this->__set( $url, $arr );
						$this->update();
					}
				}
			}
		}
		return $arr['pr'];
	}

	public function get( $url ) {
		$result = $this->__get( $url );
		return(
			isset( $result['pr'] ) ?
			$result['pr'] :
			sprintf(
				'<img class="set_pr" src="/wp-admin/images/loading.gif" alt="%s" />',
				$url
			)
		);
	}

}

class MyLCOalexa extends MyLCOpr {

	protected $option_name = '_myLCO_alexa';

	public function set( $url ) {
		$arr = array(
			'ranking' => '0', 
			'time' => time(),
		);
		$result = wp_remote_get( 
			sprintf(
				'http://data.alexa.com/data?cli=10&dat=s&url=%s',
				urlencode( $url )
			)
		);
		if ( !is_wp_error( $result ) && '200' == $result['response']['code'] ) {
			$xml = simplexml_load_string( $result['body'] );
			if ( $xml ) {
				if ( isset( $xml->SD ) ) {
					foreach ( $xml->SD as $sd ) {
						if ( isset( $sd->POPULARITY['TEXT'] ) ) {
							$arr['ranking'] = (int) $sd->POPULARITY['TEXT'];
							$this->__set( $url, $arr );
							$this->update();
							break;
						}
					}
				}
			}
		}
		return $arr['ranking'];
	}

	public function get( $url ) {
		$result = $this->__get( $url );
		return(
			isset( $result['ranking'] ) ? 
			$result['ranking'] : 
			sprintf(
				'<img class="set_alexa" src="/wp-admin/images/loading.gif" alt="%s" />',
				$url
			)
		);
	}

}
