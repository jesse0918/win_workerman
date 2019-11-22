<?php
namespace Service\Common\Java;

class java_Arg {
		public $client;
		public $exception;
		public $factory,$val;
		public $signature;

		function __construct($client) {
				$this->client=$client;
				$this->factory=$client->simpleFactory;
		}

		function linkResult(&$val) {
				$this->val=&$val;
		}

		function setResult($val) {
				$this->val=&$val;
		}

		function getResult($wrap) {
				$rc=$this->factory->getProxy($this->val,$this->signature,$this->exception,$wrap);
				$factory=$this->factory;
				$this->factory=$this->client->simpleFactory;
				$factory->checkResult($rc);
				return $rc;
		}

		function setFactory($factory) {
				$this->factory=$factory;
		}

		function setException($string) {
				$this->exception=$string;
		}

		function setVoidSignature() {
				$this->signature="@V";
				$key=$this->client->currentCacheKey;
				if($key && $key[0]!='~') {
						$this->client->currentArgumentsFormat[6]="3";
						$cacheEntry=new java_CacheEntry($this->client->currentArgumentsFormat,$this->signature,$this->factory,true);
						$this->client->methodCache[$key]=$cacheEntry;
				}
		}

		function setSignature($signature) {
				$this->signature=$signature;
				$key=$this->client->currentCacheKey;
				if($key && $key[0]!='~') {
								$cacheEntry=new java_CacheEntry($this->client->currentArgumentsFormat,$this->signature,$this->factory,false);
								$this->client->methodCache[$key]=$cacheEntry;
				}
		}
}
?>