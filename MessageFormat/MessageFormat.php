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

use Karwana\Cache\Cache;

class MessageFormat {

	public static $cache;

	private $messages, $locale, $language_file;

	public function __construct($language_files, $locale) {
		if (!isset(self::$cache)) {
			self::$cache = Cache::get();
		}

		$this->language_file = implode(DIRECTORY_SEPARATOR, array(rtrim($language_files, DIRECTORY_SEPARATOR), $locale . '.ini'));
		$this->locale = $locale;
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

	public function get($message_key) {
		$this->ensureLoaded();

		// Check for a domain key, which uses a period as a separator.
		$dot = strpos($message_key, '.');
		if (false === $dot) {
			if (!isset($this->messages[$message_key])) {
				throw new \InvalidArgumentException('Unknown key "' . $message_key . '".');
			}

			return $this->messages[$message_key];
		}

		$domain = substr($message_key, 0, $dot);
		$message_key = substr($message_key, $dot + 1);

		if (!isset($this->messages[$domain])) {
			throw new \InvalidArgumentException('Unknown domain "' . $domain . '".');
		}

		if (!isset($this->messages[$domain][$message_key])) {
			throw new \InvalidArgumentException('Unknown key "' . $message_key . '" in domain "' . $domain . '".');
		}

		return $this->messages[$domain][$message_key];		
	}

	public function format($message_key, $args) {
		return \MessageFormatter::formatMessage($this->locale, $this->get($message_key), $args);
	}
}
