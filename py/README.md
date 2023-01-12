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



