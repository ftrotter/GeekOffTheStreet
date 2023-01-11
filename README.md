# Geek Off The Street
An PHP implementation of the Regulations.gov API that will eventually attempt do some smart mirroring.


Because apparently I have to.
===============================

This is a requirement of using the API. So lets put it front and center:

“Regulations.gov and the Federal government cannot verify and are not responsible for the accuracy or authenticity of the data or analyses derived from the data after the data has been retrieved from Regulations.gov.” 
“This product uses the Regulations.gov Data API but is neither endorsed nor certified by Regulations.gov.”


Installation
================

This should run out of the box with php 7

First install composer if you have not already..

From the command prompt

`
$ composer install
`

Setup API credentials
=============
This is a simple project it is composed of several php functions that understand how to work with documents and dockets from regulations.gov

To run the code, you must apply for a Regulations.gov API key here: https://regulationsgov.github.io/developers/
The resulting API key needs to be stored in regulations.gov.api.yaml the file regulations.gov.api.yaml.template exists to show you how to build that file. 

This code also expects that you will store the underlying data on Google Cloud Storage (googles competitors to Amazon S3) in a way that allows the public download 
of your files. For the time being, the scripts will not work without google cloud credentials to store the resulting data. 
Google likes to distribute its authentication credentials in a JSON file, which this project expects to be named google_keyfile.json

So you need to setup the following two configuration files properly

* regulations.gov.api.yaml - for your regulations.gov API credentials
* google_keyfile.json - for your google cloud storage credentials. 

There are entries in .gitignore to prevent you from commiting these files, if you are contributing code, this should keep you from 
making the mistake and publishing your API credentials. 


Run the code
================


* recurse_and_mirror_docket.function.php understands how to accept a docket_id and download all of the corresponding documents in bulk
* recurse_and_mirror_documents.function.php usually documents are json files only, but they can also be pdfs/html. This can download all of the things.
* ./data/ is where the files you temporarily scrape live on the local machine. 
*  mine_docket_data.php loops over the json files found in /data/ for specific mentions of document_ids with content not found in the bulk json download.. and downloads them using recurse_and_mirror_documents.function.php
* search_for_dockets.php accepts a command line argument for a topic to search regulations.gov for, and downloads the resulting search results. By default this process excludes comments and other citizen uploaded content, so that you are only searching for docket_ids for the topics that are being discussed by the regulatory process directly. 
* mine_searches_data.php loops over the search json files and then uses recurse_and_mirror_docket.function.php to download all of the documents in the docket. 

So basically, the workflow goes... 
1. use search_for_dockets.php to search for the type of content that you want
2. use mine_searches_data.php to get the dockets from the search results
3. use mine_docket_data.php to ensure that you have all of the pdfs and html that is associated with your documents.

Eventually we will have some code in here to respect the API rate-limiting

```
php search_for_dockets 'your search term'
php mine_searches_data.php
php mine_docket_data.php
```

These are all command line programs http://linuxcommand.org/

Understanding the data results
============
Take a look at the [Data Directory README](DATA_DIR_README.md)

Also see...
=============

The spiritual predecesor to the [Mirrulations Project](https://github.com/MoravianCollege/mirrulations)




