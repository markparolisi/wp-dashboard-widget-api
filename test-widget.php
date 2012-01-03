<?php

class Test_Widget {

    public $name = 'Test Widget';

    public function form($options) {
        $class = __CLASS__;
        $options = $options[$class];
        echo "<label>MyOption :<input type='text' name='{$class}[MyOption]' value='$options[MyOption]' /></label>";
    }

    public function widget($options) {
        $options = $options[__CLASS__];
        return "this is $options[MyOption]'s Plugin";
    }

}

if (function_exists('register_dashboard_widget'))
    register_dashboard_widget('Test_Widget');


