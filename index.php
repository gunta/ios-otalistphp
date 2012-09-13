<?php 
require_once(dirname(__FILE__) . "/index.config.php");


$generatedUrls = array();
global $config;

if ($handle = opendir($config["localPath"])) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && pathinfo($entry, PATHINFO_EXTENSION) == "plist") {
            getGeneratedUrls($entry);
        }
    }
    closedir($handle);
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

$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

?> 
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $config["title"];?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <meta name="author" content="@gunta" />
	<meta name="viewport" content="width=320, user-scalable=no, initial-scale=1, maximum-scale=1">
  </head>
  <body>

  	<div class="container">
    	<h2><?php echo $config["title"];?></h2>

    	<?php if ($lang == "ja") : ?>
    	    <h4>ダウンロードページ</h4>

    	    <p>下記ボタンをタップすると、直接iOSデバイスに<b>インストール</b>されます。</p>
    	<?php else : ?>
            <h4>Download page</h4>

    	    <p>Tap below to Install <b>directly</b> on your iOS device.</p>
    	<?php endif; ?>

    	<br />

        <?php foreach ($generatedUrls as $linkFilename => $linkUrl) : ?>
            <a href="<?php echo $linkUrl; ?>" class="btn btn-large btn-block btn-primary" style="font-weight:bold;" type="button">
            <?php echo $linkFilename; ?> <i class="icon-download-alt icon-white"></i></a>
        <?php endforeach; ?>

		</div>
  </body>
</html>