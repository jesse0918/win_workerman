<?php
namespace Service\Common\Java;

class java_NativeParser {
		public $parser,$handler;
		public $level,$event;
		public $buf;

		function __construct($handler) {
				$this->handler=$handler;
				$this->parser=xml_parser_create();
				xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
				xml_set_object($this->parser,$this);
				xml_set_element_handler($this->parser,"begin","end");
				xml_parse($this->parser,"<F>");
				$this->level=0;
		}

		function begin($parser,$name,$param) {
				$this->event=true;
				switch($name) {
						case 'X': case 'A': $this->level+=1;
				}
				$this->handler->begin($name,$param);
		}

		function end($parser,$name) {
				$this->handler->end($name);
				switch($name) {
						case 'X': case 'A': $this->level-=1;
				}
		}

		function getData($str) {
				return base64_decode($str);
		}

		function parse() {
				do {
						$this->event=false;
						$buf=$this->buf=$this->handler->read(JAVA_RECV_SIZE);
						print_r($buf);
						print_r("\r\n");
						$len=strlen($buf);
						if(!xml_parse($this->parser,$buf,$len==0)) {
								$this->handler->protocol->handler->shutdownBrokenConnection(
								sprintf("protocol error: %s,%s at col %d. Check the back end log for OutOfMemoryErrors.",
								$buf,
								xml_error_string(xml_get_error_code($this->parser)),
								xml_get_current_column_number($this->parser)));
						}
				} while(!$this->event || $this->level>0);
		}

		function parserError() {
				$this->handler->protocol->handler->shutdownBrokenConnection(
				sprintf("protocol error: %s. Check the back end log for details.",$this->buf));
		}
}
?>