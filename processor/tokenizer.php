<?php

class Tokenizer {

	var $handle = null;
	
	function __construct($handle = null) {
		$this->handle = $handle;
	}

	function tokenize() {
		$tokens = array();
		
		if($this->handle == null)
			return $tokens;
	
		while(!feof($this->handle)){
			$line = rtrim(fgets($this->handle));
			$tokens = array_merge($tokens, $this->parseLine($line));
		}
		
		return $tokens;
	}
	
	function parseLine($line) {
		return $this->getTokens($line, 0);
	}
	
	function getTokens($line, $startIndex) {
		$split = str_split($line);
		$tokens = array();
		$token = "";
		$i = $startIndex;
		
		while( $i < sizeof($split) ) {
			switch( $split[$i] ) {
			case "[":
				$result = $this->getKeyword($split, $i);
				$i = $result[0];
				
				if( $result[1])  {
					if( !empty($token) )
						$tokens[] = $token;
						
					$token = $result[2];
					$tokens[] = $token;
					$token = "";
				}
				else {
					$token = $token . $result[2];
				}
				break;
			default:
				if($split[$i] != "\s")
					$token = $token . $split[$i];
			}
			$i++;
		}
		
		if( !empty($token) ) {
			$tokens[] = $token;
			$token = "";
		}
		
		return $tokens;
	}

	function getKeyword($split, $startIndex = 0) {
		$pos = array_search("]", array_slice($split, $startIndex));
		
		if( is_numeric($pos) ) {
			$token = implode( array_slice($split, $startIndex, $pos + 1) );
			$isValid = preg_match("/^\[([a-zA-Z\*])+(,[a-zA-Z]+=.+)*\]/", $token) || preg_match("/^\[\/([a-zA-Z])+\]/", $token);
			if( $isValid ) {
				return array( $startIndex + $pos, true, $token);
			}
			else {
				return array( $startIndex, false, implode( array_slice($split, $startIndex, 1) ) );
			}
		}
		else {
			return array( $startIndex, false, implode( array_slice($split, $startIndex, 1) ) );
		}
	}

}

?>