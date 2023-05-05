<?php

/**
 * @package FormEditor
 * @version 1.0.0
 */
/*
Plugin Name: FormEditor
Plugin URI: https://github.com/MakanaMakesStuff/PHPClassFormEditor
Description: This is an example plugin using a class FormEditor
Author: Makanaokeakua Edwards | Makri Software Development
*/

add_action('init', 'loadClasses', 0);

function FormEditor_page()
{
	echo '<h1>Welcome to the FormEditor Plugin page!</h1>';
}

function loadClasses()
{
	$base = "FormEditor\\";
	$class_files = glob(__DIR__ . '/Classes/*.php');
	$enqueue_files = glob(__DIR__ . '/Enqueue/*.php');
	$settings_files = glob(__DIR__ . '/Settings/*.php');
	$type_files = glob(__DIR__ . '/Types/*.php');
	$loaded_classes = [];

	foreach ($class_files as $file) {
		if (file_exists($file)) {
			require_once $file;
		}
	}

	foreach ($settings_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes[] = $base . "Settings\\" . basename($file, '.php');
		}
	}

	foreach ($enqueue_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes[] = $base . "Enqueue\\" . basename($file, '.php');
		}
	}

	foreach ($type_files as $file) {
		if (file_exists($file)) {
			require_once $file;
			$loaded_classes[] = $base . "Types\\" . basename($file, '.php');
		}
	}

	foreach ($loaded_classes as $class) {
		if (class_exists($class)) {
			$instance = new $class();
			$methods = get_class_methods($instance);
			if (in_array('init', $methods)) {
				$instance->init();
			}
		}
	}
}
