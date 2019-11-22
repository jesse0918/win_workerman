<?php
namespace Service\Common\Java;


class java_GlobalRef {
		public $map;

		function __construct() {
				$this->map=array();
		}

		function add($object) {
				if(is_null($object)) return 0;
				return array_push($this->map,$object);
		}

		function get($id) {
				if(!$id) return 0;
				return $this->map[--$id];
		}
}
?>