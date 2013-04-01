<?php require_once(dirname(__FILE__) . "/otalist.php"); ?> 
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $config["title"];?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.1.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <meta name="author" content="@gunta,@enefekt" />
	<meta name="viewport" content="width=320, user-scalable=no, initial-scale=1, maximum-scale=1">
  </head>
  <body>

  	<div class="container">
  		<img src="install.png" width="50" height="41" style="position: absolute; float: top; padding-top: 10px;" />
    	<h2 style="padding-left: 55px;"><?php echo $config["title"];?></h2>

    	<?php if ($lang == "ja") : ?>
    	    <p>下記ボタンをタップすると、直接iOSデバイスに<b>インストール</b>されます。</p>
    	<?php else : ?>
            <p>Tap below to Install <b>directly</b> on your iOS device.</p>
    	<?php endif; ?>

    	<br />

        <?php foreach ($generatedUrls as $linkFilename => $linkUrl) : ?>
            <a href="<?php echo $linkUrl; ?>" class="btn btn-large btn-block btn-inverse" style="font-weight:bold;" type="button">
            <?php echo $linkFilename; ?> <i class="icon-download-alt icon-white"></i></a>
        <?php endforeach; ?>

		</div>
  </body>
</html>