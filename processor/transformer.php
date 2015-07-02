<?php

include_once(dirname(__FILE__) . "/../utils/index.php");

class Transformer {

	var $xml = null;
	var $uri = null;
	
	var $id = 0;
	
	function __construct($uri) {
		$this->xml = new DOMDocument('1.0', 'utf-8');
		$this->xml->load($uri);
		$this->xml->normalize();
		$this->uri = $uri;
	}
	
	function transform() {
		$html = new DOMDocument('1.0', 'utf-8');
		$html->preserveWhiteSpace = false;
		$html->formatOutput = true;
		
		$head = $html->createElement("head");
		$title = $html->createElement("title");
		$body = $html->createElement("body");
		
		$head->appendChild($title);
		$html->appendChild($head);
		$html->appendChild($body);
		
		$root = $this->xml->getElementsByTagName("guide")->item(0);
		$this->transformNode($root, $html, $body);
		
		$info = pathinfo($this->uri);
		$file = $info["dirname"]. "/" . $info["filename"] . ".html";
		
		$title->appendChild( $html->createTextNode( $info["filename"] ) );
		
		$html->save($file);
		return $html->saveHTML();
	}
	
	function transformNode($xmlRoot, $html, $root, $options=array()) {
		$e = null;
		$children = $xmlRoot->childNodes;
		
		if( $children === null || $children->length === 0)
			return;
		
		foreach($children as $child) {
			switch($child->nodeName) {
			case "title":
				$this->transformTitle($child, $html, $root, $options);
				break;
			case "b":
				$this->transformBold($child, $html, $root, $options);
				break;
			case "u":
				$this->transformUnderlined($child, $html, $root, $options);
				break;
			case "i":
				$this->transformItalic($child, $html, $root, $options);
				break;
			case "size":
				$this->transformSize($child, $html, $root, $options);
				break;
			case "list":
				$this->transformList($child, $html, $root, $options);
				break;
			case "text":
				$this->transformText($child, $html, $root, $options);
				break;	
			case "url":
				$this->transformURL($child, $html, $root, $options);
				break;
			case "img":
				$this->transformImage($child, $html, $root, $options);
				break;
			case "video":
				$this->transformVideo($child, $html, $root, $options);
				break;
			case "important":
				$this->transformImportant($child, $html, $root, $options);
				break;
			case "code":
				$this->transformCode($child, $html, $root, $options);
				break;
			case "quote":
				$this->transformQuote($child, $html, $root, $options);
				break;
			case "icon":
				$this->transformIcon($child, $html, $root, $options);
				break;
			case "br":
				$this->transformBreak($child, $html, $root, $options);
				break;
			case "alert":
				$this->transformAlert($child, $html, $root, $options);
				break;
			case "panel":
				$this->transformPanel($child, $html, $root, $options);
				break;
			case "wowdb":
				$this->transformWoWDB($child, $html, $root, $options);
				break;
			case "header":
				$this->transformHeader($child, $html, $root, $options);
				break;
			default:
			}
		}
		
	}
	
