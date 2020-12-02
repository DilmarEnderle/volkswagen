<?php

class Template
{
	public $body;

	public function __construct($aFilename)
	{
		$this->body = implode("", file(HTML_DIR . $aFilename));
	}

	public function replace($aWhat, $aWith)
	{
		$this->body = str_replace($aWhat, $aWith, $this->body);
	}
}

?>
