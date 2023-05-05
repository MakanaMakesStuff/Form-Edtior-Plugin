<?php

/**
 * Base Class
 * @package FormEditor Base Class
 * @author Makanaokeakua Edwards | Makri Software Development
 * @copyright 2023 @ Makri Software Development
 * @license Proprietary
 */

declare(strict_types=1);

namespace FormEditor\Classes;

abstract class Base
{
	public function __constructor()
	{
		// This is a required function on each sub class and will be used to initialize code when our classes get loaded
		$this->init();
	}
	
	abstract public function init();
}
