<?php

if(!defined('IN_DISCUZ') || (PHP_VERSION < '7.0.0')) {
	exit('Access Denied');
}

function dpreg_replace_impl($pattern, $replacement, $subject, $limit, &$count) {
	$eval = false;
	$delimiter = $pattern[0];

	if(($position_of_modifier = (int) strrpos($pattern, $delimiter)) > 0) {
		if(($position_of_eval = strpos($pattern, 'e', $position_of_modifier)) !== false) {
			$eval = true;
			$pattern = substr($pattern, 0, $position_of_eval).substr($pattern, $position_of_eval + 1);
		}
	}

	if($eval) {
		return preg_replace_callback($pattern, function($matches) use ($replacement) {
			$result = '';
			$instance = false;

			for($i = 0, $j = strlen($replacement); $i < $j; $i ++) {
				if(($replacement[$i] == '$') || ($replacement[$i] == '\\')) {
					if((($i == 0) || ($replacement[$i - 1] != '\\')) && isset($replacement[$i + 1])) {
						$get_backref_string = substr($replacement, $i + 1);

						if($get_backref_string[0] == '{') {
							$get_backref_result = preg_match('/^\{([0-9]{1,2})}/', $get_backref_string, $get_backref_match);
						} else {
							$get_backref_result = preg_match('/^([0-9]{1,2})/', $get_backref_string, $get_backref_match);
						}

						if($get_backref_result) {
							$backref = $get_backref_match[1];

							if(isset($matches[$backref])) {
								$result .= addslashes($matches[$backref]);
							}

							$i += strlen($get_backref_match[0]);

							continue;
						} else {
							if(preg_match('/^this([^a-z0-9_]+)/i', $get_backref_string)) {
								$result .= '$_';
								$instance = true;

								continue;
							}
						}
					}
				}

				$result .= $replacement[$i];
			}

			if($instance) {
				$_this = null;
				@$stack = (array) debug_backtrace();

				if(isset($stack[6]) && isset($stack[6]['object'])) {
					if(is_object($stack[6]['object'])) {
						$_this = $stack[6]['object'];
					}
				}
			}

			return eval("return {$result};");
		}, $subject, $limit, $count);
	} else {
		return preg_replace($pattern, $replacement, $subject, $limit, $count);
	}
}

function dpreg_replace_in_subject($pattern, $replacement, $subject, $limit, &$count) {
	if(is_array($pattern)) {
		if(is_array($replacement)) {
			reset($replacement);
		} else {
			$replacement_value = $replacement;
		}

		foreach($pattern as $pattern_value) {
			if(is_array($replacement)) {
				if(key($replacement) === null) {
					$replacement_value = '';
				} else {
					$replacement_value = current($replacement);
					next($replacement);
				}
			}

			if(($subject = dpreg_replace_impl($pattern_value, $replacement_value, $subject, $limit, $count)) === null) {
				return null;
			}
		}

		return $subject;
	} else {
		return dpreg_replace_impl($pattern, $replacement, $subject, $limit, $count);
	}
}

function _dpreg_replace($pattern, $replacement, $subject, $limit = -1, &$count = null) {
	if(is_array($replacement) && !is_array($pattern))
		return preg_replace($pattern, $replacement, $subject, $limit, $count);

	if(is_array($subject)) {
		$result = array();

		foreach($subject as $subject_key => $subject_value) {
			$result[$subject_key] = dpreg_replace_in_subject($pattern, $replacement, $subject_value, $limit, $count);
		}

		return $result;
	} else {
		return dpreg_replace_in_subject($pattern, $replacement, $subject, $limit, $count);
	}
}