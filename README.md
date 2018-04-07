# GeekOffTheStreet
An PHP implementation of the Regulations.gov API that will eventually attempt do some smart mirroring.


Because apparently I have to.
===============================

“Regulations.gov and the Federal government cannot verify and are not responsible for the accuracy or authenticity of the data or analyses derived from the data after the data has been retrieved from Regulations.gov.” 
“This product uses the Regulations.gov Data API but is neither endorsed nor certified by Regulations.gov.”


Installation
================

* We want to install protobuffer because it might make using google cloud a little cheaper. Just in case...
* `sudo apt-get install php7.0-dev` So that we have phpize...
* `sudo apt-get install libz-dev` Jesus who gets make errors anymore...
* `sudo pecl channel-update pecl.php.net` which we will need to update pecl
* `sudo pecl install grpc`
* `sudo pecl install protobuf` 
* `composer install` should read the contents of the composer.json and get you all up to speed...
* Add the following lines to your cli php.ini
```
extension=grpc.so
extension=protobuf.so
```

You should be goodo to go... the jury is out whether installing grpc and protobuf actually have any benifit for this project.
https://github.com/GoogleCloudPlatform/google-cloud-php/issues/999


