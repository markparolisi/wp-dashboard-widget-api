<?php
/**
 * Demo Dashboard widget 
 */
class Test_Widget {

	public $name = 'Test Widget';

	public function form( $options ) {
		$class = __CLASS__;
		$options = $options[$class];
		echo '<label>First Name :<input type="text" name="'.$class.'[first_name]" value="' . $options['first_name'] . '" /></label>';
	}

	public function widget( $options ) {
		$options = $options[__CLASS__];
		$first_name = (isset($options['first_name'])) ? $options['first_name'] : "No One";
		return "this is $first_name's Plugin";
	}

}

if ( function_exists( 'register_dashboard_widget' ) ) {
	register_dashboard_widget( 'Test_Widget' );
}