	function transformList($xmlRoot, $html, $root, $options=array()) {
		$type = $xmlRoot->getAttribute("type");
				
		if( $type === null || empty($type))
			$type = "default";
					
		$isValid = preg_match("/^(numeric|tab|default|media|column|row|table)$/i", $type) > 0;
		
		// recover list type
		if( !$isValid)
			$type = "default";
			
		$options["list-type"] = $type;
		
		$e = null;
		
		switch($type) {
		case "table":
			$e = $html->createElement("table");
			$e->setAttribute("class", "table table-striped");
			
			$columns = $xmlRoot->getAttribute("columns");
			if( !isset($columns) || $columns === null || empty($columns))
				return;
				
			$maxWidth = intval($columns);
			$width = 0;
			
			$titles = $xmlRoot->getAttribute("title");
			if( !empty($titles) ) {
				$titles = explode(";", $titles);
				$header = $html->createElement("thead");
				
				if( sizeof($titles) > 0) {
					foreach($titles as $title) {
						$th = $html->createElement("th");
						$th->appendChild( $html->createTextNode($title) ); 
						$header->appendChild( $th );
					}
					$e->appendChild($header);
				}
			}
			
			$children = $xmlRoot->childNodes;
			
			if( $children === null || $children->length === 0)
				return;

			$root->appendChild($e);
			$body = $html->createElement("tbody");
			$e->appendChild($body);
			
			$row = $html->createElement("tr");
			$body->appendChild($row);
			foreach($children as $child) {
				if( $child->nodeName === "star") {
					if( $width >= $maxWidth) {
						$row = $html->createElement("tr");
						$body->appendChild($row);
						$width = 0;
					}
					$this->transformStar($child, $html, $row, $options);
					$width++;
				}
			}
			
			for($i = $width; $i < $maxWidth; $i++) {
				$row->appendChild( $html->createElement("td") );
			}
			
			return;
		
		case "row":
			$e = $html->createElement("div");
			$e->setAttribute("class", "row");
			
			$fix = $html->createElement("div");
			$fix->setAttribute("class", "clearfix");
			
			$root->appendChild($e);
			$root->appendChild($fix);
		
			$children = $xmlRoot->childNodes;
			
			if( $children === null || $children->length === 0)
				return;
				
			foreach($children as $child) {
				if( $child->nodeName === "star") {
					$this->transformStar($child, $html, $e, $options);
				}
			}
			return;
		case "column":
			$e = $html->createElement("div");
			$e->setAttribute("class", "row");
			
			$fix = $html->createElement("div");
			$fix->setAttribute("class", "clearfix");
			
			$root->appendChild($e);
			$root->appendChild($fix);
		
			$children = $xmlRoot->childNodes;
			
			if( $children === null || $children->length === 0)
				return;
				
			$length = 0;
			foreach($children as $child) {
				if( $child->nodeName === "star") {
					$length++;
				}
			}
			$options["children-length"] = $length;
			
			$i = 0;
			foreach($children as $child) {
				if( $child->nodeName === "star") {
					$options["child-index"] = $i++;
					$this->transformStar($child, $html, $e, $options);
				}
			}
			return;
		case "media":
			$e = $html->createElement("div");
			$e->setAttribute("style", "padding-top: 10px;");
			break;
		case "tab":
			$e = $html->createElement("div");
			$e->setAttribute("role", "tabpanel");
			
			$ul = $html->createElement("ul");
			$ul->setAttribute("class", "nav nav-tabs nav-justified");
			$ul->setAttribute("role", "tablist");
			
			$body = $html->createElement("div");
			$body->setAttribute("class", "tab-content");
			$body->setAttribute("style", "border: 1px solid #ddd; border-top:0; padding-top: 15px; padding-bottom: 15px;");
			
			$e->appendChild($ul);
			$e->appendChild($body);
			$root->appendChild($e);
			
			$options["tab-header"] = $ul;
		
			$children = $xmlRoot->childNodes;
			
			if( $children === null || $children->length === 0)
				return;
			
			$i = 0;
			foreach($children as $child) {
				if( $child->nodeName === "star") {
					$options["child-index"] = $i++;
					$this->transformStar($child, $html, $body, $options);
				}
			}
			
			return;
		case "default":
			$e = $html->createElement("ul");
			break;
		case "numeric":
			$e = $html->createElement("ol");
			break;
		default:
			$e = $html->createElement("ul");
		}
		
		$root->appendChild($e);
		
		$children = $xmlRoot->childNodes;
		
		if( $children === null || $children->length === 0)
			return;
		
		foreach($children as $child) {
			if( $child->nodeName === "star") {
				$this->transformStar($child, $html, $e, $options);
			}
		}
	}
	
