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

use Karwana\Cache\Cache;

class MessageFormat {

	public static $cache;

	private $messages, $locale, $language_file, $link;

	public function __construct($language_files, $locale, MessageFormat $link = null) {
		if (!isset(self::$cache)) {
			self::$cache = Cache::get();
		}

		$this->language_file = implode(DIRECTORY_SEPARATOR, array(rtrim($language_files, DIRECTORY_SEPARATOR), $locale . '.ini'));
		$this->locale = $locale;
		$this->link = $link;
	}

	private function ensureLoaded() {
		if (isset($this->messages)) {
			return;
		}

		$cache_key = 'messageformat:' . $this->language_file;

		if (self::$cache->hasItem($cache_key)) {
			$this->messages = self::$cache->getItem($cache_key);
		} else {
			$this->messages = parse_ini_file($this->language_file, true);
			self::$cache->setItem($cache_key, $this->messages);
		}
	}

	public function getLocale() {
		return $this->locale;
	}

	public function getLanguageFile() {
		return $this->language_file;
	}

	public function getLink() {
		return $this->link;
	}

	public function get($message_key) {
		$this->ensureLoaded();

		// Check for a section key, which uses a period as a separator.
		$dot = strpos($message_key, '.');
		if (false === $dot) {
			if (isset($this->messages[$message_key])) {
				return $this->messages[$message_key];
			}

			if (isset($this->link)) {
				return $this->link->get($message_key);
			}

			throw new \InvalidArgumentException('Unknown key "' . $message_key . '".');
		}

		$section = substr($message_key, 0, $dot);
		$sub_key = substr($message_key, $dot + 1);

		if (!isset($this->messages[$section])) {
			if (isset($this->link)) {
				return $this->link->get($message_key);
			}

			throw new \InvalidArgumentException('Unknown section "' . $section . '".');
		}

		if (!isset($this->messages[$section][$sub_key])) {
			if (isset($this->link)) {
				return $this->link->get($message_key);
			}

			throw new \InvalidArgumentException('Unknown key "' . $sub_key . '" in section "' . $section . '".');
		}

		return $this->messages[$section][$sub_key];
	}

	public function format($message_key, $args) {
		return \MessageFormatter::formatMessage($this->locale, $this->get($message_key), $args);
	}
}
