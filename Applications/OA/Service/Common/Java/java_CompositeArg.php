<?php
namespace Service\Common\Java;

class java_CompositeArg extends java_Arg {
		public $parentArg;
		public $idx;
		public $type;
		public $counter;

		function __construct($client,$type) {
				parent::__construct($client);
				$this->type=$type;
				$this->val=array();
				$this->counter=0;
		}

		function setNextIndex() {
				$this->idx=$this->counter++;
		}

		function setIndex($val) {
				$this->idx=$val;
		}

		function linkResult(&$val) {
				$this->val[$this->idx]=&$val;
		}

		function setResult($val) {
				$this->val[$this->idx]=$this->factory->getProxy($val,$this->signature,$this->exception,true);
				$this->factory=$this->client->simpleFactory;
		}
}
?>