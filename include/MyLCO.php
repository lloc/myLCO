<?php

class MyLCO {

	public $options;
	public $bookmarks;

	public function __construct( MyLCOoptions $options ) {
		$this->options = $options;
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public function init() {
		load_plugin_textdomain( 'myLCO', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		wp_enqueue_script( 'jquery' );
	}

	public function admin_menu() {
		$smenu = new MyLCOsubmenu();
		add_menu_page( 'myLCO', 'myLCO', 'administrator', __FILE__, array( $this, $smenu->mainfunc() ) );
		foreach ( $smenu->get() as $item ) {
			add_submenu_page(
				__FILE__,
				sprintf( MyLCOsubmenu::TITLE, 'myLCO', $item->text ),
				$item->text,
				'administrator',
				$item->get_page_action(),
				array( $this, $item->func )
			);
		}
	}

	public function get_bookmarks() {
		if ( ! is_array( $this->bookmarks ) ) {
			$this->bookmarks = array();
			$bookmarks       = get_bookmarks( $this->options->get() );
			foreach ( $bookmarks as $bookmark ) {
				$this->bookmarks[ $bookmark->link_id ] = new MyLCObookmark( $bookmark );
			}
		}
		return $this->bookmarks;
	}

	public function main() {
		require_once dirname( __FILE__ ) . '/include/myLCOresource.php';
		$tpage = new MyLCOtemplate( 'page.php' );
		$smenu = new MyLCOsubmenu();
		$tpage->set( 'menu', $smenu->get_ul() );
		$bookmarks = $this->get_bookmarks();
		if ( empty( $bookmarks ) ) {
			$tpage = $this->incomplete( $tpage );
		}
		else {
			$tpage->set( 'title', $smenu->get_title() )->set( 'message', '' );
			$tcontent = new MyLCOtemplate( 'main.php' );
			$temp     = '';
			$pr       = new MyLCOpr();
			$alexa    = new MyLCOalexa();
			$pr->clean();
			$alexa->clean();
			foreach ( $bookmarks as $bookmark ) {
				$temp .= sprintf(
					MyLCObookmark::HTML,
					$bookmark->link_id,
					__( 'Edit the backlinks of that project...', 'myLCO' ),
					$bookmark->link_url,
					$pr->get( $bookmark->link_url ),
					$alexa->get( $bookmark->link_url ),
					$bookmark->link_url,
					__( 'Go to that page...', 'myLCO' ),
					$bookmark->link_name,
					count( $bookmark->get() )
				);
			}
			$tcontent->set( 'content', $temp );
			if ( 1 == count( $bookmarks ) ) {
				$tcontent->set( 'tablenav', __( 'There is just 1 URL available for managing backlinks.', 'myLCO' ) );
			}
			else {
				$tcontent->set(
					'tablenav',
					sprintf(
						__( 'There are %s URLs available for managing backlinks.', 'myLCO' ),
						count( $bookmarks )
					)
				);
			}
			$tpage->set( 'content', $tcontent->get() );
		}
		echo $tpage->get();
	}

	public function edit() {
		require_once dirname( __FILE__ ) . '/include/myLCOresource.php';
		$tpage       = new MyLCOtemplate( 'page.php' );
		$smenu       = new MyLCOsubmenu( 1 );
		$tpage->menu = $smenu->get_ul();
		$bookmarks   = $this->get_bookmarks();
		if ( empty( $bookmarks ) ) {
			$tpage = $this->incomplete( $tpage );
		}
		else {
			$cl           = ( isset( $_REQUEST['cl'] ) && isset( $bookmarks[$_REQUEST['cl']] ) ? $_REQUEST['cl'] : key( $bookmarks ) );
			$link         = $bookmarks[$cl];
			$tpage->title = sprintf(
				'%s &quot;%s&quot; (%s)',
				$smenu->get_title(),
				$link->link_name,
				$link->link_url
			);
			$msg          = new MyLCOmessage();
			$msg->css     = 'updated';
			if ( ! empty( $_REQUEST['action'] ) ) {
				if ( 'delete' == $_REQUEST['action'] && ! empty( $_REQUEST['url'] ) ) {
					$link->delete( $_REQUEST['url'] );
					$msg->text = __( 'Selected backlinks have been deleted.', 'myLCO' );
				}
				if ( 'check' == $_REQUEST['action'] && ! empty( $_REQUEST['url'] ) ) {
					$link->check( $_REQUEST['url'] );
					$msg->text = __( 'Selected backlinks have been checked.', 'myLCO' );
				}
			}
			if ( ! empty( $_REQUEST['backlink'] ) ) {
				$link->add( $_REQUEST['backlink'] );
				$msg->text = __( 'A new backlink has been added.', 'myLCO' );
			}
			$tpage->message = $msg->get();
			$tform          = new MyLCOtemplate( 'form.php' );
			$temp           = '';
			foreach ( $bookmarks as $bookmark ) {
				$temp .= $bookmark->option( $cl );
			}
			$tform->options = $temp;
			$temp           = '';
			$backlinks      = $link->get();
			if ( ! empty( $backlinks ) ) {
				$tbody     = new MyLCOtemplate( 'edit.php' );
				$ipcounter = array();
				$i         = 0;
				$icon      = new MyLCOicon( PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ) );
				$pr        = new MyLCOpr();
				$trow      = new MyLCOtemplate( 'row.php' );
				$pr->clean();
				foreach ( $backlinks as $backlink ) {
					if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'contact[' . $i . ']' ) {
						$backlink->contact_name    = $_REQUEST['contact_name'][$i];
						$backlink->contact_email   = $_REQUEST['contact_email'][$i];
						$backlink->contact_remarks = $_REQUEST['contact_remarks'][$i];
						$link->modify( $backlink );
					}
					$trow->reset();
					$trow->hnum               = $i;
					$trow->cl                 = $cl;
					$trow->alternate_class    = ( $backlink->is_details() ? ' class="alternate"' : '' );
					$trow->backlink_url       = $backlink->get_url();
					$trow->backlink_pr        = $pr->get( $backlink->get_url() );
					$trow->backlink_text      = $backlink->link;
					$trow->backlink_ip        = $backlink->get_ip();
					$trow->backlink_icon      = $icon->get( $backlink );
					$trow->backlink_checkdate = $backlink->get_checkdate();
					$trow->contact_name       = $backlink->contact_name;
					$trow->contact_email      = $backlink->contact_email;
					$trow->contact_remarks    = $backlink->contact_remarks;
					if ( '' != $backlink->contact_email ) {
						$trow->Kemail = sprintf(
							__( '<a href="mailto:%s">E-mail</a>', 'myLCO' ),
							$backlink->contact_email
						);
					} else {
						$trow->Kemail = __( 'E-mail', 'myLCO' );
					}
					$trow->DeleteMessage = sprintf(
						__( 'Do you really want to delete %s? Please click on OK to continue, or CANCEL if you are not sure!', 'myLCO' ),
						$backlink->get_url()
					);
					$temp .= $trow->get();
					$i++;
					$ipcounter[$backlink->get_ip()] = 1;
				}
				$tbody->cl      = $cl;
				$tbody->content = $temp;
				if ( 1 == $i ) {
					$tbody->tablenav = __( 'There is just 1 backlink inserted so far.', 'myLCO' );
				}
				else {
					$tbody->tablenav = sprintf(
						__( 'There are %s backlinks (with %s different IP addresses) inserted so far.', 'myLCO' ),
						$i,
						count( $ipcounter )
					);
				}
				$temp = $tbody->get();
			} else {
				$temp =
					'<div class="alignleft"><p>' .
					__( 'OK! Let\'s insert some backlinks to see what\'s going on.', 'myLCO' ) .
					'</p></div>';
			}
			$tpage->content = $tform->get() . $temp;
		}
		echo $tpage->get();
	}

