<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    MessageFormat
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace Karwana\MessageFormat;

class CacheTest extends \PHPUnit_Framework_TestCase {

	private function getInstance() {
		return new MessageFormat(implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'data')), 'en');
	}

	public function testGetLocale_ReturnsLocale() {
		$mf = $this->getInstance();
		$this->assertEquals('en', $mf->getLocale());
	}

	public function testGet_ReturnsMessageFormat() {
		$mf = $this->getInstance();

		// Test without domain.
		$this->assertEquals('MessageFormat Tests - {0}', $mf->get('application_name'));

		// Test with domain.
		$this->assertEquals('{0,number,integer} thousand plants', $mf->get('plants.kingdom_size'));
	}

	public function testGet_ThrowsExceptionForBadDomain() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown domain "bananas".');
		$mf->get('bananas.name');
	}

	public function testGet_ThrowsExceptionForKey() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown key "dogs".');
		$mf->get('dogs');
	}

	public function testGet_ThrowsExceptionForKeyInDomain() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown key "status" in domain "plants".');
		$mf->get('plants.status');
	}

	public function testFormat_ReturnsMessage() {
		$mf = $this->getInstance();

		// Test without domain.
		$this->assertEquals('MessageFormat Tests - Winning at Life', $mf->format('application_name', array('Winning at Life')));

		// Test with domain.
		$this->assertEquals('300 thousand plants', $mf->format('plants.kingdom_size', array(300)));
	}

	public function test_CacheIsUsed() {
		$mf_a = $this->getInstance();
		$cache_a = $mf_a::$cache;

		$mf_b = $this->getInstance();
		$cache_b = $mf_b::$cache;

		$this->assertNotSame($mf_b, $mf_a);
		$this->assertSame($cache_b, $cache_a);
	}
}
