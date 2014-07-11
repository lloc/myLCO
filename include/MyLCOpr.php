<?php

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
			if ( ! $err->is_error() ) {
				$result = wp_remote_get(
					sprintf(
						'http://pr.webinfodb.net/pr.php?key=%s&url=%s',
						$options->api_key,
						urlencode( $url )
					)
				);
				if ( ! is_wp_error( $result ) && '200' == $result['response']['code'] ) {
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
