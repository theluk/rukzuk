<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Array tools library.
 *
 * @author     David Grudl
 */
class Arrays
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Returns item from array or $default if item is not set.
	 * @param  array
	 * @param  string|int|array one or more keys
	 * @param  mixed
	 * @return mixed
	 * @throws Nette\InvalidArgumentException if item does not exist and default value is not provided
	 */
	public static function get(array $arr, $key, $default = NULL)
	{
		foreach (is_array($key) ? $key : array($key) as $k) {
			if (is_array($arr) && array_key_exists($k, $arr)) {
				$arr = $arr[$k];
			} else {
				if (func_num_args() < 3) {
					throw new Nette\InvalidArgumentException("Missing item '$k'.");
				}
				return $default;
			}
		}
		return $arr;
	}


	/**
	 * Returns reference to array item.
	 * @param  array
	 * @param  string|int|array one or more keys
	 * @return mixed
	 * @throws Nette\InvalidArgumentException if traversed item is not an array
	 */
	public static function & getRef(& $arr, $key)
	{
		foreach (is_array($key) ? $key : array($key) as $k) {
			if (is_array($arr) || $arr === NULL) {
				$arr = & $arr[$k];
			} else {
				throw new Nette\InvalidArgumentException('Traversed item is not an array.');
			}
		}
		return $arr;
	}


	/**
	 * Recursively appends elements of remaining keys from the second array to the first.
	 * @return array
	 */
	public static function mergeTree($arr1, $arr2)
	{
		$res = $arr1 + $arr2;
		foreach (array_intersect_key($arr1, $arr2) as $k => $v) {
			if (is_array($v) && is_array($arr2[$k])) {
				$res[$k] = self::mergeTree($v, $arr2[$k]);
			}
		}
		return $res;
	}


	/**
	 * Searches the array for a given key and returns the offset if successful.
	 * @return int|FALSE offset if it is found, FALSE otherwise
	 */
	public static function searchKey($arr, $key)
	{
		$foo = array($key => NULL);
		return array_search(key($foo), array_keys($arr), TRUE);
	}


	/**
	 * Inserts new array before item specified by key.
	 * @return void
	 */
	public static function insertBefore(array & $arr, $key, array $inserted)
	{
		$offset = self::searchKey($arr, $key);
		$arr = array_slice($arr, 0, $offset, TRUE) + $inserted + array_slice($arr, $offset, count($arr), TRUE);
	}


	/**
	 * Inserts new array after item specified by key.
	 * @return void
	 */
	public static function insertAfter(array & $arr, $key, array $inserted)
	{
		$offset = self::searchKey($arr, $key);
		$offset = $offset === FALSE ? count($arr) : $offset + 1;
		$arr = array_slice($arr, 0, $offset, TRUE) + $inserted + array_slice($arr, $offset, count($arr), TRUE);
	}


	/**
	 * Renames key in array.
	 * @return void
	 */
	public static function renameKey(array & $arr, $oldKey, $newKey)
	{
		$offset = self::searchKey($arr, $oldKey);
		if ($offset !== FALSE) {
			$keys = array_keys($arr);
			$keys[$offset] = $newKey;
			$arr = array_combine($keys, $arr);
		}
	}


	/**
	 * Returns array entries that match the pattern.
	 * @return array
	 */
	public static function grep(array $arr, $pattern, $flags = 0)
	{
		return Strings::pcre('preg_grep', array($pattern, $arr, $flags));
	}


	/**
	 * Returns flattened array.
	 * @return array
	 */
	public static function flatten(array $arr, $preserveKeys = FALSE)
	{
		$res = array();
		$cb = $preserveKeys
			? function($v, $k) use (& $res) { $res[$k] = $v; }
			: function($v) use (& $res) { $res[] = $v; };
		array_walk_recursive($arr, $cb);
		return $res;
	}


	/**
	 * Finds whether a variable is a zero-based integer indexed array.
	 * @return bool
	 */
	public static function isList($value)
	{
		return is_array($value) && (!$value || array_keys($value) === range(0, count($value) - 1));
	}


	/**
	 * Reformats table to associative tree. Path looks like 'field|field[]field->field=field'.
	 * @return array|\stdClass
	 */
	public static function associate(array $arr, $path)
	{
		$parts = is_array($path)
			? $path
			: preg_split('#(\[\]|->|=|\|)#', $path, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		if (!$parts || $parts[0] === '=' || $parts[0] === '|' || $parts === array('->')) {
			throw new Nette\InvalidArgumentException("Invalid path '$path'.");
		}

		$res = $parts[0] === '->' ? new \stdClass : array();

		foreach ($arr as $rowOrig) {
			$row = (array) $rowOrig;
			$x = & $res;

			for ($i = 0; $i < count($parts); $i++) {
				$part = $parts[$i];
				if ($part === '[]') {
					$x = & $x[];

				} elseif ($part === '=') {
					if (isset($parts[++$i])) {
						$x = $row[$parts[$i]];
						$row = NULL;
					}

				} elseif ($part === '->') {
					if (isset($parts[++$i])) {
						$x = & $x->{$row[$parts[$i]]};
					} else {
						$row = is_object($rowOrig) ? $rowOrig : (object) $row;
					}

				} elseif ($part !== '|') {
					$x = & $x[(string) $row[$part]];
				}
			}

			if ($x === NULL) {
				$x = $row;
			}
		}

		return $res;
	}

}
