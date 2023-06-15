<?php

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

class extends_data {

	public $id;
	public $title;
	public $image;
	public $icon;
	public $poptype;
	public $popvalue;
	public $clicktype;
	public $clickvalue;
	public $field;

	public $list = array();
	public $page = 1;
	public $perpage = 50;

	public function __construct() {

	}

	public function common() {

	}

	public function insertrow() {
		$this->list[] = array(
			'id' => $this->id,
			'title' => $this->title,
			'image' => $this->image,
			'icon' => $this->icon,
			'poptype' => $this->poptype,
			'popvalue' => $this->popvalue,
			'clicktype' => $this->clicktype,
			'clickvalue' => $this->clickvalue,
			'fields' => $this->field,
		);
		$this->field = array();
	}

	public function field($id, $icon, $value) {
		$this->field[] = array('id' => $id, 'icon' => $icon, 'value' => $value);
	}

	public function output() {
		return array(
			__CLASS__ => array('page' => $this->page, 'perpage' => $this->perpage, 'list' => $this->list)
		);
	}
}
?>