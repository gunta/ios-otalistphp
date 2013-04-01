<?php 
require_once(dirname(__FILE__) . "/index.config.php");

$generatedUrls = array();
global $config;

if($candidates = scanDirByModifiedDate($config["localPath"])) {
    $max = (isset($config["maxDisplayCount"])) ? $config["maxDisplayCount"] : 1000;
    $counter = 0;
    foreach($candidates as $entry) {
		if($entry != "." && $entry != ".." && pathinfo($entry, PATHINFO_EXTENSION) == "plist") {
            getGeneratedUrls($entry);
            $counter++;
        }
        if($counter >= $max)
        	break;
	}
}

// Plist parser
function parseValue( $valueNode ) {
    $valueType = $valueNode->nodeName;
    $transformerName = "parse_$valueType";
    if ( is_callable($transformerName) ) {
        return call_user_func($transformerName, $valueNode);
    }
    return null;
}

function parse_integer( $integerNode ) {
  return $integerNode->textContent;
}

function parse_string( $stringNode ) {
  return $stringNode->textContent;
}

function parse_date( $dateNode ) {
  return $dateNode->textContent;
}

function parse_true( $trueNode ) {
  return true;
}

function parse_false( $trueNode ) {
  return false;
}

function parse_dict( $dictNode ) {
  $dict = array();
  for (
    $node = $dictNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeName == "key" ) {
      $key = $node->textContent;
      $valueNode = $node->nextSibling;
      while ( $valueNode->nodeType == XML_TEXT_NODE ) {
        $valueNode = $valueNode->nextSibling;
      }
      $value = parseValue($valueNode);
      $dict[$key] = $value;
    }
  }
  return $dict;
}

function parse_array( $arrayNode ) {
  $array = array();
  for (
    $node = $arrayNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeType == XML_ELEMENT_NODE ) {
      array_push($array, parseValue($node));
    }
  }
  return $array;
}

function parsePlist( $path ) {
  $document = new DOMDocument();
  $document->load($path);
  $plistNode = $document->documentElement;
  $root = $plistNode->firstChild;
  while ( $root->nodeName == "#text" ) {
    $root = $root->nextSibling;
  }
  return parseValue($root);
}

function getGeneratedUrls($plistFile) {
    global $config, $generatedUrls;

    if ($config["parsePlist"]) {
       $plistContent = parsePlist($plistFile);

        $ipaUrl = $plistContent["items"][0]["assets"][0]["url"];
        $name = $plistContent["items"][0]["metadata"]["title"];
        $plistUrl = pathinfo($ipaUrl, PATHINFO_DIRNAME) . "/" . pathinfo($ipaUrl, PATHINFO_FILENAME) . ".plist";
    } else {
        // non-parsing plist version
        global $config, $generatedUrls;
        $name = pathinfo($plistFile, PATHINFO_FILENAME);
        $plistUrl = $config["baseUrl"] . $name . ".plist";
    }

    $generatedUrls[$name] = "itms-services://?action=download-manifest&url=" . urlencode($plistUrl);
}

function scanDirByModifiedDate($dir) {
    $ignored = array('.', '..', '.htaccess');

    $files = array();    
    foreach(scandir($dir) as $file) {
        if(in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

    return ($files) ? $files : false;
}

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

?>