<?php

class MyLCObookmark {

	public $link_id = 0;
	public $link_name;
	public $link_url;

	const HTML = '<tr><td><a href="/wp-admin/admin.php?page=myLCO_edit&amp;cl=%s" title="%s">%s</td><td>%s</td><td>%s</td><td><a href="%s" title="%s" target="_blank">%s</td><td>%s</td></tr>';

	public function __construct( $obj ) {
		if ( is_object( $obj ) ) {
			foreach ( get_object_vars( $obj ) as $key => $value ) {
				$this->$key = $value;
			}
		}
	}

	public function get_option_name() {
		return '_myLCO_' . $this->link_id;
	}

	public function get() {
		$option_name = $this->get_option_name();
		$arr = get_option( $option_name );
		if ( false === $arr ) {
			$arr = array();
			add_option( $option_name, $arr, '', 'no' );
		}
		return $arr;
	}

	public function save( $arr ) {
		if ( is_array( $arr ) ) {
			ksort( $arr );
			update_option( $this->get_option_name(), $arr );
			return true;
		}
		return false;
	}

	public function add( $url ) {
		$url = trim( $url );
		if ( ! empty( $url ) ) {
			$backlinks = $this->get();
			$res       = new MyLCOresource( $url );
			if ( ! isset( $backlinks[$res->get_url()] ) ) {
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
		if ( ! empty($arr) ) {
			$arr       = ( ! is_array( $arr ) ? array( $arr ) : $arr );
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
		if ( ! empty( $arr ) ) {
			$arr       = ( ! is_array( $arr ) ? array( $arr ) : $arr );
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
