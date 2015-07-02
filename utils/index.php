<?php

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

class Tree {

	var $root = null;
	var $currentNode = null;
	
	function __construct(Node $root=null) {
		if( $root == null ) {
			$this->root = new Node();
		}
		else {
			$this->root = $root;
		}
	}
	
	function open($type=null, $value=null, $vars=null) {
		$node = new Node($type, $value, $vars);
		
		if( $this->currentNode == null ) {
			if( $this->root != null ) {
				$this->currentNode = $this->root;
			}
		}
		
		if($type == "*" && $this->currentNode->getType() == "*") {
			$this->close("*");
		}
		
		$this->currentNode->append($node);
		$node->setParent($this->currentNode);
		$this->currentNode = $node;
	}
	
	function close($type) {
		$node = $this->currentNode;
		
		if( $type == "list") {
			if( $node->getType() !== "*" && $node->getType() !== "list")
				throw new Exception("list cannot be closed for non-* or non-list elements.");
			
			if( $node->getType() === "*") {
				$node->close();
				$node = $node->getParent();
			}
		}
		
		if( $node != null) {
			if( $node->getType() === $type) {
				$node->close();
				$this->currentNode = $node->getParent();
			}
			else {
				$node->setType("text");
				$this->currentNode = $node->getParent();
				//throw new Exception("You have to close your current element first.");
			}
		} 
		else {
			$this->currentNode = $this->root;
		}
	}
	
	function add($type=null, $value=null, $vars=null) {
		$node = new Node($type, $value, $vars);
		
		if( $this->currentNode == null ) {
			if( $this->root != null ) {
				$this->currentNode = $this->root;
			}
		}
		
		$this->currentNode->append($node);
		$node->setParent($this->currentNode);
		$node->close();
	}
	
	function findParent(Node $node, $type=null) {
		if($node->getType() == $type)
			return $node;
		else
			findParent($node->getParent(), $type);
	}
	
	function toXML($file) {
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;

		$root = $doc->createElement("guide");
		$doc->appendChild($root);
		
		foreach($this->root->children() as $child) {
			$child->toXML($doc, $root);
		}
		
		$doc->normalize();
		
		$length = strrpos($file, ".");
		if( $length == 0)
			$length = strlen($file);
		$file = substr($file, 0, $length) . ".xml";
		$doc->save($file);
		return $doc;
	}
	
}


class Node {

	var $type = null;
	var $value = null;
	var $vars = null;
	var $children = array();
	var $parentNode = null;
	var $isClosed = false;

	function __construct($type=null, $value=null, $vars=null) {
		$this->type = $type;
		$this->value = $value;
		$this->vars = $vars;
		$this->children = array();
	}
	
	function setParent(Node $parent) {
		$this->parentNode = $parent;
	}
	
	function getParent() {
		return $this->parentNode;
	}
	
	function close() {
		$this->isClosed = true;
	}
	
	function isClosed() {
		return $this->isClosed;
	}
	
	function getType() {
		return $this->type;
	}
	
	function setType($type) {
		$this->type = $type;
	}
	
	function getValue() {
		return $this->value;
	}
	
	function append($child) {
		$this->children[] = $child;
	}
	
	function children() {
		return $this->children;
	}
	
	function toXML($doc, $root) {
		if( $this->type === "*") {
			$element = $doc->createElement("star");
		} else {
			$element = $doc->createElement($this->type);
		}
		
		$element->setAttribute("value", $this->value);
		
		if($this->vars !== null) {
			foreach($this->vars as $vars) {
				$element->setAttribute($vars[1], $vars[2]);
			}
		}
		
		$root->appendChild($element);
		
		foreach($this->children() as $child) {
			$child->toXML($doc, $element);
		}
	}
	
}
?>



