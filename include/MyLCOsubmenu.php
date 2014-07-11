<?php
/**
 * MyLCOsubmenu.php
 *
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.8.1
 */

/**
 * Submenu
 *
 * @package MyLCO

 * @property string $text
 * @property string $func
 * @property string $action  
 */
class MyLCOsubmenu {

	private $arr;
	private $current;

	const TITLE = '%s &raquo; %s';
	const HTML  = '<ul class="subsubsub">%s<li><a href="/wp-admin/link-manager.php">Link Manager</a></li></ul>';

	public function __construct( $current = 0 ) {
		$item          = new MyLCOsubmenuitem();
		$item->text    = __( 'Dashboard', 'myLCO' );
		$item->func    = 'main';
		$this->arr[]   = $item;
		$item          = new MyLCOsubmenuitem();
		$item->text    = __( 'Edit', 'myLCO' );
		$item->func    = 'edit';
		$item->action  = 'myLCO_edit';
		$this->arr[]   = $item;
		$item          = new MyLCOsubmenuitem();
		$item->text    = __( 'Options', 'myLCO' );
		$item->func    = 'options';
		$item->action  = 'myLCO_options';
		$this->arr[]   = $item;
		$this->current = ( isset( $this->arr[$current] ) ? $current : 0 );
	}

	public function get() {
		return $this->arr;
	}

	public function mainfunc() {
		return $this->arr[0]->func;
	}

	public function get_title() {
		return $this->arr[$this->current]->text;
	}

	public function get_ul() {
		$retval = '';
		$i      = 0;
		foreach ( $this->arr as $item ) {
			$retval .= sprintf(
				MyLCOsubmenuitem::HTML,
				$item->get_page_arg(),
				( $i == $this->current ? ' class="current"' : '' ),
				$item->text
			);
			$i++;
		}
		return(
			sprintf(
				self::HTML,
				$retval
			)
		);
	}

}