	public function options() {
		$tpage = new MyLCOtemplate( 'page.php' );
		$smenu = new MyLCOsubmenu( 2 );
		$tpage->set( 'menu', $smenu->get_ul() )->set( 'title', $smenu->get_title() );

		$msg = new MyLCOmessage();
		if ( isset( $_REQUEST['save'] ) ) {
			if ( ! empty( $_REQUEST['category_name'] ) ) {
				$this->options->category_name  = $_REQUEST['category_name'];
				$this->options->api_key  = $_REQUEST['api_key'];
				$this->options->hide_invisible = ( isset( $_REQUEST['hide_invisible'] ) ? 1 : 0 );
				$this->options->update();

				$msg->set( 'text', __( 'Options succesfully saved.', 'myLCO' ) )
					->set( 'css', 'updated' );
			} else {
				$msg->set( 'text', __( 'The name of a link category which can be used by myLCO is required!', 'myLCO' ) )
					->set( 'css', 'error' );
			}
		}
		$tpage->set( 'message', $msg->get() );

		$tcontent = new MyLCOtemplate( 'options.php' );
		$tcontent->set( 'category_name', $this->options->category_name )
			->set( 'api_key', $this->options->api_key )
			->set( 'hide_invisible', ( $this->options->hide_invisible == 1 ? ' checked="checked"' : '' ) );

		$tpage->set( 'content', $tcontent->get() );
		echo $tpage->get();
	}

	public function incomplete( $template ) {
		$template->title   = __( 'Further actions required', 'myLCO' );
		$template->message = '';
		$str               = '';
		if ( $this->options->hide_invisible == 1 ) {
			$str = __( ' or all links are private and you have decided to hide such links', 'myLCO' );
		}
		$template->content =
			'<div class="alignleft"><p>' .
			sprintf(
				__( 'There are no links in the category <strong>%s</strong>%s. Please use the <a href="/wp-admin/link-manager.php">WP Link-Manager</a> to add some links to this category or retry with other <a href="%s?page=myLCO_options">options</a>!', 'myLCO' ),
				$this->options->category_name,
				$str,
				admin_url()
			) .
			'</p></div>';
		return $template;
	}

}
