<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Tests\Adapter;

use Joomla\Cache\Adapter\XCache;
use Joomla\Cache\Tests\CacheTestCase;

/**
 * Tests for the Joomla\Cache\Adapter\XCache class.
 */
class XCacheTestCase extends CacheTestCase
{
	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		if (!XCache::isSupported())
		{
			$this->markTestSkipped('XCache Cache Handler is not supported on this system.');
		}

		$this->instance = new XCache($this->cacheOptions);
	}
}
