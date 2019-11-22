<?php
namespace Service\Common\Java;

class java_ApplyArg extends java_CompositeArg {
		public $m,$p,$v,$n;
		function __construct($client,$type,$m,$p,$v,$n) {
				parent::__construct($client,$type);
				$this->m=$m;
				$this->p=$p;
				$this->v=$v;
				$this->n=$n;
		}
}
?>