	function transformTitle($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("class", "page-header");
		
		$h = $html->createElement("h1");
		
		if( isset($options["text-size"]) ) {

		switch( $options["text-size"] ) {
			case "x-small":
				$h = $html->createElement("h6");
				break;
			case "small":
				$h = $html->createElement("h5");
				break;
			case "normal":
				$h = $html->createElement("h4");
				break;
			case "large":
				$h = $html->createElement("h3");
				break;
			case "x-large":
				$h = $html->createElement("h2");
				break;
			case "xx-large":
				$h = $html->createElement("h1");
				break;
			default:
			}
		}
		
		$e->appendChild($h);
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $h, $options);
	}
	
	function transformBold($xmlRoot, $html, $root, $options=array()) {
		if( !isset($options["text-style"]) )
			$options["text-style"] = array();
			
		$options["text-style"][] = "bold";

		$this->transformNode($xmlRoot, $html, $root, $options);
	}
	
	function transformUnderlined($xmlRoot, $html, $root, $options=array()) {
		if( !isset($options["text-style"]) )
			$options["text-style"] = array();
			
		$options["text-style"][] = "underlined";
		
		$this->transformNode($xmlRoot, $html, $root, $options);
	}

	function transformItalic($xmlRoot, $html, $root, $options=array()) {
		if( !isset($options["text-style"]) )
			$options["text-style"] = array();
			
		$options["text-style"][] = "italic";
		
		$this->transformNode($xmlRoot, $html, $root, $options);
	}
	
	function transformSize($xmlRoot, $html, $root, $options=array()) {
		$size = $xmlRoot->getAttribute("type");
		
		switch( $size ) {
		case "x-small":
		case "small":
		case "normal":
		case "large":
		case "x-large":
		case "xx-large":
			$options["text-size"] = $size;
			break;
		default:
		}

		$this->transformNode($xmlRoot, $html, $root, $options);
	}
	
	function transformText($xmlRoot, $html, $root, $options=array()) {
		$text = $xmlRoot->getAttribute("value");

		$styles = "";

		if( isset($options["text-style"]) ) {
		
			if( in_array("bold", $options["text-style"]) ) {
				$styles .= "font-weight: bold;";
			}
			if( in_array("underlined", $options["text-style"]) ) {
				$styles .= "text-decoration: underline;";
			}
			if( in_array("italic", $options["text-style"]) ) {
				$styles .= "font-style: italic;";
			}
		
		}
		
		if( isset($options["text-size"]) ) {
			$size = "14px";

			switch( $options["text-size"] ) {
			case "x-small":
				$size = "10px";
				break;
			case "small":
				$size = "12px";
				break;
			case "normal":
				$size = "14px";
				break;
			case "large":
				$size = "18px";
				break;
			case "x-large":
				$size = "20px";
				break;
			case "xx-large":
				$size = "24px";
				break;
			default:
			}
			
			$styles .= "font-size: " . $size . ";";
		}
		
		if( empty($styles) ) {
			$e = $html->createTextNode($text);
		} 
		else {
			$e = $html->createElement("font");
			$e->appendChild( $html->createTextNode($text) );
			$e->setAttribute("style", $styles);
		}

		$root->appendChild($e);
	}
	
	function transformStar($xmlRoot, $html, $root, $options=array()) {
		$e = null;

		if( isset($options["list-type"]) ) {
			switch($options["list-type"]) {
			case "table":
				$e = $html->createElement("td");
				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $e, $options);
				break;
			case "row":
				$e = $html->createElement("div");
				$e->setAttribute("class", "col-xs-12");
				
				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $e, $options);
				break;
			case "column":
				$e = $html->createElement("div");
				
				$length = intval($options["children-length"]);
				$width = 100 / $length;
				
				$e->setAttribute("style", "position:relative; padding-left:15px; padding-right:15px; min-height:1px; float:left; width:" . $width . "%;");
				
				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $e, $options);
				break;
			case "media":
				$e = $html->createElement("div");
				$e->setAttribute("class", "media");
				
				$left = $html->createElement("div");
				$left->setAttribute("class", "media-left media-middle");
				
				$body = $html->createElement("div");
				$body->setAttribute("class", "media-body");
				$header = $html->createElement("h4");
				$header->setAttribute("class", "media-heading");
				
				$title = $xmlRoot->getAttribute("title");
				if( !empty($title) && $title !== null) {
					$header->appendChild( $html->createTextNode($title) );
				}
					
				$src = $xmlRoot->getAttribute("img");
				if( !empty($src) && $src !== null) {
					$img = $html->createElement("img");
					$img->setAttribute("class", "media-object thumbnail");
					$img->setAttribute("src", $src);
					$img->setAttribute("style", "max-width: 100px; height: auto;");
					$img->setAttribute("alt", $src);
					$left->appendChild($img);
				}
				
				$e->appendChild($left);
				$body->appendChild($header);
				$e->appendChild($body);

				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $body, $options);
				break;
			case "tab":
				$index = $options["child-index"];
				if( !isset($index) || $index === null )
					$index = 0;
				
				$e = $html->createElement("div");
				if($index === 0)
					$e->setAttribute("class", "tab-pane active");
				else
					$e->setAttribute("class", "tab-pane");
				
				
				$header = $options["tab-header"];
				$title = $xmlRoot->getAttribute("title");
				$id = "tab" . ($this->id++);
				
				if( !empty($title) && $title !== null) {
					$li = $html->createElement("li");
					$li->setAttribute("role", "presentation");
					if($index === 0)
						$li->setAttribute("class", "active");
					
					$a = $html->createElement("a");
					$a->setAttribute("role", "tab");
					$a->setAttribute("aria-controls", "" . $id);
					if( $index === 0)
						$a->setAttribute("aria-expanded", "true");
					$a->setAttribute("href", "#" . $id);
					$a->setAttribute("data-toggle", "tab");
					$a->appendChild( $html->createTextNode($title) );
					
					$li->appendChild($a);
					
					if( isset($header) && $header !== null)
						$header->appendChild($li);
				}
				
				$e->setAttribute("id", "" . $id);
				
				$body = $html->createElement("div");
				$body->setAttribute("class", "container-fluid");
				$e->appendChild($body);
				
				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $body, $options);
				break;
				
			case "default":
			case "numeric":
			default:
				$e = $html->createElement("li");
				$root->appendChild($e);
				$this->transformNode($xmlRoot, $html, $e, $options);
				break;
			}
		}
		
	}
	
	function transformURL($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("a");
		
		$uri = $xmlRoot->getAttribute("uri");
		if( $uri === null || $uri === "")
				return;
				
		$text = $uri;
		
		if( $xmlRoot->childNodes !== null && $xmlRoot->childNodes->length !== 0) {
		
			foreach($xmlRoot->childNodes as $child) {
				if( $child->nodeName === "text") {
					$text = $child->getAttribute("value");
				}
			}

		}
		
		$styles = "";
		if( isset($options["text-style"]) ) {
		
			if( in_array("bold", $options["text-style"]) ) {
				$styles = $styles . "font-weight: bold;";
			}
			if( in_array("italic", $options["text-style"]) ) {
				$styles = $styles . "font-style: italic;";
			}
		
		}
		
		if( isset($options["text-size"]) ) {
			$size = "14px";

			switch( $options["text-size"] ) {
			case "x-small":
				$size = "10px";
				break;
			case "small":
				$size = "12px";
				break;
			case "normal":
				$size = "14px";
				break;
			case "large":
				$size = "18px";
				break;
			case "x-large":
				$size = "20px";
				break;
			case "xx-large":
				$size = "24px";
				break;
			default:
			}
			
			$styles .= "font-size: " . $size . ";";
		}
		

		$e->appendChild( $html->createTextNode($text) );
		$e->setAttribute("href", $uri);
		$e->setAttribute("target", "_blank");
		$e->setAttribute("class", "btn btn-link");
		$e->setAttribute("style", $styles);
		
		$root->appendChild($e);
	}
	
	function transformImage($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("img");
		
		$uri = $xmlRoot->getAttribute("uri");
		if( $uri === null || $uri === "")
				return;
				
		$text = $uri;
		
		if( $xmlRoot->childNodes !== null && $xmlRoot->childNodes->length !== 0) {
		
			foreach($xmlRoot->childNodes as $child) {
				if( $child->nodeName === "text") {
					$text = $child->getAttribute("value");
				}
			}

		}

		$e->setAttribute("src", $uri);
		$e->setAttribute("alt", $text);
		$e->setAttribute("style", "width: 100%;");
		$e->setAttribute("class", "img-responsive");
		
		$root->appendChild($e);
	}
	
	function transformImportant($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("class", "jumbotron");
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $e, $options);
	}
	
	function transformCode($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("class", "highlight");
		
		$p = $html->createElement("div");
		$p->setAttribute("class", "pre");
		
		$c = $html->createElement("code");
		
		$e->appendChild($p);
		$p->appendChild($c);
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $c, $options);
	}
	
	function transformQuote($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("blockquote");
		
		$p = $html->createElement("p");
		$e->appendChild($p);
		
		$source = $xmlRoot->getAttribute("src");
		if( !empty($source) && $source !== null) { 
			$f = $html->createElement("footer");
			$f->appendChild( $html->createTextNode($source) );
			$e->appendChild($f);
		}
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $p, $options);
	}
	
	function transformIcon($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("i");
		
		$type = $xmlRoot->getAttribute("type");
		if( !empty($type) && $type !== null ) {
			$e->setAttribute("class", "fa fa-" . $type);
			$root->appendChild($e);
		}
	}
	
	function transformBreak($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("br");
		$root->appendChild($e);
	}
	
	function transformAlert($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		
		$valid = array("success", "danger", "warning", "info");
		$type = $xmlRoot->getAttribute("type");
		
		if( in_array($type, $valid) ) {
		
			switch($type) {
			case "success":	
				$icon = $html->createElement("i");
				$icon->setAttribute("class", "fa fa-check fa-lg");
				break;
			case "danger":
				$icon = $html->createElement("i");
				$icon->setAttribute("class", "fa fa-exclamation-triangle fa-lg");
				break;
			case "warning":
				$icon = $html->createElement("i");
				$icon->setAttribute("class", "fa fa-question-circle fa-lg");
				break;
			case "info":
				$icon = $html->createElement("i");
				$icon->setAttribute("class", "fa fa-info-circle fa-lg");
				break;
			default:
			}
		
			if( isset($icon) ) {
				$e->appendChild($icon);
			}
			
			$e->setAttribute("class", "alert alert-" . $type);
			$e->setAttribute("role", "alert");
		
		}
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $e, $options);
	}
	
	function transformPanel($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("class", "panel panel-default");
		
		$title = $xmlRoot->getAttribute("title");
		if( !empty($title) && $title !== null) {
			$header = $html->createElement("div");
			$header->setAttribute("class", "panel-heading");
			$header->appendChild( $html->createTextNode($title) );
			$e->appendChild($header);
		}

		$body = $html->createElement("div");
		$body->setAttribute("class", "panel-body");
		$e->appendChild($body);
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $body, $options);
	}

	function transformVideo($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("class", "embed-responsive embed-responsive-16by9");
		
		$uri = $xmlRoot->getAttribute("uri");
		if( $uri === null || $uri === "")
			return;
				
		$text = $uri;
		
		if( $xmlRoot->childNodes !== null && $xmlRoot->childNodes->length !== 0) {
		
			foreach($xmlRoot->childNodes as $child) {
				if( $child->nodeName === "text") {
					$text = $child->getAttribute("value");
				}
			}

		}
		
		$uri = preg_replace("/watch\?v\=/i", "v/", $uri);
		$video = $html->createElement("iframe");
		$video->setAttribute("class", "embed-responsive-item");
		$video->setAttribute("src", $uri);
		$video->setAttribute("alt", $text);
		
		$e->appendChild($video);
		$root->appendChild($e);
	}
	
	function transformWoWDB($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("a");

		$uri = $xmlRoot->getAttribute("uri");
		if( $uri === null || $uri === "")
			return;
		
		$e->setAttribute("rel", $uri);
		$e->setAttribute("href", "http://de.wowhead.com/" . $uri);
		$e->setAttribute("domain", "de");
		
		$root->appendChild($e);
	}
	
	function transformHeader($xmlRoot, $html, $root, $options=array()) {
		$e = $html->createElement("div");
		$e->setAttribute("style", "background:#ccc; padding:15px; margin:-15px -15px 15px -15px;");
		
		$root->appendChild($e);
		$this->transformNode($xmlRoot, $html, $e, $options);
	}
	
}

?>