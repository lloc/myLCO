<?php

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
