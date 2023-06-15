<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

abstract class discuz_model extends discuz_base
{

	public $data;

	public $methods = array();

	public $showmessage = 'showmessage';

	public $app;

	public $member;

	public $group;

	public $setting;

	public $param = array();

	public function __construct() {
		$this->app = C::app();
		$this->setting = &$this->app->var['setting'];
		$this->group = &$this->app->var['group'];
		$this->member = &$this->app->var['member'];
		parent::__construct();
	}

	public function config($name) {
		return getglobal('config/'.$name);
	}

	public function setting($name = null, $val = null) {
		if(isset($val)) {
			return $this->setvar($this->setting, $name, $val);
		}
		return $this->getvar($this->setting, $name);
	}

	public function table($name) {
		return C::t($name);
	}

	public function cache($name, $val = null) {
		if(isset($val)) {
			savecache($name, $val);
			$this->app->var['cache'][$name] = $val;
			return true;
		} else {
			if (!isset($this->app->var['cache'][$name])) {
				loadcache($name);
			}
			if($this->app->var['cache'][$name] === null) {
				return null;
			} else {
				return getglobal('cache/'.$name);
			}
		}
	}

	public function member($name = null, $val = null){
		if(isset($val)) {
			return $this->setvar($this->member, $name, $val);
		} else {
			return $this->getvar($this->member, $name);
		}
	}

	public function group($name = null, $val = null){
		if(isset($val)) {
			return $this->setvar($this->group, $name, $val);
		} else {
			return $this->getvar($this->group, $name);
		}
	}

	public function param($name = null, $val = null){
		if(isset($val)) {
			return $this->setvar($this->param, $name, $val);
		}
		return $this->getvar($this->param, $name);
	}

	public function setvar(&$var, $key, $value) {
		if(isset($key)) {
			$key = explode('/', $key);
			$p = &$var;
			foreach ($key as $k) {
				if(!isset($p[$k]) || !is_array($p[$k])) {
					$p[$k] = array();
				}
				$p = &$p[$k];
			}
			$p = $value;
		} else {
			$var = $value;
		}
		return true;
	}

	public function getvar(&$var, $key = null) {
		if(isset($key)) {
			$key = explode('/', $key);
			foreach ($key as $k) {
				if (!isset($var[$k])) {
					return null;
				}
				$var = &$var[$k];
			}
		}
		return $var;
	}


	public function showmessage() {
		if(!empty($this->showmessage) && is_callable($this->showmessage)) {
			$p = func_get_args();
			if(is_string($this->showmessage)) {
				$fn = $this->showmessage;
				switch (func_num_args()) {
					case 0:	return $fn();break;
					case 1:	return $fn($p[0]);break;
					case 2:	return $fn($p[0], $p[1]);break;
					case 3:	return $fn($p[0], $p[1], $p[2]);exit;break;
					case 4:	return $fn($p[0], $p[1], $p[2], $p[3]);break;
					case 5:	return $fn($p[0], $p[1], $p[2], $p[3], $p[4]);break;
					default: return call_user_func_array($this->showmessage, $p);break;
				}
			} else {
				return call_user_func_array($this->showmessage, $p);
			}
		} else {
			return func_get_args();
		}
	}

	public function attach_before_method($name, $fn) {
		$this->methods[$name][0][] = $fn;
	}

	public function attach_after_method($name, $fn) {
		$this->methods[$name][1][] = $fn;
	}

	public function attach_before_methods($name, $methods){
		if(!empty($methods)) {
			foreach($methods as $method) {
				$this->methods[$name][0][] = $method;
			}
		}
	}

	public function attach_after_methods($name, $methods){
		if(!empty($methods)) {
			foreach($methods as $method) {
				$this->methods[$name][1][] = $method;
			}
		}
	}

	abstract protected function _init_parameters($parameters);

}
?>