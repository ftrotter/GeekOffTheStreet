<?php
//This is used if you want to setup your own Google Cloud mirroring...
//hopefully we can get collective mirroring working so that you do not need to do this...

require 'vendor/autoload.php';

use Google\Cloud\Core\ServiceBuilder;

// Authenticate using a keyfile path
$cloud = new ServiceBuilder([
    'keyFilePath' => './google_keyfile.json'
]);

// Authenticate using keyfile data
$cloud = new ServiceBuilder([
    'keyFile' => json_decode(file_get_contents('./google_keyfile.json'), true)
]);



