<?php
namespace Service\Common\Java;

class java_Parser {
		public $parser;
		function __construct($handler) {
				if(function_exists("xml_parser_create")) {
						$this->parser=new java_NativeParser($handler);
						$handler->RUNTIME["PARSER"]="NATIVE";
				} else {
						$this->parser=new java_SimpleParser($handler);
						$handler->RUNTIME["PARSER"]="SIMPLE";
				}
		}

		function parse() {
				$this->parser->parse();
		}

		function getData($str) {
				return $this->parser->getData($str);
		}

		function parserError() {
				$this->parser->parserError();
		}
}
?>