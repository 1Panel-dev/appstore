<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_antitheft.php 33494 2013-06-26 05:26:25Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_antitheft {

	public static function check($id, $idtype) {
		if((!isset($_GET['_dsign']) || $_GET['_dsign'] !== ($_dsign = dsign($id.$idtype, 8))) && !self::check_allow($id, $idtype)) {
			if(!isset($_dsign)) {
				$_dsign = dsign($id.$idtype, 8);
			}
			echo self::make_content($id, $idtype, $_dsign);exit;
		}
	}

	public static function get_sign($id, $idtype) {
		return !self::check_allow($id, $idtype) ? dsign($id.$idtype, 8) : '';
	}

	protected static function check_allow($id, $idtype) {
		global $_G;
		$ip = ip2long($_G['clientip']);
		if(!$ip || $ip == -1) return false;
		if($ip < 0) {
			$ip = sprintf('%u', $ip);
		}
		loadcache('antitheft');
		$antitheft = $_G['cache']['antitheft'];

		if(isset($antitheft['white'])){
			if(in_array($ip, (array)$antitheft['white']['single'], true)) {
				return true;
			}
			foreach($antitheft['white']['range'] as $_ip) {
				if($ip > $_ip['min'] && $ip < $_ip['max']) return true;
			}
		}

		if(isset($antitheft['black'])){
			if(in_array($ip, (array)$antitheft['black']['single'], true)) {
				return false;
			}
			foreach($antitheft['black']['range'] as $_ip) {
				if($ip > $_ip['min'] && $ip < $_ip['max']) return false;
			}
		}
		if(!($log = C::t('common_visit')->fetch($ip))) {
			C::t('common_visit')->insert(array(
				'ip' => $ip,
				'view' => 1,
			));
			return true;
		} elseif($log['view'] >= $_G['setting']['antitheft']['max']) {
			return false;
		} else {
			C::t('common_visit')->inc($ip);
			return true;
		}

	}

	protected static function make_content($id, $idtype, $dsign) {
		$url = '';
		$urls = parse_url($_SERVER['REQUEST_URI']);
		$addstr = $urls['query'] ? $urls['query'].'&' : '';
		$url = $urls['path'].'?'.$addstr.'_dsign='.$dsign.($urls['fragment'] ? '#'.$urls['fragment'] : '');

		return self::make_js($url);
	}

	protected static function make_js($url){
		$js = '<script type="text/javascript">';
		$varname = array();
		$codes = array();
		$window = '_'.random(5);
		$location = '_'.random(5);
		$href = '_'.random(5);
		$replace = '_'.random(5);
		$assign = '_'.random(5);
		$codes[$window] = "$window = window;";
		$codes[$location] = "$location = location;";
		$codes[$href] = "$href = 'href';";
		$codes[$replace] = "$replace = 'replace';";
		$codes[$assign] = "$assign = 'assign';";
		$codes['getname'] = 'function getName(){var caller=getName.caller;if(caller.name){return caller.name} var str=caller.toString().replace(/[\s]*/g,"");var name=str.match(/^function([^\(]+?)\(/);if(name && name[1]){return name[1];} else {return \'\';}}';
		$jskeywords = array('for' => '', 'case' => '', 'if' => '', 'else' => '', 'try'  => '', 'new' => '', 'eval' => '', 'var' => ''); //js关键字
		$methods = array(1,2,3,4,5,6,7);
		$lenths = array(2,2,3,4);
		for($i = 0, $l = strlen($url); $i < $l; $i++) {
			$len = $lenths[array_rand($lenths)];
			$cflag = $len % 2;
			$var = random($len);
			if(ctype_digit($var[0])) {
				$var = '_'.$var;
			}
			while(isset($varname[$var])) {
				$var = random(3);
				if(ctype_digit($var[0])) {
					$var = '_'.$var;
				}
			}
			$val = substr($url, $i, $len-1);
			$i = $i + $len - 2;
			switch ($methods[array_rand($methods)]) {
				case 1:
					if($cflag) {
						$varname[$var] = "'$val'";
					} else {
						$codes[] = "$var='$val';";
						$varname[$var] = $var;
					}
					break;
				case 2:
					if(!isset($jskeywords[$val]) && ctype_alnum($val) && !ctype_digit($val[0])) {
						$codes[] = "function $var({$var}_){function $val(){return getName();};return $val();return '{$var}'}";
						$varname[$var] = "$var('".random($len)."')";
					} else {
						$codes[] = "function $var(){'return $var';return '$val'}";
						$varname[$var] = $var.'()';
					}
					break;
				case 3:
					if($cflag) {
						$codes[] = "$var=function({$var}_){'return $var';return {$var}_;};";
						$varname[$var] = "$var('$val')";
					} else {
						$codes[] = "$var=function(){'return $var';return '$val';};";
						$varname[$var] = "$var()";
					}
					break;
				case 4:
					if($cflag) {
						$varname[$var] = "(function({$var}_){'return $var';return {$var}_})('$val')";
					} else {
						$varname[$var] = "(function(){'return $var';return '$val'})()";
					}
					break;
				case 5:
					if(!isset($jskeywords[$val]) && ctype_alnum($val) && !ctype_digit($val[0])) {
						$codes[] = "function $var({$var}_){function _{$var[0]}({$var}_){function $val(){return getName();}function {$var}_(){}return $val();return {$var}_}; return _{$var[0]}({$var}_);}";
						$varname[$var] = "$var('".random($len)."')";
					} else {
						$codes[] = "function $var(){'$var';function _{$var[0]}(){return '$val'}; return _{$var[0]}();}";
						$varname[$var] = $var.'()';
					}
					break;
				case 6:
					if($cflag) {
						$codes[] = "$var=function({$var}_){var _{$var[0]}=function({$var}_){'return $var';return {$var}_;}; return _{$var[0]}({$var}_);};";
						$varname[$var] = "$var('$val')";
					} else {
						$codes[] = "$var=function(){'$var';var _{$var[0]}=function(){return '$val'}; return _{$var[0]}();};";
						$varname[$var] = $var.'()';
					}
					break;
				case 7:
					if($cflag) {
						$varname[$var] = "(function({$var}_){return (function({$var}_){return {$var}_;})({$var}_);})('$val')";
					} else {
						$varname[$var] = "(function(){'return $var';return (function(){return '$val';})();})()";
					}
					break;
			}
		}
		shuffle($codes);
		$js .= implode('', $codes);
		$hrefheader = array('location.href=', 'location=', "{$location}[$href]=", "location[$href]=",
					'location.replace(', 'location.assign(', "location[$assign](", "location[$replace](");
		$hreffooter = array('','','','',')',')',')',')');
		$index = array_rand($hrefheader);
		$js .= $hrefheader[$index]. implode('+', $varname).$hreffooter[$index].';';
		$fix = array("{$window}[$href]=", "{$window}['href']=", "{$window}.href=");
		$js .= $fix[array_rand($fix)].implode('+', array_slice($varname, 0, 8)).';';
		$js .= '</script>';
		return $js;
	}

}

?>