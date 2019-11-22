<?php
namespace Service\Common\Java;

class java_Client {
		public $RUNTIME;
		public $result,$exception;
		public $parser;
		public $protocol;
		public $simpleArg,$compositeArg;
		public $simpleFactory,
					 $proxyFactory,$iteratorProxyFacroty,
					 $arrayProxyFactory,$exceptionProxyFactory,$throwExceptionProxyFactory;
		public $arg;
		public $asyncCtx,$cancelProxyCreationCounter;
		public $globalRef;
		public $stack;
		public $defaultCache=array(),$asyncCache=array(),$methodCache;
		public $isAsync=0;
		public $currentCacheKey,$currentArgumentsFormat;
		public $cachedJavaPrototype;
		public $sendBuffer,$preparedToSendBuffer;
		public $inArgs;

		public function __construct() {
				$this->RUNTIME=array();
				$this->RUNTIME["NOTICE"]='***USE echo java_inspect(jVal) OR print_r(java_values(jVal)) TO SEE THE CONTENTS OF THIS JAVA OBJECT!***';
				$this->parser=new java_Parser($this);
				$this->protocol=new java_Protocol($this);
				$this->simpleFactory=new java_SimpleFactory($this);
				$this->proxyFactory=new java_ProxyFactory($this);
				$this->arrayProxyFactory=new java_ArrayProxyFactory($this);
				$this->iteratorProxyFactory=new java_IteratorProxyFactory($this);
				$this->exceptionProxyFactory=new java_ExceptionProxyFactory($this);
				$this->throwExceptionProxyFactory=new java_ThrowExceptionProxyFactory($this);
				$this->cachedJavaPrototype=new java_JavaProxyProxy($this);
				$this->simpleArg=new java_Arg($this);
				$this->globalRef=new java_GlobalRef();
				$this->asyncCtx=$this->cancelProxyCreationCounter=0;
				$this->methodCache=$this->defaultCache;
				$this->inArgs=false;
		}

		function read($size) {
				return $this->protocol->read($size);
		}

		function setDefaultHandler() {
				$this->methodCache=$this->defaultCache;
		}

		function setAsyncHandler() {
				$this->methodCache=$this->asyncCache;
		}

		function handleRequests() {
				$tail_call=false;
				do {
						$this->stack=array($this->arg=$this->simpleArg);
						$this->idx=0;
						$this->parser->parse();
						if((count($this->stack)) > 1) {
								$arg=array_pop($this->stack);
								$this->apply($arg);
								$tail_call=true;
						} else {
								$tail_call=false;
						}
						$this->stack=null;
				} while($tail_call);
				return 1;
		}

		function getWrappedResult($wrap) {
				return $this->simpleArg->getResult($wrap);
		}

		function getInternalResult() {
				return $this->getWrappedResult(false);
		}

		function getResult() {
				return $this->getWrappedResult(true);
		}

		function getProxyFactory($type) {
				switch($type[0]) {
					case 'E':
						$factory=$this->exceptionProxyFactory;
						break;
					case 'C':
						$factory=$this->iteratorProxyFactory;
						break;
					case 'A':
						$factory=$this->arrayProxyFactory;
						break;
					default:
					case 'O':
						$factory=$this->proxyFactory;
				}
				return $factory;
		}

		function link(&$arg,&$newArg) {
				$arg->linkResult($newArg->val);
				$newArg->parentArg=$arg;
		}

		function getExact($str) {
				return hexdec($str);
		}

		function getInexact($str) {
				$val=null;
				sscanf($str,"%e",$val);
				return $val;
		}

		function begin($name,$st) {
				$arg=$this->arg;
				switch($name[0]) {
						case 'A':
								$object=$this->globalRef->get($this->getExact($st['v']));
								$newArg=new java_ApplyArg($this,'A',
																					$this->parser->getData($st['m']),
																					$this->parser->getData($st['p']),
																					$object,
																					$this->getExact($st['n']));
																					$this->link($arg,$newArg);
								array_push($this->stack,$this->arg=$newArg);
								break;
						case 'X':
								$newArg=new java_CompositeArg($this,$st['t']);
								$this->link($arg,$newArg);
								array_push($this->stack,$this->arg=$newArg);
								break;
						case 'P':
								if($arg->type=='H') {
										$s=$st['t'];
										if($s[0]=='N') {
												$arg->setIndex($this->getExact($st['v']));
										} else {
												$arg->setIndex($this->parser->getData($st['v']));
										}
								} else {
										$arg->setNextIndex();
								}
								break;
						case 'S':
								$arg->setResult($this->parser->getData($st['v']));
								break;
						case 'B':
								$s=$st['v'];
								$arg->setResult($s[0]=='T');
								break;
						case 'L':
								$sign=$st['p'];
								$val=$this->getExact($st['v']);
								if($sign[0]=='A') $val*=-1;
								$arg->setResult($val);
								break;
						case 'D':
								$arg->setResult($this->getInexact($st['v']));
								break;
						case 'V':
								if ($st['n']!='T') {
										$arg->setVoidSignature();
								}
						case 'N':
								$arg->setResult(null);
								break;
						case 'F':
								break;
						case 'O':
								$arg->setFactory($this->getProxyFactory($st['p']));
								$arg->setResult($this->asyncCtx=$this->getExact($st['v']));
								if($st['n']!='T') $arg->setSignature($st['m']);
								break;
						case 'E':
								$arg->setFactory($this->throwExceptionProxyFactory);
								$arg->setException($st['m']);
								$arg->setResult($this->asyncCtx=$this->getExact($st['v']));
								break;
						default:
								$this->parser->parserError();
				}
		}

		function end($name) {
				switch($name[0]) {
						case 'X':
								$frame=array_pop($this->stack);
								$this->arg=$frame->parentArg;
								break;
				}
		}

