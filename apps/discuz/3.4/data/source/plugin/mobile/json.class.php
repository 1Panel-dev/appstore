<?php
if(!defined('IN_MOBILE_API') && !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class CJSON {
	const JSON_SLICE = 1;
	const JSON_IN_STR = 2;
	const JSON_IN_ARR = 4;
	const JSON_IN_OBJ = 8;
	const JSON_IN_CMT = 16;

	static function encode($var) {
		global $_G;
		switch (gettype($var)) {
			case 'boolean':
				return $var ? 'true' : 'false';
			case 'NULL':
				return 'null';
			case 'integer':
				return (int) $var;
			case 'double':
			case 'float':
				return rtrim(sprintf('%.16F',$var),'0');
			case 'string':
				if(function_exists('diconv') && strtolower($_G['charset']) != 'utf-8') {
					$var = diconv($var, $_G['charset'], 'utf-8');
				}
				if(function_exists('json_encode')) {
					return json_encode($var);
				}
				$ascii = '';
				$strlen_var = strlen($var);
				for ($c = 0; $c < $strlen_var; ++$c) {
					$ord_var_c = ord($var[$c]);
					switch (true) {
						case $ord_var_c == 0x08:
							$ascii .= '\b';
							break;
						case $ord_var_c == 0x09:
							$ascii .= '\t';
							break;
						case $ord_var_c == 0x0A:
							$ascii .= '\n';
							break;
						case $ord_var_c == 0x0C:
							$ascii .= '\f';
							break;
						case $ord_var_c == 0x0D:
							$ascii .= '\r';
							break;

						case $ord_var_c == 0x22:
						case $ord_var_c == 0x2F:
						case $ord_var_c == 0x5C:
							$ascii .= '\\'.$var[$c];
							break;

						case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
							$ascii .= $var[$c];
							break;

						case (($ord_var_c & 0xE0) == 0xC0):
							$char = pack('C*', $ord_var_c, ord($var[$c+1]));
							$c+=1;
							$utf16 =  self::utf8ToUTF16BE($char);
							$ascii .= sprintf('\u%04s', bin2hex($utf16));
							break;

						case (($ord_var_c & 0xF0) == 0xE0):
							$char = pack('C*', $ord_var_c,
										 ord($var[$c+1]),
										 ord($var[$c+2]));
							$c+=2;
							$utf16 = self::utf8ToUTF16BE($char);
							$ascii .= sprintf('\u%04s', bin2hex($utf16));
							break;

						case (($ord_var_c & 0xF8) == 0xF0):
							$char = pack('C*', $ord_var_c,
										 ord($var[$c+1]),
										 ord($var[$c+2]),
										 ord($var[$c+3]));
							$c+=3;
							$utf16 = self::utf8ToUTF16BE($char);
							$ascii .= sprintf('\u%04s', bin2hex($utf16));
							break;

						case (($ord_var_c & 0xFC) == 0xF8):
							$char = pack('C*', $ord_var_c,
										 ord($var[$c+1]),
										 ord($var[$c+2]),
										 ord($var[$c+3]),
										 ord($var[$c+4]));
							$c+=4;
							$utf16 = self::utf8ToUTF16BE($char);
							$ascii .= sprintf('\u%04s', bin2hex($utf16));
							break;

						case (($ord_var_c & 0xFE) == 0xFC):
							$char = pack('C*', $ord_var_c,
										 ord($var[$c+1]),
										 ord($var[$c+2]),
										 ord($var[$c+3]),
										 ord($var[$c+4]),
										 ord($var[$c+5]));
							$c+=5;
							$utf16 = self::utf8ToUTF16BE($char);
							$ascii .= sprintf('\u%04s', bin2hex($utf16));
							break;
					}
				}

				return '"'.$ascii.'"';

			case 'array':
				if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
					return '{' .
						   join(',', array_map(array('CJSON', 'nameValue'),
											   array_keys($var),
											   array_values($var)))
						   . '}';
				}

				return '[' . join(',', array_map(array('CJSON', 'encode'), $var)) . ']';
			case 'object':
				if ($var instanceof Traversable)
				{
					$vars = array();
					foreach ($var as $k=>$v)
						$vars[$k] = $v;
				}
				else
					$vars = get_object_vars($var);
				return '{' .
					   join(',', array_map(array('CJSON', 'nameValue'),
										   array_keys($vars),
										   array_values($vars)))
					   . '}';

			default:
				return '';
		}
	}

	static function nameValue($name, $value) {
		return self::encode(strval($name)) . ':' . self::encode($value);
	}

	static function reduceString($str) {
		$str = preg_replace(array(

				'#^\s*//(.+)$#m',

				'#^\s*/\*(.+)\*/#Us',

				'#/\*(.+)\*/\s*$#Us'

			), '', $str);

		return trim($str);
	}

	static function decode($str, $useArray=true) {
		if(function_exists('json_decode')) {
			return json_decode($str, $useArray);
            }

		$str = self::reduceString($str);

		switch (strtolower($str)) {
			case 'true':
				return true;

			case 'false':
				return false;

			case 'null':
				return null;

			default:
				if (is_numeric($str)) {
					return ((float)$str == (integer)$str)
						? (integer)$str
						: (float)$str;

				} elseif (preg_match('/^("|\').+(\1)$/s', $str, $m) && $m[1] == $m[2]) {

					$delim = substr($str, 0, 1);
					$chrs = substr($str, 1, -1);
					$utf8 = '';
					$strlen_chrs = strlen($chrs);

					for ($c = 0; $c < $strlen_chrs; ++$c) {

						$substr_chrs_c_2 = substr($chrs, $c, 2);
						$ord_chrs_c = ord($chrs[$c]);

						switch (true) {
							case $substr_chrs_c_2 == '\b':
								$utf8 .= chr(0x08);
								++$c;
								break;
							case $substr_chrs_c_2 == '\t':
								$utf8 .= chr(0x09);
								++$c;
								break;
							case $substr_chrs_c_2 == '\n':
								$utf8 .= chr(0x0A);
								++$c;
								break;
							case $substr_chrs_c_2 == '\f':
								$utf8 .= chr(0x0C);
								++$c;
								break;
							case $substr_chrs_c_2 == '\r':
								$utf8 .= chr(0x0D);
								++$c;
								break;

							case $substr_chrs_c_2 == '\\"':
							case $substr_chrs_c_2 == '\\\'':
							case $substr_chrs_c_2 == '\\\\':
							case $substr_chrs_c_2 == '\\/':
								if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
								   ($delim == "'" && $substr_chrs_c_2 != '\\"')) {
									$utf8 .= $chrs[++$c];
								}
								break;

							case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
								$utf16 = chr(hexdec(substr($chrs, ($c+2), 2)))
									   . chr(hexdec(substr($chrs, ($c+4), 2)));
								$utf8 .= self::utf16beToUTF8($utf16);
								$c+=5;
								break;

							case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
								$utf8 .= $chrs[$c];
								break;

							case ($ord_chrs_c & 0xE0) == 0xC0:
								$utf8 .= substr($chrs, $c, 2);
								++$c;
								break;

							case ($ord_chrs_c & 0xF0) == 0xE0:
								$utf8 .= substr($chrs, $c, 3);
								$c += 2;
								break;

							case ($ord_chrs_c & 0xF8) == 0xF0:
								$utf8 .= substr($chrs, $c, 4);
								$c += 3;
								break;

							case ($ord_chrs_c & 0xFC) == 0xF8:
								$utf8 .= substr($chrs, $c, 5);
								$c += 4;
								break;

							case ($ord_chrs_c & 0xFE) == 0xFC:
								$utf8 .= substr($chrs, $c, 6);
								$c += 5;
								break;

						}

					}

					return $utf8;

				} elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {

					if ($str[0] == '[') {
						$stk = array(self::JSON_IN_ARR);
						$arr = array();
					} else {
						if ($useArray) {
							$stk = array(self::JSON_IN_OBJ);
							$obj = array();
						} else {
							$stk = array(self::JSON_IN_OBJ);
							$obj = new stdClass();
						}
					}

					array_push($stk, array('what'  => self::JSON_SLICE,
										   'where' => 0,
										   'delim' => false));

					$chrs = substr($str, 1, -1);
					$chrs = self::reduceString($chrs);

					if ($chrs == '') {
						if (reset($stk) == self::JSON_IN_ARR) {
							return $arr;

						} else {
							return $obj;

						}
					}

					$strlen_chrs = strlen($chrs);

					for ($c = 0; $c <= $strlen_chrs; ++$c) {

						$top = end($stk);
						$substr_chrs_c_2 = substr($chrs, $c, 2);

						if (($c == $strlen_chrs) || (($chrs[$c] == ',') && ($top['what'] == self::JSON_SLICE))) {

							$slice = substr($chrs, $top['where'], ($c - $top['where']));
							array_push($stk, array('what' => self::JSON_SLICE, 'where' => ($c + 1), 'delim' => false));
							if (reset($stk) == self::JSON_IN_ARR) {
								array_push($arr, self::decode($slice,$useArray));

							} elseif (reset($stk) == self::JSON_IN_OBJ) {
								if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
									$key = self::decode($parts[1],$useArray);
									$val = self::decode($parts[2],$useArray);

									if ($useArray) {
										$obj[$key] = $val;
									} else {
										$obj->$key = $val;
									}
								} elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
									$key = $parts[1];
									$val = self::decode($parts[2],$useArray);

									if ($useArray) {
										$obj[$key] = $val;
									} else {
										$obj->$key = $val;
									}
								}

							}

						} elseif ((($chrs[$c] == '"') || ($chrs[$c] == "'")) && ($top['what'] != self::JSON_IN_STR)) {
							array_push($stk, array('what' => self::JSON_IN_STR, 'where' => $c, 'delim' => $chrs[$c]));
						} elseif (($chrs[$c] == $top['delim']) &&
								 ($top['what'] == self::JSON_IN_STR) &&
								 (($chrs[$c - 1] != "\\") ||
								 ($chrs[$c - 1] == "\\" && $chrs[$c - 2] == "\\"))) {
							array_pop($stk);
						} elseif (($chrs[$c] == '[') &&
								 in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
							array_push($stk, array('what' => self::JSON_IN_ARR, 'where' => $c, 'delim' => false));
						} elseif (($chrs[$c] == ']') && ($top['what'] == self::JSON_IN_ARR)) {
							array_pop($stk);
						} elseif (($chrs[$c] == '{') &&
								 in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
							array_push($stk, array('what' => self::JSON_IN_OBJ, 'where' => $c, 'delim' => false));
						} elseif (($chrs[$c] == '}') && ($top['what'] == self::JSON_IN_OBJ)) {
							array_pop($stk);
						} elseif (($substr_chrs_c_2 == '/**') &&
								 in_array($top['what'], array(self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ))) {
							array_push($stk, array('what' => self::JSON_IN_CMT, 'where' => $c, 'delim' => false));
							$c++;
						} elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::JSON_IN_CMT)) {
							array_pop($stk);
							$c++;
							for ($i = $top['where']; $i <= $c; ++$i) {
								$chrs = substr_replace($chrs, ' ', $i, 1);
                                          }
						}

					}

					if (reset($stk) == self::JSON_IN_ARR) {
						return $arr;

					} elseif (reset($stk) == self::JSON_IN_OBJ) {
						return $obj;

					}

				}
		}
	}


	static function utf8ToUnicode( &$str ) {
		$unicode = array();
		$values = array();
		$lookingFor = 1;

		for ($i = 0; $i < strlen( $str ); $i++ ) {
			$thisValue = ord( $str[ $i ] );
			if ( $thisValue < 128 ) {
				$unicode[] = $thisValue;
                  } else {
				if ( count( $values ) == 0 ) {
					$lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                        }
				$values[] = $thisValue;
				if ( count( $values ) == $lookingFor ) {
					$number = ( $lookingFor == 3 ) ?
						( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
						( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
					$unicode[] = $number;
					$values = array();
					$lookingFor = 1;
				}
			}
		}
		return $unicode;
	}

	static function unicodeToUTF8( &$str )
	{
		$utf8 = '';
		foreach( $str as $unicode )
		{
			if ( $unicode < 128 )
			{
				$utf8.= chr( $unicode );
			}
			elseif ( $unicode < 2048 )
			{
				$utf8.= chr( 192 +  ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			}
			else
			{
				$utf8.= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
				$utf8.= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
				$utf8.= chr( 128 + ( $unicode % 64 ) );
			}
		}
		return $utf8;
	}

	static function utf8ToUTF16BE(&$str, $bom = false) {
		$out = $bom ? "\xFE\xFF" : '';
		if(function_exists('mb_convert_encoding'))
			return $out.mb_convert_encoding($str,'UTF-16BE','UTF-8');

		$uni = self::utf8ToUnicode($str);
		foreach($uni as $cp)
			$out .= pack('n',$cp);
		return $out;
	}

	static function utf16beToUTF8(&$str) {
		$uni = unpack('n*',$str);
		return self::unicodeToUTF8($uni);
	}
}