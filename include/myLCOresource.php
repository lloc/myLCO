<?php

if (!class_exists ('simple_html_dom'))
    require_once (dirname (__FILE__) . '/simple_html_dom.php');

class myLCObookmark {

    public $link_id = 0;
    public $link_name;
    public $link_url;

    const tr = '<tr><td><a href="/wp-admin/admin.php?page=myLCO_edit&amp;cl=%s" title="%s">%s</td><td>%s</td><td>%s</td><td><a href="%s" title="%s" target="_blank">%s</td><td>%s</td></tr>';

    function __construct ($obj) {
        foreach (get_object_vars ($obj) as $key => $value) {
            $this->$key = $value;
        }
    }

    function get () {
        $arr = get_option ('_myLCO_' . $this->link_id);
        if (FALSE === $arr) {
            $arr = array ();
            add_option ('_myLCO_' . $this->link_id, $arr, ' ', 'no');
        }
        return $arr;
    }

    function save ($arr) {
        if (is_array ($arr)) {
            ksort ($arr);
            update_option ('_myLCO_' . $this->link_id, $arr);
            return TRUE;
        }
        return FALSE;
    }

    function add ($url) {
        $url = trim ($url);
        if (!empty ($url)) {
            $backlinks = $this->get ();
            $res = new myLCOResource ($url);
            if (!isset ($backlinks[$res->getURL ()])) {
                $backlinks[$res->getURL ()] = $res->check ($this->link_url);
                $this->save ($backlinks);
                return TRUE;
            }
        }
        return FALSE;
    }

    function modify ($obj) {
        if (is_object ($obj)) {
            $backlinks = $this->get ();
            if (isset ($backlinks[$obj->getURL ()])) {
                $backlinks[$obj->getURL ()] = $obj;
                $this->save ($backlinks);
                return TRUE;
            }
        }
        return FALSE;
    }

    function check ($arr) {
        if (!empty ($arr)) {
            $arr = (!is_array ($arr) ? array ($arr) : $arr);
            $backlinks = $this->get ();
            foreach ($arr as $url) {
                if (isset ($backlinks[$url])) {
                    $backlinks[$url] = $backlinks[$url]->check ($this->link_url);
                }
            }
            $this->save ($backlinks);
            return TRUE;
        }
        return FALSE;
    }

    function delete ($arr) {
        if (!empty ($arr)) {
            $arr = (!is_array ($arr) ? array ($arr) : $arr);
            $backlinks = $this->get ();
            foreach ($arr as $url) {
                unset ($backlinks[$url]);
            }
            $this->save ($backlinks);
            return TRUE;
        }
        return FALSE;
    }

    function option ($current) {
        $str = '<option value="%s"%s>%s</option>';
        return sprintf (
            $str,
            $this->link_id,
            ($current == $this->link_id ? ' selected="selected"' : ''),
            $this->link_url
        );
    }

}

class myLCOresource {

    private $url;
    private $ip;
    private $checkdate;

    private $error = FALSE;
    private $redirect = FALSE;
    private $nofollow = FALSE;
    private $response;

    private $contact = array ();

    public $link;

    public function __construct ($str) {
        $result = parse_url ($str);
        if (!$result) {
            $this->error = TRUE;
        } else {
            $this->url = (!isset ($result['scheme']) ? 'http://' : '') . $str;
        }
    }

    public function __get ($key) {
        return (isset ($this->contact[$key]) ? $this->contact[$key] : NULL);
    }

    public function __set ($key, $value) {
        $this->contact[$key] = $value;
    }

    function is_error () {
        return $this->error;
    }

    function is_nofollow () {
        return $this->nofollow;
    }

    function is_redirect () {
        return $this->redirect;
    }

    function is_details () {
        return (!empty ($this->contact) ? TRUE : FALSE);
    }

