<?php
namespace Service\Common\Java;

class java_ParserString {
public $string,$off,$length;
function toString() {
return $this->getString();
}
function getString() {
return substr($this->string,$this->off,$this->length);
}
}
?>