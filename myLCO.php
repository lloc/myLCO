<?php

/*
Plugin Name: myLCO
Plugin URI: http://lloc.de/wp-plugin-mylco
Description: Maintain and verify the backlinks to your sites pointing from the linking pages of your link-exchange-partners
Version: 0.7
Author: Dennis Ploetner
Author URI: http://lloc.de/
Text Domain: myLCO
License: GPL2
*/

/*
Copyright 2010  Dennis Ploetner  (email : re@lloc.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined ('_MYLCO_')) define ('_MYLCO_', 'myLCO');

if (!class_exists (_MYLCO_)) {

    require_once (dirname (__FILE__) . '/include/myLCOoptions.php');

    function mylco_pagerank () {
        $pr = new myLCOpr ();
        echo $pr->set ($_POST['url']);
        die ();
    }
    add_action ('wp_ajax_mylco_pagerank', 'mylco_pagerank');

    function mylco_alexa () {
        $alexa = new myLCOalexa ();
        echo $alexa->set ($_POST['url']);
        die ();
    }
    add_action ('wp_ajax_mylco_alexa', 'mylco_alexa');

    class myLCOtemplate {
    
        protected $_file = '';
        private $params = array ();
    
        function __construct ($file) {
            $file = dirname (__FILE__) . '/templates/' . $file;
            if (is_readable ($file)) {
                ob_start ();
                include ($file);
                $this->_file = ob_get_contents ();
                ob_end_clean ();
            }
        }

        function __get ($key) {
            return (isset ($this->params[$key]) ? $this->params[$key] : NULL);
        }
    
        function __set ($key, $value) {
            $this->params[$key] = $value;
        }
    
        function reset () {
            $this->params = array ();
        }
    
        function get () {
            $content = $this->_file;
            foreach ($this->params as $key => $value) {
                $content = str_replace ('{' . $key . '}', $value, $content); 
            }
            return $content;
        }
    
    }

    class myLCOmessage extends myLCOtemplate {

        protected $_file = '<div id="message" class="{css}"><p>{text}</p></div>';

        function __construct () {}

        function get () {
            $text = $this->__get ('text');
            return (is_null ($text) ? '' : parent::get ());
        }

    }

    class myLCOsubmenu {

        private $arr;
        private $current;

        const title = '%s &raquo; %s';
        const ul = '<ul class="subsubsub">%s<li><a href="/wp-admin/link-manager.php">Link Manager</a></li></ul>';

        function __construct ($current = 0) {
            $item = new myLCOsubmenuitem ();
            $item->text = __ ('Dashboard', _MYLCO_);
            $item->func = 'main';
            $this->arr[] = $item;
            $item = new myLCOsubmenuitem ();
            $item->text = __ ('Edit', _MYLCO_);
            $item->func = 'edit';
            $item->action = 'myLCO_edit';
            $this->arr[] = $item;
            $item = new myLCOsubmenuitem ();
            $item->text = __ ('Options', _MYLCO_);
            $item->func = 'options';
            $item->action = 'myLCO_options';
            $this->arr[] = $item;
            $this->current = (isset ($this->arr[$current]) ? $current : 0);
        }

        function get () {
            return $this->arr;
        }

        function mainfunc () {
            return $this->arr[0]->func;
        }

        function getTitle () {
            return $this->arr[$this->current]->text;
        }

        function getUL () {
            $retval = '';
            $i = 0;
            foreach ($this->arr as $item) {
                $retval .= sprintf (
                    myLCOsubmenuitem::li,
                    $item->getPageArg (),
                    ($i == $this->current ? ' class="current"' : ''),
                    $item->text
                );
                $i++;
            }
            return (sprintf (self::ul, $retval));
        }

    }

    class myLCOsubmenuitem {

        private $_attributes = array ();

        const li = '<li><a href="/wp-admin/admin.php?page=%s"%s>%s</a> |</li>';

        function __get ($key) {
            return (isset ($this->_attributes[$key]) ? $this->_attributes[$key] : NULL);
        }

        function __set ($key, $value) {
            $this->_attributes[$key] = $value;
        }

        function getPageAction () {
            $action = $this->__get ('action');
            return (is_null ($action) ? __FILE__ : $action);
        }

        function getPageArg () {
            $action = $this->__get ('action');
            return (is_null ($action) ? plugin_basename (__FILE__) : $action);
        }

    }

    class myLCO {

        var $options;

        function __construct () {
            $this->options = new myLCOoptions ();
            add_action ('init', array (&$this, 'init'));
            add_action ('admin_menu', array (&$this, 'admin_menu'));
        }

        function init () {
            load_plugin_textdomain (_MYLCO_, FALSE, dirname (plugin_basename (__FILE__)) . '/lang');
            wp_enqueue_script('jquery');
        }

        function admin_menu () {
            $smenu = new myLCOsubmenu ();
            add_menu_page (_MYLCO_, _MYLCO_, 'administrator', __FILE__, array (&$this, $smenu->mainfunc ()));
            foreach ($smenu->get () as $item) {
                add_submenu_page (
                    __FILE__,
                    sprintf (myLCOsubmenu::title, _MYLCO_, $item->text),
                    $item->text,
                    'administrator',
                    $item->getPageAction (),
                    array (&$this, $item->func)
                );
            }
        }

        function get_bookmarks () {
            if (!isset ($this->bookmarks)) {
                $this->bookmarks = array ();
                $bookmarks = get_bookmarks ($this->options->get ());
                foreach ($bookmarks as $bookmark) {
                    $this->bookmarks[$bookmark->link_id] = new MyLCObookmark ($bookmark);
                }
            }
            return $this->bookmarks;
        }

        function main () {
            require_once (dirname (__FILE__) . '/include/MyLCOresource.php');

            $tpage = new myLCOtemplate ('page.php');
            $smenu = new myLCOsubmenu ();
            $tpage->menu = $smenu->getUL ();

            $bookmarks = $this->get_bookmarks ();
            if (empty ($bookmarks)) {
                $tpage = $this->incomplete ($tpage);
            } else {
                $tpage->title = $smenu->getTitle ();
                $tpage->message = '';

                $tcontent = new myLCOtemplate ('main.php');

                $temp = '';
                $pr = new myLCOpr ();
                $pr->clean ();
                $alexa = new myLCOalexa ();
                $alexa->clean ();
                foreach ($bookmarks as $bookmark) {
                    $temp .= sprintf (
                        MyLCObookmark::tr,
                        $bookmark->link_id,
                        __ ('Edit the backlinks of that project...', _MYLCO_),
                        $bookmark->link_url,
                        $pr->get ($bookmark->link_url),
                        $alexa->get ($bookmark->link_url),
                        $bookmark->link_url,
                        __ ('Go to that page...', _MYLCO_),
                        $bookmark->link_name,
                        count ($bookmark->get ())
                    );
                }
                $tcontent->content = $temp;
                if (1 == count ($bookmarks)) {
                    $tcontent->tablenav = __ ('There is just 1 URL available for managing backlinks.', _MYLCO_);
                } else {
                    $tcontent->tablenav = sprintf (__ ('There are %s URLs available for managing backlinks.', _MYLCO_), count ($bookmarks));
                }
                $tpage->content = $tcontent->get ();
            }
            echo $tpage->get ();
        }

        function edit () {
            require_once (dirname (__FILE__) . '/include/MyLCOresource.php');

            $tpage = new myLCOtemplate ('page.php');
            $smenu = new myLCOsubmenu (1);
            $tpage->menu = $smenu->getUL ();

            $bookmarks = $this->get_bookmarks ();
            if (empty ($bookmarks)) {
                $tpage = $this->incomplete ($tpage);
            } else {
                $link = (isset ($bookmarks[$_REQUEST['cl']]) ? $bookmarks[$_REQUEST['cl']] : current ($bookmarks));
                $tpage->title = sprintf ('%s &quot;%s&quot; (%s)', $smenu->getTitle (), $link->link_name, $link->link_url);

                $msg = new myLCOmessage ();
                $msg->css = 'updated';
                if (!empty ($_REQUEST['action'])) {
                    if ('delete' == $_REQUEST['action'] && !empty ($_REQUEST['url'])) {
                        $link->delete ($_REQUEST['url']);
                        $msg->text = __ ('Selected backlinks have been deleted.', _MYLCO_);
                    }
                    if ('check' == $_REQUEST['action'] && !empty ($_REQUEST['url'])) {
                        $link->check ($_REQUEST['url']);
                        $msg->text = __ ('Selected backlinks have been checked.', _MYLCO_);
                    }
                }
                if (!empty ($_REQUEST['backlink'])) {
                    $link->add ($_REQUEST['backlink']);
                    $msg->text = __ ('A new backlink has been added.', _MYLCO_);
                }
                $tpage->message = $msg->get ();

                $tform = new myLCOtemplate ('form.php');
                $temp = '';
                foreach ($bookmarks as $bookmark) {
                    $temp .= $bookmark->option ($_REQUEST['cl']);
                }
                $tform->options = $temp;

                $temp = '';
                $backlinks = $link->get ();
                if (!empty ($backlinks)) {
                    $tbody = new myLCOtemplate ('edit.php');

                    $ipcounter = array ();
                    $i = 0;
                    $icon = new MyLCOicon (PLUGINDIR . '/' . dirname (plugin_basename (__FILE__)));
                    $pr = new myLCOpr ();
                    $pr->clean ();
                    $trow = new myLCOtemplate ('row.php');
                    foreach ($backlinks as $backlink) {
                        if (isset ($_REQUEST['action']) && $_REQUEST['action'] == 'contact[' . $i . ']') {
                            $backlink->contact_name = $_REQUEST['contact_name'][$i];
                            $backlink->contact_email = $_REQUEST['contact_email'][$i];
                            $backlink->contact_remarks = $_REQUEST['contact_remarks'][$i];
                            $link->modify ($backlink);
                        }
                        $trow->reset ();
                        $trow->hnum = $i;
                        $trow->alternate_class = ($backlink->is_details () ? ' class="alternate"' : '');
                        $trow->backlink_url = $backlink->get_url ();
                        $trow->backlink_pr = $pr->get ($backlink->get_url ());
                        $trow->backlink_text = $backlink->link;
                        $trow->backlink_ip = $backlink->get_ip ();
                        $trow->backlink_icon = $icon->get ($backlink);
                        $trow->backlink_checkdate = $backlink->get_checkdate ();
                        $trow->contact_name = $backlink->contact_name;
                        $trow->contact_email = $backlink->contact_email;
                        $trow->contact_remarks = $backlink->contact_remarks;
                        if ($backlink->contact_email != '') {
                            $trow->Kemail = sprintf (__ ('<a href="mailto:%s">E-mail</a>', _MYLCO_), $backlink->contact_email);
                        } else {
                            $trow->Kemail = __ ('E-mail', _MYLCO_);
                        }
                        $trow->DeleteMessage = sprintf (__ ('Do you really want to delete %s? Please click on OK to continue, or CANCEL if you are not sure!', _MYLCO_), $backlink->get_url ());
                        $temp .= $trow->get ();

                        $i++;
                        $ipcounter[$backlink->get_ip ()] = 1;
                    }
                    $tbody->content = $temp;
                    if (1 == $i) {
                        $tbody->tablenav = __ ('There is just 1 backlink inserted so far.', _MYLCO_);
                    } else {
                        $tbody->tablenav = sprintf (__ ('There are %s backlinks (with %s different IP addresses) inserted so far.', _MYLCO_), $i, count ($ipcounter));
                    }
                    $temp = $tbody->get ();
                } else {
                    $temp =
                        '<div class="alignleft"><p>' .
                        __ ('OK! Let\'s insert some backlinks to see what\'s going on.', _MYLCO_) .
                        '</p></div>';
                }
                $tpage->content = $tform->get () . $temp;

            }
            echo $tpage->get ();
        }

        function options () {
            $tpage = new myLCOtemplate ('page.php');
            $smenu = new myLCOsubmenu (2);
            $tpage->menu = $smenu->getUL ();
            $tpage->title = $smenu->getTitle ();

            $msg = new myLCOmessage ();

            if (isset ($_REQUEST['save'])) {
                if (!empty ($_REQUEST['category_name'])) {
                    $this->options->category_name = $_REQUEST['category_name'];
                    $this->options->hide_invisible = (isset ($_REQUEST['hide_invisible']) ? 1 : 0);
                    $this->options->update ();
                    $msg->text = __ ('Options succesfully saved.', _MYLCO_);
                    $msg->css = 'updated';
                } else {
                    $msg->text = __ ('The name of a link category which can be used by myLCO is required!', _MYLCO_);
                    $msg->css = 'error';
                }
            }
            $tpage->message = $msg->get ();

            $tcontent = new myLCOtemplate ('options.php');
            $tcontent->category_name = $this->options->category_name;
            $tcontent->hide_invisible = ($this->options->hide_invisible == 1 ? ' checked="checked"' : '');

            $tpage->content = $tcontent->get ();
            echo $tpage->get ();
        }

        function incomplete ($template) {
            $template->title = __ ('Further actions required', _MYLCO_);
            $template->message = '';
            $str = '';
            if ($this->options->hide_invisible == 1) {
                $str = __ (' or all links are private and you have decided to hide such links', _MYLCO_);
            }
            $template->content = 
                '<div class="alignleft"><p>' .
                sprintf (__ ('There are no links in the category <strong>%s</strong>%s. Please use the <a href="/wp-admin/link-manager.php">WP Link-Manager</a> to add some links to this category or retry with other <a href="%s?page=myLCO_options">options</a>!', _MYLCO_), $this->options->category_name, $str, $_SERVER['PHP_SELF']) .
                '</p></div>';
            return $template;
        }

    }

}

if (function_exists ('register_activation_hook'))
    register_activation_hook (__FILE__, 'myLCO_install');

function myLCO_install() {
    $myLCO = new myLCO ();
    $myLCO->options->update ();
}

if (function_exists ('register_uninstall_hook'))
    register_uninstall_hook (__FILE__, 'myLCO_uninstall');

function myLCO_uninstall() {
    global $wpdb;
    $wpdb->query ("DELETE FROM " . $wpdb->options . " WHERE option_name like '_myLCO%'");
}

if (class_exists (_MYLCO_) && is_admin ()) {
    $myLCO = new myLCO ();
}

?>