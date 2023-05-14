<?php
/**
 * Form Editor GraphQL Forms
 * @package FormEditor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 * @link https://github.com/MakanaMakesStuff/Form-Edtior-Plugin
 */

register_graphql_object_type('FormOptions', [
	'description' => 'Form Options',
	'fields' => [
		'slug' => [
			'type' => 'String',
		],
		'name' => [
			'type' => 'String',
		]
	]
]);

register_graphql_object_type('FormInputs', [
	'description' => 'Form Inputs',
	'fields' => [
		'label' => [
			'type' => 'String',
		],
		'help_text' => [
			'type' => 'String',
		],
		'input_type' => [
			'type' => 'String',
		],
		'option_group' => [
			'type' => 'String',
		],
		'options' => [
			'type' => ['list_of' => 'FormOptions'],
		],
	]
]);

register_graphql_object_type('Form', [
	'description' => "Form Object",
	'fields' => [
		'title' => [
			'type' => 'String'
		],
		'content' => [
			'type' => 'String'
		],
		'form_inputs' => [
			'type' => ['list_of' => 'FormInputs']
		]
	]
]);
