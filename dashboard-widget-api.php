<?php

/*
  Plugin Name: Dashboard Widgets API
  Plugin URI: https://github.com/markparolisi/wp-dashboard-widget-api
  Description: Add configurable widgets to the WP dashboard using an interface similar to sidebar widgets.
  Author: markparolisi, voceplatforms
  Version: 1.0
  Author URI: http://markparolisi.com/
 */
if ( !class_exists( 'Dashboard_Widgets' ) ) {

	function register_dashboard_widget( $classname ) {
		Dashboard_Widgets::GetInstance()->add_dashboard_widget( $classname );
	}

	/*	 * *
	 * 
	 */

	class Dashboard_Widgets {

		private static $instance;
		private $widgets = array( );

		/**
		 * Singleton call
		 * @return Dashboard_Widgets object 
		 */
		public static function GetInstance() {
			if ( !isset( self::$instance ) ) {
				self::$instance = new Dashboard_Widgets();
			}
			return self::$instance;
		}

		/**
		 * @constructor
		 */
		public function __construct() {
			$this->_setup();
		}

		/**
		 * Make WP call to add dashboard widget 
		 * @return void
		 */
		private function _setup() {
			add_action( 'wp_dashboard_setup', array( $this, 'action_add_dashboard_widgets' ) );
		}

		/**
		 * Add Dasboard_Widget object to list of internal widgets
		 * @param type $classname 
		 * @return boolean|Dashboard_Widget object
		 */
		public function add_dashboard_widget( $classname ) {
			if ( !class_exists( $classname ) ) {
				return false;
			}
			$this->widgets[] = new Dashboard_Widget( $classname );
			return end( $this->widgets );
		}

		/**
		 * Called by WP action. Iterates through widgets and adds them to dashboard.
		 * @return void
		 */
		public function action_add_dashboard_widgets() {
			foreach ( $this->widgets as $w ) {
				wp_add_dashboard_widget( $w->id, $w->name, array( $w, 'widget' ), array( $w, 'control_callback' ) );
			}
		}

	}

}

// end Dashboard_Widgets class

/**
 * 
 */
class Dashboard_Widget {

	public $widget;
	public $id;
	public $name;
	public $options = array( );

	/**
	 * @constructor
	 * @param string $classname 
	 */
	public function __construct( $classname ) {
		if ( class_exists( $classname ) ) {
			$w = new $classname;
			$this->widget = $w;
			$this->id = get_class( $w );
			if ( $this->widget->name ) {
				$this->name = $this->widget->name;
			} else {
				$this->name = ucwords( str_replace( '_', ' ', $this->id ) );
			}
		}
	}

	/**
	 * Return the array of widget data
	 * @return array 
	 */
	private function _get_widget_options() {
		if ( !$widget_options = get_option( 'dashboard_widget_options' ) ) {
			$widget_options = array( );
		}
		if ( !isset( $widget_options[$this->id] ) ) {
			$widget_options[$this->id] = array( );
		}
		return $widget_options;
	}

	/**
	 * Execute the callback method for the widget
	 * @return void 
	 */
	public function widget() {
		if ( method_exists( $this->widget, 'widget' ) ) {
			echo $this->widget->widget( $this->_get_widget_options() );
		} else {
			echo "No widget display function defined";
		}
	}

	/**
	 * Create options for the dashboard widget 
	 * @return void
	 */
	public function control_callback() {
		$widget_options = $this->_get_widget_options();
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && $_POST['widget_id'] == $this->id ) {
			foreach ( $_POST as $key => $val ) {
				var_dump( $key, $val );
				if ( $key == $this->id && is_array( $val ) ) {
					foreach ( $val as $k => $v ) {
						$widget_options[$this->id][$k] = esc_attr( $v );
					}
				}
			}
			#die( var_dump( $widget_options ) );
			update_option( 'dashboard_widget_options', $widget_options );
			delete_transient( 'dash_' . md5( $this->id ) );
		}
		if ( method_exists( $this->widget, 'form' ) ) {
			$this->widget->form( $widget_options );
		} else {
			echo "This Widget has no options";
		}
	}

}
