<?php

include_once(dirname(__FILE__) . "/../utils/index.php");

$tree = new Tree();

// complete list of valid tokens
$tokenlist = array("title", "icon", "br", "u", "i", "b", "*", "img", "size", "important", "list", "code", "quote", "url", "alert", "panel", "video", "wowdb", "header");

class Parser {

	function parse($tokenstream) {
		$result = $this->parseToken($tokenstream);
	}
	
	function parseToken($tokenstream = array()) {
		if($tokenstream === null || sizeof($tokenstream) <= 0)
			return false;
			
		$token = array_shift($tokenstream);
		$result = $this->extract($token);
		
		//var_dump($result);
		
		if( $result === false ) {
			// create a text node
			global $tree;
			$tree->add("text", $token);
		}
		else {
			global $tree;
			if( $result["key"] === "br" || $result["key"] === "icon") {
				if( $result["closing"] === false ) {
					$tree->add($result["key"], $token, $result["vars"]);
				}
			}
			else {
				if( $result["closing"] === false ) {
					$tree->open($result["key"], $token, $result["vars"]);
				}
				else {
					$tree->close($result["key"]);
				}
			}
		}
		
		$this->parseToken($tokenstream);
	}
	
	function extract($token) {
		$isKeyword = preg_match("/^(\[(\/)?(\w|\*)+((\,.+\=.*)*)?\])$/i", $token) > 0;
		
		if( !$isKeyword)
			return false;

		$isClosing = preg_match("/^(\[\/(\w|\*)+((\,.+\=.*)*)?\])$/i", $token) > 0;
		
		preg_match("/^(\[(\/)?(?P<keyword>(\w|\*)+)(?P<vars>((\,(.)+\=(.)*)*)?)\])$/i", $token, $result);
		$key = $result["keyword"];
		$vars = $result["vars"];
		
		global $tokenlist;
		if( !in_array($key, $tokenlist) ) {
			return false;
		}
		
		$variables = array();
		
		preg_match_all('/\,(\s*)(?P<key>(\w|\d)+)(\s*)\=(\s*)(?P<value>([^(\,|\"|\'|\´|\`)])*)(\s*)/i', $vars, $matches);
		if(sizeof($matches["key"]) > 0) {
			$keys = $matches["key"];
			$values = $matches["value"];
			
			$i = 0;
			while($i < sizeof($keys)) {
				$variables[] = array("=", trim($keys[$i]), trim($values[$i]));
				$i++;
			}
		}
		
		return array("key"=>$key, "closing"=>$isClosing, "vars"=>$variables);
	}

}

?>