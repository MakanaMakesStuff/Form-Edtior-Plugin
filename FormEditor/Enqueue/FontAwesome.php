<?php
/**
 * Form Editor Enqueue FontAwesome
 * @package Form Editor
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary - All Rights Reserved
 * @link https://www.github.com/MakanaMakesStuff/PHPClassFormEditor
 */
declare(strict_types=1);

namespace FormEditor\Enqueue;

use FormEditor\Classes\Base;

class FontAwesome extends Base
{
	public function init()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue']);
	}

	public function enqueue()
	{
		wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css');
	}
}
?>