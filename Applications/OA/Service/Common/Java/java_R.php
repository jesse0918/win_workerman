<?php
namespace Service\Common\Java;

class java_R extends java_AbstractJava {
function java_R() {
$client=$this->__client=__javaproxy_Client_getClient();
$args=func_get_args();
$name=array_shift($args);
if(is_array($name)) {$args=$name; $name=array_shift($args);}
$sig="&{$this->__signature}@{$name}";
$len=count($args);
$args2=array();
for($i=0; $i<$len; $i++) {
switch(gettype($val=$args[$i])) {
case 'boolean': array_push($args2,$val); $sig.='@b'; break;
case 'integer': array_push($args2,$val); $sig.='@i'; break;
case 'double': array_push($args2,$val); $sig.='@d'; break;
case 'string': array_push($args2,htmlspecialchars($val,ENT_COMPAT)); $sig.='@s'; break;
case 'array':$sig="~INVALID"; break;
case 'object':
if($val instanceof java_JavaType) {
array_push($args2,$val->__java);
$sig.="@o{$val->__signature}";
}
else {
$sig="~INVALID";
}
break;
case 'resource': array_push($args2,$val); $sig.='@r'; break;
case 'NULL': array_push($args2,$val); $sig.='@N'; break;
case 'unknown type': array_push($args2,$val); $sig.='@u'; break;
default: throw new java_IllegalArgumentException($val);
}
}
if(array_key_exists($sig,$client->methodCache)) {
$cacheEntry=&$client->methodCache[$sig];
$client->sendBuffer.=$client->preparedToSendBuffer;
if(strlen($client->sendBuffer)>=JAVA_SEND_SIZE) {
if($client->protocol->handler->write($client->sendBuffer)<=0)
throw new java_IllegalStateException("Connection out of sync,check backend log for details.");
$client->sendBuffer=null;
}
$client->preparedToSendBuffer=vsprintf($cacheEntry->fmt,$args2);
$this->__java=++$client->asyncCtx;
$this->__factory=$cacheEntry->factory;
$this->__signature=$cacheEntry->signature;
$this->__cancelProxyCreationTag=++$client->cancelProxyCreationTag;
} else {
$client->currentCacheKey=$sig;
$delegate=$this->__delegate=$client->createObject($name,$args);
$this->__java=$delegate->__java;
$this->__signature=$delegate->__signature;
}
}
function __destruct() {
if(!isset($this->__client)) return;
$client=$this->__client;
$preparedToSendBuffer=&$client->preparedToSendBuffer;
if($preparedToSendBuffer &&
$client->cancelProxyCreationTag==$this->__cancelProxyCreationTag) {
$preparedToSendBuffer[6]="3";
$client->sendBuffer.=$preparedToSendBuffer;
$preparedToSendBuffer=null;
$client->asyncCtx -=1;
} else {
if(!isset($this->__delegate)) {
$client->unref($this->__java);
}
}
}
function __call($method,$args) {
$client=$this->__client;
$sig="@{$this->__signature}@$method";
$len=count($args);
$args2=array($this->__java);
for($i=0; $i<$len; $i++) {
switch(gettype($val=$args[$i])) {
case 'boolean': array_push($args2,$val); $sig.='@b'; break;
case 'integer': array_push($args2,$val); $sig.='@i'; break;
case 'double': array_push($args2,$val); $sig.='@d'; break;
case 'string': array_push($args2,htmlspecialchars($val,ENT_COMPAT)); $sig.='@s'; break;
case 'array':$sig="~INVALID"; break;
case 'object':
if($val instanceof java_JavaType) {
array_push($args2,$val->__java);
$sig.="@o{$val->__signature}";
}
else {
$sig="~INVALID";
}
break;
case 'resource': array_push($args2,$val); $sig.='@r'; break;
case 'NULL': array_push($args2,$val); $sig.='@N'; break;
case 'unknown type': array_push($args2,$val); $sig.='@u'; break;
default: throw new java_IllegalArgumentException($val);
}
}
if(array_key_exists($sig,$client->methodCache)) {
$cacheEntry=&$client->methodCache[$sig];
$client->sendBuffer.=$client->preparedToSendBuffer;
if(strlen($client->sendBuffer)>=JAVA_SEND_SIZE) {
if($client->protocol->handler->write($client->sendBuffer)<=0)
throw new java_IllegalStateException("Out of sync. Check backend log for details.");
$client->sendBuffer=null;
}
$client->preparedToSendBuffer=vsprintf($cacheEntry->fmt,$args2);
if($cacheEntry->resultVoid) {
$client->cancelProxyCreationTag +=1;
return null;
} else {
$result=clone($client->cachedJavaPrototype);
$result->__factory=$cacheEntry->factory;
$result->__java=++$client->asyncCtx;
$result->__signature=$cacheEntry->signature;
$result->__cancelProxyCreationTag=++$client->cancelProxyCreationTag;
return $result;
}
} else {
$client->currentCacheKey=$sig;
$retval=parent::__call($method,$args);
return $retval;
}
}
}
?>