<?php

/**
 * LICENSE: This source code is subject to the license that is available
 * in the LICENSE file distributed along with this package.
 *
 * @package    MessageFormat
 * @author     Matthew Caruana Galizia <mcg@karwana.com>
 * @copyright  Karwana Ltd
 * @since      File available since Release 1.0.0
 */

namespace Karwana\MessageFormat;

class CacheTest extends \PHPUnit_Framework_TestCase {

	private function getLanguageFilesDirectory() {
		return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'data'));
	}

	private function getInstance() {
		return new MessageFormat($this->getLanguageFilesDirectory(), 'en');
	}

	private function getChainedInstance() {
		$mf = $this->getInstance();
		return new MessageFormat($this->getLanguageFilesDirectory(), 'en-gb', $mf);
	}

	public function testGetLocale_ReturnsLocale() {
		$mf = $this->getInstance();
		$this->assertEquals('en', $mf->getLocale());
	}

	public function testGetLanguageFile_ReturnsLanguageFilePath() {
		$mf = $this->getInstance();
		$this->assertEquals($this->getLanguageFilesDirectory() . DIRECTORY_SEPARATOR . 'en.ini', $mf->getLanguageFile());
	}

	public function testGet_ReturnsMessageFormat() {
		$mf = $this->getInstance();

		// Test without section.
		$this->assertEquals('MessageFormat Tests - {0}', $mf->get('application_name'));

		// Test with section.
		$this->assertEquals('{0,number,integer} thousand plants', $mf->get('plants.kingdom_size'));
	}

	public function testGet_ThrowsExceptionForBadSection() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown section "bananas".');
		$mf->get('bananas.name');
	}

	public function testGet_ThrowsExceptionForKey() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown key "dogs".');
		$mf->get('dogs');
	}

	public function testGet_ThrowsExceptionForKeyInSection() {
		$mf = $this->getInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown key "status" in section "plants".');
		$mf->get('plants.status');
	}

	public function testFormat_ReturnsMessage() {
		$mf = $this->getInstance();

		// Test without section.
		$this->assertEquals('MessageFormat Tests - Winning at Life', $mf->format('application_name', array('Winning at Life')));

		// Test with section.
		$this->assertEquals('300 thousand plants', $mf->format('plants.kingdom_size', array(300)));
	}

	public function testGetLink_ReturnsChainedInstance() {
		$mf = $this->getChainedInstance();

		$this->assertEquals($this->getLanguageFilesDirectory() . DIRECTORY_SEPARATOR . 'en-gb.ini', $mf->getLanguageFile());
		$this->assertEquals('en-gb', $mf->getLocale());

		$this->assertEquals($this->getLanguageFilesDirectory() . DIRECTORY_SEPARATOR . 'en.ini', $mf->getLink()->getLanguageFile());
		$this->assertEquals('en', $mf->getLink()->getLocale());
	}

	public function testGet_ReturnsMessageFormatFromLink() {
		$mf = $this->getChainedInstance();

		// Make sure it tries en-gb first.
		$this->assertEquals('MessageFormat Tests: {0}', $mf->get('application_name'));
		$this->assertEquals('Animals', $mf->getLink()->get('animals.kingdom_name'));
		$this->assertEquals('Animalia', $mf->get('animals.kingdom_name'));

		// Then falls back to en for non-existent keys.
		$this->assertEquals('v1.0.0', $mf->get('application_version'));
		$this->assertEquals('Plants', $mf->getLink()->get('plants.kingdom_name'));
		$this->assertEquals('Plants', $mf->get('plants.kingdom_name'));

		// When the section is defined in en-gb, but the subkey only in en.
		$this->assertEquals('{0,number,integer} thousand animals', $mf->getLink()->get('animals.kingdom_size'));
		$this->assertEquals('{0,number,integer} thousand animals', $mf->get('animals.kingdom_size'));
	}

	public function testGet_ThrowsExceptionForKeyWhenChained() {
		$mf = $this->getChainedInstance();
		$this->setExpectedException('InvalidArgumentException', 'Unknown key "dogs".');
		$mf->get('dogs');
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
