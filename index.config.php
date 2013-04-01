<?php
    $config = array(
        // Title for the page
        "title" => "iOS OTA Downloads",

        // Parses each plist file
        "parsePlist" => true,

        // Base local path for scanning for plist/ipa files
        "localPath" => ".",

        // Base url for the plist/ipa files with trailing slash. Not needed if parsePlist == true.
        "baseUrl" => "http://.../",
        
        //The max number of builds to display
        "maxDisplayCount" => 25
    );

?>