		function createParserString() {
				return new java_ParserString();
		}

		function writeArg($arg) {
				if(is_string($arg)) {
						$this->protocol->writeString($arg);
				} else if(is_object($arg)) {
						if ((!$arg instanceof java_JavaType)) {
								error_log((string)new java_IllegalArgumentException($arg));
								trigger_error("argument '".get_class($arg)."' is not a Java object,using NULL instead",E_USER_WARNING);
								$this->protocol->writeObject(null);
						} else {
								$this->protocol->writeObject($arg->__java);
						}
				} else if(is_null($arg)) {
						$this->protocol->writeObject(null);
				} else if(is_bool($arg)) {
						$this->protocol->writeBoolean($arg);
				} else if(is_integer($arg)) {
						$this->protocol->writeLong($arg);
				} else if(is_float($arg)) {
						$this->protocol->writeDouble($arg);
				} else if(is_array($arg)) {
						$wrote_begin=false;
						foreach($arg as $key=>$val) {
								if(is_string($key)) {
										if(!$wrote_begin) {
												$wrote_begin=1;
												$this->protocol->writeCompositeBegin_h();
										}
										$this->protocol->writePairBegin_s($key);
										$this->writeArg($val);
										$this->protocol->writePairEnd();
								} else {
										if(!$wrote_begin) {
												$wrote_begin=1;
												$this->protocol->writeCompositeBegin_h();
										}
										$this->protocol->writePairBegin_n($key);
										$this->writeArg($val);
										$this->protocol->writePairEnd();
								}
						}
						if(!$wrote_begin) {
								$this->protocol->writeCompositeBegin_a();
						}
						$this->protocol->writeCompositeEnd();
				}
		}

		function writeArgs($args) {
				$this->inArgs=true;
				$n=count($args);
				for($i=0; $i<$n; $i++) {
						$this->writeArg($args[$i]);
				}
				$this->inArgs=false;
		}

		function createObject($name,$args) {
				$this->protocol->createObjectBegin($name);
				$this->writeArgs($args);
				$this->protocol->createObjectEnd();
				$val=$this->getInternalResult();
				return $val;
		}

		function referenceObject($name,$args) {
				$this->protocol->referenceBegin($name);
				$this->writeArgs($args);
				$this->protocol->referenceEnd();
				$val=$this->getInternalResult();
				return $val;
		}

		function getProperty($object,$property) {
				$this->protocol->propertyAccessBegin($object,$property);
				$this->protocol->propertyAccessEnd();
				return $this->getResult();
		}

		function setProperty($object,$property,$arg) {
				$this->protocol->propertyAccessBegin($object,$property);
				$this->writeArg($arg);
				$this->protocol->propertyAccessEnd();
				$this->getResult();
		}

		function invokeMethod($object,$method,$args) {
				$this->protocol->invokeBegin($object,$method);
				$this->writeArgs($args);
				$this->protocol->invokeEnd();
				$val=$this->getResult();
				return $val;
		}

		function unref($object) {
				if (isset($this->protocol)) $this->protocol->writeUnref($object);
		}

		function apply($arg) {
				$name=$arg->p;
				$object=$arg->v;
				$ob=($object==null) ? $name : array(&$object,$name);
				$isAsync=$this->isAsync;
				$methodCache=$this->methodCache;
				$currentArgumentsFormat=$this->currentArgumentsFormat;
				try {
						$res=$arg->getResult(true);
						if((($object==null) && !function_exists($name)) || (!($object==null) && !method_exists($object,$name))) throw new JavaException("java.lang.NoSuchMethodError","$name");
						$res=call_user_func_array($ob,$res);
						if (is_object($res) && (!($res instanceof java_JavaType))) {
								trigger_error("object returned from $name() is not a Java object",E_USER_WARNING);
								$this->protocol->invokeBegin(0,"makeClosure");
								$this->protocol->writeULong($this->globalRef->add($res));
								$this->protocol->invokeEnd();
								$res=$this->getResult();
						}
						$this->protocol->resultBegin();
						$this->writeArg($res);
						$this->protocol->resultEnd();
				} catch (JavaException $e) {
						$trace=$e->getTraceAsString();
						$this->protocol->resultBegin();
						$this->protocol->writeException($e->__java,$trace);
						$this->protocol->resultEnd();
				} catch(Exception $ex) {
						error_log($ex->__toString());
						trigger_error("Unchecked exception detected in callback",E_USER_ERROR);
						die (1);
				}
				$this->isAsync=$isAsync;
				$this->methodCache=$methodCache;
				$this->currentArgumentsFormat=$currentArgumentsFormat;
		}

		function cast($object,$type) {
				switch($type[0]) {
						case 'S': case 's':
								return $this->invokeMethod(0,"castToString",array($object));
						case 'B': case 'b':
								return $this->invokeMethod(0,"castToBoolean",array($object));
						case 'L': case 'I': case 'l': case 'i':
								return $this->invokeMethod(0,"castToExact",array($object));
						case 'D': case 'd': case 'F': case 'f':
								return $this->invokeMethod(0,"castToInExact",array($object));
						case 'N': case 'n':
								return null;
						case 'A': case 'a':
								return $this->invokeMethod(0,"castToArray",array($object));
						case 'O': case 'o':
								return $object;
						default:
								throw new java_RuntimeException("$type illegal");
				}
		}

		function getContext() {
				static $cache=null;
				if (!is_null($cache)) return $cache;
				return $cache=$this->invokeMethod(0,"getContext",array());
		}

		function getSession($args) {
				return $this->invokeMethod(0,"getSession",$args);
		}

		function getServerName() {
				static $cache=null;
				if (!is_null($cache)) return $cache;
				return $cache=$this->protocol->getServerName();
		}
}
?>