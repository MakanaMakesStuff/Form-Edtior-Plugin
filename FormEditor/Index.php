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
	$base = 'FormEditor';
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
	$files = [];

	foreach($iterator as $iter) {
		if($iter->isFile()) {
			$files[] = $iter->getPathname();
		}
	}

	$loaded_classes  = [];

	foreach ($files as $file) {
		if(file_exists($file)) {
			if(strpos($file, 'Classes') != false) {
				require_once($file);
			} else {
				require_once($file);
				$file = str_replace(__DIR__, '', $file);
				$file = preg_replace('/\//', '\\', $file);
				$file = str_replace('.php', '', $file);
				$loaded_classes[] = $base . $file;
			}
		}
	}

	$order = [];

	foreach($loaded_classes as $class) {
		
		if(class_exists($class)) {
			$init = new $class();

			if(property_exists($init, 'priority')) {
				$order[] = [
					'priority' => $init->priority,
					'instance' => $init
				];
			} else {
				$order[] = [
					'priority' => 0,
					'instance' => $init
				];
			}
		} 
	}

	usort($order, function($a, $b) {
		return $a['priority'] - $b['priority'];
	});

	foreach($order as $class) {
		$class['instance']->init();
	}
}
