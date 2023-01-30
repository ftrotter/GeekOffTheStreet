Geek Off The Street python version
==============================
Still waiting on Mirrulations to become stable.. but in advance of that.. 
wanted to be able to write something that could handle a regulatory text analysis regarding the provider directory. 

* mirror_docket.py	
* pdf_to_text.py uses google cloud Vision API to convert pdfs to text

mirror_docket.py
----------------
Given a docket ID, hits the regulation data API https://open.gsa.gov/api/regulationsgov/
and downloads all the things.. going from Dockets, to Documents, to Comments to Attachments. 
I expect this to be a one-off script as I wait for Mirrulations, so I will expect documentation will be minimal. 




pdf_to_text.py
-----------------
functions originally written by https://github.com/szeamer at https://github.com/szeamer/google-cloud-vision-script
ported to loop over a local directory of pdfs downloaded from regulations.gov
which were uploaded to Google Storage as per Silvias instructions here: https://towardsdatascience.com/how-to-extract-the-text-from-pdfs-using-python-and-the-google-cloud-vision-api-7a0a798adc13

Thank you Silvia! 

Notes
==================

Background
------------

* the idea of "tribal epistomology" https://www.vox.com/policy-and-politics/2017/3/22/14762030/donald-trump-tribal-epistemology
* new bulk download feature from regulations.gov https://www.regulations.gov/bulkdownload
* blog post on release of bulk downloads https://www.gsa.gov/blog/2022/10/27/bulk-data-download-is-here-on-regulationsgov
* the old regulations bulk download https://www.govinfo.gov/bulkdata/CFR (does not include comments at all.. is in XML)
* All of the bulk data available from govinfo etc https://www.govinfo.gov/bulkdata
* Federal Register API https://www.federalregister.gov/reader-aids/developer-resources/rest-api 
* Current regulations.gov API https://open.gsa.gov/api/regulationsgov/




The Regulations 
---------------

The regulations I am bothering with
https://www.federalregister.gov/documents/2022/10/07/2022-21904/request-for-information-national-directory-of-healthcare-providers-and-services

What others have said about it: 
https://www.fiercehealthcare.com/hospitals-health-systems/industry-voices-when-it-comes-to-provider-directories-accuracy-hard

https://www.fiercehealthcare.com/providers/cms-seeks-feedback-plans-build-centralized-nationwide-provider-directory

Defactos review
https://defacto.health/2022/12/09/responses-to-the-cms-rfi-on-national-healthcare-directory/


Relevant Text Processing Tools
--------------------
Understanding stop words with nltk 
https://www.geeksforgeeks.org/removing-stop-words-nltk-python/

A fast way to calculate the distance between text: 
https://ceptord.net/20181215-polyleven.html

MariaDB has full text searching:
https://mariadb.com/kb/en/full-text-index-overview/


python difflib has promise:
Here is a extensive tutorial
https://coderzcolumn.com/tutorials/python/difflib-simple-way-to-find-out-differences-between-sequences-file-contents-using-python

Here is the basic documentation
https://docs.python.org/3/library/difflib.html

Could be a faster implementation of diff
https://pypi.org/project/fast-diff-match/