    function check ($url) {
        $this->checkdate = time ();
        $host = parse_url ($this->url, PHP_URL_HOST);
        if (!empty ($host)) {
            $ip = gethostbyname ($host);
            if ($host != $ip) {
                $this->ip = $ip;
                $this->link = NULL;
                $this->error = $this->redirect = $this->nofollow = FALSE;
                $result = wp_remote_get ($this->url, array ('redirection' => 0));
                if (is_wp_error ($result)) {
                    $result = wp_remote_get ($this->url);
                    if (!is_wp_error ($result)) $this->redirect = TRUE;
                    else $this->error = TRUE;
                }
                if (!is_wp_error ($result)) {
                    $this->response = $result['response']['code'];
                    if ('200' != $this->response) $this->error = TRUE;
                    $html = str_get_html ($result['body']);
                    foreach ($html->find ('meta') as $reg) {
                        if (isset ($reg->name) && 'robots' == strtolower ($reg->name) && isset ($reg->content)) {
                            $values = explode (',', $reg->content);
                            foreach ($values as $value) {
                                if ('nofollow' == trim (strtolower ($value))) $this->nofollow = TRUE;
                            }
                        }
                    }
                    if (TRUE != $this->nofollow) {
                        foreach ($html->find ('a') as $reg) {
                            if (isset ($reg->href)) {
                                $a = new myLCOAnchor ($reg->innertext);
                                $a->href = $reg->href;
                                if (isset ($reg->rel)) $a->rel = $reg->rel;
                                if ($a->check ($url)) {
                                    $this->link = $a;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    function getResponseCode () {
        return $this->response;
    }

    function getURL () {
        return $this->url;    
    }

    function getIP () {
        return $this->ip;
    }

    function getCheckdate () {
        date_default_timezone_set (get_option('timezone_string'));
        $timezone_format = _x('Y-m-d G:i:s', 'timezone date format');
        return date ($timezone_format, $this->checkdate);
    }

}

class myLCOanchor {

    private $_text = '';
    private $attr = array ();

    public function __construct ($str) {
        $this->_text = $this->utf8 ($str);
    }

    public function __get ($key) {
        return (isset ($this->attr[$key]) ? $this->attr[$key] : NULL);
    }

    public function __set ($key, $value) {
        $this->attr[$key] = $this->utf8 ($value);
    }

    public function utf8 ($str) {
        return (seems_utf8 ($str) ? $str : utf8_encode ($str));
    }

    public function __toString () {
        $html = str_get_html ($this->_text);
        $ret = $html->find ('img', 0);
        if (!is_null ($ret)) {
            return 'image' . ($ret->alt ? ' alt: ' . $ret->alt : '');
        }
        return strip_tags ($this->_text);
    }

    public function is_nofollow () {
        $rel = $this->__get ('rel');
        if (!is_null ($rel)) {
            $arr = explode (' ', $rel);
            if (in_array ('nofollow', $arr)) return TRUE;
        }
        return FALSE;
    }

    public function check ($url) {
        if (substr ($url, -1) == '/') $url = substr ($url, 0, -1);
        return (preg_match ("|^$url|i", $this->__get ('href')));
    }

}

class myLCOicon {

    private $path;
    const html = '<img src="%s" alt="%s" title="%s" />';

    function __construct ($plugindir) {
        $this->path = '/' . $plugindir . '/icons/';
    }

    function get ($res) {
        if ($res->is_error ()) {
            $response = $res->getResponseCode ();
            if ($response) {
                $text = sprintf (__ ('Could not load the page. Error: %s', 'myLCO'), $response);
            } else {
                $text = __ ('Could not load the page.', 'myLCO');
            }
            $src = 'stop.png';
        } else {
            if ($res->is_redirect ()) {
                $text = __ ('URL redirection!', 'myLCO');
                $src = 'page_go.png';
            } else {
                if ($res->is_nofollow ()) {
                    $text = __ ('Page has defined meta robots nofollow!', 'myLCO');
                    $src = 'page_error.png';
                } else {
                    if (is_object ($res->link)) {
                        if ($res->link->is_nofollow ()) {
                            $text = __ ('Nofollow!', 'myLCO');
                            $src = 'link_error.png';
                        } else {
                            $text = sprintf (__ ('Link found (%s)!', 'myLCO'), $res->link->href);
                            $src = 'accept.png';
                        }
                    } else {
                        $text = __ ('Link not found!', 'myLCO');
                        $src = 'error.png';
                    }
                }
            }
        }
        return (sprintf (self::html, $this->path . $src, $text, $text));
    }

}

?>
