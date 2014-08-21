<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Tests;

use Joomla\Cache;
use Joomla\Test\TestHelper;

/**
 * Tests for the Joomla\Cache\FileTest class.
 *
 * @since  1.0
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Cache\File
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Tests the Joomla\Cache\File::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::__construct
	 * @since   __DEPLOY_VERSION__
	 * @expectedException  \RuntimeException
	 */
	public function test__construct()
	{
		$options = array(
			'file.path' => '/'
		);

		$this->instance = new Cache\File($options);
	}

	/**
	 * Tests for the correct Psr\Cache return values.
	 *
	 * @return  void
	 *
	 * @coversNothing
	 * @since   1.0
	 */
	public function testPsrCache()
	{
		$this->assertInternalType('boolean', $this->instance->clear(), 'Checking clear.');
		$this->assertInstanceOf('\Psr\Cache\CacheItemInterface', $this->instance->get('foo'), 'Checking get.');
		$this->assertInternalType('array', $this->instance->getMultiple(array('foo')), 'Checking getMultiple.');
		$this->assertInternalType('boolean', $this->instance->remove('foo'), 'Checking remove.');
		$this->assertInternalType('array', $this->instance->removeMultiple(array('foo')), 'Checking removeMultiple.');
		$this->assertInternalType('boolean', $this->instance->set('for', 'bar'), 'Checking set.');
		$this->assertInternalType('boolean', $this->instance->setMultiple(array('foo' => 'bar')), 'Checking setMultiple.');
	}

	/**
	 * Tests the Joomla\Cache\File::clear method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::clear
	 * @since   1.0
	 */
	public function testClear()
	{
		$this->instance->set('foo', 'bar');
		$this->instance->set('goo', 'car');

		$this->instance->clear();

		$this->assertFalse($this->instance->get('foo')->isHit());
		$this->assertFalse($this->instance->get('goo')->isHit());
	}

	/**
	 * Tests the Joomla\Cache\File::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$this->assertFalse($this->instance->get('foo')->isHit(), 'Checks an unknown key.');

		$this->instance->setOption('ttl', 1);
		$this->instance->set('foo', 'bar', 1);

		$this->assertEquals(
			'bar',
			$this->instance->get('foo')->getValue(),
			'The key should have not been deleted.'
		);

		sleep(2);

		$this->assertNull(
			$this->instance->get('foo')->getValue(),
			'The key should have been deleted.'
		);
	}

	/**
	 * Tests the Joomla\Cache\File::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::get
	 * @expectedException  \RuntimeException
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetCantRemoveExpiredKeyException()
	{
		$options = array(
			'file.path' => __DIR__ . '/tmp'
		);

		$instance = $this->getMockBuilder('Joomla\Cache\File');
		$instance = $instance->setMethods(array('remove'));
		$instance = $instance->setConstructorArgs(array($options));
		$instance = $instance->getMock();

		$instance->expects($this->any())
				->method('remove')
				->will($this->returnValue(false));

		$instance->setOption('ttl', 1);
		$instance->set('foo', 'bar', 1);
		sleep(2);

		$this->assertNull(
			$instance->get('foo')->getValue(),
			'The key should have been deleted.'
		);
	}

	/**
	 * Tests the Joomla\Cache\File::exists method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::exists
	 * @since   1.0
	 */
	public function testExists()
	{
		$this->assertFalse(TestHelper::invoke($this->instance, 'exists', 'foo'));
		$this->instance->set('foo', 'bar');
		$this->assertTrue(TestHelper::invoke($this->instance, 'exists', 'foo'));
	}

	/**
	 * Tests the Joomla\Cache\File::remove method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::remove
	 * @since   1.0
	 */
	public function testRemove()
	{
		$this->assertTrue(
			$this->instance->set('foo', 'bar'),
			'Checks the value was set'
		);
		$this->assertTrue(
			$this->instance->remove('foo'),
			'Checks the value was removed'
		);
		$this->assertNull(
			$this->instance->get('foo')->getValue(),
			'Checks for the delete'
		);
	}

	/**
	 * Tests the Joomla\Cache\File::set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::set
	 * @covers  Joomla\Cache\File::get
	 * @covers  Joomla\Cache\File::remove
	 * @since   1.0
	 * @todo    Custom ttl is not working in set yet.
	 */
	public function testSet()
	{
		$fileName = TestHelper::invoke($this->instance, 'fetchStreamUri', 'foo');

		$this->assertFileNotExists($fileName);

		$this->instance->set('foo', 'bar');
		$this->assertFileExists(
			$fileName,
			'Checks the cache file was created.'
		);

		$this->assertEquals(
			'bar', $this->instance->get('foo')->getValue(),
			'Checks we got the cached value back.'
		);

		$this->instance->remove('foo');
		$this->assertNull(
			$this->instance->get('foo')->getValue(),
			'Checks for the delete.'
		);
	}

	/**
	 * Tests the Joomla\Cache\File::checkFilePath method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::checkFilePath
	 * @since   1.0
	 */
	public function testCheckFilePath()
	{
		$this->assertTrue(TestHelper::invoke($this->instance, 'checkFilePath', __DIR__));
	}

	/**
	 * Tests the Joomla\Cache\File::checkFilePath method for a known exception.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Cache\File::checkFilePath
	 * @expectedException  \RuntimeException
	 * @since              1.0
	 */
	public function testCheckFilePathInvalidPath()
	{
		// Invalid path
		TestHelper::invoke($this->instance, 'checkFilePath', 'foo123');
	}

	/**
	 * Tests the Joomla\Cache\File::checkFilePath method for a known exception.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Cache\File::checkFilePath
	 * @expectedException  \RuntimeException
	 * @since              1.0
	 */
	public function testCheckFilePathUnwritablePath()
	{
		// Check for an unwritable folder.
		TestHelper::invoke($this->instance, 'checkFilePath', '/');
	}

	/**
	 * Tests the Joomla\Cache\File::fetchStreamUri method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::fetchStreamUri
	 * @since   1.0
	 */
	public function testFetchStreamUri()
	{
		$fileName = TestHelper::invoke($this->instance, 'fetchStreamUri', 'test');
	}

	/**
	 * Tests the Joomla\Cache\File::isExpired method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Cache\File::isExpired
	 * @since   1.0
	 */
	public function testIsExpired()
	{
		$this->instance->setOption('ttl', 1);
		$this->instance->set('foo', 'bar');
		sleep(2);
		$this->assertTrue(TestHelper::invoke($this->instance, 'isExpired', 'foo'), 'Should be expired.');

		$this->instance->setOption('ttl', 900);
		$this->instance->set('foo', 'bar');
		$this->assertFalse(TestHelper::invoke($this->instance, 'isExpired', 'foo'), 'Should not be expired.');
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		// Clean up the test folder.
		$this->tearDown();

		$options = array(
			'file.path' => __DIR__ . '/tmp'
		);

		$this->instance = new Cache\File($options);
	}

	/**
	 * Teardown the test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function tearDown()
	{
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(__DIR__ . '/tmp/'),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $file)
		{
			if ($file->isFile() && $file->getExtension() == 'data')
			{
				unlink($file->getRealPath());
			}
			elseif ($file->isDir() && strpos($file->getFilename(), '~') === 0)
			{
				rmdir($file->getRealPath());
			}
		}
	}
}
