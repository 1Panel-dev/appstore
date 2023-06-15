<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_json.php 32779 2013-03-08 02:57:37Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_json {

	public static function encode($data) {
		switch ($type = gettype($data)) {
			case 'NULL':
				return 'null';
			case 'boolean':
				return ($data ? 'true' : 'false');
			case 'integer':
			case 'double':
			case 'float':
				return $data;
			case 'string':
				return '"' . addcslashes($data, "\r\n\t\"") . '"';
			case 'object':
				$data = get_object_vars($data);
			case 'array':
				$count = 0;
				$indexed = array();
				$associative = array();
				foreach ($data as $key => $value) {
					if($count !== NULL && (gettype($key) !== 'integer' || $count++ !== $key)) {
						$count = NULL;
					}
					$one = self::encode($value);
					$indexed[] = $one;
					$associative[] = self::encode($key) . ':' . $one;
				}
				if ($count !== NULL) {
					return '[' . implode(',', $indexed) . ']';
				} else {
					return '{' . implode(',', $associative) . '}';
				}
			default:
			return ''; // Not supported
		}
	}
}

?>