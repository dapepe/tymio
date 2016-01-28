<?php

/**
 * Ring buffer for most recent named items.
 *
 * @author Huy Hoang Nguyen <hnguyen@cms-it.de>
 * @copyright Copyright (C) 2009 - 2011, CMS IT-Consulting GmbH. All rights reserved.
 * @package PROCABS
 */

/**
 * Implements a ring buffer structure where multiple key/value pairs can be stored.
 *
 * If the buffer exceeds a specified size, the oldest item will be removed.
 */
class RecentList {

	/**
	 * The maximum size of the buffer.
	 *
	 * @var int
	 */
	private $size;

	/**
	 * The current number of items in the buffer.
	 *
	 * @var int
	 */
	private $count = 0;

	/**
	 * An associative array with the key/value pairs in the order of their use.
	 * First element is the least, last element is the most recently added.
	 */
	private $items = array();

	/**
	 * Creates a new instance.
	 *
	 * @param int $size The number of items to retain.
	 * @return void
	 */
	public function __construct($size) {
		if ( $size < 1 )
			throw new Exception('Size must be at least 1.');
		$this->size = $size;
	}

	/**
	 * Returns the number of items in the list.
	 *
	 * @return int
	 */
	public function count() {
		return $this->count;
	}

	/**
	 * Checks whether the specified item exists.
	 *
	 * @param mixed $name
	 * @return bool
	 */
	public function exists($name) {
		return array_key_exists($name, $this->items);
	}

	/**
	 * Returns the value corresponding to the specified key.
	 *
	 * @param mixed $name
	 * @return mixed
	 * @see exists()
	 */
	public function get($name, $default = null) {
		return ( array_key_exists($name, $this->items) ? $this->items[$name] : $default );
	}

	/**
	 * Removes all items.
	 *
	 * @return void
	 */
	public function clear() {
		$this->count = 0;
		$this->items = array();
	}

	/**
	 * Sets a key/value pair.
	 *
	 * @param mixed $name The key (must be a string or an int).
	 * @return int The current number of items.
	 */
	public function set($name, $value) {
		// Remove a previously existing item with the same key
		if ( array_key_exists($name, $this->items) ) {
			unset($this->items[$name]);
			$this->count--;
		}

		// Check whether buffer size is exceeded
		if ( $this->count < $this->size ) {
			$this->count++;
		} else {
			// Number of elements is not changed here:
			// Oldest item is removed while a new one was added.
			array_shift($this->items);
		}

		// Item will be added to the end of the list (most recent)
		$this->items[$name] = $value;

		return $this->count;
	}

	/**
	 * Makes the specified key/value pair the most recent item.
	 * This function is almost identical to calling
	 * <code>$recentList->set($name, $recentList->get($name));</code>, with
	 * some additional error checking.
	 *
	 * @param mixed $name
	 * @return bool Returns TRUE if the item exists and has been updated,
	 *     otherwise FALSE.
	 */
	public function touch($name) {
		if ( !array_key_exists($name, $this->items) )
			return false;

		$this->set($name, $this->get($name));
		return true;
	}

	/**
	 * Removes the specified item.
	 *
	 * @param mixed $name
	 * @return bool Returns TRUE if the item previously existed and has
	 *     been removed, otherwise FALSE.
	 */
	public function delete($name) {
		if ( !array_key_exists($name, $this->items) )
			return false;

		unset($this->items[$name]);
		$this->count--;

		return true;
	}

}

?>
