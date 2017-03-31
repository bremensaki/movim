<?php

namespace Movim\Widget;

use Movim\Session;

class Wrapper
{
    private static $instance;

    private $_widgets = [];
    private $_events = [];
    private $_eventWidgets = [];

    private $_view = ''; // The current page where the widget is displayed

    private $css = []; // All the css loaded by the widgets so far.
    private $js = []; // All the js loaded by the widgets so far.

    public $title = null; // If a widget has defined a particular title
    public $image = null; // If a widget has defined a particular image
    public $description = null; // If a widget has defined a particular description
    public $url = null; // If a widget has defined a particular url

    public function __construct()
    {
    }

    public function registerAll($load = false)
    {
        $widgets_dir = scandir(APP_PATH ."widgets/");

        foreach($widgets_dir as $widget_dir) {
            if(is_dir(APP_PATH ."widgets/".$widget_dir) &&
                $widget_dir != '..' &&
                $widget_dir != '.') {

                if($load != false
                && in_array($widget_dir, $load)) {
                    $this->loadWidget($widget_dir, true);
                }

                array_push($this->_widgets, $widget_dir);
            }
        }
    }

    static function getInstance()
    {
        if(!is_object(self::$instance)) {
            self::$instance = new Wrapper;
        }
        return self::$instance;
    }

    static function destroyInstance()
    {
        if(isset(self::$instance)) {
            self::$instance = null;
        }
    }

    /**
     * @desc Set the view
     * @param $page the name of the current view
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * @desc Loads a widget and returns it
     * @param $name the name of the widget
     * @param $register know if we are loading in the daemon or displaying
     */
    public function loadWidget($name, $register = false)
    {
        if(file_exists(APP_PATH . "widgets/$name/$name.php")) {
            $path = APP_PATH . "widgets/$name/$name.php";
        }
        else {
            throw new \Exception(
                __('error.widget_load_error', $name));
        }

        require_once($path);

        if($register) {
            $widget = new $name(true);
            // We save the registered events of the widget for the filter
            if(isset($widget->events)) {
                foreach($widget->events as $key => $value) {
                    if(is_array($this->_events)
                    && array_key_exists($key, $this->_events)) {
                        $we = $this->_events[$key];
                        array_push($we, $name);
                        $we = array_unique($we);
                        $this->_events[$key] = $we;
                    } else {
                        $this->_events[$key] = [$name];
                    }
                }
                array_push($this->_eventWidgets, $name);
            }
        } else {
            if($this->_view != '') {
                $widget = new $name(false, $this->_view);
            } else {
                $widget = new $name();
            }

            if(php_sapi_name() != 'cli') {
                // Collecting stuff generated by the widgets.
                $this->css = array_merge($this->css, $widget->loadcss());
                $this->js = array_merge($this->js, $widget->loadjs());

                if(isset($widget->title)) $this->title = $widget->title;
                if(isset($widget->image)) $this->image = $widget->image;
                if(isset($widget->description)) $this->description = $widget->description;
                if(isset($widget->url)) $this->url = $widget->url;
            }

            return $widget;
        }
    }

    /**
     * @desc Loads a widget and runs a particular function on it.
     *
     * @param $widget_name is the name of the widget.
     * @param $method is the function to be run.
     * @param $params is an array containing the parameters to
     *   be passed along to the method.
     * @return what the widget's method returns.
     */
    function runWidget($widget_name, $method, array $params = null)
    {
        $widget = $this->loadWidget($widget_name);

        if(!is_array($params)) {
            $params = [];
        }

        return call_user_func_array([$widget, $method], $params);
    }

    /**
     * Calls a particular function with the given parameters on
     * all loaded widgets.
     *
     * @param $key is the key of the incoming event
     * @param $data is the Packet that is sent as a parameter
     */
    function iterate($key, $data)
    {
        if(array_key_exists($key, $this->_events)) {
            foreach($this->_events[$key] as $widget_name) {
                $widget = new $widget_name(true);
                if(array_key_exists($key, $widget->events)) {
                    foreach($widget->events[$key] as $method) {
                        /*
                         * We check if the method need to be called if the
                         * session notifs_key is set to a specific value
                         */
                        if(is_array($widget->filters)
                        && array_key_exists($method, $widget->filters)) {
                            $session = Session::start();
                            $notifs_key = $session->get('notifs_key');

                            if($notifs_key == 'blurred') {
                                $widget->{$method}($data);
                            } else {
                                $explode = explode('|', $notifs_key);
                                $notif_key = reset($explode);
                                if($notif_key == $widget->filters[$method]) {
                                    $widget->{$method}($data);
                                }
                            }
                        } else {
                            $widget->{$method}($data);
                        }
                    }
                }
            }
        }
    }

    /**
     * @desc Returns the list of loaded CSS.
     */
    function loadcss()
    {
        return $this->css;
    }

    /**
     * @desc Returns the list of loaded javascripts.
     */
    function loadjs()
    {
        return $this->js;
    }
}
