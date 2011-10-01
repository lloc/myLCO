<?php

class myLCOoptions {

    protected $option_name = '_myLCO';
    protected $params = array (
        'category_name' => 'myLCO',
        'hide_invisible' => 0,
        'orderby' => 'url',
    );

    function __construct () {
        $arr = get_option ($this->option_name);
        if (is_array ($arr)) {
            foreach ($arr as $key => $value) {
                $this->__set ($key, $value);
            }
        }
    }

    function __get ($key) {
        return (isset ($this->params[$key]) ? $this->params[$key] : NULL);
    }

    function __set ($key, $value) {
        $this->params[$key] = $value;
    }

    function get () {
        return array (
            'category_name' => $this->__get ('category_name'),
            'hide_invisible' => $this->__get ('hide_invisible'),
        );
    }

    function update () {
        update_option ($this->option_name, $this->params);
    }

}

class myLCOpr extends myLCOoptions {

    protected $option_name = '_myLCO_pr';
    protected $params = array ();

    function __construct () {
        $arr = get_option ($this->option_name);
        if (is_array ($arr)) {
            foreach ($arr as $key => $value) {
                $this->__set ($key, $value);
            }
        } else {
            add_option ($this->option_name, $this->params, ' ', 'no');
        }
    }

    function clean () {
        $yesterday = time () - 86400;
        foreach ($this->params as $key => $value) {
            if ($yesterday > $value['time']) {
                unset ($this->params[$key]);
            }
        }
    }

    function set ($url) {
        $arr = array ('pr' => 'N/A', 'time' => time ());
        $result = wp_remote_get ("http://webinfodb.net/a/pr.php?url=" . urlencode ($url));
        if (!is_wp_error ($result)) {
            if ('200' == $result['response']['code']) {
                $arr['pr'] = (int) $result['body'];
                $this->__set ($url, $arr);
                $this->update ();
            }
        }
        return $arr['pr'];
    }

    function get ($url) {
        $result = $this->__get ($url);
        return (isset ($result['pr']) ? $result['pr'] : '<img class="set_pr" src="/wp-admin/images/loading.gif" alt="' . $url . '" />');
    }

}

class myLCOalexa extends myLCOpr {

    protected $option_name = '_myLCO_alexa';

    function set ($url) {
        $arr = array ('ranking' => '0', 'time' => time ());
        $xml = @simplexml_load_file ("http://data.alexa.com/data?cli=10&dat=s&url=" . urlencode ($url));
        if ($xml) {
            if (isset ($xml->SD)) {
                foreach ($xml->SD as $sd) {
                    if (isset ($sd->POPULARITY['TEXT'])) {
                        $arr['ranking'] = (int) $sd->POPULARITY['TEXT'];
                        $this->__set ($url, $arr);
                        $this->update ();
                        break;
                    }
                }
            }
        }
        return $arr['ranking'];
    }

    function get ($url) {
        $result = $this->__get ($url);
        return (isset ($result['ranking']) ? $result['ranking'] : '<img class="set_alexa" src="/wp-admin/images/loading.gif" alt="' . $url . '" />');
    }

}

?>
