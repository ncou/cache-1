<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Tests;

use Joomla\Cache;

/**
 * Tests for the Joomla\Cache\Wincache class.
 */
class WincacheTest extends CacheTest
{
	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		if (!Cache\Wincache::isSupported())
		{
			$this->markTestSkipped('WinCache Cache Handler is not supported on this system.');
		}

		$this->instance = new Cache\Wincache($this->cacheOptions);
	}
}
