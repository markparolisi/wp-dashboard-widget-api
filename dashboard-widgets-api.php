<?php

/*
  Plugin Name: Dashboard Widgets API
  Plugin URI: http://plugins.voceconnect.com/
  Description:
  Author: markparolisi
  Version: 0.1
  Author URI: http://plugins.voceconnect.com/
 */
if (!class_exists('Dashboard_Widgets')) {

    function register_dashboard_widget($classname) {
        Dashboard_Widgets::GetInstance()->add_dashboard_widget($classname);
    }

    class Dashboard_Widgets {

        private static $instance;
        private $widgets = array();

        public static function GetInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new Dashboard_Widgets();
            }
            return self::$instance;
        }

        public function __construct() {
            $this->setup();
        }

        public function setup() {
            add_action('wp_dashboard_setup', array($this, 'action_add_dashboard_widgets'));
        }

        public function add_dashboard_widget($classname) {
            $this->widgets[] = new Dashboard_Widget($classname);
        }

        public function action_add_dashboard_widgets() {
            foreach ($this->widgets as $w) {
                wp_add_dashboard_widget($w->id, $w->name, array($w, 'widget'), array($w, 'control_callback'));
            }
        }

    }

}

// end Dashboard_Graphs class

class Dashboard_Widget {

    public $widget;
    public $id;
    public $name;
    public $options = array();

    public function __construct($classname) {
        $w = new $classname;
        $this->widget = $w;
        $this->id = get_class($w);
        if ($this->widget->name)
            $this->name = $this->widget->name;
        else
            $this->name = ucwords(str_replace('_', ' ', $this->id));
    }

    private function get_widget_options() {
        if (!$widget_options = get_option('dashboard_widget_options'))
            $widget_options = array();
        if (!isset($widget_options[$this->id]))
            $widget_options[$this->id] = array();
        return $widget_options;
    }

    public function widget() {
        if (method_exists($this->widget, 'widget'))
            echo $this->widget->widget($this->get_widget_options());
        else
            echo "No widget display function defined";
    }

    public function control_callback() {
        $widget_options = $this->get_widget_options();
        if ('POST' == $_SERVER['REQUEST_METHOD'] && $_POST['widget_id'] == $this->id) {
            foreach ($_POST as $key => $val) {
                if ($key == $this->id && is_array($val)) {
                    foreach ($val as $k => $v) {
                        $widget_options[$this->id][$k] = esc_attr($v);
                    }
                }
            }
            update_option('dashboard_widget_options', $widget_options);
            delete_transient('dash_' . md5($this->id));
        }
        if (method_exists($this->widget, 'form'))
            $this->widget->form($widget_options);
        else
            echo "This Widget has no options";
    }

}

// end Dashboard_Graphs_Widget class

require_once('test-widget.php');
