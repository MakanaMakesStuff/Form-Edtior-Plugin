<?php

/**
 * @package FormEdtior
 * @version 1.0.0
 */
/*
Plugin Name: FormEdtior
Plugin URI: https://github.com/MakanaMakesStuff/PHPClassFormEdtior
Description: This is an example plugin using a class FormEdtior
Author: Makanaokeakua Edwards | Makri Software Development
*/

add_action('init', 'loadClasses', 0);
add_action('admin_menu', 'add_menu');

function add_menu()
{
	add_menu_page(__('FormEdtior'), __('FormEdtior'), 'manage_options', 'FormEdtior-menu-page', 'FormEdtior_page', '', 0);
	add_submenu_page('FormEdtior-menu-page', __('Hello'), __('Hello'), 'manage_options', 'edit.php?post_type=hello', '', 0);
}

function FormEdtior_page()
{
	echo '<h1>Welcome to the FormEdtior Plugin page!</h1>';
}

function loadClasses()
{
	$class_files = glob(__DIR__ . '/Classes/*.php');
	$type_files = glob(__DIR__ . '/Types/*.php');
	$loaded_classes = [];

	foreach ($class_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes = array_merge($loaded_classes, get_declared_classes());
		}
	}

	foreach ($type_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes = array_merge($loaded_classes, get_declared_classes());
		}
	}

	foreach ($loaded_classes as $class) {
		if (strpos($class, 'FormEdtior\\') === 0 && class_exists($class)) {
			$instance = new $class();
			$methods = get_class_methods($instance);
			if (in_array('init', $methods)) {
				$instance->init();
			}
		}
	}
}
