<?php

/**
 * Form Editor Post Type
 * @package Form Editor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 */

declare(strict_types=1);

namespace FormEdtior\Types;

use FormEdtior\Classes\Base;

class FormOptions extends Base
{
	public function init()
	{
		add_action('init', [$this, 'registerTax']);
	}

	public function registerTax()
	{
		$id = "form_option";
		$post_type = "forms";
		$singular = "Form Option";
		$plural = "Form Options";
		$slug = "form-options";

		$options = [
			'labels'             => [
				'name'                  => $plural,
				'singular_name'         => $singular,
				'menu_name'             => $plural,
				'update_item'           => sprintf( /* translators: %s: post tax singular title */__('Update %s', 'archipelago'), $singular),
				'add_new_item'          => sprintf( /* translators: %s: post tax singular title */__('Add New %s', 'archipelago'), $singular),
				'new_item_name'         => sprintf( /* translators: %s: post tax singular title */__('New %s', 'archipelago'), $singular),
				'edit_item'             => sprintf( /* translators: %s: post tax singular title */__('Edit %s', 'archipelago'), $singular),
				'view_item'             => sprintf( /* translators: %s: post tax singular title */__('View %s', 'archipelago'), $singular),
				'all_items'             => sprintf( /* translators: %s: post tax title */__('%s', 'archipelago'), $plural),
				'search_items'          => sprintf( /* translators: %s: post tax title */__('Search %s', 'archipelago'), $plural),
				'popular_items'         => sprintf( /* translators: %s: post tax title */__('Popular %s', 'archipelago'), $plural),
			],
			'sort'                  => true,
			'hierarchical'          => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'show_in_rest' 			=> true,
			'query_var'             => true,
			'update_count_callback' => '_update_post_term_count',
			'rewrite' => array('slug' => $slug),
		];

		register_taxonomy($id, $post_type, $options);
	}
}
