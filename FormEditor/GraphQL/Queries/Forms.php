<?php
/**
 * Form Editor GraphQL Forms
 * @package FormEditor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 * @link https://github.com/MakanaMakesStuff/Form-Edtior-Plugin
 */

 declare(strict_types=1);

 namespace FormEditor\GraphQL\Queries;

 use FormEditor\Classes\Base;

 class Forms extends Base {
	public $priority = 2;

	public function init() {
		include_once(__DIR__ . '/../Types/Form.php');

		add_action('graphql_register_types', function() {
			register_graphql_field('RootQuery', 'form', [
				'type' => ['list_of' => 'Form'],
				'args' => [
					'paged' => [
						'type' => 'Int',
						'description' => 'The amount of forms returned from the endpoint'
					]
				],
				'resolve' => function($root, $args) {
					$paged = isset($args['paged']) ? $args['paged'] : -1;

					if($paged == 0) {
						return [];
					}

					$_args = [
						'post_type' => 'form',
						'posts_per_page' => $paged
					];

					$forms = get_posts($_args);

					$form_meta = [];

					foreach($forms as $form) {
						$meta = get_post_meta($form->ID, 'form_inputs');

						$title = $form->post_title;
						$content = $form->post_content;
						$form_inputs = [];

						foreach($meta[0] as $input) {
							$options = [];

							if(isset($input['options'])) {
								foreach($input['options'] as $option) {
									$options[] = [
										'slug' => $option['slug'],
										'name' => $option['name']
									];
								}
							}

							$form_inputs[] = [
								'label' => $input['label'],
								'help_text' => $input['help_text'],
								'input_type' => $input['input_type'],
								'option_group' => $input['option_group'],
								'options' => $options
							];
						}
						
						$form_meta[] = ['title' => $title, 'content' => strval($content), 'form_inputs' => $form_inputs ];
					}
					
					return $form_meta;
				}
			]);
		});
	}
 }