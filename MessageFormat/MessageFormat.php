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

use Stash;

class MessageFormat {

	private $messages, $locale, $language_file, $link, $cache;


	/**
	 * Construct a new MessageFormat instance.
	 *
	 * @param string $language_files Path to directory where language files are stored.
	 * @param string $locale Must correspond to a file in the $language_files directory. For example, 'en' for 'en.ini'.
	 * @param MessageFormat $link A MessageFormat instance to use in a fallback chain.
	 */
	public function __construct($language_files, $locale, MessageFormat $link = null) {
		$this->language_file = implode(DIRECTORY_SEPARATOR, array(rtrim($language_files, DIRECTORY_SEPARATOR), $locale . '.ini'));
		$this->locale = $locale;
		$this->link = $link;
	}

	private function ensureLoaded() {
		if (isset($this->messages)) {
			return;
		}

		if (isset($this->cache)) {
			$cache_item = $this->cache->getItem('karwana/messageformat/' . $this->language_file);
			$this->messages = $cache_item->get();

			if (!$cache_item->isMiss()) {
				return;
			}
		}

		$this->messages = parse_ini_file($this->language_file, true);

		if (isset($this->cache)) {
			$cache_item->set($this->messages);
		}
	}


	/**
	 * Set the cache pool.
	 *
	 * @param Stash\Interfaces\PoolInterface $pool
	 */
	public function setCache(Stash\Interfaces\PoolInterface $pool) {
		$this->cache = $pool;
	}


	/**
	 * Get the cache pool.
	 *
	 * @return Stash\Interfaces\PoolInterface
	 */
	public function getCache() {
		return $this->cache;
	}


	/**
	 * Gets the instance locale.
	 *
	 * @return string
	 */
	public function getLocale() {
		return $this->locale;
	}


	/**
	 * Gets the path to the instance's language file.
	 *
	 * @return string
	 */
	public function getLanguageFile() {
		return $this->language_file;
	}


	/**
	 * Gets the linked instance or null if none defined.
	 *
	 * @return MessageFormat|null
	 */
	public function getLink() {
		return $this->link;
	}


	/**
	 * Gets the raw value of a message.
	 *
	 * @param string $message_key
	 *
	 * @return string
	 */
	public function get($message_key) {
		$this->ensureLoaded();

		// Check for a section key, which uses a period as a separator.
		$separator = strpos($message_key, '.');
		if (false === $separator) {
			if (isset($this->messages[$message_key])) {
				return $this->messages[$message_key];
			}

			if (isset($this->link)) {
				return $this->link->get($message_key);
			}

			throw new \InvalidArgumentException('Unknown key "' . $message_key . '".');
		}

		$section = substr($message_key, 0, $separator);
		$sub_key = substr($message_key, $separator + 1);

		if (isset($this->messages[$section][$sub_key])) {
			return $this->messages[$section][$sub_key];
		}

		if (isset($this->link)) {
			return $this->link->get($message_key);
		}

		if (!isset($this->messages[$section])) {
			throw new \InvalidArgumentException('Unknown section "' . $section . '".');
		}

		throw new \InvalidArgumentException('Unknown key "' . $sub_key . '" in section "' . $section . '".');
	}


	/**
	 * Gets the formatted value of a message.
	 *
	 * @param string $message_key
	 * @param array $args
	 *
	 * @return string
	 */
	public function format($message_key, $args) {
		return \MessageFormatter::formatMessage($this->locale, $this->get($message_key), $args);
	}
}
