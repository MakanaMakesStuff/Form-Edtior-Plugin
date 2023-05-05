<?php
/**
 * Form Editor Menu
 * @package Form Editor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary - All Rights Reserved
 * @link https://www.github.com/MakanaMakesStuff/PHPClassFormEditor
 */
declare(strict_types=1);

namespace FormEditor\Settings;

use FormEditor\Classes\Base;

class Menu extends Base
{
	public function init()
	{
		add_action('admin_menu', [$this, 'add_menu']);
	}

	function add_menu()
	{	
		add_menu_page(__('Forms'), __('Forms'), 'edit_users', 'edit.php?post_type=form', '', '', 0);
		add_submenu_page('edit.php?post_type=form', __('Form Options'), __('Form Options'), 'edit_users', 'edit-tags.php?taxonomy=form_option&post_type=form', '');
	}
}
 